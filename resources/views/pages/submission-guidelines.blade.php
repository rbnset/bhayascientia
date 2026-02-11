{{-- resources/views/pages/submission-guidelines.blade.php --}}

@extends('layouts.app')

@section('title', 'Panduan Submit Naskah - BHAYASCIENTIA')
@section('main_class', 'pb-16')

@section('content')

{{-- Hero Section --}}
<section
    class="bg-gradient-to-br from-[#FF6B18] via-[#E64627] to-[#D63A25] relative overflow-hidden rounded-2xl sm:rounded-[28px]">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-12 sm:py-16 md:py-20 lg:py-24 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 mb-6 text-xs sm:text-sm text-white/80 sm:mb-8" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Panduan Submit</span>
        </nav>

        <div class="max-w-3xl mx-auto text-center text-white">
            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 mb-4 sm:mb-6 text-[10px] sm:text-xs font-bold rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Panduan Lengkap
            </div>

            <h1 class="mb-4 text-3xl font-black leading-tight sm:text-4xl md:text-5xl lg:text-6xl sm:mb-6">
                📄 Panduan Submit Naskah
            </h1>
            <p class="text-base leading-relaxed sm:text-xl md:text-2xl text-white/90">
                Pelajari cara submit naskah jurnal, buku, atau opini dengan mudah dan cepat
            </p>
        </div>
    </div>
</section>

