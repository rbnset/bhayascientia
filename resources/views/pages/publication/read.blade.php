@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* ─── Base Container ─────────────────────────────────────────── */
    #pdf-viewer-container {
        height: calc(100vh - 64px);
        background: #2D2D2D;
        position: relative;
        overflow: hidden;
        transition: background 0.3s ease;
    }

    /* ─── Reading Modes ──────────────────────────────────────────── */
    body.read-mode-sepia #pdf-viewer-container {
        background: #f4ecd8;
    }

    body.read-mode-night #pdf-viewer-container {
        background: #111;
    }

    body.read-mode-sepia #pdf-canvas {
        filter: sepia(0.6) brightness(0.92);
    }

    body.read-mode-night #pdf-canvas {
        filter: invert(1) hue-rotate(180deg) brightness(0.85);
    }

    body.read-mode-sepia .pdf-controls {
        background: linear-gradient(135deg, #3b2f1e, #5c4a32) !important;
    }

    body.read-mode-night .pdf-controls {
        background: linear-gradient(135deg, #0a0a0a, #1a1a1a) !important;
    }

    /* ─── Progress Bar ───────────────────────────────────────────── */
    #reading-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: #FF6B18;
        transition: width 0.4s ease;
        z-index: 20;
        border-radius: 0 2px 2px 0;
    }

    /* ─── Loading Overlay ────────────────────────────────────────── */
    #pdf-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        background: #2D2D2D;
        z-index: 10;
    }

    #pdf-loading.hidden {
        display: none !important;
    }

    /* ─── Canvas Wrapper ─────────────────────────────────────────── */
    #pdf-canvas-wrapper {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        padding: 0.75rem;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }

    #pdf-canvas-wrapper.hidden {
        display: none !important;
    }

    /* ─── Canvas ─────────────────────────────────────────────────── */
    #pdf-canvas {
        max-width: 100%;
        display: block;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.6);
        border-radius: 2px;
        transition: filter 0.3s ease;
    }

    /* ─── Fullscreen ─────────────────────────────────────────────── */
    #pdf-viewer-container.fullscreen-mode {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    /* ─── Fullscreen Toolbar ─────────────────────────────────────── */
    #pdf-fullscreen-toolbar {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10001;
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        padding: 0.5rem 1rem;
        gap: 0.5rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-fullscreen-toolbar {
        display: flex !important;
    }

    #pdf-fullscreen-toolbar.toolbar-hidden {
        opacity: 0;
        transform: translateY(-100%);
        pointer-events: none;
    }

    /* ─── Fullscreen canvas area ─────────────────────────────────── */
    #pdf-viewer-container.fullscreen-mode #pdf-canvas-wrapper {
        top: 56px;
        padding: 1rem;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-loading {
        top: 56px;
    }

    /* ─── Shortcut Hint (fullscreen) ─────────────────────────────── */
    #shortcut-hint {
        position: absolute;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.75);
        color: #ccc;
        font-size: 11px;
        padding: 6px 14px;
        border-radius: 99px;
        white-space: nowrap;
        z-index: 10002;
        pointer-events: none;
        opacity: 1;
        transition: opacity 0.5s ease;
    }

    #shortcut-hint.fade-out {
        opacity: 0;
    }

    /* ─── Resume Toast ───────────────────────────────────────────── */
    #resume-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background: #1A1A1A;
        border: 1px solid #FF6B18;
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 13px;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-width: 280px;
    }

    #resume-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    /* ─── Bookmark indicator ─────────────────────────────────────── */
    #bookmark-indicator {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: #FF6B18;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 99px;
        z-index: 15;
        display: none;
        pointer-events: none;
    }

    /* ─── Reading Mode Dropdown ──────────────────────────────────── */
    #mode-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: #1A1A1A;
        border: 1px solid #3D3D3D;
        border-radius: 10px;
        overflow: hidden;
        z-index: 200;
        min-width: 140px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
        display: none;
    }

    #mode-dropdown.open {
        display: block;
    }

    .mode-option {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.9rem;
        cursor: pointer;
        font-size: 13px;
        color: #ccc;
        transition: background 0.15s;
    }

    .mode-option:hover {
        background: #2D2D2D;
        color: white;
    }

    .mode-option.active {
        color: #FF6B18;
        font-weight: 600;
    }

    /* ─── Toolbar shared ─────────────────────────────────────────── */
    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        transition: background 0.3s ease;
    }

    .pdf-control-btn {
        transition: all 0.2s ease;
        cursor: pointer;
        border-radius: 8px;
    }

    .pdf-control-btn:hover:not(:disabled) {
        background: #FF6B18 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.35);
    }

    .pdf-control-btn:active {
        transform: translateY(0);
    }

    .pdf-control-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .pdf-control-btn.active-mode {
        background: #FF6B18 !important;
    }

    .page-input {
        background: #3D3D3D;
        border: 2px solid #4D4D4D;
        color: white;
        outline: none;
        border-radius: 6px;
    }

    .page-input:focus {
        border-color: #FF6B18;
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.15);
    }

    /* ─── Spinner ────────────────────────────────────────────────── */
    .spinner {
        border: 4px solid #3D3D3D;
        border-top-color: #FF6B18;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        animation: spin 0.9s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ─── Iframe fallback ────────────────────────────────────────── */
    #pdf-iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: none;
        display: none;
        z-index: 4;
    }

    /* ─── Responsive ─────────────────────────────────────────────── */
    @media (max-width: 640px) {
        .hide-mobile {
            display: none !important;
        }

        #resume-toast {
            right: 0.75rem;
            left: 0.75rem;
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')

{{-- ════════════════ FULLSCREEN TOOLBAR ════════════════ --}}
<div id="pdf-fullscreen-toolbar">
    {{-- Title --}}
    <div class="flex items-center flex-1 min-w-0 gap-2">
        <span class="text-white font-bold text-xs sm:text-sm truncate max-w-[160px] sm:max-w-xs">
            {{ $publication->title }}
        </span>
        <span id="fs-bookmark-badge"
            class="hidden px-2 py-0.5 bg-orange-500 text-white text-[10px] font-bold rounded-full">
            🔖 Ditandai
        </span>
    </div>

    {{-- Page Controls --}}
    <div class="flex items-center gap-1.5 bg-[#3D3D3D] rounded-lg px-2 py-1">
        <button id="fs-prev-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="text-xs font-semibold text-white">
            <span id="fs-page-num">1</span>/<span id="fs-page-count">-</span>
        </span>
        <button id="fs-next-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Zoom --}}
    <div class="flex items-center gap-1.5">
        <button id="fs-zoom-out" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
            </svg>
        </button>
        <span id="fs-zoom-level" class="w-10 text-xs font-semibold text-center text-white">100%</span>
        <button id="fs-zoom-in" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
            </svg>
        </button>
    </div>

    {{-- Bookmark + Exit --}}
    <div class="flex items-center gap-1.5">
        <button id="fs-bookmark-btn" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white"
            title="Tandai halaman ini (B)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
        </button>
        <button id="exit-fullscreen-btn"
            class="pdf-control-btn flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:!bg-red-700 text-white text-xs font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="hide-mobile">Keluar</span>
        </button>
    </div>

    {{-- Progress bar inside fullscreen toolbar --}}
    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#3D3D3D]">
        <div id="fs-reading-progress-bar" class="h-full bg-[#FF6B18] transition-all duration-300" style="width:0%">
        </div>
    </div>
</div>

{{-- ════════════════ NORMAL TOOLBAR ════════════════ --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-3 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-2.5">
        <div class="flex flex-wrap items-center justify-between gap-2">

            {{-- Kiri: Back + Title --}}
            <div class="flex items-center flex-1 min-w-0 gap-2.5">
                <a href="{{ route('publikasi.show', $publication->slug) }}"
                    class="pdf-control-btn p-2.5 bg-[#3D3D3D] text-white flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden text-sm font-semibold sm:inline">Kembali</span>
                </a>
                <div class="min-w-0">
                    <h1 class="text-xs font-bold text-white truncate sm:text-sm">{{ $publication->title }}</h1>
                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                        <span class="px-2 py-0.5 bg-[#FF6B18] text-white text-[10px] font-semibold rounded-full">
                            {{ $category }}
                        </span>
                        <span id="progress-text" class="text-gray-400 text-[10px] hidden sm:inline"></span>
                    </div>
                </div>
            </div>

            {{-- Tengah: Page Nav --}}
            <div class="flex items-center gap-2 bg-[#3D3D3D] rounded-lg px-2.5 py-1.5">
                <button id="prev-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="flex items-center gap-1 text-xs text-white sm:text-sm">
                    <input type="number" id="page-num-input"
                        class="page-input w-10 sm:w-12 text-center px-1 py-0.5 font-semibold text-xs sm:text-sm"
                        value="1" min="1">
                    <span class="text-gray-400">/</span>
                    <span id="page-count" class="font-semibold">-</span>
                </div>
                <button id="next-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Kanan: Zoom + Bookmark + Mode + Fullscreen + Download --}}
            <div class="flex items-center gap-1 sm:gap-1.5">
                <button id="zoom-out" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span id="zoom-level" class="w-10 text-xs font-semibold text-center text-white">100%</span>
                <button id="zoom-in" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>

                {{-- Bookmark --}}
                <button id="bookmark-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white"
                    title="Tandai halaman (B)">
                    <svg id="bookmark-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </button>

                {{-- Reading Mode --}}
                <div class="relative">
                    <button id="mode-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white" title="Mode Baca">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                    </button>
                    <div id="mode-dropdown">
                        <div class="mode-option active" data-mode="normal">☀️ Normal</div>
                        <div class="mode-option" data-mode="sepia">📜 Sepia</div>
                        <div class="mode-option" data-mode="night">🌙 Night</div>
                    </div>
                </div>

                {{-- Fullscreen --}}
                <button id="fullscreen-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white" title="Layar Penuh (F)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>

                {{-- Download --}}
                <a href="{{ route('publikasi.download', $publication->slug) }}"
                    class="pdf-control-btn p-2 sm:px-3 bg-[#FF6B18] hover:!bg-[#E64627] text-white flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden text-sm font-semibold sm:inline">Download</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Progress bar bawah toolbar --}}
    <div class="h-0.5 bg-[#3D3D3D]">
        <div id="reading-progress-bar" class="h-full bg-[#FF6B18] transition-all duration-300" style="width:0%"></div>
    </div>
</div>

{{-- ════════════════ PDF VIEWER ════════════════ --}}
<div id="pdf-viewer-container">

    {{-- Loading --}}
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-500" id="pdf-loading-hint">Harap tunggu sebentar</p>
    </div>

    {{-- Canvas --}}
    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>

    {{-- Fallback iframe --}}
    <iframe id="pdf-iframe" title="PDF Viewer"></iframe>

    {{-- Bookmark indicator --}}
    <div id="bookmark-indicator">🔖 Ditandai</div>

    {{-- Fullscreen shortcut hint --}}
    <div id="shortcut-hint" class="hidden">
        ← → ganti halaman &nbsp;|&nbsp; +/− zoom &nbsp;|&nbsp; F fullscreen &nbsp;|&nbsp; B bookmark &nbsp;|&nbsp; Esc
        keluar
    </div>
</div>

{{-- ════════════════ RESUME TOAST ════════════════ --}}
<div id="resume-toast">
    <span class="text-2xl">🔖</span>
    <div>
        <p class="text-sm font-semibold">Lanjut membaca?</p>
        <p class="text-xs text-gray-400" id="resume-toast-text">Terakhir di halaman —</p>
    </div>
    <button id="resume-yes"
        class="ml-auto px-3 py-1.5 bg-[#FF6B18] text-white text-xs font-bold rounded-lg flex-shrink-0">
        Lanjut
    </button>
    <button id="resume-no" class="px-2 py-1.5 bg-[#3D3D3D] text-gray-300 text-xs rounded-lg flex-shrink-0">
        Dari Awal
    </button>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
pdfjsLib.verbosity = 0;

// ─── Config ────────────────────────────────────────────────────────
const pdfUrl    = @json($pdfUrl);
const pubSlug   = @json($publication->slug);
const STORAGE_KEY_PAGE = `bhaya_read_page_${pubSlug}`;
const STORAGE_KEY_ZOOM = `bhaya_read_zoom_${pubSlug}`;
const STORAGE_KEY_MODE = `bhaya_read_mode_${pubSlug}`;
const STORAGE_KEY_BKMK = `bhaya_bookmark_${pubSlug}`;
console.log('PDF URL:', pdfUrl);

// ─── State ─────────────────────────────────────────────────────────
let pdfDoc         = null;
let pageNum        = 1;
let pageRendering  = false;
let pageNumPending = null;
let baseScale      = 1.0;
let zoomFactor     = parseFloat(localStorage.getItem(STORAGE_KEY_ZOOM)) || 1.0;
let isFullscreen   = false;
let currentMode    = localStorage.getItem(STORAGE_KEY_MODE) || 'normal';
let bookmarkedPage = parseInt(localStorage.getItem(STORAGE_KEY_BKMK)) || null;
let savedPage      = parseInt(localStorage.getItem(STORAGE_KEY_PAGE)) || 1;
let toolbarAutoHideTimer = null;

// ─── DOM refs ──────────────────────────────────────────────────────
const canvas         = document.getElementById('pdf-canvas');
const ctx            = canvas.getContext('2d');
const loadingEl      = document.getElementById('pdf-loading');
const canvasWrapper  = document.getElementById('pdf-canvas-wrapper');
const viewerEl       = document.getElementById('pdf-viewer-container');
const iframeEl       = document.getElementById('pdf-iframe');
const progressBar    = document.getElementById('reading-progress-bar');
const fsProgressBar  = document.getElementById('fs-reading-progress-bar');
const progressText   = document.getElementById('progress-text');
const bookmarkIcon   = document.getElementById('bookmark-icon');
const bookmarkInd    = document.getElementById('bookmark-indicator');
const shortcutHint   = document.getElementById('shortcut-hint');
const fsBkmkBadge    = document.getElementById('fs-bookmark-badge');

// ─── Helpers ───────────────────────────────────────────────────────
function hideLoading() { loadingEl.style.display = 'none'; }
function showCanvas()  {
    canvasWrapper.style.display = 'flex';
    canvasWrapper.classList.remove('hidden');
}

function updateProgress() {
    if (!pdfDoc) return;
    const pct = (pageNum / pdfDoc.numPages) * 100;
    progressBar.style.width    = pct + '%';
    fsProgressBar.style.width  = pct + '%';
    progressText.textContent   = `Hal. ${pageNum} dari ${pdfDoc.numPages} (${Math.round(pct)}%)`;
    const estMin = Math.ceil(((pdfDoc.numPages - pageNum) * 1.5));
    if (estMin > 0) progressText.textContent += ` · ~${estMin} mnt tersisa`;
}

function updateBookmarkUI() {
    const isBookmarked = bookmarkedPage === pageNum;
    if (isBookmarked) {
        bookmarkIcon.setAttribute('fill', '#FF6B18');
        bookmarkIcon.setAttribute('stroke', '#FF6B18');
        bookmarkInd.style.display  = 'block';
        fsBkmkBadge.classList.remove('hidden');
    } else {
        bookmarkIcon.setAttribute('fill', 'none');
        bookmarkIcon.setAttribute('stroke', 'currentColor');
        bookmarkInd.style.display  = 'none';
        fsBkmkBadge.classList.add('hidden');
    }
}

function toggleBookmark() {
    if (bookmarkedPage === pageNum) {
        bookmarkedPage = null;
        localStorage.removeItem(STORAGE_KEY_BKMK);
        showToastMessage('Bookmark dihapus');
    } else {
        bookmarkedPage = pageNum;
        localStorage.setItem(STORAGE_KEY_BKMK, pageNum);
        showToastMessage('🔖 Halaman ' + pageNum + ' ditandai!');
    }
    updateBookmarkUI();
}

function showToastMessage(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;top:1.5rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid #FF6B18;color:white;padding:0.5rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity 0.4s;';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 2000);
}

// ─── Reading Mode ──────────────────────────────────────────────────
function applyMode(mode) {
    document.body.classList.remove('read-mode-sepia','read-mode-night');
    if (mode === 'sepia') document.body.classList.add('read-mode-sepia');
    if (mode === 'night') document.body.classList.add('read-mode-night');
    currentMode = mode;
    localStorage.setItem(STORAGE_KEY_MODE, mode);
    document.querySelectorAll('.mode-option').forEach(el => {
        el.classList.toggle('active', el.dataset.mode === mode);
    });
}
applyMode(currentMode);

document.getElementById('mode-btn').addEventListener('click', e => {
    e.stopPropagation();
    document.getElementById('mode-dropdown').classList.toggle('open');
});
document.querySelectorAll('.mode-option').forEach(el => {
    el.addEventListener('click', () => {
        applyMode(el.dataset.mode);
        document.getElementById('mode-dropdown').classList.remove('open');
    });
});
document.addEventListener('click', () => document.getElementById('mode-dropdown').classList.remove('open'));

// ─── Scale ─────────────────────────────────────────────────────────
function getCurrentScale() { return baseScale * zoomFactor; }

function computeBaseScale(page) {
    const w = viewerEl.clientWidth || window.innerWidth;
    const vp = page.getViewport({ scale: 1 });
    baseScale = Math.max(0.5, Math.min((w - 24) / vp.width, 2.5));
}

// ─── Render ────────────────────────────────────────────────────────
function renderPage(num) {
    pageRendering = true;
    hideLoading();
    showCanvas();

    pdfDoc.getPage(num).then(page => {
        if (baseScale === 1.0) computeBaseScale(page);
        const vp = page.getViewport({ scale: getCurrentScale() });
        canvas.height = vp.height;
        canvas.width  = vp.width;

        page.render({ canvasContext: ctx, viewport: vp }).promise
            .then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    const p = pageNumPending; pageNumPending = null;
                    renderPage(p);
                }
            })
            .catch(err => { console.warn('Render warning:', err.message); pageRendering = false; });

        // Auto-save progress
        localStorage.setItem(STORAGE_KEY_PAGE, num);
        localStorage.setItem(STORAGE_KEY_ZOOM, zoomFactor);

        document.getElementById('page-num-input').value    = num;
        document.getElementById('fs-page-num').textContent = num;
        updateNavButtons();
        updateZoomDisplay();
        updateProgress();
        updateBookmarkUI();
        canvasWrapper.scrollTo({ top: 0, behavior: 'smooth' });

    }).catch(err => {
        console.error('getPage error:', err.message);
        pageRendering = false;
        hideLoading(); showCanvas();
    });
}

