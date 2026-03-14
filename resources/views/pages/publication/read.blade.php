{{--
resources/views/pages/publication/read.blade.php
─────────────────────────────────────────────────
CSS → public/css/pdf-viewer.css
JS → public/js/pdf-viewer.js + public/js/pdf-annotations.js
─────────────────────────────────────────────────
--}}
@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')
@section('hide_footer', 'true')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pdf-viewer.css') }}?v={{ filemtime(public_path('css/pdf-viewer.css')) }}">
<style>
    /* ══════════════════════════════════════════════
   ANNOTATION BOTTOM BAR — mobile-first, tidak
   menghalangi area baca PDF
══════════════════════════════════════════════ */
    #annot-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 18000;
        background: #111;
        border-top: 1px solid #2d2d2d;
        display: none;
        flex-direction: column;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        transition: transform .25s ease;
    }

    #annot-bottom-bar.visible {
        display: flex;
    }

    /* Handle row */
    .ab-handle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 12px 3px;
        cursor: pointer;
        user-select: none;
        gap: 8px;
    }

    .ab-handle-pip {
        width: 32px;
        height: 3px;
        background: #3d3d3d;
        border-radius: 99px;
        flex-shrink: 0;
    }

    .ab-handle-label {
        font-size: 10px;
        font-weight: 700;
        color: #ff6b18;
        letter-spacing: .05em;
        text-transform: uppercase;
        flex: 1;
        text-align: center;
    }

    .ab-collapse {
        width: 22px;
        height: 22px;
        background: #2d2d2d;
        border: none;
        border-radius: 6px;
        color: #777;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Collapsed pill */
    .ab-expand {
        display: none;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        padding-bottom: max(6px, env(safe-area-inset-bottom, 6px));
        cursor: pointer;
    }

    .ab-expand-pill {
        background: #1a1a1a;
        border: 1px solid #3d3d3d;
        border-radius: 99px;
        padding: 5px 16px;
        font-size: 11px;
        font-weight: 600;
        color: #888;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ab-expand-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #ff6b18;
    }

    /* Tools scrollable row */
    .ab-tools {
        display: flex;
        align-items: center;
        gap: 2px;
        padding: 4px 8px;
        padding-bottom: max(8px, env(safe-area-inset-bottom, 8px));
        overflow-x: auto;
        scrollbar-width: none;
    }

    .ab-tools::-webkit-scrollbar {
        display: none;
    }

    .ab-sep {
        width: 1px;
        height: 20px;
        background: #2d2d2d;
        margin: 0 2px;
        flex-shrink: 0;
    }

    /* Tool btn */
    .ab-tool {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1.5px solid transparent;
        background: transparent;
        color: #888;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
        transition: all .15s;
        position: relative;
    }

    .ab-tool:active {
        transform: scale(.88);
    }

    .ab-tool.active {
        background: rgba(255, 107, 24, .15);
        border-color: #ff6b18;
        color: #ff6b18;
    }

    .ab-tool[data-tool="eraser"].active {
        background: rgba(239, 68, 68, .15);
        border-color: #ef4444;
        color: #f87171;
    }

    /* badge */
    .ab-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #ff6b18;
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        min-width: 14px;
        height: 14px;
        border-radius: 99px;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
    }

    .ab-badge.show {
        display: flex;
    }

    /* Action (undo/redo) */
    .ab-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        background: #1f1f1f;
        color: #888;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all .15s;
    }

    .ab-action:active {
        transform: scale(.88);
    }

    .ab-action:disabled {
        opacity: .3;
        cursor: not-allowed;
    }

    /* Colors */
    .ab-colors {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }

    .ab-color {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform .15s, border-color .15s;
    }

    .ab-color:active {
        transform: scale(.8);
    }

    .ab-color.selected {
        border-color: #fff;
        transform: scale(1.15);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .25);
    }

    .ab-color[data-color="yellow"] {
        background: #ffd700;
    }

    .ab-color[data-color="green"] {
        background: #4ade80;
    }

    .ab-color[data-color="red"] {
        background: #ef4444;
    }

    .ab-color[data-color="blue"] {
        background: #60a5fa;
    }

    .ab-color[data-color="orange"] {
        background: #ff6b18;
    }

    .ab-color[data-color="black"] {
        background: #222;
        border-color: #555;
    }

    .ab-color[data-color="white"] {
        background: #fff;
        border-color: #555;
    }

    /* Sizes */
    .ab-sizes {
        display: none;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

    .ab-sizes.show {
        display: flex;
    }

    .ab-size {
        border-radius: 50%;
        background: #555;
        cursor: pointer;
        flex-shrink: 0;
        transition: background .15s, transform .15s;
    }

    .ab-size:active {
        transform: scale(.8);
    }

    .ab-size.selected {
        background: #ff6b18;
    }

    .ab-size[data-size="2"] {
        width: 6px;
        height: 6px;
    }

    .ab-size[data-size="4"] {
        width: 9px;
        height: 9px;
    }

    .ab-size[data-size="8"] {
        width: 13px;
        height: 13px;
    }

    .ab-size[data-size="14"] {
        width: 17px;
        height: 17px;
    }

    /* Shape sub-picker */
    .ab-shapes {
        display: none;
        align-items: center;
        gap: 2px;
        flex-shrink: 0;
    }

    .ab-shapes.show {
        display: flex;
    }

    .ab-shape {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1.5px solid transparent;
        background: #1f1f1f;
        color: #888;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        transition: all .15s;
    }

    .ab-shape.active {
        border-color: #ff6b18;
        color: #ff6b18;
        background: rgba(255, 107, 24, .12);
    }

    /* Desktop: bar floats centred above bottom */
    @media (min-width:768px) {
        #annot-bottom-bar {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            width: auto;
            min-width: 560px;
            max-width: 88vw;
            border-radius: 14px 14px 0 0;
            border: 1px solid #2d2d2d;
            border-bottom: none;
        }
    }

    /* Recovery overlay */
    #pdf-recovery-overlay {
        position: absolute;
        inset: 0;
        background: #1a1a1a;
        z-index: 20;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    #pdf-recovery-overlay.show {
        display: flex;
    }
