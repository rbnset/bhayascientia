import "./bootstrap";

import { initNavbar } from "./navbar";
import { initVideoModal } from "./modal-video";

document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    initVideoModal();
});
