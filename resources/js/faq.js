export function initFaqAccordion() {
    const sections = document.querySelectorAll("[data-faq]");
    if (!sections.length) return;

    const prefersReducedMotion =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    sections.forEach((section) => {
        const uid = section.getAttribute("data-faq");
        const dataEl = section.querySelector(`[data-faq-data="${uid}"]`);

        let payload = {};
        try { payload = JSON.parse(dataEl?.textContent || "{}"); } catch { }

        const singleOpen = payload.singleOpen !== false;

        const buttons = [...section.querySelectorAll(".accordion-button")];
        if (!buttons.length) return;

        function getItem(btn) {
            return btn.closest(".accordion-item");
        }

        function closeItem(btn) {
            const id = btn.getAttribute("data-accordion");
            const panel = section.querySelector(`#${CSS.escape(id)}`);
            const arrow = btn.querySelector(".accordion-arrow img");
            const item = getItem(btn);

            btn.setAttribute("aria-expanded", "false");

            // Untuk animasi grid, panel jangan langsung "display:none".
            // Tapi biar sesuai markup kamu yang pakai hidden, kita lakukan transisi dulu.
            if (!prefersReducedMotion && panel && !panel.classList.contains("hidden")) {
                // tutup dengan state, lalu setelah animasi baru hidden
                item?.classList.remove("is-open");
                panel.style.display = "grid";
                panel.classList.remove("hidden");
                panel.offsetHeight; // force reflow
                panel.classList.add("is-closing");

                window.setTimeout(() => {
                    panel.classList.add("hidden");
                    panel.classList.remove("is-closing");
                }, 260);
            } else {
                panel?.classList.add("hidden");
                item?.classList.remove("is-open");
            }

            if (arrow) arrow.classList.remove("rotate-180");
        }

        function openItem(btn) {
            const id = btn.getAttribute("data-accordion");
            const panel = section.querySelector(`#${CSS.escape(id)}`);
            const arrow = btn.querySelector(".accordion-arrow img");
            const item = getItem(btn);

            btn.setAttribute("aria-expanded", "true");

            if (panel) {
                panel.classList.remove("hidden");
                panel.style.display = "grid";
            }
            item?.classList.add("is-open");
            if (arrow) arrow.classList.add("rotate-180");
        }

        function pressFeedback(btn) {
            if (prefersReducedMotion) return;
            btn.classList.add("is-pressing");
            window.setTimeout(() => btn.classList.remove("is-pressing"), 120);
        }

        buttons.forEach((btn) => {
            // click
            btn.addEventListener("click", () => {
                pressFeedback(btn);

                const expanded = btn.getAttribute("aria-expanded") === "true";

                if (singleOpen) buttons.forEach((b) => (b !== btn ? closeItem(b) : null));

                if (!expanded) openItem(btn);
                else closeItem(btn);
            });

            // keyboard: Enter/Space toggle (umum untuk accordion) [web:827]
            btn.addEventListener("keydown", (e) => {
                if (e.key !== "Enter" && e.key !== " ") return;
                e.preventDefault();
                btn.click();
            });
        });

        // init: sync class state sesuai aria-expanded
        buttons.forEach((btn) => {
            const expanded = btn.getAttribute("aria-expanded") === "true";
            const item = getItem(btn);
            if (expanded) item?.classList.add("is-open");
            else item?.classList.remove("is-open");
        });
    });
}