</style>
@endpush

@section('content')

{{-- ══════ FULLSCREEN TOOLBAR ══════ --}}
<div id="pdf-fullscreen-toolbar">
    <span class="flex-1 hidden min-w-0 text-xs font-bold text-white truncate sm:block">{{
        Str::limit($publication->title,38) }}</span>
    <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1 flex-shrink-0">
        <button id="fs-prev" class="pcb p-1.5 bg-[#4D4D4D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg></button>
        <span class="px-1 text-xs font-semibold text-white whitespace-nowrap"><span id="fs-page-num">1</span>/<span
                id="fs-page-count">-</span></span>
        <button id="fs-next" class="pcb p-1.5 bg-[#4D4D4D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg></button>
    </div>
    <div class="flex items-center flex-shrink-0 gap-1 desktop-only">
        <button id="fs-zoom-out" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
            </svg></button>
        <span id="fs-zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
        <button id="fs-zoom-in" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
            </svg></button>
        <button id="fs-bookmark-btn" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg id="fs-bkmk-icon" class="w-4 h-4"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg></button>
    </div>
    <span class="mobile-only text-[11px] text-gray-400 flex-shrink-0">Tap layar = menu</span>
    <button id="exit-fs-btn"
        class="pcb flex items-center gap-1 px-2.5 py-1.5 bg-red-600 hover:!bg-red-700 text-white text-xs font-bold flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span class="hidden sm:inline">Keluar</span>
    </button>
    <div class="absolute bottom-0 left-0 right-0 progress-track">
        <div id="fs-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════ NORMAL TOOLBAR ══════ --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-2 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-2">
        <div class="flex items-center gap-1.5 sm:gap-2">
            <a href="{{ route('publikasi.show', $publication->slug) }}"
                class="pcb p-2 bg-[#3D3D3D] text-white flex items-center gap-1.5 flex-shrink-0">
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
                <button id="prev-page" class="pcb p-1 bg-[#4D4D4D] text-white"><svg class="w-3.5 h-3.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg></button>
                <div class="flex items-center gap-1">
                    <input type="number" id="page-num-input"
                        class="page-input w-9 sm:w-11 text-center px-0.5 py-0.5 font-semibold text-xs" value="1"
                        min="1">
                    <span class="text-xs text-gray-400">/</span>
                    <span id="page-count" class="text-xs font-semibold text-white">-</span>
                </div>
                <button id="next-page" class="pcb p-1 bg-[#4D4D4D] text-white"><svg class="w-3.5 h-3.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg></button>
            </div>
            <div class="desktop-only flex items-center gap-1.5">
                <button id="zoom-out" class="pcb p-2 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg></button>
                <span id="zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
                <button id="zoom-in" class="pcb p-2 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg></button>
                <button id="bookmark-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Tandai (B)"><svg id="bkmk-icon"
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg></button>
                <button id="search-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Cari (Ctrl+F)"><svg
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg></button>
                <div class="relative">
                    <button id="mode-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Mode Baca"><svg class="w-4 h-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg></button>
                    <div id="mode-dropdown">
                        <div class="mode-opt active" data-mode="normal">☀️ Normal</div>
                        <div class="mode-opt" data-mode="sepia">📜 Sepia</div>
                        <div class="mode-opt" data-mode="night">🌙 Night</div>
                    </div>
                </div>
                <button id="fullscreen-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Layar Penuh (F)"><svg
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg></button>
            </div>
            @auth
            <a href="{{ route('publikasi.download', $publication->slug) }}"
                class="pcb p-2 sm:px-3 bg-[#FF6B18] hover:!bg-[#E64627] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Download</span>
            </a>
            @else
            <button type="button" onclick="showGuestDownloadModal()"
                class="pcb p-2 sm:px-3 bg-[#FF6B18] hover:!bg-[#E64627] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Download</span>
            </button>
            @endauth
        </div>
    </div>
    <div class="progress-track">
        <div id="reading-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════ GUEST BANNER ══════ --}}
