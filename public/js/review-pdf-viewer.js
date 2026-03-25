/**
 * public/js/review-pdf-viewer.js  v6.0
 *
 * CHANGELOG v6.0 — FULL REWRITE / MERGE:
 *
 * ── BUG FIXES ───────────────────────────────────────────────────────
 * FIX-1  Loading tidak sampai 100%
 *        onProgress hanya update teks, tidak update progress bar.
 *        FIX: tambah progress bar #rpv-progress update dari onProgress.
 *
 * FIX-2  Loading spinner terus muter setelah error
 *        catch block tidak memanggil hideLoading().
 *        FIX: showLoading()/hideLoading() terpusat, selalu dipanggil.
 *
 * FIX-3  Horizontal scrollbar muncul
 *        computeBase pakai cw - 24 → canvas kadang lebih lebar.
 *        FIX: pakai (cw - 4) dan set overflow-x:hidden pada wrap.
 *
 * FIX-4  DPR tidak di-cap → canvas sangat besar di layar retina 3x
 *        FIX: cap DPR = Math.min(devicePixelRatio, 2).
 *
 * FIX-5  MutationObserver bisa trigger render-loop
 *        FIX: ganti ke ResizeObserver + guard di syncFC() agar tidak
 *             re-set ukuran jika sudah sama.
 *
 * FIX-6  textLayer pointer-events tidak di-reset antar halaman
 *        FIX: applyTextLayerPointerEvents() dipanggil ulang setelah
 *             setiap renderPage() selesai.
 *
 * FIX-7  Resize threshold 20px terlalu kecil → flicker di iOS
 *        FIX: threshold 40px + orientationchange handler.
 *
 * FIX-8  snack() overflow di layar sempit
 *        FIX: max-width:90vw + text-overflow:ellipsis.
 *
 * FIX-9  Guard double-init (_gk) tidak reliable setelah Livewire re-render
 *        FIX: cek visibility DOM sebelum skip, reset guard jika hidden.
 *
 * FIX-10 loadingEl.style.display = '' tidak konsisten lintas re-render
 *        FIX: gunakan 'flex'/'none' eksplisit.
 *
 * FIX-11 needsRecompute tidak direset setelah zoom
 *        FIX: set needsRecompute=false di dalam computeBase(), bukan di luar.
 *
 * FIX-12 zoomFactor tidak persist antar halaman
 *        FIX: simpan/load zoomFactor ke localStorage.
 *
 * ── FEATURES PORTED FROM pdf-viewer.js + pdf-annotations.js ────────
 * FEAT-1  Bookmark (🔖) — tandai halaman, persist di localStorage
 * FEAT-2  Reading mode (Normal / Sepia / Night) — persist di localStorage
 * FEAT-3  Resume toast — tawaran lanjut baca dari halaman terakhir
 * FEAT-4  Fullscreen mode (#rpv-outer-wrap.is-fullscreen)
 * FEAT-5  Search — full-text search semua halaman, navigasi ↑↓
 * FEAT-6  Bottom sheet mobile + FAB button
 * FEAT-7  Touch pinch-to-zoom + swipe navigasi
 * FEAT-8  Edit anotasi (inline popup edit komentar/sticky)
 * FEAT-9  Freehand brush mode (ukuran & opasitas berbeda dari pen)
 * FEAT-10 Shape preview SVG realtime saat drag
 * FEAT-11 Arrow & line shape presisi (arrow_x1/y1/x2/y2)
 * FEAT-12 Export PDF dengan anotasi — termasuk sticky note presisi
 * FEAT-13 Sticky note drag-and-drop + edit + animasi hapus
 * FEAT-14 copy-text mode — seleksi teks tanpa buat anotasi, auto copy
 * FEAT-15 Panel anotasi dengan edit & delete per item
 * FEAT-16 Undo / Redo (Ctrl+Z / Ctrl+Y)
 * FEAT-17 Eraser cursor custom
 * FEAT-18 Read-only mode untuk author (hanya lihat anotasi reviewer)
 * FEAT-19 PDF cache (window[CACHE_KEY]) agar tidak re-download
 * FEAT-20 Progress bar loading (0–100%)
 */

