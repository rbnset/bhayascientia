@props([
'featuredPublication' => null,
'publications' => [],
'selectedType' => 'publikasi',
'exploreAllUrl' => null
])

<section id="publication-popular"
    class="mx-auto mt-12 flex max-w-[1130px] flex-col gap-6 px-4 sm:mt-[70px] sm:px-6 lg:gap-[30px] lg:px-0"
    aria-labelledby="popular-heading">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 id="popular-heading"
            class="font-bold text-[18px] leading-[26px] sm:text-[22px] sm:leading-[32px] lg:text-[26px] lg:leading-[39px]">
            {{ ucfirst($selectedType) }} Populer <br />
            Untuk Kamu
        </h2>

        @if($exploreAllUrl)
        <a href="{{ $exploreAllUrl }}"
            class="flex w-fit items-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2 font-semibold text-xs transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18] sm:gap-[10px] sm:px-[18px] sm:py-[10px] sm:text-sm lg:p-[12px_22px] lg:text-base">
            Jelajahi Semua
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        @endif
    </div>

    {{-- Content --}}
    @if($featuredPublication || $publications->isNotEmpty())
    <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between lg:gap-6">

        {{-- Left: Featured --}}
        @if($featuredPublication)
        <x-publication.featured-card :title="$featuredPublication['title']"
            :coverUrl="$featuredPublication['cover_url']" :category="$featuredPublication['category']"
            :type="$featuredPublication['type']" :abstract="$featuredPublication['abstract'] ?? null"
            :downloadCount="$featuredPublication['download_count']" :detailUrl="$featuredPublication['detail_url']" />
        @endif

        {{-- Right: List --}}
        @if($publications->isNotEmpty())
        <div
            class="custom-scrollbar relative w-full overflow-x-hidden overflow-y-auto lg:h-[424px] lg:w-[455px] lg:px-5">
            <div class="flex flex-col w-full gap-4 lg:gap-5">
                @foreach($publications as $pub)
                <x-publication.popular-item :title="$pub['title']" :coverUrl="$pub['cover_url']"
                    :formattedDate="$pub['formatted_date']" :authors="$pub['authors']"
                    :totalAuthors="$pub['total_authors']" :downloadCount="$pub['download_count']"
                    :detailUrl="$pub['detail_url']" />
                @endforeach
            </div>

            {{-- Fade gradient di bottom (desktop only) --}}
            <div
                class="sticky bottom-0 z-10 hidden h-[100px] w-full bg-gradient-to-b from-[rgba(255,255,255,0.19)] to-[rgba(255,255,255,1)] lg:block">
            </div>
        </div>
        @endif
    </div>
    @else
    {{-- Empty State --}}
    <div class="rounded-[24px] bg-gradient-to-br from-[#FAFBFC] to-white p-8 text-center ring-1 ring-[#EEF0F7] sm:p-12">
        <div class="max-w-md mx-auto">
            <div
                class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[#FFF5ED] sm:h-24 sm:w-24">
                <svg class="h-10 w-10 text-[#FF6B18] sm:h-12 sm:w-12" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="mb-2 font-bold text-[#1A1D29] text-lg sm:text-xl">Belum Ada Publikasi Populer</h3>
            <p class="text-[#A3A6AE] text-sm sm:text-base">
                Belum ada publikasi populer untuk kategori
                <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
            </p>
        </div>
    </div>
    @endif
</section>