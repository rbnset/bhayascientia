{{-- resources/views/components/navbar.blade.php --}}
@props([
'items' => [
['label' => 'Beranda', 'route' => 'home'],
['label' => 'Publikasi', 'route' => 'publikasi.index'],
// ['label' => 'Event', 'route' => 'event'],
['label' => 'Tentang', 'route' => 'tentang'],
['label' => 'Kontak', 'route' => 'kontak'],
],
'ctaLabel' => 'Buka publikasi',
'ctaRoute' => 'publikasi.index',
'ctaIcon' => 'book',
'ctaSubtext' => null,
'ctaVariant' => 'primary',
'showAvatarWhenAuth' => false,
'showCtaAlways' => false,
'showSearch' => true, // 🆕 Default true = tampil di halaman publikasi seperti biasa
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

        <div class="flex items-center justify-between gap-4 py-5">
            {{-- 🔄 LOGO / USER PROFILE - Conditional Logic --}}
            @if($showAvatarWhenAuth && auth()->check())
            {{-- ✅ CASE 1: Avatar Mode AKTIF + User SUDAH LOGIN → HANYA Tampilkan Avatar + Dropdown (TANPA LOGO) --}}
            <div class="items-center hidden gap-3 xl:flex shrink-0">
                {{-- User Profile Dropdown (TANPA logo kecil) --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-full hover:bg-[#FFF7F2] transition-all duration-200 group border border-transparent hover:border-[#EEF0F7]">
                        {{-- Avatar --}}
                        <div class="relative">
                            @if(auth()->user()->profile_photo)
                            <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo) }}"
                                alt="{{ auth()->user()->name }}"
                                class="h-10 w-10 object-cover rounded-full border-2 border-[#FF6B18] shadow-sm group-hover:shadow-md transition-all">
                            @else
                            <div
                                class="h-10 w-10 rounded-full border-2 border-[#FF6B18] bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                <span class="text-sm font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>

                        {{-- User Info --}}
                        <div class="hidden text-left lg:block">
                            <div class="text-sm font-bold text-[#1A1A1A] leading-tight">{{
                                Str::limit(auth()->user()->name, 20) }}</div>
                            <div class="text-xs text-[#737373]">{{ Str::limit(auth()->user()->email, 25) }}</div>
                        </div>

                        {{-- Dropdown Icon --}}
                        <svg class="w-4 h-4 text-[#737373] group-hover:text-[#FF6B18] transition-transform"
                            :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-[#EEF0F7] py-2 z-50"
                        style="display: none;">

                        {{-- User Info Header --}}
                        <div class="px-4 py-3 border-b border-[#EEF0F7]">
                            <p class="text-sm font-bold text-[#1A1A1A]">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#737373] mt-0.5">{{ auth()->user()->email }}</p>
                            @if(auth()->user()->job_title)
                            <p class="text-xs text-[#FF6B18] mt-1 font-medium">{{ auth()->user()->job_title }}</p>
                            @endif
                        </div>

                        {{-- Menu Items --}}
                        <div class="py-1">
                            <a href="{{ route('publikasi.library') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                                <svg class="w-5 h-5 text-[#737373]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Perpustakaan Saya</span>
                            </a>

                            <a href="{{ route('filament.admin.resources.publications.index') }}" target="_blank"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                                <svg class="w-5 h-5 text-[#737373]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            <a href="{{ route('profil.saya') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                                <svg class="w-5 h-5 text-[#737373]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Profil Saya</span>
                            </a>

                            <a href="#"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                                <svg class="w-5 h-5 text-[#737373]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Pengaturan</span>
                            </a>
                        </div>

                        {{-- Logout --}}
                        <div class="border-t border-[#EEF0F7] pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors w-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile: Avatar + User Info (dengan custom CSS) --}}
            <div class="flex items-center xl:hidden shrink-0">
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-full hover:bg-[#FFF7F2] transition-all duration-200 group border border-transparent hover:border-[#EEF0F7]">
                        {{-- Avatar --}}
                        <div class="relative flex-shrink-0">
                            @if(auth()->user()->profile_photo)
                            <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo) }}"
                                alt="{{ auth()->user()->name }}"
                                class="h-10 w-10 sm:h-11 sm:w-11 object-cover rounded-full border-2 border-[#FF6B18] shadow-md group-hover:shadow-lg transition-all">
                            @else
                            <div
                                class="h-10 w-10 sm:h-11 sm:w-11 rounded-full border-2 border-[#FF6B18] bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center shadow-md group-hover:shadow-lg transition-all">
                                <span class="text-base font-bold text-white sm:text-lg">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>

                        {{-- User Info Mobile - dengan custom class CSS --}}
                        <div class="flex-1 min-w-0 text-left user-info-container">
                            <div class="text-xs sm:text-sm font-bold text-[#1A1A1A] leading-tight truncate">
                                {{ auth()->user()->name }}
                            </div>

                            {{-- Email Compact untuk layar < 370px --}} <div
                                class="text-[10px] sm:text-xs text-[#737373] truncate user-info-text-compact">
                                {{ Str::limit(auth()->user()->email, 12, '...') }}
                        </div>

                        {{-- Email Normal untuk layar >= 370px --}}
                        <div class="text-[10px] sm:text-xs text-[#737373] truncate user-info-text-normal">
                            {{ auth()->user()->email }}
                        </div>
                </div>

                {{-- Dropdown Icon --}}
                <svg class="w-4 h-4 text-[#737373] group-hover:text-[#FF6B18] transition-transform flex-shrink-0"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-[#EEF0F7] py-2 z-50"
                    style="display: none;">

                    {{-- User Info Header --}}
                    <div class="px-4 py-3 border-b border-[#EEF0F7]">
                        <p class="text-sm font-bold text-[#1A1A1A] break-words">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#737373] mt-0.5 break-all">{{ auth()->user()->email }}</p>
                        @if(auth()->user()->job_title)
                        <p class="text-xs text-[#FF6B18] mt-1 font-medium truncate">{{ auth()->user()->job_title }}</p>
                        @endif
                    </div>

                    {{-- Menu Items --}}
                    <div class="py-1">
                        <a href="{{ route('publikasi.library') }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                            <svg class="w-5 h-5 text-[#737373] flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Perpustakaan Saya</span>
                        </a>

                        <a href="{{ route('filament.admin.resources.publications.index') }}" target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                            <svg class="w-5 h-5 text-[#737373] flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('profil.saya') }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                            <svg class="w-5 h-5 text-[#737373] flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profil Saya</span>
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1A1A1A] hover:bg-[#FFF7F2] transition-colors">
                            <svg class="w-5 h-5 text-[#737373] flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    </div>

                    {{-- Logout --}}
                    <div class="border-t border-[#EEF0F7] pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors w-full">
                                <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        @else
        {{-- ✅ CASE 2: Avatar Mode TIDAK AKTIF atau User BELUM LOGIN → Tampilkan Logo Saja --}}
        <a href="{{ route('home') }}" class="flex items-center shrink-0 nav-hover-lift focus-primary"
            aria-label="BHAYASCIENTIA - Kembali ke beranda">
            <img src="{{ asset('assets/images/logos/logo.png') }}" alt="BHAYASCIENTIA Logo"
                class="h-11 w-auto object-contain sm:h-12 md:h-14 lg:h-16 max-w-[260px]">
        </a>
        @endif

        <div class="flex items-center gap-3">
            {{-- Desktop Navigation --}}
            <nav class="items-center hidden xl:flex" aria-label="Menu utama" role="navigation">
                <div
                    class="gap-1 bg-white p-1.5 inline-flex flex-wrap items-center rounded-full ring-1 ring-[#EEF0F7] shadow-sm">
                    @foreach ($items as $item)
                    @php
                    $isActive = request()->routeIs($item['route']) ||
                    (request()->routeIs('publikasi.*') && $item['route'] === 'publikasi.index') ||
                    // (request()->routeIs('event.*') && $item['route'] === 'event') ||
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

            {{-- 🔍 Desktop Search Button (hanya jika showSearch=true DAN di halaman publikasi) --}}
            @if($showSearch && request()->routeIs('publikasi.*'))
            <button onclick="openPublicationSearch()"
                class="hidden xl:flex items-center justify-center w-12 h-12 bg-white rounded-full border-2 border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300 group shadow-sm hover:shadow-md">
                <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18] group-hover:scale-110 transition-all"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            @endif

            {{-- Desktop CTA - Tampil Berdasarkan showCtaAlways atau Login Status --}}
            @if($showCtaAlways || !auth()->check())
            <a href="{{ route($ctaRoute) }}"
                class="group text-sm font-bold text-white xl:flex hidden h-[48px] shrink-0 items-center justify-center rounded-full {{ $ctaClasses[$ctaVariant] }} transition-all duration-200 hover:-translate-y-[1px] hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white relative overflow-hidden {{ $ctaSubtext ? 'px-6 gap-2.5' : 'px-5 gap-2' }}">

                <span
                    class="absolute inset-0 transition-transform duration-1000 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full"></span>

                <svg class="relative z-10 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="{{ $iconPaths[$ctaIcon] ?? $iconPaths['book'] }}" />
                </svg>

                @if ($ctaSubtext)
                <div class="relative z-10 text-left">
                    <div class="font-bold leading-tight">{{ $ctaLabel }}</div>
                    <div class="text-xs font-normal opacity-90">{{ $ctaSubtext }}</div>
                </div>
                @else
                <span class="relative z-10">{{ $ctaLabel }}</span>
                @endif

                <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5 relative z-10"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            @endif

            {{-- 🔍 Mobile Search Button (hanya jika showSearch=true DAN di halaman publikasi) --}}
            @if($showSearch && request()->routeIs('publikasi.*'))
            <button onclick="openPublicationSearch()"
                class="xl:hidden flex items-center justify-center w-11 h-11 bg-white rounded-full border border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-200 shadow-sm">
                <svg class="w-5 h-5 text-[#737373]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            @endif

            {{-- Mobile Hamburger --}}
            <button id="hamburgerBtn" type="button"
                class="h-11 w-11 bg-white xl:hidden flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-200 active:scale-95 hover:border-[#FF6B18] hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white shadow-sm"
                aria-controls="mobileMenu" aria-expanded="false" aria-label="Buka menu navigasi">
                <svg id="iconBurger" class="w-5 h-5 transition-all duration-200" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>

                <svg id="iconClose" class="hidden w-5 h-5 transition-all duration-200" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                </svg>
            </button>
        </div>
    </div>
    </div>

    {{-- Mobile Overlay --}}
    <div id="mobileOverlay" class="fixed inset-0 z-40 hidden bg-black/20 backdrop-blur-sm xl:hidden" aria-hidden="true">
    </div>

    {{-- Mobile Menu Panel --}}
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
                <div class="absolute w-24 h-24 rounded-full -right-6 -top-6 bg-white/10"></div>
                <div class="absolute w-16 h-16 rounded-full -right-2 -bottom-2 bg-white/10"></div>
            </div>

            {{-- Primary Navigation --}}
            <div class="p-4">
                {{-- User Info Mobile (only if avatar mode is active and user is logged in) --}}
                @if($showAvatarWhenAuth && auth()->check())
                <div class="mb-4 p-4 bg-gradient-to-br from-[#FFF7F2] to-[#F8F9FC] rounded-xl border border-[#EEF0F7]">
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->profile_photo)
                        <img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo) }}"
                            alt="{{ auth()->user()->name }}"
                            class="h-12 w-12 object-cover rounded-full border-2 border-[#FF6B18]">
                        @else
                        <div
                            class="h-12 w-12 rounded-full border-2 border-[#FF6B18] bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center">
                            <span class="text-lg font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-bold text-[#1A1A1A]">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#737373]">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="pb-3 mb-3">
                    <div class="grid grid-cols-1 gap-2.5">
                        @foreach ($items as $item)
                        @php
                        $isActive = request()->routeIs($item['route']) ||
                        (request()->routeIs('publikasi.*') && $item['route'] === 'publikasi.index') ||
                        // (request()->routeIs('event.*') && $item['route'] === 'event') ||
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

                {{-- CTA Section --}}
                <div class="space-y-2.5">
                    {{-- Tombol CTA Mobile --}}
                    @if($showCtaAlways || !auth()->check())
                    <a href="{{ route($ctaRoute) }}"
                        class="js-mobile-item nav-mobile-item-enter group flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-bold text-white {{ $ctaClasses[$ctaVariant] }} transition-all duration-200 hover:shadow-[0_10px_20px_0_#FF6B1880] active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white relative overflow-hidden"
                        style="animation-delay: {{ count($items) * 60 }}ms;">

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
                    @endif

                    {{-- Logout Button (Only when logged in) --}}
                    @if(auth()->check())
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full js-mobile-item nav-mobile-item-enter group flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold border-2 border-red-200 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-200">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </div>
                        </button>
                    </form>
                    @endif

                    @if ($isHomePage && !auth()->check())
                    <a href="{{ route('publikasi.index') }}#get-started"
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