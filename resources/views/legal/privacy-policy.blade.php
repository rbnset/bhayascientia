{{-- resources/views/legal/privacy-policy.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Kebijakan Privasi DABRAKA - Komitmen kami dalam melindungi data dan privasi Anda.">
    <title>Kebijakan Privasi - DABRAKA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-[#F8F9FC]">

    <x-navbar />

    {{-- Hero --}}
    <section class="pt-24 pb-12 sm:pt-32 sm:pb-16 bg-gradient-to-b from-white to-[#F8F9FC]">
        <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[900px]">
            <div class="text-center">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[#FFECE1] to-[#FFE8DC] border-2 border-orange-200">
                    <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-bold text-[#FF6B18]">Legal</span>
                </div>
                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black text-[#111827]">Kebijakan Privasi</h1>
                <p class="mt-4 text-base sm:text-lg text-[#6B7280] max-w-2xl mx-auto">
                    Komitmen DABRAKA dalam melindungi data dan privasi Anda
                </p>
                <p class="mt-6 text-sm text-[#6B7280]">Terakhir diperbarui: <strong>18 Maret 2026</strong></p>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section class="py-12 sm:py-16">
        <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[900px]">
            <div class="p-6 bg-white border-2 border-gray-100 shadow-xl rounded-3xl sm:p-10 lg:p-12">

                <p class="text-base sm:text-lg text-[#6B7280] leading-relaxed mb-8 border-l-4 border-[#FFD4BA] pl-4">
                    Kebijakan Privasi ini menjelaskan bagaimana <strong class="text-[#374151]">DABRAKA</strong>
                    mengumpulkan, menggunakan, menyimpan, dan melindungi data pribadi Anda saat menggunakan platform
                    kami. Kami berkomitmen menjaga kepercayaan Anda.
                </p>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 1. Data yang Kami Kumpulkan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">1</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Data yang Kami Kumpulkan</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span><strong class="text-[#374151]">Data registrasi:</strong> nama lengkap, alamat email,
                                dan password (disimpan dalam bentuk hash terenkripsi yang tidak dapat dibaca).</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span><strong class="text-[#374151]">Data OAuth:</strong> jika login via Google atau
                                Facebook, kami menerima nama, email, dan foto profil dari provider tersebut.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span><strong class="text-[#374151]">Data aktivitas:</strong> publikasi yang Anda baca,
                                favoritkan, dan simpan untuk memberikan pengalaman yang lebih personal.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span><strong class="text-[#374151]">Data teknis:</strong> jenis browser, sistem operasi,
                                dan alamat IP untuk keamanan dan analitik platform.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 2. Cara Kami Menggunakan Data --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">2</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Cara Kami Menggunakan Data</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Membuat dan mengelola akun pengguna di platform DABRAKA.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Mengirimkan kode OTP untuk verifikasi email dan keamanan akun.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Personalisasi tampilan dan rekomendasi publikasi berdasarkan preferensi dan riwayat
                                bacaan Anda.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Mengirimkan notifikasi penting terkait akun dan layanan (bukan iklan pihak
                                ketiga).</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Meningkatkan performa dan keamanan platform DABRAKA secara berkelanjutan.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 3. Berbagi Data --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">3</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Berbagi Data dengan Pihak Ketiga
                        </h2>
                    </div>
                    <div
                        class="p-6 border-2 border-gray-200 bg-gray-50 rounded-2xl space-y-3 text-sm text-[#6B7280] leading-relaxed">
                        <p>DABRAKA <strong class="text-[#111827]">tidak menjual atau memperdagangkan data pribadi
                                Anda</strong> kepada pihak manapun.</p>
                        <p>Data hanya dapat dibagikan kepada penyedia layanan teknis (seperti layanan email untuk
                            pengiriman OTP) yang mendukung operasional DABRAKA, dengan kewajiban menjaga kerahasiaan
                            data.</p>
                        <p>Data dapat diungkapkan jika diwajibkan oleh hukum atau permintaan resmi dari otoritas yang
                            berwenang di Indonesia.</p>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 4. Hak-Hak Pengguna --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">4</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Hak-Hak Anda sebagai Pengguna
                        </h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <span class="text-xl">👁️</span>
                            <div>
                                <h4 class="font-bold text-[#111827] text-sm mb-1">Akses</h4>
                                <p class="text-xs text-[#6B7280]">Anda berhak meminta salinan data pribadi yang kami
                                    simpan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <span class="text-xl">✏️</span>
                            <div>
                                <h4 class="font-bold text-[#111827] text-sm mb-1">Koreksi</h4>
                                <p class="text-xs text-[#6B7280]">Anda berhak memperbarui atau memperbaiki data yang
                                    tidak akurat melalui halaman profil.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <span class="text-xl">🗑️</span>
                            <div>
                                <h4 class="font-bold text-[#111827] text-sm mb-1">Penghapusan</h4>
                                <p class="text-xs text-[#6B7280]">Anda berhak meminta penghapusan akun dan data pribadi
                                    Anda dari sistem DABRAKA.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <span class="text-xl">📥</span>
                            <div>
                                <h4 class="font-bold text-[#111827] text-sm mb-1">Portabilitas</h4>
                                <p class="text-xs text-[#6B7280]">Anda berhak meminta ekspor data aktivitas Anda di
                                    platform.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 5. Keamanan Data --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">5</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Keamanan Data</h2>
                    </div>
                    <div
                        class="p-6 border-2 border-gray-200 bg-gray-50 rounded-2xl text-sm text-[#6B7280] leading-relaxed">
                        DABRAKA menerapkan enkripsi data, proteksi CSRF, validasi input, dan praktik keamanan standar
                        industri. Password disimpan dalam format hash yang tidak dapat dibalikkan. Meski demikian, tidak
                        ada sistem yang 100% kebal — segera hubungi kami jika Anda menduga ada kebocoran data.
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 6. Cookies --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">6</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Cookies & Sesi</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>DABRAKA menggunakan cookies sesi untuk menjaga status login Anda agar tidak perlu
                                login ulang setiap saat.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Kami tidak menggunakan cookies untuk tracking iklan atau pihak ketiga.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Anda dapat menghapus cookies kapan saja melalui pengaturan browser Anda.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 7. Perubahan Kebijakan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">7</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Perubahan Kebijakan</h2>
                    </div>
                    <p class="text-sm text-[#6B7280] leading-relaxed">
                        Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan signifikan akan
                        diberitahukan melalui email atau notifikasi di platform. Dengan terus menggunakan layanan kami
                        setelah perubahan diberlakukan, Anda menyetujui kebijakan yang diperbarui.
                    </p>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- Hubungi Kami --}}
                <div
                    class="p-6 sm:p-8 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl text-white text-center">
                    <h3 class="mb-2 text-2xl font-black">Punya Pertanyaan?</h3>
                    <p class="mb-6 text-sm text-white/90">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini,
                        silakan hubungi kami:</p>
                    <div class="space-y-2 text-sm">
                        <p><strong>Email:</strong> <a href="mailto:hallodabraka@dabraka.org"
                                class="underline hover:text-white/80">hallodabraka@dabraka.org</a></p>
                        <p><strong>Kontak:</strong> <a href="{{ route('kontak') }}"
                                class="underline hover:text-white/80">Halaman Kontak DABRAKA</a></p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <x-layouts.footer />
</body>

</html>