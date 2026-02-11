{{-- resources/views/components/how-it-works.blade.php (WITH MODAL) --}}

@props([
'badge' => 'Cara kerja',
'title' => '3 langkah, naskah siap tayang.',
'description' => 'Daftar, submit, revisi. Naskah diterima? kami publikasikan otomatis di BHAYACIENTIA.',
'steps' => [
[
'title' => 'Daftar akun',
'desc' => 'Buat akun untuk mulai submit naskah jurnal, buku, atau opini.',
'icon' => 'assets/images/icons/crown.svg',
'details' => 'Proses pendaftaran sangat mudah dan cepat. Cukup isi email, buat password, dan verifikasi akun. Setelah
terdaftar, kamu bisa langsung mengakses dashboard untuk submit naskah pertamamu.',
'benefits' => [
'Gratis dan tanpa biaya tersembunyi',
'Akses penuh ke dashboard penulis',
'Notifikasi real-time via email',
'Profil penulis yang dapat dikustomisasi',
'Riwayat submission lengkap'
],
'cta' => 'Daftar Sekarang',
'cta_link' => '/register'
],
[
'title' => 'Submit & revisi',
'desc' => 'Kirim naskahmu. Kami review, kamu revisi sampai lebih rapi dan siap dibaca.',
'icon' => 'assets/images/icons/crown.svg',
'details' => 'Upload naskahmu dalam format PDF atau Word. Tim reviewer kami akan melakukan review dalam 3-5 hari kerja.
Kamu akan mendapat feedback detail untuk perbaikan jika diperlukan.',
'benefits' => [
'Review profesional dari tim ahli',
'Feedback konstruktif dan detail',
'Unlimited revisi hingga sempurna',
'Track progress submission real-time',
'Komunikasi langsung dengan reviewer'
],
'cta' => 'Pelajari Proses Review',
'cta_link' => '/submission-guidelines'
],
[
'title' => 'Terbit otomatis',
'desc' => 'Jika diterima, naskah akan tayang otomatis di platform kami sebagai portofolio publikasi awalmu.',
'icon' => 'assets/images/icons/crown.svg',
'details' => 'Setelah naskah disetujui, publikasi dilakukan otomatis dalam 24 jam. Naskahmu akan mendapat DOI, dapat
diakses publik, dan diindeks di berbagai search engine untuk meningkatkan visibilitas.',
'benefits' => [
'DOI otomatis untuk setiap publikasi',
'Indexing di Google Scholar & Crossref',
'Dashboard statistik views & downloads',
'Sertifikat publikasi digital',
'Share link profesional ke sosial media'
],
'cta' => 'Lihat Contoh Publikasi',
'cta_link' => '/publications'
],
],
'arrowTop' => 'assets/images/icons/arrow-top.svg',
'arrowBottom' => 'assets/images/icons/arrow-bottom.svg',
])