@guest
@php
$typeSlug = $publicationTypeSlug ?? ($publication->publicationType?->slug ?? '');
$previewLimit = match($typeSlug) { 'buku'=>'10 halaman','opini'=>'1 halaman',default=>'3 halaman' };
@endphp
<div id="guest-banner"
    class="w-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white px-4 py-2.5 flex items-center justify-between gap-3 text-sm z-40 relative flex-shrink-0">
    <div class="flex items-center min-w-0 gap-2">
        <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span class="text-xs font-medium truncate sm:text-sm">Mode pratinjau — hanya <strong>{{ $previewLimit }}
                pertama</strong> yang ditampilkan.</span>
    </div>
    <div class="flex items-center flex-shrink-0 gap-2">
        <a href="{{ route('login') }}"
            class="px-3 py-1 bg-white text-[#FF6B18] font-bold rounded-lg text-xs hover:bg-orange-50 transition-colors whitespace-nowrap">Login</a>
        <a href="{{ route('register') }}"
            class="hidden px-3 py-1 text-xs font-bold text-white transition-colors border rounded-lg bg-white/20 border-white/50 hover:bg-white/30 whitespace-nowrap sm:block">Daftar
            Gratis</a>
    </div>
</div>

<div id="guestDownloadModal" style="display:none;" class="fixed inset-0 z-[99999]">
    <div id="guestModalBackdrop"
        class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-black/60 backdrop-blur-sm"
        onclick="hideGuestDownloadModal()"></div>
    <div id="guestModalContainer"
        class="absolute inset-0 flex items-center justify-center p-4 transition-all duration-300 scale-95 opacity-0">
        <div class="relative w-full max-w-sm overflow-hidden text-center bg-white shadow-2xl rounded-2xl"
            onclick="event.stopPropagation()">
            <div class="h-1.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627]"></div>
            <div class="p-8">
                <button onclick="hideGuestDownloadModal()"
                    class="absolute top-4 right-4 p-1.5 rounded-full hover:bg-gray-100 transition-colors"><svg
                        class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
                <div class="w-16 h-16 bg-[#FFF7F2] rounded-full flex items-center justify-center mx-auto mb-4"><svg
                        class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg></div>
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-2">Download PDF?</h3>
                <p class="text-sm text-[#737373] mb-6 leading-relaxed">Login dulu untuk mengunduh PDF ini secara
                    gratis.<br>Daftar hanya butuh 1 menit!</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('login') }}"
                        class="w-full py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"><svg
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>Masuk Sekarang</a>
                    <a href="{{ route('register') }}"
                        class="w-full py-3 border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all duration-200 flex items-center justify-center gap-2"><svg
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>Daftar Gratis</a>
                    <button onclick="hideGuestDownloadModal()"
                        class="text-sm text-[#737373] hover:text-[#1A1A1A] py-1 transition-colors">Nanti saja</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endguest

