@extends('layouts.app')

@section('title', 'Beranda')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('content')
{{-- ✨ Wrapper dengan overflow control --}}
<div class="w-full overflow-x-hidden">

    <x-hero.home badge-icon="assets/images/icons/crown.svg" badge-text="Where Knowledge Shapes Policing."
        youtube-id="rJQOQCe30EY" />

    <x-sections.steps />
    <x-sections.featured-tabs />
    <x-sections.testimoni />
    <x-sections.coming-soon />
    <x-sections.faq />

</div>
@endsection