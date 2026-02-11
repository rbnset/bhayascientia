{{-- resources/views/components/sections/faq.blade.php (BALANCED LAYOUT) --}}

@props([
'badge' => 'FAQ',
'title' => 'Pertanyaan yang sering ditanyakan',
'description' => 'Tidak menemukan jawaban? Hubungi tim BHAYASCIENTIA, nanti dibantu.',

'ctaText' => 'Tanya Tim Support',
'ctaHref' => '/kontak',

'leftTitle' => 'Bantuan cepat',
'leftDescription' => 'FAQ ini fokus pada alur publikasi, akun author, dan hal teknis umum.',
'leftIcon' => 'assets/images/icons/device-message.svg',
'leftLinks' => [
['text' => 'Lihat publikasi', 'href' => '/publikasi', 'icon' => 'book'],
['text' => 'Pelajari alur', 'href' => '/submission-guidelines', 'icon' => 'document'],
],

'items' => [
[
'q' => 'Bagaimana cara mulai menjadi author?',
'a' => 'Daftar akun author, lengkapi profil penulis, lalu unggah naskah dari dashboard author (bukan melalui form
Kontak).',
],
[
'q' => 'Apakah status review bisa dilihat?',
'a' => 'Ya. Progres review dan permintaan revisi akan ditampilkan di dashboard, sehingga author tidak perlu menebak
status.',
],
[
'q' => 'Jika ada kendala teknis, harus ke mana?',
'a' => 'Gunakan halaman Kontak untuk masalah akun/login, unggah file, atau tampilan halaman. Sertakan screenshot agar
respon lebih cepat.',
],
[
'q' => 'Berapa lama proses review naskah?',
'a' => 'Proses review initial memakan waktu 1-2 hari kerja, kemudian peer review 3-5 hari kerja. Total sekitar 5-7 hari
kerja untuk mendapat feedback pertama.',
],
[
'q' => 'Apakah ada biaya untuk publikasi?',
'a' => '100% GRATIS! Tidak ada biaya apapun untuk pendaftaran, submission, review, maupun publikasi. Hak cipta tetap
milik penulis.',
],
],

'arrowIcon' => 'assets/images/icons/arrow-circle-down.svg',
'defaultOpen' => 0,
'singleOpen' => true,
])

