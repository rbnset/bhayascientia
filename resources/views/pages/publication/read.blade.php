@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════
   BASE CONTAINER
═══════════════════════════════════════════════ */
    #pdf-viewer-container {
        height: calc(100vh - 56px);
        background: #2D2D2D;
        position: relative;
        overflow: hidden;
        transition: background 0.3s ease;
    }

    /* ═══════════════════════════════════════════════
   READING MODES
═══════════════════════════════════════════════ */
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

    /* ═══════════════════════════════════════════════
   PROGRESS BAR
═══════════════════════════════════════════════ */
    .reading-progress-track {
        height: 3px;
        background: #3D3D3D;
        flex-shrink: 0;
    }

    .reading-progress-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.4s ease;
        border-radius: 0 2px 2px 0;
    }

    /* ═══════════════════════════════════════════════
   LOADING OVERLAY
═══════════════════════════════════════════════ */
    #pdf-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        background: inherit;
        z-index: 10;
    }

    #pdf-loading.hidden {
        display: none !important;
    }

    /* ═══════════════════════════════════════════════
   CANVAS WRAPPER
═══════════════════════════════════════════════ */
    #pdf-canvas-wrapper {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        padding: 0.5rem;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    #pdf-canvas-wrapper.hidden {
        display: none !important;
    }

    #pdf-canvas {
        max-width: 100%;
        display: block;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.6);
        border-radius: 2px;
        transition: filter 0.3s ease;
    }

    /* ═══════════════════════════════════════════════
   IFRAME FALLBACK
═══════════════════════════════════════════════ */
    #pdf-iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: none;
        display: none;
        z-index: 4;
    }

    /* ═══════════════════════════════════════════════
   FULLSCREEN
═══════════════════════════════════════════════ */
    #pdf-viewer-container.fullscreen-mode {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-canvas-wrapper {
        top: 52px;
        padding: 0.75rem 0.5rem;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-loading {
        top: 52px;
    }

    /* ═══════════════════════════════════════════════
   FULLSCREEN TOOLBAR
═══════════════════════════════════════════════ */
    #pdf-fullscreen-toolbar {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10001;
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        padding: 0.4rem 0.75rem;
        align-items: center;
        gap: 0.5rem;
        transition: opacity 0.3s ease, transform 0.3s ease;
        flex-wrap: nowrap;
        overflow: hidden;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-fullscreen-toolbar {
        display: flex !important;
    }

    #pdf-fullscreen-toolbar.toolbar-hidden {
        opacity: 0;
        transform: translateY(-100%);
        pointer-events: none;
    }

    /* ═══════════════════════════════════════════════
   MOBILE FLOATING ACTION BUTTON (FAB)
═══════════════════════════════════════════════ */
    #mobile-fab {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 1000;
        display: none;
        /* shown via JS on mobile */
    }

    #mobile-fab-btn {
        width: 52px;
        height: 52px;
        background: #FF6B18;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(255, 107, 24, 0.5);
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
        border: none;
        color: white;
    }

    #mobile-fab-btn:active {
        transform: scale(0.92);
    }

    #mobile-fab-menu {
        position: absolute;
        bottom: 60px;
        right: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
        opacity: 0;
        pointer-events: none;
        transform: translateY(10px);
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #mobile-fab-menu.open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .fab-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        background: #1A1A1A;
        border: 1px solid #3D3D3D;
        color: white;
        padding: 0.5rem 0.75rem 0.5rem 0.6rem;
        border-radius: 99px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
        transition: background 0.15s;
    }

    .fab-item:active {
        background: #2D2D2D;
    }

    .fab-item .fab-icon {
        width: 32px;
        height: 32px;
        background: #2D2D2D;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .fab-item.active-bkmk .fab-icon {
        background: #FF6B18;
    }

    /* ═══════════════════════════════════════════════
   MOBILE HINT OVERLAY (first time fullscreen)
═══════════════════════════════════════════════ */
    #mobile-fs-hint {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        z-index: 10002;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        padding: 2rem;
        text-align: center;
    }

    #mobile-fs-hint.show {
        display: flex;
    }

    .hint-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        color: white;
    }

    .hint-icon-wrap {
        width: 56px;
        height: 56px;
        background: rgba(255, 107, 24, 0.2);
        border: 2px solid #FF6B18;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .hint-item p {
        font-size: 13px;
        color: #ccc;
        margin: 0;
    }

    .hint-item strong {
        font-size: 14px;
        color: white;
    }

    /* ═══════════════════════════════════════════════
   DESKTOP SHORTCUT HINT
═══════════════════════════════════════════════ */
    #desktop-shortcut-hint {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: #ccc;
        font-size: 11px;
        padding: 5px 14px;
        border-radius: 99px;
        white-space: nowrap;
        z-index: 10002;
        pointer-events: none;
        opacity: 1;
        transition: opacity 0.5s ease;
    }

    #desktop-shortcut-hint.fade-out {
        opacity: 0;
    }

    /* ═══════════════════════════════════════════════
   RESUME TOAST
═══════════════════════════════════════════════ */
    #resume-toast {
        position: fixed;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: #1A1A1A;
        border: 1px solid #FF6B18;
        color: white;
        padding: 0.65rem 0.875rem;
        border-radius: 14px;
        font-size: 13px;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        white-space: nowrap;
        max-width: calc(100vw - 2rem);
    }

    #resume-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    /* ═══════════════════════════════════════════════
   TOOLBAR SHARED
