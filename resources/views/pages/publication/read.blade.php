@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    #pdf-viewer-container {
        height: calc(100vh - 64px);
        background: #2D2D2D;
        transition: all 0.3s ease;
    }

    /* ✅ Fullscreen mode */
    #pdf-viewer-container.fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-toolbar {
        display: none !important;
    }

    #pdf-fullscreen-toolbar {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10000;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        border-bottom: 2px solid #FF6B18;
        padding: 0.5rem 1rem;
        transition: opacity 0.3s ease;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-fullscreen-toolbar {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    #pdf-canvas-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        padding: 0.75rem;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-canvas-wrapper {
        height: calc(100vh - 60px);
        margin-top: 60px;
    }

    #pdf-canvas {
        max-width: 100%;
        display: block;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }

    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        border-bottom: 2px solid #FF6B18;
    }

    .pdf-control-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pdf-control-btn:hover {
        background: #FF6B18 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
    }

    .pdf-control-btn:active {
        transform: translateY(0);
    }

    .page-input {
        background: #3D3D3D;
        border: 2px solid #4D4D4D;
        color: white;
        outline: none;
    }

    .page-input:focus {
        border-color: #FF6B18;
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.15);
    }

    .pdf-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        flex-direction: column;
        gap: 1rem;
    }

    .spinner {
        border: 4px solid #3D3D3D;
        border-top: 4px solid #FF6B18;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Fallback iframe */
    #pdf-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: none;
    }

    /* Sembunyikan loading saat canvas sudah ada */
    #pdf-canvas-wrapper:not(.hidden)~#pdf-loading {
        display: none !important;
    }

    @media (max-width: 640px) {
        .hide-mobile {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

{{-- ✅ Fullscreen Toolbar (hanya muncul di fullscreen mode) --}}
<div id="pdf-fullscreen-toolbar">
    <div class="flex items-center flex-1 min-w-0 gap-2">
        <span class="text-white font-bold text-xs sm:text-sm truncate max-w-[200px] sm:max-w-md">
            {{ $publication->title }}
        </span>
    </div>
    <div class="flex items-center gap-2 bg-[#3D3D3D] rounded-lg px-2 py-1">
        <button id="fs-prev-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white rounded disabled:opacity-40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="text-xs font-semibold text-white">
            <span id="fs-page-num">1</span> / <span id="fs-page-count">-</span>
        </span>
        <button id="fs-next-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white rounded disabled:opacity-40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    <div class="flex items-center gap-2">
        <button id="fs-zoom-out" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
            </svg>
        </button>
        <span id="fs-zoom-level" class="text-white text-xs font-semibold min-w-[2.5rem] text-center">100%</span>
        <button id="fs-zoom-in" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
            </svg>
        </button>
        <button id="exit-fullscreen-btn"
            class="pdf-control-btn p-1.5 bg-red-600 hover:bg-red-700 text-white rounded flex items-center gap-1 text-xs font-semibold px-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="hide-mobile">Keluar</span>
        </button>
    </div>
</div>

{{-- ✅ Normal Toolbar --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-3 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">

            {{-- Kiri: Back + Title --}}
            <div class="flex items-center flex-1 min-w-0 gap-3">
                <a href="{{ route('publikasi.show', $publication->slug) }}"
                    class="pdf-control-btn p-2.5 bg-[#3D3D3D] text-white rounded-lg hover:bg-[#FF6B18] flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden text-sm font-semibold sm:inline">Kembali</span>
                </a>
                <div class="min-w-0">
                    <h1 class="text-xs font-bold text-white truncate sm:text-sm md:text-base">
                        {{ $publication->title }}
                    </h1>
                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                        <span class="px-2 py-0.5 bg-[#FF6B18] text-white text-[10px] font-semibold rounded">
                            {{ $category }}
                        </span>
                        <span class="text-gray-400 text-[10px] hidden sm:inline truncate max-w-xs">
                            {{ is_object($authors) ? $authors->pluck('name')->implode(', ') :
                            collect($authors)->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tengah: Page Controls --}}
            <div class="flex items-center gap-2 bg-[#3D3D3D] rounded-lg px-2.5 py-1.5">
                <button id="prev-page"
                    class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white rounded disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="flex items-center gap-1.5 text-white text-xs sm:text-sm">
                    <input type="number" id="page-num-input"
                        class="page-input w-11 sm:w-14 text-center rounded px-1 py-0.5 font-semibold text-xs sm:text-sm"
                        value="1" min="1">
                    <span>/</span>
                    <span id="page-count" class="font-semibold">-</span>
                </div>
                <button id="next-page"
                    class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white rounded disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Kanan: Zoom + Fullscreen + Download --}}
            <div class="flex items-center gap-1.5 sm:gap-2">
                <button id="zoom-out" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span id="zoom-level" class="text-white text-xs font-semibold min-w-[2.5rem] text-center">100%</span>
                <button id="zoom-in" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>

                {{-- ✅ Fullscreen Button --}}
                <button id="fullscreen-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white rounded-lg"
                    title="Mode Baca Penuh (F)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>

                <a href="{{ route('publikasi.download', $publication->slug) }}"
                    class="pdf-control-btn p-2 sm:px-3 bg-[#FF6B18] text-white rounded-lg hover:bg-[#E64627] flex items-center gap-1.5 ml-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden text-sm font-semibold sm:inline">Download</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ✅ PDF Viewer Container --}}
