@extends('layouts.app')

@section('title', 'My Library')
@section('main_class', 'mt-0 pb-[120px] sm:pb-16')

@section('content')

{{-- Navigation --}}
<x-publication.navigation :items="config('publication.navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-2 flex items-center gap-2">
            <span class="text-3xl">📚</span>
            My Library
        </h1>
        <p class="text-[#737373]">
            Kelola koleksi publikasi favorit, riwayat bacaan, dan simpanan Anda
        </p>
    </div>

    @if(isset($requiresLogin) && $requiresLogin)
    {{-- ✅ LOGIN GATE - Show preview dengan beautiful empty state --}}

    {{-- Preview Stats Cards (Locked) --}}
    <div class="grid gap-4 mb-8 sm:grid-cols-3 relative">
        {{-- Blur overlay --}}
        <div class="absolute inset-0 backdrop-blur-[2px] bg-white/60 z-10 rounded-2xl"></div>

        <div class="bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold">--</span>
            </div>
            <h3 class="mb-1 text-lg font-bold">Favorites</h3>
            <p class="text-sm text-white/80">Publikasi favorit Anda</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-[#1A1A1A]">--</span>
            </div>
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Reading History</h3>
            <p class="text-sm text-[#737373]">Total publikasi dibaca</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-[#1A1A1A]">--</span>
            </div>
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Saved</h3>
            <p class="text-sm text-[#737373]">Disimpan untuk nanti</p>
        </div>
    </div>

    {{-- Beautiful Login Prompt --}}
    <div
        class="bg-gradient-to-br from-[#FF6B18]/5 via-white to-[#E64627]/5 rounded-3xl border-2 border-dashed border-[#FF6B18]/30 p-12 text-center">
        {{-- Icon --}}
        <div
            class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center shadow-lg">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        {{-- Heading --}}
        <h2 class="text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-4">
            Buka Akses Library Anda
        </h2>

        <p class="text-[#737373] text-lg mb-8 max-w-2xl mx-auto">
            Login untuk menyimpan publikasi favorit, melacak riwayat bacaan, dan mengelola koleksi pribadi Anda
        </p>

        {{-- Features Preview --}}
        <div class="grid gap-4 sm:grid-cols-3 mb-8 max-w-3xl mx-auto text-left">
            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1">Favorites</h3>
                <p class="text-sm text-[#737373]">Tandai publikasi penting</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1">History</h3>
                <p class="text-sm text-[#737373]">Lacak riwayat bacaan</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                <div class="w-10 h-10 bg-[#FFF7F2] rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A] mb-1">Saved</h3>
                <p class="text-sm text-[#737373]">Simpan untuk nanti</p>
            </div>
        </div>

        {{-- CTA Buttons --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}"
                class="px-8 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-[0_10px_30px_0_rgba(255,107,24,0.4)] transition-all duration-300 hover:-translate-y-1 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login Sekarang
            </a>

            <a href="{{ route('register') }}"
                class="px-8 py-4 bg-white border-2 border-[#FF6B18] text-[#FF6B18] font-bold rounded-xl hover:bg-[#FFF7F2] transition-all duration-300 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Daftar Gratis
            </a>
        </div>

        {{-- Additional Info --}}
        <p class="text-sm text-[#737373] mt-6">
            Gratis, tanpa biaya • Akses penuh ke semua fitur • Sinkronisasi multi-device
        </p>
    </div>

    @else
    {{-- ✅ LOGGED IN - Show actual library content --}}

    {{-- Stats Cards --}}
    <div class="grid gap-4 mb-8 sm:grid-cols-3">
        {{-- Favorites --}}
        <div
            class="bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl p-6 text-white hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold">{{ $stats['favorites'] }}</span>
            </div>
            <h3 class="mb-1 text-lg font-bold">Favorites</h3>
            <p class="text-sm text-white/80">Publikasi favorit Anda</p>
        </div>

        {{-- History --}}
        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7] hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-[#1A1A1A]">{{ $stats['history'] }}</span>
            </div>
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Reading History</h3>
            <p class="text-sm text-[#737373]">Total publikasi dibaca</p>
        </div>

        {{-- Saved --}}
        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7] hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-[#1A1A1A]">{{ $stats['saved'] }}</span>
            </div>
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Saved</h3>
            <p class="text-sm text-[#737373]">Disimpan untuk nanti</p>
        </div>
    </div>

    {{-- Tabs Container --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] overflow-hidden">
        {{-- Tab Headers --}}
        <div class="flex border-b border-[#EEF0F7] overflow-x-auto scrollbar-hide">
            <a href="{{ route('publikasi.library', ['tab' => 'favorites']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'favorites',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'favorites',
                ])>
                ⭐ Favorites ({{ $stats['favorites'] }})
            </a>
            <a href="{{ route('publikasi.library', ['tab' => 'history']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'history',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'history',
                ])>
                🕒 History ({{ $stats['history'] }})
            </a>
            <a href="{{ route('publikasi.library', ['tab' => 'saved']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> $activeTab === 'saved',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => $activeTab !== 'saved',
                ])>
                💾 Saved ({{ $stats['saved'] }})
            </a>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            @if($activeTab === 'favorites')
            {{-- FAVORITES TAB --}}
            <div class="space-y-4">
                @forelse($publications as $publication)
                <article
                    class="group flex gap-4 p-4 bg-[#F8F9FC] rounded-xl hover:bg-white hover:shadow-md transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}" class="shrink-0 w-24 h-24 rounded-lg overflow-hidden">
                        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                            class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                    </a>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <a href="{{ $publication['detail_url'] }}">
                                <h3
                                    class="text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                                    {{ $publication['title'] }}
                                </h3>
                            </a>
                            <button type="button" onclick="removeFavorite({{ $publication['id'] }})"
                                class="p-2 transition-colors rounded-full shrink-0 hover:bg-red-50"
                                title="Hapus dari favorit">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-sm text-[#737373] mb-2">
                            {{ $publication['authors_text'] }}
                        </p>

                        <div class="flex items-center gap-3 text-xs text-[#737373]">
                            <span>{{ $publication['category'] }}</span>
                            <span>•</span>
                            <span>{{ $publication['action_time'] }}</span>
                        </div>
                    </div>
                </article>
                @empty
                <div class="text-center py-12">
                    <svg class="w-24 h-24 mx-auto mb-4 text-[#EEF0F7]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <p class="text-[#737373] text-lg mb-4">Belum ada publikasi favorit</p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-lg hover:shadow-lg transition-all">
                        Jelajahi Publikasi
                    </a>
                </div>
                @endforelse
            </div>

            @elseif($activeTab === 'history')
            {{-- HISTORY TAB --}}
            <div class="space-y-3">
                @forelse($publications as $publication)
                <a href="{{ $publication['detail_url'] }}"
                    class="group flex items-center gap-4 p-3 rounded-xl hover:bg-[#F8F9FC] transition-colors">
                    <div class="shrink-0 w-16 h-16 rounded-lg overflow-hidden">
                        <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                            class="object-cover w-full h-full">
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-sm font-bold text-[#1A1A1A] line-clamp-1 group-hover:text-[#FF6B18] transition-colors mb-1">
                            {{ $publication['title'] }}
                        </h4>
                        <p class="text-xs text-[#737373] mb-1">
                            {{ $publication['category'] }} • {{ $publication['authors_text'] }}
                        </p>
                        <p class="text-xs text-[#737373]">{{ $publication['action_time'] }}</p>
                    </div>

                    <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18] transition-colors shrink-0"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @empty
                <div class="text-center py-12">
                    <svg class="w-24 h-24 mx-auto mb-4 text-[#EEF0F7]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-[#737373] text-lg">Riwayat bacaan kosong</p>
                </div>
                @endforelse
            </div>

            @else
            {{-- SAVED TAB --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($publications as $publication)
                <article
                    class="group bg-white border border-[#EEF0F7] rounded-xl overflow-hidden hover:shadow-lg hover:border-[#FF6B18]/20 transition-all duration-300">
                    <a href="{{ $publication['detail_url'] }}">
                        <div class="aspect-[3/4] overflow-hidden">
                            <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                                class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                        </div>
                    </a>

                    <div class="p-4">
                        <a href="{{ $publication['detail_url'] }}">
                            <h3
                                class="text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors mb-2">
                                {{ $publication['title'] }}
                            </h3>
                        </a>

                        <p class="text-sm text-[#737373] mb-3 line-clamp-1">
                            {{ $publication['authors_text'] }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-[#737373]">
                            <span>{{ $publication['action_time'] }}</span>
                            <button type="button" onclick="removeSaved({{ $publication['id'] }})"
                                class="text-[#FF6B18] hover:underline font-semibold">
                                Hapus
                            </button>
                        </div>
                    </div>
                </article>
                @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-24 h-24 mx-auto mb-4 text-[#EEF0F7]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    <p class="text-[#737373] text-lg mb-4">Belum ada publikasi yang disimpan</p>
                    <a href="{{ route('publikasi.index') }}"
                        class="inline-block px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-lg hover:shadow-lg transition-all">
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

@push('scripts')
<script>
    function removeFavorite(id) {
    if (confirm('Hapus dari favorit?')) {
        fetch(`/api/publications/${id}/unfavorite`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function removeSaved(id) {
    if (confirm('Hapus dari saved?')) {
        fetch(`/api/publications/${id}/unsave`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endpush
@endsection
