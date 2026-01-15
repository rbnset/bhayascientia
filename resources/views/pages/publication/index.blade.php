{{-- resources/views/pages/publikasi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="publikasi.library" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" />
@endsection

@section('content')

{{-- Sub Navigation --}}
<x-publication.navigation :subItems="config('publication.sub_navigation')"
    :bottomItems="config('publication.bottom_navigation')" />

{{-- Hero Section --}}
<x-hero.publication />

{{-- Main Content --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Search & Filter Bar --}}
    <div class="mb-8">
        <x-publication.search.bar :value="request('search')" />
    </div>

    {{-- Filter Bar (Jenis Publikasi) --}}
    <x-publication.filter.bar :activeType="request('type', 'all')" />

    {{-- Active Filters Display --}}
    <x-publication.search.active-filters :filters="request()->only(['search', 'category', 'year'])" />

    {{-- Stats & Sort --}}
    <x-publication.stats-sort :currentPage="1" :perPage="12" :total="248" :currentSort="request('sort', 'latest')" />

    {{-- Publications Grid --}}
    <div class="grid gap-6 mb-10 sm:grid-cols-2 lg:grid-cols-3">
        @for($i = 1; $i
        <= 12; $i++) <x-publication-card :id="$i" />
        @endfor
    </div>

    {{-- Pagination --}}
    <x-pagination />

</section>

{{-- Filter Modal --}}
<x-filter-modal />

@endsection
