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

// Generate initials from title
$words = array_filter(explode(' ', $title));
$initials = '';
foreach (array_slice($words, 0, 2) as $word) {
$initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
}
if (empty($initials)) {
$initials = mb_strtoupper(mb_substr($title, 0, 2));
}

// ✅ Generate placeholder URL
$placeholderUrl = route('placeholder.cover', [
'initials' => $initials,
'type' => $displayType,
'title' => $title,
'category' => $category ?? 'Umum',
'author' => 'Anonymous',
'v' => time(), // Cache buster
]);

// ✅ Use cover or placeholder
$finalCoverUrl = $coverUrl ?: $placeholderUrl;
@endphp

<style>
    /* ✅ Featured Card Image Styles */
    .featured-news-card {
        position: relative;
        display: flex;
        overflow: hidden;
    }

    .featured-news-card .featured-bg-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .featured-news-card:hover .featured-bg-image {
        transform: scale(1.1);
    }

    /* ✅ Ensure proper image rendering */
    .featured-news-card img {
        display: block;
        max-width: none;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    /* ✅ Prevent layout shift */
    .featured-news-card::before {
        content: '';
        display: block;
        padding-bottom: 56.25%;
        /* 16:9 aspect ratio fallback */
    }

    @media (min-width: 1024px) {
        .featured-news-card::before {
            padding-bottom: 0;
        }
    }

    /* ✅ Gradient overlay z-index fix */
    .featured-news-card .gradient-overlay {
        pointer-events: none;
    }

    /* ✅ Content visibility */
    .featured-news-card .card-detail {
        pointer-events: none;
    }

    .featured-news-card .card-detail>* {
        pointer-events: auto;
    }
</style>

<a href="{{ $detailUrl }}"
    class="featured-news-card group relative flex h-[260px] w-full overflow-hidden rounded-[20px] transition-transform duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 sm:h-[320px] md:h-[380px] lg:h-[424px] lg:flex-1"
    aria-label="Baca publikasi featured: {{ $title }}">

    {{-- ✅ Background Image (always use img tag with fallback) --}}
    <img src="{{ $finalCoverUrl }}" class="featured-bg-image" alt="Cover {{ $title }}" loading="eager"
        onerror="console.error('Image failed to load:', this.src); this.onerror=null; this.src='{{ $placeholderUrl }}';">

    {{-- Gradient Overlay (always visible) --}}
    <div
        class="gradient-overlay absolute inset-0 z-10 bg-gradient-to-b from-[rgba(0,0,0,0)] via-[rgba(0,0,0,0.3)] to-[rgba(0,0,0,0.95)]">
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