@props([
'featuredPublication' => null,
'featuredTypeContent' => null,
'publications' => [],
'selectedType' => 'publikasi',
'exploreAllUrl' => null
])

<section id="publication-popular" class="mx-auto mt-12 mb-8 max-w-[1130px] px-4 sm:mt-[70px] sm:px-6 lg:px-8"
    aria-labelledby="popular-heading">

    {{-- Header --}}
    <div class="flex flex-col gap-3 mb-8 sm:mb-10 sm:flex-row sm:items-center sm:justify-between">
        <h2 id="popular-heading"
            class="font-bold text-[18px] leading-[26px] sm:text-[22px] sm:leading-[32px] lg:text-[26px] lg:leading-[39px]">
            {{ ucfirst($selectedType) }} Populer <br />
            Untuk Kamu
        </h2>

        @if($exploreAllUrl)
        <a href="{{ $exploreAllUrl ?? route('publikasi.browse') }}"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Jelajahi Semua Publikasi
        </a>
        @endif
    </div>

    {{-- Content --}}
    @if($featuredTypeContent || $featuredPublication || $publications->isNotEmpty())
    <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between lg:gap-6">

        {{-- Left: Featured (Prioritas: TypeContent > Publication) --}}
        @if($featuredTypeContent)
        {{-- ✅ Prioritas 1: Featured dari PublicationTypeContent --}}
        <x-publication.featured-card :title="$featuredTypeContent['title']"
            :coverUrl="$featuredTypeContent['cover_url']" :category="$featuredTypeContent['category']"
            :publicationType="$featuredTypeContent['publication_type'] ?? $featuredTypeContent['type'] ?? 'Publikasi'"
            :type="$featuredTypeContent['type']" :abstract="$featuredTypeContent['abstract'] ?? null"
            :downloadCount="$featuredTypeContent['download_count'] ?? 0" :detailUrl="$featuredTypeContent['detail_url']"
            :slug="$featuredTypeContent['slug'] ?? ''" />
        @elseif($featuredPublication)
        {{-- ✅ Prioritas 2: Featured dari Publication paling populer --}}
        <x-publication.featured-card :title="$featuredPublication['title']"
            :coverUrl="$featuredPublication['cover_url']" :category="$featuredPublication['category']"
            :publicationType="$featuredPublication['publication_type'] ?? $featuredPublication['type'] ?? 'Publikasi'"
            :type="$featuredPublication['type']" :abstract="$featuredPublication['abstract'] ?? null"
            :downloadCount="$featuredPublication['download_count'] ?? 0" :detailUrl="$featuredPublication['detail_url']"
            :slug="$featuredPublication['slug'] ?? ''" />
        @endif

        {{-- Right: List --}}
        @if($publications->isNotEmpty())
        <div
            class="custom-scrollbar relative w-full overflow-x-hidden overflow-y-auto lg:h-[424px] lg:w-[455px] lg:px-5">
            <div class="flex flex-col w-full gap-4 lg:gap-5">
                @foreach($publications as $pub)
                <x-publication.popular-item :title="$pub['title']" :coverUrl="$pub['cover_url']"
                    :category="$pub['category'] ?? 'Umum'" :publicationType="$pub['publication_type'] ?? 'Publikasi'"
                    :formattedDate="$pub['formatted_date']" :authors="$pub['authors'] ?? []"
                    :totalAuthors="$pub['total_authors'] ?? 0" :downloadCount="$pub['download_count'] ?? 0"
                    :viewsCount="$pub['views_count'] ?? 0" :detailUrl="$pub['detail_url']" :slug="$pub['slug'] ?? ''" />
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
            <h3 class="mb-2 font-bold text-lg text-[#1A1D29] sm:text-xl">Belum Ada Publikasi Populer</h3>
            <p class="text-sm text-[#A3A6AE] sm:text-base">
                Belum ada publikasi populer untuk kategori
                <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
            </p>
        </div>
    </div>
    @endif
</section>