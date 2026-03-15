/**
 * public/js/review-pdf-viewer.js  v2.0
 *
 * Fixes v2:
 *  - applySearchHL dipindah sebelum doRender (hoisting fix)
 *  - Search click: close overlay + temporary highlight flash
 *  - Edit sticky/comment: klik anotasi bisa edit
 *  - Fullscreen button: label berubah jadi "✕ Keluar" saat fullscreen
 *  - Penanda terakhir dibaca (resume bookmark)
 *  - Mode baca: scroll (continuous) vs slide (per halaman)
 *  - Underline & strikethrough position fix
 *  - Sticky delete animation fix
 *  - Brush tool (freehand tebal semi-transparan)
 */
(function () {
    'use strict';

    function init() {
        if (typeof pdfjsLib === 'undefined') { setTimeout(init, 100); return; }
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        pdfjsLib.verbosity = 0;
        start();
    }

    function start() {
        const CFG = window.RPV_CONFIG;
        if (!CFG?.pdfUrl) { console.error('[RPV] config missing'); return; }

        /* ── COLORS ──────────────────────────────── */
        const COLORS = {
            yellow: '#FFD700', green: '#4ADE80', red: '#EF4444', blue: '#60A5FA',
            orange: '#FF6B18', black: '#111111', white: '#FFFFFF',
            pink: '#F472B6', purple: '#A78BFA', cyan: '#22D3EE',
        };
        const hex = n => COLORS[n] || '#FFD700';

        /* ── STATE ───────────────────────────────── */
        let pdfDoc = null, pageNum = 1, pageRendering = false, pendingPage = null;
        let baseScale = 1.0, zoomFactor = 1.0;
        const ZOOM_MIN = 0.5, ZOOM_MAX = 4.0, ZOOM_STEP = 0.25;
        const DPR = window.devicePixelRatio || 1;

        let annots = [], undoStack = [], redoStack = [];
        let activeTool = 'highlight', activeColor = 'yellow', activeSize = 2, activeShape = 'rect';
        let isDrawing = false, drawStart = null, freePoints = [], shapePreviewEl = null;
        let pendingRect = null, pendingText = null, stickyPos = null;
        let selectedId = null, isPanning = false;
        let panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;
        let renderPending = false, syncT = null, searchDebounce = null;
        let searchResults = [], searchIndex = -1, searchHighlights = [], currentQuery = '';
        let isFullscreen = false, exportInProgress = false;
        let readMode = 'slide'; // 'slide' | 'scroll'
        let lastReadPage = 1;   // penanda terakhir dibaca

        // Storage key per review
        const SK = `rpv_${CFG.reviewId}`;

        /* ── DOM ─────────────────────────────────── */
        const outerWrap = document.getElementById('rpv-outer-wrap');
        const wrap = document.getElementById('rpv-canvas-wrap');
        const stage = document.getElementById('rpv-stage');
        const mainCanvas = document.getElementById('rpv-canvas');
        const ctx = mainCanvas.getContext('2d');
        const textLayer = document.getElementById('rpv-text-layer');
        const annotLayer = document.getElementById('rpv-annotation-layer');
        const freeCanvas = document.getElementById('rpv-freehand-canvas');
        const freeCtx = freeCanvas?.getContext('2d');
        const loadingEl = document.getElementById('rpv-loading');
        const loadSub = document.getElementById('rpv-load-sub');
        const tooltip = document.getElementById('rpv-tooltip');
        const syncEl = document.getElementById('rpv-sync');
        const syncTxtEl = document.getElementById('rpv-sync-txt');
        const eraserCur = document.getElementById('rpv-eraser-cursor');
        const exportOL = document.getElementById('rpv-export-overlay');

        /* ── UTILS ───────────────────────────────── */
        function snack(msg, color = '#FF6B18') {
            const el = Object.assign(document.createElement('div'), { textContent: msg });
            el.style.cssText = `position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid ${color};color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;`;
            document.body.appendChild(el);
            setTimeout(() => { el.style.opacity = 0; setTimeout(() => el.remove(), 400); }, 2200);
        }
        function showSync(msg, ok = false) {
            if (!syncEl) return;
            if (syncTxtEl) syncTxtEl.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#FF6B18';
            syncEl.style.color = ok ? '#22c55e' : '#FF6B18';
            syncEl.classList.add('show');
            clearTimeout(syncT);
            syncT = setTimeout(() => syncEl.classList.remove('show'), ok ? 1800 : 4000);
        }
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
        function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
        function hdrs() { return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' }; }

        /* ── PERSIST LAST READ ───────────────────── */
        function saveLastRead(p) { try { localStorage.setItem(SK + '_last', p); } catch (_) { } }
        function loadLastRead() { try { return parseInt(localStorage.getItem(SK + '_last') || '1'); } catch (_) { return 1; } }

        /* ── SANITIZER ───────────────────────────── */
        const VT = ['highlight', 'underline', 'strikethrough', 'freehand', 'comment', 'sticky', 'shape'];
        const VC = ['yellow', 'green', 'red', 'blue', 'orange', 'black', 'white', 'pink', 'purple', 'cyan'];
        const VS = ['rect', 'ellipse', 'arrow', 'line'];
        function sanitize(raw) {
            const rawType = raw.type === 'brush' ? 'freehand' : raw.type;
            const type = VT.includes(rawType) ? rawType : 'highlight';
            const color = VC.includes(raw.color) ? raw.color : 'yellow';
            const p = {
                page: parseInt(raw.page) || pageNum, type, color,
                rect_x: raw.rect?.x ?? raw.rect_x ?? null, rect_y: raw.rect?.y ?? raw.rect_y ?? null,
                rect_w: raw.rect?.w ?? raw.rect_w ?? null, rect_h: raw.rect?.h ?? raw.rect_h ?? null,
                selected_text: raw.selected_text || null, comment: raw.comment || null,
                path_points: Array.isArray(raw.path_points) ? raw.path_points : null,
                shape_type: VS.includes(raw.shape_type) ? raw.shape_type : null,
                stroke_width: (typeof raw.stroke_width === 'number' && raw.stroke_width > 0) ? raw.stroke_width : 2,
                fill_opacity: (typeof raw.fill_opacity === 'number') ? raw.fill_opacity : 0,
            };
            if (p.type === 'shape' && !p.shape_type) p.shape_type = 'rect';
            return p;
        }

        /* ── API ─────────────────────────────────── */
        const API = CFG.apiBase;
        async function apiLoad() { if (!API) return []; try { const r = await fetch(API, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (!r.ok) throw new Error(r.status); const j = await r.json(); return Array.isArray(j.data) ? j.data : []; } catch (e) { console.error('[RPV] load:', e); return []; } }
        async function apiSave(payload) { if (!API) { snack('⚠️ Simpan draft review dulu!', '#F59E0B'); return null; } const clean = sanitize(payload); showSync('Menyimpan...'); try { const r = await fetch(API, { method: 'POST', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(clean) }); const j = await r.json(); if (!r.ok) { showSync('Gagal: ' + (j.message || r.status)); return null; } showSync('Tersimpan ✓', true); return j.data || null; } catch (e) { console.error('[RPV] save:', e); showSync('Error jaringan'); return null; } }
        async function apiPatch(id, payload) { if (!API) return; try { await fetch(`${API}/${id}`, { method: 'PUT', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(payload) }); } catch (e) { console.error('[RPV] patch:', e); } }
        async function apiDel(id) { if (!API) return; showSync('Menghapus...'); try { await fetch(`${API}/${id}`, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() }); showSync('Dihapus ✓', true); } catch (e) { console.error('[RPV] del:', e); } }
        async function apiDelPage(page) { if (!API) return; showSync('Membersihkan...'); try { await fetch(`${API}/page/${page}`, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() }); showSync('Selesai ✓', true); } catch (e) { console.error('[RPV] delPage:', e); } }
        async function loadAll() { annots = await apiLoad(); console.log('[RPV] loaded', annots.length, 'annotations'); scheduleRender(); updateBadge(); updateUndoRedo(); }

        /* ════════════════════════════════════════════
           SEARCH — dideklarasi SEBELUM doRender agar
           tidak terjadi ReferenceError saat dipanggil
        ════════════════════════════════════════════ */
        function clearSearchHL() { annotLayer.querySelectorAll('.rpvr-search-hl').forEach(e => e.remove()); searchHighlights = []; }

        function applySearchHL() {
            clearSearchHL();
            if (!currentQuery || !pdfDoc) return;
            const q = currentQuery.toLowerCase(), sr = stage.getBoundingClientRect();
            Array.from(textLayer.querySelectorAll('span')).forEach(span => {
                if (!span.firstChild) return;
                const text = span.textContent, lower = text.toLowerCase();
                let idx = lower.indexOf(q);
                while (idx !== -1) {
                    try {
                        const range = document.createRange();
                        range.setStart(span.firstChild, idx);
                        range.setEnd(span.firstChild, Math.min(idx + q.length, text.length));
                        Array.from(range.getClientRects()).forEach(rect => {
                            if (rect.width < 1 || rect.height < 1) return;
                            const el = document.createElement('div');
                            el.className = 'rpvr-search-hl';
                            el.style.cssText = `position:absolute;left:${rect.left - sr.left}px;top:${rect.top - sr.top}px;width:${rect.width}px;height:${rect.height}px;`;
                            annotLayer.appendChild(el);
                            searchHighlights.push(el);
                        });
                    } catch (_) { }
                    idx = lower.indexOf(q, idx + 1);
                }
            });
            searchHighlights.forEach((el, i) => el.classList.toggle('active-match', i === searchIndex));
            if (searchHighlights[searchIndex])
                searchHighlights[searchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        /* Flash highlight — menyala 1.2 detik lalu hilang */
        function flashHighlightAtIndex(idx) {
            const els = searchHighlights.filter((_, i) => i === idx);
            els.forEach(el => {
                el.style.background = 'rgba(255,107,24,.75)';
                el.style.outline = '2px solid #FF6B18';
                el.style.transition = 'background .4s, outline .4s';
                setTimeout(() => {
                    el.style.background = 'rgba(255,215,0,.45)';
                    el.style.outline = 'none';
                }, 1200);
            });
        }

        /* ── RENDER ──────────────────────────────── */
        function scheduleRender() { if (renderPending) return; renderPending = true; requestAnimationFrame(() => { renderPending = false; doRender(); }); }

        function doRender() {
            const s = baseScale * zoomFactor;
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
            stage.querySelectorAll('.rpv-sticky-note,.rpv-freetext').forEach(e => e.remove());
            annots.filter(a => a.page === pageNum).forEach(a => {
                switch (a.type) {
                    case 'highlight': case 'comment': rHL(a, s); break;
                    case 'underline': rUL(a, s); break;
                    case 'strikethrough': rST(a, s); break;
                    case 'freehand': rFH(a, s); break;
                    case 'shape': rSH(a, s); break;
                    case 'sticky': rSticky(a, s); break;
                }
            });
            updateBadge();
            // applySearchHL sudah dideklarasi di atas — tidak akan ReferenceError
            if (searchResults.length > 0 && currentQuery) applySearchHL();
        }

        /* ── RENDER HELPERS ──────────────────────── */
        function rHL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'), sel = selectedId == a.id;
            el.dataset.annotId = String(a.id);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${a.rect.y * s}px;width:${a.rect.w * s}px;height:${a.rect.h * s}px;background:${hex(a.color)};opacity:${sel ? .75 : .38};border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px solid #FF6B18' : 'none'};transition:opacity .15s;`;
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
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${(a.rect.y + a.rect.h) * s - t / 2}px;width:${a.rect.w * s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachEv(el, a); annotLayer.appendChild(el);
        }
        function rST(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);
            const t = Math.max(1.5, 2 * s);
            el.style.cssText = `position:absolute;left:${a.rect.x * s}px;top:${(a.rect.y + a.rect.h * 0.5) * s - t / 2}px;width:${a.rect.w * s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
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
            const wrap2 = document.createElement('div'); wrap2.dataset.annotId = String(a.id);
            wrap2.style.cssText = `position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel ? '2px dashed #FF6B18' : 'none'};`;
            const st = a.shape_type || 'rect'; let svg = '';
            if (st === 'rect') svg = `<rect x="${sw / 2}" y="${sw / 2}" width="${Math.max(1, w - sw)}" height="${Math.max(1, h - sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'ellipse') svg = `<ellipse cx="${w / 2}" cy="${h / 2}" rx="${Math.max(1, w / 2 - sw / 2)}" ry="${Math.max(1, h / 2 - sw / 2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if (st === 'arrow') { const hh = Math.max(4, h * .35), hx = Math.max(sw * 3, w * .25); svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - hx + sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w - sw / 2},${h / 2} ${w - hx},${h / 2 - hh} ${w - hx},${h / 2 + hh}" fill="${col}"/>"`; }
            else if (st === 'line') svg = `<line x1="${sw}" y1="${h / 2}" x2="${w - sw}" y2="${h / 2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
            wrap2.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
            attachEv(wrap2, a); annotLayer.appendChild(wrap2);
        }
        function rSticky(a, s) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'rpv-sticky-note'; note.dataset.annotId = String(a.id); note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * s) + 'px'; note.style.top = (a.rect.y * s) + 'px';
            note.innerHTML = `<div class="rpv-sn-header"><span>📌</span><div style="display:flex;gap:3px;"><button type="button" class="rpv-sn-edit" title="Edit">✏️</button><button type="button" class="rpv-sn-del" title="Hapus">×</button></div></div><div class="rpv-sn-body">${esc(a.comment)}</div>`;
            note.querySelector('.rpv-sn-del').addEventListener('click', ev => { ev.stopPropagation(); removeStickyAnimated(note, a.id); });
            note.querySelector('.rpv-sn-edit').addEventListener('click', ev => { ev.stopPropagation(); openEditPopup(a); });
            note.addEventListener('click', ev => {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeStickyAnimated(note, a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, s); stage.appendChild(note);
        }

        function removeStickyAnimated(noteEl, id) {
            noteEl.classList.add('removing');
            setTimeout(async () => { noteEl.remove(); await removeAnnot(id); }, 180);
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
            function onDown(e) { if (e.target.classList.contains('rpv-sn-del') || e.target.classList.contains('rpv-sn-edit') || e.target.classList.contains('rpv-sn-body')) return; dragging = true; moved = false; const src = e.touches?.[0] ?? e; ox = src.clientX - el.offsetLeft; oy = src.clientY - el.offsetTop; el.style.zIndex = '20'; e.stopPropagation(); if (e.cancelable) e.preventDefault(); }
            function onMove(e) { if (!dragging) return; moved = true; const src = e.touches?.[0] ?? e; el.style.left = (src.clientX - ox) + 'px'; el.style.top = (src.clientY - oy) + 'px'; if (e.cancelable) e.preventDefault(); }
            async function onUp() { if (!dragging) return; dragging = false; el.style.zIndex = '9'; if (!moved) return; const newX = parseFloat(el.style.left) / s, newY = parseFloat(el.style.top) / s; const idx = annots.findIndex(a => String(a.id) === String(annotData.id)); if (idx >= 0 && annots[idx].rect) { annots[idx].rect.x = newX; annots[idx].rect.y = newY; } await apiPatch(annotData.id, { rect_x: newX, rect_y: newY, rect_w: annotData.rect?.w || 180, rect_h: annotData.rect?.h || 90 }); }
            el.addEventListener('mousedown', onDown, { passive: false }); el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove, { passive: false }); document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp); document.addEventListener('touchend', onUp);
        }

        /* ── TOOLTIP ─────────────────────────────── */
        function showTip(a, cx, cy) {
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', brush: '🖌️', shape: '⬛', comment: '💬', sticky: '📌' };
            let txt = `${ic[a.type] || '•'} ${a.type}`;
            if (a.comment) txt = `${ic[a.type] || '•'} ${a.comment.substring(0, 80)}`;
            else if (a.selected_text) txt = `${ic[a.type] || '•'} "${a.selected_text.substring(0, 60)}"`;
            const tipTxt = document.getElementById('rpv-tip-text');
            if (tipTxt) { tipTxt.textContent = txt; tipTxt.dataset.annotId = String(a.id); }
            // Show edit button only for comment/sticky
            const editBtn = document.getElementById('rpv-tip-edit');
            if (editBtn) editBtn.style.display = (a.type === 'comment' || a.type === 'sticky' || a.type === 'highlight') ? '' : 'none';
            if (editBtn) editBtn.dataset.annotId = String(a.id);
            tooltip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            tooltip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
            tooltip.style.top = ((cy + 140 > vh) ? Math.max(4, cy - 140) : cy + 8) + 'px';
        }
        document.getElementById('rpv-tip-close')?.addEventListener('click', () => tooltip.classList.remove('show'));
        document.getElementById('rpv-tip-del')?.addEventListener('click', async () => { const id = document.getElementById('rpv-tip-text')?.dataset.annotId; tooltip.classList.remove('show'); if (id) await removeAnnot(id); });
        document.getElementById('rpv-tip-edit')?.addEventListener('click', () => { const id = document.getElementById('rpv-tip-edit')?.dataset.annotId; tooltip.classList.remove('show'); if (id) { const a = annots.find(x => String(x.id) === id); if (a) openEditPopup(a); } });
        document.addEventListener('click', e => { if (tooltip && !tooltip.contains(e.target) && !e.target.closest('[data-annot-id],.rpv-sticky-note')) tooltip.classList.remove('show'); });

        /* ── EDIT POPUP ──────────────────────────── */
        function openEditPopup(a) {
            let pop = document.getElementById('rpv-edit-popup');
            if (!pop) {
                pop = document.createElement('div'); pop.id = 'rpv-edit-popup'; pop.className = 'rpv-popup';
                pop.innerHTML = `<p class="rpv-popup-title">✏️ Edit Anotasi</p><textarea id="rpv-edit-txt" style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:80px;display:block;box-sizing:border-box;"></textarea><div class="rpv-popup-actions"><button type="button" class="rpv-popup-save" id="rpv-edit-save">Simpan</button><button type="button" class="rpv-popup-cancel" id="rpv-edit-cancel">Batal</button></div>`;
                document.body.appendChild(pop);
                document.getElementById('rpv-edit-cancel').addEventListener('click', () => pop.classList.remove('show'));
            }
            const txt = document.getElementById('rpv-edit-txt');
            txt.value = a.comment || a.selected_text || '';
            pop.style.left = Math.max(4, Math.min(window.innerWidth / 2 - 140, window.innerWidth - 292)) + 'px';
            pop.style.top = Math.max(4, window.innerHeight / 2 - 100) + 'px';
            pop.classList.add('show');
            txt.focus();

            const saveBtn = document.getElementById('rpv-edit-save');
            const newSave = saveBtn.cloneNode(true);
            saveBtn.parentNode.replaceChild(newSave, saveBtn);
            newSave.addEventListener('click', async () => {
                const newTxt = txt.value.trim();
                if (!newTxt) { snack('Teks tidak boleh kosong!'); return; }
                pop.classList.remove('show');
                await apiPatch(a.id, { comment: newTxt });
                const idx = annots.findIndex(x => String(x.id) === String(a.id));
                if (idx >= 0) { annots[idx].comment = newTxt; if (annots[idx].selected_text) annots[idx].selected_text = newTxt; }
                scheduleRender(); snack('✓ Anotasi diperbarui', '#22c55e');
            });
        }

        /* ── ADD / REMOVE ────────────────────────── */
        async function addAnnot(payload) { const saved = await apiSave(payload); if (!saved) return null; annots.push(saved); undoStack.push({ action: 'add', data: saved }); redoStack = []; updateUndoRedo(); scheduleRender(); return saved; }
        async function removeAnnot(id) { const a = annots.find(x => String(x.id) === String(id)); if (!a) return; await apiDel(a.id); annots = annots.filter(x => String(x.id) !== String(id)); if (selectedId === String(id)) selectedId = null; undoStack.push({ action: 'del', data: a }); redoStack = []; updateUndoRedo(); scheduleRender(); snack('🗑 Anotasi dihapus'); }

        /* ── UNDO / REDO ─────────────────────────── */
        function updateUndoRedo() { const u = document.getElementById('rpv-undo'); if (u) u.disabled = !undoStack.length; const r = document.getElementById('rpv-redo'); if (r) r.disabled = !redoStack.length; }
        async function doUndo() { if (!undoStack.length) return; const op = undoStack.pop(); if (op.action === 'add') { const a = annots.find(x => String(x.id) === String(op.data.id)); if (a) { await apiDel(a.id); annots = annots.filter(x => String(x.id) !== String(a.id)); redoStack.push({ action: 'readd', data: a }); } } else if (op.action === 'del') { const saved = await apiSave(op.data); if (saved) { annots.push(saved); redoStack.push({ action: 'redel', data: saved }); } } updateUndoRedo(); scheduleRender(); }
        async function doRedo() { if (!redoStack.length) return; const op = redoStack.pop(); if (op.action === 'readd') { const saved = await apiSave(op.data); if (saved) { annots.push(saved); undoStack.push({ action: 'add', data: saved }); } } else if (op.action === 'redel') { const a = annots.find(x => String(x.id) === String(op.data.id)); if (a) { await apiDel(a.id); annots = annots.filter(x => String(x.id) !== String(a.id)); undoStack.push({ action: 'del', data: a }); } } updateUndoRedo(); scheduleRender(); }
        document.getElementById('rpv-undo')?.addEventListener('click', doUndo);
        document.getElementById('rpv-redo')?.addEventListener('click', doRedo);

        /* ── BADGE & PANEL ───────────────────────── */
        function updateBadge() { const n = annots.length, badge = document.getElementById('rpv-badge'); if (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.classList.toggle('show', n > 0); } }
        document.getElementById('rpv-panel-btn')?.addEventListener('click', e => { e.stopPropagation(); document.getElementById('rpv-panel')?.classList.toggle('open'); buildPanel(); });
        document.getElementById('rpv-panel-close')?.addEventListener('click', () => document.getElementById('rpv-panel')?.classList.remove('open'));
        document.getElementById('rpv-panel-clear')?.addEventListener('click', async () => { if (!confirm(`Hapus semua anotasi di halaman ${pageNum}?`)) return; await apiDelPage(pageNum); annots = annots.filter(a => a.page !== pageNum); undoStack = []; redoStack = []; updateUndoRedo(); scheduleRender(); buildPanel(); snack(`🗑 Halaman ${pageNum} dibersihkan`); });
        function buildPanel() {
            const list = document.getElementById('rpv-panel-list'); if (!list) return;
            if (!annots.length) { list.innerHTML = '<div class="rpv-panel-empty">Belum ada anotasi.</div>'; return; }
            list.innerHTML = '';
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', brush: '🖌️', shape: '⬛', comment: '💬', sticky: '📌' };
            [...annots].sort((a, b) => a.page - b.page || a.id - b.id).forEach(a => {
                const el = document.createElement('div'); el.className = 'rpv-panel-item';
                el.innerHTML = `<div class="rpv-panel-dot" style="background:${hex(a.color)}"></div><div class="rpv-panel-body"><span class="rpv-panel-type">${ic[a.type] || '•'} ${a.type}</span><span class="rpv-panel-pg">Hal.${a.page}</span><div class="rpv-panel-text">${esc(a.comment || a.selected_text || a.shape_type || '—')}</div></div><div style="display:flex;gap:2px;flex-shrink:0;"><button type="button" class="rpv-panel-edit" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:11px;padding:2px 3px;border-radius:4px;" title="Edit">✏️</button><button type="button" class="rpv-panel-del" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:12px;padding:2px 3px;border-radius:4px;" title="Hapus">🗑</button></div>`;
                el.querySelector('.rpv-panel-del').addEventListener('click', async ev => { ev.stopPropagation(); await removeAnnot(a.id); buildPanel(); });
                el.querySelector('.rpv-panel-edit').addEventListener('click', ev => { ev.stopPropagation(); openEditPopup(a); });
                el.addEventListener('click', () => { if (a.page !== pageNum) renderPage(a.page); document.getElementById('rpv-panel')?.classList.remove('open'); });
                list.appendChild(el);
            });
        }

        /* ── TOOL MANAGEMENT ─────────────────────── */
        function setTool(tool) {
            activeTool = tool;
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode');
            if (tool === 'freehand' || tool === 'brush') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            const needsSel = ['highlight', 'comment', 'underline', 'strikethrough'].includes(tool);
            textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
            textLayer.style.userSelect = needsSel ? 'text' : 'none';
            textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
            if (freeCanvas) freeCanvas.style.pointerEvents = ['freehand', 'brush', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur) eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }
            const LABELS = { pan: '🖐 Hand', select: '↖ Pilih', highlight: '✏️ Highlight', underline: '__ Underline', strikethrough: '~~ Strikethrough', comment: '💬 Komentar', freehand: '🖊 Pen', brush: '🖌️ Brush', shape: '⬛ Shape', eraser: '🧹 Hapus' };
            const lbl = document.getElementById('rpv-active-label'); if (lbl) lbl.textContent = LABELS[tool] || tool;
            document.getElementById('rpv-sizes')?.style.setProperty('display', ['freehand', 'brush', 'shape'].includes(tool) ? 'flex' : 'none');
            document.getElementById('rpv-shapes')?.classList.toggle('show', tool === 'shape');
        }
        document.querySelectorAll('.rpv-tool[data-tool]').forEach(btn => { btn.addEventListener('click', () => { document.querySelectorAll('.rpv-tool[data-tool]').forEach(b => b.classList.remove('active')); btn.classList.add('active'); setTool(btn.dataset.tool); }); });
        document.querySelectorAll('.rpv-color').forEach(sw => { sw.addEventListener('click', () => { document.querySelectorAll('.rpv-color').forEach(s => s.classList.remove('selected')); sw.classList.add('selected'); activeColor = sw.dataset.color; }); });
        document.querySelectorAll('.rpv-size').forEach(d => { d.addEventListener('click', () => { document.querySelectorAll('.rpv-size').forEach(x => x.classList.remove('selected')); d.classList.add('selected'); activeSize = +d.dataset.size; }); });
        document.querySelectorAll('.rpv-shape').forEach(b => { b.addEventListener('click', () => { document.querySelectorAll('.rpv-shape').forEach(x => x.classList.remove('active')); b.classList.add('active'); activeShape = b.dataset.shape; }); });

        /* ── TEXT SELECTION ──────────────────────── */
        function getSelInfo() {
            const sel = window.getSelection(); if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0); if (!textLayer?.contains(range.commonAncestorContainer)) return null;
            const sr = stage.getBoundingClientRect(), s = baseScale * zoomFactor;
            const rects = Array.from(range.getClientRects()).filter(r => r.width > .5 && r.height > .5); if (!rects.length) return null;
            const L = Math.min(...rects.map(r => r.left)), T = Math.min(...rects.map(r => r.top));
            const R = Math.max(...rects.map(r => r.right)), B = Math.max(...rects.map(r => r.bottom));
            return { rect: { x: (L - sr.left) / s, y: (T - sr.top) / s, w: (R - L) / s, h: (B - T) / s }, text: sel.toString().substring(0, 1000), br: range.getBoundingClientRect() };
        }
        let selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest('.rpv-popup,#rpv-annot-bar,#rpv-panel')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(async () => {
                const info = getSelInfo(); if (!info || info.rect.w < 2) return;
                const base = { page: pageNum, color: activeColor, rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h, selected_text: info.text };
                if (activeTool === 'highlight') { await addAnnot({ ...base, type: 'highlight' }); window.getSelection()?.removeAllRanges(); snack('✏️ Highlight!'); }
                else if (activeTool === 'underline') { await addAnnot({ ...base, type: 'underline' }); window.getSelection()?.removeAllRanges(); snack('__ Underline!'); }
                else if (activeTool === 'strikethrough') { await addAnnot({ ...base, type: 'strikethrough' }); window.getSelection()?.removeAllRanges(); snack('~~ Strikethrough!'); }
                else if (activeTool === 'comment') { pendingRect = info.rect; pendingText = info.text; openPopup(document.getElementById('rpv-comment-pop'), info.br.left, info.br.bottom + 8); const t = document.getElementById('rpv-comment-txt'); if (t) { t.value = ''; t.focus(); } }
            }, 80);
        }
        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', e => { if (!['highlight', 'comment', 'underline', 'strikethrough'].includes(activeTool)) return; onSelEnd(e); }, { passive: true });

        function openPopup(popup, cx, cy) { if (!popup) return; const vw = window.innerWidth, vh = window.innerHeight, pw = 284, ph = 170; popup.style.left = Math.max(4, Math.min(cx - pw / 2, vw - pw - 4)) + 'px'; popup.style.top = Math.max(4, (cy + ph > vh ? cy - ph - 8 : cy)) + 'px'; popup.classList.add('show'); }
        document.getElementById('rpv-comment-save')?.addEventListener('click', async () => { const txt = document.getElementById('rpv-comment-txt')?.value.trim(); if (!txt || !pendingRect) { snack('Tulis komentar dulu!'); return; } document.getElementById('rpv-comment-txt').value = ''; document.getElementById('rpv-comment-pop')?.classList.remove('show'); await addAnnot({ page: pageNum, type: 'comment', color: activeColor, rect_x: pendingRect.x, rect_y: pendingRect.y, rect_w: pendingRect.w, rect_h: pendingRect.h, selected_text: pendingText || '', comment: txt }); window.getSelection()?.removeAllRanges(); pendingRect = null; pendingText = null; snack('💬 Komentar disimpan!'); });
        document.getElementById('rpv-comment-cancel')?.addEventListener('click', () => { document.getElementById('rpv-comment-pop')?.classList.remove('show'); pendingRect = null; pendingText = null; window.getSelection()?.removeAllRanges(); });
        document.getElementById('rpv-sticky-save')?.addEventListener('click', async () => { const txt = document.getElementById('rpv-sticky-txt')?.value.trim(); if (!txt || !stickyPos) { snack('Tulis catatan dulu!'); return; } document.getElementById('rpv-sticky-txt').value = ''; document.getElementById('rpv-sticky-pop')?.classList.remove('show'); await addAnnot({ page: pageNum, type: 'sticky', color: activeColor, rect_x: stickyPos.x, rect_y: stickyPos.y, rect_w: 180, rect_h: 90, comment: txt }); stickyPos = null; snack('📌 Sticky note ditempel!'); });
        document.getElementById('rpv-sticky-cancel')?.addEventListener('click', () => { document.getElementById('rpv-sticky-pop')?.classList.remove('show'); stickyPos = null; });

        /* ── FREEHAND / BRUSH ────────────────────── */
        function getFHSize() { return activeTool === 'brush' ? Math.max(6, activeSize * 3.5) : activeSize; }
        function getFHAlpha() { return activeTool === 'brush' ? .5 : .92; }
        function fhStart(e) { if (activeTool !== 'freehand' && activeTool !== 'brush') return; if (e.cancelable) e.preventDefault(); isDrawing = true; freePoints = []; const p = stageXY(e), s = baseScale * zoomFactor; freePoints.push([p.x / s, p.y / s]); }
        function fhMove(e) { if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return; if (e.cancelable) e.preventDefault(); const p = stageXY(e), s = baseScale * zoomFactor; freePoints.push([p.x / s, p.y / s]); if (!freeCtx || freePoints.length < 2) return; const last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1]; freeCtx.save(); freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = getFHSize() * s; freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = getFHAlpha(); freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s); freeCtx.stroke(); freeCtx.restore(); }
        async function fhEnd(e) { if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return; if (e.cancelable) e.preventDefault(); isDrawing = false; if (freePoints.length < 2) return; const xs = freePoints.map(p => p[0]), ys = freePoints.map(p => p[1]), bx = Math.min(...xs), by = Math.min(...ys); await addAnnot({ page: pageNum, type: 'freehand', color: activeColor, stroke_width: getFHSize(), path_points: freePoints, rect_x: bx, rect_y: by, rect_w: Math.max(...xs) - bx, rect_h: Math.max(...ys) - by }); }

        /* ── SHAPE ───────────────────────────────── */
        function shStart(e) { if (activeTool !== 'shape') return; if (e.cancelable) e.preventDefault(); isDrawing = true; drawStart = stageXY(e); shapePreviewEl = document.createElement('div'); shapePreviewEl.style.cssText = `position:absolute;pointer-events:none;z-index:25;border:${activeSize}px solid ${hex(activeColor)};${activeShape === 'ellipse' ? 'border-radius:50%;' : ''}left:${drawStart.x}px;top:${drawStart.y}px;width:0;height:0;`; stage.appendChild(shapePreviewEl); }
        function shMove(e) { if (!isDrawing || activeTool !== 'shape' || !shapePreviewEl || !drawStart) return; if (e.cancelable) e.preventDefault(); const c = stageXY(e); Object.assign(shapePreviewEl.style, { left: Math.min(drawStart.x, c.x) + 'px', top: Math.min(drawStart.y, c.y) + 'px', width: Math.abs(c.x - drawStart.x) + 'px', height: Math.abs(c.y - drawStart.y) + 'px' }); }
        async function shEnd(e) { if (!isDrawing || activeTool !== 'shape') return; if (e.cancelable) e.preventDefault(); isDrawing = false; shapePreviewEl?.remove(); shapePreviewEl = null; const c = stageXY(e), s = baseScale * zoomFactor; if (!drawStart) return; const x = Math.min(drawStart.x, c.x) / s, y = Math.min(drawStart.y, c.y) / s, w = Math.abs(c.x - drawStart.x) / s, h = Math.abs(c.y - drawStart.y) / s; drawStart = null; if (w < 4 && h < 4) return; await addAnnot({ page: pageNum, type: 'shape', color: activeColor, shape_type: activeShape, stroke_width: activeSize, rect_x: x, rect_y: y, rect_w: w, rect_h: activeShape === 'line' ? 1 : h }); }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('mousemove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('mouseup', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('mouseleave', e => { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('touchstart', e => { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('touchmove', e => { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('touchend', e => { fhEnd(e); shEnd(e); }, { passive: false });
        }

        /* ── ERASER CURSOR ───────────────────────── */
        document.addEventListener('mousemove', e => { if (!eraserCur) return; eraserCur.style.display = activeTool === 'eraser' ? 'block' : 'none'; if (activeTool === 'eraser') { eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; } });

        /* ── STAGE CLICK ─────────────────────────── */
        stage.addEventListener('click', e => {
            const hitAnnot = e.target.closest('[data-annot-id],.rpv-sticky-note');
            if (activeTool === 'sticky') { if (hitAnnot) return; if (e.target.closest('.rpv-popup')) return; const p = stageXY(e), s = baseScale * zoomFactor; stickyPos = { x: p.x / s, y: p.y / s }; openPopup(document.getElementById('rpv-sticky-pop'), e.clientX, e.clientY); const t = document.getElementById('rpv-sticky-txt'); if (t) { t.value = ''; setTimeout(() => t.focus(), 30); } return; }
            if (activeTool === 'select') { if (!hitAnnot) { selectedId = null; scheduleRender(); } return; }
            if (activeTool === 'eraser') { if (!hitAnnot) snack('Klik anotasi untuk menghapus', '#60A5FA'); return; }
        });

        /* ── PAN ─────────────────────────────────── */
        stage.addEventListener('mousedown', e => { if (activeTool !== 'pan') return; isPanning = true; panSX = e.clientX; panSY = e.clientY; panScrollX = wrap?.scrollLeft || 0; panScrollY = wrap?.scrollTop || 0; if (e.cancelable) e.preventDefault(); }, { passive: false });
        document.addEventListener('mousemove', e => { if (!isPanning || activeTool !== 'pan') return; if (wrap) { wrap.scrollLeft = panScrollX + (panSX - e.clientX); wrap.scrollTop = panScrollY + (panSY - e.clientY); } });
        document.addEventListener('mouseup', () => { isPanning = false; });
        /* Pinch zoom */
        let lastPinchDist = 0;
        wrap?.addEventListener('touchstart', e => { if (e.touches.length === 2) lastPinchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); }, { passive: true });
        wrap?.addEventListener('touchmove', e => { if (e.touches.length !== 2) return; const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); if (Math.abs(d - lastPinchDist) > 14) { d > lastPinchDist ? doZoom(1) : doZoom(-1); lastPinchDist = d; } }, { passive: true });
        /* Swipe page (slide mode only) */
        let swTX = 0, swTY = 0;
        wrap?.addEventListener('touchstart', e => { if (e.touches.length === 1) { swTX = e.touches[0].clientX; swTY = e.touches[0].clientY; } }, { passive: true });
        wrap?.addEventListener('touchend', e => {
            if (readMode !== 'slide' || e.changedTouches.length !== 1) return;
            const dx = swTX - e.changedTouches[0].clientX, dy = swTY - e.changedTouches[0].clientY;
            if (Math.abs(dx) > Math.abs(dy) * 1.8 && Math.abs(dx) > 60) { if (['freehand', 'brush', 'shape', 'pan'].includes(activeTool)) return; dx > 0 ? nextPage() : prevPage(); }
        }, { passive: true });

        /* ── READ MODE: slide vs scroll ─────────────
         * slide  = satu halaman sekaligus (default)
         * scroll = semua halaman continuous
         */
        function setReadMode(mode) {
            readMode = mode;
            // Update button labels
            document.querySelectorAll('[data-rpv-readmode]').forEach(el => {
                el.classList.toggle('active', el.dataset.rpvReadmode === mode);
            });
            if (mode === 'slide') {
                // Hapus canvas halaman lain, tampilkan hanya pageNum
                clearScrollPages();
                renderPage(pageNum);
            } else {
                renderScrollMode();
            }
        }

        function clearScrollPages() {
            // remove extra scroll canvases
            document.querySelectorAll('.rpv-scroll-page').forEach(e => e.remove());
        }

        async function renderScrollMode() {
            if (!pdfDoc) return;
            clearScrollPages();
            // Tampilkan semua halaman dalam satu wrapper
            wrap.innerHTML = ''; // bersihkan
            // Kembalikan stage
            wrap.appendChild(stage);

            for (let p = 1; p <= pdfDoc.numPages; p++) {
                const container = document.createElement('div');
                container.className = 'rpv-scroll-page';
                container.dataset.page = p;
                container.style.cssText = 'display:flex;justify-content:center;margin:.5rem 0;';

                const pageCanvas = document.createElement('canvas');
                pageCanvas.style.cssText = 'box-shadow:0 4px 20px rgba(0,0,0,.4);display:block;';
                container.appendChild(pageCanvas);
                wrap.insertBefore(container, wrap.firstChild.nextSibling);

                const page = await pdfDoc.getPage(p);
                const cw = wrap.clientWidth || 800;
                const bs = Math.max(0.5, Math.min((cw - 24) / page.getViewport({ scale: 1 }).width, 2.5));
                const vpCss = page.getViewport({ scale: bs * zoomFactor });
                const vpRender = page.getViewport({ scale: bs * zoomFactor * DPR });
                pageCanvas.width = Math.floor(vpRender.width); pageCanvas.height = Math.floor(vpRender.height);
                pageCanvas.style.width = Math.floor(vpCss.width) + 'px'; pageCanvas.style.height = Math.floor(vpCss.height) + 'px';
                await page.render({ canvasContext: pageCanvas.getContext('2d'), viewport: vpRender }).promise;
            }
        }

        /* ── FULLSCREEN ──────────────────────────── */
        function updateFsBtn() {
            const btn = document.getElementById('rpv-fs-btn');
            if (!btn) return;
            if (isFullscreen) {
                btn.innerHTML = `<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Keluar Fullscreen</span>`;
            } else {
                btn.innerHTML = `<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg><span>Layar Penuh</span>`;
            }
        }
        function enterFullscreen() {
            isFullscreen = true;
            outerWrap?.classList.add('is-fullscreen');
            document.body.style.overflow = 'hidden';
            updateFsBtn();
            snack('🔲 Layar penuh — tekan F atau Esc untuk keluar');
            if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); renderPage(pageNum); });
        }
        function exitFullscreen() {
            isFullscreen = false;
            outerWrap?.classList.remove('is-fullscreen');
            document.body.style.overflow = '';
            updateFsBtn();
            if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); renderPage(pageNum); });
        }
        document.getElementById('rpv-fs-btn')?.addEventListener('click', () => isFullscreen ? exitFullscreen() : enterFullscreen());

        /* ── RESUME TOAST ────────────────────────── */
        function showResumeTrigger(savedPage) {
            if (savedPage <= 1 || !pdfDoc || savedPage > pdfDoc.numPages) return;
            let toast = document.getElementById('rpv-resume-toast');
            if (!toast) {
                toast = document.createElement('div'); toast.id = 'rpv-resume-toast';
                toast.style.cssText = 'position:fixed;bottom:5rem;left:50%;transform:translateX(-50%) translateY(60px);background:#1a1a1a;border:1.5px solid #FF6B18;color:#fff;padding:.6rem .875rem;border-radius:14px;font-size:13px;z-index:20010;display:flex;align-items:center;gap:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.5);opacity:0;transition:all .4s cubic-bezier(.34,1.56,.64,1);pointer-events:none;white-space:nowrap;';
                toast.innerHTML = `<span style="font-size:1.2rem;">🔖</span><div><p style="font-weight:700;margin:0;font-size:12px;">Lanjut membaca?</p><p style="color:#9ca3af;margin:0;font-size:11px;" id="rpv-resume-txt">Terakhir di hal. ${savedPage}</p></div><button type="button" id="rpv-resume-yes" style="padding:.3rem .7rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;pointer-events:auto;">Lanjut</button><button type="button" id="rpv-resume-no" style="padding:.3rem .6rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:11px;cursor:pointer;pointer-events:auto;">Awal</button>`;
                document.body.appendChild(toast);
            }
            document.getElementById('rpv-resume-txt').textContent = `Terakhir di halaman ${savedPage}`;
            // Animate in
            requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateX(-50%) translateY(0)'; toast.style.pointerEvents = 'auto'; });
            // Auto hide
            const autoHide = setTimeout(() => hideResume(toast), 8000);
            document.getElementById('rpv-resume-yes').onclick = () => { clearTimeout(autoHide); hideResume(toast); renderPage(savedPage); };
            document.getElementById('rpv-resume-no').onclick = () => { clearTimeout(autoHide); hideResume(toast); renderPage(1); };
        }
        function hideResume(t) { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(60px)'; t.style.pointerEvents = 'none'; }

        /* ── MOBILE BOTTOM SHEET ─────────────────── */
        function openSheet() { document.getElementById('rpv-bottom-sheet')?.classList.add('show'); document.getElementById('rpv-sheet-backdrop')?.classList.add('show'); }
        function closeSheet() { document.getElementById('rpv-bottom-sheet')?.classList.remove('show'); document.getElementById('rpv-sheet-backdrop')?.classList.remove('show'); }
        document.getElementById('rpv-mobile-fab-btn')?.addEventListener('click', openSheet);
        document.getElementById('rpv-sheet-backdrop')?.addEventListener('click', closeSheet);
        document.getElementById('rpv-sheet-close')?.addEventListener('click', closeSheet);
        document.getElementById('rpv-sheet-prev')?.addEventListener('click', () => prevPage());
        document.getElementById('rpv-sheet-next')?.addEventListener('click', () => nextPage());
        document.getElementById('rpv-sheet-zoom-in')?.addEventListener('click', () => doZoom(1));
        document.getElementById('rpv-sheet-zoom-out')?.addEventListener('click', () => doZoom(-1));
        document.getElementById('rpv-sheet-fs')?.addEventListener('click', () => { closeSheet(); setTimeout(() => isFullscreen ? exitFullscreen() : enterFullscreen(), 200); });
        document.getElementById('rpv-sheet-search')?.addEventListener('click', () => { closeSheet(); setTimeout(openSearch, 200); });
        document.querySelectorAll('[data-rpv-sheet-mode]').forEach(btn => { btn.addEventListener('click', () => { document.querySelectorAll('[data-rpv-sheet-mode]').forEach(b => b.classList.remove('active')); btn.classList.add('active'); applyMode(btn.dataset.rpvSheetMode); closeSheet(); }); });

        /* Read mode toggle in sheet */
        document.querySelectorAll('[data-rpv-readmode]').forEach(btn => {
            btn.addEventListener('click', () => setReadMode(btn.dataset.rpvReadmode));
        });

        /* ── READING MODE (sepia/night) ──────────── */
        function applyMode(mode) { outerWrap?.classList.remove('mode-sepia', 'mode-night'); if (mode !== 'normal') outerWrap?.classList.add('mode-' + mode); document.querySelectorAll('[data-rpv-mode],[data-rpv-sheet-mode]').forEach(b => { const m = b.dataset.rpvMode || b.dataset.rpvSheetMode; b.classList.toggle('active', m === mode); }); }
        document.querySelectorAll('[data-rpv-mode]').forEach(btn => { btn.addEventListener('click', () => applyMode(btn.dataset.rpvMode)); });

        /* ── SEARCH (dideklarasi di atas, digunakan di sini) ─ */
        function openSearch() {
            document.getElementById('rpv-search')?.classList.add('show');
            document.getElementById('rpv-search-input')?.focus();
        }
        function closeSearch() {
            document.getElementById('rpv-search')?.classList.remove('show');
            clearSearchHL(); currentQuery = ''; searchResults = []; searchIndex = -1;
            const i = document.getElementById('rpv-search-input'); if (i) i.value = '';
            const rl = document.getElementById('rpv-search-results'); if (rl) rl.innerHTML = '';
            const rs = document.getElementById('rpv-search-status'); if (rs) rs.textContent = 'Ketik untuk mencari...';
        }

        async function doSearch(query) {
            if (!pdfDoc || !query.trim()) { clearSearchHL(); currentQuery = ''; const rs = document.getElementById('rpv-search-status'); if (rs) rs.textContent = 'Ketik untuk mencari...'; return; }
            const rs = document.getElementById('rpv-search-status'); if (rs) rs.textContent = 'Mencari...';
            searchResults = []; currentQuery = query; const q = query.toLowerCase();
            for (let p = 1; p <= pdfDoc.numPages; p++) { const page = await pdfDoc.getPage(p); const content = await page.getTextContent(); const text = content.items.map(i => i.str).join(' '); const lt = text.toLowerCase(); let idx = lt.indexOf(q); while (idx !== -1) { searchResults.push({ page: p, excerpt: text.substring(Math.max(0, idx - 35), idx + q.length + 50).trim(), charIdx: idx }); idx = lt.indexOf(q, idx + 1); } }
            const list = document.getElementById('rpv-search-results'); if (!list) return; list.innerHTML = '';
            if (!searchResults.length) { if (rs) rs.textContent = `Tidak ditemukan: "${query}"`; clearSearchHL(); return; }
            if (rs) rs.textContent = `${searchResults.length} hasil ditemukan`;
            searchIndex = 0;
            searchResults.slice(0, 40).forEach((r, i) => {
                const el = document.createElement('div'); el.className = 'rpv-sri';
                el.innerHTML = `<span class="pg">Hal.${r.page}</span><span>${esc(r.excerpt).replace(new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'), m => `<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">${m}</mark>`)}</span>`;
                el.addEventListener('click', () => {
                    searchIndex = i;
                    /* Klik hasil: pindah halaman, highlight flash, tutup overlay */
                    if (r.page !== pageNum) {
                        renderPage(r.page);
                        // Setelah render selesai, flash highlight lalu tutup
                        setTimeout(() => { applySearchHL(); flashHighlightAtIndex(searchIndex); }, 600);
                    } else {
                        applySearchHL();
                        flashHighlightAtIndex(searchIndex);
                    }
                    // Tutup search overlay
                    setTimeout(closeSearch, 800);
                });
                list.appendChild(el);
            });
            if (searchResults[0].page === pageNum) applySearchHL(); else renderPage(searchResults[0].page);
        }

        document.getElementById('rpv-search-input')?.addEventListener('input', function () { clearTimeout(searchDebounce); searchDebounce = setTimeout(() => doSearch(this.value), 450); });
        document.getElementById('rpv-sclose')?.addEventListener('click', closeSearch);
        document.getElementById('rpv-snext')?.addEventListener('click', () => { if (!searchResults.length) return; searchIndex = (searchIndex + 1) % searchResults.length; const r = searchResults[searchIndex]; if (r.page !== pageNum) renderPage(r.page); else { applySearchHL(); flashHighlightAtIndex(searchIndex); } });
        document.getElementById('rpv-sprev')?.addEventListener('click', () => { if (!searchResults.length) return; searchIndex = (searchIndex - 1 + searchResults.length) % searchResults.length; const r = searchResults[searchIndex]; if (r.page !== pageNum) renderPage(r.page); else { applySearchHL(); flashHighlightAtIndex(searchIndex); } });
        document.getElementById('rpv-search')?.addEventListener('click', e => { if (e.target === document.getElementById('rpv-search')) closeSearch(); });
        document.getElementById('rpv-search-btn')?.addEventListener('click', openSearch);

        /* ── EXPORT PDF ──────────────────────────── */
        async function exportPdfWithAnnotations() {
            if (exportInProgress) { snack('⏳ Sedang export...'); return; }
            if (!pdfDoc) { snack('PDF belum dimuat!'); return; }
            const jsPDFLib = window.jspdf?.jsPDF || window.jsPDF;
            if (!jsPDFLib) { snack('⚠️ Library PDF belum siap', '#F59E0B'); return; }
            exportInProgress = true; if (exportOL) exportOL.classList.add('show');
            try {
                const SCALE = 2.0; const offCanvas = document.createElement('canvas'); const offCtx = offCanvas.getContext('2d'); let pdf = null;
                for (let p = 1; p <= pdfDoc.numPages; p++) {
                    const page = await pdfDoc.getPage(p); const vp = page.getViewport({ scale: SCALE });
                    offCanvas.width = Math.floor(vp.width); offCanvas.height = Math.floor(vp.height); offCtx.clearRect(0, 0, offCanvas.width, offCanvas.height);
                    await page.render({ canvasContext: offCtx, viewport: vp }).promise;
                    annots.filter(a => a.page === p).forEach(a => renderAnnotToCanvas(offCtx, a, SCALE));
                    const wMm = vp.width * 0.264583, hMm = vp.height * 0.264583;
                    if (!pdf) pdf = new jsPDFLib({ orientation: vp.width > vp.height ? 'landscape' : 'portrait', unit: 'mm', format: [wMm, hMm] });
                    else pdf.addPage([wMm, hMm], vp.width > vp.height ? 'landscape' : 'portrait');
                    pdf.addImage(offCanvas.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, wMm, hMm, '', 'FAST');
                    showSync(`Halaman ${p}/${pdfDoc.numPages}...`);
                    const exportSt = document.getElementById('rpv-export-status'); if (exportSt) exportSt.textContent = `Memproses halaman ${p} dari ${pdfDoc.numPages}...`;
                }
                pdf.save('review-annotated-' + Date.now() + '.pdf');
                snack('✅ PDF berhasil didownload!', '#22c55e'); showSync('Export selesai ✓', true);
            } catch (err) { console.error('[RPV] export:', err); snack('❌ Gagal: ' + err.message, '#ef4444'); }
            finally { exportInProgress = false; if (exportOL) exportOL.classList.remove('show'); }
        }

        function renderAnnotToCanvas(c, a, s) {
            if (!a.rect && a.type !== 'freehand') return; c.save(); const col = hex(a.color);
            switch (a.type) {
                case 'highlight': case 'comment': if (!a.rect) break; c.globalAlpha = .38; c.fillStyle = col; c.fillRect(a.rect.x * s, a.rect.y * s, a.rect.w * s, a.rect.h * s); break;
                case 'underline': if (!a.rect) break; c.globalAlpha = .9; c.fillStyle = col; const ut = Math.max(1.5, 2 * s); c.fillRect(a.rect.x * s, (a.rect.y + a.rect.h) * s - ut / 2, a.rect.w * s, ut); break;
                case 'strikethrough': if (!a.rect) break; c.globalAlpha = .9; c.fillStyle = col; const st2 = Math.max(1.5, 2 * s); c.fillRect(a.rect.x * s, (a.rect.y + a.rect.h * .5) * s - st2 / 2, a.rect.w * s, st2); break;
                case 'freehand': if (!a.path_points?.length) break; c.globalAlpha = .92; c.strokeStyle = col; c.lineWidth = (a.stroke_width || 2) * s; c.lineCap = 'round'; c.lineJoin = 'round'; c.beginPath(); c.moveTo(a.path_points[0][0] * s, a.path_points[0][1] * s); for (let i = 1; i < a.path_points.length; i++)c.lineTo(a.path_points[i][0] * s, a.path_points[i][1] * s); c.stroke(); break;
                case 'shape': if (!a.rect) break; const x = a.rect.x * s, y = a.rect.y * s, w = Math.max(4, a.rect.w * s), h = Math.max(4, a.rect.h * s), sw = (a.stroke_width || 2) * s; c.globalAlpha = 1; c.strokeStyle = col; c.lineWidth = sw; const st3 = a.shape_type || 'rect'; if (st3 === 'rect') { c.beginPath(); c.rect(x + sw / 2, y + sw / 2, w - sw, h - sw); c.stroke(); } else if (st3 === 'ellipse') { c.beginPath(); c.ellipse(x + w / 2, y + h / 2, w / 2 - sw / 2, h / 2 - sw / 2, 0, 0, Math.PI * 2); c.stroke(); } else if (st3 === 'line' || st3 === 'arrow') { c.beginPath(); c.moveTo(x + sw, y + h / 2); c.lineTo(x + w - sw, y + h / 2); c.stroke(); } break;
                case 'sticky': if (!a.rect || !a.comment) break; const sw2 = Math.max(130, 180 * s), sh2 = Math.max(60, 90 * s); c.globalAlpha = .92; c.fillStyle = col; c.beginPath(); if (c.roundRect) c.roundRect(a.rect.x * s, a.rect.y * s, sw2, sh2, 4); else c.rect(a.rect.x * s, a.rect.y * s, sw2, sh2); c.fill(); c.globalAlpha = 1; c.fillStyle = 'rgba(0,0,0,.75)'; const fs2 = Math.max(9, 11 * s); c.font = `${fs2}px sans-serif`; const words = a.comment.split(' '), lineH = fs2 * 1.4; let line = '', ly = a.rect.y * s + fs2 + 8; for (const w2 of words) { const test = line + w2 + ' '; if (c.measureText(test).width > sw2 - 12 && line !== '') { c.fillText(line, a.rect.x * s + 6, ly); line = w2 + ' '; ly += lineH; } else line = test; } c.fillText(line, a.rect.x * s + 6, ly); break;
            }
            c.restore();
        }

        document.getElementById('rpv-download-btn')?.addEventListener('click', exportPdfWithAnnotations);

        /* ── PDF RENDER ──────────────────────────── */
        function computeBase(page) { const cw = wrap.clientWidth || 800, nw = page.getViewport({ scale: 1 }).width; baseScale = Math.max(0.5, Math.min((cw - 24) / nw, 2.5)); }
        function prevPage() { if (pageNum > 1) { pageNum--; renderPage(pageNum); } }
        function nextPage() { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } }

        function renderPage(num) {
            if (num < 1 || (pdfDoc && num > pdfDoc.numPages)) return;
            if (pageRendering) { pendingPage = num; return; }
            pageRendering = true; pageNum = num;
            // Simpan sebagai last read
            saveLastRead(num);
            document.querySelectorAll('.rpv-popup').forEach(p => p.classList.remove('show'));
            tooltip.classList.remove('show'); pendingRect = null; pendingText = null; stickyPos = null;
            window.getSelection()?.removeAllRanges();
            pdfDoc.getPage(num).then(async page => {
                if (baseScale === 1.0) computeBase(page);
                const cssScale = baseScale * zoomFactor;
                const vpCss = page.getViewport({ scale: cssScale });
                const vpRender = page.getViewport({ scale: cssScale * DPR });
                mainCanvas.width = Math.floor(vpRender.width); mainCanvas.height = Math.floor(vpRender.height);
                mainCanvas.style.width = Math.floor(vpCss.width) + 'px'; mainCanvas.style.height = Math.floor(vpCss.height) + 'px';
                stage.style.width = Math.floor(vpCss.width) + 'px'; stage.style.height = Math.floor(vpCss.height) + 'px';
                await page.render({ canvasContext: ctx, viewport: vpRender }).promise.catch(e => console.warn(e.message));
                pageRendering = false;
                if (pendingPage !== null) { const p = pendingPage; pendingPage = null; renderPage(p); return; }
                /* Text layer */
                textLayer.innerHTML = ''; textLayer.style.width = Math.floor(vpCss.width) + 'px'; textLayer.style.height = Math.floor(vpCss.height) + 'px';
                const content = await page.getTextContent();
                content.items.forEach(item => {
                    if (!item.str || !item.str.trim()) return;
                    const tx = pdfjsLib.Util.transform(vpCss.transform, item.transform);
                    const fh = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]);
                    const angle = Math.atan2(tx[1], tx[0]);
                    const span = document.createElement('span');
                    span.textContent = item.str; span.style.fontSize = fh + 'px';
                    span.style.left = tx[4] + 'px'; span.style.top = (tx[5] - fh) + 'px';
                    span.style.transformOrigin = '0% 0%'; textLayer.appendChild(span);
                    const targetW = item.width * cssScale, measuredW = span.getBoundingClientRect().width;
                    let t = angle !== 0 ? `rotate(${-angle}rad)` : '';
                    if (measuredW > 1 && targetW > 0) t += ` scaleX(${targetW / measuredW})`;
                    if (t.trim()) span.style.transform = t.trim();
                });
                scheduleRender();
                stage.style.display = 'block'; loadingEl?.classList.add('hidden');
                const piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.value = num;
                const prevEl = document.getElementById('rpv-prev'); if (prevEl) prevEl.disabled = num <= 1;
                const nextEl = document.getElementById('rpv-next'); if (nextEl) nextEl.disabled = !pdfDoc || num >= pdfDoc.numPages;
                const pct = pdfDoc ? (num / pdfDoc.numPages * 100) : 0;
                const progEl = document.getElementById('rpv-progress'); if (progEl) progEl.style.width = pct + '%';
                const zvEl = document.getElementById('rpv-zoom-val'); if (zvEl) zvEl.textContent = Math.round(zoomFactor * 100) + '%';
                const ptEl = document.getElementById('rpv-page-total'); if (ptEl && pdfDoc) ptEl.textContent = pdfDoc.numPages;
                wrap.scrollTo({ top: 0, behavior: 'smooth' });
                // update sheet page display
                const spEl = document.getElementById('rpv-sheet-page'); if (spEl) spEl.textContent = num;
            }).catch(e => { console.error('[RPV] render error:', e); pageRendering = false; loadingEl?.classList.add('hidden'); stage.style.display = 'block'; });
        }

        /* ── NAVIGATION ──────────────────────────── */
        document.getElementById('rpv-prev')?.addEventListener('click', prevPage);
        document.getElementById('rpv-next')?.addEventListener('click', nextPage);
        document.getElementById('rpv-page-input')?.addEventListener('change', function () { const n = parseInt(this.value); if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) renderPage(n); else this.value = pageNum; });

        /* ── ZOOM ────────────────────────────────── */
        function doZoom(dir) { zoomFactor = dir > 0 ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX) : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN); baseScale = 1.0; if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { computeBase(p); renderPage(pageNum); }); }
        document.getElementById('rpv-zoom-in')?.addEventListener('click', () => doZoom(1));
        document.getElementById('rpv-zoom-out')?.addEventListener('click', () => doZoom(-1));

        /* ── KEYBOARD ────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') { e.preventDefault(); openSearch(); return; }
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); return; }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); return; }
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; return; }
            switch (e.key) {
                case 'ArrowLeft': prevPage(); break; case 'ArrowRight': nextPage(); break;
                case '+': case '=': doZoom(1); break; case '-': doZoom(-1); break;
                case 'f': case 'F': isFullscreen ? exitFullscreen() : enterFullscreen(); break;
                case 'Escape': if (document.getElementById('rpv-search')?.classList.contains('show')) closeSearch(); else if (isFullscreen) exitFullscreen(); break;
            }
        });

        /* ── RESIZE ──────────────────────────────── */
        let resT = null, lastW = wrap.clientWidth;
        window.addEventListener('resize', () => { const w = wrap.clientWidth; if (Math.abs(w - lastW) < 20) return; lastW = w; clearTimeout(resT); resT = setTimeout(() => { if (!pdfDoc) return; baseScale = 1.0; renderPage(pageNum); }, 250); });
        if (mainCanvas) new MutationObserver(() => syncFC()).observe(mainCanvas, { attributes: true, attributeFilter: ['width', 'height'] });

        /* ── LOAD PDF ────────────────────────────── */
        const task = pdfjsLib.getDocument({ url: CFG.pdfUrl, withCredentials: false, verbosity: 0, rangeChunkSize: 65536 });
        task.onProgress = d => { if (d.total > 0 && loadSub) loadSub.textContent = `Mengunduh... ${Math.round(d.loaded / d.total * 100)}%`; };
        task.promise.then(async doc => {
            pdfDoc = doc;
            const ptEl = document.getElementById('rpv-page-total'); if (ptEl) ptEl.textContent = doc.numPages;
            const piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.max = doc.numPages;
            renderPage(1);
            await loadAll();
            // Tampilkan resume toast jika ada halaman terakhir
            const saved = loadLastRead();
            if (saved > 1) setTimeout(() => showResumeTrigger(saved), 1000);
            console.log('[RPV] ready, reviewId=', CFG.reviewId);
        }).catch(err => { console.error('[RPV] PDF load error:', err); if (loadingEl) loadingEl.innerHTML = `<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:11px;">${err.message}</p>`; });

        setTool('highlight');
    }

    init();
})();