{{-- ══════ PDF VIEWER ══════ --}}
<div id="pdf-viewer-container">
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-400">Harap tunggu sebentar</p>
    </div>

    <div id="pdf-recovery-overlay">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat ulang...</p>
        <p class="text-xs text-gray-400">Sebentar lagi siap</p>
    </div>

    <div id="pdf-canvas-wrapper" class="hidden">
        <div id="pdf-stage">
            <canvas id="pdf-canvas"></canvas>
            <div id="text-layer"></div>
            <div id="annotation-layer"></div>
            @auth<canvas id="freehand-canvas"></canvas>@endauth
            @guest<div id="pdf-watermark"></div>@endguest
        </div>
    </div>

    @auth
    <iframe id="pdf-iframe" title="PDF Viewer" sandbox="allow-same-origin allow-scripts"></iframe>
    @else
    <div id="pdf-iframe" style="display:none;" aria-hidden="true"></div>
    @endauth

    <div id="desktop-hint" class="hidden">← → halaman &nbsp;·&nbsp; +/− zoom &nbsp;·&nbsp; B tandai &nbsp;·&nbsp; Ctrl+F
        cari &nbsp;·&nbsp; Esc keluar</div>

    @guest
    <div id="guest-gate-overlay">
        <div class="gg-card">
            <div class="gg-lock-icon"><svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg></div>
            <p class="gg-title">Pratinjau Berakhir 🔒</p>
            <p class="gg-subtitle">Kamu sudah baca <strong id="gg-pages-shown">-</strong> halaman
                pertama.<br><strong>Login gratis</strong> untuk baca semua <strong id="gg-total-pages">-</strong>
                halaman.</p>
            <div class="gg-stats">
                <div class="gg-stat"><strong id="gg-stat-read">-</strong><span>Dibaca</span></div>
                <div class="gg-stat"><strong id="gg-stat-left">-</strong><span>Tersisa</span></div>
                <div class="gg-stat"><strong id="gg-stat-total">-</strong><span>Total hal.</span></div>
            </div>
            <a href="{{ route('login') }}" class="gg-btn-primary"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>Masuk Sekarang — Gratis</a>
            <a href="{{ route('register') }}" class="gg-btn-secondary"><svg class="w-4 h-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>Belum punya akun? Daftar Gratis</a>
            <div class="gg-benefits">
                <span class="gg-benefit"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>Gratis selamanya</span>
                <span class="gg-benefit"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>Ribuan publikasi</span>
                <span class="gg-benefit"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>Tanpa kartu kredit</span>
            </div>
        </div>
    </div>
    <div id="page-limit-warning">
        <svg class="plw-icon w-5 h-5 text-[#FF6B18] flex-shrink-0" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="plw-text"><strong id="page-limit-warning-title"></strong><span id="page-limit-warning-text"></span>
        </div>
    </div>
    @endguest

    {{-- Mobile Tap Overlay --}}
    <div id="mobile-tap-overlay">
        <p class="w-full max-w-xs text-sm font-bold text-center text-white truncate">{{
            Str::limit($publication->title,34) }}</p>
        <div class="tap-nav-row">
            <button class="tap-nav-btn" id="tap-prev"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg></button>
            <div class="tap-nav-center"><strong id="tap-page-num">1</strong><small>dari <span
                        id="tap-page-total">-</span></small></div>
            <button class="tap-nav-btn" id="tap-next"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg></button>
        </div>
        <div class="tap-zoom-row">
            <button class="tap-zoom-btn" id="tap-zoom-out">−</button>
            <div class="tap-zoom-track">
                <div id="tap-zoom-fill" class="tap-zoom-fill" style="width:25%"></div>
            </div>
            <span id="tap-zoom-val" class="tap-zoom-val">100%</span>
            <button class="tap-zoom-btn" id="tap-zoom-in">+</button>
        </div>
        <div class="tap-mode-row">
            <div class="tap-mode-card active" data-tap-mode="normal">
                <div class="tmc-ic">☀️</div>Normal
            </div>
            <div class="tap-mode-card" data-tap-mode="sepia">
                <div class="tmc-ic">📜</div>Sepia
            </div>
            <div class="tap-mode-card" data-tap-mode="night">
                <div class="tmc-ic">🌙</div>Night
            </div>
        </div>
        <div class="tap-actions">
            <button id="tap-bookmark-btn" class="tap-action-btn">
                <svg id="tap-bkmk-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span id="tap-bkmk-label">Tandai Halaman</span>
            </button>
            <button id="tap-exit-btn" class="tap-action-btn danger">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Keluar Fullscreen</span>
            </button>
        </div>
        <div class="tap-hint-tips">
            <div class="tip-badge"><span class="tip-ic">👉</span>Swipe halaman</div>
            <div class="tip-badge"><span class="tip-ic">🤏</span>Pinch zoom</div>
            <div class="tip-badge"><span class="tip-ic">👆</span>Tap menu</div>
        </div>
        <button class="tap-close-btn" id="tap-close-overlay">Tutup & Lanjut Baca</button>
    </div>
