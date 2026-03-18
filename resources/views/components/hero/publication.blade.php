@props([
'badgeText' => 'Where Knowledge Shapes Policing',
'slides' => [
[
'image' => '/assets/images/thumbnails/lms.jpg',
'title' => 'Portal Pengabdian Intelektual Bhayangkara',
'excerpt' => 'DABRAKA (Darma Brata Buana Cendekia): wadah publikasi terbuka untuk insan Polri & akademisi.',
'url' => '/tentang',
'alt' => 'Dabraka - Transformasi kepolisian Indonesia',
],
[
'image' => '/assets/images/thumbnails/event.jpg',
'title' => 'Jembatan Lapangan ↔ Ilmiah ↔ Kebijakan',
'excerpt' => 'Transformasi Polri butuh kekuatan gagasan. Ekosistem pengetahuan hidup untuk dinamika keamanan global.',
'url' => '/publikasi/jelajahi',
'alt' => 'Ekosistem pengetahuan kepolisian',
],
[
'image' => '/assets/images/thumbnails/konsultasi.jpg',
'title' => 'Pusat Referensi Pemikiran Kepolisian Indonesia',
'excerpt' => 'Publikasi, kajian strategis, forum ilmiah, jejaring global.',
'url' => '/publikasi/search',
'alt' => 'Pengabdian intelektual Bhayangkara',
],
],
'arrowIcon' => '/assets/images/icons/arrow.svg',
])

