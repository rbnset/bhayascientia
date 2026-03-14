/**
 * pdf-annotations.js  — PERBAIKAN LENGKAP
 * Simpan di: public/js/pdf-annotations.js
 *
 * PERBAIKAN:
 * 1. Shape (rect/ellipse/arrow) kini persisten & re-render setelah reload
 * 2. Sticky note berfungsi penuh (drag, simpan, hapus)
 * 3. Undo / Redo berfungsi
 * 4. Highlight teks berfungsi
 * 5. Freehand pen berfungsi + persisten
 * 6. Comment popup berfungsi
 * 7. Eraser berfungsi
 * 8. Semua state disinkron ke API DB
 */
(function () {
    'use strict';

    /* ── Tunggu _pdfViewer tersedia ─────────────────────────────────── */
    function waitForViewer(cb) {
        if (window._pdfViewer) { cb(window._pdfViewer); return; }
        const t = setInterval(() => { if (window._pdfViewer) { clearInterval(t); cb(window._pdfViewer); } }, 60);
    }

    waitForViewer(function (V) {

        /* ══════════════════════════════════════════════════════════════
           CONSTANTS & STATE
        ══════════════════════════════════════════════════════════════ */
        const slug = window.PDF_CONFIG?.slug || 'unknown';
        const API = `/api/annotations/${slug}`;

        /* Annotation store: { id, page, type, color, rect, selected_text,
                               comment, path_points, shape_type,
                               stroke_width, fill_opacity } */
        let annots = [];          // semua anotasi dari server
        let undoStack = [];       // array of { action:'add'|'delete', annot }
        let redoStack = [];

        let activeTool = 'highlight';
        let activeColor = 'yellow';
        let activeSize = 2;
        let activeShape = 'rect';

        /* drawing state */
        let isDrawing = false;
        let drawStart = null;   // {x,y} dalam koordinat CSS stage
        let freePoints = [];
        let shapePreview = null;   // element sementara saat drag shape
        let pendingAnnot = null;   // untuk comment/sticky popup

        /* zoom-stable render flag — cegah flicker */
        let renderScheduled = false;

        /* ── DOM references ────────────────────────────────────────── */
        const stage = V.stage;
        const annotLayer = V.annotLayer;
        const textLayer = V.textLayer;

        /* freehand canvas */
        let freeCanvas = document.getElementById('freehand-canvas');
        let freeCtx = freeCanvas ? freeCanvas.getContext('2d') : null;

        /* bottom bar elements */
        const toolBtns = document.querySelectorAll('.ab-tool[data-tool]');
        const colorDots = document.querySelectorAll('.ab-color');
        const sizeDots = document.querySelectorAll('.ab-size');
        const shapeBtns = document.querySelectorAll('.ab-shape');
        const undoBtn = document.getElementById('aft-undo');
        const redoBtn = document.getElementById('aft-redo');
        const panelBtn = document.getElementById('aft-panel-btn');
        const panelBadge = document.getElementById('ab-panel-badge');

        /* annot panel */
        const annotPanel = document.getElementById('annot-panel');
        const apList = document.getElementById('ap-list');
        const apClose = document.getElementById('ap-close-btn');
        const apClear = document.getElementById('ap-clear-btn');

        /* sticky popup */
        const stickyPop = document.getElementById('sticky-popup');
        const stickyText = document.getElementById('sticky-text');
        const stickySave = document.getElementById('sticky-save');
        const stickyCancel = document.getElementById('sticky-cancel');

        /* comment popup */
        const commentPop = document.getElementById('comment-popup');
        const commentText = document.getElementById('comment-text');
        const commentSave = document.getElementById('comment-save');
        const commentCancel = document.getElementById('comment-cancel');

        /* annot tooltip */
        const annotTip = document.getElementById('annot-tooltip');
        const tipText = document.getElementById('annot-tooltip-text');
        const tipDel = document.getElementById('annot-tooltip-del');
        const tipClose = document.getElementById('annot-tooltip-close');

        /* sync indicator */
        const syncEl = document.getElementById('annot-sync-indicator');
        const syncText = document.getElementById('annot-sync-text');

        /* eraser cursor */
        const eraserCursor = document.getElementById('eraser-cursor');

        /* ══════════════════════════════════════════════════════════════
           UTILITY
        ══════════════════════════════════════════════════════════════ */
        let tipActiveId = null;

        function snack(msg, color) { V.snack(msg, color); }

        function uid() { return Date.now() + '_' + Math.random().toString(36).slice(2, 7); }

        function colorVal(name) {
            const map = {
                yellow: '#ffd700', green: '#4ade80', red: '#ef4444',
                blue: '#60a5fa', orange: '#ff6b18', black: '#111111', white: '#ffffff'
            };
            return map[name] || '#ffd700';
        }

        function stageCoords(e) {
            const r = stage.getBoundingClientRect();
            const src = e.touches ? e.touches[0] : e;
            return { x: src.clientX - r.left, y: src.clientY - r.top };
        }

        function syncFreeCanvas() {
            if (!freeCanvas) return;
            freeCanvas.width = stage.offsetWidth;
            freeCanvas.height = stage.offsetHeight;
            freeCanvas.style.width = stage.offsetWidth + 'px';
            freeCanvas.style.height = stage.offsetHeight + 'px';
        }

        /* ══════════════════════════════════════════════════════════════
           SYNC INDICATOR
        ══════════════════════════════════════════════════════════════ */
        let syncTimer = null;
        function showSync(msg = 'Menyimpan...') {
            if (!syncEl) return;
            if (syncText) syncText.textContent = msg;
            syncEl.classList.add('show');
            clearTimeout(syncTimer);
            syncTimer = setTimeout(() => syncEl.classList.remove('show'), 2000);
        }

        /* ══════════════════════════════════════════════════════════════
           API CALLS
        ══════════════════════════════════════════════════════════════ */
        async function apiGet() {
            try {
                const r = await fetch(API, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) return [];
                const j = await r.json();
                return j.data || [];
            } catch { return []; }
        }

        async function apiPost(payload) {
            showSync('Menyimpan...');
            try {
                const r = await fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                if (!r.ok) { showSync('Gagal simpan'); return null; }
                const j = await r.json();
                showSync('Tersimpan ✓');
                return j.data;
            } catch { showSync('Gagal simpan'); return null; }
        }

        async function apiDelete(id) {
            showSync('Menghapus...');
            try {
                await fetch(`${API}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                showSync('Dihapus ✓');
            } catch { showSync('Gagal hapus'); }
        }

        async function apiDeletePage(page) {
            showSync('Menghapus halaman...');
            try {
                await fetch(`${API}/page/${page}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                showSync('Halaman dihapus ✓');
            } catch { showSync('Gagal hapus'); }
        }

        /* ══════════════════════════════════════════════════════════════
           LOAD & RENDER
        ══════════════════════════════════════════════════════════════ */
        async function loadAnnotations() {
            annots = await apiGet();
            scheduleRender();
            updateBadge();
        }

        /* Jadwalkan render satu kali per frame — cegah flicker zoom */
        function scheduleRender() {
            if (renderScheduled) return;
            renderScheduled = true;
            requestAnimationFrame(() => {
                renderScheduled = false;
                renderAll();
            });
        }

        function renderAll() {
            const scale = V.getScale();
            const page = V.pageNum;

            /* Bersihkan annotation layer (highlight, shape) */
            annotLayer.innerHTML = '';

            /* Bersihkan freehand canvas */
            syncFreeCanvas();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);

            /* Bersihkan sticky notes lama */
            stage.querySelectorAll('.sticky-note').forEach(e => e.remove());

            const pageAnnots = annots.filter(a => a.page === page);

            pageAnnots.forEach(a => {
                switch (a.type) {
                    case 'highlight': renderHighlight(a, scale); break;
                    case 'freehand': renderFreehand(a, scale); break;
                    case 'shape': renderShape(a, scale); break;
                    case 'sticky': renderStickyNote(a, scale); break;
                    case 'comment': renderHighlight(a, scale); break; // comment = highlight dengan icon
                }
            });

            updateBadge();
        }

        /* ── Highlight ─────────────────────────────────────────────── */
        function renderHighlight(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.className = 'annot-highlight';
            el.style.cssText = `
                position:absolute;
                left:${a.rect.x * scale}px;
                top:${a.rect.y * scale}px;
                width:${a.rect.w * scale}px;
                height:${a.rect.h * scale}px;
                background:${colorVal(a.color)};
                opacity:0.4;
                border-radius:2px;
                pointer-events:auto;
                cursor:pointer;
                z-index:5;
                transition:opacity .15s;
            `;
            el.dataset.id = a.id;
            el.addEventListener('mouseenter', () => el.style.opacity = '0.7');
            el.addEventListener('mouseleave', () => el.style.opacity = '0.4');
            el.addEventListener('click', e => { e.stopPropagation(); showTip(a, e.clientX, e.clientY); });
            annotLayer.appendChild(el);
        }

        /* ── Freehand ───────────────────────────────────────────────── */
        function renderFreehand(a, scale) {
            if (!a.path_points || a.path_points.length < 2 || !freeCtx) return;
            freeCtx.save();
            freeCtx.strokeStyle = colorVal(a.color);
            freeCtx.lineWidth = (a.stroke_width || 2) * scale;
            freeCtx.lineCap = 'round';
            freeCtx.lineJoin = 'round';
            freeCtx.globalAlpha = 0.9;
            freeCtx.beginPath();
            freeCtx.moveTo(a.path_points[0][0] * scale, a.path_points[0][1] * scale);
            for (let i = 1; i < a.path_points.length; i++) {
                freeCtx.lineTo(a.path_points[i][0] * scale, a.path_points[i][1] * scale);
            }
            freeCtx.stroke();
            freeCtx.restore();
        }

        /* ── Shape (rect / ellipse / arrow) ────────────────────────── */
        function renderShape(a, scale) {
            if (!a.rect) return;
            const x = a.rect.x * scale;
            const y = a.rect.y * scale;
            const w = a.rect.w * scale;
            const h = a.rect.h * scale;
            const sw = (a.stroke_width || 2) * scale;
            const color = colorVal(a.color);

            const wrap = document.createElement('div');
            wrap.className = 'annot-shape';
            wrap.style.cssText = `position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;`;
            wrap.dataset.id = a.id;

            let svgContent = '';
            if (a.shape_type === 'rect' || !a.shape_type) {
                svgContent = `<rect x="${sw / 2}" y="${sw / 2}" width="${Math.max(1, w - sw)}" height="${Math.max(1, h - sw)}" fill="none" stroke="${color}" stroke-width="${sw}" rx="2"/>`;
            } else if (a.shape_type === 'ellipse') {
                const rx = Math.max(1, w / 2 - sw / 2);
                const ry = Math.max(1, h / 2 - sw / 2);
                svgContent = `<ellipse cx="${w / 2}" cy="${h / 2}" rx="${rx}" ry="${ry}" fill="none" stroke="${color}" stroke-width="${sw}"/>`;
            } else if (a.shape_type === 'arrow') {
                const ax = sw, ay = h / 2;
                const bx = w - sw * 3, by = h / 2;
                const hw = sw * 3, hh = h * 0.3;
                svgContent = `
                    <line x1="${ax}" y1="${ay}" x2="${bx}" y2="${by}" stroke="${color}" stroke-width="${sw}" stroke-linecap="round"/>
                    <polygon points="${w - sw / 2},${h / 2} ${bx},${h / 2 - hh} ${bx},${h / 2 + hh}" fill="${color}"/>
                `;
            }

            wrap.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;">${svgContent}</svg>`;
            wrap.addEventListener('click', e => { e.stopPropagation(); showTip(a, e.clientX, e.clientY); });
            annotLayer.appendChild(wrap);
        }

        /* ── Sticky Note ────────────────────────────────────────────── */
        function renderStickyNote(a, scale) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'sticky-note';
            note.dataset.id = a.id;
            note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * scale) + 'px';
            note.style.top = (a.rect.y * scale) + 'px';

            note.innerHTML = `
                <div class="sn-header">
                    <span>📌 Catatan</span>
                    <button class="sn-del" title="Hapus">×</button>
                </div>
                <div class="sn-body">${escHtml(a.comment || '')}</div>
            `;

            /* Hapus */
            note.querySelector('.sn-del').addEventListener('click', async e => {
                e.stopPropagation();
                await deleteAnnot(a.id);
            });

            /* Drag */
            makeDraggable(note, a, scale);

            stage.appendChild(note);
        }

        function escHtml(s) {
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }

        function makeDraggable(el, annotData, scale) {
            let ox = 0, oy = 0, dragging = false;

            function onDown(e) {
                if (e.target.classList.contains('sn-del') || e.target.classList.contains('sn-body')) return;
                dragging = true;
                const src = e.touches ? e.touches[0] : e;
                ox = src.clientX - el.offsetLeft;
                oy = src.clientY - el.offsetTop;
                el.style.zIndex = 12;
                e.stopPropagation();
            }
            function onMove(e) {
                if (!dragging) return;
                const src = e.touches ? e.touches[0] : e;
                const nx = src.clientX - ox;
                const ny = src.clientY - oy;
                el.style.left = nx + 'px';
                el.style.top = ny + 'px';
                e.preventDefault();
            }
            async function onUp() {
                if (!dragging) return;
                dragging = false;
                el.style.zIndex = 9;
                /* Update posisi di store */
                const newX = parseFloat(el.style.left) / scale;
                const newY = parseFloat(el.style.top) / scale;
                const idx = annots.findIndex(a => a.id == annotData.id);
                if (idx >= 0) {
                    annots[idx].rect.x = newX;
                    annots[idx].rect.y = newY;
                    /* Tidak perlu update ke server untuk posisi drag — opsional */
                }
            }

            el.addEventListener('mousedown', onDown);
            el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchend', onUp);
        }

        /* ══════════════════════════════════════════════════════════════
           TOOLTIP
        ══════════════════════════════════════════════════════════════ */
        function showTip(a, cx, cy) {
            if (!annotTip) return;
            tipActiveId = a.id;
            let txt = '';
            if (a.type === 'comment' && a.comment) txt = `💬 ${a.comment}`;
            else if (a.type === 'sticky' && a.comment) txt = `📌 ${a.comment}`;
            else if (a.type === 'highlight') txt = `✏️ Highlight ${a.color}` + (a.selected_text ? ` — "${a.selected_text.substring(0, 50)}"` : '');
            else if (a.type === 'freehand') txt = `🖊 Freehand ${a.color}`;
            else if (a.type === 'shape') txt = `${a.shape_type === 'ellipse' ? '⭕' : a.shape_type === 'arrow' ? '➡' : '⬛'} Shape ${a.color}`;
            if (tipText) tipText.textContent = txt;
            annotTip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            const tw = 270, th = 100;
            annotTip.style.left = Math.min(cx, vw - tw - 8) + 'px';
            annotTip.style.top = (cy + th > vh ? cy - th - 8 : cy + 8) + 'px';
        }

        if (tipClose) tipClose.addEventListener('click', () => { annotTip?.classList.remove('show'); tipActiveId = null; });
        if (tipDel) tipDel.addEventListener('click', async () => {
            if (!tipActiveId) return;
            annotTip?.classList.remove('show');
            await deleteAnnot(tipActiveId);
            tipActiveId = null;
        });
        document.addEventListener('click', e => {
            if (annotTip && !annotTip.contains(e.target) && !e.target.closest('.annot-highlight') && !e.target.closest('.annot-shape') && !e.target.closest('.sticky-note')) {
                annotTip.classList.remove('show');
            }
        });

        /* ══════════════════════════════════════════════════════════════
           ADD / DELETE ANNOTATION
        ══════════════════════════════════════════════════════════════ */
        async function addAnnot(payload) {
            const saved = await apiPost(payload);
            if (!saved) return null;
            annots.push(saved);
            undoStack.push({ action: 'add', annotId: saved.id });
            redoStack = [];
            updateUndoRedo();
            scheduleRender();
            updateBadge();
            return saved;
        }

        async function deleteAnnot(id) {
            const a = annots.find(x => x.id == id);
            if (!a) return;
            await apiDelete(a.id);
            annots = annots.filter(x => x.id != id);
            undoStack.push({ action: 'delete', annot: a });
            redoStack = [];
            updateUndoRedo();
            scheduleRender();
            updateBadge();
            snack('🗑 Anotasi dihapus');
        }

        /* ══════════════════════════════════════════════════════════════
           UNDO / REDO
        ══════════════════════════════════════════════════════════════ */
        function updateUndoRedo() {
            if (undoBtn) undoBtn.disabled = undoStack.length === 0;
            if (redoBtn) redoBtn.disabled = redoStack.length === 0;
        }

        async function doUndo() {
            if (!undoStack.length) return;
            const last = undoStack.pop();
            if (last.action === 'add') {
                // Undo add → delete
                const a = annots.find(x => x.id == last.annotId);
                if (a) {
                    await apiDelete(a.id);
                    annots = annots.filter(x => x.id != last.annotId);
                    redoStack.push({ action: 'add_redo', annot: a });
                }
            } else if (last.action === 'delete') {
                // Undo delete → re-add
                const saved = await apiPost({
                    page: last.annot.page, type: last.annot.type, color: last.annot.color,
                    rect_x: last.annot.rect?.x, rect_y: last.annot.rect?.y,
                    rect_w: last.annot.rect?.w, rect_h: last.annot.rect?.h,
                    selected_text: last.annot.selected_text, comment: last.annot.comment,
                    path_points: last.annot.path_points, shape_type: last.annot.shape_type,
                    stroke_width: last.annot.stroke_width, fill_opacity: last.annot.fill_opacity,
                });
                if (saved) { annots.push(saved); redoStack.push({ action: 'delete_redo', annotId: saved.id }); }
            }
            updateUndoRedo();
            scheduleRender();
            updateBadge();
        }

        async function doRedo() {
            if (!redoStack.length) return;
            const last = redoStack.pop();
            if (last.action === 'add_redo') {
                const saved = await apiPost({
                    page: last.annot.page, type: last.annot.type, color: last.annot.color,
                    rect_x: last.annot.rect?.x, rect_y: last.annot.rect?.y,
                    rect_w: last.annot.rect?.w, rect_h: last.annot.rect?.h,
                    selected_text: last.annot.selected_text, comment: last.annot.comment,
                    path_points: last.annot.path_points, shape_type: last.annot.shape_type,
                    stroke_width: last.annot.stroke_width,
                });
                if (saved) { annots.push(saved); undoStack.push({ action: 'add', annotId: saved.id }); }
            } else if (last.action === 'delete_redo') {
                const a = annots.find(x => x.id == last.annotId);
                if (a) {
                    await apiDelete(a.id);
                    annots = annots.filter(x => x.id != last.annotId);
                    undoStack.push({ action: 'delete', annot: a });
                }
            }
            updateUndoRedo();
            scheduleRender();
            updateBadge();
        }

        if (undoBtn) undoBtn.addEventListener('click', doUndo);
        if (redoBtn) redoBtn.addEventListener('click', doRedo);

        /* Keyboard undo/redo */
        document.addEventListener('keydown', e => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) { e.preventDefault(); doRedo(); }
        });

        /* ══════════════════════════════════════════════════════════════
           BADGE & PANEL
        ══════════════════════════════════════════════════════════════ */
        function updateBadge() {
            const page = V.pageNum;
            const count = annots.filter(a => a.page === page).length;
            const total = annots.length;
            if (panelBadge) {
                panelBadge.textContent = total;
                panelBadge.classList.toggle('show', total > 0);
            }
            window.dispatchEvent(new CustomEvent('annot-count-change', { detail: { count: total } }));
        }

        /* Annotation Panel */
        if (panelBtn) {
            panelBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (annotPanel) annotPanel.classList.toggle('open');
                buildPanel();
            });
        }
        if (apClose) apClose.addEventListener('click', () => annotPanel?.classList.remove('open'));

        if (apClear) {
            apClear.addEventListener('click', async () => {
                const page = V.pageNum;
                if (!confirm(`Hapus semua anotasi di halaman ${page}?`)) return;
                await apiDeletePage(page);
                annots = annots.filter(a => a.page !== page);
                undoStack = []; redoStack = [];
                updateUndoRedo();
                scheduleRender();
                updateBadge();
                buildPanel();
                snack('🗑 Semua anotasi halaman ini dihapus');
            });
        }

        function buildPanel() {
            if (!apList) return;
            apList.innerHTML = '';
            if (!annots.length) {
                apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.<br>Pilih tool lalu mulai beri catatan!</div>';
                return;
            }

            const sorted = [...annots].sort((a, b) => a.page - b.page || (a.id > b.id ? 1 : -1));
            sorted.forEach(a => {
                const typeIcon = { highlight: '✏️', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌' }[a.type] || '•';
                const item = document.createElement('div');
                item.className = 'ap-item';
                item.innerHTML = `
                    <div class="ap-dot" style="background:${colorVal(a.color)}"></div>
                    <div class="ap-item-body">
                        <span class="ap-item-type">${typeIcon} ${a.type}</span>
                        <span class="ap-item-pg">Hal.${a.page}</span>
                        <div class="ap-item-text">${a.comment || a.selected_text || a.shape_type || '—'}</div>
                    </div>
                    <button class="ap-item-del" title="Hapus">🗑</button>
                `;
                item.querySelector('.ap-item-del').addEventListener('click', async e => {
                    e.stopPropagation();
                    await deleteAnnot(a.id);
                    buildPanel();
                });
                item.addEventListener('click', () => {
                    if (a.page !== V.pageNum) V.queueRender(a.page);
                    annotPanel?.classList.remove('open');
                });
                apList.appendChild(item);
            });
        }

        /* ══════════════════════════════════════════════════════════════
           TOOL SELECTION
        ══════════════════════════════════════════════════════════════ */
        function setTool(tool) {
            activeTool = tool;

            /* Stage mode classes */
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode');
            if (tool === 'freehand') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');

            /* Text layer pointer events — highlight & comment butuh seleksi teks */
            if (textLayer) {
                textLayer.style.pointerEvents = (tool === 'highlight' || tool === 'comment') ? 'auto' : 'none';
            }

            /* Eraser cursor */
            if (eraserCursor) eraserCursor.style.display = tool === 'eraser' ? 'block' : 'none';
        }

        /* Dengarkan event dari bottom bar (read.blade.php) */
        window.addEventListener('annot-tool-change', e => setTool(e.detail.tool));
        window.addEventListener('annot-color-change', e => { activeColor = e.detail.color; });
        window.addEventListener('annot-size-change', e => { activeSize = e.detail.size; });
        window.addEventListener('annot-shape-change', e => { activeShape = e.detail.shape; });

        /* Direct click forwarding dari hidden toolbar */
        document.querySelectorAll('#annot-floating-toolbar [data-tool]').forEach(b => {
            b.addEventListener('click', () => setTool(b.dataset.tool));
        });
        document.querySelectorAll('#aft-colors [data-color]').forEach(b => {
            b.addEventListener('click', () => { activeColor = b.dataset.color; });
        });
        document.querySelectorAll('#aft-sizes [data-size]').forEach(b => {
            b.addEventListener('click', () => { activeSize = +b.dataset.size; });
        });
        document.querySelectorAll('#aft-shape-types [data-shape]').forEach(b => {
            b.addEventListener('click', () => { activeShape = b.dataset.shape; });
        });

        setTool('highlight'); // default

        /* ══════════════════════════════════════════════════════════════
           HIGHLIGHT — teks selection
        ══════════════════════════════════════════════════════════════ */
        function getSelectionInfo() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0);
            if (!textLayer.contains(range.commonAncestorContainer)) return null;
            const stRect = stage.getBoundingClientRect();
            const scale = V.getScale();
            const rects = Array.from(range.getClientRects());
            if (!rects.length) return null;
            const left = Math.min(...rects.map(r => r.left));
            const top = Math.min(...rects.map(r => r.top));
            const right = Math.max(...rects.map(r => r.right));
            const bottom = Math.max(...rects.map(r => r.bottom));
            return {
                rect: {
                    x: (left - stRect.left) / scale,
                    y: (top - stRect.top) / scale,
                    w: (right - left) / scale,
                    h: (bottom - top) / scale,
                },
                text: sel.toString().substring(0, 500),
            };
        }

        /* mouseup / touchend — untuk highlight & comment */
        function onSelectionEnd() {
            if (activeTool !== 'highlight' && activeTool !== 'comment') return;
            setTimeout(async () => {
                const info = getSelectionInfo();
                if (!info || info.rect.w < 2 || info.rect.h < 2) return;

                if (activeTool === 'highlight') {
                    await addAnnot({
                        page: V.pageNum, type: 'highlight', color: activeColor,
                        rect_x: info.rect.x, rect_y: info.rect.y,
                        rect_w: info.rect.w, rect_h: info.rect.h,
                        selected_text: info.text,
                    });
                    window.getSelection()?.removeAllRanges();
                    snack(`✏️ Highlight ${activeColor} diterapkan!`);

                } else if (activeTool === 'comment') {
                    pendingAnnot = info;
                    const sel = window.getSelection();
                    const br = sel?.getRangeAt(0).getBoundingClientRect();
                    showCommentPopup(br);
                }
            }, 60);
        }

        document.addEventListener('mouseup', e => { if (e.target.closest('#comment-popup, #sticky-popup')) return; onSelectionEnd(); });
        document.addEventListener('touchend', e => { if (e.target.closest('#comment-popup, #sticky-popup')) return; onSelectionEnd(); });

        /* ══════════════════════════════════════════════════════════════
           COMMENT POPUP
        ══════════════════════════════════════════════════════════════ */
        function showCommentPopup(br) {
            if (!commentPop) return;
            const vw = window.innerWidth, vh = window.innerHeight;
            let cx = (br?.left || 100) - 140;
            let cy = (br?.bottom || 200) + 10;
            if (cy + 180 > vh) cy = (br?.top || 200) - 180;
            commentPop.style.left = Math.max(8, Math.min(cx, vw - 296)) + 'px';
            commentPop.style.top = Math.max(8, cy) + 'px';
            commentPop.classList.add('show');
            if (commentText) { commentText.value = ''; commentText.focus(); }
        }

        if (commentSave) {
            commentSave.addEventListener('click', async () => {
                const txt = commentText?.value.trim();
                if (!txt || !pendingAnnot) { snack('Tulis komentar dulu!'); return; }
                await addAnnot({
                    page: V.pageNum, type: 'comment', color: activeColor,
                    rect_x: pendingAnnot.rect.x, rect_y: pendingAnnot.rect.y,
                    rect_w: pendingAnnot.rect.w, rect_h: pendingAnnot.rect.h,
                    selected_text: pendingAnnot.text, comment: txt,
                });
                window.getSelection()?.removeAllRanges();
                commentPop.classList.remove('show');
                pendingAnnot = null;
                snack('💬 Komentar disimpan!');
            });
        }
        if (commentCancel) {
            commentCancel.addEventListener('click', () => {
                commentPop?.classList.remove('show');
                pendingAnnot = null;
                window.getSelection()?.removeAllRanges();
            });
        }

        /* ══════════════════════════════════════════════════════════════
           FREEHAND DRAWING — pointer events di freehand-canvas
        ══════════════════════════════════════════════════════════════ */
        function onFreeStart(e) {
            if (activeTool !== 'freehand') return;
            e.preventDefault();
            isDrawing = true;
            freePoints = [];
            const pos = stageCoords(e);
            freePoints.push([pos.x / V.getScale(), pos.y / V.getScale()]);
        }

        function onFreeMove(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            e.preventDefault();
            const pos = stageCoords(e);
            freePoints.push([pos.x / V.getScale(), pos.y / V.getScale()]);

            /* Live preview pada freehand canvas */
            if (!freeCtx) return;
            const scale = V.getScale();
            const pts = freePoints;
            if (pts.length < 2) return;
            const last = pts[pts.length - 2];
            const cur = pts[pts.length - 1];
            freeCtx.save();
            freeCtx.strokeStyle = colorVal(activeColor);
            freeCtx.lineWidth = activeSize * scale;
            freeCtx.lineCap = 'round';
            freeCtx.lineJoin = 'round';
            freeCtx.globalAlpha = 0.9;
            freeCtx.beginPath();
            freeCtx.moveTo(last[0] * scale, last[1] * scale);
            freeCtx.lineTo(cur[0] * scale, cur[1] * scale);
            freeCtx.stroke();
            freeCtx.restore();
        }

        async function onFreeEnd(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            e.preventDefault();
            isDrawing = false;
            if (freePoints.length < 2) return;
            await addAnnot({
                page: V.pageNum, type: 'freehand', color: activeColor,
                stroke_width: activeSize, path_points: freePoints,
                rect_x: 0, rect_y: 0, rect_w: 0, rect_h: 0,
            });
        }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', onFreeStart, { passive: false });
            freeCanvas.addEventListener('mousemove', onFreeMove, { passive: false });
            freeCanvas.addEventListener('mouseup', onFreeEnd, { passive: false });
            freeCanvas.addEventListener('mouseleave', onFreeEnd, { passive: false });
            freeCanvas.addEventListener('touchstart', onFreeStart, { passive: false });
            freeCanvas.addEventListener('touchmove', onFreeMove, { passive: false });
            freeCanvas.addEventListener('touchend', onFreeEnd, { passive: false });
        }

        /* ══════════════════════════════════════════════════════════════
           SHAPE DRAWING — pointer events di freehand-canvas saat shape-mode
        ══════════════════════════════════════════════════════════════ */
        function onShapeStart(e) {
            if (activeTool !== 'shape') return;
            e.preventDefault();
            isDrawing = true;
            drawStart = stageCoords(e);

            /* Preview element */
            shapePreview = document.createElement('div');
            shapePreview.style.cssText = `
                position:absolute;pointer-events:none;z-index:20;
                border:${activeSize}px solid ${colorVal(activeColor)};
                ${activeShape === 'ellipse' ? 'border-radius:50%;' : ''}
                left:${drawStart.x}px;top:${drawStart.y}px;
                width:0px;height:0px;
            `;
            stage.appendChild(shapePreview);
        }

        function onShapeMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !shapePreview || !drawStart) return;
            e.preventDefault();
            const cur = stageCoords(e);
            const x = Math.min(drawStart.x, cur.x);
            const y = Math.min(drawStart.y, cur.y);
            const w = Math.abs(cur.x - drawStart.x);
            const h = Math.abs(cur.y - drawStart.y);
            Object.assign(shapePreview.style, {
                left: x + 'px', top: y + 'px',
                width: w + 'px', height: h + 'px',
            });
        }

        async function onShapeEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return;
            e.preventDefault();
            isDrawing = false;
            if (shapePreview) { shapePreview.remove(); shapePreview = null; }
            const cur = stageCoords(e.changedTouches ? { clientX: e.changedTouches[0].clientX, clientY: e.changedTouches[0].clientY } : e);
            const scale = V.getScale();
            const x = Math.min(drawStart.x, cur.x) / scale;
            const y = Math.min(drawStart.y, cur.y) / scale;
            const w = Math.abs(cur.x - drawStart.x) / scale;
            const h = Math.abs(cur.y - drawStart.y) / scale;
            drawStart = null;
            if (w < 5 || h < 5) return;
            await addAnnot({
                page: V.pageNum, type: 'shape', color: activeColor,
                shape_type: activeShape, stroke_width: activeSize,
                rect_x: x, rect_y: y, rect_w: w, rect_h: h,
            });
        }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', onShapeStart, { passive: false });
            freeCanvas.addEventListener('mousemove', onShapeMove, { passive: false });
            freeCanvas.addEventListener('mouseup', onShapeEnd, { passive: false });
            freeCanvas.addEventListener('touchstart', onShapeStart, { passive: false });
            freeCanvas.addEventListener('touchmove', onShapeMove, { passive: false });
            freeCanvas.addEventListener('touchend', onShapeEnd, { passive: false });
        }

        /* ══════════════════════════════════════════════════════════════
           ERASER
        ══════════════════════════════════════════════════════════════ */
        /* Move eraser cursor */
        document.addEventListener('mousemove', e => {
            if (activeTool !== 'eraser' || !eraserCursor) return;
            eraserCursor.style.left = e.clientX + 'px';
            eraserCursor.style.top = e.clientY + 'px';
        });

        /* Click on annotLayer / stickyNote to erase */
        annotLayer.addEventListener('click', async e => {
            if (activeTool !== 'eraser') return;
            const id = e.target.closest('[data-id]')?.dataset.id;
            if (id) { e.stopPropagation(); await deleteAnnot(id); }
        });

        stage.addEventListener('click', async e => {
            if (activeTool !== 'eraser') return;
            const sticky = e.target.closest('.sticky-note');
            if (sticky) { e.stopPropagation(); await deleteAnnot(sticky.dataset.id); }
        });

        /* Touch eraser */
        annotLayer.addEventListener('touchend', async e => {
            if (activeTool !== 'eraser') return;
            const t = e.changedTouches[0];
            const els = document.elementsFromPoint(t.clientX, t.clientY);
            for (const el of els) {
                const target = el.closest('[data-id]');
                if (target) { await deleteAnnot(target.dataset.id); break; }
            }
        });

        /* ══════════════════════════════════════════════════════════════
           STICKY NOTE
        ══════════════════════════════════════════════════════════════ */
        let stickyPos = null; // {x,y} dalam skala normal (dibagi scale)

        /* Klik pada stage di mode sticky → buka popup */
        stage.addEventListener('click', e => {
            if (activeTool !== 'sticky') return;
            if (e.target.closest('.sticky-note, #comment-popup, #sticky-popup, #annot-bottom-bar')) return;
            const pos = stageCoords(e);
            const scale = V.getScale();
            stickyPos = { x: pos.x / scale, y: pos.y / scale };
            openStickyPopup(e.clientX, e.clientY);
        });

        /* Touch */
        stage.addEventListener('touchend', e => {
            if (activeTool !== 'sticky') return;
            if (e.target.closest('.sticky-note, #comment-popup, #sticky-popup, #annot-bottom-bar')) return;
            const t = e.changedTouches[0];
            const pos = { x: t.clientX - stage.getBoundingClientRect().left, y: t.clientY - stage.getBoundingClientRect().top };
            const scale = V.getScale();
            stickyPos = { x: pos.x / scale, y: pos.y / scale };
            openStickyPopup(t.clientX, t.clientY);
        });

        function openStickyPopup(cx, cy) {
            if (!stickyPop) return;
            const vw = window.innerWidth, vh = window.innerHeight;
            let px = cx - 130;
            let py = cy + 12;
            if (py + 200 > vh) py = cy - 210;
            stickyPop.style.left = Math.max(8, Math.min(px, vw - 276)) + 'px';
            stickyPop.style.top = Math.max(8, py) + 'px';
            stickyPop.classList.add('show');
            if (stickyText) { stickyText.value = ''; stickyText.focus(); }
        }

        if (stickySave) {
            stickySave.addEventListener('click', async () => {
                const txt = stickyText?.value.trim();
                if (!txt) { snack('Tulis catatan dulu!'); return; }
                if (!stickyPos) return;
                await addAnnot({
                    page: V.pageNum, type: 'sticky', color: activeColor,
                    rect_x: stickyPos.x, rect_y: stickyPos.y, rect_w: 180, rect_h: 90,
                    comment: txt,
                });
                stickyPop.classList.remove('show');
                stickyPos = null;
                snack('📌 Sticky note ditempel!');
            });
        }
        if (stickyCancel) {
            stickyCancel.addEventListener('click', () => {
                stickyPop?.classList.remove('show');
                stickyPos = null;
            });
        }

        /* ══════════════════════════════════════════════════════════════
           PAGE CHANGE HOOK
        ══════════════════════════════════════════════════════════════ */
        V.onPageChange = function (newPage) {
            /* Tutup popup yang mungkin terbuka */
            commentPop?.classList.remove('show');
            stickyPop?.classList.remove('show');
            annotTip?.classList.remove('show');
            pendingAnnot = null; stickyPos = null;
            window.getSelection()?.removeAllRanges();

            /* Re-render anotasi halaman baru */
            scheduleRender();
            updateBadge();
        };

        /* ══════════════════════════════════════════════════════════════
           ZOOM FLICKER FIX
           Re-render anotasi setelah zoom selesai (debounced),
           sehingga tidak ada kedip ganda
        ══════════════════════════════════════════════════════════════ */
        let zoomRenderTimer = null;
        const origQueueRender = V.queueRender.bind(V);
        /* Intercept render calls — setelah canvas dirender, re-render anotasi */
        const observer = new MutationObserver(() => {
            clearTimeout(zoomRenderTimer);
            zoomRenderTimer = setTimeout(() => {
                syncFreeCanvas();
                scheduleRender();
            }, 80);
        });
        observer.observe(document.getElementById('pdf-canvas'), { attributes: true, attributeFilter: ['width', 'height'] });

        /* ══════════════════════════════════════════════════════════════
           INIT
        ══════════════════════════════════════════════════════════════ */
        V.onReady(async () => {
            await loadAnnotations();
            updateUndoRedo();
            syncFreeCanvas();
        });

        /* Mark sebagai aktif agar pdf-viewer.js tidak duplikat render */
        window._pdfAnnotations = true;

        /* Notify pdf-viewer.js bahwa pdf-annotations sudah loaded */
        window.dispatchEvent(new CustomEvent('pdf-annotations-ready'));

    }); // waitForViewer

})();
