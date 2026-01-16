// resources/js/swiper-publikasi.js

export function initPublikasiSwiper() {
    const swiperEl = document.querySelector('.upToDateSwiper');

    if (!swiperEl) {
        console.log('ℹ️ Swiper element .upToDateSwiper not found');
        return;
    }

    const slideCount = swiperEl.querySelectorAll('.swiper-slide').length;

    if (slideCount === 0) {
        console.warn('⚠️ No slides found in .upToDateSwiper');
        return;
    }

    console.log(`🎨 Initializing publikasi swiper with ${slideCount} slides`);

    try {
        const swiper = new Swiper('.upToDateSwiper', {
            slidesPerView: 1.12,
            spaceBetween: 16,
            slidesPerGroup: 1,
            centeredSlides: false,

            watchSlidesProgress: true,
            watchOverflow: true,

            speed: 400,
            grabCursor: true,

            touchRatio: 1,
            touchAngle: 45,
            threshold: 5,
            resistanceRatio: 0.85,

            lazy: false,
            preloadImages: true,

            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },

            mousewheel: {
                forceToAxis: true,
                sensitivity: 0.5,
            },

            pagination: {
                el: '.upToDateSwiper .swiper-pagination',
                clickable: true,
                dynamicBullets: true,
                dynamicMainBullets: 3,
                renderBullet: function (index, className) {
                    return `<button class="${className}" aria-label="Pergi ke slide ${index + 1} dari ${slideCount}" type="button"></button>`;
                },
            },

            navigation: {
                nextEl: '.upToDateSwiper .swiper-button-next',
                prevEl: '.upToDateSwiper .swiper-button-prev',
            },

            breakpoints: {
                480: {
                    slidesPerView: 1.3,
                    spaceBetween: 16,
                    slidesPerGroup: 1 // ✅ Geser 1 card
                },
                640: {
                    slidesPerView: 2.15,
                    spaceBetween: 20,
                    slidesPerGroup: 1 // ✅ Geser 1 card
                },
                768: {
                    slidesPerView: 2.5,
                    spaceBetween: 20,
                    slidesPerGroup: 1 // ✅ Geser 1 card
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                    slidesPerGroup: 1 // ✅ PERBAIKAN: Geser 1 card, bukan 3
                },
                1280: {
                    slidesPerView: 4,
                    spaceBetween: 24,
                    slidesPerGroup: 1, // ✅ PERBAIKAN: Geser per 1 card
                    slidesOffsetBefore: 0,
                    slidesOffsetAfter: 0,
                    centeredSlides: false,
                    watchOverflow: true,
                    loop: false,
                },
            },



            a11y: {
                enabled: true,
                prevSlideMessage: 'Geser ke publikasi sebelumnya',
                nextSlideMessage: 'Geser ke publikasi berikutnya',
                firstSlideMessage: 'Ini adalah publikasi pertama',
                lastSlideMessage: 'Ini adalah publikasi terakhir',
                paginationBulletMessage: 'Pergi ke halaman {{index}}',
            },

            on: {
                init: function () {
                    console.log('✅ Publikasi swiper initialized');

                    this.el.setAttribute('role', 'region');
                    this.el.setAttribute('aria-label', 'Carousel publikasi terbaru');

                    const liveRegion = document.createElement('div');
                    liveRegion.className = 'sr-only';
                    liveRegion.setAttribute('aria-live', 'polite');
                    liveRegion.setAttribute('aria-atomic', 'true');
                    this.el.appendChild(liveRegion);

                    console.log(`✅ Slides per view: ${this.params.slidesPerView}`);
                    console.log(`✅ Total slides: ${this.slides.length}`);
                },

                slideChange: function () {
                    const current = this.activeIndex + 1;
                    const total = this.slides.length;
                    console.log(`📄 Slide: ${current}/${total}`);

                    const liveRegion = this.el.querySelector('.sr-only');
                    if (liveRegion) {
                        liveRegion.textContent = `Menampilkan publikasi ${current} dari ${total}`;
                    }
                },

                resize: function () {
                    console.log(`🔄 Swiper resized: ${window.innerWidth}px`);
                    this.update(); // ✅ Recalculate pada resize
                },
            }
        });

        return swiper;
    } catch (error) {
        console.error('❌ Error initializing publikasi swiper:', error);
    }
}
