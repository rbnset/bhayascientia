/**
 * pdf-viewer.js — v4.0
 * public/js/pdf-viewer.js
 *
 * FITUR (setara review-pdf-viewer.js v6.0):
 * ── FIX ─────────────────────────────────────────────────────────
 * FIX-1  Loading progress bar sampai 100% (onProgress update bar)
 * FIX-2  Loading spinner berhenti setelah error (showLoading/hideLoading terpusat)
 * FIX-3  Horizontal scrollbar tidak muncul (overflow-x:hidden, padding 4px)
 * FIX-4  DPR di-cap ke 2 (tidak blur di layar retina 3x)
 * FIX-5  Tidak ada render loop (ResizeObserver + guard syncFC)
 * FIX-7  Resize threshold 40px + orientationchange handler
 * FIX-11 needsRecompute/baseScaleComputed di-reset di dalam computeBase
 * FIX-12 zoomFactor persist di localStorage
 *
 * ── FITUR ────────────────────────────────────────────────────────
 * FEAT-1  Bookmark — tandai halaman, persist localStorage
 * FEAT-2  Reading mode (Normal / Sepia / Night) — persist localStorage
 * FEAT-3  Resume toast — lanjut baca dari halaman terakhir
 * FEAT-4  Fullscreen mode
 * FEAT-5  Full-text search semua halaman, navigasi ↑↓
 * FEAT-6  Bottom sheet mobile + FAB button
 * FEAT-7  Touch pinch-to-zoom + swipe navigasi
 * FEAT-19 PDF cache (window[CACHE_KEY])
 * FEAT-20 Progress bar loading 0–100%
 */
