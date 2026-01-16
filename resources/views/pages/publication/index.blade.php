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

    {{-- ✅ DEBUG ENHANCED --}}
    @if(config('app.debug') && !empty($latestPublications))
    <div class="p-4 mt-4 bg-yellow-100 rounded-lg border-2 border-yellow-500">
        <p class="text-sm font-mono mb-2">
            <strong>DEBUG INFO:</strong><br>
            Total publikasi: {{ count($latestPublications) }} |
            Selected type: <span class="text-red-600">{{ $selectedType }}</span>
        </p>

        {{-- Debug publikasi pertama --}}
        @if(isset($latestPublications[0]))
        <div class="mt-2 p-2 bg-white rounded">
            <strong>Publikasi #1:</strong><br>
            <span class="text-xs">
                Title: {{ Str::limit($latestPublications[0]['title'], 50) }}<br>
                Cover URL: <a href="{{ $latestPublications[0]['cover_url'] }}" target="_blank"
                    class="text-blue-600 underline">
                    {{ $latestPublications[0]['cover_url'] }}
                </a><br>
                Category: {{ $latestPublications[0]['category'] }}<br>
                Date: {{ $latestPublications[0]['formatted_date'] }}
            </span>

            {{-- Test image langsung --}}
            <div class="mt-2">
                <strong>Test Image Direct:</strong><br>
                <img src="{{ $latestPublications[0]['cover_url'] }}" alt="Test"
                    style="width: 100px; height: 150px; object-fit: cover; border: 2px solid red;"
                    onerror="this.style.border='2px solid red'; this.alt='❌ FAILED TO LOAD';">
            </div>
        </div>
        @endif
    </div>
    @endif

    <x-publication.swiper-section title="Tulisan Terbaru <br />Untuk Diskursus yang Bertanggung Jawab" badge="TERKINI"
        swiperClass="upToDateSwiper">

        @forelse($latestPublications as $publication)
        <x-publication.card :title="$publication['title']" :cover="$publication['cover_url']"
            :category="$publication['category']" :date="$publication['formatted_date']" :status="$publication['status']"
            :authors="$publication['authors']" :totalAuthors="$publication['total_authors']"
            :detailUrl="route('publikasi.show', $publication['slug'])" />
        @empty
        <div class="swiper-slide">
            <div class="bg-white p-8 rounded-[22px] ring-1 ring-[#EEF0F7] text-center">
                <svg class="w-16 h-16 mx-auto text-[#A3A6AE] mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-[#A3A6AE] text-base font-medium">Belum ada publikasi tersedia</p>
                <p class="text-[#A3A6AE] text-sm mt-2">Untuk kategori: <span class="font-bold text-[#FF6B18]">{{
                        ucfirst($selectedType) }}</span></p>
            </div>
        </div>
        @endforelse

    </x-publication.swiper-section>

</section>

<x-filter-modal />

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ✅ Debug: Log semua images
        const images = document.querySelectorAll('.publication-card-inner img[itemprop="image"]');
        console.log('🖼️ Total cover images found:', images.length);

        images.forEach((img, index) => {
            console.log(`Image ${index + 1}:`, {
                src: img.src,
                complete: img.complete,
                naturalWidth: img.naturalWidth,
                naturalHeight: img.naturalHeight,
                display: window.getComputedStyle(img).display,
                visibility: window.getComputedStyle(img).visibility,
                opacity: window.getComputedStyle(img).opacity,
                zIndex: window.getComputedStyle(img).zIndex
            });

            // Test load
            if (!img.complete) {
                img.addEventListener('load', () => {
                    console.log(`✅ Image ${index + 1} loaded successfully`);
                });
                img.addEventListener('error', () => {
                    console.error(`❌ Image ${index + 1} failed to load:`, img.src);
                });
            }
        });

        // Swiper initialization
        if (typeof Swiper === 'undefined') {
            console.error('❌ Swiper not loaded');
            return;
        }

        const swiperEl = document.querySelector('.upToDateSwiper');

        if (swiperEl) {
            const slideCount = swiperEl.querySelectorAll('.swiper-slide').length;
            console.log('📊 Swiper slides found:', slideCount);

            if (slideCount > 0) {
                const swiper = new Swiper('.upToDateSwiper', {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    breakpoints: {
                        640: { slidesPerView: 2, spaceBetween: 20 },
                        768: { slidesPerView: 3, spaceBetween: 24 },
                        1024: { slidesPerView: 4, spaceBetween: 24 },
                    },
                });
                console.log('✅ Swiper initialized with', slideCount, 'slides');
            } else {
                console.warn('⚠️ No slides found - showing empty state');
            }
        } else {
            console.error('❌ Swiper container (.upToDateSwiper) not found');
        }
    });
</script>
@endpush
