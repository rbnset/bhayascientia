@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* ═══════════════════════════════════════
   BASE
═══════════════════════════════════════ */
    #pdf-viewer-container {
        height: calc(100vh - 56px);
        background: #2D2D2D;
        position: relative;
        overflow: hidden;
        transition: background 0.3s ease;
    }

    /* ═══════════════════════════════════════
   READING MODES
═══════════════════════════════════════ */
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

    /* ═══════════════════════════════════════
   PROGRESS BAR
═══════════════════════════════════════ */
    .progress-track {
        height: 3px;
        background: #3D3D3D;
    }

    .progress-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.4s ease;
        border-radius: 0 2px 2px 0;
    }

    /* ═══════════════════════════════════════
   LOADING
═══════════════════════════════════════ */
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

    /* ═══════════════════════════════════════
   CANVAS
═══════════════════════════════════════ */
    #pdf-canvas-wrapper {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        padding: 0.5rem;
        -webkit-overflow-scrolling: touch;
    }

    #pdf-canvas-wrapper.hidden {
        display: none !important;
    }

    #pdf-canvas {
        max-width: 100%;
        display: block;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.6);
        transition: filter 0.3s ease;
    }

    /* ═══════════════════════════════════════
   IFRAME FALLBACK
═══════════════════════════════════════ */
    #pdf-iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: none;
        display: none;
        z-index: 4;
    }

    /* ═══════════════════════════════════════
   FULLSCREEN CONTAINER
═══════════════════════════════════════ */
    #pdf-viewer-container.fullscreen-mode {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-canvas-wrapper {
        top: 52px;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-loading {
        top: 52px;
    }

    /* ═══════════════════════════════════════
   FULLSCREEN TOOLBAR
═══════════════════════════════════════ */
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
        transition: opacity 0.3s, transform 0.3s;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-fullscreen-toolbar {
        display: flex !important;
    }

    #pdf-fullscreen-toolbar.toolbar-hidden {
        opacity: 0;
        transform: translateY(-100%);
        pointer-events: none;
    }

    /* ═══════════════════════════════════════
   HINT OVERLAY (fullscreen)
   Selalu tampil setiap masuk fullscreen
═══════════════════════════════════════ */
    #fs-hint-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.82);
        z-index: 10002;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.25rem;
        padding: 1.5rem;
        text-align: center;
    }

    #fs-hint-overlay.show {
        display: flex;
    }

    .hint-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        width: 100%;
        max-width: 360px;
    }

    .hint-card {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 107, 24, 0.3);
        border-radius: 12px;
        padding: 0.75rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
    }

    .hint-card .hi {
        font-size: 1.75rem;
        line-height: 1;
    }

    .hint-card strong {
        font-size: 12px;
        color: white;
    }

    .hint-card p {
        font-size: 11px;
        color: #aaa;
        margin: 0;
        line-height: 1.3;
    }

    /* Second row — wider cards */
    .hint-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        width: 100%;
        max-width: 360px;
    }

    /* ═══════════════════════════════════════
   MOBILE BOTTOM SHEET
   Muncul saat tap canvas di fullscreen
═══════════════════════════════════════ */
    #mobile-sheet-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10003;
        display: none;
        opacity: 0;
        transition: opacity 0.25s;
    }

    #mobile-sheet-backdrop.show {
        display: block;
        opacity: 1;
    }

    #mobile-bottom-sheet {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #1A1A1A;
        border-top: 2px solid #FF6B18;
        border-radius: 20px 20px 0 0;
        z-index: 10004;
        padding: 0 1rem 1.5rem;
        transform: translateY(100%);
        transition: transform 0.35s cubic-bezier(0.34, 1.2, 0.64, 1);
        max-height: 90vh;
        overflow-y: auto;
    }

    #mobile-bottom-sheet.show {
        transform: translateY(0);
    }

    .sheet-handle {
        width: 40px;
        height: 4px;
        background: #3D3D3D;
        border-radius: 99px;
        margin: 0.75rem auto 1rem;
    }

    .sheet-title {
        font-size: 13px;
        font-weight: 700;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.6rem;
    }

    /* Sheet sections */
    .sheet-section {
        margin-bottom: 1.25rem;
    }

    /* Page control inside sheet */
    .sheet-page-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: center;
    }

    .sheet-big-btn {
        width: 48px;
        height: 48px;
        background: #2D2D2D;
        border-radius: 12px;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .sheet-big-btn:active {
        background: #FF6B18;
    }

    .sheet-page-display {
        flex: 1;
        text-align: center;
        background: #2D2D2D;
        border-radius: 10px;
        padding: 0.6rem 0.5rem;
    }

    .sheet-page-display span {
        font-size: 18px;
        font-weight: 700;
        color: white;
    }

    .sheet-page-display small {
        display: block;
        font-size: 11px;
        color: #aaa;
    }

    /* Mode selector inside sheet */
    .mode-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .mode-card {
        background: #2D2D2D;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 0.65rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .mode-card:active {
        transform: scale(0.96);
    }

    .mode-card.active {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, 0.12);
    }

    .mode-card .mc-icon {
        font-size: 1.4rem;
    }

    .mode-card span {
        font-size: 12px;
        font-weight: 600;
        color: #ccc;
    }

    .mode-card.active span {
        color: #FF6B18;
    }

    /* Action rows */
    .sheet-action-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .sheet-action-btn {
        background: #2D2D2D;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        color: white;
        cursor: pointer;
        transition: background 0.15s;
    }

    .sheet-action-btn:active {
        background: #3D3D3D;
    }

    .sheet-action-btn svg {
        width: 22px;
        height: 22px;
    }

    .sheet-action-btn span {
        font-size: 12px;
        font-weight: 600;
    }

    .sheet-action-btn.danger {
        border: 1px solid rgba(239, 68, 68, 0.4);
    }

    .sheet-action-btn.danger:active {
        background: rgba(239, 68, 68, 0.15);
    }

    .sheet-action-btn.bookmarked {
        background: rgba(255, 107, 24, 0.2);
        border: 1px solid #FF6B18;
    }

    .sheet-action-btn.bookmarked span {
        color: #FF6B18;
    }

    /* Zoom row */
    .zoom-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .zoom-row-btn {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        background: #2D2D2D;
        border-radius: 10px;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .zoom-row-btn:active {
        background: #FF6B18;
    }

    .zoom-bar-wrap {
        flex: 1;
        height: 6px;
        background: #3D3D3D;
        border-radius: 99px;
        overflow: hidden;
    }

    .zoom-bar-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.3s;
        border-radius: 99px;
    }

    .zoom-val {
        min-width: 44px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: white;
    }

    /* ═══════════════════════════════════════
   MOBILE FAB (non-fullscreen)
═══════════════════════════════════════ */
    #mobile-fab {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 1000;
        display: none;
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
        border: none;
        color: white;
        transition: transform 0.2s;
    }

    #mobile-fab-btn:active {
        transform: scale(0.9);
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

    .fab-item .fab-icon {
        width: 32px;
        height: 32px;
        background: #2D2D2D;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fab-item.bkmk-on .fab-icon {
        background: #FF6B18;
    }

    /* ═══════════════════════════════════════
   RESUME TOAST
═══════════════════════════════════════ */
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
        white-space: nowrap;
        max-width: calc(100vw - 2rem);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #resume-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    /* ═══════════════════════════════════════
   TOOLBAR (normal)
