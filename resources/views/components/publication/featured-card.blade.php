@props([
'title',
'coverUrl',
'category',
'type',
'publicationType' => null,
'abstract' => null,
'downloadCount' => 0,
'detailUrl' => '#',
'slug' => '',
])

@php
// ✅ Use publicationType if available, fallback to type
$displayType = $publicationType ?? $type ?? 'Publikasi';

// Generate gradient berdasarkan publication type
$typeGradients = [
'Buku' => ['from' => '10B981', 'to' => '059669'],
'Jurnal' => ['from' => 'FF6B18', 'to' => 'E64627'],
'Opini' => ['from' => '3B82F6', 'to' => '1D4ED8'],
'Artikel' => ['from' => 'F59E0B', 'to' => 'D97706'],
'Penelitian' => ['from' => '8B5CF6', 'to' => '6D28D9'],
'Skripsi' => ['from' => 'EC4899', 'to' => 'BE185D'],
'Tesis' => ['from' => '06B6D4', 'to' => '0891B2'],
'Disertasi' => ['from' => 'EF4444', 'to' => 'DC2626'],
'default' => ['from' => 'A3A6AE', 'to' => '6B7280'],
];

$gradient = $typeGradients[$displayType] ?? $typeGradients['default'];
$selectedGradient = "from-[#{$gradient['from']}] to-[#{$gradient['to']}]";

// Generate initial dari title
$words = array_filter(explode(' ', $title));
$initials = '';
foreach (array_slice($words, 0, 2) as $word) {
$initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
}
@endphp

<a href="{{ $detailUrl }}"
    class="featured-news-card group relative flex h-[260px] w-full overflow-hidden rounded-[20px] transition-transform duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 sm:h-[320px] md:h-[380px] lg:h-[424px] lg:flex-1"
    aria-label="Baca publikasi featured: {{ $title }}">

    @if($coverUrl)
    {{-- Background Image --}}
    <img src="{{ $coverUrl }}"
        class="absolute inset-0 object-cover w-full h-full transition-transform duration-500 featured-bg-image thumbnail group-hover:scale-110"
        alt="Cover {{ $title }}" loading="eager"
        onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';" />

    {{-- ✅ Fallback Placeholder (ketika image error) --}}
    <div
        class="absolute inset-0 hidden items-center justify-center bg-gradient-to-br {{ $selectedGradient }} text-white p-6">
        <div class="relative flex flex-col items-center justify-center h-full space-y-4 text-center">
            {{-- Decorative Pattern Background --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="featured-pattern-{{ $slug }}" x="0" y="0" width="60" height="60"
                            patternUnits="userSpaceOnUse">
                            <circle cx="30" cy="30" r="2" fill="white" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#featured-pattern-{{ $slug }})" />
                </svg>
            </div>

            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-white/10"></div>

            {{-- Large Initial/Monogram --}}
            <div
                class="relative z-10 font-black leading-none tracking-tight text-7xl sm:text-8xl md:text-9xl opacity-95 drop-shadow-2xl">
                {{ $initials }}
            </div>

            {{-- Decorative Line --}}
            <div class="relative z-10 flex items-center justify-center gap-3">
                <div class="h-[3px] w-12 bg-white/60 rounded"></div>
                <svg class="w-4 h-4 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="h-[3px] w-12 bg-white/60 rounded"></div>
            </div>

            {{-- Title --}}
            <div
                class="relative z-10 max-w-2xl px-6 text-base font-bold leading-tight sm:text-lg md:text-xl opacity-90 line-clamp-3 drop-shadow-lg">
                {{ $title }}
            </div>

            {{-- Type Badge --}}
            <div
                class="relative z-10 inline-block px-4 py-2 text-sm font-bold border-2 rounded-full bg-white/25 backdrop-blur-sm border-white/40">
                {{ $displayType }}
            </div>
        </div>
    </div>
    @else
    {{-- ✅ No Cover - Show Placeholder --}}
    <div
        class="absolute inset-0 flex items-center justify-center bg-gradient-to-br {{ $selectedGradient }} text-white p-6">
        <div class="relative flex flex-col items-center justify-center h-full space-y-4 text-center">
            {{-- Decorative Pattern Background --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="featured-pattern-nocover-{{ $slug }}" x="0" y="0" width="60" height="60"
                            patternUnits="userSpaceOnUse">
                            <circle cx="30" cy="30" r="2" fill="white" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#featured-pattern-nocover-{{ $slug }})" />
                </svg>
            </div>

            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-white/10"></div>

            {{-- Large Initial/Monogram --}}
            <div
                class="relative z-10 font-black leading-none tracking-tight text-7xl sm:text-8xl md:text-9xl opacity-95 drop-shadow-2xl">
                {{ $initials }}
            </div>

            {{-- Decorative Line --}}
            <div class="relative z-10 flex items-center justify-center gap-3">
                <div class="h-[3px] w-12 bg-white/60 rounded"></div>
                <svg class="w-4 h-4 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="h-[3px] w-12 bg-white/60 rounded"></div>
            </div>

            {{-- Title --}}
            <div
                class="relative z-10 max-w-2xl px-6 text-base font-bold leading-tight sm:text-lg md:text-xl opacity-90 line-clamp-3 drop-shadow-lg">
                {{ $title }}
            </div>

            {{-- Type Badge --}}
            <div
                class="relative z-10 inline-block px-4 py-2 text-sm font-bold border-2 rounded-full bg-white/25 backdrop-blur-sm border-white/40">
                {{ $displayType }}
            </div>

            {{-- Corner Decorations --}}
            <div class="absolute top-0 right-0 w-32 h-32 pointer-events-none opacity-10">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0L100 0L100 100C55.8172 100 20 64.1828 20 20C20 8.9543 8.9543 0 0 0Z" fill="white" />
                </svg>
            </div>
            <div class="absolute bottom-0 left-0 w-32 h-32 rotate-180 pointer-events-none opacity-10">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0L100 0L100 100C55.8172 100 20 64.1828 20 20C20 8.9543 8.9543 0 0 0Z" fill="white" />
                </svg>
            </div>
        </div>
    </div>
    @endif

    {{-- Gradient Overlay (selalu tampil) --}}
    <div
        class="absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] via-[rgba(0,0,0,0.3)] to-[rgba(0,0,0,0.95)]">
    </div>

    {{-- Content --}}
    <div class="card-detail relative z-20 flex w-full items-end p-4 sm:p-[18px] lg:p-[30px]">
        <div class="flex max-w-[92%] flex-col gap-2 sm:gap-[10px]">
            {{-- Badge --}}
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center rounded-full bg-[#FF6B18] px-3 py-1 text-xs font-bold text-white shadow-lg">
                    {{ $displayType }}
                </span>
                @if($downloadCount > 0)
                <span
                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ number_format($downloadCount) }}
                </span>
                @endif
            </div>

            {{-- Title --}}
            <h3
                class="line-clamp-2 font-bold text-[18px] leading-[26px] text-white transition-all duration-300 group-hover:text-[#FFD9C2] sm:text-[22px] sm:leading-[30px] lg:text-[30px] lg:leading-[36px]">
                {{ $title }}
            </h3>

            {{-- Abstract --}}
            @if($abstract)
            <p class="text-xs line-clamp-2 text-white/90 sm:text-sm lg:line-clamp-3">
                {{ $abstract }}
            </p>
            @endif
        </div>
    </div>
</a>