═══════════════════════════════════════════════ */
    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        transition: background 0.3s ease;
    }

    .pdf-control-btn {
        transition: all 0.2s ease;
        cursor: pointer;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pdf-control-btn:hover:not(:disabled) {
        background: #FF6B18 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.35);
    }

    .pdf-control-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .pdf-control-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .pdf-control-btn.is-bookmarked {
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

    /* ═══════════════════════════════════════════════
   MODE DROPDOWN
═══════════════════════════════════════════════ */
    #mode-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        background: #1A1A1A;
        border: 1px solid #3D3D3D;
        border-radius: 10px;
        overflow: hidden;
        z-index: 200;
        min-width: 130px;
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
        padding: 0.6rem 0.875rem;
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
        font-weight: 700;
    }

    /* ═══════════════════════════════════════════════
   SPINNER
═══════════════════════════════════════════════ */
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

    /* ═══════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════ */
    @media (max-width: 767px) {
        #mobile-fab {
            display: block;
        }

        .desktop-only {
            display: none !important;
        }

        #pdf-viewer-container {
            height: calc(100vh - 52px);
        }
    }

    @media (min-width: 768px) {
        #mobile-fab {
            display: none !important;
        }

        .mobile-only {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

{{-- ════════════ FULLSCREEN TOOLBAR ════════════ --}}
<div id="pdf-fullscreen-toolbar">
    {{-- Title (truncate) --}}
    <span class="flex-1 hidden min-w-0 text-xs font-bold text-white truncate sm:block">
        {{ Str::limit($publication->title, 40) }}
    </span>

    {{-- Page Nav --}}
    <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1 flex-shrink-0">
        <button id="fs-prev-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="px-1 text-xs font-semibold text-white">
            <span id="fs-page-num">1</span>/<span id="fs-page-count">-</span>
        </span>
        <button id="fs-next-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Zoom --}}
    <div class="flex items-center flex-shrink-0 gap-1">
        <button id="fs-zoom-out" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
            </svg>
        </button>
        <span id="fs-zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
        <button id="fs-zoom-in" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
            </svg>
        </button>
    </div>

    {{-- Bookmark (desktop only in FS) --}}
    <button id="fs-bookmark-btn" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white desktop-only flex-shrink-0"
        title="Tandai (B)">
        <svg id="fs-bookmark-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
    </button>

    {{-- Exit --}}
    <button id="exit-fullscreen-btn"
        class="pdf-control-btn flex items-center gap-1.5 px-2.5 py-1.5 bg-red-600 hover:!bg-red-700 text-white text-xs font-bold flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span class="hidden sm:inline">Keluar</span>
    </button>

    {{-- Progress inside FS toolbar --}}
    <div class="absolute bottom-0 left-0 right-0 reading-progress-track">
        <div id="fs-reading-progress-bar" class="reading-progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ════════════ NORMAL TOOLBAR ════════════ --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-2 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-2">
        <div class="flex items-center gap-2">

            {{-- Back --}}
            <a href="{{ route('publikasi.show', $publication->slug) }}"
                class="pdf-control-btn p-2 bg-[#3D3D3D] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Kembali</span>
            </a>

            {{-- Title + progress text --}}
            <div class="flex-1 hidden min-w-0 sm:block">
                <p class="text-xs font-bold leading-tight text-white truncate">{{ $publication->title }}</p>
                <p id="progress-text" class="text-gray-400 text-[10px] leading-tight mt-0.5"></p>
            </div>

            {{-- Page Nav --}}
            <div class="flex items-center gap-1.5 bg-[#3D3D3D] rounded-lg px-2 py-1.5 flex-shrink-0">
                <button id="prev-page" class="pdf-control-btn p-1 bg-[#4D4D4D] text-white">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="flex items-center gap-1 text-white">
                    <input type="number" id="page-num-input"
                        class="page-input w-9 sm:w-11 text-center px-0.5 py-0.5 font-semibold text-xs" value="1"
                        min="1">
                    <span class="text-xs text-gray-400">/</span>
                    <span id="page-count" class="text-xs font-semibold">-</span>
                </div>
                <button id="next-page" class="pdf-control-btn p-1 bg-[#4D4D4D] text-white">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Zoom (desktop only) --}}
            <div class="flex items-center flex-shrink-0 gap-1 desktop-only">
                <button id="zoom-out" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span id="zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
                <button id="zoom-in" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>
            </div>

            {{-- Bookmark (desktop) --}}
            <button id="bookmark-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white desktop-only flex-shrink-0"
                title="Tandai halaman (B)">
                <svg id="bookmark-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </button>

            {{-- Reading Mode (desktop) --}}
            <div class="relative flex-shrink-0 desktop-only">
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
            <button id="fullscreen-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white flex-shrink-0 desktop-only"
                title="Layar Penuh (F)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>

            {{-- Download --}}
            <a href="{{ route('publikasi.download', $publication->slug) }}"
                class="pdf-control-btn p-2 sm:px-3 bg-[#FF6B18] hover:!bg-[#E64627] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Download</span>
            </a>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="reading-progress-track">
        <div id="reading-progress-bar" class="reading-progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ════════════ PDF VIEWER ════════════ --}}
