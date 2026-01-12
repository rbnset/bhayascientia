export function initVideoModal() {
    const modal = document.getElementById("video-modal");
    const frame = document.getElementById("videoFrame");
    const openers = document.querySelectorAll("[data-video-open]");
    const closers = modal?.querySelectorAll("[data-video-close]");

    if (!modal || !frame || openers.length === 0) return;

    let lastActiveEl = null;

    function openModal(youtubeId) {
        lastActiveEl = document.activeElement;

        // autoplay optional: set autoplay=1
        frame.src = `https://www.youtube.com/embed/${youtubeId}?autoplay=1`;

        modal.classList.remove("hidden");
        modal.classList.add("flex");

        document.documentElement.classList.add("overflow-hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");

        frame.removeAttribute("src"); // stop video

        document.documentElement.classList.remove("overflow-hidden");
        document.body.classList.remove("overflow-hidden");

        if (lastActiveEl && typeof lastActiveEl.focus === "function") {
            lastActiveEl.focus();
        }
    }

    openers.forEach((btn) => {
        btn.addEventListener("click", () => {
            const youtubeId = btn.getAttribute("data-youtube-id");
            if (youtubeId) openModal(youtubeId);
        });
    });

    closers?.forEach((btn) => btn.addEventListener("click", closeModal));

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.classList.contains("hidden")) {
            closeModal();
        }
    });
}
