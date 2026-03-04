<script>
    (function () {
    'use strict';

    const scrollBtn = document.getElementById('scrollToTop');
    const topAnchor = document.getElementById('top-anchor');
    if (!scrollBtn) return; // guard jika komponen tidak ada

    let lastScrollY = window.scrollY;
    let ticking = false;

    const THRESHOLD = 300;
    const BOTTOM_THRESHOLD = 0.9;

    function updateScrollBtn() {
        const currentScrollY = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const isNearBottom = docHeight > 0 && (currentScrollY / docHeight) > BOTTOM_THRESHOLD;

        const shouldShow = (currentScrollY > THRESHOLD && currentScrollY < lastScrollY) || isNearBottom;

        scrollBtn.classList.toggle('hidden', !shouldShow);
        scrollBtn.classList.toggle('show-smart', shouldShow);

        lastScrollY = currentScrollY;
        ticking = false;
    }

    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateScrollBtn);
            ticking = true;
        }
    }

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(requestTick, 250);
    });

    window.addEventListener('scroll', requestTick, { passive: true });

    scrollBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (topAnchor) {
            topAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        if ('vibrate' in navigator) navigator.vibrate(30);
        scrollBtn.classList.add('hidden');
    });

    scrollBtn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
})();
</script>

<style>
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