@extends('layouts.app')

@section('title', 'Hubungi Kami - DABRAKA')
@section('main_class', 'pb-16')

@push('styles')
<style>
    .form-input-wrapper {
        position: relative;
    }

    .form-input-wrapper input,
    .form-input-wrapper textarea {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-input-wrapper input:focus,
    .form-input-wrapper textarea:focus {
        transform: translateY(-2px);
    }

    .floating-label {
        position: absolute;
        left: 1rem;
        top: 1rem;
        font-size: 0.875rem;
        color: #737373;
        pointer-events: none;
        transition: all 0.3s ease;
        background: white;
        padding: 0 0.25rem;
    }

    .form-input-wrapper input:focus+.floating-label,
    .form-input-wrapper input:not(:placeholder-shown)+.floating-label,
    .form-input-wrapper textarea:focus+.floating-label,
    .form-input-wrapper textarea:not(:placeholder-shown)+.floating-label {
        top: -0.5rem;
        left: 0.75rem;
        font-size: 0.75rem;
        color: #FF6B18;
        font-weight: 600;
    }

    .hero-contact-btn {
        backdrop-filter: blur(12px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hero-contact-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.25);
    }

    .topic-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .topic-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .topic-btn:hover::before {
        opacity: 0.1;
    }

    .topic-btn.active {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        border-color: #FF6B18;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(255, 107, 24, 0.3);
    }

    .info-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(255, 107, 24, 0.15);
    }

    .char-counter {
        font-size: 0.75rem;
        color: #A3A6AE;
        transition: all 0.2s ease;
    }

    .char-counter.warning {
        color: #F59E0B;
        font-weight: 600;
    }

    .char-counter.danger {
        color: #EF4444;
        font-weight: 700;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .form-progress {
        height: 4px;
        background: #EEF0F7;
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 1rem;
    }

    .form-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #FF6B18 0%, #E64627 100%);
        transition: width 0.4s ease;
        box-shadow: 0 0 10px rgba(255, 107, 24, 0.5);
    }

    .security-notice {
        position: absolute;
        bottom: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #FFF7F2 0%, #FFE8DC 100%);
        border: 2px solid #FFD4BA;
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 10px 40px rgba(255, 107, 24, 0.2);
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 50;
    }

    .security-notice.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .security-notice::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 2rem;
        width: 16px;
        height: 16px;
        background: linear-gradient(135deg, #FFE8DC 0%, #FFD4BA 100%);
        border-right: 2px solid #FFD4BA;
        border-bottom: 2px solid #FFD4BA;
        transform: rotate(45deg);
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-4px);
        }

        75% {
            transform: translateX(4px);
        }
    }

    .security-notice.shake {
        animation: shake 0.5s ease;
    }

    .social-icon {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .social-icon:hover {
        transform: translateY(-4px) scale(1.05);
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        z-index: 9999;
        background: white;
        border-radius: 1.5rem;
        max-width: 90vw;
        max-height: 90vh;
        width: 900px;
        padding: 1.5rem;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-content.active {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
    }

    .modal-iframe {
        border: none;
        border-radius: 1rem;
        width: 100%;
        height: 500px;
    }

    /* reCAPTCHA badge posisi kiri bawah */
    .grecaptcha-badge {
        visibility: hidden !important;
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 95vw;
            padding: 1rem;
        }

        .modal-iframe {
            height: 400px;
        }

        .security-notice {
            font-size: 0.875rem;
            padding: 0.875rem;
        }
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-animate {
        animation: slideInDown 0.5s ease-out;
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
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="contact-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#contact-grid)" />
        </svg>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-12 sm:py-16 md:py-20 lg:py-24 relative z-10">
        <nav class="flex items-center gap-2 mb-6 text-xs sm:text-sm text-white/80 sm:mb-8" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Kontak</span>
        </nav>

        <div class="max-w-3xl mx-auto mb-8 text-center text-white sm:mb-12">
            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 mb-4 sm:mb-6 text-[10px] sm:text-xs font-bold rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                <span class="relative flex w-2 h-2">
                    <span
                        class="absolute inline-flex w-full h-full bg-green-400 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 bg-green-500 rounded-full"></span>
                </span>
                Tim Kami Siap Membantu 24/7
            </div>
            <h1 class="mb-4 text-3xl font-black leading-tight sm:text-4xl md:text-5xl lg:text-6xl sm:mb-6">
                💬 Hubungi Kami
            </h1>
            <p class="text-base leading-relaxed sm:text-xl md:text-2xl text-white/90">
                Punya pertanyaan atau butuh bantuan? Tim kami siap merespons dengan cepat!
            </p>
        </div>

        <div class="grid max-w-4xl grid-cols-1 gap-3 mx-auto sm:grid-cols-3 sm:gap-4">
            <a href="mailto:info@dabraka.id"
                class="flex items-center gap-3 p-4 border-2 hero-contact-btn group sm:gap-4 sm:p-5 bg-white/15 border-white/30 rounded-xl sm:rounded-2xl">
                <div
                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-colors sm:w-14 sm:h-14 bg-white/20 rounded-xl group-hover:bg-white/30">
                    <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-[10px] sm:text-xs text-white/70 font-semibold mb-1">EMAIL</p>
                    <p class="text-xs font-bold text-white truncate sm:text-sm">info@dabraka.id</p>
                </div>
            </a>

            <a href="https://wa.me/6281200000000" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-3 p-4 border-2 hero-contact-btn group sm:gap-4 sm:p-5 bg-white/15 border-white/30 rounded-xl sm:rounded-2xl">
                <div
                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-colors sm:w-14 sm:h-14 bg-white/20 rounded-xl group-hover:bg-white/30">
                    <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-[10px] sm:text-xs text-white/70 font-semibold mb-1">WHATSAPP</p>
                    <p class="text-xs font-bold text-white sm:text-sm">+62 812-0000-0000</p>
                </div>
            </a>

            <button onclick="openMapModal()" type="button"
                class="flex items-center gap-3 p-4 border-2 hero-contact-btn group sm:gap-4 sm:p-5 bg-white/15 border-white/30 rounded-xl sm:rounded-2xl">
                <div
                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-colors sm:w-14 sm:h-14 bg-white/20 rounded-xl group-hover:bg-white/30">
                    <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-[10px] sm:text-xs text-white/70 font-semibold mb-1">ALAMAT</p>
                    <p class="text-xs font-bold text-white truncate sm:text-sm">Depok, Yogyakarta</p>
                </div>
                <svg class="flex-shrink-0 w-5 h-5 transition-colors text-white/70 group-hover:text-white" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </button>
        </div>
    </div>
</section>

{{-- Success Alert --}}
@if(session('success'))
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8">
    <div
        class="flex items-start gap-3 p-4 border-2 border-green-500 alert-animate bg-green-50 rounded-xl sm:rounded-2xl sm:p-5 sm:gap-4">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 min-w-0">
            <h3 class="mb-1 text-sm font-bold text-green-900 sm:text-base">✅ Berhasil Terkirim!</h3>
            <p class="text-xs text-green-700 sm:text-sm">{{ session('success') }}</p>
        </div>
        <button onclick="this.closest('.alert-animate').remove()"
            class="flex-shrink-0 text-green-600 transition-colors hover:text-green-800">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Error Alert --}}
@if(session('error'))
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8">
    <div
        class="flex items-start gap-3 p-4 border-2 border-red-500 alert-animate bg-red-50 rounded-xl sm:rounded-2xl sm:p-5 sm:gap-4">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 min-w-0">
            <h3 class="mb-1 text-sm font-bold text-red-900 sm:text-base">⚠️ Terjadi Kesalahan</h3>
            <p class="text-xs text-red-700 sm:text-sm">{{ session('error') }}</p>
        </div>
        <button onclick="this.closest('.alert-animate').remove()"
            class="flex-shrink-0 text-red-600 transition-colors hover:text-red-800">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Main Content --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10 md:mt-12 mb-12 sm:mb-16">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 sm:gap-8">

        {{-- Left: Quick Topics & Info --}}
        <div class="space-y-4 lg:col-span-1 sm:space-y-6">

            {{-- Quick Topics --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-5 sm:p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-base sm:text-lg text-[#1A1A1A]">Topik Cepat</h3>
                        <p class="text-xs sm:text-sm text-[#737373] mt-1">Pilih untuk auto-fill subjek</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-subject="Kerja Sama"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        🤝 Kerja Sama
                    </button>
                    <button type="button" data-subject="Laporan Bug"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        🐛 Bug
                    </button>
                    <button type="button" data-subject="Laporan Konten"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        📢 Konten
                    </button>
                    <button type="button" data-subject="Saran Fitur"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        💡 Saran
                    </button>
                    <button type="button" data-subject="Pertanyaan Umum"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        ❓ Pertanyaan
                    </button>
                    <button type="button" data-subject="Lainnya"
                        class="topic-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold bg-white rounded-lg sm:rounded-xl border-2 border-[#EEF0F7] hover:border-[#FF6B18] transition-all">
                        📝 Lainnya
                    </button>
                </div>
            </div>

            {{-- Working Hours --}}
            <div class="info-card bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-5 sm:p-6">
                <div
                    class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base sm:text-lg text-[#1A1A1A] mb-3">Jam Operasional</h3>
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[#737373]">Senin - Jumat</span>
                        <span class="font-semibold text-[#1A1A1A]">09:00 - 17:00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#737373]">Sabtu</span>
                        <span class="font-semibold text-[#1A1A1A]">09:00 - 14:00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#737373]">Minggu</span>
                        <span class="font-semibold text-[#FF6B18]">Libur</span>
                    </div>
                </div>
            </div>

            {{-- Social Media --}}
            <div
                class="bg-gradient-to-br from-[#FFF7F2] to-[#F8F9FC] rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-5 sm:p-6">
                <h3 class="font-bold text-base sm:text-lg text-[#1A1A1A] mb-4">Ikuti Kami</h3>
                <div class="grid grid-cols-4 gap-3">
                    <a href="https://facebook.com/dabraka" target="_blank" rel="noopener noreferrer"
                        class="social-icon w-11 h-11 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center hover:bg-[#1877F2] hover:text-white transition-all shadow-sm"
                        title="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="https://twitter.com/dabraka" target="_blank" rel="noopener noreferrer"
                        class="social-icon w-11 h-11 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center hover:bg-[#1DA1F2] hover:text-white transition-all shadow-sm"
                        title="Twitter/X">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <a href="https://instagram.com/dabraka" target="_blank" rel="noopener noreferrer"
                        class="social-icon w-11 h-11 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center hover:bg-[#E1306C] hover:text-white transition-all shadow-sm"
                        title="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                        </svg>
                    </a>
                    <a href="https://linkedin.com/company/dabraka" target="_blank" rel="noopener noreferrer"
                        class="social-icon w-11 h-11 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center hover:bg-[#0A66C2] hover:text-white transition-all shadow-sm"
                        title="LinkedIn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Contact Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl sm:rounded-2xl border-2 border-[#EEF0F7] p-6 sm:p-8 md:p-10">
                <div class="mb-6 sm:mb-8">
                    <div class="flex flex-col gap-3 mb-3 sm:flex-row sm:items-center sm:justify-between sm:mb-4">
                        <h2 class="text-2xl sm:text-3xl font-black text-[#1A1A1A]">📩 Kirim Pesan</h2>
                        <span
                            class="inline-flex w-fit px-3 py-1.5 text-[10px] sm:text-xs font-bold rounded-full bg-[#FFF7F2] text-[#FF6B18] border border-[#FFE2D2]">
                            Respons &lt; 24 Jam
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-[#737373]">Isi formulir dengan lengkap untuk respons yang lebih
                        cepat dan akurat.</p>
                    <div class="form-progress">
                        <div id="formProgress" class="form-progress-bar" style="width: 0%"></div>
                    </div>
                    <p class="text-[10px] sm:text-xs text-[#A3A6AE] mt-2">
                        <span id="progressText">Mulai isi formulir</span>
                    </p>
                </div>

                <form id="contactForm" action="{{ route('kontak.submit') }}" method="POST"
                    class="space-y-4 sm:space-y-6">
                    @csrf

                    {{-- ✅ Honeypot anti-bot --}}
                    <input type="text" name="website" value="" autocomplete="off" tabindex="-1" aria-hidden="true"
                        style="display:none !important; position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;">

                    {{-- ✅ reCAPTCHA token (diisi otomatis sebelum submit) --}}
                    <input type="hidden" name="recaptcha_token" id="recaptchaToken">

                    {{-- Validation errors --}}
                    @if ($errors->any())
                    <div class="p-4 border border-red-200 rounded-xl bg-red-50 alert-animate">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <ul class="flex-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 sm:gap-6">
                        {{-- Name --}}
                        <div class="form-input-wrapper">
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder=" " required
                                data-required="true"
                                class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-4 focus:ring-[#FF6B18]/10 outline-none transition-all @error('name') border-red-500 @enderror">
                            <label for="name" class="floating-label">👤 Nama Lengkap *</label>
                            @error('name')
                            <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-input-wrapper">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" "
                                required data-required="true"
                                class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-4 focus:ring-[#FF6B18]/10 outline-none transition-all @error('email') border-red-500 @enderror">
                            <label for="email" class="floating-label">📧 Email *</label>
                            @error('email')
                            <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 sm:gap-6">
                        {{-- Phone --}}
                        <div class="form-input-wrapper">
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder=" "
                                class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-4 focus:ring-[#FF6B18]/10 outline-none transition-all @error('phone') border-red-500 @enderror">
                            <label for="phone" class="floating-label">📱 Telepon (Opsional)</label>
                            @error('phone')
                            <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="form-input-wrapper">
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder=" "
                                required data-required="true"
                                class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-4 focus:ring-[#FF6B18]/10 outline-none transition-all @error('subject') border-red-500 @enderror">
                            <label for="subject" class="floating-label">📌 Subjek *</label>
                            @error('subject')
                            <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Message --}}
                    <div class="form-input-wrapper">
                        <div id="securityNotice" class="security-notice">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 bg-[#FF6B18]/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-[#1A1A1A] mb-1 text-xs sm:text-sm">🔒 Catatan Keamanan</p>
                                    <p class="text-[10px] sm:text-xs leading-relaxed text-[#737373]">
                                        Jangan kirim password, OTP, atau data sensitif melalui form ini. Tim kami tidak
                                        akan pernah meminta informasi pribadi Anda.
                                    </p>
                                </div>
                                <button type="button" onclick="hideSecurityNotice()"
                                    class="text-[#737373] hover:text-[#FF6B18] transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <textarea id="message" name="message" rows="5" placeholder=" " required maxlength="2000"
                            data-required="true"
                            class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:ring-4 focus:ring-[#FF6B18]/10 outline-none transition-all resize-none @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        <label for="message" class="floating-label">💬 Pesan Anda *</label>
                        @error('message')
                        <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-[10px] sm:text-xs text-[#737373]">Jelaskan dengan detail (min. 10 karakter)
                            </p>
                            <p id="charCount" class="char-counter">0 / 2000</p>
                        </div>
                    </div>

                    {{-- ✅ reCAPTCHA Notice --}}
                    <p class="text-[10px] text-[#A3A6AE] leading-relaxed">
                        🤖 Form ini dilindungi oleh reCAPTCHA Google.
                        <a href="https://policies.google.com/privacy" target="_blank"
                            class="underline hover:text-[#FF6B18]">Privacy Policy</a> &amp;
                        <a href="https://policies.google.com/terms" target="_blank"
                            class="underline hover:text-[#FF6B18]">Terms of Service</a> berlaku.
                    </p>

                    {{-- Submit Buttons --}}
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit" id="submitBtn"
                            class="group flex-1 sm:flex-none px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 sm:gap-3 relative overflow-hidden disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                            <span
                                class="absolute inset-0 transition-transform duration-700 -translate-x-full bg-gradient-to-r from-white/0 via-white/20 to-white/0 group-hover:translate-x-full"></span>
                            <svg id="submitIcon" class="relative z-10 w-4 h-4 sm:w-5 sm:h-5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <svg id="submitSpinner" class="relative z-10 hidden w-4 h-4 sm:w-5 sm:h-5 animate-spin"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="submitText" class="relative z-10">Kirim Pesan</span>
                        </button>

                        <button type="reset" id="resetBtn"
                            class="px-6 py-3 sm:py-4 border-2 border-[#EEF0F7] text-[#737373] text-sm sm:text-base font-semibold rounded-xl hover:border-[#FF6B18] hover:text-[#FF6B18] hover:bg-[#FFF7F2] transition-all">
                            🔄 Reset
                        </button>
                    </div>

                    <p class="text-[10px] sm:text-xs text-[#A3A6AE] leading-relaxed">
                        Dengan mengirim pesan, Anda menyetujui komunikasi balasan dari tim kami.
                    </p>
                </form>
            </div>
        </div>

    </div>
</section>

{{-- Google Maps Modal --}}
<div id="mapModal" class="modal-overlay" onclick="closeMapModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg sm:text-xl font-black text-[#1A1A1A]">📍 Lokasi Kami</h3>
                <p class="text-xs sm:text-sm text-[#737373] mt-1">Jl. Contoh No. 123, Depok, Yogyakarta</p>
            </div>
            <button onclick="closeMapModal()"
                class="w-8 h-8 sm:w-10 sm:h-10 bg-[#EEF0F7] hover:bg-[#FF6B18] hover:text-white rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <iframe id="mapIframe" class="modal-iframe" src="" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        <div class="flex gap-3 mt-4">
            <a href="https://www.google.com/maps/dir/?api=1&destination=-7.7753,110.3751" target="_blank"
                rel="noopener noreferrer"
                class="flex-1 px-4 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Petunjuk Arah
            </a>
            <a href="https://maps.google.com/?q=-7.7753,110.3751" target="_blank" rel="noopener noreferrer"
                class="px-4 py-3 border-2 border-[#EEF0F7] text-[#737373] text-sm font-bold rounded-xl hover:border-[#FF6B18] hover:text-[#FF6B18] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Buka di Maps
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- ✅ reCAPTCHA v3 Script --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

<script>
    (function () {
    const RECAPTCHA_SITE_KEY = '{{ config('services.recaptcha.site_key') }}';

    let securityNoticeShown = false;

    // ── Security Notice ───────────────────────────────────────────────────
    const messageTextarea = document.getElementById('message');
    const securityNotice  = document.getElementById('securityNotice');

    messageTextarea?.addEventListener('focus', function () {
        if (!securityNoticeShown) {
            securityNotice?.classList.add('show');
            securityNoticeShown = true;
            setTimeout(() => securityNotice?.classList.remove('show'), 8000);
        }
    });

    window.hideSecurityNotice = function () {
        securityNotice?.classList.remove('show');
    };

    // ── Google Maps Modal ─────────────────────────────────────────────────
    window.openMapModal = function () {
        const modal   = document.getElementById('mapModal');
        const content = modal?.querySelector('.modal-content');
        const iframe  = document.getElementById('mapIframe');

        if (modal && content && iframe) {
            iframe.src = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.0!2d110.3751!3d-7.7753!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNDYnMzEuMSJTIDExMMKwMjInMzAuNCJF!5e0!3m2!1sen!2sid!4v1234567890123';
            modal.classList.add('active');
            setTimeout(() => content.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeMapModal = function () {
        const modal   = document.getElementById('mapModal');
        const content = modal?.querySelector('.modal-content');

        if (modal && content) {
            content.classList.remove('active');
            setTimeout(() => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }, 300);
        }
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeMapModal(); hideSecurityNotice(); }
    });

    // ── Quick Topic Buttons ───────────────────────────────────────────────
    const topicBtns    = document.querySelectorAll('.topic-btn');
    const subjectInput = document.getElementById('subject');

    topicBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            topicBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (subjectInput && this.dataset.subject) {
                subjectInput.value = this.dataset.subject;
                subjectInput.focus();
                subjectInput.dispatchEvent(new Event('input'));
            }
        });
    });

    // ── Character Counter ─────────────────────────────────────────────────
    const charCount = document.getElementById('charCount');

    messageTextarea?.addEventListener('input', function () {
        const length    = this.value.length;
        const maxLength = 2000;
        charCount.textContent = `${length} / ${maxLength}`;
        charCount.classList.remove('warning', 'danger');
        if (length > maxLength * 0.8 && length <= maxLength * 0.95) charCount.classList.add('warning');
        else if (length > maxLength * 0.95) charCount.classList.add('danger');
    });

    // ── Form Progress ─────────────────────────────────────────────────────
    const form          = document.getElementById('contactForm');
    const progressBar   = document.getElementById('formProgress');
    const progressText  = document.getElementById('progressText');
    const requiredInputs = form?.querySelectorAll('[data-required="true"]');

    function updateProgress() {
        if (!requiredInputs?.length) return;
        let filledCount = 0;
        requiredInputs.forEach(input => { if (input.value.trim() !== '') filledCount++; });
        const progress = (filledCount / requiredInputs.length) * 100;
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (progressText) {
            if (progress === 0)        progressText.textContent = 'Mulai isi formulir';
            else if (progress < 50)    progressText.textContent = `Pengisian: ${Math.round(progress)}%`;
            else if (progress < 100)   progressText.textContent = `Hampir selesai: ${Math.round(progress)}%`;
            else                       progressText.textContent = '✓ Formulir lengkap, siap dikirim!';
        }
    }

    requiredInputs?.forEach(input => input.addEventListener('input', updateProgress));
    updateProgress();

    // ── Form Submit + reCAPTCHA v3 ────────────────────────────────────────
    const submitBtn     = document.getElementById('submitBtn');
    const submitIcon    = document.getElementById('submitIcon');
    const submitSpinner = document.getElementById('submitSpinner');
    const submitText    = document.getElementById('submitText');
    const recaptchaInput = document.getElementById('recaptchaToken');

    form?.addEventListener('submit', function (e) {
        e.preventDefault();

        // Loading state
        submitBtn.disabled = true;
        submitIcon.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        submitText.textContent = 'Memverifikasi...';

        // Ambil token reCAPTCHA v3 dulu, baru submit
        grecaptcha.ready(function () {
            grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'contact_form' }).then(function (token) {
                recaptchaInput.value = token;
                submitText.textContent = 'Mengirim...';
                form.submit();
            }).catch(function () {
                // Kalau reCAPTCHA gagal, tetap submit (fallback)
                form.submit();
            });
        });
    });

    // ── Reset Form ────────────────────────────────────────────────────────
    const resetBtn = document.getElementById('resetBtn');

    resetBtn?.addEventListener('click', function () {
        topicBtns.forEach(b => b.classList.remove('active'));
        if (charCount) { charCount.textContent = '0 / 2000'; charCount.classList.remove('warning', 'danger'); }
        securityNoticeShown = false;
        setTimeout(updateProgress, 100);
    });

    // ── Auto-dismiss alerts ───────────────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert-animate').forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity    = '0';
            alert.style.transform  = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        });
    }, 6000);

})();
</script>
@endpush