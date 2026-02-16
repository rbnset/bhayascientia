@extends('layouts.app')

@section('title', $name . ' - Profil Penulis')

@push('styles')
<style>
    /* ========================================
       RESET & BASE
       ======================================== */
    body {
        background: #F8F9FC;
    }

    /* ========================================
       HERO SECTION - MOBILE FIRST
       ======================================== */
    .author-hero {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        position: relative;
        padding: 2rem 0 4rem 0;
        margin-top: 0;
        overflow: hidden;
    }

    .author-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        animation: float 20s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translate(0, 0);
        }

        50% {
            transform: translate(20px, -20px);
        }
    }

    /* ========================================
   AVATAR - GRADIENT BORDER RAPAT
   ======================================== */
    .author-avatar-wrapper {
        position: relative;
        display: inline-block;
        padding: 3px;
        background: linear-gradient(45deg, #FFD700, #FF6B18, #E64627, #FFD700);
        background-size: 300% 300%;
        border-radius: 50%;
        animation: gradient-rotate 4s ease infinite;
    }

    @keyframes gradient-rotate {
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
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        border: 3px solid white;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        position: relative;
        z-index: 10;
        background: white;
        display: block;
    }

    /* ========================================
   RESPONSIVE AVATAR SIZES
   ======================================== */
    @media (min-width: 640px) {
        .author-avatar-wrapper {
            padding: 4px;
        }

        .author-avatar {
            width: 120px;
            height: 120px;
            border: 4px solid white;
        }
    }

    @media (min-width: 1024px) {
        .author-avatar-wrapper {
            padding: 5px;
        }

        .author-avatar {
            width: 140px;
            height: 140px;
            border: 5px solid white;
        }
    }

    /* ========================================
       STATS SECTION - MOBILE FIRST
       ======================================== */
    .stats-section {
        margin-top: -2rem;
        position: relative;
        z-index: 20;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(255, 107, 24, 0.15);
        border-color: #FF6B18;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: rotate(5deg) scale(1.05);
    }

    /* ========================================
       PUBLICATION CARDS
       ======================================== */
    .publication-card {
        background: white;
        border-radius: 20px;
        border: 2px solid #EEF0F7;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .publication-card:hover {
        transform: translateY(-4px);
        border-color: #FF6B18;
        box-shadow: 0 12px 28px rgba(255, 107, 24, 0.12);
    }

    .publication-card-cover {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background-color: #F8F9FC;
        display: block;
        /* ✅ ADDED */
    }

    .publication-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
        display: block;
        /* ✅ ADDED */
    }

    .publication-card:hover .publication-card-cover img {
        transform: scale(1.05);
    }

    /* ========================================
       SECTION HEADERS
       ======================================== */
    .section-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1A1A1A;
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #FF6B18, transparent);
        border-radius: 2px;
    }

    /* ========================================
       COLLABORATORS - CIRCULAR AVATARS
       ======================================== */
    .collaborator-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        text-align: center;
        border: 2px solid #EEF0F7;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .collaborator-card:hover {
        transform: translateY(-4px);
        border-color: #FF6B18;
        box-shadow: 0 8px 20px rgba(255, 107, 24, 0.12);
    }

    .collaborator-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        margin: 0 auto 0.75rem;
        border: 3px solid #EEF0F7;
        transition: all 0.3s ease;
        background: #F8F9FC;
    }

    .collaborator-card:hover .collaborator-avatar {
        transform: scale(1.1);
        border-color: #FF6B18;
    }

    /* ========================================
       BUTTONS - MOBILE FIRST
       ======================================== */
    .btn-primary,
    .btn-secondary {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        text-align: center;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 24, 0.4);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: white;
    }

    /* ========================================
       EMPTY STATE
       ======================================== */
    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 3rem 1.5rem;
        text-align: center;
        border: 2px dashed #EEF0F7;
    }

    /* ========================================
       TABLET STYLES (min-width: 640px)
       ======================================== */
    @media (min-width: 640px) {
        .author-hero {
            padding: 3rem 0 5rem 0;
        }

        .stats-section {
            margin-top: -2.5rem;
        }

        .stat-card {
            padding: 1.5rem;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
        }

        .section-title {
            font-size: 1.75rem;
        }

        .collaborator-avatar {
            width: 72px;
            height: 72px;
        }

        .btn-primary,
        .btn-secondary {
            padding: 0.875rem 1.75rem;
            font-size: 0.9375rem;
        }

        .empty-state {
            padding: 4rem 2rem;
        }
    }

    /* ========================================
       DESKTOP STYLES (min-width: 1024px)
       ======================================== */
    @media (min-width: 1024px) {
        .author-hero {
            padding: 4rem 0 6rem 0;
        }

        .stats-section {
            margin-top: -3rem;
        }

        .stat-card {
            padding: 2rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
        }

        .section-title {
            font-size: 2rem;
        }

        .collaborator-avatar {
            width: 80px;
            height: 80px;
        }

        .btn-primary,
        .btn-secondary {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        .empty-state {
            padding: 5rem 2rem;
        }
    }

    /* ========================================
       ACCESSIBILITY & ANIMATIONS
       ======================================== */
    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* Focus states */
    .btn-primary:focus-visible,
    .btn-secondary:focus-visible,
    .collaborator-card:focus-visible {
        outline: 3px solid #FF6B18;
        outline-offset: 2px;
    }
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="author-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div
            class="relative z-10 flex flex-col items-center gap-4 text-center sm:gap-6 lg:gap-8 lg:flex-row lg:items-start lg:text-left">
            {{-- Profile Avatar --}}
            <div class="flex-shrink-0 author-avatar-wrapper">
                <img src="{{ $photoUrl }}" alt="Foto profil {{ $name }}" class="author-avatar"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=FF6B18&color=fff&size=160&bold=true'">
            </div>

            {{-- Profile Info --}}
            <div class="flex-1 max-w-3xl text-white">
                <h1 class="mb-2 text-2xl font-black leading-tight sm:mb-3 sm:text-3xl lg:text-4xl xl:text-5xl">
                    {{ $name }}
                </h1>

                @if($affiliation)
                <div
                    class="flex items-center justify-center gap-2 mb-4 text-sm lg:justify-start sm:mb-5 lg:mb-6 sm:text-base lg:text-lg opacity-95">
                    <svg class="flex-shrink-0 w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                    </svg>
                    <span class="font-semibold">{{ $affiliation }}</span>
                </div>
                @endif

                @if($bio)
                <p
                    class="mb-6 text-sm leading-relaxed sm:mb-7 lg:mb-8 sm:text-base opacity-95 line-clamp-3 lg:line-clamp-none">
                    {{ $bio }}
                </p>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start sm:gap-4">
                    @if($email)
                    <a href="mailto:{{ $email }}" class="w-full btn-secondary sm:w-auto">
                        <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Hubungi Penulis</span>
                    </a>
                    @endif

                    <a href="#publications" class="w-full btn-primary sm:w-auto">
                        <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Lihat Publikasi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Section --}}
