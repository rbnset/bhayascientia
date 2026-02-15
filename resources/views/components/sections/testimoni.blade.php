{{-- resources/views/components/sections/testimoni.blade.php (SPOTLIGHT EFFECT) --}}

@props([
'badge' => 'Testimoni',
'title' => 'Kata penulis & pembaca',
'description' => 'Cerita singkat dari mereka yang pernah ikut menulis, review, atau membaca publikasi di BHAYACIENTIA.',
'items' => [
[
'stars' => 5,
'text' => 'Alurnya jelas. Dari daftar author sampai revisi, semuanya kebaca dan tidak bikin panik.',
'name' => 'Dita A.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo.png',
],
[
'stars' => 5,
'text' => 'Format dan struktur tulisan jadi lebih rapi. Checklistnya membantu banget buat memastikan sitasi konsisten.',
'name' => 'Raka P.',
'role' => 'Editor',
'avatar' => 'assets/images/photos/photo1.png',
],
[
'stars' => 5,
'text' => 'Reviewnya detail tapi tetap sopan. Revisi jadi terasa terarah, bukan sekadar "benerin ini-itu".',
'name' => 'Bagas S.',
'role' => 'Reviewer',
'avatar' => 'assets/images/photos/photo2.png',
],
[
'stars' => 5,
'text' => 'Naskah jadi lebih rapi karena ada alur review yang konsisten dan tidak "loncat-loncat".',
'name' => 'Nisa K.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo3.png',
],
[
'stars' => 5,
'text' => 'Progress naskah bisa dipantau, jadi komunikasinya lebih enak dan tidak tercecer di chat.',
'name' => 'Dita A.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo.png',
],
[
'stars' => 5,
'text' => 'Cara penyajiannya ringkas tapi padat. Cocok buat pembaca yang pengin cepat nangkep inti argumen.',
'name' => 'Sinta R.',
'role' => 'Pembaca',
'avatar' => 'assets/images/photos/photo2.png',
],
],
'columns' => [
[0, 3, 4],
[1, 5, 2],
[2, 1, 5],
],
])

@php
$starsText = fn ($n) => str_repeat('★', max(1, min(5, (int) $n)));
$uid = 'testi_' . substr(md5(json_encode($items)), 0, 8);
@endphp

<section id="testimoni" class="pt-12 mt-10 sm:mt-12" data-testimoni-section="{{ $uid }}">
    <div class="mx-auto max-w-[1130px] px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center">
            <p class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18]">
                {{ $badge }}
            </p>
            <h2 class="mt-3 text-2xl font-bold text-[#111827] sm:text-3xl">
                {{ $title }}
            </h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-[22px] text-[#6B7280] sm:text-base">
                {{ $description }}
            </p>
        </div>

        <div class="relative mt-8">
            {{-- Gradient overlays (desktop only) --}}
            <div class="testi-fade testi-fade-top"></div>
            <div class="testi-fade testi-fade-bottom"></div>

            {{-- ✨ CUSTOM CURSOR (Desktop only) --}}
            <div class="hidden spotlight-cursor lg:block" data-spotlight-cursor>
                <div class="spotlight-cursor-inner"></div>
            </div>

            {{-- MOBILE: Swipe horizontal --}}
            <div class="pb-2 -mx-4 overflow-x-auto overscroll-x-contain lg:hidden sm:-mx-6 scrollbar-hide">
                <div class="flex gap-4 px-4 w-max sm:px-6">
                    @foreach (array_slice($items, 0, 3) as $t)
                    <article
                        class="testi-card-mobile w-[280px] shrink-0 rounded-2xl border-2 border-[#EEF0F7] bg-white p-5 sm:w-[340px] transition-all duration-300 hover:border-[#FF6B18] hover:shadow-xl">
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-bold text-[#FF6B18]">{{ $starsText($t['stars'] ?? 5) }}</span>
                            <span class="sr-only">Rating {{ $t['stars'] ?? 5 }} dari 5</span>
                        </div>

                        <p class="mt-3 text-sm font-semibold leading-7 text-[#111827] sm:text-base">
                            {{ $t['text'] }}
                        </p>

                        <div class="flex items-center gap-3 mt-4">
                            <div class="h-11 w-11 overflow-hidden rounded-full border-2 border-[#EEF0F7] bg-[#F4F6FB]">
                                <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}"
                                    class="object-cover w-full h-full">
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-[#111827]">{{ $t['name'] }}</p>
                                <p class="truncate text-xs font-semibold text-[#6B7280]">{{ $t['role'] }}</p>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <p class="text-center text-xs text-[#6B7280] mt-3">Geser untuk lihat testimoni lainnya →</p>
            </div>

            {{-- DESKTOP: 3 columns with spotlight effect --}}
            <div id="testiScroller" class="hidden h-[560px] grid-cols-3 gap-6 overflow-hidden lg:grid relative"
                data-spotlight-container tabindex="0">

                @foreach ($columns as $colIndex => $indices)
                @php
                $dir = $colIndex === 1 ? 'testi-down' : 'testi-up';
                $colItems = array_values(array_filter(array_map(fn($i) => $items[$i] ?? null, $indices)));
                @endphp

                <div class="testi-col">
                    <div class="testi-track {{ $dir }}">
                        {{-- SET A --}}
                        @foreach ($colItems as $t)
                        <article class="testi-card" data-spotlight-card>
                            <div class="testi-stars">{{ $starsText($t['stars'] ?? 5) }}</div>
                            <p class="testi-text">{{ $t['text'] }}</p>
                            <div class="testi-user">
                                <div class="testi-avatar">
                                    <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}">
                                </div>
                                <div class="testi-meta">
                                    <p class="testi-name">{{ $t['name'] }}</p>
                                    <p class="testi-role">{{ $t['role'] }}</p>
                                </div>
                            </div>
                        </article>
                        @endforeach

                        {{-- SET B (duplicate for seamless loop) --}}
                        @foreach ($colItems as $t)
                        <article class="testi-card" data-spotlight-card>
                            <div class="testi-stars">{{ $starsText($t['stars'] ?? 5) }}</div>
                            <p class="testi-text">{{ $t['text'] }}</p>
                            <div class="testi-user">
                                <div class="testi-avatar">
                                    <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}">
                                </div>
                                <div class="testi-meta">
                                    <p class="testi-name">{{ $t['name'] }}</p>
                                    <p class="testi-role">{{ $t['role'] }}</p>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Enhanced Styles with Spotlight Effect --}}
