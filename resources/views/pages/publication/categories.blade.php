@extends('layouts.app')

@section('title', 'Kategori Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0') {{-- ✅ UBAH: Sesuaikan dengan halaman publikasi lain --}}
@section('hide_footer', 'true') {{-- ✅ TAMBAHKAN: Hide footer untuk konsistensi --}}

{{-- ✅ TAMBAHKAN: Custom Navbar dengan Avatar --}}
@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true" {{-- Logo
    hilang saat login, avatar muncul --}} :showCtaAlways="true" {{-- CTA hilang saat login --}} />
@endsection

@section('content')

{{-- ✅ PINDAHKAN Navigation ke luar Hero Section --}}
<x-publication.navigation :items="config('publication.navigation')" />

{{-- Hero Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10">
    <div class="mb-8 text-center sm:mb-10">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#1A1A1A] mb-4">
            Jelajahi Berdasarkan Kategori
        </h1>
        <p class="text-[#737373] text-base sm:text-lg max-w-2xl mx-auto">
            Temukan publikasi ilmiah sesuai bidang minat Anda
        </p>
    </div>

    {{-- Categories Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
        @forelse($categories as $category)
        <a href="{{ route('publikasi.index', ['category' => $category['slug']]) }}"
            class="group bg-white rounded-2xl border border-[#EEF0F7] p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">

            {{-- Icon --}}
            <div
                class="w-16 h-16 rounded-xl bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}" class="object-contain w-8 h-8">
            </div>

            {{-- Content --}}
            <h3 class="text-xl font-bold text-[#1A1A1A] mb-2 group-hover:text-[#FF6B18] transition-colors">
                {{ $category['name'] }}
            </h3>

            @if($category['description'])
            <p class="text-sm text-[#737373] mb-4 line-clamp-2">
                {{ $category['description'] }}
            </p>
            @endif

            {{-- Stats --}}
            <div class="flex items-center justify-between pt-4 border-t border-[#EEF0F7]">
                <span class="text-sm font-semibold text-[#FF6B18]">
                    {{ number_format($category['publications_count']) }} Publikasi
                </span>
                <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18] group-hover:translate-x-1 transition-all"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
        @empty
        <div class="py-12 text-center col-span-full">
            <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7]">
                <svg class="w-20 h-20 mx-auto text-[#EEF0F7] mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[#A3A6AE] text-lg font-bold mb-2">Belum Ada Kategori</p>
                <p class="text-[#737373] text-sm">
                    Kategori publikasi akan segera ditambahkan
                </p>
            </div>
        </div>
        @endforelse
    </div>
</section>

@endsection
