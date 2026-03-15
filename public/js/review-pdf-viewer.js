/**
 * public/js/review-pdf-viewer.js  v5.1
 *
 * FIXES v5.1 (dari v5.0):
 *
 * BUG 1 — Search highlight tidak muncul
 *   .replace() callback pakai string literal bukan return statement
 *   FIX: return '<mark...>' + m + '</mark>'
 *
 * BUG 2 — Zoom & Resize loop / double-compute
 *   doZoom, enterFS, exitFS, resize handler semuanya set baseScale = 1
 *   yang men-trigger computeBase() DULU di renderPage() kemudian lagi
 *   di dalam .then(). FIX: pakai flag needsRecompute = true, bukan
 *   reset baseScale ke 1. computeBase() hanya jalan jika flag true.
 *
 * BUG 3 — shapePreviewSVG tidak di-clear saat ganti halaman
 *   FIX: clearShapePreview() + hapus elemen dari DOM di renderPage()
 *
 * BUG 4 — Sheet zoom val & sheet page tidak update
 *   FIX: doZoom update rpv-sheet-zoom-val; renderPage update rpv-sheet-page
 *
 * BUG 5 — boot() polling (penyebab loading terus jika CDN lambat)
 *   FIX: pakai dynamic <script> onload, tidak ada setTimeout loop
 *
 * BUG 6 — Guard double-init bisa stuck jika run() error sebelum selesai
 *   FIX: window[_gk] = true hanya di-set di dalam run() setelah DOM siap
 */
