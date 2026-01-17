@extends('layouts.app')

@section('title', 'Hasil Pencarian Publikasi' . ($searchQuery ? ' - ' . $searchQuery : ''))
@section('main_class', 'pb-16')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="publikasi.library" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" />

{{-- Search/Filter Modal --}}
<x-publication-search-filter :selectedType="$selectedType" :categories="$categories" :years="$years"
    :topKeywords="$topKeywords" :filterCategory="$filterCategory" :filterYear="$filterYear"
    :filterKeyword="$filterKeyword" :filterSort="$filterSort" :searchQuery="$searchQuery" />
@endsection

@section('content')

{{-- Breadcrumb Navigation --}}
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">
    <nav class="flex items-center gap-2 text-sm text-[#737373]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#FF6B18] transition-colors">Beranda</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('publikasi.index') }}" class="hover:text-[#FF6B18] transition-colors">Publikasi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-semibold text-[#FF6B18]">Hasil Pencarian</span>
    </nav>
</div>

{{-- Search Results Header --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8">
    <div
        class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-3xl p-8 md:p-12 text-white relative overflow-hidden">
        {{-- Background Pattern --}}
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

        <div class="relative z-10">
            <h1 class="mb-3 text-3xl font-bold md:text-4xl">
                🔍 Hasil Pencarian
            </h1>

            @if($searchQuery)
            <p class="mb-4 text-lg md:text-xl text-white/90">
                Menampilkan hasil untuk: <span class="font-bold">"{{ $searchQuery }}"</span>
            </p>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $publications->total() }} Publikasi Ditemukan
                </span>

                @if($filterCategory || $filterYear || $filterKeyword)
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white/20 backdrop-blur-sm rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    {{ ($filterCategory ? 1 : 0) + ($filterYear ? 1 : 0) + ($filterKeyword ? 1 : 0) }} Filter Aktif
                </span>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Filter Bar & Active Filters --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8">

    {{-- Quick Actions --}}
    <div class="flex flex-col items-stretch justify-between gap-4 mb-6 sm:flex-row sm:items-center">

        {{-- Publication Type Filter --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('publikasi.search', array_merge(request()->except('type'), ['type' => 'all'])) }}"
                class="px-4 py-2 rounded-xl font-semibold text-sm transition-all {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18]' }}">
                Semua Jenis
            </a>
            @foreach($publicationTypes as $type)
            <a href="{{ route('publikasi.search', array_merge(request()->except('type'), ['type' => $type->slug])) }}"
                class="px-4 py-2 rounded-xl font-semibold text-sm transition-all {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white shadow-md' : 'bg-white text-[#1A1A1A] border-2 border-[#EEF0F7] hover:border-[#FF6B18]' }}">
                {{ $type->name }}
            </a>
            @endforeach
        </div>

        {{-- Modify Search Button --}}
        <button onclick="openPublicationSearch()"
            class="px-5 py-2.5 bg-white border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Ubah Filter
        </button>
    </div>

    {{-- Active Filters Display --}}
    @if($searchQuery || $filterCategory || $filterYear || $filterKeyword)
    <div class="bg-gradient-to-r from-[#FFF7F2] to-white border-2 border-[#FF6B18]/20 rounded-2xl p-5 mb-6">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex-1">
                <p class="text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Filter yang Diterapkan
                </p>

                <div class="flex flex-wrap gap-2">
                    @if($searchQuery)
                    <span
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#EEF0F7] rounded-lg text-sm shadow-sm">
                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="font-bold">"{{ Str::limit($searchQuery, 40) }}"</span>
                    </span>
                    @endif

                    @if($filterCategory)
                    <span
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#EEF0F7] rounded-lg text-sm shadow-sm">
                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $categories->firstWhere('slug', $filterCategory)?->name ?? ucfirst($filterCategory) }}
                    </span>
                    @endif

                    @if($filterYear)
                    <span
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#EEF0F7] rounded-lg text-sm shadow-sm">
                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Tahun {{ $filterYear }}
                    </span>
                    @endif

                    @if($filterKeyword)
                    <span
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#EEF0F7] rounded-lg text-sm shadow-sm">
                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        {{ $topKeywords->firstWhere('slug', $filterKeyword)?->name ?? ucfirst($filterKeyword) }}
                    </span>
                    @endif
                </div>
            </div>

            <a href="{{ route('publikasi.search', ['type' => $selectedType, 'sort' => $filterSort]) }}"
                class="px-5 py-2.5 text-sm font-bold text-[#FF6B18] hover:bg-white rounded-xl transition-all border-2 border-transparent hover:border-[#FF6B18] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reset
            </a>
        </div>
    </div>
    @endif

    {{-- Sort Bar --}}
    <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-[#EEF0F7]">
        <p class="text-sm text-[#737373]">
            Halaman {{ $publications->currentPage() }} dari {{ $publications->lastPage() }}
            <span class="font-bold text-[#1A1A1A] ml-1">({{ $publications->total() }} total)</span>
        </p>

        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-[#737373]">Urutkan:</label>
            <select onchange="window.location.href = this.value"
                class="px-4 py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-sm focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 outline-none cursor-pointer">
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort'), ['sort' => 'latest'])) }}"
                    {{ $filterSort=='latest' ? 'selected' : '' }}>
                    Terbaru
                </option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}"
                    {{ $filterSort=='popular' ? 'selected' : '' }}>
                    Terpopuler
                </option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort'), ['sort' => 'oldest'])) }}"
                    {{ $filterSort=='oldest' ? 'selected' : '' }}>
                    Terlama
                </option>
                <option
                    value="{{ route('publikasi.search', array_merge(request()->except('sort'), ['sort' => 'title'])) }}"
                    {{ $filterSort=='title' ? 'selected' : '' }}>
                    Judul A-Z
                </option>
            </select>
        </div>
    </div>

</section>

{{-- Search Results Grid --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

    @if($searchResults->count() > 0)
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($searchResults as $publication)
        <a href="{{ $publication['detail_url'] }}"
            class="group bg-white rounded-2xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300 overflow-hidden">

            {{-- Cover Image --}}
            <div class="aspect-[3/4] overflow-hidden bg-[#F8F9FC]">
                <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                    class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
            </div>

            {{-- Content --}}
            <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1 bg-[#FFF7F2] text-[#FF6B18] text-xs font-bold rounded-full">
                        {{ $publication['category'] }}
                    </span>
                    <span class="text-xs text-[#737373]">{{ $publication['formatted_date'] }}</span>
                </div>

                <h3
                    class="font-bold text-lg text-[#1A1A1A] mb-2 line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                    {{ $publication['title'] }}
                </h3>

                <p class="text-sm text-[#737373] mb-4 line-clamp-2">
                    {{ $publication['abstract'] }}
                </p>

                <div class="flex items-center gap-2">
                    @foreach($publication['authors'] as $author)
                    @if($loop->index < 2) <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                        class="w-8 h-8 rounded-full border-2 border-white {{ $loop->index > 0 ? '-ml-3' : '' }}">
                        @endif
                        @endforeach
                        @if($publication['total_authors'] > 2)
                        <span class="text-xs font-semibold text-[#737373] ml-1">
                            +{{ $publication['total_authors'] - 2 }} lainnya
                        </span>
                        @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($publications->hasPages())
    <div class="mt-12">
        {{ $publications->links('pagination::tailwind') }}
    </div>
    @endif

    @else

    {{-- Empty State --}}
    <div class="max-w-2xl py-16 mx-auto text-center">
        <div class="w-32 h-32 mx-auto mb-6 bg-[#F8F9FC] rounded-full flex items-center justify-center">
            <svg class="w-16 h-16 text-[#A3A6AE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h3 class="text-2xl font-bold text-[#1A1A1A] mb-3">Tidak Ada Hasil Ditemukan</h3>
        <p class="text-[#737373] mb-8">
            Maaf, kami tidak menemukan publikasi yang sesuai dengan pencarian Anda. <br>
            Coba gunakan kata kunci yang berbeda atau kurangi filter.
        </p>

        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('publikasi.search', ['type' => $selectedType]) }}"
                class="px-8 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Semua Filter
            </a>

            <button onclick="openPublicationSearch()"
                class="px-8 py-3 border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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