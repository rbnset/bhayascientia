@extends('layouts.app')

@section('title', 'Kategori Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')
<x-publication.navigation :subItems="[
        [
            'label' => 'Browse',
            'icon' => 'assets/images/icons/3dcube.svg',
            'href' => route('publikasi'),
            'active' => false,
        ],
        [
            'label' => 'Categories',
            'icon' => 'assets/images/icons/grid-dark.svg',
            'href' => route('publikasi.categories'),
            'active' => true,
        ],
        [
            'label' => 'Trending',
            'icon' => 'assets/images/icons/fire-dark.svg',
            'href' => route('publikasi.trending'),
            'active' => false,
            'new' => true,
        ],
        [
            'label' => 'My Library',
            'icon' => 'assets/images/icons/book-dark.svg',
            'href' => route('publikasi.library'),
            'active' => false,
            'badge' => auth()->check() ? 24 : 0,
        ],
    ]" :bottomItems="[
        [
            'label' => 'Browse',
            'href' => route('publikasi'),
            'active' => false,
            'icon' => 'assets/images/icons/3dcube-white.svg',
            'iconActive' => 'assets/images/icons/3dcube.svg',
        ],
        [
            'label' => 'Categories',
            'href' => route('publikasi.categories'),
            'active' => true,
            'icon' => 'assets/images/icons/grid-white.svg',
            'iconActive' => 'assets/images/icons/grid-dark.svg',
        ],
        [
            'label' => 'Trending',
            'href' => route('publikasi.trending'),
            'active' => false,
            'icon' => 'assets/images/icons/fire-white.svg',
            'iconActive' => 'assets/images/icons/fire-dark.svg',
            'new' => true,
        ],
        [
            'label' => 'Library',
            'href' => route('publikasi.library'),
            'active' => false,
            'icon' => 'assets/images/icons/book-white.svg',
            'iconActive' => 'assets/images/icons/book-dark.svg',
            'badge' => auth()->check() ? 24 : 0,
        ],
    ]" />

<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-[#1A1A1A] mb-2">
            Jelajahi Berdasarkan Kategori
        </h1>
        <p class="text-[#737373]">
            Temukan publikasi ilmiah sesuai bidang minat Anda
        </p>
    </div>

    {{-- Categories Grid --}}
    <div class="grid gap-4 mb-10 sm:grid-cols-2 lg:grid-cols-3">

        {{-- Category Card: Technology --}}
        <a href="{{ route('publikasi.categories', ['category' => 'technology']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">💻</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        124 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Technology</h3>
                <p class="mb-4 text-sm text-white/90">
                    AI, Machine Learning, Software Engineering, dan teknologi terkini
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Science --}}
        <a href="{{ route('publikasi.categories', ['category' => 'science']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">🔬</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        98 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Science</h3>
                <p class="mb-4 text-sm text-white/90">
                    Fisika, Kimia, Biologi, dan penelitian sains fundamental
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Health --}}
        <a href="{{ route('publikasi.categories', ['category' => 'health']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-red-500 to-red-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">💊</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        87 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Health & Medicine</h3>
                <p class="mb-4 text-sm text-white/90">
                    Kesehatan, Kedokteran, Farmasi, dan penelitian medis
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Education --}}
        <a href="{{ route('publikasi.categories', ['category' => 'education']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-green-500 to-green-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">📚</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        76 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Education</h3>
                <p class="mb-4 text-sm text-white/90">
                    Pendidikan, Pembelajaran, Kurikulum, dan pedagogi
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Engineering --}}
        <a href="{{ route('publikasi.categories', ['category' => 'engineering']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">⚙️</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        65 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Engineering</h3>
                <p class="mb-4 text-sm text-white/90">
                    Teknik Sipil, Mesin, Elektro, dan teknologi rekayasa
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Business --}}
        <a href="{{ route('publikasi.categories', ['category' => 'business']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">💼</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        54 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Business</h3>
                <p class="mb-4 text-sm text-white/90">
                    Manajemen, Ekonomi, Kewirausahaan, dan bisnis
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Social Sciences --}}
        <a href="{{ route('publikasi.categories', ['category' => 'social']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">👥</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        43 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Social Sciences</h3>
                <p class="mb-4 text-sm text-white/90">
                    Sosiologi, Psikologi, Antropologi, dan ilmu sosial
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- Category Card: Arts & Humanities --}}
        <a href="{{ route('publikasi.categories', ['category' => 'arts']) }}"
            class="relative p-6 overflow-hidden transition-all duration-300 group bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-5xl">🎨</span>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                        32 publikasi
                    </span>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Arts & Humanities</h3>
                <p class="mb-4 text-sm text-white/90">
                    Seni, Sastra, Sejarah, Filsafat, dan budaya
                </p>
                <div class="flex items-center text-sm font-semibold text-white">
                    Lihat publikasi
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </div>
            </div>
            <div
                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100">
            </div>
        </a>

        {{-- View All Categories --}}
        <a href="{{ route('publikasi') }}"
            class="group relative overflow-hidden bg-white border-2 border-dashed border-[#EEF0F7] rounded-2xl p-6 hover:border-[#FF6B18] hover:shadow-lg transition-all duration-300 flex items-center justify-center">
            <div class="text-center">
                <div
                    class="w-16 h-16 bg-[#F8F9FC] rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-[#FFF7F2] transition-colors">
                    <svg class="w-8 h-8 text-[#737373] group-hover:text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-1">Lihat Semua</h3>
                <p class="text-sm text-[#737373]">579 total publikasi</p>
            </div>
        </a>

    </div>

</section>
@endsection
