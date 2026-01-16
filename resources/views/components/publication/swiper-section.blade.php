{{-- resources/views/components/publication/swiper-section.blade.php --}}

@props([
'title' => '',
'badge' => '',
'swiperClass' => 'publicationSwiper'
])

<div class="mt-8 sm:mt-10">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6">
        <h2 class="font-bold text-xl sm:text-2xl md:text-[28px] md:leading-[42px] text-[#111827] order-2 sm:order-1">
            {!! $title !!}
        </h2>

        @if($badge)
        <span
            class="px-3 py-2 sm:px-4 sm:py-2 font-bold text-[10px] leading-[14px] sm:text-xs sm:leading-[18px] w-fit rounded-full bg-[#FFECE1] text-[#FF6B18] order-1 sm:order-2 flex-shrink-0">
            {{ $badge }}
        </span>
        @endif
    </div>

    {{-- Swiper Container --}}
    <div class="relative">
        <div class="swiper {{ $swiperClass }} !overflow-hidden sm:!overflow-visible">
            <div class="swiper-wrapper">
                {{ $slot }}
            </div>

            <div class="swiper-pagination !relative mt-6 sm:mt-8"></div>
            <div class="swiper-button-prev !hidden sm:!flex" aria-label="Publikasi sebelumnya"></div>
            <div class="swiper-button-next !hidden sm:!flex" aria-label="Publikasi berikutnya"></div>
        </div>
    </div>
</div>
