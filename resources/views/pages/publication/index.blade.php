@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="publikasi.library" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" />
@endsection

@section('content')

<x-publication.navigation :subItems="config('publication.sub_navigation')"
    :bottomItems="config('publication.bottom_navigation')" />

<x-hero.publication />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    <x-publication.filter-bar title="Pilih Jenis Publikasi" helper="Buku, Jurnal, atau Opini" :types="$publicationTypes"
        :selectedType="$selectedType" />

    <x-publication.swiper-section title="Tulisan Terbaru <br />Untuk Diskursus yang Bertanggung Jawab" badge="TERKINI"
        swiperClass="upToDateSwiper">

        @forelse($latestPublications as $publication)
        <x-publication.card :title="$publication['title']" :cover="$publication['cover_url']"
            :category="$publication['category']" :date="$publication['formatted_date']" :status="$publication['status']"
            :authors="$publication['authors']" :totalAuthors="$publication['total_authors']"
            :detailUrl="$publication['detail_url']" />
        @empty
        <div class="swiper-slide">
            <div class="bg-white p-8 rounded-[22px] ring-1 ring-[#EEF0F7] text-center">
                <svg class="w-16 h-16 mx-auto text-[#A3A6AE] mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-[#A3A6AE] text-base font-medium">Belum ada publikasi tersedia</p>
                <p class="text-[#A3A6AE] text-sm mt-2">
                    Untuk kategori: <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
                </p>
            </div>
        </div>
        @endforelse

    </x-publication.swiper-section>

</section>

{{-- Best Authors Section --}}
<x-publication.best-authors :authors="$bestAuthors" title="Penulis Terbaik<br/>dengan Kontribusi Terbanyak"
    badge="PENULIS TERBAIK" :selectedType="$selectedType" />


{{-- ✅ Popular Publications Section --}}
<x-publication.popular-section :featuredPublication="$featuredPublication" :publications="$popularPublications"
    :selectedType="$selectedType" :exploreAllUrl="route('publikasi.index', ['type' => $selectedType])" />

<x-filter-modal />

@endsection



{{-- ✅ DEBUG: Tambahkan ini sementara di atas section popular --}}
@if(config('app.debug'))
<div class="bg-yellow-50 border-2 border-yellow-400 p-4 rounded-lg mx-auto max-w-[1130px] mb-4">
    <h4 class="mb-2 font-bold">🔍 Debug Data Popular Publications:</h4>

    <div class="text-xs">
        <p><strong>Featured Publication:</strong></p>
        <pre class="p-2 overflow-auto bg-white rounded">{{ json_encode($featuredPublication, JSON_PRETTY_PRINT) }}</pre>

        <p class="mt-2"><strong>Popular Publications Count:</strong> {{ $popularPublications->count() }}</p>

        @if($popularPublications->isNotEmpty())
        <p class="mt-2"><strong>First Item Sample:</strong></p>
        <pre
            class="p-2 overflow-auto bg-white rounded">{{ json_encode($popularPublications->first(), JSON_PRETTY_PRINT) }}</pre>
        @endif
    </div>
</div>
@endif

{{-- Popular Publications Section --}}
<x-publication.popular-section :featuredPublication="$featuredPublication" :publications="$popularPublications"
    :selectedType="$selectedType" :exploreAllUrl="route('publikasi.index', ['type' => $selectedType])" />