(function () {
    'use strict';

    /* ── Guard double-init ──────────────────────────────────────────
       Cek apakah rpv-stage masih ada di DOM.
       Jika TIDAK (Livewire rebuild) → reset guard dan re-init.
    ────────────────────────────────────────────────────────────── */
    var _gk = '_rpvA_' + ((window.RPV_CONFIG && window.RPV_CONFIG.reviewId) || 'x');
    if (window[_gk]) {
        if (document.getElementById('rpv-stage')) {
            console.log('[RPV] already running');
            return;
        }
        console.log('[RPV] Livewire rebuilt DOM, re-initializing');
        window[_gk] = false;
    }

    /* ── FIX BUG 5: Load pdf.js via onload, bukan polling setTimeout ─
       onload callback menjamin pdfjsLib siap sebelum run() dipanggil.
    ────────────────────────────────────────────────────────────── */
    var PDFJS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    var WORKER_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function boot() {
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_CDN;
            pdfjsLib.verbosity = 0;
            run();
        } else {
            var s = document.createElement('script');
            s.src = PDFJS_CDN;
            s.crossOrigin = 'anonymous';
            s.onload = function () {
                pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_CDN;
                pdfjsLib.verbosity = 0;
                run();
            };
            s.onerror = function () {
                var el = document.getElementById('rpv-loading');
                if (el) el.innerHTML = '<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat library PDF</p><p style="color:#6b7280;font-size:11px;margin:.25rem 0;">Periksa koneksi internet.</p><button type="button" onclick="window.location.reload()" style="margin-top:.75rem;padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
            };
            document.head.appendChild(s);
        }
    }
    boot();

    /* ════════════════════════════════════════════════════════════════
       MAIN
    ════════════════════════════════════════════════════════════════ */
    function run() {
        var CFG = window.RPV_CONFIG;
        if (!CFG || !CFG.pdfUrl) { console.error('[RPV] RPV_CONFIG missing'); return; }

        /* FIX BUG 6: set guard di sini, setelah DOM & config tervalidasi */
        window[_gk] = true;

        /* ── Colors ── */
        var COLORS = {
            yellow: '#FFD700', green: '#4ADE80', red: '#EF4444', blue: '#60A5FA',
            orange: '#FF6B18', black: '#111111', white: '#FFFFFF',
            pink: '#F472B6', purple: '#A78BFA', cyan: '#22D3EE'
        };
        function hex(n) { return COLORS[n] || '#FFD700'; }

        /* ── State ── */
        var CACHE_KEY = '_rpv_' + btoa(CFG.pdfUrl).slice(0, 30).replace(/[^a-z0-9]/gi, '_');
        var pdfDoc = window[CACHE_KEY] || null;
        var pageNum = 1, pageRendering = false, pendingPage = null;
        var baseScale = 1, zoomFactor = 1;
        /* FIX BUG 2: flag recompute, bukan reset baseScale = 1 */
        var needsRecompute = true;
        var ZOOM_MIN = 0.5, ZOOM_MAX = 4, ZOOM_STEP = 0.25;
        var DPR = window.devicePixelRatio || 1;
        var annots = [], undoStack = [], redoStack = [];
        var activeTool = 'highlight', activeColor = 'yellow', activeSize = 2, activeShape = 'rect';
        var isDrawing = false, drawStart = null, freePoints = [], shapePreviewEl = null;
        var pendingRect = null, pendingText = null, stickyPos = null;
        var selectedId = null, isPanning = false;
        var panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;
        var renderPending = false, syncTout = null, searchDebounce = null;
        var searchResults = [], searchIdx = -1, searchHLs = [], searchQuery = '';
        var isFullscreen = false, exportBusy = false;
        var SK = 'rpv_' + (CFG.reviewId || 'x');

        /* ── DOM ── */
        var outerWrap = document.getElementById('rpv-outer-wrap');
        var wrap = document.getElementById('rpv-canvas-wrap');
        var stage = document.getElementById('rpv-stage');
        var mainCanvas = document.getElementById('rpv-canvas');
        var ctx = mainCanvas.getContext('2d');
        var textLayer = document.getElementById('rpv-text-layer');
        var annotLayer = document.getElementById('rpv-annotation-layer');
        var freeCanvas = document.getElementById('rpv-freehand-canvas');
        var freeCtx = freeCanvas ? freeCanvas.getContext('2d') : null;
        var loadingEl = document.getElementById('rpv-loading');
        var loadSub = document.getElementById('rpv-load-sub');
        var tooltip = document.getElementById('rpv-tooltip');
        var syncEl = document.getElementById('rpv-sync');
        var syncTxt = document.getElementById('rpv-sync-txt');
        var eraserCur = document.getElementById('rpv-eraser-cursor');
        var exportOL = document.getElementById('rpv-export-overlay');

        if (freeCanvas) {
            freeCanvas.style.pointerEvents = 'none';
            freeCanvas.style.position = 'absolute';
            freeCanvas.style.inset = '0';
            freeCanvas.style.zIndex = '10';
        }

        /* ── Utils ── */
        function snack(msg, color) {
            color = color || '#FF6B18';
            var el = document.createElement('div');
            el.textContent = msg;
            el.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid ' + color + ';color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;';
            document.body.appendChild(el);
            setTimeout(function () { el.style.opacity = 0; setTimeout(function () { el.remove(); }, 400); }, 2200);
        }

        function showSync(msg, ok) {
            if (!syncEl) return;
            if (syncTxt) syncTxt.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#FF6B18';
            syncEl.style.color = ok ? '#22c55e' : '#FF6B18';
            syncEl.classList.add('show');
            clearTimeout(syncTout);
            syncTout = setTimeout(function () { syncEl.classList.remove('show'); }, ok ? 1800 : 4000);
        }

        function stageXY(e) {
            var r = stage.getBoundingClientRect();
            var s = (e.changedTouches && e.changedTouches[0]) || (e.touches && e.touches[0]) || e;
            return { x: s.clientX - r.left, y: s.clientY - r.top };
        }

        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }

        function syncFC() {
            if (!freeCanvas) return;
            var w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) {
                freeCanvas.width = w; freeCanvas.height = h;
            }
            freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
        }

        function csrf() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; }
        function hdrs() {
            return {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function saveLast(p) { try { localStorage.setItem(SK + '_last', p); } catch (e) { } }
        function loadLast() { try { return parseInt(localStorage.getItem(SK + '_last') || '1'); } catch (e) { return 1; } }
        function on(id, ev, fn) { var el = document.getElementById(id); if (el) el.addEventListener(ev, fn); }

        /* ── Sanitizer ── */
        var VT = ['highlight', 'underline', 'strikethrough', 'freehand', 'comment', 'sticky', 'shape'];
        var VC = ['yellow', 'green', 'red', 'blue', 'orange', 'black', 'white', 'pink', 'purple', 'cyan'];
        var VS = ['rect', 'ellipse', 'arrow', 'line'];

        function sanitize(raw) {
            var type = VT.includes(raw.type === 'brush' ? 'freehand' : raw.type) ? (raw.type === 'brush' ? 'freehand' : raw.type) : 'highlight';
            var color = VC.includes(raw.color) ? raw.color : 'yellow';
            var p = {
                page: parseInt(raw.page) || pageNum, type: type, color: color,
                rect_x: raw.rect ? raw.rect.x : (raw.rect_x || null),
                rect_y: raw.rect ? raw.rect.y : (raw.rect_y || null),
                rect_w: raw.rect ? raw.rect.w : (raw.rect_w || null),
                rect_h: raw.rect ? raw.rect.h : (raw.rect_h || null),
                selected_text: raw.selected_text || null,
                comment: raw.comment || null,
                path_points: Array.isArray(raw.path_points) ? raw.path_points : null,
                shape_type: VS.includes(raw.shape_type) ? raw.shape_type : null,
                stroke_width: (typeof raw.stroke_width === 'number' && raw.stroke_width > 0) ? raw.stroke_width : 2,
                fill_opacity: typeof raw.fill_opacity === 'number' ? raw.fill_opacity : 0,
                arrow_x1: typeof raw.arrow_x1 === 'number' ? raw.arrow_x1 : null,
                arrow_y1: typeof raw.arrow_y1 === 'number' ? raw.arrow_y1 : null,
                arrow_x2: typeof raw.arrow_x2 === 'number' ? raw.arrow_x2 : null,
                arrow_y2: typeof raw.arrow_y2 === 'number' ? raw.arrow_y2 : null,
            };
            if (p.type === 'shape' && !p.shape_type) p.shape_type = 'rect';
            return p;
        }

        /* ── API ── */
        var API = CFG.apiBase;

        async function apiLoad() {
            if (!API) return [];
            try {
                var r = await fetch(API, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) throw new Error(r.status);
                var j = await r.json();
                var rows = Array.isArray(j.data) ? j.data : [];
                return rows.map(function (a) {
                    if (!a.rect && a.rect_x != null) {
                        a.rect = { x: +a.rect_x, y: +a.rect_y, w: +a.rect_w, h: +a.rect_h };
                    }
                    if ((a.type === 'shape') && (a.shape_type === 'arrow' || a.shape_type === 'line')) {
                        if (a.arrow_x1 == null && Array.isArray(a.path_points) && a.path_points.length >= 2) {
                            a.arrow_x1 = +a.path_points[0][0]; a.arrow_y1 = +a.path_points[0][1];
                            a.arrow_x2 = +a.path_points[1][0]; a.arrow_y2 = +a.path_points[1][1];
                        }
                    }
                    return a;
                });
            } catch (e) { console.error('[RPV] load:', e); return []; }
        }

        async function apiSave(payload) {
            if (!API) { snack('⚠️ Simpan draft dulu!', '#F59E0B'); return null; }
            var clean = sanitize(payload);
            showSync('Menyimpan...');
            try {
                var r = await fetch(API, { method: 'POST', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(clean) });
                var j = await r.json();
                if (!r.ok) { showSync('Gagal: ' + (j.message || r.status)); return null; }
                showSync('Tersimpan ✓', true);
                var saved = j.data || null;
                if (saved && saved.type === 'shape' && (saved.shape_type === 'arrow' || saved.shape_type === 'line')) {
                    if (saved.arrow_x1 == null && Array.isArray(saved.path_points) && saved.path_points.length >= 2) {
                        saved.arrow_x1 = +saved.path_points[0][0]; saved.arrow_y1 = +saved.path_points[0][1];
                        saved.arrow_x2 = +saved.path_points[1][0]; saved.arrow_y2 = +saved.path_points[1][1];
                    }
                    if (saved.arrow_x1 == null && clean.arrow_x1 != null) {
                        saved.arrow_x1 = clean.arrow_x1; saved.arrow_y1 = clean.arrow_y1;
                        saved.arrow_x2 = clean.arrow_x2; saved.arrow_y2 = clean.arrow_y2;
                    }
                }
                return saved;
            } catch (e) { console.error('[RPV] save:', e); showSync('Error jaringan'); return null; }
        }

        async function apiPatch(id, payload) {
            if (!API) return;
            try { await fetch(API + '/' + id, { method: 'PUT', credentials: 'same-origin', headers: hdrs(), body: JSON.stringify(payload) }); }
            catch (e) { console.error('[RPV] patch:', e); }
        }

        async function apiDel(id) {
            if (!API) return;
            showSync('Menghapus...');
            try { await fetch(API + '/' + id, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() }); showSync('Dihapus ✓', true); }
            catch (e) { console.error('[RPV] del:', e); }
        }

        async function apiDelPage(pg) {
            if (!API) return;
            showSync('Membersihkan...');
            try { await fetch(API + '/page/' + pg, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() }); showSync('Selesai ✓', true); }
            catch (e) { console.error('[RPV] delPage:', e); }
        }

        async function loadAll() {
            annots = await apiLoad();
            console.log('[RPV] loaded', annots.length, 'annotations');
            scheduleRender(); updateBadge(); updateUndoRedo();
        }

        /* ── Search helpers ── */
        function clearSearchHL() {
            annotLayer.querySelectorAll('.rpvr-search-hl').forEach(function (e) { e.remove(); });
            searchHLs = [];
        }

        function applySearchHL() {
            clearSearchHL();
            if (!searchQuery || !pdfDoc) return;
            var q = searchQuery.toLowerCase();
            var sr = stage.getBoundingClientRect();
            Array.from(textLayer.querySelectorAll('span')).forEach(function (span) {
                if (!span.firstChild) return;
                var text = span.textContent, lower = text.toLowerCase(), idx = lower.indexOf(q);
                while (idx !== -1) {
                    try {
                        var range = document.createRange();
                        range.setStart(span.firstChild, idx);
                        range.setEnd(span.firstChild, Math.min(idx + q.length, text.length));
                        Array.from(range.getClientRects()).forEach(function (rect) {
                            if (rect.width < 1 || rect.height < 1) return;
                            var el = document.createElement('div');
                            el.className = 'rpvr-search-hl';
                            el.style.cssText = 'position:absolute;left:' + (rect.left - sr.left) + 'px;top:' + (rect.top - sr.top) + 'px;width:' + rect.width + 'px;height:' + rect.height + 'px;background:rgba(255,215,0,.45);border-radius:2px;pointer-events:none;z-index:7;transition:background .3s;';
                            annotLayer.appendChild(el);
                            searchHLs.push(el);
                        });
                    } catch (_) { }
                    idx = lower.indexOf(q, idx + 1);
                }
            });
            searchHLs.forEach(function (el, i) {
                el.style.background = i === searchIdx ? 'rgba(255,107,24,.75)' : 'rgba(255,215,0,.45)';
                el.style.outline = i === searchIdx ? '2px solid #FF6B18' : 'none';
            });
            if (searchHLs[searchIdx]) searchHLs[searchIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function flashHL(i) {
            if (!searchHLs[i]) return;
            searchHLs[i].style.background = 'rgba(255,107,24,.9)';
            searchHLs[i].style.outline = '2px solid #FF6B18';
            setTimeout(function () {
                if (!searchHLs[i]) return;
                searchHLs[i].style.background = 'rgba(255,215,0,.45)';
                searchHLs[i].style.outline = 'none';
            }, 1500);
        }

        /* ── FIX BUG 3: shapePreview cleanup ── */
        var shapePreviewSVG = null;

        function clearShapePreview() {
            if (shapePreviewSVG) {
                shapePreviewSVG.innerHTML = '';
                /* Jangan hapus dari DOM di sini — cukup kosongkan */
            }
        }

        function destroyShapePreview() {
            if (shapePreviewSVG) {
                if (shapePreviewSVG.parentNode) shapePreviewSVG.parentNode.removeChild(shapePreviewSVG);
                shapePreviewSVG = null;
            }
        }

        /* ── Render ── */
        function scheduleRender() {
            if (renderPending) return;
            renderPending = true;
            requestAnimationFrame(function () { renderPending = false; doRender(); });
        }

        function doRender() {
            var s = baseScale * zoomFactor;
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
            stage.querySelectorAll('.rpv-sticky-note').forEach(function (e) { e.remove(); });
            annots.filter(function (a) { return a.page === pageNum; }).forEach(function (a) {
                if (a.type === 'highlight' || a.type === 'comment') rHL(a, s);
                else if (a.type === 'underline') rUL(a, s);
                else if (a.type === 'strikethrough') rST(a, s);
                else if (a.type === 'freehand') rFH(a, s);
                else if (a.type === 'shape') rSH(a, s);
                else if (a.type === 'sticky') rSticky(a, s);
            });
            updateBadge();
            if (searchResults.length > 0 && searchQuery) applySearchHL();
        }

        /* ── Render helpers ── */
        function rHL(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'), sel = selectedId == a.id;
            el.dataset.annotId = String(a.id);
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + (a.rect.y * s) + 'px;width:' + (a.rect.w * s) + 'px;height:' + (a.rect.h * s) + 'px;background:' + hex(a.color) + ';opacity:' + (sel ? .75 : .38) + ';border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:' + (sel ? '2px solid #FF6B18' : 'none') + ';transition:opacity .15s;';
            if (a.type === 'comment' && a.comment) {
                var dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rUL(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'); el.dataset.annotId = String(a.id);
            var t = Math.max(1.5, 2 * s);
            var top = (a.rect.y + a.rect.h) * s - 1;
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + top + 'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) + ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rST(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'); el.dataset.annotId = String(a.id);
            var t = Math.max(1.5, 2 * s);
            var top = a.rect.y * s + a.rect.h * s * 0.62 - t / 2;
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + top + 'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) + ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rFH(a, s) {
            if (!a.path_points || !a.path_points.length || !freeCtx) return;
            var pts = a.path_points;
            freeCtx.save();
            freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width || 2) * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath(); freeCtx.moveTo(pts[0][0] * s, pts[0][1] * s);
            for (var i = 1; i < pts.length; i++) freeCtx.lineTo(pts[i][0] * s, pts[i][1] * s);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
                var hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
                hit.style.cssText = 'position:absolute;left:' + ((a.rect.x - 8) * s) + 'px;top:' + ((a.rect.y - 8) * s) + 'px;width:' + ((a.rect.w + 16) * s) + 'px;height:' + ((a.rect.h + 16) * s) + 'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
                attachEv(hit, a); annotLayer.appendChild(hit);
            }
        }

        function rSH(a, s) {
            if (!a.rect) return;
            var col = hex(a.color), sel = selectedId == a.id;
            var sw = Math.max(1, (a.stroke_width || 2) * s);
            var st = a.shape_type || 'rect';
            var el = document.createElement('div'); el.dataset.annotId = String(a.id);

            if (st === 'arrow' || st === 'line') {
                var ax1 = a.arrow_x1 != null ? a.arrow_x1 * s : a.rect.x * s;
                var ay1 = a.arrow_y1 != null ? a.arrow_y1 * s : (a.rect.y + a.rect.h / 2) * s;
                var ax2 = a.arrow_x2 != null ? a.arrow_x2 * s : (a.rect.x + a.rect.w) * s;
                var ay2 = a.arrow_y2 != null ? a.arrow_y2 * s : (a.rect.y + a.rect.h / 2) * s;
                var bx = Math.min(ax1, ax2) - sw * 2, by = Math.min(ay1, ay2) - sw * 2;
                var bw = Math.abs(ax2 - ax1) + sw * 4, bh = Math.abs(ay2 - ay1) + sw * 4;
                var lx1 = ax1 - bx, ly1 = ay1 - by, lx2 = ax2 - bx, ly2 = ay2 - by;
                el.style.cssText = 'position:absolute;left:' + bx + 'px;top:' + by + 'px;width:' + bw + 'px;height:' + bh + 'px;pointer-events:auto;cursor:pointer;z-index:5;outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                var svg = '';
                if (st === 'line') {
                    svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/>';
                } else {
                    var dx = lx2 - lx1, dy = ly2 - ly1, len = Math.sqrt(dx * dx + dy * dy);
                    if (len > 1) {
                        var headLen = Math.min(len * 0.35, Math.max(10, sw * 5));
                        var ang = Math.atan2(dy, dx);
                        var hx1 = lx2 - headLen * Math.cos(ang - Math.PI / 6), hy1 = ly2 - headLen * Math.sin(ang - Math.PI / 6);
                        var hx2 = lx2 - headLen * Math.cos(ang + Math.PI / 6), hy2 = ly2 - headLen * Math.sin(ang + Math.PI / 6);
                        svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/>'
                            + '<polyline points="' + hx1 + ',' + hy1 + ' ' + lx2 + ',' + ly2 + ' ' + hx2 + ',' + hy2 + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-linejoin="round"/>';
                    }
                }
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + bw + '" height="' + bh + '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            } else {
                var x = a.rect.x * s, y = a.rect.y * s, w = Math.max(4, a.rect.w * s), h = Math.max(4, a.rect.h * s);
                el.style.cssText = 'position:absolute;left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px;pointer-events:auto;cursor:pointer;z-index:5;outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                var svg = '';
                if (st === 'rect')
                    svg = '<rect x="' + (sw / 2) + '" y="' + (sw / 2) + '" width="' + Math.max(1, w - sw) + '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col + '" stroke-width="' + sw + '"/>';
                else if (st === 'ellipse')
                    svg = '<ellipse cx="' + (w / 2) + '" cy="' + (h / 2) + '" rx="' + Math.max(1, w / 2 - sw / 2) + '" ry="' + Math.max(1, h / 2 - sw / 2) + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '"/>';
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h + '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rSticky(a, s) {
            if (!a.rect) return;
            var note = document.createElement('div');
            note.className = 'rpv-sticky-note'; note.dataset.annotId = String(a.id); note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * s) + 'px'; note.style.top = (a.rect.y * s) + 'px';
            note.innerHTML = '<div class="rpv-sn-header"><span>📌</span><div style="display:flex;gap:3px;"><button type="button" class="rpv-sn-edit" style="background:none;border:none;cursor:pointer;font-size:12px;padding:0 2px;" title="Edit">✏️</button><button type="button" class="rpv-sn-del" style="background:none;border:none;cursor:pointer;font-size:14px;color:rgba(0,0,0,.5);padding:0 2px;line-height:1;" title="Hapus">×</button></div></div><div class="rpv-sn-body">' + esc(a.comment) + '</div>';
            note.querySelector('.rpv-sn-del').addEventListener('click', function (ev) { ev.stopPropagation(); stickyRemoveAnim(note, a.id); });
            note.querySelector('.rpv-sn-edit').addEventListener('click', function (ev) { ev.stopPropagation(); openEditPopup(a); });
            note.addEventListener('click', function (ev) {
                if (activeTool === 'eraser') { ev.stopPropagation(); stickyRemoveAnim(note, a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, s); stage.appendChild(note);
        }

        function stickyRemoveAnim(el, id) {
            el.style.transition = 'opacity .18s,transform .18s'; el.style.opacity = '0'; el.style.transform = 'scale(.85)';
            setTimeout(async function () { el.remove(); await removeAnnot(id); }, 180);
        }

        function attachEv(el, a) {
            el.addEventListener('click', function (ev) {
                ev.stopPropagation();
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, ev.clientX, ev.clientY);
            });
            el.addEventListener('touchend', function (ev) {
                ev.stopPropagation(); if (ev.cancelable) ev.preventDefault();
                var t = ev.changedTouches[0];
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, t.clientX, t.clientY);
            }, { passive: false });
        }

        function makeDraggable(el, annotData, s) {
            var ox = 0, oy = 0, drag = false, moved = false;
            function dn(e) {
                if (['rpv-sn-del', 'rpv-sn-edit', 'rpv-sn-body'].some(function (c) { return e.target.classList.contains(c); })) return;
                drag = true; moved = false;
                var src = e.touches ? e.touches[0] : e;
                ox = src.clientX - el.offsetLeft; oy = src.clientY - el.offsetTop;
                el.style.zIndex = '20'; e.stopPropagation(); if (e.cancelable) e.preventDefault();
            }
            function mv(e) {
                if (!drag) return; moved = true;
                var src = e.touches ? e.touches[0] : e;
                el.style.left = (src.clientX - ox) + 'px'; el.style.top = (src.clientY - oy) + 'px';
                if (e.cancelable) e.preventDefault();
            }
            async function up() {
                if (!drag) return; drag = false; el.style.zIndex = '9'; if (!moved) return;
                var nx = parseFloat(el.style.left) / s, ny = parseFloat(el.style.top) / s;
                var idx = annots.findIndex(function (a) { return String(a.id) === String(annotData.id); });
                if (idx >= 0 && annots[idx].rect) { annots[idx].rect.x = nx; annots[idx].rect.y = ny; }
                await apiPatch(annotData.id, { rect_x: nx, rect_y: ny, rect_w: annotData.rect ? annotData.rect.w : 180, rect_h: annotData.rect ? annotData.rect.h : 90 });
            }
            el.addEventListener('mousedown', dn, { passive: false }); el.addEventListener('touchstart', dn, { passive: false });
            document.addEventListener('mousemove', mv, { passive: false }); document.addEventListener('touchmove', mv, { passive: false });
            document.addEventListener('mouseup', up); document.addEventListener('touchend', up);
        }

        /* ── Tooltip ── */
        function showTip(a, cx, cy) {
            var ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌' };
            var txt = a.comment ? ic[a.type] + ' ' + a.comment.substring(0, 80) : a.selected_text ? ic[a.type] + ' "' + a.selected_text.substring(0, 60) + '"' : ic[a.type] + ' ' + a.type;
            var tipTxt = document.getElementById('rpv-tip-text');
            if (tipTxt) { tipTxt.textContent = txt; tipTxt.dataset.annotId = String(a.id); }
            var editBtn = document.getElementById('rpv-tip-edit');
            if (editBtn) { editBtn.style.display = ['comment', 'sticky'].includes(a.type) ? '' : 'none'; editBtn.dataset.annotId = String(a.id); }
            tooltip.classList.add('show');
            var vw = window.innerWidth, vh = window.innerHeight;
            tooltip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
            tooltip.style.top = ((cy + 140 > vh) ? Math.max(4, cy - 140) : cy + 8) + 'px';
        }

        on('rpv-tip-close', 'click', function () { tooltip.classList.remove('show'); });
        on('rpv-tip-del', 'click', async function () {
            var id = document.getElementById('rpv-tip-text') && document.getElementById('rpv-tip-text').dataset.annotId;
            tooltip.classList.remove('show'); if (id) await removeAnnot(id);
        });
        on('rpv-tip-edit', 'click', function () {
            var id = document.getElementById('rpv-tip-edit') && document.getElementById('rpv-tip-edit').dataset.annotId;
            tooltip.classList.remove('show');
            if (id) { var a = annots.find(function (x) { return String(x.id) === id; }); if (a) openEditPopup(a); }
        });
        document.addEventListener('click', function (e) {
            if (tooltip && tooltip.classList.contains('show')) {
                if (tooltip.contains(e.target)) return;
                if (e.target.closest('[data-annot-id],.rpv-sticky-note')) return;
                tooltip.classList.remove('show');
            }
        });

        /* ── Edit popup ── */
        function openEditPopup(a) {
            var pop = document.getElementById('rpv-edit-popup');
            if (!pop) {
                pop = document.createElement('div'); pop.id = 'rpv-edit-popup';
                pop.style.cssText = 'position:fixed;z-index:20000;background:#1a1a1a;border:2px solid #FF6B18;border-radius:14px;padding:.875rem;width:300px;box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
                pop.innerHTML = '<p style="font-size:12px;font-weight:700;color:#FF6B18;margin:0 0 .5rem;">✏️ Edit Anotasi</p>'
                    + '<textarea id="rpv-edit-txt" style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:80px;display:block;box-sizing:border-box;"></textarea>'
                    + '<div style="display:flex;gap:.4rem;margin-top:.5rem;">'
                    + '<button type="button" id="rpv-edit-save" style="flex:1;padding:.5rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Simpan</button>'
                    + '<button type="button" id="rpv-edit-cancel" style="padding:.5rem .75rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>'
                    + '</div>';
                document.body.appendChild(pop);
                document.getElementById('rpv-edit-cancel').addEventListener('click', function () { pop.style.display = 'none'; });
                document.addEventListener('click', function (e) {
                    if (pop.style.display !== 'none' && !pop.contains(e.target) && !e.target.closest('#rpv-tooltip')) pop.style.display = 'none';
                });
            }
            var txt = document.getElementById('rpv-edit-txt');
            txt.value = a.comment || '';
            pop.style.left = Math.max(4, Math.min(window.innerWidth / 2 - 150, window.innerWidth - 304)) + 'px';
            pop.style.top = Math.max(4, window.innerHeight / 2 - 85) + 'px';
            pop.style.display = 'block';
            setTimeout(function () { txt.focus(); txt.select(); }, 40);
            var oldBtn = document.getElementById('rpv-edit-save');
            var newBtn = oldBtn.cloneNode(true); oldBtn.parentNode.replaceChild(newBtn, oldBtn);
            newBtn.addEventListener('click', async function () {
                var v = txt.value.trim(); if (!v) { snack('Tidak boleh kosong!'); return; }
                pop.style.display = 'none';
                await apiPatch(a.id, { comment: v });
                var idx = annots.findIndex(function (x) { return String(x.id) === String(a.id); });
                if (idx >= 0) annots[idx].comment = v;
                scheduleRender(); snack('✓ Diperbarui', '#22c55e');
            });
        }

        /* ── Add / Remove ── */
        async function addAnnot(payload) {
            var saved = await apiSave(payload); if (!saved) return null;
            if (payload.arrow_x1 != null && saved.arrow_x1 == null) {
                saved.arrow_x1 = payload.arrow_x1; saved.arrow_y1 = payload.arrow_y1;
                saved.arrow_x2 = payload.arrow_x2; saved.arrow_y2 = payload.arrow_y2;
            }
            if (!saved.rect && saved.rect_x != null) {
                saved.rect = { x: +saved.rect_x, y: +saved.rect_y, w: +saved.rect_w, h: +saved.rect_h };
            }
            annots.push(saved);
            undoStack.push({ action: 'add', data: saved }); redoStack = [];
            updateUndoRedo(); scheduleRender(); return saved;
        }

        async function removeAnnot(id) {
            var a = annots.find(function (x) { return String(x.id) === String(id); }); if (!a) return;
            await apiDel(a.id);
            annots = annots.filter(function (x) { return String(x.id) !== String(id); });
            if (selectedId === String(id)) selectedId = null;
            undoStack.push({ action: 'del', data: a }); redoStack = [];
            updateUndoRedo(); scheduleRender(); snack('🗑 Dihapus');
        }

        /* ── Undo / Redo ── */
        function updateUndoRedo() {
            var u = document.getElementById('rpv-undo'); if (u) u.disabled = !undoStack.length;
            var r = document.getElementById('rpv-redo'); if (r) r.disabled = !redoStack.length;
        }
        async function doUndo() {
            if (!undoStack.length) return;
            var op = undoStack.pop();
            if (op.action === 'add') { var a = annots.find(function (x) { return String(x.id) === String(op.data.id); }); if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); redoStack.push({ action: 'readd', data: a }); } }
            else if (op.action === 'del') { var saved = await apiSave(op.data); if (saved) { annots.push(saved); redoStack.push({ action: 'redel', data: saved }); } }
            updateUndoRedo(); scheduleRender();
        }
        async function doRedo() {
            if (!redoStack.length) return;
            var op = redoStack.pop();
            if (op.action === 'readd') { var saved = await apiSave(op.data); if (saved) { annots.push(saved); undoStack.push({ action: 'add', data: saved }); } }
            else if (op.action === 'redel') { var a = annots.find(function (x) { return String(x.id) === String(op.data.id); }); if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); undoStack.push({ action: 'del', data: a }); } }
            updateUndoRedo(); scheduleRender();
        }
        on('rpv-undo', 'click', doUndo);
        on('rpv-redo', 'click', doRedo);

        /* ── Badge & Panel ── */
        function updateBadge() {
            var n = annots.length, badge = document.getElementById('rpv-badge');
            if (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.classList.toggle('show', n > 0); }
        }
        on('rpv-panel-btn', 'click', function (e) { e.stopPropagation(); document.getElementById('rpv-panel') && document.getElementById('rpv-panel').classList.toggle('open'); buildPanel(); });
        on('rpv-panel-close', 'click', function () { document.getElementById('rpv-panel') && document.getElementById('rpv-panel').classList.remove('open'); });
        on('rpv-panel-clear', 'click', async function () {
            if (!confirm('Hapus semua anotasi di halaman ' + pageNum + '?')) return;
            await apiDelPage(pageNum); annots = annots.filter(function (a) { return a.page !== pageNum; });
            undoStack = []; redoStack = []; updateUndoRedo(); scheduleRender(); buildPanel(); snack('🗑 Halaman ' + pageNum + ' dibersihkan');
        });

        function buildPanel() {
            var list = document.getElementById('rpv-panel-list'); if (!list) return;
            if (!annots.length) { list.innerHTML = '<div class="rpv-panel-empty">Belum ada anotasi.</div>'; return; }
            list.innerHTML = '';
            var ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌' };
            annots.slice().sort(function (a, b) { return a.page - b.page || a.id - b.id; }).forEach(function (a) {
                var el = document.createElement('div'); el.className = 'rpv-panel-item';
                el.innerHTML = '<div class="rpv-panel-dot" style="background:' + hex(a.color) + '"></div><div class="rpv-panel-body"><span class="rpv-panel-type">' + (ic[a.type] || '•') + ' ' + a.type + '</span><span class="rpv-panel-pg">Hal.' + a.page + '</span><div class="rpv-panel-text">' + esc(a.comment || a.selected_text || a.shape_type || '—') + '</div></div><div style="display:flex;gap:2px;flex-shrink:0;"><button type="button" data-pe="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:11px;padding:2px 3px;">✏️</button><button type="button" data-pd="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:12px;padding:2px 3px;">🗑</button></div>';
                el.querySelector('[data-pd="' + a.id + '"]').addEventListener('click', async function (ev) { ev.stopPropagation(); await removeAnnot(a.id); buildPanel(); });
                el.querySelector('[data-pe="' + a.id + '"]').addEventListener('click', function (ev) { ev.stopPropagation(); openEditPopup(a); });
                el.addEventListener('click', function () { if (a.page !== pageNum) renderPage(a.page); document.getElementById('rpv-panel') && document.getElementById('rpv-panel').classList.remove('open'); });
                list.appendChild(el);
            });
        }

        /* ── Tool management ── */
        function setTool(tool) {
            activeTool = tool;
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode');
            if (tool === 'freehand' || tool === 'brush') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            var needsSel = ['highlight', 'comment', 'underline', 'strikethrough'].includes(tool);
            textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
            textLayer.style.userSelect = needsSel ? 'text' : 'none';
            textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
            if (freeCanvas) freeCanvas.style.pointerEvents = ['freehand', 'brush', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur) eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }
            var LABELS = { pan: '🖐 Hand', select: '↖ Pilih', highlight: '✏️ Highlight', underline: '__ Underline', strikethrough: '~~ Strikethrough', comment: '💬 Komentar', freehand: '🖊 Pen', brush: '🖌️ Brush', shape: '⬛ Shape', eraser: '🧹 Hapus', sticky: '📌 Sticky' };
            var lbl = document.getElementById('rpv-active-label'); if (lbl) lbl.textContent = LABELS[tool] || tool;
            var sz = document.getElementById('rpv-sizes'); if (sz) sz.style.display = ['freehand', 'brush', 'shape'].includes(tool) ? 'flex' : 'none';
            var sh = document.getElementById('rpv-shapes'); if (sh) sh.classList.toggle('show', tool === 'shape');
        }

        document.querySelectorAll('.rpv-tool[data-tool]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.rpv-tool[data-tool]').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active'); setTool(btn.dataset.tool);
            });
        });
        document.querySelectorAll('.rpv-color').forEach(function (sw) {
            sw.addEventListener('click', function () { document.querySelectorAll('.rpv-color').forEach(function (s) { s.classList.remove('selected'); }); sw.classList.add('selected'); activeColor = sw.dataset.color; });
        });
        document.querySelectorAll('.rpv-size').forEach(function (d) {
            d.addEventListener('click', function () { document.querySelectorAll('.rpv-size').forEach(function (x) { x.classList.remove('selected'); }); d.classList.add('selected'); activeSize = +d.dataset.size; });
        });
        document.querySelectorAll('.rpv-shape').forEach(function (b) {
            b.addEventListener('click', function () { document.querySelectorAll('.rpv-shape').forEach(function (x) { x.classList.remove('active'); }); b.classList.add('active'); activeShape = b.dataset.shape; });
        });

        /* ── Text selection ── */
        function getSelInfo() {
            var sel = window.getSelection(); if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            var range = sel.getRangeAt(0); if (!textLayer || !textLayer.contains(range.commonAncestorContainer)) return null;
            var sr = stage.getBoundingClientRect(), s = baseScale * zoomFactor;
            var rects = Array.from(range.getClientRects()).filter(function (r) { return r.width > .5 && r.height > .5; });
            if (!rects.length) return null;
            var L = Math.min.apply(null, rects.map(function (r) { return r.left; }));
            var T = Math.min.apply(null, rects.map(function (r) { return r.top; }));
            var R = Math.max.apply(null, rects.map(function (r) { return r.right; }));
            var B = Math.max.apply(null, rects.map(function (r) { return r.bottom; }));
            return {
                rect: { x: (L - sr.left) / s, y: (T - sr.top) / s, w: (R - L) / s, h: (B - T) / s },
                text: sel.toString().substring(0, 1000),
                br: range.getBoundingClientRect()
            };
        }

        var selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest && e.target.closest('.rpv-popup,#rpv-annot-bar,#rpv-panel,#rpv-edit-popup')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(async function () {
                var info = getSelInfo(); if (!info || info.rect.w < 2) return;
                var base = { page: pageNum, color: activeColor, rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h, selected_text: info.text };
                if (activeTool === 'highlight') {
                    await addAnnot(Object.assign({ type: 'highlight' }, base)); window.getSelection() && window.getSelection().removeAllRanges(); snack('✏️ Highlight!');
                } else if (activeTool === 'underline') {
                    await addAnnot(Object.assign({ type: 'underline' }, base)); window.getSelection() && window.getSelection().removeAllRanges(); snack('__ Underline!');
                } else if (activeTool === 'strikethrough') {
                    await addAnnot(Object.assign({ type: 'strikethrough' }, base)); window.getSelection() && window.getSelection().removeAllRanges(); snack('~~ Strikethrough!');
                } else if (activeTool === 'comment') {
                    pendingRect = info.rect; pendingText = info.text;
                    var pop = document.getElementById('rpv-comment-pop');
                    if (pop) {
                        var vw = window.innerWidth, vh = window.innerHeight, pw = 284, ph = 170;
                        pop.style.left = Math.max(4, Math.min(info.br.left - pw / 2, vw - pw - 4)) + 'px';
                        pop.style.top = Math.max(4, info.br.bottom + ph > vh ? info.br.top - ph - 8 : info.br.bottom + 8) + 'px';
                        pop.classList.add('show');
                        var t = document.getElementById('rpv-comment-txt');
                        if (t) { t.value = ''; setTimeout(function () { t.focus(); }, 50); }
                    }
                }
            }, 80);
        }

        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', function (e) {
            if (!['highlight', 'comment', 'underline', 'strikethrough'].includes(activeTool)) return;
            onSelEnd(e);
        }, { passive: true });

        on('rpv-comment-save', 'click', async function () {
            var txtEl = document.getElementById('rpv-comment-txt');
            var txt = txtEl ? txtEl.value.trim() : '';
            if (!txt) { snack('Tulis komentar dulu!'); return; }
            if (!pendingRect) { snack('Pilih teks dulu!'); return; }
            var rect = { x: pendingRect.x, y: pendingRect.y, w: pendingRect.w, h: pendingRect.h };
            var selTxt = pendingText;
            if (txtEl) txtEl.value = '';
            var pop = document.getElementById('rpv-comment-pop'); if (pop) pop.classList.remove('show');
            pendingRect = null; pendingText = null;
            await addAnnot({ page: pageNum, type: 'comment', color: activeColor, rect_x: rect.x, rect_y: rect.y, rect_w: rect.w, rect_h: rect.h, selected_text: selTxt || '', comment: txt });
            window.getSelection() && window.getSelection().removeAllRanges(); snack('💬 Komentar disimpan!');
        });
        on('rpv-comment-cancel', 'click', function () {
            var pop = document.getElementById('rpv-comment-pop'); if (pop) pop.classList.remove('show');
            pendingRect = null; pendingText = null; window.getSelection() && window.getSelection().removeAllRanges();
        });

        on('rpv-sticky-save', 'click', async function () {
            var txtEl = document.getElementById('rpv-sticky-txt');
            var txt = txtEl ? txtEl.value.trim() : '';
            if (!txt) { snack('Tulis catatan dulu!'); return; }
            if (!stickyPos) { snack('Klik area PDF dulu!'); return; }
            var pos = { x: stickyPos.x, y: stickyPos.y };
            if (txtEl) txtEl.value = '';
            var pop = document.getElementById('rpv-sticky-pop'); if (pop) pop.classList.remove('show');
            stickyPos = null;
            await addAnnot({ page: pageNum, type: 'sticky', color: activeColor, rect_x: pos.x, rect_y: pos.y, rect_w: 180, rect_h: 90, comment: txt });
            snack('📌 Sticky note ditempel!');
        });
        on('rpv-sticky-cancel', 'click', function () {
            var pop = document.getElementById('rpv-sticky-pop'); if (pop) pop.classList.remove('show');
            stickyPos = null;
        });

        /* ── Freehand / Brush ── */
        function getFHSize() { return activeTool === 'brush' ? Math.max(6, activeSize * 3.5) : activeSize; }
        function getFHAlpha() { return activeTool === 'brush' ? .5 : .92; }

        function fhStart(e) { if (activeTool !== 'freehand' && activeTool !== 'brush') return; if (e.cancelable) e.preventDefault(); isDrawing = true; freePoints = []; var p = stageXY(e), s = baseScale * zoomFactor; freePoints.push([p.x / s, p.y / s]); }
        function fhMove(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return; if (e.cancelable) e.preventDefault();
            var p = stageXY(e), s = baseScale * zoomFactor; freePoints.push([p.x / s, p.y / s]);
            if (!freeCtx || freePoints.length < 2) return;
            var last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1];
            freeCtx.save(); freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = getFHSize() * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = getFHAlpha();
            freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s);
            freeCtx.stroke(); freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return; if (e.cancelable) e.preventDefault(); isDrawing = false;
            if (freePoints.length < 2) return;
            var xs = freePoints.map(function (p) { return p[0]; }), ys = freePoints.map(function (p) { return p[1]; });
            var bx = Math.min.apply(null, xs), by = Math.min.apply(null, ys);
            await addAnnot({ page: pageNum, type: 'freehand', color: activeColor, stroke_width: getFHSize(), path_points: freePoints, rect_x: bx, rect_y: by, rect_w: Math.max.apply(null, xs) - bx, rect_h: Math.max.apply(null, ys) - by });
        }

        /* ── Shape ── */
        function shapePreviewSVGEl() {
            if (!shapePreviewSVG) {
                shapePreviewSVG = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                shapePreviewSVG.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:26;overflow:visible;';
                shapePreviewSVG.setAttribute('width', stage.offsetWidth);
                shapePreviewSVG.setAttribute('height', stage.offsetHeight);
                stage.appendChild(shapePreviewSVG);
            }
            return shapePreviewSVG;
        }

        function updateShapePreview(x1, y1, x2, y2) {
            var svg = shapePreviewSVGEl();
            var col = hex(activeColor), sw = Math.max(1, activeSize);
            var w = Math.abs(x2 - x1), h = Math.abs(y2 - y1);
            var minX = Math.min(x1, x2), minY = Math.min(y1, y2);
            var st = activeShape, inner = '';
            if (st === 'rect') {
                inner = '<rect x="' + (minX + sw / 2) + '" y="' + (minY + sw / 2) + '" width="' + Math.max(1, w - sw) + '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            } else if (st === 'ellipse') {
                inner = '<ellipse cx="' + (minX + w / 2) + '" cy="' + (minY + h / 2) + '" rx="' + Math.max(1, w / 2 - sw / 2) + '" ry="' + Math.max(1, h / 2 - sw / 2) + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            } else if (st === 'line') {
                inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/>';
            } else if (st === 'arrow') {
                var dx = x2 - x1, dy = y2 - y1, len = Math.sqrt(dx * dx + dy * dy);
                if (len < 4) { svg.innerHTML = ''; return; }
                var headLen = Math.min(len * 0.35, Math.max(12, sw * 5));
                var angle = Math.atan2(dy, dx);
                var ax1 = x2 - headLen * Math.cos(angle - Math.PI / 6), ay1 = y2 - headLen * Math.sin(angle - Math.PI / 6);
                var ax2 = x2 - headLen * Math.cos(angle + Math.PI / 6), ay2 = y2 - headLen * Math.sin(angle + Math.PI / 6);
                inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/>'
                    + '<polyline points="' + ax1 + ',' + ay1 + ' ' + x2 + ',' + y2 + ' ' + ax2 + ',' + ay2 + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-linejoin="round"/>';
            }
            svg.innerHTML = inner;
        }

        var shDrawX1 = 0, shDrawY1 = 0;

        function shStart(e) {
            if (activeTool !== 'shape') return; if (e.cancelable) e.preventDefault();
            isDrawing = true; var p = stageXY(e); drawStart = p; shDrawX1 = p.x; shDrawY1 = p.y;
            shapePreviewSVGEl();
        }
        function shMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !drawStart) return; if (e.cancelable) e.preventDefault();
            var c = stageXY(e); updateShapePreview(shDrawX1, shDrawY1, c.x, c.y);
        }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return; if (e.cancelable) e.preventDefault();
            isDrawing = false; clearShapePreview();
            var c = stageXY(e), s = baseScale * zoomFactor; if (!drawStart) return;
            var x1 = shDrawX1 / s, y1 = shDrawY1 / s, x2 = c.x / s, y2 = c.y / s;
            drawStart = null;
            if (Math.abs(x2 - x1) < 2 && Math.abs(y2 - y1) < 2) return;
            var rx = Math.min(x1, x2), ry = Math.min(y1, y2), rw = Math.abs(x2 - x1), rh = Math.abs(y2 - y1);
            await addAnnot({
                page: pageNum, type: 'shape', color: activeColor, shape_type: activeShape,
                stroke_width: activeSize, rect_x: rx, rect_y: ry, rect_w: rw, rect_h: rh,
                path_points: [[x1, y1], [x2, y2]],
                arrow_x1: x1, arrow_y1: y1, arrow_x2: x2, arrow_y2: y2,
            });
        }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', function (e) { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('mousemove', function (e) { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('mouseup', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('mouseleave', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('touchstart', function (e) { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('touchmove', function (e) { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('touchend', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
        }

        /* ── Eraser cursor ── */
        document.addEventListener('mousemove', function (e) {
            if (!eraserCur) return;
            eraserCur.style.display = activeTool === 'eraser' ? 'block' : 'none';
            if (activeTool === 'eraser') { eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; }
        });

        /* ── Stage click (sticky) ── */
        stage.addEventListener('click', function (e) {
            if (e.target === freeCanvas) return;
            var hit = e.target.closest && (e.target.closest('[data-annot-id]') || e.target.closest('.rpv-sticky-note'));
            if (activeTool === 'sticky') {
                if (hit || (e.target.closest && e.target.closest('.rpv-popup'))) return;
                var p = stageXY(e), s = baseScale * zoomFactor;
                stickyPos = { x: p.x / s, y: p.y / s };
                var pop = document.getElementById('rpv-sticky-pop');
                if (pop) {
                    var vw = window.innerWidth, vh = window.innerHeight, pw = 280, ph = 150;
                    pop.style.left = Math.max(4, Math.min(e.clientX - pw / 2, vw - pw - 4)) + 'px';
                    pop.style.top = Math.max(4, e.clientY + ph > vh ? e.clientY - ph - 8 : e.clientY + 8) + 'px';
                    pop.classList.add('show');
                    var t = document.getElementById('rpv-sticky-txt');
                    if (t) { t.value = ''; setTimeout(function () { t.focus(); }, 50); }
                }
                return;
            }
            if (activeTool === 'select' && !hit) { selectedId = null; scheduleRender(); return; }
            if (activeTool === 'eraser' && !hit) { snack('Klik anotasi untuk menghapus', '#60A5FA'); return; }
        });

        /* ── Pan ── */
        stage.addEventListener('mousedown', function (e) { if (activeTool !== 'pan') return; isPanning = true; panSX = e.clientX; panSY = e.clientY; panScrollX = wrap ? wrap.scrollLeft : 0; panScrollY = wrap ? wrap.scrollTop : 0; if (e.cancelable) e.preventDefault(); }, { passive: false });
        document.addEventListener('mousemove', function (e) { if (!isPanning || activeTool !== 'pan') return; if (wrap) { wrap.scrollLeft = panScrollX + (panSX - e.clientX); wrap.scrollTop = panScrollY + (panSY - e.clientY); } });
        document.addEventListener('mouseup', function () { isPanning = false; });

        var lpd = 0;
        if (wrap) {
            wrap.addEventListener('touchstart', function (e) { if (e.touches.length === 2) lpd = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); }, { passive: true });
            wrap.addEventListener('touchmove', function (e) { if (e.touches.length !== 2) return; var d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); if (Math.abs(d - lpd) > 14) { d > lpd ? doZoom(1) : doZoom(-1); lpd = d; } }, { passive: true });
        }
        var swX = 0, swY = 0;
        if (wrap) {
            wrap.addEventListener('touchstart', function (e) { if (e.touches.length === 1) { swX = e.touches[0].clientX; swY = e.touches[0].clientY; } }, { passive: true });
            wrap.addEventListener('touchend', function (e) { if (e.changedTouches.length !== 1) return; var dx = swX - e.changedTouches[0].clientX, dy = swY - e.changedTouches[0].clientY; if (Math.abs(dx) > Math.abs(dy) * 1.8 && Math.abs(dx) > 60) { if (['freehand', 'brush', 'shape', 'pan'].includes(activeTool)) return; dx > 0 ? nextPage() : prevPage(); } }, { passive: true });
        }

        /* ── Fullscreen ── */
        function updateFsBtn() {
            var btn = document.getElementById('rpv-fs-btn'); if (!btn) return;
            btn.innerHTML = isFullscreen
                ? '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Keluar</span>'
                : '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg><span>Layar Penuh</span>';
        }

        /* FIX BUG 2: enterFS/exitFS pakai needsRecompute, bukan baseScale = 1 */
        function enterFS() {
            isFullscreen = true; if (outerWrap) outerWrap.classList.add('is-fullscreen');
            document.body.style.overflow = 'hidden'; updateFsBtn();
            needsRecompute = true; renderPage(pageNum);
        }
        function exitFS() {
            isFullscreen = false; if (outerWrap) outerWrap.classList.remove('is-fullscreen');
            document.body.style.overflow = ''; updateFsBtn();
            needsRecompute = true; renderPage(pageNum);
        }
        on('rpv-fs-btn', 'click', function () { isFullscreen ? exitFS() : enterFS(); });

        /* ── Resume toast ── */
        function showResume(savedPage) {
            if (savedPage <= 1 || !pdfDoc || savedPage > pdfDoc.numPages) return;
            var t = document.getElementById('rpv-resume-toast');
            if (!t) {
                t = document.createElement('div'); t.id = 'rpv-resume-toast';
                t.style.cssText = 'position:fixed;bottom:5rem;left:50%;transform:translateX(-50%) translateY(80px);background:#1a1a1a;border:1.5px solid #FF6B18;color:#fff;padding:.6rem .875rem;border-radius:14px;font-size:13px;z-index:20010;display:flex;align-items:center;gap:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.5);opacity:0;transition:all .4s;pointer-events:none;white-space:nowrap;';
                t.innerHTML = '<span style="font-size:1.2rem;">🔖</span><div><p style="font-weight:700;margin:0;font-size:12px;">Lanjut membaca?</p><p style="color:#9ca3af;margin:0;font-size:11px;" id="rpv-resume-txt">Hal. ' + savedPage + '</p></div><button type="button" id="rpv-resume-yes" style="padding:.3rem .7rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;pointer-events:auto;">Lanjut</button><button type="button" id="rpv-resume-no" style="padding:.3rem .6rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:11px;cursor:pointer;pointer-events:auto;">Awal</button>';
                document.body.appendChild(t);
            }
            var rt = document.getElementById('rpv-resume-txt'); if (rt) rt.textContent = 'Terakhir di halaman ' + savedPage;
            requestAnimationFrame(function () { t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)'; t.style.pointerEvents = 'auto'; });
            function hide() { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(80px)'; t.style.pointerEvents = 'none'; }
            var auto = setTimeout(hide, 8000);
            var yes = document.getElementById('rpv-resume-yes'); if (yes) yes.onclick = function () { clearTimeout(auto); hide(); renderPage(savedPage); };
            var no = document.getElementById('rpv-resume-no'); if (no) no.onclick = function () { clearTimeout(auto); hide(); renderPage(1); };
        }

        /* ── Mobile bottom sheet ── */
        function openSheet() { var s = document.getElementById('rpv-bottom-sheet'), b = document.getElementById('rpv-sheet-backdrop'); if (s) s.classList.add('show'); if (b) b.classList.add('show'); }
        function closeSheet() { var s = document.getElementById('rpv-bottom-sheet'), b = document.getElementById('rpv-sheet-backdrop'); if (s) s.classList.remove('show'); if (b) b.classList.remove('show'); }

        function bindSheet() {
            on('rpv-mobile-fab-btn', 'click', openSheet);
            on('rpv-sheet-backdrop', 'click', closeSheet);
            on('rpv-sheet-close', 'click', closeSheet);
            on('rpv-sheet-prev', 'click', function () { prevPage(); });
            on('rpv-sheet-next', 'click', function () { nextPage(); });
            on('rpv-sheet-zoom-in', 'click', function () { doZoom(1); });
            on('rpv-sheet-zoom-out', 'click', function () { doZoom(-1); });
            on('rpv-sheet-fs', 'click', function () { closeSheet(); setTimeout(function () { isFullscreen ? exitFS() : enterFS(); }, 200); });
            on('rpv-sheet-search', 'click', function () { closeSheet(); setTimeout(openSearch, 200); });
            document.querySelectorAll('[data-rpv-sheet-mode]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('[data-rpv-sheet-mode]').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active'); applyMode(btn.dataset.rpvSheetMode); closeSheet();
                });
            });
        }
        bindSheet();

        /* ── Reading mode ── */
        function applyMode(mode) {
            if (outerWrap) { outerWrap.classList.remove('mode-sepia', 'mode-night'); if (mode !== 'normal') outerWrap.classList.add('mode-' + mode); }
            document.querySelectorAll('[data-rpv-mode],[data-rpv-sheet-mode]').forEach(function (b) {
                var m = b.dataset.rpvMode || b.dataset.rpvSheetMode; b.classList.toggle('active', m === mode);
            });
        }
        document.querySelectorAll('[data-rpv-mode]').forEach(function (btn) {
            btn.addEventListener('click', function () { applyMode(btn.dataset.rpvMode); });
        });

        /* ── Search ── */
        function openSearch() {
            var ov = document.getElementById('rpv-search'); if (!ov) return;
            ov.classList.add('show');
            setTimeout(function () { var i = document.getElementById('rpv-search-input'); if (i) i.focus(); }, 60);
        }
        function closeSearch() {
            var ov = document.getElementById('rpv-search'); if (ov) ov.classList.remove('show');
            clearSearchHL(); searchQuery = ''; searchResults = []; searchIdx = -1;
            var i = document.getElementById('rpv-search-input'); if (i) i.value = '';
            var rl = document.getElementById('rpv-search-results'); if (rl) rl.innerHTML = '';
            var rs = document.getElementById('rpv-search-status'); if (rs) rs.textContent = 'Ketik untuk mencari...';
        }

        async function doSearch(query) {
            var rs = document.getElementById('rpv-search-status');
            var list = document.getElementById('rpv-search-results');
            if (!pdfDoc || !query.trim()) {
                clearSearchHL(); searchQuery = ''; searchResults = []; searchIdx = -1;
                if (rs) rs.textContent = 'Ketik untuk mencari...';
                if (list) list.innerHTML = ''; return;
            }
            if (rs) rs.textContent = 'Mencari...';
            searchResults = []; searchQuery = query;
            var q = query.toLowerCase();
            for (var p = 1; p <= pdfDoc.numPages; p++) {
                var page2 = await pdfDoc.getPage(p), content2 = await page2.getTextContent();
                var text = content2.items.map(function (i) { return i.str; }).join(' ');
                var lt = text.toLowerCase(), idx2 = lt.indexOf(q);
                while (idx2 !== -1) {
                    searchResults.push({ page: p, excerpt: text.substring(Math.max(0, idx2 - 35), idx2 + q.length + 50).trim() });
                    idx2 = lt.indexOf(q, idx2 + 1);
                }
            }
            if (!list) return;
            list.innerHTML = '';
            if (!searchResults.length) { if (rs) rs.textContent = 'Tidak ditemukan: "' + query + '"'; clearSearchHL(); return; }
            if (rs) rs.textContent = searchResults.length + ' hasil — klik untuk pergi';
            searchIdx = 0;
            var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            searchResults.slice(0, 40).forEach(function (r, i) {
                var el = document.createElement('div'); el.className = 'rpv-sri';
                /* FIX BUG 1: return statement, bukan string literal */
                var hlExcerpt = esc(r.excerpt).replace(
                    new RegExp(escaped, 'gi'),
                    function (m) { return '<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">' + m + '</mark>'; }
                );
                el.innerHTML = '<span class="pg">Hal.' + r.page + '</span><span>' + hlExcerpt + '</span>';
                el.addEventListener('click', function () {
                    searchIdx = i;
                    /* FIX BUG 5: apply highlight SEBELUM close, bukan setelah */
                    if (r.page !== pageNum) {
                        renderPage(r.page);
                        /* Tunggu render selesai lalu apply + close */
                        setTimeout(function () { applySearchHL(); flashHL(i); }, 700);
                    } else {
                        applySearchHL(); flashHL(i);
                    }
                    setTimeout(closeSearch, 1200);
                });
                list.appendChild(el);
            });
            if (searchResults[0].page !== pageNum) renderPage(searchResults[0].page);
            else applySearchHL();
        }

        function bindSearch() {
            var inp = document.getElementById('rpv-search-input');
            if (inp) {
                inp.addEventListener('input', function () { clearTimeout(searchDebounce); searchDebounce = setTimeout(function () { doSearch(inp.value); }, 500); });
                inp.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { clearTimeout(searchDebounce); doSearch(inp.value); }
                    if (e.key === 'Escape') closeSearch();
                });
            }
            on('rpv-sclose', 'click', closeSearch);
            on('rpv-snext', 'click', function () {
                if (!searchResults.length) return;
                searchIdx = (searchIdx + 1) % searchResults.length;
                var r = searchResults[searchIdx];
                if (r.page !== pageNum) { renderPage(r.page); setTimeout(function () { applySearchHL(); flashHL(searchIdx); }, 700); }
                else { applySearchHL(); flashHL(searchIdx); }
            });
            on('rpv-sprev', 'click', function () {
                if (!searchResults.length) return;
                searchIdx = (searchIdx - 1 + searchResults.length) % searchResults.length;
                var r = searchResults[searchIdx];
                if (r.page !== pageNum) { renderPage(r.page); setTimeout(function () { applySearchHL(); flashHL(searchIdx); }, 700); }
                else { applySearchHL(); flashHL(searchIdx); }
            });
            on('rpv-search-btn', 'click', openSearch);
            var ov = document.getElementById('rpv-search');
            if (ov) ov.addEventListener('click', function (e) { if (e.target === ov) closeSearch(); });
        }
        bindSearch();

        /* ── Keyboard ── */
        document.addEventListener('keydown', function (e) {
            if (e.target.id === 'rpv-search-input') return;
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') { e.preventDefault(); openSearch(); return; }
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); return; }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); return; }
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; return; }
            switch (e.key) {
                case 'ArrowLeft': prevPage(); break; case 'ArrowRight': nextPage(); break;
                case '+': case '=': doZoom(1); break; case '-': doZoom(-1); break;
                case 'f': case 'F': isFullscreen ? exitFS() : enterFS(); break;
                case 'Escape':
                    var ov = document.getElementById('rpv-search'); if (ov && ov.classList.contains('show')) closeSearch(); else if (isFullscreen) exitFS(); break;
            }
        });

        /* ── Export ── */
        on('rpv-download-btn', 'click', async function () {
            if (exportBusy) { snack('⏳ Sedang export...'); return; }
            if (!pdfDoc) { snack('PDF belum dimuat!'); return; }
            var jsPDFLib = window.jspdf && window.jspdf.jsPDF || window.jsPDF;
            if (!jsPDFLib) { snack('⚠️ Library PDF belum siap', '#F59E0B'); return; }
            exportBusy = true; if (exportOL) exportOL.classList.add('show');
            try {
                var SCALE = 2, offC = document.createElement('canvas'), offCtx = offC.getContext('2d'), pdf = null;
                for (var p = 1; p <= pdfDoc.numPages; p++) {
                    var pg = await pdfDoc.getPage(p), vp = pg.getViewport({ scale: SCALE });
                    offC.width = Math.floor(vp.width); offC.height = Math.floor(vp.height); offCtx.clearRect(0, 0, offC.width, offC.height);
                    await pg.render({ canvasContext: offCtx, viewport: vp }).promise;
                    annots.filter(function (a) { return a.page === p; }).forEach(function (a) { drawOnCanvas(offCtx, a, SCALE); });
                    var wMm = vp.width * .264583, hMm = vp.height * .264583;
                    if (!pdf) pdf = new jsPDFLib({ orientation: vp.width > vp.height ? 'landscape' : 'portrait', unit: 'mm', format: [wMm, hMm] });
                    else pdf.addPage([wMm, hMm], vp.width > vp.height ? 'landscape' : 'portrait');
                    pdf.addImage(offC.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, wMm, hMm, '', 'FAST');
                    showSync('Halaman ' + p + '/' + pdfDoc.numPages + '...');
                }
                pdf.save('review-annotated-' + Date.now() + '.pdf');
                snack('✅ PDF berhasil didownload!', '#22c55e'); showSync('Export selesai ✓', true);
            } catch (err) { console.error('[RPV] export:', err); snack('❌ Gagal: ' + err.message, '#ef4444'); }
            finally { exportBusy = false; if (exportOL) exportOL.classList.remove('show'); }
        });

        function drawOnCanvas(c, a, s) {
            if (!a.rect && a.type !== 'freehand') return;
            c.save();
            var col = hex(a.color);
            if (a.type === 'highlight' || a.type === 'comment') {
                if (!a.rect) return; c.globalAlpha = .38; c.fillStyle = col; c.fillRect(a.rect.x * s, a.rect.y * s, a.rect.w * s, a.rect.h * s);
            } else if (a.type === 'underline') {
                if (!a.rect) return; c.globalAlpha = .75; c.fillStyle = col;
                var ut = Math.max(1.5, 2 * s); c.fillRect(a.rect.x * s, (a.rect.y + a.rect.h) * s - 1, a.rect.w * s, ut);
            } else if (a.type === 'strikethrough') {
                if (!a.rect) return; c.globalAlpha = .75; c.fillStyle = col;
                var st2 = Math.max(1.5, 2 * s); c.fillRect(a.rect.x * s, a.rect.y * s + a.rect.h * s * 0.62 - st2 / 2, a.rect.w * s, st2);
            } else if (a.type === 'freehand') {
                if (!a.path_points || !a.path_points.length) return;
                c.globalAlpha = .92; c.strokeStyle = col; c.lineWidth = (a.stroke_width || 2) * s;
                c.lineCap = 'round'; c.lineJoin = 'round'; c.beginPath();
                c.moveTo(a.path_points[0][0] * s, a.path_points[0][1] * s);
                for (var i = 1; i < a.path_points.length; i++) c.lineTo(a.path_points[i][0] * s, a.path_points[i][1] * s);
                c.stroke();
            } else if (a.type === 'shape') {
                if (!a.rect) return;
                var sw = (a.stroke_width || 2) * s; c.globalAlpha = 0.85; c.strokeStyle = col; c.lineWidth = sw; c.lineCap = 'round'; c.lineJoin = 'round';
                var stype = a.shape_type || 'rect';
                if (stype === 'rect') {
                    c.strokeRect(a.rect.x * s + sw / 2, a.rect.y * s + sw / 2, Math.max(1, a.rect.w * s - sw), Math.max(1, a.rect.h * s - sw));
                } else if (stype === 'ellipse') {
                    c.beginPath(); c.ellipse((a.rect.x + a.rect.w / 2) * s, (a.rect.y + a.rect.h / 2) * s, Math.max(1, a.rect.w * s / 2 - sw / 2), Math.max(1, a.rect.h * s / 2 - sw / 2), 0, 0, Math.PI * 2); c.stroke();
                } else if (stype === 'line') {
                    var lx1 = a.arrow_x1 != null ? a.arrow_x1 * s : a.rect.x * s, ly1 = a.arrow_y1 != null ? a.arrow_y1 * s : (a.rect.y + a.rect.h / 2) * s;
                    var lx2 = a.arrow_x2 != null ? a.arrow_x2 * s : (a.rect.x + a.rect.w) * s, ly2 = a.arrow_y2 != null ? a.arrow_y2 * s : (a.rect.y + a.rect.h / 2) * s;
                    c.beginPath(); c.moveTo(lx1, ly1); c.lineTo(lx2, ly2); c.stroke();
                } else if (stype === 'arrow') {
                    var ax1 = a.arrow_x1 != null ? a.arrow_x1 * s : a.rect.x * s, ay1 = a.arrow_y1 != null ? a.arrow_y1 * s : (a.rect.y + a.rect.h / 2) * s;
                    var ax2 = a.arrow_x2 != null ? a.arrow_x2 * s : (a.rect.x + a.rect.w) * s, ay2 = a.arrow_y2 != null ? a.arrow_y2 * s : (a.rect.y + a.rect.h / 2) * s;
                    var adx = ax2 - ax1, ady = ay2 - ay1, alen = Math.sqrt(adx * adx + ady * ady);
                    if (alen < 2) { c.restore(); return; }
                    var headLen = Math.min(alen * 0.35, Math.max(10, sw * 5)), aang = Math.atan2(ady, adx);
                    c.beginPath(); c.moveTo(ax1, ay1); c.lineTo(ax2, ay2); c.stroke();
                    c.beginPath(); c.moveTo(ax2 - headLen * Math.cos(aang - Math.PI / 6), ay2 - headLen * Math.sin(aang - Math.PI / 6)); c.lineTo(ax2, ay2); c.lineTo(ax2 - headLen * Math.cos(aang + Math.PI / 6), ay2 - headLen * Math.sin(aang + Math.PI / 6)); c.stroke();
                }
            } else if (a.type === 'sticky') {
                if (!a.rect || !a.comment) return;
                var stickyW = Math.max(160, 200 * s), stickyH = Math.max(80, 110 * s);
                var sx = a.rect.x * s, sy = a.rect.y * s;
                var stickyBg = { yellow: '#FEF9C3', green: '#DCFCE7', red: '#FEE2E2', blue: '#DBEAFE', orange: '#FFEDD5', pink: '#FCE7F3', purple: '#EDE9FE', cyan: '#CFFAFE', black: '#1F2937', white: '#F9FAFB' };
                c.globalAlpha = 0.75; c.fillStyle = stickyBg[a.color] || '#FEF9C3';
                c.beginPath(); if (c.roundRect) c.roundRect(sx, sy, stickyW, stickyH, 6); else c.rect(sx, sy, stickyW, stickyH); c.fill();
                c.globalAlpha = 0.9; c.strokeStyle = col; c.lineWidth = Math.max(1.5, s);
                c.beginPath(); if (c.roundRect) c.roundRect(sx, sy, stickyW, stickyH, 6); else c.rect(sx, sy, stickyW, stickyH); c.stroke();
                c.globalAlpha = 0.35; c.fillStyle = col; c.fillRect(sx, sy, stickyW, Math.max(4, 18 * s / 2));
                c.globalAlpha = 1; c.fillStyle = a.color === 'black' ? '#E5E7EB' : 'rgba(0,0,0,.85)';
                var fs = Math.max(13, 13 * s); c.font = '600 ' + fs + 'px ui-sans-serif,system-ui,sans-serif';
                var padX = 8, padY = Math.max(4, 20 * s / 2) + fs, maxW = stickyW - padX * 2;
                var words = a.comment.split(' '), lineH = fs * 1.45, ly = sy + padY, lx = sx + padX, line = '';
                for (var wi = 0; wi < words.length; wi++) {
                    var test = line + words[wi] + ' ';
                    if (c.measureText(test).width > maxW && line !== '') { c.fillText(line.trimEnd(), lx, ly); line = words[wi] + ' '; ly += lineH; if (ly > sy + stickyH - 4) break; }
                    else { line = test; }
                }
                if (line.trim()) c.fillText(line.trimEnd(), lx, ly);
            }
            c.restore();
        }

        /* ── PDF Render ── */
        function computeBase(page) {
            var cw = wrap ? wrap.clientWidth : 800, nw = page.getViewport({ scale: 1 }).width;
            baseScale = Math.max(.5, Math.min((cw - 24) / nw, 2.5));
            needsRecompute = false; /* FIX BUG 2: reset flag setelah compute */
        }

        function prevPage() { if (pageNum > 1) { pageNum--; renderPage(pageNum); } }
        function nextPage() { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } }

        function renderPage(num) {
            if (num < 1 || (pdfDoc && num > pdfDoc.numPages)) return;
            if (pageRendering) { pendingPage = num; return; }
            pageRendering = true; pageNum = num; saveLast(num);

            /* FIX BUG 3: destroy shape preview SVG saat ganti halaman */
            destroyShapePreview();

            document.querySelectorAll('.rpv-popup').forEach(function (p) { p.classList.remove('show'); });
            tooltip.classList.remove('show');
            pendingRect = null; pendingText = null; stickyPos = null;
            if (window.getSelection) window.getSelection().removeAllRanges();

            pdfDoc.getPage(num).then(async function (page) {
                /* FIX BUG 2: hanya computeBase jika flag needsRecompute true */
                if (needsRecompute) computeBase(page);

                var cs = baseScale * zoomFactor;
                var vpCss = page.getViewport({ scale: cs }), vpR = page.getViewport({ scale: cs * DPR });
                mainCanvas.width = Math.floor(vpR.width); mainCanvas.height = Math.floor(vpR.height);
                mainCanvas.style.width = Math.floor(vpCss.width) + 'px'; mainCanvas.style.height = Math.floor(vpCss.height) + 'px';
                stage.style.width = Math.floor(vpCss.width) + 'px'; stage.style.height = Math.floor(vpCss.height) + 'px';

                await page.render({ canvasContext: ctx, viewport: vpR }).promise.catch(function (e) { console.warn(e.message); });
                pageRendering = false;
                if (pendingPage !== null) { var pp = pendingPage; pendingPage = null; renderPage(pp); return; }

                textLayer.innerHTML = '';
                textLayer.style.width = Math.floor(vpCss.width) + 'px';
                textLayer.style.height = Math.floor(vpCss.height) + 'px';
                var content = await page.getTextContent();
                content.items.forEach(function (item) {
                    if (!item.str || !item.str.trim()) return;
                    var tx = pdfjsLib.Util.transform(vpCss.transform, item.transform);
                    var fh = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]), angle = Math.atan2(tx[1], tx[0]);
                    var span = document.createElement('span');
                    span.textContent = item.str; span.style.fontSize = fh + 'px';
                    span.style.left = tx[4] + 'px'; span.style.top = (tx[5] - fh) + 'px';
                    span.style.transformOrigin = '0% 0%';
                    textLayer.appendChild(span);
                    var tw = item.width * cs, mw = span.getBoundingClientRect().width;
                    var t = angle !== 0 ? 'rotate(' + (-angle) + 'rad)' : '';
                    if (mw > 1 && tw > 0) t += ' scaleX(' + (tw / mw) + ')';
                    if (t.trim()) span.style.transform = t.trim();
                });

                scheduleRender();
                stage.style.display = 'block';
                if (loadingEl) loadingEl.classList.add('hidden');
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.value = num;
                var prevEl = document.getElementById('rpv-prev'); if (prevEl) prevEl.disabled = num <= 1;
                var nextEl = document.getElementById('rpv-next'); if (nextEl) nextEl.disabled = !pdfDoc || num >= pdfDoc.numPages;
                var pct = pdfDoc ? num / pdfDoc.numPages * 100 : 0;
                var progEl = document.getElementById('rpv-progress'); if (progEl) progEl.style.width = pct + '%';
                var zvEl = document.getElementById('rpv-zoom-val'); if (zvEl) zvEl.textContent = Math.round(zoomFactor * 100) + '%';
                /* FIX BUG 4: update sheet page & zoom display */
                var spEl = document.getElementById('rpv-sheet-page'); if (spEl) spEl.textContent = num;
                if (wrap) wrap.scrollTo({ top: 0, behavior: 'smooth' });

            }).catch(function (e) {
                console.error('[RPV] render error:', e);
                pageRendering = false;
                if (loadingEl) loadingEl.classList.add('hidden');
                stage.style.display = 'block';
            });
        }

        on('rpv-prev', 'click', prevPage);
        on('rpv-next', 'click', nextPage);
        on('rpv-page-input', 'change', function () { var n = parseInt(this.value); if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) renderPage(n); else this.value = pageNum; });

        /* FIX BUG 2: doZoom pakai needsRecompute, bukan baseScale = 1 */
        function doZoom(dir) {
            zoomFactor = dir > 0 ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX) : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN);
            needsRecompute = true; /* recalculate fit untuk viewport saat ini */
            var zvEl = document.getElementById('rpv-zoom-val'); if (zvEl) zvEl.textContent = Math.round(zoomFactor * 100) + '%';
            /* FIX BUG 4: update sheet zoom val */
            var szv = document.getElementById('rpv-sheet-zoom-val'); if (szv) szv.textContent = Math.round(zoomFactor * 100) + '%';
            if (pdfDoc) renderPage(pageNum);
        }
        on('rpv-zoom-in', 'click', function () { doZoom(1); });
        on('rpv-zoom-out', 'click', function () { doZoom(-1); });

        /* FIX BUG 2: resize pakai needsRecompute */
        var resT = null, lastW = wrap ? wrap.clientWidth : 0;
        window.addEventListener('resize', function () {
            var w = wrap ? wrap.clientWidth : 0; if (Math.abs(w - lastW) < 20) return; lastW = w;
            clearTimeout(resT); resT = setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; renderPage(pageNum); }, 250);
        });
        if (mainCanvas) new MutationObserver(function () { syncFC(); }).observe(mainCanvas, { attributes: true, attributeFilter: ['width', 'height'] });

        /* ── Load PDF ── */
        function startViewer() {
            stage.style.display = 'none';
            if (loadingEl) { loadingEl.classList.remove('hidden'); loadingEl.style.display = ''; }

            if (pdfDoc) {
                console.log('[RPV] using cached PDF');
                var ptEl = document.getElementById('rpv-page-total'); if (ptEl) ptEl.textContent = pdfDoc.numPages;
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.max = pdfDoc.numPages;
                needsRecompute = true;
                renderPage(pageNum); loadAll(); return;
            }

            var task = pdfjsLib.getDocument({ url: CFG.pdfUrl, withCredentials: false, verbosity: 0, rangeChunkSize: 65536 });
            task.onProgress = function (d) {
                if (d.total > 0 && loadSub) loadSub.textContent = 'Mengunduh... ' + Math.min(100, Math.round(d.loaded / d.total * 100)) + '%';
            };
            task.promise.then(async function (doc) {
                pdfDoc = doc; window[CACHE_KEY] = doc;
                console.log('[RPV] PDF loaded,', doc.numPages, 'pages');
                var ptEl = document.getElementById('rpv-page-total'); if (ptEl) ptEl.textContent = doc.numPages;
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.max = doc.numPages;
                renderPage(1); await loadAll();
                var saved = loadLast(); if (saved > 1) setTimeout(function () { showResume(saved); }, 1200);
                console.log('[RPV] ready, reviewId=', CFG.reviewId);
            }).catch(function (err) {
                console.error('[RPV] PDF load error:', err);
                if (loadingEl) loadingEl.innerHTML = '<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:11px;margin:.25rem 0;">' + err.message + '</p><button type="button" onclick="window.location.reload()" style="margin-top:.75rem;padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
            });
        }

        setTool('highlight');
        startViewer();
    }

})();
