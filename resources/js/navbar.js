/**
 * BHAYASCIENTIA - Ultimate Smart Navbar Module
 * @version 3.1.1 - Fixed active state rendering
 */

export function initNavbar() {
    // Guard: Cegah double initialization
    if (window.__navbarInitialized) {
        console.warn('⚠️ Navbar already initialized');
        return window.__navbarInstance;
    }

    // ========================================
    // DOM ELEMENTS
    // ========================================
    const elements = {
        btn: document.getElementById("hamburgerBtn"),
        panel: document.getElementById("mobileMenu"),
        overlay: document.getElementById("mobileOverlay"),
        iconBurger: document.getElementById("iconBurger"),
        iconClose: document.getElementById("iconClose"),
        header: document.getElementById("mainHeader"),
        stickyBg: document.getElementById("stickyBg"),
        mobileItems: null,
        desktopMenuItems: null
    };

    // Validation
    if (!elements.btn || !elements.panel || !elements.overlay || !elements.header) {
        console.warn('❌ Navbar: Required elements not found');
        return;
    }

    elements.mobileItems = elements.panel.querySelectorAll(".js-mobile-item");
    elements.desktopMenuItems = document.querySelectorAll('nav[aria-label="Menu utama"] a');

    // ========================================
    // STATE MANAGEMENT
    // ========================================
    const state = {
        lastScrollY: window.scrollY,
        ticking: false,
        isMenuOpen: false,
        isHeaderVisible: true,
        scrollDirection: 'up'
    };

    // Configuration
    const config = {
        scrollThreshold: 100,
        hideThreshold: 150,
        backdropBlurAt: 50,
        scrollHysteresis: 10,
        transitionDuration: 300,
        mobileBreakpoint: 1280,
        staggerDelay: 60
    };

    // ========================================
    // INTELLIGENT SCROLL HANDLER
    // ========================================

    function handleScroll() {
        const currentScrollY = window.scrollY;
        const scrollDelta = currentScrollY - state.lastScrollY;

        if (Math.abs(scrollDelta) < config.scrollHysteresis) {
            return;
        }

        state.scrollDirection = scrollDelta > 0 ? 'down' : 'up';
        updateBackdrop(currentScrollY);
        updateNavbarVisibility(currentScrollY, scrollDelta);
        state.lastScrollY = currentScrollY;
    }

    function updateBackdrop(scrollY) {
        if (!elements.stickyBg) return;

        if (scrollY > config.backdropBlurAt) {
            elements.stickyBg.style.opacity = '1';
        } else {
            elements.stickyBg.style.opacity = '0';
        }
    }

    function updateNavbarVisibility(scrollY, scrollDelta) {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const isNearTop = scrollY < config.scrollThreshold;
        const isNearBottom = scrollY + windowHeight >= documentHeight - 100;

        if (isNearTop) {
            showNavbar();
            return;
        }

        if (isNearBottom) {
            showNavbar();
            return;
        }

        if (state.scrollDirection === 'down' && scrollY > config.hideThreshold) {
            hideNavbar();
            return;
        }

        if (state.scrollDirection === 'up') {
            showNavbar();
            return;
        }
    }

    function showNavbar() {
        if (state.isHeaderVisible) return;

        elements.header.style.transform = 'translateY(0)';
        elements.header.style.opacity = '1';
        elements.header.setAttribute('aria-hidden', 'false');
        state.isHeaderVisible = true;
    }

    function hideNavbar() {
        if (!state.isHeaderVisible) return;
        if (state.isMenuOpen) return;

        elements.header.style.transform = 'translateY(-100%)';
        elements.header.style.opacity = '0';
        elements.header.setAttribute('aria-hidden', 'true');
        state.isHeaderVisible = false;
    }

    function onScroll() {
        if (!state.ticking) {
            window.requestAnimationFrame(() => {
                handleScroll();
                state.ticking = false;
            });
            state.ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    // ========================================
    // MOBILE MENU ANIMATIONS
    // ========================================

    function playOpenAnimations() {
        elements.overlay.classList.remove("nav-overlay-enter");
        void elements.overlay.offsetWidth;
        elements.overlay.classList.add("nav-overlay-enter");

        elements.panel.classList.remove("nav-menu-enter");
        void elements.panel.offsetWidth;
        elements.panel.classList.add("nav-menu-enter");

        elements.mobileItems.forEach((el, index) => {
            el.classList.remove("nav-mobile-item-enter");
            void el.offsetWidth;
            el.style.animationDelay = `${index * config.staggerDelay}ms`;
            el.classList.add("nav-mobile-item-enter");
        });
    }

    function setMobileOpen(open) {
        state.isMenuOpen = open;

        elements.panel.classList.toggle("hidden", !open);
        elements.overlay.classList.toggle("hidden", !open);

        elements.btn.setAttribute("aria-expanded", String(open));
        elements.btn.setAttribute("aria-label", open ? "Tutup menu navigasi" : "Buka menu navigasi");

        if (elements.iconBurger && elements.iconClose) {
            elements.iconBurger.classList.toggle("hidden", open);
            elements.iconClose.classList.toggle("hidden", !open);
        }

        if (open) {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        } else {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }

        if (open) {
            showNavbar();
            playOpenAnimations();

            setTimeout(() => {
                const firstItem = elements.panel.querySelector('a');
                firstItem?.focus();
            }, 100);

            trapFocus(elements.panel);
        } else {
            elements.btn.focus();
            releaseFocus();
        }
    }

    // ========================================
    // FOCUS TRAP (Accessibility)
    // ========================================

    let focusableElements = [];
    let firstFocusable = null;
    let lastFocusable = null;

    function trapFocus(container) {
        focusableElements = Array.from(
            container.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )
        );

        firstFocusable = focusableElements[0];
        lastFocusable = focusableElements[focusableElements.length - 1];

        container.addEventListener('keydown', handleFocusTrap);
    }

    function handleFocusTrap(e) {
        if (e.key !== 'Tab') return;

        if (e.shiftKey) {
            if (document.activeElement === firstFocusable) {
                e.preventDefault();
                lastFocusable?.focus();
            }
        } else {
            if (document.activeElement === lastFocusable) {
                e.preventDefault();
                firstFocusable?.focus();
            }
        }
    }

    function releaseFocus() {
        elements.panel.removeEventListener('keydown', handleFocusTrap);
        focusableElements = [];
        firstFocusable = null;
        lastFocusable = null;
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================

    elements.btn.addEventListener("click", (e) => {
        e.stopPropagation();
        const isOpen = elements.panel.classList.contains("hidden");
        setMobileOpen(isOpen);
    });

    elements.overlay.addEventListener("click", () => {
        setMobileOpen(false);
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && state.isMenuOpen) {
            e.preventDefault();
            setMobileOpen(false);
        }
    });

    elements.mobileItems.forEach(item => {
        item.addEventListener("click", (e) => {
            const href = item.getAttribute('href');

            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    setMobileOpen(false);
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, config.transitionDuration);
                }
            } else {
                setTimeout(() => {
                    setMobileOpen(false);
                }, 150);
            }
        });
    });

    // ========================================
    // KEYBOARD NAVIGATION (Desktop)
    // ========================================

    function initKeyboardNavigation() {
        if (!elements.desktopMenuItems.length) return;

        elements.desktopMenuItems.forEach((item, index) => {
            item.addEventListener('keydown', (e) => {
                let targetIndex = -1;

                switch (e.key) {
                    case 'ArrowRight':
                    case 'ArrowDown':
                        e.preventDefault();
                        targetIndex = (index + 1) % elements.desktopMenuItems.length;
                        break;

                    case 'ArrowLeft':
                    case 'ArrowUp':
                        e.preventDefault();
                        targetIndex = (index - 1 + elements.desktopMenuItems.length) % elements.desktopMenuItems.length;
                        break;

                    case 'Home':
                        e.preventDefault();
                        targetIndex = 0;
                        break;

                    case 'End':
                        e.preventDefault();
                        targetIndex = elements.desktopMenuItems.length - 1;
                        break;
                }

                if (targetIndex >= 0) {
                    elements.desktopMenuItems[targetIndex].focus();
                }
            });
        });
    }

    initKeyboardNavigation();

    // ========================================
    // WINDOW RESIZE HANDLER
    // ========================================

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth >= config.mobileBreakpoint && state.isMenuOpen) {
                setMobileOpen(false);
            }

            handleScroll();
        }, 250);
    }, { passive: true });

    // ========================================
    // PAGE VISIBILITY CHANGE
    // ========================================

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            state.lastScrollY = window.scrollY;
            handleScroll();
        }
    });

    // ========================================
    // INITIALIZE
    // ========================================

    handleScroll();

    window.__navbarInitialized = true;

    const publicAPI = {
        open: () => setMobileOpen(true),
        close: () => setMobileOpen(false),
        toggle: () => setMobileOpen(!state.isMenuOpen),
        show: showNavbar,
        hide: hideNavbar,
        isOpen: () => state.isMenuOpen,
        isVisible: () => state.isHeaderVisible,
        destroy: () => {
            window.removeEventListener('scroll', onScroll);
            releaseFocus();
            window.__navbarInitialized = false;
            window.__navbarInstance = null;
            console.log('🗑️ Navbar destroyed');
        }
    };

    window.__navbarInstance = publicAPI;

    console.log('✅ Navbar initialized (v3.1.1)');

    return publicAPI;
}
