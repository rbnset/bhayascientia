@props([
'title' => 'DABRAKA — Portal Pengabdian Intelektual Kepolisian Indonesia',
'description' => 'Darma Brata Buana Cendekia merupakan wadah pengabdian intelektual yang menghimpun kontribusi pemikiran
dari insan Bhayangkara dan akademisi untuk pengembangan ilmu kepolisian Indonesia.',
'image' => null,
'url' => null,
'type' => 'website',
'noindex' => false,
])

@php
$siteUrl = config('app.url');
$siteName = 'DABRAKA';
$image = $image ?? $siteUrl . '/assets/images/logos/logo-brand.png';
$url = $url ?? request()->url();
$fullTitle = str_contains($title, 'DABRAKA') ? $title : $title . ' — DABRAKA';

$schema = json_encode([
'@context' => 'https://schema.org',
'@type' => 'Organization',
'name' => 'DABRAKA',
'alternateName' => 'Darma Brata Buana Cendekia',
'url' => $siteUrl,
'logo' => $siteUrl . '/assets/images/logos/logo.png',
'description' => $description,
'sameAs' => [],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp

{{-- Primary --}}
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $noindex ? 'noindex,nofollow' : 'index,follow' }}">
<link rel="canonical" href="{{ $url }}">
<meta name="google-site-verification" content="izrAA6h2p_0rmT38OaogoIuNTErZzzhGhjSVhsqeDbI" />

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="id_ID">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

{{-- Schema.org Organization --}}
<script type="application/ld+json">
    {!! $schema !!}
</script>