<div id="pdf-viewer-container">

    {{-- Loading --}}
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-400">Harap tunggu sebentar</p>
    </div>

    {{-- Canvas --}}
    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>

    {{-- Fallback --}}
    <iframe id="pdf-iframe" title="PDF Viewer"></iframe>

    {{-- Desktop hint (fullscreen) --}}
    <div id="desktop-shortcut-hint">
        ← → halaman &nbsp;·&nbsp; +/− zoom &nbsp;·&nbsp; B tandai &nbsp;·&nbsp; Esc keluar
    </div>

    {{-- Mobile fullscreen hint overlay --}}
    <div id="mobile-fs-hint">
        <p class="mb-1 text-base font-bold text-white">Cara Navigasi</p>
        <div class="grid w-full max-w-xs grid-cols-3 gap-3">
            <div class="hint-item">
                <div class="hint-icon-wrap">👆</div>
                <strong>Tap</strong>
                <p>Tampilkan toolbar</p>
            </div>
            <div class="hint-item">
                <div class="hint-icon-wrap">👉</div>
                <strong>Swipe</strong>
                <p>Ganti halaman</p>
            </div>
            <div class="hint-item">
                <div class="hint-icon-wrap">🤏</div>
                <strong>Pinch</strong>
                <p>Zoom in/out</p>
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-400">Gunakan tombol <span class="text-[#FF6B18] font-bold">⊕</span> untuk fitur
            lainnya</p>
        <button id="close-fs-hint" class="mt-4 px-6 py-2 bg-[#FF6B18] text-white text-sm font-bold rounded-lg">
            Mengerti!
        </button>
    </div>
</div>

{{-- ════════════ MOBILE FAB ════════════ --}}
<div id="mobile-fab">
    <div id="mobile-fab-menu">
        {{-- Bookmark --}}
        <button class="fab-item" id="fab-bookmark">
            <div class="fab-icon">
                <svg id="fab-bookmark-icon" class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </div>
            <span id="fab-bookmark-label">Tandai Halaman</span>
        </button>
        {{-- Mode --}}
        <button class="fab-item" id="fab-mode">
            <div class="fab-icon">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
            </div>
            <span id="fab-mode-label">Mode: Normal</span>
        </button>
        {{-- Fullscreen --}}
        <button class="fab-item" id="fab-fullscreen">
            <div class="fab-icon">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </div>
            <span>Layar Penuh</span>
        </button>
        {{-- Zoom In --}}
        <button class="fab-item" id="fab-zoom-in">
            <div class="fab-icon">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                </svg>
            </div>
            <span id="fab-zoom-label">Zoom: 100%</span>
        </button>
        {{-- Zoom Out --}}
        <button class="fab-item" id="fab-zoom-out">
            <div class="fab-icon">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                </svg>
            </div>
            <span>Zoom Out</span>
        </button>
    </div>

    {{-- FAB trigger --}}
    <button id="mobile-fab-btn" aria-label="Menu">
        <svg id="fab-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
        <svg id="fab-icon-close" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

{{-- ════════════ RESUME TOAST ════════════ --}}
<div id="resume-toast">
    <span class="flex-shrink-0 text-xl">🔖</span>
    <div class="min-w-0">
        <p class="text-xs font-bold">Lanjut membaca?</p>
        <p class="text-gray-400 text-[11px]" id="resume-toast-text">Terakhir di halaman —</p>
    </div>
    <button id="resume-yes"
        class="px-3 py-1.5 bg-[#FF6B18] text-white text-xs font-bold rounded-lg flex-shrink-0">Lanjut</button>
    <button id="resume-no"
        class="px-2.5 py-1.5 bg-[#3D3D3D] text-gray-300 text-xs rounded-lg flex-shrink-0">Awal</button>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
pdfjsLib.verbosity = 0;

// ── Config ──────────────────────────────────────────────────────────
const pdfUrl  = @json($pdfUrl);
const pubSlug = @json($publication->slug);
const SK = {
    page : `bhaya_page_${pubSlug}`,
    zoom : `bhaya_zoom_${pubSlug}`,
    mode : `bhaya_mode_${pubSlug}`,
    bkmk : `bhaya_bkmk_${pubSlug}`,
    hint : `bhaya_fs_hint_shown`,
};

// ── State ───────────────────────────────────────────────────────────
let pdfDoc         = null;
let pageNum        = 1;
let pageRendering  = false;
let pageNumPending = null;
let baseScale      = 1.0;
let zoomFactor     = parseFloat(localStorage.getItem(SK.zoom)) || 1.0;
let isFullscreen   = false;
let currentMode    = localStorage.getItem(SK.mode) || 'normal';
let bookmarkedPage = parseInt(localStorage.getItem(SK.bkmk)) || null;
let savedPage      = parseInt(localStorage.getItem(SK.page)) || 1;
let fabOpen        = false;
let toolbarTimer   = null;
const isMobile     = () => window.innerWidth < 768;

// ── DOM ─────────────────────────────────────────────────────────────
const canvas       = document.getElementById('pdf-canvas');
const ctx          = canvas.getContext('2d');
const loadingEl    = document.getElementById('pdf-loading');
const canvasWrap   = document.getElementById('pdf-canvas-wrapper');
const viewerEl     = document.getElementById('pdf-viewer-container');
const iframeEl     = document.getElementById('pdf-iframe');
const progressBar  = document.getElementById('reading-progress-bar');
const fsProgressBar= document.getElementById('fs-reading-progress-bar');
const progressText = document.getElementById('progress-text');

// ── Helpers ─────────────────────────────────────────────────────────
const hideLoading = () => { loadingEl.style.display = 'none'; };
const showCanvas  = () => { canvasWrap.style.display = 'flex'; canvasWrap.classList.remove('hidden'); };

function showSnack(msg) {
    const el = document.createElement('div');
    el.textContent = msg;
    el.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid #FF6B18;color:#fff;padding:0.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity 0.4s;pointer-events:none;white-space:nowrap;';
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 2200);
}

