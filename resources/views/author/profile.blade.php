@extends('layouts.app')

@section('title', $seoTitle ?? ($name . ' — Profil Author | DABRAKA'))
@section('description', $seoDescription ?? ('Profil dan publikasi ilmiah dari ' . $name . ' di DABRAKA.'))
@section('main_class', 'pb-16')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="subscription.index" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" :showSearch="false" />
@endsection

@push('head')
<link rel="canonical" href="{{ $seoUrl ?? request()->url() }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="{{ $seoTitle ?? $name }}">
<meta property="og:description" content="{{ $seoDescription ?? '' }}">
<meta property="og:url" content="{{ $seoUrl ?? request()->url() }}">
@if(!empty($seoImage))
<meta property="og:image" content="{{ $seoImage }}">
@endif
<meta property="og:site_name" content="DABRAKA">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $seoTitle ?? $name }}">
<meta name="twitter:description" content="{{ $seoDescription ?? '' }}">
@if(!empty($seoImage))
<meta name="twitter:image" content="{{ $seoImage }}">
@endif

@php
$personSchema = [
'@context' => 'https://schema.org',
'@type' => 'Person',
'name' => $name,
'url' => $seoUrl ?? request()->url(),
];
if (!empty($seoImage)) $personSchema['image'] = $seoImage;
if (!empty($seoAffiliation)) $personSchema['affiliation'] = ['@type' => 'Organization', 'name' => $seoAffiliation];
if (!empty($bio)) $personSchema['description'] = Str::limit(strip_tags($bio), 200);
if (!empty($author?->orcid_url)) $personSchema['sameAs'] = [$author->orcid_url];
if ($formattedPublications->isNotEmpty()) {
$personSchema['author'] = $formattedPublications->take(5)->map(fn($pub) => [
'@type' => 'ScholarlyArticle',
'name' => $pub['title'],
'url' => $pub['detail_url'],
'datePublished' => $pub['formatted_date'] ?? null,
])->filter()->values()->toArray();
}
$breadcrumbSchema = [
'@context' => 'https://schema.org',
'@type' => 'BreadcrumbList',
'itemListElement' => [
['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('beranda')],
['@type' => 'ListItem', 'position' => 2, 'name' => 'Publikasi','item' => route('publikasi.index')],
['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $seoUrl ?? request()->url()],
],
];
@endphp
<script type="application/ld+json">
    {!! json_encode($personSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
    body {
        background: #F8F9FC;
    }

    /* ── HERO ── */
    .author-hero {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 55%, #C73D1F 100%);
        position: relative;
        padding: 2.5rem 0 6rem;
        overflow: hidden;
    }

    .author-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 15% 50%, rgba(255, 255, 255, .12) 0%, transparent 50%),
            radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .10) 0%, transparent 50%),
            radial-gradient(circle at 60% 90%, rgba(255, 255, 255, .08) 0%, transparent 40%);
        animation: heroFloat 18s ease-in-out infinite;
        pointer-events: none;
    }

    .author-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M0 40L40 0' stroke='rgba(255,255,255,0.04)' stroke-width='1'/%3E%3C/svg%3E");
        pointer-events: none;
    }

    @keyframes heroFloat {

        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }

        33% {
            transform: translate(15px, -15px) scale(1.02);
        }

        66% {
            transform: translate(-10px, 10px) scale(.98);
        }
    }

    @media(min-width:640px) {
        .author-hero {
            padding: 3rem 0 6rem;
        }
    }

    @media(min-width:1024px) {
        .author-hero {
            padding: 4rem 0 7rem;
        }
    }

    /* ── AVATAR ── */
    .author-avatar-ring {
        position: relative;
        display: inline-flex;
        padding: 3px;
        border-radius: 50%;
        background: linear-gradient(45deg, #FFD700, #FF6B18, #E64627, #FF6B18, #FFD700);
        background-size: 300% 300%;
        animation: ringRotate 4s ease infinite;
        flex-shrink: 0;
    }

    @keyframes ringRotate {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .author-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        border: 3px solid white;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .25);
        display: block;
        background: white;
        position: relative;
        z-index: 1;
    }

    @media(min-width:640px) {
        .author-avatar {
            width: 110px;
            height: 110px;
            border-width: 4px;
        }
    }

    @media(min-width:1024px) {
        .author-avatar {
            width: 136px;
            height: 136px;
            border-width: 5px;
        }
    }

    .avatar-badge {
        position: absolute;
        bottom: 6px;
        right: 6px;
        z-index: 2;
        width: 18px;
        height: 18px;
        background: #4ADE80;
        border: 3px solid white;
        border-radius: 50%;
        animation: pulseBadge 2.5s ease-in-out infinite;
    }

    @keyframes pulseBadge {

        0%,
        100% {
            box-shadow: 0 0 0 3px rgba(74, 222, 128, .3);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(74, 222, 128, .1);
        }
    }

    @media(min-width:640px) {
        .avatar-badge {
            width: 20px;
            height: 20px;
        }
    }

    @media(min-width:1024px) {
        .avatar-badge {
            width: 22px;
            height: 22px;
            bottom: 9px;
            right: 9px;
        }
    }

    /* ── BADGES DI HERO ── */
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        transition: all .25s ease;
    }

    .hero-badge-ghost {
        background: rgba(255, 255, 255, .15);
        border: 1.5px solid rgba(255, 255, 255, .35);
        color: white;
        backdrop-filter: blur(8px);
    }

    .hero-badge-ghost:hover {
        background: rgba(255, 255, 255, .25);
        border-color: rgba(255, 255, 255, .7);
    }

    .hero-badge-orcid {
        background: #A6CE39;
        color: #1A1A1A;
        font-weight: 800;
    }

    .hero-badge-orcid:hover {
        background: #8fb82d;
    }

    .hero-badge-verified {
        background: rgba(74, 222, 128, .2);
        border: 1.5px solid rgba(74, 222, 128, .5);
        color: #bbf7d0;
    }

    @media(min-width:640px) {
        .hero-badge {
            font-size: 12px;
            padding: 6px 14px;
        }
    }

    /* ── INFO CARD (float di bawah hero) ── */
    .info-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .09);
        border: 2px solid #EEF0F7;
        overflow: hidden;
        transition: box-shadow .3s ease;
    }

    .info-card:hover {
        box-shadow: 0 16px 48px rgba(0, 0, 0, .12);
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 20px;
        border-bottom: 1px solid #F3F4F6;
        transition: background .2s ease;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row:hover {
        background: #FAFBFC;
    }

    .info-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .info-label {
        font-size: 10px;
        font-weight: 700;
        color: #A3A6AE;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 13px;
        font-weight: 600;
        color: #1A1A1A;
        word-break: break-word;
    }

    @media(min-width:640px) {
        .info-row {
            padding: 16px 24px;
            gap: 14px;
        }

        .info-icon {
            width: 38px;
            height: 38px;
        }

        .info-value {
            font-size: 14px;
        }
    }

    /* ── ORCID BADGE (di info card) ── */
    .orcid-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px 4px 6px;
        background: #f0f9d8;
        border: 1.5px solid #A6CE39;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #5a7a10;
        transition: all .25s ease;
        text-decoration: none;
    }

    .orcid-link:hover {
        background: #A6CE39;
        color: #1A1A1A;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(166, 206, 57, .3);
    }

    .orcid-logo {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #A6CE39;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 10px;
        font-weight: 900;
        color: white;
        border: 1.5px solid rgba(0, 0, 0, .1);
    }

    /* ── NOT LINKED BADGE ── */
    .not-linked-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #F8F9FC;
        border: 1.5px dashed #D1D5DB;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        color: #9CA3AF;
    }

    /* ── STATS FLOATING ── */
    .stats-section {
        margin-top: -3.5rem;
        position: relative;
        z-index: 20;
    }

    @media(min-width:640px) {
        .stats-section {
            margin-top: -4rem;
        }
    }

    @media(min-width:1024px) {
        .stats-section {
            margin-top: -5rem;
        }
    }

    /* ── STATS GRID ── */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .stat-card-hero {
        grid-column: 1 / -1;
    }

    @media(min-width:640px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .stat-card-hero {
            grid-column: auto;
        }
    }

    @media(min-width:1024px) {
        .stats-grid {
            gap: 20px;
        }
    }

    .stat-card {
        background: white;
        border-radius: 18px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
        transition: all .3s cubic-bezier(.4, 0, .2, 1);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 0 18px 0 100%;
        opacity: .06;
        transition: opacity .3s ease;
    }

    .stat-card.orange::before {
        background: #FF6B18;
    }

    .stat-card.blue::before {
        background: #3B82F6;
    }

    .stat-card.green::before {
        background: #22C55E;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(0, 0, 0, .1);
    }

    .stat-card.orange:hover {
        border-color: #FF6B18;
        box-shadow: 0 14px 32px rgba(255, 107, 24, .15);
    }

    .stat-card.blue:hover {
        border-color: #3B82F6;
        box-shadow: 0 14px 32px rgba(59, 130, 246, .15);
    }

    .stat-card.green:hover {
        border-color: #22C55E;
        box-shadow: 0 14px 32px rgba(34, 197, 94, .15);
    }

    .stat-card:hover::before {
        opacity: .12;
    }

    .stat-card-hero .stat-inner {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
    }

    .stat-card-small .stat-inner {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 14px 16px;
        gap: 6px;
    }

    @media(min-width:640px) {
        .stat-card-small .stat-inner {
            flex-direction: row;
            align-items: center;
            padding: 20px;
            gap: 14px;
        }

        .stat-card-hero .stat-inner {
            padding: 20px;
        }
    }

    @media(min-width:1024px) {

        .stat-card-hero .stat-inner,
        .stat-card-small .stat-inner {
            padding: 22px 24px;
            gap: 16px;
        }
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform .3s ease;
    }

    .stat-card-small .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
    }

    @media(min-width:640px) {

        .stat-icon,
        .stat-card-small .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
        }
    }

    @media(min-width:1024px) {

        .stat-icon,
        .stat-card-small .stat-icon {
            width: 56px;
            height: 56px;
        }
    }

    .stat-card:hover .stat-icon {
        transform: rotate(6deg) scale(1.08);
    }

    .stat-number {
        font-size: 1.875rem;
        font-weight: 900;
        color: #1A1A1A;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .stat-card-small .stat-number {
        font-size: 1.5rem;
    }

    @media(min-width:640px) {

        .stat-number,
        .stat-card-small .stat-number {
            font-size: 2rem;
        }
    }

    @media(min-width:1024px) {

        .stat-number,
        .stat-card-small .stat-number {
            font-size: 2.5rem;
        }
    }

    .stat-label {
        font-size: .625rem;
        font-weight: 800;
        color: #A3A6AE;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 2px;
    }

    .stat-sublabel {
        font-size: .6875rem;
        color: #737373;
        font-weight: 500;
    }

    @media(min-width:640px) {
        .stat-label {
            font-size: .6875rem;
        }
    }

    @media(max-width:399px) {
        .stat-sublabel {
            display: none;
        }
    }

    /* ── SECTION TITLE ── */
    .section-title {
        font-size: 1.375rem;
        font-weight: 800;
        color: #1A1A1A;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 48px;
        height: 3px;
        background: linear-gradient(90deg, #FF6B18, transparent);
        border-radius: 2px;
    }

    @media(min-width:640px) {
        .section-title {
            font-size: 1.625rem;
        }
    }

    @media(min-width:1024px) {
        .section-title {
            font-size: 1.875rem;
        }
    }

    /* ── VIEW TOGGLE ── */
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #737373;
        cursor: pointer;
        transition: all .18s ease;
        flex-shrink: 0;
    }

    .view-btn:hover {
        color: #FF6B18;
        background: #FFF7F2;
    }

    .view-btn.active {
        background: #FF6B18;
        color: white;
    }

    /* ── PUBLICATION CARD ── */
    .publication-card {
        background: white;
        border-radius: 16px;
        border: 2px solid #EEF0F7;
        overflow: hidden;
        transition: all .3s cubic-bezier(.4, 0, .2, 1);
        display: flex;
        flex-direction: column;
    }

    .publication-card:hover {
        border-color: #FF6B18;
        box-shadow: 0 16px 36px rgba(255, 107, 24, .12);
    }

    .pub-cover {
        position: relative;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
        flex-shrink: 0;
    }

    .pub-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform .4s ease;
        display: block;
    }

    .view-grid .publication-card:hover {
        transform: translateY(-5px);
    }

    .view-grid .pub-cover {
        aspect-ratio: 3/4;
        width: 100%;
    }

    .view-grid .publication-card:hover .pub-cover img {
        transform: scale(1.07);
    }

    .view-list .publication-card {
        flex-direction: row !important;
    }

    .view-list .publication-card:hover {
        transform: translateY(-2px);
    }

    .view-list .pub-cover {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        aspect-ratio: 3/4;
        border-radius: 0;
    }

    @media(min-width:400px) {
        .view-list .pub-cover {
            width: 95px;
            min-width: 95px;
            max-width: 95px;
        }
    }

    @media(min-width:600px) {
        .view-list .pub-cover {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
        }
    }

    @media(min-width:1024px) {
        .view-list .pub-cover {
            width: 150px;
            min-width: 150px;
            max-width: 150px;
        }
    }

    .view-list .stats-overlay {
        display: none !important;
    }

    .view-list .pub-content {
        padding: 10px 12px !important;
        min-width: 0;
        flex: 1 1 0%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    @media(min-width:400px) {
        .view-list .pub-content {
            padding: 12px 14px !important;
        }
    }

    @media(min-width:600px) {
        .view-list .pub-content {
            padding: 14px 16px !important;
        }
    }

    @media(min-width:1024px) {
        .view-list .pub-content {
            padding: 16px 20px !important;
        }
    }

    .view-list .pub-title {
        font-size: .75rem !important;
        line-height: 1.35 !important;
        -webkit-line-clamp: 3 !important;
        display: -webkit-box !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
        margin-bottom: 0 !important;
    }

    @media(min-width:400px) {
        .view-list .pub-title {
            font-size: .8125rem !important;
        }
    }

    @media(min-width:600px) {
        .view-list .pub-title {
            font-size: .9375rem !important;
        }
    }

    @media(min-width:1024px) {
        .view-list .pub-title {
            font-size: 1.0625rem !important;
            -webkit-line-clamp: 4 !important;
        }
    }

    .view-list .pub-abstract {
        display: none;
    }

    @media(min-width:600px) {
        .view-list .pub-abstract {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: .8125rem;
            color: #737373;
            margin-top: 3px;
        }
    }

    .view-list .pub-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-bottom: 4px;
    }

    .view-list .pub-meta .cat-badge {
        font-size: 9px !important;
        padding: 1px 7px !important;
    }

    .view-list .pub-meta .date-text {
        font-size: 9px !important;
        white-space: nowrap;
    }

    @media(min-width:400px) {
        .view-list .pub-meta .cat-badge {
            font-size: 10px !important;
        }

        .view-list .pub-meta .date-text {
            font-size: 10px !important;
        }
    }

    @media(min-width:600px) {
        .view-list .pub-meta .cat-badge {
            font-size: 11px !important;
        }

        .view-list .pub-meta .date-text {
            font-size: 11px !important;
        }
    }

    .view-list .pub-stats {
        margin-top: 5px;
        padding-top: 5px;
        border-top: 1px solid #F0F0F0;
        font-size: 9px !important;
    }

    @media(min-width:400px) {
        .view-list .pub-stats {
            font-size: 10px !important;
        }
    }

    @media(min-width:600px) {
        .view-list .pub-stats {
            font-size: 11px !important;
        }
    }

    @media(max-width:349px) {
        .view-list .pub-arrow {
            display: none !important;
        }
    }

    /* ── COLLABORATOR CARD ── */
    .collaborator-card {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        text-align: center;
        border: 2px solid #EEF0F7;
        transition: all .3s cubic-bezier(.4, 0, .2, 1);
        display: block;
    }

    .collaborator-card:hover {
        transform: translateY(-5px);
        border-color: #FF6B18;
        box-shadow: 0 10px 24px rgba(255, 107, 24, .12);
    }

    .collaborator-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        margin: 0 auto .5rem;
        border: 2.5px solid #EEF0F7;
        transition: all .3s ease;
        background: #F8F9FC;
        display: block;
    }

    .collaborator-card:hover .collaborator-avatar {
        transform: scale(1.1);
        border-color: #FF6B18;
    }

    @media(min-width:640px) {
        .collaborator-avatar {
            width: 68px;
            height: 68px;
        }
    }

    @media(min-width:1024px) {
        .collaborator-avatar {
            width: 80px;
            height: 80px;
        }
    }

    /* ── BUTTONS ── */
    .btn-hero-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .625rem 1.25rem;
        background: white;
        color: #FF6B18;
        font-weight: 700;
        font-size: .8125rem;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
        transition: all .3s ease;
        white-space: nowrap;
    }

    .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
    }

    .btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .625rem 1.25rem;
        background: rgba(255, 255, 255, .15);
        color: white;
        font-weight: 700;
        font-size: .8125rem;
        border-radius: 12px;
        border: 2px solid rgba(255, 255, 255, .4);
        backdrop-filter: blur(10px);
        transition: all .3s ease;
        white-space: nowrap;
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, .25);
        border-color: rgba(255, 255, 255, .8);
        transform: translateY(-2px);
    }

    @media(min-width:640px) {

        .btn-hero-primary,
        .btn-hero-secondary {
            padding: .75rem 1.5rem;
            font-size: .9375rem;
            border-radius: 14px;
        }
    }

    /* ── PAGINATION ── */
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 9px;
        font-weight: 600;
        font-size: .8125rem;
        transition: all .18s ease;
        border: 2px solid #EEF0F7;
        background: white;
        color: #1A1A1A;
    }

    .pagination-btn:hover {
        border-color: #FF6B18;
        color: #FF6B18;
        background: #FFF7F2;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #FF6B18, #E64627);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 24, .35);
    }

    .pagination-btn[aria-disabled="true"] {
        opacity: .35;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ── MISC ── */
    .h-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .h-scroll::-webkit-scrollbar {
        display: none;
    }

    .h-scroll>* {
        flex-shrink: 0;
    }

    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 3rem 1.5rem;
        text-align: center;
        border: 2px dashed #EEF0F7;
    }

    @media(prefers-reduced-motion:reduce) {

        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')

<div id="top-anchor"></div>

{{-- ═══════════════ HERO ═══════════════ --}}
<section class="author-hero">
    <div class="relative z-10 px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">

        {{-- Breadcrumb --}}
        <nav class="h-scroll mb-5 sm:mb-7 text-[11px] sm:text-sm text-white/70 items-center" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="self-center w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('publikasi.index') }}" class="transition-colors hover:text-white">Publikasi</a>
            <svg class="self-center w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-semibold truncate text-white/90">{{ Str::limit($name, 30) }}</span>
        </nav>

        {{-- Profile --}}
        <div
            class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-end sm:text-left sm:gap-6 lg:gap-8">

            {{-- Avatar --}}
            <div class="relative author-avatar-ring">
                <img src="{{ $photoUrl }}" alt="Foto {{ $name }}" class="author-avatar"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=FF6B18&color=fff&size=160&bold=true'">
                <span class="avatar-badge" title="Penulis aktif"></span>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0 pb-1 text-white">

                {{-- Badge baris atas --}}
                <div class="flex flex-wrap items-center justify-center gap-2 mb-3 sm:justify-start">

                    {{-- Penulis Publikasi --}}
                    <span class="hero-badge hero-badge-ghost">
                        <svg class="flex-shrink-0 w-3 h-3 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Penulis Publikasi
                    </span>

                    {{-- ✅ Verified (hanya jika punya akun user) --}}
                    @if($isUserProfile)
                    <span class="hero-badge hero-badge-verified">
                        <svg class="flex-shrink-0 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        Terverifikasi
                    </span>
                    @endif

                    {{-- ✅ ORCID di hero (jika ada) --}}
                    @if(!empty($author?->orcid_id))
                    <a href="{{ $author->orcid_url }}" target="_blank" rel="noopener noreferrer"
                        class="hero-badge hero-badge-orcid">
                        <span style="font-size:10px;font-weight:900;line-height:1;">iD</span>
                        ORCID
                    </a>
                    @endif

                </div>

                <h1 class="mb-2 text-2xl font-black leading-tight sm:text-3xl lg:text-4xl xl:text-5xl sm:mb-3">
                    {{ $name }}
                </h1>

                @if($affiliation)
                <div
                    class="flex items-center justify-center sm:justify-start gap-1.5 mb-3 text-white/85 text-sm sm:text-base">
                    <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z" />
                    </svg>
                    <span class="font-semibold">{{ $affiliation }}</span>
                </div>
                @endif

                @if($bio)
                <p
                    class="max-w-2xl mx-auto mb-4 text-sm leading-relaxed sm:text-base text-white/80 sm:mb-5 line-clamp-3 lg:line-clamp-4 sm:mx-0">
                    {{ $bio }}
                </p>
                @endif

                <div class="flex flex-col items-center justify-center gap-2.5 sm:flex-row sm:justify-start sm:gap-3">
                    @if($email)
                    <a href="mailto:{{ $email }}" class="w-full sm:w-auto btn-hero-secondary">
                        <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Hubungi
                    </a>
                    @endif
                    <a href="#publications" class="w-full sm:w-auto btn-hero-primary">
                        <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Lihat Publikasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ STATS ═══════════════ --}}
