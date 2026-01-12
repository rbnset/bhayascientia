@props([
'items' => [
['label' => 'Beranda', 'route' => 'home'],
['label' => 'Publikasi', 'route' => 'publikasi'],
['label' => 'Event', 'route' => 'event'],
['label' => 'Tentang', 'route' => 'tentang'],
['label' => 'Kontak', 'route' => 'kontak'],
],

'ctaLabel' => 'Buka publikasi',
'ctaRoute' => 'publikasi',
])

<header class="mt-6">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center shrink-0" aria-label="Beranda BHAYASCIENTIA">
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="BHAYASCIENTIA"
                    class="h-11 w-auto object-contain sm:h-12 md:h-14 lg:h-16 max-w-[260px]">
            </a>



            <div class="flex items-center gap-3">
                {{-- Desktop menu --}}
                <nav class="items-center hidden xl:flex" aria-label="Menu utama">
                    <div
                        class="gap-1 bg-white p-1 inline-flex flex-wrap items-center rounded-full ring-1 ring-[#EEF0F7]">
                        @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}"
                            @class([ 'px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB]'
                            , 'bg-[#FFF7F2] text-[#FF6B18]'=> request()->routeIs($item['route']),
                            ])>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                </nav>

                {{-- Desktop CTA --}}
                <a href="{{ route($ctaRoute) }}"
                    class="text-sm font-bold text-white xl:flex hidden h-[44px] shrink-0 items-center justify-center rounded-full bg-[#FF6B18] px-[18px] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880]">
                    {{ $ctaLabel }}
                </a>

                {{-- Mobile hamburger (butuh ID agar JS bisa hook) --}}
                <button id="hamburgerBtn"
                    class="h-10 w-10 bg-white xl:hidden flex items-center justify-center rounded-full border border-[#EEF0F7]"
                    aria-controls="mobileMenu" aria-expanded="false" aria-label="Buka menu" type="button">

                    <svg id="iconBurger" class="block w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>

                    <svg id="iconClose" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- MOBILE OVERLAY --}}
        <div id="mobileOverlay" class="inset-0 bg-black/25 xl:hidden fixed z-40 hidden backdrop-blur-[2px]"
            aria-hidden="true"></div>

        {{-- MOBILE PANEL --}}
        <div id="mobileMenu" class="relative z-50 hidden mt-4 xl:hidden">
            <div class="space-y-4 rounded-2xl bg-white p-3 border border-[#EEF0F7]">
                <div class="grid grid-cols-1 gap-2" aria-label="Menu utama versi mobile">
                    @foreach ($items as $item)
                    <a href="{{ route($item['route']) }}"
                        @class([ 'rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]'
                        , 'border-[#FF6B18] bg-[#FFF7F2] text-[#FF6B18]'=> request()->routeIs($item['route']),
                        ])>
                        {{ $item['label'] }}
                    </a>
                    @endforeach

                    <a href="{{ route($ctaRoute) }}"
                        class="rounded-xl px-4 py-3 text-sm font-bold text-white bg-[#FF6B18] text-center transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880]">
                        {{ $ctaLabel }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</header>
