@extends('layouts.app')

@section('title', 'Trending Publikasi')
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* Smooth transition for filter pills */
    .filter-pill {
        transition: all 0.3s ease;
    }

    .filter-pill.active {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
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
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8">
    <div class="mb-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-4">
            🔥 Publikasi Trending
        </h1>
        <p class="text-[#737373] text-lg max-w-2xl mx-auto">
            Publikasi paling populer dalam <span class="font-bold text-[#FF6B18]">{{ $period }} hari terakhir</span>
        </p>

        {{-- Quick Stats --}}
        @if($typeStats && count($typeStats) > 0)
        <div class="flex flex-wrap items-center justify-center gap-3 mt-4">
            @foreach($typeStats as $stat)
            <span class="px-4 py-1.5 bg-[#F8F9FC] rounded-full text-sm">
                <span class="font-bold text-[#1A1A1A]">{{ $stat['count'] }}</span>
                <span class="text-[#737373]">{{ $stat['name'] }}</span>
            </span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Navigation --}}
    <x-publication.navigation :items="config('publication.navigation')" />

    {{-- Filter Section --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 mt-8 mb-6">

        {{-- Period Filter --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">
                📅 Periode Waktu
            </h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('publikasi.trending', ['period' => '7', 'type' => $typeSlug]) }}"
                    class="filter-pill px-6 py-2.5 rounded-lg font-semibold text-sm {{ $period == '7' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    7 Hari Terakhir
                </a>
                <a href="{{ route('publikasi.trending', ['period' => '30', 'type' => $typeSlug]) }}"
                    class="filter-pill px-6 py-2.5 rounded-lg font-semibold text-sm {{ $period == '30' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    30 Hari Terakhir
                </a>
            </div>
        </div>

        {{-- Type Filter --}}
        <div>
            <h3 class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">
                📚 Jenis Publikasi
            </h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => 'all']) }}"
                    class="filter-pill px-6 py-2.5 rounded-lg font-semibold text-sm {{ $typeSlug == 'all' ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    Semua Tipe
                </a>
                @foreach($publicationTypes as $type)
                <a href="{{ route('publikasi.trending', ['period' => $period, 'type' => $type->slug]) }}"
                    class="filter-pill px-6 py-2.5 rounded-lg font-semibold text-sm {{ $typeSlug == $type->slug ? 'active' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                    {{ $type->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Trending List --}}
    <div class="space-y-4">
        @forelse($trendingPublications as $index => $publication)
        <a href="{{ $publication['detail_url'] }}"
            class="group flex gap-4 bg-white rounded-2xl border border-[#EEF0F7] p-4 md:p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">

            {{-- Background gradient for top 3 --}}
            @if($index < 3) <div
                class="absolute inset-0 bg-gradient-to-r from-[#FFF7F2] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
    </div>
    @endif

    {{-- Rank Badge --}}
    <div class="relative z-10 flex-shrink-0">
        @if($index < 3) {{-- Top 3 badges dengan style khusus --}} <div
            class="rank-badge-top3 w-14 h-14 rounded-xl bg-gradient-to-br {{ $index == 0 ? 'from-yellow-400 to-yellow-600' : ($index == 1 ? 'from-gray-300 to-gray-500' : 'from-orange-400 to-orange-600') }} flex items-center justify-center shadow-lg">
            <span class="text-xl font-bold text-white">
                {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}
            </span>
    </div>
    @else
    {{-- Regular rank badge --}}
    <div
        class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center shadow-md">
        <span class="text-lg font-bold text-white">{{ $index + 1 }}</span>
    </div>
    @endif
    </div>

    {{-- Cover --}}
    <div class="relative z-10 flex-shrink-0">
        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
            class="object-cover w-20 transition-shadow rounded-lg shadow-md h-28 md:w-24 md:h-32 group-hover:shadow-xl">

        {{-- Type badge on cover --}}
        <div class="absolute -top-2 -right-2 px-2 py-1 bg-[#FF6B18] text-white text-xs font-bold rounded-md shadow">
            {{ $publication['type'] }}
        </div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 flex-1 min-w-0">
        <h3
            class="text-lg md:text-xl font-bold text-[#1A1A1A] mb-2 group-hover:text-[#FF6B18] transition-colors line-clamp-2">
            {{ $publication['title'] }}
        </h3>

        {{-- Authors --}}
        <div class="flex items-center gap-2 mb-3">
            @foreach($publication['authors'] as $author)
            @if($loop->index < 2) <div class="flex items-center gap-1.5">
                <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                    class="object-cover w-6 h-6 border-2 border-white rounded-full shadow">
                @if($loop->first)
                <span class="text-sm text-[#737373]">{{ $author['name'] }}</span>
                @endif
        </div>
        @endif
        @endforeach
        @if($publication['total_authors'] > 1)
        <span class="text-sm text-[#737373]">+{{ $publication['total_authors'] - 1 }} lainnya</span>
        @endif
    </div>

    {{-- Stats Row --}}
    <div class="flex flex-wrap items-center gap-4 text-xs md:text-sm">
        {{-- Trending Score --}}
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#FFF7F2] rounded-lg">
            <span class="text-lg">🔥</span>
            <span class="font-bold text-[#FF6B18]">{{ number_format($publication['trending_score']) }}</span>
            <span class="text-[#737373]">poin</span>
        </div>

        {{-- Views --}}
        <span class="flex items-center gap-1.5 text-[#737373]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="font-semibold">{{ number_format($publication['recent_views']) }}</span>
        </span>

        {{-- Downloads --}}
        <span class="flex items-center gap-1.5 text-[#737373]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="font-semibold">{{ number_format($publication['recent_downloads']) }}</span>
        </span>

        {{-- Category --}}
        <span class="hidden md:inline-flex items-center gap-1 text-[#737373]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            {{ $publication['category'] }}
        </span>
    </div>
    </div>

    {{-- Arrow Icon --}}
    <div class="relative z-10 self-center flex-shrink-0">
        <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] group-hover:translate-x-2 transition-all"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
    </a>
    @empty
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-12 text-center">
        <svg class="w-24 h-24 mx-auto mb-4 text-[#EEF0F7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h3 class="text-xl font-bold text-[#1A1A1A] mb-2">
            Belum Ada Publikasi Trending
        </h3>
        <p class="text-[#737373] mb-6">
            Belum ada aktivitas dalam periode ini untuk {{ $typeSlug == 'all' ? 'semua tipe' : 'tipe ini' }}
        </p>
        <a href="{{ route('publikasi.index') }}"
            class="inline-block px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-lg hover:shadow-lg transition-all">
            Jelajahi Semua Publikasi
        </a>
    </div>
    @endforelse
    </div>

    {{-- Info Footer --}}
    @if($trendingPublications->count() > 0)
    <div class="mt-8 p-6 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7]">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-[#737373]">
                <p class="font-semibold text-[#1A1A1A] mb-1">Cara Perhitungan Trending Score</p>
                <p><strong>Score = (Views × 1) + (Downloads × 2)</strong></p>
                <p class="mt-1">Download memiliki bobot 2x lebih tinggi karena menunjukkan engagement yang lebih serius.
                </p>
            </div>
        </div>
    </div>
    @endif
</section>

@endsection