function queueRender(num) {
    if (pageRendering) pageNumPending = num;
    else renderPage(num);
}

// ─── Navigation ────────────────────────────────────────────────────
function prevPage() { if (pageNum > 1)               { pageNum--; queueRender(pageNum); } }
function nextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; queueRender(pageNum); } }
function goToPage(num) {
    if (!pdfDoc || num < 1 || num > pdfDoc.numPages) return;
    pageNum = num;
    queueRender(pageNum);
}

function updateNavButtons() {
    ['prev-page','fs-prev-page'].forEach(id => {
        const el = document.getElementById(id); if(el) el.disabled = pageNum <= 1;
    });
    ['next-page','fs-next-page'].forEach(id => {
        const el = document.getElementById(id); if(el) el.disabled = pageNum >= pdfDoc.numPages;
    });
}

// ─── Zoom ───────────────────────────────────────────────────────────
function zoomIn()  { zoomFactor = Math.min(zoomFactor + 0.25, 4.0);  queueRender(pageNum); }
function zoomOut() { zoomFactor = Math.max(zoomFactor - 0.25, 0.25); queueRender(pageNum); }

function updateZoomDisplay() {
    const pct = Math.round(getCurrentScale() * 100) + '%';
    document.getElementById('zoom-level').textContent    = pct;
    document.getElementById('fs-zoom-level').textContent = pct;
}