<div class="stats-section px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
    <div class="stats-grid">

        {{-- Publikasi --}}
        <div class="stat-card orange stat-card-hero">
            <div class="stat-inner">
                <div class="stat-icon bg-gradient-to-br from-[#FF6B18] to-[#E64627]">
                    <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Publikasi</p>
                    <p class="stat-number">{{ number_format($totalPublications) }}</p>
                    <p class="stat-sublabel">Total karya dipublikasikan</p>
                </div>
                <div class="flex-col items-end flex-shrink-0 hidden gap-1 sm:flex opacity-30">
                    <div class="w-2 h-2 rounded-full bg-[#FF6B18]"></div>
                    <div class="w-2 h-8 rounded-full bg-[#FF6B18]"></div>
                    <div class="w-2 h-4 rounded-full bg-[#FF6B18]"></div>
                    <div class="w-2 h-6 rounded-full bg-[#FF6B18]"></div>
                </div>
            </div>
        </div>

        {{-- Views --}}
        <div class="stat-card blue stat-card-small">
            <div class="stat-inner">
                <div class="stat-icon bg-gradient-to-br from-blue-400 to-blue-600">
                    <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Dilihat</p>
                    <p class="stat-number">{{ number_format($totalViews) }}</p>
                    <p class="stat-sublabel">Total views</p>
                </div>
            </div>
        </div>

        {{-- Downloads --}}
        <div class="stat-card green stat-card-small">
            <div class="stat-inner">
                <div class="stat-icon bg-gradient-to-br from-green-400 to-green-600">
                    <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Unduhan</p>
                    <p class="stat-number">{{ number_format($totalDownloads) }}</p>
                    <p class="stat-sublabel">Total unduh</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════ INFO CARD ═══════════════ --}}
{{-- Tampil jika ada minimal satu info yang relevan --}}
@php
$hasOrcid = !empty($author?->orcid_id);
$hasAffiliation = !empty($affiliation);
$hasEmail = !empty($email);
$hasBio = !empty($bio);
$isExternal = $author && !$isUserProfile; // author tanpa akun
$showInfoCard = $hasOrcid || $hasAffiliation || $hasEmail || $isExternal;
@endphp

