@extends('layouts.app')

@section('title', 'Hasil Pencarian Publikasi' . ($searchQuery ? ' - ' . $searchQuery : ''))
@section('main_class', 'pb-16')

@push('styles')
<style>
    .search-result-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .search-result-card:hover {
        transform: translateY(-4px);
    }

    .search-result-cover {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
    }

    .search-result-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
        display: block;
    }

    .search-result-card:hover .search-result-cover img {
        transform: scale(1.05);
    }

    /* Skeleton loader */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 8px;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Pagination custom */
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
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

    .pagination-btn:disabled,
    .pagination-btn[aria-disabled="true"] {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Filter badge pill */
    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: white;
        border: 1.5px solid #EEF0F7;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #1A1A1A;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: all 0.18s ease;
    }

    .filter-badge:hover {
        border-color: #FF6B18;
        color: #FF6B18;
    }

    .filter-badge .icon {
        color: #FF6B18;
        flex-shrink: 0;
    }

    /* Card status badge */
    .status-peer {
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
    }

    .status-verified {
        background: #EEF0F7;
        color: #737373;
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
// Normalize filterKeyword jadi array
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
$perPage = $publications->perPage();
$fromItem = $publications->firstItem() ?? 0;
$toItem = $publications->lastItem() ?? 0;
@endphp

{{-- Breadcrumb --}}
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">
    <nav class="flex items-center gap-2 text-sm text-[#737373]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#FF6B18] transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('publikasi.index') }}" class="hover:text-[#FF6B18] transition-colors">Publikasi</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-semibold text-[#FF6B18]">Hasil Pencarian</span>
    </nav>
</div>

{{-- Hero Header --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">
    <div
        class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-3xl p-7 md:p-10 text-white relative overflow-hidden">
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
        {{-- Decorative circles --}}
        <div class="absolute w-40 h-40 rounded-full -top-10 -right-10 bg-white/10"></div>
        <div class="absolute w-32 h-32 rounded-full -bottom-8 -left-8 bg-white/10"></div>

        <div class="relative z-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">🔍</span>
                    <h1 class="text-2xl font-bold md:text-3xl">Hasil Pencarian</h1>
                </div>

                @if($searchQuery)
                <p class="mb-3 text-base text-white/90">
                    Menampilkan hasil untuk:
                    <span class="font-bold bg-white/20 px-2 py-0.5 rounded-lg">"{{ $searchQuery }}"</span>
                </p>
                @endif

                <div class="flex flex-wrap items-center gap-2 mt-3">
                    {{-- Total hasil --}}
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ number_format($totalResults) }} Publikasi
                    </span>

                    {{-- Halaman --}}
                    @if($lastPage > 1)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        {{ $lastPage }} Halaman
                    </span>
                    @endif

                    {{-- Filter aktif --}}
                    @if($activeFilterCount > 0)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ $activeFilterCount }} Filter Aktif
                    </span>
                    @endif
                </div>
            </div>

            {{-- CTA Button --}}
            <button onclick="openPublicationSearch()"
                class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-3 bg-white text-[#FF6B18] font-bold rounded-2xl hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                Ubah Filter
            </button>
        </div>
    </div>
</section>

{{-- Toolbar --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">

    {{-- Publication Type Tabs --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('publikasi.search', array_merge(request()->except('type', 'page'), ['type' => 'all'])) }}"
            class="px-4 py-2 rounded-xl font-semibold text-sm transition-all {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md shadow-orange-200' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]' }}">
            Semua Jenis
        </a>
        @foreach($publicationTypes as $type)
        <a href="{{ route('publikasi.search', array_merge(request()->except('type', 'page'), ['type' => $type->slug])) }}"
            class="px-4 py-2 rounded-xl font-semibold text-sm transition-all {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md shadow-orange-200' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]' }}">
            {{ $type->name }}
        </a>
        @endforeach
    </div>

    {{-- Active Filters Badges --}}
    @if($activeFilterCount > 0)
    <div class="flex flex-wrap items-center gap-2 mb-5 p-4 bg-[#FFF7F2] border-2 border-[#FF6B18]/20 rounded-2xl">
        <span class="text-xs font-bold text-[#FF6B18] uppercase tracking-wide mr-1">Filter:</span>

        @if($searchQuery)
        <span class="filter-badge">
            <svg class="icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            "{{ Str::limit($searchQuery, 30) }}"
        </span>
        @endif

        @if($filterCategory)
        <span class="filter-badge">
            <svg class="icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            {{ $categories->firstWhere('slug', $filterCategory)?->name ?? ucfirst((string) $filterCategory) }}
        </span>
        @endif

        @if($filterYear)
        <span class="filter-badge">
            <svg class="icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ $filterYear }}
        </span>
        @endif

        @foreach($activeKeywords as $kwSlug)
        @php $kwName = $topKeywords->firstWhere('slug', $kwSlug)?->name ?? ucfirst((string) $kwSlug); @endphp
        <span class="filter-badge">
            <svg class="icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
            </svg>
            {{ $kwName }}
        </span>
        @endforeach

        {{-- Reset --}}
        <a href="{{ route('publikasi.search', ['type' => $selectedType, 'sort' => $filterSort]) }}"
            class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-[#FF6B18] hover:bg-white rounded-xl border-2 border-transparent hover:border-[#FF6B18] transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Hapus Filter
        </a>
    </div>
    @endif

    {{-- Sort + Info Bar --}}
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6 pb-4 border-b-2 border-[#EEF0F7]">
        {{-- Info --}}
        <div>
            <p class="text-sm text-[#737373]">
                @if($totalResults > 0)
                Menampilkan
                <span class="font-bold text-[#1A1A1A]">{{ $fromItem }}–{{ $toItem }}</span>
                dari
                <span class="font-bold text-[#1A1A1A]">{{ number_format($totalResults) }}</span>
                publikasi
                @if($lastPage > 1)
                · Halaman <span class="font-bold text-[#FF6B18]">{{ $currentPage }}</span> / {{ $lastPage }}
                @endif
                @else
                Tidak ada hasil ditemukan
                @endif
            </p>
        </div>

        {{-- Sort --}}
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-[#737373] whitespace-nowrap">Urutkan:</span>
            <select onchange="window.location.href = this.value"
                class="px-3 py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-sm focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 outline-none cursor-pointer bg-white">
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'latest'])) }}"
                    {{ $filterSort=='latest' ? 'selected' : '' }}>🕐 Terbaru</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'popular'])) }}"
                    {{ $filterSort=='popular' ? 'selected' : '' }}>🔥 Terpopuler</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'oldest'])) }}"
                    {{ $filterSort=='oldest' ? 'selected' : '' }}>📅 Terlama</option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort','page'), ['sort' => 'title'])) }}"
                    {{ $filterSort=='title' ? 'selected' : '' }}>🔤 Judul A-Z</option>
            </select>
        </div>
    </div>

