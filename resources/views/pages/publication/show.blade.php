@extends('layouts.app')

@section('title', $publication->title)
@section('main_class', 'mt-0 pb-16')

@push('styles')
<style>
    /* Force Grid Layout - Override any conflicting CSS */
    #publication-detail-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }

    @media (min-width: 1024px) {
        #publication-detail-grid {
            grid-template-columns: 1fr 380px !important;
        }
    }

    /* Ensure sidebar stickiness works */
    #publication-sidebar {
        position: relative;
    }

    @media (min-width: 1024px) {
        #publication-sidebar .sticky-cover {
            position: sticky !important;
            top: 1.5rem !important;
        }
    }

    /* Prevent any flex conflicts */
    #publication-detail-grid>* {
        min-width: 0;
    }

    /* Enhanced Cover Image Hover Effect */
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

    /* Button Ripple Effect */
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

    /* Download Icon Animation */
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

    /* ✅ Button Container Spacing */
    .action-buttons-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* ✅ Enhanced Version Badge Styling */
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

    /* Grid Details Styling */
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

    /* Icon Styling */
    .version-badge svg {
        flex-shrink: 0;
    }

    /* Text Styles */
    .version-badge .text-xs.uppercase {
        letter-spacing: 0.05em;
    }

    /* Animation for status indicator */
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
</style>
@endpush

