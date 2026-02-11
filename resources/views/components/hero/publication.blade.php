@props([
'badgeText' => 'Ruang antara jurnal dan opini bebas',
'slides' => [
[
'image' => 'assets/images/thumbnails/lms.jpg',
'title' => 'Tulis dengan data. Terbit dengan tanggung jawab.',
'excerpt' => 'Platform publikasi akademik non-formal untuk penulis yang ingin naskahnya lebih rapi, risetnya lebih kuat,
dan prosesnya lebih jelas—tanpa klaim peer-review jurnal.',
'url' => '/submission-guidelines',
'alt' => 'Publikasi akademik berbasis data',
],
[
'image' => 'assets/images/thumbnails/event.jpg',
'title' => 'Dari ide ke publikasi. Kami dampingi prosesnya.',
'excerpt' => 'Dapatkan feedback editorial untuk struktur, argumentasi, dan sitasi. Publikasikan karya Anda dengan
standar akademik yang terukur dan kredibel.',
'url' => '/publikasi',
'alt' => 'Proses editorial akademik',
],
[
'image' => 'assets/images/thumbnails/konsultasi.jpg',
'title' => 'Riset kuat. Tulisan jelas. Publikasi etis.',
'excerpt' => 'Tidak sekadar opini. Tidak sekaku jurnal. BHAYASCIENTIA hadir sebagai jembatan antara pemikiran ilmiah dan
aksesibilitas publik.',
'url' => '/tentang',
'alt' => 'Standar etika akademik',
],
],
'arrowIcon' => 'assets/images/icons/arrow.svg',
])


<section id="Featured" data-featured-carousel
    class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">
    <div class="relative">
        {{-- Carousel --}}
        <div class="w-full main-carousel">
            @foreach ($slides as $slide)
            <article
                class="featured-news-card relative flex h-[420px] w-full overflow-hidden sm:h-[360px] md:h-[480px] lg:h-[550px]">
                {{-- Gambar slide --}}
                <img src="{{ asset($slide['image']) }}" class="absolute inset-0 object-cover w-full h-full"
                    alt="{{ $slide['alt'] ?? 'thumbnail' }}">

                {{-- Overlay gradient --}}
                <div
                    class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] to-[rgba(0,0,0,0.9)]">
                </div>

                {{-- Konten --}}
                <div class="relative z-20 flex w-full h-full pb-6 items_end md:pb-10">
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
