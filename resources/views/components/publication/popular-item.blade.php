@props([
'title',
'coverUrl' => null,
'category' => 'Umum',
'publicationType' => 'Publikasi',
'formattedDate' => '',
'authors' => [],
'totalAuthors' => 0,
'downloadCount' => 0,
'viewsCount' => 0,
'detailUrl' => '#',
'slug' => '',
])

@php
// Generate initials dari title
$words = array_filter(explode(' ', $title ?? ''));
$initials = '';
foreach (array_slice($words, 0, 2) as $word) {
$initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
}
if (empty($initials)) {
$initials = mb_strtoupper(mb_substr($title ?? 'NN', 0, 2));
}

// First author name
$firstAuthor = (is_array($authors) && count($authors) > 0)
? ($authors[0]['name'] ?? 'Anonymous')
: 'Anonymous';

// ✅ Normalize coverUrl: null kalau kosong/string 'null'/'undefined'
$hasCover = !empty($coverUrl)
&& $coverUrl !== 'null'
&& $coverUrl !== 'undefined'
&& filter_var($coverUrl, FILTER_VALIDATE_URL) !== false;

// ✅ Placeholder SVG via PlaceholderCoverController
$placeholderUrl = route('placeholder.cover', [
'initials' => $initials,
'type' => $publicationType,
'title' => Str::limit($title ?? '', 60),
'category' => $category,
'author' => Str::limit($firstAuthor, 35),
]);

// ✅ Final cover: asli kalau ada, placeholder kalau tidak
$finalCover = $hasCover ? $coverUrl : $placeholderUrl;

$uniqueId = 'pi-' . md5(($slug ?? '') . ($title ?? ''));
@endphp

<a href="{{ $detailUrl }}"
    class="group block rounded-[22px] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white"
    aria-label="Baca detail: {{ $title }}">
    <article
        class="flex items-center gap-4 rounded-[22px] border border-[#EEF0F7] bg-white p-3 transition duration-300 hover:-translate-y-[1px] hover:shadow-sm hover:ring-2 hover:ring-[#FF6B18] hover:ring-inset sm:p-[14px]">

        {{-- ===================== COVER ===================== --}}
        <div class="relative shrink-0">
            <div
                class="relative aspect-[2/3] w-[74px] overflow-hidden rounded-[16px] bg-[#F5F6FA] shadow-[0_12px_30px_-18px_rgba(0,0,0,0.6)] ring-1 ring-black/5 sm:w-[88px] lg:w-[104px]">

                {{-- ✅ Selalu tampilkan img — pakai placeholder SVG kalau tidak ada cover --}}
                <img id="img-{{ $uniqueId }}" src="{{ $finalCover }}" alt="Cover {{ $title }}"
                    class="absolute inset-0 object-cover object-center w-full h-full transition-opacity duration-300 opacity-0"
                    loading="eager" onload="this.style.opacity='1';" onerror="
                        if(!this.dataset.errored){
                            this.dataset.errored='1';
                            this.src='{{ $placeholderUrl }}';
                            this.style.opacity='1';
                        }
                    " />

                {{-- Book spine shadow --}}
                <div
                    class="absolute inset-y-0 left-0 w-[10%] bg-gradient-to-r from-black/15 to-transparent pointer-events-none z-10">
                </div>
                {{-- Glossy overlay --}}
                <div
                    class="absolute inset-0 z-10 pointer-events-none bg-gradient-to-tr from-white/0 via-white/0 to-white/12">
                </div>
            </div>
        </div>

        {{-- ===================== CONTENT ===================== --}}
        <div class="flex flex-col self-stretch flex-1 min-w-0">
            <h3
                class="font-bold text-sm leading-[20px] text-[#111827] sm:text-base sm:leading-[24px] lg:text-lg lg:leading-[27px] transition-colors group-hover:text-[#FF6B18]">
                <span class="line-clamp-2 sm:line-clamp-3 md:line-clamp-4 lg:line-clamp-3">
                    {{ $title }}
                </span>
            </h3>

            <div class="flex flex-wrap items-center gap-3 mt-2">
                <p class="text-[11px] leading-[16px] text-[#A3A6AE] sm:text-sm sm:leading-[21px]">
                    {{ $formattedDate }}
                </p>

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
                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($author['initials'] ?? $author['name'] ?? 'UN') }}&background=FF6B18&color=fff&size=64&bold=true&font-size=0.5';" />
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