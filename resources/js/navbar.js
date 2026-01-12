export function initNavbar() {
    const btn = document.getElementById("hamburgerBtn");
    const panel = document.getElementById("mobileMenu");
    const overlay = document.getElementById("mobileOverlay");
    const iconBurger = document.getElementById("iconBurger");
    const iconClose = document.getElementById("iconClose");

    if (!btn || !panel || !overlay) return;

    function setMobileOpen(open) {
        panel.classList.toggle("hidden", !open);
        overlay.classList.toggle("hidden", !open);

        btn.setAttribute("aria-expanded", String(open));
        iconBurger?.classList.toggle("hidden", open);
        iconClose?.classList.toggle("hidden", !open);

        document.documentElement.classList.toggle("overflow-hidden", open);
        document.body.classList.toggle("overflow-hidden", open);
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
