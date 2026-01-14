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
'ctaIcon' => 'book',
'ctaSubtext' => null,
'ctaVariant' => 'primary',
])

{{-- Detect if we're on home page --}}
@php
$isHomePage = request()->routeIs('home');
$isPublikasiPage = request()->routeIs('publikasi*');

// Icon variants
$iconPaths = [
'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477
4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746
0-3.332.477-4.5 1.253',
'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6
8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
'sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13
3z',
'zap' => 'M13 10V3L4 14h7v7l9-11h-7z',
];

// CTA gradient variants
$ctaClasses = [
'primary' => 'bg-gradient-to-r from-[#FF6B18] to-[#E64627]',
'secondary' => 'bg-gradient-to-r from-[#6366F1] to-[#8B5CF6]',
'premium' => 'bg-gradient-to-r from-[#F59E0B] to-[#EF4444]',
];
@endphp

{{-- Sticky Header --}}
<header id="mainHeader" class="sticky top-0 z-50 transition-all duration-300" role="banner">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] relative">
        <div id="stickyBg"
            class="absolute inset-0 -z-10 backdrop-blur-xl bg-white/80 rounded-2xl opacity-0 transition-opacity duration-300 shadow-[0_2px_20px_0_rgba(0,0,0,0.08)]">
        </div>

        {{-- FIXED: Consistent padding across all pages --}}
        <div class="flex items-center justify-between gap-4 py-5">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center shrink-0 nav-hover-lift focus-primary"
                aria-label="BHAYASCIENTIA - Kembali ke beranda">
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="BHAYASCIENTIA Logo"
                    class="h-11 w-auto object-contain sm:h-12 md:h-14 lg:h-16 max-w-[260px]">
            </a>

            <div class="flex items-center gap-3">
                {{-- Desktop Navigation --}}
                <nav class="items-center hidden xl:flex" aria-label="Menu utama" role="navigation">
                    <div
                        class="gap-1 bg-white p-1.5 inline-flex flex-wrap items-center rounded-full ring-1 ring-[#EEF0F7] shadow-sm">
                        @foreach ($items as $item)
                        @php
                        $isActive = request()->routeIs($item['route']) ||
                        (request()->routeIs('publikasi.*') && $item['route'] === 'publikasi') ||
                        (request()->routeIs('event.*') && $item['route'] === 'event') ||
                        (request()->routeIs('tentang.*') && $item['route'] === 'tentang') ||
                        (request()->routeIs('kontak.*') && $item['route'] === 'kontak');
                        @endphp
                        <a href="{{ route($item['route']) }}"
                            @class([ 'group relative px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-200 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white'
                            , 'bg-[#FFF7F2] text-[#FF6B18]'=> $isActive,
                            'text-[#1A1A1A]' => !$isActive,
                            ])
                            aria-current="{{ $isActive ? 'page' : 'false' }}">
                            {{ $item['label'] }}

                            @if ($isActive)
                            <span
                                class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1 w-1.5 h-1.5 bg-[#FF6B18] rounded-full animate-pulse"></span>
                            @endif

                            <span
                                class="absolute inset-x-0 -bottom-1 h-0.5 bg-[#FF6B18] scale-x-0 group-hover:scale-x-100 transition-transform duration-200 rounded-full"></span>
                        </a>
                        @endforeach
                    </div>
                </nav>

                {{-- Desktop CTA: Context-Aware --}}
                <a href="{{ route($ctaRoute) }}"
                    class="group text-sm font-bold text-white xl:flex hidden h-[48px] shrink-0 items-center justify-center rounded-full {{ $ctaClasses[$ctaVariant] }} transition-all duration-200 hover:-translate-y-[1px] hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white relative overflow-hidden {{ $ctaSubtext ? 'px-6 gap-2.5' : 'px-5 gap-2' }}">

                    {{-- Shimmer effect --}}
                    <span
                        class="absolute inset-0 transition-transform duration-1000 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full"></span>

                    {{-- Icon --}}
                    <svg class="relative z-10 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $iconPaths[$ctaIcon] ?? $iconPaths['book'] }}" />
                    </svg>

                    {{-- Text --}}
                    @if ($ctaSubtext)
                    <div class="relative z-10 text-left">
                        <div class="font-bold leading-tight">{{ $ctaLabel }}</div>
                        <div class="text-xs font-normal opacity-90">{{ $ctaSubtext }}</div>
                    </div>
                    @else
                    <span class="relative z-10">{{ $ctaLabel }}</span>
                    @endif

                    {{-- Arrow --}}
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5 relative z-10"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>

                {{-- Mobile Hamburger --}}
                <button id="hamburgerBtn" type="button"
                    class="h-11 w-11 bg-white xl:hidden flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-200 active:scale-95 hover:border-[#FF6B18] hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white shadow-sm"
                    aria-controls="mobileMenu" aria-expanded="false" aria-label="Buka menu navigasi">
                    <svg id="iconBurger" class="w-5 h-5 transition-all duration-200" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>

                    <svg id="iconClose" class="hidden w-5 h-5 transition-all duration-200" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Overlay dengan subtle backdrop --}}
    <div id="mobileOverlay" class="fixed inset-0 z-40 hidden bg-black/20 backdrop-blur-sm xl:hidden" aria-hidden="true">
    </div>

    {{-- Mobile Menu Panel - FIXED: Consistent top position --}}
    <div id="mobileMenu"
        class="fixed inset-x-4 top-[88px] z-50 hidden xl:hidden max-h-[calc(100vh-7rem)] overflow-y-auto" role="dialog"
        aria-modal="true" aria-labelledby="mobile-menu-title">
        <div class="rounded-2xl bg-white border border-[#EEF0F7] shadow-2xl overflow-hidden">

            {{-- Mobile Menu Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h2 id="mobile-menu-title" class="text-base font-bold flex items-center gap-2.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Menu Navigasi
                    </h2>
                    <p class="text-xs opacity-90 mt-1.5">Platform Publikasi Ilmiah Indonesia</p>
                </div>
                {{-- Decorative circles --}}
                <div class="absolute w-24 h-24 rounded-full -right-6 -top-6 bg-white/10"></div>
                <div class="absolute w-16 h-16 rounded-full -right-2 -bottom-2 bg-white/10"></div>
            </div>

            {{-- Primary Navigation --}}
            <div class="p-4">
                <div class="pb-3 mb-3">
                    <div class="grid grid-cols-1 gap-2.5">
                        @foreach ($items as $item)
                        @php
                        $isActive = request()->routeIs($item['route']) ||
                        (request()->routeIs('publikasi.*') && $item['route'] === 'publikasi') ||
                        (request()->routeIs('event.*') && $item['route'] === 'event') ||
                        (request()->routeIs('tentang.*') && $item['route'] === 'tentang') ||
                        (request()->routeIs('kontak.*') && $item['route'] === 'kontak');
                        @endphp
                        <a href="{{ route($item['route']) }}"
                            @class([ 'js-mobile-item nav-mobile-item-enter group rounded-xl px-4 py-3.5 text-sm font-semibold border transition-all duration-200 hover:border-[#FF6B18] hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white relative'
                            , 'border-[#FF6B18] bg-[#FFF7F2] text-[#FF6B18]'=> $isActive,
                            'border-[#EEF0F7] text-[#1A1A1A]' => !$isActive,
                            ])
                            style="animation-delay: {{ $loop->index * 60 }}ms;"
                            aria-current="{{ $isActive ? 'page' : 'false' }}">

                            {{-- Vertical indicator bar untuk active state --}}
                            @if ($isActive)
                            <span class="absolute left-0 top-0 bottom-0 w-1 bg-[#FF6B18] rounded-r-full"></span>
                            @endif

                            <div class="flex items-center justify-between {{ $isActive ? 'pl-2' : '' }}">
                                <span>{{ $item['label'] }}</span>
                                @if ($isActive)
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-[#737373] group-hover:text-[#FF6B18] transition-all group-hover:translate-x-1"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-[#EEF0F7] pt-3 mb-3">
                    <p class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-2.5 px-2">Aksi Cepat</p>
                </div>

                {{-- CTA Section - FIXED: Consistent styling --}}
                <div class="space-y-2.5">
                    {{-- Primary CTA --}}
                    <a href="{{ route($ctaRoute) }}"
                        class="js-mobile-item nav-mobile-item-enter group flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-bold text-white {{ $ctaClasses[$ctaVariant] }} transition-all duration-200 hover:shadow-[0_10px_20px_0_#FF6B1880] active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white relative overflow-hidden"
                        style="animation-delay: {{ count($items) * 60 }}ms;">

                        {{-- Shimmer effect --}}
                        <span
                            class="absolute inset-0 transition-transform duration-700 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full"></span>

                        <div class="relative z-10 flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $iconPaths[$ctaIcon] ?? $iconPaths['book'] }}" />
                            </svg>

                            @if ($ctaSubtext)
                            <div class="text-left">
                                <div class="font-bold">{{ $ctaLabel }}</div>
                                <div class="text-xs font-normal opacity-90">{{ $ctaSubtext }}</div>
                            </div>
                            @else
                            <span>{{ $ctaLabel }}</span>
                            @endif
                        </div>

                        <svg class="relative z-10 w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>

                    {{-- Secondary CTA (optional for home) --}}
                    @if ($isHomePage)
                    <a href="{{ route('publikasi') }}#get-started"
                        class="js-mobile-item nav-mobile-item-enter group flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold border-2 border-[#FF6B18] text-[#FF6B18] bg-[#FFF7F2] transition-all duration-200 hover:bg-[#FF6B18] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                        style="animation-delay: {{ (count($items) + 1) * 60 }}ms;">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Mulai Gratis</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @endif
                </div>

                {{-- Feature Teaser --}}
                <div
                    class="mt-4 p-3.5 bg-gradient-to-br from-[#FFF7F2] to-[#F8F9FC] rounded-xl border border-[#EEF0F7]">
                    <p class="text-xs font-bold text-[#1A1A1A] mb-2.5">✨ Fitur Platform:</p>
                    <ul class="space-y-2 text-xs text-[#737373]">
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#FF6B18] flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Browse ribuan publikasi gratis</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#FF6B18] flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Download PDF full article</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#FF6B18] flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Simpan ke perpustakaan pribadi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#FF6B18] flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Berlangganan newsletter gratis</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
