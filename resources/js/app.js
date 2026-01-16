// resources/js/app.js

import "./bootstrap";
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

// Make Swiper available globally for inline scripts
window.Swiper = Swiper;

import { initNavbar } from "./navbar";
import { initVideoModal } from "./modal-video";
import { initStepsSection } from "./steps";
import { initFeaturedTabs } from "./featured-tabs";
import { initComingSoon } from "./coming-soon";
import { initFaqAccordion } from "./faq";
import { initFeaturedCarousel } from "./featured-carousel";
import { initPublikasiSwiper } from "./swiper-publikasi";

// Navigation, Pagination, A11y sudah included di 'swiper/bundle'
// Tidak perlu import lagi jika pakai bundle

document.addEventListener("DOMContentLoaded", () => {
    console.log('🚀 Initializing BHAYASCIENTIA app...');

    initNavbar();
    initVideoModal();
    initStepsSection();
    initFeaturedTabs();
    initComingSoon();
    initFaqAccordion();
    initFeaturedCarousel();
    initPublikasiSwiper();

    console.log('✅ All modules initialized');
});
