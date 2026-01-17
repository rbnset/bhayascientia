@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* Full Screen PDF Viewer */
    #pdf-viewer-container {
        height: calc(100vh - 80px);
        background: #2D2D2D;
    }

    #pdf-canvas {
        max-width: 100%;
        margin: 0 auto;
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
</style>
@endpush

@section('content')

{{-- Header/Control Bar --}}
<div class="pdf-controls sticky top-0 z-50 shadow-lg">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1400px] py-4">
        <div class="flex items-center justify-between flex-wrap gap-4">

            {{-- Left: Back Button + Title --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <a href="{{ route('publikasi.show', $publication->slug) }}"
                    class="pdf-control-btn p-3 bg-[#3D3D3D] text-white rounded-lg hover:bg-[#FF6B18] transition-all flex items-center gap-2 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline font-semibold">Kembali</span>
                </a>

                <div class="min-w-0">
                    <h1 class="text-white font-bold text-sm sm:text-base md:text-lg truncate">
                        {{ $publication->title }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 bg-[#FF6B18] text-white text-xs font-semibold rounded">
                            {{ $category }}
                        </span>
                        <span class="text-gray-400 text-xs hidden sm:inline">
                            {{ $authors->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Center: Page Controls --}}
            <div class="flex items-center gap-2 bg-[#3D3D3D] rounded-lg px-3 py-2">
                <button id="prev-page"
                    class="pdf-control-btn p-2 bg-[#4D4D4D] text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex items-center gap-2 text-white text-sm">
                    <input type="number" id="page-num-input"
                        class="page-input w-14 text-center rounded px-2 py-1 font-semibold" value="1" min="1">
                    <span>/</span>
                    <span id="page-count" class="font-semibold">-</span>
                </div>

                <button id="next-page"
                    class="pdf-control-btn p-2 bg-[#4D4D4D] text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Right: Zoom + Download --}}
            <div class="flex items-center gap-2">
                <button id="zoom-out" class="pdf-control-btn p-3 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>

                <span id="zoom-level" class="text-white text-sm font-semibold min-w-[3rem] text-center">100%</span>

                <button id="zoom-in" class="pdf-control-btn p-3 bg-[#3D3D3D] text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>

                <a href="{{ route('publikasi.download', $publication->slug) }}"
                    class="pdf-control-btn p-3 bg-[#FF6B18] text-white rounded-lg hover:bg-[#E64627] flex items-center gap-2 ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden sm:inline font-semibold">Download</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- PDF Viewer Container --}}
<div id="pdf-viewer-container" class="relative overflow-auto">
    {{-- Loading State --}}
    <div id="pdf-loading" class="pdf-loading">
        <div class="spinner"></div>
        <p class="text-white font-semibold">Loading PDF...</p>
    </div>

    {{-- Canvas untuk render PDF --}}
    <canvas id="pdf-canvas" class="hidden"></canvas>
</div>

@endsection

@push('scripts')
{{-- PDF.js Library dari CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    // ✅ Set PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 1.0;

    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const loadingEl = document.getElementById('pdf-loading');

    /**
     * ✅ Render halaman PDF
     */
    function renderPage(num) {
        pageRendering = true;

        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
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
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        });

        // Update page number display
        document.getElementById('page-num-input').value = num;
        updateNavigationButtons();
    }

    /**
     * ✅ Queue render jika sedang rendering
     */
    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    /**
     * ✅ Previous page
     */
    function onPrevPage() {
        if (pageNum <= 1) {
            return;
        }
        pageNum--;
        queueRenderPage(pageNum);
    }

    /**
     * ✅ Next page
     */
    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) {
            return;
        }
        pageNum++;
        queueRenderPage(pageNum);
    }

    /**
     * ✅ Zoom in/out
     */
    function onZoomIn() {
        scale = Math.min(scale + 0.25, 3.0);
        updateZoomDisplay();
        queueRenderPage(pageNum);
    }

    function onZoomOut() {
        scale = Math.max(scale - 0.25, 0.5);
        updateZoomDisplay();
        queueRenderPage(pageNum);
    }

    function updateZoomDisplay() {
        document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
    }

    /**
     * ✅ Update navigation buttons state
     */
    function updateNavigationButtons() {
        document.getElementById('prev-page').disabled = pageNum <= 1;
        document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;
    }

    /**
     * ✅ Load PDF
     */
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;

        // Hide loading, show canvas
        loadingEl.classList.add('hidden');
        canvas.classList.remove('hidden');

        // Render first page
        renderPage(pageNum);
    }).catch(function(error) {
        loadingEl.innerHTML = `
            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-red-500 font-semibold">Failed to load PDF</p>
            <p class="text-gray-400 text-sm">${error.message}</p>
        `;
        console.error('Error loading PDF:', error);
    });

    // ✅ Event Listeners
    document.getElementById('prev-page').addEventListener('click', onPrevPage);
    document.getElementById('next-page').addEventListener('click', onNextPage);
    document.getElementById('zoom-in').addEventListener('click', onZoomIn);
    document.getElementById('zoom-out').addEventListener('click', onZoomOut);

    // ✅ Page number input
    document.getElementById('page-num-input').addEventListener('change', function() {
        let num = parseInt(this.value);
        if (num >= 1 && num <= pdfDoc.numPages) {
            pageNum = num;
            queueRenderPage(pageNum);
        } else {
            this.value = pageNum;
        }
    });

    // ✅ Keyboard shortcuts
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
