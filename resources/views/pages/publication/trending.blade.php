@extends('layouts.app')

@section('title', 'Trending Publikasi')
@section('main_class', 'pb-16')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true"
    :showCtaAlways="true" :showSearch="false" />
@endsection

@push('styles')
<style>
    .filter-pill {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .filter-pill.active {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(255, 107, 24, 0.35);
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 107, 24, 0.35);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(255, 107, 24, 0);
        }
    }

    .rank-badge-top3 {
        animation: pulse-glow 2.2s ease-in-out infinite;
    }

    .pub-card {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            border-color 0.25s ease;
    }

    @media (hover: hover) {
        .pub-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.09);
        }
    }

    .pub-card:active {
        transform: scale(0.985);
    }

    .pub-cover-wrap {
        width: 56px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        background: #E8EAF0;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        transition: box-shadow 0.25s ease;
        display: block;
    }

    @media (min-width: 640px) {
        .pub-cover-wrap {
            width: 72px;
            height: 100px;
            border-radius: 10px;
        }
    }

    .pub-cover-wrap img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .pub-card:hover .pub-cover-wrap {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }

    .avatar-item {
        display: block;
        flex-shrink: 0;
    }

    .arrow-circle {
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .pub-card:hover .arrow-circle {
        background: #FF6B18;
        transform: translateX(2px);
    }

    .pub-card:hover .arrow-circle svg {
        color: white;
    }

    @keyframes card-in {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pub-card {
        animation: card-in 0.35s ease both;
    }
</style>
@endpush

@section('content')

<div id="top-anchor"></div>
<x-publication.navigation :items="config('publication.navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-10">

    {{-- Hero Header --}}
    <div class="mb-6 text-center sm:mb-8">
        <div
            class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FFF7F2] border border-[#FFD4B8] rounded-full text-[11px] font-black text-[#FF6B18] uppercase tracking-wider mb-3">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd"
                    d="M12.963 2.286a.75.75 0 00-1.071-.136 9.742 9.742 0 00-3.539 6.177A7.547 7.547 0 016.648 6.61a.75.75 0 00-1.152-.082A9 9 0 1015.68 4.534a7.46 7.46 0 01-2.717-2.248zM15.75 14.25a3.75 3.75 0 11-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 011.925-3.545 3.75 3.75 0 013.255 3.717z"
                    clip-rule="evenodd" />
            </svg>
            Sedang Trending
        </div>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-2 leading-tight tracking-tight">
            Publikasi Trending
        </h1>
        <p class="text-[#737373] text-sm sm:text-base max-w-xl mx-auto">
            Paling populer dalam
            <span class="font-bold text-[#FF6B18]">{{ $period }} hari terakhir</span>
            —
            <span class="font-semibold text-[#1A1A1A]">Top {{ $trendingPublications->count() }}</span>
        </p>

        @if($typeStats && count($typeStats) > 0)
        <div class="flex flex-wrap items-center justify-center gap-2 mt-3">
            @foreach($typeStats as $stat)
            <span class="px-3 py-1 bg-[#F8F9FC] border border-[#EEF0F7] rounded-full text-xs">
                <span class="font-bold text-[#1A1A1A]">{{ $stat['count'] }}</span>
                <span class="text-[#737373] ml-0.5">{{ $stat['name'] }}</span>
            </span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-4 sm:p-5 mb-5 shadow-sm">

        {{-- Period --}}
        <div class="mb-4">
            <p class="flex items-center gap-1.5 text-[10px] font-black text-[#A3A6AE] uppercase tracking-widest mb-2.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                Periode Waktu
            </p>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-2">
                @foreach([['7','7 Hari'],['30','30 Hari']] as [$val,$label])
                <a href="{{ route('publikasi.trending', ['period' => $val, 'type' => $typeSlug]) }}"
                    class="filter-pill px-4 py-2 rounded-xl font-bold text-xs sm:text-sm text-center {{ $period == $val ? 'active' : 'bg-[#F8F9FC] text-[#555] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- Type --}}
        <div>
            <p class="flex items-center gap-1.5 text-[10px] font-black text-[#A3A6AE] uppercase tracking-widest mb-2.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                Jenis Publikasi
            </p>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2">
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => 'all']) }}"
                    class="filter-pill px-4 py-2 rounded-xl font-bold text-xs sm:text-sm text-center {{ $typeSlug == 'all' ? 'active' : 'bg-[#F8F9FC] text-[#555] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    Semua
                </a>
                @foreach($publicationTypes as $type)
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => $type->slug]) }}"
                    class="filter-pill px-4 py-2 rounded-xl font-bold text-xs sm:text-sm text-center {{ $typeSlug == $type->slug ? 'active' : 'bg-[#F8F9FC] text-[#555] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    {{ $type->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Trending List --}}
    <div class="space-y-3">
        @forelse($trendingPublications as $index => $publication)
        @php
        $words = array_filter(explode(' ', $publication['title'] ?? ''));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication['title'] ?? 'UN', 0, 2));
        }

        $firstAuthor = 'Anonymous';
        if (!empty($publication['authors']) && count($publication['authors']) > 0) {
        $firstAuthor = $publication['authors'][0]['name'] ?? 'Unknown';
        }

        $publicationType = $publication['publication_type'] ?? $publication['type'] ?? 'Publikasi';

        // ✅ FIX: Hapus 'v' => time() — merusak cache placeholder
        // Gunakan placeholder_url dari controller jika ada, fallback generate tanpa time()
        $placeholderUrl = $publication['placeholder_url']
        ?? route('placeholder.cover', [
        'initials' => $initials,
        'type' => $publicationType,
        'title' => $publication['title'] ?? 'Untitled',
        'category' => $publication['category'] ?? 'Umum',
        'author' => $firstAuthor,
        // ✅ Tidak ada 'v' => time()
        ]);

        $fallbackUrl = 'https://placehold.co/300x420/E64627/white?text=' . urlencode($initials);
        $finalCoverUrl = !empty($publication['cover_url'])
        ? $publication['cover_url']
        : $placeholderUrl;

        $ranks = [
        0 => ['bg' => 'linear-gradient(135deg,#F59E0B,#D97706)', 'emoji' => '🥇'],
        1 => ['bg' => 'linear-gradient(135deg,#94A3B8,#64748B)', 'emoji' => '🥈'],
        2 => ['bg' => 'linear-gradient(135deg,#F97316,#EA580C)', 'emoji' => '🥉'],
        ];
        $rank = $ranks[$index] ?? null;
        $typeLabel = mb_strtoupper(mb_substr($publication['type'] ?? 'PUB', 0, 6));
        $cardDelay = min($index * 60, 400);

        // ✅ FIX: Gunakan key yang konsisten dari controller
        // Controller kirim: recent_views, recent_downloads, trending_score
        // views_count & download_count juga tersedia sebagai alias
        $viewsCount = (int) ($publication['recent_views'] ?? $publication['views_count'] ?? 0);
        $downloadsCount = (int) ($publication['recent_downloads'] ?? $publication['download_count'] ?? 0);
        $trendingScore = (int) ($publication['trending_score'] ?? ($viewsCount + $downloadsCount * 2));
        @endphp

        <a href="{{ $publication['detail_url'] }}"
            class="pub-card group flex gap-3 bg-white rounded-2xl border border-[#EEF0F7] p-3 sm:p-4 hover:border-[#FFD4B8] relative overflow-hidden"
            style="animation-delay: {{ $cardDelay }}ms">

            {{-- Hover glow overlay --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-[#FFF7F2] via-[#FFFAF7] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
            </div>

            {{-- Rank Badge --}}
            <div class="relative z-10 self-start flex-shrink-0" style="padding-top: 22px;">
                @if($rank)
                <div class="flex items-center justify-center w-10 h-10 rank-badge-top3 sm:w-11 sm:h-11 rounded-xl"
                    style="background: {{ $rank['bg'] }}; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <span class="text-lg leading-none">{{ $rank['emoji'] }}</span>
                </div>
                @else
                <div class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl"
                    style="background: linear-gradient(135deg,#FF6B18,#E64627); box-shadow: 0 4px 12px rgba(255,107,24,0.3);">
                    <span class="text-sm font-black text-white sm:text-base">{{ $index + 1 }}</span>
                </div>
                @endif
            </div>

            {{-- Cover + Label tipe --}}
            <div class="relative z-10 self-start flex-shrink-0">
                <div class="flex justify-end mb-1">
                    <span
                        class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-black tracking-wider text-white leading-none"
                        style="background: #FF6B18; box-shadow: 0 2px 6px rgba(255,107,24,0.4);">
                        {{ $typeLabel }}
                    </span>
                </div>
                <div class="pub-cover-wrap">
                    <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                        decoding="async"
                        onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
                </div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex-1 min-w-0 flex flex-col pt-1 gap-1.5">

                {{-- Title --}}
                <h3
                    class="text-sm sm:text-base font-bold text-[#1A1A1A] group-hover:text-[#FF6B18] transition-colors duration-200 line-clamp-2 leading-snug">
                    {{ $publication['title'] }}
                </h3>

                {{-- Authors row --}}
                <div class="flex items-center gap-2 overflow-hidden">
                    @if(!empty($publication['authors']))
                    <div class="flex items-center flex-shrink-0">
                        @foreach($publication['authors'] as $author)
                        @if($loop->index < 3) <div class="avatar-item {{ $loop->first ? '' : '-ml-2' }}"
                            style="position: relative; z-index: {{ 10 - $loop->index }};">
                            <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}" title="{{ $author['name'] }}"
                                style="width:24px; height:24px; border-radius:9999px; object-fit:cover; border:2px solid white; box-shadow: 0 1px 4px rgba(0,0,0,0.15); display:block;">
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                <div class="flex items-center flex-1 min-w-0 gap-1">
                    <svg class="w-3.5 h-3.5 text-[#C0C3CC] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="text-xs text-[#555] font-medium truncate">{{ $firstAuthor }}</span>
                    @if($publication['total_authors'] > 1)
                    <span class="flex-shrink-0 text-xs text-[#A3A6AE]">+{{ $publication['total_authors'] - 1 }}</span>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex flex-wrap items-center gap-1.5 mt-auto">

                {{-- Score --}}
                <div class="flex items-center gap-1 px-2 py-1 rounded-lg border border-[#FFD4B8] bg-[#FFF7F2]">
                    <svg class="w-3.5 h-3.5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M12.963 2.286a.75.75 0 00-1.071-.136 9.742 9.742 0 00-3.539 6.177A7.547 7.547 0 016.648 6.61a.75.75 0 00-1.152-.082A9 9 0 1015.68 4.534a7.46 7.46 0 01-2.717-2.248zM15.75 14.25a3.75 3.75 0 11-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 011.925-3.545 3.75 3.75 0 013.255 3.717z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-black text-[#FF6B18]">{{ number_format($trendingScore) }}</span>
                </div>

                {{-- Views --}}
                <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-[#F8F9FC]">
                    <svg class="w-3.5 h-3.5 text-[#737373]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{-- ✅ Pakai $viewsCount yang sudah di-resolve di atas --}}
                    <span class="text-xs font-semibold text-[#555]">{{ number_format($viewsCount) }}</span>
                </div>

                {{-- Downloads --}}
                <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-[#F8F9FC]">
                    <svg class="w-3.5 h-3.5 text-[#737373]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{-- ✅ Pakai $downloadsCount yang sudah di-resolve di atas --}}
                    <span class="text-xs font-semibold text-[#555]">{{ number_format($downloadsCount) }}</span>
                </div>

                {{-- Category --}}
                @if(!empty($publication['category']))
                <div class="hidden sm:flex items-center gap-1 px-2 py-1 rounded-lg bg-[#F8F9FC] max-w-[120px]">
                    <svg class="w-3.5 h-3.5 text-[#A3A6AE] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <span class="text-xs text-[#737373] truncate">{{ $publication['category'] }}</span>
                </div>
                @endif
            </div>
    </div>

    {{-- Arrow --}}
    <div class="relative z-10 self-center flex-shrink-0 hidden ml-1 sm:flex">
        <div class="arrow-circle w-8 h-8 rounded-full bg-[#F4F6FB] flex items-center justify-center">
            <svg class="w-4 h-4 text-[#A3A6AE] transition-colors duration-200" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </div>
    </div>

    </a>
    @empty

    {{-- Empty State --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-10 sm:p-14 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#FFF7F2] flex items-center justify-center">
            <svg class="w-8 h-8 text-[#FFD4B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-[#1A1A1A] mb-2">Belum Ada Publikasi Trending</h3>
        <p class="text-sm text-[#737373] mb-6 max-w-xs mx-auto">
            Belum ada aktivitas dalam periode ini untuk {{ $typeSlug == 'all' ? 'semua tipe' : 'tipe ini' }}
        </p>
        <a href="{{ route('publikasi.index') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-200"
            style="background: linear-gradient(135deg,#FF6B18,#E64627)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
            Jelajahi Semua Publikasi
        </a>
    </div>

    @endforelse
    </div>

    {{-- Info Footer --}}
    @if($trendingPublications->count() > 0)
    <div class="mt-6 p-4 sm:p-5 bg-[#F8F9FC] rounded-2xl border border-[#EEF0F7]">
        <div class="flex items-start gap-3">
            <div
                class="flex-shrink-0 w-8 h-8 rounded-xl bg-[#FFF7F2] border border-[#FFD4B8] flex items-center justify-center mt-0.5">
                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
            </div>
            <div class="text-xs sm:text-sm text-[#737373] leading-relaxed flex-1">
                <p class="font-bold text-[#1A1A1A] mb-2 text-sm">Cara Perhitungan Trending Score</p>
                <p class="mb-2">
                    <span class="font-black text-[#FF6B18]">Score = (Views × 1) + (Downloads × 2)</span>
                </p>
                <p class="mb-3">Download memiliki bobot <strong class="text-[#1A1A1A]">2× lebih tinggi</strong> karena
                    menunjukkan engagement yang lebih serius.</p>
                <div class="pt-3 border-t border-[#E8EAF0]">
                    <p class="font-bold text-[#1A1A1A] mb-2">Jika Score Sama</p>
                    <div class="space-y-1.5">
                        @foreach(['Downloads tertinggi','Views tertinggi','Publikasi terbaru'] as $i => $rule)
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full text-white text-[10px] font-black flex items-center justify-center flex-shrink-0"
                                style="background: linear-gradient(135deg,#FF6B18,#E64627);">{{ $i+1 }}</span>
                            <span class="text-xs text-[#555]">{{ $rule }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-[#E8EAF0] text-[11px] text-[#A3A6AE]">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Data diperbarui setiap ada aktivitas baru
                </div>
            </div>
        </div>
    </div>
    @endif

</section>

<x-scroll-to-top />

@endsection

@push('scripts')
<x-scroll-to-top-script />
@endpush