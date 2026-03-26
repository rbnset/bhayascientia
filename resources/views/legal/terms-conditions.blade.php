{{-- resources/views/legal/terms-conditions.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Syarat dan Ketentuan DABRAKA - Panduan penggunaan platform publikasi ilmiah insan Polri.">
    <title>Syarat & Ketentuan - DABRAKA</title>
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
                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-bold text-[#FF6B18]">Legal</span>
                </div>
                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black text-[#111827]">Syarat & Ketentuan</h1>
                <p class="mt-4 text-base sm:text-lg text-[#6B7280] max-w-2xl mx-auto">
                    Panduan lengkap penggunaan platform publikasi ilmiah DABRAKA
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
                    Dokumen ini mengatur syarat dan ketentuan penggunaan platform <strong
                        class="text-[#374151]">DABRAKA</strong> — portal pengabdian intelektual insan Polri dan
                    akademisi. Dengan menggunakan layanan DABRAKA, Anda dianggap telah membaca dan menyetujui seluruh
                    ketentuan ini.
                </p>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 1. Tentang DABRAKA --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">1</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Tentang DABRAKA</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>DABRAKA (Darma Brata Buana Cendekia) adalah platform digital publikasi ilmiah terbuka
                                untuk insan Polri yang berkolaborasi dengan intelektual di bidang kepolisian, keamanan,
                                kebijakan publik, serta keilmuan terkait lainnya.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Platform ini beroperasi di bawah domain <strong
                                    class="text-[#374151]">dabraka.org</strong> dan tunduk pada hukum yang berlaku
                                di Republik Indonesia.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Layanan dapat berkembang, ditingkatkan, atau diubah sewaktu-waktu untuk memberikan
                                pengalaman terbaik bagi pengguna.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 2. Syarat Penggunaan Akun --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">2</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Syarat Penggunaan Akun</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Pengguna wajib mendaftarkan diri dengan data yang benar, lengkap, dan valid.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Satu orang hanya diperbolehkan memiliki satu akun aktif di DABRAKA.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Pengguna bertanggung jawab penuh atas keamanan akun, termasuk menjaga kerahasiaan
                                password.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>DABRAKA berhak menonaktifkan akun yang melanggar ketentuan tanpa pemberitahuan
                                sebelumnya.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 3. Hak & Kewajiban Penulis --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">3</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Hak & Kewajiban Penulis</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Penulis yang mempublikasikan karya di DABRAKA menjamin bahwa karya tersebut adalah
                                karya original dan tidak melanggar hak cipta pihak lain.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Penulis memberikan izin kepada DABRAKA untuk menampilkan, mendistribusikan, dan
                                mempromosikan karya dalam platform.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Penulis tetap memegang hak cipta atas karya yang dipublikasikan.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Konten yang mengandung plagiarisme, SARA, pornografi, atau melanggar hukum akan
                                dihapus tanpa pemberitahuan.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 4. Hak & Kewajiban Pembaca --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">4</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Hak & Kewajiban Pembaca</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Pembaca dapat mengakses, membaca, dan menyimpan publikasi untuk keperluan pribadi dan
                                non-komersial.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Dilarang mendistribusikan, menjual, atau menggunakan konten dari DABRAKA untuk
                                keperluan komersial tanpa izin tertulis.</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                            <span class="text-[#FF6B18] mt-1 font-bold">✓</span>
                            <span>Penggunaan konten untuk keperluan akademik wajib menyertakan atribusi dan sitasi yang
                                tepat.</span>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 5. Larangan Penggunaan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">5</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Larangan Penggunaan</h2>
                    </div>
                    <p class="text-sm text-[#6B7280] mb-4">Anda DILARANG menggunakan platform untuk:</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Plagiarisme</h4>
                                    <p class="text-xs text-[#6B7280]">Mengcopy karya orang lain tanpa izin atau sitasi
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Konten Ilegal</h4>
                                    <p class="text-xs text-[#6B7280]">Melanggar hukum, menghasut kebencian, atau SARA
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Spam & Scam</h4>
                                    <p class="text-xs text-[#6B7280]">Mengirim spam, phishing, atau konten menyesatkan
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Hacking</h4>
                                    <p class="text-xs text-[#6B7280]">Mengakses sistem tanpa izin atau merusak
                                        infrastruktur</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Data Scraping</h4>
                                    <p class="text-xs text-[#6B7280]">Mengambil data secara massal dengan bot atau
                                        crawler</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Harassment</h4>
                                    <p class="text-xs text-[#6B7280]">Melecehkan, mengintimidasi, atau mengancam
                                        pengguna lain</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 6. Pembatasan Tanggung Jawab --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">6</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Pembatasan Tanggung Jawab</h2>
                    </div>
                    <div
                        class="p-6 border-2 border-gray-200 bg-gray-50 rounded-2xl text-sm text-[#6B7280] leading-relaxed">
                        DABRAKA menyediakan layanan "sebagaimana adanya" tanpa jaminan apapun. DABRAKA tidak bertanggung
                        jawab atas kerugian langsung maupun tidak langsung akibat penggunaan layanan, termasuk kesalahan
                        konten yang dipublikasikan oleh penulis.
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 7. Biaya Layanan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">7</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Biaya Layanan</h2>
                    </div>
                    <div class="flex items-start gap-3 p-5 border-2 border-orange-200 bg-orange-50 rounded-2xl">
                        <svg class="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-[#6B7280]">
                            DABRAKA saat ini <strong class="text-[#111827]">100% GRATIS</strong>. Tidak ada biaya
                            submission, review, atau publikasi. Jika di masa depan ada perubahan kebijakan, kami akan
                            memberitahu Anda terlebih dahulu.
                        </p>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 8. Perubahan Syarat --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">8</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">Perubahan Syarat & Ketentuan
                        </h2>
                    </div>
                    <p class="text-sm text-[#6B7280] leading-relaxed">
                        Kami berhak memperbarui Syarat dan Ketentuan ini kapan saja. Perubahan signifikan akan
                        diberitahukan melalui email notifikasi ke akun terdaftar atau notifikasi in-app saat login.
                        Dengan terus menggunakan platform setelah perubahan diberlakukan, Anda menyetujui Syarat dan
                        Ketentuan yang diperbarui.
                    </p>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- Hubungi Kami --}}
                <div
                    class="p-6 sm:p-8 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl text-white text-center">
                    <h3 class="mb-2 text-2xl font-black">Butuh Klarifikasi?</h3>
                    <p class="mb-6 text-sm text-white/90">Jika ada yang kurang jelas mengenai Syarat dan Ketentuan ini,
                        jangan ragu menghubungi kami:</p>
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