@extends('layouts.app')

@section('title', $publication->title)
@section('main_class', 'mt-0 pb-16')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="subscription.index" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" :showSearch="false" />
@endsection

@push('styles')
<style>
    #publication-detail-grid {
        display: block !important;
        width: 100% !important;
    }

    #publication-detail-grid.has-file {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
        width: 100% !important;
    }

    @media (min-width: 1024px) {
        #publication-detail-grid.has-file {
            grid-template-columns: 1fr 380px !important;
        }
    }

    #publication-detail-grid:not(.has-file)>* {
        width: 100% !important;
        max-width: 100% !important;
        margin-bottom: 1.5rem;
    }

    #publication-detail-grid:not(.has-file)>*:last-child {
        margin-bottom: 0;
    }

    #publication-sidebar {
        position: relative;
    }

    @media (min-width: 1024px) {
        #publication-sidebar .sticky-cover {
            position: sticky !important;
            top: 1.5rem !important;
        }
    }

    #publication-detail-grid>* {
        min-width: 0;
    }

    .cover-image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
    }

    .cover-image-wrapper::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 107, 24, 0) 0%, rgba(255, 107, 24, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 1;
        pointer-events: none;
    }

    .cover-image-wrapper:hover::before {
        opacity: 1;
    }

    .cover-image-wrapper img {
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .cover-image-wrapper:hover img {
        transform: scale(1.05);
    }

    .btn-ripple {
        position: relative;
        overflow: hidden;
    }

    .btn-ripple::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
        transform: scale(0);
        transition: transform 0.5s ease;
    }

    .btn-ripple:hover::after {
        transform: scale(2);
    }

    @keyframes download-bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(4px);
        }
    }

    .download-icon:hover {
        animation: download-bounce 0.6s ease infinite;
    }

    .action-buttons-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .version-badge {
        margin-top: 0.75rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #FAFBFC 0%, #FFFFFF 100%);
        border: 2px solid #EEF0F7;
        border-radius: 0.875rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .version-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 107, 24, 0.1), transparent);
        transition: left 0.5s ease;
        pointer-events: none;
    }

    .version-badge:hover::before {
        left: 100%;
    }

    .version-badge:hover {
        border-color: #FF6B18;
        box-shadow: 0 8px 16px rgba(255, 107, 24, 0.08);
        transform: translateY(-2px);
    }

    .version-badge .grid>div {
        background: linear-gradient(135deg, #FAFBFC 0%, #F3F4F6 100%);
        transition: all 0.3s ease;
    }

    .version-badge .grid>div:hover {
        background: linear-gradient(135deg, #FFF7F2 0%, #FEFCFB 100%);
        border-color: #FF6B18;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.1);
    }

    .version-badge svg {
        flex-shrink: 0;
    }

    @keyframes pulse-glow {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .version-badge .animate-pulse {
        animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* ── Prose full width saat tidak ada file (no-file mode) ── */
    #publication-detail-grid:not(.has-file) .prose,
    #publication-detail-grid:not(.has-file) .prose-sm,
    #publication-detail-grid:not(.has-file) .prose-base {
        max-width: 100% !important;
        width: 100% !important;
    }

    /* ── Authors Modal ── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9998;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.show {
        opacity: 1;
    }

    .modal-container {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        transform: scale(0.95);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-container.show {
        opacity: 1;
        transform: scale(1);
    }

    .modal-content {
        background: white;
        border-radius: 1.5rem;
        max-width: 800px;
        width: 100%;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        padding: 1.5rem 2rem;
        border-bottom: 2px solid #EEF0F7;
        background: linear-gradient(135deg, #FAFBFC 0%, #FFFFFF 100%);
    }

    .modal-body {
        padding: 2rem;
        overflow-y: auto;
        max-height: calc(85vh - 120px);
    }

    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #F8F9FC;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #FF6B18;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #E64627;
    }

    .author-card-modal {
        padding: 1.25rem;
        background: linear-gradient(135deg, #FAFBFC 0%, #FFFFFF 100%);
        border: 2px solid #EEF0F7;
        border-radius: 1rem;
        transition: all 0.3s ease;
    }

    .author-card-modal:hover {
        border-color: #FF6B18;
        box-shadow: 0 8px 16px rgba(255, 107, 24, 0.1);
        transform: translateY(-2px);
    }

    /* ── Login Required Modal ── */
    #loginRequiredModal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: none;
    }

    #loginModalBackdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(6px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #loginModalBackdrop.show {
        opacity: 1;
    }

    #loginModalContainer {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        transform: scale(0.92) translateY(12px);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #loginModalContainer.show {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(110%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(110%);
            opacity: 0;
        }
    }

    @keyframes actionPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 107, 24, 0.5);
        }

        70% {
            box-shadow: 0 0 0 12px rgba(255, 107, 24, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 107, 24, 0);
        }
    }

    .action-pulse {
        animation: actionPulse 0.8s ease-out;
    }
