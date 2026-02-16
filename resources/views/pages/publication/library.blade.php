@extends('layouts.app')

@section('title', 'My Library')
@section('main_class', 'mt-0 pb-32 sm:pb-20')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true"
    :showCtaAlways="true" />
@endsection

@push('styles')
<style>
    * {
        -webkit-tap-highlight-color: transparent;
    }

    .stat-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card.active {
        transform: scale(1.02);
        border-color: #FF6B18;
        box-shadow: 0 8px 24px rgba(255, 107, 24, 0.25);
    }

    .stat-card.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #FF6B18, #E64627);
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(-100%);
        }

        to {
            transform: translateX(0);
        }
    }

    .stat-card.active {
        animation: gentlePulse 2s ease-in-out infinite;
    }

    @keyframes gentlePulse {

        0%,
        100% {
            box-shadow: 0 8px 24px rgba(255, 107, 24, 0.25);
        }

        50% {
            box-shadow: 0 12px 32px rgba(255, 107, 24, 0.35);
        }
    }

    @media (hover: hover) {
        .stat-card:hover:not(.active) {
            transform: translateY(-4px);
        }
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes slideOut {
        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }

    @keyframes scaleOut {
        to {
            transform: scale(0.8);
            opacity: 0;
        }
    }

    html {
        scroll-behavior: smooth;
        scroll-padding-bottom: 100px;
    }

    .filter-dropdown {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .filter-dropdown.active {
        max-height: 400px;
    }

    /* ✅ Cover Image Styles - FIXED */
    .library-cover {
        position: relative;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
        /* ✅ ADDED */
    }

    .library-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        /* ✅ ADDED */
    }
</style>
@endpush

@section('content')

{{-- Navigation --}}
<x-publication.navigation :items="config('publication.navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8 lg:mt-10">

    {{-- Header --}}
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-2 flex items-center gap-2">
            <span class="text-2xl sm:text-3xl">📚</span>
            <span>My Library</span>
        </h1>
        <p class="text-sm sm:text-base text-[#737373]">
            Kelola koleksi publikasi favorit, riwayat bacaan, dan simpanan Anda
        </p>
    </div>

    @if(isset($requiresLogin) && $requiresLogin)
    {{-- LOGIN GATE --}}
    <div class="relative grid grid-cols-1 gap-3 mb-6 sm:gap-4 sm:mb-8 sm:grid-cols-3">
        <div class="absolute inset-0 backdrop-blur-[2px] bg-white/60 z-10 rounded-xl sm:rounded-2xl"></div>

        <div class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">--</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1">Favorites</h3>
            <p class="text-xs sm:text-sm text-[#737373]">Publikasi favorit Anda</p>
        </div>

        <div class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">--</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1">Reading History</h3>
            <p class="text-xs sm:text-sm text-[#737373]">Total publikasi dibaca</p>
        </div>

        <div class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">--</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1">Saved</h3>
            <p class="text-xs sm:text-sm text-[#737373]">Disimpan untuk nanti</p>
        </div>
    </div>

    <div
        class="bg-gradient-to-br from-[#FF6B18]/5 via-white to-[#E64627]/5 rounded-2xl sm:rounded-3xl border-2 border-dashed border-[#FF6B18]/30 p-6 sm:p-8 md:p-12 text-center">
        <div
            class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-4 sm:mb-6 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white sm:w-10 sm:h-10 md:w-12 md:h-12" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-3 sm:mb-4">
            Buka Akses Library Anda
        </h2>

        <p class="text-sm sm:text-base md:text-lg text-[#737373] mb-6 sm:mb-8 max-w-2xl mx-auto px-2">
            Login untuk menyimpan publikasi favorit, melacak riwayat bacaan, dan mengelola koleksi pribadi Anda
        </p>

        <div class="grid max-w-3xl grid-cols-1 gap-3 mx-auto mb-6 text-left sm:gap-4 sm:mb-8 sm:grid-cols-3">
            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1 text-sm sm:text-base">Favorites</h3>
                <p class="text-xs sm:text-sm text-[#737373]">Tandai publikasi penting</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1 text-sm sm:text-base">History</h3>
                <p class="text-xs sm:text-sm text-[#737373]">Lacak riwayat bacaan</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1 text-sm sm:text-base">Saved</h3>
                <p class="text-xs sm:text-sm text-[#737373]">Simpan untuk nanti</p>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
            <a href="{{ route('login') }}"
                class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-xl hover:shadow-[0_10px_30px_0_rgba(255,107,24,0.4)] transition-all duration-300 hover:-translate-y-1 inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login Sekarang
            </a>
            <a href="{{ route('register') }}"
                class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-white border-2 border-[#FF6B18] text-[#FF6B18] text-sm sm:text-base font-bold rounded-xl hover:bg-[#FFF7F2] transition-all duration-300 inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Daftar Gratis
            </a>
        </div>

        <p class="text-xs sm:text-sm text-[#737373] mt-4 sm:mt-6 px-4">
            Gratis, tanpa biaya • Akses penuh ke semua fitur • Sinkronisasi multi-device
        </p>
    </div>

    @else
    {{-- LOGGED IN Content --}}

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-3 mb-6 sm:gap-4 sm:mb-8 sm:grid-cols-3">

        <a href="{{ route('publikasi.library', ['tab' => 'favorites']) }}"
            class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 transition-all duration-300 {{ $activeTab === 'favorites' ? 'active border-[#FF6B18]' : 'border-[#EEF0F7] hover:shadow-lg' }}">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 {{ $activeTab === 'favorites' ? 'bg-gradient-to-br from-[#FF6B18] to-[#E64627]' : 'bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10' }} rounded-full flex items-center justify-center transition-all duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $activeTab === 'favorites' ? 'text-white' : 'text-[#FF6B18]' }}"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ $stats['favorites'] }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1 flex items-center justify-between">
                Favorites
                @if($activeTab === 'favorites')
                <span class="text-xs bg-[#FF6B18] text-white px-2 py-0.5 rounded-full">Active</span>
                @endif
            </h3>
            <p class="text-xs sm:text-sm text-[#737373]">Publikasi favorit Anda</p>
        </a>

        <a href="{{ route('publikasi.library', ['tab' => 'history']) }}"
            class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 transition-all duration-300 {{ $activeTab === 'history' ? 'active border-[#FF6B18]' : 'border-[#EEF0F7] hover:shadow-lg' }}">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 {{ $activeTab === 'history' ? 'bg-gradient-to-br from-[#FF6B18] to-[#E64627]' : 'bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10' }} rounded-full flex items-center justify-center transition-all duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $activeTab === 'history' ? 'text-white' : 'text-[#FF6B18]' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ $stats['history'] }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1 flex items-center justify-between">
                Reading History
                @if($activeTab === 'history')
                <span class="text-xs bg-[#FF6B18] text-white px-2 py-0.5 rounded-full">Active</span>
                @endif
            </h3>
            <p class="text-xs sm:text-sm text-[#737373]">Total publikasi dibaca</p>
        </a>

        <a href="{{ route('publikasi.library', ['tab' => 'saved']) }}"
            class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 transition-all duration-300 {{ $activeTab === 'saved' ? 'active border-[#FF6B18]' : 'border-[#EEF0F7] hover:shadow-lg' }}">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 {{ $activeTab === 'saved' ? 'bg-gradient-to-br from-[#FF6B18] to-[#E64627]' : 'bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10' }} rounded-full flex items-center justify-center transition-all duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $activeTab === 'saved' ? 'text-white' : 'text-[#FF6B18]' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ $stats['saved'] }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1 flex items-center justify-between">
                Saved
                @if($activeTab === 'saved')
                <span class="text-xs bg-[#FF6B18] text-white px-2 py-0.5 rounded-full">Active</span>
                @endif
            </h3>
            <p class="text-xs sm:text-sm text-[#737373]">Disimpan untuk nanti</p>
        </a>
    </div>

    {{-- Tabs Container --}}
    <div class="bg-white rounded-xl sm:rounded-2xl border border-[#EEF0F7] overflow-hidden">
        {{-- Tab Headers --}}
        <div class="border-b border-[#EEF0F7]">
            <div class="flex overflow-x-auto scrollbar-hide">
                <a href="{{ route('publikasi.library', ['tab' => 'favorites']) }}"
                    @class([ 'px-4 sm:px-6 py-3 sm:py-4 font-semibold text-xs sm:text-sm whitespace-nowrap border-b-2 transition-colors flex-shrink-0'
                    , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'favorites',
                    'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'favorites',
                    ])>
                    ⭐ Favorites <span class="hidden xs:inline">({{ $stats['favorites'] }})</span>
                </a>
                <a href="{{ route('publikasi.library', ['tab' => 'history']) }}"
                    @class([ 'px-4 sm:px-6 py-3 sm:py-4 font-semibold text-xs sm:text-sm whitespace-nowrap border-b-2 transition-colors flex-shrink-0'
                    , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'history',
                    'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'history',
                    ])>
                    🕒 History <span class="hidden xs:inline">({{ $stats['history'] }})</span>
                </a>
                <a href="{{ route('publikasi.library', ['tab' => 'saved']) }}"
                    @class([ 'px-4 sm:px-6 py-3 sm:py-4 font-semibold text-xs sm:text-sm whitespace-nowrap border-b-2 transition-colors flex-shrink-0'
                    , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'saved',
                    'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'saved',
                    ])>
                    💾 Saved <span class="hidden xs:inline">({{ $stats['saved'] }})</span>
                </a>
            </div>

            {{-- Search & Filter Bar --}}
            <div class="p-4 pb-0 space-y-3 sm:p-6">
                <form action="{{ route('publikasi.library') }}" method="GET" id="filterForm">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">

                    {{-- Search Input --}}
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari judul, penulis, atau abstrak..."
                            class="w-full pl-10 pr-24 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 transition-all">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#737373]" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        {{-- Filter Toggle Button --}}
                        <button type="button" onclick="toggleFilter()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all {{ $typeFilter ? 'bg-[#FF6B18] text-white' : 'bg-[#F8F9FC] text-[#737373] hover:bg-[#FFF7F2] hover:text-[#FF6B18]' }}">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter {{ $typeFilter ? '(1)' : '' }}
                        </button>
                    </div>

                    {{-- Type Filter Dropdown --}}
                    <div id="filterDropdown" class="filter-dropdown {{ $typeFilter ? 'active' : '' }}">
                        <div class="pt-3">
                            <label class="block text-xs sm:text-sm font-semibold text-[#737373] mb-2">
                                📚 Jenis Publikasi
                            </label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                <label
                                    class="flex items-center gap-2 p-2 sm:p-3 border-2 rounded-lg cursor-pointer transition-all {{ !$typeFilter ? 'border-[#FF6B18] bg-[#FFF7F2]' : 'border-[#EEF0F7] hover:border-[#FF6B18]/30' }}">
                                    <input type="radio" name="type" value="" {{ !$typeFilter ? 'checked' : '' }}
                                        onchange="document.getElementById('filterForm').submit()"
                                        class="w-4 h-4 text-[#FF6B18] focus:ring-[#FF6B18]">
                                    <span class="text-xs sm:text-sm font-semibold text-[#1A1A1A]">Semua</span>
                                </label>
                                @foreach($publicationTypes as $type)
                                <label
                                    class="flex items-center gap-2 p-2 sm:p-3 border-2 rounded-lg cursor-pointer transition-all {{ $typeFilter == $type->id ? 'border-[#FF6B18] bg-[#FFF7F2]' : 'border-[#EEF0F7] hover:border-[#FF6B18]/30' }}">
                                    <input type="radio" name="type" value="{{ $type->id }}" {{ $typeFilter==$type->id ?
                                    'checked' : '' }}
                                    onchange="document.getElementById('filterForm').submit()"
                                    class="w-4 h-4 text-[#FF6B18] focus:ring-[#FF6B18]">
                                    <span class="text-xs sm:text-sm font-semibold text-[#1A1A1A] truncate">{{
                                        $type->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Active Filters Display --}}
                    @if($search || $typeFilter)
                    <div class="flex flex-wrap items-center gap-2 pt-3">
                        <span class="text-xs sm:text-sm text-[#737373] font-semibold">Filter aktif:</span>
                        @if($search)
                        <a href="{{ route('publikasi.library', ['tab' => $activeTab, 'type' => $typeFilter]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FF6B18]/10 text-[#FF6B18] text-xs sm:text-sm font-semibold rounded-lg hover:bg-[#FF6B18]/20 transition-all">
                            <span>Pencarian: "{{ Str::limit($search, 20) }}"</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                        @endif
                        @if($typeFilter)
                        <a href="{{ route('publikasi.library', ['tab' => $activeTab, 'search' => $search]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FF6B18]/10 text-[#FF6B18] text-xs sm:text-sm font-semibold rounded-lg hover:bg-[#FF6B18]/20 transition-all">
                            <span>{{ $publicationTypes->find($typeFilter)?->name ?? 'Tipe' }}</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                        @endif
                        <a href="{{ route('publikasi.library', ['tab' => $activeTab]) }}"
                            class="text-xs sm:text-sm text-[#737373] hover:text-[#FF6B18] font-semibold">
                            Hapus semua
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="p-4 sm:p-6">
            @if($activeTab === 'favorites')
            {{-- FAVORITES TAB --}}
            <div class="grid grid-cols-1 gap-3 sm:gap-4">
                @forelse($formattedPublications as $publication)
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

                // ✅ Generate placeholder URL
                $placeholderParams = http_build_query([
                'initials' => $initials,
                'type' => $publication['publication_type'] ?? 'Publikasi',
                'title' => $publication['title'],
                'category' => $publication['category'] ?? 'Umum',
                'author' => $firstAuthor,
                'v' => time(),
                ]);

                $placeholderUrl = route('placeholder.cover') . '?' . $placeholderParams;

                // Fallback eksternal
                $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);

                // Use cover or placeholder
                $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
                @endphp

                <article
                    class="group flex gap-3 sm:gap-4 p-3 sm:p-4 bg-[#F8F9FC] rounded-xl hover:bg-white hover:shadow-md transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}"
                        class="library-cover w-16 h-20 sm:w-20 sm:h-24 md:w-24 md:h-28 shrink-0 rounded-lg"
                        style="display: block; background-color: #F8F9FC;">
                        <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                            decoding="async" class="transition-transform duration-300 group-hover:scale-105"
                            style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
                            onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
                    </a>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 sm:gap-4 mb-1.5 sm:mb-2">
                            <a href="{{ $publication['detail_url'] }}">
                                <h3
                                    class="text-sm sm:text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                                    {{ $publication['title'] }}
                                </h3>
                            </a>
                            <button type="button" onclick="removeFavorite({{ $publication['id'] }})"
                                class="p-1.5 sm:p-2 transition-colors rounded-full shrink-0 hover:bg-red-50"
                                title="Hapus dari favorit">
                                <svg class="w-4 h-4 text-red-600 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-xs sm:text-sm text-[#737373] mb-1.5 sm:mb-2 line-clamp-1">
                            {{ $publication['authors_text'] ?? 'Tanpa penulis' }}
                        </p>

                        <div class="flex items-center gap-2 text-[10px] sm:text-xs text-[#737373]">
                            <span class="px-2 py-0.5 bg-[#FFF7F2] text-[#FF6B18] rounded font-semibold">{{
                                $publication['type'] ?? 'Publikasi' }}</span>
                            <span class="truncate">{{ $publication['category'] ?? 'Umum' }}</span>
                            <span class="hidden xs:inline">•</span>
                            <span class="hidden xs:inline">{{ $publication['action_time'] ?? '-' }}</span>
                        </div>
                    </div>
                </article>
                @empty
                <div class="py-8 text-center sm:py-12">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-3 sm:mb-4 text-[#EEF0F7]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    @if($search || $typeFilter)
                    <p class="text-[#737373] text-base sm:text-lg mb-2 font-semibold">Tidak ada hasil ditemukan</p>
                    <p class="text-[#737373] text-sm mb-4">
                        Coba ubah kata kunci atau filter Anda
                    </p>
                    <a href="{{ route('publikasi.library', ['tab' => $activeTab]) }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Hapus Filter
                    </a>
                    @else
                    <p class="text-[#737373] text-base sm:text-lg mb-2 font-semibold">Belum ada publikasi favorit</p>
                    <p class="text-[#737373] text-sm mb-4">
                        Mulai tandai publikasi yang Anda sukai
                    </p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Jelajahi Publikasi
                    </a>
                    @endif
                </div>
                @endforelse
            </div>

            @elseif($activeTab === 'history')
            {{-- HISTORY TAB --}}
            <div class="space-y-2 sm:space-y-3">
                @forelse($formattedPublications as $publication)
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
                    class="group flex items-center gap-3 sm:gap-4 p-3 rounded-xl hover:bg-[#F8F9FC] transition-colors">
                    <div class="library-cover w-12 h-16 sm:w-14 sm:h-18 md:w-16 md:h-20 shrink-0 rounded-lg"
                        style="display: block; background-color: #F8F9FC;">
                        <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                            style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
                            onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-xs sm:text-sm font-bold text-[#1A1A1A] line-clamp-1 group-hover:text-[#FF6B18] transition-colors mb-1">
                            {{ $publication['title'] }}
                        </h4>
                        <p class="text-[10px] sm:text-xs text-[#737373] mb-0.5 sm:mb-1 line-clamp-1">
                            <span
                                class="px-1.5 py-0.5 bg-[#FFF7F2] text-[#FF6B18] rounded text-[9px] sm:text-[10px] font-semibold mr-1">{{
                                $publication['type'] ?? 'Publikasi' }}</span>
                            {{ $publication['category'] ?? 'Umum' }} • {{ $publication['authors_text'] ?? 'Tanpa
                            penulis' }}
                        </p>
                        <p class="text-[10px] sm:text-xs text-[#737373]">{{ $publication['action_time'] ?? '-' }}</p>
                    </div>

                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#737373] group-hover:text-[#FF6B18] transition-colors shrink-0"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @empty
                <div class="py-8 text-center sm:py-12">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-3 sm:mb-4 text-[#EEF0F7]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if($search || $typeFilter)
                    <p class="text-[#737373] text-base sm:text-lg mb-2 font-semibold">Tidak ada hasil ditemukan</p>
                    <p class="text-[#737373] text-sm mb-4">
                        Coba ubah kata kunci atau filter Anda
                    </p>
                    <a href="{{ route('publikasi.library', ['tab' => $activeTab]) }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Hapus Filter
                    </a>
                    @else
                    <p class="text-[#737373] text-base sm:text-lg">Riwayat bacaan kosong</p>
                    @endif
                </div>
                @endforelse
            </div>

            @else
            {{-- SAVED TAB --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                @forelse($formattedPublications as $publication)
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

                <article
                    class="group bg-white border border-[#EEF0F7] rounded-xl overflow-hidden hover:shadow-lg hover:border-[#FF6B18]/20 transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}">
                        <div class="library-cover aspect-[3/4]" style="display: block; background-color: #F8F9FC;">
                            <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                                class="transition-transform duration-300 group-hover:scale-105"
                                style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
                                onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">

                            {{-- Type Badge (overlay on top) --}}
                            <div
                                class="absolute top-2 left-2 px-2 py-1 bg-[#FF6B18] text-white text-[10px] sm:text-xs font-bold rounded shadow z-10">
                                {{ $publication['type'] ?? 'Publikasi' }}
                            </div>
                        </div>
                    </a>

                    <div class="p-3 sm:p-4">
                        <a href="{{ $publication['detail_url'] }}">
                            <h3
                                class="text-xs sm:text-sm md:text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors mb-1.5 sm:mb-2">
                                {{ $publication['title'] }}
                            </h3>
                        </a>

                        <p class="text-[10px] sm:text-xs md:text-sm text-[#737373] mb-2 sm:mb-3 line-clamp-1">
                            {{ $publication['authors_text'] ?? 'Tanpa penulis' }}
                        </p>

                        <div class="flex items-center justify-between text-[10px] sm:text-xs text-[#737373]">
                            <span class="truncate">{{ $publication['action_time'] ?? '-' }}</span>
                            <button type="button" onclick="removeSaved({{ $publication['id'] }})"
                                class="text-[#FF6B18] hover:underline font-semibold flex-shrink-0 ml-2">
                                Hapus
                            </button>
                        </div>
                    </div>
                </article>
                @empty
                <div class="py-8 text-center sm:py-12 col-span-full">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-3 sm:mb-4 text-[#EEF0F7]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    @if($search || $typeFilter)
                    <p class="text-[#737373] text-base sm:text-lg mb-2 font-semibold">Tidak ada hasil ditemukan</p>
                    <p class="text-[#737373] text-sm mb-4">
                        Coba ubah kata kunci atau filter Anda
                    </p>
                    <a href="{{ route('publikasi.library', ['tab' => $activeTab]) }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Hapus Filter
                    </a>
                    @else
                    <p class="text-[#737373] text-base sm:text-lg mb-2 font-semibold">Belum ada publikasi yang disimpan
                    </p>
                    <p class="text-[#737373] text-sm mb-4">
                        Simpan publikasi untuk dibaca nanti
                    </p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Jelajahi Publikasi
                    </a>
                    @endif
                </div>
                @endforelse
            </div>
            @endif
        </div>
    </div>

    @endif

</section>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Toggle Filter Dropdown
function toggleFilter() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('active');
}

function removeFavorite(publicationId) {
    if (!confirm('Hapus publikasi ini dari favorit?')) return;

    const button = event.currentTarget;
    const article = button.closest('article');
    const link = article.querySelector('a[href*="/publikasi/"]');

    if (!link) {
        showNotification('Tidak dapat menemukan publikasi', 'error');
        return;
    }

    const url = link.getAttribute('href');
    const slug = url.split('/publikasi/')[1];

    button.disabled = true;
    button.style.opacity = '0.5';

    fetch(`/publikasi/${slug}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            article.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => window.location.reload(), 300);
        } else {
            showNotification(data.message || 'Terjadi kesalahan', 'error');
            button.disabled = false;
            button.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan jaringan', 'error');
        button.disabled = false;
        button.style.opacity = '1';
    });
}

function removeSaved(publicationId) {
    if (!confirm('Hapus publikasi ini dari saved?')) return;

    const button = event.currentTarget;
    const article = button.closest('article');
    const link = article.querySelector('a[href*="/publikasi/"]');

    if (!link) {
        showNotification('Tidak dapat menemukan publikasi', 'error');
        return;
    }

    const url = link.getAttribute('href');
    const slug = url.split('/publikasi/')[1];

    button.disabled = true;
    button.style.opacity = '0.5';

    fetch(`/publikasi/${slug}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            article.style.animation = 'scaleOut 0.3s ease forwards';
            setTimeout(() => window.location.reload(), 300);
        } else {
            showNotification(data.message || 'Terjadi kesalahan', 'error');
            button.disabled = false;
            button.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan jaringan', 'error');
        button.disabled = false;
        button.style.opacity = '1';
    });
}

function showNotification(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        info: 'bg-blue-500',
        error: 'bg-red-500'
    };

    const icons = {
        success: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>`,
        info: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>`,
        error: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>`
    };

    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 max-w-sm`;
    notification.style.transform = 'translateX(400px)';
    notification.innerHTML = `
        <div class="flex items-center gap-2 sm:gap-3">
            <svg class="flex-shrink-0 w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type]}
            </svg>
            <span class="text-xs font-medium sm:text-sm">${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => notification.style.transform = 'translateX(0)', 10);
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Library page loaded with advanced search & filter');
});
</script>
@endpush