@props([
'featuredPublication' => null,
'featuredTypeContent' => null,
'publications' => [],
'selectedType' => 'publikasi',
'exploreAllUrl' => null
])

@php
$hasPublications = is_array($publications)
? count($publications) > 0
: (method_exists($publications, 'isNotEmpty') ? $publications->isNotEmpty() : count($publications) > 0);

// ✅ Sort by trending_score DESC di Blade sebagai double-safety
$sortedPublications = collect($publications)->sortByDesc(function ($pub) {
return (int) ($pub['trending_score'] ?? (
(int) ($pub['views_count'] ?? 0) + ((int) ($pub['download_count'] ?? 0) * 2)
));
})->values();

// ✅ Cek apakah ada TypeContent valid
$hasFeaturedContent = $featuredTypeContent && (
(is_object($featuredTypeContent) && ($featuredTypeContent->title || $featuredTypeContent->image_url)) ||
(is_array($featuredTypeContent) && !empty($featuredTypeContent['title']))
);
@endphp

<section id="publication-popular" class="mx-auto mt-12 mb-8 max-w-[1130px] px-4 sm:mt-[70px] sm:px-6 lg:px-8"
    aria-labelledby="popular-heading">

    {{-- Header --}}
    <div class="flex flex-col gap-3 mb-8 sm:mb-10 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 id="popular-heading"
                class="font-bold text-[18px] leading-[26px] sm:text-[22px] sm:leading-[32px] lg:text-[26px] lg:leading-[39px]">
                {{ ucfirst($selectedType) }} Populer <br />
                Untuk Kamu
            </h2>
            <span
                class="inline-flex items-center gap-1.5 mt-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FFF5ED] text-[#FF6B18] border border-[#FFD4B8]">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                        clip-rule="evenodd" />
                </svg>
                Terpopuler Sepanjang Waktu
            </span>
        </div>

        @if($exploreAllUrl)
        <a href="{{ $exploreAllUrl }}"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Jelajahi Semua Publikasi
        </a>
        @endif
    </div>

    {{-- ======================== Content Layout ======================== --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between lg:gap-6">

        {{-- ======================== KIRI: Featured Type Content SAJA ======================== --}}
        {{-- Lebar kiri menyesuaikan: kalau tidak ada list kanan, melebar penuh --}}
        <div class="{{ $hasPublications ? 'w-full lg:w-[calc(100%-455px-24px)] lg:min-w-[260px]' : 'w-full' }}">

            @if($hasFeaturedContent && is_object($featuredTypeContent))
            {{-- ✅ Instance model PublicationTypeContent --}}
            <x-publication.featured-card
                :title="$featuredTypeContent->title ?? ($featuredTypeContent->publicationType?->name ?? 'Publikasi')"
                :coverUrl="$featuredTypeContent->image_url"
                :category="$featuredTypeContent->publicationType?->name ?? 'Umum'"
                :publicationType="$featuredTypeContent->publicationType?->name ?? 'Publikasi'"
                :type="$featuredTypeContent->publicationType?->slug ?? 'publikasi'"
                :abstract="$featuredTypeContent->description" :downloadCount="0"
                :detailUrl="route('publikasi.browse', ['type' => $featuredTypeContent->publicationType?->slug ?? 'publikasi'])"
                :slug="$featuredTypeContent->publicationType?->slug ?? ''" />
            @elseif($hasFeaturedContent && is_array($featuredTypeContent))
            {{-- ✅ Fallback array --}}
            <x-publication.featured-card
                :title="$featuredTypeContent['title'] ?? ($featuredTypeContent['publication_type'] ?? 'Publikasi')"
                :coverUrl="$featuredTypeContent['cover_url'] ?? null"
                :category="$featuredTypeContent['category'] ?? 'Umum'"
                :publicationType="$featuredTypeContent['publication_type'] ?? 'Publikasi'"
                :type="$featuredTypeContent['type'] ?? 'publikasi'" :abstract="$featuredTypeContent['abstract'] ?? null"
                :downloadCount="$featuredTypeContent['download_count'] ?? 0"
                :detailUrl="$featuredTypeContent['detail_url'] ?? '#'" :slug="$featuredTypeContent['slug'] ?? ''" />
            @else
            {{-- ✅ EMPTY STATE KIRI - Tidak ada TypeContent, TIDAK pakai publikasi populer --}}
            <div
                class="h-full min-h-[320px] lg:min-h-[424px] flex flex-col items-center justify-center rounded-[28px] border-2 border-dashed border-[#EEF0F7] bg-gradient-to-br from-[#FAFBFC] to-white p-8 sm:p-10 text-center relative overflow-hidden">

                {{-- Background decoration --}}
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-[#FFF5ED] to-transparent rounded-bl-full opacity-60 pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-[#FFF5ED] to-transparent rounded-tr-full opacity-60 pointer-events-none">
                </div>

                {{-- Icon --}}
                <div class="relative mb-5">
                    <div
                        class="flex h-[72px] w-[72px] items-center justify-center rounded-2xl bg-gradient-to-br from-[#FFF5ED] to-[#FFE4CC] shadow-sm">
                        <svg class="h-9 w-9 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    {{-- Badge status --}}
                    <span
                        class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-[#FF6B18] text-white shadow">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>

                <h3 class="mb-2 font-bold text-[15px] text-[#1A1D29]">
                    Konten Unggulan Belum Ditambahkan
                </h3>
                <p class="text-[13px] text-[#A3A6AE] mb-6 max-w-[220px] leading-relaxed">
                    Tambahkan gambar & deskripsi unggulan untuk
                    <span class="font-semibold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
                    melalui panel admin
                </p>

                {{-- Step guide --}}
                <div class="w-full max-w-[260px] space-y-2 mb-6 text-left">
                    <div class="flex items-start gap-2.5 p-2.5 rounded-[10px] bg-[#F8F9FA]">
                        <span
                            class="flex-shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-[#FF6B18] text-white text-[10px] font-bold mt-0.5">1</span>
                        <p class="text-[11px] text-[#6B7280] leading-relaxed">Buka <strong class="text-[#1A1D29]">Admin
                                Panel</strong> → Jenis Publikasi</p>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-[10px] bg-[#F8F9FA]">
                        <span
                            class="flex-shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-[#FF6B18] text-white text-[10px] font-bold mt-0.5">2</span>
                        <p class="text-[11px] text-[#6B7280] leading-relaxed">Pilih <strong class="text-[#1A1D29]">{{
                                ucfirst($selectedType) }}</strong> → Edit Konten</p>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-[10px] bg-[#F8F9FA]">
                        <span
                            class="flex-shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-[#FF6B18] text-white text-[10px] font-bold mt-0.5">3</span>
                        <p class="text-[11px] text-[#6B7280] leading-relaxed">Upload gambar & isi deskripsi</p>
                    </div>
                </div>

                @if($exploreAllUrl)
                <a href="{{ $exploreAllUrl }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Jelajahi Semua {{ ucfirst($selectedType) }}
                </a>
                @endif
            </div>
            @endif
        </div>

        {{-- ======================== KANAN: List Popular ======================== --}}
        @if($hasPublications)
        <div
            class="custom-scrollbar relative w-full overflow-x-hidden overflow-y-auto lg:h-[424px] lg:w-[455px] lg:px-5">
            <div class="flex flex-col w-full gap-4 lg:gap-5">
                @foreach($sortedPublications as $pub)
                <x-publication.popular-item :title="$pub['title']" :coverUrl="$pub['cover_url'] ?? null"
                    :category="$pub['category'] ?? 'Umum'" :publicationType="$pub['publication_type'] ?? 'Publikasi'"
                    :formattedDate="$pub['formatted_date'] ?? ''" :authors="$pub['authors'] ?? []"
                    :totalAuthors="$pub['total_authors'] ?? 0" :downloadCount="$pub['download_count'] ?? 0"
                    :viewsCount="$pub['views_count'] ?? 0" :detailUrl="$pub['detail_url']" :slug="$pub['slug'] ?? ''" />
                @endforeach
            </div>

            {{-- Fade gradient bottom (desktop only) --}}
            <div
                class="sticky bottom-0 z-10 hidden h-[100px] w-full bg-gradient-to-b from-[rgba(255,255,255,0.19)] to-[rgba(255,255,255,1)] lg:block">
            </div>
        </div>
        @else
        {{-- ✅ Empty State Kanan - tidak ada publikasi sama sekali --}}
        <div
            class="w-full lg:w-[455px] flex flex-col items-center justify-center rounded-[24px] bg-gradient-to-br from-[#FAFBFC] to-white p-8 text-center ring-1 ring-[#EEF0F7] min-h-[320px] lg:min-h-[424px]">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#FFF5ED]">
                <svg class="h-8 w-8 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="mb-1.5 font-bold text-base text-[#1A1D29]">Belum Ada Publikasi Populer</h3>
            <p class="text-sm text-[#A3A6AE]">
                Belum ada publikasi untuk kategori
                <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
            </p>
        </div>
        @endif

    </div>
</section>