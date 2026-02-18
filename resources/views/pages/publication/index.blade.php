@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
{{-- Navbar dengan Avatar/Logo Logic --}}
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="subscription.index" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" />

{{-- Search/Filter Modal Component --}}
<x-publication-search-filter :selectedType="$selectedType" :categories="$categories ?? []" :years="$years ?? []"
    :topKeywords="$topKeywords ?? []" :filterCategory="null" :filterYear="null" :filterKeyword="null"
    :filterSort="$filterSort" :searchQuery="$searchQuery" />
@endsection

@section('content')

{{-- Publication Navigation (SUB MENU) --}}
<x-publication.navigation :items="config('publication.navigation')" />

{{-- Hero Section --}}
<x-hero.publication />

{{-- Main Content Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Quick Filter Bar (Type + Sort) --}}
    <x-publication.filter-bar title="Pilih Jenis Publikasi" helper="Buku, Jurnal, atau Opini"
        :types="$publicationTypes ?? []" :selectedType="$selectedType" :filterSort="$filterSort"
        :hasActiveFilters="false" />

    {{-- ✅ TAMBAHKAN INI: Search Trigger Bar --}}
    <div class="flex items-center gap-3 mt-4 mb-8">

        {{-- Search Input (sebagai trigger, bukan form langsung) --}}
        <button onclick="openPublicationSearch()"
            class="flex-1 flex items-center gap-3 px-5 py-3.5 bg-white border-2 border-[#EEF0F7] rounded-2xl hover:border-[#FF6B18] hover:shadow-md transition-all group text-left">
            <svg class="w-5 h-5 text-[#A3A6AE] group-hover:text-[#FF6B18] transition-colors flex-shrink-0" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="text-sm text-[#A3A6AE] group-hover:text-[#737373] transition-colors">
                {{ $searchQuery ? '"'.$searchQuery.'"' : 'Cari judul, penulis, kata kunci...' }}
            </span>
            @if($searchQuery)
            <span class="ml-auto text-xs font-bold text-[#FF6B18] bg-[#FFF7F2] px-2 py-1 rounded-lg">
                Aktif
            </span>
            @endif
        </button>

        {{-- Filter Button --}}
        <button onclick="openPublicationSearch()"
            class="flex items-center gap-2 px-4 py-3.5 bg-white border-2 border-[#EEF0F7] rounded-2xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-all text-[#737373] font-semibold text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <span class="hidden sm:inline">Filter</span>
        </button>

    </div>

    {{-- Latest Publications Grid/Swiper --}}
    <x-publication.swiper-section title="Tulisan Terbaru <br />Untuk Diskursus yang Bertanggung Jawab" badge="TERKINI"
        swiperClass="upToDateSwiper">

        @forelse($latestPublications as $publication)
        @php
        // ✅ Generate initials from title
        $words = array_filter(explode(' ', $publication['title'] ?? ''));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication['title'] ?? 'UN', 0, 2));
        }

        // ✅ Get first author name
        $firstAuthor = $publication['first_author_name'] ??
        (isset($publication['authors']) && count($publication['authors']) > 0
        ? ($publication['authors'][0]['name'] ?? 'Unknown')
        : 'Anonymous');

        // ✅ Get publication type with fallback
        $publicationType = $publication['publication_type'] ??
        $publication['type'] ??
        'Publikasi';

        // ✅ Generate placeholder URL
        $placeholderUrl = route('placeholder.cover', [
        'initials' => $initials,
        'type' => $publicationType,
        'title' => $publication['title'] ?? 'Untitled',
        'category' => $publication['category'] ?? 'Umum',
        'author' => $firstAuthor,
        ]);

        // ✅ Use cover if exists, otherwise use placeholder
        $coverImage = !empty($publication['cover_url'])
        ? $publication['cover_url']
        : $placeholderUrl;
        @endphp

        <x-publication.card :title="$publication['title'] ?? 'Untitled'" :cover="$coverImage"
            :category="$publication['category'] ?? 'Umum'" :publicationType="$publicationType"
            :date="$publication['formatted_date'] ?? ''" :authors="$publication['authors'] ?? []"
            :totalAuthors="$publication['total_authors'] ?? 0" :detailUrl="$publication['detail_url'] ?? '#'"
            :slug="$publication['slug'] ?? ''" />
        @empty
        <div class="swiper-slide">
            <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7] text-center">
                <svg class="w-20 h-20 mx-auto text-[#EEF0F7] mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[#A3A6AE] text-lg font-bold mb-2">Belum Ada Publikasi</p>
                <p class="text-[#737373] text-sm mb-6">
                    Belum ada publikasi tersedia untuk kategori ini
                </p>
            </div>
        </div>
        @endforelse
    </x-publication.swiper-section>

</section>

{{-- Best Authors Section --}}
<x-publication.best-authors :authors="$bestAuthors ?? collect([])"
    title="Penulis Terbaik<br/>dengan Kontribusi Terbanyak" badge="PENULIS TERBAIK" :selectedType="$selectedType" />

{{-- Popular Publications Section --}}
<x-publication.popular-section :featuredTypeContent="$featuredTypeContent ?? null"
    :featuredPublication="$featuredPublication ?? null" :publications="$popularPublications ?? collect([])"
    :selectedType="$selectedType" :exploreAllUrl="route('publikasi.browse', ['type' => $selectedType])" />


@endsection