</section>

{{-- Results Grid --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

    @if($searchResults->count() > 0)

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($searchResults as $publication)
        @php
        $words = array_filter(explode(' ', $publication['title']));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication['title'], 0, 2));
        }

        $firstAuthor = 'Anonymous';
        if (isset($publication['authors']) && count($publication['authors']) > 0) {
        $firstAuthor = $publication['authors'][0]['name'] ?? 'Unknown';
        }

        $placeholderParams = http_build_query([
        'initials' => $initials,
        'type' => $publication['publication_type'] ?? 'Publikasi',
        'title' => $publication['title'],
        'category' => $publication['category'] ?? 'Umum',
        'author' => $firstAuthor,
        'v' => time(),
        ]);

        $placeholderUrl = route('placeholder.cover') . '?' . $placeholderParams;
        $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);
        $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
        @endphp

        <a href="{{ $publication['detail_url'] }}"
            class="search-result-card group bg-white rounded-2xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col">

            {{-- Cover --}}
            <div class="search-result-cover" style="display: block; background-color: #F8F9FC;">
                <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy" decoding="async"
                    style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
                    onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">

                {{-- Status badge di atas cover --}}
                @if(!empty($publication['status']))
                <div class="absolute top-3 left-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold
                        {{ $publication['status'] === 'Peer-reviewed' ? 'status-peer' : 'status-verified' }}">
                        @if($publication['status'] === 'Peer-reviewed')
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        @endif
                        {{ $publication['status'] }}
                    </span>
                </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex flex-col flex-1 p-4">
                {{-- Category + Date --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span
                        class="px-2.5 py-1 bg-[#FFF7F2] text-[#FF6B18] text-[11px] font-bold rounded-full truncate max-w-[60%]">
                        {{ $publication['category'] ?? 'Umum' }}
                    </span>
                    <span class="text-[11px] text-[#A3A6AE] whitespace-nowrap flex-shrink-0">
                        {{ $publication['formatted_date'] }}
                    </span>
                </div>

                {{-- Title --}}
                <h3
                    class="font-bold text-base text-[#1A1A1A] mb-2 line-clamp-2 group-hover:text-[#FF6B18] transition-colors leading-snug flex-1">
                    {{ $publication['title'] }}
                </h3>

                {{-- Abstract --}}
                <p class="text-sm text-[#737373] mb-4 line-clamp-2 leading-relaxed">
                    {{ $publication['abstract'] ?? 'Tidak ada abstrak' }}
                </p>

                {{-- Authors + Type --}}
                <div class="flex items-center justify-between gap-2 mt-auto pt-3 border-t border-[#F0F0F0]">
                    {{-- Author avatars --}}
                    <div class="flex items-center gap-1.5">
                        <div class="flex -space-x-2">
                            @foreach(array_slice($publication['authors'], 0, 3) as $i => $author)
                            <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}" title="{{ $author['name'] }}"
                                class="object-cover border-2 border-white rounded-full w-7 h-7">
                            @endforeach
                        </div>
                        @if($publication['total_authors'] > 3)
                        <span class="text-[11px] font-semibold text-[#737373]">
                            +{{ $publication['total_authors'] - 3 }}
                        </span>
                        @elseif($publication['total_authors'] == 1)
                        <span class="text-[11px] text-[#737373] truncate max-w-[100px]">
                            {{ $publication['authors'][0]['name'] ?? '' }}
                        </span>
                        @endif
                    </div>

                    {{-- Read more arrow --}}
                    <span
                        class="flex-shrink-0 w-8 h-8 rounded-full bg-[#FFF7F2] text-[#FF6B18] flex items-center justify-center group-hover:bg-[#FF6B18] group-hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ✅ Pagination UX ditingkatkan --}}
    @if($publications->hasPages())
    <div class="flex flex-col items-center gap-4 mt-12">

        {{-- Info halaman --}}
        <p class="text-sm text-[#737373]">
            Halaman <span class="font-bold text-[#1A1A1A]">{{ $currentPage }}</span>
            dari <span class="font-bold text-[#1A1A1A]">{{ $lastPage }}</span>
            &nbsp;·&nbsp;
            <span class="font-bold text-[#FF6B18]">{{ number_format($totalResults) }}</span> total publikasi
        </p>

        {{-- Pagination links --}}
        <div class="flex items-center gap-1.5 flex-wrap justify-center">

            {{-- Prev --}}
            @if($publications->onFirstPage())
            <span class="pagination-btn" aria-disabled="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </span>
            @else
            <a href="{{ $publications->previousPageUrl() }}" class="pagination-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            @endif

            {{-- Page numbers --}}
            @php
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
            @endphp

            @if($start > 1)
            <a href="{{ $publications->url(1) }}" class="pagination-btn">1</a>
            @if($start > 2)
            <span class="pagination-btn" style="border:none;background:none;cursor:default;">…</span>
            @endif
            @endif

            @for($p = $start; $p <= $end; $p++) <a href="{{ $publications->url($p) }}"
                class="pagination-btn {{ $p == $currentPage ? 'active' : '' }}">
                {{ $p }}
                </a>
                @endfor

                @if($end < $lastPage) @if($end < $lastPage - 1) <span class="pagination-btn"
                    style="border:none;background:none;cursor:default;">…</span>
                    @endif
                    <a href="{{ $publications->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    {{-- Next --}}
                    @if($publications->hasMorePages())
                    <a href="{{ $publications->nextPageUrl() }}" class="pagination-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @else
                    <span class="pagination-btn" aria-disabled="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                    @endif

        </div>

        {{-- Jump to page (kalau > 5 halaman) --}}
        @if($lastPage > 5)
        <form method="GET" action="{{ route('publikasi.search') }}" class="flex items-center gap-2 mt-1">
            @foreach(request()->except('page') as $key => $val)
            @if(is_array($val))
            @foreach($val as $v)
            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
            @else
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
            @endforeach
            <label class="text-sm text-[#737373] font-medium">Ke halaman:</label>
            <input type="number" name="page" min="1" max="{{ $lastPage }}" value="{{ $currentPage }}"
                class="w-16 px-3 py-1.5 border-2 border-[#EEF0F7] rounded-xl text-sm font-semibold text-center outline-none focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20">
            <button type="submit"
                class="px-4 py-1.5 bg-[#FF6B18] text-white text-sm font-bold rounded-xl hover:bg-[#E64627] transition-colors">
                Go
            </button>
        </form>
        @endif

    </div>
    @endif

    @else

    {{-- Empty State --}}
    <div class="max-w-lg py-20 mx-auto text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-[#FFF7F2] rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-[#FF6B18]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h3 class="text-2xl font-bold text-[#1A1A1A] mb-2">Tidak Ada Hasil</h3>
        <p class="text-[#737373] mb-2">
            @if($searchQuery)
            Tidak ditemukan publikasi untuk <strong>"{{ $searchQuery }}"</strong>.
            @else
            Tidak ditemukan publikasi dengan filter yang dipilih.
            @endif
        </p>
        <p class="text-sm text-[#A3A6AE] mb-8">Coba kurangi filter atau gunakan kata kunci yang lebih umum.</p>

        <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('publikasi.search', ['type' => $selectedType]) }}"
                class="px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Semua Filter
            </a>
            <button onclick="openPublicationSearch()"
                class="px-6 py-3 border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Coba Pencarian Lain
            </button>
        </div>
    </div>

    @endif

</section>

@endsection