@extends('layouts.app')

@section('title', $name . ' - Author Profile')

@push('styles')
<style>
    /* Reset Main Padding */
    body {
        background: #F8F9FC;
    }

    /* Hero Section - Konsisten dengan Brand */
    .author-hero {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        position: relative;
        padding: 4rem 0 8rem 0;
        margin-top: -2rem;
        /* Overlap dengan navbar */
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
        animation: float 15s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translate(0, 0);
        }

        50% {
            transform: translate(30px, -30px);
        }
    }

    /* Profile Image - SELALU LINGKARAN */
    .author-avatar {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        /* Force circular */
        object-fit: cover;
        object-position: center;
        border: 6px solid white;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
        z-index: 10;
        background: white;
        /* Fallback bg */
    }

    .author-avatar-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 50%;
    }

    .author-avatar-wrapper::before {
        content: '';
        position: absolute;
        inset: -6px;
        background: linear-gradient(45deg, #FFD700, #FF6B18, #E64627, #FFD700);
        border-radius: 50%;
        z-index: -1;
        animation: rotate-border 4s linear infinite;
    }

    @keyframes rotate-border {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Stats Cards - Brand Consistent */
    .stats-section {
        margin-top: -4rem;
        position: relative;
        z-index: 20;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(255, 107, 24, 0.15);
        border-color: #FF6B18;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: rotate(10deg) scale(1.1);
    }

    /* Section Headers */
    .section-title {
        font-size: 1.875rem;
        font-weight: 800;
        color: #1A1A1A;
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 50%;
        height: 4px;
        background: linear-gradient(90deg, #FF6B18, transparent);
        border-radius: 2px;
    }

    /* Publication Card - Konsisten dengan Home */
    .publication-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 2px solid #EEF0F7;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .publication-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(255, 107, 24, 0.12);
        border-color: #FF6B18;
    }

    .publication-cover {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background: linear-gradient(135deg, #FFE5D9 0%, #FFF7F2 100%);
    }

    .publication-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.5s ease;
    }

    .publication-card:hover .publication-cover img {
        transform: scale(1.1);
    }

    .publication-info {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .category-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #FFF7F2;
        color: #FF6B18;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Collaborators - Circular Avatars */
    .collaborator-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid #EEF0F7;
        transition: all 0.3s ease;
    }

    .collaborator-card:hover {
        transform: translateY(-4px);
        border-color: #FF6B18;
        box-shadow: 0 8px 20px rgba(255, 107, 24, 0.12);
    }

    .collaborator-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        /* SELALU LINGKARAN */
        object-fit: cover;
        object-position: center;
        margin: 0 auto 1rem;
        border: 3px solid #EEF0F7;
        transition: transform 0.3s ease;
        background: #F8F9FC;
    }

    .collaborator-card:hover .collaborator-avatar {
        transform: scale(1.1);
        border-color: #FF6B18;
    }

    /* Buttons - Brand Consistent */
    .btn-primary {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 24, 0.4);
    }

    .btn-secondary {
        background: white;
        color: #FF6B18;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: white;
    }

    /* Empty State */
    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 5rem 2rem;
        text-align: center;
        border: 2px dashed #EEF0F7;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .author-hero {
            padding: 3rem 0 6rem 0;
        }

        .author-avatar {
            width: 120px;
            height: 120px;
        }

        .section-title {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="author-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div class="relative z-10 flex flex-col items-center gap-8 text-center md:flex-row md:items-start md:text-left">
            {{-- Profile Avatar - LINGKARAN --}}
            <div class="flex-shrink-0 author-avatar-wrapper">
                <img src="{{ $photoUrl }}" alt="{{ $name }}" class="author-avatar">
            </div>

            {{-- Profile Info --}}
            <div class="flex-1 text-white">
                <h1 class="mb-3 text-4xl font-black leading-tight md:text-5xl">
                    {{ $name }}
                </h1>

                @if($affiliation)
                <div class="flex items-center justify-center gap-2 mb-6 text-lg md:justify-start opacity-95">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                    </svg>
                    <span class="font-semibold">{{ $affiliation }}</span>
                </div>
                @endif

                @if($bio)
                <p class="max-w-2xl mb-8 text-base leading-relaxed opacity-95">
                    {{ $bio }}
                </p>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-4 md:justify-start">
                    @if($email)
                    <a href="mailto:{{ $email }}" class="btn-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Contact Author
                    </a>
                    @endif

                    <a href="#publications" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        View Publications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Section --}}