<section class="mt-6 sm:mt-10" data-steps-section>
    <div class="mx-auto max-w-[1130px] px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 lg:pt-12">
        <div class="text-center">
            <p class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18]">
                {{ $badge }}
            </p>

            <h2 class="mt-3 text-2xl font-bold text-[#111827] sm:text-3xl">
                {{ $title }}
            </h2>

            <p class="mx-auto mt-2 max-w-2xl text-sm leading-[21px] text-[#6B7280] sm:text-base sm:leading-[24px]">
                {{ $description }}
            </p>
        </div>

        <div class="w-full mt-6">
            {{-- Arrow Top (desktop only) --}}
            <div class="hidden lg:block">
                <img src="{{ asset($arrowTop) }}" alt="" class="mb-3 ml-10 select-none" aria-hidden="true">
            </div>

            {{-- Steps --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 lg:gap-10">
                @foreach ($steps as $index => $step)
                <article
                    class="group step-card rounded-2xl border border-[#EEF0F4] bg-white p-4 sm:p-5 transition-all duration-300 hover:border-[#FF6B18] hover:shadow-xl hover:-translate-y-1 cursor-pointer"
                    data-step-item data-step-index="{{ $index }}" tabindex="0" role="button"
                    aria-label="Lihat detail {{ $step['title'] }}">

                    <div class="flex items-center gap-3">
                        <div
                            class="step-icon flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6B18] transition-transform duration-300 group-hover:scale-110">
                            <img src="{{ asset($step['icon']) }}" alt="" class="w-5 h-5" aria-hidden="true">
                        </div>

                        <h3
                            class="text-[18px] font-semibold leading-[26px] text-[#111827] group-hover:text-[#FF6B18] transition-colors">
                            {{ $step['title'] }}
                        </h3>
                    </div>

                    <p class="mt-3 max-w-[52ch] text-[14px] font-medium leading-6 text-[#6B7280] sm:text-[15px]">
                        {{ $step['desc'] }}
                    </p>

                    {{-- Click indicator --}}
                    <div
                        class="mt-4 flex items-center gap-2 text-xs font-semibold text-[#FF6B18] opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <span>Lihat detail</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </article>
                @endforeach
            </div>

            {{-- Arrow Bottom (desktop only) --}}
            <div class="hidden lg:block">
                <img src="{{ asset($arrowBottom) }}" alt="" class="mt-3 ml-[560px] select-none" aria-hidden="true">
            </div>
        </div>
    </div>

    {{-- ✨ MODAL POPUP --}}
    <div id="stepModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300"
        data-modal role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div
            class="modal-content bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">

            {{-- Modal Header --}}
            <div
                class="sticky top-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-5 flex items-start justify-between z-10">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/20">
                            <img id="modalIcon" src="" alt="" class="w-5 h-5" aria-hidden="true">
                        </div>
                        <span class="text-sm font-bold text-white/90">Langkah <span id="modalStepNumber"></span></span>
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
            <div class="overflow-y-auto max-h-[calc(90vh-180px)] px-6 py-6 space-y-6">

                {{-- Description --}}
                <div>
                    <h4 class="text-sm font-bold text-[#111827] mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        Detail Proses
                    </h4>
                    <p id="modalDetails" class="text-sm sm:text-base text-[#6B7280] leading-relaxed">
                        <!-- Details dari JS -->
                    </p>
                </div>

                {{-- Benefits List --}}
                <div>
                    <h4 class="text-sm font-bold text-[#111827] mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Keuntungan
                    </h4>
                    <ul id="modalBenefits" class="space-y-2">
                        <!-- Benefits dari JS -->
                    </ul>
                </div>

                {{-- CTA Button --}}
                <div class="pt-4">
                    <a id="modalCta" href="#"
                        class="block w-full text-center px-6 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <!-- CTA text dari JS -->
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Enhanced JavaScript --}}
@pushOnce('scripts')
<script>
    (function() {
    'use strict';

    const section = document.querySelector('[data-steps-section]');
    if (!section) return;

    // Data steps
    const stepsData = @json($steps);

    // Elements
    const modal = section.querySelector('[data-modal]');
    const modalContent = modal?.querySelector('.modal-content');
    const closeModalBtn = modal?.querySelector('[data-close-modal]');
    const stepCards = section.querySelectorAll('[data-step-item]');

    // ✅ Open Modal
    stepCards.forEach(card => {
        const openModal = () => {
            const index = parseInt(card.dataset.stepIndex);
            const step = stepsData[index];
            if (!step || !modal) return;

            // Populate modal
            modal.querySelector('#modalIcon').src = "{{ asset('') }}" + step.icon;
            modal.querySelector('#modalStepNumber').textContent = index + 1;
            modal.querySelector('#modalTitle').textContent = step.title || '';
            modal.querySelector('#modalSubtitle').textContent = step.desc || '';
            modal.querySelector('#modalDetails').textContent = step.details || '';

            // Benefits list
            const benefitsList = modal.querySelector('#modalBenefits');
            if (benefitsList && step.benefits) {
                benefitsList.innerHTML = step.benefits.map(benefit => `
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-[#6B7280]">${benefit}</span>
                    </li>
                `).join('');
            }

            // CTA
            const ctaBtn = modal.querySelector('#modalCta');
            if (ctaBtn) {
                ctaBtn.textContent = step.cta || 'Selengkapnya';
                ctaBtn.href = step.cta_link || '#';
            }

            // Show modal
            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.classList.add('pointer-events-auto', 'opacity-100');

            setTimeout(() => {
                if (modalContent) {
                    modalContent.style.transform = 'scale(1)';
                }
            }, 10);

            // Lock body scroll
            document.body.style.overflow = 'hidden';
        };

        // Click handler
        card.addEventListener('click', openModal);

        // Keyboard handler
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal();
            }
        });
    });

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

    console.log('✅ How It Works with Modal initialized');
})();
</script>
@endPushOnce

{{-- Modal Styles --}}
@pushOnce('styles')
<style>
    #stepModal.pointer-events-auto {
        display: flex !important;
    }

    .modal-content {
        transform: scale(0.95);
    }

    /* Hover effect untuk cards */
    .step-card {
        position: relative;
    }

    .step-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        background: linear-gradient(135deg, rgba(255, 107, 24, 0.1) 0%, rgba(230, 70, 39, 0.1) 100%);
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .step-card:hover::after {
        opacity: 1;
    }

    /* Focus visible */
    .step-card:focus-visible {
        outline: 3px solid #FF6B18;
        outline-offset: 2px;
    }
</style>
@endPushOnce
