@extends('layouts.app')

@section('title', 'Trending Publikasi')
@section('main_class', 'pb-16')

@section('content')

{{-- Hero Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-4">
            🔥 Publikasi Trending
        </h1>
        <p class="text-[#737373] text-lg max-w-2xl mx-auto">
            Publikasi paling populer dalam 30 hari terakhir
        </p>
    </div>

    {{-- Navigation --}}
    <x-publication.navigation :items="config('publication.navigation')" />

    {{-- Trending List --}}
    <div class="mt-8 space-y-4">
        @forelse($trendingPublications as $index => $publication)
        <a href="{{ $publication['detail_url'] }}"
            class="group flex gap-4 bg-white rounded-2xl border border-[#EEF0F7] p-4 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">

            {{-- Rank Badge --}}
            <div
                class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ $index + 1 }}</span>
            </div>

            {{-- Cover --}}
            <img src="{{ $publication['cover_url'] }}" alt="{{ $publication['title'] }}"
                class="flex-shrink-0 w-20 h-28 object-cover rounded-lg">

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <h3
                    class="text-lg font-bold text-[#1A1A1A] mb-2 group-hover:text-[#FF6B18] transition-colors line-clamp-2">
                    {{ $publication['title'] }}
                </h3>

                <p class="text-sm text-[#737373] mb-3">
                    {{ $publication['authors'][0]['name'] ?? 'Unknown' }}
                    @if($publication['total_authors'] > 1)
                    <span>+{{ $publication['total_authors'] - 1 }} lainnya</span>
                    @endif
                </p>

                {{-- Stats --}}
                <div class="flex items-center gap-4 text-xs text-[#737373]">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ number_format($publication['recent_views']) }} views
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ number_format($publication['recent_downloads']) }} downloads
                    </span>
                </div>
            </div>

            {{-- Arrow Icon --}}
            <div class="flex-shrink-0 self-center">
                <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] group-hover:translate-x-1 transition-all"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
        @empty
        <div class="text-center py-12">
            <p class="text-[#737373] text-lg">Belum ada publikasi trending</p>
        </div>
        @endforelse
    </div>
</section>

@endsection
