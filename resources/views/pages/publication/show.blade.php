@extends('layouts.app')

@section('title', 'Detail Publikasi')
@section('main_class', 'mt-0 pb-16')

@section('content')

{{-- Breadcrumb --}}
<nav class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 text-sm">
        <li><a href="{{ route('home') }}" class="text-[#737373] hover:text-[#FF6B18] transition-colors">Beranda</a></li>
        <li><span class="text-[#737373]">/</span></li>
        <li><a href="{{ route('publikasi') }}"
                class="text-[#737373] hover:text-[#FF6B18] transition-colors">Publikasi</a></li>
        <li><span class="text-[#737373]">/</span></li>
        <li><span class="text-[#1A1A1A] font-semibold">Detail</span></li>
    </ol>
</nav>

{{-- Main Content --}}
<article class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6">
    <div class="grid lg:grid-cols-[1fr,320px] gap-8">

        {{-- Left Column: Main Content --}}
        <div>
            {{-- Header --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
                {{-- Category & Actions --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="px-4 py-1.5 bg-[#FFF7F2] text-sm font-bold text-[#FF6B18] rounded-full">
                        Technology
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleFavorite()"
                            class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] transition-colors group"
                            title="Tambah ke favorit">
                            <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </button>
                        <button type="button" onclick="saveForLater()"
                            class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] transition-colors group"
                            title="Simpan untuk nanti">
                            <svg class="w-6 h-6 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                        </button>
                        <button type="button" onclick="sharePublication()"
                            class="p-2.5 rounded-full bg-[#F8F9FC] hover:bg-[#FFF7F2] transition-colors group"
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
                    The Impact of Artificial Intelligence on Modern Healthcare Systems:
                    A Comprehensive Analysis of Current Trends and Future Prospects
                </h1>

                {{-- Authors --}}
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Authors</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="#"
                            class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] transition-colors group">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white text-sm font-bold">
                                JD
                            </div>
                            <div class="text-left">
                                <p
                                    class="text-sm font-bold text-[#1A1A1A] group-hover:text-[#FF6B18] transition-colors">
                                    Dr. John Doe</p>
                                <p class="text-xs text-[#737373]">Stanford University</p>
                            </div>
                        </a>
                        <a href="#"
                            class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] transition-colors group">
                            <div
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full bg-gradient-to-br from-blue-500 to-blue-600">
                                JS
                            </div>
                            <div class="text-left">
                                <p
                                    class="text-sm font-bold text-[#1A1A1A] group-hover:text-[#FF6B18] transition-colors">
                                    Jane Smith, PhD</p>
                                <p class="text-xs text-[#737373]">MIT</p>
                            </div>
                        </a>
                        <button type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] transition-colors">
                            <span class="text-sm font-semibold text-[#737373]">+3 more authors</span>
                        </button>
                    </div>
                </div>

                {{-- Meta Info --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-[#EEF0F7]">
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Published
                        </p>
                        <p class="text-sm font-bold text-[#1A1A1A]">January 12, 2026</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Journal
                        </p>
                        <p class="text-sm font-bold text-[#1A1A1A]">Nature Medicine</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            DOI
                        </p>
                        <a href="#" class="text-sm font-bold text-[#FF6B18] hover:underline">10.1038/s41591-026</a>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Citations
                        </p>
                        <p class="text-sm font-bold text-[#1A1A1A]">342</p>
                    </div>
                </div>
            </div>

            {{-- Abstract --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Abstract
                </h2>
                <div class="prose prose-sm md:prose-base max-w-none text-[#1A1A1A] space-y-4">
                    <p>
                        This comprehensive study examines the transformative effects of artificial intelligence (AI)
                        technologies in healthcare delivery, patient outcomes, and medical diagnosis accuracy. Through
                        a systematic review of 150 recent studies and case analyses from leading healthcare institutions
                        worldwide, we demonstrate how AI-driven solutions are revolutionizing clinical decision-making
                        processes and operational efficiency in healthcare systems.
                    </p>
                    <p>
                        Our findings reveal that AI implementation in diagnostic imaging has improved accuracy rates by
                        an average of 23%, while reducing diagnosis time by 40%. Machine learning algorithms show
                        particular promise in early disease detection, with sensitivity rates exceeding 95% in certain
                        cancer screenings. The integration of natural language processing in electronic health records
                        has streamlined patient data management and enhanced care coordination.
                    </p>
                    <p>
                        However, challenges remain in areas of data privacy, algorithm bias, and the need for regulatory
                        frameworks. This study concludes with recommendations for ethical AI implementation and future
                        research directions to ensure equitable access to AI-enhanced healthcare services.
                    </p>
                </div>
            </div>

            {{-- Keywords --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Keywords
                </h2>
                <div class="flex flex-wrap gap-2">
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Artificial Intelligence
                    </span>
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Healthcare Systems
                    </span>
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Machine Learning
                    </span>
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Medical Diagnosis
                    </span>
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Clinical Decision Making
                    </span>
                    <span
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        Patient Outcomes
                    </span>
                </div>
            </div>

            {{-- Download CTA --}}
            <div
                class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-2xl p-8 text-white mb-6 relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItMnptLTIgMGgyLTJ6bTAgMmgyLTJ6bS0yIDBoMi0yem0yLTJoLTJ2Mmgydi0yem0wIDBoMnYyaC0ydi0yem0wIDBoMnYtMmgtMnYyem0wLTJoMnYtMmgtMnYyem0wIDBoLTJ2Mmgydi0yem0wIDBoLTJ2LTJoMnYyem0wLTJoMnYtMmgtMnYyem0wIDBoLTJ2LTJoMnYyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-30">
                </div>

                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="mb-2 text-2xl font-bold">Download Full Paper</h3>
                        <p class="mb-1 text-sm text-white/90">
                            Access the complete research paper in PDF format
                        </p>
                        <p class="text-xs text-white/75">
                            File size: 2.4 MB • 24 pages
                        </p>
                    </div>
                    <button type="button"
                        class="px-8 py-4 bg-white text-[#FF6B18] font-bold rounded-xl shrink-0 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-2 group">
                        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>

            {{-- Related Publications --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Related Publications
                </h2>
                <div class="space-y-4">
                    @for($i = 1; $i <= 4; $i++) <article
                        class="flex gap-4 p-4 rounded-xl hover:bg-[#F8F9FC] transition-colors group">
                        <div
                            class="shrink-0 w-20 h-20 bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 rounded-lg overflow-hidden">
                            <img src="https://placehold.co/200x200/FFF7F2/FF6B18?text={{ $i }}"
                                alt="Related publication" class="object-cover w-full h-full">
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('publikasi.show', $i) }}">
                                <h3
                                    class="text-sm font-bold text-[#1A1A1A] mb-1 line-clamp-2 group-hover:text-[#FF6B18] transition-colors">
                                    Machine Learning Applications in Early Disease Detection Systems
                                </h3>
                            </a>
                            <p class="text-xs text-[#737373] mb-2">Dr. Sarah Johnson, et al. • 2025</p>
                            <div class="flex items-center gap-3 text-xs text-[#737373]">
                                <span class="px-2 py-0.5 bg-[#F8F9FC] rounded-full">Technology</span>
                                <span>245 citations</span>
                            </div>
                        </div>
</article>
@endfor
</div>
</div>
</div>

{{-- Right Column: Sidebar --}}
<aside class="space-y-6">
    {{-- Metrics Card --}}
    <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 sticky top-6">
        <h3 class="text-lg font-bold text-[#1A1A1A] mb-4">Publication Metrics</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-[#737373] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Total Views
                </span>
                <span class="text-lg font-bold text-[#1A1A1A]">12,458</span>
            </div>
            <div class="h-px bg-[#EEF0F7]"></div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-[#737373] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Downloads
                </span>
                <span class="text-lg font-bold text-[#1A1A1A]">3,842</span>
            </div>
            <div class="h-px bg-[#EEF0F7]"></div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-[#737373] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    Citations
                </span>
                <span class="text-lg font-bold text-[#FF6B18]">342</span>
            </div>
            <div class="h-px bg-[#EEF0F7]"></div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-[#737373] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Altmetric
                </span>
                <span class="text-lg font-bold text-[#1A1A1A]">127</span>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-[#EEF0F7]">
            <p class="text-xs font-bold text-[#737373] uppercase mb-3">Share this publication</p>
            <div class="grid grid-cols-3 gap-2">
                <button class="p-3 bg-[#F8F9FC] rounded-lg hover:bg-[#FFF7F2] transition-colors group">
                    <svg class="w-5 h-5 mx-auto text-[#1A1A1A] group-hover:text-[#FF6B18]" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                </button>
                <button class="p-3 bg-[#F8F9FC] rounded-lg hover:bg-[#FFF7F2] transition-colors group">
                    <svg class="w-5 h-5 mx-auto text-[#1A1A1A] group-hover:text-[#FF6B18]" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                    </svg>
                </button>
                <button class="p-3 bg-[#F8F9FC] rounded-lg hover:bg-[#FFF7F2] transition-colors group">
                    <svg class="w-5 h-5 mx-auto text-[#1A1A1A] group-hover:text-[#FF6B18]" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-[#FFF7F2] rounded-2xl border border-[#FF6B18]/20 p-6">
        <h3 class="text-lg font-bold text-[#1A1A1A] mb-4">Quick Actions</h3>
        <div class="space-y-2">
            <button onclick="citePaper()"
                class="w-full px-4 py-3 bg-white text-[#1A1A1A] font-semibold rounded-xl text-sm text-left hover:bg-[#FF6B18] hover:text-white transition-all flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cite this Paper
            </button>
            <button onclick="emailAuthor()"
                class="w-full px-4 py-3 bg-white text-[#1A1A1A] font-semibold rounded-xl text-sm text-left hover:bg-[#FF6B18] hover:text-white transition-all flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Contact Author
            </button>
            <button onclick="getAlerts()"
                class="w-full px-4 py-3 bg-white text-[#1A1A1A] font-semibold rounded-xl text-sm text-left hover:bg-[#FF6B18] hover:text-white transition-all flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Get Alerts
            </button>
            <button onclick="exportData()"
                class="w-full px-4 py-3 bg-white text-[#1A1A1A] font-semibold rounded-xl text-sm text-left hover:bg-[#FF6B18] hover:text-white transition-all flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Data
            </button>
        </div>
    </div>
</aside>
</div>
</article>

@push('scripts')
<script>
    function toggleFavorite() {
    console.log('Toggle favorite');
    // TODO: Implement AJAX
}

function saveForLater() {
    console.log('Save for later');
    // TODO: Implement AJAX
}

function sharePublication() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

function citePaper() {
    alert('Citation dialog will open here');
    // TODO: Show citation modal with different formats (APA, MLA, Chicago, etc.)
}

function emailAuthor() {
    alert('Email form will open here');
    // TODO: Show contact form
}

function getAlerts() {
    alert('Alert subscription will be set up');
    // TODO: Show alert subscription form
}

function exportData() {
    alert('Export options will be shown');
    // TODO: Show export format options (BibTeX, RIS, EndNote, etc.)
}
</script>
@endpush
@endsection
