@extends('layouts.app')

@section('title', 'Jelajahi Semua Publikasi - BHAYASCIENTIA')
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* Filter Sidebar Sticky */
    .filter-sidebar {
        position: sticky;
        top: 6rem;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
    }

    .filter-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .filter-sidebar::-webkit-scrollbar-track {
        background: #F8F9FC;
        border-radius: 3px;
    }

    .filter-sidebar::-webkit-scrollbar-thumb {
        background: #FF6B18;
        border-radius: 3px;
    }

    /* Card Hover Animation */
    .publication-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .publication-card:hover {
        transform: translateY(-8px);
    }

    /* View Toggle Buttons */
    .view-toggle-btn {
        transition: all 0.3s ease;
    }

    .view-toggle-btn.active {
        background: linear-gradient(135deg, #FF6B18, #E64627);
        color: white;
    }

    /* Grid Layout Transitions */
    .publications-grid {
        transition: all 0.5s ease;
    }

    /* List View Style */
    .list-view-card {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .list-view-card {
            grid-template-columns: 1fr;
        }
    }

    /* Stats Counter Animation */
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

    /* Category Badge Hover */
    .category-badge {
        transition: all 0.3s ease;
    }

    .category-badge:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
    }
</style>
@endpush

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('content')

{{-- Hero Section --}}
<section class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] relative overflow-hidden">
    {{-- Background Pattern --}}
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

    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-16 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 mb-8 text-sm text-white/80" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('publikasi.index') }}" class="transition-colors hover:text-white">Publikasi</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Jelajahi Semua</span>
        </nav>

        {{-- Header --}}
        <div class="text-white">
            <h1 class="mb-4 text-4xl font-black leading-tight md:text-5xl">
                🔍 Jelajahi Publikasi
            </h1>
            <p class="max-w-2xl mb-8 text-xl text-white/90">
                Temukan dan eksplorasi koleksi lengkap publikasi ilmiah dari berbagai bidang penelitian
            </p>

            {{-- Statistics --}}
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="p-5 border bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                    <p class="text-3xl font-black stat-number">{{ number_format($stats['total']) }}</p>
                    <p class="mt-1 text-sm text-white/80">Total Publikasi</p>
                </div>
                <div class="p-5 border bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                    <p class="text-3xl font-black stat-number">{{ number_format($stats['this_year']) }}</p>
                    <p class="mt-1 text-sm text-white/80">Publikasi 2026</p>
                </div>
                <div class="p-5 border bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                    <p class="text-3xl font-black stat-number">{{ number_format($stats['categories']) }}</p>
                    <p class="mt-1 text-sm text-white/80">Kategori</p>
                </div>
                <div class="p-5 border bg-white/10 backdrop-blur-sm rounded-2xl border-white/20">
                    <p class="text-3xl font-black stat-number">{{ number_format($stats['authors']) }}</p>
                    <p class="mt-1 text-sm text-white/80">Penulis</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Main Content --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-12">
    <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">

        {{-- LEFT: Filter Sidebar --}}
        <aside class="filter-sidebar">
            <div class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 space-y-6">

                {{-- Filter Header --}}
                <div class="flex items-center justify-between pb-4 border-b-2 border-[#EEF0F7]">
                    <h2 class="text-lg font-bold text-[#1A1A1A] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </h2>
                    @if($filterCategory || $filterYear)
                    <a href="{{ route('publikasi.browse', ['type' => $selectedType, 'sort' => $filterSort]) }}"
                        class="text-sm font-semibold text-[#FF6B18] hover:text-[#E64627] transition-colors">
                        Reset
                    </a>
                    @endif
                </div>

                {{-- Publication Type Filter --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Jenis Publikasi</h3>
                    <div class="space-y-2">
                        <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => 'all'])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl font-semibold text-sm transition-all {{ $selectedType == 'all' ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>Semua Jenis</span>
                            <span class="text-xs">{{ $stats['total'] }}</span>
                        </a>
                        @foreach($publicationTypes as $type)
                        <a href="{{ route('publikasi.browse', array_merge(request()->except('type'), ['type' => $type->slug])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl font-semibold text-sm transition-all {{ $selectedType == $type->slug ? 'bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>{{ $type->name }}</span>
                            <span class="text-xs">{{ $type->publications_count ?? 0 }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Category Filter --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Kategori</h3>
                    <div class="space-y-2 overflow-y-auto max-h-64">
                        @foreach($categories as $category)
                        <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['category' => $category->slug])) }}"
                            class="category-badge flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-all {{ $filterCategory == $category->slug ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>{{ $category->name }}</span>
                            <span class="text-xs font-semibold">{{ $category->publications_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Year Filter --}}
                <div>
                    <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Tahun Publikasi</h3>
                    <div class="space-y-2">
                        @foreach($years as $year)
                        <a href="{{ route('publikasi.browse', array_merge(request()->all(), ['year' => $year])) }}"
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-all {{ $filterYear == $year ? 'bg-[#FFF7F2] text-[#FF6B18] font-bold border-2 border-[#FF6B18]' : 'bg-[#F8F9FC] text-[#1A1A1A] hover:bg-[#FFF7F2]' }}">
                            <span>{{ $year }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </aside>

        {{-- RIGHT: Publications Grid --}}
        <div class="space-y-6">

            {{-- Toolbar --}}
            <div
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white rounded-2xl border-2 border-[#EEF0F7] p-4">

                {{-- Results Info --}}
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-[#737373]">Menampilkan</p>
                        <p class="text-lg font-bold text-[#1A1A1A]">{{ $publications->total() }} Publikasi</p>
                    </div>
                </div>

                {{-- View & Sort Controls --}}
                <div class="flex items-center gap-3">
                    {{-- View Toggle --}}
                    <div class="flex items-center gap-2 p-1 bg-[#F8F9FC] rounded-xl">
                        <button onclick="switchView('grid')"
                            class="px-3 py-2 transition-all rounded-lg view-toggle-btn active" data-view="grid">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button onclick="switchView('list')" class="px-3 py-2 transition-all rounded-lg view-toggle-btn"
                            data-view="list">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    {{-- Sort Dropdown --}}
                    <select onchange="window.location.href = this.value"
                        class="px-4 py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-sm focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 outline-none cursor-pointer">
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'latest'])) }}"
                            {{ $filterSort=='latest' ? 'selected' : '' }}>
                            Terbaru
                        </option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}"
                            {{ $filterSort=='popular' ? 'selected' : '' }}>
                            Terpopuler
                        </option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'oldest'])) }}"
                            {{ $filterSort=='oldest' ? 'selected' : '' }}>
                            Terlama
                        </option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('sort'), ['sort' => 'title'])) }}"
                            {{ $filterSort=='title' ? 'selected' : '' }}>
                            Judul A-Z
                        </option>
                    </select>

                    {{-- Items Per Page --}}
                    <select onchange="window.location.href = this.value"
                        class="px-4 py-2 border-2 border-[#EEF0F7] rounded-xl font-semibold text-sm focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 outline-none cursor-pointer">
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('per_page'), ['per_page' => 12])) }}"
                            {{ $perPage==12 ? 'selected' : '' }}>12</option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('per_page'), ['per_page' => 24])) }}"
                            {{ $perPage==24 ? 'selected' : '' }}>24</option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('per_page'), ['per_page' => 36])) }}"
                            {{ $perPage==36 ? 'selected' : '' }}>36</option>
                        <option
                            value="{{ route('publikasi.browse', array_merge(request()->except('per_page'), ['per_page' => 48])) }}"
                            {{ $perPage==48 ? 'selected' : '' }}>48</option>
                    </select>
                </div>
            </div>

            {{-- Publications Grid --}}
            <div id="publicationsContainer"
                class="grid grid-cols-1 gap-6 publications-grid md:grid-cols-2 lg:grid-cols-3">
                @foreach($formattedPublications as $publication)
                <a href="{{ $publication['detail_url'] }}"
                    class="publication-card group bg-white rounded-2xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300 overflow-hidden">

                    {{-- Cover Image --}}
                    <div class="aspect-[3/4] overflow-hidden bg-[#F8F9FC] relative">
                        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                            class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110">

                        {{-- Stats Overlay --}}
                        <div
                            class="absolute bottom-0 left-0 right-0 p-4 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/70 to-transparent group-hover:opacity-100">
                            <div class="flex items-center gap-4 text-sm text-white">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $publication['views_count'] }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    {{ $publication['downloads_count'] }}
                                </span>
                            </div>
                        </div>
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
                            class="font-bold text-lg text-[#1A1A1A] mb-2 line-clamp-2 group-hover:text-[#FF6B18] transition-colors leading-tight">
                            {{ $publication['title'] }}
                        </h3>

                        <p class="text-sm text-[#737373] mb-4 line-clamp-2">
                            {{ $publication['abstract'] }}
                        </p>

                        {{-- Authors --}}
                        <div class="flex items-center gap-2 pt-3 border-t border-[#EEF0F7]">
                            <div class="flex items-center -space-x-2">
                                @foreach($publication['authors'] as $author)
                                <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                                    class="object-cover w-8 h-8 border-2 border-white rounded-full"
                                    title="{{ $author['name'] }}">
                                @endforeach
                            </div>
                            @if($publication['total_authors'] > 3)
                            <span class="text-xs font-semibold text-[#737373]">
                                +{{ $publication['total_authors'] - 3 }} lainnya
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
                {{ $publications->links() }}
            </div>
            @endif

        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
    // Switch between grid and list view
function switchView(viewType) {
    const container = document.getElementById('publicationsContainer');
    const buttons = document.querySelectorAll('.view-toggle-btn');

    // Remove active class from all buttons
    buttons.forEach(btn => btn.classList.remove('active'));

    // Add active to clicked button
    document.querySelector(`[data-view="${viewType}"]`).classList.add('active');

    if (viewType === 'list') {
        container.classList.remove('md:grid-cols-2', 'lg:grid-cols-3');
        container.classList.add('grid-cols-1');

        // Change card style to list
        document.querySelectorAll('.publication-card').forEach(card => {
            card.classList.add('list-view-card');
        });
    } else {
        container.classList.add('md:grid-cols-2', 'lg:grid-cols-3');
        container.classList.remove('grid-cols-1');

        // Change card style to grid
        document.querySelectorAll('.publication-card').forEach(card => {
            card.classList.remove('list-view-card');
        });
    }
}
</script>
@endpush