<div class="stats-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5 lg:gap-6">
            {{-- Publications --}}
            <div class="stat-card">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="stat-icon bg-gradient-to-br from-[#FF6B18] to-[#E64627]">
                        <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold tracking-wide text-gray-500 uppercase mb-0.5">Total
                            Publikasi</p>
                        <p class="text-2xl font-black text-gray-900 sm:text-3xl lg:text-4xl">{{
                            number_format($totalPublications) }}</p>
                    </div>
                </div>
            </div>

            {{-- Views --}}
            <div class="stat-card">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="stat-icon bg-gradient-to-br from-blue-500 to-blue-600">
                        <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold tracking-wide text-gray-500 uppercase mb-0.5">Total
                            Dilihat</p>
                        <p class="text-2xl font-black text-gray-900 sm:text-3xl lg:text-4xl">{{
                            number_format($totalViews) }}</p>
                    </div>
                </div>
            </div>

            {{-- Downloads --}}
            <div class="stat-card">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="stat-icon bg-gradient-to-br from-green-500 to-green-600">
                        <svg class="w-6 h-6 text-white sm:w-7 sm:h-7" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold tracking-wide text-gray-500 uppercase mb-0.5">Total
                            Unduhan</p>
                        <p class="text-2xl font-black text-gray-900 sm:text-3xl lg:text-4xl">{{
                            number_format($totalDownloads) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Publications Section --}}
