@props([
'title' => 'BHAYASCIENTIA, jembatan menuju publikasi.',
'description' => 'Review gratis naskah jurnal, buku, dan opini. Hak cipta tetap milikmu - naskah hasil review tayang di
platform kami.',
'primaryLabel' => 'Mulai gratis',
'primaryUrl' => 'http://bhayascientia.test/admin/register',

// Demo video
'secondaryLabel' => 'Lihat demo',
'youtubeId' => 'ZWZfwmObdvc',

// Badge
'badgeText' => 'Bantu naskahmu naik kelas.',
'badgeIcon' => 'assets/icons/crown.svg',

// Thumbnails
'imageMain' => 'assets/images/thumbnails/overview.png',
'imageBottomLeft' => 'assets/images/thumbnails/review.png',
'imageTopRight' => 'assets/images/thumbnails/sitasi.png',
])

<x-hero.base reverse-on-mobile>
    <x-slot:text>
        <div class="flex flex-col gap-6 anim-hero-fade-up">

            {{-- BADGE --}}
            <div class="flex items-center bg-white p-[8px_16px] gap-[10px] rounded-full w-fit ring-1 ring-[#EEF0F7]">
                <div class="flex w-5 h-5 overflow-hidden shrink-0">
                    <img src="{{ asset($badgeIcon) }}" class="object-contain w-full h-full" alt="Badge icon">
                </div>
                <p class="font-semibold text-sm text-[#111827]">{{ $badgeText }}</p>
            </div>

            {{-- TITLE --}}
            <h1 class="text-[34px] font-extrabold leading-[1.25] text-[#111827] sm:text-[44px] lg:text-[52px]">
                <mark
                    class="inline-block rounded-md bg-[#FF6B18] px-2 py-[2px] text-white align-baseline">BHAYASCIENTIA</mark>
                jembatan menuju
                <mark
                    class="inline-block rounded-md bg-[#FF6B18] px-2 py-[2px] text-white align-baseline">publikasi</mark>
            </h1>

            <p class="max-w-prose text-sm leading-6 text-[#6B7280] sm:text-base sm:leading-7 lg:text-lg lg:leading-8">
                {{ $description }}
            </p>

            <div class="flex flex-col gap-3.5 sm:flex-row">
                <a href="{{ $primaryUrl }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#FF6B18] px-6 py-3 text-sm font-bold text-white transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] sm:text-[16px]">
                    {{ $primaryLabel }}
                </a>

                {{-- Trigger modal video --}}
                <button type="button" data-video-open data-youtube-id="{{ $youtubeId }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#111827] px-6 py-3 text-sm font-bold text-[#111827]
                 transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:text-white sm:text-[16px]
                 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F8F9FC]">
                    {{ $secondaryLabel }}
                </button>
            </div>
        </div>
    </x-slot:text>

    <x-slot:media>
        <div class="relative mx-auto w-full max-w-[560px] shrink-0 lg:mx-0 lg:h-[507px] lg:w-[550px] anim-hero-fade-in">
            <div
                class="relative mx-auto h-[320px] w-full max-w-[447px] overflow-hidden rounded-[26px] sm:h-[420px] lg:ml-[52px] lg:mr-[51px] lg:h-[506px] lg:w-[447px]">
                <img src="{{ asset($imageMain) }}" alt="Overview" class="object-cover w-full h-full">
            </div>

            <div
                class="absolute bottom-4 left-0 h-auto w-[220px] drop-shadow-[0_18px_45px_rgba(17,24,39,0.18)] sm:bottom-6 sm:w-[280px] lg:bottom-[68px] lg:w-[316px]">
                <img src="{{ asset($imageBottomLeft) }}" alt="Review" class="h-auto w-full rounded-[20px]">
            </div>

            <div
                class="absolute right-0 top-4 h-auto w-[110px] drop-shadow-[0_18px_45px_rgba(17,24,39,0.18)] sm:top-6 sm:w-[136px] lg:top-[77px] lg:w-[136px]">
                <img src="{{ asset($imageTopRight) }}" alt="Sitasi" class="h-auto w-full rounded-[18px]">
            </div>

            <div class="h-10 sm:h-12 lg:h-0"></div>
        </div>
    </x-slot:media>
</x-hero.base>

{{-- MODAL--}}
<div id="video-modal" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    {{-- overlay --}}
    <button type="button" data-video-close class="absolute inset-0 bg-black/60" aria-label="Tutup video"></button>

    {{-- dialog --}}
    <div class="relative w-full max-w-3xl overflow-hidden rounded-[20px] bg-white shadow">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 id="video-modal-title" class="text-base sm:text-lg font-bold text-[#111827]">
                Demo BHAYASCIENTIA
            </h3>

            <button type="button" data-video-close class="h-10 w-10 inline-flex items-center justify-center rounded-lg hover:bg-gray-100
                     focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2">
                <span class="sr-only">Close modal</span>
                <svg class="w-4 h-4" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>

        <div class="bg-black">
            <iframe id="videoFrame" class="aspect-[16/9] w-full" src="" title="Demo BHAYASCIENTIA" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>
</div>