</div>{{-- /pdf-viewer-container --}}

{{-- ══════ OVERLAYS ══════ --}}

{{-- Annotation Tooltip --}}
<div id="annot-tooltip">
    <div class="at-text" id="annot-tooltip-text"></div>
    <div class="at-actions">
        <button class="at-btn del" id="annot-tooltip-del">🗑 Hapus</button>
        <button class="at-btn close" id="annot-tooltip-close">✕ Tutup</button>
    </div>
</div>

{{-- Comment Popup --}}
<div id="comment-popup">
    <p class="cp-title">💬 Tambah Komentar</p>
    <textarea id="comment-text" placeholder="Tulis komentar untuk teks ini..."></textarea>
    <div class="cp-actions">
        <button class="cp-save" id="comment-save">Simpan</button>
        <button class="cp-cancel" id="comment-cancel">Batal</button>
    </div>
</div>

{{-- Legacy annot-toolbar: hidden, kept for JS compat --}}
<div id="annot-toolbar" style="display:none!important">
    <button class="annot-tool-btn" data-color="yellow"></button>
    <button class="annot-tool-btn" data-color="green"></button>
    <button class="annot-tool-btn" data-color="pink"></button>
    <button class="annot-tool-btn" data-color="blue"></button>
    <button class="annot-tool-btn" data-color="orange"></button>
    <button id="add-comment-btn"></button>
    <button id="annot-close-btn"></button>
</div>

{{-- ══════ ANNOTATION BOTTOM BAR (login only) ══════ --}}
@auth
<div id="annot-bottom-bar">

    {{-- Handle --}}
    <div class="ab-handle" id="ab-handle">
        <div class="ab-handle-pip"></div>
        <span class="ab-handle-label" id="ab-active-label">✏️ Highlight</span>
        <button class="ab-collapse" id="ab-collapse" title="Sembunyikan">▾</button>
    </div>

    {{-- Collapsed pill --}}
    <div class="ab-expand" id="ab-expand">
        <div class="ab-expand-pill">
            <div class="ab-expand-dot"></div>
            <span>Alat Anotasi</span>
            <span style="color:#ff6b18">▴</span>
        </div>
    </div>

    {{-- Tools --}}
    <div class="ab-tools" id="ab-tools">

        {{-- Tool buttons --}}
        <button class="ab-tool active" data-tool="highlight" title="Highlight teks">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 19l-2 2H5l1-2L15 9l2 2L9 19z" stroke-linejoin="round" />
                <path d="M15 9l2 2-1.5 1.5L13.5 10.5 15 9z" fill="currentColor" stroke="none" />
                <line x1="5" y1="21" x2="19" y2="21" />
            </svg>
        </button>
        <button class="ab-tool" data-tool="freehand" title="Pen bebas">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
            </svg>
        </button>
        <button class="ab-tool" data-tool="shape" title="Kotak/Lingkaran/Panah">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <circle cx="17.5" cy="6.5" r="3.5" />
                <path d="M3 20h4M5 18v4M14 15l5 5m0-5l-5 5" />
            </svg>
        </button>
        <button class="ab-tool" data-tool="comment" title="Komentar teks">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
            </svg>
        </button>
        <button class="ab-tool" data-tool="sticky" title="Sticky note">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="9" y1="13" x2="15" y2="13" />
                <line x1="9" y1="17" x2="13" y2="17" />
            </svg>
        </button>
        <button class="ab-tool" data-tool="eraser" title="Hapus anotasi">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 20H7L3 16l10-10 7 7-3 3" />
                <path d="M6.5 17.5l5-5" />
            </svg>
        </button>

        <div class="ab-sep"></div>

        {{-- Shape sub-picker --}}
        <div class="ab-shapes" id="ab-shapes">
            <button class="ab-shape active" data-shape="rect" title="Kotak">⬛</button>
            <button class="ab-shape" data-shape="ellipse" title="Lingkaran">⭕</button>
            <button class="ab-shape" data-shape="arrow" title="Panah">➡</button>
            <div class="ab-sep"></div>
        </div>

        {{-- Size picker --}}
        <div class="ab-sizes" id="ab-sizes">
            <div class="ab-size selected" data-size="2" title="Tipis"></div>
            <div class="ab-size" data-size="4" title="Normal"></div>
            <div class="ab-size" data-size="8" title="Tebal"></div>
            <div class="ab-size" data-size="14" title="Sangat tebal"></div>
            <div class="ab-sep"></div>
        </div>

        {{-- Colors --}}
        <div class="ab-colors">
            <div class="ab-color selected" data-color="yellow" title="Kuning"></div>
            <div class="ab-color" data-color="green" title="Hijau"></div>
            <div class="ab-color" data-color="red" title="Merah"></div>
            <div class="ab-color" data-color="blue" title="Biru"></div>
            <div class="ab-color" data-color="orange" title="Oranye"></div>
            <div class="ab-color" data-color="black" title="Hitam"></div>
            <div class="ab-color" data-color="white" title="Putih"></div>
        </div>

        <div class="ab-sep"></div>

        {{-- Undo / Redo --}}
        <button class="ab-action" id="aft-undo" title="Undo (Ctrl+Z)" disabled>↩</button>
        <button class="ab-action" id="aft-redo" title="Redo" disabled>↪</button>

        <div class="ab-sep"></div>

        {{-- Panel --}}
        <button class="ab-tool" id="aft-panel-btn" title="Daftar anotasi">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6" />
                <line x1="8" y1="12" x2="21" y2="12" />
                <line x1="8" y1="18" x2="21" y2="18" />
                <circle cx="3" cy="6" r="1" fill="currentColor" />
                <circle cx="3" cy="12" r="1" fill="currentColor" />
                <circle cx="3" cy="18" r="1" fill="currentColor" />
            </svg>
            <span class="ab-badge" id="ab-panel-badge">0</span>
        </button>
    </div>
