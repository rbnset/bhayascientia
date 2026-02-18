@extends('layouts.app')

@section('title', isset($currentCategory) && $currentCategory
? $currentCategory->name . ' — Kategori Publikasi'
: 'Kategori Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="true"
    :showCtaAlways="true" />

<x-publication-search-filter :selectedType="$selectedType ?? 'all'" :categories="$categories ?? collect([])"
    :years="$years ?? collect([])" :topKeywords="$topKeywords ?? collect([])" :filterCategory="$filterCategory ?? null"
    :filterYear="$filterYear ?? null" :filterKeyword="$filterKeyword ?? null" :filterSort="$filterSort ?? 'latest'"
    :searchQuery="$searchQuery ?? ''" />
@endsection

{{-- ============================================================
HELPER: Resolve icon URL dari path mentah di DB
============================================================ --}}
@php
function resolveIconUrl(?string $rawIcon): ?string {
if (!$rawIcon) return null;
if (str_starts_with($rawIcon, 'http://') || str_starts_with($rawIcon, 'https://')) {
return $rawIcon; // sudah URL penuh
}
// path dari storage disk public, mis: "categories/icons/foo.png"
return asset('storage/' . ltrim($rawIcon, '/'));
}
@endphp

@section('content')

{{-- Publication Navigation --}}
<x-publication.navigation :items="config('publication.navigation')" />

@if(isset($currentCategory) && $currentCategory)

{{-- =============================================
MODE 2: Sudah pilih kategori → list publikasi
============================================= --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-[#737373] mb-6" aria-label="Breadcrumb">
        <a href="{{ route('publikasi.category') }}"
            class="hover:text-[#FF6B18] transition-colors font-medium flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Semua Kategori
        </a>
        <svg class="w-4 h-4 flex-shrink-0 text-[#C4C7CE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-bold text-[#1A1A1A] truncate">{{ $currentCategory->name }}</span>
    </nav>

    {{-- Header Kategori --}}
    <div class="flex items-start gap-4 mb-8 sm:mb-10">

        {{-- Icon Kategori --}}
        @php
        $heroIconUrl = resolveIconUrl($currentCategory->icon ?? null);
        $heroInitial = mb_strtoupper(mb_substr($currentCategory->name, 0, 1));
        @endphp
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl flex-shrink-0 flex items-center justify-center
            bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10 border-2 border-[#FFE2D2]">
            @if($heroIconUrl)
            <img src="{{ $heroIconUrl }}" alt="{{ $currentCategory->name }}"
                class="object-contain w-8 h-8 sm:w-10 sm:h-10"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="text-2xl font-black text-[#FF6B18]" style="display:none;">{{ $heroInitial }}</span>
            @else
            <span class="text-2xl font-black text-[#FF6B18]">{{ $heroInitial }}</span>
            @endif
        </div>

        {{-- Info --}}
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-2 text-xs font-bold rounded-full
                bg-[#FFF7F2] text-[#FF6B18] border border-[#FFE2D2]">
                KATEGORI
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1A1A1A] mb-1 leading-tight">
                {{ $currentCategory->name }}
            </h1>
            @if($currentCategory->description)
            <p class="text-[#737373] text-sm sm:text-base max-w-2xl leading-relaxed">
                {{ $currentCategory->description }}
            </p>
            @endif
            @if($publications)
            <div class="flex items-center gap-1.5 mt-2">
                <span class="w-2 h-2 rounded-full bg-[#FF6B18]"></span>
                <span class="text-sm text-[#737373]">
                    <strong class="text-[#1A1A1A]">{{ $publications->total() }}</strong> Publikasi ditemukan
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Publications Grid --}}
    @if($publications && $publications->count() > 0)

    <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
        @foreach($publications as $pub)
        <a href="{{ route('publikasi.show', $pub->slug) }}" class="group bg-white rounded-2xl border-2 border-[#EEF0F7] p-5
                hover:border-[#FF6B18] hover:shadow-xl hover:-translate-y-1
                transition-all duration-300 flex flex-col">

            {{-- Type Badge --}}
            @if($pub->publicationType)
            <span class="inline-flex self-start items-center px-2.5 py-1 mb-3 text-xs font-bold
                rounded-full bg-[#FFF7F2] text-[#FF6B18] border border-[#FFE2D2]">
                {{ $pub->publicationType->name }}
            </span>
            @endif

            {{-- Title --}}
            <h3 class="text-base font-black text-[#1A1A1A] mb-2 line-clamp-2 flex-1
                group-hover:text-[#FF6B18] transition-colors leading-tight">
                {{ $pub->title }}
            </h3>

            {{-- Authors --}}
            @if($pub->authors && $pub->authors->count() > 0)
            <p class="text-xs text-[#737373] mb-3 line-clamp-1">
                {{ $pub->authors->take(2)->map(fn($a) => $a->user?->name ?? $a->name ?? '')->filter()->join(', ') }}
                @if($pub->authors->count() > 2)
                <span>+{{ $pub->authors->count() - 2 }} lainnya</span>
                @endif
            </p>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-[#EEF0F7] mt-auto">
                <span class="text-xs text-[#A3A6AE]">
                    {{ $pub->published_at?->locale('id')->isoFormat('D MMM YYYY') }}
                </span>
                <svg class="w-4 h-4 text-[#FF6B18] group-hover:translate-x-1 transition-transform flex-shrink-0"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>

        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $publications->links() }}
    </div>

    @else

    {{-- Empty State --}}
    <div class="py-16 text-center">
        <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7] max-w-md mx-auto">
            <div class="w-20 h-20 mx-auto mb-4 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-[#A3A6AE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-[#1A1A1A] text-lg font-black mb-2">Belum Ada Publikasi</p>
            <p class="text-[#737373] text-sm mb-6">
                Belum ada publikasi dalam kategori
                <strong>{{ $currentCategory->name }}</strong>
            </p>
            <a href="{{ route('publikasi.category') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF6B18]
                    to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Semua Kategori
            </a>
        </div>
    </div>

    @endif

</section>

@else

{{-- =============================================
MODE 1: Belum pilih kategori → grid kategori
============================================= --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10">

    <div class="mb-8 text-center sm:mb-10">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-2 mb-4 text-xs font-bold rounded-full
            bg-gradient-to-r from-[#FFF7F2] to-[#FFE2D2] text-[#FF6B18] border border-[#FFE2D2]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            KATEGORI PUBLIKASI
        </div>

        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-[#1A1A1A] mb-4 leading-tight">
            Jelajahi Berdasarkan
            <span class="text-transparent bg-gradient-to-r from-[#FF6B18] to-[#E64627] bg-clip-text">
                Kategori
            </span>
        </h1>
        <p class="text-[#737373] text-base sm:text-lg max-w-2xl mx-auto">
            Temukan publikasi ilmiah sesuai bidang minat Anda
        </p>

        {{-- Search Trigger Bar --}}
        <div class="flex items-center max-w-2xl gap-3 mx-auto mt-6">
            <button onclick="openPublicationSearch()" class="flex-1 flex items-center gap-3 px-5 py-3.5 bg-white border-2 border-[#EEF0F7]
                    rounded-2xl hover:border-[#FF6B18] hover:shadow-md transition-all group text-left">
                <svg class="w-5 h-5 text-[#A3A6AE] group-hover:text-[#FF6B18] transition-colors flex-shrink-0"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="text-sm text-[#A3A6AE] group-hover:text-[#737373] transition-colors">
                    Cari publikasi, penulis, kata kunci...
                </span>
                <span class="ml-auto hidden sm:flex items-center gap-1 text-xs text-[#C4C7CE]">
                    <kbd class="px-1.5 py-0.5 bg-[#F8F9FC] border border-[#EEF0F7] rounded text-[10px]">Ctrl</kbd>
                    <kbd class="px-1.5 py-0.5 bg-[#F8F9FC] border border-[#EEF0F7] rounded text-[10px]">K</kbd>
                </span>
            </button>

            <button onclick="openPublicationSearch()" class="flex items-center gap-2 px-4 py-3.5 bg-white border-2 border-[#EEF0F7]
                    rounded-2xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] hover:text-[#FF6B18]
                    transition-all text-[#737373] font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="hidden sm:inline">Filter</span>
            </button>
        </div>
    </div>

    {{-- Stats Bar --}}
    @if(isset($categories) && $categories->count() > 0)
    <div class="flex items-center justify-center gap-6 mb-8 text-sm text-[#737373]">
        <span class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#FF6B18]"></span>
            <strong class="text-[#1A1A1A]">{{ $categories->count() }}</strong> Kategori Tersedia
        </span>
        <span class="w-px h-4 bg-[#EEF0F7]"></span>
        <span class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#E64627]"></span>
            <strong class="text-[#1A1A1A]">{{ $categories->sum('publications_count') }}</strong> Total Publikasi
        </span>
    </div>
    @endif

    {{-- Categories Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">

        @forelse($categories ?? [] as $category)

        @php
        $catSlug = is_array($category) ? ($category['slug'] ?? '#') : ($category->slug ?? '#');
        $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
        $catDesc = is_array($category) ? ($category['description'] ?? null) : ($category->description ?? null);
        $catCount = is_array($category) ? ($category['publications_count'] ?? 0) : ($category->publications_count ?? 0);
        $initial = $catName ? mb_strtoupper(mb_substr($catName, 0, 1)) : '?';

        // ✅ Resolve icon URL dengan benar
        $rawIcon = is_array($category) ? ($category['icon'] ?? null) : ($category->icon ?? null);
        $catIconUrl = resolveIconUrl($rawIcon);
        @endphp

        <a href="{{ $catSlug && $catSlug !== '#'
                ? route('publikasi.category.show', ['categorySlug' => $catSlug])
                : route('publikasi.category') }}" class="group bg-white rounded-2xl border-2 border-[#EEF0F7] p-6
                hover:border-[#FF6B18] hover:shadow-xl hover:-translate-y-1
                transition-all duration-300 flex flex-col">

            {{-- ✅ Icon / Initial — dengan fallback yang benar --}}
            <div class="w-14 h-14 rounded-xl mb-4 flex items-center justify-center flex-shrink-0
                bg-gradient-to-br from-[#FF6B18]/10 to-[#E64627]/10
                group-hover:from-[#FF6B18]/20 group-hover:to-[#E64627]/20
                group-hover:scale-110 transition-all duration-300">
                @if($catIconUrl)
                <img src="{{ $catIconUrl }}" alt="{{ $catName }}" class="object-contain w-7 h-7"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                {{-- Fallback initial jika gambar gagal load --}}
                <span class="text-xl font-black text-[#FF6B18]" style="display:none;">{{ $initial }}</span>
                @else
                {{-- Tidak ada icon → tampilkan initial huruf pertama --}}
                <span class="text-xl font-black text-[#FF6B18]">{{ $initial }}</span>
                @endif
            </div>

            {{-- Name --}}
            <h3 class="text-lg font-black text-[#1A1A1A] mb-2 leading-tight
                group-hover:text-[#FF6B18] transition-colors">
                {{ $catName }}
            </h3>

            {{-- Description --}}
            @if($catDesc)
            <p class="text-sm text-[#737373] line-clamp-2 flex-1 leading-relaxed mb-4">
                {{ $catDesc }}
            </p>
            @else
            <div class="flex-1 mb-4"></div>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-4 border-t border-[#EEF0F7] mt-auto">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#FF6B18] flex-shrink-0"></span>
                    <span class="text-sm font-bold text-[#FF6B18]">
                        {{ number_format($catCount) }} Publikasi
                    </span>
                </div>
                <div class="w-8 h-8 rounded-full bg-[#F8F9FC] group-hover:bg-[#FF6B18]
                    flex items-center justify-center transition-all duration-300 flex-shrink-0">
                    <svg class="w-4 h-4 text-[#737373] group-hover:text-white
                        group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>

        </a>

        @empty

        {{-- Empty State --}}
        <div class="py-16 text-center col-span-full">
            <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7] max-w-md mx-auto">
                <div class="w-20 h-20 mx-auto mb-4 bg-[#F8F9FC] rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-[#A3A6AE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <p class="text-[#1A1A1A] text-lg font-black mb-2">Belum Ada Kategori</p>
                <p class="text-[#737373] text-sm mb-6">Kategori publikasi akan segera ditambahkan</p>
                <a href="{{ route('publikasi.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF6B18]
                        to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Publikasi
                </a>
            </div>
        </div>

        @endforelse

    </div>

</section>

@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            if (typeof openPublicationSearch === 'function') {
                openPublicationSearch();
            }
        }
    });
</script>
@endpush