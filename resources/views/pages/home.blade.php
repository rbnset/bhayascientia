@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<section class="mt-10 sm:mt-12">
    <x-hero.home badge-icon="assets/images/icons/crown.svg" badge-text="Bantu Naskahmu Naik Kelas."
        youtube-id="rJQOQCe30EY" />
    <x-sections.steps />
    <x-sections.testimoni />
    <x-sections.featured-tabs />

</section>
@endsection