// ── Progress ─────────────────────────────────────────────────────────
function updateProgress() {
    if (!pdfDoc) return;
    const pct = (pageNum / pdfDoc.numPages) * 100;
    [progressBar, fsProgressBar].forEach(b => b.style.width = pct + '%');
    const est = Math.ceil((pdfDoc.numPages - pageNum) * 1.5);
    progressText.textContent = `Hal. ${pageNum}/${pdfDoc.numPages} · ${Math.round(pct)}%` + (est > 0 ? ` · ~${est} mnt` : '');
}

// ── Bookmark ─────────────────────────────────────────────────────────
function updateBookmarkUI() {
    const on = bookmarkedPage === pageNum;
    // Desktop icons
    ['bookmark-icon','fs-bookmark-icon'].forEach(id => {
        const ic = document.getElementById(id);
        if (!ic) return;
        ic.setAttribute('fill', on ? '#FF6B18' : 'none');
        ic.setAttribute('stroke', on ? '#FF6B18' : 'currentColor');
    });
    ['bookmark-btn','fs-bookmark-btn'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.classList.toggle('is-bookmarked', on);
    });
    // FAB
    const fabBkmk = document.getElementById('fab-bookmark');
    const fabIcon = document.getElementById('fab-bookmark-icon');
    const fabLbl  = document.getElementById('fab-bookmark-label');
    if (fabBkmk) fabBkmk.classList.toggle('active-bkmk', on);
    if (fabIcon) { fabIcon.setAttribute('fill', on ? '#fff' : 'none'); }
    if (fabLbl)  fabLbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';
}

