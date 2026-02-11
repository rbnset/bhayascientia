{{-- resources/views/privacy-policy.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Kebijakan Privasi BHAYASCIENTIA - Komitmen kami dalam melindungi data dan privasi Anda.">
    <title>Kebijakan Privasi - BHAYASCIENTIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-[#F8F9FC]">

    {{-- Header/Navbar (gunakan component navbar Anda) --}}
    <x-navbar />

    {{-- Hero Section --}}
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

                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black text-[#111827]">
                    Kebijakan Privasi
                </h1>

                <p class="mt-4 text-base sm:text-lg text-[#6B7280] max-w-2xl mx-auto">
                    Komitmen kami dalam melindungi data dan privasi Anda
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
                        BHAYASCIENTIA ("kami", "kita", atau "platform") berkomitmen untuk melindungi privasi dan
                        keamanan data pribadi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan,
                        menggunakan, menyimpan, dan melindungi informasi Anda saat menggunakan layanan publikasi
                        akademik kami.
                    </p>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 1. Informasi yang Kami Kumpulkan --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">1</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Informasi yang Kami Kumpulkan
                        </h2>
                    </div>

                    <div class="ml-0 space-y-6 sm:ml-13">
                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd" />
                                </svg>
                                1.1 Informasi Pribadi
                            </h3>
                            <ul class="space-y-2 text-[#6B7280]">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Nama lengkap, email, nomor telepon</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Afiliasi institusi (universitas, lembaga riset)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Bidang keahlian dan minat penelitian</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Informasi akun (username, password terenkripsi)</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd" />
                                </svg>
                                1.2 Konten Akademik
                            </h3>
                            <ul class="space-y-2 text-[#6B7280]">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Naskah publikasi yang Anda submit</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Metadata publikasi (judul, abstrak, kata kunci)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Komentar dan feedback dari reviewer</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-[#111827] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z"
                                        clip-rule="evenodd" />
                                </svg>
                                1.3 Data Teknis
                            </h3>
                            <ul class="space-y-2 text-[#6B7280]">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Alamat IP, tipe browser, sistem operasi</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Log aktivitas (waktu akses, halaman yang dikunjungi)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Cookies dan teknologi pelacakan serupa</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 2. Penggunaan Informasi --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">2</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Bagaimana Kami Menggunakan Informasi Anda
                        </h2>
                    </div>

                    <div class="ml-0 space-y-4 sm:ml-13">
                        <div class="p-5 border-2 border-orange-100 bg-orange-50 rounded-2xl">
                            <h4 class="font-bold text-[#111827] mb-2">✅ Pengelolaan Akun & Publikasi</h4>
                            <p class="text-sm text-[#6B7280]">Memproses registrasi, mengelola akun Anda, dan
                                memfasilitasi proses submission hingga publikasi naskah.</p>
                        </div>

                        <div class="p-5 border-2 border-blue-100 bg-blue-50 rounded-2xl">
                            <h4 class="font-bold text-[#111827] mb-2">✅ Komunikasi</h4>
                            <p class="text-sm text-[#6B7280]">Mengirim notifikasi status review, update platform,
                                newsletter, dan informasi penting lainnya.</p>
                        </div>

                        <div class="p-5 border-2 border-green-100 bg-green-50 rounded-2xl">
                            <h4 class="font-bold text-[#111827] mb-2">✅ Peningkatan Layanan</h4>
                            <p class="text-sm text-[#6B7280]">Menganalisis penggunaan platform untuk meningkatkan fitur,
                                user experience, dan kualitas layanan.</p>
                        </div>

                        <div class="p-5 border-2 border-purple-100 bg-purple-50 rounded-2xl">
                            <h4 class="font-bold text-[#111827] mb-2">✅ Keamanan</h4>
                            <p class="text-sm text-[#6B7280]">Mendeteksi dan mencegah fraud, spam, penyalahgunaan, serta
                                menjaga integritas platform.</p>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 3. Pembagian Informasi --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">3</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Pembagian Informasi
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Kami <strong>TIDAK menjual</strong> data pribadi Anda kepada pihak ketiga. Namun, kami dapat
                            membagikan informasi dalam kondisi berikut:
                        </p>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                                <svg class="w-6 h-6 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Dengan Reviewer</h4>
                                    <p class="text-sm text-[#6B7280]">Naskah dan metadata Anda dibagikan ke reviewer
                                        untuk proses peer review (dengan atau tanpa identitas, sesuai kebijakan).</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                                <svg class="w-6 h-6 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Publikasi Publik</h4>
                                    <p class="text-sm text-[#6B7280]">Publikasi yang diterima akan dipublikasikan dengan
                                        nama penulis, afiliasi, dan metadata akademik.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-4 border border-gray-200 bg-gray-50 rounded-xl">
                                <svg class="w-6 h-6 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Kewajiban Hukum</h4>
                                    <p class="text-sm text-[#6B7280]">Jika diwajibkan oleh hukum, pengadilan, atau
                                        otoritas pemerintah yang berwenang.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 4. Keamanan Data --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">4</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Keamanan Data
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-6">
                            Kami menerapkan langkah-langkah keamanan teknis dan organisasi untuk melindungi data Anda:
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Enkripsi SSL/TLS</h4>
                                    <p class="text-xs text-[#6B7280]">Semua data ditransmisikan melalui koneksi
                                        terenkripsi</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Password Hashing</h4>
                                    <p class="text-xs text-[#6B7280]">Password disimpan dengan algoritma bcrypt</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Firewall & Monitoring</h4>
                                    <p class="text-xs text-[#6B7280]">Sistem pemantauan 24/7 untuk deteksi ancaman</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-red-100 rounded-lg">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#111827] text-sm mb-1">Backup Rutin</h4>
                                    <p class="text-xs text-[#6B7280]">Data di-backup secara berkala dan tersimpan aman
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px my-8 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                {{-- 5. Hak Anda --}}
                <div class="mb-10">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B18] to-[#E64627] flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black text-white">5</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Hak Anda
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Anda memiliki hak untuk:
                        </p>

                        <div class="space-y-3">
                            <div
                                class="flex items-start gap-3 p-4 border-2 border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl">
                                <span class="text-2xl">👁️</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Mengakses Data Anda</h4>
                                    <p class="text-sm text-[#6B7280]">Melihat data pribadi yang kami simpan tentang Anda
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 p-4 border-2 border-green-200 bg-gradient-to-r from-green-50 to-green-100 rounded-xl">
                                <span class="text-2xl">✏️</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Memperbaiki Data</h4>
                                    <p class="text-sm text-[#6B7280]">Memperbarui informasi yang tidak akurat atau tidak
                                        lengkap</p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 p-4 border-2 border-red-200 bg-gradient-to-r from-red-50 to-red-100 rounded-xl">
                                <span class="text-2xl">🗑️</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Menghapus Data</h4>
                                    <p class="text-sm text-[#6B7280]">Meminta penghapusan data pribadi Anda (dengan
                                        syarat tertentu)</p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 p-4 border-2 border-purple-200 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl">
                                <span class="text-2xl">📥</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Mengunduh Data</h4>
                                    <p class="text-sm text-[#6B7280]">Mendapatkan salinan data Anda dalam format yang
                                        dapat dibaca</p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 p-4 border-2 border-orange-200 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl">
                                <span class="text-2xl">🚫</span>
                                <div>
                                    <h4 class="font-bold text-[#111827] mb-1">Menolak Pemrosesan</h4>
                                    <p class="text-sm text-[#6B7280]">Keberatan terhadap pemrosesan data Anda untuk
                                        tujuan tertentu</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="p-5 mt-6 border-2 border-orange-200 bg-gradient-to-r from-orange-50 to-red-50 rounded-2xl">
                            <p class="text-sm text-[#6B7280]">
                                <strong class="text-[#111827]">Untuk menggunakan hak Anda:</strong> Hubungi kami di
                                <a href="mailto:privacy@bhayascientia.id"
                                    class="text-[#FF6B18] font-bold hover:underline">privacy@bhayascientia.id</a>
                            </p>
                        </div>
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
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Cookies & Teknologi Pelacakan
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280] mb-4">
                            Kami menggunakan cookies untuk meningkatkan pengalaman Anda. Anda dapat mengelola preferensi
                            cookies melalui pengaturan browser.
                        </p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="p-4 border-2 border-gray-200 rounded-xl">
                                <h4 class="font-bold text-[#111827] text-sm mb-2">🍪 Cookies Esensial</h4>
                                <p class="text-xs text-[#6B7280]">Diperlukan untuk login dan fungsi dasar platform</p>
                            </div>

                            <div class="p-4 border-2 border-gray-200 rounded-xl">
                                <h4 class="font-bold text-[#111827] text-sm mb-2">📊 Cookies Analitik</h4>
                                <p class="text-xs text-[#6B7280]">Membantu kami memahami penggunaan platform</p>
                            </div>
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
                        <h2 class="text-2xl sm:text-3xl font-black text-[#111827] mt-1">
                            Perubahan Kebijakan
                        </h2>
                    </div>

                    <div class="ml-0 sm:ml-13">
                        <p class="text-[#6B7280]">
                            Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan signifikan akan
                            diberitahukan melalui email atau notifikasi di platform. Dengan terus menggunakan layanan
                            kami setelah perubahan diberlakukan, Anda menyetujui kebijakan yang diperbarui.
                        </p>
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
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="mb-2 text-2xl font-black">Punya Pertanyaan?</h3>
                    <p class="mb-6 text-white/90">
                        Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami:
                    </p>

                    <div class="space-y-3 text-sm">
                        <p>
                            <strong>Email:</strong>
                            <a href="mailto:privacy@bhayascientia.id"
                                class="underline hover:text-white/80">privacy@bhayascientia.id</a>
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
