{{-- resources/views/terms-conditions.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Syarat dan Ketentuan BHAYASCIENTIA - Panduan penggunaan platform publikasi akademik kami.">
    <title>Syarat & Ketentuan - BHAYASCIENTIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-[#F8F9FC]">

    {{-- Navbar --}}
    <x-navbar />

    {{-- Hero Section --}}
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

                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black text-[#111827]">
                    Syarat & Ketentuan
                </h1>

                <p class="mt-4 text-base sm:text-lg text-[#6B7280] max-w-2xl mx-auto">
                    Panduan lengkap penggunaan platform publikasi akademik BHAYASCIENTIA
                </p>

                <p class="mt-6 text-sm text-[#6B7280]">
                    Terakhir diperbarui: <strong>12 Februari 2026</strong>
                </p>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="py-12 sm:py-16">
        <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[900px]">
            <div class="p-6 bg-white border-2 border-gray-100 shadow-xl rounded-3xl sm:p-10 lg:p-12">

                {{-- Introduction --}}
                <div class="prose prose-lg max-w-none">
                    <p class="text-base sm:text-lg text-[#6B7280] leading-relaxed">
                        Selamat datang di BHAYASCIENTIA. Dengan mengakses dan menggunakan platform ini, Anda setuju
                        untuk terikat oleh Syarat dan Ketentuan berikut. Harap baca dengan saksama sebelum menggunakan
                        layanan kami.
                    </p>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 1. Definisi --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">1</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Definisi
                        </h2>
                    </div>

                    <div class="ml-0 space-y-3 sm:ml-13">
                        <div class="p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <p class="text-[#6B7280]">
                                <strong class="text-[#111827]">"Platform"</strong> merujuk pada website, aplikasi, dan
                                layanan BHAYASCIENTIA.
                            </p>
                        </div>

                        <div class="p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <p class="text-[#6B7280]">
                                <strong class="text-[#111827]">"Pengguna"</strong> adalah individu atau entitas yang
                                menggunakan layanan kami, termasuk penulis, reviewer, dan pengunjung.
                            </p>
                        </div>

                        <div class="p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <p class="text-[#6B7280]">
                                <strong class="text-[#111827]">"Konten"</strong> meliputi naskah publikasi, artikel,
                                komentar, dan semua materi yang diunggah ke platform.
                            </p>
                        </div>

                        <div class="p-4 border border-gray-200 bg-gray-50 rounded-xl">
                            <p class="text-[#6B7280]">
                                <strong class="text-[#111827]">"Layanan"</strong> mencakup publikasi, peer review,
                                hosting konten, dan fitur lain yang disediakan platform.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 2. Penerimaan Syarat --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">2</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Penerimaan Syarat
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Dengan menggunakan BHAYASCIENTIA, Anda menyatakan bahwa:
                        </p>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-[#6B7280]">Anda berusia minimal 18 tahun atau memiliki izin wali/orang
                                    tua</p>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-[#6B7280]">Anda telah membaca, memahami, dan menyetujui Syarat &
                                    Ketentuan ini</p>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-[#6B7280]">Anda bertanggung jawab atas keakuratan informasi yang Anda
                                    berikan</p>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-[#6B7280]">Anda setuju untuk mematuhi semua hukum dan regulasi yang
                                    berlaku</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 3. Registrasi Akun --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">3</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Registrasi & Keamanan Akun
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <h3 class="text-lg font-bold text-[#111827] mb-3">3.1 Pembuatan Akun</h3>
                        <p class="text-[#6B7280] mb-4">
                            Untuk menggunakan fitur lengkap platform, Anda perlu membuat akun dengan menyediakan
                            informasi yang akurat dan lengkap.
                        </p>

                        <div class="p-5 mb-6 border-2 border-yellow-200 bg-yellow-50 rounded-2xl">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Tanggung Jawab Anda:</h4>
                                    <ul class="text-sm text-[#6B7280] space-y-1">
                                        <li>• Menjaga kerahasiaan password</li>
                                        <li>• Bertanggung jawab atas semua aktivitas yang terjadi di akun Anda</li>
                                        <li>• Segera memberitahu kami jika terjadi penggunaan tidak sah</li>
                                        <li>• Tidak membagikan akun dengan pihak lain</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-[#111827] mb-3">3.2 Suspens & Penutupan Akun</h3>
                        <p class="text-[#6B7280]">
                            Kami berhak menangguhkan atau menutup akun Anda jika:
                        </p>

                        <div class="mt-3 space-y-2">
                            <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                                <span class="text-red-600">❌</span>
                                <span>Melanggar Syarat & Ketentuan atau Kebijakan Privasi</span>
                            </div>
                            <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                                <span class="text-red-600">❌</span>
                                <span>Melakukan plagiarisme atau pelanggaran hak cipta</span>
                            </div>
                            <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                                <span class="text-red-600">❌</span>
                                <span>Memberikan informasi palsu atau menyesatkan</span>
                            </div>
                            <div class="flex items-start gap-2 text-sm text-[#6B7280]">
                                <span class="text-red-600">❌</span>
                                <span>Melakukan spam, harassment, atau perilaku tidak pantas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 4. Hak Kekayaan Intelektual --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">4</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Hak Kekayaan Intelektual
                        </h2>
                    </div>

                    <div class="ml-0 space-y-6 sm:ml-13">
                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3">4.1 Hak Cipta Penulis</h3>
                            <div class="p-5 border-2 border-green-200 bg-green-50 rounded-2xl">
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">✅</span>
                                    <div>
                                        <h4 class="font-bold text-[#111827] mb-2">Hak Cipta TETAP Milik Anda</h4>
                                        <p class="text-sm text-[#6B7280]">
                                            Anda mempertahankan 100% hak cipta atas semua karya yang Anda unggah.
                                            BHAYASCIENTIA tidak mengklaim kepemilikan atas konten Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3">4.2 Lisensi kepada Platform</h3>
                            <p class="text-[#6B7280] mb-3">
                                Dengan mempublikasikan konten di platform, Anda memberikan BHAYASCIENTIA lisensi
                                non-eksklusif untuk:
                            </p>

                            <div class="space-y-2">
                                <div class="flex items-start gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-[#6B7280]">Menyimpan, menampilkan, dan mendistribusikan
                                        konten Anda di platform</span>
                                </div>

                                <div class="flex items-start gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-[#6B7280]">Membuat backup dan arsip untuk keamanan
                                        data</span>
                                </div>

                                <div class="flex items-start gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-[#6B7280]">Mempromosikan publikasi Anda melalui media
                                        sosial dan newsletter</span>
                                </div>

                                <div class="flex items-start gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-[#6B7280]">Mengindeks konten untuk keperluan pencarian dan
                                        discovery</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3">4.3 Jaminan Orisinalitas</h3>
                            <p class="text-[#6B7280] mb-3">
                                Dengan mengunggah konten, Anda menjamin bahwa:
                            </p>

                            <div class="p-5 border-2 border-red-200 bg-red-50 rounded-2xl">
                                <ul class="space-y-2 text-sm text-[#6B7280]">
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold text-red-600">•</span>
                                        <span>Konten adalah karya orisinal Anda atau Anda memiliki izin untuk
                                            menggunakannya</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold text-red-600">•</span>
                                        <span>Konten tidak melanggar hak cipta, merek dagang, atau hak kekayaan
                                            intelektual pihak lain</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold text-red-600">•</span>
                                        <span>Anda memiliki izin dari semua co-author (jika ada)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold text-red-600">•</span>
                                        <span>Sitasi dan referensi telah dilakukan dengan benar</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 5. Proses Publikasi --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">5</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Proses Publikasi & Peer Review
                        </h2>
                    </div>

                    <div class="ml-0 space-y-6 sm:ml-13">
                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3">5.1 Submission</h3>
                            <p class="text-[#6B7280] mb-3">
                                Semua submission akan melalui:
                            </p>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg">
                                        <span class="font-bold text-blue-600">1</span>
                                    </div>
                                    <p class="text-sm text-[#6B7280]"><strong class="text-[#111827]">Initial
                                            Check:</strong> Verifikasi format dan kelengkapan dokumen</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg">
                                        <span class="font-bold text-green-600">2</span>
                                    </div>
                                    <p class="text-sm text-[#6B7280]"><strong class="text-[#111827]">Peer
                                            Review:</strong> Evaluasi oleh reviewer ahli di bidangnya</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-purple-100 rounded-lg">
                                        <span class="font-bold text-purple-600">3</span>
                                    </div>
                                    <p class="text-sm text-[#6B7280]"><strong class="text-[#111827]">Revision:</strong>
                                        Penulis melakukan perbaikan berdasarkan feedback</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-orange-100 rounded-lg">
                                        <span class="font-bold text-orange-600">4</span>
                                    </div>
                                    <p class="text-sm text-[#6B7280]"><strong class="text-[#111827]">Final
                                            Decision:</strong> Diterima, revisi lagi, atau ditolak</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3">5.2 Hak Platform</h3>
                            <p class="text-[#6B7280] mb-3">
                                BHAYASCIENTIA berhak untuk:
                            </p>

                            <ul class="space-y-2 text-sm text-[#6B7280]">
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Menolak submission yang tidak memenuhi standar editorial atau akademik</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Meminta revisi atau klarifikasi sebelum publikasi</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Melakukan copy editing untuk konsistensi format</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Menarik publikasi jika ditemukan plagiarisme atau pelanggaran etika</span>
                                </li>
                            </ul>
                        </div>

                        <div class="p-5 border-2 border-orange-200 bg-orange-50 rounded-2xl">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">⚠️ Biaya Publikasi</h4>
                                    <p class="text-sm text-[#6B7280]">
                                        BHAYASCIENTIA saat ini <strong>100% GRATIS</strong>. Tidak ada biaya submission,
                                        review, atau publikasi. Jika di masa depan ada perubahan kebijakan, kami akan
                                        memberitahu Anda terlebih dahulu.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 6. Larangan Penggunaan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">6</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Larangan Penggunaan
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Anda DILARANG menggunakan platform untuk:
                        </p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                                <div class="flex items-start gap-2">
                                    <span class="text-2xl">🚫</span>
                                    <div>
                                        <h4 class="font-bold text-[#111827] text-sm mb-1">Plagiarisme</h4>
                                        <p class="text-xs text-[#6B7280]">Mengcopy karya orang lain tanpa izin atau
                                            sitasi</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                                <div class="flex items-start gap-2">
                                    <span class="text-2xl">🚫</span>
                                    <div>
                                        <h4 class="font-bold text-[#111827] text-sm mb-1">Konten Ilegal</h4>
                                        <p class="text-xs text-[#6B7280]">Melanggar hukum, menghasut kebencian, atau
                                            SARA</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 border-2 border-red-200 bg-red-50 rounded-xl">
                                <div class="flex items-start gap-2">
                                    <span class="text-2xl">🚫</span>
                                    <div>
                                        <h4 class="font-bold text-[#111827] text-sm mb-1">Spam & Scam</h4>
                                        <p class="text-xs text-[#6B7280]">Mengirim spam, phishing, atau konten
                                            menyesatkan</p>
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
                                        <p class="text-xs text-[#6B7280]">Mengambil data secara massal dengan
                                            bot/crawler</p>
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
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 7. Disclaimer --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">7</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Disclaimer & Batasan Tanggung Jawab
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <div class="p-6 space-y-4 border-2 border-gray-200 bg-gray-50 rounded-2xl">
                            <p class="text-[#6B7280]">
                                <strong class="text-[#111827]">Platform disediakan "sebagaimana adanya"</strong> tanpa
                                jaminan apapun. BHAYASCIENTIA tidak bertanggung jawab atas:
                            </p>

                            <ul class="space-y-2 text-sm text-[#6B7280]">
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Keakuratan, kelengkapan, atau keandalan konten yang dipublikasikan
                                        pengguna</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Kerugian langsung atau tidak langsung dari penggunaan platform</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Gangguan teknis, downtime, atau kehilangan data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span>•</span>
                                    <span>Tindakan pihak ketiga atau link eksternal</span>
                                </li>
                            </ul>

                            <p class="text-sm text-[#6B7280] italic">
                                <strong>Tanggung jawab maksimal kami terbatas pada biaya yang telah Anda bayarkan kepada
                                    platform (jika ada).</strong>
                            </p>
                        </div>
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
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Perubahan Syarat & Ketentuan
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Kami berhak memperbarui Syarat & Ketentuan ini kapan saja. Perubahan signifikan akan
                            diberitahukan melalui:
                        </p>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                <svg class="flex-shrink-0 w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-[#6B7280]">Email notifikasi ke akun terdaftar</span>
                            </div>

                            <div class="flex items-center gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                <svg class="flex-shrink-0 w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="text-sm text-[#6B7280]">Notifikasi in-app saat login</span>
                            </div>

                            <div class="flex items-center gap-3 p-3 border border-blue-200 bg-blue-50 rounded-xl">
                                <svg class="flex-shrink-0 w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-sm text-[#6B7280]">Update halaman ini dengan tanggal revisi
                                    terbaru</span>
                            </div>
                        </div>

                        <p class="text-[#6B7280] mt-4">
                            Dengan terus menggunakan platform setelah perubahan diberlakukan, Anda menyetujui Syarat &
                            Ketentuan yang diperbarui.
                        </p>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 9. Hukum yang Berlaku --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">9</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Hukum yang Berlaku & Penyelesaian Sengketa
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Syarat & Ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum Negara Republik
                            Indonesia. Setiap sengketa yang timbul akan diselesaikan melalui:
                        </p>

                        <div class="space-y-3">
                            <div class="p-4 border-2 border-blue-200 bg-blue-50 rounded-xl">
                                <h4 class="font-bold text-[#111827] mb-2">1️⃣ Musyawarah</h4>
                                <p class="text-sm text-[#6B7280]">Prioritas pertama adalah penyelesaian secara
                                    musyawarah dan kekeluargaan</p>
                            </div>

                            <div class="p-4 border-2 border-green-200 bg-green-50 rounded-xl">
                                <h4 class="font-bold text-[#111827] mb-2">2️⃣ Mediasi</h4>
                                <p class="text-sm text-[#6B7280]">Jika musyawarah tidak berhasil, akan dilakukan mediasi
                                    oleh pihak ketiga netral</p>
                            </div>

                            <div class="p-4 border-2 border-orange-200 bg-orange-50 rounded-xl">
                                <h4 class="font-bold text-[#111827] mb-2">3️⃣ Arbitrase/Pengadilan</h4>
                                <p class="text-sm text-[#6B7280]">Sebagai langkah terakhir, sengketa akan diselesaikan
                                    melalui Pengadilan Negeri Yogyakarta</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- Contact --}}
                <div
                    class="p-6 sm:p-8 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-2xl text-white text-center">
                    <div class="flex items-center justify-center mb-4">
                        <div
                            class="flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="mb-2 text-2xl font-black">Butuh Klarifikasi?</h3>
                    <p class="mb-6 text-white/90">
                        Jika ada yang kurang jelas mengenai Syarat & Ketentuan ini, jangan ragu menghubungi kami:
                    </p>

                    <div class="space-y-3 text-sm">
                        <p>
                            <strong>Email:</strong>
                            <a href="mailto:legal@bhayascientia.id"
                                class="underline hover:text-white/80">legal@bhayascientia.id</a>
                        </p>
                        <p>
                            <strong>WhatsApp:</strong>
                            <a href="https://wa.me/6281234567890" target="_blank"
                                class="underline hover:text-white/80">+62 812-3456-7890</a>
                        </p>
                        <p>
                            <strong>Alamat:</strong> Depok, Sleman, Yogyakarta
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <x-layouts.footer />

</body>

</html>
