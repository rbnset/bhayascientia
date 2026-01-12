export function initFaqAccordion() {
    const sections = document.querySelectorAll("[data-faq]");
    if (!sections.length) return;

    sections.forEach((section) => {
        const uid = section.getAttribute("data-faq");
        const dataEl = section.querySelector(`[data-faq-data="${uid}"]`);

        let payload = {};
        try { payload = JSON.parse(dataEl?.textContent || "{}"); } catch { }

        const singleOpen = payload.singleOpen !== false;

        const buttons = [...section.querySelectorAll(".accordion-button")];
        if (!buttons.length) return;

        function closeItem(btn) {
            const id = btn.getAttribute("data-accordion");
            const panel = section.querySelector(`#${CSS.escape(id)}`);
            const arrow = btn.querySelector(".arrow img");

            btn.setAttribute("aria-expanded", "false");
            if (panel) panel.classList.add("hidden");
            if (arrow) arrow.classList.remove("rotate-180");
        }

        function openItem(btn) {
            const id = btn.getAttribute("data-accordion");
            const panel = section.querySelector(`#${CSS.escape(id)}`);
            const arrow = btn.querySelector(".arrow img");

            btn.setAttribute("aria-expanded", "true");
            if (panel) panel.classList.remove("hidden");
            if (arrow) arrow.classList.add("rotate-180");
        }

        buttons.forEach((btn) => {
            btn.addEventListener("click", () => {
                const expanded = btn.getAttribute("aria-expanded") === "true";

                if (singleOpen) buttons.forEach(closeItem);

                if (!expanded) openItem(btn);
                else closeItem(btn);
            });
        });
    });
}
