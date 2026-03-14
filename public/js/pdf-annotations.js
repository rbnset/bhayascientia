/**
 * pdf-annotations.js — FULL REWRITE v3.0
 * public/js/pdf-annotations.js
 *
 * TOOLS LENGKAP:
 *  - highlight      : stabilo teks
 *  - freehand       : pen bebas
 *  - shape          : kotak / lingkaran / panah
 *  - comment        : highlight + catatan teks
 *  - sticky         : sticky note (dapat dipindah)
 *  - eraser         : hapus anotasi dengan klik/sentuh
 *  - select         : pilih & hapus anotasi
 *  - pan            : geser dokumen (hand tool)
 *  - text           : tambah teks bebas di canvas
 *  - underline      : garis bawah teks
 *  - strikethrough  : garis tengah teks
 *
 * PERBAIKAN:
 *  - Simpan ke DB (API) dengan benar, load kembali saat halaman dibuka
 *  - Semua tools berfungsi di desktop & mobile (touch)
 *  - Undo / Redo benar
 *  - Shape, freehand, sticky persisten setelah reload
 *  - Zoom tidak menyebabkan flicker / kedip
 *  - Eraser berfungsi
 */
(function () {
    'use strict';

    /* ═══════════════════════════════════════════════════════════════
       WAIT FOR _pdfViewer
    ═══════════════════════════════════════════════════════════════ */
    let _waitTick = 0;
    function waitForViewer(cb) {
        if (window._pdfViewer && window._pdfViewer.pdfDoc) { cb(window._pdfViewer); return; }
        if (_waitTick++ > 300) { console.error('[annot] _pdfViewer timeout'); return; }
        setTimeout(() => waitForViewer(cb), 80);
    }

    waitForViewer(function boot(V) {

        /* ═══════════════════════════════════════════════════════════
           CONFIG
        ═══════════════════════════════════════════════════════════ */
        const slug = window.PDF_CONFIG?.slug || 'unknown';
        const API = `/api/annotations/${slug}`;
        const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
        const HEADERS = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF(),
            'X-Requested-With': 'XMLHttpRequest',
        });

        /* ═══════════════════════════════════════════════════════════
           STATE
        ═══════════════════════════════════════════════════════════ */
        let annots = [];       // [{id, page, type, color, rect, ...}]
        let undoStack = [];       // [{action:'add'|'del', data: annot}]
        let redoStack = [];

        let activeTool = 'highlight';
        let activeColor = 'yellow';
        let activeSize = 2;        // stroke width
        let activeShape = 'rect';

        /* drawing */
        let isDrawing = false;
        let drawStart = null;     // {x,y} css coords on stage
        let freePoints = [];
        let shapePreviewEl = null;
        let pendingRect = null;  // for comment/sticky popup
        let pendingText = null;  // selected text for comment

        /* select */
        let selectedId = null;

        /* pan */
        let panStartX = 0, panStartY = 0, panScrollX = 0, panScrollY = 0;
        let isPanning = false;

        /* render debounce */
        let renderPending = false;

        /* ═══════════════════════════════════════════════════════════
           DOM
        ═══════════════════════════════════════════════════════════ */
        const stage = V.stage;
        const annotLayer = V.annotLayer;
        const textLayer = V.textLayer;
        const canvasWrap = document.getElementById('pdf-canvas-wrapper');

        let freeCanvas = document.getElementById('freehand-canvas');
        let freeCtx = freeCanvas?.getContext('2d');

        /* popups */
        const commentPop = document.getElementById('comment-popup');
        const commentText = document.getElementById('comment-text');
        const commentSave = document.getElementById('comment-save');
        const commentCancel = document.getElementById('comment-cancel');

        const stickyPop = document.getElementById('sticky-popup');
        const stickyText = document.getElementById('sticky-text');
        const stickySave = document.getElementById('sticky-save');
        const stickyCancel = document.getElementById('sticky-cancel');

        const textPop = document.getElementById('freetext-popup');
        const textInput = document.getElementById('freetext-input');
        const textSave = document.getElementById('freetext-save');
        const textCancel = document.getElementById('freetext-cancel');

        const annotTip = document.getElementById('annot-tooltip');
        const tipText = document.getElementById('annot-tooltip-text');
        const tipDel = document.getElementById('annot-tooltip-del');
        const tipClose = document.getElementById('annot-tooltip-close');

        const annotPanel = document.getElementById('annot-panel');
        const apList = document.getElementById('ap-list');
        const apClose = document.getElementById('ap-close-btn');
        const apClear = document.getElementById('ap-clear-btn');
        const panelBadge = document.getElementById('ab-panel-badge');
        const panelBtn = document.getElementById('aft-panel-btn');

        const undoBtn = document.getElementById('aft-undo');
        const redoBtn = document.getElementById('aft-redo');

        const syncEl = document.getElementById('annot-sync-indicator');
        const syncTxt = document.getElementById('annot-sync-text');
        const eraserCursor = document.getElementById('eraser-cursor');

        /* ═══════════════════════════════════════════════════════════
           UTILITY
        ═══════════════════════════════════════════════════════════ */
        function snack(msg, col) { V.snack(msg, col); }

        function colorHex(name) {
            return {
                yellow: '#FFD700', green: '#4ADE80', red: '#EF4444',
                blue: '#60A5FA', orange: '#FF6B18', black: '#111111',
                white: '#FFFFFF', pink: '#F472B6', purple: '#A78BFA',
                cyan: '#22D3EE',
            }[name] || '#FFD700';
        }

        function stageXY(e) {
            const r = stage.getBoundingClientRect();
            const src = e.touches ? e.touches[0] : (e.changedTouches ? e.changedTouches[0] : e);
            return { x: src.clientX - r.left, y: src.clientY - r.top };
        }

        function syncFreeCanvas() {
            if (!freeCanvas) return;
            const w = stage.offsetWidth;
            const h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) {
                freeCanvas.width = w;
                freeCanvas.height = h;
            }
            freeCanvas.style.width = w + 'px';
            freeCanvas.style.height = h + 'px';
        }

        function escHtml(s) {
            if (!s) return '';
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }

        /* ═══════════════════════════════════════════════════════════
           SYNC INDICATOR
        ═══════════════════════════════════════════════════════════ */
        let syncTimer = null;
        function showSync(msg = 'Menyimpan...', ok = false) {
            if (!syncEl) return;
            if (syncTxt) syncTxt.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#ff6b18';
            syncEl.style.color = ok ? '#22c55e' : '#ff6b18';
            syncEl.classList.add('show');
            clearTimeout(syncTimer);
            syncTimer = setTimeout(() => syncEl.classList.remove('show'), ok ? 1800 : 3500);
        }

        /* ═══════════════════════════════════════════════════════════
           API
        ═══════════════════════════════════════════════════════════ */
        async function apiLoad() {
            try {
                const r = await fetch(API, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!r.ok) { console.warn('[annot] load', r.status); return []; }
                const j = await r.json();
                return Array.isArray(j.data) ? j.data : [];
            } catch (e) { console.error('[annot] apiLoad', e); return []; }
        }

        async function apiSave(payload) {
            showSync('Menyimpan...');
            try {
                const r = await fetch(API, {
                    method: 'POST',
                    headers: HEADERS(),
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });
                if (!r.ok) {
                    const txt = await r.text();
                    console.error('[annot] save err', r.status, txt);
                    showSync('Gagal simpan ✗');
                    return null;
                }
                const j = await r.json();
                showSync('Tersimpan ✓', true);
                return j.data || null;
            } catch (e) { console.error('[annot] apiSave', e); showSync('Gagal simpan ✗'); return null; }
        }

        async function apiDel(id) {
            showSync('Menghapus...');
            try {
                const r = await fetch(`${API}/${id}`, {
                    method: 'DELETE',
                    headers: HEADERS(),
                    credentials: 'same-origin',
                });
                if (!r.ok) console.warn('[annot] del', r.status);
                showSync('Dihapus ✓', true);
            } catch (e) { console.error('[annot] apiDel', e); }
        }

        async function apiDelPage(page) {
            showSync('Menghapus halaman...');
            try {
                await fetch(`${API}/page/${page}`, {
                    method: 'DELETE',
                    headers: HEADERS(),
                    credentials: 'same-origin',
                });
                showSync('Halaman dihapus ✓', true);
            } catch (e) { console.error('[annot] apiDelPage', e); }
        }

        /* ═══════════════════════════════════════════════════════════
           LOAD
        ═══════════════════════════════════════════════════════════ */
        async function loadAll() {
            annots = await apiLoad();
            console.log('[annot] loaded', annots.length);
            scheduleRender();
            updateBadge();
            updateUndoRedo();
        }

        /* ═══════════════════════════════════════════════════════════
           RENDER (schedule → requestAnimationFrame)
        ═══════════════════════════════════════════════════════════ */
        function scheduleRender() {
            if (renderPending) return;
            renderPending = true;
            requestAnimationFrame(() => { renderPending = false; renderPage(); });
        }

        function renderPage() {
            const page = V.pageNum;
            const scale = V.getScale();

            /* clear annotation layer */
            annotLayer.innerHTML = '';

            /* sync + clear freehand canvas */
            syncFreeCanvas();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);

            /* clear sticky notes from stage */
            stage.querySelectorAll('.sticky-note').forEach(e => e.remove());
            /* clear freetext */
            stage.querySelectorAll('.annot-freetext').forEach(e => e.remove());

            const list = annots.filter(a => a.page === page);
            list.forEach(a => {
                switch (a.type) {
                    case 'highlight': renderHL(a, scale); break;
                    case 'underline': renderUnderline(a, scale); break;
                    case 'strikethrough': renderStrike(a, scale); break;
                    case 'comment': renderHL(a, scale); break;
                    case 'freehand': renderFreehand(a, scale); break;
                    case 'shape': renderShape(a, scale); break;
                    case 'sticky': renderSticky(a, scale); break;
                    case 'text': renderFreeText(a, scale); break;
                }
            });

            updateBadge();
        }

        /* ── Highlight ────────────────────────────────────────────── */
        function renderHL(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            const isSelected = selectedId == a.id;
            el.className = 'annot-hl';
            el.dataset.id = String(a.id);
            el.style.cssText = `
                position:absolute;
                left:${a.rect.x * scale}px; top:${a.rect.y * scale}px;
                width:${a.rect.w * scale}px; height:${a.rect.h * scale}px;
                background:${colorHex(a.color)}; opacity:${isSelected ? 0.75 : 0.38};
                border-radius:2px; pointer-events:auto; cursor:pointer; z-index:5;
                outline:${isSelected ? '2px solid #FF6B18' : 'none'};
                transition:opacity .12s;
            `;
            el.title = a.comment || a.selected_text?.substring(0, 60) || '';
            el.addEventListener('mouseenter', () => el.style.opacity = '0.65');
            el.addEventListener('mouseleave', () => el.style.opacity = isSelected ? '0.75' : '0.38');
            el.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
            el.addEventListener('touchend', e => { e.preventDefault(); e.stopPropagation(); handleAnnotClick(a, e); }, { passive: false });
            annotLayer.appendChild(el);
        }

        /* ── Underline ────────────────────────────────────────────── */
        function renderUnderline(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.id = String(a.id);
            el.style.cssText = `
                position:absolute;
                left:${a.rect.x * scale}px;
                top:${(a.rect.y + a.rect.h - 1) * scale}px;
                width:${a.rect.w * scale}px; height:${2 * scale}px;
                background:${colorHex(a.color)}; opacity:0.9;
                pointer-events:auto; cursor:pointer; z-index:5;
            `;
            el.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
            annotLayer.appendChild(el);
        }

        /* ── Strikethrough ────────────────────────────────────────── */
        function renderStrike(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.id = String(a.id);
            el.style.cssText = `
                position:absolute;
                left:${a.rect.x * scale}px;
                top:${(a.rect.y + a.rect.h / 2) * scale}px;
                width:${a.rect.w * scale}px; height:${2 * scale}px;
                background:${colorHex(a.color)}; opacity:0.9;
                pointer-events:auto; cursor:pointer; z-index:5;
            `;
            el.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
            annotLayer.appendChild(el);
        }

        /* ── Freehand ─────────────────────────────────────────────── */
        function renderFreehand(a, scale) {
            if (!a.path_points || a.path_points.length < 2 || !freeCtx) return;
            freeCtx.save();
            freeCtx.strokeStyle = colorHex(a.color);
            freeCtx.lineWidth = (a.stroke_width || 2) * scale;
            freeCtx.lineCap = 'round';
            freeCtx.lineJoin = 'round';
            freeCtx.globalAlpha = 0.92;
            freeCtx.beginPath();
            const pts = a.path_points;
            freeCtx.moveTo(pts[0][0] * scale, pts[0][1] * scale);
            for (let i = 1; i < pts.length; i++) {
                freeCtx.lineTo(pts[i][0] * scale, pts[i][1] * scale);
            }
            freeCtx.stroke();
            freeCtx.restore();

            /* hitbox overlay di annotLayer agar bisa di-klik/erase */
            if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
                const hit = document.createElement('div');
                hit.dataset.id = String(a.id);
                hit.style.cssText = `
                    position:absolute;
                    left:${a.rect.x * scale}px; top:${a.rect.y * scale}px;
                    width:${Math.max(20, a.rect.w * scale)}px; height:${Math.max(20, a.rect.h * scale)}px;
                    background:transparent; pointer-events:auto; cursor:pointer; z-index:6;
                `;
                hit.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
                hit.addEventListener('touchend', e => { e.preventDefault(); e.stopPropagation(); handleAnnotClick(a, e); }, { passive: false });
                annotLayer.appendChild(hit);
            }
        }

        /* ── Shape ────────────────────────────────────────────────── */
        function renderShape(a, scale) {
            if (!a.rect) return;
            const x = a.rect.x * scale;
            const y = a.rect.y * scale;
            const w = Math.max(4, a.rect.w * scale);
            const h = Math.max(4, a.rect.h * scale);
            const sw = Math.max(1, (a.stroke_width || 2) * scale);
            const col = colorHex(a.color);
            const isSelected = selectedId == a.id;

            const wrap = document.createElement('div');
            wrap.className = 'annot-shape';
            wrap.dataset.id = String(a.id);
            wrap.style.cssText = `
                position:absolute; left:${x}px; top:${y}px;
                width:${w}px; height:${h}px;
                pointer-events:auto; cursor:pointer; z-index:5;
                outline:${isSelected ? '2px dashed #FF6B18' : 'none'};
            `;

            let svg = '';
            const st = a.shape_type || 'rect';
            if (st === 'rect') {
                const r = Math.min(2, w / 4, h / 4);
                svg = `<rect x="${sw / 2}" y="${sw / 2}" width="${w - sw}" height="${h - sw}" rx="${r}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            } else if (st === 'ellipse') {
                svg = `<ellipse cx="${w / 2}" cy="${h / 2}" rx="${Math.max(1, w / 2 - sw / 2)}" ry="${Math.max(1, h / 2 - sw / 2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            } else if (st === 'arrow') {
                const hh = Math.max(4, h * 0.35);
                const hx = Math.max(sw * 3, w * 0.25);
                svg = `
                    <line x1="${sw}" y1="${h / 2}" x2="${w - hx + sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>
                    <polygon points="${w - sw / 2},${h / 2} ${w - hx},${h / 2 - hh} ${w - hx},${h / 2 + hh}" fill="${col}"/>
                `;
            } else if (st === 'line') {
                svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
            }

            wrap.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
            wrap.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
            wrap.addEventListener('touchend', e => { e.preventDefault(); e.stopPropagation(); handleAnnotClick(a, e); }, { passive: false });
            annotLayer.appendChild(wrap);
        }

        /* ── Sticky ───────────────────────────────────────────────── */
        function renderSticky(a, scale) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'sticky-note';
            note.dataset.id = String(a.id);
            note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * scale) + 'px';
            note.style.top = (a.rect.y * scale) + 'px';
            note.innerHTML = `
                <div class="sn-header">
                    <span>📌 Catatan</span>
                    <button class="sn-del" data-id="${a.id}">×</button>
                </div>
                <div class="sn-body">${escHtml(a.comment || '')}</div>
            `;
            note.querySelector('.sn-del').addEventListener('click', async ev => {
                ev.stopPropagation();
                await removeAnnot(a.id);
            });
            makeDraggable(note, a, scale);
            stage.appendChild(note);
        }

        /* ── Free Text ────────────────────────────────────────────── */
        function renderFreeText(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.className = 'annot-freetext';
            el.dataset.id = String(a.id);
            el.style.cssText = `
                position:absolute;
                left:${a.rect.x * scale}px; top:${a.rect.y * scale}px;
                font-size:${(a.stroke_width || 14) * scale}px;
                color:${colorHex(a.color)};
                pointer-events:auto; cursor:pointer; z-index:8;
                white-space:pre-wrap; word-break:break-word;
                max-width:${200 * scale}px;
                text-shadow:0 1px 3px rgba(0,0,0,.5);
                font-family:sans-serif; font-weight:600;
            `;
            el.textContent = a.comment || '';
            el.addEventListener('click', e => { e.stopPropagation(); handleAnnotClick(a, e); });
            stage.appendChild(el);
        }

        /* ── Draggable helper ─────────────────────────────────────── */
        function makeDraggable(el, annotData, scale) {
            let ox = 0, oy = 0, dragging = false, moved = false;

            function onDown(e) {
                if (e.target.classList.contains('sn-del') || e.target.classList.contains('sn-body')) return;
                dragging = true; moved = false;
                const src = e.touches ? e.touches[0] : e;
                ox = src.clientX - el.offsetLeft;
                oy = src.clientY - el.offsetTop;
                el.style.zIndex = '20';
                e.stopPropagation();
                if (e.cancelable) e.preventDefault();
            }
            function onMove(e) {
                if (!dragging) return;
                moved = true;
                const src = e.touches ? e.touches[0] : e;
                el.style.left = (src.clientX - ox) + 'px';
                el.style.top = (src.clientY - oy) + 'px';
                if (e.cancelable) e.preventDefault();
            }
            function onUp() {
                if (!dragging) return;
                dragging = false; el.style.zIndex = '9';
                if (!moved) return;
                const newX = parseFloat(el.style.left) / scale;
                const newY = parseFloat(el.style.top) / scale;
                const idx = annots.findIndex(a => String(a.id) === String(annotData.id));
                if (idx >= 0 && annots[idx].rect) {
                    annots[idx].rect.x = newX;
                    annots[idx].rect.y = newY;
                }
            }

            el.addEventListener('mousedown', onDown, { passive: false });
            el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchend', onUp);
        }

        /* ═══════════════════════════════════════════════════════════
           ANNOT CLICK HANDLER (tooltip / select / erase)
        ═══════════════════════════════════════════════════════════ */
        function handleAnnotClick(a, e) {
            if (activeTool === 'eraser') {
                removeAnnot(a.id);
                return;
            }
            if (activeTool === 'select') {
                selectedId = (selectedId == a.id) ? null : a.id;
                scheduleRender();
                return;
            }
            /* default: tampilkan tooltip */
            showTip(a, e.clientX || (e.changedTouches?.[0]?.clientX || 0), e.clientY || (e.changedTouches?.[0]?.clientY || 0));
        }

        /* ═══════════════════════════════════════════════════════════
           TOOLTIP
        ═══════════════════════════════════════════════════════════ */
        function showTip(a, cx, cy) {
            if (!annotTip) return;
            const icons = { highlight: '✏️', underline: '_U_', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            const typeLabel = icons[a.type] || '•';
            let content = `${typeLabel} ${a.type}`;
            if (a.comment) content = `${typeLabel} ${a.comment.substring(0, 80)}`;
            else if (a.selected_text) content = `${typeLabel} "${a.selected_text.substring(0, 60)}"`;
            if (tipText) tipText.textContent = content;
            tipText.dataset.annotId = String(a.id);

            annotTip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            const tw = 270, th = 110;
            annotTip.style.left = Math.max(4, Math.min(cx - tw / 2, vw - tw - 4)) + 'px';
            annotTip.style.top = (cy + th > vh ? Math.max(4, cy - th - 8) : cy + 8) + 'px';
        }

        if (tipClose) tipClose.addEventListener('click', () => { annotTip?.classList.remove('show'); });
        if (tipDel) tipDel.addEventListener('click', async () => {
            const id = tipText?.dataset.annotId;
            annotTip?.classList.remove('show');
            if (id) await removeAnnot(id);
        });
        document.addEventListener('click', e => {
            if (annotTip && !annotTip.contains(e.target)
                && !e.target.closest('[data-id]')) {
                annotTip.classList.remove('show');
            }
        });

        /* ═══════════════════════════════════════════════════════════
           ADD / REMOVE
        ═══════════════════════════════════════════════════════════ */
        async function addAnnot(payload) {
            const saved = await apiSave(payload);
            if (!saved) { snack('❌ Gagal menyimpan — periksa koneksi', '#ef4444'); return null; }
            annots.push(saved);
            undoStack.push({ action: 'add', data: saved });
            redoStack = [];
            updateUndoRedo();
            scheduleRender();
            return saved;
        }

        async function removeAnnot(id) {
            const a = annots.find(x => String(x.id) === String(id));
            if (!a) return;
            await apiDel(a.id);
            annots = annots.filter(x => String(x.id) !== String(id));
            undoStack.push({ action: 'del', data: a });
            redoStack = [];
            updateUndoRedo();
            scheduleRender();
            snack('🗑 Anotasi dihapus');
        }

        /* ═══════════════════════════════════════════════════════════
           UNDO / REDO
        ═══════════════════════════════════════════════════════════ */
        function updateUndoRedo() {
            if (undoBtn) undoBtn.disabled = undoStack.length === 0;
            if (redoBtn) redoBtn.disabled = redoStack.length === 0;
        }

        async function doUndo() {
            if (!undoStack.length) return;
            const op = undoStack.pop();
            if (op.action === 'add') {
                /* undo add → delete */
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(op.data.id));
                    redoStack.push({ action: 'del', data: a });
                }
            } else {
                /* undo del → re-add */
                const a = op.data;
                const saved = await apiSave({
                    page: a.page, type: a.type, color: a.color,
                    rect_x: a.rect?.x, rect_y: a.rect?.y, rect_w: a.rect?.w, rect_h: a.rect?.h,
                    selected_text: a.selected_text, comment: a.comment,
                    path_points: a.path_points, shape_type: a.shape_type,
                    stroke_width: a.stroke_width, fill_opacity: a.fill_opacity,
                });
                if (saved) { annots.push(saved); redoStack.push({ action: 'add', data: saved }); }
            }
            updateUndoRedo();
            scheduleRender();
        }

        async function doRedo() {
            if (!redoStack.length) return;
            const op = redoStack.pop();
            if (op.action === 'add') {
                const a = op.data;
                const saved = await apiSave({
                    page: a.page, type: a.type, color: a.color,
                    rect_x: a.rect?.x, rect_y: a.rect?.y, rect_w: a.rect?.w, rect_h: a.rect?.h,
                    selected_text: a.selected_text, comment: a.comment,
                    path_points: a.path_points, shape_type: a.shape_type,
                    stroke_width: a.stroke_width,
                });
                if (saved) { annots.push(saved); undoStack.push({ action: 'add', data: saved }); }
            } else {
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(op.data.id));
                    undoStack.push({ action: 'del', data: a });
                }
            }
            updateUndoRedo();
            scheduleRender();
        }

        if (undoBtn) undoBtn.addEventListener('click', doUndo);
        if (redoBtn) redoBtn.addEventListener('click', doRedo);
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); }
        });

        /* ═══════════════════════════════════════════════════════════
           BADGE & PANEL
        ═══════════════════════════════════════════════════════════ */
        function updateBadge() {
            const total = annots.length;
            if (panelBadge) {
                panelBadge.textContent = total > 99 ? '99+' : String(total);
                panelBadge.classList.toggle('show', total > 0);
            }
            window.dispatchEvent(new CustomEvent('annot-count-change', { detail: { count: total } }));
        }

        if (panelBtn) panelBtn.addEventListener('click', e => {
            e.stopPropagation();
            annotPanel?.classList.toggle('open');
            buildPanel();
        });
        if (apClose) apClose.addEventListener('click', () => annotPanel?.classList.remove('open'));
        if (apClear) apClear.addEventListener('click', async () => {
            const page = V.pageNum;
            if (!confirm(`Hapus SEMUA anotasi di halaman ${page}?`)) return;
            await apiDelPage(page);
            annots = annots.filter(a => a.page !== page);
            undoStack = []; redoStack = [];
            updateUndoRedo();
            scheduleRender();
            buildPanel();
            snack(`🗑 Halaman ${page} dibersihkan`);
        });

        function buildPanel() {
            if (!apList) return;
            const pageAnnots = annots;
            if (!pageAnnots.length) {
                apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.<br>Gunakan tools di bawah untuk mulai!</div>';
                return;
            }
            apList.innerHTML = '';
            const icons = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            const sorted = [...pageAnnots].sort((a, b) => a.page - b.page || a.id - b.id);
            sorted.forEach(a => {
                const el = document.createElement('div');
                el.className = 'ap-item';
                el.innerHTML = `
                    <div class="ap-dot" style="background:${colorHex(a.color)}"></div>
                    <div class="ap-item-body">
                        <span class="ap-item-type">${icons[a.type] || '•'} ${a.type}</span>
                        <span class="ap-item-pg">Hal.${a.page}</span>
                        <div class="ap-item-text">${escHtml(a.comment || a.selected_text || a.shape_type || '—')}</div>
                    </div>
                    <button class="ap-item-del" data-id="${a.id}">🗑</button>
                `;
                el.querySelector('.ap-item-del').addEventListener('click', async ev => {
                    ev.stopPropagation();
                    await removeAnnot(a.id);
                    buildPanel();
                });
                el.addEventListener('click', () => {
                    if (a.page !== V.pageNum) V.queueRender(a.page);
                    annotPanel?.classList.remove('open');
                });
                apList.appendChild(el);
            });
        }

        /* ═══════════════════════════════════════════════════════════
           TOOL MANAGEMENT
        ═══════════════════════════════════════════════════════════ */
        function setTool(tool) {
            activeTool = tool;

            /* Stage cursor & pointer-events classes */
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode', 'text-tool-mode');
            if (tool === 'freehand') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            if (tool === 'text') stage.classList.add('text-tool-mode');

            /* text layer pointer events */
            if (textLayer) {
                const needsSel = ['highlight', 'comment', 'underline', 'strikethrough'].includes(tool);
                textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
                textLayer.style.userSelect = needsSel ? 'text' : 'none';
                textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
            }

            /* eraser cursor visibility */
            if (eraserCursor) eraserCursor.style.display = tool === 'eraser' ? 'block' : 'none';

            /* freehand canvas pointer events */
            if (freeCanvas) {
                const needsDraw = ['freehand', 'shape', 'eraser'].includes(tool);
                freeCanvas.style.pointerEvents = needsDraw ? 'auto' : 'none';
            }

            /* deselect on tool change */
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }

            console.log('[annot] tool →', tool);
        }

        /* listen to bottom bar events */
        window.addEventListener('annot-tool-change', e => setTool(e.detail.tool));
        window.addEventListener('annot-color-change', e => { activeColor = e.detail.color; });
        window.addEventListener('annot-size-change', e => { activeSize = +e.detail.size; });
        window.addEventListener('annot-shape-change', e => { activeShape = e.detail.shape; });

        setTool('highlight');

        /* ═══════════════════════════════════════════════════════════
           TEXT SELECTION → highlight / underline / strikethrough / comment
        ═══════════════════════════════════════════════════════════ */
        function getSelInfo() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0);
            if (!textLayer?.contains(range.commonAncestorContainer)) return null;
            const sr = stage.getBoundingClientRect();
            const scale = V.getScale();
            const rects = Array.from(range.getClientRects()).filter(r => r.width > 0.5 && r.height > 0.5);
            if (!rects.length) return null;
            const L = Math.min(...rects.map(r => r.left));
            const T = Math.min(...rects.map(r => r.top));
            const R = Math.max(...rects.map(r => r.right));
            const B = Math.max(...rects.map(r => r.bottom));
            return {
                rect: { x: (L - sr.left) / scale, y: (T - sr.top) / scale, w: (R - L) / scale, h: (B - T) / scale },
                text: sel.toString().substring(0, 1000),
            };
        }

        async function processSelectionTool() {
            const info = getSelInfo();
            if (!info || info.rect.w < 2 || info.rect.h < 1) return;

            if (activeTool === 'highlight') {
                await addAnnot({
                    page: V.pageNum, type: 'highlight', color: activeColor,
                    rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h,
                    selected_text: info.text
                });
                window.getSelection()?.removeAllRanges();
                snack('✏️ Highlight diterapkan!');
            } else if (activeTool === 'underline') {
                await addAnnot({
                    page: V.pageNum, type: 'underline', color: activeColor,
                    rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h,
                    selected_text: info.text
                });
                window.getSelection()?.removeAllRanges();
                snack('__ Underline diterapkan!');
            } else if (activeTool === 'strikethrough') {
                await addAnnot({
                    page: V.pageNum, type: 'strikethrough', color: activeColor,
                    rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h,
                    selected_text: info.text
                });
                window.getSelection()?.removeAllRanges();
                snack('~~ Strikethrough diterapkan!');
            } else if (activeTool === 'comment') {
                pendingRect = info.rect;
                pendingText = info.text;
                const br = window.getSelection()?.getRangeAt(0)?.getBoundingClientRect();
                openCommentPopup(br?.left ?? 200, br?.bottom ?? 300);
            }
        }

        /* Debounce mouseup/touchend untuk selection */
        let selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar,#annot-panel')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(processSelectionTool, 80);
        }
        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', onSelEnd, { passive: true });

        /* ═══════════════════════════════════════════════════════════
           COMMENT POPUP
        ═══════════════════════════════════════════════════════════ */
        function openCommentPopup(cx, cy) {
            if (!commentPop) return;
            const vw = window.innerWidth, vh = window.innerHeight;
            commentPop.style.left = Math.max(4, Math.min(cx - 140, vw - 292)) + 'px';
            commentPop.style.top = Math.max(4, (cy + 190 > vh ? cy - 200 : cy + 10)) + 'px';
            commentPop.classList.add('show');
            commentText?.focus();
        }

        if (commentSave) commentSave.addEventListener('click', async () => {
            const txt = commentText?.value.trim();
            if (!txt) { snack('Tulis komentar dulu!'); return; }
            if (!pendingRect) return;
            if (commentText) commentText.value = '';
            commentPop?.classList.remove('show');
            await addAnnot({
                page: V.pageNum, type: 'comment', color: activeColor,
                rect_x: pendingRect.x, rect_y: pendingRect.y, rect_w: pendingRect.w, rect_h: pendingRect.h,
                selected_text: pendingText || '', comment: txt
            });
            window.getSelection()?.removeAllRanges();
            pendingRect = null; pendingText = null;
            snack('💬 Komentar disimpan!');
        });
        if (commentCancel) commentCancel.addEventListener('click', () => {
            commentPop?.classList.remove('show');
            pendingRect = null; pendingText = null;
            window.getSelection()?.removeAllRanges();
        });

        /* ═══════════════════════════════════════════════════════════
           FREEHAND — events on freehand-canvas
        ═══════════════════════════════════════════════════════════ */
        function fhStart(e) {
            if (activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true;
            freePoints = [];
            const pos = stageXY(e);
            freePoints.push([pos.x / V.getScale(), pos.y / V.getScale()]);
        }
        function fhMove(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            const pos = stageXY(e);
            const scale = V.getScale();
            freePoints.push([pos.x / scale, pos.y / scale]);
            if (!freeCtx || freePoints.length < 2) return;
            const last = freePoints[freePoints.length - 2];
            const cur = freePoints[freePoints.length - 1];
            freeCtx.save();
            freeCtx.strokeStyle = colorHex(activeColor);
            freeCtx.lineWidth = activeSize * scale;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round';
            freeCtx.globalAlpha = 0.92;
            freeCtx.beginPath();
            freeCtx.moveTo(last[0] * scale, last[1] * scale);
            freeCtx.lineTo(cur[0] * scale, cur[1] * scale);
            freeCtx.stroke();
            freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = false;
            if (freePoints.length < 2) return;
            const scale = V.getScale();
            const xs = freePoints.map(p => p[0]), ys = freePoints.map(p => p[1]);
            const bx = Math.min(...xs), by = Math.min(...ys);
            const bw = Math.max(...xs) - bx, bh = Math.max(...ys) - by;
            await addAnnot({
                page: V.pageNum, type: 'freehand', color: activeColor, stroke_width: activeSize,
                path_points: freePoints,
                rect_x: bx, rect_y: by, rect_w: bw, rect_h: bh,
            });
        }

        /* ═══════════════════════════════════════════════════════════
           SHAPE — events on freehand-canvas
        ═══════════════════════════════════════════════════════════ */
        function shStart(e) {
            if (activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true;
            drawStart = stageXY(e);
            shapePreviewEl = document.createElement('div');
            const col = colorHex(activeColor);
            shapePreviewEl.style.cssText = `
                position:absolute; pointer-events:none; z-index:25;
                border:${activeSize}px solid ${col};
                ${activeShape === 'ellipse' ? 'border-radius:50%;' : ''}
                ${activeShape === 'line' ? 'border-width:0 0 ${activeSize}px 0;height:0 !important;' : ''}
                left:${drawStart.x}px; top:${drawStart.y}px; width:0px; height:0px;
            `;
            stage.appendChild(shapePreviewEl);
        }
        function shMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !shapePreviewEl || !drawStart) return;
            if (e.cancelable) e.preventDefault();
            const cur = stageXY(e);
            const x = Math.min(drawStart.x, cur.x);
            const y = Math.min(drawStart.y, cur.y);
            const w = Math.abs(cur.x - drawStart.x);
            const h = Math.abs(cur.y - drawStart.y);
            Object.assign(shapePreviewEl.style, { left: x + 'px', top: y + 'px', width: w + 'px', height: h + 'px' });
        }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = false;
            shapePreviewEl?.remove(); shapePreviewEl = null;
            const src = e.changedTouches ? e.changedTouches[0] : e;
            const cur = { x: src.clientX - stage.getBoundingClientRect().left, y: src.clientY - stage.getBoundingClientRect().top };
            const scale = V.getScale();
            const x = Math.min(drawStart.x, cur.x) / scale;
            const y = Math.min(drawStart.y, cur.y) / scale;
            const w = Math.abs(cur.x - drawStart.x) / scale;
            const h = Math.abs(cur.y - drawStart.y) / scale;
            drawStart = null;
            if (w < 4 && h < 4) return;
            const finalH = activeShape === 'line' ? 1 : h;
            await addAnnot({
                page: V.pageNum, type: 'shape', color: activeColor, shape_type: activeShape, stroke_width: activeSize,
                rect_x: x, rect_y: y, rect_w: w, rect_h: finalH,
            });
        }

        /* attach freehand & shape events */
        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('mousemove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('mouseup', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('mouseleave', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('touchstart', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('touchmove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('touchend', e => { fhEnd(e); shEnd(e); }, { passive: false });
        }

        /* ═══════════════════════════════════════════════════════════
           STICKY NOTE
        ═══════════════════════════════════════════════════════════ */
        let stickyClickPos = null;

        stage.addEventListener('click', e => {
            if (activeTool !== 'sticky') return;
            if (e.target.closest('.sticky-note,#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar')) return;
            const pos = stageXY(e);
            const scale = V.getScale();
            stickyClickPos = { x: pos.x / scale, y: pos.y / scale };
            openStickyPopup(e.clientX, e.clientY);
        });
        stage.addEventListener('touchend', e => {
            if (activeTool !== 'sticky') return;
            if (e.target.closest('.sticky-note,#comment-popup,#sticky-popup,#annot-bottom-bar')) return;
            const t = e.changedTouches[0];
            const pos = stageXY(e);
            const scale = V.getScale();
            stickyClickPos = { x: pos.x / scale, y: pos.y / scale };
            openStickyPopup(t.clientX, t.clientY);
        }, { passive: true });

        function openStickyPopup(cx, cy) {
            if (!stickyPop) { console.warn('[annot] #sticky-popup not found'); return; }
            const vw = window.innerWidth, vh = window.innerHeight;
            stickyPop.style.left = Math.max(4, Math.min(cx - 130, vw - 276)) + 'px';
            stickyPop.style.top = Math.max(4, (cy + 210 > vh ? cy - 220 : cy + 12)) + 'px';
            stickyPop.classList.add('show');
            if (stickyText) { stickyText.value = ''; setTimeout(() => stickyText.focus(), 50); }
        }

        if (stickySave) stickySave.addEventListener('click', async () => {
            const txt = stickyText?.value.trim();
            if (!txt) { snack('Tulis catatan dulu!'); return; }
            if (!stickyClickPos) return;
            if (stickyText) stickyText.value = '';
            stickyPop?.classList.remove('show');
            await addAnnot({
                page: V.pageNum, type: 'sticky', color: activeColor,
                rect_x: stickyClickPos.x, rect_y: stickyClickPos.y, rect_w: 180, rect_h: 90,
                comment: txt,
            });
            stickyClickPos = null;
            snack('📌 Sticky note ditempel!');
        });
        if (stickyCancel) stickyCancel.addEventListener('click', () => {
            stickyPop?.classList.remove('show');
            stickyClickPos = null;
        });

        /* ═══════════════════════════════════════════════════════════
           FREE TEXT TOOL
        ═══════════════════════════════════════════════════════════ */
        let textClickPos = null;

        stage.addEventListener('click', e => {
            if (activeTool !== 'text') return;
            if (e.target.closest('.annot-freetext,#freetext-popup,#annot-bottom-bar')) return;
            const pos = stageXY(e);
            const scale = V.getScale();
            textClickPos = { x: pos.x / scale, y: pos.y / scale };
            openTextPopup(e.clientX, e.clientY);
        });

        function openTextPopup(cx, cy) {
            if (!textPop) {
                /* Buat popup on-the-fly jika belum ada di DOM */
                const p = document.createElement('div');
                p.id = 'freetext-popup';
                p.style.cssText = 'position:fixed;background:#1a1a1a;border:2px solid #ff6b18;border-radius:14px;padding:.875rem;width:260px;z-index:20006;box-shadow:0 12px 40px rgba(0,0,0,.6);';
                p.innerHTML = `
                    <p style="font-size:12px;font-weight:700;color:#ff6b18;margin:0 0 .5rem">🔤 Tambah Teks</p>
                    <textarea id="freetext-input" placeholder="Ketik teks di sini..."
                        style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:60px;"></textarea>
                    <div style="display:flex;gap:.4rem;margin-top:.5rem">
                        <button id="freetext-save" style="flex:1;padding:.45rem;background:#ff6b18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Tambahkan</button>
                        <button id="freetext-cancel" style="padding:.45rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>
                    </div>
                `;
                document.body.appendChild(p);
                document.getElementById('freetext-save').addEventListener('click', saveText);
                document.getElementById('freetext-cancel').addEventListener('click', () => {
                    p.style.display = 'none'; textClickPos = null;
                });
            }
            const pop = document.getElementById('freetext-popup');
            const vw = window.innerWidth, vh = window.innerHeight;
            pop.style.display = 'block';
            pop.style.left = Math.max(4, Math.min(cx - 130, vw - 276)) + 'px';
            pop.style.top = Math.max(4, (cy + 180 > vh ? cy - 190 : cy + 12)) + 'px';
            setTimeout(() => document.getElementById('freetext-input')?.focus(), 50);
        }

        async function saveText() {
            const inp = document.getElementById('freetext-input');
            const txt = inp?.value.trim();
            if (!txt || !textClickPos) return;
            if (inp) inp.value = '';
            const pop = document.getElementById('freetext-popup');
            if (pop) pop.style.display = 'none';
            await addAnnot({
                page: V.pageNum, type: 'text', color: activeColor, stroke_width: activeSize,
                rect_x: textClickPos.x, rect_y: textClickPos.y, rect_w: 200, rect_h: 30,
                comment: txt,
            });
            textClickPos = null;
            snack('🔤 Teks ditambahkan!');
        }

        /* ═══════════════════════════════════════════════════════════
           ERASER — klik/touch pada elemen yang memiliki data-id
        ═══════════════════════════════════════════════════════════ */
        /* Eraser cursor follow mouse */
        document.addEventListener('mousemove', e => {
            if (activeTool !== 'eraser' || !eraserCursor) return;
            eraserCursor.style.display = 'block';
            eraserCursor.style.left = e.clientX + 'px';
            eraserCursor.style.top = e.clientY + 'px';
        });
        document.addEventListener('mouseleave', () => {
            if (eraserCursor) eraserCursor.style.display = 'none';
        });

        /* Click on annotLayer elements */
        annotLayer.addEventListener('click', async e => {
            if (activeTool !== 'eraser') return;
            e.stopPropagation();
            const target = e.target.closest('[data-id]');
            if (target) await removeAnnot(target.dataset.id);
        });

        /* Click on sticky notes */
        stage.addEventListener('click', async e => {
            if (activeTool !== 'eraser') return;
            const sn = e.target.closest('.sticky-note');
            if (sn) { e.stopPropagation(); await removeAnnot(sn.dataset.id); }
            const ft = e.target.closest('.annot-freetext');
            if (ft) { e.stopPropagation(); await removeAnnot(ft.dataset.id); }
        });

        /* Touch eraser */
        annotLayer.addEventListener('touchend', async e => {
            if (activeTool !== 'eraser') return;
            e.preventDefault();
            const t = e.changedTouches[0];
            const els = document.elementsFromPoint(t.clientX, t.clientY);
            for (const el of els) {
                const tgt = el.closest('[data-id]');
                if (tgt) { await removeAnnot(tgt.dataset.id); return; }
            }
        }, { passive: false });

        /* ═══════════════════════════════════════════════════════════
           SELECT TOOL
        ═══════════════════════════════════════════════════════════ */
        /* Click on empty area deselects */
        stage.addEventListener('click', e => {
            if (activeTool !== 'select') return;
            if (!e.target.closest('[data-id],.sticky-note,.annot-freetext')) {
                selectedId = null;
                scheduleRender();
            }
        });
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId && activeTool === 'select') {
                removeAnnot(selectedId);
                selectedId = null;
            }
        });

        /* ═══════════════════════════════════════════════════════════
           PAN TOOL (hand)
        ═══════════════════════════════════════════════════════════ */
        function panStart(e) {
            if (activeTool !== 'pan') return;
            isPanning = true;
            const src = e.touches ? e.touches[0] : e;
            panStartX = src.clientX;
            panStartY = src.clientY;
            panScrollX = canvasWrap?.scrollLeft || 0;
            panScrollY = canvasWrap?.scrollTop || 0;
            if (e.cancelable) e.preventDefault();
        }
        function panMove(e) {
            if (!isPanning || activeTool !== 'pan') return;
            const src = e.touches ? e.touches[0] : e;
            const dx = panStartX - src.clientX;
            const dy = panStartY - src.clientY;
            if (canvasWrap) {
                canvasWrap.scrollLeft = panScrollX + dx;
                canvasWrap.scrollTop = panScrollY + dy;
            }
            if (e.cancelable) e.preventDefault();
        }
        function panEnd() { isPanning = false; }

        stage.addEventListener('mousedown', panStart, { passive: false });
        stage.addEventListener('mousemove', panMove, { passive: false });
        stage.addEventListener('mouseup', panEnd);
        stage.addEventListener('touchstart', panStart, { passive: false });
        stage.addEventListener('touchmove', panMove, { passive: false });
        stage.addEventListener('touchend', panEnd, { passive: true });

        /* ═══════════════════════════════════════════════════════════
           ZOOM ANTI-FLICKER
           Observe canvas size changes → schedule re-render anotasi
        ═══════════════════════════════════════════════════════════ */
        let zoomTimer = null;
        const canvasEl = document.getElementById('pdf-canvas');
        if (canvasEl) {
            new MutationObserver(() => {
                clearTimeout(zoomTimer);
                zoomTimer = setTimeout(() => {
                    syncFreeCanvas();
                    scheduleRender();
                }, 60);
            }).observe(canvasEl, { attributes: true, attributeFilter: ['width', 'height'] });
        }

        /* ═══════════════════════════════════════════════════════════
           PAGE CHANGE HOOK
        ═══════════════════════════════════════════════════════════ */
        V.onPageChange = function (newPage) {
            commentPop?.classList.remove('show');
            stickyPop?.classList.remove('show');
            annotTip?.classList.remove('show');
            pendingRect = null; pendingText = null;
            stickyClickPos = null; textClickPos = null;
            window.getSelection()?.removeAllRanges();
            scheduleRender();
        };

        /* ═══════════════════════════════════════════════════════════
           INIT
        ═══════════════════════════════════════════════════════════ */
        V.onReady(async () => {
            syncFreeCanvas();
            await loadAll();
        });

        window._pdfAnnotations = true;
        console.log('[annot] v3.0 loaded, slug=', slug);

    }); // waitForViewer

})();
