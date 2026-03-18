@props([
'badgeText' => 'Where Knowledge Shapes Policing',
'slides' => [
[
'image' => '/assets/images/thumbnails/lms.jpg',
'title' => 'Portal Pengabdian Intelektual Bhayangkara',
'excerpt' => 'DABRAKA (Darma Brata Buana Cendekia): wadah publikasi terbuka untuk insan Polri & akademisi. Kontribusi
pemikiran ilmu kepolisian, keamanan publik, dan kebijakan evidence-based.',
'url' => '/tentang',
'alt' => 'Dabraka - Transformasi kepolisian Indonesia',
],
[
'image' => '/assets/images/thumbnails/event.jpg',
'title' => 'Jembatan Lapangan ↔ Ilmiah ↔ Kebijakan',
'excerpt' => 'Transformasi Polri butuh kekuatan gagasan. Dari pengalaman praktisi Bhayangkara ke refleksi akademik
berkelanjutan. Ekosistem pengetahuan hidup untuk dinamika keamanan global.',
'url' => '/publikasi/jelajahi',
'alt' => 'Ekosistem pengetahuan kepolisian',
],
[
'image' => '/assets/images/thumbnails/konsultasi.jpg',
'title' => 'Pusat Referensi Pemikiran Kepolisian Indonesia',
'excerpt' => 'Publikasi, kajian strategis, forum ilmiah, jejaring global. Pengabdian intelektual untuk memperkuat
institusi Polri, melayani masyarakat, dan kemajuan bangsa.',
'url' => '/publikasi/search',
'alt' => 'Pengabdian intelektual Bhayangkara',
],
],
'arrowIcon' => '/assets/images/icons/arrow.svg',
])

