@props([
'title' => '',
'badge' => '',
'swiperClass' => 'publicationSwiper'
])

<div class="mt-8 sm:mt-10">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if($badge)
            <span
                class="px-3 py-2 sm:px-4 sm:py-2 font-bold sm:text-xs sm:leading-[18px] w-fit rounded-full bg-[#FFECE1] text-[10px] leading-[14px] text-[#FF6B18]">
                {{ $badge }}
            </span>
            @endif
            <h2 class="font-bold text-xl sm:text-2xl md:text-[28px] md:leading-[42px] text-[#111827]">
                {!! $title !!}
            </h2>
        </div>
    </div>

    {{-- Swiper Container dengan overflow visible --}}
    <div class="-mx-4 overflow-visible"> {{-- ✅ Ubah dari overflow-hidden --}}
        <div class="upToDateClip overflow-visible"> {{-- ✅ Tambah overflow-visible --}}
            <div class="swiper {{ $swiperClass }} px-4 w-full overflow-visible"> {{-- ✅ Tambah overflow-visible --}}
                <div class="swiper-wrapper">
                    {{ $slot }}
                </div>

                <div class="swiper-pagination mt-6"></div>
                <div class="swiper-button-prev" aria-label="Sebelumnya"></div>
                <div class="swiper-button-next" aria-label="Berikutnya"></div>
            </div>
        </div>
    </div>
</div>
