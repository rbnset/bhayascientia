<div id="subscriptionModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 overflow-hidden bg-white shadow-2xl rounded-2xl">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-5 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="text-xl font-bold">Ikuti Publikasi Terbaru</h3>
                </div>
                <p class="text-sm opacity-90">Dapatkan rangkuman publikasi langsung di email Anda</p>
            </div>
            {{-- Decorative pattern --}}
            <div class="absolute w-32 h-32 rounded-full -right-8 -top-8 bg-white/10"></div>
            <div class="absolute w-24 h-24 rounded-full -right-4 -bottom-4 bg-white/10"></div>
        </div>

        {{-- Body --}}
        <form action="{{ route('subscription.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- Categories --}}
            <div>
                <label class="block text-sm font-bold text-[#1A1A1A] mb-3">
                    📚 Kategori yang Ingin Diikuti
                </label>
                <div class="space-y-2">
                    <label
                        class="flex items-center gap-3 p-3 border border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] cursor-pointer transition-all group">
                        <input type="checkbox" name="categories[]" value="buku" checked
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A] group-hover:text-[#FF6B18]">Buku</span>
                            <p class="text-xs text-[#737373] mt-0.5">Publikasi buku ilmiah terbaru</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] cursor-pointer transition-all group">
                        <input type="checkbox" name="categories[]" value="jurnal" checked
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A] group-hover:text-[#FF6B18]">Jurnal</span>
                            <p class="text-xs text-[#737373] mt-0.5">Paper penelitian peer-reviewed</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] cursor-pointer transition-all group">
                        <input type="checkbox" name="categories[]" value="opini"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A] group-hover:text-[#FF6B18]">Opini</span>
                            <p class="text-xs text-[#737373] mt-0.5">Essay dan perspektif ilmuwan</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Frequency --}}
            <div>
                <label class="block text-sm font-bold text-[#1A1A1A] mb-3">
                    📅 Frekuensi Pengiriman
                </label>
                <div class="space-y-2">
                    <label
                        class="flex items-center gap-3 p-3 border-2 border-[#FF6B18] bg-[#FFF7F2] rounded-xl cursor-pointer">
                        <input type="radio" name="frequency" value="weekly" checked
                            class="w-5 h-5 text-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A]">Mingguan</span>
                            <p class="text-xs text-[#737373] mt-0.5">Setiap Jumat pukul 08.00 WIB</p>
                        </div>
                        <span class="px-2 py-1 bg-[#FF6B18] text-white text-xs font-bold rounded-full">Populer</span>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] cursor-pointer transition-all">
                        <input type="radio" name="frequency" value="monthly"
                            class="w-5 h-5 text-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A]">Bulanan</span>
                            <p class="text-xs text-[#737373] mt-0.5">Tanggal 1 setiap bulan</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] cursor-pointer transition-all">
                        <input type="radio" name="frequency" value="realtime"
                            class="w-5 h-5 text-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]">
                        <div class="flex-1">
                            <span class="font-semibold text-[#1A1A1A]">Real-time</span>
                            <p class="text-xs text-[#737373] mt-0.5">Segera saat ada publikasi baru</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Email Confirm --}}
            <div class="bg-[#F8F9FC] border border-[#EEF0F7] rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-[#1A1A1A]">Email tujuan:</p>
                        <p class="text-sm text-[#737373] mt-1">{{ auth()->user()->email ?? 'user@email.com' }}</p>
                        <button type="button" class="text-xs text-[#FF6B18] font-semibold hover:underline mt-1">
                            Ubah email
                        </button>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="button" onclick="closeSubscriptionModal()"
                    class="flex-1 px-6 py-3 border-2 border-[#EEF0F7] text-[#737373] font-bold rounded-xl hover:border-[#FF6B18] hover:text-[#FF6B18] transition-all">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-[0_10px_20px_0_#FF6B1880] transition-all">
                    Aktifkan Berlangganan
                </button>
            </div>

            <p class="text-xs text-center text-[#737373]">
                💡 Kamu bisa berhenti berlangganan kapan saja dari email yang dikirim
            </p>
        </form>
    </div>
</div>
