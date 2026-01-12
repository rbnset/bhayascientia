export function initNavbar() {
    const btn = document.getElementById("hamburgerBtn");
    const panel = document.getElementById("mobileMenu");
    const overlay = document.getElementById("mobileOverlay");
    const iconBurger = document.getElementById("iconBurger");
    const iconClose = document.getElementById("iconClose");

    if (!btn || !panel || !overlay) return;

    const mobileItems = panel.querySelectorAll(".js-mobile-item");

    function playOpenAnimations() {
        overlay.classList.remove("nav-overlay-enter");
        panel.classList.remove("nav-menu-enter");

        // reflow hack supaya animasi bisa retrigger
        void overlay.offsetWidth;
        void panel.offsetWidth;

        overlay.classList.add("nav-overlay-enter");
        panel.classList.add("nav-menu-enter");

        mobileItems.forEach((el) => {
            el.classList.remove("nav-mobile-item-enter");
            void el.offsetWidth;
            el.classList.add("nav-mobile-item-enter");
        });
    }

    function setMobileOpen(open) {
        panel.classList.toggle("hidden", !open);
        overlay.classList.toggle("hidden", !open);

        btn.setAttribute("aria-expanded", String(open));
        iconBurger?.classList.toggle("hidden", open);
        iconClose?.classList.toggle("hidden", !open);

        document.documentElement.classList.toggle("overflow-hidden", open);
        document.body.classList.toggle("overflow-hidden", open);

        if (open) playOpenAnimations();
    }

    btn.addEventListener("click", () => {
        const open = panel.classList.contains("hidden");
        setMobileOpen(open);
    });

    overlay.addEventListener("click", () => setMobileOpen(false));

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") setMobileOpen(false);
    });
}
