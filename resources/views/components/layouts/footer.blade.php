@props([
'brandHref' => '/',
'brandLogo' => 'assets/images/logos/logo-dark.svg',
'brandAlt' => 'DABRAKA',
'brandDesc' => 'Kami mendorong budaya literasi, riset, dan kolaborasi untuk mendukung transformasi Polri yang unggul,
adaptif, dan berstandar global.',

'primaryCtaText' => 'Buka publikasi',
'primaryCtaHref' => '/publikasi',

'secondaryCtaText' => 'Hubungi kami',
'secondaryCtaHref' => '/kontak',

'menuLinks' => [
['text' => 'Beranda', 'href' => '/'],
['text' => 'Publikasi', 'href' => '/publikasi'],
['text' => 'Tentang', 'href' => '/tentang'],
['text' => 'Kontak', 'href' => '/kontak'],
],

'helpLinks' => [
['text' => 'Alur publikasi', 'href' => '/submission-guidelines'],
['text' => 'FAQ', 'href' => '/#faq'],
['text' => 'Pencarian', 'href' => '/publikasi?search='],
],

'contactEmail' => 'hallodabraka@dabraka.org',
'contactWhatsapp' => '+62 812-6956-3333',
'contactWhatsappLink' => 'https://wa.me/628126956333',
'contactAddress' => 'JL. Trunojoyo No.3 Kebayoran Baru, Jakarta Selatan Indonesia',
'contactAddressLink' => 'https://maps.app.goo.gl/H8tz9e4WqnuhGvyh8',

'socialLinks' => [
['label' => 'Instagram', 'text' => 'IG', 'href' => 'https://instagram.com', 'icon' => 'instagram'],
['label' => 'X (Twitter)', 'text' => 'X', 'href' => 'https://twitter.com', 'icon' => 'twitter'],
['label' => 'LinkedIn', 'text' => 'in', 'href' => 'https://linkedin.com', 'icon' => 'linkedin'],
],

'privacyHref' => route('privacy-policy'),
'termsHref' => route('terms-conditions'),

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

                {{-- Brand Section --}}
                <div class="lg:col-span-5">
                    <a href="{{ $brandHref }}" class="inline-flex items-center gap-3 group"
                        aria-label="Beranda {{ $brandAlt }}">
                        <img src="{{ asset($brandLogo) }}" alt="{{ $brandAlt }}"
                            class="w-auto h-16 transition-transform duration-300 group-hover:scale-105" />
                    </a>

                    <p class="mt-4 text-sm leading-7 sm:text-base text-white/70 max-w-prose">
                        {{ $brandDesc }}
                    </p>

                    <div class="flex flex-col gap-3 mt-6 sm:flex-row">
                        <a href="{{ $primaryCtaHref }}"
                            class="px-6 py-3 text-white text-sm sm:text-[16px] font-bold inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] transition-all duration-300 hover:shadow-2xl hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $primaryCtaText }}
                        </a>

                        <a href="{{ $secondaryCtaHref }}"
                            class="px-6 py-3 text-sm sm:text-[16px] font-bold border-white/25 text-white inline-flex items-center justify-center gap-2 rounded-full border-2 transition-all duration-300 hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $secondaryCtaText }}
                        </a>
                    </div>
                </div>

                {{-- Links Grid --}}
                <div class="lg:col-span-7">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                        {{-- Menu --}}
                        <div
                            class="p-5 transition-all duration-300 border-2 rounded-2xl border-white/10 bg-white/5 hover:bg-white/10">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <p class="text-sm font-bold">Menu</p>
                            </div>
                            <ul class="space-y-2.5 text-sm">
                                @foreach ($menuLinks as $lnk)
                                <li>
                                    <a class="font-semibold text-white/70 hover:text-[#FF6B18] transition-colors duration-300 flex items-center gap-2 group"
                                        href="{{ $lnk['href'] ?? '#' }}">
                                        <svg class="w-4 h-4 opacity-0 -ml-6 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-[#FF6B18]"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                        {{ $lnk['text'] ?? 'Link' }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Bantuan --}}
                        <div
                            class="p-5 transition-all duration-300 border-2 rounded-2xl border-white/10 bg-white/5 hover:bg-white/10">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm font-bold">Bantuan</p>
                            </div>
                            <ul class="space-y-2.5 text-sm">
                                @foreach ($helpLinks as $lnk)
                                <li>
                                    <a class="font-semibold text-white/70 hover:text-[#FF6B18] transition-colors duration-300 flex items-center gap-2 group"
                                        href="{{ $lnk['href'] ?? '#' }}">
                                        <svg class="w-4 h-4 opacity-0 -ml-6 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-[#FF6B18]"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                        {{ $lnk['text'] ?? 'Link' }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- ✨ Kontak (dengan Maps link) --}}
                        <div
                            class="p-5 transition-all duration-300 border-2 rounded-2xl border-white/10 bg-white/5 hover:bg-white/10">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                </svg>
                                <p class="text-sm font-bold">Kontak</p>
                            </div>

                            <address class="space-y-3 text-sm not-italic">
                                {{-- Email --}}
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div class="flex-1 min-w-0 font-semibold">
                                        <a href="mailto:{{ $contactEmail }}"
                                            class="break-words text-white/80 hover:text-[#FF6B18] transition-colors">
                                            {{ $contactEmail }}
                                        </a>
                                    </div>
                                </div>

                                {{-- WhatsApp --}}
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-[#25D366] flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                    <div class="flex-1 min-w-0 font-semibold">
                                        <a href="{{ $contactWhatsappLink }}" target="_blank" rel="noopener noreferrer"
                                            class="break-words text-white/80 hover:text-[#25D366] transition-colors">
                                            {{ $contactWhatsapp }}
                                        </a>
                                    </div>
                                </div>

                                {{-- ✨ Alamat (dengan Google Maps link) --}}
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-[#4285F4] flex-shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div class="flex-1 min-w-0 font-semibold">
                                        <a href="{{ $contactAddressLink }}" target="_blank" rel="noopener noreferrer"
                                            class="break-words text-white/80 hover:text-[#4285F4] transition-colors inline-flex items-start gap-1 group">
                                            <span>{{ $contactAddress }}</span>
                                            <svg class="w-3.5 h-3.5 text-white/50 group-hover:text-[#4285F4] flex-shrink-0 mt-0.5"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </address>

                            {{-- Social Links --}}
                            <div class="flex flex-wrap gap-2 pt-4 mt-4 border-t border-white/10">
                                @foreach ($socialLinks as $s)
                                <a href="{{ $s['href'] ?? '#' }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-10 h-10 transition-all duration-300 border-2 rounded-full border-white/20 bg-white/5 hover:bg-[#FF6B18] hover:border-[#FF6B18] hover:scale-110"
                                    aria-label="{{ $s['label'] ?? 'Social link' }}" title="{{ $s['label'] ?? '' }}">
                                    <span class="text-xs font-bold">{{ $s['text'] ?? '' }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div
                class="flex flex-col items-start justify-between gap-4 pt-6 mt-10 border-t-2 border-white/10 sm:flex-row sm:items-center">
                <p class="text-sm font-semibold text-white/60">
                    © {{ $year }} {{ $brandAlt }}. Semua hak dilindungi.
                </p>

                <div class="flex flex-wrap text-sm font-semibold gap-x-6 gap-y-2">
                    <a href="{{ $privacyHref }}"
                        class="text-white/60 hover:text-[#FF6B18] transition-colors duration-300">
                        Kebijakan Privasi
                    </a>
                    <a href="{{ $termsHref }}"
                        class="text-white/60 hover:text-[#FF6B18] transition-colors duration-300">
                        Syarat &amp; Ketentuan
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