{{--
PERFORMA FIX:
1. Preload gambar slide pertama di

<head> — paling impactful
    2. Slide 1: eager + fetchpriority high
    3. Slide 2 & 3: loading="lazy" — tidak di-download sampai dibutuhkan
    4. Skeleton shimmer selama gambar slide 1 belum siap
    5. Flickity: imagesLoaded=false agar tidak tunggu semua gambar
    6. Fix mobile autoplay: gunakan visibility API + manual resume
    --}}

    @push('head')
    <link rel="preload" as="image" href="{{ asset($slides[0]['image']) }}" fetchpriority="high">
    @endpush

    <style>
        /* ── Skeleton shimmer ── */
        #fc-skeleton {
            position: absolute;
            inset: 0;
            z-index: 10;
            background: linear-gradient(90deg, #111827 0%, #1f2937 45%, #111827 90%);
            background-size: 200% 100%;
            animation: fc-shimmer 1.6s ease-in-out infinite;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        #fc-skeleton.hide {
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

        /* ── Pastikan semua slide tidak stack vertikal sebelum Flickity init ── */
        .main-carousel .featured-news-card {
            /* Sebelum Flickity init, sembunyikan slide 2 & 3 */
            display: none;
        }

        /* Flickity akan override display setelah init */
        .flickity-enabled .featured-news-card {
            display: flex !important;
        }

        /* Slide pertama tetap visible sebelum Flickity init */
        .main-carousel .featured-news-card:first-child {
            display: flex;
        }
    </style>

    <section id="Featured" data-featured-carousel
        class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">
        <div class="relative">

            {{-- Skeleton — tampil sampai gambar slide 1 selesai load --}}
            <div id="fc-skeleton"></div>

            {{-- Carousel --}}
            <div class="w-full main-carousel">
                @foreach ($slides as $index => $slide)
                <article
                    class="featured-news-card relative flex h-[420px] w-full overflow-hidden sm:h-[360px] md:h-[480px] lg:h-[550px]"
                    style="min-width: 100%;">

                    {{-- ✅ Slide 1: eager + fetchpriority high (prioritas tertinggi di browser) --}}
                    {{-- ✅ Slide 2 & 3: lazy — tidak di-download sampai hampir terlihat --}}
                    <img src="{{ asset($slide['image']) }}" class="absolute inset-0 object-cover w-full h-full"
                        alt="{{ $slide['alt'] ?? 'thumbnail' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                        decoding="{{ $index === 0 ? 'sync' : 'async' }}" @if($index===0)
                        onload="document.getElementById('fc-skeleton')?.classList.add('hide')" @endif
                        onerror="this.style.backgroundColor='#111827'; @if($index === 0) document.getElementById('fc-skeleton')?.classList.add('hide'); @endif"
                        style="display:block !important; opacity:1 !important; visibility:visible !important; width:100% !important; height:100% !important; object-fit:cover !important;">

                    {{-- Overlay gradient --}}
                    <div
                        class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] to-[rgba(0,0,0,0.9)]">
                    </div>

                    {{-- Konten --}}
                    <div class="relative z-20 flex items-end w-full h-full pb-6 md:pb-10">
                        <div class="mx-auto flex w-full max-w-[1130px] items-end justify-between px-4 sm:px-6 lg:px-8">
                            <div
                                class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                                <p class="text-xs text-white sm:text-sm">{{ $badgeText }}</p>
                                <a href="{{ $slide['url'] ?? '#' }}"
                                    class="two-lines text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline sm:text-2xl sm:leading-[32px] md:text-3xl md:leading-[40px] lg:text-4xl lg:leading-[45px]">
                                    {{ $slide['title'] }}
                                </a>
                                <p class="text-xs text-white sm:text-sm">{{ $slide['excerpt'] }}</p>
                            </div>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>

                </article>
                @endforeach
            </div>

            {{-- Tombol navigasi (desktop) --}}
            <div class="absolute inset-0 z-30 hidden pointer-events-none md:block">
                <div class="mx-auto flex h-full w-full max-w-[1130px] items-center justify-end px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4 pointer-events-auto">
                        <button type="button" data-carousel-prev aria-label="Previous"
                            class="flex h-[38px] w-[38px] items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="prev" width="16" height="16">
                        </button>
                        <button type="button" data-carousel-next aria-label="Next"
                            class="flex h-[38px] w-[38px] rotate-180 items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="next" width="16" height="16">
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.querySelector('.main-carousel');
    if (!carousel || typeof Flickity === 'undefined') return;

    const flkty = new Flickity(carousel, {
        cellAlign      : 'left',
        contain        : true,
        prevNextButtons: false,
        pageDots       : true,       // dot bawaan Flickity
        wrapAround     : true,
        autoPlay       : 5000,
        imagesLoaded   : false,      // ✅ jangan tunggu semua gambar — langsung init
        lazyLoad       : false,
        adaptiveHeight : false,
        draggable      : true,
        freeScroll     : false,
        percentPosition: true,
        setGallerySize : true,
    });

    // ── Nav buttons ──────────────────────────────────────────────────────────
    document.querySelector('[data-carousel-prev]')
        ?.addEventListener('click', () => flkty.previous());
    document.querySelector('[data-carousel-next]')
        ?.addEventListener('click', () => flkty.next());

    // ── Pause on hover (desktop) ──────────────────────────────────────────
    carousel.addEventListener('mouseenter', () => flkty.stopPlayer());
    carousel.addEventListener('mouseleave', () => flkty.playPlayer());

    // ── FIX MOBILE AUTOPLAY ───────────────────────────────────────────────
    // Flickity autoPlay berhenti saat tab tidak aktif atau layar terkunci.
    // Page Visibility API: resume autoPlay saat tab aktif kembali.
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            flkty.playPlayer();
        } else {
            flkty.stopPlayer();
        }
    });

    // Flickity di mobile kadang berhenti setelah touch.
    // Resume autoPlay setelah user selesai swipe.
    flkty.on('dragEnd', function () {
        flkty.playPlayer();
    });

    // ── Prefetch gambar slide berikutnya saat slide berubah ──────────────
    // Slide 2 & 3 pakai loading="lazy" — kita trigger prefetch
    // saat Flickity hampir menampilkan slide tersebut
    flkty.on('change', function (index) {
        const nextIndex = (index + 1) % flkty.slides.length;
        const nextCell  = flkty.slides[nextIndex]?.cells[0]?.element;
        if (nextCell) {
            const img = nextCell.querySelector('img');
            if (img && img.getAttribute('loading') === 'lazy') {
                img.setAttribute('loading', 'eager');
            }
        }
    });
});
    </script>
    @endpush