═══════════════════════════════════════ */
    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        transition: background 0.3s;
    }

    .pdf-control-btn {
        transition: all 0.2s;
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

    /* Mode dropdown (desktop) */
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

    .mode-opt {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.875rem;
        cursor: pointer;
        font-size: 13px;
        color: #ccc;
        transition: background 0.15s;
    }

    .mode-opt:hover {
        background: #2D2D2D;
        color: white;
    }

    .mode-opt.active {
        color: #FF6B18;
        font-weight: 700;
    }

    /* Spinner */
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

    /* Desktop hint */
    #desktop-hint {
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
        transition: opacity 0.5s;
    }

    #desktop-hint.fade-out {
        opacity: 0;
    }

    /* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
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

{{-- ══════════ FULLSCREEN TOOLBAR ══════════ --}}
<div id="pdf-fullscreen-toolbar">
    <span class="flex-1 hidden min-w-0 text-xs font-bold text-white truncate sm:block">
        {{ Str::limit($publication->title, 38) }}
    </span>

    <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1 flex-shrink-0">
        <button id="fs-prev-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="px-1 text-xs font-semibold text-white whitespace-nowrap">
            <span id="fs-page-num">1</span>/<span id="fs-page-count">-</span>
        </span>
        <button id="fs-next-page" class="pdf-control-btn p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <div class="flex items-center flex-shrink-0 gap-1 desktop-only">
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

    <button id="fs-bookmark-btn" class="pdf-control-btn p-1.5 bg-[#3D3D3D] text-white desktop-only flex-shrink-0">
        <svg id="fs-bkmk-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
    </button>

    {{-- Mobile: tap hint button --}}
    <button id="fs-mobile-menu-btn" class="mobile-only pdf-control-btn p-1.5 bg-[#3D3D3D] text-white flex-shrink-0"
        title="Menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <button id="exit-fullscreen-btn"
        class="pdf-control-btn flex items-center gap-1.5 px-2.5 py-1.5 bg-red-600 hover:!bg-red-700 text-white text-xs font-bold flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span class="hidden sm:inline">Keluar</span>
    </button>

    <div class="absolute bottom-0 left-0 right-0 progress-track">
        <div id="fs-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════════ NORMAL TOOLBAR ══════════ --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-2 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-2">
        <div class="flex items-center gap-1.5 sm:gap-2">

            <a href="{{ route('publikasi.show', $publication->slug) }}"
                class="pdf-control-btn p-2 bg-[#3D3D3D] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Kembali</span>
            </a>

            <div class="flex-1 hidden min-w-0 sm:block">
                <p class="text-xs font-bold text-white truncate">{{ $publication->title }}</p>
                <p id="progress-text" class="text-gray-400 text-[10px] mt-0.5"></p>
            </div>

            <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1.5 flex-shrink-0">
                <button id="prev-page" class="pdf-control-btn p-1 bg-[#4D4D4D] text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Desktop controls --}}
            <div class="desktop-only flex items-center gap-1.5">
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

                <button id="bookmark-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white" title="Tandai (B)">
                    <svg id="bkmk-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </button>

                <div class="relative">
                    <button id="mode-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white" title="Mode Baca">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                    </button>
                    <div id="mode-dropdown">
                        <div class="mode-opt active" data-mode="normal">☀️ Normal</div>
                        <div class="mode-opt" data-mode="sepia">📜 Sepia</div>
                        <div class="mode-opt" data-mode="night">🌙 Night</div>
                    </div>
                </div>

                <button id="fullscreen-btn" class="pdf-control-btn p-2 bg-[#3D3D3D] text-white" title="Layar Penuh (F)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>
            </div>

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
    <div class="progress-track">
        <div id="reading-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════════ PDF VIEWER ══════════ --}}
<div id="pdf-viewer-container">
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-400">Harap tunggu sebentar</p>
    </div>

    <div id="pdf-canvas-wrapper" class="hidden">
        <canvas id="pdf-canvas"></canvas>
    </div>

    <iframe id="pdf-iframe" title="PDF Viewer"></iframe>

    {{-- Desktop hint --}}
    <div id="desktop-hint" class="hidden">
        ← → halaman &nbsp;·&nbsp; +/− zoom &nbsp;·&nbsp; B tandai &nbsp;·&nbsp; Esc keluar
    </div>

    {{-- ══ FULLSCREEN HINT OVERLAY (setiap masuk fullscreen) ══ --}}
    <div id="fs-hint-overlay">
        <p class="text-base font-bold text-white">Cara Navigasi Fullscreen</p>

        {{-- Mobile gestures --}}
        <div class="hint-grid mobile-only">
            <div class="hint-card">
                <div class="hi">👉</div><strong>Swipe</strong>
                <p>Ganti halaman</p>
            </div>
            <div class="hint-card">
                <div class="hi">🤏</div><strong>Pinch</strong>
                <p>Zoom in/out</p>
            </div>
            <div class="hint-card">
                <div class="hi">👆</div><strong>Tap</strong>
                <p>Tampilkan toolbar</p>
            </div>
        </div>
        <div class="hint-grid-2 mobile-only">
            <div class="hint-card">
                <div class="hi">☰</div><strong>Menu</strong>
                <p>Semua fitur: tandai, mode, zoom, keluar</p>
            </div>
            <div class="hint-card">
                <div class="hi">✕</div><strong>Keluar</strong>
                <p>Tombol merah di toolbar atas</p>
            </div>
        </div>

        {{-- Desktop shortcuts --}}
        <div class="hint-grid desktop-only">
            <div class="hint-card">
                <div class="hi">←→</div><strong>Halaman</strong>
                <p>Arrow keys</p>
            </div>
            <div class="hint-card">
                <div class="hi">+−</div><strong>Zoom</strong>
                <p>Tombol +/−</p>
            </div>
            <div class="hint-card">
                <div class="hi">B</div><strong>Tandai</strong>
                <p>Keyboard B</p>
            </div>
        </div>
        <div class="hint-grid-2 desktop-only">
            <div class="hint-card">
                <div class="hi">F</div><strong>Fullscreen</strong>
                <p>Toggle fullscreen</p>
            </div>
            <div class="hint-card">
                <div class="hi">Esc</div><strong>Keluar</strong>
                <p>Keluar fullscreen</p>
            </div>
        </div>

        <button id="close-hint-btn" class="mt-1 px-8 py-2.5 bg-[#FF6B18] text-white text-sm font-bold rounded-xl">
            Mengerti, Mulai Baca!
        </button>
    </div>
</div>

{{-- ══════════ MOBILE BOTTOM SHEET ══════════ --}}
<div id="mobile-sheet-backdrop"></div>
<div id="mobile-bottom-sheet">
    <div class="sheet-handle"></div>

    {{-- Title info --}}
    <p class="mb-3 text-sm font-bold text-white truncate">{{ Str::limit($publication->title, 45) }}</p>

    {{-- Halaman --}}
    <div class="sheet-section">
        <p class="sheet-title">Navigasi Halaman</p>
        <div class="sheet-page-row">
            <button class="sheet-big-btn" id="sheet-prev">
                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div class="sheet-page-display">
                <span id="sheet-page-num">1</span>
                <small>dari <span id="sheet-page-total">-</span> halaman</small>
            </div>
            <button class="sheet-big-btn" id="sheet-next">
                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
        <div class="flex items-center gap-2 mt-2">
            <span class="flex-shrink-0 text-xs text-gray-400">Lompat ke hal:</span>
            <input type="number" id="sheet-jump-input" class="flex-1 py-1 text-sm font-bold text-center page-input"
                placeholder="No. halaman" min="1">
            <button id="sheet-jump-go"
                class="px-3 py-1.5 bg-[#FF6B18] text-white text-xs font-bold rounded-lg flex-shrink-0">Go</button>
        </div>
    </div>

    {{-- Zoom --}}
    <div class="sheet-section">
        <p class="sheet-title">Zoom</p>
        <div class="zoom-row">
            <button class="zoom-row-btn" id="sheet-zoom-out">−</button>
            <div class="zoom-bar-wrap">
                <div id="sheet-zoom-bar" class="zoom-bar-fill" style="width:25%"></div>
            </div>
            <span id="sheet-zoom-val" class="zoom-val">100%</span>
            <button class="zoom-row-btn" id="sheet-zoom-in">+</button>
        </div>
    </div>

    {{-- Mode baca --}}
    <div class="sheet-section">
        <p class="sheet-title">Mode Baca</p>
        <div class="mode-row">
            <div class="mode-card active" data-sheet-mode="normal">
                <div class="mc-icon">☀️</div><span>Normal</span>
            </div>
            <div class="mode-card" data-sheet-mode="sepia">
                <div class="mc-icon">📜</div><span>Sepia</span>
            </div>
            <div class="mode-card" data-sheet-mode="night">
                <div class="mc-icon">🌙</div><span>Night</span>
            </div>
        </div>
    </div>

    {{-- Aksi --}}
    <div class="sheet-section">
        <p class="sheet-title">Aksi</p>
        <div class="sheet-action-row">
            <button id="sheet-bookmark-btn" class="sheet-action-btn">
                <svg id="sheet-bkmk-icon" class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span id="sheet-bkmk-label">Tandai Halaman</span>
            </button>
            <button id="sheet-fullscreen-btn" class="sheet-action-btn">
                <svg class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Layar Penuh</span>
            </button>
            <button id="sheet-exit-btn" class="sheet-action-btn danger" style="display:none">
                <svg class="text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-red-400">Keluar Fullscreen</span>
            </button>
        </div>
    </div>

    <button id="sheet-close-btn" class="w-full py-2.5 bg-[#2D2D2D] text-gray-400 text-sm font-semibold rounded-xl">
        Tutup
    </button>
</div>

{{-- ══════════ MOBILE FAB (non-fullscreen) ══════════ --}}
<div id="mobile-fab">
    <div id="mobile-fab-menu">
        <button class="fab-item" id="fab-sheet-open">
            <div class="fab-icon">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <span>Fitur & Pengaturan</span>
        </button>
    </div>
    <button id="mobile-fab-btn" aria-label="Menu">
        <svg id="fab-open-ic" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
        <svg id="fab-close-ic" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

{{-- ══════════ RESUME TOAST ══════════ --}}
<div id="resume-toast">
    <span class="flex-shrink-0 text-xl">🔖</span>
    <div class="min-w-0">
        <p class="text-xs font-bold">Lanjut membaca?</p>
        <p class="text-gray-400 text-[11px]" id="resume-text">Terakhir di halaman —</p>
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
const slug    = @json($publication->slug);
const SK = {
    page: `bp_${slug}`,
    zoom: `bz_${slug}`,
    mode: `bm_${slug}`,
    bkmk: `bb_${slug}`,
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
let sheetOpen      = false;
let fabMenuOpen    = false;
let toolbarTimer   = null;
const isMobile     = () => window.innerWidth < 768;

// ── DOM ─────────────────────────────────────────────────────────────
const canvas      = document.getElementById('pdf-canvas');
const ctx         = canvas.getContext('2d');
const loadingEl   = document.getElementById('pdf-loading');
const canvasWrap  = document.getElementById('pdf-canvas-wrapper');
const viewerEl    = document.getElementById('pdf-viewer-container');
const iframeEl    = document.getElementById('pdf-iframe');
const progBar     = document.getElementById('reading-progress-bar');
const fsProgBar   = document.getElementById('fs-progress-bar');
const progText    = document.getElementById('progress-text');
const fsTbEl      = document.getElementById('pdf-fullscreen-toolbar');
const hintOverlay = document.getElementById('fs-hint-overlay');
const deskHint    = document.getElementById('desktop-hint');
const backdrop    = document.getElementById('mobile-sheet-backdrop');
const sheet       = document.getElementById('mobile-bottom-sheet');

// ── Helpers ─────────────────────────────────────────────────────────
const hideLoading = () => loadingEl.style.display = 'none';
const showCanvas  = () => { canvasWrap.style.display = 'flex'; canvasWrap.classList.remove('hidden'); };

function snack(msg) {
    const el = Object.assign(document.createElement('div'), { textContent: msg });
    el.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid #FF6B18;color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;';
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = 0; setTimeout(() => el.remove(), 400); }, 2200);
}

// ── Progress ─────────────────────────────────────────────────────────
function updateProgress() {
    if (!pdfDoc) return;
    const pct = (pageNum / pdfDoc.numPages) * 100;
    [progBar, fsProgBar].forEach(b => b.style.width = pct + '%');
    const est = Math.ceil((pdfDoc.numPages - pageNum) * 1.5);
    progText.textContent = `Hal. ${pageNum}/${pdfDoc.numPages} · ${Math.round(pct)}%` + (est > 0 ? ` · ~${est} mnt` : '');
    // Update sheet
    document.getElementById('sheet-page-num').textContent = pageNum;
}

// ── Zoom bar ─────────────────────────────────────────────────────────
function updateZoomDisplay() {
    const pct = Math.round((zoomFactor / 4.0) * 100);
    const label = Math.round(zoomFactor * 100) + '%';
    ['zoom-level','fs-zoom-level'].forEach(id => { const e = document.getElementById(id); if(e) e.textContent = label; });
    document.getElementById('sheet-zoom-val').textContent = label;
    document.getElementById('sheet-zoom-bar').style.width = Math.max(5, pct) + '%';
}

// ── Bookmark ─────────────────────────────────────────────────────────
function updateBookmarkUI() {
    const on = bookmarkedPage === pageNum;
    // All bookmark icons
    [
        { icon: 'bkmk-icon',    btn: 'bookmark-btn' },
        { icon: 'fs-bkmk-icon', btn: 'fs-bookmark-btn' },
    ].forEach(({ icon, btn }) => {
        const ic = document.getElementById(icon);
        const bt = document.getElementById(btn);
        if (ic) { ic.setAttribute('fill', on ? '#FF6B18' : 'none'); ic.setAttribute('stroke', on ? '#FF6B18' : 'currentColor'); }
        if (bt) bt.classList.toggle('is-bookmarked', on);
    });
    // Sheet bookmark
    const sBtn  = document.getElementById('sheet-bookmark-btn');
    const sIcon = document.getElementById('sheet-bkmk-icon');
    const sLbl  = document.getElementById('sheet-bkmk-label');
    if (sBtn)  sBtn.classList.toggle('bookmarked', on);
    if (sIcon) { sIcon.setAttribute('fill', on ? '#FF6B18' : 'none'); sIcon.setAttribute('stroke', on ? '#FF6B18' : 'currentColor'); }
    if (sLbl)  sLbl.textContent = on ? '✓ Ditandai' : 'Tandai Halaman';
}

function toggleBookmark() {
    if (bookmarkedPage === pageNum) {
        bookmarkedPage = null; localStorage.removeItem(SK.bkmk);
        snack('Bookmark dihapus');
    } else {
        bookmarkedPage = pageNum; localStorage.setItem(SK.bkmk, pageNum);
        snack('🔖 Halaman ' + pageNum + ' ditandai!');
    }
    updateBookmarkUI();
}

// ── Reading Mode ──────────────────────────────────────────────────────
function applyMode(mode) {
    document.body.classList.remove('read-mode-sepia','read-mode-night');
    if (mode !== 'normal') document.body.classList.add('read-mode-' + mode);
    currentMode = mode;
    localStorage.setItem(SK.mode, mode);
    // Desktop dropdown
    document.querySelectorAll('.mode-opt').forEach(e => e.classList.toggle('active', e.dataset.mode === mode));
    // Sheet mode cards
    document.querySelectorAll('[data-sheet-mode]').forEach(e => e.classList.toggle('active', e.dataset.sheetMode === mode));
}
applyMode(currentMode);

// ── Scale ─────────────────────────────────────────────────────────────
const getScale = () => baseScale * zoomFactor;
function computeBase(page) {
    const w = viewerEl.clientWidth || window.innerWidth;
    baseScale = Math.max(0.5, Math.min((w - 16) / page.getViewport({ scale: 1 }).width, 2.5));
}

// ── Render ────────────────────────────────────────────────────────────
function renderPage(num) {
    pageRendering = true;
    hideLoading(); showCanvas();

    pdfDoc.getPage(num).then(page => {
        if (baseScale === 1.0) computeBase(page);
        const vp = page.getViewport({ scale: getScale() });
        canvas.height = vp.height; canvas.width = vp.width;

        page.render({ canvasContext: ctx, viewport: vp }).promise
            .then(() => {
                pageRendering = false;
                if (pageNumPending !== null) { const p = pageNumPending; pageNumPending = null; renderPage(p); }
            })
            .catch(e => { console.warn(e.message); pageRendering = false; });

        localStorage.setItem(SK.page, num);
        localStorage.setItem(SK.zoom, zoomFactor);
        document.getElementById('page-num-input').value    = num;
        document.getElementById('fs-page-num').textContent = num;
        updateNavButtons(); updateZoomDisplay(); updateProgress(); updateBookmarkUI();
        canvasWrap.scrollTo({ top: 0, behavior: 'smooth' });
    }).catch(e => { console.error(e.message); pageRendering = false; hideLoading(); showCanvas(); });
}

function queueRender(num) { if (pageRendering) pageNumPending = num; else renderPage(num); }

// ── Navigation ────────────────────────────────────────────────────────
function prevPage() { if (pageNum > 1)               { pageNum--; queueRender(pageNum); } }
function nextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; queueRender(pageNum); } }
function goTo(n)    { if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) { pageNum = n; queueRender(n); } }

function updateNavButtons() {
    ['prev-page','fs-prev-page'].forEach(id => { const e = document.getElementById(id); if(e) e.disabled = pageNum <= 1; });
    ['next-page','fs-next-page'].forEach(id => { const e = document.getElementById(id); if(e) e.disabled = pageNum >= pdfDoc.numPages; });
    ['sheet-prev'].forEach(id => { const e = document.getElementById(id); if(e) e.disabled = pageNum <= 1; });
    ['sheet-next'].forEach(id => { const e = document.getElementById(id); if(e) e.disabled = pageNum >= pdfDoc.numPages; });
}

// ── Zoom ──────────────────────────────────────────────────────────────
function zoomIn()  { zoomFactor = Math.min(zoomFactor + 0.25, 4.0);  queueRender(pageNum); }
function zoomOut() { zoomFactor = Math.max(zoomFactor - 0.25, 0.25); queueRender(pageNum); }

// ── Bottom Sheet ──────────────────────────────────────────────────────
function openSheet() {
    sheetOpen = true;
    // Show/hide exit button based on fullscreen
    document.getElementById('sheet-exit-btn').style.display    = isFullscreen ? 'flex' : 'none';
    document.getElementById('sheet-fullscreen-btn').style.display = isFullscreen ? 'none' : 'flex';
    backdrop.classList.add('show');
    sheet.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSheet() {
    sheetOpen = false;
    backdrop.classList.remove('show');
    sheet.classList.remove('show');
    if (!isFullscreen) document.body.style.overflow = '';
}

backdrop.addEventListener('click', closeSheet);
document.getElementById('sheet-close-btn').addEventListener('click', closeSheet);

document.getElementById('sheet-prev').addEventListener('click', () => { prevPage(); });
document.getElementById('sheet-next').addEventListener('click', () => { nextPage(); });

document.getElementById('sheet-jump-go').addEventListener('click', () => {
    const n = parseInt(document.getElementById('sheet-jump-input').value);
    if (n) { goTo(n); closeSheet(); }
});
document.getElementById('sheet-jump-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') { const n = parseInt(e.target.value); if(n) { goTo(n); closeSheet(); } }
});

document.getElementById('sheet-zoom-in').addEventListener('click', zoomIn);
document.getElementById('sheet-zoom-out').addEventListener('click', zoomOut);

document.querySelectorAll('[data-sheet-mode]').forEach(el => {
    el.addEventListener('click', () => { applyMode(el.dataset.sheetMode); snack({ normal:'☀️ Normal', sepia:'📜 Sepia', night:'🌙 Night' }[el.dataset.sheetMode]); });
});

document.getElementById('sheet-bookmark-btn').addEventListener('click', () => { toggleBookmark(); });
document.getElementById('sheet-fullscreen-btn').addEventListener('click', () => { closeSheet(); setTimeout(enterFullscreen, 200); });
document.getElementById('sheet-exit-btn').addEventListener('click', () => { closeSheet(); exitFullscreen(); });

// ── Mobile FAB ────────────────────────────────────────────────────────
document.getElementById('mobile-fab-btn').addEventListener('click', e => {
    e.stopPropagation();
    fabMenuOpen = !fabMenuOpen;
    document.getElementById('mobile-fab-menu').classList.toggle('open', fabMenuOpen);
    document.getElementById('fab-open-ic').classList.toggle('hidden', fabMenuOpen);
    document.getElementById('fab-close-ic').classList.toggle('hidden', !fabMenuOpen);
});
document.getElementById('fab-sheet-open').addEventListener('click', () => {
    fabMenuOpen = false;
    document.getElementById('mobile-fab-menu').classList.remove('open');
    document.getElementById('fab-open-ic').classList.remove('hidden');
    document.getElementById('fab-close-ic').classList.add('hidden');
    openSheet();
});
document.addEventListener('click', () => {
    if (fabMenuOpen) {
        fabMenuOpen = false;
        document.getElementById('mobile-fab-menu').classList.remove('open');
        document.getElementById('fab-open-ic').classList.remove('hidden');
        document.getElementById('fab-close-ic').classList.add('hidden');
    }
});

// ── Fullscreen ────────────────────────────────────────────────────────
function showFsHint() {
    hintOverlay.classList.add('show');
}

function enterFullscreen() {
    isFullscreen = true;
    viewerEl.classList.add('fullscreen-mode');
    document.body.style.overflow = 'hidden';
    showFsHint(); // ✅ SELALU tampilkan hint
    if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); });
}

function exitFullscreen() {
    isFullscreen = false;
    viewerEl.classList.remove('fullscreen-mode');
    document.body.style.overflow = '';
    hintOverlay.classList.remove('show');
    deskHint.classList.add('hidden');
    if (pdfDoc) pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); });
}

// Close hint → tampilkan desktop shortcut hint sebentar
document.getElementById('close-hint-btn').addEventListener('click', () => {
    hintOverlay.classList.remove('show');
    if (!isMobile()) {
        deskHint.classList.remove('hidden','fade-out');
        clearTimeout(toolbarTimer);
        toolbarTimer = setTimeout(() => deskHint.classList.add('fade-out'), 4000);
    }
});

// Fullscreen menu button (mobile)
document.getElementById('fs-mobile-menu-btn').addEventListener('click', () => openSheet());

// Auto-hide toolbar on desktop idle
viewerEl.addEventListener('mousemove', () => {
    if (!isFullscreen || isMobile()) return;
    fsTbEl.classList.remove('toolbar-hidden');
    clearTimeout(toolbarTimer);
    toolbarTimer = setTimeout(() => fsTbEl.classList.add('toolbar-hidden'), 3000);
});

// Tap canvas on mobile fullscreen → toggle toolbar
viewerEl.addEventListener('click', e => {
    if (!isFullscreen || !isMobile()) return;
    if (sheetOpen) return;
    if (e.target.closest('#pdf-fullscreen-toolbar, #mobile-bottom-sheet, #fs-hint-overlay')) return;
    fsTbEl.classList.toggle('toolbar-hidden');
});

// ── Iframe Fallback ───────────────────────────────────────────────────
function showFallback() {
    hideLoading();
    canvasWrap.style.display = 'none';
    iframeEl.style.display   = 'block';
    iframeEl.src             = pdfUrl;
}

// ── Resume Toast ──────────────────────────────────────────────────────
function showResumeToast(page) {
    const toast = document.getElementById('resume-toast');
    document.getElementById('resume-text').textContent = `Terakhir di halaman ${page}`;
    toast.classList.add('show');
    document.getElementById('resume-yes').onclick = () => { goTo(page); toast.classList.remove('show'); };
    document.getElementById('resume-no').onclick  = () => { goTo(1);    toast.classList.remove('show'); };
    setTimeout(() => toast.classList.remove('show'), 7000);
}

// ── Load PDF ──────────────────────────────────────────────────────────
const fbTimer = setTimeout(() => { if (!pdfDoc) showFallback(); }, 8000);

pdfjsLib.getDocument({ url: pdfUrl, withCredentials: false, verbosity: 0 })
    .promise.then(doc => {
        clearTimeout(fbTimer);
        pdfDoc = doc;
        const total = doc.numPages;
        ['page-count','fs-page-count','sheet-page-total'].forEach(id => {
            const e = document.getElementById(id); if (e) e.textContent = total;
        });
        document.getElementById('page-num-input').max = total;
        document.getElementById('sheet-jump-input').max = total;

        renderPage(1);
        if (savedPage > 1 && savedPage <= total) setTimeout(() => showResumeToast(savedPage), 900);
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
        pdfDoc.getPage(pageNum).then(p => { baseScale = 1.0; computeBase(p); queueRender(pageNum); });
    }, 250);
});

// ── Desktop Events ────────────────────────────────────────────────────
['prev-page','fs-prev-page'].forEach(id => document.getElementById(id)?.addEventListener('click', prevPage));
['next-page','fs-next-page'].forEach(id => document.getElementById(id)?.addEventListener('click', nextPage));
['zoom-in','fs-zoom-in'].forEach(id => document.getElementById(id)?.addEventListener('click', zoomIn));
['zoom-out','fs-zoom-out'].forEach(id => document.getElementById(id)?.addEventListener('click', zoomOut));
document.getElementById('bookmark-btn')?.addEventListener('click', toggleBookmark);
document.getElementById('fs-bookmark-btn')?.addEventListener('click', toggleBookmark);
document.getElementById('fullscreen-btn')?.addEventListener('click', enterFullscreen);
document.getElementById('exit-fullscreen-btn')?.addEventListener('click', exitFullscreen);

document.getElementById('mode-btn')?.addEventListener('click', e => {
    e.stopPropagation();
    document.getElementById('mode-dropdown').classList.toggle('open');
});
document.querySelectorAll('.mode-opt').forEach(el => {
    el.addEventListener('click', () => { applyMode(el.dataset.mode); document.getElementById('mode-dropdown').classList.remove('open'); });
});
document.addEventListener('click', () => document.getElementById('mode-dropdown')?.classList.remove('open'));

document.getElementById('page-num-input').addEventListener('change', function() {
    const n = parseInt(this.value);
    if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) goTo(n); else this.value = pageNum;
});

// ── Keyboard ─────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
    switch(e.key) {
        case 'ArrowLeft': case 'ArrowUp':    prevPage(); break;
        case 'ArrowRight': case 'ArrowDown': nextPage(); break;
        case '+': case '=': zoomIn();  break;
        case '-':           zoomOut(); break;
        case 'b': case 'B': toggleBookmark(); break;
        case 'f': case 'F': isFullscreen ? exitFullscreen() : enterFullscreen(); break;
        case 'Escape': if (isFullscreen) exitFullscreen(); break;
    }
});

// ── Touch: Swipe + Pinch ──────────────────────────────────────────────
let tx = 0, ty = 0, pd = 0;
viewerEl.addEventListener('touchstart', e => {
    if (e.touches.length === 1) { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }
    if (e.touches.length === 2) {
        pd = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    }
}, { passive: true });
viewerEl.addEventListener('touchmove', e => {
    if (e.touches.length !== 2) return;
    const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    if (Math.abs(d - pd) > 12) { d > pd ? zoomIn() : zoomOut(); pd = d; }
}, { passive: true });
viewerEl.addEventListener('touchend', e => {
    if (sheetOpen || hintOverlay.classList.contains('show')) return;
    const dx = tx - e.changedTouches[0].clientX;
    const dy = ty - e.changedTouches[0].clientY;
    if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 55) { dx > 0 ? nextPage() : prevPage(); }
}, { passive: true });
</script>
@endpush