@extends('layouts.app')

@section('title', 'Trending Publikasi')
@section('main_class', 'pb-16')
@section('hide_footer', 'true')

{{-- Custom Navbar dengan Avatar --}}
@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true"
    :showCtaAlways="true" />
@endsection

@push('styles')
<style>
    /* Smooth transition for filter pills */
    .filter-pill {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .filter-pill.active {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
        transform: scale(1.02);
    }

    /* Ranking badge animation */
    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 107, 24, 0.4);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(255, 107, 24, 0);
        }
    }

    .rank-badge-top3 {
        animation: pulse-glow 2s infinite;
    }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }

    /* Mobile-optimized card hover */
    @media (hover: hover) {
        .publication-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
    }

    /* Touch feedback for mobile */
    .publication-card:active {
        transform: scale(0.98);
    }
</style>
@endpush

@section('content')

{{-- Navigation --}}
<x-publication.navigation :items="config('publication.navigation')" />

{{-- Hero Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8">

    {{-- Hero Header --}}
    <div class="mb-6 text-center sm:mb-8">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-3 sm:mb-4 leading-tight">
            🔥 Publikasi Trending
        </h1>
        <p class="text-[#737373] text-sm sm:text-base md:text-lg max-w-2xl mx-auto px-2">
            Publikasi paling populer dalam
            <span class="font-bold text-[#FF6B18]">{{ $period }} hari terakhir</span>
        </p>

        {{-- Quick Stats --}}
        @if($typeStats && count($typeStats) > 0)
        <div class="flex flex-wrap items-center justify-center gap-2 mt-3 sm:gap-3 sm:mt-4">
            @foreach($typeStats as $stat)
            <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-[#F8F9FC] rounded-full text-xs sm:text-sm">
                <span class="font-bold text-[#1A1A1A]">{{ $stat['count'] }}</span>
                <span class="text-[#737373]">{{ $stat['name'] }}</span>
            </span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Filter Section (Mobile-First) --}}
    <div class="bg-white rounded-xl sm:rounded-2xl border border-[#EEF0F7] p-4 sm:p-6 mb-4 sm:mb-6">

        {{-- Period Filter --}}
        <div class="mb-4 sm:mb-6">
            <h3 class="text-xs sm:text-sm font-bold text-[#737373] uppercase tracking-wide mb-2 sm:mb-3">
                📅 Periode Waktu
            </h3>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-3">
                <a href="{{ route('publikasi.trending', ['period' => '7', 'type' => $typeSlug]) }}"
                    class="filter-pill px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-xs sm:text-sm text-center {{ $period == '7' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    7 Hari
                </a>
                <a href="{{ route('publikasi.trending', ['period' => '30', 'type' => $typeSlug]) }}"
                    class="filter-pill px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-xs sm:text-sm text-center {{ $period == '30' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    30 Hari
                </a>
            </div>
        </div>

        {{-- Type Filter --}}
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-[#737373] uppercase tracking-wide mb-2 sm:mb-3">
                📚 Jenis Publikasi
            </h3>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-3">
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => 'all']) }}"
                    class="filter-pill px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-xs sm:text-sm text-center {{ $typeSlug == 'all' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    Semua
                </a>
                @foreach($publicationTypes as $type)
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => $type->slug]) }}"
                    class="filter-pill px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-xs sm:text-sm text-center {{ $typeSlug == $type->slug ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    {{ $type->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Trending List (Mobile-Optimized) --}}
    <div class="space-y-3 sm:space-y-4">
        @forelse($trendingPublications as $index => $publication)
        @php
        // Generate initials
        $words = array_filter(explode(' ', $publication['title']));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication['title'], 0, 2));
        }

        // Get first author
        $firstAuthor = 'Anonymous';
        if (isset($publication['authors']) && count($publication['authors']) > 0) {
        $firstAuthor = $publication['authors'][0]['name'] ?? 'Unknown';
        }

        // ✅ Generate placeholder URL dengan http_build_query
        $placeholderParams = http_build_query([
        'initials' => $initials,
        'type' => $publication['publication_type'] ?? $publication['type'] ?? 'Publikasi',
        'title' => $publication['title'],
        'category' => $publication['category'] ?? 'Umum',
        'author' => $firstAuthor,
        'v' => time(),
        ]);

        $placeholderUrl = route('placeholder.cover') . '?' . $placeholderParams;

        // Fallback eksternal
        $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);

        // Final URL
        $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
        @endphp

        <a href="{{ $publication['detail_url'] }}"
            class="publication-card group flex gap-3 sm:gap-4 bg-white rounded-xl sm:rounded-2xl border border-[#EEF0F7] p-3 sm:p-4 md:p-5 hover:shadow-xl transition-all duration-300 relative overflow-hidden">

            {{-- Background gradient for top 3 --}}
            @if($index < 3) <div
                class="absolute inset-0 bg-gradient-to-r from-[#FFF7F2] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
    </div>
    @endif

    {{-- Rank Badge (Mobile Optimized) --}}
    <div class="relative z-10 flex-shrink-0">
        @if($index < 3) <div
            class="rank-badge-top3 w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-lg sm:rounded-xl bg-gradient-to-br {{ $index == 0 ? 'from-yellow-400 to-yellow-600' : ($index == 1 ? 'from-gray-300 to-gray-500' : 'from-orange-400 to-orange-600') }} flex items-center justify-center shadow-lg">
            <span class="text-base font-bold text-white sm:text-lg md:text-xl">
                {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}
            </span>
    </div>
    @else
    <div
        class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-lg sm:rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center shadow-md">
        <span class="text-sm font-bold text-white sm:text-base md:text-lg">{{ $index + 1 }}</span>
    </div>
    @endif
    </div>

    {{-- ✅ Cover with Placeholder (FIXED WITH INLINE STYLES) --}}
    <div class="relative z-10 flex-shrink-0 w-16 h-20 sm:w-20 sm:h-28 md:w-24 md:h-32 rounded-lg shadow-md group-hover:shadow-xl transition-shadow overflow-hidden"
        style="display: block; background-color: #F8F9FC;">
        <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy" decoding="async"
            style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
            onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">

        {{-- Type badge on cover --}}
        <div
            class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 px-1.5 py-0.5 sm:px-2 sm:py-1 bg-[#FF6B18] text-white text-[10px] sm:text-xs font-bold rounded shadow z-10">
            {{ $publication['type'] ?? 'PUB' }}
        </div>
    </div>

    {{-- Content (Mobile Optimized) --}}
    <div class="relative z-10 flex-1 min-w-0">
        {{-- Title --}}
        <h3
            class="text-sm sm:text-base md:text-lg lg:text-xl font-bold text-[#1A1A1A] mb-1.5 sm:mb-2 group-hover:text-[#FF6B18] transition-colors line-clamp-2">
            {{ $publication['title'] }}
        </h3>

        {{-- Authors (Mobile Optimized) --}}
        <div class="flex items-center gap-1.5 sm:gap-2 mb-2 sm:mb-3 overflow-hidden">
            @foreach($publication['authors'] as $author)
            @if($loop->index < 2) <div class="flex items-center gap-1 sm:gap-1.5 flex-shrink-0">
                <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                    class="object-cover w-5 h-5 border-2 border-white rounded-full shadow sm:w-6 sm:h-6">
                @if($loop->first)
                <span class="text-xs sm:text-sm text-[#737373] truncate max-w-[120px] sm:max-w-none">{{ $author['name']
                    }}</span>
                @endif
        </div>
        @endif
        @endforeach
        @if($publication['total_authors'] > 1)
        <span class="text-xs sm:text-sm text-[#737373] flex-shrink-0">+{{ $publication['total_authors'] - 1 }}</span>
        @endif
    </div>

    {{-- Stats Row (Mobile Optimized) --}}
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[10px] sm:text-xs md:text-sm">
        {{-- Trending Score --}}
        <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 sm:py-1.5 bg-[#FFF7F2] rounded-lg">
            <span class="text-sm sm:text-base md:text-lg">🔥</span>
            <span class="font-bold text-[#FF6B18]">{{ number_format($publication['trending_score']) }}</span>
            <span class="hidden xs:inline text-[#737373]">poin</span>
        </div>

        {{-- Views --}}
        <span class="flex items-center gap-1 sm:gap-1.5 text-[#737373]">
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="font-semibold">{{ number_format($publication['recent_views']) }}</span>
        </span>

        {{-- Downloads --}}
        <span class="flex items-center gap-1 sm:gap-1.5 text-[#737373]">
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="font-semibold">{{ number_format($publication['recent_downloads']) }}</span>
        </span>

        {{-- Category (Hidden on very small screens) --}}
        <span class="hidden sm:inline-flex items-center gap-1 text-[#737373]">
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span class="truncate max-w-[100px]">{{ $publication['category'] ?? 'Umum' }}</span>
        </span>
    </div>
    </div>

    {{-- Arrow Icon (Hidden on mobile) --}}
    <div class="relative z-10 self-center flex-shrink-0 hidden sm:block">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#737373] group-hover:text-[#FF6B18] group-hover:translate-x-2 transition-all"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
    </a>
    @empty
    {{-- Empty State (Mobile Optimized) --}}
    <div class="bg-white rounded-xl sm:rounded-2xl border border-[#EEF0F7] p-8 sm:p-12 text-center">
        <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-3 sm:mb-4 text-[#EEF0F7]" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h3 class="text-lg sm:text-xl font-bold text-[#1A1A1A] mb-2">
            Belum Ada Publikasi Trending
        </h3>
        <p class="text-sm sm:text-base text-[#737373] mb-4 sm:mb-6 px-4">
            Belum ada aktivitas dalam periode ini untuk {{ $typeSlug == 'all' ? 'semua tipe' : 'tipe ini' }}
        </p>
        <a href="{{ route('publikasi.index') }}"
            class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
            Jelajahi Semua Publikasi
        </a>
    </div>
    @endforelse
    </div>

    {{-- Info Footer (Mobile Optimized) --}}
    @if($trendingPublications->count() > 0)
    <div class="mt-6 sm:mt-8 p-4 sm:p-6 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7]">
        <div class="flex items-start gap-2 sm:gap-3">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs sm:text-sm text-[#737373] leading-relaxed">
                <p class="font-semibold text-[#1A1A1A] mb-2">📊 Cara Perhitungan Trending Score</p>

                <div class="space-y-2">
                    <p>
                        <strong class="text-[#FF6B18]">Score = (Views × 1) + (Downloads × 2)</strong>
                    </p>

                    <p>
                        Download memiliki bobot <strong>2x lebih tinggi</strong> karena menunjukkan engagement yang
                        lebih serius.
                    </p>

                    <div class="mt-3 pt-3 border-t border-[#EEF0F7]">
                        <p class="font-semibold text-[#1A1A1A] mb-1.5">🎯 Jika Score Sama</p>
                        <p class="mb-1">Urutan ditentukan berdasarkan:</p>
                        <ol class="list-decimal list-inside mt-1 space-y-0.5 ml-2">
                            <li>Jumlah <strong>downloads</strong> tertinggi</li>
                            <li>Jumlah <strong>views</strong> tertinggi</li>
                            <li>Tanggal publikasi <strong>terbaru</strong></li>
                        </ol>
                    </div>

                    <div class="mt-3 pt-3 border-t border-[#EEF0F7] text-[10px] sm:text-xs">
                        <p class="flex items-start sm:items-center gap-1.5">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-[#FF6B18] flex-shrink-0 mt-0.5 sm:mt-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Data diperbarui setiap kali ada aktivitas baru (view/download)</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</section>

@endsection