// ─── Fullscreen ─────────────────────────────────────────────────────
function enterFullscreen() {
    isFullscreen = true;
    viewerEl.classList.add('fullscreen-mode');
    document.body.style.overflow = 'hidden';

    // Show shortcut hint, fade after 4s
    shortcutHint.classList.remove('hidden');
    shortcutHint.classList.remove('fade-out');
    clearTimeout(toolbarAutoHideTimer);
    toolbarAutoHideTimer = setTimeout(() => {
        shortcutHint.classList.add('fade-out');
    }, 4000);

    if (pdfDoc) {
        pdfDoc.getPage(pageNum).then(page => {
            baseScale = 1.0; computeBaseScale(page); queueRender(pageNum);
        });
    }
}

function exitFullscreen() {
    isFullscreen = false;
    viewerEl.classList.remove('fullscreen-mode');
    document.body.style.overflow = '';
    shortcutHint.classList.add('hidden');
    shortcutHint.classList.remove('fade-out');

    if (pdfDoc) {
        pdfDoc.getPage(pageNum).then(page => {
            baseScale = 1.0; computeBaseScale(page); queueRender(pageNum);
        });
    }
}

// Auto-hide fullscreen toolbar on mouse idle
viewerEl.addEventListener('mousemove', () => {
    if (!isFullscreen) return;
    const tb = document.getElementById('pdf-fullscreen-toolbar');
    tb.classList.remove('toolbar-hidden');
    clearTimeout(toolbarAutoHideTimer);
    toolbarAutoHideTimer = setTimeout(() => tb.classList.add('toolbar-hidden'), 3000);
});

