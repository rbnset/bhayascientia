@extends('layouts.app')

@section('title', $publication->title)
@section('main_class', 'mt-0 pb-16')

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
    <div class="grid lg:grid-cols-[1fr,320px] gap-8">

        {{-- Left Column: Main Content --}}
        <div>
            {{-- Header --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
                {{-- Category & Actions --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="px-4 py-1.5 bg-[#FFF7F2] text-sm font-bold text-[#FF6B18] rounded-full">
                        {{ $category }}
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
                    {{ $publication->title }}
                </h1>

                {{-- Authors --}}
                @if($authors->count() > 0)
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#737373] uppercase tracking-wide mb-3">Authors</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach($authors->take(3) as $author)
                        <div class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#F8F9FC] rounded-xl group">
                            @if($author['photo'])
                            <img src="{{ $author['photo'] }}" alt="{{ $author['name'] }}"
                                class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-full flex items-center justify-center text-white text-sm font-bold">
                                {{ $author['initials'] }}
                            </div>
                            @endif
                            <div class="text-left">
                                <p class="text-sm font-bold text-[#1A1A1A]">
                                    {{ $author['name'] }}
                                    @if($author['is_corresponding'])
                                    <span class="text-[#FF6B18]">*</span>
                                    @endif
                                </p>
                                <p class="text-xs text-[#737373]">{{ $author['affiliation'] }}</p>
                            </div>
                        </div>
                        @endforeach

                        @if($authors->count() > 3)
                        <button type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#F8F9FC] rounded-xl hover:bg-[#FFF7F2] transition-colors">
                            <span class="text-sm font-semibold text-[#737373]">+{{ $authors->count() - 3 }} more
                                authors</span>
                        </button>
                        @endif
                    </div>
                </div>
                @endif

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
                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $formatted_date }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Type
                        </p>
                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $publication->publicationType->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Downloads
                        </p>
                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $publication->downloadLogs->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#737373] mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Status
                        </p>
                        <p class="text-sm font-bold text-[#FF6B18]">{{ ucfirst($publication->status) }}</p>
                    </div>
                </div>
            </div>

            {{-- Abstract --}}
            @if($publication->abstract)
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Abstract
                </h2>
                <div class="prose prose-sm md:prose-base max-w-none text-[#1A1A1A]">
                    {!! nl2br(e($publication->abstract)) !!}
                </div>
            </div>
            @endif

            {{-- Keywords --}}
            @if($keywords->count() > 0)
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 md:p-8 mb-6">
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
                        class="px-4 py-2 bg-[#F8F9FC] text-sm font-medium text-[#1A1A1A] rounded-full hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-colors cursor-pointer">
                        {{ $keyword }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Download CTA --}}
            @if($publication->versions->first()?->file_path)
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
                            Latest version: {{ $publication->versions->first()->version_number }}
                        </p>
                    </div>
                    <a href="{{ route('publikasi.download', $publication->slug) }}"
                        class="px-8 py-4 bg-white text-[#FF6B18] font-bold rounded-xl shrink-0 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-2 group">
                        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Sidebar (Metrics, dll) --}}
        <aside class="space-y-6">
            {{-- Cover Image --}}
            @if($cover_url)
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-4 sticky top-6 overflow-hidden">
                <img src="{{ $cover_url }}" alt="Cover {{ $publication->title }}" class="w-full rounded-xl shadow-lg">
            </div>
            @endif

            {{-- Metrics Card - sisanya tetap sama --}}
            <div class="bg-white rounded-2xl border border-[#EEF0F7] p-6 sticky top-6">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-4">Publication Metrics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#737373] flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Downloads
                        </span>
                        <span class="text-lg font-bold text-[#1A1A1A]">{{
                            number_format($publication->downloadLogs->count()) }}</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</article>

@push('scripts')
<script>
    function toggleFavorite() {
    console.log('Toggle favorite');
}

function saveForLater() {
    console.log('Save for later');
}

function sharePublication() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $publication->title }}',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}
</script>
@endpush
@endsection