</style>
@endpush

@section('content')

{{-- ✅ SEO Article Schema --}}
<x-seo-article :publication="$publication" :authors="is_array($authors) ? $authors : $authors->toArray()"
    :coverUrl="$cover_url" :keywords="$keywords" />

{{-- ✅ Breadcrumb Schema untuk Google --}}
@php
$breadcrumbSchema = json_encode([
'@context' => 'https://schema.org',
'@type' => 'BreadcrumbList',
'itemListElement' => [
[
'@type' => 'ListItem',
'position' => 1,
'name' => 'Beranda',
'item' => route('beranda'),
],
[
'@type' => 'ListItem',
'position' => 2,
'name' => 'Publikasi',
'item' => route('publikasi.index'),
],
[
'@type' => 'ListItem',
'position' => 3,
'name' => $publication->title,
'item' => route('publikasi.show', $publication->slug),
],
],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">
    {!! $breadcrumbSchema !!}
</script>

{{-- ✅ Meta keywords tambahan untuk search engine --}}
@if($keywords && count($keywords) > 0)
<meta name="keywords" content="{{ implode(', ', $keywords) }}, DABRAKA, publikasi ilmiah, kepolisian Indonesia">
@endif

{{-- ✅ Meta author --}}
@if(is_array($authors) ? count($authors) > 0 : $authors->count() > 0)
<meta name="author"
    content="{{ is_array($authors) ? ($authors[0]['name'] ?? '') : ($authors->first()['name'] ?? '') }}">
@endif

@php
$latestVersion = $publication->versions->first();
$hasFile = $latestVersion && !empty($latestVersion->pdf_file_path);
@endphp

{{-- Breadcrumb --}}
<nav class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 text-sm">
        <li><a href="{{ route('home') }}" class="text-[#737373] hover:text-[#FF6B18] transition-colors">Beranda</a></li>
        <li><span class="text-[#737373]">/</span></li>
        <li><a href="{{ route('publikasi.index') }}"
                class="text-[#737373] hover:text-[#FF6B18] transition-colors">Publikasi</a></li>
        <li><span class="text-[#737373]">/</span></li>
        <li><span class="text-[#1A1A1A] font-semibold">Detail</span></li>
    </ol>
</nav>

{{-- Main Content --}}
<article class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">

    {{-- Header Section --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">

        {{-- Category & Actions --}}
        <div class="flex items-center justify-between mb-4">
            <span class="px-4 py-1.5 bg-[#FFF7F2] text-sm font-bold text-[#FF6B18] rounded-full">
                {{ $category }}
            </span>
            <div class="flex items-center gap-2">

                {{-- Favorite Button --}}
                <button type="button" id="btnFavorite" onclick="toggleFavorite(event)"
                    class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] hover:scale-110 transition-all duration-300 group"
                    title="Tambah ke favorit">
                    <svg id="iconFavorite" class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </button>

                {{-- Save Button --}}
                <button type="button" id="btnSave" onclick="saveForLater(event)"
                    class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] hover:scale-110 transition-all duration-300 group"
                    title="Simpan untuk nanti">
                    <svg id="iconSave" class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </button>

                {{-- Share Button --}}
                <button type="button" onclick="sharePublication()"
                    class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] hover:scale-110 transition-all duration-300 group"
                    title="Bagikan">
                    <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </button>

            </div>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-[#1A1A1A] mb-6 leading-tight">
            {{ $publication->title }}
        </h1>

        {{-- Authors --}}
        @if($authors->count() > 0)
        <div class="mb-6">
            <p class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Authors</p>
            <div class="flex flex-wrap gap-3">
                @foreach($authors->take(3) as $author)
                <a href="{{ route('author.profile', $author['slug']) }}"
                    class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] hover:shadow-md hover:scale-105 transition-all duration-300 cursor-pointer group">
                    @if($author['photo'])
                    <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                        class="w-10 h-10 rounded-full object-cover ring-2 ring-white group-hover:ring-[#FF6B18] transition-all">
                    @else
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white group-hover:scale-110 transition-all">
                        {{ $author['initials'] }}
                    </div>
                    @endif
                    <div class="text-left">
                        <p class="text-sm font-bold text-[#1A1A1A] group-hover:text-[#FF6B18] transition-colors">
                            {{ $author['name'] }}
                            @if($author['is_corresponding'])
                            <span class="text-[#FF6B18]" title="Corresponding Author">*</span>
                            @endif
                        </p>
                        <p class="text-xs text-[#737373] line-clamp-1">{{ $author['affiliation'] }}</p>
                    </div>
                </a>
                @endforeach

                @if($authors->count() > 3)
                <button type="button" onclick="showAllAuthors()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] hover:shadow-md hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-sm font-semibold text-[#737373]">+{{ $authors->count() - 3 }} more authors</span>
                </button>
                @endif
            </div>
        </div>
        @endif

        {{-- Meta Info --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-[#EEF0F7]">
            <div class="hover:bg-[#F8F9FC] p-3 rounded-xl transition-colors duration-300">
                <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Published
                </p>
                <p class="text-sm font-bold text-[#1A1A1A]">{{ $formatted_date }}</p>
            </div>
            <div class="hover:bg-[#F8F9FC] p-3 rounded-xl transition-colors duration-300">
                <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Type
                </p>
                <p class="text-sm font-bold text-[#1A1A1A]">{{ $publication->publicationType->name }}</p>
            </div>
            <div class="hover:bg-[#F8F9FC] p-3 rounded-xl transition-colors duration-300">
                <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Views
                </p>
                <p class="text-sm font-bold text-[#1A1A1A]">{{ number_format($viewsCount ?? 0) }}</p>
            </div>
            <div class="hover:bg-[#F8F9FC] p-3 rounded-xl transition-colors duration-300">
                <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Downloads
                </p>
                <p class="text-sm font-bold text-[#1A1A1A]">{{ number_format($downloadCount ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
    SECTION: Riwayat Publikasi Sebelumnya
    Hanya tampil jika is_previously_published = true/1
    Taruh di dalam <article> setelah blok meta info (setelah penutup
        div "Header Section"), sebelum @if($hasFile) ... grid layout.
        ══════════════════════════════════════════════════════════════════ --}}

        @php
        $isPrevPublished = (bool) $publication->is_previously_published;
        $pubTypeSlug = $publication->publicationType?->slug ?? '';

        // Label identifier dinamis sesuai tipe karya
        $identifierLabel = match($pubTypeSlug) {
        'jurnal' => 'DOI',
        'buku' => 'ISBN',
        'opini' => 'Media / Portal',
        default => 'Identifier',
        };

        // Icon identifier
        $identifierIcon = match($pubTypeSlug) {
        'jurnal' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
        ',
        'buku' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        ',
        'opini' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
        ',
        default => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        ',
        };

        // URL identifier (khusus jurnal → buat link DOI)
        $identifierUrl = null;
        if ($pubTypeSlug === 'jurnal' && filled($publication->prior_identifier_value)) {
        $identifierUrl = 'https://doi.org/' . ltrim($publication->prior_identifier_value, '/');
        } elseif ($pubTypeSlug !== 'jurnal' && filled($publication->prior_publisher_url)) {
        $identifierUrl = $publication->prior_publisher_url;
        }

        // Format tanggal prior_published_date
        $priorDate = null;
        if ($publication->prior_published_date) {
        $priorDate = \Carbon\Carbon::parse($publication->prior_published_date)
        ->locale('id')
        ->isoFormat('D MMMM YYYY');
        }
        @endphp

        @if($isPrevPublished)
        <div
            class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6 hover:shadow-lg transition-shadow duration-300">

            {{-- Header --}}
            <h2 class="text-xl font-bold text-[#1A1A1A] mb-1 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Publikasi
            </h2>
            <p class="text-sm text-[#737373] mb-6">
                Karya ini telah dipublikasikan sebelumnya dan berstatus
                <span class="font-semibold text-green-700">Open Access</span> di sumber aslinya.
            </p>

            {{-- Grid info cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- Platform / Penerbit --}}
                @if(filled($publication->prior_publisher_name))
                <div
                    class="flex items-start gap-3 p-4 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300">
                    <div class="w-9 h-9 rounded-lg bg-[#FFF7F2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-1">Platform / Penerbit</p>
                        <p class="text-sm font-bold text-[#1A1A1A] break-words">{{ $publication->prior_publisher_name }}
                        </p>
                    </div>
                </div>
                @endif

                {{-- Identifier: DOI / ISBN / Nama Media --}}
                @if(filled($publication->prior_identifier_value))
                <div
                    class="flex items-start gap-3 p-4 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300">
                    <div class="w-9 h-9 rounded-lg bg-[#FFF7F2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $identifierIcon !!}
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-1">{{ $identifierLabel }}
                        </p>
                        @if($identifierUrl)
                        <a href="{{ $identifierUrl }}" target="_blank" rel="noopener noreferrer"
                            class="text-sm font-bold text-[#FF6B18] hover:text-[#E64627] hover:underline break-all flex items-center gap-1 group">
                            <span>{{ $publication->prior_identifier_value }}</span>
                            <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-60 group-hover:opacity-100" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        @else
                        <p class="text-sm font-bold text-[#1A1A1A] break-words">{{ $publication->prior_identifier_value
                            }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Tanggal Pertama Diterbitkan --}}
                @if($priorDate)
                <div
                    class="flex items-start gap-3 p-4 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300">
                    <div class="w-9 h-9 rounded-lg bg-[#FFF7F2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-1">Pertama Diterbitkan</p>
                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $priorDate }}</p>
                    </div>
                </div>
                @endif

                {{-- Lisensi Open Access --}}
                @if(filled($publication->origin_license))
                <div
                    class="flex items-start gap-3 p-4 bg-[#F8F9FC] rounded-xl border border-[#EEF0F7] hover:border-[#FF6B18] hover:bg-[#FFF7F2] transition-all duration-300">
                    <div class="w-9 h-9 rounded-lg bg-[#FFF7F2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[#737373] uppercase tracking-wide mb-1">Lisensi Open Access</p>
                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $publication->origin_license }}</p>
                        @php
                        $ccSlug = match($publication->origin_license) {
                        'CC BY 4.0' => 'by/4.0',
                        'CC BY-SA 4.0' => 'by-sa/4.0',
                        'CC BY-NC 4.0' => 'by-nc/4.0',
                        'CC BY-NC-SA 4.0' => 'by-nc-sa/4.0',
                        'CC BY-ND 4.0' => 'by-nd/4.0',
                        'CC BY-NC-ND 4.0' => 'by-nc-nd/4.0',
                        'CC0 1.0' => 'zero/1.0',
                        default => null,
                        };
                        @endphp
                        @if($ccSlug)
                        <a href="https://creativecommons.org/licenses/{{ $ccSlug }}/" target="_blank"
                            rel="noopener noreferrer"
                            class="text-xs text-[#FF6B18] hover:underline mt-0.5 inline-block">
                            Lihat detail lisensi →
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- URL sumber asli — tampil full width di bawah grid --}}
            @if(filled($publication->prior_publisher_url))
            <div class="mt-4 flex items-center gap-3 p-4 bg-[#F0FDF4] rounded-xl border border-[#DCFCE7]">
                <svg class="flex-shrink-0 w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-green-700 uppercase tracking-wide mb-0.5">Sumber Asli (Open Access)
                    </p>
                    <a href="{{ $publication->prior_publisher_url }}" target="_blank" rel="noopener noreferrer"
                        class="text-sm font-medium text-green-700 break-all hover:text-green-900 hover:underline">
                        {{ $publication->prior_publisher_url }}
                    </a>
                </div>
                <a href="{{ $publication->prior_publisher_url }}" target="_blank" rel="noopener noreferrer"
                    class="flex-shrink-0 px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition-colors flex items-center gap-1">
                    Buka
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
            @endif

        </div>
        @endif

        @php
        $pubTypeSlug = $publication->publicationType?->slug ?? '';
        $abstractLabel = match($pubTypeSlug) {
        'buku' => 'Sinopsis',
        'opini' => 'Isi Opini',
        default => 'Abstract',
        };
        $keywordLabel = match($pubTypeSlug) {
        'buku' => 'Tags',
        'opini' => 'Topik',
        default => 'Keywords',
        };

        $words = array_filter(explode(' ', $publication->title));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication->title, 0, 2));
        }
        $firstAuthor = $authors->count() > 0 ? ($authors->first()['name'] ?? 'Unknown') : 'Anonymous';
        $publicationType = $publication->publicationType->name ?? 'Publikasi';
        $placeholderUrl = route('placeholder.cover', [
        'initials' => $initials,
        'type' => $publicationType,
        'title' => $publication->title,
        'category' => $category,
        'author' => $firstAuthor,
        'v' => time(),
        ]);
        @endphp

        @if($hasFile)
        {{-- ══════════════════════════════════════════════════════
        ADA FILE: Layout 2 kolom (konten kiri, sidebar kanan)
        ══════════════════════════════════════════════════════ --}}
        <div id="publication-detail-grid" class="has-file">

            {{-- LEFT: Abstract + Keywords --}}
            <div class="space-y-6">

                @if($publication->abstract)
                <div
                    class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300">
                    <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ $abstractLabel }}
                    </h2>
                    <div class="prose prose-sm md:prose-base max-w-none text-[#1A1A1A] leading-relaxed text-justify">
                        {!! $publication->abstract !!}
                    </div>
                </div>
                @endif

                @if($keywords && count($keywords) > 0)
                <div
                    class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300">
                    <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $keywordLabel }}
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($keywords as $keyword)
                        <span
                            class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] hover:shadow-md hover:scale-105 transition-all duration-300 cursor-pointer">
                            {{ $keyword }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- RIGHT: Cover + Action Buttons + Version Badge --}}
            <aside id="publication-sidebar">
                <div class="sticky-cover">

                    {{-- Cover Image --}}
                    <div class="mb-6 cover-image-wrapper aspect-[2/3] relative overflow-hidden rounded-xl">
                        @if($cover_url)
                        <img src="{{ $cover_url }}" alt="Cover {{ $publication->title }}"
                            class="object-cover w-full h-full"
                            onerror="this.onerror=null; this.src='{{ $placeholderUrl }}';">
                        <div
                            class="absolute inset-0 z-20 flex items-end justify-center p-6 transition-opacity duration-500 opacity-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent hover:opacity-100 rounded-xl">
                            <button onclick="viewCoverFullscreen()"
                                class="px-6 py-3 bg-white/95 backdrop-blur-sm text-[#1A1A1A] text-sm font-bold rounded-lg hover:bg-white transition-all duration-300 transform hover:scale-105 shadow-xl flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                                View Full Size
                            </button>
                        </div>
                        @else
                        <img src="{{ $placeholderUrl }}" alt="Cover {{ $publication->title }}"
                            class="object-cover w-full h-full">
                        @endif
                    </div>

                    {{-- Action Buttons — ada file --}}
                    <div class="action-buttons-container">

                        {{-- Baca Sekarang / Preview Gratis --}}
                        @auth
                        <a href="{{ route('publikasi.read', $publication->slug) }}"
                            class="btn-ripple w-full px-5 py-3.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold text-base rounded-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3 group">
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-12" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Baca Sekarang</span>
                        </a>
                        @else
                        <a href="{{ route('publikasi.read', $publication->slug) }}"
                            class="btn-ripple w-full px-5 py-3.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold text-base rounded-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3 group">
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-12" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Preview Gratis</span>
                        </a>
                        <div class="flex items-center gap-2 px-3 py-2 bg-[#FFF7F2] rounded-lg border border-[#FFD6B8]">
                            <svg class="w-4 h-4 text-[#FF6B18] flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs text-[#FF6B18] font-medium">
                                Preview dengan watermark.
                                <a href="{{ route('login') }}"
                                    class="font-bold underline hover:text-[#E64627]">Login</a>
                                untuk akses penuh.
                            </p>
                        </div>
                        @endauth

                        {{-- Download PDF --}}
                        @auth
                        <a href="{{ route('publikasi.download', $publication->slug) }}"
                            class="btn-ripple w-full px-5 py-3.5 bg-white border-2 border-[#FF6B18] text-[#FF6B18] font-bold text-base rounded-xl hover:bg-[#FF6B18] hover:text-white hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3 group">
                            <svg class="w-5 h-5 download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download PDF</span>
                        </a>
                        @else
                        <button type="button" onclick="showLoginModal('download')"
                            class="btn-ripple w-full px-5 py-3.5 bg-white border-2 border-[#FF6B18] text-[#FF6B18] font-bold text-base rounded-xl hover:bg-[#FFF7F2] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download PDF</span>
                            <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </button>
                        @endauth

                    </div>

                    {{-- Version Badge --}}
                    @if($latestVersion)
                    <div class="version-badge">
                        <div class="flex items-start justify-between mb-3 pb-3 border-b border-[#EEF0F7]">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#737373] uppercase tracking-wide">Version</p>
                                    <p class="text-lg font-bold text-[#1A1A1A]">v{{ $latestVersion->version_number }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 bg-[#FFF7F2] text-xs font-bold text-[#FF6B18] rounded-full">Published</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-[#FAFBFC] p-3 rounded-lg border border-[#EEF0F7]">
                                <p class="text-xs text-[#737373] font-semibold mb-1.5 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Format
                                </p>
                                <p class="text-sm font-bold text-[#1A1A1A]">PDF</p>
                            </div>
                            <div class="bg-[#FAFBFC] p-3 rounded-lg border border-[#EEF0F7]">
                                <p class="text-xs text-[#737373] font-semibold mb-1.5 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                    </svg>
                                    Size
                                </p>
                                <p class="text-sm font-bold text-[#1A1A1A]">{{ $fileSizeFormatted ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-[#FAFBFC] p-3 rounded-lg border border-[#EEF0F7]">
                                <p class="text-xs text-[#737373] font-semibold mb-1.5 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Downloads
                                </p>
                                <p class="text-sm font-bold text-[#1A1A1A]">{{ number_format($downloadCount ?? 0) }}</p>
                            </div>
                            <div class="bg-[#FAFBFC] p-3 rounded-lg border border-[#EEF0F7]">
                                <p class="text-xs text-[#737373] font-semibold mb-1.5 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Added
                                </p>
                                <p class="text-sm font-bold text-[#1A1A1A]">
                                    {{ $latestVersion->created_at->locale('id_ID')->isoFormat('D MMM YY') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-[#EEF0F7]">
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-[#F0FDF4] rounded-lg border border-[#DCFCE7]">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs font-semibold text-green-700">File ready for download</span>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </aside>

        </div>

        @else
        {{-- ══════════════════════════════════════════════════════
        TIDAK ADA FILE: Layout 1 kolom penuh
        Hanya tampilkan abstract & keywords, tanpa cover/info card
        ══════════════════════════════════════════════════════ --}}
        <div id="publication-detail-grid">

            {{-- Abstract — full width --}}
            @if($publication->abstract)
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300"
                style="width:100%">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $abstractLabel }}
                </h2>
                {{-- max-w-none + style override untuk pastikan prose tidak dibatasi lebar apapun --}}
                <div class="prose prose-sm md:prose-base text-[#1A1A1A] leading-relaxed text-justify"
                    style="max-width:100% !important; width:100% !important">
                    {!! $publication->abstract !!}
                </div>
            </div>
            @endif

            {{-- Keywords — full width --}}
            @if($keywords && count($keywords) > 0)
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300"
                style="width:100%">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    {{ $keywordLabel }}
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($keywords as $keyword)
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] hover:shadow-md hover:scale-105 transition-all duration-300 cursor-pointer">
                        {{ $keyword }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endif

    </article>

    {{-- Login Required Modal --}}
    <div id="loginRequiredModal">
        <div id="loginModalBackdrop" onclick="closeLoginModal()"></div>
        <div id="loginModalContainer">
            <div class="relative w-full max-w-sm p-8 overflow-hidden text-center bg-white shadow-2xl rounded-2xl"
                onclick="event.stopPropagation()">
                <div class="absolute -top-8 -left-8 w-32 h-32 rounded-full bg-[#FFF7F2] opacity-60 pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-8 -right-8 w-24 h-24 rounded-full bg-[#FFF7F2] opacity-60 pointer-events-none">
                </div>
                <button onclick="closeLoginModal()"
                    class="absolute top-4 right-4 p-1.5 rounded-full hover:bg-[#F8F9FC] transition-colors group z-10">
                    <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div
                    class="relative z-10 w-20 h-20 bg-gradient-to-br from-[#FFF7F2] to-[#FFE8D6] rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <svg id="loginModalIcon" class="w-10 h-10 text-[#FF6B18]" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 id="loginModalTitle" class="relative z-10 text-xl font-bold text-[#1A1A1A] mb-2">Masuk untuk
                    melanjutkan
                </h3>
                <p id="loginModalDesc" class="relative z-10 text-[#737373] text-sm mb-7 leading-relaxed px-2">Yuk login
                    dulu
                    untuk menggunakan fitur ini!</p>
                <div class="relative z-10 flex flex-col gap-3">
                    <a id="loginModalBtn" href="{{ route('login') }}"
                        class="w-full py-3.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register') }}"
                        class="w-full py-3.5 border-2 border-[#FF6B18] text-[#FF6B18] hover:bg-[#FFF7F2] font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Daftar Gratis
                    </a>
                    <button onclick="closeLoginModal()"
                        class="text-[#737373] hover:text-[#1A1A1A] text-sm font-medium transition-colors py-1">
                        Nanti saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Authors Modal --}}
    <div id="authorsModal" class="modal-overlay" style="display: none;" onclick="closeAuthorsModal(event)">
        <div class="modal-container">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-[#1A1A1A] mb-1">All Authors</h3>
                            <p class="text-sm text-[#737373]">
                                {{ $authors->count() }} {{ $authors->count() > 1 ? 'contributors' : 'contributor' }} to
                                this
                                publication
                            </p>
                        </div>
                        <button onclick="closeAuthorsModal()"
                            class="p-2 rounded-full hover:bg-[#FFF7F2] transition-colors duration-300 group">
                            <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18]" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($authors as $index => $author)
                        <a href="{{ route('author.profile', $author['slug']) }}" class="block author-card-modal">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($author['photo'])
                                    <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                                        class="object-cover w-16 h-16 rounded-full shadow-md ring-2 ring-white">
                                    @else
                                    <div
                                        class="w-16 h-16 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white text-lg font-bold ring-2 ring-white shadow-md">
                                        {{ $author['initials'] }}
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h4
                                            class="text-base font-bold text-[#1A1A1A] leading-snug hover:text-[#FF6B18] transition-colors">
                                            {{ $author['name'] }}
                                        </h4>
                                        @if($author['is_corresponding'])
                                        <span
                                            class="px-2 py-0.5 bg-[#FFF7F2] text-xs font-bold text-[#FF6B18] rounded-full flex-shrink-0">CA</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-[#737373] mb-2">{{ $author['affiliation'] }}</p>
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-[#EEF0F7]">
                                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        <span class="text-xs font-semibold text-[#737373]">Author #{{ $index + 1
                                            }}</span>
                                        <svg class="w-4 h-4 text-[#FF6B18] ml-auto" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('scripts')
    <script>
        const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

    const loginMessages = {
        favorite: {
            title: 'Tambahkan ke Favorit? ⭐',
            desc:  'Login dulu untuk menandai publikasi favoritmu. Temukan lagi dengan mudah kapan saja!',
        },
        save: {
            title: 'Simpan untuk Dibaca Nanti? 📚',
            desc:  'Login dulu agar publikasi ini tersimpan di koleksimu dan bisa kamu baca kapan pun.',
        },
        download: {
            title: 'Download PDF? 📥',
            desc:  'Login dulu untuk mengunduh PDF publikasi ini secara gratis. Daftar hanya butuh 1 menit!',
        },
    };

    function showLoginModal(action = 'default') {
        const msg = loginMessages[action] ?? {
            title: 'Masuk untuk Melanjutkan',
            desc:  'Kamu perlu login untuk menggunakan fitur ini.',
        };
        document.getElementById('loginModalTitle').textContent = msg.title;
        document.getElementById('loginModalDesc').textContent  = msg.desc;
        const baseUrl   = window.location.href.split('#')[0].split('?')[0];
        const returnUrl = encodeURIComponent(baseUrl + '?after_login=' + action);
        document.getElementById('loginModalBtn').href = `{{ route('login') }}?redirect=${returnUrl}`;
        const modal     = document.getElementById('loginRequiredModal');
        const backdrop  = document.getElementById('loginModalBackdrop');
        const container = document.getElementById('loginModalContainer');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            backdrop.classList.add('show');
            container.classList.add('show');
        });
    }

    function closeLoginModal() {
        const modal     = document.getElementById('loginRequiredModal');
        const backdrop  = document.getElementById('loginModalBackdrop');
        const container = document.getElementById('loginModalContainer');
        backdrop.classList.remove('show');
        container.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    function toggleFavorite(e) {
        if (!isLoggedIn) { showLoginModal('favorite'); return; }
        doToggleFavorite();
    }

    function doToggleFavorite() {
        const button = document.getElementById('btnFavorite');
        if (button.disabled) return;
        button.disabled = true;
        button.classList.add('opacity-60');
        fetch('{{ route("publikasi.favorite", $publication->slug) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            button.disabled = false;
            button.classList.remove('opacity-60');
            if (!data.success) { showNotification(data.message || 'Terjadi kesalahan', 'error'); return; }
            setFavoriteState(data.isFavorited);
            button.classList.add('action-pulse');
            setTimeout(() => button.classList.remove('action-pulse'), 800);
            showNotification(data.message, data.status === 'added' ? 'success' : 'info');
        })
        .catch(() => {
            button.disabled = false;
            button.classList.remove('opacity-60');
            showNotification('Terjadi kesalahan jaringan', 'error');
        });
    }

    function setFavoriteState(active) {
        const svg = document.getElementById('iconFavorite');
        if (active) {
            svg.setAttribute('fill', 'currentColor');
            svg.setAttribute('stroke', 'none');
            svg.classList.add('text-[#FF6B18]');
            svg.innerHTML = '<path fill="currentColor" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>';
        } else {
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.classList.remove('text-[#FF6B18]');
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>';
        }
    }

    function saveForLater(e) {
        if (!isLoggedIn) { showLoginModal('save'); return; }
        doSaveForLater();
    }

    function doSaveForLater() {
        const button = document.getElementById('btnSave');
        if (button.disabled) return;
        button.disabled = true;
        button.classList.add('opacity-60');
        fetch('{{ route("publikasi.save", $publication->slug) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            button.disabled = false;
            button.classList.remove('opacity-60');
            if (!data.success) { showNotification(data.message || 'Terjadi kesalahan', 'error'); return; }
            setSaveState(data.isSaved);
            button.classList.add('action-pulse');
            setTimeout(() => button.classList.remove('action-pulse'), 800);
            showNotification(data.message, data.status === 'added' ? 'success' : 'info');
        })
        .catch(() => {
            button.disabled = false;
            button.classList.remove('opacity-60');
            showNotification('Terjadi kesalahan jaringan', 'error');
        });
    }

    function setSaveState(active) {
        const svg = document.getElementById('iconSave');
        if (active) {
            svg.setAttribute('fill', 'currentColor');
            svg.setAttribute('stroke', 'none');
            svg.classList.add('text-[#FF6B18]');
        } else {
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.classList.remove('text-[#FF6B18]');
        }
    }

    function sharePublication() {
        const title   = '{{ addslashes($publication->title) }}';
        const authors = '{{ is_array($authors) ? ($authors[0]["name"] ?? "") : ($authors->first()["name"] ?? "") }}';
        const desc    = '{{ Str::limit(strip_tags($publication->abstract ?? ""), 100) }}';
        const url     = window.location.href;

        // ✅ Teks share yang informatif untuk WhatsApp/sosmed
        const shareText = `📄 *${title}*\n\n✍️ ${authors}\n\n${desc}\n\n🔗 Baca selengkapnya di DABRAKA:`;

        if (navigator.share) {
            navigator.share({
                title: title,
                text:  shareText,
                url:   url,
            }).catch(err => { if (err.name !== 'AbortError') console.log('Share error:', err); });
        } else {
            // Fallback: salin dengan teks lengkap
            const fullText = `${shareText}\n${url}`;
            navigator.clipboard.writeText(fullText)
                .then(() => showNotification('Link & info publikasi berhasil disalin!', 'success'))
                .catch(()  => showNotification('Gagal menyalin link', 'error'));
        }
    }

    function showAllAuthors() {
        const modal     = document.getElementById('authorsModal');
        const container = modal.querySelector('.modal-container');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            modal.classList.add('show');
            container.classList.add('show');
        });
    }

    function closeAuthorsModal(event) {
        if (event && event.target !== event.currentTarget) return;
        const modal     = document.getElementById('authorsModal');
        const container = modal.querySelector('.modal-container');
        modal.classList.remove('show');
        container.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    function viewCoverFullscreen() {
        window.open('{{ $cover_url }}', '_blank', 'noopener,noreferrer');
    }

    function showNotification(message, type = 'success') {
        const colors = { success: 'bg-green-500', info: 'bg-blue-500', error: 'bg-red-500' };
        const icons  = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            info:    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            error:   '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        };
        const el = document.createElement('div');
        el.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-5 py-3 rounded-xl shadow-xl z-[999999] flex items-center gap-2.5 font-medium text-sm`;
        el.style.animation = 'slideInRight 0.3s ease-out';
        el.innerHTML = `
            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icons[type]}</svg>
            <span>${message}</span>
        `;
        document.body.appendChild(el);
        setTimeout(() => {
            el.style.animation = 'slideOutRight 0.3s ease-in forwards';
            setTimeout(() => el.remove(), 300);
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        @auth
            @if(auth()->user()->isFavorited($publication->id))
                setFavoriteState(true);
            @endif
            @if(auth()->user()->isSaved($publication->id))
                setSaveState(true);
            @endif
        @endauth

        const urlParams  = new URLSearchParams(window.location.search);
        const afterLogin = urlParams.get('after_login');

        if (afterLogin && isLoggedIn) {
            history.replaceState(null, '', window.location.pathname);
            const btnId = afterLogin === 'favorite' ? 'btnFavorite' : 'btnSave';
            const btn   = document.getElementById(btnId);
            if (btn) {
                btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                btn.classList.add('action-pulse');
                setTimeout(() => btn.classList.remove('action-pulse'), 800);
            }
            setTimeout(() => {
                if (afterLogin === 'favorite') doToggleFavorite();
                else if (afterLogin === 'save') doSaveForLater();
            }, 800);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const loginModal = document.getElementById('loginRequiredModal');
            if (loginModal.style.display === 'block') closeLoginModal();
            const authorsModal = document.getElementById('authorsModal');
            if (authorsModal && authorsModal.style.display === 'block') closeAuthorsModal();
        }
    });

    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(110%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0);    opacity: 1; }
            to   { transform: translateX(110%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    </script>
    @endpush