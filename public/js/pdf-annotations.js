/**
 * pdf-annotations.js
 * Simpan di: public/js/pdf-annotations.js
 * Load SETELAH pdf-viewer.js
 *
 * Requires globals exposed by pdf-viewer.js:
 *   window._pdfViewer = { pageNum, getScale, pdfDoc, stage, annotLayer, textLayer, snack, queueRender }
 *
 * Dan window.PDF_CONFIG.slug, window.PDF_CONFIG.isGuest, window.PDF_CONFIG.csrfToken
 */
(function () {
    'use strict';

    // ── Guard: hanya aktif untuk user yang sudah login ─────────────────
    if (!window.PDF_CONFIG || window.PDF_CONFIG.isGuest) return;

    const cfg = window.PDF_CONFIG;
    const slug = cfg.slug;
    const API_BASE = `/api/annotations/${slug}`;
    const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Wait for pdf-viewer to expose its API ─────────────────────────
    function waitForViewer(cb) {
        if (window._pdfViewer) { cb(); return; }
        const t = setInterval(() => { if (window._pdfViewer) { clearInterval(t); cb(); } }, 80);
    }

    waitForViewer(init);

    // ═══════════════════════════════════════════════════════════════════
    function init() {
        const V = window._pdfViewer; // shorthand

        // ── DOM refs ──────────────────────────────────────────────────
        const stage = V.stage;
        const annotLayer = V.annotLayer;
        const freehandCanvas = document.getElementById('freehand-canvas');
        const floatTb = document.getElementById('annot-floating-toolbar');
        const stickyPopup = document.getElementById('sticky-popup');
        const annotPanel = document.getElementById('annot-panel');
        const apList = document.getElementById('ap-list');
        const syncIndicator = document.getElementById('annot-sync-indicator');
        const syncText = document.getElementById('annot-sync-text');
        const eraserCursor = document.getElementById('eraser-cursor');

        if (!floatTb) return; // blade snippet not present (guest)

        // ── State ──────────────────────────────────────────────────────
        let annotations = [];        // all loaded annotations
        let undoStack = [];        // array of {action:'add'|'del', annot}
        let redoStack = [];
        let activeTool = 'highlight';
        let activeColor = 'yellow';
        let activeSize = 2;
        let activeShape = 'rect';
        let isDrawing = false;
        let drawStart = null;      // {x, y} normalized
        let currentPath = [];        // freehand points
        let pendingSticky = null;    // {x, y} normalized where sticky will land
        let syncTimer = null;

        // ── Freehand canvas ctx ───────────────────────────────────────
        const fhCtx = freehandCanvas.getContext('2d');

        // ═══════════════ COLOR HELPERS ═════════════════════════════════
        const COLOR_HEX = {
            yellow: '#ffd700', green: '#4ade80', red: '#ef4444',
            blue: '#60a5fa', orange: '#ff6b18', black: '#1a1a1a', white: '#ffffff',
        };
        const getHex = c => COLOR_HEX[c] || c;

        // ═══════════════ API LAYER ══════════════════════════════════════
        function showSync(msg = 'Menyimpan...') {
            syncText.textContent = msg;
            syncIndicator.classList.add('show');
        }
        function hideSync() { setTimeout(() => syncIndicator.classList.remove('show'), 800); }

        async function apiGet() {
            const r = await fetch(API_BASE, { credentials: 'same-origin' });
            if (!r.ok) throw new Error('Fetch failed');
            return (await r.json()).data;
        }

        async function apiPost(payload) {
            const r = await fetch(API_BASE, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
                body: JSON.stringify(payload),
            });
            if (!r.ok) throw new Error('Save failed');
            return (await r.json()).data;
        }

        async function apiDelete(id) {
            const r = await fetch(`${API_BASE}/${id}`, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF() },
            });
            if (!r.ok) throw new Error('Delete failed');
        }

        async function apiDeletePage(page) {
            const r = await fetch(`${API_BASE}/page/${page}`, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF() },
            });
            if (!r.ok) throw new Error('Delete page failed');
        }

        // ═══════════════ LOAD ═══════════════════════════════════════════
        async function loadAnnotations() {
            try {
                annotations = await apiGet();
                renderAll();
                refreshPanel();
            } catch (e) { console.warn('[annot] load failed', e); }
        }

        // ═══════════════ SAVE ═══════════════════════════════════════════
        async function saveAnnotation(annot) {
            showSync();
            try {
                const saved = await apiPost(buildPayload(annot));
                // Replace temp id with DB id
                const idx = annotations.findIndex(a => a.id === annot.id);
                if (idx !== -1) { annotations[idx].id = saved.id; annot.id = saved.id; }
                hideSync();
                refreshPanel();
            } catch (e) {
                console.error('[annot] save failed', e);
                V.snack('❌ Gagal menyimpan anotasi');
                syncIndicator.classList.remove('show');
            }
        }

        function buildPayload(annot) {
            const p = {
                page: annot.page, type: annot.type, color: annot.color,
                stroke_width: annot.strokeWidth || 2,
                fill_opacity: annot.fillOpacity || 0,
            };
            if (annot.rect) {
                p.rect_x = annot.rect.x; p.rect_y = annot.rect.y;
                p.rect_w = annot.rect.w; p.rect_h = annot.rect.h;
            }
            if (annot.selectedText) p.selected_text = annot.selectedText;
            if (annot.comment) p.comment = annot.comment;
            if (annot.pathPoints) p.path_points = annot.pathPoints;
            if (annot.shapeType) p.shape_type = annot.shapeType;
            return p;
        }

        async function deleteAnnotation(id) {
            showSync('Menghapus...');
            try {
                await apiDelete(id);
                annotations = annotations.filter(a => a.id !== id);
                hideSync();
                renderAll();
                refreshPanel();
            } catch (e) {
                console.error('[annot] delete failed', e);
                V.snack('❌ Gagal menghapus anotasi');
                syncIndicator.classList.remove('show');
            }
        }

        // ═══════════════ UNDO / REDO ════════════════════════════════════
        function pushUndo(action, annot) {
            undoStack.push({ action, annot: { ...annot } });
            redoStack = [];
            updateUndoRedoBtns();
        }

        async function undo() {
            if (!undoStack.length) return;
            const { action, annot } = undoStack.pop();
            redoStack.push({ action, annot });
            if (action === 'add') {
                await deleteAnnotation(annot.id);
            }
            // 'del' undo: re-create — simplified: just reload
            updateUndoRedoBtns();
        }

        function updateUndoRedoBtns() {
            document.getElementById('aft-undo').disabled = undoStack.length === 0;
            document.getElementById('aft-redo').disabled = redoStack.length === 0;
        }

        // ═══════════════ RENDER ═════════════════════════════════════════
        function renderAll() {
            const page = V.pageNum;
            const scale = V.getScale();

            // Remove only annotation children (keep search highlights etc.)
            annotLayer.querySelectorAll(
                '.annot-highlight,.annot-shape,.sticky-note,.fh-svg'
            ).forEach(el => el.remove());

            // Resize freehand canvas
            freehandCanvas.width = Math.floor(V.stage.offsetWidth);
            freehandCanvas.height = Math.floor(V.stage.offsetHeight);
            freehandCanvas.style.width = V.stage.style.width;
            freehandCanvas.style.height = V.stage.style.height;

            annotations.filter(a => a.page === page).forEach(a => renderOne(a, scale));
        }

        function renderOne(a, scale) {
            scale = scale || V.getScale();
            if (a.type === 'highlight' || a.type === 'comment') renderHighlight(a, scale);
            else if (a.type === 'freehand') renderFreehand(a, scale);
            else if (a.type === 'shape') renderShape(a, scale);
            else if (a.type === 'sticky') renderSticky(a, scale);
        }

        // ── Highlight ─────────────────────────────────────────────────
        function renderHighlight(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.className = `annot-highlight color-${a.color}`;
            el.style.left = (a.rect.x * scale) + 'px';
            el.style.top = (a.rect.y * scale) + 'px';
            el.style.width = (a.rect.w * scale) + 'px';
            el.style.height = (a.rect.h * scale) + 'px';
            el.dataset.id = a.id;
            if (a.comment) {
                el.title = a.comment;
                el.style.borderBottom = '2px dashed ' + getHex(a.color);
            }
            el.addEventListener('click', ev => {
                ev.stopPropagation();
                showAnnotTooltip(a, ev.clientX, ev.clientY);
            });
            annotLayer.appendChild(el);
        }

        // ── Freehand SVG ──────────────────────────────────────────────
        function renderFreehand(a, scale) {
            if (!a.pathPoints || !a.pathPoints.length) return;
            const pts = a.pathPoints;
            const d = pts.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x * scale} ${p.y * scale}`).join(' ');
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.classList.add('fh-svg');
            svg.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:6;overflow:visible;';
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            path.setAttribute('stroke', getHex(a.color));
            path.setAttribute('stroke-width', (a.strokeWidth || 2) * (scale / V.getScale()));
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('fill', 'none');
            path.style.pointerEvents = 'stroke';
            path.style.cursor = 'pointer';
            path.addEventListener('click', ev => { ev.stopPropagation(); showAnnotTooltip(a, ev.clientX, ev.clientY); });
            svg.appendChild(path);
            annotLayer.appendChild(svg);
        }

        // ── Shape ─────────────────────────────────────────────────────
        function renderShape(a, scale) {
            if (!a.rect) return;
            const x = a.rect.x * scale, y = a.rect.y * scale;
            const w = a.rect.w * scale, h = a.rect.h * scale;
            const sw = (a.strokeWidth || 2);
            const color = getHex(a.color);
            const fillOpacity = a.fillOpacity || 0;

            const wrap = document.createElement('div');
            wrap.className = 'annot-shape';
            wrap.dataset.id = a.id;
            wrap.style.left = x + 'px';
            wrap.style.top = y + 'px';
            wrap.style.width = w + 'px';
            wrap.style.height = h + 'px';

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
            svg.style.overflow = 'visible';

            const fill = fillOpacity > 0
                ? `${color}${Math.round(fillOpacity * 255).toString(16).padStart(2, '0')}`
                : 'none';

            let el;
            if (a.shapeType === 'rect') {
                el = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                el.setAttribute('x', sw / 2); el.setAttribute('y', sw / 2);
                el.setAttribute('width', Math.max(1, w - sw)); el.setAttribute('height', Math.max(1, h - sw));
                el.setAttribute('rx', 3);
            } else if (a.shapeType === 'ellipse') {
                el = document.createElementNS('http://www.w3.org/2000/svg', 'ellipse');
                el.setAttribute('cx', w / 2); el.setAttribute('cy', h / 2);
                el.setAttribute('rx', Math.max(1, w / 2 - sw / 2));
                el.setAttribute('ry', Math.max(1, h / 2 - sw / 2));
            } else if (a.shapeType === 'arrow') {
                // Arrow from top-left to bottom-right
                el = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                el.setAttribute('x1', 0); el.setAttribute('y1', 0);
                el.setAttribute('x2', w); el.setAttribute('y2', h);
                el.setAttribute('marker-end', 'url(#arrowhead-' + a.id + ')');
                const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                const marker = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                marker.setAttribute('id', 'arrowhead-' + a.id);
                marker.setAttribute('markerWidth', '10'); marker.setAttribute('markerHeight', '7');
                marker.setAttribute('refX', '10'); marker.setAttribute('refY', '3.5');
                marker.setAttribute('orient', 'auto');
                const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                polygon.setAttribute('points', '0 0, 10 3.5, 0 7');
                polygon.setAttribute('fill', color);
                marker.appendChild(polygon);
                defs.appendChild(marker);
                svg.appendChild(defs);
            }

            if (el) {
                el.setAttribute('stroke', color);
                el.setAttribute('stroke-width', sw);
                if (a.shapeType !== 'arrow') el.setAttribute('fill', fill);
                svg.appendChild(el);
            }

            wrap.appendChild(svg);
            wrap.addEventListener('click', ev => { ev.stopPropagation(); showAnnotTooltip(a, ev.clientX, ev.clientY); });
            annotLayer.appendChild(wrap);
        }

        // ── Sticky Note ───────────────────────────────────────────────
        function renderSticky(a, scale) {
            const el = document.createElement('div');
            el.className = 'sticky-note';
            el.dataset.id = a.id;
            el.dataset.color = a.color;
            el.style.left = (a.rect.x * scale) + 'px';
            el.style.top = (a.rect.y * scale) + 'px';
            el.innerHTML = `
                <div class="sn-header">
                    <span>📌 Catatan</span>
                    <button class="sn-del" data-id="${a.id}">✕</button>
                </div>
                <div class="sn-body">${escHtml(a.comment || '')}</div>`;
            el.querySelector('.sn-del').addEventListener('click', ev => {
                ev.stopPropagation();
                deleteAnnotation(a.id);
            });
            // Drag support
            makeDraggable(el, a, scale);
            annotLayer.appendChild(el);
        }

        function makeDraggable(el, a, scale) {
            let ox = 0, oy = 0, startX = 0, startY = 0, dragging = false;
            el.addEventListener('mousedown', ev => {
                if (ev.target.classList.contains('sn-del')) return;
                dragging = true;
                ox = parseFloat(el.style.left);
                oy = parseFloat(el.style.top);
                startX = ev.clientX; startY = ev.clientY;
                ev.preventDefault();
            });
            document.addEventListener('mousemove', ev => {
                if (!dragging) return;
                const nx = ox + (ev.clientX - startX);
                const ny = oy + (ev.clientY - startY);
                el.style.left = nx + 'px';
                el.style.top = ny + 'px';
            });
            document.addEventListener('mouseup', async () => {
                if (!dragging) return;
                dragging = false;
                // Update position in DB
                a.rect.x = parseFloat(el.style.left) / scale;
                a.rect.y = parseFloat(el.style.top) / scale;
                try {
                    await fetch(`${API_BASE}/${a.id}`, {
                        method: 'PUT', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
                        body: JSON.stringify({ rect_x: a.rect.x, rect_y: a.rect.y }),
                    });
                } catch (e) { console.warn('[annot] drag update failed', e); }
            });
        }

        function escHtml(s) {
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // ═══════════════ TOOLTIP ════════════════════════════════════════
        const oldTip = document.getElementById('annot-tooltip');
        function showAnnotTooltip(a, cx, cy) {
            if (!oldTip) return;
            document.getElementById('annot-tooltip-text').textContent =
                a.comment ? `💬 ${a.comment}` :
                    a.type === 'freehand' ? `✏️ Gambar bebas (${a.color})` :
                        a.type === 'shape' ? `⬛ Shape (${a.shapeType}, ${a.color})` :
                            `🖊️ Highlight ${a.color} — "${(a.selectedText || '').substring(0, 55)}..."`;
            oldTip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            oldTip.style.left = Math.min(cx, vw - 272) + 'px';
            oldTip.style.top = (cy + 112 > vh ? cy - 108 : cy + 12) + 'px';

            document.getElementById('annot-tooltip-del').onclick = () => {
                deleteAnnotation(a.id);
                oldTip.classList.remove('show');
            };
        }

        // ═══════════════ TOOLBAR UI ══════════════════════════════════════
        // Show toolbar only when pdf loaded
        V.onReady(() => floatTb.classList.add('visible'));

        // Tool selection
        floatTb.querySelectorAll('[data-tool]').forEach(btn => {
            btn.addEventListener('click', () => {
                activeTool = btn.dataset.tool;
                floatTb.querySelectorAll('[data-tool]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Mode classes
                stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode');
                if (activeTool === 'freehand') stage.classList.add('freehand-mode');
                else if (activeTool === 'shape') stage.classList.add('shape-mode');
                else if (activeTool === 'eraser') stage.classList.add('eraser-mode');

                // Show/hide size picker
                const sizeWrap = document.getElementById('aft-sizes');
                sizeWrap.style.display = ['freehand', 'shape'].includes(activeTool) ? 'flex' : 'none';

                // Show/hide shape type picker
                const shapeTypes = document.getElementById('aft-shape-types');
                shapeTypes.style.display = activeTool === 'shape' ? 'flex' : 'none';

                // Eraser cursor
                eraserCursor.style.display = activeTool === 'eraser' ? 'block' : 'none';
            });
        });

        // Color selection
        document.querySelectorAll('.aft-color').forEach(swatch => {
            swatch.addEventListener('click', () => {
                activeColor = swatch.dataset.color;
                document.querySelectorAll('.aft-color').forEach(s => s.classList.remove('selected'));
                swatch.classList.add('selected');
            });
        });

        // Size selection
        document.querySelectorAll('.aft-size').forEach(dot => {
            dot.addEventListener('click', () => {
                activeSize = parseInt(dot.dataset.size);
                document.querySelectorAll('.aft-size').forEach(d => d.classList.remove('selected'));
                dot.classList.add('selected');
            });
        });

        // Shape type selection
        floatTb.querySelectorAll('[data-shape]').forEach(btn => {
            btn.addEventListener('click', () => {
                activeShape = btn.dataset.shape;
                floatTb.querySelectorAll('[data-shape]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Undo / Redo
        document.getElementById('aft-undo').addEventListener('click', undo);
        document.getElementById('aft-redo').addEventListener('click', () => { /* simplified */ });

        // Panel toggle
        document.getElementById('aft-panel-btn').addEventListener('click', () => {
            annotPanel.classList.toggle('open');
            refreshPanel();
        });
        document.getElementById('ap-close-btn').addEventListener('click', () => annotPanel.classList.remove('open'));
        document.getElementById('ap-clear-btn').addEventListener('click', async () => {
            if (!confirm(`Hapus semua anotasi di halaman ${V.pageNum}?`)) return;
            showSync('Menghapus...');
            try {
                await apiDeletePage(V.pageNum);
                annotations = annotations.filter(a => a.page !== V.pageNum);
                hideSync();
                renderAll();
                refreshPanel();
                V.snack('🗑 Anotasi halaman ini dihapus');
            } catch (e) { V.snack('❌ Gagal hapus'); syncIndicator.classList.remove('show'); }
        });

        // ═══════════════ PANEL REFRESH ══════════════════════════════════
        function refreshPanel() {
            if (!apList) return;
            apList.innerHTML = '';
            const sorted = [...annotations].sort((a, b) => a.page - b.page || a.id - b.id);
            if (!sorted.length) {
                apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.<br>Pilih tool lalu mulai beri catatan!</div>';
                return;
            }
            sorted.forEach(a => {
                const item = document.createElement('div');
                item.className = 'ap-item';
                const typeLabel = { highlight: '🖊 Highlight', freehand: '✏️ Freehand', comment: '💬 Komentar', sticky: '📌 Sticky', shape: '⬛ Shape' }[a.type] || a.type;
                const preview = a.comment || a.selectedText || `${a.shapeType || a.type}`;
                item.innerHTML = `
                    <div class="ap-dot" style="background:${getHex(a.color)}"></div>
                    <div class="ap-item-body">
                        <div><span class="ap-item-type">${typeLabel}</span><span class="ap-item-pg">Hal.${a.page}</span></div>
                        <div class="ap-item-text">${escHtml(preview.substring(0, 60))}</div>
                    </div>
                    <button class="ap-item-del" data-id="${a.id}" title="Hapus">🗑</button>`;
                item.querySelector('.ap-item-body').addEventListener('click', () => {
                    if (a.page !== V.pageNum) V.queueRender(a.page);
                    annotPanel.classList.remove('open');
                });
                item.querySelector('.ap-item-del').addEventListener('click', ev => {
                    ev.stopPropagation();
                    deleteAnnotation(a.id);
                });
                apList.appendChild(item);
            });
        }

        // ═══════════════ HIGHLIGHT TOOL ══════════════════════════════════
        // (Uses mouseup on text layer, same as before but routes to new system)

        document.addEventListener('mouseup', ev => {
            if (!['highlight', 'comment'].includes(activeTool)) return;
            if (ev.target.closest('#annot-floating-toolbar,#comment-popup,#annot-tooltip')) return;
            setTimeout(() => {
                const sel = window.getSelection();
                if (!sel || sel.isCollapsed || !sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                if (!V.textLayer.contains(range.commonAncestorContainer)) return;

                const rect = getSelectionRect(range);
                if (!rect) return;
                const selText = sel.toString();

                if (activeTool === 'comment') {
                    pendingCommentRect = rect;
                    pendingCommentText = selText;
                    openCommentPopup(sel.getRangeAt(0).getBoundingClientRect());
                } else {
                    addAnnotation({
                        type: 'highlight', color: activeColor,
                        rect, selectedText: selText, comment: '',
                    });
                    sel.removeAllRanges();
                }
            }, 50);
        });

        let pendingCommentRect = null, pendingCommentText = '';
        function openCommentPopup(br) {
            const pop = document.getElementById('comment-popup');
            const vw = window.innerWidth, vh = window.innerHeight;
            pop.style.left = Math.min((br.left || 100) - 140, vw - 296) + 'px';
            pop.style.top = ((br.bottom || 200) + 10 + 160 > vh ? (br.top || 200) - 170 : (br.bottom || 200) + 10) + 'px';
            pop.classList.add('show');
            document.getElementById('comment-text').value = '';
            document.getElementById('comment-text').focus();
        }

        // Override old comment-save to use new system
        document.getElementById('comment-save').addEventListener('click', () => {
            const comment = document.getElementById('comment-text').value.trim();
            if (!pendingCommentRect || !comment) { V.snack('Tulis komentar dulu!'); return; }
            addAnnotation({
                type: 'comment', color: activeColor,
                rect: pendingCommentRect,
                selectedText: pendingCommentText,
                comment,
            });
            window.getSelection()?.removeAllRanges();
            document.getElementById('comment-popup').classList.remove('show');
            pendingCommentRect = null; pendingCommentText = '';
        });

        function getSelectionRect(range) {
            const stRect = stage.getBoundingClientRect();
            const rects = Array.from(range.getClientRects());
            if (!rects.length) return null;
            const scale = V.getScale();
            const left = Math.min(...rects.map(r => r.left));
            const top = Math.min(...rects.map(r => r.top));
            const right = Math.max(...rects.map(r => r.right));
            const bottom = Math.max(...rects.map(r => r.bottom));
            return {
                x: (left - stRect.left) / scale,
                y: (top - stRect.top) / scale,
                w: (right - left) / scale,
                h: (bottom - top) / scale,
            };
        }

        // ═══════════════ FREEHAND DRAW ═══════════════════════════════════
        let fhPoints = [];

        freehandCanvas.addEventListener('pointerdown', ev => {
            if (activeTool !== 'freehand') return;
            isDrawing = true;
            fhPoints = [normalizePoint(ev)];
            freehandCanvas.setPointerCapture(ev.pointerId);
        });

        freehandCanvas.addEventListener('pointermove', ev => {
            if (activeTool === 'eraser') {
                eraserCursor.style.left = ev.clientX + 'px';
                eraserCursor.style.top = ev.clientY + 'px';
            }
            if (!isDrawing || activeTool !== 'freehand') return;
            fhPoints.push(normalizePoint(ev));
            drawFreehandPreview();
        });

        freehandCanvas.addEventListener('pointerup', async () => {
            if (!isDrawing || activeTool !== 'freehand') return;
            isDrawing = false;
            if (fhPoints.length < 2) return;
            fhCtx.clearRect(0, 0, freehandCanvas.width, freehandCanvas.height);
            await addAnnotation({
                type: 'freehand', color: activeColor,
                strokeWidth: activeSize,
                pathPoints: fhPoints,
            });
            fhPoints = [];
        });

        function drawFreehandPreview() {
            fhCtx.clearRect(0, 0, freehandCanvas.width, freehandCanvas.height);
            const scale = V.getScale();
            fhCtx.strokeStyle = getHex(activeColor);
            fhCtx.lineWidth = activeSize;
            fhCtx.lineCap = 'round';
            fhCtx.lineJoin = 'round';
            fhCtx.beginPath();
            fhPoints.forEach((p, i) => {
                if (i === 0) fhCtx.moveTo(p.x * scale, p.y * scale);
                else fhCtx.lineTo(p.x * scale, p.y * scale);
            });
            fhCtx.stroke();
        }

        function normalizePoint(ev) {
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            return {
                x: (ev.clientX - r.left) / scale,
                y: (ev.clientY - r.top) / scale,
            };
        }

        // ═══════════════ SHAPE DRAW ══════════════════════════════════════
        freehandCanvas.addEventListener('pointerdown', ev => {
            if (activeTool !== 'shape') return;
            isDrawing = true;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            drawStart = {
                x: (ev.clientX - r.left) / scale,
                y: (ev.clientY - r.top) / scale,
            };
            freehandCanvas.setPointerCapture(ev.pointerId);
        });

        freehandCanvas.addEventListener('pointermove', ev => {
            if (!isDrawing || activeTool !== 'shape') return;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            const cx = (ev.clientX - r.left) / scale;
            const cy = (ev.clientY - r.top) / scale;
            drawShapePreview(drawStart, cx, cy, scale);
        });

        freehandCanvas.addEventListener('pointerup', async ev => {
            if (!isDrawing || activeTool !== 'shape') return;
            isDrawing = false;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            const ex = (ev.clientX - r.left) / scale;
            const ey = (ev.clientY - r.top) / scale;
            fhCtx.clearRect(0, 0, freehandCanvas.width, freehandCanvas.height);
            if (Math.abs(ex - drawStart.x) < 5 && Math.abs(ey - drawStart.y) < 5) return;
            await addAnnotation({
                type: 'shape', color: activeColor,
                strokeWidth: activeSize, fillOpacity: 0.08,
                shapeType: activeShape,
                rect: {
                    x: Math.min(drawStart.x, ex),
                    y: Math.min(drawStart.y, ey),
                    w: Math.abs(ex - drawStart.x),
                    h: Math.abs(ey - drawStart.y),
                },
            });
            drawStart = null;
        });

        function drawShapePreview(s, ex, ey, scale) {
            fhCtx.clearRect(0, 0, freehandCanvas.width, freehandCanvas.height);
            const x = Math.min(s.x, ex) * scale, y = Math.min(s.y, ey) * scale;
            const w = Math.abs(ex - s.x) * scale, h = Math.abs(ey - s.y) * scale;
            fhCtx.strokeStyle = getHex(activeColor);
            fhCtx.lineWidth = activeSize;
            fhCtx.fillStyle = getHex(activeColor) + '14'; // ~8% fill
            fhCtx.beginPath();
            if (activeShape === 'rect') { fhCtx.roundRect(x, y, w, h, 3); }
            else if (activeShape === 'ellipse') { fhCtx.ellipse(x + w / 2, y + h / 2, w / 2, h / 2, 0, 0, Math.PI * 2); }
            else if (activeShape === 'arrow') {
                fhCtx.moveTo(s.x * scale, s.y * scale);
                fhCtx.lineTo(ex * scale, ey * scale);
            }
            fhCtx.stroke();
            if (activeShape !== 'arrow') fhCtx.fill();
        }

        // ═══════════════ STICKY NOTE ════════════════════════════════════
        // Click on canvas with sticky tool → show popup
        freehandCanvas.addEventListener('click', ev => {
            if (activeTool !== 'sticky') return;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            pendingSticky = {
                x: (ev.clientX - r.left) / scale,
                y: (ev.clientY - r.top) / scale,
            };
            const sp = stickyPopup;
            sp.style.left = Math.min(ev.clientX, window.innerWidth - 280) + 'px';
            sp.style.top = Math.min(ev.clientY + 10, window.innerHeight - 180) + 'px';
            sp.classList.add('show');
            document.getElementById('sticky-text').value = '';
            document.getElementById('sticky-text').focus();
        }, true);

        document.getElementById('sticky-save').addEventListener('click', () => {
            const text = document.getElementById('sticky-text').value.trim();
            if (!text || !pendingSticky) { V.snack('Tulis catatan dulu!'); return; }
            addAnnotation({
                type: 'sticky', color: activeColor, comment: text,
                rect: { x: pendingSticky.x, y: pendingSticky.y, w: 0, h: 0 },
            });
            stickyPopup.classList.remove('show');
            pendingSticky = null;
        });
        document.getElementById('sticky-cancel').addEventListener('click', () => {
            stickyPopup.classList.remove('show');
            pendingSticky = null;
        });

        // ═══════════════ ERASER ════════════════════════════════════════
        freehandCanvas.addEventListener('pointerdown', ev => {
            if (activeTool !== 'eraser') return;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            const px = (ev.clientX - r.left) / scale;
            const py = (ev.clientY - r.top) / scale;
            eraseAt(px, py, scale);
        });

        freehandCanvas.addEventListener('pointermove', ev => {
            if (activeTool !== 'eraser' || !(ev.buttons & 1)) return;
            const r = freehandCanvas.getBoundingClientRect();
            const scale = V.getScale();
            const px = (ev.clientX - r.left) / scale;
            const py = (ev.clientY - r.top) / scale;
            eraseAt(px, py, scale);
        });

        async function eraseAt(px, py, scale) {
            const TOLERANCE = 20 / scale;
            const toDelete = annotations.filter(a => {
                if (a.page !== V.pageNum) return false;
                if (a.rect) {
                    return px >= a.rect.x - TOLERANCE && px <= a.rect.x + a.rect.w + TOLERANCE &&
                        py >= a.rect.y - TOLERANCE && py <= a.rect.y + a.rect.h + TOLERANCE;
                }
                if (a.pathPoints) {
                    return a.pathPoints.some(p => Math.hypot(p.x - px, p.y - py) < TOLERANCE);
                }
                return false;
            });
            for (const a of toDelete) {
                await deleteAnnotation(a.id);
            }
        }

        document.addEventListener('mousemove', ev => {
            if (activeTool === 'eraser') {
                eraserCursor.style.left = ev.clientX + 'px';
                eraserCursor.style.top = ev.clientY + 'px';
            }
        });

        // ═══════════════ ADD ANNOTATION ═════════════════════════════════
        async function addAnnotation(partial) {
            const annot = {
                id: 'tmp_' + Date.now(),
                page: V.pageNum,
                ...partial,
            };
            annotations.push(annot);
            renderOne(annot, V.getScale());
            pushUndo('add', annot);
            refreshPanel();
            await saveAnnotation(annot);
        }

        // ═══════════════ KEYBOARD SHORTCUTS ═════════════════════════════
        document.addEventListener('keydown', ev => {
            if (['INPUT', 'TEXTAREA'].includes(ev.target.tagName)) return;
            if ((ev.ctrlKey || ev.metaKey) && ev.key === 'z') { ev.preventDefault(); undo(); }
        });

        // ═══════════════ HOOK INTO PDF VIEWER PAGE CHANGE ════════════════
        // Override _pdfViewer.onPageChange to re-render annotations
        const origOnPageChange = V.onPageChange;
        V.onPageChange = function (pageNum) {
            if (origOnPageChange) origOnPageChange(pageNum);
            renderAll();
        };

        // ═══════════════ BOOTSTRAP ═══════════════════════════════════════
        loadAnnotations();

        // Expose for debugging
        window._pdfAnnotations = { annotations, renderAll, loadAnnotations };
    }

})();
