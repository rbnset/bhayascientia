@extends('layouts.app')

@section('title', 'Login')

@section('custom_navbar')
{{-- Custom navbar untuk auth pages --}}
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
    <header class="mt-6">
        <div class="gap-4 flex items-center justify-between">
            <!-- Logo -->
            <div class="gap-4 flex shrink-0 items-center">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                    <img src="{{ asset('assets/images/logos/logo.svg') }}" alt="BHAYASCIENTIA" />
                </a>
            </div>

            <!-- Right -->
            <div class="gap-3 flex items-center">
                <!-- Desktop Menu -->
                <nav id="MenuBar" class="xl:flex hidden items-center" aria-label="Main menu">
                    <div
                        class="gap-1 bg-white p-1 inline-flex flex-wrap items-center rounded-full ring-1 ring-[#EEF0F7]">
                        <a href="{{ route('home') }}"
                            class="px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">Beranda</a>
                        <a href="{{ route('publikasi.index') }}"
                            class="px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">Publikasi</a>
                        <a href="{{ route('event') }}"
                            class="px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">Event</a>
                        <a href="{{ route('tentang') }}"
                            class="px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">Tentang</a>
                        <a href="{{ route('kontak') }}"
                            class="px-4 py-2 text-sm font-bold rounded-full transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">Kontak</a>
                    </div>
                </nav>

                <!-- Desktop CTA -->
                <a href="{{ route('register') }}"
                    class="bg-white text-sm font-bold xl:flex hidden h-[44px] items-center justify-center rounded-full border border-[#EEF0F7] px-[18px] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Daftar
                </a>

                <!-- Mobile hamburger -->
                <button id="hamburgerBtn"
                    class="h-10 w-10 bg-white xl:hidden flex items-center justify-center rounded-full border border-[#EEF0F7]"
                    aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle menu" type="button">
                    <svg id="iconBurger" class="h-5 w-5 block" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="iconClose" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- MOBILE OVERLAY -->
        <div id="mobileOverlay" class="inset-0 bg-black/25 xl:hidden fixed z-40 hidden backdrop-blur-[2px]"
            aria-hidden="true"></div>

        <!-- MOBILE PANEL -->
        <div id="mobileMenu" class="mt-4 xl:hidden relative z-50 hidden">
            <div class="space-y-4 rounded-2xl bg-white p-3 border border-[#EEF0F7]">
                <div class="gap-2 grid grid-cols-1" aria-label="Main menu mobile">
                    <a href="{{ route('home') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]">Beranda</a>
                    <a href="{{ route('publikasi.index') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]">Publikasi</a>
                    <a href="{{ route('event') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]">Event</a>
                    <a href="{{ route('tentang') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]">Tentang</a>
                    <a href="{{ route('kontak') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold border border-[#EEF0F7] transition-colors duration-300 hover:border-[#FF6B18]">Kontak</a>
                    <a href="{{ route('register') }}"
                        class="rounded-xl px-4 py-3 text-sm font-bold text-white bg-[#FF6B18] text-center transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880]">Daftar</a>
                </div>
            </div>
        </div>
    </header>
</div>
@endsection

@section('hide_footer', 'true')

