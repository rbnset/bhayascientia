{{-- resources/views/components/coming-soon.blade.php (WITH MODAL) --}}

@props([
'badge' => 'Fitur pengembangan',
'title' => 'Fitur berikutnya',
'description' => 'Roadmap berikutnya fokus pada pembelajaran, event, konsultasi dan artikel untuk memperluas akses
publik terhadap gagasan akademik yang bertanggung jawab.',
'cards' => [
[
'key' => 'LMS',
'title' => 'LMS',
'subtitle' => 'Kelas, modul, kuis, sertifikat',
'image' => 'assets/images/thumbnails/lms.jpg',
'aria' => 'Lihat detail LMS',
'description' => 'Platform Learning Management System (LMS) yang memungkinkan dosen dan penulis membuat kelas online,
modul pembelajaran interaktif, kuis evaluasi, dan memberikan sertifikat kepada peserta yang menyelesaikan
pembelajaran.',
'features' => [
'Pembuatan kelas online dengan materi multimedia',
'Modul pembelajaran interaktif dan responsif',
'Sistem kuis dan ujian dengan auto-grading',
'Sertifikat digital otomatis untuk peserta',
'Dashboard progress tracking untuk peserta',
'Forum diskusi dan Q&A dengan instruktur'
],
'launch_date' => 'Q3 2026'
],
[
'key' => 'Event',
'title' => 'Event',
'subtitle' => 'Webinar, seminar, workshop',
'image' => 'assets/images/thumbnails/event.jpg',
'aria' => 'Lihat detail Event',
'description' => 'Platform manajemen event akademik yang memudahkan penyelenggaraan webinar, seminar, dan workshop.
Lengkap dengan sistem registrasi, reminder otomatis, dan sertifikat kehadiran.',
'features' => [
'Manajemen event webinar dan seminar online',
'Sistem registrasi peserta terintegrasi',
'Email reminder otomatis untuk peserta',
'Live streaming terintegrasi Zoom/Meet',
'Sertifikat kehadiran otomatis',
'Rekaman event untuk akses on-demand'
],
'launch_date' => 'Q2 2026'
],
[
'key' => 'Konsultasi',
'title' => 'Konsultasi',
'subtitle' => 'Rapikan struktur & sitasi',
'image' => 'assets/images/thumbnails/konsultasi.jpg',
'aria' => 'Lihat detail Konsultasi',
'description' => 'Layanan konsultasi akademik profesional untuk membantu mahasiswa dan peneliti merapikan struktur
penulisan, memperbaiki sitasi, dan meningkatkan kualitas karya ilmiah mereka.',
'features' => [
'Konsultasi struktur penulisan karya ilmiah',
'Review dan perbaikan sistem sitasi (APA, IEEE, dll)',
'Pengecekan plagiarisme dan similarity',
'Saran perbaikan metodologi penelitian',
'Konsultasi statistik dan analisis data',
'Booking jadwal konsultasi online/offline'
],
'launch_date' => 'Q4 2026'
],
[
'key' => 'Artikel',
'title' => 'Artikel',
'subtitle' => 'Insight, studi kasus, komunitas',
'image' => 'assets/images/thumbnails/blog.jpg',
'aria' => 'Lihat detail Artikel',
'description' => 'Platform artikel dan blog akademik yang menyajikan insight terkini, studi kasus menarik, dan membangun
komunitas diskusi antar akademisi dan praktisi.',
'features' => [
'Artikel insight dari para ahli dan praktisi',
'Studi kasus nyata dari berbagai bidang',
'Komunitas diskusi dan networking',
'Sistem komentar dan feedback interaktif',
'Newsletter mingguan artikel terpilih',
'Kontribusi artikel dari komunitas'
],
'launch_date' => 'Q2 2026'
],
],
'iconStar' => 'assets/images/icons/star.svg',
'iconCrown' => 'assets/images/icons/crown.svg',
'iconArrow' => 'assets/images/icons/sign_right.svg',
])

@php
$cards = is_array($cards) ? $cards : [];
$uid = 'comingSoon_' . substr(md5(json_encode($cards)), 0, 8);
@endphp

