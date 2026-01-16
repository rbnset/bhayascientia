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
            // Mobile: 1 card + sedikit peek
            slidesPerView: 1.08,
            spaceBetween: 16,
            slidesPerGroup: 1,

            // Smooth scrolling
            speed: 600,
            grabCursor: true,
            watchOverflow: true,
            watchSlidesProgress: true,

            // Pagination
            pagination: {
                el: '.upToDateSwiper .swiper-pagination',
                clickable: true,
                dynamicBullets: true,
                dynamicMainBullets: 3,
            },

            // Navigation
            navigation: {
                nextEl: '.upToDateSwiper .swiper-button-next',
                prevEl: '.upToDateSwiper .swiper-button-prev',
            },

            // Breakpoints - ✅ EXACT slides tanpa peek di desktop
            breakpoints: {
                // Mobile landscape (>= 480px)
                480: {
                    slidesPerView: 1.5,
                    spaceBetween: 16
                },
                // Tablet (>= 640px)
                640: {
                    slidesPerView: 2,    // ✅ Exact 2 card
                    spaceBetween: 20
                },
                // Tablet landscape (>= 768px)
                768: {
                    slidesPerView: 3,    // ✅ Exact 3 card
                    spaceBetween: 20
                },
                // Desktop (>= 1024px)
                1024: {
                    slidesPerView: 3,    // ✅ Exact 3 card
                    spaceBetween: 24,
                    slidesPerGroup: 3
                },
                // Desktop large (>= 1280px)
                1280: {
                    slidesPerView: 4,    // ✅ Exact 4 card, NO PEEK!
                    spaceBetween: 24,
                    slidesPerGroup: 4
                },
            },

            // ✅ Prevent showing partial slides at the end
            watchSlidesProgress: true,

            // Accessibility
            a11y: {
                enabled: true,
                prevSlideMessage: 'Publikasi sebelumnya',
                nextSlideMessage: 'Publikasi berikutnya',
                paginationBulletMessage: 'Ke halaman {{index}}'
            },

            // Callbacks
            on: {
                init: function () {
                    console.log('✅ Publikasi swiper initialized successfully');
                    console.log(`Active breakpoint: ${this.currentBreakpoint}`);
                    console.log(`Slides per view: ${this.params.slidesPerView}`);
                },
                slideChange: function () {
                    console.log(`📄 Slide: ${this.activeIndex + 1}/${this.slides.length}`);
                }
            }
        });

        return swiper;
    } catch (error) {
        console.error('❌ Error initializing publikasi swiper:', error);
    }
}