{{-- Timeline Process --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-12 sm:mt-16">
    <div class="mb-10 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#111827] mb-3">
            ⏱️ Timeline Proses Submit
        </h2>
        <p class="text-sm sm:text-base text-[#6B7280] max-w-2xl mx-auto">
            Dari submit hingga publikasi, semua transparan dan terukur
        </p>
    </div>

    <div class="relative">
        {{-- Vertical line (desktop) --}}
        <div
            class="hidden lg:block absolute left-1/2 top-0 bottom-0 w-1 bg-gradient-to-b from-[#FF6B18] via-[#E64627] to-[#FF6B18] -translate-x-1/2">
        </div>

        <div class="space-y-8 lg:space-y-12">
            @foreach($timeline as $index => $item)
            <div
                class="relative flex flex-col lg:flex-row lg:items-center {{ $index % 2 === 0 ? 'lg:flex-row-reverse' : '' }} gap-6">
                {{-- Content Card --}}
                <div class="flex-1 lg:{{ $index % 2 === 0 ? 'text-right' : 'text-left' }}">
                    <div
                        class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-5 sm:p-6 hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start gap-3 {{ $index % 2 === 0 ? 'lg:flex-row-reverse' : '' }}">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-black text-white">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0 {{ $index % 2 === 0 ? 'lg:text-right' : 'lg:text-left' }}">
                                <h3 class="text-lg font-bold text-[#111827] mb-1">{{ $item['step'] }}</h3>
                                <p class="text-sm text-[#6B7280] mb-2">{{ $item['description'] }}</p>
                                <span
                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold bg-orange-100 text-[#FF6B18] rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $item['duration'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Center dot (desktop) --}}
                <div class="hidden lg:block w-6 h-6 rounded-full bg-[#FF6B18] border-4 border-white shadow-lg z-10">
                </div>

                {{-- Spacer --}}
                <div class="flex-1 hidden lg:block"></div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Requirements by Type --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-16 sm:mt-20">
    <div class="mb-10 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#111827] mb-3">
            📋 Persyaratan Dokumen
        </h2>
        <p class="text-sm sm:text-base text-[#6B7280] max-w-2xl mx-auto">
            Setiap jenis publikasi memiliki persyaratan yang berbeda
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @foreach($requirements as $key => $req)
        <div
            class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 hover:border-[#FF6B18] hover:shadow-xl transition-all duration-300">
            <div class="mb-4 text-4xl">
                @if($key === 'jurnal') 📚
                @elseif($key === 'buku') 📖
                @else ✍️
                @endif
            </div>

            <h3 class="text-xl font-bold text-[#111827] mb-4">{{ $req['name'] }}</h3>

            <div class="mb-5 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[#6B7280]">Format:</span>
                    <span class="font-semibold text-[#111827]">{{ implode(', ', $req['formats']) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[#6B7280]">Max Size:</span>
                    <span class="font-semibold text-[#FF6B18]">{{ $req['max_size'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[#6B7280]">Halaman:</span>
                    <span class="font-semibold text-[#111827]">
                        {{ $req['min_pages'] }}{{ $req['max_pages'] ? '-' . $req['max_pages'] : '+' }} hal
                    </span>
                </div>
            </div>

            <div class="pt-5 border-t border-[#EEF0F7]">
                <h4 class="text-sm font-bold text-[#111827] mb-3">Checklist Kelengkapan:</h4>
                <ul class="space-y-2">
                    @foreach($req['checklist'] as $item)
                    <li class="flex items-start gap-2 text-xs text-[#6B7280]">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Format Guidelines --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-16 sm:mt-20">
    <div class="p-6 border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl sm:p-8">
        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mb-6 flex items-center gap-3">
            <span class="text-3xl">🎨</span>
            Panduan Format Penulisan
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="p-4 bg-white border border-blue-200 rounded-xl">
                <h4 class="font-bold text-[#111827] mb-2">Font</h4>
                <p class="text-sm text-[#6B7280]">{{ $format_guidelines['font'] }}</p>
            </div>
            <div class="p-4 bg-white border border-blue-200 rounded-xl">
                <h4 class="font-bold text-[#111827] mb-2">Ukuran Font</h4>
                <p class="text-sm text-[#6B7280]">{{ $format_guidelines['font_size'] }}</p>
            </div>
            <div class="p-4 bg-white border border-blue-200 rounded-xl">
                <h4 class="font-bold text-[#111827] mb-2">Spasi</h4>
                <p class="text-sm text-[#6B7280]">{{ $format_guidelines['spacing'] }}</p>
            </div>
            <div class="p-4 bg-white border border-blue-200 rounded-xl">
                <h4 class="font-bold text-[#111827] mb-2">Margin</h4>
                <p class="text-sm text-[#6B7280]">{{ $format_guidelines['margins'] }}</p>
            </div>
            <div class="p-4 bg-white border border-blue-200 rounded-xl sm:col-span-2">
                <h4 class="font-bold text-[#111827] mb-2">Sistem Sitasi</h4>
                <p class="text-sm text-[#6B7280]">{{ $format_guidelines['citation'] }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Review Criteria --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-16 sm:mt-20">
    <div class="mb-10 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#111827] mb-3">
            ⭐ Kriteria Review
        </h2>
        <p class="text-sm sm:text-base text-[#6B7280] max-w-2xl mx-auto">
            Naskah akan dinilai berdasarkan kriteria berikut
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($review_criteria as $index => $criteria)
        <div
            class="bg-white rounded-xl border-2 border-[#EEF0F7] p-5 hover:border-[#FF6B18] hover:shadow-lg transition-all duration-300">
            <div class="flex items-start gap-3">
                <div
                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-black text-white">{{ $index + 1 }}</span>
                </div>
                <p class="text-sm text-[#6B7280] leading-relaxed">{{ $criteria }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Tips --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-16 sm:mt-20">
    <div class="mb-10 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#111827] mb-3">
            💡 Tips Sukses Submit
        </h2>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($tips as $tip)
        <div
            class="p-6 text-center transition-all duration-300 border-2 border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl hover:shadow-xl hover:-translate-y-1">
            <div class="mb-3 text-4xl">{{ $tip['icon'] }}</div>
            <h3 class="text-base font-bold text-[#111827] mb-2">{{ $tip['title'] }}</h3>
            <p class="text-sm text-[#6B7280]">{{ $tip['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-16 sm:mt-20 mb-12">
    <div class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-2xl p-8 sm:p-12 text-center text-white">
        <h2 class="mb-4 text-2xl font-black sm:text-3xl lg:text-4xl">
            Siap Submit Naskahmu?
        </h2>
        <p class="mb-8 text-base sm:text-lg opacity-90">
            Daftar sekarang dan mulai publikasikan karya ilmiahmu
        </p>
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('register') }}"
                class="px-8 py-4 bg-white text-[#FF6B18] text-base font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                Daftar Sekarang
            </a>
            <a href="{{ route('home') }}"
                class="px-8 py-4 text-base font-bold text-white transition-all duration-300 border-2 bg-white/20 backdrop-blur-sm rounded-xl border-white/30 hover:bg-white/30">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</section>

@endsection
