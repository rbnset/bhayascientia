/**
 * pdf-annotations.js — v3.2
 * public/js/pdf-annotations.js
 *
 * FIXES v3.2:
 * - Text tool: single unified click handler di stage (tidak konflik)
 * - Redo: logic arah stack diperbaiki
 * - Sticky note: posisi drag disimpan ke API (PATCH)
 * - FAB: tidak ada handler di sini, biarkan blade/pdf-viewer.js
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

        /* ── CONFIG ────────────────────────────────────────────── */
        const slug = window.PDF_CONFIG?.slug || 'unknown';
        const API = `/api/annotations/${slug}`;

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

        let isDrawing = false;
        let drawStart = null;
        let freePoints = [];
        let shapePreviewEl = null;
        let pendingRect = null;
        let pendingText = null;
        let stickyPos = null;
        let textPos = null;
        let selectedId = null;
        let isPanning = false;
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
        const syncTxt2 = document.getElementById('annot-sync-text');
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
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }
        function syncFC() {
            if (!freeCanvas) return;
            const w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) { freeCanvas.width = w; freeCanvas.height = h; }
            freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
        }

        /* ── SYNC INDICATOR ────────────────────────────────────── */
        let syncT = null;
        function showSync(msg, ok = false) {
            if (!syncEl) return;
            if (syncTxt2) syncTxt2.textContent = msg;
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
            showSync('Menyimpan...');
            try {
                const r = await fetch(API, { method: 'POST', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(payload) });
                const j = await r.json();
                if (!r.ok) { console.error('[annot] 422:', j); showSync('Gagal: ' + (j.message || r.status)); return null; }
                showSync('Tersimpan ✓', true);
                return j.data || null;
            } catch (e) { console.error('[annot] save:', e); showSync('Error jaringan'); return null; }
        }

        async function apiPatch(id, payload) {
            /* Untuk update posisi sticky note setelah drag */
            try {
                const r = await fetch(`${API}/${id}`, { method: 'PUT', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(payload) });
                if (!r.ok) { const j = await r.json(); console.error('[annot] patch:', j); }
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
            scheduleRender();
            updateBadge();
            updateUndoRedo();
        }

        /* ── RENDER ─────────────────────────────────────────────── */
        function scheduleRender() {
            if (renderPending) return;
            renderPending = true;
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
                    case 'highlight': rHL(a, scale); break;
                    case 'comment': rHL(a, scale); break;
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

        function rHL(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.annotId = String(a.id);
            const sel = selectedId == a.id;
            el.style.cssText = `position:absolute;left:${a.rect.x * scale}px;top:${a.rect.y * scale}px;width:${a.rect.w * scale}px;height:${a.rect.h * scale}px;background:${hex(a.color)};opacity:${sel ? .75 : .38};border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px solid #FF6B18' : 'none'};transition:opacity .12s;`;
            if (a.comment) {
                const dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachEv(el, a);
            annotLayer.appendChild(el);
        }

        function rUL(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.annotId = String(a.id);
            const thick = Math.max(1.5, 2 * scale);
            el.style.cssText = `position:absolute;left:${a.rect.x * scale}px;top:${(a.rect.y + a.rect.h) * scale - thick}px;width:${a.rect.w * scale}px;height:${thick}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachEv(el, a);
            annotLayer.appendChild(el);
        }

        function rST(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.annotId = String(a.id);
            const thick = Math.max(1.5, 2 * scale);
            el.style.cssText = `position:absolute;left:${a.rect.x * scale}px;top:${(a.rect.y + a.rect.h / 2) * scale - thick / 2}px;width:${a.rect.w * scale}px;height:${thick}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachEv(el, a);
            annotLayer.appendChild(el);
        }

        function rFH(a, scale) {
            if (!a.path_points?.length || !freeCtx) return;
            const pts = a.path_points;
            freeCtx.save();
            freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width || 2) * scale;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath();
            freeCtx.moveTo(pts[0][0] * scale, pts[0][1] * scale);
            for (let i = 1; i < pts.length; i++)freeCtx.lineTo(pts[i][0] * scale, pts[i][1] * scale);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
                const hit = document.createElement('div');
                hit.dataset.annotId = String(a.id);
                hit.style.cssText = `position:absolute;left:${(a.rect.x - 8) * scale}px;top:${(a.rect.y - 8) * scale}px;width:${(a.rect.w + 16) * scale}px;height:${(a.rect.h + 16) * scale}px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;`;
                attachEv(hit, a);
                annotLayer.appendChild(hit);
            }
        }

        function rSH(a, scale) {
            if (!a.rect) return;
            const x = a.rect.x * scale, y = a.rect.y * scale;
            const w = Math.max(4, a.rect.w * scale), h = Math.max(4, a.rect.h * scale);
            const sw = Math.max(1, (a.stroke_width || 2) * scale), col = hex(a.color);
            const sel = selectedId == a.id;
            const wrap = document.createElement('div');
            wrap.dataset.annotId = String(a.id);
            wrap.style.cssText = `position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px dashed #FF6B18' : 'none'};`;
            const st = a.shape_type || 'rect';
            let svg = '';
            if (st === 'rect') svg = `<rect x="${sw / 2}" y="${sw / 2}" width="${Math.max(1, w - sw)}" height="${Math.max(1, h - sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'ellipse') svg = `<ellipse cx="${w / 2}" cy="${h / 2}" rx="${Math.max(1, w / 2 - sw / 2)}" ry="${Math.max(1, h / 2 - sw / 2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'arrow') { const hh = Math.max(4, h * .35), hx = Math.max(sw * 3, w * .25); svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - hx + sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w - sw / 2},${h / 2} ${w - hx},${h / 2 - hh} ${w - hx},${h / 2 + hh}" fill="${col}"/>`; }
            else if (st === 'line') svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
            wrap.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
            attachEv(wrap, a);
            annotLayer.appendChild(wrap);
        }

        function rSticky(a, scale) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'sticky-note';
            note.dataset.annotId = String(a.id);
            note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * scale) + 'px';
            note.style.top = (a.rect.y * scale) + 'px';
            note.innerHTML = `<div class="sn-header"><span>📌</span><button class="sn-del">×</button></div><div class="sn-body">${esc(a.comment)}</div>`;
            note.querySelector('.sn-del').addEventListener('click', ev => { ev.stopPropagation(); removeAnnot(a.id); });
            note.addEventListener('click', ev => {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                if (activeTool === 'select') { ev.stopPropagation(); selectedId = String(a.id); scheduleRender(); return; }
                ev.stopPropagation();
                showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, scale);
            stage.appendChild(note);
        }

        function rText(a, scale) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.className = 'annot-freetext';
            el.dataset.annotId = String(a.id);
            el.style.cssText = `position:absolute;left:${a.rect.x * scale}px;top:${a.rect.y * scale}px;font-size:${(a.stroke_width || 14) * scale}px;color:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:${200 * scale}px;font-family:sans-serif;font-weight:600;text-shadow:0 1px 3px rgba(0,0,0,.4);`;
            el.textContent = a.comment || '';
            el.addEventListener('click', ev => {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            stage.appendChild(el);
        }

        /* ── attachEv: unified click+touch handler ──────────────── */
        function attachEv(el, a) {
            el.addEventListener('click', ev => {
                ev.stopPropagation();
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, ev.clientX, ev.clientY);
            });
            el.addEventListener('touchend', ev => {
                ev.stopPropagation();
                if (ev.cancelable) ev.preventDefault();
                const t = ev.changedTouches[0];
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, t.clientX, t.clientY);
            }, { passive: false });
        }

        /* ── DRAGGABLE (sticky) + simpan posisi ke API ─────────── */
        function makeDraggable(el, annotData, scale) {
            let ox = 0, oy = 0, dragging = false, moved = false;
            function onDown(e) {
                if (e.target.classList.contains('sn-del') || e.target.classList.contains('sn-body')) return;
                dragging = true; moved = false;
                const s = e.touches?.[0] ?? e;
                ox = s.clientX - el.offsetLeft; oy = s.clientY - el.offsetTop;
                el.style.zIndex = '20';
                e.stopPropagation();
                if (e.cancelable) e.preventDefault();
            }
            function onMove(e) {
                if (!dragging) return; moved = true;
                const s = e.touches?.[0] ?? e;
                el.style.left = (s.clientX - ox) + 'px'; el.style.top = (s.clientY - oy) + 'px';
                if (e.cancelable) e.preventDefault();
            }
            async function onUp() {
                if (!dragging) return; dragging = false; el.style.zIndex = '9';
                if (!moved) return;
                const newX = parseFloat(el.style.left) / scale;
                const newY = parseFloat(el.style.top) / scale;
                /* Update posisi di store lokal */
                const idx = annots.findIndex(a => String(a.id) === String(annotData.id));
                if (idx >= 0 && annots[idx].rect) {
                    annots[idx].rect.x = newX;
                    annots[idx].rect.y = newY;
                }
                /* Simpan posisi baru ke server via PATCH/PUT */
                await apiPatch(annotData.id, {
                    rect_x: newX, rect_y: newY,
                    rect_w: annotData.rect?.w || 180,
                    rect_h: annotData.rect?.h || 90,
                });
            }
            el.addEventListener('mousedown', onDown, { passive: false });
            el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchend', onUp);
        }

        /* ── TOOLTIP ────────────────────────────────────────────── */
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

        if (tipClose) tipClose.addEventListener('click', () => annotTip?.classList.remove('show'));
        if (tipDel) tipDel.addEventListener('click', async () => {
            const id = tipTxt?.dataset.annotId;
            annotTip?.classList.remove('show');
            if (id) await removeAnnot(id);
        });
        document.addEventListener('click', e => {
            if (annotTip && !annotTip.contains(e.target) && !e.target.closest('[data-annot-id],.sticky-note,.annot-freetext'))
                annotTip.classList.remove('show');
        });

        /* ── ADD / REMOVE ───────────────────────────────────────── */
        async function addAnnot(payload) {
            const saved = await apiSave(payload);
            if (!saved) return null;
            annots.push(saved);
            undoStack.push({ action: 'add', data: saved });
            redoStack = [];
            updateUndoRedo(); scheduleRender();
            return saved;
        }

        async function removeAnnot(id) {
            const a = annots.find(x => String(x.id) === String(id));
            if (!a) return;
            await apiDel(a.id);
            annots = annots.filter(x => String(x.id) !== String(id));
            if (selectedId === String(id)) selectedId = null;
            undoStack.push({ action: 'del', data: a });
            redoStack = [];
            updateUndoRedo(); scheduleRender();
            snack('🗑 Anotasi dihapus');
        }

        /* ── UNDO / REDO ─────────────────────────────────────────
         * PERBAIKAN: redo stack seharusnya menyimpan operasi KEBALIKAN
         * dari undo. Jika undo "add" → redo harus bisa "add lagi".
         * Jika undo "del" → redo harus bisa "del lagi".
         * Caranya: setelah undo, push ke redoStack dengan action SAMA
         * (bukan terbalik), tapi data yang sudah di-resolve.
         ─────────────────────────────────────────────────────── */
        function updateUndoRedo() {
            if (undoBtn) undoBtn.disabled = !undoStack.length;
            if (redoBtn) redoBtn.disabled = !redoStack.length;
        }

        function payload(a) {
            return {
                page: a.page, type: a.type, color: a.color,
                rect_x: a.rect?.x, rect_y: a.rect?.y, rect_w: a.rect?.w, rect_h: a.rect?.h,
                selected_text: a.selected_text, comment: a.comment,
                path_points: a.path_points, shape_type: a.shape_type,
                stroke_width: a.stroke_width, fill_opacity: a.fill_opacity
            };
        }

        async function doUndo() {
            if (!undoStack.length) return;
            const op = undoStack.pop();
            if (op.action === 'add') {
                /* Undo add → hapus dari server & local */
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(a.id));
                    /* Push ke redo: "bisa di-add kembali" */
                    redoStack.push({ action: 'readd', data: a });
                }
            } else if (op.action === 'del') {
                /* Undo del → tambah kembali ke server */
                const saved = await apiSave(payload(op.data));
                if (saved) {
                    annots.push(saved);
                    /* Push ke redo: "bisa di-del kembali" */
                    redoStack.push({ action: 'redel', data: saved });
                }
            }
            updateUndoRedo(); scheduleRender();
        }

        async function doRedo() {
            if (!redoStack.length) return;
            const op = redoStack.pop();
            if (op.action === 'readd') {
                /* Redo: tambah kembali yang tadi di-undo */
                const saved = await apiSave(payload(op.data));
                if (saved) {
                    annots.push(saved);
                    undoStack.push({ action: 'add', data: saved });
                }
            } else if (op.action === 'redel') {
                /* Redo: hapus lagi yang tadi di-restore */
                const a = annots.find(x => String(x.id) === String(op.data.id));
                if (a) {
                    await apiDel(a.id);
                    annots = annots.filter(x => String(x.id) !== String(a.id));
                    undoStack.push({ action: 'del', data: a });
                }
            }
            updateUndoRedo(); scheduleRender();
        }

        if (undoBtn) undoBtn.addEventListener('click', doUndo);
        if (redoBtn) redoBtn.addEventListener('click', doRedo);
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); }
        });

        /* ── BADGE & PANEL ──────────────────────────────────────── */
        function updateBadge() {
            const n = annots.length;
            if (panelBadge) { panelBadge.textContent = n > 99 ? '99+' : String(n); panelBadge.classList.toggle('show', n > 0); }
            window.dispatchEvent(new CustomEvent('annot-count-change', { detail: { count: n } }));
        }

        if (panelBtn) panelBtn.addEventListener('click', e => { e.stopPropagation(); annotPanel?.classList.toggle('open'); buildPanel(); });
        if (apClose) apClose.addEventListener('click', () => annotPanel?.classList.remove('open'));
        if (apClear) apClear.addEventListener('click', async () => {
            if (!confirm(`Hapus semua anotasi di halaman ${V.pageNum}?`)) return;
            await apiDelPage(V.pageNum);
            annots = annots.filter(a => a.page !== V.pageNum);
            undoStack = []; redoStack = [];
            updateUndoRedo(); scheduleRender(); buildPanel();
            snack(`🗑 Halaman ${V.pageNum} dibersihkan`);
        });

        function buildPanel() {
            if (!apList) return;
            if (!annots.length) { apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.</div>'; return; }
            apList.innerHTML = '';
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            [...annots].sort((a, b) => a.page - b.page || a.id - b.id).forEach(a => {
                const el = document.createElement('div');
                el.className = 'ap-item';
                el.innerHTML = `<div class="ap-dot" style="background:${hex(a.color)}"></div><div class="ap-item-body"><span class="ap-item-type">${ic[a.type] || '•'} ${a.type}</span><span class="ap-item-pg">Hal.${a.page}</span><div class="ap-item-text">${esc(a.comment || a.selected_text || a.shape_type || '—')}</div></div><button class="ap-item-del">🗑</button>`;
                el.querySelector('.ap-item-del').addEventListener('click', async ev => { ev.stopPropagation(); await removeAnnot(a.id); buildPanel(); });
                el.addEventListener('click', () => { if (a.page !== V.pageNum) V.queueRender(a.page); annotPanel?.classList.remove('open'); });
                apList.appendChild(el);
            });
        }

        /* ── TOOL MANAGEMENT ────────────────────────────────────── */
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
            if (textLayer) {
                textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
                textLayer.style.userSelect = needsSel ? 'text' : 'none';
                textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
            }
            if (freeCanvas) freeCanvas.style.pointerEvents = ['freehand', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur) eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }
        }

        window.addEventListener('annot-tool-change', e => setTool(e.detail.tool));
        window.addEventListener('annot-color-change', e => { activeColor = e.detail.color; });
        window.addEventListener('annot-size-change', e => { activeSize = +e.detail.size; });
        window.addEventListener('annot-shape-change', e => { activeShape = e.detail.shape; });
        setTool('highlight');

        /* ── TEXT SELECTION → highlight/underline/strike/comment ── */
        function getSelInfo() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0);
            if (!textLayer?.contains(range.commonAncestorContainer)) return null;
            const sr = stage.getBoundingClientRect(), scale = V.getScale();
            const rects = Array.from(range.getClientRects()).filter(r => r.width > .5 && r.height > .5);
            if (!rects.length) return null;
            const L = Math.min(...rects.map(r => r.left)), T = Math.min(...rects.map(r => r.top));
            const R = Math.max(...rects.map(r => r.right)), B = Math.max(...rects.map(r => r.bottom));
            return {
                rect: { x: (L - sr.left) / scale, y: (T - sr.top) / scale, w: (R - L) / scale, h: (B - T) / scale },
                text: sel.toString().substring(0, 1000),
                br: range.getBoundingClientRect(),
            };
        }

        let selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar,#annot-panel')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(async () => {
                const info = getSelInfo();
                if (!info || info.rect.w < 2) return;
                const base = { page: V.pageNum, color: activeColor, rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h, selected_text: info.text };
                if (activeTool === 'highlight') {
                    await addAnnot({ ...base, type: 'highlight' });
                    window.getSelection()?.removeAllRanges(); snack('✏️ Highlight!');
                } else if (activeTool === 'underline') {
                    await addAnnot({ ...base, type: 'underline' });
                    window.getSelection()?.removeAllRanges(); snack('__ Underline!');
                } else if (activeTool === 'strikethrough') {
                    await addAnnot({ ...base, type: 'strikethrough' });
                    window.getSelection()?.removeAllRanges(); snack('~~ Strikethrough!');
                } else if (activeTool === 'comment') {
                    pendingRect = info.rect; pendingText = info.text;
                    openPopup(commentPop, info.br.left, info.br.bottom + 8);
                    if (commentTxt) { commentTxt.value = ''; commentTxt.focus(); }
                }
            }, 80);
        }
        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', e => {
            if (!['highlight', 'comment', 'underline', 'strikethrough'].includes(activeTool)) return;
            onSelEnd(e);
        }, { passive: true });

        function openPopup(popup, cx, cy) {
            if (!popup) return;
            const vw = window.innerWidth, vh = window.innerHeight, pw = 284, ph = 170;
            popup.style.left = Math.max(4, Math.min(cx - pw / 2, vw - pw - 4)) + 'px';
            popup.style.top = Math.max(4, (cy + ph > vh ? cy - ph - 8 : cy)) + 'px';
            popup.classList.add('show');
        }

        if (commentSave) commentSave.addEventListener('click', async () => {
            const txt = commentTxt?.value.trim();
            if (!txt || !pendingRect) { snack('Tulis komentar dulu!'); return; }
            if (commentTxt) commentTxt.value = '';
            commentPop?.classList.remove('show');
            await addAnnot({ page: V.pageNum, type: 'comment', color: activeColor, rect_x: pendingRect.x, rect_y: pendingRect.y, rect_w: pendingRect.w, rect_h: pendingRect.h, selected_text: pendingText || '', comment: txt });
            window.getSelection()?.removeAllRanges();
            pendingRect = null; pendingText = null; snack('💬 Komentar disimpan!');
        });
        if (commentCancel) commentCancel.addEventListener('click', () => {
            commentPop?.classList.remove('show'); pendingRect = null; pendingText = null;
            window.getSelection()?.removeAllRanges();
        });

        /* ── FREEHAND ───────────────────────────────────────────── */
        function fhStart(e) {
            if (activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true; freePoints = [];
            const p = stageXY(e), s = V.getScale();
            freePoints.push([p.x / s, p.y / s]);
        }
        function fhMove(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            const p = stageXY(e), s = V.getScale();
            freePoints.push([p.x / s, p.y / s]);
            if (!freeCtx || freePoints.length < 2) return;
            const last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1];
            freeCtx.save(); freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = activeSize * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s);
            freeCtx.stroke(); freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || activeTool !== 'freehand') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = false;
            if (freePoints.length < 2) return;
            const xs = freePoints.map(p => p[0]), ys = freePoints.map(p => p[1]);
            const bx = Math.min(...xs), by = Math.min(...ys);
            await addAnnot({ page: V.pageNum, type: 'freehand', color: activeColor, stroke_width: activeSize, path_points: freePoints, rect_x: bx, rect_y: by, rect_w: Math.max(...xs) - bx, rect_h: Math.max(...ys) - by });
        }

        /* ── SHAPE ──────────────────────────────────────────────── */
        function shStart(e) {
            if (activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true; drawStart = stageXY(e);
            shapePreviewEl = document.createElement('div');
            shapePreviewEl.style.cssText = `position:absolute;pointer-events:none;z-index:25;border:${activeSize}px solid ${hex(activeColor)};${activeShape === 'ellipse' ? 'border-radius:50%;' : ''}left:${drawStart.x}px;top:${drawStart.y}px;width:0;height:0;`;
            stage.appendChild(shapePreviewEl);
        }
        function shMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !shapePreviewEl || !drawStart) return;
            if (e.cancelable) e.preventDefault();
            const c = stageXY(e);
            Object.assign(shapePreviewEl.style, { left: Math.min(drawStart.x, c.x) + 'px', top: Math.min(drawStart.y, c.y) + 'px', width: Math.abs(c.x - drawStart.x) + 'px', height: Math.abs(c.y - drawStart.y) + 'px' });
        }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = false; shapePreviewEl?.remove(); shapePreviewEl = null;
            const c = stageXY(e), s = V.getScale();
            if (!drawStart) return;
            const x = Math.min(drawStart.x, c.x) / s, y = Math.min(drawStart.y, c.y) / s;
            const w = Math.abs(c.x - drawStart.x) / s, h = Math.abs(c.y - drawStart.y) / s;
            drawStart = null;
            if (w < 4 && h < 4) return;
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

        /* ── ERASER cursor ──────────────────────────────────────── */
        document.addEventListener('mousemove', e => {
            if (!eraserCur) return;
            if (activeTool === 'eraser') { eraserCur.style.display = 'block'; eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; }
            else eraserCur.style.display = 'none';
        });

        /* ── UNIFIED STAGE CLICK ─────────────────────────────────
         * KUNCI: satu handler untuk sticky, text, select-deselect, eraser hint
         * Tidak konflik karena pakai if/else if
         ─────────────────────────────────────────────────────── */
        stage.addEventListener('click', async e => {
            /* Jangan proses jika klik pada elemen anotasi (sudah dihandle attachEv) */
            const hitAnnot = e.target.closest('[data-annot-id],.sticky-note,.annot-freetext');

            if (activeTool === 'sticky') {
                if (hitAnnot) return; /* biarkan attachEv */
                if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale();
                stickyPos = { x: p.x / s, y: p.y / s };
                openPopup(stickyPop, e.clientX, e.clientY);
                if (stickyTxt) { stickyTxt.value = ''; setTimeout(() => stickyTxt.focus(), 30); }
                return;
            }

            if (activeTool === 'text') {
                if (hitAnnot) return;
                if (e.target.closest('#comment-popup,#sticky-popup,#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale();
                textPos = { x: p.x / s, y: p.y / s };
                ensureTextPopup();
                openPopup(document.getElementById('freetext-popup'), e.clientX, e.clientY);
                setTimeout(() => document.getElementById('freetext-input')?.focus(), 30);
                return;
            }

            if (activeTool === 'select') {
                if (!hitAnnot) { selectedId = null; scheduleRender(); }
                return;
            }

            if (activeTool === 'eraser') {
                if (!hitAnnot) snack('Klik/sentuh anotasi untuk menghapus', '#60A5FA');
                return;
            }
        });

        /* Touch untuk sticky & text */
        stage.addEventListener('touchend', e => {
            const t = e.changedTouches[0];
            const hitAnnot = e.target.closest('[data-annot-id],.sticky-note,.annot-freetext');

            if (activeTool === 'sticky' && !hitAnnot) {
                if (e.target.closest('#comment-popup,#sticky-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale();
                stickyPos = { x: p.x / s, y: p.y / s };
                openPopup(stickyPop, t.clientX, t.clientY);
                if (stickyTxt) { stickyTxt.value = ''; setTimeout(() => stickyTxt.focus(), 30); }
                return;
            }
            if (activeTool === 'text' && !hitAnnot) {
                if (e.target.closest('#freetext-popup,#annot-bottom-bar')) return;
                const p = stageXY(e), s = V.getScale();
                textPos = { x: p.x / s, y: p.y / s };
                ensureTextPopup();
                openPopup(document.getElementById('freetext-popup'), t.clientX, t.clientY);
                setTimeout(() => document.getElementById('freetext-input')?.focus(), 30);
                return;
            }
        }, { passive: true });

        /* ── STICKY POPUP handlers ──────────────────────────────── */
        if (stickySave) stickySave.addEventListener('click', async () => {
            const txt = stickyTxt?.value.trim();
            if (!txt) { snack('Tulis catatan dulu!'); return; }
            if (!stickyPos) return;
            if (stickyTxt) stickyTxt.value = '';
            stickyPop?.classList.remove('show');
            await addAnnot({ page: V.pageNum, type: 'sticky', color: activeColor, rect_x: stickyPos.x, rect_y: stickyPos.y, rect_w: 180, rect_h: 90, comment: txt });
            stickyPos = null; snack('📌 Sticky note ditempel!');
        });
        if (stickyCancel) stickyCancel.addEventListener('click', () => { stickyPop?.classList.remove('show'); stickyPos = null; });

        /* ── FREE TEXT POPUP ────────────────────────────────────── */
        function ensureTextPopup() {
            if (document.getElementById('freetext-popup')) return;
            const p = document.createElement('div');
            p.id = 'freetext-popup';
            p.style.cssText = 'position:fixed;background:#1a1a1a;border:2px solid #ff6b18;border-radius:14px;padding:.875rem;width:260px;z-index:20006;box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
            p.innerHTML = `<p style="font-size:12px;font-weight:700;color:#ff6b18;margin:0 0 .5rem">🔤 Tambah Teks</p><textarea id="freetext-input" placeholder="Ketik teks..." style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:60px;display:block;"></textarea><div style="display:flex;gap:.4rem;margin-top:.5rem"><button id="freetext-save" style="flex:1;padding:.45rem;background:#ff6b18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Tambah</button><button id="freetext-cancel" style="padding:.45rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button></div>`;
            document.body.appendChild(p);
            document.getElementById('freetext-save').addEventListener('click', async () => {
                const inp = document.getElementById('freetext-input');
                const txt = inp?.value.trim();
                if (!txt || !textPos) return;
                if (inp) inp.value = '';
                p.style.display = 'none';
                await addAnnot({ page: V.pageNum, type: 'text', color: activeColor, stroke_width: Math.max(activeSize, 10), rect_x: textPos.x, rect_y: textPos.y, rect_w: 200, rect_h: 30, comment: txt });
                textPos = null; snack('🔤 Teks ditambahkan!');
            });
            document.getElementById('freetext-cancel').addEventListener('click', () => { p.style.display = 'none'; textPos = null; });
        }

        /* ── SELECT: delete key ─────────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; }
        });

        /* ── PAN (hand) ─────────────────────────────────────────── */
        stage.addEventListener('mousedown', e => {
            if (activeTool !== 'pan') return;
            isPanning = true; panSX = e.clientX; panSY = e.clientY;
            panScrollX = canvasWrap?.scrollLeft || 0; panScrollY = canvasWrap?.scrollTop || 0;
            if (e.cancelable) e.preventDefault();
        }, { passive: false });
        document.addEventListener('mousemove', e => {
            if (!isPanning || activeTool !== 'pan') return;
            if (canvasWrap) { canvasWrap.scrollLeft = panScrollX + (panSX - e.clientX); canvasWrap.scrollTop = panScrollY + (panSY - e.clientY); }
        });
        document.addEventListener('mouseup', () => { isPanning = false; });
        stage.addEventListener('touchstart', e => {
            if (activeTool !== 'pan' || e.touches.length !== 1) return;
            isPanning = true; panSX = e.touches[0].clientX; panSY = e.touches[0].clientY;
            panScrollX = canvasWrap?.scrollLeft || 0; panScrollY = canvasWrap?.scrollTop || 0;
        }, { passive: true });
        document.addEventListener('touchmove', e => {
            if (!isPanning || activeTool !== 'pan' || e.touches.length !== 1) return;
            if (canvasWrap) { canvasWrap.scrollLeft = panScrollX + (panSX - e.touches[0].clientX); canvasWrap.scrollTop = panScrollY + (panSY - e.touches[0].clientY); }
            if (e.cancelable) e.preventDefault();
        }, { passive: false });
        document.addEventListener('touchend', () => { isPanning = false; });

        /* ── ZOOM ANTI-FLICKER ──────────────────────────────────── */
        let zoomTimer = null;
        if (mainCanvas) {
            new MutationObserver(() => {
                clearTimeout(zoomTimer);
                zoomTimer = setTimeout(() => { syncFC(); scheduleRender(); }, 60);
            }).observe(mainCanvas, { attributes: true, attributeFilter: ['width', 'height'] });
        }

        /* ── PAGE CHANGE ────────────────────────────────────────── */
        V.onPageChange = function () {
            commentPop?.classList.remove('show');
            stickyPop?.classList.remove('show');
            const fp = document.getElementById('freetext-popup');
            if (fp) fp.style.display = 'none';
            annotTip?.classList.remove('show');
            pendingRect = null; pendingText = null; stickyPos = null; textPos = null;
            window.getSelection()?.removeAllRanges();
            scheduleRender();
        };

        /* ── INIT ───────────────────────────────────────────────── */
        V.onReady(async () => {
            syncFC();
            await loadAll();
        });

        window._pdfAnnotations = true;
        console.log('[annot] v3.2 ready, slug=', slug);

    }); // waitForViewer
})();
