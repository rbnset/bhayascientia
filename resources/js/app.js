import "./bootstrap";

import { initNavbar } from "./navbar";
import { initVideoModal } from "./modal-video";
import { initStepsSection } from "./steps";
import { initFeaturedTabs } from "./featured-tabs";

document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    initVideoModal();
    initStepsSection();
    initFeaturedTabs();
});
