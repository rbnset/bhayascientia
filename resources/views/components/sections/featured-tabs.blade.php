{{-- resources/views/components/sections/featured-tabs.blade.php (CLICKABLE ARROWS) --}}

@props([
'badge' => 'Fitur Unggulan',
'title' => 'Fitur inti untuk semua pengguna',
'description' => 'Akses gratis, temukan karya ilmiah terbaik, kelola koleksi pribadi, dan publikasikan naskah dengan
proses yang transparan dari satu platform.',
'tabs' => [
[
'step' => 'Fitur 1',
'label' => 'Akses',
'icon' => 'assets/images/icons/crown.svg',
'title' => 'Baca Gratis Tanpa Batas',
'description' => 'Nikmati akses penuh ke semua publikasi ilmiah tanpa biaya atau registrasi.',
'features' => [
'Unduh PDF lengkap kapan saja',
'Lihat semua publikasi tanpa login',
'Gratis selamanya, tanpa paywall',
'Mobile-friendly di semua device',
],
'image' => 'assets/images/thumbnails/overview.png',
'ctaText' => 'Jelajahi Publikasi',
'ctaHref' => '/publikasi',
],
[
'step' => 'Fitur 2',
'label' => 'Library',
'icon' => 'assets/images/icons/note-2.svg',
'title' => 'Koleksi Pribadi Pintar',
'description' => 'Atur, simpan, dan lanjutkan membaca karya favorit dengan mudah.',
'features' => [
'Favorites untuk akses instan',
'History baca otomatis tersimpan',
'Daftar "akan dibaca" dengan reminder',
'Sinkronisasi antar device',
],
'image' => 'assets/images/thumbnails/overview.png',
'ctaText' => 'Kelola Library',
'ctaHref' => 'publikasi/library',
],
[
'step' => 'Fitur 3',
'label' => 'Discovery',
'icon' => 'assets/images/icons/device-message.svg',
'title' => 'Temukan Karya Terbaik',
'description' => 'Pencarian canggih dan rekomendasi berbasis tren untuk riset tepat sasaran.',
'features' => [
'Filter advance: kategori, author, tanggal',
'Trending 7/30 hari berdasarkan views',
'Top author & publikasi terpopuler',
'Pencarian keyword + tag spesifik',
],
'image' => 'assets/images/thumbnails/overview.png',
'ctaText' => 'Cari Sekarang',
'ctaHref' => 'publikasi/jelajahi',
],
[
'step' => 'Fitur 4',
'label' => 'Author',
'icon' => 'assets/images/icons/lock.svg',
'title' => 'Publikasi Mudah & Transparan',
'description' => 'Upload naskah, dapatkan review profesional, dan tayang di platform terpercaya.',
'features' => [
'Upload dengan kategori & metadata lengkap',
'Review transparan + catatan revisi jelas',
'Notifikasi real-time di dashboard',
'Tayang publik setelah approval admin',
],
'image' => 'assets/images/thumbnails/overview.png',
'ctaText' => 'Mulai Upload',
'ctaHref' => 'admin/publications',
],
],
'checkIcon' => 'assets/images/icons/ic_check.svg',
])


@php
$tabs = is_array($tabs) ? $tabs : [];
$first = $tabs[0] ?? null;
$uid = 'featuredTabs_' . substr(md5(json_encode($tabs)), 0, 8);
@endphp

