<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DABRAKA - Portal Pengabdian intelektual</title>

    <x-seo title="Selamat Datang di DABRAKA"
        description="Darma Brata Buana Cendekia — Portal Pengabdian Intelektual Kepolisian Indonesia."
        :noindex="true" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F0F2F5] antialiased">

    {{-- ============================================================ --}}
    {{-- SPLASH SCREEN — fixed, full screen, semua device --}}
    {{-- ============================================================ --}}
    <div id="splash-screen" style="
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FF6B18 0%, #D63A1F 100%);
        opacity: 1;
        transition: opacity 0.7s ease-in-out;
        isolation: isolate;
        overflow: hidden;
    ">
        {{-- Decorative circles --}}
        <div
            style="position:absolute;top:-4rem;right:-4rem;width:16rem;height:16rem;border-radius:9999px;background:rgba(255,255,255,0.05);pointer-events:none;">
        </div>
        <div
            style="position:absolute;bottom:-5rem;left:-2.5rem;width:18rem;height:18rem;border-radius:9999px;background:rgba(255,255,255,0.04);pointer-events:none;">
        </div>

        {{-- Content --}}
        <div
            style="position:relative;z-index:10;display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem;">

            {{-- Logo --}}
            <img src="{{ config('app.url') }}/assets/images/logos/logo-light.svg" alt="DABRAKA" id="splash-logo"
                style="height:4rem;width:auto;display:block;"
                onerror="this.style.display='none';document.getElementById('splash-logo-text').style.display='block';">
            <span id="splash-logo-text"
                style="display:none;font-size:2rem;font-weight:900;color:white;letter-spacing:-0.02em;">
                DABRAKA
            </span>

            {{-- App name --}}
            <div>
                <h1
                    style="font-size:clamp(1.5rem,4vw,2.25rem);font-weight:900;color:white;letter-spacing:-0.02em;margin:0;">
                    DABRAKA
                </h1>
                <p
                    style="margin-top:0.25rem;font-size:clamp(0.75rem,2vw,0.875rem);color:rgba(255,255,255,0.7);font-weight:500;margin-bottom:0;">
                    Darma Brata Buana Cendekia
                </p>
            </div>

            {{-- Loading dots --}}
            <div style="display:flex;align-items:center;gap:0.375rem;margin-top:1rem;">
                <span
                    style="width:0.5rem;height:0.5rem;border-radius:9999px;background:rgba(255,255,255,0.6);display:inline-block;animation:splashBounce 1s ease-in-out infinite;animation-delay:0ms;"></span>
                <span
                    style="width:0.5rem;height:0.5rem;border-radius:9999px;background:rgba(255,255,255,0.6);display:inline-block;animation:splashBounce 1s ease-in-out infinite;animation-delay:150ms;"></span>
                <span
                    style="width:0.5rem;height:0.5rem;border-radius:9999px;background:rgba(255,255,255,0.6);display:inline-block;animation:splashBounce 1s ease-in-out infinite;animation-delay:300ms;"></span>
            </div>
        </div>
    </div>

    <style>
        @keyframes splashBounce {

            0%,
            100% {
                transform: translateY(0);
                opacity: 0.6;
            }

            50% {
                transform: translateY(-6px);
                opacity: 1;
            }
        }

        body.splash-active {
            overflow: hidden !important;
            height: 100vh !important;
        }

        body.splash-active>div[x-data] {
            position: relative;
            z-index: 0 !important;
        }
    </style>

    <script>
        (function () {
            const splash      = document.getElementById('splash-screen');
            const STORAGE_KEY = 'dabraka_splash_shown';

            // ✅ Safety net: jika user login & sudah seen onboarding → redirect ke home
            // Ini fallback jika controller somehow tidak menangkapnya
            const isLoggedIn    = {{ auth()->check() ? 'true' : 'false' }};
            const hasSeenInDB   = {{ auth()->check() && auth()->user()->has_seen_onboarding ? 'true' : 'false' }};
            const homeUrl       = '{{ route("home") }}';

            if (isLoggedIn && hasSeenInDB) {
                // Sembunyikan splash langsung & redirect
                splash.style.display = 'none';
                window.location.href = homeUrl;
                return;
            }

            // ✅ Blok scroll saat splash aktif
            document.body.classList.add('splash-active');

            function hideSplash() {
                splash.style.opacity = '0';
                setTimeout(function () {
                    splash.style.display    = 'none';
                    splash.style.visibility = 'hidden';
                    document.body.classList.remove('splash-active');
                }, 700);
            }

            // ✅ Sudah pernah lihat splash → sembunyikan langsung tanpa animasi
            if (localStorage.getItem(STORAGE_KEY)) {
                splash.style.display    = 'none';
                splash.style.visibility = 'hidden';
                document.body.classList.remove('splash-active');
                return;
            }

            // ✅ First visit → tampil 2 detik lalu fade out
            setTimeout(function () {
                hideSplash();
                localStorage.setItem(STORAGE_KEY, '1');
            }, 2000);
        })();
    </script>

    {{-- ============================================================ --}}
    {{-- ONBOARDING CONTENT --}}
    {{-- ============================================================ --}}
    <div x-data="onboarding()" x-cloak class="flex flex-col min-h-screen lg:flex-row">

        {{-- ================================================================ --}}
        {{-- LEFT PANEL — Desktop only --}}
        {{-- ================================================================ --}}
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-[55%] relative overflow-hidden bg-gradient-to-br from-[#FF6B18] to-[#D63A1F] flex-col justify-between p-10 xl:p-16 min-h-screen">

            {{-- Decorative circles --}}
            <div class="absolute rounded-full pointer-events-none -top-20 -right-20 w-72 h-72 bg-white/5"></div>
            <div class="absolute -bottom-24 -left-12 w-80 h-80 bg-white/[0.04] rounded-full pointer-events-none"></div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-white/[0.03] rounded-full pointer-events-none">
            </div>

            {{-- Logo --}}
            <div class="relative z-10">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-light.svg" alt="DABRAKA" class="w-auto h-10"
                    onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                <span class="hidden text-2xl font-extrabold tracking-tight text-white">DABRAKA</span>
            </div>

            {{-- Center Content — berubah per step --}}
            <div class="relative z-10 flex flex-col justify-center flex-1 py-12">

                {{-- Step 1 Left --}}
                <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="mb-8 text-7xl">🎓</div>
                    <h2 class="mb-4 text-4xl font-black leading-tight text-white xl:text-5xl">
                        Where Knowledge<br>Shapes Policing
                    </h2>
                    <p class="max-w-sm text-lg leading-relaxed text-white/80">
                        Portal pengabdian intelektual untuk transformasi kepolisian Indonesia.
                    </p>
                </div>

                {{-- Step 2 Left --}}
                <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="mb-8 text-7xl">📚</div>
                    <h2 class="mb-4 text-4xl font-black leading-tight text-white xl:text-5xl">
                        Ekosistem<br>Pengetahuan
                    </h2>
                    <p class="max-w-sm text-lg leading-relaxed text-white/80">
                        Menjembatani pengalaman lapangan dengan pendekatan ilmiah dan kebijakan berbasis bukti.
                    </p>
                </div>

                {{-- Step 3 Left --}}
                <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="mb-8 text-7xl">🇮🇩</div>
                    <h2 class="mb-4 text-4xl font-black leading-tight text-white xl:text-5xl">
                        Pengabdian<br>Intelektual
                    </h2>
                    <p class="max-w-sm text-lg leading-relaxed text-white/80">
                        Memperkuat institusi, melayani masyarakat, dan berkontribusi bagi kemajuan bangsa.
                    </p>
                </div>

            </div>

            {{-- Step Dots --}}
            <div class="relative z-10 flex items-center gap-2">
                <template x-for="i in totalSteps" :key="i">
                    <div class="transition-all duration-300 rounded-full" :class="i === step
                            ? 'w-8 h-2.5 bg-white'
                            : i < step
                                ? 'w-2.5 h-2.5 bg-white/50'
                                : 'w-2.5 h-2.5 bg-white/25'">
                    </div>
                </template>
                <span class="ml-3 text-sm font-medium text-white/60">
                    <span x-text="step"></span> / <span x-text="totalSteps"></span>
                </span>
            </div>

        </div>

        {{-- ================================================================ --}}
        {{-- RIGHT PANEL --}}
        {{-- ================================================================ --}}
        <div class="flex flex-col flex-1 min-h-screen lg:min-h-0">

            {{-- Mobile Header --}}
            <div
                class="lg:hidden bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-4 flex items-center justify-between">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-light.svg" alt="DABRAKA" class="w-auto h-8"
                    onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                <span class="hidden text-xl font-extrabold tracking-tight text-white">DABRAKA</span>
                <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-white rounded-full bg-white/20">
                    <span x-text="step"></span>/<span x-text="totalSteps"></span>
                </span>
            </div>

            {{-- Mobile Progress Bar --}}
            <div class="lg:hidden h-1 w-full bg-[#F4F6FB]">
                <div class="h-1 bg-gradient-to-r from-[#FF6B18] to-[#E64627] transition-all duration-700 ease-out"
                    :style="`width: ${progress}%`"></div>
            </div>

            {{-- Content Area --}}
            <div
                class="flex flex-col justify-center flex-1 w-full px-5 py-8 sm:px-8 lg:px-12 xl:px-16 lg:max-w-lg lg:mx-auto">

                {{-- ========================= --}}
                {{-- STEP 1 --}}
                {{-- ========================= --}}
                <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">

                    <div class="flex justify-center mb-5 lg:hidden">
                        <div class="w-16 h-16 bg-[#FFECE1] rounded-[14px] flex items-center justify-center text-4xl">🎓
                        </div>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18] mb-4">
                        ✨ Selamat Datang
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-[#1A1A1A] mb-3">
                        Selamat Datang di <span class="text-[#FF6B18]">DABRAKA</span>!
                    </h1>
                    <p class="text-sm sm:text-base text-[#6B7280] leading-relaxed mb-6">
                        <strong class="text-[#374151]">Darma Brata Buana Cendekia</strong> merupakan wadah pengabdian
                        intelektual yang menghimpun kontribusi pemikiran dari insan Bhayangkara dan akademisi untuk
                        pengembangan ilmu kepolisian Indonesia.
                    </p>

                    <div class="bg-white rounded-[18px] ring-1 ring-[#EEF0F7] p-4 sm:p-5 mb-6">
                        <h3 class="text-sm font-bold text-[#111827] mb-3">✨ Yang kamu dapatkan</h3>
                        <ul class="space-y-2.5">
                            @foreach([
                            'Akses publikasi ilmiah kepolisian & keamanan',
                            'Kajian strategis & kebijakan berbasis bukti',
                            'Jejaring akademisi nasional & internasional',
                            'Forum ilmiah & referensi pemikiran progresif',
                            ] as $item)
                            <li class="flex items-start gap-2 text-sm text-[#6B7280]">
                                <span class="text-[#FF6B18] mt-0.5 shrink-0 font-bold">✓</span>
                                <span>{{ $item }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <button @click="next()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                        Mulai Jelajahi →
                    </button>

                    <div class="mt-4 text-center">
                        <button type="button" onclick="skipOnboarding()"
                            class="text-xs text-[#A3A6AE] hover:text-[#6B7280] underline underline-offset-2 transition-colors focus:outline-none">
                            Lewati untuk sekarang
                        </button>
                    </div>
                </div>

                {{-- ========================= --}}
                {{-- STEP 2 --}}
                {{-- ========================= --}}
                <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">

                    <div class="flex justify-center mb-5 lg:hidden">
                        <div class="w-16 h-16 bg-[#FFECE1] rounded-[14px] flex items-center justify-center text-4xl">📚
                        </div>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18] mb-4">
                        📚 Langkah 2 dari 3
                    </span>

                    <h2 class="text-2xl sm:text-3xl font-bold leading-tight text-[#1A1A1A] mb-3">
                        Pusat Referensi Pemikiran Kepolisian
                    </h2>
                    <p class="text-sm sm:text-base text-[#6B7280] leading-relaxed mb-6">
                        DABRAKA berkomitmen menjadi pusat referensi pemikiran kepolisian Indonesia yang progresif dan
                        berwawasan global.
                    </p>

                    <div class="mb-6 space-y-3">
                        @foreach([
                        ['📄', 'Publikasi & Kajian Strategis', 'Karya ilmiah dari insan Bhayangkara dan akademisi
                        terpilih'],
                        ['🔬', 'Ilmu Kepolisian & Keamanan', 'Riset mendalam tentang kebijakan publik dan keamanan
                        nasional'],
                        ['🌐', 'Jejaring Nasional & Internasional', 'Kolaborasi lintas institusi untuk transformasi
                        Polri'],
                        ] as [$icon, $title, $desc])
                        <div class="flex items-start gap-3 bg-white rounded-[14px] ring-1 ring-[#EEF0F7] p-3.5">
                            <div
                                class="w-9 h-9 bg-[#FFECE1] rounded-[10px] flex items-center justify-center text-lg shrink-0">
                                {{ $icon }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#111827]">{{ $title }}</p>
                                <p class="text-xs text-[#6B7280] mt-0.5 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex gap-3">
                        <button @click="prev()"
                            class="inline-flex flex-1 items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-4 py-3 text-sm font-bold text-[#6B7280] transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] active:scale-95">
                            ← Kembali
                        </button>
                        <button @click="next()"
                            class="inline-flex flex-[2] items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                            Lanjut →
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" onclick="skipOnboarding()"
                            class="text-xs text-[#A3A6AE] hover:text-[#6B7280] underline underline-offset-2 transition-colors focus:outline-none">
                            Lewati untuk sekarang
                        </button>
                    </div>
                </div>

                {{-- ========================= --}}
                {{-- STEP 3 --}}
                {{-- ========================= --}}
                <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">

                    <div class="flex justify-center mb-5 lg:hidden">
                        <div class="w-16 h-16 bg-[#FFECE1] rounded-[14px] flex items-center justify-center text-4xl">
                            🇮🇩</div>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18] mb-4">
                        🚀 Langkah 3 dari 3
                    </span>

                    <h2 class="text-2xl sm:text-3xl font-bold leading-tight text-[#1A1A1A] mb-3">
                        Siap Berkontribusi!
                    </h2>
                    <p class="text-sm sm:text-base text-[#6B7280] leading-relaxed mb-6">
                        Bergabunglah dengan insan Bhayangkara dan akademisi yang mempercayai DABRAKA sebagai portal
                        pengabdian intelektual untuk transformasi kepolisian Indonesia.
                    </p>

                    {{-- CTA Utama --}}
                    <form method="POST" action="{{ route('onboarding.complete') }}" class="mb-3">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95">
                            🎓 Mulai Jelajahi DABRAKA
                        </button>
                    </form>

                    {{-- Secondary CTA --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <button type="button" onclick="goToRegister()"
                            class="inline-flex items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-[#111827] transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#FFF7F2] hover:text-[#FF6B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            ✨ Daftar Kontributor
                        </button>
                        <button type="button" onclick="goToLogin()"
                            class="inline-flex items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-[#111827] transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#FFF7F2] hover:text-[#FF6B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                            🔑 Masuk
                        </button>
                    </div>

                    <button @click="prev()"
                        class="w-full py-2 text-xs text-[#A3A6AE] hover:text-[#6B7280] transition-colors focus:outline-none">
                        ← Kembali
                    </button>
                </div>

            </div>{{-- end content --}}

            {{-- Footer --}}
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-[#9CA3AF]">
                    © {{ date('Y') }} DABRAKA — Darma Brata Buana Cendekia. Portal Pengabdian Intelektual Kepolisian
                    Indonesia.
                </p>
            </div>

        </div>{{-- end right panel --}}
    </div>

    <script>
        function onboarding() {
            return {
                step: 1,
                totalSteps: 3,
                get progress() {
                    return (this.step / this.totalSteps) * 100;
                },
                next() {
                    if (this.step < this.totalSteps) this.step++;
                },
                prev() {
                    if (this.step > 1) this.step--;
                },
            }
        }

        const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
        const COMPLETE = '{{ route('onboarding.complete') }}';
        const HOME     = '{{ route('home') }}';
        const REGISTER = '{{ route('register') }}';
        const LOGIN    = '{{ route('login') }}';

        function postComplete(destination) {
            fetch(COMPLETE, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ _token: CSRF }),
            })
            .finally(() => {
                window.location.href = destination;
            });
        }

        function skipOnboarding() { postComplete(HOME);     }
        function goToRegister()    { postComplete(REGISTER); }
        function goToLogin()       { postComplete(LOGIN);    }
    </script>

</body>

</html>