function toggleBookmark() {
    if (bookmarkedPage === pageNum) {
        bookmarkedPage = null;
        localStorage.removeItem(SK.bkmk);
        showSnack('Bookmark dihapus');
    } else {
        bookmarkedPage = pageNum;
        localStorage.setItem(SK.bkmk, pageNum);
        showSnack('🔖 Halaman ' + pageNum + ' ditandai!');
    }
    updateBookmarkUI();
}

// ── Reading Mode ──────────────────────────────────────────────────────
const modeLabels = { normal:'☀️ Normal', sepia:'📜 Sepia', night:'🌙 Night' };

function applyMode(mode) {
    document.body.classList.remove('read-mode-sepia','read-mode-night');
    if (mode !== 'normal') document.body.classList.add('read-mode-' + mode);
    currentMode = mode;
    localStorage.setItem(SK.mode, mode);
    document.querySelectorAll('.mode-option').forEach(el =>
        el.classList.toggle('active', el.dataset.mode === mode));
    const fabModeLbl = document.getElementById('fab-mode-label');
    if (fabModeLbl) fabModeLbl.textContent = 'Mode: ' + (mode === 'normal' ? 'Normal' : mode === 'sepia' ? 'Sepia' : 'Night');
}
applyMode(currentMode);

// ── Scale ────────────────────────────────────────────────────────────
const getScale = () => baseScale * zoomFactor;

function computeBase(page) {
    const w  = viewerEl.clientWidth || window.innerWidth;
    const vp = page.getViewport({ scale: 1 });
    baseScale = Math.max(0.5, Math.min((w - 16) / vp.width, 2.5));
}

// ── Render ────────────────────────────────────────────────────────────
function renderPage(num) {
    pageRendering = true;
    hideLoading(); showCanvas();

    pdfDoc.getPage(num).then(page => {
        if (baseScale === 1.0) computeBase(page);
        const vp = page.getViewport({ scale: getScale() });
        canvas.height = vp.height;
        canvas.width  = vp.width;

        page.render({ canvasContext: ctx, viewport: vp }).promise
            .then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    const p = pageNumPending; pageNumPending = null; renderPage(p);
                }
            })
            .catch(e => { console.warn('Render warn:', e.message); pageRendering = false; });

        localStorage.setItem(SK.page, num);
        localStorage.setItem(SK.zoom, zoomFactor);

        document.getElementById('page-num-input').value    = num;
        document.getElementById('fs-page-num').textContent = num;
        updateNavButtons(); updateZoomDisplay(); updateProgress(); updateBookmarkUI();
        canvasWrap.scrollTo({ top: 0, behavior: 'smooth' });

    }).catch(e => { console.error('getPage:', e.message); pageRendering = false; hideLoading(); showCanvas(); });
}

function queueRender(num) {
    if (pageRendering) pageNumPending = num; else renderPage(num);
}

// ── Navigation ────────────────────────────────────────────────────────
function prevPage() { if (pageNum > 1)               { pageNum--; queueRender(pageNum); } }
function nextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; queueRender(pageNum); } }
function goToPage(n) { if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) { pageNum = n; queueRender(n); } }