// ─── Iframe Fallback ────────────────────────────────────────────────
function showIframeFallback() {
    hideLoading();
    canvasWrapper.style.display = 'none';
    iframeEl.style.display      = 'block';
    iframeEl.style.height       = isFullscreen ? '100vh' : 'calc(100vh - 64px)';
    iframeEl.src                = pdfUrl;
    console.warn('Fallback → iframe');
}

// ─── Resume Toast ───────────────────────────────────────────────────
function showResumeToast(page) {
    const toast = document.getElementById('resume-toast');
    document.getElementById('resume-toast-text').textContent = `Terakhir di halaman ${page}`;
    toast.classList.add('show');

    document.getElementById('resume-yes').onclick = () => {
        goToPage(page); toast.classList.remove('show');
    };
    document.getElementById('resume-no').onclick = () => {
        goToPage(1); toast.classList.remove('show');
    };

    setTimeout(() => toast.classList.remove('show'), 8000);
}

// ─── Load PDF ───────────────────────────────────────────────────────
const fallbackTimer = setTimeout(() => {
    if (!pdfDoc) { console.warn('Timeout → iframe'); showIframeFallback(); }
}, 8000);

pdfjsLib.getDocument({ url: pdfUrl, withCredentials: false, verbosity: 0 })
    .promise.then(doc => {
        clearTimeout(fallbackTimer);
        pdfDoc = doc;

        const total = doc.numPages;
        document.getElementById('page-count').textContent    = total;
        document.getElementById('fs-page-count').textContent = total;
        document.getElementById('page-num-input').max        = total;

        // Start from page 1, then show resume toast if savedPage > 1
        renderPage(1);

        if (savedPage > 1 && savedPage <= total) {
            setTimeout(() => showResumeToast(savedPage), 800);
        }
    })
    .catch(err => {
        clearTimeout(fallbackTimer);
        console.error('PDF load error:', err.message);
        showIframeFallback();
    });

