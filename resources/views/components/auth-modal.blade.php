<div id="authModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50 backdrop-blur-sm">
    <div class="w-full max-w-md mx-4 overflow-hidden bg-white shadow-2xl rounded-2xl">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-4 text-white">
            <h3 class="text-lg font-bold">Login untuk Akses Penuh</h3>
            <p class="mt-1 text-sm opacity-90">Download PDF, simpan favorit, dan lebih banyak lagi</p>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-4">
            {{-- Social Login --}}
            <button
                class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-white border-2 border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] transition-all">
                <img src="/assets/icons/google.svg" class="w-5 h-5" alt="Google">
                <span class="font-semibold">Lanjutkan dengan Google</span>
            </button>

            <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-[#EEF0F7]"></div>
                <span class="text-sm text-[#737373]">atau</span>
                <div class="flex-1 h-px bg-[#EEF0F7]"></div>
            </div>

            {{-- Email Login Form --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 bg-white border border-[#EEF0F7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-white border border-[#EEF0F7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full px-6 py-3.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-[0_10px_20px_0_#FF6B1880] transition-all">
                    Login
                </button>
            </form>

            <p class="text-center text-sm text-[#737373]">
                Belum punya akun?
                <button onclick="switchToRegister()" class="text-[#FF6B18] font-semibold hover:underline">
                    Daftar Gratis
                </button>
            </p>
        </div>
    </div>
</div>