function updateNavButtons() {
    ['prev-page','fs-prev-page'].forEach(id => { const e=document.getElementById(id); if(e) e.disabled=pageNum<=1; });
    ['next-page','fs-next-page'].forEach(id => { const e=document.getElementById(id); if(e) e.disabled=pageNum>=pdfDoc.numPages; });
}

// ── Zoom ──────────────────────────────────────────────────────────────
function zoomIn()  { zoomFactor = Math.min(zoomFactor + 0.25, 4.0);  queueRender(pageNum); }
function zoomOut() { zoomFactor = Math.max(zoomFactor - 0.25, 0.25); queueRender(pageNum); }

function updateZoomDisplay() {
    const pct = Math.round(getScale() * 100) + '%';
    ['zoom-level','fs-zoom-level'].forEach(id => { const e=document.getElementById(id); if(e) e.textContent=pct; });
    const fabZoom = document.getElementById('fab-zoom-label');
    if (fabZoom) fabZoom.textContent = 'Zoom: ' + pct;
}

// ── FAB (Mobile) ──────────────────────────────────────────────────────
function toggleFab(force) {
    fabOpen = force !== undefined ? force : !fabOpen;
    document.getElementById('mobile-fab-menu').classList.toggle('open', fabOpen);
    document.getElementById('fab-icon-open').classList.toggle('hidden', fabOpen);
    document.getElementById('fab-icon-close').classList.toggle('hidden', !fabOpen);
}

document.getElementById('mobile-fab-btn').addEventListener('click', e => { e.stopPropagation(); toggleFab(); });
document.getElementById('fab-bookmark').addEventListener('click', () => { toggleBookmark(); toggleFab(false); });
document.getElementById('fab-fullscreen').addEventListener('click', () => { enterFullscreen(); toggleFab(false); });
document.getElementById('fab-zoom-in').addEventListener('click', () => { zoomIn(); toggleFab(false); });
document.getElementById('fab-zoom-out').addEventListener('click', () => { zoomOut(); toggleFab(false); });
document.getElementById('fab-mode').addEventListener('click', () => {
    const modes = ['normal','sepia','night'];
    const next = modes[(modes.indexOf(currentMode) + 1) % modes.length];
    applyMode(next);
    showSnack(modeLabels[next]);
    toggleFab(false);
});
document.addEventListener('click', () => { if (fabOpen) toggleFab(false); });

// ── Fullscreen ────────────────────────────────────────────────────────
const fsHintShown = localStorage.getItem(SK.hint);
const fsTbEl      = document.getElementById('pdf-fullscreen-toolbar');
const deskHint    = document.getElementById('desktop-shortcut-hint');
const mobHint     = document.getElementById('mobile-fs-hint');

function enterFullscreen() {
    isFullscreen = true;
    viewerEl.classList.add('fullscreen-mode');
    document.body.style.overflow = 'hidden';

    if (isMobile() && !fsHintShown) {
        mobHint.classList.add('show');
        localStorage.setItem(SK.hint, '1');
    } else if (!isMobile()) {
        deskHint.classList.remove('hidden','fade-out');
        clearTimeout(toolbarTimer);
        toolbarTimer = setTimeout(() => deskHint.classList.add('fade-out'), 4000);
    }

    if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale=1.0; computeBase(p); queueRender(pageNum); });
}

function exitFullscreen() {
    isFullscreen = false;
    viewerEl.classList.remove('fullscreen-mode');
    document.body.style.overflow = '';
    mobHint.classList.remove('show');
    deskHint.classList.add('hidden');
    if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale=1.0; computeBase(p); queueRender(pageNum); });
}

// Auto-hide FS toolbar on idle (desktop)
viewerEl.addEventListener('mousemove', () => {
    if (!isFullscreen || isMobile()) return;
    fsTbEl.classList.remove('toolbar-hidden');
    clearTimeout(toolbarTimer);
    toolbarTimer = setTimeout(() => fsTbEl.classList.add('toolbar-hidden'), 3000);
});

// Tap to show/hide FS toolbar (mobile)
viewerEl.addEventListener('click', e => {
    if (!isFullscreen || !isMobile()) return;
    if (fabOpen) return;
    fsTbEl.classList.toggle('toolbar-hidden');
});

