@extends('layouts.app')

@section('title', 'Tentang Kami - DABRAKA')
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* Stats Counter Animation */
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ✅ Animate on Scroll — pakai class bukan inline style */
    .animate-hidden {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .animate-visible {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }

    /* ✅ Paksa team-image selalu visible — tidak ikut animasi */
    .team-image {
        opacity: 1 !important;
        transition: transform 0.3s ease;
    }


    .stat-card {
        animation: countUp 0.6s ease-out forwards;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(255, 107, 24, 0.15);
    }

    /* Timeline Line Animation */
    .timeline-line {
        position: relative;
        background: linear-gradient(180deg, #FF6B18 0%, #E64627 100%);
    }

    .timeline-line::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 0%;
        background: linear-gradient(180deg, #FFD4BA 0%, #FF6B18 100%);
        animation: fillTimeline 2s ease-out forwards;
    }

    @keyframes fillTimeline {
        to {
            height: 100%;
        }
    }

    /* Timeline Item */
    .timeline-item {
        opacity: 0;
        transform: translateX(-30px);
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .timeline-item:nth-child(even) {
        transform: translateX(30px);
        animation: slideInRight 0.6s ease-out forwards;
    }

    @keyframes slideInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Team Card Hover */
    .team-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(255, 107, 24, 0.15);
    }

    .team-card:hover .team-image {
        transform: scale(1.05);
    }

    .team-image {
        transition: transform 0.3s ease;
    }

    /* Value Card */
    .value-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .value-card:hover {
        transform: translateY(-4px);
        border-color: #FF6B18;
    }

    .value-card:hover .value-icon {
        transform: rotate(10deg) scale(1.1);
    }

    .value-icon {
        transition: transform 0.3s ease;
    }

    /* Parallax Effect */
    .parallax-bg {
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Floating Animation */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
</style>
@endpush

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('content')

{{-- Hero Section --}}
<section
    class="bg-gradient-to-br from-[#FF6B18] via-[#E64627] to-[#D63A25] relative overflow-hidden rounded-2xl sm:rounded-[28px]">
    {{-- Background Pattern --}}
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="about-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#about-grid)" />
        </svg>
    </div>

    {{-- Floating Elements --}}
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-20 h-20 rounded-full top-20 left-10 bg-white/10 blur-xl float-animation"></div>
        <div class="absolute w-32 h-32 rounded-full bottom-20 right-10 bg-white/10 blur-xl float-animation"
            style="animation-delay: 1s;"></div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-12 sm:py-16 md:py-20 lg:py-24 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 mb-6 text-xs sm:text-sm text-white/80 sm:mb-8" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Tentang Kami</span>
        </nav>

        {{-- Header Content --}}
        <div class="max-w-4xl mx-auto text-center text-white">
            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 mb-4 sm:mb-6 text-[10px] sm:text-xs font-bold rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd"
                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                        clip-rule="evenodd" />
                </svg>
                Where Knowledge
                Shapes Policing
            </div>

            <h1 class="mb-4 text-3xl font-black leading-tight sm:text-4xl md:text-5xl lg:text-6xl sm:mb-6">
                🚀 Tentang DABRAKA
            </h1>
            <p class="text-base leading-relaxed sm:text-xl md:text-2xl text-white/90">
                Platform publikasi ilmiah terpercaya yang menghubungkan peneliti, akademisi, dan masyarakat dengan
                pengetahuan berkualitas
            </p>
        </div>
    </div>
</section>

{{-- Stats Section (DINAMIS dari Database) --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] -mt-12 sm:-mt-16 relative z-20 mb-12 sm:mb-16">
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4 md:gap-6">

        {{-- Stat 1: Publikasi Published --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-4 sm:p-6 text-center">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-1 sm:mb-2 gradient-text"
                data-count="{{ $stats['publications'] }}">
                {{ number_format($stats['publications'], 0, ',', '.') }}+
            </h3>
            <p class="text-xs sm:text-sm text-[#737373] font-semibold">Publikasi Ilmiah</p>
        </div>

        {{-- Stat 2: Users --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-4 sm:p-6 text-center"
            style="animation-delay: 0.1s">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-1 sm:mb-2 gradient-text"
                data-count="{{ $stats['users'] }}">
                {{ number_format($stats['users'], 0, ',', '.') }}+
            </h3>
            <p class="text-xs sm:text-sm text-[#737373] font-semibold">Pengguna Terdaftar</p>
        </div>

        {{-- Stat 3: Authors --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-4 sm:p-6 text-center"
            style="animation-delay: 0.2s">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-1 sm:mb-2 gradient-text"
                data-count="{{ $stats['authors'] }}">
                {{ number_format($stats['authors'], 0, ',', '.') }}+
            </h3>
            <p class="text-xs sm:text-sm text-[#737373] font-semibold">Penulis Aktif</p>
        </div>

        {{-- Stat 4: Categories --}}
        <div class="stat-card bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-4 sm:p-6 text-center"
            style="animation-delay: 0.3s">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-1 sm:mb-2 gradient-text"
                data-count="{{ $stats['categories'] }}">
                {{ number_format($stats['categories'], 0, ',', '.') }}+
            </h3>
            <p class="text-xs sm:text-sm text-[#737373] font-semibold">Kategori</p>
        </div>

    </div>
</section>


{{-- About Content --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mb-12 sm:mb-16 lg:mb-20">
    <div class="grid items-start grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-12 xl:gap-16">

        {{-- Left: Vertical Image Gallery --}}
        <div class="order-2 lg:order-1 lg:sticky lg:top-24">
            <div class="relative space-y-4">

                {{-- Image 1: Large Featured --}}
                <div class="relative overflow-hidden shadow-2xl rounded-2xl group">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=500&fit=crop"
                        alt="DABRAKA - Ekosistem Pengetahuan Kepolisian"
                        class="w-full h-[280px] sm:h-[320px] lg:h-[360px] object-cover transition-transform duration-700 group-hover:scale-110">

                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

                    {{-- Caption with Icon --}}
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 p-2.5 bg-white/20 backdrop-blur-md rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-base font-bold text-white sm:text-lg">Ekosistem Pengetahuan</p>
                                <p class="mt-1 text-xs text-white/90 sm:text-sm">Riset kepolisian & kebijakan publik</p>
                            </div>
                        </div>
                    </div>

                    {{-- Badge --}}
                    <div class="absolute top-4 right-4">
                        <span
                            class="px-3 py-1.5 text-xs font-bold text-white bg-[#FF6B18] rounded-full shadow-lg backdrop-blur-sm">
                            Featured
                        </span>
                    </div>
                </div>

                {{-- Grid 2 Images --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Image 2: Kolaborasi --}}
                    <div class="relative overflow-hidden shadow-lg rounded-xl group">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=500&h=400&fit=crop"
                            alt="Kolaborasi Akademik"
                            class="w-full h-[160px] sm:h-[180px] lg:h-[200px] object-cover transition-transform duration-700 group-hover:scale-110">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        {{-- Hover Overlay --}}
                        <div
                            class="absolute inset-0 bg-[#FF6B18]/0 group-hover:bg-[#FF6B18]/20 transition-all duration-300">
                        </div>

                        {{-- Icon Badge --}}
                        <div class="absolute top-3 left-3">
                            <div class="p-2 rounded-lg bg-white/30 backdrop-blur-sm">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <div class="absolute bottom-3 left-3 right-3">
                            <p class="text-sm font-bold text-white">Kolaborasi</p>
                            <p class="mt-0.5 text-xs text-white/80">Forum & Diskusi</p>
                        </div>
                    </div>

                    {{-- Image 3: Riset --}}
                    <div class="relative overflow-hidden shadow-lg rounded-xl group">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=500&h=400&fit=crop"
                            alt="Riset & Kajian Strategis"
                            class="w-full h-[160px] sm:h-[180px] lg:h-[200px] object-cover transition-transform duration-700 group-hover:scale-110">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        {{-- Hover Overlay --}}
                        <div
                            class="absolute inset-0 bg-[#FF6B18]/0 group-hover:bg-[#FF6B18]/20 transition-all duration-300">
                        </div>

                        {{-- Icon Badge --}}
                        <div class="absolute top-3 left-3">
                            <div class="p-2 rounded-lg bg-white/30 backdrop-blur-sm">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <div class="absolute bottom-3 left-3 right-3">
                            <p class="text-sm font-bold text-white">Riset</p>
                            <p class="mt-0.5 text-xs text-white/80">Kajian Strategis</p>
                        </div>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="grid grid-cols-3 gap-3">
                    <div
                        class="p-3 text-center bg-white border rounded-lg border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-lg transition-all group">
                        <div class="text-xl font-black text-[#FF6B18] sm:text-2xl">500+</div>
                        <div class="mt-1 text-xs text-[#737373] group-hover:text-[#1A1A1A] transition-colors">Publikasi
                        </div>
                    </div>
                    <div
                        class="p-3 text-center bg-white border rounded-lg border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-lg transition-all group">
                        <div class="text-xl font-black text-[#FF6B18] sm:text-2xl">150+</div>
                        <div class="mt-1 text-xs text-[#737373] group-hover:text-[#1A1A1A] transition-colors">
                            Kontributor</div>
                    </div>
                    <div
                        class="p-3 text-center bg-white border rounded-lg border-[#EEF0F7] hover:border-[#FF6B18] hover:shadow-lg transition-all group">
                        <div class="text-xl font-black text-[#FF6B18] sm:text-2xl">20+</div>
                        <div class="mt-1 text-xs text-[#737373] group-hover:text-[#1A1A1A] transition-colors">Event
                        </div>
                    </div>
                </div>

                {{-- Decorative Elements --}}
                <div class="absolute -z-10 -top-8 -right-8 w-40 h-40 bg-[#FF6B18]/10 rounded-full blur-3xl"></div>
                <div class="absolute -z-10 -bottom-8 -left-8 w-48 h-48 bg-[#E64627]/10 rounded-full blur-3xl"></div>
            </div>
        </div>

        {{-- Right: Content --}}
        <div class="order-1 lg:order-2">
            {{-- Badge --}}
            <div
                class="inline-flex items-center gap-2 px-4 py-2 mb-6 text-xs font-bold rounded-full bg-gradient-to-r from-[#FFF7F2] to-[#FFE2D2] text-[#FF6B18] border border-[#FFE2D2] shadow-sm">
                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                Tentang Kami
            </div>

            {{-- Heading --}}
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-[#1A1A1A] mb-3 leading-tight">
                Where Knowledge
                <span class="block mt-1 text-transparent bg-gradient-to-r from-[#FF6B18] to-[#E64627] bg-clip-text">
                    Shapes Policing
                </span>
            </h2>

            <p class="text-base text-[#737373] mb-8 sm:text-lg">
                Portal pengabdian intelektual untuk transformasi kepolisian Indonesia
            </p>

            {{-- Content with Better Typography --}}
            <div class="space-y-6">
                {{-- Paragraph 1 --}}
                <div class="pl-4 border-l-4 border-[#FF6B18]">
                    <p class="text-base leading-relaxed text-[#4A5568] lg:text-lg">
                        <strong class="text-xl font-black text-[#1A1A1A] lg:text-2xl">DABRAKA</strong>
                        <span class="ml-1 text-sm italic text-[#6B7280]">(Darma Brata Buana Cendekia)</span>
                        <span class="block mt-2">
                            merupakan wadah dan portal pengabdian intelektual yang menghimpun kontribusi pemikiran dari
                            <strong class="text-[#FF6B18]">insan Bhayangkara</strong> serta
                            <strong class="text-[#FF6B18]">kaum akademisi</strong> yang memiliki perhatian terhadap
                            pengembangan ilmu kepolisian, keamanan, kebijakan publik, serta keilmuan terkait lainnya.
                        </span>
                    </p>
                </div>

                {{-- Paragraph 2 --}}
                <div class="p-5 bg-gradient-to-br from-[#FFF7F2] to-white rounded-xl border border-[#FFE2D2]">
                    <p class="text-base leading-relaxed text-[#4A5568] lg:text-lg">
                        DABRAKA lahir dari kesadaran bahwa
                        <strong class="text-[#FF6B18]">transformasi institusi</strong> tidak hanya ditopang
                        oleh struktur dan regulasi, tetapi juga oleh
                        <mark class="bg-[#FFE5D3] text-[#1A1A1A] px-2 py-0.5 rounded font-semibold">kekuatan gagasan,
                            literasi, dan refleksi akademik</mark>
                        yang berkelanjutan.
                    </p>
                </div>

                {{-- Paragraph 3 --}}
                <p class="text-base leading-relaxed text-[#4A5568] lg:text-lg">
                    Di tengah dinamika keamanan global yang semakin kompleks, Polri membutuhkan
                    <span class="font-semibold text-[#2D3748]">ekosistem pengetahuan yang hidup</span>, yang mampu
                    menjembatani pengalaman lapangan dengan pendekatan ilmiah dan kebijakan berbasis bukti.
                </p>

                {{-- Paragraph 4 with Highlight --}}
                <div
                    class="relative p-6 overflow-hidden bg-gradient-to-r from-[#1A1A1A] to-[#2D3748] rounded-xl shadow-lg">
                    <div class="absolute top-0 right-0 w-40 h-40 -mt-20 -mr-20 rounded-full bg-white/5"></div>
                    <p class="relative text-base leading-relaxed text-white lg:text-lg">
                        Melalui publikasi, kajian strategis, forum ilmiah, dan jejaring nasional maupun internasional,
                        DABRAKA berkomitmen menjadi
                        <strong class="text-[#FFE5D3]">pusat referensi pemikiran kepolisian Indonesia</strong>
                        yang progresif dan berwawasan global.
                    </p>
                </div>

                {{-- Closing Statement --}}
                <p class="text-base leading-relaxed text-[#4A5568] lg:text-lg">
                    Kami percaya bahwa
                    <span class="relative inline-block group cursor-help">
                        <span class="relative z-10 font-bold text-[#1A1A1A]">pengabdian intelektual</span>
                        <span
                            class="absolute bottom-0 left-0 w-full h-2 bg-[#FFE5D3] -z-10 transition-all group-hover:h-full group-hover:bg-[#FFE5D3]/30"></span>
                    </span>
                    adalah bagian dari ikrar moral untuk memperkuat institusi, melayani masyarakat, dan berkontribusi
                    bagi kemajuan bangsa.
                </p>
            </div>

            {{-- CTA Buttons --}}
            <div class="flex flex-col gap-4 mt-8 sm:flex-row sm:mt-10">
                <a href="{{ route('publikasi.index') }}"
                    class="group relative px-8 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-2xl hover:shadow-[#FF6B18]/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 overflow-hidden">
                    <span
                        class="absolute inset-0 w-full h-full transition-transform duration-500 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full"></span>
                    <svg class="relative w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    <span class="relative">Jelajahi Publikasi</span>
                    <svg class="relative w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>

                <a href="{{ route('kontak') }}"
                    class="group px-8 py-4 bg-white border-2 border-[#EEF0F7] text-[#737373] text-sm font-bold rounded-xl hover:border-[#FF6B18] hover:text-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300 flex items-center justify-center gap-3 hover:shadow-lg">
                    <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Hubungi Kami</span>
                </a>
            </div>

            {{-- Trust Indicators --}}
            <div class="flex flex-wrap items-center gap-6 mt-10 pt-8 border-t border-[#EEF0F7]">
                <div class="flex items-center gap-2 text-sm text-[#737373]">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold">Terverifikasi Resmi</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-[#737373]">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    <span class="font-semibold">150+ Kontributor Aktif</span>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- Mission & Vision --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1280px] mb-12 sm:mb-16 lg:mb-20">
    {{-- Header --}}
    <div class="mb-10 text-center sm:mb-12 lg:mb-16">
        <div
            class="inline-flex items-center gap-2 px-4 py-2 mb-4 text-xs font-bold rounded-full bg-gradient-to-r from-[#FFF7F2] to-[#FFE2D2] text-[#FF6B18] border border-[#FFE2D2] shadow-sm">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
            </svg>
            Visi & Misi
        </div>

        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-[#1A1A1A] mb-3 sm:mb-4">
            Visi & Misi
            <span class="block mt-2 text-transparent bg-gradient-to-r from-[#FF6B18] to-[#E64627] bg-clip-text">
                DABRAKA
            </span>
        </h2>
        <p class="text-base sm:text-lg lg:text-xl text-[#737373] max-w-3xl mx-auto leading-relaxed">
            Membangun masa depan yang lebih cerah melalui ekosistem pengetahuan kepolisian yang kolaboratif dan
            berstandar global
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-10 xl:gap-12">

        {{-- Vision Card --}}
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-[#FFF7F2] via-[#FFE8DC] to-[#FFE2D2] rounded-2xl border-2 border-[#FFE2D2] p-6 sm:p-8 lg:p-10 hover:shadow-2xl hover:border-[#FF6B18] transition-all duration-300">
            {{-- Decorative Background --}}
            <div
                class="absolute top-0 right-0 w-40 h-40 -mt-20 -mr-20 transition-transform duration-700 rounded-full bg-white/20 group-hover:scale-150">
            </div>
            <div
                class="absolute bottom-0 left-0 w-32 h-32 bg-[#FF6B18]/10 rounded-full -ml-16 -mb-16 group-hover:scale-150 transition-transform duration-700">
            </div>

            {{-- Content --}}
            <div class="relative">
                {{-- Icon --}}
                <div
                    class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white sm:w-10 sm:h-10" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>

                {{-- Title --}}
                <h3 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#1A1A1A] mb-4 sm:mb-6">Visi</h3>

                {{-- Content --}}
                <div class="space-y-4">
                    <p class="text-base sm:text-lg lg:text-xl text-[#4A5568] leading-relaxed">
                        Menjadi <strong class="text-[#FF6B18] font-bold">portal pengabdian intelektual
                            Bhayangkara</strong> yang kolaboratif dan berstandar global dalam pengembangan:
                    </p>

                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-2 h-2 mt-2.5 bg-[#FF6B18] rounded-full"></div>
                            <span class="text-base sm:text-lg text-[#4A5568]">Ilmu kepolisian</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-2 h-2 mt-2.5 bg-[#FF6B18] rounded-full"></div>
                            <span class="text-base sm:text-lg text-[#4A5568]">Keamanan publik</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-2 h-2 mt-2.5 bg-[#FF6B18] rounded-full"></div>
                            <span class="text-base sm:text-lg text-[#4A5568]">Kebijakan berbasis bukti</span>
                        </li>
                    </ul>

                    <div class="pt-4 mt-6 border-t-2 border-[#FF6B18]/20">
                        <p class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Guna mendukung <strong class="text-[#1A1A1A]">transformasi Polri</strong> yang unggul,
                            adaptif, dan berbasis pengetahuan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mission Card --}}
        <div
            class="group relative overflow-hidden bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 sm:p-8 lg:p-10 hover:shadow-2xl hover:border-[#FF6B18] transition-all duration-300">
            {{-- Decorative Background --}}
            <div
                class="absolute top-0 right-0 w-40 h-40 bg-[#FF6B18]/5 rounded-full -mr-20 -mt-20 group-hover:scale-150 transition-transform duration-700">
            </div>

            {{-- Content --}}
            <div class="relative">
                {{-- Icon --}}
                <div
                    class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white sm:w-10 sm:h-10" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>

                {{-- Title --}}
                <h3 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#1A1A1A] mb-4 sm:mb-6">Misi</h3>

                {{-- Mission List --}}
                <ul class="space-y-4 sm:space-y-5">
                    <li class="flex items-start gap-3 group/item">
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-[#FF6B18]/10 group-hover/item:bg-[#FF6B18] transition-colors">
                                <svg class="w-4 h-4 text-[#FF6B18] group-hover/item:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Menghimpun dan mempublikasikan gagasan strategis dari insan Polri dan intelektual sipil
                        </span>
                    </li>

                    <li class="flex items-start gap-3 group/item">
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-[#FF6B18]/10 group-hover/item:bg-[#FF6B18] transition-colors">
                                <svg class="w-4 h-4 text-[#FF6B18] group-hover/item:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Mendorong budaya literasi, riset, dan diskursus ilmiah berbasis bukti (evidence-based
                            policy)
                        </span>
                    </li>

                    <li class="flex items-start gap-3 group/item">
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-[#FF6B18]/10 group-hover/item:bg-[#FF6B18] transition-colors">
                                <svg class="w-4 h-4 text-[#FF6B18] group-hover/item:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Membangun jejaring kolaboratif nasional dan internasional antara Polri, akademisi, dan
                            komunitas keilmuan
                        </span>
                    </li>

                    <li class="flex items-start gap-3 group/item">
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-[#FF6B18]/10 group-hover/item:bg-[#FF6B18] transition-colors">
                                <svg class="w-4 h-4 text-[#FF6B18] group-hover/item:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Mengembangkan ekosistem pengetahuan kepolisian yang progresif, inklusif, dan relevan dengan
                            dinamika global
                        </span>
                    </li>

                    <li class="flex items-start gap-3 group/item">
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-[#FF6B18]/10 group-hover/item:bg-[#FF6B18] transition-colors">
                                <svg class="w-4 h-4 text-[#FF6B18] group-hover/item:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-base sm:text-lg text-[#4A5568] leading-relaxed">
                            Menjadi ruang refleksi dan kontribusi intelektual bagi transformasi kelembagaan Polri
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>


{{-- Values --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mb-12 sm:mb-16">
    <div class="mb-8 text-center sm:mb-12">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-3 sm:mb-4">💎 Nilai-Nilai Kami</h2>
        <p class="text-sm sm:text-base md:text-lg text-[#737373] max-w-2xl mx-auto">
            Prinsip yang menjadi fondasi setiap keputusan dan tindakan kami
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6">
        {{-- Value 1 --}}
        <div class="value-card bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 text-center">
            <div
                class="value-icon w-16 h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-[#1A1A1A] mb-2">Integritas</h3>
            <p class="text-sm text-[#737373]">Menjunjung tinggi kejujuran dan transparansi dalam setiap aspek</p>
        </div>

        {{-- Value 2 --}}
        <div class="value-card bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 text-center">
            <div
                class="value-icon w-16 h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-[#1A1A1A] mb-2">Inovasi</h3>
            <p class="text-sm text-[#737373]">Terus berinovasi untuk memberikan solusi terbaik</p>
        </div>

        {{-- Value 3 --}}
        <div class="value-card bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 text-center">
            <div
                class="value-icon w-16 h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-[#1A1A1A] mb-2">Kolaborasi</h3>
            <p class="text-sm text-[#737373]">Membangun sinergi dengan berbagai pihak</p>
        </div>

        {{-- Value 4 --}}
        <div class="value-card bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 text-center">
            <div
                class="value-icon w-16 h-16 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-[#1A1A1A] mb-2">Kualitas</h3>
            <p class="text-sm text-[#737373]">Berkomitmen pada standar kualitas tertinggi</p>
        </div>
    </div>
</section>

{{-- Team Structure / Organizational Chart --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mb-12 sm:mb-16">

    {{-- Header --}}
    <div class="mb-8 text-center sm:mb-12">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-3 sm:mb-4">
            👥 Struktur Organisasi
        </h2>
        <p class="text-sm sm:text-base md:text-lg text-[#737373] max-w-2xl mx-auto">
            Tim profesional yang berdedikasi untuk memberikan layanan terbaik
        </p>
    </div>

    {{-- ===================================================== --}}
    {{-- LEADERSHIP --}}
    {{-- ===================================================== --}}
    @if(isset($leadership) && $leadership->isNotEmpty())
    <div class="mb-8 sm:mb-12">
        <div class="flex flex-wrap justify-center gap-6">
            @foreach($leadership as $member)
            @php
            // ✅ Resolve foto: storage path atau UI Avatars fallback
            $photoUrl = (!empty($member->photo) && !filter_var($member->photo, FILTER_VALIDATE_URL))
            ? asset('storage/' . ltrim($member->photo, '/'))
            : ($member->photo ?? null);

            $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($member->name)
            . '&size=200&background=ffffff&color=FF6B18&bold=true';

            $finalPhoto = $photoUrl ?? $fallbackUrl;
            @endphp
            <div
                class="team-card bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl border-2 border-[#FF6B18] p-6 sm:p-8 text-center max-w-sm w-full shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">

                {{-- Foto --}}
                <div class="relative inline-block mb-4">
                    <div
                        class="w-24 h-24 mx-auto overflow-hidden border-4 border-white shadow-lg sm:w-28 sm:h-28 rounded-2xl bg-white/20">
                        <img src="{{ $finalPhoto }}" alt="{{ $member->name }}"
                            class="object-cover w-full h-full team-image" loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';" />
                    </div>
                    <div
                        class="absolute flex items-center justify-center w-10 h-10 bg-white shadow-lg -bottom-2 -right-2 rounded-xl">
                        <svg class="w-6 h-6 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                {{-- Info --}}
                <h3 class="mb-1 text-xl font-black text-white sm:text-2xl">{{ $member->name }}</h3>
                <p class="mb-2 text-sm font-semibold sm:text-base text-white/90">{{ $member->title }}</p>
                @if($member->description)
                <p class="mb-4 text-xs leading-relaxed sm:text-sm text-white/80">{{ $member->description }}</p>
                @endif

                {{-- Kontak --}}
                <div class="flex items-center justify-center gap-2">
                    @if($member->email)
                    <a href="mailto:{{ $member->email }}"
                        class="flex items-center justify-center transition-all rounded-lg w-9 h-9 bg-white/20 hover:bg-white/40"
                        title="Email {{ $member->name }}">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </a>
                    @endif
                    @if($member->linkedin)
                    <a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center justify-center transition-all rounded-lg w-9 h-9 bg-white/20 hover:bg-white/40"
                        title="LinkedIn {{ $member->name }}">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if(isset($management) && $management->isNotEmpty())
        <div class="flex justify-center my-4 sm:my-6">
            <div class="w-0.5 h-8 bg-gradient-to-b from-[#FF6B18] to-[#EEF0F7]"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- MANAGEMENT --}}
    {{-- ===================================================== --}}
    @if(isset($management) && $management->isNotEmpty())
    <div class="mb-8 sm:mb-12">
        <div class="grid max-w-5xl grid-cols-1 gap-6 mx-auto sm:grid-cols-2 lg:grid-cols-3">
            @foreach($management as $member)
            @php
            $photoUrl = (!empty($member->photo) && !filter_var($member->photo, FILTER_VALIDATE_URL))
            ? asset('storage/' . ltrim($member->photo, '/'))
            : ($member->photo ?? null);

            $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($member->name)
            . '&size=200&background=FFF7F2&color=FF6B18&bold=true';

            $finalPhoto = $photoUrl ?? $fallbackUrl;
            @endphp
            <div
                class="team-card bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-[#FF6B18]/30">

                {{-- Foto --}}
                <div class="relative inline-block mb-4">
                    <div
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-4 border-[#FFF7F2] mx-auto bg-[#FFF7F2]">
                        <img src="{{ $finalPhoto }}" alt="{{ $member->name }}"
                            class="object-cover w-full h-full team-image" loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';" />
                    </div>
                    {{-- Icon badge per icon_type --}}
                    <div
                        class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-lg flex items-center justify-center shadow-lg">
                        @switch($member->icon_type)
                        @case('code')
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        @break
                        @case('operations')
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        @break
                        @case('marketing')
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        @break
                        @case('content')
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        @break
                        @case('support')
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        @break
                        @default
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        @endswitch
                    </div>
                </div>

                {{-- Info --}}
                <h3 class="text-lg sm:text-xl font-black text-[#1A1A1A] mb-1">{{ $member->name }}</h3>
                <p class="text-sm text-[#FF6B18] font-semibold mb-2">{{ $member->title }}</p>
                @if($member->description)
                <p class="text-xs sm:text-sm text-[#737373] leading-relaxed mb-4">{{ $member->description }}</p>
                @endif

                {{-- Kontak --}}
                <div class="flex items-center justify-center gap-2">
                    @if($member->email)
                    <a href="mailto:{{ $member->email }}"
                        class="w-8 h-8 bg-[#EEF0F7] hover:bg-[#FF6B18] rounded-lg flex items-center justify-center transition-all group"
                        title="Email {{ $member->name }}">
                        <svg class="w-4 h-4 text-[#6B7280] group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </a>
                    @endif
                    @if($member->linkedin)
                    <a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer"
                        class="w-8 h-8 bg-[#EEF0F7] hover:bg-[#FF6B18] rounded-lg flex items-center justify-center transition-all group"
                        title="LinkedIn {{ $member->name }}">
                        <svg class="w-4 h-4 text-[#6B7280] group-hover:text-white transition-colors" fill="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if(isset($departments) && $departments->isNotEmpty())
        <div class="flex justify-center my-4 sm:my-6">
            <div class="w-0.5 h-8 bg-gradient-to-b from-[#EEF0F7] to-transparent"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- DEPARTMENTS --}}
    {{-- ===================================================== --}}
    @if(isset($departments) && $departments->isNotEmpty())
    <div>
        <div class="mb-6 text-center sm:mb-8">
            <h3 class="text-xl sm:text-2xl font-black text-[#1A1A1A] mb-2">Tim Departemen</h3>
            <p class="text-sm text-[#737373]">Tim ahli di setiap bidang yang mendukung kesuksesan kami</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6">
            @foreach($departments as $dept)
            <div
                class="team-card bg-white rounded-xl border-2 border-[#EEF0F7] p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-[#FF6B18]/30">

                {{-- Icon --}}
                <div
                    class="w-12 h-12 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl flex items-center justify-center mb-4">
                    @switch($dept->icon_type)
                    @case('code')
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    @break
                    @case('content')
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    @break
                    @case('marketing')
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    @break
                    @case('operations')
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    @break
                    @case('support')
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    @break
                    @default
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    @endswitch
                </div>

                {{-- Info --}}
                <h4 class="text-base sm:text-lg font-black text-[#1A1A1A] mb-2">{{ $dept->name }}</h4>
                @if($dept->description)
                <p class="text-xs sm:text-sm text-[#737373] mb-3 leading-relaxed">{{ $dept->description }}</p>
                @endif
                @if($dept->member_count > 0)
                <div class="flex items-center gap-2 text-xs text-[#737373]">
                    <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    <span class="font-semibold">{{ $dept->member_count }} Anggota</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- EMPTY STATE --}}
    {{-- ===================================================== --}}
    @if(
    (!isset($leadership) || $leadership->isEmpty()) &&
    (!isset($management) || $management->isEmpty()) &&
    (!isset($departments) || $departments->isEmpty())
    )
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[#FFF5ED] mb-4">
            <svg class="w-10 h-10 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-black text-[#1A1A1A] mb-2">Data tim belum ditambahkan</h3>
        <p class="text-sm text-[#A3A6AE] max-w-xs leading-relaxed">
            Tambahkan anggota tim melalui panel admin untuk menampilkan struktur organisasi.
        </p>
    </div>
    @endif

</section>


{{-- CTA Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mb-12 sm:mb-16">
    <div
        class="bg-gradient-to-br from-[#FF6B18] via-[#E64627] to-[#D63A25] rounded-2xl sm:rounded-[28px] p-8 sm:p-12 md:p-16 text-center relative overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="cta-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#cta-grid)" />
            </svg>
        </div>

        <div class="relative z-10">
            <h2 class="mb-4 text-2xl font-black text-white sm:text-3xl md:text-4xl sm:mb-6">
                🚀 Siap Bergabung dengan Kami?
            </h2>
            <p class="max-w-2xl mx-auto mb-6 text-base sm:text-lg md:text-xl text-white/90 sm:mb-8">
                Mulai jelajahi ribuan publikasi ilmiah berkualitas dan bergabung dengan komunitas peneliti dari seluruh
                Indonesia
            </p>

            <div class="flex flex-col justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('register') }}"
                    class="group px-8 py-4 bg-white text-[#FF6B18] text-sm font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Daftar Sekarang</span>
                </a>

                <a href="{{ route('publikasi.index') }}"
                    class="flex items-center justify-center gap-2 px-8 py-4 text-sm font-bold text-white transition-all border-2 border-white bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Jelajahi Publikasi</span>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {

    // =========================================
    // 1. Smooth Scroll
    // =========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // =========================================
    // 2. Animate on Scroll — fix opacity issue
    // =========================================
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                requestAnimationFrame(() => {
                    entry.target.classList.remove('animate-hidden');
                    entry.target.classList.add('animate-visible');
                });
                observer.unobserve(entry.target); // ✅ stop setelah muncul
            }
        });
    }, {
        threshold: 0.05,                    // ✅ lebih kecil agar tidak miss
        rootMargin: '0px 0px -30px 0px'    // ✅ kurangi margin agar tidak skip
    });

    // ✅ Pakai class, bukan el.style.opacity — inline style tidak bisa di-override CSS
    document.querySelectorAll('.stat-card, .value-card, .team-card').forEach(el => {
        el.classList.add('animate-hidden');
        observer.observe(el);
    });

    // =========================================
    // 3. Stats Counter Animation
    // =========================================
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-count') || '0');
                if (!target || el.dataset.counted) return;

                el.dataset.counted = true;
                let current = 0;
                const duration = 1500;
                const step = Math.ceil(target / (duration / 16));

                const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    el.textContent = new Intl.NumberFormat('id-ID').format(current) + '+';
                    if (current >= target) clearInterval(timer);
                }, 16);

                counterObserver.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('[data-count]').forEach(el => {
        counterObserver.observe(el);
    });

})();
</script>
@endpush