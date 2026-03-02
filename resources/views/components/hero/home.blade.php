@props([
'title' => 'DABRAKA, portal pengabdian intelektual.',
'description' => 'Menghimpun gagasan dan karya insan Polri yang berkolaborasi dengan intelektual di bidang kepolisian,
keamanan, kebijakan publik, serta keilmuan terkait lainnya.',
'primaryLabel' => 'Mulai gratis',
'primaryUrl' => 'https://dabraka.rbnset.me/login',

// Demo video
'secondaryLabel' => 'Lihat demo',
'youtubeId' => 'https://www.instagram.com/reel/DRTjxJCEThZ/?igsh=Z3o4czNodDUxOG4=',

// Badge
'badgeText' => 'Where Knowledge Shapes Policing.',
'badgeIcon' => 'assets/icons/crown.svg',

// Thumbnails
'imageMain' => 'assets/images/thumbnails/main.png',
'imageBottomLeft' => 'assets/images/thumbnails/founder-quote.png',
'imageTopRight' => 'assets/images/thumbnails/tagline.png',
])

<section class="relative w-full overflow-hidden bg-gradient-to-b from-white to-[#F8F9FC] pb-12 sm:pb-16 lg:pb-20">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

        {{-- ✨ MOBILE: Image First, Text Below --}}
        <div class="flex flex-col gap-8 pt-8 lg:hidden sm:gap-10">

            {{-- Media (Mobile First) --}}
            <div class="relative mx-auto w-full max-w-[560px] order-1 anim-hero-fade-in">
                <div
                    class="relative mx-auto h-[280px] w-full max-w-[400px] overflow-hidden rounded-2xl sm:h-[380px] sm:max-w-[480px] shadow-2xl">
                    <img src="{{ asset($imageMain) }}" alt="BHAYASCIENTIA Platform Overview"
                        class="object-cover w-full h-full" loading="eager">
                </div>

                {{-- Floating Card - Bottom Left --}}
                <div class="absolute -bottom-4 -left-4 h-auto w-[180px] drop-shadow-2xl sm:w-[240px] animate-float">
                    <img src="{{ asset($imageBottomLeft) }}" alt="Review Process"
                        class="w-full h-auto border-4 border-white rounded-xl sm:rounded-2xl" loading="eager">
                </div>

                {{-- Floating Card - Top Right --}}
                <div
                    class="absolute -top-4 -right-4 h-auto w-[100px] drop-shadow-2xl sm:w-[120px] animate-float-delayed">
                    <img src="{{ asset($imageTopRight) }}" alt="Citation Manager"
                        class="w-full h-auto border-4 border-white rounded-lg sm:rounded-xl" loading="eager">
                </div>
            </div>

            {{-- Text Content (Mobile Second) --}}
            <div class="flex flex-col order-2 gap-5 sm:gap-6 anim-hero-fade-up">

                {{-- Badge --}}
                <div
                    class="inline-flex items-center self-start bg-gradient-to-r from-orange-50 to-red-50 px-4 py-2 gap-2.5 rounded-full border-2 border-orange-200 shadow-sm">
                    <div class="flex w-5 h-5 overflow-hidden shrink-0">
                        <img src="{{ asset($badgeIcon) }}" class="object-contain w-full h-full" alt="Premium badge"
                            loading="eager">
                    </div>
                    <p class="font-bold text-sm text-[#FF6B18]">{{ $badgeText }}</p>
                </div>

                {{-- Title --}}
                <h1 class="text-[28px] font-black leading-tight text-[#111827] sm:text-[36px]">
                    <mark
                        class="inline-block rounded-lg bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-2 py-1 text-white">
                        DABRAKA
                    </mark>
                    <br class="sm:hidden">
                    portal pengabdian
                    <mark
                        class="inline-block rounded-lg bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-2 py-1 text-white">
                        intelektual
                    </mark>
                </h1>

                {{-- Description --}}
                <p class="text-sm leading-relaxed text-[#6B7280] sm:text-base max-w-lg">
                    {{ $description }}
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                    <a href="{{ $primaryUrl }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 hover:shadow-[0_10px_30px_0_rgba(255,107,24,0.5)] hover:-translate-y-0.5 sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        {{ $primaryLabel }}
                    </a>

                    <button type="button" data-video-open data-youtube-id="{{ $youtubeId }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#111827] bg-white px-6 py-3.5 text-sm font-bold text-[#111827] transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:text-white sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                        </svg>
                        {{ $secondaryLabel }}
                    </button>
                </div>

                {{-- Trust Indicators (Optional) --}}
                <div class="flex items-center gap-4 pt-2 text-xs text-[#6B7280]">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">100% Gratis</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Hak Cipta Terlindungi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✨ DESKTOP: Side by Side (Text Left, Image Right) --}}
        <div class="hidden lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center lg:pt-16">

            {{-- Text Content (Desktop First) --}}
            <div class="flex flex-col gap-6 anim-hero-fade-up">

                {{-- Badge --}}
                <div
                    class="inline-flex items-center self-start bg-gradient-to-r from-orange-50 to-red-50 px-4 py-2 gap-2.5 rounded-full border-2 border-orange-200 shadow-sm">
                    <div class="flex w-5 h-5 overflow-hidden shrink-0">
                        <img src="{{ asset($badgeIcon) }}" class="object-contain w-full h-full" alt="Premium badge">
                    </div>
                    <p class="font-bold text-sm text-[#FF6B18]">{{ $badgeText }}</p>
                </div>

                {{-- Title --}}
                <h1 class="text-[42px] font-black leading-tight text-[#111827] xl:text-[50px]">
                    <mark
                        class="inline-block rounded-lg bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-2 py-1 text-white">
                        DABRAKA
                    </mark>
                    <br>
                    portal pengabdian
                    <br>
                    <mark
                        class="inline-block rounded-lg bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-2 py-1 text-white">
                        intelektual
                    </mark>
                </h1>

                {{-- Description --}}
                <p class="text-base leading-relaxed text-[#6B7280] lg:text-lg max-w-xl">
                    {{ $description }}
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col gap-4 sm:flex-row">
                    <a href="{{ $primaryUrl }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-7 py-4 text-base font-bold text-white transition-all duration-300 hover:shadow-[0_10px_30px_0_rgba(255,107,24,0.5)] hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        {{ $primaryLabel }}
                    </a>

                    <button type="button" data-video-open data-youtube-id="{{ $youtubeId }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#111827] bg-white px-7 py-4 text-base font-bold text-[#111827] transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                        </svg>
                        {{ $secondaryLabel }}
                    </button>
                </div>

                {{-- Trust Indicators --}}
                <div class="flex items-center gap-6 pt-4 text-sm text-[#6B7280]">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">100% Gratis</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Hak Cipta Terlindungi</span>
                    </div>
                </div>
            </div>

            {{-- Media (Desktop Second) --}}
            <div class="relative mx-auto w-full max-w-[550px] anim-hero-fade-in">
                <div
                    class="relative mx-auto h-[450px] w-full max-w-[450px] overflow-hidden rounded-3xl shadow-2xl xl:h-[506px] xl:max-w-[500px]">
                    <img src="{{ asset($imageMain) }}" alt="BHAYASCIENTIA Platform Overview"
                        class="object-cover w-full h-full">
                </div>

                {{-- Floating Card - Bottom Left --}}
                <div class="absolute -bottom-6 -left-8 h-auto w-[280px] drop-shadow-2xl xl:w-[316px] animate-float">
                    <img src="{{ asset($imageBottomLeft) }}" alt="Review Process"
                        class="w-full h-auto border-4 border-white rounded-2xl">
                </div>

                {{-- Floating Card - Top Right --}}
                <div
                    class="absolute -top-6 -right-6 h-auto w-[120px] drop-shadow-2xl xl:w-[136px] animate-float-delayed">
                    <img src="{{ asset($imageTopRight) }}" alt="Citation Manager"
                        class="w-full h-auto border-4 border-white rounded-xl">
                </div>
            </div>
        </div>
    </div>

    {{-- Decorative Background Elements --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-orange-200 rounded-full opacity-10 blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 bg-red-200 rounded-full w-96 h-96 opacity-10 blur-3xl -z-10"></div>
</section>

{{-- ✨ ENHANCED VIDEO MODAL --}}
<div id="video-modal" class="fixed inset-0 z-[9999] items-center justify-center hidden p-4 bg-black/70 backdrop-blur-sm"
    role="dialog" aria-modal="true" aria-labelledby="video-modal-title">

    {{-- Overlay --}}
    <button type="button" data-video-close class="absolute inset-0" aria-label="Tutup video"></button>

    {{-- Dialog --}}
    <div class="relative w-full max-w-4xl overflow-hidden transition-all duration-300 transform scale-95 bg-white shadow-2xl opacity-0 rounded-2xl"
        data-modal-content>

        {{-- Header --}}
        <div
            class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-200 bg-gradient-to-r from-[#FF6B18] to-[#E64627]">
            <h3 id="video-modal-title" class="flex items-center gap-2 text-base font-bold text-white sm:text-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                </svg>
                Demo DABRAKA
            </h3>

            <button type="button" data-video-close
                class="h-10 w-10 inline-flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#FF6B18]">
                <span class="sr-only">Tutup modal</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Video Container --}}
        <div class="bg-black">
            <iframe id="videoFrame" class="w-full aspect-video" src="" title="Demo DABRAKA" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
            </iframe>
        </div>
    </div>
</div>

{{-- Styles --}}
@pushOnce('styles')
<style>
    /* Float animations */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    @keyframes float-delayed {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-12px);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float-delayed 5s ease-in-out infinite;
        animation-delay: 1s;
    }

    /* Hero animations */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .anim-hero-fade-up {
        animation: fadeUp 0.8s ease-out;
    }

    .anim-hero-fade-in {
        animation: fadeIn 0.8s ease-out 0.2s both;
    }

    /* Modal animations */
    #video-modal.show {
        display: flex !important;
    }

    #video-modal.show [data-modal-content] {
        opacity: 1;
        transform: scale(1);
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {

        .animate-float,
        .animate-float-delayed,
        .anim-hero-fade-up,
        .anim-hero-fade-in {
            animation: none !important;
        }
    }
</style>
@endPushOnce

{{-- Enhanced JavaScript --}}
@pushOnce('scripts')
<script>
    (function() {
    'use strict';

    const modal = document.getElementById('video-modal');
    const modalContent = modal?.querySelector('[data-modal-content]');
    const videoFrame = document.getElementById('videoFrame');
    const openBtns = document.querySelectorAll('[data-video-open]');
    const closeBtns = document.querySelectorAll('[data-video-close]');

    // Open modal
    openBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const youtubeId = btn.dataset.youtubeId;
            if (!youtubeId || !modal) return;

            // Set video URL with autoplay
            videoFrame.src = `https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0`;

            // Show modal with animation
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal
    function closeModal() {
        if (!modal) return;

        modal.classList.remove('show');
        videoFrame.src = '';
        document.body.style.overflow = '';
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // ESC key to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal?.classList.contains('show')) {
            closeModal();
        }
    });

    console.log('✅ Hero component initialized');
})();
</script>
@endPushOnce