(function () {
    'use strict';

    if (!window.PDF_CONFIG) {
        console.error('[pdf-viewer] window.PDF_CONFIG tidak ditemukan!');
        return;
    }

    const {
        pdfUrl,
        slug,
        guestPageLimit: GUEST_PAGE_LIMIT,
        isGuest: IS_GUEST,
        loginUrl,
        registerUrl,
    } = window.PDF_CONFIG;

    /* ── AUTH STATE CHECK ─────────────────────────────────────────── */
    const AUTH_KEY = 'pdf_auth_state';
    const currentAuthState = IS_GUEST ? 'guest' : 'auth';
    const prevAuthState = sessionStorage.getItem(AUTH_KEY);
    if (prevAuthState !== null && prevAuthState !== currentAuthState) {
        sessionStorage.setItem(AUTH_KEY, currentAuthState);
        window.location.reload();
        return;
    }
    sessionStorage.setItem(AUTH_KEY, currentAuthState);

    /* ── STORAGE KEYS ─────────────────────────────────────────────── */
    const SK = {
        page: `bp_${slug}`,
        zoom: `bz_${slug}`,
        mode: `bm_${slug}`,
        bkmk: `bb_${slug}`,
    };

    /* ── CACHE ────────────────────────────────────────────────────── */
    const CACHE_KEY = '_pdfv_' + btoa(pdfUrl).slice(0, 30).replace(/[^a-z0-9]/gi, '_');

    /* ── CDN ──────────────────────────────────────────────────────── */
    const WORKER_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    /* ── STATE ────────────────────────────────────────────────────── */
    /* FIX-4: cap DPR ke 2 */
    const DPR = Math.min(window.devicePixelRatio || 1, 2);

    let pdfDoc = window[CACHE_KEY] || null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let baseScale = 1.0;
    let baseScaleComputed = false;
    let needsRecompute = true;

    const ZOOM_MIN = 0.5;
    const ZOOM_MAX = 4.0;
    const ZOOM_STEP = 0.25;
    /* FIX-12: restore zoom dari localStorage */
    let zoomFactor = Math.max(ZOOM_MIN, parseFloat(localStorage.getItem(SK.zoom) || '1') || 1);

    let isFullscreen = false;
    let currentMode = localStorage.getItem(SK.mode) || 'normal';
    let bookmarkedPage = parseInt(localStorage.getItem(SK.bkmk)) || null;
    let savedPage = parseInt(localStorage.getItem(SK.page)) || 1;

    let searchResults = [], searchIndex = -1, searchHighlightEls = [];
    let currentSearchQuery = '';
    let searchDebounce = null;
    let gateShown = false;
    let sheetIsOpen = false;
    let toolbarTimer = null;
    let tapOverlayOpen = false;

    /* callbacks untuk pdf-annotations.js — array agar multiple listener ok */
    let _onReadyCbs = [];
    let _onReadyCb = null; /* alias tunggal (legacy) */
    let _onPageChangeCb = null;

    function _fireReady() {
        /* panggil semua callback onReady */
        const cbs = _onReadyCbs.slice(); _onReadyCbs = [];
        cbs.forEach(function (fn) { try { fn(); } catch (e) { console.error('[pdf-viewer] onReady cb:', e); } });
        if (_onReadyCb) { try { _onReadyCb(); } catch (e) { } _onReadyCb = null; }
    }

    /* ── DOM ──────────────────────────────────────────────────────── */
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas ? canvas.getContext('2d') : null;
    const stage = document.getElementById('pdf-stage');
    const textLayer = document.getElementById('text-layer');
    const annotLayer = document.getElementById('annotation-layer');
    const loadingEl = document.getElementById('pdf-loading');
    const canvasWrap = document.getElementById('pdf-canvas-wrapper');
    const viewerEl = document.getElementById('pdf-viewer-container');
    const iframeEl = document.getElementById('pdf-iframe');
    const fsTb = document.getElementById('pdf-fullscreen-toolbar');
    const deskHint = document.getElementById('desktop-hint');
    const annotTip = document.getElementById('annot-tooltip');
    const tapOverlay = document.getElementById('mobile-tap-overlay');
    const guestGate = document.getElementById('guest-gate-overlay');
    const limitWarn = document.getElementById('page-limit-warning');

    /* FIX-3: paksa overflow-x hidden */
    if (canvasWrap) canvasWrap.style.overflowX = 'hidden';

    const isMobile = () => window.innerWidth < 768;

    /* ── LOADING HELPERS (FIX-2 & FIX-10) ───────────────────────── */
    function showLoading(msg) {
        if (!loadingEl) return;
        loadingEl.classList.remove('hidden');
        loadingEl.style.display = 'flex';
        const sub = document.getElementById('pdf-load-sub');
        if (msg && sub) sub.textContent = msg;
    }
    function hideLoading() {
        if (!loadingEl) return;
        loadingEl.classList.add('hidden');
        loadingEl.style.display = 'none';
    }

    /* FIX-1: progress bar 0-100% */
    function updateLoadProgress(pct) {
        const bar = document.getElementById('pdf-load-progress');
        if (bar) bar.style.width = Math.min(100, Math.round(pct)) + '%';
        const sub = document.getElementById('pdf-load-sub');
        if (sub) sub.textContent = 'Mengunduh... ' + Math.min(100, Math.round(pct)) + '%';
    }
    function updateReadProgress() {
        if (!pdfDoc) return;
        const pct = pageNum / pdfDoc.numPages * 100;
        ['reading-progress-bar', 'fs-progress-bar'].forEach(id => {
            const e = document.getElementById(id); if (e) e.style.width = pct + '%';
        });
        const pt = document.getElementById('progress-text');
        const est = Math.ceil((pdfDoc.numPages - pageNum) * 1.5);
        if (pt) pt.textContent = `Hal. ${pageNum}/${pdfDoc.numPages} · ${Math.round(pct)}%` + (est > 0 ? ` · ~${est} mnt` : '');
    }

    /* ── SNACK ────────────────────────────────────────────────────── */
    function snack(msg, color) {
        color = color || '#FF6B18';
        const el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText =
            'position:fixed;top:1rem;left:50%;transform:translateX(-50%);' +
            'background:#1A1A1A;border:1px solid ' + color + ';color:#fff;' +
            'padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;' +
            'z-index:99999;transition:opacity .4s;pointer-events:none;' +
            'white-space:nowrap;max-width:90vw;overflow:hidden;text-overflow:ellipsis;';
        document.body.appendChild(el);
        setTimeout(function () { el.style.opacity = 0; setTimeout(function () { el.remove(); }, 400); }, 2200);
    }

    /* ── COMPUTE BASE SCALE (FIX-11) ─────────────────────────────── */
    function getContainerWidth() {
        /* Ambil lebar yg paling reliabel — canvasWrap bisa hidden (clientWidth=0) saat pertama load */
        const cwW = canvasWrap ? canvasWrap.clientWidth : 0;
        const veW = viewerEl ? viewerEl.clientWidth : 0;
        const win = window.innerWidth;
        /* Prioritas: viewerEl > canvasWrap > innerWidth */
        return Math.max(cwW, veW, win) || win;
    }

    function computeBase(page) {
        const cw = getContainerWidth();
        const pad = isMobile() ? 4 : 16;
        const avail = Math.max(cw - pad, 200); /* min 200px agar tidak collapse */
        const nw = page.getViewport({ scale: 1 }).width;
        baseScale = Math.max(0.5, Math.min(avail / nw, 3.0));
        baseScaleComputed = true;
        needsRecompute = false; /* FIX-11 */
    }

    const getScale = () => baseScale * zoomFactor;

    /* ── ZOOM DISPLAY ─────────────────────────────────────────────── */
    function updateZoomDisplay() {
        const label = Math.round(zoomFactor * 100) + '%';
        const barPct = Math.round(((zoomFactor - ZOOM_MIN) / (ZOOM_MAX - ZOOM_MIN)) * 100);
        ['zoom-level', 'fs-zoom-level'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = label; });
        ['sheet-zoom-val', 'tap-zoom-val'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = label; });
        ['sheet-zoom-fill', 'tap-zoom-fill'].forEach(id => { const e = document.getElementById(id); if (e) e.style.width = Math.max(4, barPct) + '%'; });
    }

    /* ── BOOKMARK ─────────────────────────────────────────────────── */
    function updateBookmarkUI() {
        const on = bookmarkedPage === pageNum;
        ['bkmk-icon', 'fs-bkmk-icon', 'sheet-bkmk-icon', 'tap-bkmk-icon'].forEach(function (id) {
            const ic = document.getElementById(id); if (!ic) return;
            ic.setAttribute('fill', on ? '#FF6B18' : 'none');
            ic.setAttribute('stroke', on ? '#FF6B18' : 'currentColor');
        });
        ['bookmark-btn', 'fs-bookmark-btn'].forEach(function (id) {
            const b = document.getElementById(id); if (b) b.classList.toggle('is-bkmk', on);
        });
        const sbtn = document.getElementById('sheet-bookmark-btn'); if (sbtn) sbtn.classList.toggle('bookmarked', on);
        const slbl = document.getElementById('sheet-bkmk-label'); if (slbl) slbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';
        const tbtn = document.getElementById('tap-bookmark-btn'); if (tbtn) tbtn.classList.toggle('bookmarked', on);
        const tlbl = document.getElementById('tap-bkmk-label'); if (tlbl) tlbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';

        /* juga update bookmark toast text */
        showBMToast_msg && null; /* akan diisi saat toggle */
    }

    let showBMToast_msg = '';
    function showBMToast(msg) {
        const t = document.getElementById('bm-toast');
        const msgEl = document.getElementById('bm-toast-msg');
        if (!t) {
            const el = document.createElement('div');
            el.id = 'bm-toast';
            el.style.cssText = 'position:fixed;bottom:5rem;left:50%;transform:translateX(-50%) translateY(60px);background:#1a1a1a;border:1.5px solid #FF6B18;color:#fff;padding:.5rem .875rem;border-radius:99px;font-size:13px;font-weight:600;z-index:20010;opacity:0;transition:all .35s;pointer-events:none;max-width:90vw;';
            el.innerHTML = '<span id="bm-toast-msg">' + msg + '</span>';
            document.body.appendChild(el);
            requestAnimationFrame(function () { el.style.opacity = '1'; el.style.transform = 'translateX(-50%) translateY(0)'; });
            setTimeout(function () { el.style.opacity = '0'; el.style.transform = 'translateX(-50%) translateY(60px)'; setTimeout(function () { el.remove(); }, 350); }, 2200);
            return;
        }
        if (msgEl) msgEl.textContent = msg;
        t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(function () { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(60px)'; }, 2200);
    }

    function toggleBookmark() {
        if (bookmarkedPage === pageNum) {
            bookmarkedPage = null; localStorage.removeItem(SK.bkmk);
            showBMToast('Tanda baca dihapus');
        } else {
            bookmarkedPage = pageNum; localStorage.setItem(SK.bkmk, pageNum);
            showBMToast('🔖 Halaman ' + pageNum + ' ditandai!');
        }
        updateBookmarkUI();
    }

    /* ── READING MODE ─────────────────────────────────────────────── */
    function applyMode(mode) {
        const ow = document.getElementById('pdf-viewer-container') || document.body;
        ow.classList.remove('read-mode-sepia', 'read-mode-night');
        if (mode !== 'normal') ow.classList.add('read-mode-' + mode);
        currentMode = mode; localStorage.setItem(SK.mode, mode);
        document.querySelectorAll('.mode-opt,[data-sheet-mode],[data-tap-mode]').forEach(function (e) {
            const m = e.dataset.mode || e.dataset.sheetMode || e.dataset.tapMode;
            e.classList.toggle('active', m === mode);
        });
    }
    applyMode(currentMode);

    /* ── WATERMARK (guest) ────────────────────────────────────────── */
    function renderWatermark(cssW, cssH) {
        if (!IS_GUEST) return;
        const wm = document.getElementById('pdf-watermark'); if (!wm) return;
        const rows = Math.ceil(cssH / 160), cols = Math.ceil(cssW / 260);
        let rects = '';
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const x = c * 260 + 30, y = r * 160 + 80;
                rects += `<text x="${x}" y="${y}" fill="#FF6B18" fill-opacity=".18" font-size="22" font-family="Arial" font-weight="bold" transform="rotate(-35,${x},${y})">PRATINJAU</text>`;
                rects += `<text x="${x - 10}" y="${y + 30}" fill="#FF6B18" fill-opacity=".18" font-size="11" font-family="Arial" transform="rotate(-35,${x - 10},${y + 30})">Login untuk akses penuh</text>`;
            }
        }
        wm.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${cssW}" height="${cssH}" style="position:absolute;inset:0;pointer-events:none;z-index:4">${rects}</svg>`;
    }

    /* ── GUEST GATE ───────────────────────────────────────────────── */
    function checkGuestGate() {
        if (!IS_GUEST || GUEST_PAGE_LIMIT === null || !pdfDoc) return;
        const total = pdfDoc.numPages, limit = GUEST_PAGE_LIMIT;
        if (limitWarn && pageNum === Math.max(1, limit - 1) && !gateShown) {
            const remaining = limit - pageNum;
            const te = document.getElementById('page-limit-warning-title');
            const tx = document.getElementById('page-limit-warning-text');
            if (te) te.textContent = `⚠️ ${remaining} halaman lagi!`;
            if (tx) tx.textContent = `Login untuk baca semua ${total} halaman secara gratis.`;
            limitWarn.classList.add('show'); setTimeout(function () { limitWarn.classList.remove('show'); }, 6000);
        }
        if (pageNum >= limit && !gateShown) {
            gateShown = true;
            const ids = { 'gg-pages-shown': limit + ' hal.', 'gg-total-pages': total + ' hal.', 'gg-stat-read': limit, 'gg-stat-left': (total - limit), 'gg-stat-total': total };
            Object.entries(ids).forEach(function ([id, val]) { const e = document.getElementById(id); if (e) e.textContent = val; });
            if (guestGate) guestGate.classList.add('show');
        }
        if (pageNum < limit && gateShown) { gateShown = false; if (guestGate) guestGate.classList.remove('show'); }
    }

    /* ── RENDER PAGE ──────────────────────────────────────────────── */
    function renderPage(num) {
        if (!pdfDoc) return;
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null && num > GUEST_PAGE_LIMIT) {
            num = GUEST_PAGE_LIMIT;
            if (!gateShown) checkGuestGate();
            return;
        }
        if (num < 1 || num > pdfDoc.numPages) return;
        if (pageRendering) { pageNumPending = num; return; }

        pageRendering = true;
        pageNum = num;
        localStorage.setItem(SK.page, num);

        /* close popups */
        document.querySelectorAll('.rpv-popup,.show-popup').forEach(function (p) { p.classList.remove('show'); });
        if (annotTip) annotTip.classList.remove('show');
        if (window.getSelection) window.getSelection().removeAllRanges();

        pdfDoc.getPage(num).then(async function (page) {
            if (!baseScaleComputed || needsRecompute) computeBase(page);

            const cssScale = getScale();
            const renderScale = cssScale * DPR;
            const vpCss = page.getViewport({ scale: cssScale });
            const vpRender = page.getViewport({ scale: renderScale });

            if (!canvas || !ctx) { pageRendering = false; return; }

            canvas.width = Math.floor(vpRender.width);
            canvas.height = Math.floor(vpRender.height);
            canvas.style.width = Math.floor(vpCss.width) + 'px';
            canvas.style.height = Math.floor(vpCss.height) + 'px';
            if (stage) {
                stage.style.width = Math.floor(vpCss.width) + 'px';
                stage.style.height = Math.floor(vpCss.height) + 'px';
            }

            await page.render({ canvasContext: ctx, viewport: vpRender }).promise.catch(function (e) {
                console.warn('[pdf-viewer] render:', e.message);
            });

            pageRendering = false;

            if (pageNumPending !== null) {
                const pp = pageNumPending; pageNumPending = null;
                renderPage(pp); return;
            }

            /* FIX-2: hide loading setelah render */
            hideLoading();
            if (canvasWrap) { canvasWrap.classList.remove('hidden'); canvasWrap.style.visibility = ''; canvasWrap.style.pointerEvents = ''; }
            if (stage) stage.style.display = 'block';

            await renderTextLayer(page, vpCss);
            renderWatermark(Math.floor(vpCss.width), Math.floor(vpCss.height));

            /* update UI */
            const pi = document.getElementById('page-num-input'); if (pi) pi.value = num;
            const fp = document.getElementById('fs-page-num'); if (fp) fp.textContent = num;
            const sp = document.getElementById('sheet-page-num'); if (sp) sp.textContent = num;
            const tp = document.getElementById('tap-page-num'); if (tp) tp.textContent = num;
            updateNavButtons();
            updateZoomDisplay();
            updateReadProgress();
            updateBookmarkUI();

            /* FIX-12: simpan zoom */
            try { localStorage.setItem(SK.zoom, zoomFactor); } catch (e) { /* ignore */ }

            if (canvasWrap) canvasWrap.scrollTo({ top: 0, behavior: 'smooth' });
            if (searchResults.length && currentSearchQuery) applySearchHighlights();
            checkGuestGate();

            if (_onPageChangeCb) _onPageChangeCb(num);

        }).catch(function (e) {
            console.error('[pdf-viewer] render error:', e);
            pageRendering = false;
            hideLoading(); /* FIX-2 */
            if (canvasWrap) { canvasWrap.classList.remove('hidden'); canvasWrap.style.visibility = ''; canvasWrap.style.pointerEvents = ''; }
        });
    }

    function queueRender(n) {
        if (pageRendering) pageNumPending = n;
        else renderPage(n);
    }

    /* ── TEXT LAYER ───────────────────────────────────────────────── */
    async function renderTextLayer(page, viewport) {
        if (!textLayer) return;
        textLayer.innerHTML = '';
        textLayer.style.width = Math.floor(viewport.width) + 'px';
        textLayer.style.height = Math.floor(viewport.height) + 'px';
        const content = await page.getTextContent();
        content.items.forEach(function (item) {
            if (!item.str || !item.str.trim()) return;
            const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
            const fh = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]);
            const angle = Math.atan2(tx[1], tx[0]);
            const span = document.createElement('span');
            span.textContent = item.str;
            span.style.cssText =
                'position:absolute;left:' + tx[4] + 'px;top:' + (tx[5] - fh) + 'px;' +
                'font-size:' + fh + 'px;line-height:1;white-space:pre;' +
                'padding:0;margin:0;color:transparent;cursor:text;' +
                'transform-origin:0% 0%;-webkit-touch-callout:none;';
            textLayer.appendChild(span);
            const tw = item.width * viewport.scale;
            const mw = span.getBoundingClientRect().width || span.scrollWidth;
            let tf = angle !== 0 ? 'rotate(' + (-angle) + 'rad)' : '';
            if (mw > 1 && tw > 0 && Math.abs(tw - mw) > 0.5) tf += (tf ? ' ' : '') + 'scaleX(' + (tw / mw) + ')';
            if (tf.trim()) span.style.transform = tf.trim();
        });
    }

    /* ── NAVIGATION ───────────────────────────────────────────────── */
    function prevPage() { if (pageNum > 1) { pageNum--; queueRender(pageNum); } }
    function nextPage() {
        if (!pdfDoc) return;
        const maxPage = (IS_GUEST && GUEST_PAGE_LIMIT !== null)
            ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages)
            : pdfDoc.numPages;
        if (pageNum < maxPage) { pageNum++; queueRender(pageNum); }
        else if (IS_GUEST && GUEST_PAGE_LIMIT !== null && pageNum >= GUEST_PAGE_LIMIT) { if (!gateShown) checkGuestGate(); }
    }
    function goTo(n) {
        if (!pdfDoc) return;
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null) n = Math.min(n, GUEST_PAGE_LIMIT);
        if (n >= 1 && n <= pdfDoc.numPages) { pageNum = n; queueRender(n); }
    }
    function updateNavButtons() {
        ['prev-page', 'fs-prev', 'sheet-prev', 'tap-prev'].forEach(function (id) {
            const e = document.getElementById(id); if (e) e.disabled = pageNum <= 1;
        });
        const maxForGuest = (IS_GUEST && GUEST_PAGE_LIMIT !== null && pdfDoc)
            ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages)
            : (pdfDoc ? pdfDoc.numPages : 1);
        ['next-page', 'fs-next', 'sheet-next', 'tap-next'].forEach(function (id) {
            const e = document.getElementById(id); if (e) e.disabled = pageNum >= maxForGuest;
        });
    }

    /* ── ZOOM ─────────────────────────────────────────────────────── */
    function doZoom(dir) {
        zoomFactor = dir > 0
            ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX)
            : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN);
        needsRecompute = false; /* tetap pakai baseScale yg ada, hanya zoom */
        updateZoomDisplay();
        try { localStorage.setItem(SK.zoom, zoomFactor); } catch (e) { /* ignore */ }
        if (pdfDoc) queueRender(pageNum);
    }
    function zoomIn() { doZoom(1); }
    function zoomOut() { doZoom(-1); }

    /* ── SEARCH ───────────────────────────────────────────────────── */
    function clearSearchHL() {
        if (!annotLayer) return;
        annotLayer.querySelectorAll('.search-highlight').forEach(function (e) { e.remove(); });
        searchHighlightEls = [];
    }

    function applySearchHighlights() {
        clearSearchHL();
        if (!currentSearchQuery || !pdfDoc || !textLayer || !annotLayer) return;
        const q = currentSearchQuery.toLowerCase();
        const sr = stage ? stage.getBoundingClientRect() : { left: 0, top: 0 };
        const spans = Array.from(textLayer.querySelectorAll('span'));
        spans.forEach(function (span) {
            if (!span.firstChild) return;
            const text = span.textContent, lower = text.toLowerCase();
            let idx = lower.indexOf(q);
            while (idx !== -1) {
                try {
                    const range = document.createRange();
                    range.setStart(span.firstChild, idx);
                    range.setEnd(span.firstChild, Math.min(idx + q.length, text.length));
                    Array.from(range.getClientRects()).forEach(function (rect) {
                        if (rect.width < 1 || rect.height < 1) return;
                        const el = document.createElement('div');
                        el.className = 'search-highlight';
                        el.style.cssText =
                            'position:absolute;left:' + (rect.left - sr.left) + 'px;top:' + (rect.top - sr.top) +
                            'px;width:' + rect.width + 'px;height:' + rect.height +
                            'px;background:rgba(255,215,0,.45);border-radius:2px;pointer-events:none;z-index:7;transition:background .25s;';
                        annotLayer.appendChild(el);
                        searchHighlightEls.push(el);
                    });
                } catch (_) { /* ignore */ }
                idx = lower.indexOf(q, idx + 1);
            }
        });
        highlightActiveMatch();
    }

    function highlightActiveMatch() {
        searchHighlightEls.forEach(function (el, i) {
            el.style.background = i === searchIndex ? 'rgba(255,107,24,.75)' : 'rgba(255,215,0,.45)';
            el.style.outline = i === searchIndex ? '2px solid #FF6B18' : 'none';
        });
        if (searchHighlightEls[searchIndex])
            searchHighlightEls[searchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
        document.querySelectorAll('.sri').forEach(function (el, i) {
            el.classList.toggle('active-sri', i === searchIndex);
        });
    }

    function openSearch() {
        const ov = document.getElementById('search-overlay'); if (!ov) return;
        ov.classList.add('show');
        setTimeout(function () { const i = document.getElementById('search-input'); if (i) i.focus(); }, 60);
    }
    function closeSearch() {
        const ov = document.getElementById('search-overlay'); if (ov) ov.classList.remove('show');
        clearSearchHL(); currentSearchQuery = ''; searchResults = []; searchIndex = -1;
        const inp = document.getElementById('search-input'); if (inp) inp.value = '';
        const rl = document.getElementById('search-results-list'); if (rl) rl.innerHTML = '';
        const rs = document.getElementById('search-status'); if (rs) rs.textContent = 'Ketik untuk mencari...';
        const mi = document.getElementById('search-match-info'); if (mi) mi.textContent = '';
    }

    async function doSearch(query) {
        const rs = document.getElementById('search-status');
        const list = document.getElementById('search-results-list');
        const mi = document.getElementById('search-match-info');
        if (!pdfDoc || !query.trim()) {
            clearSearchHL(); currentSearchQuery = ''; searchResults = []; searchIndex = -1;
            if (rs) rs.textContent = 'Ketik untuk mencari...';
            if (list) list.innerHTML = ''; if (mi) mi.textContent = ''; return;
        }
        if (rs) rs.textContent = 'Mencari di semua halaman...';
        searchResults = []; currentSearchQuery = query;
        const q = query.toLowerCase();
        const maxPage = (IS_GUEST && GUEST_PAGE_LIMIT !== null)
            ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages) : pdfDoc.numPages;
        for (let p = 1; p <= maxPage; p++) {
            const pg = await pdfDoc.getPage(p);
            const ct = await pg.getTextContent();
            const text = ct.items.map(function (i) { return i.str; }).join(' ');
            const lt = text.toLowerCase();
            let ix = lt.indexOf(q);
            while (ix !== -1) {
                searchResults.push({ page: p, excerpt: text.substring(Math.max(0, ix - 35), ix + q.length + 50).trim() });
                ix = lt.indexOf(q, ix + 1);
            }
        }
        if (!list) return;
        list.innerHTML = '';
        if (!searchResults.length) {
            if (rs) rs.textContent = 'Tidak ditemukan: "' + query + '"';
            if (mi) mi.textContent = ''; clearSearchHL(); return;
        }
        if (rs) rs.textContent = searchResults.length + ' hasil ditemukan';
        searchIndex = 0;
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const esc = function (s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
        searchResults.slice(0, 40).forEach(function (r, i) {
            const el = document.createElement('div'); el.className = 'sri' + (i === 0 ? ' active-sri' : '');
            const hlEx = esc(r.excerpt).replace(
                new RegExp(escaped, 'gi'),
                function (m) { return '<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">' + m + '</mark>'; }
            );
            el.innerHTML = '<span class="pg">Hal.' + r.page + '</span><span class="ex">' + hlEx + '</span>';
            el.addEventListener('click', function () {
                searchIndex = i;
                if (r.page !== pageNum) { goTo(r.page); setTimeout(function () { applySearchHighlights(); highlightActiveMatch(); }, 700); }
                else { applySearchHighlights(); highlightActiveMatch(); }
                updateSearchMatchInfo();
            });
            list.appendChild(el);
        });
        updateSearchMatchInfo();
        if (searchResults[0].page === pageNum) applySearchHighlights();
        else goTo(searchResults[0].page);
    }

    function updateSearchMatchInfo() {
        const mi = document.getElementById('search-match-info');
        if (!mi || !searchResults.length) return;
        const onPage = searchResults.filter(function (r) { return r.page === pageNum; }).length;
        mi.textContent = (searchIndex + 1) + '/' + searchResults.length + ' hasil · ' + onPage + ' di halaman ini';
    }
    function searchNavNext() {
        if (!searchResults.length) return;
        searchIndex = (searchIndex + 1) % searchResults.length;
        const r = searchResults[searchIndex];
        if (r.page !== pageNum) { goTo(r.page); setTimeout(function () { applySearchHighlights(); highlightActiveMatch(); }, 700); }
        else { applySearchHighlights(); highlightActiveMatch(); }
        updateSearchMatchInfo();
    }
    function searchNavPrev() {
        if (!searchResults.length) return;
        searchIndex = (searchIndex - 1 + searchResults.length) % searchResults.length;
        const r = searchResults[searchIndex];
        if (r.page !== pageNum) { goTo(r.page); setTimeout(function () { applySearchHighlights(); highlightActiveMatch(); }, 700); }
        else { applySearchHighlights(); highlightActiveMatch(); }
        updateSearchMatchInfo();
    }

    /* bind search */
    (function () {
        const inp = document.getElementById('search-input');
        if (inp) {
            inp.addEventListener('input', function () {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(function () { doSearch(inp.value); }, 450);
            });
            inp.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { clearTimeout(searchDebounce); e.shiftKey ? searchNavPrev() : searchNavNext(); }
                if (e.key === 'Escape') closeSearch();
            });
        }
        const ov = document.getElementById('search-overlay');
        if (ov) ov.addEventListener('click', function (e) { if (e.target === ov) closeSearch(); });
        const sc = document.getElementById('search-close-btn'); if (sc) sc.addEventListener('click', closeSearch);
        const sn = document.getElementById('search-next-btn'); if (sn) sn.addEventListener('click', searchNavNext);
        const sp = document.getElementById('search-prev-btn'); if (sp) sp.addEventListener('click', searchNavPrev);
        const sb = document.getElementById('search-btn'); if (sb) sb.addEventListener('click', openSearch);
    })();

    /* ── FULLSCREEN ───────────────────────────────────────────────── */
    function enterFullscreen() {
        isFullscreen = true;
        if (viewerEl) viewerEl.classList.add('fullscreen-mode');
        document.body.style.overflow = 'hidden';
        if (fsTb) fsTb.classList.remove('toolbar-hidden');
        if (!isMobile() && deskHint) {
            deskHint.classList.remove('hidden', 'fade-out');
            clearTimeout(toolbarTimer);
            toolbarTimer = setTimeout(function () { deskHint.classList.add('fade-out'); }, 4500);
        }
        needsRecompute = true;
        if (pdfDoc) queueRender(pageNum);
    }
    function exitFullscreen() {
        isFullscreen = false;
        if (viewerEl) viewerEl.classList.remove('fullscreen-mode');
        document.body.style.overflow = '';
        if (deskHint) deskHint.classList.add('hidden');
        closeTapOverlay();
        needsRecompute = true;
        if (pdfDoc) queueRender(pageNum);
    }

    if (viewerEl) {
        viewerEl.addEventListener('mousemove', function () {
            if (!isFullscreen || isMobile()) return;
            if (fsTb) fsTb.classList.remove('toolbar-hidden');
            clearTimeout(toolbarTimer);
            toolbarTimer = setTimeout(function () { if (fsTb) fsTb.classList.add('toolbar-hidden'); }, 3000);
        });
        viewerEl.addEventListener('click', function (e) {
            if (!isFullscreen || !isMobile()) return;
            if (e.target.closest('#pdf-fullscreen-toolbar,#mobile-tap-overlay,#bottom-sheet,#guest-gate-overlay')) return;
            if (window.getSelection && window.getSelection().toString()) return;
            tapOverlayOpen ? closeTapOverlay() : openTapOverlay();
        });
    }

    /* ── TAP OVERLAY (mobile fullscreen) ─────────────────────────── */
    function openTapOverlay() { tapOverlayOpen = true; if (tapOverlay) tapOverlay.classList.add('show'); }
    function closeTapOverlay() { tapOverlayOpen = false; if (tapOverlay) tapOverlay.classList.remove('show'); }

    (function () {
        const tc = document.getElementById('tap-close-overlay'); if (tc) tc.addEventListener('click', closeTapOverlay);
        const tp = document.getElementById('tap-prev'); if (tp) tp.addEventListener('click', prevPage);
        const tn = document.getElementById('tap-next'); if (tn) tn.addEventListener('click', nextPage);
        const tzi = document.getElementById('tap-zoom-in'); if (tzi) tzi.addEventListener('click', zoomIn);
        const tzo = document.getElementById('tap-zoom-out'); if (tzo) tzo.addEventListener('click', zoomOut);
        const tbm = document.getElementById('tap-bookmark-btn'); if (tbm) tbm.addEventListener('click', toggleBookmark);
        const tex = document.getElementById('tap-exit-btn'); if (tex) tex.addEventListener('click', function () { closeTapOverlay(); exitFullscreen(); });
        document.querySelectorAll('[data-tap-mode]').forEach(function (el) {
            el.addEventListener('click', function () { applyMode(el.dataset.tapMode); snack({ normal: '☀️ Normal', sepia: '📜 Sepia', night: '🌙 Night' }[el.dataset.tapMode]); });
        });
    })();

    /* ── BOTTOM SHEET ─────────────────────────────────────────────── */
    function openSheet() { sheetIsOpen = true; document.getElementById('sheet-backdrop')?.classList.add('show'); document.getElementById('bottom-sheet')?.classList.add('show'); }
    function closeSheet() { sheetIsOpen = false; document.getElementById('sheet-backdrop')?.classList.remove('show'); document.getElementById('bottom-sheet')?.classList.remove('show'); }

    (function () {
        const bd = document.getElementById('sheet-backdrop'); if (bd) bd.addEventListener('click', closeSheet);
        const sc = document.getElementById('sheet-close'); if (sc) sc.addEventListener('click', closeSheet);
        const sp = document.getElementById('sheet-prev'); if (sp) sp.addEventListener('click', prevPage);
        const sn = document.getElementById('sheet-next'); if (sn) sn.addEventListener('click', nextPage);
        const szi = document.getElementById('sheet-zoom-in'); if (szi) szi.addEventListener('click', zoomIn);
        const szo = document.getElementById('sheet-zoom-out'); if (szo) szo.addEventListener('click', zoomOut);
        const sbm = document.getElementById('sheet-bookmark-btn'); if (sbm) sbm.addEventListener('click', toggleBookmark);
        const sfs = document.getElementById('sheet-fs-btn'); if (sfs) sfs.addEventListener('click', function () { closeSheet(); setTimeout(enterFullscreen, 200); });
        const sse = document.getElementById('sheet-search-btn'); if (sse) sse.addEventListener('click', function () { closeSheet(); setTimeout(openSearch, 200); });
        const sjg = document.getElementById('sheet-jump-go'); if (sjg) sjg.addEventListener('click', function () { const n = parseInt(document.getElementById('sheet-jump')?.value); if (n) { goTo(n); closeSheet(); } });
        const sji = document.getElementById('sheet-jump'); if (sji) sji.addEventListener('keydown', function (e) { if (e.key === 'Enter') { const n = parseInt(sji.value); if (n) { goTo(n); closeSheet(); } } });
        document.querySelectorAll('[data-sheet-mode]').forEach(function (el) {
            el.addEventListener('click', function () { applyMode(el.dataset.sheetMode); snack({ normal: '☀️ Normal', sepia: '📜 Sepia', night: '🌙 Night' }[el.dataset.sheetMode]); });
        });
        const fab = document.getElementById('mobile-fab-btn'); if (fab) fab.addEventListener('click', function (e) { e.stopPropagation(); openSheet(); });
    })();

    /* Expose openSheet/closeSheet globally untuk script inline */
    window.openBottomSheet = openSheet;
    window.closeBottomSheet = closeSheet;

    /* ── MODE DROPDOWN (desktop) ─────────────────────────────────── */
    (function () {
        const btn = document.getElementById('mode-btn');
        if (btn) btn.addEventListener('click', function (e) { e.stopPropagation(); document.getElementById('mode-dropdown')?.classList.toggle('open'); });
        document.querySelectorAll('.mode-opt').forEach(function (el) {
            el.addEventListener('click', function () { applyMode(el.dataset.mode); document.getElementById('mode-dropdown')?.classList.remove('open'); });
        });
        document.addEventListener('click', function () { document.getElementById('mode-dropdown')?.classList.remove('open'); });
    })();

    /* ── TOOLBAR BUTTONS ──────────────────────────────────────────── */
    (function () {
        const prev = document.getElementById('prev-page'); if (prev) prev.addEventListener('click', prevPage);
        const next = document.getElementById('next-page'); if (next) next.addEventListener('click', nextPage);
        const fsPrev = document.getElementById('fs-prev'); if (fsPrev) fsPrev.addEventListener('click', prevPage);
        const fsNext = document.getElementById('fs-next'); if (fsNext) fsNext.addEventListener('click', nextPage);
        const zi = document.getElementById('zoom-in'); if (zi) zi.addEventListener('click', zoomIn);
        const zo = document.getElementById('zoom-out'); if (zo) zo.addEventListener('click', zoomOut);
        const fzi = document.getElementById('fs-zoom-in'); if (fzi) fzi.addEventListener('click', zoomIn);
        const fzo = document.getElementById('fs-zoom-out'); if (fzo) fzo.addEventListener('click', zoomOut);
        const bm = document.getElementById('bookmark-btn'); if (bm) bm.addEventListener('click', toggleBookmark);
        const fsbm = document.getElementById('fs-bookmark-btn'); if (fsbm) fsbm.addEventListener('click', toggleBookmark);
        const fsBtn = document.getElementById('fullscreen-btn'); if (fsBtn) fsBtn.addEventListener('click', enterFullscreen);
        const exBtn = document.getElementById('exit-fs-btn'); if (exBtn) exBtn.addEventListener('click', exitFullscreen);
        const seBtn = document.getElementById('search-btn'); if (seBtn) seBtn.addEventListener('click', openSearch);
        const pi = document.getElementById('page-num-input');
        if (pi) pi.addEventListener('change', function () {
            const n = parseInt(pi.value);
            if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) goTo(n); else pi.value = pageNum;
        });
    })();

    /* ── KEYBOARD ─────────────────────────────────────────────────── */
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') { e.preventDefault(); openSearch(); return; }
        const ov = document.getElementById('search-overlay');
        if (ov && ov.classList.contains('show') && e.target.id === 'search-input') return;
        if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
        switch (e.key) {
            case 'ArrowLeft': prevPage(); break;
            case 'ArrowRight': nextPage(); break;
            case 'ArrowUp': if (canvasWrap) canvasWrap.scrollBy({ top: -120, behavior: 'smooth' }); break;
            case 'ArrowDown': if (canvasWrap) canvasWrap.scrollBy({ top: 120, behavior: 'smooth' }); break;
            case '+': case '=': zoomIn(); break;
            case '-': zoomOut(); break;
            case 'b': case 'B': toggleBookmark(); break;
            case 'f': case 'F': isFullscreen ? exitFullscreen() : enterFullscreen(); break;
            case 'Escape':
                if (ov && ov.classList.contains('show')) closeSearch();
                else if (isFullscreen) exitFullscreen();
                break;
        }
    });

    /* ── TOUCH (pinch + swipe) ────────────────────────────────────── */
    (function () {
        let tx = 0, ty = 0, pd = 0, touchMoved = false, pinching = false;
        if (!viewerEl) return;
        viewerEl.addEventListener('touchstart', function (e) {
            touchMoved = false; pinching = false;
            if (e.touches.length === 1) { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }
            if (e.touches.length === 2) { pinching = true; pd = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); }
        }, { passive: true });
        viewerEl.addEventListener('touchmove', function (e) {
            touchMoved = true;
            if (e.touches.length !== 2) return;
            const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
            if (Math.abs(d - pd) > 14) { d > pd ? zoomIn() : zoomOut(); pd = d; }
        }, { passive: true });
        /* FIX-7: threshold swipe 60px */
        viewerEl.addEventListener('touchend', function (e) {
            if (pinching || !touchMoved) return;
            const dx = tx - e.changedTouches[0].clientX;
            const dy = ty - e.changedTouches[0].clientY;
            if (Math.abs(dx) > Math.abs(dy) * 1.8 && Math.abs(dx) > 60) {
                if (tapOverlayOpen) { closeTapOverlay(); return; }
                if (window.getSelection && window.getSelection().toString()) return;
                dx > 0 ? nextPage() : prevPage();
            }
        }, { passive: true });
    })();

    /* ── RESIZE (FIX-7: threshold 40px + orientationchange) ──────── */
    (function () {
        let lastW = viewerEl ? viewerEl.clientWidth : window.innerWidth, resT = null;
        window.addEventListener('resize', function () {
            const w = viewerEl ? viewerEl.clientWidth : window.innerWidth;
            if (Math.abs(w - lastW) < 40) return; lastW = w;
            clearTimeout(resT);
            resT = setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; queueRender(pageNum); }, 300);
        });
        window.addEventListener('orientationchange', function () {
            setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; lastW = 0; queueRender(pageNum); }, 400);
        });
    })();

    /* ── RESUME TOAST (FEAT-3) ────────────────────────────────────── */
    function showResumeToast(page) {
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null) page = Math.min(page, GUEST_PAGE_LIMIT);
        if (page <= 1 || !pdfDoc || page > pdfDoc.numPages) return;
        let t = document.getElementById('resume-toast');
        if (!t) {
            t = document.createElement('div'); t.id = 'resume-toast';
            t.style.cssText = 'position:fixed;bottom:5rem;left:50%;transform:translateX(-50%) translateY(80px);background:#1a1a1a;border:1.5px solid #FF6B18;color:#fff;padding:.6rem .875rem;border-radius:14px;font-size:13px;z-index:20010;display:flex;align-items:center;gap:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.5);opacity:0;transition:all .4s;pointer-events:none;white-space:nowrap;max-width:90vw;';
            t.innerHTML = '<span style="font-size:1.2rem">🔖</span><div><p style="font-weight:700;margin:0;font-size:12px;">Lanjut membaca?</p><p style="color:#9ca3af;margin:0;font-size:11px;" id="resume-text">—</p></div><button id="resume-yes" style="padding:.3rem .7rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;pointer-events:auto;">Lanjut</button><button id="resume-no" style="padding:.3rem .6rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:11px;cursor:pointer;pointer-events:auto;">Awal</button>';
            document.body.appendChild(t);
        }
        const rt = document.getElementById('resume-text'); if (rt) rt.textContent = 'Terakhir di halaman ' + page;
        requestAnimationFrame(function () { t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)'; t.style.pointerEvents = 'auto'; });
        function hide() { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(80px)'; t.style.pointerEvents = 'none'; }
        const auto = setTimeout(hide, 8000);
        const yes = document.getElementById('resume-yes'); if (yes) yes.onclick = function () { clearTimeout(auto); hide(); goTo(page); };
        const no = document.getElementById('resume-no'); if (no) no.onclick = function () { clearTimeout(auto); hide(); goTo(1); };
    }

    /* ── FALLBACK (iframe) ────────────────────────────────────────── */
    function showFallback() {
        if (IS_GUEST) {
            hideLoading();
            if (canvasWrap) { canvasWrap.classList.remove('hidden'); canvasWrap.style.visibility = ''; canvasWrap.style.pointerEvents = ''; }
            const stEl = document.getElementById('pdf-stage'); if (stEl) stEl.style.display = 'none';
            const errDiv = document.createElement('div');
            errDiv.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;padding:2rem;text-align:center;max-width:360px;margin:auto;';
            errDiv.innerHTML = '<div style="font-size:2.5rem">📄</div><p style="color:#fff;font-weight:700;font-size:1rem">Gagal memuat dokumen</p><p style="color:#9CA3AF;font-size:.875rem">Login untuk membaca publikasi ini secara penuh.</p><a href="' + (loginUrl || '/login') + '" style="padding:.65rem 1.5rem;background:#FF6B18;color:#fff;border-radius:10px;font-weight:700;font-size:.875rem;text-decoration:none;">🔓 Masuk Sekarang</a>';
            if (canvasWrap) canvasWrap.appendChild(errDiv); return;
        }
        hideLoading();
        if (canvasWrap) { canvasWrap.style.visibility = 'hidden'; canvasWrap.style.pointerEvents = 'none'; }
        if (iframeEl) { iframeEl.style.display = 'block'; iframeEl.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'; }
    }

    /* ── GUEST DOWNLOAD MODAL ─────────────────────────────────────── */
    window.showGuestDownloadModal = function () {
        const modal = document.getElementById('guestDownloadModal');
        const backdrop = document.getElementById('guestModalBackdrop');
        const container = document.getElementById('guestModalContainer');
        if (!modal) return; modal.style.display = 'block'; document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () {
            if (backdrop) { backdrop.classList.add('opacity-100'); backdrop.classList.remove('opacity-0'); }
            if (container) { container.classList.add('opacity-100', 'scale-100'); container.classList.remove('opacity-0', 'scale-95'); }
        });
    };
    window.hideGuestDownloadModal = function () {
        const modal = document.getElementById('guestDownloadModal');
        const backdrop = document.getElementById('guestModalBackdrop');
        const container = document.getElementById('guestModalContainer');
        if (!modal) return;
        if (backdrop) { backdrop.classList.remove('opacity-100'); backdrop.classList.add('opacity-0'); }
        if (container) { container.classList.remove('opacity-100', 'scale-100'); container.classList.add('opacity-0', 'scale-95'); }
        setTimeout(function () { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
    };
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') window.hideGuestDownloadModal && window.hideGuestDownloadModal(); });

    /* ── EXPOSE API (sebelum load PDF) ───────────────────────────── */
    window._pdfViewer = {
        get stage() { return stage; },
        get annotLayer() { return annotLayer; },
        get textLayer() { return textLayer; },
        get pageNum() { return pageNum; },
        get pdfDoc() { return pdfDoc; },
        getScale,
        snack,
        queueRender,
        goTo,
        prevPage,
        nextPage,
        set onPageChange(fn) { _onPageChangeCb = fn; },
        get onPageChange() { return _onPageChangeCb; },
        onReady: function (cb) { if (pdfDoc) { try { cb(); } catch (e) { } } else { _onReadyCbs.push(cb); } },
    };

    /* dispatch event agar pdf-annotations.js bisa bootstrap */
    window.dispatchEvent(new CustomEvent('pdf-viewer-ready'));

    /* ── LOAD PDF ─────────────────────────────────────────────────── */
    function startLoad() {
        showLoading('Memuat dokumen...');
        if (stage) stage.style.display = 'none';

        /* Gunakan cache jika ada (FEAT-19) */
        if (pdfDoc) {
            console.log('[pdf-viewer] using cached PDF');
            updateLoadProgress(100);
            const ptEl = document.getElementById('page-count'); if (ptEl) ptEl.textContent = pdfDoc.numPages;
            const piEl = document.getElementById('page-num-input'); if (piEl) piEl.max = pdfDoc.numPages;
            const ftEl = document.getElementById('fs-page-count'); if (ftEl) ftEl.textContent = pdfDoc.numPages;
            const stEl = document.getElementById('sheet-total'); if (stEl) stEl.textContent = pdfDoc.numPages;
            const ttEl = document.getElementById('tap-page-total'); if (ttEl) ttEl.textContent = pdfDoc.numPages;
            needsRecompute = true;
            renderPage(1);
            setTimeout(_fireReady, 100); /* beri waktu renderPage selesai dulu */
            return;
        }

        /* FIX-2: fallback timeout */
        const FALLBACK_TIMEOUT = IS_GUEST ? 30000 : 12000;
        let fbTimer = setTimeout(function () { if (!pdfDoc) showFallback(); }, FALLBACK_TIMEOUT);

        const task = pdfjsLib.getDocument({
            url: pdfUrl, withCredentials: false, verbosity: 0,
            rangeChunkSize: 65536, disableAutoFetch: false, disableStream: false,
        });

        /* FIX-1: progress bar saat download */
        task.onProgress = function (d) {
            if (d.total > 0) updateLoadProgress(Math.min(99, Math.round(d.loaded / d.total * 100)));
        };

        task.promise.then(async function (doc) {
            clearTimeout(fbTimer); fbTimer = null;
            pdfDoc = doc;
            window[CACHE_KEY] = doc; /* FEAT-19: cache */
            console.log('[pdf-viewer] PDF loaded,', doc.numPages, 'pages');

            /* FIX-1: set 100% setelah load */
            updateLoadProgress(100);

            const total = doc.numPages;
            const ptEl = document.getElementById('page-count'); if (ptEl) ptEl.textContent = total;
            const ftEl = document.getElementById('fs-page-count'); if (ftEl) ftEl.textContent = total;
            const stEl = document.getElementById('sheet-total'); if (stEl) stEl.textContent = total;
            const ttEl = document.getElementById('tap-page-total'); if (ttEl) ttEl.textContent = total;
            const piEl = document.getElementById('page-num-input'); if (piEl) { piEl.max = total; }
            const sji = document.getElementById('sheet-jump'); if (sji) sji.max = total;
            if (IS_GUEST && GUEST_PAGE_LIMIT !== null && total > GUEST_PAGE_LIMIT) {
                const pc = document.getElementById('page-count');
                if (pc) pc.textContent = GUEST_PAGE_LIMIT + '* (dari ' + total + ')';
            }

            renderPage(1);

            /* panggil onReady hook untuk pdf-annotations.js — delay agar renderPage selesai dulu */
            setTimeout(_fireReady, 150);

            if (savedPage > 1 && savedPage <= total) setTimeout(function () { showResumeToast(savedPage); }, 900);

            /* bookmark hint */
            const bm = parseInt(localStorage.getItem(SK.bkmk));
            if (bm && bm !== savedPage && bm <= total) {
                setTimeout(function () { snack('🔖 Tanda baca ada di hal.' + bm, '#60A5FA'); }, 2500);
            }
            updateBookmarkUI();

            /* dispatch ready event */
            window.dispatchEvent(new CustomEvent('pdf-viewer-document-ready'));

        }).catch(function (err) {
            clearTimeout(fbTimer); fbTimer = null;
            console.error('[pdf-viewer] PDF load error:', err);
            /* FIX-2: tampilkan error, stop spinner */
            if (loadingEl) {
                showLoading();
                loadingEl.innerHTML =
                    '<div style="font-size:2rem">⚠️</div>' +
                    '<p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat PDF</p>' +
                    '<p style="color:#6b7280;font-size:11px;margin:.25rem 0;">' + err.message + '</p>' +
                    '<button type="button" onclick="window.location.reload()" style="margin-top:.75rem;padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
            }
        });
    }

    /* ── FIX: Sinkronkan tinggi #pdf-viewer-container dengan toolbar aktual ── */
    (function fixViewerHeight() {
        const toolbar = document.getElementById('pdf-toolbar');
        const container = viewerEl;
        if (!toolbar || !container) return;
        function applyHeight() {
            const tbH = toolbar.getBoundingClientRect().height || toolbar.offsetHeight || 56;
            container.style.height = 'calc(100dvh - ' + Math.round(tbH) + 'px)';
            /* fallback */
            if (typeof CSS === 'undefined' || !CSS.supports('height', '100dvh')) {
                container.style.height = 'calc(100vh - ' + Math.round(tbH) + 'px)';
            }
        }
        applyHeight();
        /* update saat resize / orientasi berubah */
        window.addEventListener('resize', applyHeight, { passive: true });
        window.addEventListener('orientationchange', applyHeight, { passive: true });
    })();

    startLoad();

})();