// ─── Resize ─────────────────────────────────────────────────────────
let lastWidth = viewerEl.clientWidth, resizeTimer = null;
window.addEventListener('resize', () => {
    const w = viewerEl.clientWidth;
    if (Math.abs(w - lastWidth) < 30) return;
    lastWidth = w;
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (!pdfDoc) return;
        pdfDoc.getPage(pageNum).then(page => {
            baseScale = 1.0; computeBaseScale(page); queueRender(pageNum);
        });
    }, 250);
});

// ─── Events ─────────────────────────────────────────────────────────
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
document.getElementById('bookmark-btn').addEventListener('click', toggleBookmark);
document.getElementById('fs-bookmark-btn').addEventListener('click', toggleBookmark);

document.getElementById('page-num-input').addEventListener('change', function() {
    const num = parseInt(this.value);
    if (pdfDoc && num >= 1 && num <= pdfDoc.numPages) goToPage(num);
    else this.value = pageNum;
});

// ─── Keyboard ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
    switch (e.key) {
        case 'ArrowLeft': case 'ArrowUp':    prevPage(); break;
        case 'ArrowRight': case 'ArrowDown': nextPage(); break;
        case '+': case '=':                  zoomIn();   break;
        case '-':                            zoomOut();  break;
        case 'b': case 'B':                  toggleBookmark(); break;
        case 'f': case 'F':
            isFullscreen ? exitFullscreen() : enterFullscreen(); break;
        case 'Escape':
            if (isFullscreen) exitFullscreen(); break;
    }
});

// ─── Touch Swipe ────────────────────────────────────────────────────
let touchStartX = 0, touchStartY = 0;
viewerEl.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
}, { passive: true });

viewerEl.addEventListener('touchend', e => {
    const dx = touchStartX - e.changedTouches[0].clientX;
    const dy = touchStartY - e.changedTouches[0].clientY;
    // Swipe horizontal lebih dominan dari vertikal → ganti halaman
    if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 60) {
        dx > 0 ? nextPage() : prevPage();
    }
}, { passive: true });

// ─── Pinch Zoom (mobile) ────────────────────────────────────────────
let lastPinchDist = 0;
viewerEl.addEventListener('touchstart', e => {
    if (e.touches.length === 2) {
        lastPinchDist = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
    }
}, { passive: true });

viewerEl.addEventListener('touchmove', e => {
    if (e.touches.length !== 2) return;
    const dist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
    );
    if (Math.abs(dist - lastPinchDist) > 10) {
        dist > lastPinchDist ? zoomIn() : zoomOut();
        lastPinchDist = dist;
    }
}, { passive: true });
</script>
@endpush