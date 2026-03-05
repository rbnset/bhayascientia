@extends('layouts.app')

@section('title', 'Profil Saya - ' . auth()->user()->name)

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="subscription.index" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" :showSearch="false" />
@endsection


@section('content')

{{-- ✨ Anchor scroll ke atas --}}
<div id="top-anchor"></div>

<div x-data="profilePage()" class="min-h-screen bg-gradient-to-br from-[#F8F9FC] via-white to-[#FFF7F2] py-8 sm:py-12">
    <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">

        {{-- ===================== ALERT MESSAGES ===================== --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-2"
            class="flex items-start gap-3 p-4 mb-6 border border-green-200 shadow-sm bg-green-50 rounded-2xl">
            <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-green-100 rounded-full">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <p class="flex-1 pt-1 text-sm font-medium text-green-800">{{ session('success') }}</p>
            <button @click="show = false" class="p-1 text-green-400 transition-colors hover:text-green-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-2"
            class="flex items-start gap-3 p-4 mb-6 border border-red-200 shadow-sm bg-red-50 rounded-2xl">
            <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-red-100 rounded-full">
                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <p class="flex-1 pt-1 text-sm font-medium text-red-800">{{ session('error') }}</p>
            <button @click="show = false" class="p-1 text-red-400 transition-colors hover:text-red-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
        <div class="flex items-start gap-3 p-4 mb-6 border border-red-200 shadow-sm bg-red-50 rounded-2xl">
            <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-red-100 rounded-full">
                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="mb-1 text-sm font-bold text-red-800">Terdapat kesalahan:</p>
                <ul class="text-sm text-red-700 space-y-0.5 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- ===================== PAGE HEADER ===================== --}}
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-black text-[#1A1A1A]">Profil Saya</h1>
            <p class="text-sm text-[#737373]">Kelola informasi profil dan keamanan akun Anda</p>
        </div>

        {{-- ===================== PROFILE OVERVIEW CARD ===================== --}}
        <div class="mb-6 bg-white rounded-2xl shadow-sm border border-[#EEF0F7] overflow-hidden">
            {{-- Cover --}}
            <div class="relative h-28 sm:h-36 bg-gradient-to-r from-[#FF6B18] to-[#E64627]">
                <div class="absolute inset-0 opacity-20"
                    style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')">
                </div>
                {{-- Decorative circles --}}
                <div class="absolute w-20 h-20 rounded-full top-4 right-8 bg-white/10"></div>
                <div class="absolute w-32 h-32 rounded-full -top-4 right-24 bg-white/5"></div>
            </div>

            <div class="px-4 pb-5 sm:px-6 -mt-14 sm:-mt-16">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">

                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0">
                        <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" id="headerAvatar"
                            class="object-cover w-24 h-24 bg-white border-4 border-white shadow-xl sm:w-32 sm:h-32 rounded-2xl">
                        @if($user->isEmailVerified())
                        <div class="absolute flex items-center justify-center w-6 h-6 bg-green-500 border-2 border-white rounded-full shadow sm:w-7 sm:h-7 bottom-1 right-1"
                            title="Email Terverifikasi">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- User Info --}}
                    <div class="flex-1 min-w-0 sm:pb-1">
                        <h2 class="text-xl sm:text-2xl font-black text-[#1A1A1A] mb-0.5 truncate">{{ $user->name }}</h2>
                        <p class="text-sm text-[#737373] mb-2 truncate">{{ $user->email }}</p>
                        <div class="flex flex-wrap gap-2">
                            @if($user->job_title)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FFF7F2] text-[#FF6B18]
                                rounded-full text-xs font-semibold border border-[#FFE2D2]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $user->job_title }}
                            </span>
                            @endif
                            @if($user->affiliation)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F8F9FC] text-[#737373]
                                rounded-full text-xs font-semibold border border-[#EEF0F7]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ $user->affiliation }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="flex gap-1 sm:gap-2 sm:ml-auto sm:pb-1">
                        @foreach([
                        ['value' => $publicationsCount, 'label' => 'Publikasi'],
                        ['value' => $savedCount, 'label' => 'Tersimpan'],
                        ['value' => $favoritesCount, 'label' => 'Favorit'],
                        ] as $stat)
                        <div
                            class="flex-1 sm:flex-none text-center px-3 sm:px-4 py-2 bg-[#FFF7F2] rounded-xl border border-[#FFE2D2]">
                            <div class="text-lg sm:text-2xl font-black text-[#FF6B18]">{{ $stat['value'] }}</div>
                            <div class="text-[10px] sm:text-xs text-[#737373] font-medium">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ===================== MAIN CONTENT ===================== --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-[#EEF0F7] p-3 sticky top-24">
                    <p class="px-3 pt-1 pb-2 text-[10px] font-black text-[#A3A6AE] uppercase tracking-widest">
                        Menu Profil
                    </p>
                    <nav class="space-y-1">
                        @foreach([
                        ['tab' => 'info', 'label' => 'Informasi Pribadi', 'icon' => 'user'],
                        ['tab' => 'photo', 'label' => 'Foto Profil', 'icon' => 'photo'],
                        ['tab' => 'security', 'label' => 'Keamanan', 'icon' => 'lock'],
                        ] as $nav)
                        <button @click="setTab('{{ $nav['tab'] }}')" :class="activeTab === '{{ $nav['tab'] }}'
                                ? 'bg-[#FFF7F2] text-[#FF6B18] border-[#FF6B18] shadow-sm'
                                : 'text-[#737373] hover:bg-[#F8F9FC] border-transparent hover:text-[#1A1A1A]'" class="flex items-center w-full gap-3 px-4 py-3 text-sm font-semibold
                                transition-all duration-200 border-l-[3px] rounded-xl text-left">

                            @if($nav['icon'] === 'user')
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            @elseif($nav['icon'] === 'photo')
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            @else
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            @endif

                            <span>{{ $nav['label'] }}</span>

                            {{-- Active indicator arrow --}}
                            <svg x-show="activeTab === '{{ $nav['tab'] }}'" class="w-4 h-4 ml-auto text-[#FF6B18]"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        @endforeach
                    </nav>

                    {{-- Divider --}}
                    <div class="mx-3 my-3 border-t border-[#EEF0F7]"></div>

                    {{-- Quick Info --}}
                    <div class="px-3 pb-1 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#A3A6AE]">Status Email</span>
                            <span
                                class="font-semibold {{ $user->isEmailVerified() ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $user->isEmailVerified() ? '✓ Terverifikasi' : '⚠ Belum' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#A3A6AE]">Login via</span>
                            <span class="font-semibold text-[#1A1A1A]">{{ $user->provider_name ?? 'Email' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#A3A6AE]">Bergabung</span>
                            <span class="font-semibold text-[#1A1A1A]">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT CONTENT ===== --}}
            <div class="lg:col-span-2">

                {{-- ============ TAB: INFORMASI PRIBADI ============ --}}
                <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="bg-white rounded-2xl shadow-sm border border-[#EEF0F7] overflow-hidden">

                    {{-- Card Header --}}
                    <div class="px-6 py-4 border-b border-[#EEF0F7] bg-gradient-to-r from-[#FFF7F2] to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627]
                                flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-[#1A1A1A]">Informasi Pribadi</h3>
                                <p class="text-xs text-[#737373]">Perbarui data diri Anda</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('profil.update') }}" method="POST" class="p-6 space-y-5">
                        @csrf

                        {{-- Name & Email Row --}}
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    placeholder="Nama lengkap Anda" class="w-full px-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                        focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                        transition-all duration-200 outline-none
                                        @error('name') border-red-400 bg-red-50 @enderror">
                                @error('name')
                                <p class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    placeholder="email@example.com" class="w-full px-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                        focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                        transition-all duration-200 outline-none
                                        @error('email') border-red-400 bg-red-50 @enderror">
                                @error('email')
                                <p class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Job Title & Affiliation Row --}}
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                    Jabatan / Posisi
                                </label>
                                <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                    placeholder="Dosen, Peneliti, Mahasiswa..." class="w-full px-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                        focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                        transition-all duration-200 outline-none
                                        @error('job_title') border-red-400 bg-red-50 @enderror">
                                @error('job_title')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                    Afiliasi / Institusi
                                </label>
                                <input type="text" name="affiliation"
                                    value="{{ old('affiliation', $user->affiliation) }}"
                                    placeholder="Universitas Indonesia..." class="w-full px-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                        focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                        transition-all duration-200 outline-none
                                        @error('affiliation') border-red-400 bg-red-50 @enderror">
                                @error('affiliation')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                Nomor WhatsApp
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <svg class="w-4 h-4 text-[#A3A6AE]" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                        <path
                                            d="M11.95 0C5.354 0 0 5.354 0 11.95c0 2.087.544 4.04 1.495 5.737L0 23.9l6.385-1.673A11.898 11.898 0 0011.95 23.9C18.546 23.9 23.9 18.546 23.9 11.95S18.546 0 11.95 0zm0 21.81a9.853 9.853 0 01-5.024-1.377l-.36-.214-3.73.978.996-3.644-.235-.375A9.826 9.826 0 012.09 11.95c0-5.44 4.42-9.86 9.86-9.86 5.44 0 9.86 4.42 9.86 9.86 0 5.44-4.42 9.86-9.86 9.86z" />
                                    </svg>
                                </div>
                                <input type="text" name="whatsapp_number"
                                    value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                                    placeholder="+628123456789" class="w-full pl-11 pr-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                        focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                        transition-all duration-200 outline-none
                                        @error('whatsapp_number') border-red-400 bg-red-50 @enderror">
                            </div>
                            <p class="mt-1 text-xs text-[#A3A6AE]">Format: +62 diikuti nomor tanpa 0 di depan</p>
                            @error('whatsapp_number')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bio --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                Bio
                            </label>
                            <textarea name="bio" rows="4" maxlength="500"
                                placeholder="Ceritakan sedikit tentang diri Anda..." x-data="charCounter(500)"
                                @input="count($event.target.value)"
                                class="w-full px-4 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                    focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                    transition-all duration-200 outline-none resize-none
                                    @error('bio') border-red-400 bg-red-50 @enderror">{{ old('bio', $user->bio) }}</textarea>
                            <div class="flex items-center justify-between mt-1">
                                @error('bio')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                                @else
                                <span></span>
                                @enderror
                                <span class="text-xs text-[#A3A6AE]" x-data="charCounter(500)"
                                    x-init="count(document.querySelector('[name=bio]').value)">
                                    <span x-text="remaining"></span>/500
                                </span>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center justify-between pt-2 border-t border-[#EEF0F7]">
                            <p class="text-xs text-[#A3A6AE]">
                                <span class="text-red-400">*</span> Wajib diisi
                            </p>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r
                                    from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl
                                    hover:shadow-lg hover:shadow-[#FF6B18]/25 hover:-translate-y-0.5
                                    active:translate-y-0 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============ TAB: FOTO PROFIL ============ --}}
                <div x-show="activeTab === 'photo'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="bg-white rounded-2xl shadow-sm border border-[#EEF0F7] overflow-hidden">

                    {{-- Card Header --}}
                    <div class="px-6 py-4 border-b border-[#EEF0F7] bg-gradient-to-r from-[#FFF7F2] to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627]
                                flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-[#1A1A1A]">Foto Profil</h3>
                                <p class="text-xs text-[#737373]">Upload atau ubah foto profil Anda</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6" x-data="photoUploader()">

                        {{-- Preview Area --}}
                        <div class="flex flex-col items-center gap-6 mb-6 sm:flex-row sm:items-start">

                            {{-- Avatar Preview --}}
                            <div class="relative flex-shrink-0">
                                <div
                                    class="relative w-36 h-36 rounded-2xl overflow-hidden border-4 border-[#EEF0F7] shadow-lg">
                                    <img :src="preview || '{{ $user->photo_url }}'" id="previewPhoto"
                                        alt="{{ $user->name }}" class="object-cover w-full h-full">
                                    {{-- Overlay saat hover --}}
                                    <label for="photoInput"
                                        class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-200 cursor-pointer bg-black/0 hover:bg-black/40 group">
                                        <svg class="text-white transition-opacity opacity-0 w-7 h-7 group-hover:opacity-100"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span
                                            class="mt-1 text-xs font-bold text-white transition-opacity opacity-0 group-hover:opacity-100">
                                            Ganti
                                        </span>
                                    </label>
                                </div>

                                {{-- Delete button --}}
                                @if($user->profile_photo)
                                <button type="button" @click="showDeleteModal = true"
                                    class="absolute flex items-center justify-center transition-all duration-200 bg-red-500 border-2 border-white rounded-full shadow-lg -top-2 -right-2 w-7 h-7 hover:bg-red-600 hover:scale-110"
                                    title="Hapus foto">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                @endif
                            </div>

                            {{-- Upload Info --}}
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="font-bold text-[#1A1A1A] mb-1">{{ $user->name }}</h4>
                                <p class="text-sm text-[#737373] mb-4">{{ $user->email }}</p>
                                <div class="space-y-1.5 text-xs text-[#737373]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-[#FF6B18]"></div>
                                        Format: JPEG, PNG, JPG, WEBP
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-[#FF6B18]"></div>
                                        Ukuran maksimal: 2MB
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-[#FF6B18]"></div>
                                        Rekomendasi: 400×400px atau lebih besar
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Upload Form --}}
                        <form action="{{ route('profil.updatePhoto') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Drop Zone --}}
                            <div class="relative border-2 border-dashed border-[#EEF0F7] rounded-2xl p-6 text-center
                                    hover:border-[#FF6B18] hover:bg-[#FFF7F2]/50 transition-all duration-200 cursor-pointer"
                                @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop($event)"
                                :class="isDragging ? 'border-[#FF6B18] bg-[#FFF7F2]/50 scale-[1.01]' : ''"
                                @click="$refs.fileInput.click()">

                                <input type="file" name="profile_photo" accept="image/*" required id="photoInput"
                                    x-ref="fileInput" @change="handleFile($event)" class="hidden">

                                <div x-show="!selectedFile">
                                    <div
                                        class="w-12 h-12 mx-auto mb-3 bg-[#FFF7F2] rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#1A1A1A] mb-1">
                                        Klik atau drag & drop foto di sini
                                    </p>
                                    <p class="text-xs text-[#A3A6AE]">JPEG, PNG, WEBP hingga 2MB</p>
                                </div>

                                <div x-show="selectedFile" class="flex items-center justify-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-xl">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-semibold text-[#1A1A1A]" x-text="selectedFile?.name"></p>
                                        <p class="text-xs text-[#737373]" x-text="fileSize"></p>
                                    </div>
                                    <button type="button" @click.stop="clearFile()"
                                        class="flex items-center justify-center w-6 h-6 ml-2 transition-colors bg-red-100 rounded-full hover:bg-red-200">
                                        <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @error('profile_photo')
                            <p class="flex items-center gap-1 mt-2 text-xs text-red-500">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror

                            <button type="submit" x-show="selectedFile"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 w-full inline-flex items-center justify-center gap-2
                                    px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627]
                                    text-white text-sm font-bold rounded-xl
                                    hover:shadow-lg hover:shadow-[#FF6B18]/25 hover:-translate-y-0.5
                                    active:translate-y-0 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload Foto Profil
                            </button>
                        </form>

                        @if($user->isSocialLogin() && $user->avatar)
                        <div class="p-4 mt-4 border border-blue-200 bg-blue-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-xs text-blue-800">
                                    Anda login via <strong>{{ $user->provider_name }}</strong>.
                                    Foto dari {{ $user->provider_name }} digunakan jika tidak ada foto manual.
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Delete Photo Modal --}}
                        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            @keydown.escape.window="showDeleteModal = false"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                            style="display:none;">
                            <div x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" @click.outside="showDeleteModal = false"
                                class="w-full max-w-sm p-6 bg-white shadow-2xl rounded-2xl">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-xl">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-black text-[#1A1A1A]">Hapus Foto Profil?</h3>
                                        <p class="text-xs text-[#737373]">Tindakan ini tidak dapat dibatalkan</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#737373] mb-5">
                                    Foto akan diganti dengan avatar default berdasarkan nama Anda.
                                </p>
                                <div class="flex gap-3">
                                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-[#F8F9FC] text-[#1A1A1A] text-sm
                                            font-semibold rounded-xl hover:bg-[#EEF0F7] transition-all">
                                        Batal
                                    </button>
                                    <form action="{{ route('profil.deletePhoto') }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white
                                                text-sm font-bold rounded-xl transition-all">
                                            Ya, Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ============ TAB: KEAMANAN ============ --}}
                <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2" class="space-y-4">

                    {{-- Password Card --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EEF0F7] overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#EEF0F7] bg-gradient-to-r from-[#FFF7F2] to-white">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627]
                                    flex items-center justify-center shadow-sm">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-[#1A1A1A]">Ubah Password</h3>
                                    <p class="text-xs text-[#737373]">Pastikan password kuat dan unik</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            @if($user->hasPassword())
                            <form action="{{ route('profil.updatePassword') }}" method="POST" x-data="passwordForm()"
                                class="space-y-5">
                                @csrf

                                {{-- Current Password --}}
                                <div>
                                    <label
                                        class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                        Password Saat Ini <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showCurrent ? 'text' : 'password'" name="current_password"
                                            required placeholder="Masukkan password saat ini" class="w-full pl-4 pr-12 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
            focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
            transition-all duration-200 outline-none
            @error('current_password') border-red-400 bg-red-50 @enderror">
                                        <button type="button" @click="showCurrent = !showCurrent"
                                            class="absolute inset-y-0 right-0 flex items-center px-4 text-[#A3A6AE] hover:text-[#FF6B18] transition-colors">
                                            <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('current_password')
                                    <p class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>

                                {{-- New Password --}}
                                <div>
                                    <label
                                        class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                        Password Baru <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showNew ? 'text' : 'password'" name="password" required
                                            @input="checkStrength($event.target.value)" placeholder="Minimal 8 karakter"
                                            class="w-full pl-4 pr-12 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                                focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                                transition-all duration-200 outline-none
                                                @error('password') border-red-400 bg-red-50 @enderror">
                                        <button type="button" @click="showNew = !showNew"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[#A3A6AE] hover:text-[#FF6B18] transition-colors">
                                            <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Password Strength --}}
                                    <div x-show="strength > 0" class="mt-2 space-y-1.5">
                                        <div class="flex gap-1">
                                            <div class="flex-1 h-1 transition-all duration-300 rounded-full"
                                                :class="strength >= 1 ? (strength <= 1 ? 'bg-red-400' : strength <= 2 ? 'bg-yellow-400' : 'bg-green-400') : 'bg-[#EEF0F7]'">
                                            </div>
                                            <div class="flex-1 h-1 transition-all duration-300 rounded-full"
                                                :class="strength >= 2 ? (strength <= 2 ? 'bg-yellow-400' : 'bg-green-400') : 'bg-[#EEF0F7]'">
                                            </div>
                                            <div class="flex-1 h-1 transition-all duration-300 rounded-full"
                                                :class="strength >= 3 ? 'bg-green-400' : 'bg-[#EEF0F7]'"></div>
                                            <div class="flex-1 h-1 transition-all duration-300 rounded-full"
                                                :class="strength >= 4 ? 'bg-green-500' : 'bg-[#EEF0F7]'"></div>
                                        </div>
                                        <p class="text-xs font-medium" :class="{
                                                'text-red-500': strength <= 1,
                                                'text-yellow-500': strength === 2,
                                                'text-green-500': strength >= 3
                                            }" x-text="strengthLabel"></p>
                                    </div>

                                    @error('password')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div>
                                    <label
                                        class="block text-xs font-bold text-[#1A1A1A] mb-1.5 uppercase tracking-wide">
                                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                            required placeholder="Ulangi password baru" class="w-full pl-4 pr-12 py-3 text-sm bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl
                                                focus:ring-2 focus:ring-[#FF6B18]/30 focus:border-[#FF6B18] focus:bg-white
                                                transition-all duration-200 outline-none">
                                        <button type="button" @click="showConfirm = !showConfirm"
                                            class="absolute inset-y-0 right-0 flex items-center px-4 text-[#A3A6AE] hover:text-[#FF6B18] transition-colors">
                                            <svg x-show=" !showConfirm" class="w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-[#EEF0F7]">
                                    <p class="text-xs text-[#A3A6AE]">Minimal 8 karakter</p>
                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r
                                            from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl
                                            hover:shadow-lg hover:shadow-[#FF6B18]/25 hover:-translate-y-0.5
                                            active:translate-y-0 transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                            @else
                            <div class="p-4 border border-blue-200 bg-blue-50 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="mb-1 text-sm font-bold text-blue-800">Login via Social Media</p>
                                        <p class="text-xs text-blue-700">
                                            Anda login menggunakan <strong>{{ $user->provider_name }}</strong>.
                                            Password tidak dapat diubah untuk akun social login.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Account Info Card --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EEF0F7] overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#EEF0F7]">
                            <h4 class="text-sm font-black text-[#1A1A1A] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Informasi Akun
                            </h4>
                        </div>
                        <div class="p-6 space-y-0">
                            @foreach([
                            ['label' => 'Status Email', 'value' => $user->isEmailVerified() ? 'Terverifikasi ✓' : 'Belum
                            Terverifikasi', 'color' => $user->isEmailVerified() ? 'text-green-600' : 'text-yellow-600'],
                            ['label' => 'Metode Login', 'value' => $user->provider_name ?? 'Email & Password', 'color'
                            => 'text-[#1A1A1A]'],
                            ['label' => 'Bergabung', 'value' => $user->created_at->locale('id')->isoFormat('D MMMM
                            YYYY'), 'color' => 'text-[#1A1A1A]'],
                            ['label' => 'Terakhir Login','value' => $user->updated_at->locale('id')->diffForHumans(),
                            'color' => 'text-[#1A1A1A]'],
                            ] as $info)
                            <div class="flex items-center justify-between py-3 border-b border-[#F8F9FC] last:border-0">
                                <span class="text-sm text-[#737373]">{{ $info['label'] }}</span>
                                <span class="text-sm font-semibold {{ $info['color'] }}">{{ $info['value'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                {{-- end security --}}

            </div>
        </div>
    </div>
</div>

{{-- ✨ Scroll to Top --}}
<x-scroll-to-top />


@push('scripts')

{{-- ✨ Scroll to Top Script --}}
<x-scroll-to-top-script />

<script>
    // ✅ Detect tab dari session (setelah redirect error validasi)
    function profilePage() {
        return {
            activeTab: '{{ session("active_tab", "info") }}',
            setTab(tab) {
                this.activeTab = tab;
                // Update URL hash untuk bookmark
                history.replaceState(null, '', '#' + tab);
            },
            init() {
                // Baca hash dari URL
                const hash = window.location.hash.replace('#', '');
                if (['info', 'photo', 'security'].includes(hash)) {
                    this.activeTab = hash;
                }
                // Jika ada error validasi, buka tab yang sesuai
                @if($errors->hasAny(['name', 'email', 'job_title', 'affiliation', 'whatsapp_number', 'bio']))
                    this.activeTab = 'info';
                @elseif($errors->has('profile_photo'))
                    this.activeTab = 'photo';
                @elseif($errors->hasAny(['current_password', 'password']))
                    this.activeTab = 'security';
                @endif
            }
        }
    }

    // ✅ Photo uploader
    function photoUploader() {
        return {
            preview: null,
            selectedFile: null,
            fileSize: '',
            isDragging: false,
            showDeleteModal: false,
            handleFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.processFile(file);
            },
            handleDrop(event) {
                this.isDragging = false;
                const file = event.dataTransfer.files[0];
                if (!file) return;
                // Inject ke input file
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.fileInput.files = dt.files;
                this.processFile(file);
            },
            processFile(file) {
                this.selectedFile = file;
                this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                    // Update header avatar juga
                    const headerAvatar = document.getElementById('headerAvatar');
                    if (headerAvatar) headerAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            clearFile() {
                this.selectedFile = null;
                this.preview = null;
                this.$refs.fileInput.value = '';
            }
        }
    }

    // ✅ Password form
    function passwordForm() {
        return {
            showCurrent: false,
            showNew: false,
            showConfirm: false,
            strength: 0,
            strengthLabel: '',
            checkStrength(val) {
                let score = 0;
                if (val.length >= 8)  score++;
                if (val.length >= 12) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                this.strength = Math.min(score, 4);
                const labels = ['', 'Sangat Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
                this.strengthLabel = labels[this.strength] || '';
            }
        }
    }

    // ✅ Char counter bio
    function charCounter(max) {
        return {
            remaining: max,
            count(val) {
                this.remaining = max - (val ? val.length : 0);
            }
        }
    }
</script>
@endpush
@endsection