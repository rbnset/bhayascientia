@extends('layouts.app')

@section('title', 'My Library')
@section('main_class', 'mt-0 pb-20 sm:pb-16')
@section('hide_footer', 'true')

{{-- Custom Navbar dengan Avatar --}}
@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true"
    :showCtaAlways="true" />
@endsection

@push('styles')
<style>
    /* Smooth transitions */
    * {
        -webkit-tap-highlight-color: transparent;
    }

    /* Card animations */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @media (hover: hover) {
        .stat-card:hover {
            transform: translateY(-4px);
        }
    }

    /* Tab scrollbar hide */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Smooth article removal animation */
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
</style>
@endpush

@section('content')

{{-- Navigation --}}
<x-publication.navigation :items="config('publication.navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8 lg:mt-10">

    {{-- Header (Mobile Optimized) --}}
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
    {{-- ✅ LOGIN GATE - Beautiful Preview --}}

    {{-- Preview Stats Cards (Locked & Mobile Optimized) --}}
    <div class="relative grid grid-cols-1 gap-3 mb-6 sm:gap-4 sm:mb-8 sm:grid-cols-3">
        {{-- Blur overlay --}}
        <div class="absolute inset-0 backdrop-blur-[2px] bg-white/60 z-10 rounded-xl sm:rounded-2xl"></div>

        {{-- Favorites Card --}}
        <div
            class="stat-card bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-xl sm:rounded-2xl p-5 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full sm:w-12 sm:h-12 bg-white/20 backdrop-blur-sm">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold sm:text-3xl">--</span>
            </div>
            <h3 class="mb-1 text-base font-bold sm:text-lg">Favorites</h3>
            <p class="text-xs sm:text-sm text-white/80">Publikasi favorit Anda</p>
        </div>

        {{-- History Card --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
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

        {{-- Saved Card --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
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

    {{-- Beautiful Login Prompt (Mobile Optimized) --}}
    <div
        class="bg-gradient-to-br from-[#FF6B18]/5 via-white to-[#E64627]/5 rounded-2xl sm:rounded-3xl border-2 border-dashed border-[#FF6B18]/30 p-6 sm:p-8 md:p-12 text-center">
        {{-- Icon --}}
        <div
            class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-4 sm:mb-6 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white sm:w-10 sm:h-10 md:w-12 md:h-12" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        {{-- Heading --}}
        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-3 sm:mb-4">
            Buka Akses Library Anda
        </h2>

        <p class="text-sm sm:text-base md:text-lg text-[#737373] mb-6 sm:mb-8 max-w-2xl mx-auto px-2">
            Login untuk menyimpan publikasi favorit, melacak riwayat bacaan, dan mengelola koleksi pribadi Anda
        </p>

        {{-- Features Preview (Mobile Optimized) --}}
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

        {{-- CTA Buttons (Mobile Optimized) --}}
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

        {{-- Additional Info --}}
        <p class="text-xs sm:text-sm text-[#737373] mt-4 sm:mt-6 px-4">
            Gratis, tanpa biaya • Akses penuh ke semua fitur • Sinkronisasi multi-device
        </p>
    </div>

    @else
    {{-- ✅ LOGGED IN - Actual Library Content --}}

    {{-- Stats Cards (Mobile Optimized) --}}
    <div class="grid grid-cols-1 gap-3 mb-6 sm:gap-4 sm:mb-8 sm:grid-cols-3">
        {{-- Favorites --}}
        <div
            class="stat-card bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-xl sm:rounded-2xl p-5 sm:p-6 text-white hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full sm:w-12 sm:h-12 bg-white/20 backdrop-blur-sm">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold sm:text-3xl">{{ $stats['favorites'] }}</span>
            </div>
            <h3 class="mb-1 text-base font-bold sm:text-lg">Favorites</h3>
            <p class="text-xs sm:text-sm text-white/80">Publikasi favorit Anda</p>
        </div>

        {{-- History --}}
        <div
            class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border border-[#EEF0F7] hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ $stats['history'] }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1">Reading History</h3>
            <p class="text-xs sm:text-sm text-[#737373]">Total publikasi dibaca</p>
        </div>

        {{-- Saved --}}
        <div
            class="stat-card bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 border border-[#EEF0F7] hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ $stats['saved'] }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1A1A1A] mb-1">Saved</h3>
            <p class="text-xs sm:text-sm text-[#737373]">Disimpan untuk nanti</p>
        </div>
    </div>

    {{-- Tabs Container (Mobile Optimized) --}}
    <div class="bg-white rounded-xl sm:rounded-2xl border border-[#EEF0F7] overflow-hidden">
        {{-- Tab Headers (Mobile Scroll) --}}
        <div class="flex border-b border-[#EEF0F7] overflow-x-auto scrollbar-hide">
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

        {{-- Tab Content --}}
        <div class="p-4 sm:p-6">
            @if($activeTab === 'favorites')
            {{-- FAVORITES TAB (Mobile Optimized) --}}
            <div class="space-y-3 sm:space-y-4">
                @forelse($publications as $publication)
                <article
                    class="group flex gap-3 sm:gap-4 p-3 sm:p-4 bg-[#F8F9FC] rounded-xl hover:bg-white hover:shadow-md transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}"
                        class="w-16 h-20 overflow-hidden rounded-lg sm:w-20 sm:h-24 md:w-24 md:h-28 shrink-0">
                        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                            class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
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
                            {{ $publication['authors_text'] }}
                        </p>

                        <div class="flex items-center gap-2 text-[10px] sm:text-xs text-[#737373]">
                            <span class="truncate">{{ $publication['category'] }}</span>
                            <span class="hidden xs:inline">•</span>
                            <span class="hidden xs:inline">{{ $publication['action_time'] }}</span>
                        </div>
                    </div>
                </article>
                @empty
                <div class="py-8 text-center sm:py-12">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-3 sm:mb-4 text-[#EEF0F7]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <p class="text-[#737373] text-base sm:text-lg mb-3 sm:mb-4">Belum ada publikasi favorit</p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Jelajahi Publikasi
                    </a>
                </div>
                @endforelse
            </div>

            @elseif($activeTab === 'history')
            {{-- HISTORY TAB (Mobile Optimized) --}}
            <div class="space-y-2 sm:space-y-3">
                @forelse($publications as $publication)
                <a href="{{ $publication['detail_url'] }}"
                    class="group flex items-center gap-3 sm:gap-4 p-3 rounded-xl hover:bg-[#F8F9FC] transition-colors">
                    <div class="w-12 h-16 overflow-hidden rounded-lg sm:w-14 sm:h-18 md:w-16 md:h-20 shrink-0">
                        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                            class="object-cover w-full h-full">
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-xs sm:text-sm font-bold text-[#1A1A1A] line-clamp-1 group-hover:text-[#FF6B18] transition-colors mb-1">
                            {{ $publication['title'] }}
                        </h4>
                        <p class="text-[10px] sm:text-xs text-[#737373] mb-0.5 sm:mb-1 line-clamp-1">
                            {{ $publication['category'] }} • {{ $publication['authors_text'] }}
                        </p>
                        <p class="text-[10px] sm:text-xs text-[#737373]">{{ $publication['action_time'] }}</p>
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
                    <p class="text-[#737373] text-base sm:text-lg">Riwayat bacaan kosong</p>
                </div>
                @endforelse
            </div>

            @else
            {{-- SAVED TAB (Mobile Optimized) --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                @forelse($publications as $publication)
                <article
                    class="group bg-white border border-[#EEF0F7] rounded-xl overflow-hidden hover:shadow-lg hover:border-[#FF6B18]/20 transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}">
                        <div class="aspect-[3/4] overflow-hidden">
                            <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                                class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
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
                            {{ $publication['authors_text'] }}
                        </p>

                        <div class="flex items-center justify-between text-[10px] sm:text-xs text-[#737373]">
                            <span class="truncate">{{ $publication['action_time'] }}</span>
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
                    <p class="text-[#737373] text-base sm:text-lg mb-3 sm:mb-4">Belum ada publikasi yang disimpan</p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-lg hover:shadow-lg transition-all">
                        Jelajahi Publikasi
                    </a>
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
    // ✅ CSRF Token Setup
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

/**
 * ✅ Remove from Favorites
 */
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

/**
 * ✅ Remove from Saved
 */
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

/**
 * ✅ Notification Helper
 */
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

// ✅ Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Library page loaded');
});
</script>
@endpush
