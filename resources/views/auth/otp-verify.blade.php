@extends('layouts.app')
@section('title', 'Verifikasi Email – DABRAKA')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#F0F2F5] px-4 py-16">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="overflow-hidden bg-white shadow-xl rounded-2xl">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#FF6B18] to-[#D63A1F] px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 rounded-full bg-white/10">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 -translate-x-1/2 translate-y-1/2 rounded-full bg-white/5">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-white/20 rounded-2xl">
                        <span class="text-3xl">🔐</span>
                    </div>
                    <h1 class="mb-2 text-2xl font-black text-white">Verifikasi Email</h1>
                    <p class="text-sm text-white/80">
                        Kode OTP telah dikirim ke<br>
                        <strong class="text-white">{{ Auth::user()->email }}</strong>
                    </p>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-8 py-8">

                {{-- Alert Success --}}
                @if(session('success'))
                <div class="flex items-start gap-3 p-4 mb-6 border border-green-200 bg-green-50 rounded-xl">
                    <span class="flex-shrink-0 text-lg text-green-500">✅</span>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
                @endif

                {{-- Alert Info --}}
                @if(session('info'))
                <div class="flex items-start gap-3 p-4 mb-6 border border-blue-200 bg-blue-50 rounded-xl">
                    <span class="flex-shrink-0 text-lg text-blue-500">📧</span>
                    <p class="text-sm text-blue-700">{{ session('info') }}</p>
                </div>
                @endif

                {{-- Error --}}
                @if($errors->any())
                <div class="flex items-start gap-3 p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
                    <span class="flex-shrink-0 text-lg text-red-500">❌</span>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
                @endif

                <p class="text-sm text-[#6B7280] mb-6 text-center">
                    Masukkan 6 digit kode yang dikirim ke email Anda. Kode berlaku selama <strong
                        class="text-[#374151]">10 menit</strong>.
                </p>

                {{-- Form OTP --}}
                <form action="{{ route('otp.verify') }}" method="POST" id="otp-form">
                    @csrf

                    {{-- OTP Input 6 Digit --}}
                    <div class="flex justify-center gap-3 mb-6" id="otp-inputs">
                        @for($i = 0; $i < 6; $i++) <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                            class="otp-digit w-12 h-14 text-center text-2xl font-black border-2 border-[#EEF0F7] rounded-xl focus:border-[#FF6B18] focus:outline-none transition-colors text-[#111827]"
                            autocomplete="off">
                            @endfor
                    </div>

                    {{-- Hidden input untuk submit --}}
                    <input type="hidden" name="code" id="otp-code">

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-[#FF6B18] to-[#D63A1F] text-white font-bold text-base rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        ✅ Verifikasi Sekarang
                    </button>
                </form>

                <hr class="border-[#F3F4F6] my-6">

                {{-- Resend --}}
                <div class="text-center">
                    <p class="text-sm text-[#9CA3AF] mb-3">Tidak menerima kode?</p>
                    <form action="{{ route('otp.resend') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="text-sm font-semibold text-[#FF6B18] hover:text-[#D63A1F] transition-colors"
                            id="resend-btn">
                            🔄 Kirim Ulang Kode
                        </button>
                    </form>
                    <p class="text-xs text-[#D1D5DB] mt-2">Maksimal 3x pengiriman ulang</p>
                </div>

                {{-- Logout --}}
                <div class="mt-4 text-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-[#9CA3AF] hover:text-[#6B7280] transition-colors">
                            Bukan akun Anda? Keluar
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Info di bawah card --}}
        <p class="text-center text-xs text-[#9CA3AF] mt-4">
            🔒 Kode OTP bersifat rahasia. Jangan bagikan ke siapapun.
        </p>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.otp-digit');
    const hiddenInput = document.getElementById('otp-code');
    const form = document.getElementById('otp-form');

    // Auto focus & pindah antar input
    inputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            // Hanya angka
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            updateHiddenInput();

            // Auto submit kalau semua terisi
            const code = Array.from(inputs).map(i => i.value).join('');
            if (code.length === 6) {
                hiddenInput.value = code;
                setTimeout(() => form.submit(), 300);
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                updateHiddenInput();
            }
        });

        // Handle paste
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            pasted.split('').forEach((char, i) => {
                if (inputs[i]) inputs[i].value = char;
            });
            updateHiddenInput();
            if (pasted.length === 6) {
                hiddenInput.value = pasted;
                setTimeout(() => form.submit(), 300);
            }
        });
    });

    function updateHiddenInput() {
        hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
    }

    // Auto focus input pertama
    inputs[0]?.focus();

    // Countdown resend button
    let countdown = 60;
    const resendBtn = document.getElementById('resend-btn');
    const timer = setInterval(() => {
        countdown--;
        if (countdown > 0) {
            resendBtn.textContent = `⏱ Kirim ulang dalam ${countdown}s`;
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            resendBtn.textContent = '🔄 Kirim Ulang Kode';
            resendBtn.disabled = false;
            resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            clearInterval(timer);
        }
    }, 1000);
});
</script>
@endpush