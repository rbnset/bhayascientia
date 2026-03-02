@extends('layouts.app')

@section('title', 'Hasil Pencarian Publikasi' . ($searchQuery ? ' - ' . $searchQuery : ''))
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* ═══════════════════════════════════════
       BASE CARD
    ═══════════════════════════════════════ */
    .search-result-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .search-result-cover {
        position: relative;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
        flex-shrink: 0;
    }

    /* ═══════════════════════════════════════
       GRID MODE
    ═══════════════════════════════════════ */
    .view-grid .search-result-card:hover {
        transform: translateY(-4px);
    }

    .view-grid .search-result-cover {
        aspect-ratio: 3/4;
        width: 100%;
    }

    .view-grid .search-result-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
        display: block;
    }

    .view-grid .search-result-card:hover .search-result-cover img {
        transform: scale(1.05);
    }

    /* ═══════════════════════════════════════
       LIST MODE
    ═══════════════════════════════════════ */
    .view-list .search-result-card {
        flex-direction: row !important;
        align-items: stretch;
        min-height: 0;
    }

    .view-list .search-result-card:hover {
        transform: translateY(-2px);
    }

    /* Cover di list mode */
    .view-list .search-result-cover {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        aspect-ratio: 3/4;
        border-radius: 0;
    }

    @media (min-width: 400px) {
        .view-list .search-result-cover {
            width: 95px;
            min-width: 95px;
            max-width: 95px;
        }
    }

    @media (min-width: 600px) {
        .view-list .search-result-cover {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
        }
    }

    .view-list .search-result-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    /* Content area di list mode */
    .view-list .card-content {
        padding: 10px 11px !important;
        min-width: 0;
        /* penting: cegah overflow flex child */
        flex: 1 1 0%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    @media (min-width: 400px) {
        .view-list .card-content {
            padding: 12px 13px !important;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-content {
            padding: 14px 16px !important;
        }
    }

    /* Judul di list mode — tidak terpotong aneh */
    .view-list .card-title {
        font-size: 0.75rem !important;
        /* 12px */
        line-height: 1.35 !important;
        -webkit-line-clamp: 3 !important;
        display: -webkit-box !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
    }

    @media (min-width: 400px) {
        .view-list .card-title {
            font-size: 0.8125rem !important;
            /* 13px */
            -webkit-line-clamp: 3 !important;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-title {
            font-size: 0.9375rem !important;
            /* 15px */
            -webkit-line-clamp: 4 !important;
        }
    }

    /* Meta row (category + date) di list mode */
    .view-list .card-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-bottom: 5px;
    }

    .view-list .card-meta .cat-badge {
        font-size: 9px !important;
        padding: 1px 7px !important;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .view-list .card-meta .date-text {
        font-size: 9px !important;
        white-space: nowrap;
        flex-shrink: 0;
    }

    @media (min-width: 400px) {
        .view-list .card-meta .cat-badge {
            font-size: 10px !important;
        }

        .view-list .card-meta .date-text {
            font-size: 10px !important;
        }
    }

    /* Author row di list mode */
    .view-list .card-authors {
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid #F0F0F0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4px;
    }

    .view-list .card-authors .author-name {
        font-size: 9px !important;
        max-width: 80px;
    }

    @media (min-width: 400px) {
        .view-list .card-authors .author-name {
            font-size: 10px !important;
            max-width: 100px;
        }
    }

    @media (min-width: 600px) {
        .view-list .card-authors .author-name {
            font-size: 11px !important;
            max-width: 140px;
        }
    }

    /* Sembunyikan arrow di list mode ukuran sangat kecil */
    @media (max-width: 349px) {
        .view-list .card-arrow {
            display: none !important;
        }
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
       FILTER BADGE
    ═══════════════════════════════════════ */
    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 9px;
        background: white;
        border: 1.5px solid #EEF0F7;
        border-radius: 9999px;
        font-size: 0.6875rem;
        /* 11px */
        font-weight: 600;
        color: #1A1A1A;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        white-space: nowrap;
    }

    .filter-badge .icon {
        color: #FF6B18;
        flex-shrink: 0;
    }

    /* ═══════════════════════════════════════
       HORIZONTAL SCROLL HELPER
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
       VIEW TOGGLE BUTTON
    ═══════════════════════════════════════ */
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
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
        border-radius: 7px;
    }

    /* ═══════════════════════════════════════
       GLOBAL: pastikan tidak ada overflow
    ═══════════════════════════════════════ */
    @media (max-width: 400px) {

        /* Hero stat chips lebih kecil */
        .hero-chip {
            padding: 4px 8px !important;
            font-size: 0.6875rem !important;
        }

        /* Sort select lebih kecil */
        .sort-select {
            max-width: 110px !important;
            font-size: 0.6875rem !important;
        }

        /* Breadcrumb lebih kecil */
        .breadcrumb-nav {
            font-size: 0.6875rem !important;
        }
    }

    @media (max-width: 320px) {
        .hero-chip {
            padding: 3px 7px !important;
            font-size: 0.625rem !important;
        }

        .sort-select {
            max-width: 95px !important;
            font-size: 0.625rem !important;
        }
    }
</style>
@endpush

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
<x-publication-search-filter :selectedType="$selectedType" :categories="$categories" :years="$years"
    :topKeywords="$topKeywords" :filterCategory="$filterCategory" :filterYear="$filterYear"
    :filterKeyword="$filterKeyword" :filterSort="$filterSort" :searchQuery="$searchQuery" />
@endsection

@section('content')

@php
$activeKeywords = collect(
is_array($filterKeyword)
? array_filter($filterKeyword)
: array_filter([$filterKeyword ?? ''])
);
$activeFilterCount = ($searchQuery ? 1 : 0)
+ ($filterCategory ? 1 : 0)
+ ($filterYear ? 1 : 0)
+ $activeKeywords->count();
$totalResults = $publications->total();
$currentPage = $publications->currentPage();
$lastPage = $publications->lastPage();
$fromItem = $publications->firstItem() ?? 0;
$toItem = $publications->lastItem() ?? 0;
@endphp

{{-- Breadcrumb --}}
<div class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-4">
    <nav class="breadcrumb-nav flex items-center gap-1 text-[11px] sm:text-sm text-[#737373] overflow-hidden"
        aria-label="Breadcrumb">
        <a href="{{ route('home') }}"
            class="hover:text-[#FF6B18] transition-colors truncate shrink-0 max-w-[60px] sm:max-w-none">Beranda</a>
        <svg class="flex-shrink-0 w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('publikasi.index') }}"
            class="hover:text-[#FF6B18] transition-colors truncate shrink-0 max-w-[70px] sm:max-w-none">Publikasi</a>
        <svg class="flex-shrink-0 w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-semibold text-[#FF6B18] truncate">Hasil Pencarian</span>
    </nav>
</div>

{{-- Hero Header --}}
<section class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-3">
    <div
        class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-2xl p-4 sm:p-7 md:p-10 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        <div class="absolute rounded-full w-28 h-28 -top-6 -right-6 bg-white/10"></div>
        <div class="absolute w-20 h-20 rounded-full -bottom-5 -left-5 bg-white/10"></div>

        <div class="relative z-10 flex flex-col gap-3">
            {{-- Title row --}}
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="flex-shrink-0 text-lg sm:text-2xl">🔍</span>
                        <h1 class="text-lg font-bold leading-tight truncate sm:text-2xl md:text-3xl">
                            Hasil Pencarian
                        </h1>
                    </div>
                    @if($searchQuery)
                    <p class="text-xs leading-relaxed break-all sm:text-sm text-white/90">
                        Untuk: <span class="font-bold bg-white/20 px-1.5 py-0.5 rounded-md">"{{ Str::limit($searchQuery,
                            40) }}"</span>
                    </p>
                    @endif
                </div>
                {{-- Ubah Filter — always visible, compact at small sizes --}}
                <button onclick="openPublicationSearch()"
                    class="flex-shrink-0 self-start inline-flex items-center gap-1.5 px-3 py-2 sm:px-5 sm:py-3 bg-white text-[#FF6B18] font-bold rounded-xl hover:shadow-lg transition-all text-xs sm:text-sm whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span class="hidden xs:inline sm:inline">Ubah</span> Filter
                </button>
            </div>

            {{-- Stats chips --}}
            <div class="h-scroll">
                <span
                    class="hero-chip inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] sm:text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ number_format($totalResults) }} Publikasi
                </span>
                @if($lastPage > 1)
                <span
                    class="hero-chip inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] sm:text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    {{ $lastPage }} Hal.
                </span>
                @endif
                @if($activeFilterCount > 0)
                <span
                    class="hero-chip inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] sm:text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    {{ $activeFilterCount }} Filter
                </span>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Toolbar --}}
<section class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-4">

    {{-- Type Tabs --}}
    <div class="mb-3 h-scroll">
        <a href="{{ route('publikasi.search', array_merge(request()->except('type','page'), ['type' => 'all'])) }}"
            class="px-3 py-1.5 rounded-xl font-semibold text-[11px] sm:text-sm transition-all
            {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]' }}">
            Semua
        </a>
        @foreach($publicationTypes as $type)
        <a href="{{ route('publikasi.search', array_merge(request()->except('type','page'), ['type' => $type->slug])) }}"
            class="px-3 py-1.5 rounded-xl font-semibold text-[11px] sm:text-sm transition-all
            {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]' }}">
            {{ $type->name }}
        </a>
        @endforeach
    </div>

    {{-- Active Filters --}}
    @if($activeFilterCount > 0)
    <div class="mb-3 p-2.5 sm:p-4 bg-[#FFF7F2] border-2 border-[#FF6B18]/20 rounded-xl">
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[10px] sm:text-xs font-bold text-[#FF6B18] uppercase tracking-wide">Filter Aktif</span>
            <a href="{{ route('publikasi.search', ['type' => $selectedType, 'sort' => $filterSort]) }}"
                class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold text-[#FF6B18] hover:underline flex-shrink-0">
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hapus
            </a>
        </div>
        <div class="h-scroll">
            @if($searchQuery)
            <span class="filter-badge">
                <svg class="icon w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                "{{ Str::limit($searchQuery, 20) }}"
            </span>
            @endif
            @if($filterCategory)
            <span class="filter-badge">
                <svg class="icon w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ $categories->firstWhere('slug', $filterCategory)?->name ?? ucfirst((string) $filterCategory) }}
            </span>
            @endif
            @if($filterYear)
            <span class="filter-badge">
                <svg class="icon w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $filterYear }}
            </span>
            @endif
            @foreach($activeKeywords as $kwSlug)
            @php $kwName = $topKeywords->firstWhere('slug', $kwSlug)?->name ?? ucfirst((string) $kwSlug); @endphp
            <span class="filter-badge">
                <svg class="icon w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
                {{ $kwName }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Sort + Info + View Toggle --}}
    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b-2 border-[#EEF0F7]">

        {{-- Info --}}
        <p class="text-[10px] sm:text-sm text-[#737373] leading-snug min-w-0 flex-1 truncate">
            @if($totalResults > 0)
            <span class="font-bold text-[#1A1A1A]">{{ $fromItem }}–{{ $toItem }}</span>/<span
                class="font-bold text-[#1A1A1A]">{{ number_format($totalResults) }}</span>
            @if($lastPage > 1)
            <span class="text-[#A3A6AE]"> · </span><span class="font-bold text-[#FF6B18]">{{ $currentPage }}</span><span
                class="text-[#A3A6AE]">/{{ $lastPage }}</span>
            @endif
            @else
            Tidak ada hasil
            @endif
        </p>

        <div class="flex items-center gap-1.5 flex-shrink-0">
            {{-- Sort select --}}
            <select onchange="window.location.href = this.value"
                class="sort-select px-2 py-1.5 border-2 border-[#EEF0F7] rounded-xl font-semibold text-[10px] sm:text-sm focus:border-[#FF6B18] outline-none cursor-pointer bg-white max-w-[100px] sm:max-w-none">
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'latest'])) }}"
                    {{ $filterSort=='latest' ? 'selected' : '' }}>🕐 Terbaru</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'popular'])) }}"
                    {{ $filterSort=='popular' ? 'selected' : '' }}>🔥 Populer</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'oldest'])) }}"
                    {{ $filterSort=='oldest' ? 'selected' : '' }}>📅 Terlama</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'title'])) }}"
                    {{ $filterSort=='title' ? 'selected' : '' }}>🔤 A-Z</option>
            </select>

            {{-- View Toggle --}}
            <div class="flex items-center gap-0.5 border-2 border-[#EEF0F7] rounded-xl p-0.5 bg-white flex-shrink-0">
                <button type="button" id="btn-grid2" onclick="setView('grid2')" class="view-btn" title="2 Kolom">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <rect x="2" y="2" width="7" height="7" rx="1.5" />
                        <rect x="11" y="2" width="7" height="7" rx="1.5" />
                        <rect x="2" y="11" width="7" height="7" rx="1.5" />
                        <rect x="11" y="11" width="7" height="7" rx="1.5" />
                    </svg>
                </button>
                <button type="button" id="btn-grid3" onclick="setView('grid3')" class="view-btn" title="3 Kolom">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <rect x="1" y="2" width="5" height="7" rx="1" />
                        <rect x="7.5" y="2" width="5" height="7" rx="1" />
                        <rect x="14" y="2" width="5" height="7" rx="1" />
                        <rect x="1" y="11" width="5" height="7" rx="1" />
                        <rect x="7.5" y="11" width="5" height="7" rx="1" />
                        <rect x="14" y="11" width="5" height="7" rx="1" />
                    </svg>
                </button>
                <button type="button" id="btn-list" onclick="setView('list')" class="view-btn" title="Mode Baris">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

</section>

{{-- Results --}}
<section class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

    @if($searchResults->count() > 0)

    <div id="resultsContainer" class="view-grid">
        <div id="resultsGrid" class="grid grid-cols-2 gap-3">

            @foreach($searchResults as $publication)
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
                class="search-result-card group bg-white rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col">

                {{-- Cover --}}
                <div class="search-result-cover" style="display:block;background-color:#F8F9FC;">
                    <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                        decoding="async"
                        style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;opacity:1!important;visibility:visible!important;"
                        onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
                </div>

                {{-- Content --}}
                <div class="card-content flex flex-col flex-1 p-2 sm:p-3.5">

                    {{-- Meta: Category + Date --}}
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
                    <h3 class="card-title font-bold text-[12px] sm:text-sm md:text-base text-[#1A1A1A] line-clamp-3 group-hover:text-[#FF6B18] transition-colors leading-snug flex-1 mb-2"
                        style="overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;word-break:break-word;">
                        {{ $publication['title'] }}
                    </h3>

                    {{-- Authors --}}
                    <div
                        class="card-authors flex items-center justify-between gap-1 mt-auto pt-2 border-t border-[#F0F0F0]">
                        <div class="flex items-center min-w-0 gap-1 overflow-hidden">
                            <div class="flex -space-x-1.5 flex-shrink-0">
                                @foreach(array_slice($publication['authors'], 0, 2) as $author)
                                <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                                    title="{{ $author['name'] }}"
                                    class="object-cover w-5 h-5 border-2 border-white rounded-full sm:w-6 sm:h-6">
                                @endforeach
                            </div>
                            <span class="author-name text-[10px] sm:text-[11px] text-[#737373] truncate">
                                @if($publication['total_authors'] >= 1)
                                {{ Str::limit($publication['authors'][0]['name'] ?? '', 12) }}
                                @if($publication['total_authors'] > 1)
                                <span class="text-[#A3A6AE]">+{{ $publication['total_authors'] - 1 }}</span>
                                @endif
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
    <div class="flex flex-col items-center gap-3 mt-8">

        <p class="text-[11px] sm:text-sm text-[#737373] text-center">
            Hal. <span class="font-bold text-[#1A1A1A]">{{ $currentPage }}</span> /
            <span class="font-bold text-[#1A1A1A]">{{ $lastPage }}</span>
            &nbsp;·&nbsp;
            <span class="font-bold text-[#FF6B18]">{{ number_format($totalResults) }}</span> publikasi
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

            @php $start = max(1, $currentPage - 2); $end = min($lastPage, $currentPage + 2); @endphp

            @if($start > 1)
            <a href="{{ $publications->url(1) }}" class="pagination-btn">1</a>
            @if($start > 2)<span class="text-[#A3A6AE] text-xs font-bold px-0.5">…</span>@endif
            @endif

            @for($p = $start; $p <= $end; $p++) <a href="{{ $publications->url($p) }}"
                class="pagination-btn {{ $p == $currentPage ? 'active' : '' }}">{{ $p }}</a>
                @endfor

                @if($end < $lastPage) @if($end < $lastPage - 1)<span class="text-[#A3A6AE] text-xs font-bold px-0.5">
                    …</span>@endif
                    <a href="{{ $publications->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    @if($publications->hasMorePages())
                    <a href="{{ $publications->nextPageUrl() }}" class="pagination-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @else
                    <span class="pagination-btn" aria-disabled="true">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                    @endif
        </div>

        @if($lastPage > 5)
        <form method="GET" action="{{ route('publikasi.search') }}"
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

    @else

    {{-- Empty State --}}
    <div class="max-w-xs px-2 mx-auto text-center sm:max-w-lg py-14">
        <div class="w-16 h-16 sm:w-24 sm:h-24 mx-auto mb-4 bg-[#FFF7F2] rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 sm:w-12 sm:h-12 text-[#FF6B18]/40" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-lg sm:text-2xl font-bold text-[#1A1A1A] mb-2">Tidak Ada Hasil</h3>
        <p class="text-xs sm:text-sm text-[#737373] mb-1 break-words">
            @if($searchQuery)
            Tidak ditemukan untuk <strong>"{{ Str::limit($searchQuery, 25) }}"</strong>.
            @else
            Tidak ditemukan publikasi dengan filter yang dipilih.
            @endif
        </p>
        <p class="text-[11px] text-[#A3A6AE] mb-6">Coba kurangi filter atau gunakan kata kunci yang lebih umum.</p>
        <div class="flex flex-col items-stretch gap-2.5">
            <a href="{{ route('publikasi.search', ['type' => $selectedType]) }}"
                class="px-4 py-2.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Filter
            </a>
            <button onclick="openPublicationSearch()"
                class="px-4 py-2.5 border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all flex items-center justify-center gap-2 text-xs sm:text-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Coba Pencarian Lain
            </button>
        </div>
    </div>

    @endif

</section>

@push('scripts')
<script>
    (function () {
    const STORAGE_KEY = 'pub_search_view';
    const grid        = document.getElementById('resultsGrid');
    const container   = document.getElementById('resultsContainer');
    if (!grid || !container) return;

    const isMobile = () => window.innerWidth < 600;

    const views = {
        grid2: {
            containerClass: 'view-grid',
            getGridClass  : () => 'grid grid-cols-2 gap-3',
        },
        grid3: {
            containerClass: 'view-grid',
            // <600px → 2 kolom, ≥600px → 3 kolom
            getGridClass  : () => isMobile()
                ? 'grid grid-cols-2 gap-3'
                : 'grid grid-cols-3 gap-4 lg:gap-5',
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

    // Re-evaluate saat resize (terutama grid3 yg breakpoint di 600px)
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            const cur = localStorage.getItem(STORAGE_KEY) || 'auto';
            if (views[cur]) applyView(cur);
        }, 150);
    });

    // Init
    const saved = localStorage.getItem(STORAGE_KEY);
    applyView(saved && views[saved] ? saved : (isMobile() ? 'grid2' : 'grid3'));
})();
</script>
@endpush

@endsection