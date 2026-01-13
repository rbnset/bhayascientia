// resources/js/featured-carousel.js
import Flickity from "flickity";

export function initFeaturedCarousel() {
    const carouselEl = document.querySelector(".main-carousel");
    if (!carouselEl) return;

    // Hindari init dobel (mis. kalau layout re-render)
    if (carouselEl.__flkty) return;

    const flkty = new Flickity(carouselEl, {
        cellAlign: "left",
        contain: true,
        prevNextButtons: false,
        pageDots: false,
        wrapAround: true,
    });

    carouselEl.__flkty = flkty;

    const prevBtn = document.querySelector(".button--previous");
    const nextBtn = document.querySelector(".button--next");

    if (prevBtn) {
        prevBtn.addEventListener("click", (e) => {
            e.preventDefault();
            flkty.previous(true);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", (e) => {
            e.preventDefault();
            flkty.next(true);
        });
    }

    // Stabilkan layout setelah semua aset siap
    window.addEventListener("load", () => {
        flkty.resize();
        flkty.reposition();
    });
}
