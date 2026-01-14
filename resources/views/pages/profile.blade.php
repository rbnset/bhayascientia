@extends('layouts.app')

@section('title', 'Profile')
@section('main_class', 'mt-0 pb-16')

@section('content')
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10">
    <div class="p-12 text-center bg-white border rounded-2xl border-[#EEF0F7]">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-[#F8F9FC] flex items-center justify-center">
            <svg class="w-10 h-10 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-[#1A1A1A] mb-2">Profile Page</h1>
        <p class="text-[#737373] mb-6">Halaman profile akan dikembangkan nanti</p>
        <a href="{{ route('publikasi') }}"
            class="inline-flex items-center gap-2 px-6 py-3 font-bold text-white transition-all duration-200 rounded-xl bg-[#FF6B18] hover:-translate-y-1 hover:shadow-[0_10px_20px_0_#FF6B1880]">
            Kembali ke Publikasi
        </a>
    </div>
</section>
@endsection
