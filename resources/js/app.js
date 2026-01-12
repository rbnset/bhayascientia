import "./bootstrap";

import { initNavbar } from "./navbar";
import { initVideoModal } from "./modal-video";
import { initStepsSection } from "./steps";

document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    initVideoModal();
    initStepsSection();
});
