/**
 * BHAYASCIENTIA - Ultimate Smart Navbar Module
 *
 * Features:
 * - Intelligent scroll detection with hysteresis
 * - Smooth hide/show with easing
 * - Context-aware behavior (top vs middle vs bottom)
 * - Mobile menu with stagger animations
 * - Keyboard navigation (Arrow keys, Tab, Escape)
 * - Focus trap for accessibility
 * - Performance optimized with RAF throttling
 * - Adaptive sticky backdrop
 *
 * Behavior:
 * - Top of page (0-100px): Always visible
 * - Scroll down: Hide after 150px
 * - Scroll up: Show immediately
 * - Bottom of page: Show for footer navigation
 *
 * @version 3.0.0
 * @author BHAYASCIENTIA Team
 * @date 2026-01-14
 */

export function initNavbar() {
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
        mobileItems: null
    };

    // Validation
    if (!elements.btn || !elements.panel || !elements.overlay || !elements.header) {
        console.warn('❌ Navbar: Required elements not found');
        return;
    }

    elements.mobileItems = elements.panel.querySelectorAll(".js-mobile-item");

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
        scrollThreshold: 100,      // Minimum scroll before hiding
        hideThreshold: 150,        // Start hiding after this point
        backdropBlurAt: 50,        // Add backdrop blur after this scroll
        scrollHysteresis: 10,      // Prevent jitter (pixels)
        transitionDuration: 300,   // CSS transition duration (ms)
        mobileBreakpoint: 1280     // Desktop/mobile breakpoint
    };

    // ========================================
    // INTELLIGENT SCROLL HANDLER
    // ========================================

    /**
     * Main scroll handler with smart logic
     */
    function handleScroll() {
        const currentScrollY = window.scrollY;
        const scrollDelta = currentScrollY - state.lastScrollY;

        // Ignore small scroll movements (hysteresis)
        if (Math.abs(scrollDelta) < config.scrollHysteresis) {
            return;
        }

        // Determine scroll direction
        state.scrollDirection = scrollDelta > 0 ? 'down' : 'up';

        // Update backdrop blur
        updateBackdrop(currentScrollY);

        // Update navbar visibility
        updateNavbarVisibility(currentScrollY, scrollDelta);

        state.lastScrollY = currentScrollY;
    }

    /**
     * Update backdrop blur based on scroll position
     */
    function updateBackdrop(scrollY) {
        if (!elements.stickyBg) return;

        if (scrollY > config.backdropBlurAt) {
            elements.stickyBg.classList.remove('opacity-0');
            elements.stickyBg.classList.add('opacity-100');
        } else {
            elements.stickyBg.classList.remove('opacity-100');
            elements.stickyBg.classList.add('opacity-0');
        }
    }

    /**
     * Smart navbar visibility logic
     */
    function updateNavbarVisibility(scrollY, scrollDelta) {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const isNearTop = scrollY < config.scrollThreshold;
        const isNearBottom = scrollY + windowHeight >= documentHeight - 100;

        // Case 1: Near top - Always show
        if (isNearTop) {
            showNavbar();
            return;
        }

        // Case 2: Near bottom - Always show (for footer navigation)
        if (isNearBottom) {
            showNavbar();
            return;
        }

        // Case 3: Scrolling down - Hide
        if (state.scrollDirection === 'down' && scrollY > config.hideThreshold) {
            hideNavbar();
            return;
        }

        // Case 4: Scrolling up - Show
        if (state.scrollDirection === 'up') {
            showNavbar();
            return;
        }
    }

    /**
     * Show navbar with smooth transition
     */
    function showNavbar() {
        if (state.isHeaderVisible) return; // Already visible

        elements.header.style.transform = 'translateY(0)';
        elements.header.style.opacity = '1';
        elements.header.setAttribute('aria-hidden', 'false');
        state.isHeaderVisible = true;
    }

    /**
     * Hide navbar with smooth transition
     */
    function hideNavbar() {
        if (!state.isHeaderVisible) return; // Already hidden
        if (state.isMenuOpen) return; // Don't hide if mobile menu is open

        elements.header.style.transform = 'translateY(-100%)';
        elements.header.style.opacity = '0';
        elements.header.setAttribute('aria-hidden', 'true');
        state.isHeaderVisible = false;
    }

    /**
     * Throttled scroll event with requestAnimationFrame
     */
    function onScroll() {
        if (!state.ticking) {
            window.requestAnimationFrame(() => {
                handleScroll();
                state.ticking = false;
            });
            state.ticking = true;
        }
    }

    // Attach scroll listener
    window.addEventListener('scroll', onScroll, { passive: true });

    // ========================================
    // MOBILE MENU ANIMATIONS
    // ========================================

    /**
     * Play stagger animations for mobile menu
     */
    function playOpenAnimations() {
        // Reset and retrigger overlay animation
        elements.overlay.classList.remove("nav-overlay-enter");
        void elements.overlay.offsetWidth; // Force reflow
        elements.overlay.classList.add("nav-overlay-enter");

        // Reset and retrigger panel animation
        elements.panel.classList.remove("nav-menu-enter");
        void elements.panel.offsetWidth;
        elements.panel.classList.add("nav-menu-enter");

        // Stagger animation for menu items
        elements.mobileItems.forEach((el, index) => {
            el.classList.remove("nav-mobile-item-enter");
            void el.offsetWidth;
            el.style.animationDelay = `${index * 60}ms`;
            el.classList.add("nav-mobile-item-enter");
        });
    }

    /**
     * Set mobile menu open/closed state
     */
    function setMobileOpen(open) {
        state.isMenuOpen = open;

        // Toggle visibility
        elements.panel.classList.toggle("hidden", !open);
        elements.overlay.classList.toggle("hidden", !open);

        // Update ARIA attributes
        elements.btn.setAttribute("aria-expanded", String(open));
        elements.btn.setAttribute("aria-label", open ? "Tutup menu navigasi" : "Buka menu navigasi");

        // Toggle icons
        elements.iconBurger?.classList.toggle("hidden", open);
        elements.iconClose?.classList.toggle("hidden", !open);

        // Prevent body scroll when menu is open
        document.documentElement.classList.toggle("overflow-hidden", open);
        document.body.classList.toggle("overflow-hidden", open);

        if (open) {
            // Force show navbar when menu opens
            showNavbar();

            // Play animations
            playOpenAnimations();

            // Set focus to first menu item
            setTimeout(() => {
                const firstItem = elements.panel.querySelector('a');
                firstItem?.focus();
            }, 100);

            // Enable focus trap
            trapFocus(elements.panel);
        } else {
            // Return focus to hamburger button
            elements.btn.focus();

            // Disable focus trap
            releaseFocus();
        }
    }

    // ========================================
    // FOCUS TRAP (Accessibility)
    // ========================================

    let focusableElements = [];
    let firstFocusable = null;
    let lastFocusable = null;

    /**
     * Trap focus within container for accessibility
     */
    function trapFocus(container) {
        focusableElements = container.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        firstFocusable = focusableElements[0];
        lastFocusable = focusableElements[focusableElements.length - 1];

        container.addEventListener('keydown', handleFocusTrap);
    }

    /**
     * Handle Tab key within focus trap
     */
    function handleFocusTrap(e) {
        if (e.key !== 'Tab') return;

        if (e.shiftKey) {
            // Shift + Tab - Move backwards
            if (document.activeElement === firstFocusable) {
                e.preventDefault();
                lastFocusable.focus();
            }
        } else {
            // Tab - Move forwards
            if (document.activeElement === lastFocusable) {
                e.preventDefault();
                firstFocusable.focus();
            }
        }
    }

    /**
     * Release focus trap
     */
    function releaseFocus() {
        elements.panel.removeEventListener('keydown', handleFocusTrap);
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================

    /**
     * Toggle mobile menu on hamburger click
     */
    elements.btn.addEventListener("click", () => {
        const isOpen = elements.panel.classList.contains("hidden");
        setMobileOpen(isOpen);
    });

    /**
     * Close menu on overlay click
     */
    elements.overlay.addEventListener("click", () => {
        setMobileOpen(false);
    });

    /**
     * Close menu on ESC key
     */
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && state.isMenuOpen) {
            setMobileOpen(false);
        }
    });

    /**
     * Close menu when clicking menu items
     */
    elements.mobileItems.forEach(item => {
        item.addEventListener("click", (e) => {
            // Don't close if it's an anchor link on same page
            const href = item.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    setTimeout(() => {
                        setMobileOpen(false);
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200);
                }
            } else {
                // Close menu after short delay for external navigation
                setTimeout(() => {
                    setMobileOpen(false);
                }, 200);
            }
        });
    });

    // ========================================
    // KEYBOARD NAVIGATION (Desktop)
    // ========================================

    /**
     * Enable arrow key navigation for desktop menu
     */
    function initKeyboardNavigation() {
        const desktopMenuItems = document.querySelectorAll('nav[aria-label="Menu utama"] a');

        desktopMenuItems.forEach((item, index) => {
            item.addEventListener('keydown', (e) => {
                let targetIndex = -1;

                switch (e.key) {
                    case 'ArrowRight':
                    case 'ArrowDown':
                        e.preventDefault();
                        targetIndex = (index + 1) % desktopMenuItems.length;
                        break;

                    case 'ArrowLeft':
                    case 'ArrowUp':
                        e.preventDefault();
                        targetIndex = (index - 1 + desktopMenuItems.length) % desktopMenuItems.length;
                        break;

                    case 'Home':
                        e.preventDefault();
                        targetIndex = 0;
                        break;

                    case 'End':
                        e.preventDefault();
                        targetIndex = desktopMenuItems.length - 1;
                        break;
                }

                if (targetIndex >= 0) {
                    desktopMenuItems[targetIndex].focus();
                }
            });
        });
    }

    initKeyboardNavigation();

    // ========================================
    // WINDOW RESIZE HANDLER
    // ========================================

    /**
     * Handle window resize events
     */
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            // Close mobile menu if resizing to desktop
            if (window.innerWidth >= config.mobileBreakpoint && state.isMenuOpen) {
                setMobileOpen(false);
            }

            // Recalculate navbar visibility on orientation change
            handleScroll();
        }, 250);
    });

    // ========================================
    // PAGE VISIBILITY CHANGE
    // ========================================

    /**
     * Reset navbar state when page becomes visible again
     */
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            state.lastScrollY = window.scrollY;
            handleScroll();
        }
    });

    // ========================================
    // INITIALIZE
    // ========================================

    // Set initial state
    handleScroll();

    console.log('✅ Navbar initialized successfully (v3.0)');
    console.log('📊 Config:', config);
}