@section('content')

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

    {{-- Header Section (Full Width) --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
        {{-- Category & Actions --}}
        <div class="flex items-center justify-between mb-4">
            <span class="px-4 py-1.5 bg-[#FFF7F2] text-sm font-bold text-[#FF6B18] rounded-full">
                {{ $category }}
            </span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleFavorite()"
                    class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] hover:scale-110 transition-all duration-300 group"
                    title="Tambah ke favorit">
                    <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </button>
                <button type="button" onclick="saveForLater()"
                    class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] hover:scale-110 transition-all duration-300 group"
                    title="Simpan untuk nanti">
                    <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </button>
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
                <div
                    class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] hover:shadow-md transition-all duration-300 cursor-pointer">
                    @if($author['photo'])
                    <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                        class="w-10 h-10 rounded-full object-cover ring-2 ring-white">
                    @else
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white">
                        {{ $author['initials'] }}
                    </div>
                    @endif
                    <div class="text-left">
                        <p class="text-sm font-bold text-[#1A1A1A]">
                            {{ $author['name'] }}
                            @if($author['is_corresponding'])
                            <span class="text-[#FF6B18]" title="Corresponding Author">*</span>
                            @endif
                        </p>
                        <p class="text-xs text-[#737373]">{{ $author['affiliation'] }}</p>
                    </div>
                </div>
                @endforeach

                @if($authors->count() > 3)
                <button type="button" onclick="showAllAuthors()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] hover:shadow-md transition-all duration-300">
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

    {{-- ✅ TWO COLUMN LAYOUT --}}
    <div id="publication-detail-grid">

        {{-- ✅ LEFT COLUMN: Abstract + Keywords --}}
        <div class="space-y-6">

            {{-- Abstract --}}
            @if($publication->abstract)
            <div
                class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Abstract
                </h2>
                <div class="prose prose-sm md:prose-base max-w-none text-[#1A1A1A] leading-relaxed text-justify">
                    {!! nl2br(e($publication->abstract)) !!}
                </div>
            </div>
            @endif

            {{-- Keywords --}}
            @if($keywords && count($keywords) > 0)
            <div
                class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 hover:shadow-lg transition-shadow duration-300">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Keywords
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

        {{-- ✅ RIGHT COLUMN: Cover + Action Buttons --}}
        <aside id="publication-sidebar">
            @if($cover_url)
            <div class="sticky-cover">
                {{-- Cover Image with Enhanced Hover --}}
                <div class="cover-image-wrapper mb-6">
                    <img src="{{ $cover_url }}" alt="Cover {{ $publication->title }}" class="w-full object-cover">

                    {{-- Enhanced Overlay --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-500 rounded-xl flex items-end justify-center p-6">
                        <button onclick="viewCoverFullscreen()"
                            class="px-6 py-3 bg-white/95 backdrop-blur-sm text-[#1A1A1A] text-sm font-bold rounded-lg hover:bg-white transition-all duration-300 transform hover:scale-105 shadow-xl flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                            View Full Size
                        </button>
                    </div>
                </div>

                @php
                $latestVersion = $publication->versions->first();
                $hasFile = $latestVersion && !empty($latestVersion->pdf_file_path);
                @endphp

                {{-- ✅ ACTION BUTTONS CONTAINER --}}
                <div class="action-buttons-container">
                    {{-- Button Baca Sekarang (Primary) --}}
                    <a href="{{ route('publikasi.read', $publication->slug) }}"
                        class="btn-ripple w-full px-5 py-3.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold text-base rounded-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3 group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>Baca Sekarang</span>
                    </a>

                    {{-- Button Download PDF (Secondary) --}}
                    @if($hasFile)
                    <a href="{{ route('publikasi.download', $publication->slug) }}"
                        class="btn-ripple w-full px-5 py-3.5 bg-white border-2 border-[#FF6B18] text-[#FF6B18] font-bold text-base rounded-xl hover:bg-[#FF6B18] hover:text-white hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3 group">
                        <svg class="w-5 h-5 download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download PDF</span>
                    </a>
                    @else
                    {{-- Button Disabled State --}}
                    <button type="button" disabled
                        class="w-full px-5 py-3.5 bg-gray-100 border-2 border-gray-300 text-gray-400 font-bold text-base rounded-xl cursor-not-allowed flex items-center justify-center gap-3 opacity-60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>PDF Not Available</span>
                    </button>
                    @endif
                </div>

                {{-- ✅ ENHANCED VERSION BADGE --}}
                @if($latestVersion)
                <div class="version-badge">
                    {{-- Version Header --}}
                    <div class="flex items-start justify-between mb-3 pb-3 border-b border-[#EEF0F7]">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#737373] uppercase tracking-wide">Version</p>
                                <p class="text-lg font-bold text-[#1A1A1A]">v{{ $latestVersion->version_number }}</p>
                            </div>
                        </div>
                        @if($hasFile)
                        <span class="px-3 py-1 bg-[#FFF7F2] text-xs font-bold text-[#FF6B18] rounded-full">
                            Published
                        </span>
                        @else
                        <span class="px-3 py-1 bg-gray-100 text-xs font-bold text-gray-500 rounded-full">
                            Unpublished
                        </span>
                        @endif
                    </div>

                    {{-- File Details Grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Format --}}
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

                        {{-- File Size --}}
                        <div class="bg-[#FAFBFC] p-3 rounded-lg border border-[#EEF0F7]">
                            <p class="text-xs text-[#737373] font-semibold mb-1.5 flex items-center gap-1">
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                                Size
                            </p>
                            <p class="text-sm font-bold text-[#1A1A1A]">
                                {{ $fileSizeFormatted ?? 'N/A' }}
                            </p>
                        </div>

                        {{-- Downloads --}}
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

                        {{-- Date Added --}}
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

                    {{-- File Status Indicator --}}
                    <div class="mt-3 pt-3 border-t border-[#EEF0F7]">
                        @if($hasFile)
                        <div class="flex items-center gap-2 px-3 py-2 bg-[#F0FDF4] rounded-lg border border-[#DCFCE7]">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-xs font-semibold text-green-700">File ready for download</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2 px-3 py-2 bg-[#FEF2F2] rounded-lg border border-[#FECACA]">
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <span class="text-xs font-semibold text-red-700">File not uploaded yet</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif
        </aside>

    </div>

</article>

@push('scripts')
<script>
    function toggleFavorite() {
    const button = event.currentTarget;
    button.classList.add('animate-ping');
    setTimeout(() => button.classList.remove('animate-ping'), 600);
    console.log('Toggle favorite');
}

function saveForLater() {
    const button = event.currentTarget;
    button.classList.add('animate-ping');
    setTimeout(() => button.classList.remove('animate-ping'), 600);
    console.log('Save for later');
}

function sharePublication() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $publication->title }}',
            text: 'Check out this publication',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(window.location.href);

        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-[#FF6B18] text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
        notification.innerHTML = '<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>Link copied to clipboard!</span></div>';
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
}

function showAllAuthors() {
    console.log('Show all authors modal');
    // TODO: Implement modal
}

function viewCoverFullscreen() {
    const coverUrl = '{{ $cover_url }}';
    window.open(coverUrl, '_blank');
}
</script>
@endpush
@endsection
