@props([
'brandHref' => 'index.html',
'brandLogo' => 'assets/images/logos/logo.svg',
'brandAlt' => 'BHAYASCIENTIA',
'brandDesc' => 'Publikasi akademik non-formal untuk penulis yang ingin naskahnya lebih rapi, risetnya lebih kuat, dan
prosesnya lebih jelas.',

'primaryCtaText' => 'Buka publikasi',
'primaryCtaHref' => 'publicationPage.html',

'secondaryCtaText' => 'Hubungi kami',
'secondaryCtaHref' => 'contatUsPage.html',

'menuLinks' => [
['text' => 'Beranda', 'href' => 'index.html'],
['text' => 'Publikasi', 'href' => 'publicationPage.html'],
['text' => 'Event', 'href' => 'eventPage.html'],
['text' => 'Tentang', 'href' => 'aboutPage.html'],
['text' => 'Kontak', 'href' => 'contatUsPage.html'],
],

'helpLinks' => [
['text' => 'Alur publikasi', 'href' => '#alur'],
['text' => 'FAQ', 'href' => '#faq'],
['text' => 'Pencarian', 'href' => 'searchPage.html'],
],

'contactEmail' => 'halo@bhayascientia.id',
'contactAddress' => 'Depok, Yogyakarta',

'socialLinks' => [
['label' => 'Instagram', 'text' => 'IG', 'href' => '#'],
['label' => 'X', 'text' => 'X', 'href' => '#'],
],

'privacyHref' => '#',
'termsHref' => '#',

// kalau mau override tahun (opsional)
'year' => null,
])

@php
$year = $year ?? now()->year;
@endphp

<footer class="text-white w-full bg-[#111827]">
    <div class="w-full h-px bg-white/10"></div>

    <div class="px-4 py-12 sm:px-6 lg:px-8 sm:py-14">
        <div class="mx-auto max-w-[1130px]">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">
                {{-- Brand --}}
                <div class="lg:col-span-5">
                    <a href="{{ $brandHref }}" class="inline-flex items-center gap-3"
                        aria-label="Beranda {{ $brandAlt }}">
                        <img src="{{ asset($brandLogo) }}" alt="{{ $brandAlt }}" class="w-auto h-8" />
                    </a>

                    <p class="mt-4 text-sm leading-7 sm:text-base text-white/70 max-w-prose">
                        {{ $brandDesc }}
                    </p>

                    <div class="flex flex-col gap-3 mt-6 sm:flex-row">
                        <a href="{{ $primaryCtaHref }}"
                            class="px-6 py-3 text-white text-sm sm:text-[16px] font-bold inline-flex items-center justify-center rounded-full bg-[#FF6B18] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            {{ $primaryCtaText }}
                        </a>

                        <a href="{{ $secondaryCtaHref }}"
                            class="px-6 py-3 text-sm sm:text-[16px] font-bold border-white/25 text-white inline-flex items-center justify-center rounded-full border transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            {{ $secondaryCtaText }}
                        </a>
                    </div>
                </div>

                {{-- Cards --}}
                <div class="lg:col-span-7">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        {{-- Menu --}}
                        <div class="p-5 border rounded-2xl border-white/10 bg-white/5">
                            <p class="text-sm font-bold">Menu</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                @foreach ($menuLinks as $lnk)
                                <li>
                                    <a class="font-semibold text-white/70 hover:text-white"
                                        href="{{ $lnk['href'] ?? '#' }}">
                                        {{ $lnk['text'] ?? 'Link' }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Bantuan --}}
                        <div class="p-5 border rounded-2xl border-white/10 bg-white/5">
                            <p class="text-sm font-bold">Bantuan</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                @foreach ($helpLinks as $lnk)
                                <li>
                                    <a class="font-semibold text-white/70 hover:text-white"
                                        href="{{ $lnk['href'] ?? '#' }}">
                                        {{ $lnk['text'] ?? 'Link' }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Kontak --}}
                        <div class="p-5 border rounded-2xl border-white/10 bg-white/5">
                            <p class="text-sm font-bold">Kontak</p>

                            <address class="mt-3 space-y-3 text-sm not-italic">
                                <div class="flex items-start gap-3">
                                    <div class="font-semibold w-14 text-white/60 shrink-0">Email</div>
                                    <div class="min-w-0 font-semibold">
                                        <a href="mailto:{{ $contactEmail }}"
                                            class="break-words text-white/80 hover:text-white">
                                            {{ $contactEmail }}
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="font-semibold w-14 text-white/60 shrink-0">Alamat</div>
                                    <div class="min-w-0 font-semibold leading-6 break-words text-white/80">
                                        {{ $contactAddress }}
                                    </div>
                                </div>
                            </address>

                            <div class="flex flex-wrap gap-2 mt-4">
                                @foreach ($socialLinks as $s)
                                <a href="{{ $s['href'] ?? '#' }}"
                                    class="inline-flex items-center justify-center w-10 h-10 transition-colors border rounded-full border-white/10 bg-white/5 hover:bg-white/10"
                                    aria-label="{{ $s['label'] ?? 'Social link' }}" title="{{ $s['label'] ?? '' }}">
                                    <span class="text-xs font-bold">{{ $s['text'] ?? '' }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom --}}
            <div
                class="flex flex-col items-start justify-between gap-4 pt-6 mt-10 border-t border-white/10 sm:flex-row sm:items-center">
                <p class="text-sm font-semibold text-white/60">
                    © {{ $year }} {{ $brandAlt }}. Semua hak dilindungi.
                </p>

                <div class="flex flex-wrap text-sm font-semibold gap-x-6 gap-y-2">
                    <a href="{{ $privacyHref }}" class="text-white/60 hover:text-white">Kebijakan Privasi</a>
                    <a href="{{ $termsHref }}" class="text-white/60 hover:text-white">Syarat &amp; Ketentuan</a>
                </div>
            </div>
        </div>
    </div>
</footer>
