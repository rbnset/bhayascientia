{{--
resources/views/publikasi/read.blade.php
─────────────────────────────────────────
File ini hanya berisi HTML struktur + config injection.
CSS → public/css/pdf-viewer.css
JS → public/js/pdf-viewer.js
─────────────────────────────────────────
--}}
@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')
@section('hide_footer', 'true')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pdf-viewer.css') }}?v={{ filemtime(public_path('css/pdf-viewer.css')) }}">
@endpush

@section('content')

{{-- ══════════ FULLSCREEN TOOLBAR ══════════ --}}
<div id="pdf-fullscreen-toolbar">
    <span class="flex-1 hidden min-w-0 text-xs font-bold text-white truncate sm:block">{{
        Str::limit($publication->title, 38) }}</span>
    <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1 flex-shrink-0">
        <button id="fs-prev" class="pcb p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="px-1 text-xs font-semibold text-white whitespace-nowrap"><span id="fs-page-num">1</span>/<span
                id="fs-page-count">-</span></span>
        <button id="fs-next" class="pcb p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
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

{{-- ══════════ NORMAL TOOLBAR ══════════ --}}
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

{{-- ══════════ GUEST BANNER ══════════ --}}
@guest
@php
$typeSlug = $publicationTypeSlug ?? ($publication->publicationType?->slug ?? '');
$previewLimit = match($typeSlug) { 'buku' => '10 halaman', 'opini' => '1 halaman', default => '3 halaman' };
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

{{-- ✅ GUEST DOWNLOAD MODAL --}}
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
                    class="absolute top-4 right-4 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="w-16 h-16 bg-[#FFF7F2] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-2">Download PDF?</h3>
                <p class="text-sm text-[#737373] mb-6 leading-relaxed">Login dulu untuk mengunduh PDF ini secara
                    gratis.<br>Daftar hanya butuh 1 menit!</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('login') }}"
                        class="w-full py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register') }}"
                        class="w-full py-3 border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Daftar Gratis
                    </a>
                    <button onclick="hideGuestDownloadModal()"
                        class="text-sm text-[#737373] hover:text-[#1A1A1A] py-1 transition-colors">Nanti saja</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endguest

{{-- ══════════ PDF VIEWER ══════════ --}}
<div id="pdf-viewer-container">
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-400">Harap tunggu sebentar</p>
    </div>

    <div id="pdf-canvas-wrapper" class="hidden">
        <div id="pdf-stage">
            <canvas id="pdf-canvas"></canvas>
            <div id="text-layer"></div>
            <div id="annotation-layer"></div>
            {{-- ✅ Watermark hanya tampil untuk guest (diisi via JS) --}}
            @guest<div id="pdf-watermark"></div>@endguest
        </div>
    </div>

    @auth
    <iframe id="pdf-iframe" title="PDF Viewer" sandbox="allow-same-origin allow-scripts"></iframe>
    @else
    <div id="pdf-iframe" style="display:none;" aria-hidden="true"></div>
    @endauth

    <div id="desktop-hint" class="hidden">← → halaman &nbsp;·&nbsp; ↑↓ scroll &nbsp;·&nbsp; +/− zoom &nbsp;·&nbsp; B
        tandai &nbsp;·&nbsp; Ctrl+F cari &nbsp;·&nbsp; Esc keluar</div>

    {{-- ✅ GUEST GATE OVERLAY --}}
    @guest
    <div id="guest-gate-overlay">
        <div class="gg-card">
            <div class="gg-lock-icon">
                <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <p class="gg-title">Pratinjau Berakhir 🔒</p>
            <p class="gg-subtitle">Kamu sudah baca <strong id="gg-pages-shown">-</strong> halaman
                pertama.<br><strong>Login gratis</strong> untuk baca semua <strong id="gg-total-pages">-</strong>
                halaman.</p>
            <div class="gg-stats">
                <div class="gg-stat"><strong id="gg-stat-read">-</strong><span>Dibaca</span></div>
                <div class="gg-stat"><strong id="gg-stat-left">-</strong><span>Tersisa</span></div>
                <div class="gg-stat"><strong id="gg-stat-total">-</strong><span>Total hal.</span></div>
            </div>
            <a href="{{ route('login') }}" class="gg-btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Masuk Sekarang — Gratis
            </a>
            <a href="{{ route('register') }}" class="gg-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Belum punya akun? Daftar Gratis
            </a>
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
        <div class="plw-text">
            <strong id="page-limit-warning-title"></strong>
            <span id="page-limit-warning-text"></span>
        </div>
    </div>
    @endguest

    {{-- Mobile Tap Overlay --}}
    <div id="mobile-tap-overlay">
        <p class="w-full max-w-xs text-sm font-bold text-center text-white truncate">{{ Str::limit($publication->title,
            34) }}</p>
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
</div>

{{-- ══════════ OVERLAYS & PANELS ══════════ --}}

{{-- Annotation Toolbar --}}
<div id="annot-toolbar">
    <span class="text-[11px] text-gray-400 px-1">Stabilo:</span>
    <button class="annot-tool-btn" data-color="yellow" title="Kuning">
        <div class="color-swatch cs-yellow"></div>
    </button>
    <button class="annot-tool-btn" data-color="green" title="Hijau">
        <div class="color-swatch cs-green"></div>
    </button>
    <button class="annot-tool-btn" data-color="pink" title="Pink">
        <div class="color-swatch cs-pink"></div>
    </button>
    <button class="annot-tool-btn" data-color="blue" title="Biru">
        <div class="color-swatch cs-blue"></div>
    </button>
    <button class="annot-tool-btn" data-color="orange" title="Oranye">
        <div class="color-swatch cs-orange"></div>
    </button>
    <div class="at-sep"></div>
    <button class="annot-tool-btn" id="add-comment-btn" title="Tambah komentar">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>
    <div class="at-sep"></div>
    <button class="annot-tool-btn" id="annot-close-btn" title="Tutup">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
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

{{-- Annotation Tooltip --}}
<div id="annot-tooltip">
    <div class="at-text" id="annot-tooltip-text"></div>
    <div class="at-actions">
        <button class="at-btn del" id="annot-tooltip-del">🗑 Hapus</button>
        <button class="at-btn close" id="annot-tooltip-close">✕ Tutup</button>
    </div>
</div>

{{-- Bottom Sheet --}}
<div id="sheet-backdrop"></div>
<div id="bottom-sheet">
    <div class="sheet-handle"></div>
    <p class="mb-3 text-sm font-bold text-white truncate">{{ Str::limit($publication->title, 42) }}</p>
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
            <button type="button" onclick="showGuestDownloadModal(); closeSheet();" class="sheet-act-btn">
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
            <button class="snav-btn" id="search-prev-btn" title="Sebelumnya">↑</button>
            <button class="snav-btn" id="search-next-btn" title="Berikutnya">↓</button>
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
{{-- PDF.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

{{-- ✅ Inject config dari Blade ke JS (satu-satunya Blade logic di scripts) --}}
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
pdfjsLib.verbosity = 0;

// ✅ Config object yang dibaca oleh pdf-viewer.js
window.PDF_CONFIG = {
    pdfUrl         : @json($pdfUrl),
    slug           : @json($publication->slug),
    guestPageLimit : @json($pageLimit),
    isGuest        : @json($isGuest),
    loginUrl       : @json(route('login')),
    registerUrl    : @json(route('register')),
};
</script>

{{-- Load external JS setelah config siap --}}
<script src="{{ asset('js/pdf-viewer.js') }}?v={{ filemtime(public_path('js/pdf-viewer.js')) }}"></script>
@endpush