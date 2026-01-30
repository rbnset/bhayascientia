{{-- resources/views/components/publication/card.blade.php --}}

@props([
'title' => '',
'cover' => '',
'category' => 'Umum',
'date' => '',
'status' => 'Terverifikasi',
'authors' => [],
'totalAuthors' => 0,
'detailUrl' => '#',
'slug' => '',
])

<div class="h-auto p-1 swiper-slide">
    <a href="{{ $detailUrl }}"
        class="publication-card-link group block h-full rounded-[22px] transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F4F6FB]"
        aria-label="Baca publikasi: {{ Str::limit($title, 60) }}">

        <article
            class="publication-card-inner bg-white p-4 sm:p-5 gap-3 flex h-full flex-col rounded-[22px] ring-1 ring-[#EEF0F7] transition-all duration-300 group-hover:ring-[#FF6B18]/20 group-hover:shadow-lg group-hover:shadow-[#FF6B18]/5"
            itemscope itemtype="https://schema.org/ScholarlyArticle">

            {{-- COVER IMAGE SECTION --}}
            <div class="relative">
                <div
                    class="relative aspect-[2/3] w-full overflow-hidden rounded-[20px] bg-[#F4F6FB] shadow-[0_18px_40px_-26px_rgba(0,0,0,0.65)] ring-1 ring-[#EEF0F7] transition-all duration-300 group-hover:shadow-[0_20px_45px_-28px_rgba(255,107,24,0.4)]">

                    {{-- Cover Image --}}
                    @if($cover)
                    <img src="{{ $cover }}" class="object-cover w-full h-full publication-cover-image"
                        alt="Cover publikasi {{ $title }}" loading="lazy" itemprop="image"
                        onerror="this.onerror=null; this.src='https://placehold.co/400x600/FF6B18/white?text={{ urlencode(Str::limit($title, 15, '')) }}';" />
                    @else
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-[#FF6B18] to-[#FF8B3D] text-white p-6">
                        <span class="text-sm font-bold leading-tight text-center sm:text-base">
                            {{ Str::limit($title, 50) }}
                        </span>
                    </div>
                    @endif

                    {{-- Shadow gradient overlay --}}
                    <div class="absolute inset-y-0 left-0 w-[10%] bg-gradient-to-r from-black/16 to-transparent pointer-events-none"
                        aria-hidden="true"></div>

                    {{-- Shine effect overlay --}}
                    <div class="absolute inset-0 pointer-events-none bg-gradient-to-tr from-white/0 via-white/0 to-white/10"
                        aria-hidden="true"></div>

                    {{-- Category Badge --}}
                    <span
                        class="absolute top-3 left-3 sm:top-4 sm:left-4 bg-white/95 backdrop-blur-sm px-3 py-1.5 sm:px-4 sm:py-2 font-bold text-[10px] leading-[14px] sm:text-xs sm:leading-[18px] rounded-full ring-1 ring-black/5 shadow-sm transition-all duration-200 group-hover:bg-[#FF6B18] group-hover:text-white"
                        itemprop="articleSection">
                        {{ $category }}
                    </span>
                </div>
            </div>

            {{-- CONTENT INFO SECTION --}}
            <div class="flex flex-col flex-1 min-w-0 gap-2">
                <h3 class="font-bold text-sm sm:text-base sm:leading-[24px] md:text-lg md:leading-[27px] leading-[20px] text-[#111827] transition-colors duration-200 group-hover:text-[#FF6B18]"
                    itemprop="headline">
                    <span class="line-clamp-3">{{ $title }}</span>
                </h3>

                <div class="flex flex-wrap items-center gap-2">
                    @if($date)
                    <time
                        class="text-[11px] leading-[16px] sm:text-sm sm:leading-[21px] text-[#A3A6AE] transition-colors duration-200 group-hover:text-[#6B7280]"
                        datetime="{{ $date }}" itemprop="datePublished">
                        {{ $date }}
                    </time>
                    @endif

                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] sm:text-xs font-bold ring-1 ring-emerald-200/50 transition-all duration-200 group-hover:ring-emerald-300"
                        role="status">
                        <svg class="flex-shrink-0 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $status }}
                    </span>
                </div>

                <div class="gap-3 mt-auto pt-3 flex items-center justify-between border-t border-[#EEF0F7]">
                    <div class="flex-shrink min-w-0">
                        @if(is_array($authors) && count($authors) > 0)
                        <div class="flex items-center -space-x-2">
                            @foreach(array_slice($authors, 0, 3) as $index => $author)
                            <img src="{{ $author['photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($author['name'] ?? 'Unknown') . '&background=FF6B18&color=fff&size=128&bold=true' }}"
                                class="h-8 w-8 rounded-full object-cover ring-2 ring-white transition-all duration-200 hover:scale-110 hover:z-20 hover:ring-[#FF6B18]"
                                alt="Foto {{ $author['name'] ?? 'Penulis' }}"
                                title="{{ $author['name'] ?? 'Penulis ' . ($index + 1) }}" loading="lazy"
                                style="z-index: {{ 10 - $index }};"
                                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($author['name'] ?? 'Unknown') }}&background=FF6B18&color=fff&size=128&bold=true';" />
                            @endforeach

                            @if($totalAuthors > 3)
                            <span
                                class="h-8 w-8 font-bold ring-2 ring-white grid place-items-center rounded-full bg-[#FF6B18] text-white text-[11px] transition-all duration-200 hover:scale-110"
                                style="z-index: 5;">
                                +{{ $totalAuthors - 3 }}
                            </span>
                            @endif
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-[#EEF0F7] ring-2 ring-white grid place-items-center">
                                <svg class="w-4 h-4 text-[#A3A6AE]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span class="text-[11px] text-[#A3A6AE]">Tanpa penulis</span>
                        </div>
                        @endif
                    </div>

                    <span
                        class="gap-1.5 sm:gap-2 inline-flex items-center text-[11px] sm:text-sm font-medium text-[#6B7280] transition-colors duration-200 group-hover:text-[#FF6B18] flex-shrink-0">
                        Baca detail
                        <svg class="w-4 h-4 transition-transform duration-200 rotate-180 sm:h-5 sm:w-5 group-hover:translate-x-1"
                            viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3175 3.06L6.4275 7.95C5.85 8.5275 5.85 9.4725 6.4275 10.05L11.3175 14.94"
                                stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
            </div>

        </article>
    </a>
</div>