</div>

{{-- Sticky Note Popup --}}
<div id="sticky-popup">
    <p class="sp-title">📌 Tambah Sticky Note</p>
    <textarea id="sticky-text" placeholder="Tulis catatan di sini..."></textarea>
    <div class="sp-actions">
        <button class="sp-save" id="sticky-save">Tempel</button>
        <button class="sp-cancel" id="sticky-cancel">Batal</button>
    </div>
</div>

{{-- Annotation Panel --}}
<div id="annot-panel">
    <div class="ap-header">
        <span class="ap-title">📝 Anotasi Saya</span>
        <button class="ap-close" id="ap-close-btn">✕</button>
    </div>
    <div class="ap-list" id="ap-list">
        <div class="ap-empty">Belum ada anotasi.<br>Pilih tool di bawah lalu mulai beri catatan!</div>
    </div>
    <div class="ap-footer">
        <button class="ap-clear-btn" id="ap-clear-btn">🗑 Hapus semua di halaman ini</button>
    </div>
</div>

{{-- Sync indicator --}}
<div id="annot-sync-indicator">
    <div class="sync-dot"></div>
    <span id="annot-sync-text">Menyimpan...</span>
</div>

{{-- Eraser cursor --}}
<div id="eraser-cursor"></div>

{{-- Hidden aliases for pdf-annotations.js compatibility --}}
<div id="annot-floating-toolbar" style="display:none!important">
    <div id="aft-colors">
        <div data-color="yellow"></div>
        <div data-color="green"></div>
        <div data-color="red"></div>
        <div data-color="blue"></div>
        <div data-color="orange"></div>
        <div data-color="black"></div>
        <div data-color="white"></div>
    </div>
    <div id="aft-sizes">
        <div data-size="2"></div>
        <div data-size="4"></div>
        <div data-size="8"></div>
        <div data-size="14"></div>
    </div>
    <div id="aft-shape-types"><button data-shape="rect"></button><button data-shape="ellipse"></button><button
            data-shape="arrow"></button></div>
</div>

@endauth