{{--
STRATEGI:
1. Hanya render slide pertama di HTML awal — slide 2 & 3 di-inject via JS setelah
slide 1 selesai load. Ini mencegah "3 slide kebawah" saat pertama render.
2. autoPlay pakai setInterval manual — lebih reliable di mobile daripada
Flickity autoPlay yang sering mati saat tab tidak aktif / touch event.
3. Preload gambar slide pertama via
<link rel="preload"> di

<head>.
    4. Skeleton shimmer selama gambar belum siap.
    5. Dot indicator dengan progress bar animasi.
    --}}

    @push('head')
    @if(isset($slides[0]['image']))
    <link rel="preload" as="image" href="{{ asset($slides[0]['image']) }}" fetchpriority="high">
    @endif
    @endpush

    @push('styles')
    <style>
        /* ── Carousel wrapper ── */
        #featured-carousel-wrap {
            position: relative;
            width: 100%;
            height: 420px;
            overflow: hidden;
            background: #111827;
        }

        @media (min-width: 640px) {
            #featured-carousel-wrap {
                height: 360px;
            }
        }

        @media (min-width: 768px) {
            #featured-carousel-wrap {
                height: 480px;
            }
        }

        @media (min-width: 1024px) {
            #featured-carousel-wrap {
                height: 550px;
            }
        }

        /* ── Slide ── */
        .fc-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.6s ease;
            pointer-events: none;
            z-index: 1;
        }

        .fc-slide.fc-active {
            opacity: 1;
            pointer-events: auto;
            z-index: 2;
        }

        .fc-slide img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .fc-slide .fc-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.85) 100%);
            z-index: 2;
        }

        .fc-slide .fc-content {
            position: absolute;
            inset: 0;
            z-index: 3;
            display: flex;
            align-items: flex-end;
            padding-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .fc-slide .fc-content {
                padding-bottom: 2.5rem;
            }
        }

        /* ── Skeleton shimmer ── */
        .fc-skeleton {
            position: absolute;
            inset: 0;
            z-index: 5;
            background: linear-gradient(90deg, #1a1a2e 0%, #2d2d5e 40%, #1a1a2e 80%);
            background-size: 200% 100%;
            animation: fc-shimmer 1.6s ease-in-out infinite;
            transition: opacity 0.5s ease;
        }

        .fc-skeleton.fc-skeleton-hide {
            opacity: 0;
            pointer-events: none;
        }

        @keyframes fc-shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* ── Dot + progress ── */
        .fc-dots {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .fc-dot {
            width: 28px;
            height: 4px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.35);
            overflow: hidden;
            cursor: pointer;
            transition: background 0.3s;
        }

        .fc-dot.fc-dot-active {
            background: rgba(255, 255, 255, 0.25);
        }

        .fc-dot-bar {
            height: 100%;
            width: 0%;
            background: #FF6B18;
            border-radius: 2px;
            transition: width linear;
        }

        /* ── Nav buttons ── */
        .fc-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            display: none;
        }

        @media (min-width: 768px) {
            .fc-nav {
                display: flex;
            }
        }

        .fc-nav-prev {
            left: 1.5rem;
        }

        .fc-nav-next {
            right: 1.5rem;
        }

        .fc-nav button {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            ring: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .fc-nav button:hover {
            background: rgba(255, 107, 24, 0.7);
            transform: scale(1.08);
        }

        /* ── Touch swipe hint (mobile) ── */
        @media (max-width: 767px) {
            .fc-dots {
                bottom: 0.75rem;
            }
        }
    </style>
    @endpush

    <section id="Featured" class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">

        <div id="featured-carousel-wrap">

            {{-- Skeleton — tampil saat slide pertama belum siap --}}
            <div class="fc-skeleton" id="fc-skeleton"></div>

            {{-- Slide 1 di-render langsung di HTML — slide 2 & 3 di-inject JS --}}
            <div class="fc-slide fc-active" id="fc-slide-0" data-index="0">
                <img src="{{ asset($slides[0]['image']) }}" alt="{{ $slides[0]['alt'] ?? '' }}" fetchpriority="high"
                    loading="eager" decoding="sync" id="fc-img-0" onload="fcImgLoaded(0)" onerror="fcImgLoaded(0)">
                <div class="fc-overlay"></div>
                <div class="fc-content">
                    <div class="mx-auto w-full max-w-[1130px] px-4 sm:px-6 lg:px-8">
                        <div class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                            <p class="text-xs text-white sm:text-sm">{{ $badgeText }}</p>
                            <a href="{{ $slides[0]['url'] ?? '#' }}"
                                class="text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline sm:text-2xl sm:leading-[32px] md:text-3xl md:leading-[40px] lg:text-4xl lg:leading-[45px]"
                                style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ $slides[0]['title'] }}
                            </a>
                            <p class="text-xs text-white sm:text-sm">{{ $slides[0]['excerpt'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dot indicators --}}
            <div class="fc-dots" id="fc-dots">
                @foreach($slides as $i => $slide)
                <div class="fc-dot {{ $i === 0 ? 'fc-dot-active' : '' }}" onclick="fcGoTo({{ $i }})"
                    id="fc-dot-{{ $i }}">
                    <div class="fc-dot-bar" id="fc-bar-{{ $i }}"></div>
                </div>
                @endforeach
            </div>

            {{-- Nav buttons (desktop) --}}
            <div class="fc-nav fc-nav-prev">
                <button onclick="fcPrev()" aria-label="Previous">
                    <img src="{{ asset($arrowIcon) }}" alt="prev" width="16" height="16">
                </button>
            </div>
            <div class="fc-nav fc-nav-next">
                <button onclick="fcNext()" aria-label="Next" style="transform:translateY(-50%) rotate(180deg);">
                    <img src="{{ asset($arrowIcon) }}" alt="next" width="16" height="16">
                </button>
            </div>

        </div>
    </section>

    @push('scripts')
    <script>
        (function () {
    // ── Data slides dari blade ──────────────────────────────────────────────
    const SLIDES = @json($slides);
    const BADGE  = @json($badgeText);
    const INTERVAL_MS = 5000; // durasi tiap slide

    let current      = 0;
    let total        = SLIDES.length;
    let timer        = null;
    let barTimer     = null;
    let injected     = false; // flag: slide 2 & 3 sudah di-inject?
    let paused       = false;

    // ── Sembunyikan skeleton setelah slide 1 load ───────────────────────────
    window.fcImgLoaded = function (index) {
        if (index === 0) {
            const skeleton = document.getElementById('fc-skeleton');
            if (skeleton) {
                skeleton.classList.add('fc-skeleton-hide');
                setTimeout(() => skeleton.remove(), 500);
            }
            // Inject slide 2 & 3 setelah slide 1 siap — tidak bloking render awal
            setTimeout(injectRemainingSlides, 100);
        }
    };

    // ── Inject slide 2 & 3 secara progresif ────────────────────────────────
    function injectRemainingSlides() {
        if (injected) return;
        injected = true;

        const wrap = document.getElementById('featured-carousel-wrap');
        const dots = document.getElementById('fc-dots');

        SLIDES.forEach(function (slide, i) {
            if (i === 0) return; // slide 0 sudah ada di HTML

            const el = document.createElement('div');
            el.className    = 'fc-slide';
            el.id           = 'fc-slide-' + i;
            el.dataset.index = i;

            el.innerHTML = `
                <img
                    src="${assetUrl(slide.image)}"
                    alt="${escHtml(slide.alt || '')}"
                    loading="lazy"
                    decoding="async"
                    onload="fcImgLoaded(${i})"
                    onerror="fcImgLoaded(${i})">
                <div class="fc-overlay"></div>
                <div class="fc-content">
                    <div class="mx-auto w-full max-w-[1130px] px-4 sm:px-6 lg:px-8">
                        <div class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                            <p class="text-xs text-white sm:text-sm">${escHtml(BADGE)}</p>
                            <a href="${escHtml(slide.url || '#')}"
                                class="text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline sm:text-2xl sm:leading-[32px] md:text-3xl md:leading-[40px] lg:text-4xl lg:leading-[45px]"
                                style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                ${escHtml(slide.title)}
                            </a>
                            <p class="text-xs text-white sm:text-sm">${escHtml(slide.excerpt)}</p>
                        </div>
                    </div>
                </div>`;

            // Insert sebelum dots agar z-order benar
            wrap.insertBefore(el, dots);
        });

        // Mulai autoplay setelah semua slide siap
        startAutoplay();
    }

    // ── Go to slide ─────────────────────────────────────────────────────────
    window.fcGoTo = function (index) {
        if (index === current) return;

        // Nonaktifkan slide lama
        const oldSlide = document.getElementById('fc-slide-' + current);
        const oldDot   = document.getElementById('fc-dot-' + current);
        const oldBar   = document.getElementById('fc-bar-' + current);
        if (oldSlide) oldSlide.classList.remove('fc-active');
        if (oldDot)   oldDot.classList.remove('fc-dot-active');
        if (oldBar)   { oldBar.style.transition = 'none'; oldBar.style.width = '0%'; }

        current = (index + total) % total;

        // Aktifkan slide baru
        const newSlide = document.getElementById('fc-slide-' + current);
        const newDot   = document.getElementById('fc-dot-' + current);
        if (newSlide) newSlide.classList.add('fc-active');
        if (newDot)   newDot.classList.add('fc-dot-active');

        resetTimer();
    };

    window.fcNext = function () { fcGoTo(current + 1); };
    window.fcPrev = function () { fcGoTo(current - 1); };

    // ── Progress bar animasi ─────────────────────────────────────────────────
    function startBar() {
        stopBar();
        const bar = document.getElementById('fc-bar-' + current);
        if (!bar) return;
        bar.style.transition = 'none';
        bar.style.width = '0%';
        // Force reflow
        bar.getBoundingClientRect();
        bar.style.transition = 'width ' + INTERVAL_MS + 'ms linear';
        bar.style.width = '100%';
    }

    function stopBar() {
        for (let i = 0; i < total; i++) {
            const bar = document.getElementById('fc-bar-' + i);
            if (bar && i !== current) {
                bar.style.transition = 'none';
                bar.style.width = '0%';
            }
        }
    }

    // ── Autoplay ─────────────────────────────────────────────────────────────
    function startAutoplay() {
        stopAutoplay();
        startBar();
        timer = setInterval(function () {
            if (!paused) fcGoTo(current + 1);
        }, INTERVAL_MS);
    }

    function stopAutoplay() {
        if (timer) clearInterval(timer);
        timer = null;
    }

    function resetTimer() {
        stopAutoplay();
        startAutoplay();
    }

    // ── Touch / swipe support (mobile) ───────────────────────────────────────
    let touchStartX = 0;
    let touchStartY = 0;

    document.addEventListener('DOMContentLoaded', function () {
        const wrap = document.getElementById('featured-carousel-wrap');
        if (!wrap) return;

        wrap.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            paused = true;
        }, { passive: true });

        wrap.addEventListener('touchend', function (e) {
            const dx = e.changedTouches[0].clientX - touchStartX;
            const dy = e.changedTouches[0].clientY - touchStartY;

            // Hanya proses swipe horizontal (bukan scroll vertikal)
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                if (dx < 0) fcGoTo(current + 1); // swipe kiri → next
                else        fcGoTo(current - 1); // swipe kanan → prev
            }

            paused = false;
        }, { passive: true });

        // Pause saat hover (desktop)
        wrap.addEventListener('mouseenter', function () { paused = true; });
        wrap.addEventListener('mouseleave', function () { paused = false; });
    });

    // ── Helpers ──────────────────────────────────────────────────────────────
    function assetUrl(path) {
        // Sama seperti Laravel asset() — prepend APP_URL jika path relatif
        if (path.startsWith('http')) return path;
        return '{{ rtrim(asset(''), '/') }}' + '/' + path.replace(/^\//, '');
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
    </script>
    @endpush