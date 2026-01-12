export function initComingSoon() {
    const section = document.querySelector("[data-coming-soon]");
    if (!section) return;

    const uid = section.getAttribute("data-coming-soon");
    const dataEl = section.querySelector(`[data-coming-soon-data="${uid}"]`);
    let payload = {};
    try { payload = JSON.parse(dataEl?.textContent || "{}"); } catch { }

    const wrap = section.querySelector("#roadmapCards");
    if (!wrap) return;

    const cards = [...wrap.querySelectorAll(".card")];
    if (!cards.length) return;

    const toast = section.querySelector("#toastComingSoon");
    let toastTimer = null;

    // Mode "fit" mulai lg (>=1024)
    const mqDesktop = window.matchMedia("(min-width: 1024px)");

    function showToast() {
        if (!toast) return;
        toast.classList.remove("hidden");
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.add("hidden"), 1800);
    }

    function showInfo(card, show) {
        const info = card.querySelector(".card-info");
        if (!info) return;
        info.classList.toggle("hidden", !show);
        info.classList.toggle("flex", show);
    }

    function resetInlineFlex() {
        cards.forEach((c) => (c.style.flex = ""));
    }

    function applyActive(index = 0) {
        if (!mqDesktop.matches) {
            // mobile/tablet: scroll allowed
            wrap.classList.add("overflow-x-auto");
            resetInlineFlex();

            // minimal 1 info tampil agar tidak kosong
            cards.forEach((c, i) => showInfo(c, i === index));
            return;
        }

        // desktop: no scroll + fit to container
        wrap.classList.remove("overflow-x-auto");

        const gap = 20; // gap-5 = 20px
        const n = cards.length;
        const W = wrap.clientWidth;
        const gaps = gap * (n - 1);

        const EXPANDED_RATIO = 0.42;
        const expanded = Math.max(280, Math.min(460, Math.floor(W * EXPANDED_RATIO)));
        const collapsed = Math.max(150, Math.floor((W - gaps - expanded) / (n - 1)));

        cards.forEach((card, i) => {
            const active = i === index;
            card.style.flex = `0 1 ${active ? expanded : collapsed}px`;
            showInfo(card, active);
        });
    }

    // init
    applyActive(0);

    // hover/focus interactions
    cards.forEach((card, idx) => {
        card.addEventListener("mouseenter", () => {
            if (mqDesktop.matches) applyActive(idx);
        });
        card.addEventListener("focusin", () => applyActive(idx));

        // click tombol "coming soon" -> toast demo
        const btn = card.querySelector("[data-coming]");
        if (btn) btn.addEventListener("click", showToast);
    });

    wrap.addEventListener("mouseleave", () => {
        if (mqDesktop.matches) applyActive(0);
    });

    // recalc (matchMedia change event) [web:810]
    if (mqDesktop.addEventListener) {
        mqDesktop.addEventListener("change", () => applyActive(0));
    } else {
        // fallback browser lama
        mqDesktop.addListener(() => applyActive(0));
    }

    window.addEventListener("resize", () => applyActive(0));
}
