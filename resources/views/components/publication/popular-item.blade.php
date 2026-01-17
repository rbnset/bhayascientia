@props([
'title',
'coverUrl',
'formattedDate',
'authors' => [],
'totalAuthors' => 0,
'downloadCount' => 0,
'detailUrl' => '#'
])

{{-- ✅ DEBUG: Uncomment untuk cek data --}}
{{-- @dump($coverUrl, $authors) --}}

<a href="{{ $detailUrl }}"
    class="group block rounded-[22px] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white"
    aria-label="Baca detail: {{ $title }}">
    <article
        class="flex items-center gap-4 rounded-[22px] border border-[#EEF0F7] bg-white p-3 transition duration-300 hover:-translate-y-[1px] hover:shadow-sm hover:ring-2 hover:ring-[#FF6B18] hover:ring-inset sm:p-[14px]">

        {{-- Cover --}}
        <div class="relative shrink-0">
            <div
                class="relative aspect-[2/3] w-[74px] overflow-hidden rounded-[16px] bg-[#F5F6FA] shadow-[0_12px_30px_-18px_rgba(0,0,0,0.6)] ring-1 ring-black/5 sm:w-[88px] lg:w-[104px]">

                {{-- Book spine shadow --}}
                <div class="absolute inset-y-0 left-0 w-[10%] bg-gradient-to-r from-black/15 to-transparent"></div>

                {{-- Glossy overlay --}}
                <div
                    class="absolute inset-0 pointer-events-none bg-gradient-to-tr from-white/0 via-white/0 to-white/12">
                </div>

                {{-- Cover Image --}}
                <img src="{{ $coverUrl }}" alt="Cover {{ $title }}" class="object-cover w-full h-full" loading="eager"
                    onload="console.log('✅ Cover loaded:', this.src)"
                    onerror="console.error('❌ Cover failed:', this.src); this.onerror=null; this.src='https://placehold.co/200x300/FF6B18/white?text=Cover';" />
            </div>
        </div>

        {{-- Content --}}
        <div class="flex flex-col self-stretch flex-1 min-w-0">
            {{-- Title --}}
            <h3
                class="font-bold text-sm leading-[20px] text-[#111827] sm:text-base sm:leading-[24px] lg:text-lg lg:leading-[27px]">
                <span class="line-clamp-2 sm:line-clamp-3 md:line-clamp-4 lg:line-clamp-3">
                    {{ $title }}
                </span>
            </h3>

            {{-- Date & Download --}}
            <div class="flex items-center gap-3 mt-2">
                <p class="text-[11px] leading-[16px] text-[#A3A6AE] sm:text-sm sm:leading-[21px]">
                    {{ $formattedDate }}
                </p>
                @if($downloadCount > 0)
                <span class="inline-flex items-center gap-1 text-[10px] text-[#6B7280] sm:text-xs">
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
                    <img src="{{ $author['photo'] }}"
                        class="object-cover w-6 h-6 transition-all duration-200 rounded-full ring-2 ring-white hover:scale-110 hover:z-20"
                        alt="{{ $author['name'] }}" title="{{ $author['name'] }}" loading="eager"
                        style="z-index: {{ 10 - $index }};" onload="console.log('✅ Author photo loaded:', this.src)"
                        onerror="console.error('❌ Author photo failed:', this.src); this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($author['initials'] ?? 'UN') }}&background=FF6B18&color=fff&size=64&bold=true&font-size=0.5';" />
                    @endforeach

                    @if($totalAuthors > 3)
                    <span
                        class="grid h-6 w-6 place-items-center rounded-full bg-[#111827]/5 text-[10px] font-bold text-[#111827] ring-2 ring-white"
                        style="z-index: 5;">
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
                    </div>
                    @endif
                </div>

                {{-- CTA --}}
                <span
                    class="inline-flex items-center gap-2 text-[11px] text-[#6B7280] transition group-hover:text-[#FF6B18] sm:text-sm">
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