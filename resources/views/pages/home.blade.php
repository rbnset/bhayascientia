@extends('layouts.app')

@section('title', 'Beranda')

@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false" {{-- Logo
    hilang saat login, avatar muncul --}} :showCtaAlways="true" {{-- CTA hilang saat login --}} />
@endsection

@section('content')
<main class="mt-10 sm:mt-12">
    <x-hero.home badge-icon="assets/images/icons/crown.svg" badge-text="Bantu Naskahmu Naik Kelas."
        youtube-id="rJQOQCe30EY" />

    <x-sections.steps />
    <x-sections.featured-tabs />
    <x-sections.testimoni />
    <x-sections.coming-soon />
    <x-sections.faq />
</main>
@endsection