@if($showInfoCard)
<div class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">
    <div class="info-card">

        {{-- Header info card --}}
        <div
            class="flex items-center gap-3 px-5 py-4 border-b-2 border-[#EEF0F7] bg-gradient-to-r from-[#FAFBFC] to-white">
            <div
                class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1A1A1A]">Informasi Penulis</p>
                <p class="text-xs text-[#737373]">Detail profil dan identitas akademik</p>
            </div>
            {{-- Status akun --}}
            @if($isUserProfile)
            <span
                class="ml-auto flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-xs font-bold text-green-700">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                Akun Terverifikasi
            </span>
            @else
            <span
                class="ml-auto flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full text-xs font-bold text-amber-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Penulis Eksternal
            </span>
            @endif
        </div>

        <div class="divide-y divide-[#F3F4F6]">

            {{-- Nama --}}
            <div class="info-row">
                <div class="info-icon bg-[#FFF7F2]">
                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="info-label">Nama Lengkap</p>
                    <p class="info-value">{{ $name }}</p>
                </div>
            </div>

            {{-- Afiliasi --}}
            @if($hasAffiliation)
            <div class="info-row">
                <div class="info-icon bg-[#EFF6FF]">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="info-label">Institusi / Afiliasi</p>
                    <p class="info-value">{{ $affiliation }}</p>
                </div>
            </div>
            @endif

            {{-- Email --}}
            @if($hasEmail)
            <div class="info-row">
                <div class="info-icon bg-[#F0FDF4]">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="info-label">Email</p>
                    <a href="mailto:{{ $email }}" class="info-value text-[#FF6B18] hover:underline">{{ $email }}</a>
                </div>
            </div>
            @endif

            {{-- ORCID ── tampil selalu (ada atau tidak) --}}
            <div class="info-row">
                <div class="info-icon" style="background:#f0f9d8;">
                    {{-- ORCID logo sederhana --}}
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#A6CE39">
                        <path
                            d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zM7.369 4.378c.525 0 .947.431.947.947s-.422.947-.947.947a.95.95 0 01-.947-.947c0-.516.422-.947.947-.947zm-.722 3.038h1.444v10.041H6.647V7.416zm3.562 0h3.9c3.712 0 5.344 2.653 5.344 5.025 0 2.578-2.016 5.016-5.325 5.016h-3.919V7.416zm1.444 1.303v7.435h2.297c3.272 0 3.922-2.484 3.922-3.722 0-2.016-1.328-3.713-3.884-3.713h-2.335z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="info-label">ORCID iD</p>
                    @if($hasOrcid)
                    <a href="{{ $author->orcid_url }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex mt-1 orcid-link">
                        <span class="orcid-logo">iD</span>
                        {{ $author->orcid_id }}
                        <svg class="flex-shrink-0 w-3 h-3 opacity-60" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                    @else
                    <span class="mt-1 not-linked-badge">
                        <svg class="flex-shrink-0 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Belum terdaftar
                    </span>
                    @endif
                </div>
            </div>

            {{-- Status akun (hanya untuk external author) --}}
            @if($isExternal)
            <div class="info-row">
                <div class="info-icon bg-amber-50">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="info-label">Status Akun</p>
                    <p class="text-sm info-value text-amber-600">Penulis eksternal — belum memiliki akun DABRAKA</p>
                    <p class="text-xs text-[#737373] mt-1">
                        Apakah Anda penulis ini?
                        <a href="{{ route('register') }}" class="text-[#FF6B18] font-semibold hover:underline">Daftar &
                            klaim profil</a>
                    </p>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif

{{-- ═══════════════ PUBLICATIONS ═══════════════ --}}
<div id="publications" class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10 lg:mt-12">

    <div class="flex flex-col gap-3 mb-4 sm:mb-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="section-title">Karya Publikasi</h2>
            <p class="mt-2.5 text-xs sm:text-sm text-[#737373]">
                Karya ilmiah yang dipublikasikan oleh
                <span class="font-semibold text-[#1A1A1A]">{{ $name }}</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1.5 px-3 py-2 bg-white border-2 border-[#EEF0F7] rounded-xl">
                <svg class="w-4 h-4 text-[#FF6B18] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd"
                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-xs sm:text-sm font-bold text-[#1A1A1A]">{{ $publications->total() }}</span>
                <span class="text-xs text-[#737373] hidden sm:inline">Karya</span>
            </div>
            <div class="flex items-center gap-0.5 border-2 border-[#EEF0F7] rounded-xl p-0.5 bg-white">
                <button type="button" id="btn-grid2" onclick="setPubView('grid2')" class="view-btn" title="2 Kolom">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <rect x="2" y="2" width="7" height="7" rx="1.5" />
                        <rect x="11" y="2" width="7" height="7" rx="1.5" />
                        <rect x="2" y="11" width="7" height="7" rx="1.5" />
                        <rect x="11" y="11" width="7" height="7" rx="1.5" />
                    </svg>
                </button>
                <button type="button" id="btn-grid3" onclick="setPubView('grid3')" class="view-btn" title="3 Kolom">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <rect x="1" y="2" width="5" height="7" rx="1" />
                        <rect x="7.5" y="2" width="5" height="7" rx="1" />
                        <rect x="14" y="2" width="5" height="7" rx="1" />
                        <rect x="1" y="11" width="5" height="7" rx="1" />
                        <rect x="7.5" y="11" width="5" height="7" rx="1" />
                        <rect x="14" y="11" width="5" height="7" rx="1" />
                    </svg>
                </button>
                <button type="button" id="btn-list" onclick="setPubView('list')" class="view-btn" title="Mode Baris">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if($formattedPublications->count() > 0)
    <div id="pubContainer" class="view-grid">
        <div id="pubGrid" class="grid grid-cols-2 gap-3 sm:gap-4 lg:gap-5">
            @foreach($formattedPublications as $publication)
            @php
            $words = array_filter(explode(' ', $publication['title']));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $word) { $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1)); }
            if (empty($initials)) $initials = mb_strtoupper(mb_substr($publication['title'], 0, 2));
            $firstAuthor = $publication['authors'][0]['name'] ?? 'Anonymous';
            $placeholderUrl = route('placeholder.cover') . '?' . http_build_query([
            'initials' => $initials,
            'type' => $publication['publication_type'] ?? 'Publikasi',
            'title' => $publication['title'],
            'category' => $publication['category'] ?? 'Umum',
            'author' => $firstAuthor,
            'v' => time(),
            ]);
            $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);
            $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
            @endphp
            <a href="{{ $publication['detail_url'] }}" class="publication-card group">
                <div class="pub-cover" style="display:block;background-color:#F8F9FC;">
                    <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy"
                        decoding="async"
                        style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;opacity:1!important;visibility:visible!important;"
                        onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
                    <div
                        class="absolute bottom-0 left-0 right-0 z-20 p-3 transition-opacity duration-300 opacity-0 stats-overlay bg-gradient-to-t from-black/75 to-transparent group-hover:opacity-100">
                        <div class="flex items-center gap-3 text-xs text-white">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ number_format($publication['views_count'] ?? 0) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                {{ number_format($publication['download_count'] ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col flex-1 p-2 pub-content sm:p-4">
                    <div class="pub-meta flex items-start gap-1 mb-1.5 flex-wrap">
                        <span
                            class="cat-badge px-2 py-0.5 bg-[#FFF7F2] text-[#FF6B18] text-[10px] sm:text-xs font-bold rounded-full truncate max-w-[60%] leading-[1.6]">{{
                            $publication['category'] ?? 'Umum' }}</span>
                        <span class="date-text text-[10px] sm:text-xs text-[#A3A6AE] whitespace-nowrap leading-[1.6]">{{
                            $publication['formatted_date'] }}</span>
                    </div>
                    <h3 class="pub-title font-bold text-[12px] sm:text-sm lg:text-base text-[#1A1A1A] line-clamp-3 group-hover:text-[#FF6B18] transition-colors leading-snug flex-1 mb-2"
                        style="overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;word-break:break-word;">
                        {{ $publication['title'] }}
                    </h3>
                    <p class="pub-abstract text-[11px] sm:text-xs text-[#737373] line-clamp-2 mb-3">{{
                        $publication['abstract'] ?? 'Tidak ada abstrak' }}</p>
                    <div
                        class="pub-stats flex items-center gap-3 text-[10px] sm:text-xs text-[#A3A6AE] mt-auto pt-2.5 border-t border-[#F0F0F0]">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ number_format($publication['views_count'] ?? 0) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ number_format($publication['download_count'] ?? 0) }}
                        </span>
                        <span
                            class="pub-arrow flex-shrink-0 ml-auto w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#FFF7F2] text-[#FF6B18] flex items-center justify-center group-hover:bg-[#FF6B18] group-hover:text-white transition-all">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    @if($publications->hasPages())
    @php
    $currentPage = $publications->currentPage();
    $lastPage = $publications->lastPage();
    $start = max(1, $currentPage - 2);
    $end = min($lastPage, $currentPage + 2);
    @endphp
    <div class="flex flex-col items-center gap-3 mt-8 pt-6 border-t-2 border-[#EEF0F7]">
        <p class="text-[11px] sm:text-sm text-[#737373] text-center">
            Halaman <span class="font-bold text-[#1A1A1A]">{{ $currentPage }}</span> dari
            <span class="font-bold text-[#1A1A1A]">{{ $lastPage }}</span>
            &nbsp;·&nbsp;
            <span class="font-bold text-[#FF6B18]">{{ number_format($publications->total()) }}</span> karya
        </p>
        <div class="flex flex-wrap items-center justify-center gap-1">
            @if($publications->onFirstPage())
            <span class="pagination-btn" aria-disabled="true"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg></span>
            @else
            <a href="{{ $publications->previousPageUrl() }}#publications" class="pagination-btn"><svg
                    class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg></a>
            @endif
            @if($start > 1)<a href="{{ $publications->url(1) }}#publications" class="pagination-btn">1</a>@if($start >
            2)<span class="text-[#A3A6AE] text-xs font-bold px-0.5">…</span>@endif@endif
            @for($p = $start; $p <= $end; $p++)<a href="{{ $publications->url($p) }}#publications"
                class="pagination-btn {{ $p == $currentPage ? 'active' : '' }}">{{ $p }}</a>@endfor
                @if($end < $lastPage)@if($end < $lastPage - 1)<span class="text-[#A3A6AE] text-xs font-bold px-0.5">
                    …</span>@endif<a href="{{ $publications->url($lastPage) }}#publications" class="pagination-btn">{{
                        $lastPage }}</a>@endif
                    @if($publications->hasMorePages())
                    <a href="{{ $publications->nextPageUrl() }}#publications" class="pagination-btn"><svg
                            class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg></a>
                    @else
                    <span class="pagination-btn" aria-disabled="true"><svg class="w-3.5 h-3.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg></span>
                    @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 bg-[#FFF7F2] rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-[#FF6B18]/40" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h3 class="text-base sm:text-xl font-bold text-[#1A1A1A] mb-2">Belum Ada Publikasi</h3>
        <p class="text-xs sm:text-sm text-[#737373]">Penulis ini belum mempublikasikan karya apapun.</p>
    </div>
    @endif

</div>

{{-- ═══════════════ COLLABORATORS ═══════════════ --}}
@if($coAuthors->count() > 0)
<div class="mt-10 sm:mt-14 py-10 sm:py-12 lg:py-16 bg-white border-t-2 border-[#EEF0F7]">
    <div class="px-3 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="mb-5 sm:mb-7">
            <h2 class="section-title">Kolaborator</h2>
            <p class="mt-2.5 text-xs sm:text-sm text-[#737373]">
                Penulis lain yang pernah berkolaborasi dengan
                <span class="font-semibold text-[#1A1A1A]">{{ $name }}</span>
            </p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 sm:gap-4 lg:gap-5">
            @foreach($coAuthors as $coAuthor)
            <a href="{{ $coAuthor['profile_url'] }}" class="collaborator-card group">
                <div class="relative mx-auto mb-2 w-fit">
                    <img src="{{ $coAuthor['photo_url'] }}" alt="{{ $coAuthor['name'] }}" class="collaborator-avatar"
                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($coAuthor['name']) }}&background=FF6B18&color=fff&size=128&bold=true'">
                </div>
                <p
                    class="text-[11px] sm:text-xs font-bold text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18] transition-colors leading-tight mb-1">
                    {{ $coAuthor['name'] }}</p>
                <p class="text-[10px] sm:text-[11px] text-[#A3A6AE] font-medium">
                    <span class="text-[#FF6B18] font-bold">{{ $coAuthor['publications_count'] }}</span> karya
                </p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<x-scroll-to-top />

