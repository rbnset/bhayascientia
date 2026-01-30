@extends('layouts.app')

@section('title', 'About')
@section('custom_navbar')
<x-navbar ctaLabel="Browse Publikasi" ctaRoute="publikasi.index" ctaIcon="book" :showAvatarWhenAuth="false" {{-- Logo
    hilang saat login, avatar muncul --}} :showCtaAlways="true" {{-- CTA hilang saat login --}} />
@endsection

@section('content')
<main class="mt-10 sm:mt-12">

</main>
@endsection
