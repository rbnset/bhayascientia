@extends('layouts.app')

@section('title', 'Jelajahi Semua Publikasi - DABRAKA')
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* ═══════════════════════════════════════
       FILTER SIDEBAR
    ═══════════════════════════════════════ */
    .filter-sidebar {
        position: sticky;
        top: 6rem;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
    }

    .filter-sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .filter-sidebar::-webkit-scrollbar-track {
        background: #F8F9FC;
        border-radius: 3px;
    }

    .filter-sidebar::-webkit-scrollbar-thumb {
        background: #FF6B18;
        border-radius: 3px;
    }

    /* ═══════════════════════════════════════
       BASE CARD
    ═══════════════════════════════════════ */
    .publication-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .publication-card-cover {
        position: relative;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
        flex-shrink: 0;
    }

    /* ═══════════════════════════════════════
       GRID MODE
    ═══════════════════════════════════════ */
    .view-grid .publication-card:hover {
        transform: translateY(-6px);
    }

    .view-grid .publication-card-cover {
        aspect-ratio: 3/4;
        width: 100%;
    }

    .view-grid .publication-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        display: block;
    }

    .view-grid .publication-card:hover .publication-card-cover img {
        transform: scale(1.08);
    }

    /* ═══════════════════════════════════════
       LIST MODE
    ═══════════════════════════════════════ */
    .view-list .publication-card {
        flex-direction: row !important;
        align-items: stretch;
    }

    .view-list .publication-card:hover {
        transform: translateY(-2px);
    }

    .view-list .publication-card-cover {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        aspect-ratio: 3/4;
        border-radius: 0;
    }

    @media (min-width: 400px) {
        .view-list .publication-card-cover {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
        }
    }

    @media (min-width: 600px) {
        .view-list .publication-card-cover {
            width: 130px;
            min-width: 130px;
            max-width: 130px;
        }
    }

    @media (min-width: 1024px) {
        .view-list .publication-card-cover {
            width: 160px;
            min-width: 160px;
            max-width: 160px;
        }
    }

    .view-list .publication-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    .view-list .stats-overlay {
        display: none !important;
    }

    /* Content area list mode */
    .view-list .card-content {
        padding: 10px 12px !important;
        min-width: 0;
        flex: 1 1 0%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    @media (min-width: 400px) {
        .view-list .card-content {
            padding: 12px 14px !important;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-content {
            padding: 14px 18px !important;
        }
    }

    @media (min-width: 1024px) {
        .view-list .card-content {
            padding: 18px 22px !important;
        }
    }

    /* Judul list mode */
    .view-list .card-title {
        font-size: 0.75rem !important;
        line-height: 1.35 !important;
        -webkit-line-clamp: 3 !important;
        display: -webkit-box !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
        margin-bottom: 0 !important;
    }

    @media (min-width: 400px) {
        .view-list .card-title {
            font-size: 0.8125rem !important;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-title {
            font-size: 1rem !important;
            -webkit-line-clamp: 3 !important;
        }
    }

    @media (min-width: 1024px) {
        .view-list .card-title {
            font-size: 1.125rem !important;
            -webkit-line-clamp: 4 !important;
        }
    }

    /* Abstrak: tampil di list mode hanya ≥600px */
    .view-list .card-abstract {
        display: none;
    }

    @media (min-width: 600px) {
        .view-list .card-abstract {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.8125rem;
            color: #737373;
            margin-top: 4px;
        }
    }

    @media (min-width: 1024px) {
        .view-list .card-abstract {
            -webkit-line-clamp: 3;
        }
    }

    /* Meta row list mode */
    .view-list .card-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-bottom: 4px;
    }

    .view-list .card-meta .cat-badge {
        font-size: 9px !important;
        padding: 1px 7px !important;
    }

    .view-list .card-meta .date-text {
        font-size: 9px !important;
        white-space: nowrap;
    }

    @media (min-width: 400px) {
        .view-list .card-meta .cat-badge {
            font-size: 10px !important;
        }

        .view-list .card-meta .date-text {
            font-size: 10px !important;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-meta .cat-badge {
            font-size: 11px !important;
        }

        .view-list .card-meta .date-text {
            font-size: 11px !important;
        }
    }

    /* Authors list mode */
    .view-list .card-authors {
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid #F0F0F0;
    }

    .view-list .card-authors .author-avatar {
        width: 18px !important;
        height: 18px !important;
    }

    @media (min-width: 600px) {
        .view-list .card-authors .author-avatar {
            width: 24px !important;
            height: 24px !important;
        }
    }

    .view-list .card-authors .author-name {
        font-size: 9px !important;
        max-width: 70px;
    }

    @media (min-width: 400px) {
        .view-list .card-authors .author-name {
            font-size: 10px !important;
            max-width: 90px;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-authors .author-name {
            font-size: 11px !important;
            max-width: 120px;
        }
    }

    @media (max-width: 349px) {
        .view-list .card-arrow {
            display: none !important;
        }
    }

    /* ═══════════════════════════════════════
       VIEW TOGGLE BUTTON
    ═══════════════════════════════════════ */
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #737373;
        cursor: pointer;
        transition: all 0.18s ease;
        flex-shrink: 0;
    }

    .view-btn:hover {
        color: #FF6B18;
        background: #FFF7F2;
    }

    .view-btn.active {
        background: #FF6B18;
        color: white;
    }

    /* ═══════════════════════════════════════
       STAT ANIMATION
    ═══════════════════════════════════════ */
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-number {
        animation: countUp 0.6s ease-out;
    }

    .category-badge {
        transition: all 0.3s ease;
    }

    .category-badge:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.2);
    }

    /* ═══════════════════════════════════════
       HORIZONTAL SCROLL
    ═══════════════════════════════════════ */
    .h-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 4px;
    }

    .h-scroll::-webkit-scrollbar {
        display: none;
    }

    .h-scroll>* {
        flex-shrink: 0;
    }

    /* ═══════════════════════════════════════
       PAGINATION
    ═══════════════════════════════════════ */
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.8125rem;
        transition: all 0.18s ease;
        border: 2px solid #EEF0F7;
        background: white;
        color: #1A1A1A;
    }

    .pagination-btn:hover {
        border-color: #FF6B18;
        color: #FF6B18;
        background: #FFF7F2;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #FF6B18, #E64627);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.35);
    }

    .pagination-btn[aria-disabled="true"] {
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ═══════════════════════════════════════
       RESPONSIVE GLOBAL — safe hingga 300px
    ═══════════════════════════════════════ */
    @media (max-width: 400px) {
        .hero-stat-card {
            padding: 12px 10px !important;
        }

        .hero-stat-num {
            font-size: 1.5rem !important;
        }

        .hero-stat-label {
            font-size: 0.6875rem !important;
        }

        .toolbar-select {
            font-size: 0.6875rem !important;
            max-width: 90px;
        }
    }

    @media (max-width: 320px) {
        .hero-stat-num {
            font-size: 1.25rem !important;
        }

        .toolbar-select {
            max-width: 78px;
            font-size: 0.625rem !important;
        }
    }

    /* Mobile filter drawer */
    #filterDrawer {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    #filterDrawer.open {
        transform: translateX(0);
    }