(function () {
    'use strict';

    /* ── GUARD: cegah double-init ───────────────────────────────── */
    var _gkId = (window.RPV_CONFIG && window.RPV_CONFIG.reviewId) || 'x';
    var _gk = '_rpvA_' + _gkId;

    if (window[_gk] === true) {
        var _cw = document.getElementById('rpv-canvas-wrap');
        if (_cw && _cw.offsetParent !== null && _cw.getBoundingClientRect().width > 0) {
            console.log('[RPV] already running, skip');
            return;
        }
        window[_gk] = false; /* reset jika tidak visible */
    }

    /* FIX-3: paksa overflow-x hidden sejak awal */
    (function () {
        var cw = document.getElementById('rpv-canvas-wrap');
        if (cw) cw.style.overflowX = 'hidden';
    })();

    /* ── CDN ─────────────────────────────────────────────────────── */
    var PDFJS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    var WORKER_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    /* ── BOOT: load pdfjs jika belum ada ─────────────────────────── */
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
                if (el) {
                    el.style.display = 'flex';
                    el.classList.remove('hidden');
                    el.innerHTML =
                        '<div style="font-size:2rem">⚠️</div>' +
                        '<p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat library PDF</p>' +
                        '<p style="color:#6b7280;font-size:11px;margin:.25rem 0;">Periksa koneksi internet.</p>' +
                        '<button type="button" onclick="window.location.reload()" style="margin-top:.75rem;padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
                }
            };
            document.head.appendChild(s);
        }
    }
    window.RPV_boot = boot;

    /* ════════════════════════════════════════════════════════════════
       MAIN RUN
    ════════════════════════════════════════════════════════════════ */
    function run() {
        var CFG = window.RPV_CONFIG;
        if (!CFG || !CFG.pdfUrl) { console.error('[RPV] RPV_CONFIG missing'); return; }

        window[_gk] = true;

        /* ── Constants & Colors ───────────────────────────────────── */
        var COLORS = {
            yellow: '#FFD700', green: '#4ADE80', red: '#EF4444', blue: '#60A5FA',
            orange: '#FF6B18', black: '#111111', white: '#FFFFFF',
            pink: '#F472B6', purple: '#A78BFA', cyan: '#22D3EE'
        };
        var STICKY_BG = {
            yellow: '#FEF9C3', green: '#DCFCE7', red: '#FEE2E2', blue: '#DBEAFE',
            orange: '#FFEDD5', pink: '#FCE7F3', purple: '#EDE9FE', cyan: '#CFFAFE',
            black: '#1F2937', white: '#F9FAFB'
        };
        var STICKY_BORDER = {
            yellow: '#FDE047', green: '#86EFAC', red: '#FCA5A5', blue: '#93C5FD',
            orange: '#FDBA74', pink: '#F9A8D4', purple: '#C4B5FD', cyan: '#67E8F9',
            black: '#374151', white: '#D1D5DB'
        };
        function hex(n) { return COLORS[n] || '#FFD700'; }

        /* ── State ────────────────────────────────────────────────── */
        var CACHE_KEY = '_rpv_' + btoa(CFG.pdfUrl).slice(0, 30).replace(/[^a-z0-9]/gi, '_');
        var IS_RO = !!CFG.readOnly;          /* read-only mode untuk author */
        var SK = 'rpv_' + (CFG.reviewId || 'x');
        var SK_LAST = SK + '_last';
        var SK_ZOOM = SK + '_zoom';             /* FIX-12 */
        var SK_MODE = SK + '_mode';
        var SK_BM = SK + '_bm';

        /* FIX-4: cap DPR ke 2 */
        var DPR = Math.min(window.devicePixelRatio || 1, 2);

        var pdfDoc = window[CACHE_KEY] || null;
        var pageNum = 1;
        var pageRendering = false;
        var pendingPage = null;
        var baseScale = 1;
        var needsRecompute = true;
        var ZOOM_MIN = 0.5, ZOOM_MAX = 4, ZOOM_STEP = 0.25;
        /* FIX-12: restore zoom dari localStorage */
        var zoomFactor = Math.max(ZOOM_MIN, parseFloat(localStorage.getItem(SK_ZOOM) || '1') || 1);

        var annots = [], undoStack = [], redoStack = [];
        var activeTool = 'highlight';
        var activeColor = 'yellow';
        var activeSize = 2;
        var activeShape = 'rect';

        var isDrawing = false, drawStart = null;
        var freePoints = [], shapePreviewSVG = null;
        var pendingRect = null, pendingText = null, stickyPos = null;
        var selectedId = null;
        var isPanning = false, panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;

        var renderPending = false, syncTout = null;
        var searchResults = [], searchIdx = -1, searchHLs = [], searchQuery = '', searchDebounce = null;
        var isFullscreen = false, exportBusy = false;

        /* ── DOM ──────────────────────────────────────────────────── */
        var outerWrap = document.getElementById('rpv-outer-wrap');
        var wrap = document.getElementById('rpv-canvas-wrap');
        var stage = document.getElementById('rpv-stage');
        var mainCanvas = document.getElementById('rpv-canvas');
        var ctx = mainCanvas ? mainCanvas.getContext('2d') : null;
        var textLayer = document.getElementById('rpv-text-layer');
        var annotLayer = document.getElementById('rpv-annotation-layer');
        var freeCanvas = document.getElementById('rpv-freehand-canvas');
        var freeCtx = freeCanvas ? freeCanvas.getContext('2d') : null;
        var loadingEl = document.getElementById('rpv-loading');
        var loadSub = document.getElementById('rpv-load-sub');
        var tooltip = document.getElementById('rpv-tooltip');
        var syncEl = document.getElementById('rpv-sync');
        var syncTxtEl = document.getElementById('rpv-sync-txt');
        var eraserCur = document.getElementById('rpv-eraser-cursor');
        var exportOL = document.getElementById('rpv-export-overlay');

        /* FIX-3: overflow-x hidden */
        if (wrap) wrap.style.overflowX = 'hidden';

        /* Init freehand canvas */
        if (freeCanvas) {
            freeCanvas.style.pointerEvents = 'none';
            freeCanvas.style.position = 'absolute';
            freeCanvas.style.inset = '0';
            freeCanvas.style.zIndex = '10';
        }

        /* ── Utils ────────────────────────────────────────────────── */
        function on(id, ev, fn) {
            var el = document.getElementById(id);
            if (el) el.addEventListener(ev, fn);
        }

        /* FIX-8: snack dengan max-width */
        function snack(msg, color) {
            color = color || '#FF6B18';
            var el = document.createElement('div');
            el.textContent = msg;
            el.style.cssText =
                'position:fixed;top:1rem;left:50%;transform:translateX(-50%);' +
                'background:#1A1A1A;border:1px solid ' + color + ';color:#fff;' +
                'padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;' +
                'z-index:99999;transition:opacity .4s;pointer-events:none;' +
                'white-space:nowrap;max-width:90vw;overflow:hidden;text-overflow:ellipsis;';
            document.body.appendChild(el);
            setTimeout(function () {
                el.style.opacity = 0;
                setTimeout(function () { el.remove(); }, 400);
            }, 2200);
        }

        function showSync(msg, ok) {
            if (!syncEl) return;
            if (syncTxtEl) syncTxtEl.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#FF6B18';
            syncEl.style.color = ok ? '#22c55e' : '#FF6B18';
            syncEl.classList.add('show');
            clearTimeout(syncTout);
            syncTout = setTimeout(function () { syncEl.classList.remove('show'); }, ok ? 1800 : 4000);
        }

        /* FIX-2 & FIX-10: loading helpers terpusat */
        function showLoading(msg) {
            if (!loadingEl) return;
            loadingEl.classList.remove('hidden');
            loadingEl.style.display = 'flex';
            if (msg && loadSub) loadSub.textContent = msg;
        }
        function hideLoading() {
            if (!loadingEl) return;
            loadingEl.classList.add('hidden');
            loadingEl.style.display = 'none';
        }

        /* FIX-1: update progress bar dari nilai 0–100 */
        function updateLoadProgress(pct) {
            var progEl = document.getElementById('rpv-progress');
            if (progEl) progEl.style.width = Math.min(100, Math.round(pct)) + '%';
            if (loadSub) loadSub.textContent = 'Mengunduh... ' + Math.min(100, Math.round(pct)) + '%';
        }
        function updateReadProgress() {
            if (!pdfDoc) return;
            var pct = pageNum / pdfDoc.numPages * 100;
            var progEl = document.getElementById('rpv-progress');
            if (progEl) progEl.style.width = Math.min(100, Math.round(pct)) + '%';
        }

        function stageXY(e) {
            var r = stage.getBoundingClientRect();
            var s = (e.changedTouches && e.changedTouches[0]) ||
                (e.touches && e.touches[0]) || e;
            return { x: s.clientX - r.left, y: s.clientY - r.top };
        }

        function esc(s) {
            return String(s || '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }

        /* FIX-5: syncFC dengan guard agar tidak trigger loop */
        function syncFC() {
            if (!freeCanvas) return;
            var w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width === w && freeCanvas.height === h) return; /* guard */
            freeCanvas.width = w;
            freeCanvas.height = h;
            freeCanvas.style.width = w + 'px';
            freeCanvas.style.height = h + 'px';
        }

        function csrf() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }
        function hdrs() {
            return {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function saveLast(p) { try { localStorage.setItem(SK_LAST, p); } catch (e) { } }
        function loadLast() { try { return parseInt(localStorage.getItem(SK_LAST) || '1'); } catch (e) { return 1; } }

        /* ── Bookmark ─────────────────────────────────────────────── */
        function getBM() { try { var v = localStorage.getItem(SK_BM); return v ? parseInt(v) : null; } catch (e) { return null; } }
        function setBM(pg) { try { localStorage.setItem(SK_BM, pg); } catch (e) { } }
        function clearBM() { try { localStorage.removeItem(SK_BM); } catch (e) { } }

        function showBMToast(msg) {
            var t = document.getElementById('rpv-bookmark-toast');
            var msgEl = document.getElementById('rpv-bookmark-msg');
            if (!t) return;
            if (msgEl) msgEl.textContent = msg;
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 2500);
        }

        function updateBMBtn() {
            var btn = document.getElementById('rpv-bookmark-btn'); if (!btn) return;
            var bm = getBM(), isMarked = bm && bm === pageNum;
            btn.classList.toggle('bookmarked', !!isMarked);
            btn.title = isMarked
                ? 'Halaman ini ditandai — klik untuk hapus'
                : (bm ? 'Tandai halaman ini (tanda di hal.' + bm + ')' : 'Tandai halaman ini');

            var shBtn = document.getElementById('rpv-sheet-bookmark-btn');
            if (shBtn) {
                shBtn.textContent = isMarked ? '🔖 Hapus Tanda Baca' : '🔖 Tandai Halaman Ini';
                shBtn.style.borderColor = isMarked ? '#FF6B18' : '#3d3d3d';
                shBtn.style.color = isMarked ? '#FF6B18' : '#d1d5db';
            }
        }

        function toggleBookmark() {
            var bm = getBM();
            if (bm && bm === pageNum) { clearBM(); showBMToast('Tanda baca dihapus'); }
            else { setBM(pageNum); showBMToast('Halaman ' + pageNum + ' ditandai ✓'); }
            updateBMBtn();
        }

        on('rpv-bookmark-btn', 'click', toggleBookmark);

        /* ── Reading Mode ─────────────────────────────────────────── */
        function applyMode(mode) {
            if (outerWrap) {
                outerWrap.classList.remove('mode-sepia', 'mode-night');
                if (mode !== 'normal') outerWrap.classList.add('mode-' + mode);
            }
            try { localStorage.setItem(SK_MODE, mode); } catch (e) { }
            document.querySelectorAll('[data-rpv-mode],[data-rpv-sheet-mode]').forEach(function (b) {
                var m = b.dataset.rpvMode || b.dataset.rpvSheetMode;
                b.classList.toggle('active', m === mode);
            });
        }
        document.querySelectorAll('[data-rpv-mode]').forEach(function (btn) {
            btn.addEventListener('click', function () { applyMode(btn.dataset.rpvMode); });
        });
        /* restore mode */
        (function () {
            try { var m = localStorage.getItem(SK_MODE); if (m) applyMode(m); } catch (e) { }
        })();

        /* ── Sanitizer ────────────────────────────────────────────── */
        var VT = ['highlight', 'underline', 'strikethrough', 'freehand', 'comment', 'sticky', 'shape', 'copy-text', 'text'];
        var VC = ['yellow', 'green', 'red', 'blue', 'orange', 'black', 'white', 'pink', 'purple', 'cyan'];
        var VS = ['rect', 'ellipse', 'arrow', 'line'];

        function sanitize(raw) {
            var type = (raw.type === 'brush') ? 'freehand' : raw.type;
            if (!VT.includes(type)) type = 'highlight';
            var color = VC.includes(raw.color) ? raw.color : 'yellow';
            var p = {
                page: parseInt(raw.page) || pageNum,
                type: type,
                color: color,
                rect_x: raw.rect ? raw.rect.x : (raw.rect_x ?? null),
                rect_y: raw.rect ? raw.rect.y : (raw.rect_y ?? null),
                rect_w: raw.rect ? raw.rect.w : (raw.rect_w ?? null),
                rect_h: raw.rect ? raw.rect.h : (raw.rect_h ?? null),
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

        /* ── API ──────────────────────────────────────────────────── */
        var API = CFG.apiBase;

        function normalizeAnnot(a) {
            if (!a.rect && a.rect_x != null)
                a.rect = { x: +a.rect_x, y: +a.rect_y, w: +a.rect_w, h: +a.rect_h };
            if (a.type === 'shape' && (a.shape_type === 'arrow' || a.shape_type === 'line')) {
                if (a.arrow_x1 == null && Array.isArray(a.path_points) && a.path_points.length >= 2) {
                    a.arrow_x1 = +a.path_points[0][0]; a.arrow_y1 = +a.path_points[0][1];
                    a.arrow_x2 = +a.path_points[1][0]; a.arrow_y2 = +a.path_points[1][1];
                }
            }
            return a;
        }

        async function apiLoad() {
            if (!API) return [];
            try {
                var r = await fetch(API, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) throw new Error(r.status);
                var j = await r.json();
                return (Array.isArray(j.data) ? j.data : []).map(normalizeAnnot);
            } catch (e) { console.error('[RPV] load:', e); return []; }
        }

        async function apiSave(payload) {
            if (!API) { snack('⚠️ Simpan draft dulu!', '#F59E0B'); return null; }
            var clean = sanitize(payload);
            showSync('Menyimpan...');
            try {
                var r = await fetch(API, {
                    method: 'POST', credentials: 'same-origin',
                    headers: hdrs(), body: JSON.stringify(clean)
                });
                var j = await r.json();
                if (!r.ok) { showSync('Gagal: ' + (j.message || r.status)); return null; }
                showSync('Tersimpan ✓', true);
                var saved = j.data || null;
                if (saved) {
                    normalizeAnnot(saved);
                    /* fallback arrow coords jika server tidak kembalikan */
                    if (saved.type === 'shape' && saved.arrow_x1 == null && clean.arrow_x1 != null) {
                        saved.arrow_x1 = clean.arrow_x1; saved.arrow_y1 = clean.arrow_y1;
                        saved.arrow_x2 = clean.arrow_x2; saved.arrow_y2 = clean.arrow_y2;
                    }
                    if (payload.arrow_x1 != null && saved.arrow_x1 == null) {
                        saved.arrow_x1 = payload.arrow_x1; saved.arrow_y1 = payload.arrow_y1;
                        saved.arrow_x2 = payload.arrow_x2; saved.arrow_y2 = payload.arrow_y2;
                    }
                }
                return saved;
            } catch (e) { console.error('[RPV] save:', e); showSync('Error jaringan'); return null; }
        }

        async function apiPatch(id, payload) {
            if (!API) return;
            try {
                await fetch(API + '/' + id, {
                    method: 'PUT', credentials: 'same-origin',
                    headers: hdrs(), body: JSON.stringify(payload)
                });
            } catch (e) { console.error('[RPV] patch:', e); }
        }

        async function apiDel(id) {
            if (!API) return;
            showSync('Menghapus...');
            try {
                await fetch(API + '/' + id, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Dihapus ✓', true);
            } catch (e) { console.error('[RPV] del:', e); }
        }

        async function apiDelPage(pg) {
            if (!API) return;
            showSync('Membersihkan...');
            try {
                await fetch(API + '/page/' + pg, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Selesai ✓', true);
            } catch (e) { console.error('[RPV] delPage:', e); }
        }

        async function loadAll() {
            annots = await apiLoad();
            console.log('[RPV] loaded', annots.length, 'annotations');
            scheduleRender(); updateBadge(); updateUndoRedo();
        }

        /* ── Search ───────────────────────────────────────────────── */
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
                            el.style.cssText = 'position:absolute;left:' + (rect.left - sr.left) +
                                'px;top:' + (rect.top - sr.top) + 'px;width:' + rect.width +
                                'px;height:' + rect.height +
                                'px;background:rgba(255,215,0,.45);border-radius:2px;' +
                                'pointer-events:none;z-index:7;transition:background .3s;';
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
            if (searchHLs[searchIdx])
                searchHLs[searchIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                var pg = await pdfDoc.getPage(p);
                var ct = await pg.getTextContent();
                var text = ct.items.map(function (i) { return i.str; }).join(' ');
                var lt = text.toLowerCase(), ix = lt.indexOf(q);
                while (ix !== -1) {
                    searchResults.push({ page: p, excerpt: text.substring(Math.max(0, ix - 35), ix + q.length + 50).trim() });
                    ix = lt.indexOf(q, ix + 1);
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
                var hlEx = esc(r.excerpt).replace(
                    new RegExp(escaped, 'gi'),
                    function (m) { return '<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">' + m + '</mark>'; }
                );
                el.innerHTML = '<span class="pg">Hal.' + r.page + '</span><span>' + hlEx + '</span>';
                el.addEventListener('click', function () {
                    searchIdx = i;
                    if (r.page !== pageNum) { renderPage(r.page); setTimeout(function () { applySearchHL(); flashHL(i); }, 700); }
                    else { applySearchHL(); flashHL(i); }
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
                inp.addEventListener('input', function () {
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(function () { doSearch(inp.value); }, 500);
                });
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

        /* ── Render (annotations) ─────────────────────────────────── */
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

        function rHL(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'), sel = selectedId == a.id;
            el.dataset.annotId = String(a.id);
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + (a.rect.y * s) +
                'px;width:' + (a.rect.w * s) + 'px;height:' + (a.rect.h * s) +
                'px;background:' + hex(a.color) + ';opacity:' + (sel ? .75 : .38) +
                ';border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;' +
                'outline:' + (sel ? '2px solid #FF6B18' : 'none') + ';transition:opacity .15s;';
            if (a.type === 'comment' && a.comment) {
                var dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;' +
                    'background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rUL(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'); el.dataset.annotId = String(a.id);
            var t = Math.max(1.5, 2 * s), top = (a.rect.y + a.rect.h) * s - 1;
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + top +
                'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) +
                ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rST(a, s) {
            if (!a.rect) return;
            var el = document.createElement('div'); el.dataset.annotId = String(a.id);
            var t = Math.max(1.5, 2 * s), top = a.rect.y * s + a.rect.h * s * 0.62 - t / 2;
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + top +
                'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) +
                ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
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
                hit.style.cssText = 'position:absolute;left:' + ((a.rect.x - 8) * s) + 'px;top:' + ((a.rect.y - 8) * s) +
                    'px;width:' + ((a.rect.w + 16) * s) + 'px;height:' + ((a.rect.h + 16) * s) +
                    'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
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
                el.style.cssText = 'position:absolute;left:' + bx + 'px;top:' + by + 'px;width:' + bw + 'px;height:' + bh +
                    'px;pointer-events:auto;cursor:pointer;z-index:5;' +
                    'outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                var svg = '';
                if (st === 'line') {
                    svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 +
                        '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/>';
                } else {
                    var dx = lx2 - lx1, dy = ly2 - ly1, len = Math.sqrt(dx * dx + dy * dy);
                    if (len > 1) {
                        var hLen = Math.min(len * .35, Math.max(10, sw * 5)), ang = Math.atan2(dy, dx);
                        var hx1 = lx2 - hLen * Math.cos(ang - Math.PI / 6), hy1 = ly2 - hLen * Math.sin(ang - Math.PI / 6);
                        var hx2 = lx2 - hLen * Math.cos(ang + Math.PI / 6), hy2 = ly2 - hLen * Math.sin(ang + Math.PI / 6);
                        svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 +
                            '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/>' +
                            '<polyline points="' + hx1 + ',' + hy1 + ' ' + lx2 + ',' + ly2 + ' ' + hx2 + ',' + hy2 +
                            '" fill="none" stroke="' + col + '" stroke-width="' + sw +
                            '" stroke-linecap="round" stroke-linejoin="round"/>';
                    }
                }
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + bw + '" height="' + bh +
                    '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            } else {
                var x = a.rect.x * s, y = a.rect.y * s, w = Math.max(4, a.rect.w * s), h = Math.max(4, a.rect.h * s);
                el.style.cssText = 'position:absolute;left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h +
                    'px;pointer-events:auto;cursor:pointer;z-index:5;' +
                    'outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                var svg = '';
                if (st === 'rect')
                    svg = '<rect x="' + (sw / 2) + '" y="' + (sw / 2) + '" width="' + Math.max(1, w - sw) +
                        '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col +
                        '" stroke-width="' + sw + '"/>';
                else if (st === 'ellipse')
                    svg = '<ellipse cx="' + (w / 2) + '" cy="' + (h / 2) + '" rx="' + Math.max(1, w / 2 - sw / 2) +
                        '" ry="' + Math.max(1, h / 2 - sw / 2) + '" fill="none" stroke="' + col +
                        '" stroke-width="' + sw + '"/>';
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h +
                    '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rSticky(a, s) {
            if (!a.rect) return;
            var note = document.createElement('div');
            note.className = 'rpv-sticky-note';
            note.dataset.annotId = String(a.id);
            note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * s) + 'px';
            note.style.top = (a.rect.y * s) + 'px';
            note.innerHTML =
                '<div class="rpv-sn-header"><span>📌</span>' +
                '<div style="display:flex;gap:3px;">' +
                '<button type="button" class="rpv-sn-edit" title="Edit">✏️</button>' +
                '<button type="button" class="rpv-sn-del" title="Hapus">×</button>' +
                '</div></div>' +
                '<div class="rpv-sn-body">' + esc(a.comment) + '</div>';

            note.querySelector('.rpv-sn-del').addEventListener('click', function (ev) {
                ev.stopPropagation(); stickyAnim(note, a.id);
            });
            note.querySelector('.rpv-sn-edit').addEventListener('click', function (ev) {
                ev.stopPropagation(); openEditPopup(a);
            });
            note.addEventListener('click', function (ev) {
                if (activeTool === 'eraser') { ev.stopPropagation(); stickyAnim(note, a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, s);
            stage.appendChild(note);
        }

        function stickyAnim(el, id) {
            el.style.transition = 'opacity .18s,transform .18s';
            el.style.opacity = '0'; el.style.transform = 'scale(.85)';
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
                await apiPatch(annotData.id, {
                    rect_x: nx, rect_y: ny,
                    rect_w: annotData.rect ? annotData.rect.w : 180,
                    rect_h: annotData.rect ? annotData.rect.h : 90
                });
            }
            el.addEventListener('mousedown', dn, { passive: false });
            el.addEventListener('touchstart', dn, { passive: false });
            document.addEventListener('mousemove', mv, { passive: false });
            document.addEventListener('touchmove', mv, { passive: false });
            document.addEventListener('mouseup', up);
            document.addEventListener('touchend', up);
        }

        /* ── Tooltip ──────────────────────────────────────────────── */
        function showTip(a, cx, cy) {
            var ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌' };
            var txt = a.comment
                ? (ic[a.type] || '•') + ' ' + a.comment.substring(0, 80)
                : a.selected_text
                    ? (ic[a.type] || '•') + ' "' + a.selected_text.substring(0, 60) + '"'
                    : (ic[a.type] || '•') + ' ' + a.type;
            var tipTxt = document.getElementById('rpv-tip-text');
            if (tipTxt) { tipTxt.textContent = txt; tipTxt.dataset.annotId = String(a.id); }
            var editBtn = document.getElementById('rpv-tip-edit');
            if (editBtn) {
                editBtn.style.display = ['comment', 'sticky'].includes(a.type) ? '' : 'none';
                editBtn.dataset.annotId = String(a.id);
            }
            tooltip.classList.add('show');
            var vw = window.innerWidth, vh = window.innerHeight;
            tooltip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
            tooltip.style.top = ((cy + 140 > vh) ? Math.max(4, cy - 140) : cy + 8) + 'px';
        }

        on('rpv-tip-close', 'click', function () { tooltip.classList.remove('show'); });
        on('rpv-tip-del', 'click', async function () {
            var id = document.getElementById('rpv-tip-text') && document.getElementById('rpv-tip-text').dataset.annotId;
            tooltip.classList.remove('show');
            if (id) await removeAnnot(id);
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

        /* ── Edit popup ───────────────────────────────────────────── */
        function openEditPopup(a) {
            var pop = document.getElementById('rpv-edit-popup');
            if (!pop) {
                pop = document.createElement('div'); pop.id = 'rpv-edit-popup';
                pop.style.cssText = 'position:fixed;z-index:99995;background:#1a1a1a;border:2px solid #FF6B18;' +
                    'border-radius:14px;padding:.875rem;width:min(300px,90vw);' +
                    'box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
                pop.innerHTML =
                    '<p style="font-size:12px;font-weight:700;color:#FF6B18;margin:0 0 .5rem;">✏️ Edit Anotasi</p>' +
                    '<textarea id="rpv-edit-txt" style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;' +
                    'color:#fff;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:80px;' +
                    'display:block;box-sizing:border-box;"></textarea>' +
                    '<div style="display:flex;gap:.4rem;margin-top:.5rem;">' +
                    '<button type="button" id="rpv-edit-save" style="flex:1;padding:.5rem;background:#FF6B18;' +
                    'color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Simpan</button>' +
                    '<button type="button" id="rpv-edit-cancel" style="padding:.5rem .75rem;background:#2d2d2d;' +
                    'color:#9ca3af;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button></div>';
                document.body.appendChild(pop);
                document.getElementById('rpv-edit-cancel').addEventListener('click', function () { pop.style.display = 'none'; });
                document.addEventListener('click', function (e) {
                    if (pop.style.display !== 'none' && !pop.contains(e.target) && !e.target.closest('#rpv-tooltip'))
                        pop.style.display = 'none';
                });
            }
            var txt = document.getElementById('rpv-edit-txt'); txt.value = a.comment || '';
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

        /* ── Add / Remove ─────────────────────────────────────────── */
        async function addAnnot(payload) {
            var saved = await apiSave(payload); if (!saved) return null;
            if (!saved.rect && saved.rect_x != null)
                saved.rect = { x: +saved.rect_x, y: +saved.rect_y, w: +saved.rect_w, h: +saved.rect_h };
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

        /* ── Undo / Redo ──────────────────────────────────────────── */
        function updateUndoRedo() {
            var u = document.getElementById('rpv-undo'); if (u) u.disabled = !undoStack.length;
            var r = document.getElementById('rpv-redo'); if (r) r.disabled = !redoStack.length;
        }
        async function doUndo() {
            if (!undoStack.length) return;
            var op = undoStack.pop();
            if (op.action === 'add') {
                var a = annots.find(function (x) { return String(x.id) === String(op.data.id); });
                if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); redoStack.push({ action: 'readd', data: a }); }
            } else if (op.action === 'del') {
                var saved = await apiSave(op.data);
                if (saved) { annots.push(saved); redoStack.push({ action: 'redel', data: saved }); }
            }
            updateUndoRedo(); scheduleRender();
        }
        async function doRedo() {
            if (!redoStack.length) return;
            var op = redoStack.pop();
            if (op.action === 'readd') {
                var saved = await apiSave(op.data);
                if (saved) { annots.push(saved); undoStack.push({ action: 'add', data: saved }); }
            } else if (op.action === 'redel') {
                var a = annots.find(function (x) { return String(x.id) === String(op.data.id); });
                if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); undoStack.push({ action: 'del', data: a }); }
            }
            updateUndoRedo(); scheduleRender();
        }
        on('rpv-undo', 'click', doUndo);
        on('rpv-redo', 'click', doRedo);

        /* ── Badge & Panel ────────────────────────────────────────── */
        function updateBadge() {
            var n = annots.length, badge = document.getElementById('rpv-badge');
            if (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.classList.toggle('show', n > 0); }
        }
        on('rpv-panel-btn', 'click', function (e) {
            e.stopPropagation();
            var panel = document.getElementById('rpv-panel');
            if (panel) panel.classList.toggle('open');
            buildPanel();
        });
        on('rpv-panel-close', 'click', function () {
            var panel = document.getElementById('rpv-panel'); if (panel) panel.classList.remove('open');
        });
        on('rpv-panel-clear', 'click', async function () {
            if (!confirm('Hapus semua anotasi di halaman ' + pageNum + '?')) return;
            await apiDelPage(pageNum);
            annots = annots.filter(function (a) { return a.page !== pageNum; });
            undoStack = []; redoStack = []; updateUndoRedo(); scheduleRender(); buildPanel();
            snack('🗑 Halaman ' + pageNum + ' dibersihkan');
        });

        function buildPanel() {
            var list = document.getElementById('rpv-panel-list'); if (!list) return;
            if (!annots.length) { list.innerHTML = '<div class="rpv-panel-empty">Belum ada anotasi.</div>'; return; }
            list.innerHTML = '';
            var ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌' };
            annots.slice().sort(function (a, b) { return a.page - b.page || a.id - b.id; }).forEach(function (a) {
                var el = document.createElement('div'); el.className = 'rpv-panel-item';
                el.innerHTML =
                    '<div class="rpv-panel-dot" style="background:' + hex(a.color) + '"></div>' +
                    '<div class="rpv-panel-body"><span class="rpv-panel-type">' + (ic[a.type] || '•') + ' ' + a.type + '</span>' +
                    '<span class="rpv-panel-pg">Hal.' + a.page + '</span>' +
                    '<div class="rpv-panel-text">' + esc(a.comment || a.selected_text || a.shape_type || '—') + '</div></div>' +
                    '<div style="display:flex;gap:2px;flex-shrink:0;">' +
                    '<button type="button" data-pe="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:11px;padding:2px 3px;">✏️</button>' +
                    '<button type="button" data-pd="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:12px;padding:2px 3px;">🗑</button></div>';
                el.querySelector('[data-pd="' + a.id + '"]').addEventListener('click', async function (ev) {
                    ev.stopPropagation(); await removeAnnot(a.id); buildPanel();
                });
                el.querySelector('[data-pe="' + a.id + '"]').addEventListener('click', function (ev) {
                    ev.stopPropagation(); openEditPopup(a);
                });
                el.addEventListener('click', function () {
                    if (a.page !== pageNum) renderPage(a.page);
                    var panel = document.getElementById('rpv-panel'); if (panel) panel.classList.remove('open');
                });
                list.appendChild(el);
            });
        }

        /* ── Tool management ──────────────────────────────────────── */
        function setTool(tool) {
            activeTool = tool;
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode', 'copy-text-mode');
            if (tool === 'freehand' || tool === 'brush') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            if (tool === 'copy-text') stage.classList.add('copy-text-mode');

            /* FIX-6: selalu apply pointer-events */
            applyTextLayerPE();

            if (freeCanvas)
                freeCanvas.style.pointerEvents = ['freehand', 'brush', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur)
                eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }

            var LABELS = {
                pan: '🖐 Hand', select: '↖ Pilih', highlight: '✏️ Highlight',
                underline: '__ Underline', strikethrough: '~~ Strikethrough',
                comment: '💬 Komentar', freehand: '🖊 Pen', brush: '🖌️ Brush',
                shape: '⬛ Shape', eraser: '🧹 Hapus', sticky: '📌 Sticky', 'copy-text': '📋 Salin Teks'
            };
            var lbl = document.getElementById('rpv-active-label'); if (lbl) lbl.textContent = LABELS[tool] || tool;
            var sz = document.getElementById('rpv-sizes');
            if (sz) sz.style.display = ['freehand', 'brush', 'shape'].includes(tool) ? 'flex' : 'none';
            var sh = document.getElementById('rpv-shapes');
            if (sh) sh.classList.toggle('show', tool === 'shape');
        }

        /* FIX-6: fungsi terpisah untuk pointer-events textLayer */
        function applyTextLayerPE() {
            var needsSel = ['highlight', 'comment', 'underline', 'strikethrough', 'copy-text'].includes(activeTool);
            if (!textLayer) return;
            textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
            textLayer.style.userSelect = needsSel ? 'text' : 'none';
            textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
        }

        document.querySelectorAll('.rpv-tool[data-tool]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.rpv-tool[data-tool]').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active'); setTool(btn.dataset.tool);
            });
        });
        document.querySelectorAll('.rpv-color').forEach(function (sw) {
            sw.addEventListener('click', function () {
                document.querySelectorAll('.rpv-color').forEach(function (s) { s.classList.remove('selected'); });
                sw.classList.add('selected'); activeColor = sw.dataset.color;
            });
        });
        document.querySelectorAll('.rpv-size').forEach(function (d) {
            d.addEventListener('click', function () {
                document.querySelectorAll('.rpv-size').forEach(function (x) { x.classList.remove('selected'); });
                d.classList.add('selected'); activeSize = +d.dataset.size;
            });
        });
        document.querySelectorAll('.rpv-shape').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll('.rpv-shape').forEach(function (x) { x.classList.remove('active'); });
                b.classList.add('active'); activeShape = b.dataset.shape;
            });
        });

        /* ── Text selection handler ───────────────────────────────── */
        function getSelInfo() {
            var sel = window.getSelection(); if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            var range = sel.getRangeAt(0);
            if (!textLayer || !textLayer.contains(range.commonAncestorContainer)) return null;
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

                /* copy-text mode: tidak buat anotasi, cukup salin */
                if (activeTool === 'copy-text') {
                    if (info.text && info.text.trim()) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(info.text.trim())
                                .then(function () { snack('📋 Teks disalin!', '#22c55e'); })
                                .catch(function () { snack('📋 Teks dipilih — Ctrl+C untuk salin', '#60A5FA'); });
                        } else {
                            snack('📋 Teks dipilih — Ctrl+C untuk salin', '#60A5FA');
                        }
                    }
                    return;
                }

                var base = {
                    page: pageNum, color: activeColor,
                    rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h,
                    selected_text: info.text
                };
                if (activeTool === 'highlight') {
                    await addAnnot(Object.assign({ type: 'highlight' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges();
                    snack('✏️ Highlight!');
                } else if (activeTool === 'underline') {
                    await addAnnot(Object.assign({ type: 'underline' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges();
                    snack('__ Underline!');
                } else if (activeTool === 'strikethrough') {
                    await addAnnot(Object.assign({ type: 'strikethrough' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges();
                    snack('~~ Strikethrough!');
                } else if (activeTool === 'comment') {
                    pendingRect = info.rect; pendingText = info.text;
                    var pop = document.getElementById('rpv-comment-pop');
                    if (pop) {
                        var vw2 = window.innerWidth, vh2 = window.innerHeight, pw = 284, ph = 170;
                        pop.style.left = Math.max(4, Math.min(info.br.left - pw / 2, vw2 - pw - 4)) + 'px';
                        pop.style.top = Math.max(4, info.br.bottom + ph > vh2 ? info.br.top - ph - 8 : info.br.bottom + 8) + 'px';
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

        /* Comment popup */
        on('rpv-comment-save', 'click', async function () {
            var txtEl = document.getElementById('rpv-comment-txt');
            var txt = txtEl ? txtEl.value.trim() : '';
            if (!txt) { snack('Tulis komentar dulu!'); return; }
            if (!pendingRect) { snack('Pilih teks dulu!'); return; }
            var rect = { x: pendingRect.x, y: pendingRect.y, w: pendingRect.w, h: pendingRect.h }, selTxt = pendingText;
            if (txtEl) txtEl.value = '';
            var pop = document.getElementById('rpv-comment-pop'); if (pop) pop.classList.remove('show');
            pendingRect = null; pendingText = null;
            await addAnnot({
                page: pageNum, type: 'comment', color: activeColor,
                rect_x: rect.x, rect_y: rect.y, rect_w: rect.w, rect_h: rect.h,
                selected_text: selTxt || '', comment: txt
            });
            window.getSelection() && window.getSelection().removeAllRanges();
            snack('💬 Komentar disimpan!');
        });
        on('rpv-comment-cancel', 'click', function () {
            var pop = document.getElementById('rpv-comment-pop'); if (pop) pop.classList.remove('show');
            pendingRect = null; pendingText = null;
            window.getSelection() && window.getSelection().removeAllRanges();
        });

        /* Sticky popup */
        on('rpv-sticky-save', 'click', async function () {
            var txtEl = document.getElementById('rpv-sticky-txt');
            var txt = txtEl ? txtEl.value.trim() : '';
            if (!txt) { snack('Tulis catatan dulu!'); return; }
            if (!stickyPos) { snack('Klik area PDF dulu!'); return; }
            var pos = { x: stickyPos.x, y: stickyPos.y };
            if (txtEl) txtEl.value = '';
            var pop = document.getElementById('rpv-sticky-pop'); if (pop) pop.classList.remove('show');
            stickyPos = null;
            await addAnnot({
                page: pageNum, type: 'sticky', color: activeColor,
                rect_x: pos.x, rect_y: pos.y, rect_w: 180, rect_h: 90, comment: txt
            });
            snack('📌 Sticky note ditempel!');
        });
        on('rpv-sticky-cancel', 'click', function () {
            var pop = document.getElementById('rpv-sticky-pop'); if (pop) pop.classList.remove('show');
            stickyPos = null;
        });

        /* ── Freehand / Brush ─────────────────────────────────────── */
        function getFHSize() { return activeTool === 'brush' ? Math.max(6, activeSize * 3.5) : activeSize; }
        function getFHAlpha() { return activeTool === 'brush' ? .5 : .92; }

        function fhStart(e) {
            if (activeTool !== 'freehand' && activeTool !== 'brush') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true; freePoints = [];
            var p = stageXY(e), s = baseScale * zoomFactor;
            freePoints.push([p.x / s, p.y / s]);
        }
        function fhMove(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return;
            if (e.cancelable) e.preventDefault();
            var p = stageXY(e), s = baseScale * zoomFactor; freePoints.push([p.x / s, p.y / s]);
            if (!freeCtx || freePoints.length < 2) return;
            var last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1];
            freeCtx.save();
            freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = getFHSize() * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = getFHAlpha();
            freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s);
            freeCtx.stroke(); freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return;
            if (e.cancelable) e.preventDefault(); isDrawing = false;
            if (freePoints.length < 2) return;
            var xs = freePoints.map(function (p) { return p[0]; });
            var ys = freePoints.map(function (p) { return p[1]; });
            var bx = Math.min.apply(null, xs), by = Math.min.apply(null, ys);
            await addAnnot({
                page: pageNum, type: 'freehand', color: activeColor, stroke_width: getFHSize(),
                path_points: freePoints, rect_x: bx, rect_y: by,
                rect_w: Math.max.apply(null, xs) - bx, rect_h: Math.max.apply(null, ys) - by
            });
        }

        /* ── Shape ────────────────────────────────────────────────── */
        var shapePreviewSVGEl = null;
        function getOrCreateSVG() {
            if (!shapePreviewSVGEl) {
                shapePreviewSVGEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                shapePreviewSVGEl.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:26;overflow:visible;';
                shapePreviewSVGEl.setAttribute('width', stage.offsetWidth);
                shapePreviewSVGEl.setAttribute('height', stage.offsetHeight);
                stage.appendChild(shapePreviewSVGEl);
            }
            return shapePreviewSVGEl;
        }
        function clearShapePreview() { if (shapePreviewSVGEl) shapePreviewSVGEl.innerHTML = ''; }
        function destroyShapePreview() {
            if (shapePreviewSVGEl) { if (shapePreviewSVGEl.parentNode) shapePreviewSVGEl.parentNode.removeChild(shapePreviewSVGEl); shapePreviewSVGEl = null; }
        }
        function updateShapePreview(x1, y1, x2, y2) {
            var svg = getOrCreateSVG();
            var col = hex(activeColor), sw = Math.max(1, activeSize);
            var w = Math.abs(x2 - x1), h = Math.abs(y2 - y1);
            var minX = Math.min(x1, x2), minY = Math.min(y1, y2);
            var inner = '';
            if (activeShape === 'rect')
                inner = '<rect x="' + (minX + sw / 2) + '" y="' + (minY + sw / 2) + '" width="' + Math.max(1, w - sw) +
                    '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col +
                    '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            else if (activeShape === 'ellipse')
                inner = '<ellipse cx="' + (minX + w / 2) + '" cy="' + (minY + h / 2) +
                    '" rx="' + Math.max(1, w / 2 - sw / 2) + '" ry="' + Math.max(1, h / 2 - sw / 2) +
                    '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            else if (activeShape === 'line')
                inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col +
                    '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/>';
            else if (activeShape === 'arrow') {
                var dx = x2 - x1, dy = y2 - y1, len = Math.sqrt(dx * dx + dy * dy);
                if (len >= 4) {
                    var hLen = Math.min(len * .35, Math.max(12, sw * 5)), ang = Math.atan2(dy, dx);
                    var ax1 = x2 - hLen * Math.cos(ang - Math.PI / 6), ay1 = y2 - hLen * Math.sin(ang - Math.PI / 6);
                    var ax2 = x2 - hLen * Math.cos(ang + Math.PI / 6), ay2 = y2 - hLen * Math.sin(ang + Math.PI / 6);
                    inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col +
                        '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/>' +
                        '<polyline points="' + ax1 + ',' + ay1 + ' ' + x2 + ',' + y2 + ' ' + ax2 + ',' + ay2 +
                        '" fill="none" stroke="' + col + '" stroke-width="' + sw +
                        '" stroke-linecap="round" stroke-linejoin="round"/>';
                }
            }
            svg.innerHTML = inner;
        }

        var shX1 = 0, shY1 = 0;
        function shStart(e) {
            if (activeTool !== 'shape') return; if (e.cancelable) e.preventDefault();
            isDrawing = true; var p = stageXY(e); drawStart = p; shX1 = p.x; shY1 = p.y;
            getOrCreateSVG();
        }
        function shMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !drawStart) return;
            if (e.cancelable) e.preventDefault();
            var c = stageXY(e); updateShapePreview(shX1, shY1, c.x, c.y);
        }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault(); isDrawing = false; clearShapePreview();
            var c = stageXY(e), s = baseScale * zoomFactor; if (!drawStart) return;
            var x1 = shX1 / s, y1 = shY1 / s, x2 = c.x / s, y2 = c.y / s; drawStart = null;
            if (Math.abs(x2 - x1) < 2 && Math.abs(y2 - y1) < 2) return;
            var rx = Math.min(x1, x2), ry = Math.min(y1, y2);
            await addAnnot({
                page: pageNum, type: 'shape', color: activeColor, shape_type: activeShape,
                stroke_width: activeSize, rect_x: rx, rect_y: ry,
                rect_w: Math.abs(x2 - x1), rect_h: Math.abs(y2 - y1),
                path_points: [[x1, y1], [x2, y2]],
                arrow_x1: x1, arrow_y1: y1, arrow_x2: x2, arrow_y2: y2
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

        /* ── Eraser cursor ────────────────────────────────────────── */
        document.addEventListener('mousemove', function (e) {
            if (!eraserCur) return;
            eraserCur.style.display = activeTool === 'eraser' ? 'block' : 'none';
            if (activeTool === 'eraser') { eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; }
        });

        /* ── Stage click (sticky) ─────────────────────────────────── */
        stage.addEventListener('click', function (e) {
            if (e.target === freeCanvas) return;
            var hit = e.target.closest && (e.target.closest('[data-annot-id]') || e.target.closest('.rpv-sticky-note'));
            if (activeTool === 'sticky') {
                if (hit || (e.target.closest && e.target.closest('.rpv-popup'))) return;
                var p = stageXY(e), s = baseScale * zoomFactor;
                stickyPos = { x: p.x / s, y: p.y / s };
                var pop = document.getElementById('rpv-sticky-pop');
                if (pop) {
                    var vw2 = window.innerWidth, vh2 = window.innerHeight, pw = 280, ph = 150;
                    pop.style.left = Math.max(4, Math.min(e.clientX - pw / 2, vw2 - pw - 4)) + 'px';
                    pop.style.top = Math.max(4, e.clientY + ph > vh2 ? e.clientY - ph - 8 : e.clientY + 8) + 'px';
                    pop.classList.add('show');
                    var t = document.getElementById('rpv-sticky-txt');
                    if (t) { t.value = ''; setTimeout(function () { t.focus(); }, 50); }
                }
                return;
            }
            if (activeTool === 'select' && !hit) { selectedId = null; scheduleRender(); return; }
            if (activeTool === 'eraser' && !hit) { snack('Klik anotasi untuk menghapus', '#60A5FA'); return; }
        });

        /* ── Pan ──────────────────────────────────────────────────── */
        stage.addEventListener('mousedown', function (e) {
            if (activeTool !== 'pan') return;
            isPanning = true; panSX = e.clientX; panSY = e.clientY;
            panScrollX = wrap ? wrap.scrollLeft : 0; panScrollY = wrap ? wrap.scrollTop : 0;
            if (e.cancelable) e.preventDefault();
        }, { passive: false });
        document.addEventListener('mousemove', function (e) {
            if (!isPanning || activeTool !== 'pan') return;
            if (wrap) { wrap.scrollLeft = panScrollX + (panSX - e.clientX); wrap.scrollTop = panScrollY + (panSY - e.clientY); }
        });
        document.addEventListener('mouseup', function () { isPanning = false; });

        /* ── Touch pinch zoom ─────────────────────────────────────── */
        var lpd = 0, swX = 0, swY = 0;
        if (wrap) {
            wrap.addEventListener('touchstart', function (e) {
                if (e.touches.length === 2)
                    lpd = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
                if (e.touches.length === 1) { swX = e.touches[0].clientX; swY = e.touches[0].clientY; }
            }, { passive: true });
            wrap.addEventListener('touchmove', function (e) {
                if (e.touches.length !== 2) return;
                var d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
                if (Math.abs(d - lpd) > 14) { d > lpd ? doZoom(1) : doZoom(-1); lpd = d; }
            }, { passive: true });
            wrap.addEventListener('touchend', function (e) {
                if (e.changedTouches.length !== 1) return;
                var dx = swX - e.changedTouches[0].clientX, dy = swY - e.changedTouches[0].clientY;
                if (Math.abs(dx) > Math.abs(dy) * 1.8 && Math.abs(dx) > 60) {
                    if (['freehand', 'brush', 'shape', 'pan'].includes(activeTool)) return;
                    dx > 0 ? nextPage() : prevPage();
                }
            }, { passive: true });
        }

        /* ── Fullscreen ───────────────────────────────────────────── */
        function updateFsBtn() {
            var btn = document.getElementById('rpv-fs-btn'); if (!btn) return;
            btn.innerHTML = isFullscreen
                ? '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Keluar</span>'
                : '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg><span>Layar Penuh</span>';
        }
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

        /* ── Resume toast ─────────────────────────────────────────── */
        function showResume(savedPg) {
            if (savedPg <= 1 || !pdfDoc || savedPg > pdfDoc.numPages) return;
            var t = document.getElementById('rpv-resume-toast');
            if (!t) {
                t = document.createElement('div'); t.id = 'rpv-resume-toast';
                t.style.cssText = 'position:fixed;bottom:5rem;left:50%;' +
                    'transform:translateX(-50%) translateY(80px);background:#1a1a1a;' +
                    'border:1.5px solid #FF6B18;color:#fff;padding:.6rem .875rem;' +
                    'border-radius:14px;font-size:13px;z-index:20010;display:flex;' +
                    'align-items:center;gap:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.5);' +
                    'opacity:0;transition:all .4s;pointer-events:none;white-space:nowrap;max-width:90vw;';
                t.innerHTML = '<span style="font-size:1.2rem;">🔖</span>' +
                    '<div><p style="font-weight:700;margin:0;font-size:12px;">Lanjut membaca?</p>' +
                    '<p style="color:#9ca3af;margin:0;font-size:11px;" id="rpv-resume-txt">Hal. ' + savedPg + '</p></div>' +
                    '<button type="button" id="rpv-resume-yes" style="padding:.3rem .7rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;pointer-events:auto;">Lanjut</button>' +
                    '<button type="button" id="rpv-resume-no" style="padding:.3rem .6rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:11px;cursor:pointer;pointer-events:auto;">Awal</button>';
                document.body.appendChild(t);
            }
            var rt = document.getElementById('rpv-resume-txt'); if (rt) rt.textContent = 'Terakhir di halaman ' + savedPg;
            requestAnimationFrame(function () { t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)'; t.style.pointerEvents = 'auto'; });
            function hide() { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(80px)'; t.style.pointerEvents = 'none'; }
            var auto = setTimeout(hide, 8000);
            var yes = document.getElementById('rpv-resume-yes'); if (yes) yes.onclick = function () { clearTimeout(auto); hide(); renderPage(savedPg); };
            var no = document.getElementById('rpv-resume-no'); if (no) no.onclick = function () { clearTimeout(auto); hide(); renderPage(1); };
        }

        /* ── Mobile bottom sheet ──────────────────────────────────── */
        function openSheet() { var s = document.getElementById('rpv-bottom-sheet'), b = document.getElementById('rpv-sheet-backdrop'); if (s) s.classList.add('show'); if (b) b.classList.add('show'); }
        function closeSheet() { var s = document.getElementById('rpv-bottom-sheet'), b = document.getElementById('rpv-sheet-backdrop'); if (s) s.classList.remove('show'); if (b) b.classList.remove('show'); }

        function bindSheet() {
            on('rpv-mobile-fab-btn', 'click', openSheet);
            on('rpv-sheet-backdrop', 'click', closeSheet);
            on('rpv-sheet-close', 'click', closeSheet);
            on('rpv-sheet-prev', 'click', prevPage);
            on('rpv-sheet-next', 'click', nextPage);
            on('rpv-sheet-zoom-in', 'click', function () { doZoom(1); });
            on('rpv-sheet-zoom-out', 'click', function () { doZoom(-1); });
            on('rpv-sheet-fs', 'click', function () { closeSheet(); setTimeout(function () { isFullscreen ? exitFS() : enterFS(); }, 200); });
            on('rpv-sheet-search', 'click', function () { closeSheet(); setTimeout(openSearch, 200); });
            on('rpv-sheet-bookmark-btn', 'click', function () { toggleBookmark(); closeSheet(); });
            document.querySelectorAll('[data-rpv-sheet-mode]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('[data-rpv-sheet-mode]').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active'); applyMode(btn.dataset.rpvSheetMode); closeSheet();
                });
            });
        }
        bindSheet();

        /* ── Export PDF dengan anotasi ────────────────────────────── */
        on('rpv-download-btn', 'click', async function () {
            if (exportBusy) { snack('⏳ Sedang export...'); return; }
            if (!pdfDoc) { snack('PDF belum dimuat!'); return; }
            var jsPDFLib = window.jspdf && window.jspdf.jsPDF || window.jsPDF;
            if (!jsPDFLib) { snack('⚠️ Library jsPDF belum siap', '#F59E0B'); return; }
            exportBusy = true; if (exportOL) exportOL.classList.add('show');
            try {
                var SCALE = 2, offC = document.createElement('canvas'), offCtx = offC.getContext('2d'), pdf = null;
                for (var p = 1; p <= pdfDoc.numPages; p++) {
                    var pg = await pdfDoc.getPage(p), vp = pg.getViewport({ scale: SCALE });
                    offC.width = Math.floor(vp.width); offC.height = Math.floor(vp.height);
                    offCtx.clearRect(0, 0, offC.width, offC.height);
                    await pg.render({ canvasContext: offCtx, viewport: vp }).promise;
                    annots.filter(function (a) { return a.page === p; }).forEach(function (a) { drawOnCanvas(offCtx, a, SCALE); });
                    var wMm = vp.width * .264583, hMm = vp.height * .264583;
                    if (!pdf) pdf = new jsPDFLib({ orientation: vp.width > vp.height ? 'landscape' : 'portrait', unit: 'mm', format: [wMm, hMm] });
                    else pdf.addPage([wMm, hMm], vp.width > vp.height ? 'landscape' : 'portrait');
                    pdf.addImage(offC.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, wMm, hMm, '', 'FAST');
                    showSync('Halaman ' + p + '/' + pdfDoc.numPages + '...');
                    var expStatus = document.getElementById('rpv-export-status');
                    if (expStatus) expStatus.textContent = 'Halaman ' + p + ' dari ' + pdfDoc.numPages;
                }
                pdf.save('review-annotated-' + Date.now() + '.pdf');
                snack('✅ PDF berhasil didownload!', '#22c55e'); showSync('Export selesai ✓', true);
            } catch (err) {
                console.error('[RPV] export:', err); snack('❌ Gagal: ' + err.message, '#ef4444');
            } finally {
                exportBusy = false; if (exportOL) exportOL.classList.remove('show');
            }
        });

        function drawOnCanvas(c, a, s) {
            c.save();
            var col = hex(a.color);
            if (a.type === 'highlight' || a.type === 'comment') {
                if (!a.rect) { c.restore(); return; }
                c.globalAlpha = .38; c.fillStyle = col;
                c.fillRect(a.rect.x * s, a.rect.y * s, a.rect.w * s, a.rect.h * s);
            } else if (a.type === 'underline') {
                if (!a.rect) { c.restore(); return; }
                c.globalAlpha = .75; c.fillStyle = col;
                c.fillRect(a.rect.x * s, (a.rect.y + a.rect.h) * s - 1, a.rect.w * s, Math.max(1.5, 2 * s));
            } else if (a.type === 'strikethrough') {
                if (!a.rect) { c.restore(); return; }
                c.globalAlpha = .75; c.fillStyle = col;
                var st2 = Math.max(1.5, 2 * s);
                c.fillRect(a.rect.x * s, a.rect.y * s + a.rect.h * s * 0.62 - st2 / 2, a.rect.w * s, st2);
            } else if (a.type === 'freehand') {
                if (!a.path_points || !a.path_points.length) { c.restore(); return; }
                c.globalAlpha = .92; c.strokeStyle = col; c.lineWidth = (a.stroke_width || 2) * s;
                c.lineCap = 'round'; c.lineJoin = 'round'; c.beginPath();
                c.moveTo(a.path_points[0][0] * s, a.path_points[0][1] * s);
                for (var i = 1; i < a.path_points.length; i++) c.lineTo(a.path_points[i][0] * s, a.path_points[i][1] * s);
                c.stroke();
            } else if (a.type === 'shape') {
                if (!a.rect) { c.restore(); return; }
                var sw = (a.stroke_width || 2) * s; c.globalAlpha = .85; c.strokeStyle = col;
                c.lineWidth = sw; c.lineCap = 'round'; c.lineJoin = 'round';
                var stype = a.shape_type || 'rect';
                if (stype === 'rect') {
                    c.strokeRect(a.rect.x * s + sw / 2, a.rect.y * s + sw / 2, Math.max(1, a.rect.w * s - sw), Math.max(1, a.rect.h * s - sw));
                } else if (stype === 'ellipse') {
                    c.beginPath();
                    c.ellipse((a.rect.x + a.rect.w / 2) * s, (a.rect.y + a.rect.h / 2) * s,
                        Math.max(1, a.rect.w * s / 2 - sw / 2), Math.max(1, a.rect.h * s / 2 - sw / 2), 0, 0, Math.PI * 2);
                    c.stroke();
                } else if (stype === 'line' || stype === 'arrow') {
                    var lx1 = a.arrow_x1 != null ? a.arrow_x1 * s : a.rect.x * s;
                    var ly1 = a.arrow_y1 != null ? a.arrow_y1 * s : (a.rect.y + a.rect.h / 2) * s;
                    var lx2 = a.arrow_x2 != null ? a.arrow_x2 * s : (a.rect.x + a.rect.w) * s;
                    var ly2 = a.arrow_y2 != null ? a.arrow_y2 * s : (a.rect.y + a.rect.h / 2) * s;
                    c.beginPath(); c.moveTo(lx1, ly1); c.lineTo(lx2, ly2); c.stroke();
                    if (stype === 'arrow') {
                        var adx = lx2 - lx1, ady = ly2 - ly1, alen = Math.sqrt(adx * adx + ady * ady);
                        if (alen >= 2) {
                            var hLen2 = Math.min(alen * .35, Math.max(10, sw * 5)), aAng = Math.atan2(ady, adx);
                            c.beginPath();
                            c.moveTo(lx2 - hLen2 * Math.cos(aAng - Math.PI / 6), ly2 - hLen2 * Math.sin(aAng - Math.PI / 6));
                            c.lineTo(lx2, ly2);
                            c.lineTo(lx2 - hLen2 * Math.cos(aAng + Math.PI / 6), ly2 - hLen2 * Math.sin(aAng + Math.PI / 6));
                            c.stroke();
                        }
                    }
                }
            } else if (a.type === 'sticky') {
                if (!a.rect || !a.comment) { c.restore(); return; }
                /* Sticky presisi — ukuran konsisten dengan CSS */
                var CSS_W = 180, CSS_H = 90, CSS_FS = 12, CSS_PAD = 8, CSS_HDR = 22;
                var screenS = baseScale * zoomFactor, ratio = s / screenS;
                var sW = CSS_W * ratio, sH = CSS_H * ratio, hdrH = CSS_HDR * ratio;
                var fsB = CSS_FS * ratio, padX = CSS_PAD * ratio;
                var sx = a.rect.x * s, sy = a.rect.y * s;
                c.globalAlpha = .92; c.fillStyle = STICKY_BG[a.color] || '#FEF9C3';
                c.beginPath();
                if (c.roundRect) c.roundRect(sx, sy, sW, sH, 4 * ratio); else c.rect(sx, sy, sW, sH);
                c.fill();
                c.globalAlpha = 1; c.strokeStyle = STICKY_BORDER[a.color] || '#FDE047';
                c.lineWidth = Math.max(1, 1.5 * ratio);
                c.beginPath();
                if (c.roundRect) c.roundRect(sx, sy, sW, sH, 4 * ratio); else c.rect(sx, sy, sW, sH);
                c.stroke();
                c.globalAlpha = .2; c.fillStyle = STICKY_BORDER[a.color] || '#FDE047';
                c.fillRect(sx, sy, sW, hdrH);
                c.globalAlpha = 1; c.font = Math.round(fsB * 1.1) + 'px serif';
                c.fillStyle = a.color === 'black' ? '#9CA3AF' : '#374151';
                c.fillText('📌', sx + padX * .5, sy + hdrH - ratio * 2);
                c.fillStyle = a.color === 'black' ? '#D1D5DB' : '#374151';
                c.font = '500 ' + Math.round(fsB) + 'px ui-sans-serif,system-ui,sans-serif';
                var lX2 = sx + padX, lY2 = sy + hdrH + fsB + ratio * 2, maxW2 = sW - padX * 2, lineH = fsB * 1.5;
                var words = a.comment.split(' '), line = '';
                for (var wi = 0; wi < words.length; wi++) {
                    var test = line + words[wi] + ' ';
                    if (c.measureText(test).width > maxW2 && line !== '') {
                        c.fillText(line.trimEnd(), lX2, lY2); line = words[wi] + ' '; lY2 += lineH;
                        if (lY2 > sy + sH - ratio * 3) { c.fillText('...', lX2, lY2 - lineH + fsB); break; }
                    } else line = test;
                }
                if (line.trim() && lY2 <= sy + sH - ratio * 3) c.fillText(line.trimEnd(), lX2, lY2);
            }
            c.restore();
        }

        /* ── PDF Render ───────────────────────────────────────────── */
        /* FIX-3 & FIX-11: computeBase set needsRecompute=false di dalam */
        function computeBase(page) {
            var cw = wrap ? wrap.clientWidth : window.innerWidth;
            var nw = page.getViewport({ scale: 1 }).width;
            baseScale = Math.max(.5, Math.min((cw - 4) / nw, 2.5));
            needsRecompute = false; /* FIX-11 */
        }

        function prevPage() { if (pageNum > 1) { pageNum--; renderPage(pageNum); } }
        function nextPage() { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } }

        function renderPage(num) {
            if (num < 1 || (pdfDoc && num > pdfDoc.numPages)) return;
            if (pageRendering) { pendingPage = num; return; }
            pageRendering = true; pageNum = num; saveLast(num);

            destroyShapePreview();
            document.querySelectorAll('.rpv-popup').forEach(function (p) { p.classList.remove('show'); });
            if (tooltip) tooltip.classList.remove('show');
            pendingRect = null; pendingText = null; stickyPos = null;
            if (window.getSelection) window.getSelection().removeAllRanges();

            pdfDoc.getPage(num).then(async function (page) {
                if (needsRecompute) computeBase(page);

                var cs = baseScale * zoomFactor;
                var vpCss = page.getViewport({ scale: cs });
                var vpR = page.getViewport({ scale: cs * DPR });

                mainCanvas.width = Math.floor(vpR.width);
                mainCanvas.height = Math.floor(vpR.height);
                mainCanvas.style.width = Math.floor(vpCss.width) + 'px';
                mainCanvas.style.height = Math.floor(vpCss.height) + 'px';
                stage.style.width = Math.floor(vpCss.width) + 'px';
                stage.style.height = Math.floor(vpCss.height) + 'px';

                await page.render({ canvasContext: ctx, viewport: vpR }).promise.catch(function (e) { console.warn(e.message); });
                pageRendering = false;

                if (pendingPage !== null) { var pp = pendingPage; pendingPage = null; renderPage(pp); return; }

                /* FIX-6: re-apply pointer-events setelah render */
                applyTextLayerPE();

                /* Render text layer */
                textLayer.innerHTML = '';
                textLayer.style.width = Math.floor(vpCss.width) + 'px';
                textLayer.style.height = Math.floor(vpCss.height) + 'px';
                var content = await page.getTextContent();
                content.items.forEach(function (item) {
                    if (!item.str || !item.str.trim()) return;
                    var tx = pdfjsLib.Util.transform(vpCss.transform, item.transform);
                    var fh = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]);
                    var angle = Math.atan2(tx[1], tx[0]);
                    var span = document.createElement('span');
                    span.textContent = item.str;
                    span.style.cssText =
                        'position:absolute;left:' + tx[4] + 'px;top:' + (tx[5] - fh) + 'px;' +
                        'font-size:' + fh + 'px;line-height:1;white-space:pre;' +
                        'padding:0;margin:0;color:transparent;cursor:text;' +
                        'transform-origin:0% 0%;-webkit-touch-callout:none;';
                    textLayer.appendChild(span);
                    var tw = item.width * cs;
                    var mw = span.scrollWidth || span.getBoundingClientRect().width;
                    var tf = '';
                    if (angle !== 0) tf = 'rotate(' + -angle + 'rad)';
                    if (mw > 1 && tw > 0 && Math.abs(tw - mw) > 0.5) tf += (tf ? ' ' : '') + 'scaleX(' + (tw / mw) + ')';
                    if (tf) span.style.transform = tf;
                });

                scheduleRender();
                stage.style.display = 'block';
                /* FIX-2 & FIX-10 */
                hideLoading();

                /* update UI */
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.value = num;
                var ptEl = document.getElementById('rpv-page-total'); if (ptEl && pdfDoc) ptEl.textContent = pdfDoc.numPages;
                var prevEl = document.getElementById('rpv-prev'); if (prevEl) prevEl.disabled = num <= 1;
                var nextEl = document.getElementById('rpv-next'); if (nextEl) nextEl.disabled = !pdfDoc || num >= pdfDoc.numPages;
                var zvEl = document.getElementById('rpv-zoom-val'); if (zvEl) zvEl.textContent = Math.round(zoomFactor * 100) + '%';
                var spEl = document.getElementById('rpv-sheet-page'); if (spEl) spEl.textContent = num;
                if (wrap) wrap.scrollTo({ top: 0, behavior: 'smooth' });

                /* FIX-1: update progress bar ke posisi baca */
                updateReadProgress();
                updateBMBtn();

                /* FIX-12: simpan zoomFactor */
                try { localStorage.setItem(SK_ZOOM, zoomFactor); } catch (e) { }

            }).catch(function (e) {
                console.error('[RPV] render error:', e);
                pageRendering = false;
                /* FIX-2: selalu hide loading saat error */
                hideLoading();
                stage.style.display = 'block';
            });
        }

        on('rpv-prev', 'click', prevPage);
        on('rpv-next', 'click', nextPage);
        on('rpv-page-input', 'change', function () {
            var n = parseInt(this.value);
            if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) renderPage(n);
            else this.value = pageNum;
        });

        function doZoom(dir) {
            zoomFactor = dir > 0 ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX) : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN);
            needsRecompute = true;
            var zvEl = document.getElementById('rpv-zoom-val'); if (zvEl) zvEl.textContent = Math.round(zoomFactor * 100) + '%';
            var szv = document.getElementById('rpv-sheet-zoom-val'); if (szv) szv.textContent = Math.round(zoomFactor * 100) + '%';
            try { localStorage.setItem(SK_ZOOM, zoomFactor); } catch (e) { }
            if (pdfDoc) renderPage(pageNum);
        }
        on('rpv-zoom-in', 'click', function () { doZoom(1); });
        on('rpv-zoom-out', 'click', function () { doZoom(-1); });

        /* FIX-7: threshold 40px + orientationchange */
        var resT = null, lastW = wrap ? wrap.clientWidth : 0;
        window.addEventListener('resize', function () {
            var w = wrap ? wrap.clientWidth : 0;
            if (Math.abs(w - lastW) < 40) return; lastW = w;
            clearTimeout(resT);
            resT = setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; renderPage(pageNum); }, 300);
        });
        window.addEventListener('orientationchange', function () {
            setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; lastW = 0; renderPage(pageNum); }, 400);
        });

        /* FIX-5: ResizeObserver pengganti MutationObserver */
        if (typeof ResizeObserver !== 'undefined' && mainCanvas) {
            new ResizeObserver(function () { syncFC(); }).observe(mainCanvas);
        }

        /* ── Keyboard shortcuts ───────────────────────────────────── */
        document.addEventListener('keydown', function (e) {
            if (e.target.id === 'rpv-search-input') return;
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') { e.preventDefault(); openSearch(); return; }
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); return; }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); return; }
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; return; }
            switch (e.key) {
                case 'ArrowLeft': prevPage(); break;
                case 'ArrowRight': nextPage(); break;
                case '+': case '=': doZoom(1); break;
                case '-': doZoom(-1); break;
                case 'b': case 'B': toggleBookmark(); break;
                case 'f': case 'F': isFullscreen ? exitFS() : enterFS(); break;
                case 'Escape': {
                    var ov = document.getElementById('rpv-search');
                    if (ov && ov.classList.contains('show')) closeSearch();
                    else if (isFullscreen) exitFS();
                    break;
                }
            }
        });

        /* ── Load PDF ─────────────────────────────────────────────── */
        function startViewer() {
            stage.style.display = 'none';
            showLoading('Memuat dokumen...');

            /* Gunakan cache jika ada */
            if (pdfDoc) {
                console.log('[RPV] using cached PDF');
                var ptEl = document.getElementById('rpv-page-total'); if (ptEl) ptEl.textContent = pdfDoc.numPages;
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.max = pdfDoc.numPages;
                needsRecompute = true;
                /* FIX-1: set progress 100% untuk cached */
                updateLoadProgress(100);
                renderPage(1); loadAll(); return;
            }

            var task = pdfjsLib.getDocument({
                url: CFG.pdfUrl, withCredentials: false, verbosity: 0, rangeChunkSize: 65536
            });

            /* FIX-1: update progress bar saat download */
            task.onProgress = function (d) {
                if (d.total > 0) {
                    var pct = Math.min(99, Math.round(d.loaded / d.total * 100));
                    updateLoadProgress(pct);
                }
            };

            task.promise.then(async function (doc) {
                pdfDoc = doc; window[CACHE_KEY] = doc;
                console.log('[RPV] PDF loaded,', doc.numPages, 'pages');

                /* FIX-1: set 100% setelah load selesai */
                updateLoadProgress(100);

                var ptEl = document.getElementById('rpv-page-total'); if (ptEl) ptEl.textContent = doc.numPages;
                var piEl = document.getElementById('rpv-page-input'); if (piEl) piEl.max = doc.numPages;

                renderPage(1);
                await loadAll();

                var saved = loadLast();
                if (saved > 1) setTimeout(function () { showResume(saved); }, 1200);

                var bm = getBM();
                if (bm && bm !== saved && bm <= doc.numPages) {
                    setTimeout(function () {
                        snack('🔖 Tanda baca ada di hal.' + bm + ' — klik 🔖 untuk ke sana', '#60A5FA');
                    }, 2500);
                }
                updateBMBtn();
                console.log('[RPV] ready, reviewId=', CFG.reviewId);

            }).catch(function (err) {
                console.error('[RPV] PDF load error:', err);
                /* FIX-2: tampilkan error, jangan spinning forever */
                if (loadingEl) {
                    showLoading();
                    loadingEl.innerHTML =
                        '<div style="font-size:2rem">⚠️</div>' +
                        '<p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat PDF</p>' +
                        '<p style="color:#6b7280;font-size:11px;margin:.25rem 0;">' + err.message + '</p>' +
                        '<button type="button" onclick="window.location.reload()" style="margin-top:.75rem;' +
                        'padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;' +
                        'font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
                    /* Jangan hideLoading() — biarkan pesan error terlihat */
                }
            });
        }

        /* Init tools */
        if (!IS_RO) setTool('highlight');
        startViewer();
    } /* end run() */

    window.RPV_boot = boot;

})();
