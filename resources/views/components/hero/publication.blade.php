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
✅ FIX PERFORMA:
1. Slide pertama pakai
<link rel="preload"> agar browser download gambar
lebih awal, sebelum HTML selesai di-parse — paling impactful
2. Slide 2 & 3 pakai loading="lazy" — tidak di-download sampai hampir terlihat
3. Skeleton placeholder (warna abu abu) tampil saat gambar belum siap
sehingga tidak ada blank putih yang mengganggu
4. fetchpriority="high" pada gambar slide pertama
5. Tidak ada compress di PHP — compress gambar dari sumbernya (TinyPNG/Squoosh)
adalah cara paling efektif, target <200KB per slide --}} {{-- ✅ Preload gambar slide pertama — diletakkan di <head> via
    @push --}}
    @push('head')
    @if(isset($slides[0]['image']))
    <link rel="preload" as="image" href="{{ asset($slides[0]['image']) }}" fetchpriority="high">
    @endif
    @endpush

    <style>
        /* Skeleton shimmer saat gambar belum load */
        .slide-skeleton {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg,
                    #1a1a2e 0%,
                    #2a2a4e 40%,
                    #1a1a2e 80%);
            background-size: 200% 100%;
            animation: shimmer 1.8s ease-in-out infinite;
            z-index: 1;
            transition: opacity 0.4s ease;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* Hilangkan skeleton setelah gambar load */
        .slide-img-loaded+.slide-skeleton,
        .slide-skeleton.hidden-skeleton {
            opacity: 0;
            pointer-events: none;
        }

        .featured-news-card img {
            /* Pastikan gambar tidak invisible saat flickity manipulasi DOM */
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>

    <section id="Featured" data-featured-carousel
        class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">
        <div class="relative">

            <div class="w-full main-carousel">
                @foreach ($slides as $index => $slide)
                <article
                    class="featured-news-card relative flex h-[420px] w-full overflow-hidden sm:h-[360px] md:h-[480px] lg:h-[550px]"
                    style="min-width: 100%;">

                    {{-- ✅ Skeleton placeholder — tampil saat gambar belum siap --}}
                    <div class="slide-skeleton" id="skeleton-{{ $index }}"></div>

                    {{-- ✅ Gambar:
                    - Slide pertama: eager + fetchpriority high (prioritas tertinggi)
                    - Slide lain: lazy (tidak di-download dulu)
                    --}}
                    <img src="{{ asset($slide['image']) }}" class="absolute inset-0 object-cover w-full h-full"
                        alt="{{ $slide['alt'] ?? 'thumbnail' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $index === 0 ? 'high' : 'low' }}"
                        decoding="{{ $index === 0 ? 'sync' : 'async' }}"
                        style="z-index:2; display:block; width:100%; height:100%; object-fit:cover;"
                        data-slide-index="{{ $index }}" onload="hideSkeleton({{ $index }})"
                        onerror="hideSkeleton({{ $index }}); this.style.backgroundColor='#1a1a2e';">

                    {{-- Overlay gradient --}}
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[rgba(0,0,0,0)] to-[rgba(0,0,0,0.9)]"
                        style="z-index:3;"></div>

                    {{-- Konten --}}
                    <div class="relative flex items-end w-full h-full pb-6 md:pb-10" style="z-index:4;">
                        <div class="mx-auto flex w-full max-w-[1130px] items-end justify-between px-4 sm:px-6 lg:px-8">
                            <div
                                class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                                <p class="text-xs text-white sm:text-sm">
                                    {{ $badgeText }}
                                </p>
                                <a href="{{ $slide['url'] ?? '#' }}"
                                    class="two-lines text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline sm:text-2xl sm:leading-[32px] md:text-3xl md:leading-[40px] lg:text-4xl lg:leading-[45px]">
                                    {{ $slide['title'] }}
                                </a>
                                <p class="text-xs text-white sm:text-sm">
                                    {{ $slide['excerpt'] }}
                                </p>
                            </div>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>

                </article>
                @endforeach
            </div>

            {{-- Tombol navigasi --}}
            <div class="absolute inset-0 z-30 hidden pointer-events-none md:block">
                <div class="mx-auto flex h-full w-full max-w-[1130px] items-center justify-end px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4 pointer-events-auto">
                        <button type="button" data-carousel-prev aria-label="Previous"
                            class="flex h-[38px] w-[38px] items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="arrow" width="16" height="16">
                        </button>
                        <button type="button" data-carousel-next aria-label="Next"
                            class="flex h-[38px] w-[38px] rotate-180 items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="arrow" width="16" height="16">
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @push('scripts')
    <script>
        // ✅ Hilangkan skeleton setelah gambar selesai load
function hideSkeleton(index) {
    const skeleton = document.getElementById('skeleton-' + index);
    if (skeleton) {
        skeleton.classList.add('hidden-skeleton');
        // Hapus dari DOM setelah animasi selesai agar tidak makan memori
        setTimeout(() => skeleton.remove(), 400);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.querySelector('.main-carousel');
    if (!carousel || typeof Flickity === 'undefined') return;

    const flkty = new Flickity(carousel, {
        cellAlign      : 'left',
        contain        : true,
        prevNextButtons: false,
        pageDots       : false,
        wrapAround     : true,
        autoPlay       : 5000,
        imagesLoaded   : false, // ✅ false — jangan tunggu semua gambar load baru init
        lazyLoad       : false,
        adaptiveHeight : false,
        draggable      : true,
        freeScroll     : false,
        percentPosition: true,
        setGallerySize : true,
    });

    // ✅ Saat pindah slide, trigger load gambar slide berikutnya
    // (workaround untuk lazy load + flickity)
    flkty.on('change', function (index) {
        const nextIndex = (index + 1) % flkty.slides.length;
        const nextSlide = flkty.slides[nextIndex]?.cells[0]?.element;
        if (nextSlide) {
            const img = nextSlide.querySelector('img');
            if (img && img.getAttribute('loading') === 'lazy') {
                // Paksa browser mulai download gambar berikutnya
                img.setAttribute('loading', 'eager');
            }
        }
    });

    // Custom nav buttons
    document.querySelector('[data-carousel-prev]')
        ?.addEventListener('click', () => flkty.previous());
    document.querySelector('[data-carousel-next]')
        ?.addEventListener('click', () => flkty.next());

    // Pause on hover
    carousel.addEventListener('mouseenter', () => flkty.pausePlayer());
    carousel.addEventListener('mouseleave', () => flkty.unpausePlayer());
});
    </script>
    @endpush