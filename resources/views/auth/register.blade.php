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
        <div class="order-1 text-center lg:order-1 lg:text-left">
            <span
                class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs sm:text-sm font-bold text-[#FF6B18]">
                ✨ Create account
            </span>

            <h1
                class="mt-4 text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-3xl sm:leading-[40px] lg:text-4xl lg:leading-[50px]">
                Daftar akun baru <br class="hidden sm:inline" />
                di Dabraka
            </h1>

            <p
                class="mt-3 max-w-xl mx-auto lg:mx-0 text-sm leading-relaxed text-[#6B7280] sm:text-base sm:leading-[24px]">
                Buat akun untuk menyimpan artikel, akses fitur personal, dan menikmati pengalaman yang lebih baik.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3 mt-6 lg:justify-start">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none">
                    🏠 Kembali ke Home
                </a>
                <a href="{{ route('login') }}"
                    class="px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold rounded-full text-[#FF6B18] transition-all duration-300 hover:bg-[#FFF7F2] focus:outline-none">
                    Sudah punya akun? <span class="underline">Login</span>
                </a>
            </div>

            {{-- Benefits Card --}}
            <div class="mt-8 bg-white p-5 sm:p-6 rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm">
                <h3 class="text-base font-bold text-[#111827] mb-3">✨ Yang kamu dapatkan</h3>
                <ul class="space-y-2 text-sm text-[#6B7280]">
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Simpan publikasi favorit ke library pribadi</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Ikuti kategori dan topik yang kamu minati</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Lacak riwayat bacaan dan progres membaca</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#FF6B18] mt-0.5">✓</span>
                        <span>Akses fitur premium Dabraka (coming soon)</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Right: Register Card --}}
        <div class="order-2 lg:order-2">
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
                    <a href="{{ route('auth.google') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#F4F6FB] hover:shadow-sm focus:outline-none">
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
                        Google
                    </a>

                    {{-- ORCID Register --}}
                    <a href="{{ route('auth.orcid') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:border-[#A6CE39]/30 hover:bg-[#F4F6FB] hover:shadow-sm focus:outline-none">
                        <img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" alt="ORCID"
                            class="w-5 h-5">
                        <span class="hidden sm:inline">ORCID</span>
                        <span class="sm:hidden">ID</span>
                    </a>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                    <p class="text-xs font-bold tracking-wide text-[#A3A6AE]">OR</p>
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                </div>

                {{-- Register Form --}}
                <form action="{{ route('register.post') }}" method="POST" class="space-y-4" id="registerForm">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-[#111827]">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <div class="input-wrapper @error('name') error @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input type="text" id="name" name="name" autocomplete="name" value="{{ old('name') }}"
                                required placeholder="Budi Santoso">
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
                        <div class="input-wrapper @error('email') error @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            <input type="email" id="email" name="email" autocomplete="email" value="{{ old('email') }}"
                                required placeholder="nama@email.com">
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
                        <div class="input-wrapper @error('password') error @enderror">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input type="password" id="password" name="password" autocomplete="new-password" required
                                minlength="6" placeholder="Minimal 6 karakter">
                            <button type="button" id="togglePassword"
                                class="shrink-0 rounded-full px-3 py-1 text-xs font-bold text-[#6B7280] transition hover:bg-[#F4F6FB] hover:text-[#111827]"
                                aria-label="Toggle password visibility">
                                Show
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[#111827]">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <div class="input-wrapper" id="confirmWrapper">
                            <svg class="w-5 h-5 text-[#A3A6AE] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                autocomplete="new-password" required minlength="6" placeholder="Ulangi password">
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
                                Policy</button>
                            Dabraka.
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none active:scale-95 disabled:cursor-not-allowed disabled:opacity-60">
                        Buat Akun
                    </button>

                    <p class="text-center text-xs sm:text-sm text-[#A3A6AE]">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-bold text-[#FF6B18] hover:underline">Login</a>
                    </p>
                </form>

            </div>
        </div>
    </div>
</section>

{{-- Legal Modal --}}
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
                        Syarat penggunaan platform Dabraka.
                    </p>
                </div>
                <button id="legalCloseBtn" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] transition-all hover:bg-[#FF6B18] hover:text-white focus:outline-none"
                    aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex gap-2 px-5 pb-4 sm:px-6">
                <button id="tabTerms" type="button"
                    class="rounded-full px-5 py-2.5 text-sm font-bold transition-all duration-300 focus:outline-none"
                    aria-selected="true">
                    📜 Terms
                </button>
                <button id="tabPrivacy" type="button"
                    class="rounded-full px-5 py-2.5 text-sm font-bold transition-all duration-300 focus:outline-none"
                    aria-selected="false">
                    🔒 Privacy Policy
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="max-h-[60vh] overflow-y-auto px-5 py-6 sm:px-6">

            {{-- Terms --}}
            <article id="contentTerms" class="prose-sm prose max-w-none">
                <div class="bg-[#FFF7F2] rounded-2xl p-4 mb-6 border-l-4 border-[#FF6B18]">
                    <p class="text-sm leading-relaxed text-[#6B7280] m-0">
                        Dokumen ini mengatur syarat dan ketentuan penggunaan platform <strong>Dabraka</strong> —
                        platform publikasi ilmiah dan akademik Indonesia. Dengan mendaftar, Anda dianggap telah membaca
                        dan menyetujui seluruh ketentuan ini.
                    </p>
                </div>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">1</span>
                            Tentang Dabraka
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Dabraka
                                    adalah platform digital yang menyediakan akses terhadap publikasi ilmiah, jurnal,
                                    dan karya akademik dari berbagai penulis dan institusi di Indonesia.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Platform
                                    ini beroperasi di bawah domain <strong>dabraka.rbnset.me</strong> dan tunduk pada
                                    hukum yang berlaku di Republik Indonesia.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Layanan
                                    dapat berkembang, ditingkatkan, atau diubah sewaktu-waktu untuk memberikan
                                    pengalaman terbaik bagi pengguna.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">2</span>
                            Syarat Penggunaan Akun
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Pengguna
                                    wajib mendaftarkan diri dengan data yang benar, lengkap, dan valid.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Satu
                                    orang hanya diperbolehkan memiliki satu akun aktif di Dabraka.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Pengguna
                                    bertanggung jawab penuh atas keamanan akun, termasuk menjaga kerahasiaan
                                    password.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Dabraka
                                    berhak menonaktifkan akun yang melanggar ketentuan tanpa pemberitahuan
                                    sebelumnya.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">3</span>
                            Hak & Kewajiban Penulis
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Penulis
                                    menjamin bahwa karya yang dipublikasikan adalah original dan tidak melanggar hak
                                    cipta pihak lain.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Penulis
                                    memberikan izin kepada Dabraka untuk menampilkan, mendistribusikan, dan
                                    mempromosikan karya dalam platform.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Penulis
                                    tetap memegang hak cipta atas karya yang dipublikasikan.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Konten
                                    yang mengandung plagiarisme, SARA, pornografi, atau melanggar hukum akan dihapus
                                    tanpa pemberitahuan.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">4</span>
                            Hak & Kewajiban Pembaca
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Pembaca
                                    dapat mengakses, membaca, dan menyimpan publikasi untuk keperluan pribadi dan
                                    non-komersial.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Dilarang
                                    mendistribusikan, menjual, atau menggunakan konten untuk keperluan komersial tanpa
                                    izin tertulis.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span>Penggunaan konten untuk keperluan akademik
                                    wajib menyertakan atribusi/sitasi yang tepat.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">5</span>
                            Pembatasan Tanggung Jawab
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed">
                            <p>Dabraka menyediakan layanan "sebagaimana adanya" tanpa jaminan apapun. Dabraka tidak
                                bertanggung jawab atas kerugian langsung maupun tidak langsung akibat penggunaan
                                layanan, termasuk kesalahan konten yang dipublikasikan oleh penulis.</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">6</span>
                            Hubungi Kami
                        </h3>
                        <p class="text-sm text-[#6B7280] leading-relaxed">
                            Pertanyaan terkait Terms & Conditions dapat disampaikan melalui
                            <a href="{{ route('kontak') }}" class="text-[#FF6B18] font-semibold hover:underline">halaman
                                Kontak Dabraka</a>.
                        </p>
                    </div>
                </div>
            </article>

            {{-- Privacy --}}
            <article id="contentPrivacy" class="hidden prose-sm prose max-w-none">
                <div class="bg-[#FFF7F2] rounded-2xl p-4 mb-6 border-l-4 border-[#FF6B18]">
                    <p class="text-sm leading-relaxed text-[#6B7280] m-0">
                        Kebijakan Privasi ini menjelaskan bagaimana <strong>Dabraka</strong> mengumpulkan, menggunakan,
                        menyimpan, dan melindungi data pribadi Anda. Kami berkomitmen menjaga kepercayaan Anda.
                    </p>
                </div>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">1</span>
                            Data yang Kami Kumpulkan
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Data registrasi:</strong> nama
                                    lengkap, alamat email, dan password (disimpan dalam bentuk hash terenkripsi).</span>
                            </li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Data OAuth:</strong> jika login
                                    via Google/Facebook, kami menerima nama, email, dan foto profil dari provider
                                    tersebut.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Data aktivitas:</strong> publikasi
                                    yang Anda baca, favoritkan, dan simpan untuk personalisasi pengalaman.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Data teknis:</strong> jenis
                                    browser, sistem operasi, dan alamat IP untuk keamanan dan analitik platform.</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">2</span>
                            Cara Kami Menggunakan Data
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Membuat
                                    dan mengelola akun pengguna di platform Dabraka.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span>Mengirimkan kode OTP untuk verifikasi
                                    email dan keamanan akun.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span>Personalisasi tampilan dan rekomendasi
                                    publikasi berdasarkan preferensi dan riwayat bacaan Anda.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span>Mengirimkan notifikasi penting terkait
                                    akun dan layanan (bukan iklan pihak ketiga).</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">3</span>
                            Berbagi Data dengan Pihak Ketiga
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed space-y-2">
                            <p>Dabraka <strong>tidak menjual atau memperdagangkan data pribadi Anda</strong> kepada
                                pihak manapun.</p>
                            <p>Data hanya dapat dibagikan kepada penyedia layanan teknis (seperti layanan email untuk
                                OTP) yang mendukung operasional Dabraka, dengan kewajiban menjaga kerahasiaan.</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">4</span>
                            Hak-Hak Anda
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Akses:</strong> Anda berhak
                                    meminta salinan data pribadi yang kami simpan.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Koreksi:</strong> Anda berhak
                                    memperbarui data yang tidak akurat melalui halaman profil.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Penghapusan:</strong> Anda berhak
                                    meminta penghapusan akun dan data pribadi dari sistem Dabraka.</span></li>
                            <li class="flex items-start gap-2"><span
                                    class="text-[#FF6B18] mt-1">✓</span><span><strong>Portabilitas:</strong> Anda berhak
                                    meminta ekspor data aktivitas Anda di platform.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">5</span>
                            Keamanan Data
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-[#6B7280] leading-relaxed">
                            <p>Dabraka menerapkan enkripsi data, proteksi CSRF, validasi input, dan praktik keamanan
                                standar industri. Password disimpan dalam format hash yang tidak dapat dibalikkan.
                                Segera hubungi kami jika Anda menduga ada kebocoran data.</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">6</span>
                            Cookies & Sesi
                        </h3>
                        <ul class="space-y-2 text-sm text-[#6B7280] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Dabraka
                                    menggunakan cookies sesi untuk menjaga status login Anda.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Kami
                                    tidak menggunakan cookies untuk tracking iklan atau pihak ketiga.</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#FF6B18] mt-1">✓</span><span>Anda
                                    dapat menghapus cookies kapan saja melalui pengaturan browser.</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#FF6B18] text-white text-sm font-bold">7</span>
                            Hubungi Kami
                        </h3>
                        <p class="text-sm text-[#6B7280] leading-relaxed">
                            Untuk pertanyaan terkait privasi atau permintaan penghapusan data, silakan hubungi kami
                            melalui
                            <a href="{{ route('kontak') }}" class="text-[#FF6B18] font-semibold hover:underline">halaman
                                Kontak Dabraka</a>.
                        </p>
                    </div>
                </div>
            </article>
        </div>

        {{-- Modal Footer --}}
        <div class="sticky bottom-0 border-t border-[#EEF0F7] bg-white p-5 sm:p-6">
            <button id="legalOkBtn" type="button"
                class="inline-flex w-full items-center justify-center rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none active:scale-95">
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ========================================
    // Toggle Password Visibility
    // ========================================
    (function() {
        const input = document.getElementById("password");
        const btn   = document.getElementById("togglePassword");
        if (!input || !btn) return;
        btn.addEventListener("click", () => {
            const isHidden = input.type === "password";
            input.type     = isHidden ? "text" : "password";
            btn.textContent = isHidden ? "Hide" : "Show";
            btn.setAttribute("aria-pressed", String(isHidden));
        });
    })();

    // ========================================
    // Password Confirmation Validation
    // ========================================
    (function() {
        const form    = document.getElementById("registerForm");
        const hint    = document.getElementById("pwHint");
        const wrapper = document.getElementById("confirmWrapper");
        if (!form || !hint) return;

        function validatePasswords() {
            const p1 = form.password?.value || "";
            const p2 = form.password_confirmation?.value || "";
            const mismatch = p1 && p2 && p1 !== p2;
            hint.classList.toggle("hidden", !mismatch);
            if (wrapper) wrapper.classList.toggle("error", mismatch);
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

    // ========================================
    // Legal Modal
    // ========================================
    (function() {
        const openTerms   = document.getElementById("openTermsReg");
        const openPrivacy = document.getElementById("openPrivacyReg");
        const overlay     = document.getElementById("legalOverlay");
        const modal       = document.getElementById("legalModal");
        const closeBtn    = document.getElementById("legalCloseBtn");
        const okBtn       = document.getElementById("legalOkBtn");
        const tabTerms    = document.getElementById("tabTerms");
        const tabPrivacy  = document.getElementById("tabPrivacy");
        const contentTerms   = document.getElementById("contentTerms");
        const contentPrivacy = document.getElementById("contentPrivacy");
        const title    = document.getElementById("legalTitle");
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

        const activeClass   = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#FF6B18] text-white shadow-md focus:outline-none";
        const inactiveClass = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#F4F6FB] text-[#6B7280] hover:bg-[#FFECE1] focus:outline-none";

        function setTab(which) {
            const isTerms = which === "terms";
            tabTerms.setAttribute("aria-selected", String(isTerms));
            tabPrivacy.setAttribute("aria-selected", String(!isTerms));

            if (isTerms) {
                tabTerms.className   = activeClass;
                tabPrivacy.className = inactiveClass;
                contentTerms.classList.remove("hidden");
                contentPrivacy.classList.add("hidden");
                title.innerHTML      = '<span class="text-2xl">📋</span> <span>Terms & Conditions</span>';
                subtitle.textContent = "Syarat penggunaan platform Dabraka.";
            } else {
                tabTerms.className   = inactiveClass;
                tabPrivacy.className = activeClass;
                contentTerms.classList.add("hidden");
                contentPrivacy.classList.remove("hidden");
                title.innerHTML      = '<span class="text-2xl">🔒</span> <span>Privacy Policy</span>';
                subtitle.textContent = "Kebijakan privasi platform Dabraka.";
            }
        }

        openTerms.addEventListener("click",  () => { setOpen(true); setTab("terms"); });
        openPrivacy.addEventListener("click", () => { setOpen(true); setTab("privacy"); });
        closeBtn.addEventListener("click",   () => setOpen(false));
        okBtn.addEventListener("click",      () => setOpen(false));
        overlay.addEventListener("click",    () => setOpen(false));
        tabTerms.addEventListener("click",   () => setTab("terms"));
        tabPrivacy.addEventListener("click", () => setTab("privacy"));
        document.addEventListener("keydown", (e) => { if (e.key === "Escape") setOpen(false); });

        setTab("terms");
    })();
</script>

<style>
    /* ✅ FIX DOUBLE BORDER — sama seperti login.blade.php */
    .input-wrapper {
        display: flex;
        align-items: center;
        width: 100%;
        border-radius: 9999px;
        border: 1.5px solid #E8EBF4;
        padding: 0 14px;
        gap: 10px;
        overflow: hidden;
        background: white;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .input-wrapper:hover {
        border-color: rgba(255, 107, 24, 0.5);
    }

    .input-wrapper:focus-within {
        border-color: #FF6B18;
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.15);
    }

    .input-wrapper.error {
        border-color: #ef4444;
    }

    .input-wrapper input {
        width: 100%;
        padding: 12px 0;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: white;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        -webkit-appearance: none;
        appearance: none;
    }

    .input-wrapper input::placeholder {
        font-weight: 400;
        color: #A3A6AE;
    }

    .input-wrapper input:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
    }

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