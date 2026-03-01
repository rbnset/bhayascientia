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
'url' => '/publikasi',
'alt' => 'Ekosistem pengetahuan kepolisian',
],
[
'image' => '/assets/images/thumbnails/konsultasi.jpg',
'title' => 'Pusat Referensi Pemikiran Kepolisian Indonesia',
'excerpt' => 'Publikasi, kajian strategis, forum ilmiah, jejaring global. Pengabdian intelektual untuk memperkuat
institusi Polri, melayani masyarakat, dan kemajuan bangsa.',
'url' => '/upload',
'alt' => 'Pengabdian intelektual Bhayangkara',
],
],
'arrowIcon' => '/assets/images/icons/arrow.svg',
])


<section id="Featured" data-featured-carousel
    class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">
    <div class="relative">
        {{-- Carousel --}}
        <div class="w-full main-carousel">
            @foreach ($slides as $index => $slide)
            <article
                class="featured-news-card relative flex h-[420px] w-full overflow-hidden sm:h-[360px] md:h-[480px] lg:h-[550px]"
                style="min-width: 100%;">
                {{-- ✅ Gambar slide dengan inline styles --}}
                <img src="{{ asset($slide['image']) }}" class="absolute inset-0 object-cover w-full h-full"
                    alt="{{ $slide['alt'] ?? 'thumbnail' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    style="display: block !important; opacity: 1 !important; visibility: visible !important; width: 100% !important; height: 100% !important; object-fit: cover !important;"
                    onerror="console.error('Image failed to load:', this.src); this.style.backgroundColor='#EEF0F7';">

                {{-- Overlay gradient --}}
                <div
                    class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] to-[rgba(0,0,0,0.9)]">
                </div>

                {{-- Konten --}}
                <div class="relative z-20 flex items-end w-full h-full pb-6 md:pb-10">
                    <div class="mx-auto flex w-full max-w-[1130px] items-end justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                            <p class="text-xs text-white sm:text-sm">
                                {{ $badgeText }}
                            </p>

                            <a href="{{ $slide['url'] ?? '#' }}" class="two-lines text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline
                                    sm:text-2xl sm:leading-[32px]
                                    md:text-3xl md:leading-[40px]
                                    lg:text-4xl lg:leading-[45px]">
                                {{ $slide['title'] }}
                            </a>

                            <p class="text-xs text-white sm:text-sm">
                                {{ $slide['excerpt'] }}
                            </p>
                        </div>

                        {{-- kanan dibiarkan kosong, tombol di overlay global --}}
                        <div class="hidden md:block"></div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Tombol global (tidak ikut slide) --}}
        <div class="absolute inset-0 z-30 hidden pointer-events-none md:block">
            <div class="mx-auto flex h-full w-full max-w-[1130px] items-center justify-end px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 pointer-events-auto">
                    <button type="button" data-carousel-prev aria-label="Previous"
                        class="flex h-[38px] w-[38px] items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                        <img src="{{ asset($arrowIcon) }}" alt="arrow">
                    </button>

                    <button type="button" data-carousel-next aria-label="Next"
                        class="flex h-[38px] w-[38px] rotate-180 items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                        <img src="{{ asset($arrowIcon) }}" alt="arrow">
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    console.log('🎬 Initializing Flickity carousel...');

    const carousel = document.querySelector('.main-carousel');

    if (!carousel) {
        console.error('❌ Carousel element not found');
        return;
    }

    if (typeof Flickity === 'undefined') {
        console.error('❌ Flickity library not loaded');
        return;
    }

    // ✅ Initialize Flickity
    const flkty = new Flickity(carousel, {
        cellAlign: 'left',
        contain: true,
        prevNextButtons: false,
        pageDots: false,
        wrapAround: true,
        autoPlay: 5000,
        imagesLoaded: true,
        lazyLoad: false, // Disable lazy load untuk debugging
        adaptiveHeight: false,
        draggable: true,
        freeScroll: false,
        percentPosition: true,
        setGallerySize: true
    });

    console.log('✅ Flickity initialized with', flkty.slides.length, 'slides');

    // Debug: Log setiap slide
    flkty.slides.forEach((slide, index) => {
        const img = slide.cells[0].element.querySelector('img');
        console.log(`Slide ${index + 1}:`, {
            hasImage: !!img,
            src: img?.src,
            loaded: img?.complete,
            naturalWidth: img?.naturalWidth
        });
    });

    // Custom navigation buttons
    const prevBtn = document.querySelector('[data-carousel-prev]');
    const nextBtn = document.querySelector('[data-carousel-next]');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            console.log('⬅️ Previous clicked');
            flkty.previous();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            console.log('➡️ Next clicked');
            flkty.next();
        });
    }

    // Event listener untuk track perubahan slide
    flkty.on('change', function(index) {
        console.log('📍 Slide changed to:', index + 1);

        // Force reload image jika belum loaded
        const currentSlide = flkty.slides[index].cells[0].element;
        const img = currentSlide.querySelector('img');

        if (img && !img.complete) {
            console.log('🔄 Reloading image for slide', index + 1);
            const src = img.src;
            img.src = '';
            img.src = src;
        }
    });

    // Pause on hover
    carousel.addEventListener('mouseenter', function() {
        flkty.pausePlayer();
    });

    carousel.addEventListener('mouseleave', function() {
        flkty.unpausePlayer();
    });

    console.log('✅ Carousel setup complete');
});
</script>
@endpush