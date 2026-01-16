export function initPublikasiSwiper() {
    const swiperEl = document.querySelector('.upToDateSwiper');

    if (!swiperEl) return;

    const slideCount = swiperEl.querySelectorAll('.swiper-slide').length;

    if (slideCount === 0) return;

    new Swiper('.upToDateSwiper', {
        slidesPerView: 1.2,
        spaceBetween: 16,
        slidesPerGroup: 1,

        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true,
            dynamicMainBullets: 3,
        },

        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 16
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 16
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 16,
                slidesPerGroup: 4  // Geser 4 card sekaligus di desktop
            },
        },

        // Smooth transition
        speed: 600,
        watchOverflow: true,

        // Accessibility
        a11y: {
            prevSlideMessage: 'Publikasi sebelumnya',
            nextSlideMessage: 'Publikasi berikutnya',
            paginationBulletMessage: 'Ke halaman {{index}}'
        }
    });
}