</style>
@endpush

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('content')

{{-- ✨ Anchor scroll ke atas --}}
<div id="top-anchor"></div>

{{-- ═══════════════════════════════════
FILTER CONTENT (Blade Component)
Dipakai oleh drawer & sidebar
═══════════════════════════════════ --}}
@php
// Supaya bisa dipakai di 2 tempat (drawer & sidebar) tanpa duplikat logika
$filterHtml = function() use ($selectedType, $filterCategory, $filterYear, $filterSort, $publicationTypes, $categories,
$years, $stats) {
// Tidak bisa return HTML dari closure di Blade, jadi kita pakai cara lain
// → lihat @include diganti dengan komponen inline di bawah
};
@endphp

{{-- Hero Section --}}
<section class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="hero-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hero-grid)" />
        </svg>
    </div>
    <div class="absolute w-40 h-40 rounded-full -top-10 -right-10 bg-white/10"></div>
    <div class="absolute rounded-full w-28 h-28 -bottom-8 -left-8 bg-white/10"></div>

    <div class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-10 sm:py-14 relative z-10">

        {{-- Breadcrumb --}}
        <nav class="h-scroll mb-5 text-[11px] sm:text-sm text-white/80 pb-0" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="flex-shrink-0 w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('publikasi.index') }}" class="transition-colors hover:text-white">Publikasi</a>
            <svg class="flex-shrink-0 w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Jelajahi Semua</span>
        </nav>

        {{-- Heading --}}
        <div class="mb-5 text-white sm:mb-8">
            <h1 class="mb-2 text-2xl font-black leading-tight sm:text-4xl md:text-5xl sm:mb-4">
                🔍 Jelajahi Publikasi
            </h1>
            <p class="max-w-2xl text-sm sm:text-lg text-white/90">
                Temukan dan eksplorasi koleksi lengkap publikasi ilmiah dari berbagai bidang penelitian
            </p>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-2 gap-2 sm:gap-4 md:grid-cols-4">
            <div class="p-4 border hero-stat-card sm:p-5 bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                <p class="text-2xl font-black text-white hero-stat-num sm:text-3xl stat-number">{{
                    number_format($stats['total'])
                    }}</p>
                <p class="hero-stat-label mt-0.5 text-[11px] sm:text-sm text-white/80">Total Publikasi</p>
            </div>
            <div class="p-4 border hero-stat-card sm:p-5 bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                <p class="text-2xl font-black text-white hero-stat-num sm:text-3xl stat-number">{{
                    number_format($stats['this_year']) }}</p>
                <p class="hero-stat-label mt-0.5 text-[11px] sm:text-sm text-white/80">Publikasi {{ date('Y') }}</p>
            </div>
            <div class="p-4 border hero-stat-card sm:p-5 bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                <p class="text-2xl font-black text-white hero-stat-num sm:text-3xl stat-number">{{
                    number_format($stats['categories']) }}</p>
                <p class="hero-stat-label mt-0.5 text-[11px] sm:text-sm text-white/80">Kategori</p>
            </div>
            <div class="p-4 border hero-stat-card sm:p-5 bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                <p class="text-2xl font-black text-white hero-stat-num sm:text-3xl stat-number">{{
                    number_format($stats['authors'])
                    }}</p>
                <p class="hero-stat-label mt-0.5 text-[11px] sm:text-sm text-white/80">Penulis</p>
            </div>
        </div>
    </div>
