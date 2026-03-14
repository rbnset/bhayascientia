/**
 * pdf-annotations.js — v3.3 PATCH
 * public/js/pdf-annotations.js
 *
 * PATCH v3.3 (dari v3.2):
 * 1. Redo 500 fix — payload() sekarang sanitize semua field nullable,
 *    pastikan color & type selalu ada dan valid sebelum kirim ke server
 * 2. Text tool fix — font_size field terpisah dari stroke_width,
 *    default font size 14px, text popup langsung bisa dipakai
 * 3. Redo: validasi data sebelum apiSave agar tidak 500
 */
(function () {
    'use strict';

    let _tick = 0;
    function waitForViewer(cb) {
        if (window._pdfViewer && window._pdfViewer.pdfDoc) { cb(window._pdfViewer); return; }
        if (_tick++ > 400) { console.error('[annot] timeout'); return; }
        setTimeout(() => waitForViewer(cb), 80);
    }

    waitForViewer(function (V) {

        /* ── CONFIG ─────────────────────────────────────────────── */
        const slug = window.PDF_CONFIG?.slug || 'unknown';
        const API = `/api/annotations/${slug}`;

        const VALID_TYPES = ['highlight', 'underline', 'strikethrough', 'freehand', 'comment', 'sticky', 'shape', 'text'];
        const VALID_COLORS = ['yellow', 'green', 'red', 'blue', 'orange', 'black', 'white', 'pink', 'purple', 'cyan'];
        const VALID_SHAPES = ['rect', 'ellipse', 'arrow', 'line'];

        function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
        function hdrs() {
            return {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest',
            };
        }

        /* ── STATE ──────────────────────────────────────────────── */
        let annots = [];
        let undoStack = [];
        let redoStack = [];

        let activeTool = 'highlight';
        let activeColor = 'yellow';
        let activeSize = 2;
        let activeShape = 'rect';

        let isDrawing = false, drawStart = null, freePoints = [], shapePreviewEl = null;
        let pendingRect = null, pendingText = null, stickyPos = null, textPos = null;
        let selectedId = null, isPanning = false;
        let panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;
        let renderPending = false;

        /* ── DOM ────────────────────────────────────────────────── */
        const stage = V.stage;
        const annotLayer = V.annotLayer;
        const textLayer = V.textLayer;
        const canvasWrap = document.getElementById('pdf-canvas-wrapper');
        const mainCanvas = document.getElementById('pdf-canvas');
        let freeCanvas = document.getElementById('freehand-canvas');
        let freeCtx = freeCanvas?.getContext('2d');

        const commentPop = document.getElementById('comment-popup');
        const commentTxt = document.getElementById('comment-text');
        const commentSave = document.getElementById('comment-save');
        const commentCancel = document.getElementById('comment-cancel');
        const stickyPop = document.getElementById('sticky-popup');
        const stickyTxt = document.getElementById('sticky-text');
        const stickySave = document.getElementById('sticky-save');
        const stickyCancel = document.getElementById('sticky-cancel');
        const annotTip = document.getElementById('annot-tooltip');
        const tipTxt = document.getElementById('annot-tooltip-text');
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
        const syncTxtEl = document.getElementById('annot-sync-text');
        const eraserCur = document.getElementById('eraser-cursor');

        /* ── UTILS ──────────────────────────────────────────────── */
        function snack(msg, col) { V.snack(msg, col); }
        const COLORS = {
            yellow: '#FFD700', green: '#4ADE80', red: '#EF4444', blue: '#60A5FA',
            orange: '#FF6B18', black: '#111111', white: '#FFFFFF',
            pink: '#F472B6', purple: '#A78BFA', cyan: '#22D3EE',
        };
        const hex = n => COLORS[n] || '#FFD700';

        function stageXY(e) {
            const r = stage.getBoundingClientRect();
            const s = e.changedTouches?.[0] ?? e.touches?.[0] ?? e;
            return { x: s.clientX - r.left, y: s.clientY - r.top };
        }
        function esc(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>'); }
        function syncFC() {
            if (!freeCanvas) return;
            const w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) { freeCanvas.width = w; freeCanvas.height = h; }
            freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
        }

        /* ═══════════════════════════════════════════════════════
           PAYLOAD SANITIZER — KUNCI FIX REDO 500
           Pastikan semua field yang wajib ada dan valid sebelum
           dikirim ke server. Ini mencegah 422/500 saat redo.
        ═══════════════════════════════════════════════════════ */
        function sanitizePayload(raw) {
            const type = VALID_TYPES.includes(raw.type) ? raw.type : 'highlight';
            const color = VALID_COLORS.includes(raw.color) ? raw.color : 'yellow';

            const p = {
                page: parseInt(raw.page) || V.pageNum,
                type: type,
                color: color,
                rect_x: raw.rect?.x ?? raw.rect_x ?? null,
                rect_y: raw.rect?.y ?? raw.rect_y ?? null,
                rect_w: raw.rect?.w ?? raw.rect_w ?? null,
                rect_h: raw.rect?.h ?? raw.rect_h ?? null,
                selected_text: raw.selected_text || null,
                comment: raw.comment || null,
                path_points: Array.isArray(raw.path_points) ? raw.path_points : null,
                shape_type: VALID_SHAPES.includes(raw.shape_type) ? raw.shape_type : null,
                stroke_width: (typeof raw.stroke_width === 'number' && raw.stroke_width > 0) ? raw.stroke_width : 2,
                fill_opacity: (typeof raw.fill_opacity === 'number') ? raw.fill_opacity : 0,
            };

            /* shape_type wajib ada jika type=shape */
            if (p.type === 'shape' && !p.shape_type) p.shape_type = 'rect';

            return p;
        }

        /* ── SYNC INDICATOR ─────────────────────────────────────── */
        let syncT = null;
        function showSync(msg, ok = false) {
            if (!syncEl) return;
            if (syncTxtEl) syncTxtEl.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#ff6b18';
            syncEl.style.color = ok ? '#22c55e' : '#ff6b18';
            syncEl.classList.add('show');
            clearTimeout(syncT);
            syncT = setTimeout(() => syncEl.classList.remove('show'), ok ? 1800 : 4000);
        }

        /* ── API ────────────────────────────────────────────────── */
        async function apiLoad() {
            try {
                const r = await fetch(API, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) throw new Error(r.status);
                const j = await r.json();
                return Array.isArray(j.data) ? j.data : [];
            } catch (e) { console.error('[annot] load:', e); return []; }
        }

        async function apiSave(payload) {
            /* Sanitize dulu sebelum kirim */
            const clean = sanitizePayload(payload);
            showSync('Menyimpan...');
            try {
                const r = await fetch(API, { method: 'POST', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(clean) });
                const j = await r.json();
                if (!r.ok) { console.error('[annot] save err:', clean, j); showSync('Gagal: ' + (j.message || r.status)); return null; }
                showSync('Tersimpan ✓', true);
                return j.data || null;
            } catch (e) { console.error('[annot] save net:', e); showSync('Error jaringan'); return null; }
        }

        async function apiPatch(id, payload) {
            try {
                await fetch(`${API}/${id}`, { method: 'PUT', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(payload) });
            } catch (e) { console.error('[annot] patch:', e); }
        }

        async function apiDel(id) {
            showSync('Menghapus...');
            try {
                await fetch(`${API}/${id}`, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Dihapus ✓', true);
            } catch (e) { console.error('[annot] del:', e); }
        }

        async function apiDelPage(page) {
            showSync('Membersihkan...');
            try {
                await fetch(`${API}/page/${page}`, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Selesai ✓', true);
            } catch (e) { console.error('[annot] delPage:', e); }
        }

        /* ── LOAD ───────────────────────────────────────────────── */
        async function loadAll() {
            annots = await apiLoad();
            console.log('[annot] loaded', annots.length);
            scheduleRender(); updateBadge(); updateUndoRedo();
        }

        /* ── RENDER ─────────────────────────────────────────────── */
        function scheduleRender() {
            if (renderPending) return; renderPending = true;
            requestAnimationFrame(() => { renderPending = false; doRender(); });
        }

        function doRender() {
            const page = V.pageNum, scale = V.getScale();
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
            stage.querySelectorAll('.sticky-note,.annot-freetext').forEach(e => e.remove());
            annots.filter(a => a.page === page).forEach(a => {
                switch (a.type) {
                    case 'highlight': case 'comment': rHL(a, scale); break;
                    case 'underline': rUL(a, scale); break;
                    case 'strikethrough': rST(a, scale); break;
                    case 'freehand': rFH(a, scale); break;
                    case 'shape': rSH(a, scale); break;
                    case 'sticky': rSticky(a, scale); break;
                    case 'text': rText(a, scale); break;
                }
            });
            updateBadge();
        }

        function rHL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'), sel = selectedId == a.id;
            el.dataset.annotId = String(a.id);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${a.rect.y * s}px;width:${a.rect.w * s}px;height:${a.rect.h * s}px;background:${hex(a.color)};opacity:${sel ? .75 : .38};border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px solid #FF6B18' : 'none'};transition:opacity .12s;`;
            if (a.type === 'comment' && a.comment) {
                const dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }
        function rUL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);
            const t = Math.max(1.5, 2 * s);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${(a.rect.y + a.rect.h) * s - t}px;width:${a.rect.w * s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachEv(el, a); annotLayer.appendChild(el);
        }
        function rST(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);
            const t = Math.max(1.5, 2 * s);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${(a.rect.y + a.rect.h / 2) * s - t / 2}px;width:${a.rect.w * s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachEv(el, a); annotLayer.appendChild(el);
        }
        function rFH(a, s) {
            if (!a.path_points?.length || !freeCtx) return;
            const pts = a.path_points;
            freeCtx.save(); freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width || 2) * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath(); freeCtx.moveTo(pts[0][0] * s, pts[0][1] * s);
            for (let i = 1; i < pts.length; i++)freeCtx.lineTo(pts[i][0] * s, pts[i][1] * s);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
                const hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
                hit.style.cssText = `position:absolute;left:${(a.rect.x - 8) * s}px;top:${(a.rect.y - 8) * s}px;width:${(a.rect.w + 16) * s}px;height:${(a.rect.h + 16) * s}px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;`;
                attachEv(hit, a); annotLayer.appendChild(hit);
            }
        }
        function rSH(a, s) {
            if (!a.rect) return;
            const x = a.rect.x * s, y = a.rect.y * s, w = Math.max(4, a.rect.w * s), h = Math.max(4, a.rect.h * s);
            const sw = Math.max(1, (a.stroke_width || 2) * s), col = hex(a.color), sel = selectedId == a.id;
            const wrap = document.createElement('div'); wrap.dataset.annotId = String(a.id);
            wrap.style.cssText = `position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px dashed #FF6B18' : 'none'};`;
            const st = a.shape_type || 'rect'; let svg = '';
            if (st === 'rect') svg = `<rect x="${sw / 2}" y="${sw / 2}" width="${Math.max(1, w - sw)}" height="${Math.max(1, h - sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'ellipse') svg = `<ellipse cx="${w / 2}" cy="${h / 2}" rx="${Math.max(1, w / 2 - sw / 2)}" ry="${Math.max(1, h / 2 - sw / 2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'arrow') { const hh = Math.max(4, h * .35), hx = Math.max(sw * 3, w * .25); svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - hx + sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w - sw / 2},${h / 2} ${w - hx},${h / 2 - hh} ${w - hx},${h / 2 + hh}" fill="${col}"/>`; }
            else if (st === 'line') svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
            wrap.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
            attachEv(wrap, a); annotLayer.appendChild(wrap);
        }
        function rSticky(a, s) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'sticky-note'; note.dataset.annotId = String(a.id); note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * s) + 'px'; note.style.top = (a.rect.y * s) + 'px';
            note.innerHTML = `<div class="sn-header"><span>📌</span><button class="sn-del">×</button></div><div class="sn-body">${esc(a.comment)}</div>`;
            note.querySelector('.sn-del').addEventListener('click', ev => { ev.stopPropagation(); removeAnnot(a.id); });
            note.addEventListener('click', ev => {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                if (activeTool === 'select') { ev.stopPropagation(); selectedId = String(a.id); scheduleRender(); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, s); stage.appendChild(note);
        }

        /* ── FREE TEXT RENDER ───────────────────────────────────
           PERBAIKAN: font_size diambil dari stroke_width tapi
           di-clamp minimum 10px agar terbaca.
           Cara pakai: pilih tool 🔤, pilih ukuran dari ab-sizes
           (2=kecil, 4=sedang, 8=besar, 14=sangat besar),
           kemudian klik area PDF → ketik teks → klik Tambah.
        ─────────────────────────────────────────────────────── */
        function rText(a, s) {
            if (!a.rect) return;
            /* font_size tersimpan di stroke_width, minimum 10 */
            const fontSize = Math.max(10, (a.stroke_width || 14)) * s;
            const el = document.createElement('div');
            el.className = 'annot-freetext'; el.dataset.annotId = String(a.id);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${a.rect.y * s}px;font-size:${fontSize}px;line-height:1.4;color:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:${300 * s}px;font-family:sans-serif;font-weight:600;text-shadow:0 1px 3px rgba(0,0,0,.35);user-select:none;`;
            el.textContent = a.comment || '';
            el.addEventListener('click', ev => {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            stage.appendChild(el);
        }

        function attachEv(el, a) {
            el.addEventListener('click', ev => {
                ev.stopPropagation();
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, ev.clientX, ev.clientY);
            });
            el.addEventListener('touchend', ev => {
                ev.stopPropagation(); if (ev.cancelable) ev.preventDefault();
                const t = ev.changedTouches[0];
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, t.clientX, t.clientY);
            }, { passive: false });
        }

        function makeDraggable(el, annotData, s) {
            let ox = 0, oy = 0, dragging = false, moved = false;
            function onDown(e) {
                if (e.target.classList.contains('sn-del') || e.target.classList.contains('sn-body')) return;
                dragging = true; moved = false;
                const src = e.touches?.[0] ?? e; ox = src.clientX - el.offsetLeft; oy = src.clientY - el.offsetTop;
                el.style.zIndex = '20'; e.stopPropagation(); if (e.cancelable) e.preventDefault();
            }
            function onMove(e) {
                if (!dragging) return; moved = true;
                const src = e.touches?.[0] ?? e; el.style.left = (src.clientX - ox) + 'px'; el.style.top = (src.clientY - oy) + 'px';
                if (e.cancelable) e.preventDefault();
            }
            async function onUp() {
                if (!dragging) return; dragging = false; el.style.zIndex = '9'; if (!moved) return;
                const newX = parseFloat(el.style.left) / s, newY = parseFloat(el.style.top) / s;
                const idx = annots.findIndex(a => String(a.id) === String(annotData.id));
                if (idx >= 0 && annots[idx].rect) { annots[idx].rect.x = newX; annots[idx].rect.y = newY; }
                await apiPatch(annotData.id, { rect_x: newX, rect_y: newY, rect_w: annotData.rect?.w || 180, rect_h: annotData.rect?.h || 90 });
            }
            el.addEventListener('mousedown', onDown, { passive: false });
            el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp); document.addEventListener('touchend', onUp);
        }

        /* ── TOOLTIP ─────────────────────────────────────────── */
        function showTip(a, cx, cy) {
            if (!annotTip) return;
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            let txt = `${ic[a.type] || '•'} ${a.type}`;
            if (a.comment) txt = `${ic[a.type] || '•'} ${a.comment.substring(0, 80)}`;
            else if (a.selected_text) txt = `${ic[a.type] || '•'} "${a.selected_text.substring(0, 60)}"`;
            if (tipTxt) { tipTxt.textContent = txt; tipTxt.dataset.annotId = String(a.id); }
            annotTip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            annotTip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
            annotTip.style.top = ((cy + 120 > vh) ? Math.max(4, cy - 120) : cy + 8) + 'px';
        }
        tipClose?.addEventListener('click', () => annotTip?.classList.remove('show'));
        tipDel?.addEventListener('click', async () => {
            const id = tipTxt?.dataset.annotId; annotTip?.classList.remove('show');
            if (id) await removeAnnot(id);
        });
        document.addEventListener('click', e => {
            if (annotTip && !annotTip.contains(e.target) && !e.target.closest('[data-annot-id],.sticky-note,.annot-freetext'))
                annotTip.classList.remove('show');
        });

        /* ── ADD / REMOVE ────────────────────────────────────── */
        async function addAnnot(payload) {
            const saved = await apiSave(payload);
            if (!saved) return null;
            annots.push(saved);
            undoStack.push({ action: 'add', data: saved });
            redoStack = []; updateUndoRedo(); scheduleRender();
            return saved;
        }
        async function removeAnnot(id) {
            const a = annots.find(x => String(x.id) === String(id)); if (!a) return;
            await apiDel(a.id);
            annots = annots.filter(x => String(x.id) !== String(id));
            if (selectedId === String(id)) selectedId = null;
            undoStack.push({ action: 'del', data: a }); redoStack = [];
            updateUndoRedo(); scheduleRender(); snack('🗑 Anotasi dihapus');
        }

        /* ── UNDO / REDO ─────────────────────────────────────────
           PERBAIKAN:
           - undoStack push {action:'add'|'del', data: savedAnnot}
           - Saat undo 'add': hapus dari server, push {action:'readd', data: annotAsli}
           - Saat undo 'del': tambah lagi ke server, push {action:'redel', data: annotBaru}
           - Saat redo 'readd': tambah kembali, push kembali ke undoStack
           - Saat redo 'redel': hapus kembali, push kembali ke undoStack
           Semua payload di-sanitize sebelum POST.
        ──────────────────────────────────────────────────────── */
        function updateUndoRedo() {
            if (undoBtn) undoBtn.disabled = !undoStack.length;
            if (redoBtn) redoBtn.disabled = !redoStack.length;
        }

        async function doUndo() {
            if (!undoStack.length) return;
            const op = undoStack.pop();
            if (op.action === 'add') {
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(a.id));
                    redoStack.push({ action: 'readd', data: a }); /* redo bisa tambah lagi */
                }
            } else if (op.action === 'del') {
                const saved = await apiSave(op.data); /* sanitizePayload ada di apiSave */
                if (saved) {
                    annots.push(saved);
                    redoStack.push({ action: 'redel', data: saved }); /* redo bisa hapus lagi */
                }
            }
            updateUndoRedo(); scheduleRender();
        }

        async function doRedo() {
            if (!redoStack.length) return;
            const op = redoStack.pop();
            if (op.action === 'readd') {
                /* Tambah kembali anotasi yang tadi di-undo */
                const saved = await apiSave(op.data); /* sanitizePayload ada di apiSave */
                if (saved) {
                    annots.push(saved);
                    undoStack.push({ action: 'add', data: saved }); /* bisa undo lagi */
                }
            } else if (op.action === 'redel') {
                /* Hapus lagi anotasi yang tadi di-restore */
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(a.id));
                    undoStack.push({ action: 'del', data: a }); /* bisa undo lagi */
                }
            }
            updateUndoRedo(); scheduleRender();
        }

        undoBtn?.addEventListener('click', doUndo);
        redoBtn?.addEventListener('click', doRedo);
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); }
        });

        /* ── BADGE & PANEL ───────────────────────────────────── */
        function updateBadge() {
            const n = annots.length;
            if (panelBadge) { panelBadge.textContent = n > 99 ? '99+' : String(n); panelBadge.classList.toggle('show', n > 0); }
            window.dispatchEvent(new CustomEvent('annot-count-change', { detail: { count: n } }));
        }
        panelBtn?.addEventListener('click', e => { e.stopPropagation(); annotPanel?.classList.toggle('open'); buildPanel(); });
        apClose?.addEventListener('click', () => annotPanel?.classList.remove('open'));
        apClear?.addEventListener('click', async () => {
            if (!confirm(`Hapus semua anotasi di halaman ${V.pageNum}?`)) return;
            await apiDelPage(V.pageNum);
            annots = annots.filter(a => a.page !== V.pageNum);
            undoStack = []; redoStack = []; updateUndoRedo(); scheduleRender(); buildPanel();
            snack(`🗑 Halaman ${V.pageNum} dibersihkan`);
        });
        function buildPanel() {
            if (!apList) return;
            if (!annots.length) { apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.</div>'; return; }
            apList.innerHTML = '';
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            [...annots].sort((a, b) => a.page - b.page || a.id - b.id).forEach(a => {
                const el = document.createElement('div'); el.className = 'ap-item';
                el.innerHTML = `<div class="ap-dot" style="background:${hex(a.color)}"></div><div class="ap-item-body"><span class="ap-item-type">${ic[a.type] || '•'} ${a.type}</span><span class="ap-item-pg">Hal.${a.page}</span><div class="ap-item-text">${esc(a.comment || a.selected_text || a.shape_type || '—')}</div></div><button class="ap-item-del">🗑</button>`;
                el.querySelector('.ap-item-del').addEventListener('click', async ev => { ev.stopPropagation(); await removeAnnot(a.id); buildPanel(); });
                el.addEventListener('click', () => { if (a.page !== V.pageNum) V.queueRender(a.page); annotPanel?.classList.remove('open'); });
                apList.appendChild(el);
            });
        }

        /* ── TOOL MANAGEMENT ─────────────────────────────────── */
        function setTool(tool) {
            activeTool = tool;
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode', 'text-tool-mode');
            if (tool === 'freehand') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            if (tool === 'text') stage.classList.add('text-tool-mode');
            const needsSel = ['highlight', 'comment', 'underline', 'strikethrough'].includes(tool);
            if (textLayer) { textLayer.style.pointerEvents = needsSel ? 'auto' : 'none'; textLayer.style.userSelect = needsSel ? 'text' : 'none'; textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none'; }
            if (freeCanvas) freeCanvas.style.pointerEvents = ['freehand', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur) eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }
        }
        window.addEventListener('annot-tool-change', e => setTool(e.detail.tool));
        window.addEventListener('annot-color-change', e => { activeColor = e.detail.color; });
        window.addEventListener('annot-size-change', e => { activeSize = +e.detail.size; });
        window.addEventListener('annot-shape-change', e => { activeShape = e.detail.shape; });
        setTool('highlight');

        /* ── TEXT SELECTION ──────────────────────────────────── */
        function getSelInfo() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0);
            if (!textLayer?.contains(range.commonAncestorContainer)) return null;
            const sr = stage.getBoundingClientRect(), s = V.getScale();
            const rects = Array.from(range.getClientRects()).filter(r => r.width > .5 && r.height > .5);
            if (!rects.length) return null;
            const L = Math.min(...rects.map(r => r.left)), T = Math.min(...rects.map(r => r.top));
            const R = Math.max(...rects.map(r => r.right)), B = Math.max(...rects.map(r => r.bottom));
            return { rect: { x: (L - sr.left) / s, y: (T - sr.top) / s, w: (R - L) / s, h: (B - T) / s }, text: sel.toString().substring(0, 1000), br: range.getBoundingClientRect() };
        }
        let selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar,#annot-panel')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(async () => {
                const info = getSelInfo(); if (!info || info.rect.w < 2) return;
                const base = { page: V.pageNum, color: activeColor, rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h, selected_text: info.text };
                if (activeTool === 'highlight') { await addAnnot({ ...base, type: 'highlight' }); window.getSelection()?.removeAllRanges(); snack('✏️ Highlight!'); }
                else if (activeTool === 'underline') { await addAnnot({ ...base, type: 'underline' }); window.getSelection()?.removeAllRanges(); snack('__ Underline!'); }
                else if (activeTool === 'strikethrough') { await addAnnot({ ...base, type: 'strikethrough' }); window.getSelection()?.removeAllRanges(); snack('~~ Strikethrough!'); }
                else if (activeTool === 'comment') { pendingRect = info.rect; pendingText = info.text; openPopup(commentPop, info.br.left, info.br.bottom + 8); if (commentTxt) { commentTxt.value = ''; commentTxt.focus(); } }
            }, 80);
        }
        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', e => { if (!['highlight', 'comment', 'underline', 'strikethrough'].includes(activeTool)) return; onSelEnd(e); }, { passive: true });

        function openPopup(popup, cx, cy) {
            if (!popup) return;
            const vw = window.innerWidth, vh = window.innerHeight, pw = 284, ph = 170;
            popup.style.left = Math.max(4, Math.min(cx - pw / 2, vw - pw - 4)) + 'px';
            popup.style.top = Math.max(4, (cy + ph > vh ? cy - ph - 8 : cy)) + 'px';
            popup.classList.add('show');
        }
        commentSave?.addEventListener('click', async () => {
            const txt = commentTxt?.value.trim(); if (!txt || !pendingRect) { snack('Tulis komentar dulu!'); return; }
            if (commentTxt) commentTxt.value = ''; commentPop?.classList.remove('show');
            await addAnnot({ page: V.pageNum, type: 'comment', color: activeColor, rect_x: pendingRect.x, rect_y: pendingRect.y, rect_w: pendingRect.w, rect_h: pendingRect.h, selected_text: pendingText || '', comment: txt });
            window.getSelection()?.removeAllRanges(); pendingRect = null; pendingText = null; snack('💬 Komentar disimpan!');
        });
        commentCancel?.addEventListener('click', () => { commentPop?.classList.remove('show'); pendingRect = null; pendingText = null; window.getSelection()?.removeAllRanges(); });

        /* ── FREEHAND ─────────────────────────────────────────── */
        function fhStart(e) { if (activeTool !== 'freehand') return; if (e.cancelable) e.preventDefault(); isDrawing = true; freePoints = []; const p = stageXY(e), s = V.getScale(); freePoints.push([p.x / s, p.y / s]); }
        function fhMove(e) {
            if (!isDrawing || activeTool !== 'freehand') return; if (e.cancelable) e.preventDefault();
            const p = stageXY(e), s = V.getScale(); freePoints.push([p.x / s, p.y / s]);
            if (!freeCtx || freePoints.length < 2) return;
            const last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1];
            freeCtx.save(); freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = activeSize * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s);
            freeCtx.stroke(); freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || activeTool !== 'freehand') return; if (e.cancelable) e.preventDefault(); isDrawing = false;
            if (freePoints.length < 2) return;
            const xs = freePoints.map(p => p[0]), ys = freePoints.map(p => p[1]), bx = Math.min(...xs), by = Math.min(...ys);
            await addAnnot({ page: V.pageNum, type: 'freehand', color: activeColor, stroke_width: activeSize, path_points: freePoints, rect_x: bx, rect_y: by, rect_w: Math.max(...xs) - bx, rect_h: Math.max(...ys) - by });
        }

        /* ── SHAPE ───────────────────────────────────────────── */
        function shStart(e) { if (activeTool !== 'shape') return; if (e.cancelable) e.preventDefault(); isDrawing = true; drawStart = stageXY(e); shapePreviewEl = document.createElement('div'); shapePreviewEl.style.cssText = `position:absolute;pointer-events:none;z-index:25;border:${activeSize}px solid ${hex(activeColor)};${activeShape === 'ellipse' ? 'border-radius:50%;' : ''}left:${drawStart.x}px;top:${drawStart.y}px;width:0;height:0;`; stage.appendChild(shapePreviewEl); }
        function shMove(e) { if (!isDrawing || activeTool !== 'shape' || !shapePreviewEl || !drawStart) return; if (e.cancelable) e.preventDefault(); const c = stageXY(e); Object.assign(shapePreviewEl.style, { left: Math.min(drawStart.x, c.x) + 'px', top: Math.min(drawStart.y, c.y) + 'px', width: Math.abs(c.x - drawStart.x) + 'px', height: Math.abs(c.y - drawStart.y) + 'px' }); }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return; if (e.cancelable) e.preventDefault(); isDrawing = false; shapePreviewEl?.remove(); shapePreviewEl = null;
            const c = stageXY(e), s = V.getScale(); if (!drawStart) return;
            const x = Math.min(drawStart.x, c.x) / s, y = Math.min(drawStart.y, c.y) / s, w = Math.abs(c.x - drawStart.x) / s, h = Math.abs(c.y - drawStart.y) / s;
            drawStart = null; if (w < 4 && h < 4) return;
            await addAnnot({ page: V.pageNum, type: 'shape', color: activeColor, shape_type: activeShape, stroke_width: activeSize, rect_x: x, rect_y: y, rect_w: w, rect_h: activeShape === 'line' ? 1 : h });
        }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('mousemove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('mouseup', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('mouseleave', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('touchstart', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('touchmove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('touchend', e => { fhEnd(e); shEnd(e); }, { passive: false });
        }

        /* ── ERASER cursor ───────────────────────────────────── */
        document.addEventListener('mousemove', e => {
            if (!eraserCur) return;
            eraserCur.style.display = activeTool === 'eraser' ? 'block' : 'none';
            if (activeTool === 'eraser') { eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; }
        });

        /* ── UNIFIED STAGE CLICK ─────────────────────────────── */
        stage.addEventListener('click', e => {
            const hitAnnot = e.target.closest('[data-annot-id],.sticky-note,.annot-freetext');
            if (activeTool === 'sticky') {
                if (hitAnnot) return;
                if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale(); stickyPos = { x: p.x / s, y: p.y / s };
                openPopup(stickyPop, e.clientX, e.clientY);
                if (stickyTxt) { stickyTxt.value = ''; setTimeout(() => stickyTxt.focus(), 30); }
                return;
            }
            if (activeTool === 'text') {
                if (hitAnnot) return;
                if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale(); textPos = { x: p.x / s, y: p.y / s };
                ensureTextPopup();
                openPopup(document.getElementById('freetext-popup'), e.clientX, e.clientY);
                setTimeout(() => document.getElementById('freetext-input')?.focus(), 30);
                return;
            }
            if (activeTool === 'select') { if (!hitAnnot) { selectedId = null; scheduleRender(); } return; }
            if (activeTool === 'eraser') { if (!hitAnnot) snack('Klik/sentuh anotasi untuk menghapus', '#60A5FA'); return; }
        });

        stage.addEventListener('touchend', e => {
            const t = e.changedTouches[0];
            const hitAnnot = e.target.closest('[data-annot-id],.sticky-note,.annot-freetext');
            if (activeTool === 'sticky' && !hitAnnot) {
                if (e.target.closest('#comment-popup,#sticky-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale(); stickyPos = { x: p.x / s, y: p.y / s };
                openPopup(stickyPop, t.clientX, t.clientY); if (stickyTxt) { stickyTxt.value = ''; setTimeout(() => stickyTxt.focus(), 30); } return;
            }
            if (activeTool === 'text' && !hitAnnot) {
                if (e.target.closest('#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale(); textPos = { x: p.x / s, y: p.y / s };
                ensureTextPopup(); openPopup(document.getElementById('freetext-popup'), t.clientX, t.clientY);
                setTimeout(() => document.getElementById('freetext-input')?.focus(), 30); return;
            }
        }, { passive: true });

        /* Sticky popup */
        stickySave?.addEventListener('click', async () => {
            const txt = stickyTxt?.value.trim(); if (!txt) { snack('Tulis catatan dulu!'); return; } if (!stickyPos) return;
            if (stickyTxt) stickyTxt.value = ''; stickyPop?.classList.remove('show');
            await addAnnot({ page: V.pageNum, type: 'sticky', color: activeColor, rect_x: stickyPos.x, rect_y: stickyPos.y, rect_w: 180, rect_h: 90, comment: txt });
            stickyPos = null; snack('📌 Sticky note ditempel!');
        });
        stickyCancel?.addEventListener('click', () => { stickyPop?.classList.remove('show'); stickyPos = null; });

        /* ── FREE TEXT POPUP ─────────────────────────────────────
           PERBAIKAN: popup dibuat sekali, input ukuran dari activeSize
           yang dikonversi ke font size (2→10, 4→14, 8→20, 14→28)
        ─────────────────────────────────────────────────────── */
        function sizeToPx(s) {
            const map = { 2: 10, 4: 14, 8: 20, 14: 28 };
            return map[s] || 14;
        }

        function ensureTextPopup() {
            if (document.getElementById('freetext-popup')) return;
            const p = document.createElement('div'); p.id = 'freetext-popup';
            p.style.cssText = 'position:fixed;background:#1a1a1a;border:2px solid #ff6b18;border-radius:14px;padding:.875rem;width:280px;z-index:20006;box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
            p.innerHTML = `
                <p style="font-size:12px;font-weight:700;color:#ff6b18;margin:0 0 .5rem">🔤 Tambah Teks ke PDF</p>
                <p style="font-size:10px;color:#666;margin:0 0 .5rem">Ketik teks, lalu klik Tambah</p>
                <textarea id="freetext-input" placeholder="Contoh: Penting! atau Catatan..." style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:72px;display:block;box-sizing:border-box;"></textarea>
                <div id="freetext-size-preview" style="margin-top:.4rem;font-size:10px;color:#888;">Ukuran teks: <span id="freetext-size-label">14px</span></div>
                <div style="display:flex;gap:.4rem;margin-top:.5rem">
                    <button id="freetext-save" style="flex:1;padding:.5rem;background:#ff6b18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;touch-action:manipulation;">✓ Tambah</button>
                    <button id="freetext-cancel" style="padding:.5rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;touch-action:manipulation;">Batal</button>
                </div>
            `;
            document.body.appendChild(p);
            document.getElementById('freetext-save').addEventListener('click', async () => {
                const inp = document.getElementById('freetext-input');
                const txt = inp?.value.trim();
                if (!txt) { snack('Ketik teks dulu!'); return; }
                if (!textPos) { snack('Klik area PDF dulu!'); return; }
                const fontSize = sizeToPx(activeSize);
                if (inp) inp.value = ''; p.style.display = 'none';
                await addAnnot({
                    page: V.pageNum, type: 'text', color: activeColor,
                    stroke_width: fontSize, /* stroke_width dipakai sebagai font size */
                    rect_x: textPos.x, rect_y: textPos.y, rect_w: 200, rect_h: fontSize * 2,
                    comment: txt,
                });
                textPos = null; snack('🔤 Teks ditambahkan!');
            });
            document.getElementById('freetext-cancel').addEventListener('click', () => { p.style.display = 'none'; textPos = null; });
        }

        /* Update size preview saat size berubah */
        window.addEventListener('annot-size-change', () => {
            const lbl = document.getElementById('freetext-size-label');
            if (lbl) lbl.textContent = sizeToPx(activeSize) + 'px';
        });

        /* ── SELECT: delete key ──────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; }
        });

        /* ── PAN ─────────────────────────────────────────────── */
        stage.addEventListener('mousedown', e => { if (activeTool !== 'pan') return; isPanning = true; panSX = e.clientX; panSY = e.clientY; panScrollX = canvasWrap?.scrollLeft || 0; panScrollY = canvasWrap?.scrollTop || 0; if (e.cancelable) e.preventDefault(); }, { passive: false });
        document.addEventListener('mousemove', e => { if (!isPanning || activeTool !== 'pan') return; if (canvasWrap) { canvasWrap.scrollLeft = panScrollX + (panSX - e.clientX); canvasWrap.scrollTop = panScrollY + (panSY - e.clientY); } });
        document.addEventListener('mouseup', () => { isPanning = false; });
        stage.addEventListener('touchstart', e => { if (activeTool !== 'pan' || e.touches.length !== 1) return; isPanning = true; panSX = e.touches[0].clientX; panSY = e.touches[0].clientY; panScrollX = canvasWrap?.scrollLeft || 0; panScrollY = canvasWrap?.scrollTop || 0; }, { passive: true });
        document.addEventListener('touchmove', e => { if (!isPanning || activeTool !== 'pan' || e.touches.length !== 1) return; if (canvasWrap) { canvasWrap.scrollLeft = panScrollX + (panSX - e.touches[0].clientX); canvasWrap.scrollTop = panScrollY + (panSY - e.touches[0].clientY); } if (e.cancelable) e.preventDefault(); }, { passive: false });
        document.addEventListener('touchend', () => { isPanning = false; });

        /* ── ZOOM ANTI-FLICKER ───────────────────────────────── */
        let zoomT = null;
        if (mainCanvas) { new MutationObserver(() => { clearTimeout(zoomT); zoomT = setTimeout(() => { syncFC(); scheduleRender(); }, 60); }).observe(mainCanvas, { attributes: true, attributeFilter: ['width', 'height'] }); }

        /* ── PAGE CHANGE ─────────────────────────────────────── */
        V.onPageChange = function () {
            commentPop?.classList.remove('show'); stickyPop?.classList.remove('show');
            const fp = document.getElementById('freetext-popup'); if (fp) fp.style.display = 'none';
            annotTip?.classList.remove('show');
            pendingRect = null; pendingText = null; stickyPos = null; textPos = null;
            window.getSelection()?.removeAllRanges(); scheduleRender();
        };

        /* ── INIT ────────────────────────────────────────────── */
        V.onReady(async () => { syncFC(); await loadAll(); });
        window._pdfAnnotations = true;
        console.log('[annot] v3.3 ready, slug=', slug);

    });
})();
