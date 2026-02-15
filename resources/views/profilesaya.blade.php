@extends('layouts.app')

@section('title', 'Profil Saya - ' . auth()->user()->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#F8F9FC] via-white to-[#FFF7F2] py-8 sm:py-12">
    <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">

        {{-- Alert Messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="flex items-start gap-3 p-4 mb-6 border border-green-200 bg-green-50 rounded-xl">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-green-600 transition-colors hover:text-green-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="flex items-start gap-3 p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="text-red-600 transition-colors hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif

        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('home') }}" class="text-[#737373] hover:text-[#FF6B18] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Profil Saya</h1>
            </div>
            <p class="text-[#737373]">Kelola informasi profil dan keamanan akun Anda</p>
        </div>

        {{-- Profile Overview Card --}}
        <div class="mb-6 bg-white rounded-2xl shadow-lg border border-[#EEF0F7] overflow-hidden">
            <div class="relative h-32 bg-gradient-to-r from-[#FF6B18] to-[#E64627]">
                <div
                    class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-30">
                </div>
            </div>

            <div class="px-6 pb-6 -mt-16">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0">
                        <img src="{{ $user->photo_url }}" alt="{{ $user->name }}"
                            class="object-cover w-32 h-32 border-4 border-white shadow-xl rounded-2xl">
                        @if($user->isEmailVerified())
                        <div class="absolute flex items-center justify-center w-8 h-8 bg-green-500 border-4 border-white rounded-full shadow-lg bottom-2 right-2"
                            title="Email Terverifikasi">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- User Info --}}
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold text-[#1A1A1A] mb-1 truncate">{{ $user->name }}</h2>
                        <p class="text-[#737373] mb-2 truncate">{{ $user->email }}</p>

                        <div class="flex flex-wrap gap-2">
                            @if($user->job_title)
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 bg-[#FFF7F2] text-[#FF6B18] rounded-full text-sm font-medium">
                                <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate">{{ $user->job_title }}</span>
                            </div>
                            @endif

                            @if($user->isSocialLogin())
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-blue-700 rounded-full bg-blue-50">
                                <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                                <span>{{ $user->provider_name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="flex gap-4 sm:gap-6 sm:ml-auto">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-[#FF6B18]">{{ $publicationsCount }}</div>
                            <div class="text-xs text-[#737373]">Publikasi</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-[#FF6B18]">{{ $savedCount }}</div>
                            <div class="text-xs text-[#737373]">Tersimpan</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-[#FF6B18]">{{ $favoritesCount }}</div>
                            <div class="text-xs text-[#737373]">Favorit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left Sidebar - Navigation --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-[#EEF0F7] p-4 sticky top-24"
                    x-data="{ activeTab: 'info' }">
                    <nav class="space-y-1">
                        <button @click="activeTab = 'info'"
                            :class="activeTab === 'info' ? 'bg-[#FFF7F2] text-[#FF6B18] border-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC] border-transparent'"
                            class="flex items-center w-full gap-3 px-4 py-3 font-medium transition-all border-l-4 rounded-xl">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Informasi Pribadi</span>
                        </button>

                        <button @click="activeTab = 'photo'"
                            :class="activeTab === 'photo' ? 'bg-[#FFF7F2] text-[#FF6B18] border-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC] border-transparent'"
                            class="flex items-center w-full gap-3 px-4 py-3 font-medium transition-all border-l-4 rounded-xl">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Foto Profil</span>
                        </button>

                        <button @click="activeTab = 'security'"
                            :class="activeTab === 'security' ? 'bg-[#FFF7F2] text-[#FF6B18] border-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC] border-transparent'"
                            class="flex items-center w-full gap-3 px-4 py-3 font-medium transition-all border-l-4 rounded-xl">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Keamanan</span>
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Right Content Area --}}
            <div class="lg:col-span-2" x-data="{ activeTab: 'info' }">

                {{-- Informasi Pribadi Tab --}}
                <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-white rounded-2xl shadow-lg border border-[#EEF0F7] p-6">
                    <h3 class="text-xl font-bold text-[#1A1A1A] mb-6">Informasi Pribadi</h3>

                    <form action="{{ route('profil.update') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('email') border-red-500 @enderror">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Job Title --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Jabatan/Posisi</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                placeholder="Contoh: Dosen, Peneliti, Mahasiswa"
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('job_title') border-red-500 @enderror">
                            @error('job_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Affiliation --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Afiliasi/Institusi</label>
                            <input type="text" name="affiliation" value="{{ old('affiliation', $user->affiliation) }}"
                                placeholder="Contoh: Universitas Indonesia"
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('affiliation') border-red-500 @enderror">
                            @error('affiliation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Nomor WhatsApp</label>
                            <input type="text" name="whatsapp_number"
                                value="{{ old('whatsapp_number', $user->whatsapp_number) }}" placeholder="+62xxxxxxxxxx"
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('whatsapp_number') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-[#737373]">Format: +62 diikuti nomor tanpa 0 di depan</p>
                            @error('whatsapp_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bio --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Bio</label>
                            <textarea name="bio" rows="4" maxlength="500"
                                placeholder="Ceritakan sedikit tentang diri Anda..."
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all resize-none @error('bio') border-red-500 @enderror">{{ old('bio', $user->bio) }}</textarea>
                            <p class="mt-1 text-xs text-[#737373]">Maksimal 500 karakter</p>
                            @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Foto Profil Tab --}}
                <div x-show="activeTab === 'photo'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-white rounded-2xl shadow-lg border border-[#EEF0F7] p-6">
                    <h3 class="text-xl font-bold text-[#1A1A1A] mb-6">Foto Profil</h3>

                    <div class="flex flex-col items-center">
                        <div class="relative mb-6">
                            <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" id="previewPhoto"
                                class="w-40 h-40 rounded-2xl object-cover border-4 border-[#EEF0F7] shadow-lg">

                            @if($user->profile_photo)
                            <button type="button"
                                onclick="document.getElementById('deletePhotoModal').classList.remove('hidden')"
                                class="absolute flex items-center justify-center w-8 h-8 text-white transition-all bg-red-500 rounded-full shadow-lg -top-2 -right-2 hover:bg-red-600"
                                title="Hapus foto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>

                        <form action="{{ route('profil.updatePhoto') }}" method="POST" enctype="multipart/form-data"
                            class="w-full max-w-md">
                            @csrf

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Upload Foto Baru</label>
                                <input type="file" name="profile_photo" accept="image/*" required
                                    onchange="previewImage(event)"
                                    class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#FFF7F2] file:text-[#FF6B18] hover:file:bg-[#FFE5D6] cursor-pointer">
                                <p class="mt-2 text-xs text-[#737373]">Format: JPEG, PNG, JPG, WEBP. Maksimal 2MB</p>
                                @error('profile_photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                class="w-full px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload Foto
                            </button>
                        </form>

                        @if($user->isSocialLogin() && $user->avatar)
                        <div class="w-full max-w-md p-4 mt-6 border border-blue-200 bg-blue-50 rounded-xl">
                            <p class="text-sm text-blue-800">
                                <strong>Info:</strong> Anda login menggunakan {{ $user->provider_name }}.
                                Foto dari {{ $user->provider_name }} akan otomatis digunakan jika Anda tidak mengupload
                                foto manual.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Keamanan Tab --}}
                <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-white rounded-2xl shadow-lg border border-[#EEF0F7] p-6">
                    <h3 class="text-xl font-bold text-[#1A1A1A] mb-6">Keamanan Akun</h3>

                    @if($user->hasPassword())
                    <form action="{{ route('profil.updatePassword') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Current Password --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">
                                Password Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('current_password') border-red-500 @enderror">
                            @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- New Password --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all @error('password') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-[#737373]">Minimal 8 karakter</p>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1A1A1A] mb-2">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-3 bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent transition-all">
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="mb-1 text-sm font-medium text-blue-800">Login via Social Media</p>
                                <p class="text-sm text-blue-700">
                                    Anda login menggunakan {{ $user->provider_name }}. Tidak dapat mengubah password
                                    untuk akun social login.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Account Info --}}
                    <div class="mt-8 p-4 bg-[#F8F9FC] rounded-xl">
                        <h4 class="font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informasi Akun
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-[#EEF0F7]">
                                <span class="text-[#737373]">Status Email:</span>
                                <span
                                    class="font-medium {{ $user->isEmailVerified() ? 'text-green-600' : 'text-yellow-600' }} flex items-center gap-1">
                                    @if($user->isEmailVerified())
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    @endif
                                    {{ $user->isEmailVerified() ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-[#EEF0F7]">
                                <span class="text-[#737373]">Metode Login:</span>
                                <span class="font-medium text-[#1A1A1A]">{{ $user->provider_name }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-[#737373]">Bergabung Sejak:</span>
                                <span class="font-medium text-[#1A1A1A]">{{ $user->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Delete Photo Modal --}}
<div id="deletePhotoModal"
    class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-black/50 backdrop-blur-sm">
    <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-[#1A1A1A]">Hapus Foto Profil</h3>
                <p class="text-sm text-[#737373]">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>

        <p class="text-sm text-[#737373] mb-6">
            Apakah Anda yakin ingin menghapus foto profil? Foto default akan digunakan sebagai gantinya.
        </p>

        <div class="flex gap-3">
            <button onclick="document.getElementById('deletePhotoModal').classList.add('hidden')"
                class="flex-1 px-4 py-2.5 bg-[#F8F9FC] text-[#1A1A1A] font-medium rounded-xl hover:bg-[#EEF0F7] transition-all">
                Batal
            </button>
            <form action="{{ route('profil.deletePhoto') }}" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-red-500 text-white font-medium rounded-xl hover:bg-red-600 transition-all">
                    Hapus Foto
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('previewPhoto');
            if (preview) {
                preview.src = reader.result;
            }
        }
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endpush
@endsection