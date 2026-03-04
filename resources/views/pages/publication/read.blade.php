@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* Container full height minus navbar (sesuaikan tinggi navbar jika beda) */
    #pdf-viewer-container {
        height: calc(100vh - 64px);
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

    /* Control Bar Styling */
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

    /* Page Number Input */
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

    /* Loading Animation */
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

    /* Mobile-first tweaks */
    @media (max-width: 640px) {
        .pdf-header-title {
            font-size: 0.85rem;
        }

        .pdf-header-meta {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')

{{-- Header/Control Bar --}}
<div class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-3 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-3 sm:py-4">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">

            {{-- Left: Back Button + Title --}}
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
                    <div class="pdf-header-meta flex items-center gap-1.5 sm:gap-2 mt-1">
                        <span class="px-2 py-0.5 bg-[#FF6B18] text-white text-[10px] sm:text-xs font-semibold rounded">
                            {{ $category }}
                        </span>
                        <span class="text-gray-400 text-[10px] sm:text-xs hidden xs:inline">
                            {{ $authors->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Center: Page Controls --}}
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

            {{-- Right: Zoom + Download --}}
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

{{-- PDF Viewer Container --}}
<div id="pdf-viewer-container" class="relative">
    {{-- Loading State --}}
    <div id="pdf-loading" class="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Loading PDF...</p>
    </div>

    {{-- Wrapper agar canvas gampang di-center dan scrollable --}}
    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let baseScale = 1.0; // scale dasar berdasar lebar container
    let zoomFactor = 1.0; // zoom user (1x, 1.25x, dst)

    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const loadingEl = document.getElementById('pdf-loading');
    const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
    const viewerContainer = document.getElementById('pdf-viewer-container');

    function getCurrentScale() {
        return baseScale * zoomFactor;
    }

    /**
     * Hitung baseScale agar PDF fit-width dan responsif
     */
    function computeBaseScale(page) {
        const containerWidth = viewerContainer.clientWidth;
        const viewport = page.getViewport({ scale: 1 });
        // padding kiri-kanan wrapper ~16px (0.5rem x 2), kita kurangi sedikit
        const availableWidth = containerWidth - 16;
        const scale = availableWidth / viewport.width;
        // batas minimal / maksimal supaya tetap nyaman
        baseScale = Math.max(0.5, Math.min(scale, 2.0));
    }

    /**
     * Render halaman
     */
    function renderPage(num) {
        pageRendering = true;

        pdfDoc.getPage(num).then(function(page) {
            // untuk pertama kali atau saat resize: hitung baseScale dari lebar container
            if (!baseScale || baseScale === 1.0) {
                computeBaseScale(page); // responsif, fit width[web:1][web:5]
            }

            const scale = getCurrentScale();
            const viewport = page.getViewport({ scale });

            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            const renderTask = page.render(renderContext);

            renderTask.promise.then(function() {
                pageRendering = false;
                if (pageNumPending !== null) {
                    const pending = pageNumPending;
                    pageNumPending = null;
                    renderPage(pending);
                }
            });

            // Setelah render dipanggil, update UI
            document.getElementById('page-num-input').value = num;
            updateNavigationButtons();
            updateZoomDisplay();
        });
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    function onZoomIn() {
        zoomFactor = Math.min(zoomFactor + 0.25, 3.0);
        queueRenderPage(pageNum);
    }

    function onZoomOut() {
        zoomFactor = Math.max(zoomFactor - 0.25, 0.5);
        queueRenderPage(pageNum);
    }

    function updateZoomDisplay() {
        const zoomLevelEl = document.getElementById('zoom-level');
        const percentage = Math.round(getCurrentScale() * 100);
        zoomLevelEl.textContent = percentage + '%';
    }

    function updateNavigationButtons() {
        document.getElementById('prev-page').disabled = pageNum <= 1;
        document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;
    }

    /**
     * Handle resize: hitung ulang baseScale hanya jika lebar container berubah cukup signifikan
     * supaya tidak terasa "loading terus" pada perubahan kecil.
     */
    let lastContainerWidth = viewerContainer.clientWidth;
    let resizeTimeout = null;

    function handleResize() {
        const newWidth = viewerContainer.clientWidth;
        if (Math.abs(newWidth - lastContainerWidth) < 40) {
            return; // abaikan perubahan kecil
        }
        lastContainerWidth = newWidth;

        if (!pdfDoc) return;

        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            pdfDoc.getPage(pageNum).then(function(page) {
                computeBaseScale(page);
                queueRenderPage(pageNum);
            });
        }, 200);
    }

    window.addEventListener('resize', handleResize);

    /**
     * Load PDF
     */
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;

        // Hide loading, show canvas wrapper
        loadingEl.classList.add('hidden');
        canvasWrapper.classList.remove('hidden');

        // Render first page
        renderPage(pageNum);
    }).catch(function(error) {
        loadingEl.innerHTML = `
            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="font-semibold text-red-500">Failed to load PDF</p>
            <p class="text-sm text-gray-400">${error.message}</p>
        `;
        console.error('Error loading PDF:', error);
    });

    // Event Listeners
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

    // Keyboard shortcuts (desktop)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            onPrevPage();
        } else if (e.key === 'ArrowRight') {
            onNextPage();
        } else if (e.key === '+' || e.key === '=') {
            onZoomIn();
        } else if (e.key === '-') {
            onZoomOut();
        }
    });
</script>
@endpush