</section>

{{-- Mobile Filter Toggle --}}
<div class="lg:hidden px-3 sm:px-6 mx-auto max-w-[1130px] mt-4">
    <button onclick="toggleFilterDrawer()"
        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-[#EEF0F7] rounded-xl font-semibold text-sm text-[#1A1A1A] hover:border-[#FF6B18] hover:text-[#FF6B18] transition-all">
        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Tampilkan Filter
        @if($filterCategory || $filterYear)
        <span class="px-1.5 py-0.5 bg-[#FF6B18] text-white text-[10px] font-bold rounded-full">
            {{ ($filterCategory ? 1 : 0) + ($filterYear ? 1 : 0) }}
        </span>
        @endif
    </button>
</div>

{{-- Mobile Overlay --}}
<div id="filterOverlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden" onclick="toggleFilterDrawer()">
</div>

{{-- Mobile Filter Drawer --}}
<div id="filterDrawer"
    class="fixed top-0 left-0 h-full w-[280px] max-w-[85vw] bg-white z-50 lg:hidden overflow-y-auto shadow-2xl">
    <div class="p-4 space-y-5">
        {{-- Drawer Header --}}
        <div class="flex items-center justify-between pb-3 border-b-2 border-[#EEF0F7]">
            <h2 class="text-sm font-bold text-[#1A1A1A] flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </h2>
            <div class="flex items-center gap-2">
                @if($filterCategory || $filterYear)
                <a href="{{ route('publikasi.browse', ['type' => $selectedType, 'sort' => $filterSort]) }}"
                    class="text-xs font-semibold text-[#FF6B18] hover:underline">Reset</a>
                @endif
                <button onclick="toggleFilterDrawer()" class="p-1.5 rounded-lg hover:bg-[#F8F9FC] transition-colors">
                    <svg class="w-4 h-4 text-[#737373]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Jenis Publikasi --}}
        <div>
            <h3 class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-2">Jenis Publikasi</h3>
            <div class="space-y-1.5">
                <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => 'all'])) }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl font-semibold text-xs transition-all
                    {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                    <span>Semua Jenis</span>
                    <span class="text-[10px]">{{ $stats['total'] }}</span>
                </a>
                @foreach($publicationTypes as $type)
                <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => $type->slug])) }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl font-semibold text-xs transition-all
                    {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                    <span>{{ $type->name }}</span>
                    <span class="text-[10px]">{{ $type->publications_count ?? 0 }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Kategori --}}
        <div>
            <h3 class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-2">Kategori</h3>
            <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                @foreach($categories as $category)
                <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['category' => $category->slug])) }}"
                    class="category-badge flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all
                    {{ $filterCategory == $category->slug ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                    <span class="truncate">{{ $category->name }}</span>
                    <span class="text-[10px] font-semibold flex-shrink-0 ml-1">{{ $category->publications_count
                        }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Tahun --}}
        <div>
            <h3 class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-2">Tahun</h3>
            <div class="space-y-1.5">
                @foreach($years as $year)
                <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['year' => $year])) }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all
                    {{ $filterYear == $year ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                    <span>{{ $year }}</span>
                    <svg class="flex-shrink-0 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<section class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-4 sm:mt-8 lg:mt-10">
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 lg:gap-8">

        {{-- LEFT: Filter Sidebar (desktop only) --}}
        <aside class="hidden filter-sidebar lg:block">
            <div class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-5 space-y-5">

                {{-- Sidebar Header --}}
                <div class="flex items-center justify-between pb-3 border-b-2 border-[#EEF0F7]">
                    <h2 class="text-base font-bold text-[#1A1A1A] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </h2>
                    @if($filterCategory || $filterYear)
                    <a href="{{ route('publikasi.browse', ['type' => $selectedType, 'sort' => $filterSort]) }}"
                        class="text-sm font-semibold text-[#FF6B18] hover:text-[#E64627] transition-colors">Reset</a>
                    @endif
                </div>

                {{-- Jenis Publikasi --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Jenis Publikasi</h3>
                    <div class="space-y-2">
                        <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => 'all'])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl font-semibold text-sm transition-all
                            {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>Semua Jenis</span>
                            <span class="text-xs">{{ $stats['total'] }}</span>
                        </a>
                        @foreach($publicationTypes as $type)
                        <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => $type->slug])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl font-semibold text-sm transition-all
                            {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>{{ $type->name }}</span>
                            <span class="text-xs">{{ $type->publications_count ?? 0 }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Kategori --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Kategori</h3>
                    <div class="pr-1 space-y-2 overflow-y-auto max-h-64">
                        @foreach($categories as $category)
                        <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['category' => $category->slug])) }}"
                            class="category-badge flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-all
                            {{ $filterCategory == $category->slug ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span class="truncate">{{ $category->name }}</span>
                            <span class="flex-shrink-0 ml-1 text-xs font-semibold">{{ $category->publications_count
                                }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Tahun --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Tahun Publikasi</h3>
                    <div class="space-y-2">
                        @foreach($years as $year)
                        <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['year' => $year])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-all
                            {{ $filterYear == $year ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>{{ $year }}</span>
                            <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </aside>

        {{-- RIGHT: Publications --}}
        <div class="min-w-0 space-y-4">

            {{-- Toolbar --}}
            <div class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-3 sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">

                    {{-- Info --}}
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div
                            class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] sm:text-xs text-[#737373]">Menampilkan</p>
                            <p class="text-sm sm:text-base font-bold text-[#1A1A1A] truncate">
                                {{ number_format($publications->total()) }} Publikasi
                            </p>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0 flex-wrap justify-end">
                        {{-- Sort --}}
                        <select onchange="window.location.href = this.value"
                            class="toolbar-select px-2 py-1.5 sm:px-3 sm:py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-[10px] sm:text-sm focus:border-[#FF6B18] outline-none cursor-pointer bg-white max-w-[90px] sm:max-w-none">
                            <option
                                value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'latest'])) }}"
                                {{ $filterSort=='latest' ? 'selected' : '' }}>🕐 Terbaru</option>
                            <option
                                value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}"
                                {{ $filterSort=='popular' ? 'selected' : '' }}>🔥 Populer</option>
                            <option
                                value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'oldest'])) }}"
                                {{ $filterSort=='oldest' ? 'selected' : '' }}>📅 Terlama</option>
                            <option
                                value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'title'])) }}"
                                {{ $filterSort=='title' ? 'selected' : '' }}>🔤 A-Z</option>
                        </select>

                        {{-- Per page --}}
                        <select onchange="window.location.href = this.value"
                            class="toolbar-select px-2 py-1.5 sm:px-3 sm:py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-[10px] sm:text-sm focus:border-[#FF6B18] outline-none cursor-pointer bg-white">
                            @foreach([12, 24, 36, 48] as $n)
                            <option
                                value="{{ route('publikasi.browse', array_merge(request()->except('per_page'), ['per_page' => $n])) }}"
                                {{ $perPage==$n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>

                        {{-- View Toggle --}}
                        <div class="flex items-center gap-0.5 border-2 border-[#EEF0F7] rounded-xl p-0.5 bg-white">
                            <button type="button" id="btn-grid2" onclick="setView('grid2')" class="view-btn"
                                title="2 Kolom">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <rect x="2" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="2" y="11" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="11" width="7" height="7" rx="1.5" />
                                </svg>
                            </button>
                            <button type="button" id="btn-grid3" onclick="setView('grid3')" class="view-btn"
                                title="3 Kolom">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <rect x="1" y="2" width="5" height="7" rx="1" />
                                    <rect x="7.5" y="2" width="5" height="7" rx="1" />
                                    <rect x="14" y="2" width="5" height="7" rx="1" />
                                    <rect x="1" y="11" width="5" height="7" rx="1" />
                                    <rect x="7.5" y="11" width="5" height="7" rx="1" />
                                    <rect x="14" y="11" width="5" height="7" rx="1" />
                                </svg>
                            </button>
                            <button type="button" id="btn-list" onclick="setView('list')" class="view-btn"
                                title="Mode Baris">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Publications Container --}}
            <div id="browseContainer" class="view-grid">
                <div id="browseGrid" class="grid grid-cols-2 gap-3 sm:gap-4">

                    @foreach($formattedPublications as $publication)
                    @php
                    $words = array_filter(explode(' ', $publication['title']));
                    $initials = '';
                    foreach (array_slice($words, 0, 2) as $word) {
                    $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
                    }
                    if (empty($initials)) $initials = mb_strtoupper(mb_substr($publication['title'], 0, 2));
                    $firstAuthor = $publication['authors'][0]['name'] ?? 'Anonymous';
                    $placeholderUrl = route('placeholder.cover') . '?' . http_build_query([
                    'initials' => $initials,
                    'type' => $publication['publication_type'] ?? 'Publikasi',
                    'title' => $publication['title'],
                    'category' => $publication['category'] ?? 'Umum',
                    'author' => $firstAuthor,
                    'v' => time(),
                    ]);
                    $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);
                    $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
                    @endphp

                    <a href="{{ $publication['detail_url'] }}"
                        class="publication-card group bg-white rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col">

                        {{-- Cover --}}
                        <div class="publication-card-cover" style="display:block;background-color:#F8F9FC;">
                            <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                                decoding="async"
                                style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;opacity:1!important;visibility:visible!important;"
                                onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">

                            {{-- Stats Overlay --}}
                            <div
                                class="absolute bottom-0 left-0 right-0 z-20 p-3 transition-opacity duration-300 opacity-0 stats-overlay bg-gradient-to-t from-black/70 to-transparent group-hover:opacity-100">
                                <div class="flex items-center gap-3 text-xs text-white">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        {{ number_format($publication['views_count'] ?? 0) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        {{ number_format($publication['download_count'] ?? 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="card-content flex flex-col flex-1 p-2 sm:p-3.5">

                            {{-- Meta --}}
                            <div class="card-meta flex items-start gap-1 mb-1.5 flex-wrap">
                                <span
                                    class="cat-badge px-2 py-0.5 bg-[#FFF7F2] text-[#FF6B18] text-[10px] sm:text-[11px] font-bold rounded-full truncate max-w-[60%] leading-[1.6]">
                                    {{ $publication['category'] ?? 'Umum' }}
                                </span>
                                <span
                                    class="date-text text-[10px] sm:text-[11px] text-[#A3A6AE] whitespace-nowrap leading-[1.6]">
                                    {{ $publication['formatted_date'] }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <h3 class="card-title font-bold text-[12px] sm:text-sm lg:text-base text-[#1A1A1A] line-clamp-3 group-hover:text-[#FF6B18] transition-colors leading-snug flex-1 mb-2"
                                style="overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;word-break:break-word;">
                                {{ $publication['title'] }}
                            </h3>

                            {{-- Abstract --}}
                            <p class="card-abstract text-xs sm:text-sm text-[#737373] line-clamp-2 mb-3">
                                {{ $publication['abstract'] ?? 'Tidak ada abstrak' }}
                            </p>

                            {{-- Authors --}}
                            <div
                                class="card-authors flex items-center justify-between gap-1.5 mt-auto pt-2 border-t border-[#F0F0F0]">
                                <div class="flex items-center min-w-0 gap-1 overflow-hidden">
                                    <div class="flex -space-x-1.5 flex-shrink-0">
                                        @foreach(array_slice($publication['authors'], 0, 2) as $author)
                                        <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                                            title="{{ $author['name'] }}"
                                            class="object-cover w-5 h-5 border-2 border-white rounded-full author-avatar sm:w-6 sm:h-6">
                                        @endforeach
                                    </div>
                                    <span class="author-name text-[10px] sm:text-[11px] text-[#737373] truncate">
                                        {{ Str::limit($publication['authors'][0]['name'] ?? '', 13) }}
                                        @if($publication['total_authors'] > 1)
                                        <span class="text-[#A3A6AE]">+{{ $publication['total_authors'] - 1 }}</span>
                                        @endif
                                    </span>
                                </div>
                                <span
                                    class="card-arrow flex-shrink-0 w-5 h-5 sm:w-7 sm:h-7 rounded-full bg-[#FFF7F2] text-[#FF6B18] flex items-center justify-center group-hover:bg-[#FF6B18] group-hover:text-white transition-all">
                                    <svg class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach

                </div>
            </div>

            {{-- Pagination --}}
            @if($publications->hasPages())
            @php
            $currentPage = $publications->currentPage();
            $lastPage = $publications->lastPage();
            $totalRes = $publications->total();
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
            @endphp
            <div class="flex flex-col items-center gap-3 mt-6">
                <p class="text-[11px] sm:text-sm text-[#737373] text-center">
                    Hal. <span class="font-bold text-[#1A1A1A]">{{ $currentPage }}</span> /
                    <span class="font-bold text-[#1A1A1A]">{{ $lastPage }}</span>
                    &nbsp;·&nbsp;
                    <span class="font-bold text-[#FF6B18]">{{ number_format($totalRes) }}</span> publikasi
                </p>
                <div class="flex flex-wrap items-center justify-center gap-1">
                    @if($publications->onFirstPage())
                    <span class="pagination-btn" aria-disabled="true">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                    @else
                    <a href="{{ $publications->previousPageUrl() }}" class="pagination-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    @endif

                    @if($start > 1)
                    <a href="{{ $publications->url(1) }}" class="pagination-btn">1</a>
                    @if($start > 2)<span class="text-[#A3A6AE] text-xs font-bold px-0.5">…</span>@endif
                    @endif

                    @for($p = $start; $p <= $end; $p++) <a href="{{ $publications->url($p) }}"
                        class="pagination-btn {{ $p == $currentPage ? 'active' : '' }}">{{ $p }}</a>
                        @endfor

                        @if($end < $lastPage) @if($end < $lastPage - 1)<span
                            class="text-[#A3A6AE] text-xs font-bold px-0.5">…</span>@endif
                            <a href="{{ $publications->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
                            @endif

                            @if($publications->hasMorePages())
                            <a href="{{ $publications->nextPageUrl() }}" class="pagination-btn">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            @else
                            <span class="pagination-btn" aria-disabled="true">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            @endif
                </div>

                @if($lastPage > 5)
                <form method="GET" action="{{ route('publikasi.browse') }}"
                    class="flex items-center gap-1.5 mt-1 flex-wrap justify-center">
                    @foreach(request()->except('page') as $key => $val)
                    @if(is_array($val))
                    @foreach($val as $v)<input type="hidden" name="{{ $key }}[]" value="{{ $v }}">@endforeach
                    @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                    @endforeach
                    <label class="text-[11px] sm:text-sm text-[#737373] font-medium whitespace-nowrap">Ke hal:</label>
                    <input type="number" name="page" min="1" max="{{ $lastPage }}" value="{{ $currentPage }}"
                        class="w-12 px-2 py-1.5 border-2 border-[#EEF0F7] rounded-xl text-xs sm:text-sm font-semibold text-center outline-none focus:border-[#FF6B18]">
                    <button type="submit"
                        class="px-3 py-1.5 bg-[#FF6B18] text-white text-xs sm:text-sm font-bold rounded-xl hover:bg-[#E64627] transition-colors">
                        Go
                    </button>
                </form>
                @endif
            </div>
            @endif

        </div>
    </div>
</section>


{{-- ✨ Scroll to Top --}}
<x-scroll-to-top />

@endsection

@push('scripts')

{{-- ✨ Scroll to Top Script --}}
<x-scroll-to-top-script />

<script>
    (function () {
    const STORAGE_KEY = 'pub_browse_view';
    const grid        = document.getElementById('browseGrid');
    const container   = document.getElementById('browseContainer');
    if (!grid || !container) return;

    const isMobile = () => window.innerWidth < 600;

    const views = {
        grid2: {
            containerClass: 'view-grid',
            getGridClass  : () => 'grid grid-cols-2 gap-3 sm:gap-4',
        },
        grid3: {
            containerClass: 'view-grid',
            getGridClass  : () => isMobile()
                ? 'grid grid-cols-2 gap-3'
                : 'grid grid-cols-3 gap-4',
        },
        list: {
            containerClass: 'view-list',
            getGridClass  : () => 'flex flex-col gap-2.5',
        },
    };

    function applyView(mode) {
        if (!views[mode]) mode = 'grid2';
        const v = views[mode];
        container.className = v.containerClass;
        grid.className      = v.getGridClass();
        ['grid2', 'grid3', 'list'].forEach(k => {
            const btn = document.getElementById('btn-' + k);
            if (btn) btn.classList.toggle('active', k === mode);
        });
        localStorage.setItem(STORAGE_KEY, mode);
    }

    window.setView = applyView;

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            const cur = localStorage.getItem(STORAGE_KEY);
            if (cur && views[cur]) applyView(cur);
        }, 150);
    });

    const saved = localStorage.getItem(STORAGE_KEY);
    applyView(saved && views[saved] ? saved : (isMobile() ? 'grid2' : 'grid3'));
})();

function toggleFilterDrawer() {
    const drawer  = document.getElementById('filterDrawer');
    const overlay = document.getElementById('filterOverlay');
    const isOpen  = drawer.classList.contains('open');
    drawer.classList.toggle('open', !isOpen);
    overlay.classList.toggle('hidden', isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
}
</script>
@endpush