{{-- resources/views/components/publication/swiper-section.blade.php --}}

@props([
'title' => '',
'badge' => '',
'swiperClass' => 'publicationSwiper'
])

<section class="mt-8 sm:mt-10" aria-labelledby="swiper-title-{{ $swiperClass }}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6">
        {{-- ✅ Semantic heading dengan ID untuk ARIA --}}
        <h2 id="swiper-title-{{ $swiperClass }}"
            class="font-bold text-xl sm:text-2xl md:text-[28px] md:leading-[42px] text-[#111827] order-2 sm:order-1">
            {!! $title !!}
        </h2>

        @if($badge)
        {{-- ✅ Badge dengan semantic tag --}}
        <span
            class="px-3 py-2 sm:px-4 sm:py-2 font-bold text-[10px] leading-[14px] sm:text-xs sm:leading-[18px] w-fit rounded-full bg-[#FFECE1] text-[#FF6B18] order-1 sm:order-2 flex-shrink-0"
            role="status" aria-label="{{ $badge }}">
            {{ $badge }}
        </span>
        @endif
    </div>

    {{-- Swiper Container --}}
    <div class="relative">
        {{-- ✅ Proper semantic structure --}}
        <div class="swiper {{ $swiperClass }}" role="group" aria-roledescription="carousel"
            aria-label="Carousel publikasi">

            <div class="swiper-wrapper" role="list">
                {{ $slot }}
            </div>

            {{-- ✅ Navigation dengan proper labels --}}
            <div class="swiper-pagination" role="group" aria-label="Pagination publikasi"></div>

            <button class="swiper-button-prev" type="button" aria-label="Lihat publikasi sebelumnya">
                <span class="sr-only">Sebelumnya</span>
            </button>

            <button class="swiper-button-next" type="button" aria-label="Lihat publikasi berikutnya">
                <span class="sr-only">Berikutnya</span>
            </button>
        </div>
    </div>

    {{-- ✅ Optional: Skip link untuk keyboard users --}}
    <a href="#after-{{ $swiperClass }}"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-[#ff6b18] focus:text-white">
        Lewati carousel
    </a>
    <div id="after-{{ $swiperClass }}"></div>
</section>
