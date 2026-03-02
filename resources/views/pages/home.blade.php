@extends('layouts.app')

@section('title', 'Beranda')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('content')
{{-- ✨ Target anchor untuk scroll ke atas --}}
<div id="top-anchor"></div>

{{-- ✨ Wrapper dengan overflow control --}}
<div class="w-full overflow-x-hidden">
    <x-hero.home badge-icon="assets/images/icons/crown.svg" badge-text="Where Knowledge Shapes Policing."
        youtube-id="rJQOQCe30EY" />

    <x-sections.steps />
    <x-sections.featured-tabs />
    <x-sections.testimoni />
    <x-sections.coming-soon />
    <x-sections.faq />
</div>

{{-- ✨ Scroll to Top - Right Position + Scroll UP Trigger --}}
<button id="scrollToTop" title="Kembali ke atas" aria-label="Scroll ke atas halaman"
    class="fixed hidden p-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] hover:from-[#E64627] hover:to-[#FF6B18] text-white rounded-full shadow-lg hover:shadow-orange-500/50 active:shadow-orange-600/75 transition-all duration-300 z-50 border-0 focus:outline-none focus:ring-4 focus:ring-orange-300/50 active:scale-[0.95] right-6 bottom-6 w-[52px] h-[52px] md:w-[56px] md:h-[56px] md:right-8 md:bottom-8"
    style="--tw-ring-color: rgba(255,107,24,0.5);">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        class="w-5 h-5 mx-auto md:w-6 md:h-6">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
</button>


@endsection
@push('scripts')
<script>
    (function() {
    'use strict';

    const scrollBtn = document.getElementById('scrollToTop');
    const topAnchor = document.getElementById('top-anchor');
    let lastScrollY = window.scrollY;
    let ticking = false;

    const THRESHOLD = 300;
    const BOTTOM_THRESHOLD = 0.9; // 90% document height

    function updateScrollBtn() {
        const currentScrollY = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const isNearBottom = (currentScrollY / docHeight) > BOTTOM_THRESHOLD;

        // Show jika: (scroll UP > threshold) ATAU sudah di bottom
        const shouldShow = (currentScrollY > THRESHOLD && currentScrollY < lastScrollY) || isNearBottom;

        if (shouldShow) {
            scrollBtn.classList.remove('hidden');
            scrollBtn.classList.add('show-smart');
        } else {
            scrollBtn.classList.add('hidden');
            scrollBtn.classList.remove('show-smart');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    }

    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateScrollBtn);
            ticking = true;
        }
    }

    // Resize handler untuk dynamic content
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(requestTick, 250);
    });

    window.addEventListener('scroll', requestTick, { passive: true });

    scrollBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        topAnchor.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        if ('vibrate' in navigator) navigator.vibrate(30);
        scrollBtn.classList.add('hidden');
    });

    scrollBtn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });

    console.log('🚀 Smart Scroll to Top: UP scroll + Bottom trigger active');
})();
</script>

<style>
    /* Enhanced animations untuk dual trigger */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    @keyframes slideInBottom {
        from {
            opacity: 0;
            transform: translateY(100%) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .show-smart {
        animation: slideInRight 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    html {
        scroll-behavior: smooth;
    }

    #scrollToTop:hover {
        transform: translateY(-2px) scale(1.05);
    }

    #scrollToTop:active {
        transform: translateY(0) scale(0.98);
    }
</style>
@endpush