@pushOnce('styles')
<style>
    /* ================================
       BASE TESTIMONI STYLES
    ================================ */
    .testi-col {
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .testi-track {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Animasi naik/turun */
    @keyframes scrollUp {
        0% {
            transform: translateY(0);
        }

        100% {
            transform: translateY(-50%);
        }
    }

    @keyframes scrollDown {
        0% {
            transform: translateY(-50%);
        }

        100% {
            transform: translateY(0);
        }
    }

    .testi-up {
        animation: scrollUp 40s linear infinite;
    }

    .testi-down {
        animation: scrollDown 40s linear infinite;
    }

    /* Pause on hover (container level) */
    #testiScroller:hover .testi-track {
        animation-play-state: paused;
    }

    /* Card styles */
    .testi-card {
        background: white;
        border: 2px solid #EEF0F7;
        border-radius: 1rem;
        padding: 1.25rem;
        min-height: 160px;
        flex-shrink: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }

    .testi-stars {
        color: #FF6B18;
        font-size: 0.875rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .testi-text {
        font-size: 0.875rem;
        line-height: 1.6;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }

    .testi-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .testi-avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 9999px;
        overflow: hidden;
        border: 2px solid #EEF0F7;
        background: #F4F6FB;
        flex-shrink: 0;
    }

    .testi-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .testi-meta {
        min-width: 0;
        flex: 1;
    }

    .testi-name {
        font-size: 0.875rem;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .testi-role {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6B7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Fade gradients */
    .testi-fade {
        position: absolute;
        left: 0;
        right: 0;
        height: 80px;
        pointer-events: none;
        z-index: 10;
    }

    .testi-fade-top {
        top: 0;
        background: linear-gradient(to bottom, #F8F9FC 0%, transparent 100%);
    }

    .testi-fade-bottom {
        bottom: 0;
        background: linear-gradient(to top, #F8F9FC 0%, transparent 100%);
    }

    /* Hide scrollbar for mobile */
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* ================================
       ✨ SPOTLIGHT EFFECT
    ================================ */

    /* Custom cursor */
    .spotlight-cursor {
        position: fixed;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        transform: translate(-50%, -50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .spotlight-cursor-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: radial-gradient(circle,
                rgba(255, 107, 24, 0.15) 0%,
                rgba(255, 107, 24, 0.08) 30%,
                rgba(255, 107, 24, 0.02) 60%,
                transparent 100%);
        box-shadow: 0 0 60px 30px rgba(255, 107, 24, 0.1);
        animation: spotlightPulse 2s ease-in-out infinite;
    }

    @keyframes spotlightPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    /* Show cursor only inside container */
    [data-spotlight-container]:hover~[data-spotlight-cursor],
    [data-spotlight-container] [data-spotlight-cursor] {
        opacity: 1;
    }

    /* Blur effect on non-hovered cards */
    [data-spotlight-container].spotlight-active [data-spotlight-card] {
        filter: blur(2px) brightness(0.7);
        opacity: 0.4;
        transform: scale(0.98);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Focused card - no blur, highlighted */
    [data-spotlight-container].spotlight-active [data-spotlight-card].spotlight-focus {
        filter: blur(0) brightness(1);
        opacity: 1;
        transform: scale(1.02);
        border-color: #FF6B18;
        box-shadow:
            0 20px 40px rgba(255, 107, 24, 0.15),
            0 0 0 3px rgba(255, 107, 24, 0.1),
            inset 0 0 0 1px rgba(255, 107, 24, 0.2);
        z-index: 50;
        background: linear-gradient(135deg, #FFFFFF 0%, #FFF9F5 100%);
    }

    /* Glow effect on focused card */
    [data-spotlight-card].spotlight-focus::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        background: linear-gradient(135deg,
                rgba(255, 107, 24, 0.3),
                rgba(230, 70, 39, 0.3));
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
        filter: blur(10px);
    }

    [data-spotlight-card].spotlight-focus::before {
        opacity: 1;
    }

    /* Enhanced text on focus */
    [data-spotlight-card].spotlight-focus .testi-stars {
        transform: scale(1.05);
        text-shadow: 0 2px 8px rgba(255, 107, 24, 0.3);
    }

    [data-spotlight-card].spotlight-focus .testi-text {
        color: #0F172A;
    }

    [data-spotlight-card].spotlight-focus .testi-name {
        color: #FF6B18;
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {

        .testi-up,
        .testi-down,
        .spotlight-cursor-inner {
            animation: none !important;
        }

        .testi-card,
        [data-spotlight-card] {
            transition: none !important;
        }
    }

    /* Mobile enhancements */
    @media (max-width: 1023px) {
        .testi-card-mobile:active {
            transform: scale(0.98);
        }
    }
</style>
@endPushOnce

{{-- Enhanced JavaScript with Spotlight Effect --}}
@pushOnce('scripts')
<script>
    (function() {
    'use strict';

    const uid = '{{ $uid }}';
    const section = document.querySelector(`[data-testimoni-section="${uid}"]`);
    if (!section) return;

    const container = section.querySelector('[data-spotlight-container]');
    const cursor = section.querySelector('[data-spotlight-cursor]');
    const cards = section.querySelectorAll('[data-spotlight-card]');

    if (!container || !cursor || !cards.length) return;

    let currentFocusedCard = null;
    let rafId = null;

    // ✅ Update cursor position smoothly
    function updateCursorPosition(x, y) {
        if (rafId) cancelAnimationFrame(rafId);

        rafId = requestAnimationFrame(() => {
            cursor.style.left = x + 'px';
            cursor.style.top = y + 'px';
        });
    }

    // ✅ Find closest card to cursor
    function findClosestCard(mouseX, mouseY) {
        let closest = null;
        let closestDistance = Infinity;

        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const cardCenterX = rect.left + rect.width / 2;
            const cardCenterY = rect.top + rect.height / 2;

            const distance = Math.hypot(mouseX - cardCenterX, mouseY - cardCenterY);

            if (distance < closestDistance && distance < 250) { // 250px threshold
                closestDistance = distance;
                closest = card;
            }
        });

        return closest;
    }

    // ✅ Mouse move handler
    function handleMouseMove(e) {
        const rect = container.getBoundingClientRect();
        const x = e.clientX;
        const y = e.clientY;

        // Update cursor position
        updateCursorPosition(x, y);

        // Find and highlight closest card
        const closestCard = findClosestCard(x, y);

        if (closestCard !== currentFocusedCard) {
            // Remove focus from previous card
            if (currentFocusedCard) {
                currentFocusedCard.classList.remove('spotlight-focus');
            }

            // Add focus to new card
            if (closestCard) {
                closestCard.classList.add('spotlight-focus');
                container.classList.add('spotlight-active');
            } else {
                container.classList.remove('spotlight-active');
            }

            currentFocusedCard = closestCard;
        }
    }

    // ✅ Mouse enter container
    function handleMouseEnter() {
        cursor.style.opacity = '1';
    }

    // ✅ Mouse leave container
    function handleMouseLeave() {
        cursor.style.opacity = '0';

        // Remove all focus states
        cards.forEach(card => card.classList.remove('spotlight-focus'));
        container.classList.remove('spotlight-active');
        currentFocusedCard = null;
    }

    // ✅ Attach event listeners
    container.addEventListener('mousemove', handleMouseMove);
    container.addEventListener('mouseenter', handleMouseEnter);
    container.addEventListener('mouseleave', handleMouseLeave);

    // ✅ Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (rafId) cancelAnimationFrame(rafId);
        container.removeEventListener('mousemove', handleMouseMove);
        container.removeEventListener('mouseenter', handleMouseEnter);
        container.removeEventListener('mouseleave', handleMouseLeave);
    });

    console.log('✅ Testimonial Spotlight Effect initialized');
})();
</script>
@endPushOnce