@php
$items = is_array($items) ? $items : [];
$uid = 'faq_' . substr(md5(json_encode($items)), 0, 8);
$defaultOpen = is_numeric($defaultOpen) ? (int) $defaultOpen : -1;
if ($defaultOpen < 0 || $defaultOpen>= count($items)) $defaultOpen = -1;
    @endphp

    <section id="faq" class="pt-12 pb-16 mt-10 sm:mt-16 sm:pb-20 bg-gradient-to-b from-white to-[#F8F9FC]"
        data-faq="{{ $uid }}">
        <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

            {{-- ✅ Header (Tanpa CTA redundan) --}}
            <div class="text-center">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[#FFECE1] to-[#FFE8DC] border-2 border-orange-200">
                    <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-bold text-[#FF6B18]">{{ $badge }}</span>
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-black text-[#111827]">
                    {{ $title }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm sm:text-base lg:text-lg mx-auto leading-relaxed text-[#6B7280]">
                    {{ $description }}
                </p>
            </div>

            {{-- ✅ Balanced 2-Column Layout --}}
            <div class="grid items-start grid-cols-1 gap-6 mt-10 sm:mt-12 lg:grid-cols-2 lg:gap-10">

                {{-- ✅ LEFT COLUMN: Info Card + CTA --}}
                <div class="space-y-6">

                    {{-- Info Card --}}
                    <div
                        class="relative p-6 overflow-hidden transition-all duration-300 border-2 border-orange-200 shadow-lg rounded-2xl sm:rounded-3xl bg-gradient-to-br from-white to-orange-50 sm:p-8 hover:shadow-2xl">
                        <div
                            class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-[#FF6B18]/10 to-transparent rounded-full blur-3xl -z-0">
                        </div>

                        <div class="relative z-10">
                            <div class="flex items-start gap-4 mb-6">
                                <div
                                    class="h-14 w-14 flex shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] shadow-xl">
                                    <img src="{{ asset($leftIcon) }}" alt="" class="w-7 h-7 brightness-0 invert"
                                        aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="text-xl sm:text-2xl font-black text-[#111827]">
                                        {{ $leftTitle }}
                                    </h3>
                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-[#6B7280]">
                                        {{ $leftDescription }}
                                    </p>
                                </div>
                            </div>

                            {{-- Quick Links --}}
                            <div class="space-y-3">
                                <p class="text-xs font-bold text-[#6B7280] uppercase tracking-wide">Quick Access</p>

                                @foreach ($leftLinks as $lnk)
                                <a href="{{ $lnk['href'] ?? '#' }}"
                                    class="group flex items-center gap-3 px-4 py-3 font-semibold rounded-xl bg-white border-2 border-[#EEF0F7] text-[#111827] hover:border-[#FF6B18] hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">

                                    @if(($lnk['icon'] ?? '') === 'book')
                                    <svg class="w-5 h-5 text-[#FF6B18] group-hover:scale-110 transition-transform"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                    </svg>
                                    @elseif(($lnk['icon'] ?? '') === 'document')
                                    <svg class="w-5 h-5 text-[#FF6B18] group-hover:scale-110 transition-transform"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    @endif

                                    <span class="flex-1 text-sm sm:text-base">{{ $lnk['text'] ?? 'Link' }}</span>

                                    <svg class="w-5 h-5 text-[#6B7280] group-hover:text-[#FF6B18] group-hover:translate-x-1 transition-all"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                                @endforeach
                            </div>

                            {{-- Info Text --}}
                            <div class="p-4 mt-6 border border-orange-200 rounded-xl bg-white/60">
                                <p class="text-xs sm:text-sm text-[#6B7280] flex items-start gap-2">
                                    <svg class="w-4 h-4 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Masih bingung? Tim support kami siap membantu 24/7 via email atau live
                                        chat.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ✨ CTA Card (Balance dengan accordion) --}}
                    <div
                        class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] p-6 sm:p-8 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                        <div class="text-center text-white">
                            <div
                                class="inline-flex items-center justify-center mb-4 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-sm">
                                <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>

                            <h4 class="mb-2 text-xl font-black sm:text-2xl">
                                Masih ada pertanyaan?
                            </h4>

                            <p class="mb-5 text-sm leading-relaxed sm:text-base text-white/90">
                                Tim support kami siap membantu kamu dengan segala pertanyaan terkait publikasi,
                                submission, atau kendala teknis.
                            </p>

                            <a href="{{ $ctaHref }}"
                                class="inline-flex items-center justify-center gap-2 w-full px-6 py-3.5 text-[#FF6B18] bg-white font-bold rounded-xl hover:bg-gray-50 transition-all duration-300 hover:shadow-xl hover:scale-105 text-sm sm:text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $ctaText }}
                            </a>

                            <p class="mt-3 text-xs text-white/70">
                                Response time: ~1-2 jam (working hours)
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ✅ RIGHT COLUMN: Accordion --}}
                <div class="flex flex-col gap-4" data-accordion-container>
                    @foreach ($items as $i => $it)
                    @php
                    $panelId = $uid . '_panel_' . $i;
                    $btnId = $uid . '_btn_' . $i;
                    $open = ($defaultOpen === $i);
                    @endphp

                    <div class="accordion-item rounded-2xl bg-white border-2 border-[#EEF0F7] transition-all duration-300 {{ $open ? 'is-open' : '' }}"
                        data-accordion-item data-index="{{ $i }}">

                        <button type="button" id="{{ $btnId }}"
                            class="accordion-button gap-4 p-5 sm:p-6 rounded-2xl flex w-full items-center justify-between text-left group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2"
                            aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $panelId }}"
                            data-accordion-button="{{ $i }}">

                            <div class="flex items-start flex-1 gap-3">
                                <span
                                    class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-lg bg-gradient-to-br from-[#FFECE1] to-[#FFE8DC] text-[#FF6B18] font-black text-sm transition-transform duration-300">
                                    {{ $i + 1 }}
                                </span>

                                <span
                                    class="font-bold text-base sm:text-lg text-[#111827] transition-colors duration-300">
                                    {{ $it['q'] ?? '' }}
                                </span>
                            </div>

                            <span
                                class="accordion-arrow h-10 w-10 flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#F4F6FB] to-[#EEF0F7] transition-all duration-300">
                                <svg class="w-5 h-5 text-[#6B7280] transition-all duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>

                        <div id="{{ $panelId }}" class="overflow-hidden transition-all accordion-content duration-400"
                            role="region" style="max-height: {{ $open ? '500px' : '0' }};"
                            aria-labelledby="{{ $btnId }}">
                            <div class="px-5 pb-5 sm:px-6 sm:pb-6">
                                <div class="pt-3 pl-10 sm:pl-11 border-t-2 border-[#F4F6FB]">
                                    <p class="text-sm sm:text-base leading-relaxed text-[#6B7280]">
                                        {{ $it['a'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Data untuk JS --}}
        <script type="application/json" data-faq-data="{{ $uid }}">
            {!! json_encode([
            'uid' => $uid,
            'singleOpen' => (bool) $singleOpen,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    </section>

    {{-- Styles (Same as before) --}}
    @pushOnce('styles')
    <style>
        .accordion-content {
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .accordion-item {
            border-color: #EEF0F7;
            background: white;
            box-shadow: none;
        }

        .accordion-item.is-open {
            border-color: #FF6B18 !important;
            background: linear-gradient(135deg, #FFFFFF 0%, #FFF9F5 100%);
            box-shadow: 0 10px 30px rgba(255, 107, 24, 0.15);
            transform: translateY(-2px);
        }

        .accordion-item.is-open .accordion-button span:first-child {
            transform: scale(1.15);
        }

        .accordion-item.is-open .accordion-button>div>span:last-child {
            color: #FF6B18;
        }

        .accordion-item.is-open .accordion-arrow svg {
            transform: rotate(180deg);
            color: #FF6B18;
        }

        .accordion-item.is-open .accordion-arrow {
            background: linear-gradient(135deg, #FFECE1 0%, #FFE8DC 100%);
        }

        .accordion-item:not(.is-open):hover {
            border-color: #FFD4B8;
            box-shadow: 0 4px 12px rgba(255, 107, 24, 0.1);
            transform: translateY(-1px);
        }

        .accordion-item:not(.is-open):hover .accordion-button {
            background: linear-gradient(135deg, rgba(255, 107, 24, 0.02) 0%, rgba(230, 70, 39, 0.02) 100%);
        }

        .accordion-button:focus-visible {
            outline: 3px solid #FF6B18;
            outline-offset: 2px;
        }

        .accordion-item,
        .accordion-button,
        .accordion-button>div>span,
        .accordion-arrow,
        .accordion-arrow svg {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    @endPushOnce

    {{-- JavaScript (Same as before) --}}
    @pushOnce('scripts')
    <script>
        (function() {
    'use strict';

    const uid = '{{ $uid }}';
    const section = document.querySelector(`[data-faq="${uid}"]`);
    if (!section) return;

    const dataEl = section.querySelector(`[data-faq-data="${uid}"]`);
    const config = dataEl ? JSON.parse(dataEl.textContent) : { singleOpen: true };

    const container = section.querySelector('[data-accordion-container]');
    const items = section.querySelectorAll('[data-accordion-item]');
    const buttons = section.querySelectorAll('[data-accordion-button]');

    function closeItem(item) {
        const button = item.querySelector('[data-accordion-button]');
        const index = button.getAttribute('data-accordion-button');
        const panelId = `${uid}_panel_${index}`;
        const panel = document.getElementById(panelId);

        item.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
        if (panel) panel.style.maxHeight = '0';
    }

    function openItem(item) {
        const button = item.querySelector('[data-accordion-button]');
        const index = button.getAttribute('data-accordion-button');
        const panelId = `${uid}_panel_${index}`;
        const panel = document.getElementById(panelId);

        item.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        if (panel) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }
    }

    function toggleItem(clickedItem) {
        const isOpen = clickedItem.classList.contains('is-open');

        if (config.singleOpen) {
            items.forEach(item => {
                if (item !== clickedItem) {
                    closeItem(item);
                }
            });
        }

        if (isOpen) {
            closeItem(clickedItem);
        } else {
            openItem(clickedItem);
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const item = button.closest('[data-accordion-item]');
            toggleItem(item);
        });
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            items.forEach(item => {
                if (item.classList.contains('is-open')) {
                    const button = item.querySelector('[data-accordion-button]');
                    const index = button.getAttribute('data-accordion-button');
                    const panelId = `${uid}_panel_${index}`;
                    const panel = document.getElementById(panelId);
                    if (panel) {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    }
                }
            });
        }, 250);
    });

    console.log('✅ FAQ Accordion initialized');
})();
    </script>
    @endPushOnce