document.getElementById('close-fs-hint').addEventListener('click', () => mobHint.classList.remove('show'));

// ── Iframe fallback ──────────────────────────────────────────────────
function showFallback() {
    hideLoading();
    canvasWrap.style.display = 'none';
    iframeEl.style.display   = 'block';
    iframeEl.src             = pdfUrl;
}

// ── Resume toast ─────────────────────────────────────────────────────
function showResumeToast(page) {
    const toast = document.getElementById('resume-toast');
    document.getElementById('resume-toast-text').textContent = `Terakhir di halaman ${page}`;
    toast.classList.add('show');
    document.getElementById('resume-yes').onclick = () => { goToPage(page); toast.classList.remove('show'); };
    document.getElementById('resume-no').onclick  = () => { goToPage(1);    toast.classList.remove('show'); };
    setTimeout(() => toast.classList.remove('show'), 7000);
}

// ── Load PDF ──────────────────────────────────────────────────────────
const fbTimer = setTimeout(() => { if (!pdfDoc) showFallback(); }, 8000);

pdfjsLib.getDocument({ url: pdfUrl, withCredentials: false, verbosity: 0 })
    .promise.then(doc => {
        clearTimeout(fbTimer);
        pdfDoc = doc;
        const total = doc.numPages;
        document.getElementById('page-count').textContent    = total;
        document.getElementById('fs-page-count').textContent = total;
        document.getElementById('page-num-input').max        = total;

        renderPage(1);
        if (savedPage > 1 && savedPage <= total)
            setTimeout(() => showResumeToast(savedPage), 900);
    })
    .catch(err => { clearTimeout(fbTimer); console.error(err.message); showFallback(); });

// ── Resize ────────────────────────────────────────────────────────────
let lastW = viewerEl.clientWidth, rTimer = null;
window.addEventListener('resize', () => {
    const w = viewerEl.clientWidth;
    if (Math.abs(w - lastW) < 20) return;
    lastW = w;
    clearTimeout(rTimer);
    rTimer = setTimeout(() => {
        if (!pdfDoc) return;
        pdfDoc.getPage(pageNum).then(p => { baseScale=1.0; computeBase(p); queueRender(pageNum); });
    }, 250);
});

// ── Events ───────────────────────────────────────────────────────────
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

document.getElementById('mode-btn').addEventListener('click', e => {
    e.stopPropagation();
    document.getElementById('mode-dropdown').classList.toggle('open');
});
document.querySelectorAll('.mode-option').forEach(el => {
    el.addEventListener('click', () => { applyMode(el.dataset.mode); document.getElementById('mode-dropdown').classList.remove('open'); });
});
document.addEventListener('click', () => document.getElementById('mode-dropdown').classList.remove('open'));

document.getElementById('page-num-input').addEventListener('change', function() {
    const n = parseInt(this.value);
    if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) goToPage(n); else this.value = pageNum;
});

// ── Keyboard ─────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
    switch(e.key) {
        case 'ArrowLeft': case 'ArrowUp':    prevPage(); break;
        case 'ArrowRight': case 'ArrowDown': nextPage(); break;
        case '+': case '=':                  zoomIn();   break;
        case '-':                            zoomOut();  break;
        case 'b': case 'B':                  toggleBookmark(); break;
        case 'f': case 'F':                  isFullscreen ? exitFullscreen() : enterFullscreen(); break;
        case 'Escape':                       if (isFullscreen) exitFullscreen(); break;
    }
});

// ── Touch: Swipe + Pinch ──────────────────────────────────────────────
let tx = 0, ty = 0, pinchDist = 0;
viewerEl.addEventListener('touchstart', e => {
    if (e.touches.length === 1) { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }
    if (e.touches.length === 2) {
        pinchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    }
}, { passive: true });

viewerEl.addEventListener('touchmove', e => {
    if (e.touches.length !== 2) return;
    const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    if (Math.abs(d - pinchDist) > 12) { d > pinchDist ? zoomIn() : zoomOut(); pinchDist = d; }
}, { passive: true });

viewerEl.addEventListener('touchend', e => {
    const dx = tx - e.changedTouches[0].clientX;
    const dy = ty - e.changedTouches[0].clientY;
    if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 55) { dx > 0 ? nextPage() : prevPage(); }
}, { passive: true });
</script>
@endpush