@props([
'badgeText' => 'Ruang antara jurnal dan opini bebas',
'slides' => [
[
'image' => 'assets/images/thumbnails/lms.jpg',
'title' => 'Tulis dengan data. Terbit dengan tanggung jawab.',
'excerpt' => 'Dapatkan peninjauan editorial untuk struktur, argumentasi, dan etika akademik tanpa klaim peer-review
jurnal.',
'url' => '#',
'alt' => 'thumbnail',
],
[
'image' => 'assets/images/thumbnails/event.jpg',
'title' => 'Tulis dengan data. Terbit dengan tanggung jawab.',
'excerpt' => 'Dapatkan peninjauan editorial untuk struktur, argumentasi, dan etika akademik tanpa klaim peer-review
jurnal.',
'url' => '#',
'alt' => 'thumbnail',
],
[
'image' => 'assets/images/thumbnails/konsultasi.jpg',
'title' => 'Tulis dengan data. Terbit dengan tanggung jawab.',
'excerpt' => 'Dapatkan peninjauan editorial untuk struktur, argumentasi, dan etika akademik tanpa klaim peer-review
jurnal.',
'url' => '#',
'alt' => 'thumbnail',
],
],
'arrowIcon' => 'assets/images/icons/arrow.svg',
])

<section id="Featured" class="mt-2 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-none">
    <div class="w-full main-carousel">
        @foreach ($slides as $slide)
        <div
            class="featured-news-card relative flex h-[420px] w-full overflow-hidden sm:h-[360px] md:h-[480px] lg:h-[550px]">
            <img src="{{ asset($slide['image']) }}" class="absolute inset-0 object-cover w-full h-full"
                alt="{{ $slide['alt'] ?? 'thumbnail' }}" loading="lazy">

            <div class="absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] to-[rgba(0,0,0,0.9)]"></div>

            <div class="relative z-20 flex items-end w-full h-full pb-6 md:pb-10">
                <div class="px-4 sm:px-6 lg:px-8 mx-auto flex w-full max-w-[1130px] items-end justify-between">
                    <div class="flex max-w-[340px] flex-col gap-2 sm:max-w-[400px] sm:gap-[10px] md:max-w-[70%]">
                        <p class="text-xs text-white sm:text-sm">{{ $badgeText }}</p>

                        <a href="{{ $slide['url'] ?? '#' }}" class="two-lines text-lg font-bold leading-[26px] text-white transition-all duration-300 hover:underline
                                       sm:text-2xl sm:leading-[32px]
                                       md:text-3xl md:leading-[40px]
                                       lg:text-4xl lg:leading-[45px]">
                            {{ $slide['title'] }}
                        </a>

                        <p class="text-xs text-white sm:text-sm">{{ $slide['excerpt'] }}</p>
                    </div>

                    <div class="prevNextButtons mb-[60px] hidden shrink-0 items-center gap-4 md:flex">
                        <button type="button" aria-label="Previous"
                            class="button--previous flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="arrow">
                        </button>

                        <button type="button" aria-label="Next"
                            class="button--next flex h-[38px] w-[38px] shrink-0 rotate-180 items-center justify-center rounded-full ring-1 ring-white transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18]">
                            <img src="{{ asset($arrowIcon) }}" alt="arrow">
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