<div class="stats-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- Publications --}}
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm font-bold tracking-wide text-gray-500 uppercase">Publications</p>
                        <p class="text-4xl font-black text-gray-900">{{ number_format($totalPublications) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Published works</p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-[#FF6B18] to-[#E64627]">
                        <svg class="text-white w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Views --}}
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm font-bold tracking-wide text-gray-500 uppercase">Total Views</p>
                        <p class="text-4xl font-black text-gray-900">{{ number_format($totalViews) }}</p>
                        <p class="mt-1 text-xs text-gray-400">All time</p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-blue-500 to-blue-600">
                        <svg class="text-white w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Downloads --}}
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm font-bold tracking-wide text-gray-500 uppercase">Downloads</p>
                        <p class="text-4xl font-black text-gray-900">{{ number_format($totalDownloads) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Total downloads</p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-green-500 to-green-600">
                        <svg class="text-white w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Publications Section --}}
<div id="publications" class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px] mt-16 mb-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="section-title">Publications</h2>
            <p class="mt-2 text-gray-500">Discover published works by {{ explode(' ', $name)[0] }}</p>
        </div>
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border-2 border-[#EEF0F7]">
            <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd"
                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-bold text-gray-700">{{ $publications->total() }} Total</span>
        </div>
    </div>

    @if($publications->count() > 0)
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($publications as $publication)
        <a href="{{ route('publikasi.show', $publication->slug) }}" class="publication-card">
            {{-- Cover --}}
            @php
            $latestVersion = $publication->versions->first();
            $coverUrl = $latestVersion && $latestVersion->cover_image_path
            ? asset('storage/' . str_replace('public/', '', $latestVersion->cover_image_path))
            : 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode(substr($publication->title, 0, 30));
            @endphp
            <div class="publication-cover">
                <img src="{{ $coverUrl }}" alt="{{ $publication->title }}" loading="lazy">
            </div>

            {{-- Info --}}
            <div class="publication-info">
                <span class="category-badge">
                    {{ $publication->categories->first()->name ?? 'Uncategorized' }}
                </span>

                <h3 class="mt-3 mb-2 text-lg font-bold text-gray-900 line-clamp-2">
                    {{ $publication->title }}
                </h3>

                <p class="flex items-center gap-2 mb-auto text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $publication->published_at ? $publication->published_at->format('M d, Y') : 'No date' }}
                </p>

                {{-- Stats --}}
                <div class="flex items-center gap-6 pt-4 mt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="font-semibold">{{ number_format($publication->viewLogs->count() ?? 0) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="font-semibold">{{ number_format($publication->downloadLogs->count() ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mt-12">
        {{ $publications->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="mb-2 text-2xl font-bold text-gray-900">No Publications Yet</h3>
        <p class="text-gray-500">This author hasn't published any work yet.</p>
    </div>
    @endif
</div>

{{-- Collaborators Section --}}
@if($coAuthors->count() > 0)
<div class="py-16 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1130px]">
        <div class="mb-8">
            <h2 class="section-title">Collaborators</h2>
            <p class="mt-2 text-gray-500">Authors who have worked with {{ explode(' ', $name)[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
            @foreach($coAuthors as $coAuthor)
            <a href="{{ route('author.profile', $coAuthor->user_id ?? $coAuthor->id) }}" class="collaborator-card">
                <img src="{{ $coAuthor->photo_url }}" alt="{{ $coAuthor->name }}" class="collaborator-avatar">
                <p class="mb-1 text-sm font-bold text-gray-900 line-clamp-1">
                    {{ $coAuthor->name }}
                </p>
                <p class="text-xs font-semibold text-gray-500">
                    <span class="text-[#FF6B18]">{{ $coAuthor->publications_count }}</span> works
                </p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection