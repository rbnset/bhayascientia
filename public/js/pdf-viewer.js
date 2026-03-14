/**
 * pdf-viewer.js
 * Simpan di: public/js/pdf-viewer.js
 * Load via: <script src="{{ asset('js/pdf-viewer.js') }}"></script>
 *
 * PENTING: File ini butuh variabel global yang di-inject dari Blade:
 *   window.PDF_CONFIG = {
 *     pdfUrl         : "...",
 *     slug           : "...",
 *     guestPageLimit : null | number,
 *     isGuest        : true | false,
 *     loginUrl       : "...",
 *     registerUrl    : "...",
 *   };
 */
(function () {
    'use strict';

    // ── Guard: pastikan config tersedia ──────────────────────────────
    if (!window.PDF_CONFIG) {
        console.error('[pdf-viewer] window.PDF_CONFIG tidak ditemukan!');
        return;
    }

    const { pdfUrl, slug, guestPageLimit: GUEST_PAGE_LIMIT, isGuest: IS_GUEST } = window.PDF_CONFIG;

    // ═══════════════════════════════════════════════════════════════
    // ✅ FIX: AUTH-STATE CHANGE DETECTION
    // ═══════════════════════════════════════════════════════════════
    const AUTH_KEY = 'pdf_auth_state';
    const currentAuthState = IS_GUEST ? 'guest' : 'auth';
    const prevAuthState = sessionStorage.getItem(AUTH_KEY);

    if (prevAuthState !== null && prevAuthState !== currentAuthState) {
        sessionStorage.setItem(AUTH_KEY, currentAuthState);
        window.location.reload();
        return;
    }
    sessionStorage.setItem(AUTH_KEY, currentAuthState);

    // ── Storage Keys ─────────────────────────────────────────────────
    const SK = {
        page: `bp_${slug}`,
        zoom: `bz_${slug}`,
        mode: `bm_${slug}`,
        bkmk: `bb_${slug}`,
        annot: `ba_${slug}`,
    };

    // ── State ─────────────────────────────────────────────────────────
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let baseScale = 1.0;
    const ZOOM_MIN = 1.0;
    const ZOOM_MAX = 4.0;
    const ZOOM_STEP = 0.25;
    let zoomFactor = Math.max(ZOOM_MIN, parseFloat(localStorage.getItem(SK.zoom)) || 1.0);
    let isFullscreen = false;
    let currentMode = localStorage.getItem(SK.mode) || 'normal';
    let bookmarkedPage = parseInt(localStorage.getItem(SK.bkmk)) || null;
    let savedPage = parseInt(localStorage.getItem(SK.page)) || 1;
    let toolbarTimer = null;
    let tapOverlayOpen = false;
    let sheetIsOpen = false;
    let searchResults = [];
    let searchIndex = -1;
    let searchHighlightEls = [];
    let annotations = JSON.parse(localStorage.getItem(SK.annot) || '[]');
    let activeAnnotId = null;
    let gateShown = false;

    // ✅ devicePixelRatio untuk sharp rendering di Retina / HiDPI
    const DPR = window.devicePixelRatio || 1;
    const isMobile = () => window.innerWidth < 768;

    // ── DOM ───────────────────────────────────────────────────────────
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const stage = document.getElementById('pdf-stage');
    const textLayer = document.getElementById('text-layer');
    const annotLayer = document.getElementById('annotation-layer');
    const loadingEl = document.getElementById('pdf-loading');
    const canvasWrap = document.getElementById('pdf-canvas-wrapper');
    const viewerEl = document.getElementById('pdf-viewer-container');
    const iframeEl = document.getElementById('pdf-iframe');
    const fsTb = document.getElementById('pdf-fullscreen-toolbar');
    const deskHint = document.getElementById('desktop-hint');
    const annotTb = document.getElementById('annot-toolbar');
    const commentPop = document.getElementById('comment-popup');
    const annotTip = document.getElementById('annot-tooltip');
    const tapOverlay = document.getElementById('mobile-tap-overlay');
    const guestGate = document.getElementById('guest-gate-overlay');
    const limitWarn = document.getElementById('page-limit-warning');

    // ── Helpers ───────────────────────────────────────────────────────
    const hideLoading = () => loadingEl.style.display = 'none';
    const showCanvas = () => { canvasWrap.style.display = 'flex'; canvasWrap.classList.remove('hidden'); };

    function snack(msg, color = '#FF6B18') {
        const el = Object.assign(document.createElement('div'), { textContent: msg });
        el.style.cssText = `position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid ${color};color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;`;
        document.body.appendChild(el);
        setTimeout(() => { el.style.opacity = 0; setTimeout(() => el.remove(), 400); }, 2200);
    }

    // ── Watermark (hanya untuk guest) ─────────────────────────────────
    function renderWatermark(cssW, cssH) {
        if (!IS_GUEST) return;
        const wm = document.getElementById('pdf-watermark');
        if (!wm) return;
        const rows = Math.ceil(cssH / 160);
        const cols = Math.ceil(cssW / 260);
        let rects = '';
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const x = c * 260 + 30;
                const y = r * 160 + 80;
                rects += `<text x="${x}" y="${y}" fill="#FF6B18" font-size="22" font-family="Arial" font-weight="bold" transform="rotate(-35,${x},${y})">PRATINJAU</text>`;
                rects += `<text x="${x - 10}" y="${y + 30}" fill="#FF6B18" font-size="11" font-family="Arial" transform="rotate(-35,${x - 10},${y + 30})">Login untuk akses penuh</text>`;
            }
        }
        wm.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${cssW}" height="${cssH}">${rects}</svg>`;
    }

    // ── Progress ──────────────────────────────────────────────────────
    function updateProgress() {
        if (!pdfDoc) return;
        const pct = (pageNum / pdfDoc.numPages) * 100;
        ['reading-progress-bar', 'fs-progress-bar'].forEach(id => { const e = document.getElementById(id); if (e) e.style.width = pct + '%'; });
        const est = Math.ceil((pdfDoc.numPages - pageNum) * 1.5);
        const pt = document.getElementById('progress-text');
        if (pt) pt.textContent = `Hal. ${pageNum}/${pdfDoc.numPages} · ${Math.round(pct)}%` + (est > 0 ? ` · ~${est} mnt` : '');
        ['sheet-page-num', 'tap-page-num'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = pageNum; });
    }

    // ── Zoom display ──────────────────────────────────────────────────
    function updateZoomDisplay() {
        const label = Math.round(zoomFactor * 100) + '%';
        const barPct = Math.round(((zoomFactor - ZOOM_MIN) / (ZOOM_MAX - ZOOM_MIN)) * 100);
        ['zoom-level', 'fs-zoom-level'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = label; });
        ['sheet-zoom-val', 'tap-zoom-val'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = label; });
        ['sheet-zoom-fill', 'tap-zoom-fill'].forEach(id => { const e = document.getElementById(id); if (e) e.style.width = Math.max(4, barPct) + '%'; });
    }

    // ── Bookmark ──────────────────────────────────────────────────────
    function updateBookmarkUI() {
        const on = bookmarkedPage === pageNum;
        ['bkmk-icon', 'fs-bkmk-icon', 'sheet-bkmk-icon', 'tap-bkmk-icon'].forEach(id => {
            const ic = document.getElementById(id);
            if (ic) { ic.setAttribute('fill', on ? '#FF6B18' : 'none'); ic.setAttribute('stroke', on ? '#FF6B18' : 'currentColor'); }
        });
        ['bookmark-btn', 'fs-bookmark-btn'].forEach(id => { const b = document.getElementById(id); if (b) b.classList.toggle('is-bkmk', on); });
        const sbtn = document.getElementById('sheet-bookmark-btn'); if (sbtn) sbtn.classList.toggle('bookmarked', on);
        const slbl = document.getElementById('sheet-bkmk-label'); if (slbl) slbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';
        const tbtn = document.getElementById('tap-bookmark-btn'); if (tbtn) tbtn.classList.toggle('bookmarked', on);
        const tlbl = document.getElementById('tap-bkmk-label'); if (tlbl) tlbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';
    }

    function toggleBookmark() {
        if (bookmarkedPage === pageNum) { bookmarkedPage = null; localStorage.removeItem(SK.bkmk); snack('Bookmark dihapus'); }
        else { bookmarkedPage = pageNum; localStorage.setItem(SK.bkmk, pageNum); snack('🔖 Halaman ' + pageNum + ' ditandai!'); }
        updateBookmarkUI();
    }

    // ── Reading Mode ──────────────────────────────────────────────────
    function applyMode(mode) {
        document.body.classList.remove('read-mode-sepia', 'read-mode-night');
        if (mode !== 'normal') document.body.classList.add('read-mode-' + mode);
        currentMode = mode; localStorage.setItem(SK.mode, mode);
        document.querySelectorAll('.mode-opt').forEach(e => e.classList.toggle('active', e.dataset.mode === mode));
        document.querySelectorAll('[data-sheet-mode]').forEach(e => e.classList.toggle('active', e.dataset.sheetMode === mode));
        document.querySelectorAll('[data-tap-mode]').forEach(e => e.classList.toggle('active', e.dataset.tapMode === mode));
    }
    applyMode(currentMode);

    // ── Scale ─────────────────────────────────────────────────────────
    const getScale = () => baseScale * zoomFactor;

    function computeBase(page) {
        const containerWidth = viewerEl.clientWidth || window.innerWidth;
        const padding = isMobile() ? 4 : 16;
        const availW = containerWidth - padding * 2;
        const nativeW = page.getViewport({ scale: 1 }).width;
        baseScale = Math.max(0.6, Math.min(availW / nativeW, 2.5));
    }

    // ═══════════════════════════════════════════════════════════════════
    // renderPage — DPR-aware untuk tampilan tajam di semua layar
    // ═══════════════════════════════════════════════════════════════════
    function renderPage(num) {
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null && num > GUEST_PAGE_LIMIT) {
            num = GUEST_PAGE_LIMIT;
            if (!gateShown) checkGuestGate();
            return;
        }

        pageRendering = true;
        hideLoading(); showCanvas();

        pdfDoc.getPage(num).then(async page => {
            if (baseScale === 1.0) computeBase(page);

            const cssScale = getScale();
            const renderScale = cssScale * DPR;
            const vpCss = page.getViewport({ scale: cssScale });
            const vpRender = page.getViewport({ scale: renderScale });

            canvas.width = Math.floor(vpRender.width);
            canvas.height = Math.floor(vpRender.height);
            canvas.style.width = Math.floor(vpCss.width) + 'px';
            canvas.style.height = Math.floor(vpCss.height) + 'px';
            stage.style.width = Math.floor(vpCss.width) + 'px';
            stage.style.height = Math.floor(vpCss.height) + 'px';

            await page.render({ canvasContext: ctx, viewport: vpRender }).promise.catch(e => console.warn(e.message));

            pageRendering = false;
            if (pageNumPending !== null) { const p = pageNumPending; pageNumPending = null; renderPage(p); return; }

            await renderTextLayer(page, vpCss);
            renderAnnotationsOnLayer();
            renderWatermark(Math.floor(vpCss.width), Math.floor(vpCss.height));

            localStorage.setItem(SK.page, num);
            localStorage.setItem(SK.zoom, zoomFactor);
            document.getElementById('page-num-input').value = num;
            document.getElementById('fs-page-num').textContent = num;
            updateNavButtons(); updateZoomDisplay(); updateProgress(); updateBookmarkUI();
            canvasWrap.scrollTo({ top: 0, behavior: 'smooth' });
            if (searchResults.length > 0) applySearchHighlights();

            checkGuestGate();

        }).catch(e => { console.error(e.message); pageRendering = false; hideLoading(); showCanvas(); });
    }

    function queueRender(n) { if (pageRendering) pageNumPending = n; else renderPage(n); }

    // ═══════════════════════════════════════════════════════════════════
    // ✅ FIX TEXT LAYER — pakai official pdfjsLib.renderTextLayer
    //    agar posisi span EXACT match dengan canvas (tidak meleset)
    // ═══════════════════════════════════════════════════════════════════
    async function renderTextLayer(page, viewport) {
        textLayer.innerHTML = '';
        textLayer.style.width = Math.floor(viewport.width) + 'px';
        textLayer.style.height = Math.floor(viewport.height) + 'px';

        const textContent = await page.getTextContent();

        // ── Coba official API dulu (pdf.js >= 2.x) ───────────────────
        try {
            const renderTask = pdfjsLib.renderTextLayer({
                textContentSource: textContent,
                container: textLayer,
                viewport: viewport,
                textDivs: [],
            });
            // Normalkan: beberapa versi return object {promise}, beberapa return Promise langsung
            await (renderTask.promise || renderTask);
            return;
        } catch (e) {
            // Fallback ke manual jika API tidak tersedia / error
        }

        // ── Fallback manual — lebih akurat dari versi sebelumnya ──────
        textContent.items.forEach(item => {
            if (!item.str || !item.str.trim()) return;

            const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
            const fontHeight = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]);
            const angle = Math.atan2(tx[1], tx[0]);

            const span = document.createElement('span');
            span.textContent = item.str;
            span.style.position = 'absolute';
            span.style.fontSize = fontHeight + 'px';
            span.style.fontFamily = 'sans-serif';
            span.style.left = tx[4] + 'px';
            span.style.top = (tx[5] - fontHeight) + 'px';
            span.style.lineHeight = '1';
            span.style.whiteSpace = 'pre';
            span.style.color = 'transparent';
            span.style.cursor = 'text';
            span.style.transformOrigin = '0% 0%';
            span.style.userSelect = 'text';
            span.style.webkitUserSelect = 'text';

            // Append dulu agar bisa diukur getBoundingClientRect
            textLayer.appendChild(span);

            // ✅ scaleX: ukur lebar NYATA di DOM, bukan estimasi 0.55
            if (item.width > 0) {
                const measuredWidth = span.getBoundingClientRect().width;
                const targetWidth = item.width * viewport.scale;
                let transform = angle !== 0 ? `rotate(${-angle}rad)` : '';
                if (measuredWidth > 0 && targetWidth > 0) {
                    transform += ` scaleX(${targetWidth / measuredWidth})`;
                }
                if (transform.trim()) span.style.transform = transform.trim();
            } else if (angle !== 0) {
                span.style.transform = `rotate(${-angle}rad)`;
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════
    // ✅ GUEST GATE
    // ═══════════════════════════════════════════════════════════════════
    function checkGuestGate() {
        if (!IS_GUEST || GUEST_PAGE_LIMIT === null || !pdfDoc) return;
        const totalPages = pdfDoc.numPages;
        const limit = GUEST_PAGE_LIMIT;

        if (limitWarn && pageNum === Math.max(1, limit - 1) && !gateShown) {
            const remaining = limit - pageNum;
            const titleEl = document.getElementById('page-limit-warning-title');
            const textEl = document.getElementById('page-limit-warning-text');
            if (titleEl) titleEl.textContent = `⚠️ ${remaining} halaman lagi!`;
            if (textEl) textEl.textContent = `Login untuk baca semua ${totalPages} halaman secara gratis.`;
            limitWarn.classList.add('show');
            setTimeout(() => limitWarn.classList.remove('show'), 6000);
        }

        if (pageNum >= limit && !gateShown) {
            gateShown = true;
            const ids = {
                'gg-pages-shown': limit + ' hal.',
                'gg-total-pages': totalPages + ' hal.',
                'gg-stat-read': limit,
                'gg-stat-left': (totalPages - limit),
                'gg-stat-total': totalPages,
            };
            Object.entries(ids).forEach(([id, val]) => { const e = document.getElementById(id); if (e) e.textContent = val; });
            if (guestGate) guestGate.classList.add('show');
        }

        if (pageNum < limit && gateShown) {
            gateShown = false;
            if (guestGate) guestGate.classList.remove('show');
        }
    }

    // ── Navigation ────────────────────────────────────────────────────
    function prevPage() { if (pageNum > 1) { pageNum--; queueRender(pageNum); } }
    function nextPage() {
        if (!pdfDoc) return;
        const maxPage = (IS_GUEST && GUEST_PAGE_LIMIT !== null) ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages) : pdfDoc.numPages;
        if (pageNum < maxPage) { pageNum++; queueRender(pageNum); }
        else if (IS_GUEST && GUEST_PAGE_LIMIT !== null && pageNum >= GUEST_PAGE_LIMIT) { if (!gateShown) checkGuestGate(); }
    }
    function goTo(n) {
        if (!pdfDoc) return;
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null) n = Math.min(n, GUEST_PAGE_LIMIT);
        if (n >= 1 && n <= pdfDoc.numPages) { pageNum = n; queueRender(n); }
    }

    function updateNavButtons() {
        ['prev-page', 'fs-prev', 'sheet-prev', 'tap-prev'].forEach(id => { const e = document.getElementById(id); if (e) e.disabled = pageNum <= 1; });
        const maxForGuest = (IS_GUEST && GUEST_PAGE_LIMIT !== null && pdfDoc) ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages) : (pdfDoc ? pdfDoc.numPages : 1);
        ['next-page', 'fs-next', 'sheet-next', 'tap-next'].forEach(id => { const e = document.getElementById(id); if (e) e.disabled = pageNum >= maxForGuest; });
    }

    // ── Zoom ──────────────────────────────────────────────────────────
    function zoomIn() { zoomFactor = Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX); queueRender(pageNum); }
    function zoomOut() { zoomFactor = Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN); queueRender(pageNum); }

    // ═══════════════════════════════════════════════════════════════════
    // ANNOTATION SYSTEM
    // ═══════════════════════════════════════════════════════════════════
    function saveAnnotations() { localStorage.setItem(SK.annot, JSON.stringify(annotations)); }

    function renderAnnotationsOnLayer() {
        annotLayer.innerHTML = '';
        const pageAnnots = annotations.filter(a => a.page === pageNum);
        const scale = getScale();
        pageAnnots.forEach(annot => {
            const el = document.createElement('div');
            el.className = `annot-highlight color-${annot.color}`;
            el.style.left = (annot.rect.x * scale) + 'px';
            el.style.top = (annot.rect.y * scale) + 'px';
            el.style.width = (annot.rect.w * scale) + 'px';
            el.style.height = (annot.rect.h * scale) + 'px';
            el.dataset.id = annot.id;
            el.addEventListener('click', e => { e.stopPropagation(); showAnnotTooltip(annot, e.clientX, e.clientY); });
            annotLayer.appendChild(el);
        });
    }

    function showAnnotTooltip(annot, x, y) {
        activeAnnotId = annot.id;
        document.getElementById('annot-tooltip-text').textContent =
            annot.comment ? `💬 ${annot.comment}` : `Stabilo ${annot.color} — "${annot.selectedText?.substring(0, 60)}..."`;
        annotTip.classList.add('show');
        const vw = window.innerWidth, vh = window.innerHeight;
        const tw = 260, th = 100;
        annotTip.style.left = Math.min(x, vw - tw - 12) + 'px';
        annotTip.style.top = (y + 12 + th > vh ? y - th - 8 : y + 12) + 'px';
    }

    document.getElementById('annot-tooltip-close').addEventListener('click', () => { annotTip.classList.remove('show'); activeAnnotId = null; });
    document.getElementById('annot-tooltip-del').addEventListener('click', () => {
        if (!activeAnnotId) return;
        annotations = annotations.filter(a => a.id !== activeAnnotId);
        saveAnnotations(); renderAnnotationsOnLayer();
        annotTip.classList.remove('show'); activeAnnotId = null;
        snack('Anotasi dihapus');
    });

    function getSelectionRect() {
        const sel = window.getSelection();
        if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
        const range = sel.getRangeAt(0);
        const stRect = stage.getBoundingClientRect();
        const rects = Array.from(range.getClientRects());
        if (!rects.length) return null;
        const left = Math.min(...rects.map(r => r.left));
        const top = Math.min(...rects.map(r => r.top));
        const right = Math.max(...rects.map(r => r.right));
        const bottom = Math.max(...rects.map(r => r.bottom));
        const scale = getScale();
        return { x: (left - stRect.left) / scale, y: (top - stRect.top) / scale, w: (right - left) / scale, h: (bottom - top) / scale };
    }

    function showAnnotToolbar(range) {
        const sel = window.getSelection();
        if (!sel || sel.isCollapsed) return;
        const rect = sel.getRangeAt(0).getBoundingClientRect();
        const vw = window.innerWidth, vh = window.innerHeight;
        const tbW = 300, tbH = 52;
        let tx = Math.min(rect.left + rect.width / 2 - tbW / 2, vw - tbW - 8);
        let ty = rect.top - tbH - 12;
        if (ty < 8) ty = rect.top + 20;
        annotTb.style.left = Math.max(8, tx) + 'px';
        annotTb.style.top = ty + 'px';
        annotTb.classList.add('show');
    }
    function hideAnnotToolbar() { annotTb.classList.remove('show'); }

    document.addEventListener('mouseup', e => {
        if (e.target.closest('#annot-toolbar, #comment-popup, #annot-tooltip')) return;
        setTimeout(() => {
            const sel = window.getSelection();
            if (sel && !sel.isCollapsed && sel.rangeCount > 0) {
                const range = sel.getRangeAt(0);
                if (textLayer.contains(range.commonAncestorContainer)) showAnnotToolbar(range);
            } else hideAnnotToolbar();
        }, 50);
    });

    document.addEventListener('touchend', e => {
        if (e.target.closest('#annot-toolbar, #comment-popup, #annot-tooltip, #mobile-tap-overlay')) return;
        setTimeout(() => {
            const sel = window.getSelection();
            if (sel && !sel.isCollapsed && sel.rangeCount > 0) {
                const range = sel.getRangeAt(0);
                if (textLayer.contains(range.commonAncestorContainer)) showAnnotToolbar(range);
            }
        }, 200);
    });

    document.querySelectorAll('.annot-tool-btn[data-color]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const color = btn.dataset.color;
            const rect = getSelectionRect();
            const sel = window.getSelection();
            if (!rect) { snack('Pilih teks dulu!'); return; }
            annotations.push({ id: Date.now(), page: pageNum, color, rect, selectedText: sel ? sel.toString() : '', comment: '' });
            saveAnnotations(); renderAnnotationsOnLayer();
            sel?.removeAllRanges(); hideAnnotToolbar();
            snack(`✏️ Stabilo ${color} diterapkan!`);
        });
    });

    document.getElementById('add-comment-btn').addEventListener('click', e => {
        e.stopPropagation();
        const rect = getSelectionRect();
        if (!rect) { snack('Pilih teks dulu!'); return; }
        const sel = window.getSelection();
        const br = sel?.getRangeAt(0).getBoundingClientRect();
        const vw = window.innerWidth, vh = window.innerHeight;
        commentPop.style.left = Math.min((br?.left || 100) - 140, vw - 296) + 'px';
        commentPop.style.top = ((br?.bottom || 200) + 10 + 160 > vh ? (br?.top || 200) - 170 : (br?.bottom || 200) + 10) + 'px';
        commentPop.classList.add('show');
        document.getElementById('comment-text').value = '';
        document.getElementById('comment-text').focus();
    });

    document.getElementById('comment-save').addEventListener('click', () => {
        const rect = getSelectionRect();
        const sel = window.getSelection();
        const comment = document.getElementById('comment-text').value.trim();
        if (!rect || !comment) { snack('Tulis komentar dulu!'); return; }
        annotations.push({ id: Date.now(), page: pageNum, color: 'yellow', rect, selectedText: sel?.toString() || '', comment });
        saveAnnotations(); renderAnnotationsOnLayer();
        sel?.removeAllRanges(); commentPop.classList.remove('show'); hideAnnotToolbar();
        snack('💬 Komentar disimpan!');
    });

    document.getElementById('comment-cancel').addEventListener('click', () => commentPop.classList.remove('show'));
    document.getElementById('annot-close-btn').addEventListener('click', () => { window.getSelection()?.removeAllRanges(); hideAnnotToolbar(); });
    document.addEventListener('click', e => {
        if (!annotTb.contains(e.target) && !commentPop.contains(e.target) && !annotTip.contains(e.target)) {
            if (!e.target.closest('#text-layer')) hideAnnotToolbar();
            annotTip.classList.remove('show');
        }
    });

    // ═══════════════════════════════════════════════════════════════════
    // SEARCH
    // ═══════════════════════════════════════════════════════════════════
    let searchDebounce = null;
    let currentSearchQuery = '';

    function openSearch() { document.getElementById('search-overlay').classList.add('show'); document.getElementById('search-input').focus(); }
    function closeSearch() {
        document.getElementById('search-overlay').classList.remove('show');
        document.getElementById('search-results-list').innerHTML = '';
        document.getElementById('search-status').textContent = 'Ketik untuk mencari...';
        document.getElementById('search-match-info').textContent = '';
        document.getElementById('search-input').value = '';
        searchResults = []; searchIndex = -1; currentSearchQuery = '';
        clearSearchHighlights();
    }

    // ✅ FIX clearSearchHighlights — hapus dari annotLayer juga
    function clearSearchHighlights() {
        annotLayer.querySelectorAll('.search-highlight').forEach(el => el.remove());
        searchHighlightEls = [];
    }

    // ═══════════════════════════════════════════════════════════════════
    // ✅ FIX applySearchHighlights — pakai Range API
    //    Lebih akurat karena mengukur posisi karakter yang NYATA di DOM,
    //    bukan estimasi charWidth rata-rata yang sering meleset
    // ═══════════════════════════════════════════════════════════════════
    function applySearchHighlights() {
        clearSearchHighlights();
        if (!currentSearchQuery || !pdfDoc) return;

        const q = currentSearchQuery.toLowerCase();
        const stRect = stage.getBoundingClientRect();
        const spans = Array.from(textLayer.querySelectorAll('span'));

        let globalIdx = 0;

        spans.forEach(span => {
            if (!span.firstChild) return;
            const text = span.textContent;
            const lower = text.toLowerCase();
            let idx = lower.indexOf(q);

            while (idx !== -1) {
                try {
                    // ✅ Range API: dapatkan rect TEPAT untuk substring yang match
                    const range = document.createRange();
                    range.setStart(span.firstChild, idx);
                    range.setEnd(span.firstChild, Math.min(idx + q.length, text.length));

                    const rects = Array.from(range.getClientRects());

                    rects.forEach(rect => {
                        if (rect.width < 1 || rect.height < 1) return;

                        const el = document.createElement('div');
                        el.className = 'search-highlight';

                        // Posisi relatif terhadap stage
                        el.style.left = (rect.left - stRect.left) + 'px';
                        el.style.top = (rect.top - stRect.top) + 'px';
                        el.style.width = rect.width + 'px';
                        el.style.height = rect.height + 'px';
                        el.dataset.matchIdx = globalIdx;

                        annotLayer.appendChild(el);
                        searchHighlightEls.push(el);
                    });
                } catch (e) {
                    // Abaikan error Range (span kosong, dll)
                }

                globalIdx++;
                idx = lower.indexOf(q, idx + 1);
            }
        });

        highlightActiveMatch();
    }

    function highlightActiveMatch() {
        searchHighlightEls.forEach((el, i) => el.classList.toggle('active-match', i === searchIndex));
        if (searchHighlightEls[searchIndex]) {
            searchHighlightEls[searchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        document.querySelectorAll('.sri').forEach((el, i) => el.classList.toggle('active-sri', i === searchIndex));
    }

    async function doSearch(query) {
        if (!pdfDoc || !query.trim()) {
            document.getElementById('search-status').textContent = 'Ketik untuk mencari...';
            document.getElementById('search-results-list').innerHTML = '';
            document.getElementById('search-match-info').textContent = '';
            clearSearchHighlights(); currentSearchQuery = ''; return;
        }
        document.getElementById('search-status').textContent = 'Mencari di semua halaman...';
        searchResults = []; currentSearchQuery = query;
        const q = query.toLowerCase();
        const maxSearchPage = (IS_GUEST && GUEST_PAGE_LIMIT !== null) ? Math.min(GUEST_PAGE_LIMIT, pdfDoc.numPages) : pdfDoc.numPages;

        for (let p = 1; p <= maxSearchPage; p++) {
            const page = await pdfDoc.getPage(p);
            const content = await page.getTextContent();
            const text = content.items.map(i => i.str).join(' ');
            const lText = text.toLowerCase();
            let idx = lText.indexOf(q);
            while (idx !== -1) {
                searchResults.push({ page: p, excerpt: text.substring(Math.max(0, idx - 35), idx + q.length + 50).trim(), charIdx: idx });
                idx = lText.indexOf(q, idx + 1);
            }
        }

        const list = document.getElementById('search-results-list');
        const status = document.getElementById('search-status');
        list.innerHTML = '';

        if (!searchResults.length) {
            status.textContent = `Tidak ditemukan: "${query}"`;
            document.getElementById('search-match-info').textContent = '';
            clearSearchHighlights();
            return;
        }

        status.textContent = `${searchResults.length} hasil ditemukan`;
        searchIndex = 0;

        searchResults.slice(0, 40).forEach((r, i) => {
            const item = document.createElement('div');
            item.className = 'sri' + (i === 0 ? ' active-sri' : '');
            const hl = r.excerpt.replace(
                new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'),
                m => `<mark${i === 0 ? ' class="active-mark"' : ''}>${m}</mark>`
            );
            item.innerHTML = `<span class="pg">Hal.${r.page}</span><span class="ex">${hl}</span>`;
            item.addEventListener('click', () => {
                searchIndex = i;
                document.querySelectorAll('.sri').forEach((el, j) => el.classList.toggle('active-sri', j === i));
                if (r.page !== pageNum) goTo(r.page); else { applySearchHighlights(); highlightActiveMatch(); }
                updateMatchInfo();
            });
            list.appendChild(item);
        });

        updateMatchInfo();
        if (searchResults[0].page === pageNum) applySearchHighlights(); else goTo(searchResults[0].page);
    }

    function updateMatchInfo() {
        const el = document.getElementById('search-match-info');
        const onPage = searchResults.filter(r => r.page === pageNum);
        if (searchResults.length) el.textContent = `${searchIndex + 1}/${searchResults.length} hasil · ${onPage.length} di halaman ini`;
    }

    function searchNavNext() {
        if (!searchResults.length) return;
        searchIndex = (searchIndex + 1) % searchResults.length;
        const r = searchResults[searchIndex];
        if (r.page !== pageNum) goTo(r.page); else { applySearchHighlights(); highlightActiveMatch(); }
        updateMatchInfo();
    }
    function searchNavPrev() {
        if (!searchResults.length) return;
        searchIndex = (searchIndex - 1 + searchResults.length) % searchResults.length;
        const r = searchResults[searchIndex];
        if (r.page !== pageNum) goTo(r.page); else { applySearchHighlights(); highlightActiveMatch(); }
        updateMatchInfo();
    }

    document.getElementById('search-input').addEventListener('input', function () { clearTimeout(searchDebounce); searchDebounce = setTimeout(() => doSearch(this.value), 450); });
    document.getElementById('search-close-btn').addEventListener('click', closeSearch);
    document.getElementById('search-prev-btn').addEventListener('click', searchNavPrev);
    document.getElementById('search-next-btn').addEventListener('click', searchNavNext);
    document.getElementById('search-overlay').addEventListener('click', e => { if (e.target === document.getElementById('search-overlay')) closeSearch(); });
    document.getElementById('search-input').addEventListener('keydown', e => { if (e.key === 'Enter') e.shiftKey ? searchNavPrev() : searchNavNext(); });

    // ── Mobile Tap Overlay ────────────────────────────────────────────
    function openTapOverlay() { tapOverlayOpen = true; tapOverlay.classList.add('show'); }
    function closeTapOverlay() { tapOverlayOpen = false; tapOverlay.classList.remove('show'); }

    document.getElementById('tap-close-overlay').addEventListener('click', closeTapOverlay);
    document.getElementById('tap-prev').addEventListener('click', prevPage);
    document.getElementById('tap-next').addEventListener('click', nextPage);
    document.getElementById('tap-zoom-in').addEventListener('click', zoomIn);
    document.getElementById('tap-zoom-out').addEventListener('click', zoomOut);
    document.getElementById('tap-bookmark-btn').addEventListener('click', toggleBookmark);
    document.getElementById('tap-exit-btn').addEventListener('click', () => { closeTapOverlay(); exitFullscreen(); });
    document.querySelectorAll('[data-tap-mode]').forEach(el => {
        el.addEventListener('click', () => { applyMode(el.dataset.tapMode); snack({ normal: '☀️ Normal', sepia: '📜 Sepia', night: '🌙 Night' }[el.dataset.tapMode]); });
    });

    // ── Bottom Sheet ──────────────────────────────────────────────────
    function openSheet() { sheetIsOpen = true; document.getElementById('sheet-backdrop').classList.add('show'); document.getElementById('bottom-sheet').classList.add('show'); }
    function closeSheet() { sheetIsOpen = false; document.getElementById('sheet-backdrop').classList.remove('show'); document.getElementById('bottom-sheet').classList.remove('show'); }

    document.getElementById('sheet-backdrop').addEventListener('click', closeSheet);
    document.getElementById('sheet-close').addEventListener('click', closeSheet);
    document.getElementById('sheet-prev').addEventListener('click', prevPage);
    document.getElementById('sheet-next').addEventListener('click', nextPage);
    document.getElementById('sheet-zoom-in').addEventListener('click', zoomIn);
    document.getElementById('sheet-zoom-out').addEventListener('click', zoomOut);
    document.getElementById('sheet-bookmark-btn').addEventListener('click', toggleBookmark);
    document.getElementById('sheet-fs-btn').addEventListener('click', () => { closeSheet(); setTimeout(enterFullscreen, 200); });
    document.getElementById('sheet-search-btn').addEventListener('click', () => { closeSheet(); setTimeout(openSearch, 200); });
    document.getElementById('sheet-jump-go').addEventListener('click', () => { const n = parseInt(document.getElementById('sheet-jump').value); if (n) { goTo(n); closeSheet(); } });
    document.getElementById('sheet-jump').addEventListener('keydown', e => { if (e.key === 'Enter') { const n = parseInt(e.target.value); if (n) { goTo(n); closeSheet(); } } });
    document.querySelectorAll('[data-sheet-mode]').forEach(el => {
        el.addEventListener('click', () => { applyMode(el.dataset.sheetMode); snack({ normal: '☀️ Normal', sepia: '📜 Sepia', night: '🌙 Night' }[el.dataset.sheetMode]); });
    });

    // ── Mobile FAB ────────────────────────────────────────────────────
    document.getElementById('mobile-fab-btn').addEventListener('click', e => { e.stopPropagation(); openSheet(); });

    // ── Fullscreen ────────────────────────────────────────────────────
    function enterFullscreen() {
        isFullscreen = true;
        viewerEl.classList.add('fullscreen-mode');
        document.body.style.overflow = 'hidden';
        if (!isMobile()) {
            deskHint.classList.remove('hidden', 'fade-out');
            clearTimeout(toolbarTimer);
            toolbarTimer = setTimeout(() => deskHint.classList.add('fade-out'), 4500);
        }
        if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); });
    }
    function exitFullscreen() {
        isFullscreen = false;
        viewerEl.classList.remove('fullscreen-mode');
        document.body.style.overflow = '';
        deskHint.classList.add('hidden');
        closeTapOverlay();
        if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); });
    }

    viewerEl.addEventListener('mousemove', () => {
        if (!isFullscreen || isMobile()) return;
        fsTb.classList.remove('toolbar-hidden');
        clearTimeout(toolbarTimer);
        toolbarTimer = setTimeout(() => fsTb.classList.add('toolbar-hidden'), 3000);
    });

    viewerEl.addEventListener('click', e => {
        if (!isFullscreen || !isMobile()) return;
        if (e.target.closest('#pdf-fullscreen-toolbar,#mobile-tap-overlay,#bottom-sheet,#guest-gate-overlay')) return;
        if (window.getSelection()?.toString()) return;
        tapOverlayOpen ? closeTapOverlay() : openTapOverlay();
    });

    // ── Iframe Fallback ───────────────────────────────────────────────
    function showFallback() {
        if (IS_GUEST) {
            hideLoading(); canvasWrap.style.display = 'flex'; canvasWrap.classList.remove('hidden');
            const stageEl = document.getElementById('pdf-stage');
            if (stageEl) stageEl.style.display = 'none';
            const errDiv = document.createElement('div');
            errDiv.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;padding:2rem;text-align:center;max-width:360px;margin:auto;';
            errDiv.innerHTML = `<div style="font-size:2.5rem">📄</div><p style="color:#fff;font-weight:700;font-size:1rem">Gagal memuat dokumen</p><p style="color:#9CA3AF;font-size:.875rem">Login untuk membaca publikasi ini secara penuh.</p><a href="${window.PDF_CONFIG.loginUrl}" style="padding:.65rem 1.5rem;background:#FF6B18;color:#fff;border-radius:10px;font-weight:700;font-size:.875rem;text-decoration:none;">🔓 Masuk Sekarang</a>`;
            canvasWrap.appendChild(errDiv);
            return;
        }
        hideLoading();
        canvasWrap.style.display = 'none';
        iframeEl.style.display = 'block';
        iframeEl.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH';
    }

    // ── Resume Toast ──────────────────────────────────────────────────
    function showResumeToast(page) {
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null) page = Math.min(page, GUEST_PAGE_LIMIT);
        const t = document.getElementById('resume-toast');
        document.getElementById('resume-text').textContent = `Terakhir di halaman ${page}`;
        t.classList.add('show');
        document.getElementById('resume-yes').onclick = () => { goTo(page); t.classList.remove('show'); };
        document.getElementById('resume-no').onclick = () => { goTo(1); t.classList.remove('show'); };
        setTimeout(() => t.classList.remove('show'), 7000);
    }

    // ── Load PDF ──────────────────────────────────────────────────────
    const FALLBACK_TIMEOUT = IS_GUEST ? 30000 : 12000;
    let fbTimer = setTimeout(() => { if (!pdfDoc) showFallback(); }, FALLBACK_TIMEOUT);
    const loadingText = document.querySelector('#pdf-loading p:first-of-type');
    const loadingSubtext = document.querySelector('#pdf-loading p:last-of-type');

    const pdfLoadingTask = pdfjsLib.getDocument({
        url: pdfUrl, withCredentials: false, verbosity: 0,
        rangeChunkSize: 65536, disableAutoFetch: false, disableStream: false,
    });

    pdfLoadingTask.onProgress = function (data) {
        if (data.total && data.total > 0) {
            const pct = Math.round((data.loaded / data.total) * 100);
            if (loadingText) loadingText.textContent = `Mengunduh dokumen... ${pct}%`;
            if (pct >= 100 && loadingSubtext) loadingSubtext.textContent = 'Merender halaman...';
        }
    };

    pdfLoadingTask.promise.then(doc => {
        clearTimeout(fbTimer); fbTimer = null; pdfDoc = doc;
        const total = doc.numPages;
        ['page-count', 'fs-page-count', 'sheet-total', 'tap-page-total'].forEach(id => { const e = document.getElementById(id); if (e) e.textContent = total; });
        document.getElementById('page-num-input').max = total;
        document.getElementById('sheet-jump').max = total;
        if (IS_GUEST && GUEST_PAGE_LIMIT !== null && total > GUEST_PAGE_LIMIT) {
            const pc = document.getElementById('page-count');
            if (pc) pc.textContent = `${GUEST_PAGE_LIMIT}* (dari ${total})`;
        }
        renderPage(1);
        if (savedPage > 1 && savedPage <= total) setTimeout(() => showResumeToast(savedPage), 900);
    }).catch(err => { clearTimeout(fbTimer); fbTimer = null; console.error('PDF load error:', err); showFallback(); });

    // ── Resize ────────────────────────────────────────────────────────
    let lastW = viewerEl.clientWidth, rTimer = null;
    window.addEventListener('resize', () => {
        const w = viewerEl.clientWidth; if (Math.abs(w - lastW) < 20) return; lastW = w;
        clearTimeout(rTimer);
        rTimer = setTimeout(() => { if (!pdfDoc) return; pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); }); }, 250);
    });

    // ── Desktop Event Listeners ───────────────────────────────────────
    document.getElementById('prev-page').addEventListener('click', prevPage);
    document.getElementById('next-page').addEventListener('click', nextPage);
    document.getElementById('fs-prev').addEventListener('click', prevPage);
    document.getElementById('fs-next').addEventListener('click', nextPage);
    document.getElementById('zoom-in').addEventListener('click', zoomIn);
    document.getElementById('zoom-out').addEventListener('click', zoomOut);
    document.getElementById('fs-zoom-in').addEventListener('click', zoomIn);
    document.getElementById('fs-zoom-out').addEventListener('click', zoomOut);
    document.getElementById('bookmark-btn').addEventListener('click', toggleBookmark);
    document.getElementById('fs-bookmark-btn').addEventListener('click', toggleBookmark);
    document.getElementById('fullscreen-btn').addEventListener('click', enterFullscreen);
    document.getElementById('exit-fs-btn').addEventListener('click', exitFullscreen);
    document.getElementById('search-btn').addEventListener('click', openSearch);
    document.getElementById('mode-btn').addEventListener('click', e => { e.stopPropagation(); document.getElementById('mode-dropdown').classList.toggle('open'); });
    document.querySelectorAll('.mode-opt').forEach(el => { el.addEventListener('click', () => { applyMode(el.dataset.mode); document.getElementById('mode-dropdown').classList.remove('open'); }); });
    document.addEventListener('click', () => document.getElementById('mode-dropdown')?.classList.remove('open'));
    document.getElementById('page-num-input').addEventListener('change', function () {
        const n = parseInt(this.value);
        if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) goTo(n); else this.value = pageNum;
    });

    // ── Keyboard ──────────────────────────────────────────────────────
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') { e.preventDefault(); openSearch(); return; }
        if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
        switch (e.key) {
            case 'ArrowLeft': prevPage(); break;
            case 'ArrowRight': nextPage(); break;
            case 'ArrowUp': canvasWrap.scrollBy({ top: -120, behavior: 'smooth' }); break;
            case 'ArrowDown': canvasWrap.scrollBy({ top: 120, behavior: 'smooth' }); break;
            case '+': case '=': zoomIn(); break;
            case '-': zoomOut(); break;
            case 'b': case 'B': toggleBookmark(); break;
            case 'f': case 'F': isFullscreen ? exitFullscreen() : enterFullscreen(); break;
            case 'Escape':
                if (document.getElementById('search-overlay').classList.contains('show')) closeSearch();
                else if (commentPop.classList.contains('show')) commentPop.classList.remove('show');
                else if (isFullscreen) exitFullscreen();
                break;
        }
    });

    // ── Touch: Swipe + Pinch ─────────────────────────────────────────
    let tx = 0, ty = 0, pd = 0, touchMoved = false, pinching = false;

    viewerEl.addEventListener('touchstart', e => {
        touchMoved = false; pinching = false;
        if (e.touches.length === 1) { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }
        if (e.touches.length === 2) { pinching = true; pd = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); }
    }, { passive: true });

    viewerEl.addEventListener('touchmove', e => {
        touchMoved = true;
        if (e.touches.length !== 2) return;
        const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
        if (Math.abs(d - pd) > 14) { if (d > pd) zoomIn(); else zoomOut(); pd = d; }
    }, { passive: true });

    viewerEl.addEventListener('touchend', e => {
        if (pinching || !touchMoved) return;
        const dx = tx - e.changedTouches[0].clientX;
        const dy = ty - e.changedTouches[0].clientY;
        if (Math.abs(dx) > Math.abs(dy) * 1.8 && Math.abs(dx) > 65) {
            if (tapOverlayOpen) { closeTapOverlay(); return; }
            if (window.getSelection()?.toString()) return;
            dx > 0 ? nextPage() : prevPage();
        }
    }, { passive: true });

    // ── Guest Download Modal ──────────────────────────────────────────
    window.showGuestDownloadModal = function () {
        const modal = document.getElementById('guestDownloadModal');
        const backdrop = document.getElementById('guestModalBackdrop');
        const container = document.getElementById('guestModalContainer');
        if (!modal) return;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100'); backdrop.classList.remove('opacity-0');
            container.classList.add('opacity-100', 'scale-100'); container.classList.remove('opacity-0', 'scale-95');
        });
    };
    window.hideGuestDownloadModal = function () {
        const modal = document.getElementById('guestDownloadModal');
        const backdrop = document.getElementById('guestModalBackdrop');
        const container = document.getElementById('guestModalContainer');
        if (!modal) return;
        backdrop.classList.remove('opacity-100'); backdrop.classList.add('opacity-0');
        container.classList.remove('opacity-100', 'scale-100'); container.classList.add('opacity-0', 'scale-95');
        setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
    };
    document.addEventListener('keydown', e => { if (e.key === 'Escape') window.hideGuestDownloadModal?.(); });

})();