{{-- ══════ BOTTOM SHEET ══════ --}}
<div id="sheet-backdrop"></div>
<div id="bottom-sheet">
    <div class="sheet-handle"></div>
    <p class="mb-3 text-sm font-bold text-white truncate">{{ Str::limit($publication->title,42) }}</p>
    <div class="sheet-sec">
        <span class="sheet-lbl">Navigasi Halaman</span>
        <div class="sheet-page-row">
            <button class="sheet-page-btn" id="sheet-prev"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg></button>
            <div class="sheet-page-display"><strong id="sheet-page-num">1</strong><small>dari <span
                        id="sheet-total">-</span> halaman</small></div>
            <button class="sheet-page-btn" id="sheet-next"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg></button>
        </div>
        <div class="sheet-jump-row">
            <input type="number" id="sheet-jump" class="sheet-jump-input" placeholder="Lompat ke halaman..." min="1">
            <button id="sheet-jump-go" class="sheet-jump-go">Go</button>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Zoom</span>
        <div class="sheet-zoom-row">
            <button class="sheet-zoom-btn" id="sheet-zoom-out">−</button>
            <div class="sheet-zoom-track">
                <div id="sheet-zoom-fill" class="sheet-zoom-fill" style="width:25%"></div>
            </div>
            <span id="sheet-zoom-val" class="sheet-zoom-val">100%</span>
            <button class="sheet-zoom-btn" id="sheet-zoom-in">+</button>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Mode Baca</span>
        <div class="sheet-mode-row">
            <div class="sheet-mode-card active" data-sheet-mode="normal">
                <div class="smc-ic">☀️</div><span>Normal</span>
            </div>
            <div class="sheet-mode-card" data-sheet-mode="sepia">
                <div class="smc-ic">📜</div><span>Sepia</span>
            </div>
            <div class="sheet-mode-card" data-sheet-mode="night">
                <div class="smc-ic">🌙</div><span>Night</span>
            </div>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Aksi</span>
        <div class="sheet-actions">
            <button id="sheet-bookmark-btn" class="sheet-act-btn">
                <svg id="sheet-bkmk-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span id="sheet-bkmk-label">Tandai Halaman</span>
            </button>
            <button id="sheet-fs-btn" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Layar Penuh</span>
            </button>
            <button id="sheet-search-btn" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Cari Kata</span>
            </button>
            @auth
            <a href="{{ route('publikasi.download', $publication->slug) }}" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download</span>
            </a>
            @else
            <button type="button" onclick="showGuestDownloadModal();closeSheet();" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download</span>
            </button>
            @endauth
        </div>
    </div>
    <button id="sheet-close" class="sheet-close">Tutup</button>
</div>

{{-- Mobile FAB --}}
<div id="mobile-fab">
    <button id="mobile-fab-btn" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
    </button>
</div>

{{-- Search Overlay --}}
<div id="search-overlay">
    <div id="search-box">
        <div class="search-input-row">
            <input type="text" id="search-input" placeholder="Cari kata atau kalimat..." autocomplete="off">
            <button class="snav-btn" id="search-prev-btn">↑</button>
            <button class="snav-btn" id="search-next-btn">↓</button>
            <button class="snav-btn" id="search-close-btn">✕</button>
        </div>
        <div id="search-status">Ketik untuk mencari...</div>
        <div id="search-match-info"></div>
        <div id="search-results-list"></div>
    </div>
</div>

{{-- Resume Toast --}}
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
    window.PDF_CONFIG = {
        pdfUrl         : @json($pdfUrl),
        slug           : @json($publication->slug),
        guestPageLimit : @json($pageLimit),
        isGuest        : @json($isGuest),
        loginUrl       : @json(route('login')),
        registerUrl    : @json(route('register')),
    };
</script>

<script src="{{ asset('js/pdf-viewer.js') }}?v={{ filemtime(public_path('js/pdf-viewer.js')) }}"></script>

@auth
<script src="{{ asset('js/pdf-annotations.js') }}?v={{ filemtime(public_path('js/pdf-annotations.js')) }}"></script>
@endauth