<div id="pdf-viewer-container" class="relative">

    {{-- Loading State --}}
    <div id="pdf-loading" class="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Loading PDF...</p>
        <p class="text-xs text-gray-500" id="pdf-loading-hint"></p>
    </div>

    {{-- Canvas --}}
    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>

    {{-- ✅ Fallback iframe (jika PDF.js gagal total) --}}
    <iframe id="pdf-iframe" title="PDF Viewer"></iframe>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    console.log('PDF URL:', pdfUrl);

    let pdfDoc       = null;
    let pageNum      = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let baseScale    = 1.0;
    let zoomFactor   = 1.0;
    let isFullscreen = false;

    const canvas        = document.getElementById('pdf-canvas');
    const ctx           = canvas.getContext('2d');
    const loadingEl     = document.getElementById('pdf-loading');
    const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
    const viewerEl      = document.getElementById('pdf-viewer-container');
    const iframeEl      = document.getElementById('pdf-iframe');

    // ─── Scale ───────────────────────────────────────────────────────────
    function getCurrentScale() { return baseScale * zoomFactor; }

    function computeBaseScale(page) {
        const containerWidth = viewerEl.clientWidth || window.innerWidth;
        const viewport = page.getViewport({ scale: 1 });
        baseScale = Math.max(0.5, Math.min((containerWidth - 24) / viewport.width, 2.5));
    }

    // ─── Render ───────────────────────────────────────────────────────────
    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then(page => {
            if (baseScale === 1.0) computeBaseScale(page);
            const scale    = getCurrentScale();
            const viewport = page.getViewport({ scale });
            canvas.height  = viewport.height;
            canvas.width   = viewport.width;

            page.render({ canvasContext: ctx, viewport }).promise.then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    const p = pageNumPending;
                    pageNumPending = null;
                    renderPage(p);
                }
            });

            document.getElementById('page-num-input').value = num;
            document.getElementById('fs-page-num').textContent = num;
            updateNavButtons();
            updateZoomDisplay();
        });
    }

    function queueRender(num) {
        if (pageRendering) pageNumPending = num;
        else renderPage(num);
    }

    // ─── Navigation ───────────────────────────────────────────────────────
    function prevPage() { if (pageNum > 1)               { pageNum--; queueRender(pageNum); } }
    function nextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; queueRender(pageNum); } }

    function updateNavButtons() {
        ['prev-page', 'fs-prev-page'].forEach(id => document.getElementById(id).disabled = pageNum <= 1);
        ['next-page', 'fs-next-page'].forEach(id => document.getElementById(id).disabled = pageNum >= pdfDoc.numPages);
    }

    // ─── Zoom ─────────────────────────────────────────────────────────────
    function zoomIn()  { zoomFactor = Math.min(zoomFactor + 0.25, 4.0); queueRender(pageNum); }
    function zoomOut() { zoomFactor = Math.max(zoomFactor - 0.25, 0.25); queueRender(pageNum); }

    function updateZoomDisplay() {
        const pct = Math.round(getCurrentScale() * 100) + '%';
        document.getElementById('zoom-level').textContent    = pct;
        document.getElementById('fs-zoom-level').textContent = pct;
    }

    // ─── Fullscreen ───────────────────────────────────────────────────────
    function enterFullscreen() {
        isFullscreen = true;
        viewerEl.classList.add('fullscreen-mode');
        document.body.style.overflow = 'hidden';

        // Re-hitung scale sesuai layar penuh
        if (pdfDoc) {
            pdfDoc.getPage(pageNum).then(page => {
                baseScale = 1.0; // reset agar recompute
                computeBaseScale(page);
                queueRender(pageNum);
            });
        }
    }

    function exitFullscreen() {
        isFullscreen = false;
        viewerEl.classList.remove('fullscreen-mode');
        document.body.style.overflow = '';

        if (pdfDoc) {
            pdfDoc.getPage(pageNum).then(page => {
                baseScale = 1.0;
                computeBaseScale(page);
                queueRender(pageNum);
            });
        }
    }

    // ─── Fallback iframe ──────────────────────────────────────────────────
    function showIframeFallback() {
        loadingEl.classList.add('hidden');
        canvasWrapper.classList.add('hidden');
        iframeEl.style.display = 'block';
        iframeEl.style.height  = isFullscreen ? '100vh' : 'calc(100vh - 64px)';
        iframeEl.src           = pdfUrl;
        console.warn('PDF.js fallback → menggunakan iframe');
    }

    // ─── Load PDF ─────────────────────────────────────────────────────────
    // Timeout fallback 8 detik
    const fallbackTimer = setTimeout(() => {
        if (!pdfDoc) {
            console.warn('PDF.js timeout, switch ke iframe fallback');
            showIframeFallback();
        }
    }, 8000);

    pdfjsLib.getDocument({
        url: pdfUrl,
        withCredentials: false,
    }).promise.then(doc => {
        clearTimeout(fallbackTimer);
        pdfDoc = doc;

        const total = doc.numPages;
        document.getElementById('page-count').textContent    = total;
        document.getElementById('fs-page-count').textContent = total;
        document.getElementById('page-num-input').max        = total;

        loadingEl.classList.add('hidden');
        canvasWrapper.classList.remove('hidden');

        renderPage(pageNum);
    }).catch(err => {
        clearTimeout(fallbackTimer);
        console.error('PDF.js error:', err);
        showIframeFallback();
    });

    // ─── Resize Debounce ──────────────────────────────────────────────────
    let lastWidth  = viewerEl.clientWidth;
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        const w = viewerEl.clientWidth;
        if (Math.abs(w - lastWidth) < 30) return;
        lastWidth = w;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (!pdfDoc) return;
            pdfDoc.getPage(pageNum).then(page => {
                baseScale = 1.0;
                computeBaseScale(page);
                queueRender(pageNum);
            });
        }, 250);
    });

    // ─── Event Listeners ──────────────────────────────────────────────────
    document.getElementById('prev-page').addEventListener('click', prevPage);
    document.getElementById('next-page').addEventListener('click', nextPage);
    document.getElementById('fs-prev-page').addEventListener('click', prevPage);
    document.getElementById('fs-next-page').addEventListener('click', nextPage);
    document.getElementById('zoom-in').addEventListener('click', zoomIn);
    document.getElementById('zoom-out').addEventListener('click', zoomOut);
    document.getElementById('fs-zoom-in').addEventListener('click', zoomIn);
    document.getElementById('fs-zoom-out').addEventListener('click', zoomOut);
    document.getElementById('fullscreen-btn').addEventListener('click', enterFullscreen);
    document.getElementById('exit-fullscreen-btn').addEventListener('click', exitFullscreen);

    document.getElementById('page-num-input').addEventListener('change', function() {
        if (!pdfDoc) return;
        const num = parseInt(this.value);
        if (num >= 1 && num <= pdfDoc.numPages) { pageNum = num; queueRender(pageNum); }
        else this.value = pageNum;
    });

    // ─── Keyboard Shortcuts ───────────────────────────────────────────────
    document.addEventListener('keydown', e => {
        if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

        switch (e.key) {
            case 'ArrowLeft':  case 'ArrowUp':    prevPage();  break;
            case 'ArrowRight': case 'ArrowDown':  nextPage();  break;
            case '+': case '=':                   zoomIn();    break;
            case '-':                              zoomOut();   break;
            case 'f': case 'F':
                isFullscreen ? exitFullscreen() : enterFullscreen();
                break;
            case 'Escape':
                if (isFullscreen) exitFullscreen();
                break;
        }
    });

    // ─── Touch swipe (mobile) ─────────────────────────────────────────────
    let touchStartX = 0;
    viewerEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
    viewerEl.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextPage(); else prevPage();
        }
    }, { passive: true });
</script>
@endpush