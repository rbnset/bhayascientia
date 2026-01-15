{{-- resources/views/pages/publikasi/library.blade.php --}}
@extends('layouts.app')

@section('title', 'My Library')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')

{{-- Sub Navigation - GUNAKAN CONFIG --}}
<x-publication.navigation :subItems="config('publication.sub_navigation')"
    :bottomItems="config('publication.bottom_navigation')" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

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

    {{-- Stats Cards --}}
    <div class="grid gap-4 mb-8 sm:grid-cols-3">
        <div class="bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold">24</span>
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
                <span class="text-3xl font-bold text-[#1A1A1A]">156</span>
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
                <span class="text-3xl font-bold text-[#1A1A1A]">12</span>
            </div>
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Saved</h3>
            <p class="text-sm text-[#737373]">Disimpan untuk nanti</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] overflow-hidden mb-8">
        {{-- Tab Headers --}}
        <div class="flex border-b border-[#EEF0F7] overflow-x-auto">
            <a href="{{ route('publikasi.library', ['tab' => 'favorites']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> request('tab', 'favorites') === 'favorites',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => request('tab', 'favorites') !== 'favorites',
                ])>
                Favorites (24)
            </a>
            <a href="{{ route('publikasi.library', ['tab' => 'history']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> request('tab') === 'history',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => request('tab') !== 'history',
                ])>
                History (156)
            </a>
            <a href="{{ route('publikasi.library', ['tab' => 'saved']) }}"
                @class([ 'px-6 py-4 font-semibold text-sm whitespace-nowrap border-b-2 transition-colors'
                , 'border-[#FF6B18] text-[#FF6B18]'=> request('tab') === 'saved',
                'border-transparent text-[#737373] hover:text-[#FF6B18]' => request('tab') !== 'saved',
                ])>
                Saved (12)
            </a>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            @if(request('tab', 'favorites') === 'favorites')
            {{-- Favorites Tab --}}
            <div class="space-y-4">
                @for($i = 1; $i <= 8; $i++) <article
                    class="group flex gap-4 p-4 bg-[#F8F9FC] rounded-xl hover:bg-white hover:shadow-md transition-all duration-300">
                    <a href="{{ route('publikasi.show', $i) }}"
                        class="shrink-0 w-24 h-24 bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 rounded-lg overflow-hidden">
                        <img src="https://placehold.co/300x300/FFF7F2/FF6B18?text={{ $i }}" alt="Publication"
                            class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                    </a>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <a href="{{ route('publikasi.show', $i) }}">
                                <h3
                                    class="text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                                    Advanced Machine Learning Techniques for Healthcare Analytics
                                </h3>
                            </a>
                            <button type="button" onclick="removeFavorite({{ $i }})"
                                class="p-2 transition-colors rounded-full shrink-0 hover:bg-red-50"
                                title="Hapus dari favorit">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-sm text-[#737373] mb-2">Dr. John Doe, Jane Smith</p>

                        <div class="flex items-center gap-3 text-xs text-[#737373]">
                            <span>Technology</span>
                            <span>•</span>
                            <span>Ditambahkan {{ $i }} hari lalu</span>
                        </div>
                    </div>
                    </article>
                    @endfor
            </div>

            @elseif(request('tab') === 'history')
            {{-- History Tab --}}
            <div class="space-y-3">
                @for($i = 1; $i <= 10; $i++) <a href="{{ route('publikasi.show', $i) }}"
                    class="group flex items-center gap-4 p-3 rounded-xl hover:bg-[#F8F9FC] transition-colors">
                    <div
                        class="shrink-0 w-16 h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 rounded-lg overflow-hidden">
                        <img src="https://placehold.co/200x200/FFF7F2/FF6B18?text={{ $i }}" alt="Publication"
                            class="object-cover w-full h-full">
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-sm font-bold text-[#1A1A1A] line-clamp-1 group-hover:text-[#FF6B18] transition-colors mb-1">
                            Research Paper Title Number {{ $i }}
                        </h4>
                        <p class="text-xs text-[#737373] mb-1">Technology • Dr. Sarah Johnson</p>
                        <p class="text-xs text-[#737373]">Dibaca {{ $i }} hari lalu</p>
                    </div>

                    <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18] transition-colors shrink-0"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    </a>
                    @endfor
            </div>

            @else
            {{-- Saved Tab --}}
            <div class="grid gap-4 sm:grid-cols-2">
                @for($i = 1; $i <= 6; $i++) <article
                    class="group bg-white border border-[#EEF0F7] rounded-xl p-4 hover:shadow-lg hover:border-[#FF6B18]/20 transition-all duration-300">
                    <a href="{{ route('publikasi.show', $i) }}">
                        <div
                            class="aspect-video bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 rounded-lg overflow-hidden mb-4">
                            <img src="https://placehold.co/600x400/FFF7F2/FF6B18?text=Saved+{{ $i }}" alt="Publication"
                                class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                        </div>

                        <h3
                            class="text-base font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors mb-2">
                            Saved Publication Title {{ $i }}
                        </h3>

                        <p class="text-sm text-[#737373] mb-3 line-clamp-1">
                            Author Name, et al.
                        </p>

                        <div class="flex items-center justify-between text-xs text-[#737373]">
                            <span>Disimpan {{ $i * 2 }} hari lalu</span>
                            <button type="button" onclick="removeSaved({{ $i }})"
                                class="text-[#FF6B18] hover:underline font-semibold">
                                Hapus
                            </button>
                        </div>
                    </a>
                    </article>
                    @endfor
            </div>
            @endif
        </div>
    </div>

</section>

@push('scripts')
<script>
    function removeFavorite(id) {
    if (confirm('Hapus dari favorit?')) {
        console.log('Remove favorite:', id);
        // TODO: Implement AJAX
    }
}

function removeSaved(id) {
    if (confirm('Hapus dari saved?')) {
        console.log('Remove saved:', id);
        // TODO: Implement AJAX
    }
}
</script>
@endpush
@endsection
