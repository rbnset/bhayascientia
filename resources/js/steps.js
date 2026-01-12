export function initStepsSection() {
    const sections = document.querySelectorAll("[data-steps-section]");
    if (!sections.length) return;

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    // 1) Arm: baru menyiapkan state animasi saat mendekati viewport
    const armObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-armed");
                    armObserver.unobserve(entry.target);
                }
            }
        },
        {
            root: null,
            rootMargin: "120px 0px 120px 0px",
            threshold: 0,
        }
    );

    // 2) Play: animasi jalan saat section benar-benar mulai terlihat
    const playObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-inview");
                    playObserver.unobserve(entry.target);
                }
            }
        },
        {
            root: null,
            rootMargin: "0px 0px -10% 0px",
            threshold: 0.2,
        }
    );

    sections.forEach((sec) => {
        armObserver.observe(sec);
        playObserver.observe(sec);
    });
}
