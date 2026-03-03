@extends('layouts.app')

@section('title', 'Verifikasi Email – DABRAKA')

@section('custom_navbar')
<x-navbar ctaLabel="Daftar Sekarang" ctaRoute="register" ctaIcon="user-plus" :showAvatarWhenAuth="false"
    :showCtaAlways="true" />
@endsection

@section('hide_footer', 'true')

@section('content')
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-8 sm:py-12 lg:py-16">
    <div class="grid items-center gap-8 lg:gap-12 lg:grid-cols-2">

        {{-- Left: Hero Copy --}}
        <div class="order-2 text-center lg:order-1 lg:text-left">
            <span
                class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs sm:text-sm font-bold text-[#FF6B18]">
                🔐 Verifikasi Akun
            </span>

            <h1
                class="mt-4 text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-3xl sm:leading-[40px] lg:text-4xl lg:leading-[50px]">
                Satu langkah lagi untuk <br class="hidden sm:inline" />
                mengaktifkan akunmu
            </h1>

            <p
                class="mt-3 max-w-xl mx-auto lg:mx-0 text-sm leading-relaxed text-[#6B7280] sm:text-base sm:leading-[24px]">
                Kami mengirimkan kode verifikasi 6 digit ke email
                <strong class="text-[#111827]">{{ Auth::user()->email }}</strong>.
                Masukkan kode tersebut untuk mengaktifkan akun DABRAKA kamu.
            </p>

            {{-- Info cards --}}
            <div class="mt-6 space-y-3">
                <div class="flex items-center gap-3 p-3 bg-white rounded-2xl ring-1 ring-[#EEF0F7] shadow-sm">
                    <div
                        class="flex-shrink-0 w-9 h-9 bg-[#FFECE1] rounded-full flex items-center justify-center text-base">
                        ⏱
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-[#111827]">Kode berlaku 10 menit</p>
                        <p class="text-xs text-[#A3A6AE]">Segera masukkan sebelum kode kadaluarsa</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white rounded-2xl ring-1 ring-[#EEF0F7] shadow-sm">
                    <div
                        class="flex-shrink-0 w-9 h-9 bg-[#FFECE1] rounded-full flex items-center justify-center text-base">
                        🔄
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-[#111827]">Kirim ulang kode</p>
                        <p class="text-xs text-[#A3A6AE]">Bisa kirim ulang maksimal 3 kali</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white rounded-2xl ring-1 ring-[#EEF0F7] shadow-sm">
                    <div
                        class="flex-shrink-0 w-9 h-9 bg-[#FFECE1] rounded-full flex items-center justify-center text-base">
                        🛡️
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-[#111827]">Jaga kerahasiaanmu</p>
                        <p class="text-xs text-[#A3A6AE]">Jangan bagikan kode OTP ke siapapun</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 mt-6 lg:justify-start">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#EEF0F7] bg-white px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                    🏠 Kembali ke Home
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold rounded-full text-[#FF6B18] transition-all duration-300 hover:bg-[#FFF7F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        Bukan akun kamu? <span class="underline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Right: OTP Card --}}
        <div class="order-1 lg:order-2">
            <div class="bg-white rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm p-5 sm:p-6 lg:p-8">

                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-bold text-[#111827] sm:text-2xl">Verifikasi Email</h2>
                        <p class="mt-1 text-xs sm:text-sm text-[#A3A6AE]">
                            Masukkan 6 digit kode yang dikirim ke emailmu.
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
                            @foreach ($errors->all() as $error)
                            <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                            @endforeach
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

                {{-- Info Alert --}}
                @if (session('info'))
                <div class="p-4 mb-5 border border-blue-200 rounded-xl bg-blue-50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                    </div>
                </div>
                @endif

                {{-- Email Target --}}
                <div class="flex items-center gap-3 p-3 mb-5 bg-[#F4F6FB] rounded-xl">
                    <div
                        class="w-9 h-9 bg-white rounded-full flex items-center justify-center ring-1 ring-[#EEF0F7] shrink-0">
                        <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-[#A3A6AE]">Kode dikirim ke</p>
                        <p class="text-sm font-bold text-[#111827] truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                {{-- OTP Form --}}
                <form action="{{ route('otp.verify') }}" method="POST" id="otp-form">
                    @csrf

                    <label class="block mb-3 text-sm font-bold text-[#111827]">
                        Kode Verifikasi <span class="text-red-500">*</span>
                    </label>

                    {{-- 6 Digit OTP Input --}}
                    <div class="flex justify-between gap-2 mb-5" id="otp-inputs">
                        @for ($i = 0; $i < 6; $i++) <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                            class="otp-digit w-full aspect-square max-w-[52px] text-center text-xl font-black rounded-2xl ring-1 ring-[#E8EBF4] bg-white text-[#111827] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:bg-[#FFF7F2] hover:ring-[#FF6B18]/60 @error('code') !ring-2 !ring-red-500 @enderror"
                            autocomplete="off">
                            @endfor
                    </div>

                    {{-- Hidden input --}}
                    <input type="hidden" name="code" id="otp-code">

                    {{-- Submit Button --}}
                    <button type="submit" id="submit-btn"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-3 sm:py-3.5 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                        <svg class="hidden w-4 h-4 animate-spin" id="submit-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="submit-text">✅ Verifikasi Sekarang</span>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="flex items-center gap-3 my-5">
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                    <p class="text-xs font-bold tracking-wide text-[#A3A6AE]">OR</p>
                    <div class="h-px flex-1 bg-[#E8EBF4]"></div>
                </div>

                {{-- Resend OTP --}}
                <form action="{{ route('otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" id="resend-btn"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#EEF0F7] bg-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold transition-all duration-300 hover:border-[#FF6B18]/30 hover:bg-[#FFF7F2] hover:text-[#FF6B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span id="resend-text">Kirim Ulang Kode</span>
                    </button>
                </form>

                {{-- Footer note --}}
                <p class="text-center text-xs text-[#A3A6AE] mt-4">
                    Cek folder <strong>Spam</strong> jika email tidak masuk ke inbox.
                </p>

            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
    const inputs      = document.querySelectorAll('.otp-digit');
    const hiddenInput = document.getElementById('otp-code');
    const form        = document.getElementById('otp-form');
    const submitBtn   = document.getElementById('submit-btn');
    const submitText  = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const resendBtn   = document.getElementById('resend-btn');
    const resendText  = document.getElementById('resend-text');

    // ── OTP Input Logic ──────────────────────────────────────────────────────
    inputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            updateHiddenInput();

            // Auto submit saat semua terisi
            const code = getCode();
            if (code.length === 6) {
                hiddenInput.value = code;
                setTimeout(() => triggerSubmit(), 300);
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                updateHiddenInput();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            pasted.split('').forEach((char, i) => {
                if (inputs[i]) inputs[i].value = char;
            });
            updateHiddenInput();
            if (pasted.length === 6) {
                hiddenInput.value = pasted;
                inputs[Math.min(pasted.length - 1, 5)].focus();
                setTimeout(() => triggerSubmit(), 300);
            }
        });

        // Focus styling
        input.addEventListener('focus', function () {
            this.select();
        });
    });

    function getCode() {
        return Array.from(inputs).map(i => i.value).join('');
    }

    function updateHiddenInput() {
        hiddenInput.value = getCode();
    }

    function triggerSubmit() {
        submitText.textContent = 'Memverifikasi...';
        submitSpinner.classList.remove('hidden');
        submitBtn.disabled = true;
        form.submit();
    }

    // Form submit loading state
    form.addEventListener('submit', function () {
        submitText.textContent = 'Memverifikasi...';
        submitSpinner.classList.remove('hidden');
        submitBtn.disabled = true;
    });

    // Auto focus input pertama
    inputs[0]?.focus();

    // ── Resend Countdown ─────────────────────────────────────────────────────
    let countdown = 60;

    function startCountdown() {
        resendBtn.disabled = true;
        resendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        const timer = setInterval(() => {
            countdown--;
            if (countdown > 0) {
                resendText.textContent = `Tunggu ${countdown} detik...`;
            } else {
                resendText.textContent = 'Kirim Ulang Kode';
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                clearInterval(timer);
            }
        }, 1000);
    }

    startCountdown();

    // ── Auto-dismiss Alerts ──────────────────────────────────────────────────
    function autoDismiss(selector, delay) {
        const el = document.querySelector(selector);
        if (!el) return;
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease-out';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, delay);
    }

    autoDismiss('.bg-green-50', 5000);
    autoDismiss('.bg-blue-50',  6000);
    autoDismiss('.bg-red-50',   8000);
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

    .bg-green-50,
    .bg-red-50,
    .bg-blue-50 {
        transition: opacity 0.5s ease-out;
    }

    .otp-digit::-webkit-inner-spin-button,
    .otp-digit::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .otp-digit[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush