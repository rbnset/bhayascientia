@props([
'badge' => 'FAQ',
'title' => 'Pertanyaan yang sering ditanyakan',
'description' => 'Tidak menemukan jawaban? Hubungi tim BHAYASCIENTIA, nanti dibantu.',

// CTA kanan atas
'ctaText' => 'Hubungi kami',
'ctaHref' => 'contatUsPage.html',

// Left note
'leftTitle' => 'Bantuan cepat',
'leftDescription' => 'FAQ ini fokus pada alur publikasi, akun author, dan hal teknis umum.',
'leftIcon' => 'assets/images/icons/device-message.svg',
'leftLinks' => [
['text' => 'Lihat publikasi', 'href' => 'publicationPage.html'],
['text' => 'Pelajari alur', 'href' => '#alur'],
],

// Accordion items
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
],

'arrowIcon' => 'assets/images/icons/arrow-circle-down.svg',

// default open index (null = semua tertutup)
'defaultOpen' => 0,
'singleOpen' => true, // UX: hanya 1 terbuka
])

@php
$items = is_array($items) ? $items : [];
$uid = 'faq_' . substr(md5(json_encode($items)), 0, 8);

// normalisasi defaultOpen
$defaultOpen = is_numeric($defaultOpen) ? (int) $defaultOpen : -1;
if ($defaultOpen < 0 || $defaultOpen>= count($items)) $defaultOpen = -1;
    @endphp

    <section id="faq" class="pt-12 mt-10 sm:mt-12" data-faq="{{ $uid }}">
        <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
            <div class="text-center">
                <p
                    class="text-xs font-bold px-4 py-2 inline-flex items-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                    {{ $badge }}
                </p>

                <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-[#111827]">
                    {{ $title }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm sm:text-base mx-auto leading-[22px] text-[#6B7280]">
                    {{ $description }}
                </p>

                <a href="{{ $ctaHref }}"
                    class="mt-5 px-6 py-3 text-white text-sm sm:text-[16px] font-bold inline-flex items-center justify-center rounded-full bg-[#FF6B18] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    {{ $ctaText }}
                </a>
            </div>

            <div class="grid items-start grid-cols-1 gap-6 mt-8 lg:grid-cols-2 lg:gap-10">
                {{-- Left note --}}
                <div class="rounded-3xl bg-white p-6 sm:p-8 border border-[#EEF0F7]">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 flex shrink-0 items-center justify-center rounded-full bg-[#FF6B18]">
                            <img src="{{ asset($leftIcon) }}" alt="" class="w-6 h-6" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#111827]">
                                {{ $leftTitle }}
                            </h3>
                            <p class="mt-1 text-sm sm:text-base leading-6 text-[#6B7280]">
                                {{ $leftDescription }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-5 text-sm">
                        @foreach ($leftLinks as $lnk)
                        <a href="{{ $lnk['href'] ?? '#' }}"
                            class="px-4 py-2 font-semibold rounded-full bg-[#F4F6FB] text-[#111827] hover:bg-[#EEF0F7]">
                            {{ $lnk['text'] ?? 'Link' }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Accordion --}}
                <div class="flex flex-col gap-4">
                    @foreach ($items as $i => $it)
                    @php
                    $panelId = $uid . '_panel_' . $i;
                    $btnId = $uid . '_btn_' . $i;
                    $open = ($defaultOpen === $i);
                    @endphp

                    <div class="rounded-2xl bg-white border border-[#EEF0F7]">
                        <button type="button" id="{{ $btnId }}"
                            class="accordion-button gap-4 p-5 sm:p-6 rounded-2xl flex w-full items-center justify-between text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                            aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $panelId }}"
                            data-accordion="{{ $panelId }}" data-index="{{ $i }}">
                            <span class="font-bold text-base sm:text-lg text-[#111827]">
                                {{ $it['q'] ?? '' }}
                            </span>

                            <span
                                class="arrow h-9 w-9 flex shrink-0 items-center justify-center rounded-full bg-[#F4F6FB]">
                                <img src="{{ asset($arrowIcon) }}" alt="" aria-hidden="true"
                                    class="h-5 w-5 transition-transform duration-300 {{ $open ? 'rotate-180' : '' }}" />
                            </span>
                        </button>

                        <div id="{{ $panelId }}"
                            class="accordion-content px-5 sm:px-6 pb-5 sm:pb-6 {{ $open ? '' : 'hidden' }}"
                            role="region" aria-labelledby="{{ $btnId }}">
                            <p class="text-sm sm:text-base leading-7 text-[#6B7280]">
                                {{ $it['a'] ?? '' }}
                            </p>
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
