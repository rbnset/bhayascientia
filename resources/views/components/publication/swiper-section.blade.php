@props([
'title' => '',
'badge' => '',
'swiperClass' => 'publicationSwiper'
])


<section class="mt-8 sm:mt-10" aria-labelledby="swiper-title-{{ $swiperClass }}">
    {{-- Header --}}
    <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
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


            {{-- ✅ Custom Navigation Buttons dengan SVG dari arrow.svg --}}
            <button class="swiper-button-prev custom-swiper-nav group" type="button"
                aria-label="Lihat publikasi sebelumnya">
                <svg class="flex-shrink-0 w-5 h-5 transition-transform duration-200 group-hover:scale-110"
                    viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.3175 3.06L6.4275 7.95C5.85 8.5275 5.85 9.4725 6.4275 10.05L11.3175 14.94"
                        stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span class="sr-only">Sebelumnya</span>
            </button>


            <button class="swiper-button-next custom-swiper-nav group" type="button"
                aria-label="Lihat publikasi berikutnya">
                <svg class="flex-shrink-0 w-5 h-5 transition-transform duration-200 rotate-180 group-hover:scale-110"
                    viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.3175 3.06L6.4275 7.95C5.85 8.5275 5.85 9.4725 6.4275 10.05L11.3175 14.94"
                        stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
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


<style>
    /* ✅ Custom Swiper Navigation Styling */
    .custom-swiper-nav {
        position: absolute;
        top: 75%;
        transform: translateY(-50%);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #EEF0F7;
        color: #6B7280;
        cursor: pointer;
        outline: none;
    }


    .custom-swiper-nav:hover {
        background: #FF6B18;
        color: white;
        box-shadow: 0 8px 20px rgba(255, 107, 24, 0.25);
        border-color: #FF6B18;
        transform: translateY(-50%) scale(1.05);
    }


    .custom-swiper-nav:active {
        transform: translateY(-50%) scale(0.95);
    }


    .custom-swiper-nav:focus-visible {
        outline: 2px solid #FF6B18;
        outline-offset: 2px;
    }


    .custom-swiper-nav.swiper-button-disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
        background: #F4F6FB;
    }


    /* ✅ Posisi arrow yang tidak mepet - agak masuk ke dalam */
    .swiper-button-prev.custom-swiper-nav {
        left: 8px;
    }


    .swiper-button-next.custom-swiper-nav {
        right: 8px;
    }


    /* ✅ Responsive: Hide arrows di mobile, show di tablet+ */
    @media (max-width: 640px) {
        .custom-swiper-nav {
            display: none;
        }
    }


    @media (min-width: 641px) and (max-width: 768px) {
        .swiper-button-prev.custom-swiper-nav {
            left: 12px;
        }


        .swiper-button-next.custom-swiper-nav {
            right: 12px;
        }


        .custom-swiper-nav {
            width: 40px;
            height: 40px;
        }
    }


    @media (min-width: 769px) and (max-width: 1024px) {
        .swiper-button-prev.custom-swiper-nav {
            left: 16px;
        }


        .swiper-button-next.custom-swiper-nav {
            right: 16px;
        }


        .custom-swiper-nav {
            width: 42px;
            height: 42px;
        }
    }


    @media (min-width: 1025px) and (max-width: 1280px) {
        .swiper-button-prev.custom-swiper-nav {
            left: 20px;
        }


        .swiper-button-next.custom-swiper-nav {
            right: 20px;
        }
    }


    @media (min-width: 1281px) {
        .swiper-button-prev.custom-swiper-nav {
            left: 24px;
        }


        .swiper-button-next.custom-swiper-nav {
            right: 24px;
        }
    }


    /* ✅ Override default Swiper styles */
    .custom-swiper-nav::after {
        content: none !important;
        display: none !important;
    }


    /* ✅ Prevent text selection on button */
    .custom-swiper-nav {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }


    /* ✅ SVG pointer events */
    .custom-swiper-nav svg {
        pointer-events: none;
    }


    /* ✅ Reduced motion support */
    @media (prefers-reduced-motion: reduce) {


        .custom-swiper-nav,
        .custom-swiper-nav svg {
            transition: none;
        }
    }
</style>
