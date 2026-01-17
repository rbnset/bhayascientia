@extends('layouts.app')

@section('title', 'Register')

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
                <a href="{{ route('login') }}"
                    class="bg-white text-sm font-bold xl:flex hidden h-[44px] items-center justify-center rounded-full border border-[#EEF0F7] px-[18px] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Masuk
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
                    <a href="{{ route('login') }}"
                        class="rounded-xl px-4 py-3 text-sm font-bold text-white bg-[#FF6B18] text-center transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880]">Masuk</a>
                </div>
            </div>
        </div>
    </header>
</div>
@endsection

@section('hide_footer', 'true')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] pb-16 sm:pb-20">
    <section class="gap-8 lg:grid-cols-2 grid items-start">
        <!-- Left info -->
        <div class="lg:order-1 order-1">
            <p class="px-4 py-2 text-xs font-bold inline-flex items-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                Create account
            </p>

            <h1 class="mt-4 text-3xl font-bold sm:text-4xl sm:leading-[45px] leading-[40px]">
                Daftar akun baru
            </h1>

            <p class="mt-3 max-w-xl text-sm sm:text-base sm:leading-[24px] leading-[21px] text-[#6B7280]">
                Buat akun untuk menyimpan artikel, akses fitur personal, dan menikmati pengalaman yang lebih baik.
            </p>

            <div class="mt-6 gap-3 flex flex-wrap items-center">
                <a href="{{ route('home') }}"
                    class="bg-white px-5 py-3 text-sm font-bold rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Kembali ke Home
                </a>
                <a href="{{ route('login') }}"
                    class="px-5 py-3 text-sm font-bold rounded-full text-[#FF6B18] transition-all duration-300 hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    Sudah punya akun? Login
                </a>
            </div>

            <div class="mt-8 bg-white p-5 sm:p-6 rounded-[22px] ring-1 ring-[#EEF0F7]">
                <h3 class="text-base font-bold text-[#111827]">
                    Yang kamu dapatkan
                </h3>
                <ul class="mt-3 space-y-2 pl-5 text-sm list-disc leading-[21px] text-[#6B7280]">
                    <li>Simpan artikel favorit.</li>
                    <li>Ikuti kategori yang kamu suka.</li>
                    <li>Notifikasi konten terbaru (opsional dari backend).</li>
                </ul>
            </div>
        </div>

        <!-- Right form -->
        <div class="lg:order-2 order-2">
            <div class="bg-white p-5 sm:p-6 rounded-[22px] ring-1 ring-[#EEF0F7]">
                <div class="gap-4 flex items-start justify-between">
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold sm:text-[22px] leading-[28px] text-[#111827]">
                            Register
                        </h2>
                        <p class="mt-1 sm:text-sm sm:leading-[21px] text-[11px] leading-[16px] text-[#A3A6AE]">
                            Isi data di bawah untuk membuat akun.
                        </p>
                    </div>
                    <span
                        class="px-4 py-2 text-xs font-bold sm:inline-flex hidden rounded-full bg-[#F4F6FB] text-[#111827]">
                        Secure
                    </span>
                </div>

                <!-- Social buttons (optional - implementasi nanti) -->
                <div class="mt-5 gap-3 sm:grid-cols-2 grid grid-cols-1">
                    <button type="button"
                        class="gap-2 bg-white px-5 py-3 text-sm font-bold inline-flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
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
                        class="gap-2 bg-white px-5 py-3 text-sm font-bold inline-flex items-center justify-center rounded-full border border-[#EEF0F7] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        <span class="h-5 w-5">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                                <path fill="currentColor"
                                    d="M22 12.06c0-5.52-4.48-10-10-10S2 6.54 2 12.06c0 4.99 3.66 9.13 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.89h-2.34v6.99C18.34 21.19 22 17.05 22 12.06z" />
                            </svg>
                        </span>
                        Facebook
                    </button>
                </div>

                <div class="mt-6 gap-3 flex items-center">
                    <p class="font-bold tracking-wide sm:text-sm shrink-0 text-[11px] text-[#A3A6AE]">
                        OR
                    </p>
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                </div>

                <!-- Alerts -->
                @if (session('success'))
                <div class="mt-5 rounded-2xl border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 border"
                    role="status">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mt-5 rounded-2xl border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 border"
                    role="alert">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form -->
                <form action="{{ route('register.post') }}" method="POST" class="mt-6 space-y-4" id="registerForm">
                    @csrf

                    <div class="gap-4 sm:grid-cols-2 grid">
                        <div>
                            <label for="firstName" class="text-sm font-bold text-[#111827]">Nama depan</label>
                            <div
                                class="mt-2 gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('firstName') ring-2 ring-red-500 @enderror">
                                <input id="firstName" name="firstName" type="text" required autocomplete="given-name"
                                    placeholder="Budi" value="{{ old('firstName') }}"
                                    class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                            </div>
                            @error('firstName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lastName" class="text-sm font-bold text-[#111827]">Nama belakang</label>
                            <div
                                class="mt-2 gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('lastName') ring-2 ring-red-500 @enderror">
                                <input id="lastName" name="lastName" type="text" autocomplete="family-name"
                                    placeholder="Santoso" value="{{ old('lastName') }}"
                                    class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                            </div>
                            @error('lastName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="text-sm font-bold text-[#111827]">Email</label>
                        <div
                            class="mt-2 gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('email') ring-2 ring-red-500 @enderror">
                            <input id="email" name="email" type="email" required autocomplete="email"
                                placeholder="nama@email.com" value="{{ old('email') }}"
                                class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                        </div>
                        @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-sm font-bold text-[#111827]">Password</label>
                        <div
                            class="mt-2 gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18] @error('password') ring-2 ring-red-500 @enderror">
                            <input id="password" name="password" type="password" required minlength="6"
                                autocomplete="new-password" placeholder="Minimal 6 karakter"
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
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-sm font-bold text-[#111827]">Konfirmasi
                            password</label>
                        <div
                            class="mt-2 gap-3 bg-white flex items-center rounded-full border border-[#E8EBF4] p-[12px_16px] transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FF6B18]">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                minlength="6" autocomplete="new-password" placeholder="Ulangi password"
                                class="text-sm font-semibold placeholder:font-normal w-full appearance-none bg-transparent outline-none placeholder:text-[#A3A6AE]" />
                        </div>
                        <p id="pwHint"
                            class="mt-2 font-semibold text-red-600 sm:text-sm sm:leading-[21px] hidden text-[11px] leading-[16px]">
                            Password dan konfirmasi password tidak sama.
                        </p>
                    </div>

                    <div class="gap-3 flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                            class="mt-1 h-4 w-4 rounded border-[#E8EBF4] text-[#FF6B18] focus:ring-[#FF6B18]" />
                        <label for="terms" class="text-sm font-semibold leading-[21px] text-[#111827]">
                            Saya setuju dengan
                            <a href="#" class="font-bold text-[#FF6B18] hover:underline">Terms</a>
                            dan
                            <a href="#" class="font-bold text-[#FF6B18] hover:underline">Privacy Policy</a>.
                        </label>
                    </div>

                    <button id="submitBtn" type="submit"
                        class="px-5 py-3 text-sm font-bold text-white focus-visible:ring-offset-white inline-flex w-full items-center justify-center rounded-full bg-[#FF6B18] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                        Buat akun
                    </button>

                    <p class="sm:text-sm sm:leading-[21px] text-center text-[11px] leading-[16px] text-[#A3A6AE]">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-bold text-[#FF6B18] hover:underline">Login</a>
                    </p>
                </form>
            </div>
        </div>
    </section>
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
</script>
@endpush
