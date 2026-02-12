@extends('layouts.app')

@section('title', 'Register')

@section('custom_navbar')
<x-navbar ctaLabel="Masuk" ctaRoute="login" ctaIcon="log-in" :showAvatarWhenAuth="false" :showCtaAlways="true" />
@endsection

@section('hide_footer', 'true')

@section('content')
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-8 sm:py-12 lg:py-16">
    <div class="grid items-start gap-8 lg:gap-12 lg:grid-cols-2">

        {{-- Left: Hero Copy --}}
        <div class="order-2 text-center lg:order-1 lg:text-left">
            <span
                class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs sm:text-sm font-bold text-[#FF6B18]">
                ✨ Create account
            </span>

            <h1
                class="mt-4 text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-3xl sm:leading-[40px] lg:text-4xl lg:leading-[50px]">
                Daftar akun baru <br class="hidden sm:inline" />
                di BHAYASCIENTIA
            </h1>

            <p
                class="mt-3 max-w-xl mx-auto lg:mx-0 text-sm leading-relaxed text-[#6B7280] sm:text-base sm:leading-[24px]">
                Buat akun untuk menyimpan artikel, akses fitur personal, dan menikmati pengalaman yang lebih baik.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3 mt-6 lg:justify-start">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    🏠 Kembali ke Home
                </a>
                <a href="{{ route('login') }}"
                    class="px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold rounded-full text-[#FF6B18] transition-all duration-300 hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Sudah punya akun? <span class="underline">Login</span>
                </a>
            </div>

            {{-- Benefits Card --}}
            <div class="mt-8 bg-white p-5 sm:p-6 rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm">
                <h3 class="text-base font-bold text-[#111827] mb-3">
                    ✨ Yang kamu dapatkan
                </h3>
                <ul class="space-y-2 text-sm text-[#6B7280]">
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Simpan publikasi favorit</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Ikuti kategori yang kamu suka</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Notifikasi konten terbaru</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Akses fitur premium (coming soon)</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Right: Register Card --}}
        <div class="order-1 lg:order-2">
            <div class="bg-white rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm p-5 sm:p-6 lg:p-8">

                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-bold text-[#111827] sm:text-2xl">Register</h2>
                        <p class="mt-1 text-xs sm:text-sm text-[#A3A6AE]">
                            Isi data di bawah untuk membuat akun.
                        </p>
                    </div>

                    <span
                        class="hidden sm:inline-flex items-center rounded-full bg-[#F4F6FB] px-4 py-2 text-xs font-bold text-[#111827]">
                        🔒 Secure
                    </span>
                </div>

                {{-- Error Alert --}}
                @if ($errors->any())
                <div class="p-4 mb-5 border border-red-200 animate-shake rounded-xl bg-red-50">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <ul class="space-y-1 text-sm font-medium text-red-800">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Success Alert --}}
                @if (session('success'))
                <div class="p-4 mb-5 border border-green-200 rounded-xl bg-green-50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                {{-- Social Login --}}
                <div class="grid grid-cols-1 gap-3 mb-5 sm:grid-cols-2">
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#F4F6FB] hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        <svg viewBox="0 0 48 48" class="w-5 h-5" aria-hidden="true">
                            <path fill="#FFC107"
                                d="M43.611 20.083H42V20H24v8h11.303C33.656 32.91 29.236 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" />
                            <path fill="#FF3D00"
                                d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4c-7.682 0-14.354 4.337-17.694 10.691z" />
                            <path fill="#4CAF50"
                                d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.214 0-9.62-3.06-11.283-7.438l-6.522 5.024C9.505 39.556 16.227 44 24 44z" />
                            <path fill="#1976D2"
                                d="M43.611 20.083H42V20H24v8h11.303c-.792 2.238-2.231 4.166-4.094 5.565l.003-.002 6.19 5.238C36.97 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" />
                        </svg>
                        <span class="hidden sm:inline">Google</span>
                        <span class="sm:hidden">G</span>
                    </button>

                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#F4F6FB] hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" aria-hidden="true">
                            <path fill="currentColor"
                                d="M22 12.06c0-5.52-4.48-10-10-10S2 6.54 2 12.06c0 4.99 3.66 9.13 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.89h-2.34v6.99C18.34 21.19 22 17.05 22 12.06z" />
                        </svg>
                        <span class="hidden sm:inline">Facebook</span>
                        <span class="sm:hidden">FB</span>
                    </button>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                    <p class="text-xs font-bold tracking-wide text-[#A3A6AE]">OR</p>
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                </div>

                {{-- Register Form --}}
                {{-- Register Form --}}
                <form action="{{ route('register.post') }}" method="POST" class="space-y-4" id="registerForm">
                    @csrf

                    {{-- Full Name (GABUNG firstName + lastName) --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-[#111827]">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center w-full rounded-full ring-1 ring-[#E8EBF4] px-[14px] gap-[10px] overflow-hidden bg-white transition-all duration-300 hover:ring-[#FF6B18]/60 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('name') !ring-2 !ring-red-500 @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input type="text" id="name" name="name" autocomplete="name" value="{{ old('name') }}"
                                required placeholder="Budi Santoso"
                                class="w-full py-[12px] bg-transparent text-sm font-semibold placeholder:font-normal placeholder:text-[#A3A6AE] border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none appearance-none">
                        </div>
                        @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-[#111827]">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center w-full rounded-full ring-1 ring-[#E8EBF4] px-[14px] gap-[10px] overflow-hidden bg-white transition-all duration-300 hover:ring-[#FF6B18]/60 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('email') !ring-2 !ring-red-500 @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            <input type="email" id="email" name="email" autocomplete="email" value="{{ old('email') }}"
                                required placeholder="nama@email.com"
                                class="w-full py-[12px] bg-transparent text-sm font-semibold placeholder:font-normal placeholder:text-[#A3A6AE] border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none appearance-none">
                        </div>
                        @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-[#111827]">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center w-full rounded-full ring-1 ring-[#E8EBF4] px-[14px] gap-[10px] overflow-hidden bg-white transition-all duration-300 hover:ring-[#FF6B18]/60 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('password') !ring-2 !ring-red-500 @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input type="password" id="password" name="password" autocomplete="new-password" required
                                minlength="6" placeholder="Minimal 6 karakter"
                                class="w-full py-[12px] bg-transparent text-sm font-semibold placeholder:font-normal placeholder:text-[#A3A6AE] border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none appearance-none">
                            <button type="button" id="togglePassword"
                                class="shrink-0 rounded-full px-3 py-1 text-xs font-bold text-[#6B7280] transition hover:bg-[#F4F6FB] hover:text-[#111827] focus:outline-none"
                                aria-label="Toggle password visibility" aria-pressed="false">
                                Show
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Confirmation --}}
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[#111827]">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center w-full rounded-full ring-1 ring-[#E8EBF4] px-[14px] gap-[10px] overflow-hidden bg-white transition-all duration-300 hover:ring-[#FF6B18]/60 focus-within:ring-2 focus-within:ring-[#FF6B18]">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                autocomplete="new-password" required minlength="6" placeholder="Ulangi password"
                                class="w-full py-[12px] bg-transparent text-sm font-semibold placeholder:font-normal placeholder:text-[#A3A6AE] border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none appearance-none">
                        </div>
                        <p id="pwHint" class="hidden mt-2 text-xs font-semibold text-red-600">
                            ❌ Password dan konfirmasi password tidak sama.
                        </p>
                    </div>

                    {{-- Terms Checkbox --}}
                    <div class="flex items-start gap-3">
                        <input id="terms" name="terms" type="checkbox" required
                            class="mt-1 h-4 w-4 cursor-pointer rounded border-[#E8EBF4] text-[#FF6B18] focus:ring-[#FF6B18]" />
                        <label for="terms" class="text-xs sm:text-sm font-semibold leading-relaxed text-[#111827]">
                            Saya setuju dengan
                            <button type="button" id="openTermsReg"
                                class="font-bold text-[#FF6B18] hover:underline focus:outline-none">Terms</button>
                            dan
                            <button type="button" id="openPrivacyReg"
                                class="font-bold text-[#FF6B18] hover:underline focus:outline-none">Privacy
                                Policy</button>.
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60">
                        Buat Akun
                    </button>

                    {{-- Login Link --}}
                    <p class="text-center text-xs sm:text-sm text-[#A3A6AE]">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-bold text-[#FF6B18] hover:underline">Login</a>
                    </p>
                </form>

            </div>
        </div>

    </div>
</section>

{{-- Legal Modal (Terms & Privacy) - SAMA SEPERTI LOGIN --}}
<div id="legalOverlay" class="fixed inset-0 z-[60] hidden bg-black/40 backdrop-blur-sm transition-opacity duration-300"
    aria-hidden="true"></div>

<div id="legalModal" class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto p-4" role="dialog"
    aria-modal="true" aria-labelledby="legalTitle">
    <div
        class="my-8 w-full max-w-[720px] transform overflow-hidden rounded-3xl bg-white ring-1 ring-[#EEF0F7] shadow-2xl transition-all duration-300">

        {{-- Modal Header --}}
        <div class="sticky top-0 z-10 border-b border-[#EEF0F7] bg-white">
            <div class="flex items-center justify-between gap-4 p-5 sm:p-6">
                <div class="flex-1 min-w-0">
                    <h2 id="legalTitle" class="flex items-center gap-2 text-xl font-bold text-[#111827] sm:text-2xl">
                        <span class="text-2xl">📋</span>
                        <span>Terms & Conditions</span>
                    </h2>
                    <p id="legalSubtitle" class="mt-1 text-sm text-[#6B7280]">
                        Syarat penggunaan layanan BHAYASCIENTIA.
                    </p>
                </div>

                <button id="legalCloseBtn" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] transition-all hover:bg-[#FF6B18] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-2 px-5 pb-4 sm:px-6">
                <button id="tabTerms" type="button"
                    class="rounded-full px-5 py-2.5 text-sm font-bold transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-selected="true">
                    📜 Terms
                </button>
                <button id="tabPrivacy" type="button"
                    class="rounded-full px-5 py-2.5 text-sm font-bold transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-selected="false">
                    🔒 Privacy Policy
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="max-h-[60vh] overflow-y-auto px-5 py-6 sm:px-6">

            {{-- Terms Content (SAMA SEPERTI LOGIN) --}}
            <article id="contentTerms" class="prose-sm prose max-w-none">
                <div class="bg-[#FFF7F2] rounded-2xl p-4 mb-6 border-l-4 border-[#FF6B18]">
                    <p class="text-sm leading-relaxed text-[#6B7280] m-0">
                        Dokumen ini menjelaskan syarat dan ketentuan penggunaan platform BHAYASCIENTIA untuk publikasi
                        akademik non-formal.
                    </p>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">1</span>
                            Penggunaan Layanan
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Pengguna wajib memberikan data yang benar dan valid saat registrasi.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Dilarang menyalahgunakan layanan, termasuk mencoba mengakses akun orang
                                    lain.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Layanan dapat berubah atau ditingkatkan sewaktu-waktu untuk pengalaman yang lebih
                                    baik.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">2</span>
                            Akun & Keamanan
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Jaga kerahasiaan password dan token reset Anda.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Semua aktivitas pada akun dianggap dilakukan oleh pemilik akun yang sah.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Segera hubungi kami jika ada indikasi akun Anda diakses tanpa izin.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">3</span>
                            Konten Publikasi
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Konten disediakan untuk tujuan informasi dan edukasi, bukan nasihat
                                    profesional.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Dilarang menyalin atau menyebarkan konten tanpa izin jika dilindungi hak
                                    cipta.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Penulis bertanggung jawab penuh atas konten yang dipublikasikan.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">4</span>
                            Pembatasan Tanggung Jawab
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed">
                            <p>Layanan disediakan "sebagaimana adanya". BHAYASCIENTIA tidak bertanggung jawab atas
                                kerugian tidak langsung akibat penggunaan layanan.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">5</span>
                            Kontak
                        </h3>
                        <p class="text-sm text-[#6B7280] leading-relaxed">
                            Pertanyaan terkait Terms dapat dikirim melalui halaman
                            <a href="{{ route('kontak') }}"
                                class="text-[#FF6B18] font-semibold hover:underline">Contact</a>.
                        </p>
                    </div>
                </div>
            </article>

            {{-- Privacy Content (SAMA SEPERTI LOGIN) --}}
            <article id="contentPrivacy" class="hidden prose-sm prose max-w-none">
                <div class="bg-[#FFF7F2] rounded-2xl p-4 mb-6 border-l-4 border-[#FF6B18]">
                    <p class="text-sm leading-relaxed text-[#6B7280] m-0">
                        Kebijakan Privasi BHAYASCIENTIA menjelaskan bagaimana kami mengumpulkan, menggunakan, dan
                        melindungi data pribadi Anda.
                    </p>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">1</span>
                            Data yang Dikumpulkan
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span><strong>Data akun:</strong> nama, email, dan password (disimpan dalam bentuk hash
                                    yang aman).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span><strong>Data penggunaan:</strong> halaman yang diakses, device/browser untuk
                                    analitik dan perbaikan layanan.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span><strong>Data interaksi:</strong> publikasi yang disimpan, dibaca, dan
                                    difavoritkan.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">2</span>
                            Cara Penggunaan Data
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Membuat dan mengelola akun pengguna.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Memproses reset password dan menjaga keamanan akun.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Mengirim informasi penting terkait layanan (opsional: newsletter jika Anda
                                    setuju).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Personalisasi rekomendasi publikasi berdasarkan preferensi Anda.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">3</span>
                            Berbagi Data
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed">
                            <p>Data dapat dibagikan ke penyedia layanan pihak ketiga (mis. email delivery, analytics)
                                hanya untuk operasional platform. Kami <strong>tidak menjual data Anda</strong> ke pihak
                                lain.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">4</span>
                            Hak Pengguna
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Meminta akses, perubahan, atau penghapusan data pribadi.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Menonaktifkan atau menghapus akun kapan saja.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF6B18] mt-1">✓</span>
                                <span>Mengontrol pengaturan privasi dan notifikasi.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">5</span>
                            Keamanan Data
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed">
                            <p>Kami menerapkan upaya keamanan standar industri untuk melindungi data Anda. Namun, tidak
                                ada sistem yang 100% aman. Segera hubungi kami bila ada indikasi kebocoran data.</p>
                        </div>
                    </div>
                </div>
            </article>

        </div>

        {{-- Modal Footer --}}
        <div class="sticky bottom-0 border-t border-[#EEF0F7] bg-white p-5 sm:p-6">
            <button id="legalOkBtn" type="button"
                class="inline-flex w-full items-center justify-center rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                Saya mengerti
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    (function() {
        const input = document.getElementById("password");
        const btn = document.getElementById("togglePassword");
        if (!input || !btn) return;

        btn.addEventListener("click", () => {
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            btn.textContent = isHidden ? "Hide" : "Show";
            btn.setAttribute("aria-pressed", String(isHidden));
        });
    })();

    // Client-side password confirmation validation
    (function() {
        const form = document.getElementById("registerForm");
        const hint = document.getElementById("pwHint");
        if (!form || !hint) return;

        function validatePasswords() {
            const p1 = form.password?.value || "";
            const p2 = form.password_confirmation?.value || "";
            const mismatch = p1 && p2 && p1 !== p2;
            hint.classList.toggle("hidden", !mismatch);
            return !mismatch;
        }

        form.password_confirmation?.addEventListener("input", validatePasswords);
        form.password?.addEventListener("input", validatePasswords);

        form.addEventListener("submit", (e) => {
            if (!validatePasswords()) {
                e.preventDefault();
                hint.classList.remove("hidden");
            }
        });
    })();

    // Legal Modal with improved UX
    (function() {
        const openTerms = document.getElementById("openTermsReg");
        const openPrivacy = document.getElementById("openPrivacyReg");
        const overlay = document.getElementById("legalOverlay");
        const modal = document.getElementById("legalModal");
        const closeBtn = document.getElementById("legalCloseBtn");
        const okBtn = document.getElementById("legalOkBtn");
        const tabTerms = document.getElementById("tabTerms");
        const tabPrivacy = document.getElementById("tabPrivacy");
        const contentTerms = document.getElementById("contentTerms");
        const contentPrivacy = document.getElementById("contentPrivacy");
        const title = document.getElementById("legalTitle");
        const subtitle = document.getElementById("legalSubtitle");

        if (!openTerms || !modal) return;

        function lockScroll(lock) {
            document.documentElement.classList.toggle("overflow-hidden", lock);
            document.body.classList.toggle("overflow-hidden", lock);
        }

        function setOpen(open) {
            if (open) {
                overlay.classList.remove("hidden");
                modal.classList.remove("hidden");
                modal.classList.add("flex");
                lockScroll(true);
            } else {
                overlay.classList.add("hidden");
                modal.classList.add("hidden");
                modal.classList.remove("flex");
                lockScroll(false);
            }
        }

        function setTab(which) {
            const isTerms = which === "terms";

            tabTerms.setAttribute("aria-selected", String(isTerms));
            tabPrivacy.setAttribute("aria-selected", String(!isTerms));

            if (isTerms) {
                tabTerms.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#FF6B18] text-white shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                tabPrivacy.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#F4F6FB] text-[#6B7280] hover:bg-[#FFECE1] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                contentTerms.classList.remove("hidden");
                contentPrivacy.classList.add("hidden");
                title.innerHTML = '<span class="text-2xl">📋</span> <span>Terms & Conditions</span>';
                subtitle.textContent = "Syarat penggunaan layanan BHAYASCIENTIA.";
            } else {
                tabTerms.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#F4F6FB] text-[#6B7280] hover:bg-[#FFECE1] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                tabPrivacy.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#FF6B18] text-white shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                contentTerms.classList.add("hidden");
                contentPrivacy.classList.remove("hidden");
                title.innerHTML = '<span class="text-2xl">🔒</span> <span>Privacy Policy</span>';
                subtitle.textContent = "Kebijakan privasi BHAYASCIENTIA.";
            }
        }

        openTerms.addEventListener("click", () => { setOpen(true); setTab("terms"); });
        openPrivacy.addEventListener("click", () => { setOpen(true); setTab("privacy"); });
        closeBtn.addEventListener("click", () => setOpen(false));
        okBtn.addEventListener("click", () => setOpen(false));
        overlay.addEventListener("click", () => setOpen(false));
        tabTerms.addEventListener("click", () => setTab("terms"));
        tabPrivacy.addEventListener("click", () => setTab("privacy"));

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") setOpen(false);
        });
    })();
</script>

<style>
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }
</style>
@endpush