<section id="featured" class="pt-6 mt-10 sm:mt-12" data-featured-tabs="{{ $uid }}">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex flex-col gap-8">

            {{-- Heading --}}
            <div class="flex flex-col gap-3 text-center">
                <p
                    class="text-xs font-bold px-4 py-2 mx-auto inline-flex w-fit items-center justify-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                    {{ $badge }}
                </p>

                <h2 class="text-2xl sm:text-3xl font-bold text-[#111827]">
                    {{ $title }}
                </h2>

                <p class="text-sm sm:text-base max-w-2xl mx-auto leading-[22px] text-[#6B7280]">
                    {{ $description }}
                </p>
            </div>

            {{-- Tabs Navigation --}}
            <div class="w-full">
                <div class="pb-3 overflow-x-auto overscroll-x-contain scrollbar-hide" data-tabs-container>
                    <div class="gap-2 sm:gap-3 flex w-max min-w-full justify-start border-b border-[#E7EBEA]"
                        role="tablist" aria-label="Fitur unggulan">

                        @foreach ($tabs as $i => $tab)
                        {{-- Tab Button --}}
                        <button type="button"
                            class="tab-menu group sm:min-w-[220px] py-3 rounded-2xl flex min-w-[200px] cursor-pointer flex-col justify-between focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F8F9FC] active:scale-[0.99] transition-transform duration-200"
                            aria-label="{{ $tab['step'] }}: {{ $tab['label'] }}"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}" role="tab" id="{{ $uid }}_tab_{{ $i }}"
                            aria-controls="{{ $uid }}_panel" tabindex="{{ $i === 0 ? '0' : '-1' }}"
                            data-tab-index="{{ $i }}">

                            <div class="flex items-center gap-4">
                                <div
                                    class="tab-icon-container h-11 w-11 sm:h-[50px] sm:w-[50px] flex shrink-0 items-center justify-center rounded-full {{ $i === 0 ? 'bg-[#FF6B18]' : 'bg-[#EEF0F7]' }} transition-colors group-hover:bg-[#FF6B18]">
                                    <img src="{{ asset($tab['icon']) }}" class="w-6 h-6" alt="" aria-hidden="true" />
                                </div>

                                <div class="leading-tight text-left">
                                    <h3
                                        class="{{ $i === 0 ? 'font-semibold' : 'font-medium' }} text-base sm:text-[20px] text-[#111827]">
                                        {{ $tab['step'] }}
                                    </h3>
                                    <span class="mt-1 text-xs sm:text-sm font-semibold block text-[#6B7280]">
                                        {{ $tab['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 tab-indicator">
                                <div
                                    class="h-[3px] w-full rounded-full {{ $i === 0 ? 'bg-[#111827]' : 'bg-transparent' }} transition-all duration-300">
                                </div>
                            </div>
                        </button>

                        {{-- ✨ CLICKABLE ARROW BUTTON (Kecuali tab terakhir) --}}
                        @if(!$loop->last)
                        <div class="flex items-center justify-center px-1 pb-3 sm:px-2">
                            <button type="button" data-arrow-next="{{ $i }}"
                                class="arrow-btn w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full hover:bg-[#FFECE1] transition-all duration-300 group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2"
                                aria-label="Lanjut ke {{ $tabs[$i + 1]['step'] ?? 'fitur berikutnya' }}">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#FF6B18] transition-transform duration-300 group-hover:translate-x-1 group-hover:scale-110"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Navigation Hints --}}
                <div class="flex items-center justify-between mt-3 text-xs text-[#6B7280]">
                    <p class="sm:hidden">Geser atau klik panah →</p>
                    <p class="hidden sm:block">Klik tab atau panah untuk navigasi</p>

                    {{-- Current indicator --}}
                    <span class="px-2 py-1 rounded-md bg-[#EEF0F7] font-semibold" data-current-indicator>
                        <span data-current-num>1</span>/{{ count($tabs) }}
                    </span>
                </div>
            </div>

            {{-- Panel --}}
            <div class="flex flex-col gap-6 tab-content lg:flex-row lg:items-center lg:gap-10" role="tabpanel"
                id="{{ $uid }}_panel" aria-labelledby="{{ $uid }}_tab_0" tabindex="0">

                <div
                    class="tab-img lg:w-[450px] sm:h-[360px] lg:h-[470px] h-[240px] w-full shrink-0 overflow-hidden rounded-[26px] border border-[#EEF0F7] transition-opacity duration-300">
                    <img src="{{ $first ? asset($first['image']) : '' }}" alt="Ilustrasi fitur"
                        class="object-cover w-full h-full" />
                </div>

                <div class="flex flex-col gap-6">
                    <div class="gap-2.5 flex flex-col">
                        <h4
                            class="tab-title text-xl sm:text-2xl lg:text-[32px] lg:leading-[46px] font-bold text-[#111827]">
                            {{ $first['title'] ?? '' }}
                        </h4>

                        <p class="tab-description text-sm sm:text-base leading-7 text-[#6B7280]">
                            {{ $first['description'] ?? '' }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-4 tab-features">
                        @foreach (($first['features'] ?? []) as $f)
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-full bg-[#FF6B18]">
                                <img src="{{ asset($checkIcon) }}" alt="" class="w-4 h-4" aria-hidden="true" />
                            </div>
                            <p class="text-sm sm:text-base leading-6 font-semibold text-[#111827]">
                                {{ $f }}
                            </p>
                        </div>
                        @endforeach
                    </div>

                    <a href="{{ $first['ctaHref'] ?? '#' }}"
                        class="tab-cta px-5 py-3 text-sm font-bold hover:text-white w-fit rounded-full border border-[#111827] transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        {{ $first['ctaText'] ?? 'Pelajari' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Data untuk JS --}}
    <script type="application/json" data-featured-tabs-data="{{ $uid }}">
        {!! json_encode([
            'tabs' => array_map(function ($t) {
                return [
                    'title' => $t['title'] ?? '',
                    'description' => $t['description'] ?? '',
                    'features' => $t['features'] ?? [],
                    'image' => asset($t['image'] ?? ''),
                    'ctaText' => $t['ctaText'] ?? 'Pelajari',
                    'ctaHref' => $t['ctaHref'] ?? '#',
                ];
            }, $tabs),
            'checkIcon' => asset($checkIcon),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</section>

{{-- Enhanced Styles --}}
@pushOnce('styles')
<style>
    /* Hide scrollbar */
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Smooth scroll */
    @media (prefers-reduced-motion: no-preference) {
        .overflow-x-auto {
            scroll-behavior: smooth;
        }
    }

    /* Arrow button hover animation */
    .arrow-btn:hover svg {
        animation: arrowPulse 0.6s ease-in-out;
    }

    @keyframes arrowPulse {

        0%,
        100% {
            transform: translateX(0) scale(1);
        }

        50% {
            transform: translateX(4px) scale(1.1);
        }
    }

    /* Tab content fade */
    .tab-content.fade-out {
        opacity: 0.5;
    }
</style>
@endPushOnce

{{-- Enhanced JavaScript --}}
@pushOnce('scripts')
<script>
    (function() {
    'use strict';

    const uid = '{{ $uid }}';
    const section = document.querySelector(`[data-featured-tabs="${uid}"]`);
    if (!section) return;

    // Get data
    const dataEl = section.querySelector(`[data-featured-tabs-data="${uid}"]`);
    const data = dataEl ? JSON.parse(dataEl.textContent) : { tabs: [] };

    // Elements
    const tabButtons = section.querySelectorAll('[data-tab-index]');
    const arrowButtons = section.querySelectorAll('[data-arrow-next]');
    const tabsContainer = section.querySelector('[data-tabs-container]');
    const imgEl = section.querySelector('.tab-img img');
    const titleEl = section.querySelector('.tab-title');
    const descEl = section.querySelector('.tab-description');
    const featuresEl = section.querySelector('.tab-features');
    const ctaEl = section.querySelector('.tab-cta');
    const tabContent = section.querySelector('.tab-content');
    const currentNumEl = section.querySelector('[data-current-num]');

    let currentTab = 0;
    let isAnimating = false;

    // ✅ Switch tab function
    function switchTab(index, scrollIntoView = true) {
        if (isAnimating || index === currentTab || !data.tabs[index]) return;

        isAnimating = true;
        const tab = data.tabs[index];

        // Fade out content
        tabContent.classList.add('fade-out');

        setTimeout(() => {
            // Update buttons
            tabButtons.forEach((btn, i) => {
                const isActive = i === index;
                btn.setAttribute('aria-selected', isActive);
                btn.setAttribute('tabindex', isActive ? '0' : '-1');

                // Update styles
                const iconContainer = btn.querySelector('.tab-icon-container');
                const indicator = btn.querySelector('.tab-indicator > div');
                const heading = btn.querySelector('h3');

                if (isActive) {
                    iconContainer.classList.remove('bg-[#EEF0F7]');
                    iconContainer.classList.add('bg-[#FF6B18]');
                    indicator.classList.remove('bg-transparent');
                    indicator.classList.add('bg-[#111827]');
                    heading.classList.remove('font-medium');
                    heading.classList.add('font-semibold');
                } else {
                    iconContainer.classList.add('bg-[#EEF0F7]');
                    iconContainer.classList.remove('bg-[#FF6B18]');
                    indicator.classList.add('bg-transparent');
                    indicator.classList.remove('bg-[#111827]');
                    heading.classList.add('font-medium');
                    heading.classList.remove('font-semibold');
                }
            });

            // Update content
            if (imgEl) imgEl.src = tab.image;
            if (titleEl) titleEl.textContent = tab.title;
            if (descEl) descEl.textContent = tab.description;
            if (ctaEl) {
                ctaEl.textContent = tab.ctaText;
                ctaEl.href = tab.ctaHref;
            }

            // Update current indicator
            if (currentNumEl) currentNumEl.textContent = index + 1;

            // Update features
            if (featuresEl && tab.features) {
                featuresEl.innerHTML = tab.features.map(f => `
                    <div class="flex items-start gap-3">
                        <div class="flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-full bg-[#FF6B18]">
                            <img src="${data.checkIcon}" alt="" class="w-4 h-4" aria-hidden="true" />
                        </div>
                        <p class="text-sm sm:text-base leading-6 font-semibold text-[#111827]">${f}</p>
                    </div>
                `).join('');
            }

            // Fade in content
            setTimeout(() => {
                tabContent.classList.remove('fade-out');
                isAnimating = false;
            }, 100);

            currentTab = index;

            // Scroll active tab into view
            if (scrollIntoView && tabsContainer) {
                const activeBtn = tabButtons[index];
                if (activeBtn) {
                    const container = tabsContainer;
                    const btnLeft = activeBtn.offsetLeft;
                    const btnWidth = activeBtn.offsetWidth;
                    const containerWidth = container.clientWidth;
                    const scrollLeft = btnLeft - (containerWidth / 2) + (btnWidth / 2);

                    container.scrollTo({
                        left: scrollLeft,
                        behavior: 'smooth'
                    });
                }
            }
        }, 200);
    }

    // ✅ Tab button click handlers
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const index = parseInt(btn.dataset.tabIndex);
            switchTab(index, false); // Don't scroll when clicking tab directly
        });
    });

    // ✅ Arrow button click handlers
    arrowButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const currentIndex = parseInt(btn.dataset.arrowNext);
            const nextIndex = currentIndex + 1;

            // Visual feedback
            btn.style.transform = 'scale(0.9)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);

            // Switch to next tab
            if (nextIndex < tabButtons.length) {
                switchTab(nextIndex, true); // Scroll into view
            }
        });
    });

    // ✅ Keyboard navigation
    section.addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;

        e.preventDefault();
        let newIndex = currentTab;

        switch(e.key) {
            case 'ArrowLeft':
                newIndex = currentTab > 0 ? currentTab - 1 : tabButtons.length - 1;
                break;
            case 'ArrowRight':
                newIndex = currentTab < tabButtons.length - 1 ? currentTab + 1 : 0;
                break;
            case 'Home':
                newIndex = 0;
                break;
            case 'End':
                newIndex = tabButtons.length - 1;
                break;
        }

        switchTab(newIndex, true);
        tabButtons[newIndex].focus();
    });

    console.log('✅ Featured Tabs with clickable arrows initialized');
})();
</script>
@endPushOnce