@endsection

@push('scripts')
<x-scroll-to-top-script />
<script>
    (function () {
    const STORAGE_KEY = 'pub_author_view';
    const grid        = document.getElementById('pubGrid');
    const container   = document.getElementById('pubContainer');
    if (!grid || !container) return;

    const isMobile = () => window.innerWidth < 600;

    const views = {
        grid2: { containerClass: 'view-grid', getGridClass: () => 'grid grid-cols-2 gap-3 sm:gap-4 lg:gap-5' },
        grid3: { containerClass: 'view-grid', getGridClass: () => isMobile() ? 'grid grid-cols-2 gap-3' : 'grid grid-cols-3 gap-4 lg:gap-5' },
        list:  { containerClass: 'view-list', getGridClass: () => 'flex flex-col gap-2.5' },
    };

    function applyView(mode) {
        if (!views[mode]) mode = 'grid2';
        const v = views[mode];
        container.className = v.containerClass;
        grid.className      = v.getGridClass();
        ['grid2', 'grid3', 'list'].forEach(k => {
            const btn = document.getElementById('btn-' + k);
            if (btn) btn.classList.toggle('active', k === mode);
        });
        localStorage.setItem(STORAGE_KEY, mode);
    }

    window.setPubView = applyView;

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            const cur = localStorage.getItem(STORAGE_KEY);
            if (cur && views[cur]) applyView(cur);
        }, 150);
    });

    const saved = localStorage.getItem(STORAGE_KEY);
    applyView(saved && views[saved] ? saved : (isMobile() ? 'grid2' : 'grid3'));
})();
</script>
@endpush