<section class="pt-12 mt-10 sm:mt-12" data-coming-soon="{{ $uid }}">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="text-center">
            <p class="px-4 py-2 text-xs font-bold inline-flex items-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                {{ $badge }}
            </p>

            <h2 class="mt-4 text-2xl font-bold sm:text-3xl text-[#111827]">
                {{ $title }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm sm:text-base sm:leading-[24px] mx-auto leading-[21px] text-[#6B7280]">
                {{ $description }}
            </p>
        </div>

        {{-- Cards --}}
        <div class="mt-8">
            <div class="relative">
                {{-- Gradient hints mobile --}}
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 w-10 bg-gradient-to-r from-[#F8F9FC] to-transparent lg:hidden">
                </div>
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-[#F8F9FC] to-transparent lg:hidden">
                </div>

                <div id="roadmapCards"
                    class="flex gap-5 pb-4 overflow-x-auto lg:pb-0 lg:overflow-x-hidden overscroll-x-contain scroll-smooth snap-x snap-mandatory"
                    aria-label="Roadmap fitur berikutnya" data-roadmap-wrap>

                    @foreach ($cards as $i => $c)
                    <article
                        class="card snap-start group rounded-3xl sm:h-[475px] sm:w-[320px] lg:shrink relative h-[360px] w-[260px] shrink-0 overflow-clip transition-all duration-300 border border-transparent hover:border-[#FF6B18] hover:shadow-2xl"
                        data-card="{{ $i + 1 }}" data-key="{{ $c['key'] ?? ($i + 1) }}" tabindex="0">

                        {{-- Background Image --}}
                        <img src="{{ asset($c['image'] ?? '') }}" alt="{{ $c['title'] ?? '' }}"
                            class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110" />

                        {{-- Dark Overlay --}}
                        <div
                            class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent group-hover:opacity-100">
                        </div>

                        {{-- Info Card Overlay --}}
                        <div
                            class="card-info left-6 right-6 bottom-6 gap-2 rounded-3xl bg-white p-6 ease-in-out absolute opacity-0 group-hover:opacity-100 flex items-center transition-all duration-300 border border-[#EEF0F7] shadow-xl z-10">
                            <div class="flex flex-col flex-1 min-w-0 gap-1">
                                <div class="flex items-center">
                                    <img src="{{ asset($iconStar) }}" alt="" class="-mt-[3px] mr-[3px] h-auto w-[18px]"
                                        aria-hidden="true" />
                                    <p class="mr-1 font-semibold leading-6 text-[16px] text-[#FF6B18]">Segera</p>
                                    <p class="leading-6 text-[16px] text-[#6B7280]">(Roadmap)</p>
                                </div>

                                <h3 class="text-lg font-semibold leading-[27px] text-[#111827]">
                                    {{ $c['title'] ?? '' }}
                                </h3>

                                <div class="gap-1.5 flex">
                                    <img src="{{ asset($iconCrown) }}" alt="" class="w-6 h-6" aria-hidden="true" />
                                    <p class="leading-6 text-[16px] text-[#6B7280]">
                                        {{ $c['subtitle'] ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <button type="button" data-open-modal="{{ $c['key'] ?? '' }}"
                                class="h-9 w-9 rounded-xl flex shrink-0 items-center justify-center bg-[#FF6B18] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] hover:scale-110 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                                aria-label="{{ $c['aria'] ?? 'Lihat detail' }}">
                                <img src="{{ asset($iconArrow) }}" alt="" aria-hidden="true" />
                            </button>
                        </div>
                    </article>
                    @endforeach
                </div>

                <p class="mt-2 text-xs lg:hidden text-[#6B7280]">
                    Geser untuk melihat fitur lainnya.
                </p>
            </div>
        </div>
    </div>

    {{-- ✨ MODAL POPUP --}}
    <div id="featureModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300"
        data-modal role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-content bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300"
            data-modal-content>

            {{-- Modal Header --}}
            <div
                class="sticky top-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-5 flex items-start justify-between z-10">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <img src="{{ asset($iconStar) }}" alt="" class="w-5 h-5" aria-hidden="true" />
                        <span class="text-sm font-bold text-white/90">Segera Hadir</span>
                        <span id="modalLaunchDate"
                            class="text-xs font-semibold px-2 py-0.5 bg-white/20 rounded-full text-white"></span>
                    </div>
                    <h3 id="modalTitle" class="text-2xl font-black text-white sm:text-3xl">
                        <!-- Title dari JS -->
                    </h3>
                    <p id="modalSubtitle" class="mt-1 text-sm sm:text-base text-white/90">
                        <!-- Subtitle dari JS -->
                    </p>
                </div>

                <button type="button" data-close-modal
                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 ml-4 transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                    aria-label="Tutup modal">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto max-h-[calc(90vh-140px)] px-6 py-6 space-y-6">

                {{-- Description --}}
                <div>
                    <h4 class="text-sm font-bold text-[#111827] mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        Tentang Fitur Ini
                    </h4>
                    <p id="modalDescription" class="text-sm sm:text-base text-[#6B7280] leading-relaxed">
                        <!-- Description dari JS -->
                    </p>
                </div>

                {{-- Features List --}}
                <div>
                    <h4 class="text-sm font-bold text-[#111827] mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                        Fitur Utama
                    </h4>
                    <ul id="modalFeatures" class="space-y-2">
                        <!-- Features dari JS -->
                    </ul>
                </div>

                {{-- Call to Action --}}
                <div class="p-5 border-2 border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FF6B18] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-[#111827] mb-1">Ingin Diberitahu?</h5>
                            <p class="text-sm text-[#6B7280] mb-3">Kami akan kirim notifikasi email saat fitur ini sudah
                                tersedia.</p>
                            <button type="button" data-notify-me
                                class="px-5 py-2.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                                🔔 Beritahu Saya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Notification (untuk konfirmasi) --}}
    <div id="notificationToast"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[10000] w-[92%] max-w-md pointer-events-none opacity-0 transform translate-y-4 transition-all duration-300"
        role="status" aria-live="polite" data-toast>
        <div class="flex items-center gap-3 px-5 py-4 bg-white border-2 border-green-200 shadow-2xl rounded-xl">
            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-green-100 rounded-xl">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-[#111827]">Notifikasi berhasil disimpan!</p>
                <p class="text-xs text-[#6B7280] mt-0.5">Kami akan kabari via email</p>
            </div>
        </div>
    </div>

    {{-- Data JSON --}}
    <script type="application/json" data-coming-soon-data="{{ $uid }}">
        {!! json_encode([
            'uid' => $uid,
            'cards' => $cards,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</section>

{{-- Enhanced JavaScript --}}
@pushOnce('scripts')
<script>
    (function() {
    'use strict';

    const uid = '{{ $uid }}';
    const section = document.querySelector(`[data-coming-soon="${uid}"]`);
    if (!section) return;

    // Get data
    const dataEl = section.querySelector(`[data-coming-soon-data="${uid}"]`);
    const data = dataEl ? JSON.parse(dataEl.textContent) : { cards: [] };

    // Elements
    const modal = section.querySelector('[data-modal]');
    const modalContent = modal?.querySelector('[data-modal-content]');
    const closeModalBtn = modal?.querySelector('[data-close-modal]');
    const openModalBtns = section.querySelectorAll('[data-open-modal]');
    const notifyBtn = modal?.querySelector('[data-notify-me]');
    const toast = section.querySelector('[data-toast]');

    let currentFeatureKey = null;

    // ✅ Open Modal
    openModalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const featureKey = btn.dataset.openModal;
            openModal(featureKey);
        });
    });

    function openModal(featureKey) {
        const feature = data.cards.find(c => c.key === featureKey);
        if (!feature || !modal) return;

        currentFeatureKey = featureKey;

        // Populate modal content
        modal.querySelector('#modalTitle').textContent = feature.title || '';
        modal.querySelector('#modalSubtitle').textContent = feature.subtitle || '';
        modal.querySelector('#modalLaunchDate').textContent = feature.launch_date || '';
        modal.querySelector('#modalDescription').textContent = feature.description || '';

        // Populate features list
        const featuresList = modal.querySelector('#modalFeatures');
        if (featuresList && feature.features) {
            featuresList.innerHTML = feature.features.map(f => `
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-[#6B7280]">${f}</span>
                </li>
            `).join('');
        }

        // Show modal with animation
        modal.classList.remove('pointer-events-none', 'opacity-0');
        modal.classList.add('pointer-events-auto', 'opacity-100');

        setTimeout(() => {
            if (modalContent) {
                modalContent.style.transform = 'scale(1)';
            }
        }, 10);

        // Lock body scroll
        document.body.style.overflow = 'hidden';
    }

    // ✅ Close Modal
    function closeModal() {
        if (!modal) return;

        if (modalContent) {
            modalContent.style.transform = 'scale(0.95)';
        }

        setTimeout(() => {
            modal.classList.remove('pointer-events-auto', 'opacity-100');
            modal.classList.add('pointer-events-none', 'opacity-0');
            document.body.style.overflow = '';
            currentFeatureKey = null;
        }, 150);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close on backdrop click
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('pointer-events-none')) {
            closeModal();
        }
    });

    // ✅ Notify Me Button
    if (notifyBtn) {
        notifyBtn.addEventListener('click', () => {
            // Button animation
            notifyBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                notifyBtn.style.transform = '';
            }, 150);

            // Close modal
            closeModal();

            // Show toast
            setTimeout(() => {
                showToast();
            }, 200);

            // Log
            console.log('📬 Notification requested for:', currentFeatureKey);

            // TODO: Send to backend
            // fetch('/api/notify-feature', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ feature: currentFeatureKey })
            // });
        });
    }

    // ✅ Toast Functions
    function showToast() {
        if (!toast) return;

        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');

        setTimeout(() => {
            hideToast();
        }, 5000);
    }

    function hideToast() {
        if (!toast) return;

        toast.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
        toast.classList.add('opacity-0', 'translate-y-4');
    }

    console.log('🚀 Coming Soon with Modal initialized:', uid);
})();
</script>
@endPushOnce

{{-- Modal Styles --}}
@pushOnce('styles')
<style>
    #featureModal.pointer-events-auto {
        display: flex !important;
    }

    .modal-content {
        transform: scale(0.95);
    }
</style>
@endPushOnce