@section('content')
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] pb-16 sm:pb-20">
    <div class="gap-8 lg:grid-cols-2 grid items-center">

        {{-- Left: Copy & Info --}}
        <div class="lg:order-1 order-1">
            <p class="px-4 py-2 text-xs font-bold inline-flex items-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                Welcome back
            </p>

            <h1 class="mt-4 text-3xl font-bold sm:text-4xl sm:leading-[45px] leading-[40px] text-[#1A1A1A]">
                Login untuk lanjut baca <br />
                publikasi favoritmu
            </h1>

            <p class="mt-3 max-w-xl text-sm sm:text-base sm:leading-[24px] leading-[21px] text-[#6B7280]">
                Masuk untuk akses fitur personal, simpan publikasi, dan kelola library yang sudah kamu miliki.
            </p>

            <div class="mt-6 gap-3 flex flex-wrap items-center">
                <a href="{{ route('home') }}"
                    class="bg-white px-5 py-3 text-sm font-bold rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Kembali ke Home
                </a>
                <a href="{{ route('register') }}"
                    class="px-5 py-3 text-sm font-bold rounded-full text-[#FF6B18] transition-all duration-300 hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Belum punya akun? Daftar
                </a>
            </div>
        </div>

        {{-- Right: Login Card --}}
        <div class="lg:order-2 order-2">
            <div class="bg-white p-5 sm:p-6 rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm">

                {{-- Card Header --}}
                <div class="gap-4 flex items-start justify-between mb-5">
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold sm:text-[22px] leading-[28px] text-[#111827]">
                            Login
                        </h2>
                        <p class="mt-1 sm:text-sm sm:leading-[21px] text-[11px] leading-[16px] text-[#A3A6AE]">
                            Gunakan email dan password yang terdaftar.
                        </p>
                    </div>

                    <div class="sm:block hidden">
                        <span class="px-4 py-2 text-xs font-bold inline-flex rounded-full bg-[#F4F6FB] text-[#111827]">
                            🔒 Secure
                        </span>
                    </div>
                </div>

                {{-- Error Alert --}}
                @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 animate-shake">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            @foreach ($errors->all() as $error)
                            <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Success Alert --}}
                @if (session('success'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4">
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

                {{-- Social Login Buttons (optional - implement later) --}}
                <div class="gap-3 sm:grid-cols-2 grid grid-cols-1 mb-5">
                    <button type="button"
                        class="gap-2 bg-white px-5 py-3 text-sm font-bold inline-flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] hover:border-[#FF6B18]/30 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        <span class="h-5 w-5">
                            <svg viewBox="0 0 48 48" class="h-5 w-5" aria-hidden="true">
                                <path fill="#FFC107"
                                    d="M43.611 20.083H42V20H24v8h11.303C33.656 32.91 29.236 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" />
                                <path fill="#FF3D00"
                                    d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4c-7.682 0-14.354 4.337-17.694 10.691z" />
                                <path fill="#4CAF50"
                                    d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.214 0-9.62-3.06-11.283-7.438l-6.522 5.024C9.505 39.556 16.227 44 24 44z" />
                                <path fill="#1976D2"
                                    d="M43.611 20.083H42V20H24v8h11.303c-.792 2.238-2.231 4.166-4.094 5.565l.003-.002 6.19 5.238C36.97 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" />
                            </svg>
                        </span>
                        Google
                    </button>

                    <button type="button"
                        class="gap-2 bg-white px-5 py-3 text-sm font-bold inline-flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] hover:border-[#FF6B18]/30 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        <span class="h-5 w-5">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                                <path fill="currentColor"
                                    d="M22 12.06c0-5.52-4.48-10-10-10S2 6.54 2 12.06c0 4.99 3.66 9.13 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.89h-2.34v6.99C18.34 21.19 22 17.05 22 12.06z" />
                            </svg>
                        </span>
                        Facebook
                    </button>
                </div>

                {{-- Divider --}}
                <div class="mb-5 gap-3 flex items-center">
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                    <p class="font-bold tracking-wide text-xs text-[#A3A6AE]">
                        OR
                    </p>
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                </div>

                {{-- Login Form --}}
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-bold text-[#111827] mb-2">Email</label>
                        <div
                            class="gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('email') ring-2 ring-red-500 @enderror">
                            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}"
                                required placeholder="nama@email.com"
                                class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                        </div>
                        @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-bold text-[#111827] mb-2">Password</label>
                        <div
                            class="gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('password') ring-2 ring-red-500 @enderror">
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                required minlength="6" placeholder="Minimal 6 karakter"
                                class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                            <button type="button" id="togglePassword"
                                class="px-3 py-1 text-xs font-bold rounded-full text-[#6B7280] transition hover:bg-[#F4F6FB] hover:text-[#111827] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                                aria-label="Toggle password visibility" aria-pressed="false">
                                Show
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Remember & Forgot --}}
                        <div class="mt-3 gap-3 flex items-center justify-between">
                            <label
                                class="gap-2 text-sm font-semibold inline-flex items-center text-[#111827] cursor-pointer hover:text-[#FF6B18] transition-colors">
                                <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : ''
                                    }}
                                    class="h-4 w-4 rounded border-[#E8EBF4] text-[#FF6B18] focus:ring-[#FF6B18] cursor-pointer" />
                                Remember me
                            </label>

                            <a href="#"
                                class="text-sm font-bold text-[#FF6B18] transition hover:text-[#d85712] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] rounded">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="mt-2 px-5 py-3.5 text-sm font-bold text-white w-full inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                        Login
                    </button>

                    {{-- Register Link --}}
                    <p class="sm:text-sm sm:leading-[21px] text-center text-[11px] leading-[16px] text-[#A3A6AE]">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-bold text-[#FF6B18] hover:underline">Daftar</a>
                    </p>

                    {{-- Terms --}}
                    <p class="sm:text-sm sm:leading-[21px] text-center text-[11px] leading-[16px] text-[#A3A6AE]">
                        Dengan login, kamu setuju dengan
                        <button type="button" id="openTerms"
                            class="font-bold text-[#FF6B18] hover:underline focus:outline-none">Terms</button>
                        dan
                        <button type="button" id="openPrivacy"
                            class="font-bold text-[#FF6B18] hover:underline focus:outline-none">Privacy Policy</button>.
                    </p>
                </form>

            </div>
        </div>

    </div>
</section>

{{-- Legal Modal (Terms & Privacy) --}}
<div id="legalOverlay" class="inset-0 bg-black/40 fixed z-[60] hidden backdrop-blur-sm transition-opacity duration-300"
    aria-hidden="true"></div>

<div id="legalModal" class="inset-0 p-4 fixed z-[70] hidden items-center justify-center overflow-y-auto" role="dialog"
    aria-modal="true" aria-labelledby="legalTitle">
    <div
        class="bg-white w-full max-w-[720px] overflow-hidden rounded-3xl ring-1 ring-[#EEF0F7] shadow-2xl transform transition-all duration-300 my-8">

        {{-- Modal Header - Sticky --}}
        <div class="sticky top-0 z-10 bg-white border-b border-[#EEF0F7]">
            <div class="gap-4 p-5 sm:p-6 flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <h2 id="legalTitle" class="text-xl font-bold sm:text-2xl text-[#111827] flex items-center gap-2">
                        <span class="text-2xl">📋</span>
                        <span>Terms & Conditions</span>
                    </h2>
                    <p id="legalSubtitle" class="mt-1 text-sm text-[#6B7280]">
                        Syarat penggunaan layanan BHAYASCIENTIA.
                    </p>
                </div>

                <button id="legalCloseBtn" type="button"
                    class="h-10 w-10 bg-[#F4F6FB] inline-flex items-center justify-center rounded-full transition-all hover:bg-[#FF6B18] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="px-5 sm:px-6 pb-4 flex gap-2">
                <button id="tabTerms" type="button"
                    class="px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-selected="true">
                    📜 Terms
                </button>
                <button id="tabPrivacy" type="button"
                    class="px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                    aria-selected="false">
                    🔒 Privacy Policy
                </button>
            </div>
        </div>

        {{-- Modal Body - Scrollable --}}
        <div class="px-5 sm:px-6 py-6 max-h-[60vh] overflow-y-auto">

            {{-- Terms Content --}}
            <article id="contentTerms" class="prose prose-sm max-w-none">
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

            {{-- Privacy Content --}}
            <article id="contentPrivacy" class="prose prose-sm max-w-none hidden">
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

        {{-- Modal Footer - Sticky --}}
        <div class="sticky bottom-0 p-5 sm:p-6 border-t border-[#EEF0F7] bg-white">
            <button id="legalOkBtn" type="button"
                class="px-6 py-3.5 text-sm font-bold text-white w-full inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                Saya mengerti
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Mobile menu
    (function() {
        const btn = document.getElementById("hamburgerBtn");
        const panel = document.getElementById("mobileMenu");
        const overlay = document.getElementById("mobileOverlay");
        const iconBurger = document.getElementById("iconBurger");
        const iconClose = document.getElementById("iconClose");

        function setMobileOpen(open) {
            panel?.classList.toggle("hidden", !open);
            overlay?.classList.toggle("hidden", !open);
            btn?.setAttribute("aria-expanded", String(open));
            iconBurger?.classList.toggle("hidden", open);
            iconClose?.classList.toggle("hidden", !open);
            document.documentElement.classList.toggle("overflow-hidden", open);
            document.body.classList.toggle("overflow-hidden", open);
        }

        btn?.addEventListener("click", () => {
            const open = panel?.classList.contains("hidden");
            setMobileOpen(!!open);
        });

        overlay?.addEventListener("click", () => setMobileOpen(false));

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") setMobileOpen(false);
        });
    })();

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

    // Legal Modal with improved UX
    (function() {
        const openTerms = document.getElementById("openTerms");
        const openPrivacy = document.getElementById("openPrivacy");
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
                requestAnimationFrame(() => {
                    overlay.classList.remove("opacity-0");
                    modal.querySelector("div").classList.remove("scale-95", "opacity-0");
                });
            } else {
                overlay.classList.add("opacity-0");
                modal.querySelector("div").classList.add("scale-95", "opacity-0");
                setTimeout(() => {
                    overlay.classList.add("hidden");
                    modal.classList.add("hidden");
                    modal.classList.remove("flex");
                }, 300);
            }
            lockScroll(open);
        }

        function setTab(which) {
            const isTerms = which === "terms";

            tabTerms.setAttribute("aria-selected", String(isTerms));
            tabPrivacy.setAttribute("aria-selected", String(!isTerms));

            if (isTerms) {
                tabTerms.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#FF6B18] text-white shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                tabPrivacy.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#F4F6FB] text-[#6B7280] hover:bg-[#FFECE1] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
            } else {
                tabTerms.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#F4F6FB] text-[#6B7280] hover:bg-[#FFECE1] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
                tabPrivacy.className = "px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-300 bg-[#FF6B18] text-white shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]";
            }

            contentTerms.classList.toggle("hidden", !isTerms);
            contentPrivacy.classList.toggle("hidden", isTerms);

            if (isTerms) {
                title.innerHTML = '<span class="text-2xl">📋</span><span>Terms & Conditions</span>';
                subtitle.textContent = "Syarat penggunaan layanan BHAYASCIENTIA.";
            } else {
                title.innerHTML = '<span class="text-2xl">🔒</span><span>Privacy Policy</span>';
                subtitle.textContent = "Kebijakan pengelolaan data pengguna.";
            }

            modal.querySelector(".overflow-y-auto").scrollTop = 0;
        }

        openTerms.addEventListener("click", (e) => {
            e.preventDefault();
            setTab("terms");
            setOpen(true);
        });

        openPrivacy.addEventListener("click", (e) => {
            e.preventDefault();
            setTab("privacy");
            setOpen(true);
        });

        tabTerms.addEventListener("click", () => setTab("terms"));
        tabPrivacy.addEventListener("click", () => setTab("privacy"));

        function close() {
            setOpen(false);
        }
        closeBtn.addEventListener("click", close);
        okBtn.addEventListener("click", close);
        overlay.addEventListener("click", close);

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && !modal.classList.contains("hidden")) {
                close();
            }
        });

        overlay.classList.add("opacity-0");
        modal.querySelector("div").classList.add("scale-95", "opacity-0");
        setTab("terms");
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