<script>
    (function () {
    'use strict';

    /* ── Auto-recovery: reload 1x jika PDF gagal load ── */
    const RKEY = 'pdf_reload_' + (window.PDF_CONFIG?.slug || 'x');
    const att  = parseInt(sessionStorage.getItem(RKEY) || '0');
    if (att < 1) {
        const t = setTimeout(() => {
            const c = document.getElementById('pdf-canvas');
            const w = document.getElementById('pdf-canvas-wrapper');
            if (!(c?.width > 0 && w && !w.classList.contains('hidden'))) {
                document.getElementById('pdf-recovery-overlay')?.classList.add('show');
                sessionStorage.setItem(RKEY, att + 1);
                setTimeout(() => location.reload(), 700);
            }
        }, 9000);
        window.addEventListener('pdf-viewer-ready', () => {
            clearTimeout(t);
            sessionStorage.removeItem(RKEY);
        });
    }

    /* ── Bottom annotation bar ── */
    const bar       = document.getElementById('annot-bottom-bar');
    if (!bar) return;

    const toolsRow  = document.getElementById('ab-tools');
    const expandRow = document.getElementById('ab-expand');
    const label     = document.getElementById('ab-active-label');
    const handle    = document.getElementById('ab-handle');
    const colBtn    = document.getElementById('ab-collapse');
    let collapsed   = false;

    const LABELS = { highlight:'✏️ Highlight', freehand:'🖊 Pen Bebas', shape:'⬛ Shape', comment:'💬 Komentar', sticky:'📌 Sticky Note', eraser:'🧹 Hapus' };

    function collapse() {
        collapsed = true;
        toolsRow.style.display = 'none';
        handle.style.display   = 'none';
        expandRow.style.display= 'flex';
    }
    function expand() {
        collapsed = false;
        toolsRow.style.display = '';
        handle.style.display   = '';
        expandRow.style.display= 'none';
    }

    colBtn.addEventListener('click', e => { e.stopPropagation(); collapsed ? expand() : collapse(); });
    expandRow.addEventListener('click', expand);

    /* Show bar when PDF ready */
    function showBar() { bar.classList.add('visible'); }
    const waitV = setInterval(() => {
        if (window._pdfViewer) { clearInterval(waitV); window._pdfViewer.onReady(showBar); }
    }, 80);

    /* Tool selection — forward to pdf-annotations.js */
    document.querySelectorAll('.ab-tool[data-tool]').forEach(btn => {
        btn.addEventListener('click', () => {
            const tool = btn.dataset.tool;
            document.querySelectorAll('.ab-tool[data-tool]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            label.textContent = LABELS[tool] || tool;

            document.getElementById('ab-sizes').classList.toggle('show',  ['freehand','shape'].includes(tool));
            document.getElementById('ab-shapes').classList.toggle('show', tool === 'shape');

            /* Forward to hidden aft-toolbar that pdf-annotations.js listens to */
            const hidden = document.querySelector(`#annot-floating-toolbar [data-tool="${tool}"]`);
            if (hidden) hidden.click();
            window.dispatchEvent(new CustomEvent('annot-tool-change', { detail: { tool } }));
        });
    });

    /* Color */
    document.querySelectorAll('.ab-color').forEach(sw => {
        sw.addEventListener('click', () => {
            document.querySelectorAll('.ab-color').forEach(s => s.classList.remove('selected'));
            sw.classList.add('selected');
            const hidden = document.querySelector(`#aft-colors [data-color="${sw.dataset.color}"]`);
            if (hidden) hidden.click();
            window.dispatchEvent(new CustomEvent('annot-color-change', { detail: { color: sw.dataset.color } }));
        });
    });

    /* Size */
    document.querySelectorAll('.ab-size').forEach(d => {
        d.addEventListener('click', () => {
            document.querySelectorAll('.ab-size').forEach(x => x.classList.remove('selected'));
            d.classList.add('selected');
            const hidden = document.querySelector(`#aft-sizes [data-size="${d.dataset.size}"]`);
            if (hidden) hidden.click();
            window.dispatchEvent(new CustomEvent('annot-size-change', { detail: { size: +d.dataset.size } }));
        });
    });

    /* Shape type */
    document.querySelectorAll('.ab-shape').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.ab-shape').forEach(x => x.classList.remove('active'));
            b.classList.add('active');
            const hidden = document.querySelector(`#aft-shape-types [data-shape="${b.dataset.shape}"]`);
            if (hidden) hidden.click();
            window.dispatchEvent(new CustomEvent('annot-shape-change', { detail: { shape: b.dataset.shape } }));
        });
    });

    /* Badge counter from annotations */
    window.addEventListener('annot-count-change', e => {
        const badge = document.getElementById('ab-panel-badge');
        if (!badge) return;
        const n = e.detail?.count || 0;
        badge.textContent = n;
        badge.classList.toggle('show', n > 0);
    });

})();
</script>
@endpush