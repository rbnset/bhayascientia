import "./bootstrap";

import { initNavbar } from "./navbar";
import { initVideoModal } from "./modal-video";
import { initStepsSection } from "./steps";
import { initFeaturedTabs } from "./featured-tabs";
import { initComingSoon } from "./coming-soon";
import { initFaqAccordion } from "./faq";
import { initFeaturedCarousel } from "./featured-carousel";

document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    initVideoModal();
    initStepsSection();
    initFeaturedTabs();
    initComingSoon();
    initFaqAccordion();
    initFeaturedCarousel();
});
