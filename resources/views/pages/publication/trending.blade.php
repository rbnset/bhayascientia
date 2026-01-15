{{-- resources/views/pages/publikasi/trending.blade.php --}}
@extends('layouts.app')

@section('title', 'Trending Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')

{{-- Sub Navigation - GUNAKAN CONFIG --}}
<x-publication.navigation :subItems="config('publication.sub_navigation')"
    :bottomItems="config('publication.bottom_navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Header with Period Filter --}}
    <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-2 flex items-center gap-2">
                <span class="text-3xl">🔥</span>
                Trending Publikasi
            </h1>
            <p class="text-[#737373]">
                Publikasi paling populer dan banyak dibaca
            </p>
        </div>

        {{-- Period Selector --}}
        <div class="flex gap-2 bg-white rounded-full p-1 border border-[#EEF0F7]">
            <a href="{{ route('publikasi.trending', ['period' => 'week']) }}"
                @class([ 'px-4 py-2 text-sm font-semibold rounded-full transition-all' , 'bg-[#FF6B18] text-white'=>
                request('period', 'week') === 'week',
                'text-[#737373] hover:text-[#FF6B18]' => request('period', 'week') !== 'week',
                ])>
                Minggu Ini
            </a>
            <a href="{{ route('publikasi.trending', ['period' => 'month']) }}"
                @class([ 'px-4 py-2 text-sm font-semibold rounded-full transition-all' , 'bg-[#FF6B18] text-white'=>
                request('period') === 'month',
                'text-[#737373] hover:text-[#FF6B18]' => request('period') !== 'month',
                ])>
                Bulan Ini
            </a>
            <a href="{{ route('publikasi.trending', ['period' => 'year']) }}"
                @class([ 'px-4 py-2 text-sm font-semibold rounded-full transition-all' , 'bg-[#FF6B18] text-white'=>
                request('period') === 'year',
                'text-[#737373] hover:text-[#FF6B18]' => request('period') !== 'year',
                ])>
                Tahun Ini
            </a>
        </div>
    </div>

    {{-- Top 3 Trending (Featured) --}}
    <div class="grid gap-6 mb-10 md:grid-cols-3">
        @for($i = 1; $i <= 3; $i++) <article
            class="group relative bg-white rounded-2xl border border-[#EEF0F7] overflow-hidden hover:shadow-xl hover:border-[#FF6B18]/20 transition-all duration-300">
            {{-- Rank Badge --}}
            <div class="absolute z-10 top-4 left-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                    {{ $i }}
                </div>
            </div>

            <a href="{{ route('publikasi.show', $i) }}" class="block">
                {{-- Thumbnail --}}
                <div class="aspect-[16/10] bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 relative overflow-hidden">
                    <img src="https://placehold.co/600x400/FFF7F2/FF6B18?text=Top+{{ $i }}"
                        alt="Top trending publication"
                        class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                </div>

                {{-- Content --}}
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3 text-xs text-[#737373]">
                        <span class="px-2 py-1 bg-[#FFF7F2] text-[#FF6B18] rounded-full font-semibold">
                            Technology
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ 15420 + ($i * 1000) }} views
                        </span>
                    </div>

                    <h3
                        class="text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                        Revolutionary Advances in Quantum Computing and AI Integration
                    </h3>

                    <p class="text-sm text-[#737373] mb-3 line-clamp-1">
                        Dr. Sarah Johnson, et al.
                    </p>

                    {{-- Stats --}}
                    <div class="flex items-center gap-4 pt-3 border-t border-[#EEF0F7] text-xs text-[#737373]">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            {{ 342 - ($i * 50) }} citations
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ 1234 - ($i * 100) }} downloads
                        </span>
                    </div>
                </div>
            </a>
            </article>
            @endfor
    </div>

    {{-- Rest of Trending List - Sama seperti sebelumnya --}}
    {{-- ... (sisa kode sama) ... --}}

</section>

@push('scripts')
<script>
    function toggleFavorite(id) {
    console.log('Toggle favorite:', id);
    // TODO: Implement AJAX favorite toggle
}
</script>
@endpush
@endsection
