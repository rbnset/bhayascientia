import Flickity from "flickity";

export function initFeaturedCarousel() {
    const section = document.querySelector("[data-featured-carousel]");
    if (!section) return;

    const carouselEl = section.querySelector(".main-carousel");
    if (!carouselEl) return;

    if (Flickity.data(carouselEl)) return;

    const flkty = new Flickity(carouselEl, {
        cellAlign: "left",
        contain: true,
        prevNextButtons: false,
        pageDots: false,
        wrapAround: true,
    });

    const prevBtn = section.querySelector("[data-carousel-prev]");
    const nextBtn = section.querySelector("[data-carousel-next]");

    prevBtn?.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        flkty.previous(true);
    });

    nextBtn?.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        flkty.next(true);
    });
}
