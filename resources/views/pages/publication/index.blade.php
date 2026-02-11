@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
{{-- Navbar dengan Avatar/Logo Logic --}}
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="publikasi.library" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" />

{{-- Search/Filter Modal Component --}}
<x-publication-search-filter :selectedType="$selectedType" :categories="$categories ?? []" :years="$years ?? []"
    :topKeywords="$topKeywords ?? []" :filterCategory="null" :filterYear="null" :filterKeyword="null"
    :filterSort="$filterSort" :searchQuery="$searchQuery" />
@endsection

@section('content')

{{-- Publication Navigation (SUB MENU) --}}
<x-publication.navigation :items="config('publication.navigation')" />

{{-- Hero Section --}}
<x-hero.publication />

{{-- Main Content Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Quick Filter Bar (Type + Sort) --}}
    <x-publication.filter-bar title="Pilih Jenis Publikasi" helper="Buku, Jurnal, atau Opini"
        :types="$publicationTypes ?? []" :selectedType="$selectedType" :filterSort="$filterSort"
        :hasActiveFilters="false" />

    {{-- Latest Publications Grid/Swiper --}}
    <x-publication.swiper-section title="Tulisan Terbaru <br />Untuk Diskursus yang Bertanggung Jawab" badge="TERKINI"
        swiperClass="upToDateSwiper">
        @forelse($latestPublications as $publication)
        <x-publication.card :title="$publication['title']" :cover="$publication['cover_url']"
            :category="$publication['category']" :date="$publication['formatted_date']" :status="$publication['status']"
            :authors="$publication['authors']" :totalAuthors="$publication['total_authors']"
            :detailUrl="$publication['detail_url']" />
        @empty
        <div class="swiper-slide">
            <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7] text-center">
                <svg class="w-20 h-20 mx-auto text-[#EEF0F7] mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[#A3A6AE] text-lg font-bold mb-2">Belum Ada Publikasi</p>
                <p class="text-[#737373] text-sm mb-6">
                    Belum ada publikasi tersedia
                    @if($selectedType)
                    untuk kategori: <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
                    @endif
                </p>

                <button onclick="openPublicationSearch()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Publikasi Lain
                </button>
            </div>
        </div>
        @endforelse
    </x-publication.swiper-section>

</section>

{{-- Best Authors Section --}}
<x-publication.best-authors :authors="$bestAuthors ?? collect([])"
    title="Penulis Terbaik<br/>dengan Kontribusi Terbanyak" badge="PENULIS TERBAIK" :selectedType="$selectedType" />

{{-- Popular Publications Section --}}
<x-publication.popular-section :featuredTypeContent="$featuredTypeContent ?? null"
    :featuredPublication="$featuredPublication ?? null" :publications="$popularPublications ?? collect([])"
    :selectedType="$selectedType" :exploreAllUrl="route('publikasi.browse', ['type' => $selectedType])" />

@endsection