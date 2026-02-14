@props([
'title' => '',
'cover' => '',
'category' => 'Umum',
'date' => '',
'status' => '',
'authors' => [],
'totalAuthors' => 0,
'detailUrl' => '#',
'slug' => '',
])

<div class="h-auto swiper-slide">
    <a href="{{ $detailUrl }}"
        class="publication-card-link group block h-full rounded-[22px] transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-[#F4F6FB]"
        aria-label="Baca publikasi: {{ Str::limit($title, 60) }}">
        <article
            class="bg-white p-4 sm:p-4 md:p-5 gap-3 hover:shadow-sm flex h-full flex-col rounded-[22px] ring-1 ring-[#EEF0F7] transition duration-300 hover:-translate-y-[1px] hover:ring-2 hover:ring-[#FF6B18]"
            itemscope itemtype="https://schema.org/ScholarlyArticle">

            {{-- COVER IMAGE --}}
            <div class="relative">
                <div
                    class="relative aspect-[2/3] w-full overflow-hidden rounded-[20px] bg-[#F4F6FB] shadow-[0_18px_40px_-26px_rgba(0,0,0,0.65)] ring-1 ring-[#EEF0F7]">

                    {{-- ✅ IMAGE - Z-INDEX 1 (BASE LAYER) --}}
                    @if($cover)
                    <img src="{{ $cover }}" class="object-cover w-full h-full"
                        style="position: absolute; inset: 0; z-index: 1;" alt="Cover publikasi {{ $title }}"
                        loading="lazy" itemprop="image"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />

                    {{-- Fallback gradient --}}
                    <div
                        style="position: absolute; inset: 0; display: none; align-items: center; justify-content: center; background: linear-gradient(135deg, #FF6B18, #FF8B3D); color: white; padding: 1.5rem; z-index: 1;">
                        <span style="font-size: 0.875rem; font-weight: 700; line-height: 1.25; text-align: center;">
                            {{ Str::limit($title, 50) }}
                        </span>
                    </div>
                    @else
                    <div
                        style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FF6B18, #FF8B3D); color: white; padding: 1.5rem; z-index: 1;">
                        <span style="font-size: 0.875rem; font-weight: 700; line-height: 1.25; text-align: center;">
                            {{ Str::limit($title, 50) }}
                        </span>
                    </div>
                    @endif

                    {{-- ✅ SHADOW OVERLAY - Z-INDEX 2 --}}
                    <div style="position: absolute; top: 0; bottom: 0; left: 0; width: 10%; background: linear-gradient(to right, rgba(0,0,0,0.16), transparent); pointer-events: none; z-index: 2;"
                        aria-hidden="true"></div>

                    {{-- ✅ SHINE OVERLAY - Z-INDEX 3 --}}
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top right, rgba(255,255,255,0), rgba(255,255,255,0), rgba(255,255,255,0.15)); pointer-events: none; z-index: 3;"
                        aria-hidden="true"></div>

                    {{-- ✅ CATEGORY BADGE - Z-INDEX 10 (TOP LAYER) --}}
                    <p class="absolute top-3 left-3 sm:top-4 sm:left-4 bg-white/95 backdrop-blur px-3 py-2 sm:px-4 sm:py-2 font-bold text-[10px] leading-[14px] sm:text-xs sm:leading-[18px] rounded-full ring-1 ring-black/5"
                        style="z-index: 10;" itemprop="articleSection">
                        {{ $category }}
                    </p>
                </div>
            </div>

            {{-- CONTENT INFO --}}
            <div class="flex flex-col flex-1 min-w-0 gap-2">
                {{-- Title --}}
                <h3 class="font-bold text-sm sm:text-base sm:leading-[24px] md:text-lg md:leading-[27px] leading-[20px] text-[#111827]"
                    itemprop="headline">
                    <span class="line-clamp-3">{{ $title }}</span>
                </h3>

                {{-- Date & Status --}}
                @if($date)
                <p class="text-[11px] leading-[16px] sm:text-sm sm:leading-[21px] text-[#A3A6AE]">
                    <time datetime="{{ $date }}" itemprop="datePublished">{{ $date }}</time>
                    @if($status)
                    <span class="mx-1">•</span>
                    <span>{{ $status }}</span>
                    @endif
                </p>
                @endif

                {{-- Authors & CTA --}}
                <div class="flex items-center justify-between gap-3 mt-auto">
                    {{-- Avatar authors --}}
                    <div class="flex-shrink-0">
                        @if(is_array($authors) && count($authors) > 0)
                        <div class="flex items-center -space-x-2">
                            @foreach(array_slice($authors, 0, 3) as $index => $author)
                            <img src="{{ $author['photo'] }}"
                                class="block object-cover transition-all duration-200 rounded-full h-7 w-7 sm:h-8 sm:w-8 ring-2 ring-white hover:scale-110"
                                style="position: relative; z-index: {{ 10 - $index }};" alt="Foto {{ $author['name'] }}"
                                title="{{ $author['name'] }}" loading="lazy"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($author['name']) }}&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';" />
                            @endforeach

                            @if($totalAuthors > 3)
                            <span
                                class="h-7 w-7 sm:h-8 sm:w-8 font-bold rounded-full bg-[#111827]/5 text-[11px] text-[#111827] ring-2 ring-white grid place-items-center transition-all duration-200 hover:scale-110"
                                style="position: relative; z-index: 5;">
                                +{{ $totalAuthors - 3 }}
                            </span>
                            @endif
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <div
                                class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-[#EEF0F7] ring-2 ring-white grid place-items-center">
                                <svg class="w-4 h-4 text-[#A3A6AE]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <span
                        class="gap-2 sm:text-sm inline-flex items-center text-[11px] text-[#6B7280] transition group-hover:text-[#FF6B18] flex-shrink-0">
                        Baca detail
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24c.3.3.3.77 0 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0Z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
            </div>
        </article>
    </a>
</div>