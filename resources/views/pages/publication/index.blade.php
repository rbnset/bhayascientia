@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')
<x-publication.navigation :subItems="[
        [
            'label' => 'Browse',
            'icon' => 'assets/images/icons/3dcube.svg',
            'href' => route('publikasi'),
            'active' => request()->routeIs('publikasi') || request()->routeIs('publikasi.index'),
        ],
        [
            'label' => 'Categories',
            'icon' => 'assets/images/icons/calendar-date-range-dark.svg',
            'href' => route('publikasi.categories'),
            'active' => request()->routeIs('publikasi.categories'),
        ],
        [
            'label' => 'Trending',
            'icon' => 'assets/images/icons/user-dark.svg',
            'href' => route('publikasi.trending'),
            'active' => request()->routeIs('publikasi.trending'),
            'new' => true,
        ],
        [
            'label' => 'My Library',
            'icon' => 'assets/images/icons/star-dark.svg',
            'href' => route('publikasi.library'),
            'active' => request()->routeIs('publikasi.library'),
            'badge' => auth()->check() ? 24 : 0,
        ],
    ]" :bottomItems="[
        [
            'label' => 'Browse',
            'href' => route('publikasi'),
            'active' => request()->routeIs('publikasi') || request()->routeIs('publikasi.index'),
            'icon' => 'assets/images/icons/3dcube-white.svg',
            'iconActive' => 'assets/images/icons/3dcube.svg',
        ],
        [
            'label' => 'Categories',
            'href' => route('publikasi.categories'),
            'active' => request()->routeIs('publikasi.categories'),
            'icon' => 'assets/images/icons/grid-white.svg',
            'iconActive' => 'assets/images/icons/grid-dark.svg',
        ],
        [
            'label' => 'Trending',
            'href' => route('publikasi.trending'),
            'active' => request()->routeIs('publikasi.trending'),
            'icon' => 'assets/images/icons/fire-white.svg',
            'iconActive' => 'assets/images/icons/fire-dark.svg',
            'new' => true,
        ],
        [
            'label' => 'Library',
            'href' => route('publikasi.library'),
            'active' => request()->routeIs('publikasi.library'),
            'icon' => 'assets/images/icons/book-white.svg',
            'iconActive' => 'assets/images/icons/book-dark.svg',
            'badge' => auth()->check() ? 24 : 0,
        ],
    ]" />

<x-hero.publication />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Quick Search & Filter Bar --}}
    <div class="mb-8">
        <div class="flex gap-3">
            {{-- Search Input --}}
            <form action="{{ route('publikasi') }}" method="GET" class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari publikasi berdasarkan judul, penulis, atau kata kunci..."
                        class="w-full px-5 py-4 pl-12 text-sm transition-all duration-200 bg-white border rounded-2xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
                    <svg class="absolute w-5 h-5 text-[#737373] left-4 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>

            {{-- Advanced Filter Button --}}
            <button type="button" onclick="toggleFilterModal()"
                class="flex items-center gap-2 px-6 py-4 font-bold text-white transition-all duration-200 rounded-2xl bg-[#FF6B18] hover:-translate-y-[1px] hover:shadow-[0_10px_20px_0_#FF6B1880] shrink-0 focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:ring-offset-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <span class="hidden sm:inline">Filter</span>
            </button>
        </div>
    </div>

    {{-- Active Filters Display --}}
    @if(request()->hasAny(['search', 'category', 'year']))
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <span class="text-sm font-semibold text-[#737373]">Filter aktif:</span>

        @if(request('search'))
        <span
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-full bg-[#FFF7F2] text-[#FF6B18]">
            "{{ request('search') }}"
            <a href="{{ route('publikasi', request()->except('search')) }}"
                class="p-0.5 transition-colors rounded-full hover:bg-[#FF6B18] hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </span>
        @endif

        <a href="{{ route('publikasi') }}" class="text-sm font-semibold text-[#FF6B18] hover:underline">
            Hapus semua filter
        </a>
    </div>
    @endif

    {{-- Stats & Sort --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-[#737373]">
            Menampilkan <span class="font-semibold text-[#1A1A1A]">1-12</span> dari
            <span class="font-semibold text-[#1A1A1A]">248</span> publikasi
        </p>

        <select onchange="window.location.href=this.value"
            class="px-4 py-2 text-sm font-medium bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
            <option value="{{ route('publikasi', array_merge(request()->except('sort'), ['sort' => 'latest'])) }}" {{
                request('sort', 'latest' )=='latest' ? 'selected' : '' }}>
                Terbaru
            </option>
            <option value="{{ route('publikasi', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}" {{
                request('sort')=='popular' ? 'selected' : '' }}>
                Terpopuler
            </option>
            <option value="{{ route('publikasi', array_merge(request()->except('sort'), ['sort' => 'title'])) }}" {{
                request('sort')=='title' ? 'selected' : '' }}>
                Judul (A-Z)
            </option>
        </select>
    </div>

    {{-- Publications Grid --}}
    <div class="grid gap-6 mb-10 sm:grid-cols-2 lg:grid-cols-3">
        @for($i = 1; $i
        <= 12; $i++) <x-publication-card :id="$i" />
        @endfor
    </div>

    {{-- Pagination --}}
    <x-pagination />

</section>

{{-- Filter Modal --}}
<x-filter-modal />

@endsection
