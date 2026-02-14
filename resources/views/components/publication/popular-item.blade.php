@props([
'title',
'coverUrl',
'category' => 'Umum',
'publicationType' => 'Publikasi',
'formattedDate',
'authors' => [],
'totalAuthors' => 0,
'downloadCount' => 0,
'viewsCount' => 0,
'detailUrl' => '#',
'slug' => '',
])

@php
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

$gradient = $typeGradients[$publicationType] ?? $typeGradients['default'];
$selectedGradient = "from-[#{$gradient['from']}] to-[#{$gradient['to']}]";

// Generate initial dari title
$words = array_filter(explode(' ', $title));
$initials = '';
foreach (array_slice($words, 0, 2) as $word) {
$initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
}
@endphp

<a href="{{ $detailUrl }}"
    class="group block rounded-[22px] focus:outline-none focus-visible:ring-2 focus-visual:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white"
    aria-label="Baca detail: {{ $title }}">
    <article
        class="flex items-center gap-4 rounded-[22px] border border-[#EEF0F7] bg-white p-3 transition duration-300 hover:-translate-y-[1px] hover:shadow-sm hover:ring-2 hover:ring-[#FF6B18] hover:ring-inset sm:p-[14px]">

        {{-- Cover --}}
        <div class="relative shrink-0">
            <div
                class="relative aspect-[2/3] w-[74px] overflow-hidden rounded-[16px] bg-[#F5F6FA] shadow-[0_12px_30px_-18px_rgba(0,0,0,0.6)] ring-1 ring-black/5 sm:w-[88px] lg:w-[104px]">

                @if($coverUrl)
                {{-- Real Cover Image --}}
                <img src="{{ $coverUrl }}" alt="Cover {{ $title }}" class="object-cover w-full h-full cover-image"
                    loading="eager"
                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';" />

                {{-- ✅ Fallback Placeholder (ketika image error) --}}
                <div
                    class="absolute inset-0 hidden items-center justify-center bg-gradient-to-br {{ $selectedGradient }} text-white p-2">
                    <div class="flex flex-col items-center justify-center h-full space-y-1 text-center">
                        {{-- Decorative Pattern --}}
                        <div class="absolute inset-0 opacity-10">
                            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="item-pattern-{{ $slug }}" x="0" y="0" width="20" height="20"
                                        patternUnits="userSpaceOnUse">
                                        <circle cx="10" cy="10" r="1" fill="white" />
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#item-pattern-{{ $slug }})" />
                            </svg>
                        </div>

                        {{-- Initial --}}
                        <div class="relative z-10 text-2xl font-black leading-none opacity-95 drop-shadow">
                            {{ $initials }}
                        </div>

                        {{-- Small decorative line --}}
                        <div class="relative z-10 flex items-center justify-center gap-1">
                            <div class="h-[1.5px] w-3 bg-white/60 rounded"></div>
                            <svg class="w-2 h-2 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="h-[1.5px] w-3 bg-white/60 rounded"></div>
                        </div>

                        {{-- Type badge --}}
                        <div
                            class="relative z-10 px-1.5 py-0.5 text-[8px] font-bold bg-white/25 backdrop-blur-sm rounded-full border border-white/30">
                            {{ $publicationType }}
                        </div>
                    </div>
                </div>
                @else
                {{-- ✅ No Cover - Show Placeholder --}}
                <div
                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br {{ $selectedGradient }} text-white p-2">
                    <div class="flex flex-col items-center justify-center h-full space-y-1 text-center">
                        {{-- Decorative Pattern --}}
                        <div class="absolute inset-0 opacity-10">
                            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="item-pattern-nocover-{{ $slug }}" x="0" y="0" width="20" height="20"
                                        patternUnits="userSpaceOnUse">
                                        <circle cx="10" cy="10" r="1" fill="white" />
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#item-pattern-nocover-{{ $slug }})" />
                            </svg>
                        </div>

                        {{-- Initial --}}
                        <div class="relative z-10 text-2xl font-black leading-none opacity-95 drop-shadow">
                            {{ $initials }}
                        </div>

                        {{-- Small decorative line --}}
                        <div class="relative z-10 flex items-center justify-center gap-1">
                            <div class="h-[1.5px] w-3 bg-white/60 rounded"></div>
                            <svg class="w-2 h-2 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="h-[1.5px] w-3 bg-white/60 rounded"></div>
                        </div>

                        {{-- Type badge --}}
                        <div
                            class="relative z-10 px-1.5 py-0.5 text-[8px] font-bold bg-white/25 backdrop-blur-sm rounded-full border border-white/30">
                            {{ $publicationType }}
                        </div>
                    </div>
                </div>
                @endif

                {{-- Book spine shadow --}}
                <div
                    class="absolute inset-y-0 left-0 w-[10%] bg-gradient-to-r from-black/15 to-transparent pointer-events-none">
                </div>

                {{-- Glossy overlay --}}
                <div
                    class="absolute inset-0 pointer-events-none bg-gradient-to-tr from-white/0 via-white/0 to-white/12">
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex flex-col self-stretch flex-1 min-w-0">
            {{-- Title --}}
            <h3
                class="font-bold text-sm leading-[20px] text-[#111827] sm:text-base sm:leading-[24px] lg:text-lg lg:leading-[27px] transition-colors group-hover:text-[#FF6B18]">
                <span class="line-clamp-2 sm:line-clamp-3 md:line-clamp-4 lg:line-clamp-3">
                    {{ $title }}
                </span>
            </h3>

            {{-- Date & Stats --}}
            <div class="flex flex-wrap items-center gap-3 mt-2">
                <p class="text-[11px] leading-[16px] text-[#A3A6AE] sm:text-sm sm:leading-[21px]">
                    {{ $formattedDate }}
                </p>

                {{-- ✅ Views Count --}}
                @if($viewsCount > 0)
                <span class="inline-flex items-center gap-1 text-[10px] text-[#6B7280] sm:text-xs"
                    title="{{ number_format($viewsCount) }} views">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ number_format($viewsCount) }}
                </span>
                @endif

                {{-- ✅ Download Count --}}
                @if($downloadCount > 0)
                <span class="inline-flex items-center gap-1 text-[10px] text-[#6B7280] sm:text-xs"
                    title="{{ number_format($downloadCount) }} downloads">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ number_format($downloadCount) }}
                </span>
                @endif
            </div>

            {{-- Authors & CTA --}}
            <div class="flex items-center justify-between gap-3 mt-2">
                {{-- Authors --}}
                <div class="flex items-center -space-x-2 shrink-0">
                    @if(is_array($authors) && count($authors) > 0)
                    @foreach(array_slice($authors, 0, 3) as $index => $author)
                    <img src="{{ $author['photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($author['name'] ?? 'Unknown') . '&background=FF6B18&color=fff&size=64&bold=true&font-size=0.5' }}"
                        class="object-cover w-6 h-6 transition-all duration-200 rounded-full ring-2 ring-white hover:scale-110 hover:z-20"
                        alt="{{ $author['name'] ?? 'Penulis' }}"
                        title="{{ $author['name'] ?? 'Penulis ' . ($index + 1) }}" loading="eager"
                        style="z-index: {{ 10 - $index }};"
                        onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($author['initials'] ?? $author['name'] ?? 'UN') }}&background=FF6B18&color=fff&size=64&bold=true&font-size=0.5';" />
                    @endforeach

                    @if($totalAuthors > 3)
                    <span
                        class="grid h-6 w-6 place-items-center rounded-full bg-[#FF6B18] text-[10px] font-bold text-white ring-2 ring-white transition-transform hover:scale-110"
                        style="z-index: 5;" title="+{{ $totalAuthors - 3 }} penulis lainnya">
                        +{{ $totalAuthors - 3 }}
                    </span>
                    @endif
                    @else
                    <div class="flex items-center gap-2">
                        <div class="grid h-6 w-6 place-items-center rounded-full bg-[#EEF0F7] ring-2 ring-white">
                            <svg class="h-3 w-3 text-[#A3A6AE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="text-[10px] text-[#A3A6AE] sm:text-xs">Tanpa penulis</span>
                    </div>
                    @endif
                </div>

                {{-- CTA --}}
                <span
                    class="inline-flex items-center gap-2 text-[11px] text-[#6B7280] transition group-hover:text-[#FF6B18] sm:text-sm">
                    Baca detail
                    <svg viewBox="0 0 20 20" fill="currentColor"
                        class="w-4 h-4 transition-transform group-hover:translate-x-1">
                        <path fill-rule="evenodd"
                            d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24c.3.3.3.77 0 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
        </div>
    </article>
</a>