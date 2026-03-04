@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    #pdf-viewer-container {
        height: calc(100vh - 64px);
        /* sesuaikan dengan navbar height */
        background: #2D2D2D;
    }

    #pdf-canvas-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        padding: 0.5rem;
    }

    #pdf-canvas {
        max-width: 100%;
        height: auto;
        display: block;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }

    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        border-bottom: 2px solid #FF6B18;
    }

    .pdf-control-btn {
        transition: all 0.3s ease;
    }

    .pdf-control-btn:hover {
        background: #FF6B18;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
    }

    .pdf-control-btn:active {
        transform: translateY(0);
    }

    .page-input {
        background: #3D3D3D;
        border: 2px solid #4D4D4D;
        color: white;
        transition: all 0.3s ease;
    }

    .page-input:focus {
        border-color: #FF6B18;
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.1);
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
        width: 50px;
        height: 50px;
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

    @media (max-width: 640px) {
        .pdf-header-title {
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-3 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-3 sm:py-4">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center flex-1 min-w-0 gap-3 sm:gap-4">
                <a href="{{ route('publikasi.show', $publication->slug) }}"
                    class="pdf-control-btn p-2.5 sm:p-3 bg-[#3D3D3D] text-white rounded-lg hover:bg-[#FF6B18] transition-all flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden font-semibold sm:inline">Kembali</span>
                </a>

                <div class="min-w-0">
                    <h1 class="text-xs font-bold text-white truncate pdf-header-title sm:text-sm md:text-lg">
                        {{ $publication->title }}
                    </h1>
                    <div class="flex items-center gap-1.5 sm:gap-2 mt-1">
                        <span class="px-2 py-0.5 bg-[#FF6B18] text-white text-[10px] sm:text-xs font-semibold rounded">
                            {{ $category }}
                        </span>
                        <span class="text-gray-400 text-[10px] sm:text-xs hidden sm:inline">
                            {{ $authors->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-[#3D3D3D] rounded-lg px-2.5 py-1.5 text-xs sm:text-sm">
                <button id="prev-page"
                    class="pdf-control-btn p-1.5 sm:p-2 bg-[#4D4D4D] text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex items-center gap-1.5 text-white">
                    <input type="number" id="page-num-input"
                        class="page-input w-12 sm:w-14 text-center rounded px-1.5 py-0.5 sm:py-1 font-semibold text-xs sm:text-sm"
                        value="1" min="1">
                    <span>/</span>
                    <span id="page-count" class="font-semibold">-</span>
                </div>

                <button id="next-page"
                    class="pdf-control-btn p-1.5 sm:p-2 bg-[#4D4D4D] text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button id="zoom-out" class="pdf-control-btn p-2 sm:p-3 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span id="zoom-level"
                    class="text-white text-xs sm:text-sm font-semibold min-w-[2.5rem] text-center">100%</span>
                <button id="zoom-in" class="pdf-control-btn p-2 sm:p-3 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>
                <a href="{{ route('publikasi.download', $publication->slug) }}"
                    class="pdf-control-btn p-2.5 sm:p-3 bg-[#FF6B18] text-white rounded-lg hover:bg-[#E64627] flex items-center gap-1.5 sm:gap-2 ml-1.5 sm:ml-2 text-xs sm:text-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden font-semibold sm:inline">Download</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div id="pdf-viewer-container" class="relative">
    <div id="pdf-loading" class="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Loading PDF...</p>
    </div>
    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    console.log('PDF URL:', pdfUrl); // ✅ Debug URL

    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let baseScale = 1.0;
    let zoomFactor = 1.0;

    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const loadingEl = document.getElementById('pdf-loading');
    const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
    const viewerContainer = document.getElementById('pdf-viewer-container');

    function getCurrentScale() { return baseScale * zoomFactor; }

    function computeBaseScale(page) {
        const containerWidth = viewerContainer.clientWidth || window.innerWidth;
        const viewport = page.getViewport({ scale: 1 });
        const availableWidth = containerWidth - 16;
        baseScale = Math.max(0.5, Math.min(availableWidth / viewport.width, 2.0));
    }

    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then(function(page) {
            if (baseScale === 1.0) computeBaseScale(page);
            const scale = getCurrentScale();
            const viewport = page.getViewport({ scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const renderContext = { canvasContext: ctx, viewport };
            page.render(renderContext).promise.then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    const pending = pageNumPending;
                    pageNumPending = null;
                    renderPage(pending);
                }
            });
            document.getElementById('page-num-input').value = num;
            updateNavigationButtons();
            updateZoomDisplay();
        });
    }

    function queueRenderPage(num) {
        if (pageRendering) pageNumPending = num;
        else renderPage(num);
    }

    function onPrevPage() { if (pageNum > 1) { pageNum--; queueRenderPage(pageNum); } }
    function onNextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; queueRenderPage(pageNum); } }

    function onZoomIn() { zoomFactor = Math.min(zoomFactor + 0.25, 3.0); queueRenderPage(pageNum); }
    function onZoomOut() { zoomFactor = Math.max(zoomFactor - 0.25, 0.5); queueRenderPage(pageNum); }

    function updateZoomDisplay() {
        document.getElementById('zoom-level').textContent = Math.round(getCurrentScale() * 100) + '%';
    }

    function updateNavigationButtons() {
        document.getElementById('prev-page').disabled = pageNum <= 1;
        document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;
    }

    let lastContainerWidth = 0;
    let resizeTimeout = null;
    function handleResize() {
        const newWidth = viewerContainer.clientWidth;
        if (Math.abs(newWidth - lastContainerWidth) < 40) return;
        lastContainerWidth = newWidth;
        if (!pdfDoc) return;
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            pdfDoc.getPage(pageNum).then(page => {
                computeBaseScale(page);
                queueRenderPage(pageNum);
            });
        }, 200);
    }
    window.addEventListener('resize', handleResize);

    // ✅ LOAD PDF + ANTI-LOADING-STUCK
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;

        // ✅ HIDE LOADING IMMEDIATELY
        loadingEl.classList.add('hidden');
        canvasWrapper.classList.remove('hidden');

        renderPage(pageNum);
    }).catch(function(error) {
        // ✅ HIDE LOADING + SHOW ERROR
        loadingEl.classList.remove('hidden');
        loadingEl.innerHTML = `
            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="font-semibold text-red-500">PDF tidak tersedia</p>
            <p class="text-sm text-gray-400">${error.message}</p>
        `;
        console.error('Error loading PDF:', error);
    });

    // ✅ FALLBACK: hide loading max 5 detik
    setTimeout(() => {
        if (loadingEl.classList.contains('flex')) {
            loadingEl.classList.add('hidden');
            canvasWrapper.classList.remove('hidden');
        }
    }, 5000);

    // Event listeners
    document.getElementById('prev-page').addEventListener('click', onPrevPage);
    document.getElementById('next-page').addEventListener('click', onNextPage);
    document.getElementById('zoom-in').addEventListener('click', onZoomIn);
    document.getElementById('zoom-out').addEventListener('click', onZoomOut);

    document.getElementById('page-num-input').addEventListener('change', function() {
        if (!pdfDoc) return;
        let num = parseInt(this.value);
        if (num >= 1 && num <= pdfDoc.numPages) {
            pageNum = num;
            queueRenderPage(pageNum);
        } else {
            this.value = pageNum;
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') onPrevPage();
        else if (e.key === 'ArrowRight') onNextPage();
        else if (e.key === '+' || e.key === '=') onZoomIn();
        else if (e.key === '-') onZoomOut();
    });
</script>
@endpush