<div id="publications"
    class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px] mt-10 sm:mt-12 lg:mt-16 mb-12 sm:mb-14 lg:mb-16">
    <div class="flex flex-col items-start justify-between gap-4 mb-6 sm:flex-row sm:items-center sm:mb-8">
        <div>
            <h2 class="section-title">Karya Publikasi</h2>
            <p class="mt-2 text-sm text-gray-500 sm:text-base">Temukan karya ilmiah yang telah dipublikasikan oleh {{
                explode(' ', $name)[0] }}</p>
        </div>
        <div class="flex items-center gap-2 bg-white px-3 sm:px-4 py-2 rounded-xl border-2 border-[#EEF0F7]">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd"
                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-bold text-gray-700 sm:text-sm">{{ $publications->total() }} Karya</span>
        </div>
    </div>

    @if($formattedPublications->count() > 0)
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
        @foreach($formattedPublications as $publication)
        @php
        // Generate initials
        $words = array_filter(explode(' ', $publication['title']));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
        }
        if (empty($initials)) {
        $initials = mb_strtoupper(mb_substr($publication['title'], 0, 2));
        }

        // Get first author
        $firstAuthor = 'Anonymous';
        if (isset($publication['authors']) && count($publication['authors']) > 0) {
        $firstAuthor = $publication['authors'][0]['name'] ?? 'Unknown';
        }

        // ✅ Generate placeholder URL
        $placeholderParams = http_build_query([
        'initials' => $initials,
        'type' => $publication['publication_type'] ?? 'Publikasi',
        'title' => $publication['title'],
        'category' => $publication['category'] ?? 'Umum',
        'author' => $firstAuthor,
        'v' => time(),
        ]);

        $placeholderUrl = route('placeholder.cover') . '?' . $placeholderParams;

        // Fallback eksternal
        $fallbackUrl = 'https://placehold.co/600x900/6B7280/white?text=' . urlencode($initials);

        // ✅ Use cover or placeholder
        $finalCoverUrl = $publication['cover_url'] ?? $placeholderUrl;
        @endphp

        <a href="{{ $publication['detail_url'] }}" class="publication-card group">
            {{-- ✅ Cover Image WITH INLINE STYLES --}}
            <div class="publication-card-cover" style="display: block; background-color: #F8F9FC;">
                <img src="{{ $finalCoverUrl }}" alt="Cover {{ $publication['title'] }}" loading="lazy" decoding="async"
                    style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; opacity: 1 !important; visibility: visible !important;"
                    onerror="if(!this.dataset.errored){this.dataset.errored='1';this.src='{{ $fallbackUrl }}';}">
            </div>

            {{-- Content --}}
            <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1 bg-[#FFF7F2] text-[#FF6B18] text-xs font-bold rounded-full">
                        {{ $publication['category'] ?? 'Umum' }}
                    </span>
                    <span class="text-xs text-[#737373]">{{ $publication['formatted_date'] }}</span>
                </div>

                <h3
                    class="font-bold text-lg text-[#1A1A1A] mb-2 line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                    {{ $publication['title'] }}
                </h3>

                <p class="text-sm text-[#737373] mb-4 line-clamp-2">
                    {{ $publication['abstract'] ?? 'Tidak ada abstrak' }}
                </p>

                {{-- Stats --}}
                <div class="flex items-center gap-4 text-xs text-[#737373] pt-3 border-t border-[#EEF0F7]">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ number_format($publication['views_count'] ?? 0) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ number_format($publication['download_count'] ?? 0) }}
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mt-8 sm:mt-10 lg:mt-12">
        {{ $publications->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 sm:w-20 sm:h-20" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="mb-2 text-xl font-bold text-gray-900 sm:text-2xl">Belum Ada Publikasi</h3>
        <p class="text-sm text-gray-500 sm:text-base">Penulis ini belum mempublikasikan karya apapun.</p>
    </div>
    @endif
</div>

{{-- Collaborators Section --}}
@if($coAuthors->count() > 0)
<div class="py-10 bg-white sm:py-12 lg:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div class="mb-6 sm:mb-8">
            <h2 class="section-title">Kolaborator</h2>
            <p class="mt-2 text-sm text-gray-500 sm:text-base">Penulis lain yang pernah berkolaborasi dengan {{
                explode(' ', $name)[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 sm:gap-5 lg:gap-6">
            @foreach($coAuthors as $coAuthor)
            <a href="{{ $coAuthor['profile_url'] }}" class="collaborator-card group">
                <img src="{{ $coAuthor['photo_url'] }}" alt="Foto profil {{ $coAuthor['name'] }}"
                    class="collaborator-avatar"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($coAuthor['name']) }}&background=FF6B18&color=fff&size=128&bold=true'">
                <p
                    class="mb-1 text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                    {{ $coAuthor['name'] }}
                </p>
                <p class="text-xs font-semibold text-gray-500">
                    <span class="text-[#FF6B18]">{{ $coAuthor['publications_count'] }}</span> karya
                </p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection