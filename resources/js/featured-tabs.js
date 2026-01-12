export function initFeaturedTabs() {
    const sections = document.querySelectorAll("[data-featured-tabs]");
    if (!sections.length) return;

    const prefersReducedMotion =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    sections.forEach((section) => {
        const uid = section.getAttribute("data-featured-tabs");
        const dataEl = section.querySelector(`[data-featured-tabs-data="${uid}"]`);
        if (!dataEl) return;

        let payload;
        try { payload = JSON.parse(dataEl.textContent || "{}"); } catch { payload = {}; }

        const tabsData = payload.tabs || [];
        const checkIcon = payload.checkIcon || "";

        const tabs = [...section.querySelectorAll(".tab-menu")];
        const panel = section.querySelector(`#${uid}_panel`);
        if (!panel || !tabs.length || !tabsData.length) return;

        const img = panel.querySelector(".tab-img img");
        const title = panel.querySelector(".tab-title");
        const desc = panel.querySelector(".tab-description");
        const features = panel.querySelector(".tab-features");
        const cta = panel.querySelector(".tab-cta");

        let switchingTimer = null;
        let pressingTimer = null;

        function getActiveIndex() {
            const idx = tabs.findIndex((t) => t.getAttribute("aria-selected") === "true");
            return idx >= 0 ? idx : 0;
        }

        function startSwitchAnimation() {
            if (prefersReducedMotion) return;

            section.classList.add("is-switching");
            window.clearTimeout(switchingTimer);
            switchingTimer = window.setTimeout(() => {
                section.classList.remove("is-switching");
            }, 180); // samakan dengan durasi transition CSS
        }

        function pressFeedback(tabEl) {
            if (prefersReducedMotion) return;

            tabEl.classList.add("is-pressing");
            window.clearTimeout(pressingTimer);
            pressingTimer = window.setTimeout(() => {
                tabEl.classList.remove("is-pressing");
            }, 120);
        }

        function render(index) {
            const data = tabsData[index];
            if (!data) return;

            panel.setAttribute("aria-labelledby", `${uid}_tab_${index}`);

            if (img) img.src = data.image;
            if (title) title.textContent = data.title || "";
            if (desc) desc.textContent = data.description || "";

            if (features) {
                features.innerHTML = "";
                (data.features || []).forEach((text) => {
                    const row = document.createElement("div");
                    row.className = "flex items-start gap-3";
                    row.innerHTML = `
            <div class="flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-full bg-[#FF6B18]">
              <img src="${checkIcon}" alt="" class="h-4 w-4" aria-hidden="true">
            </div>
            <p class="text-sm sm:text-base leading-6 font-semibold text-[#111827]"></p>
          `;
                    row.querySelector("p").textContent = text;
                    features.appendChild(row);
                });
            }

            if (cta) {
                cta.textContent = data.ctaText || "Pelajari";
                cta.setAttribute("href", data.ctaHref || "#");
            }
        }

        function setActive(index, { focus = false, sourceTab = null } = {}) {
            // 1) microanimation state (fade panel image / content)
            startSwitchAnimation();

            // 2) microinteraction pressed (kalau trigger dari click)
            if (sourceTab) pressFeedback(sourceTab);

            tabs.forEach((t, i) => {
                const isActive = i === index;

                t.setAttribute("aria-selected", isActive ? "true" : "false");
                t.tabIndex = isActive ? 0 : -1;

                const bar = t.querySelector(".tab-indicator > div");
                if (bar) {
                    bar.classList.toggle("bg-[#111827]", isActive);
                    bar.classList.toggle("bg-transparent", !isActive);
                }

                const iconWrap = t.querySelector(".tab-icon-container");
                if (iconWrap) {
                    iconWrap.classList.toggle("bg-[#FF6B18]", isActive);
                    iconWrap.classList.toggle("bg-[#EEF0F7]", !isActive);
                }

                const h3 = t.querySelector("h3");
                if (h3) {
                    h3.classList.toggle("font-semibold", isActive);
                    h3.classList.toggle("font-medium", !isActive);
                }
            });

            render(index);

            if (focus) tabs[index]?.focus();
        }

        tabs.forEach((tab, i) => {
            tab.addEventListener("click", () => setActive(i, { sourceTab: tab }));

            tab.addEventListener("keydown", (e) => {
                const cur = getActiveIndex();

                // mengikuti pola keyboard tabs (Arrow/Home/End) [web:702]
                if (e.key === "ArrowRight") { e.preventDefault(); setActive((cur + 1) % tabs.length, { focus: true }); }
                if (e.key === "ArrowLeft") { e.preventDefault(); setActive((cur - 1 + tabs.length) % tabs.length, { focus: true }); }
                if (e.key === "Home") { e.preventDefault(); setActive(0, { focus: true }); }
                if (e.key === "End") { e.preventDefault(); setActive(tabs.length - 1, { focus: true }); }
                if (e.key === "Enter" || e.key === " ") { e.preventDefault(); setActive(cur); }
            });
        });

        setActive(0);
    });
}
