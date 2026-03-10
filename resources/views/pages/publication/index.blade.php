@extends('layouts.app')

@section('title', 'Browse Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('custom_navbar')
<x-navbar ctaLabel="Mulai Berlangganan" ctaRoute="subscription.index" ctaIcon="sparkles" ctaSubtext="Gratis"
    ctaVariant="premium" :showAvatarWhenAuth="true" :showCtaAlways="true" />

<x-publication-search-filter :selectedType="$selectedType" :categories="$categories ?? []" :years="$years ?? []"
    :topKeywords="$topKeywords ?? []" :filterCategory="null" :filterYear="null" :filterKeyword="null"
    :filterSort="$filterSort" :searchQuery="$searchQuery" />
@endsection

@section('content')

{{-- ✨ Anchor scroll ke atas --}}
<div id="top-anchor"></div>

{{-- Publication Navigation (SUB MENU) --}}
<x-publication.navigation :items="config('publication.navigation')" />

{{-- Hero Section --}}
<x-hero.publication />

{{-- Main Content Section --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-10 sm:mt-12">

    {{-- Quick Filter Bar --}}
    <div id="tour-filter-bar">
        <x-publication.filter-bar title="Pilih Jenis Publikasi" helper="Buku, Jurnal, atau Opini"
            :types="$publicationTypes ?? []" :selectedType="$selectedType" :filterSort="$filterSort"
            :hasActiveFilters="false" />
    </div>

    {{-- Search Trigger Bar --}}
    <div class="flex items-center gap-2 mt-4 mb-8 sm:gap-3" id="tour-search-bar">

        {{-- Search Button --}}
        <button onclick="openPublicationSearch()"
            class="flex-1 flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-0 bg-white border-2 border-[#EEF0F7] rounded-2xl hover:border-[#FF6B18] hover:shadow-md transition-all group text-left h-12 sm:h-14">

            {{-- Icon Search --}}
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#A3A6AE] group-hover:text-[#FF6B18] transition-colors flex-shrink-0"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            {{-- Placeholder / Query Text --}}
            <span class="text-xs sm:text-sm text-[#A3A6AE] group-hover:text-[#737373] transition-colors truncate">
                {{ $searchQuery ? '"'.$searchQuery.'"' : 'Cari judul, penulis, kata kunci...' }}
            </span>

            {{-- Badge Aktif --}}
            @if($searchQuery)
            <span class="ml-auto flex-shrink-0 text-xs font-bold text-[#FF6B18] bg-[#FFF7F2] px-2 py-1 rounded-lg">
                Aktif
            </span>
            @endif
        </button>

        {{-- Filter Button --}}
        <button onclick="openPublicationSearch()"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-3 sm:px-4 bg-white border-2 border-[#EEF0F7] rounded-2xl hover:border-[#FF6B18] hover:bg-[#FFF7F2] hover:text-[#FF6B18] transition-all text-[#737373] font-semibold text-sm h-12 sm:h-14">
            <svg class="flex-shrink-0 w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <span class="hidden sm:inline">Filter</span>
        </button>

    </div>

    {{-- Latest Publications --}}
    <div id="tour-publications">
        <x-publication.swiper-section title="Tulisan Terbaru <br />Untuk Diskursus yang Bertanggung Jawab"
            badge="TERKINI" swiperClass="upToDateSwiper">
            @forelse($latestPublications as $publication)
            @php
            $words = array_filter(explode(' ', $publication['title'] ?? ''));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
            }
            if (empty($initials)) {
            $initials = mb_strtoupper(mb_substr($publication['title'] ?? 'UN', 0, 2));
            }
            $firstAuthor = $publication['first_author_name'] ??
            (isset($publication['authors']) && count($publication['authors']) > 0
            ? ($publication['authors'][0]['name'] ?? 'Unknown')
            : 'Anonymous');
            $publicationType = $publication['publication_type'] ?? $publication['type'] ?? 'Publikasi';
            $placeholderUrl = route('placeholder.cover', [
            'initials' => $initials,
            'type' => $publicationType,
            'title' => $publication['title'] ?? 'Untitled',
            'category' => $publication['category'] ?? 'Umum',
            'author' => $firstAuthor,
            ]);
            $coverImage = !empty($publication['cover_url']) ? $publication['cover_url'] : $placeholderUrl;
            @endphp
            <x-publication.card :title="$publication['title'] ?? 'Untitled'" :cover="$coverImage"
                :category="$publication['category'] ?? 'Umum'" :publicationType="$publicationType"
                :date="$publication['formatted_date'] ?? ''" :authors="$publication['authors'] ?? []"
                :totalAuthors="$publication['total_authors'] ?? 0" :detailUrl="$publication['detail_url'] ?? '#'"
                :slug="$publication['slug'] ?? ''" />
            @empty
            <div class="swiper-slide">
                <div class="bg-white p-12 rounded-2xl border-2 border-dashed border-[#EEF0F7] text-center">
                    <svg class="w-20 h-20 mx-auto text-[#EEF0F7] mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-[#A3A6AE] text-lg font-bold mb-2">Belum Ada Publikasi</p>
                    <p class="text-[#737373] text-sm">Belum ada publikasi tersedia untuk kategori ini</p>
                </div>
            </div>
            @endforelse
        </x-publication.swiper-section>
    </div>

</section>

{{-- Best Authors Section --}}
<div id="tour-authors">
    <x-publication.best-authors :authors="$bestAuthors ?? collect([])"
        title="Penulis Terbaik<br/>dengan Kontribusi Terbanyak" badge="PENULIS TERBAIK" :selectedType="$selectedType" />
</div>

{{-- Popular Publications Section --}}
<div id="tour-popular">
    <x-publication.popular-section :featuredTypeContent="$featuredTypeContent ?? null"
        :featuredPublication="$featuredPublication ?? null" :publications="$popularPublications ?? collect([])"
        :selectedType="$selectedType" :exploreAllUrl="route('publikasi.browse', ['type' => $selectedType])" />
</div>

{{-- ✨ Scroll to Top --}}
<x-scroll-to-top />

{{-- ✨ Scroll to Top Script --}}
<x-scroll-to-top-script />

{{-- ================================================================ --}}
{{-- PRODUCT TOUR --}}
{{-- ================================================================ --}}
@if($showTour ?? false)

<style>
    .tour-panel {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        transition: top 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            left 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            width 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            height 0.45s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes tour-ring-pulse {

        0%,
        100% {
            box-shadow: 0 0 0 3px #FF6B18, 0 0 0 6px rgba(255, 107, 24, 0.4), 0 0 24px rgba(255, 107, 24, 0.25);
        }

        50% {
            box-shadow: 0 0 0 3px #FF6B18, 0 0 0 11px rgba(255, 107, 24, 0.15), 0 0 36px rgba(255, 107, 24, 0.1);
        }
    }

    .tour-spotlight {
        animation: tour-ring-pulse 2.2s ease-in-out infinite;
        transition: top 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            left 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            width 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            height 0.45s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<div x-data="indexTour()" x-init="init()" id="index-tour">

    <template x-if="active">
        <div aria-hidden="true">
            <div class="tour-panel fixed z-[9998] pointer-events-auto"
                :style="`top:0;left:0;right:0;height:${sr.top}px`" @click="finish()"></div>
            <div class="tour-panel fixed z-[9998] pointer-events-auto"
                :style="`top:${sr.bottom}px;left:0;right:0;bottom:0`" @click="finish()"></div>
            <div class="tour-panel fixed z-[9998] pointer-events-auto"
                :style="`top:${sr.top}px;left:0;width:${sr.left}px;height:${sr.height}px`" @click="finish()"></div>
            <div class="tour-panel fixed z-[9998] pointer-events-auto"
                :style="`top:${sr.top}px;left:${sr.right}px;right:0;height:${sr.height}px`" @click="finish()"></div>
        </div>
    </template>

    <div x-show="active" class="tour-spotlight fixed z-[9999] pointer-events-none rounded-2xl" :style="spotlightStyle">
    </div>

    <div x-show="active" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed z-[10000] w-[calc(100vw-24px)] sm:w-[340px]"
        :style="tooltipStyle">
        <div class="overflow-hidden bg-white shadow-2xl rounded-2xl border border-[#FFE4D6]">

            <div
                class="bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-4 py-3 sm:px-5 sm:py-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg font-black text-white" x-text="currentStep.emoji"></span>
                    <div>
                        <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider leading-none mb-0.5">
                            Panduan Penggunaan
                        </p>
                        <p class="text-xs font-semibold leading-none text-white">
                            Langkah <span x-text="stepIndex + 1"></span> dari <span x-text="steps.length"></span>
                        </p>
                    </div>
                </div>
                <button @click="finish()"
                    class="flex items-center justify-center flex-shrink-0 text-white transition-colors rounded-full w-7 h-7 bg-white/20 hover:bg-white/30 focus:outline-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-4 py-3 sm:px-5 sm:py-4">
                <h3 class="text-sm font-bold text-[#111827] mb-1.5" x-text="currentStep.title"></h3>
                <p class="text-xs text-[#6B7280] leading-relaxed" x-text="currentStep.description"></p>
            </div>

            <div class="flex items-center justify-between px-4 pb-4 sm:px-5">
                <div class="flex items-center gap-1.5">
                    <template x-for="(s, i) in steps" :key="i">
                        <div class="transition-all duration-300 rounded-full" :class="i === stepIndex
                                ? 'w-5 h-2 bg-[#FF6B18]'
                                : i < stepIndex
                                    ? 'w-2 h-2 bg-[#FF6B18]/40'
                                    : 'w-2 h-2 bg-[#EEF0F7]'">
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <button x-show="stepIndex > 0" @click="prevStep()"
                        class="px-3 py-1.5 text-xs font-bold text-[#6B7280] bg-[#F4F6FB] rounded-full hover:bg-[#EEF0F7] transition-colors focus:outline-none">
                        ← Kembali
                    </button>
                    <button x-show="stepIndex === 0" @click="finish()"
                        class="px-3 py-1.5 text-xs font-semibold text-[#A3A6AE] hover:text-[#6B7280] transition-colors focus:outline-none">
                        Lewati
                    </button>
                    <button @click="stepIndex < steps.length - 1 ? nextStep() : finish()"
                        class="px-4 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-full hover:shadow-md transition-all focus:outline-none active:scale-95"
                        x-text="stepIndex < steps.length - 1 ? 'Lanjut →' : '✓ Mengerti!'">
                    </button>
                </div>
            </div>

        </div>

        <div class="absolute w-3 h-3 rotate-45 bg-white border border-[#FFE4D6] hidden sm:block" :style="arrowStyle">
        </div>

    </div>

</div>

<script>
    function indexTour() {
    return {
        active: false,
        stepIndex: 0,
        spotlightStyle: '',
        tooltipStyle: '',
        arrowStyle: '',
        sr: { top: 0, bottom: 0, left: 0, right: 0, height: 0 },

        steps: [
            {
                emoji: '🗂️',
                targetId: 'tour-filter-bar',
                title: 'Filter Jenis Publikasi',
                description: 'Pilih jenis publikasi — Buku, Jurnal, Opini, atau semua sekaligus. Filter ini menyesuaikan seluruh konten halaman secara otomatis.',
                position: 'bottom',
            },
            {
                emoji: '🔍',
                targetId: 'tour-search-bar',
                title: 'Cari & Filter Publikasi',
                description: 'Cari judul, penulis, atau kata kunci. Tombol Filter membuka opsi lanjutan seperti tahun dan kategori.',
                position: 'bottom',
            },
            {
                emoji: '📄',
                targetId: 'tour-publications',
                title: 'Tulisan Terbaru',
                description: 'Karya ilmiah terbaru dari insan Bhayangkara dan akademisi DABRAKA. Geser kanan-kiri untuk melihat lebih banyak.',
                position: 'auto',
            },
            {
                emoji: '✍️',
                targetId: 'tour-authors',
                title: 'Penulis Terbaik',
                description: 'Kenali kontributor terbaik DABRAKA. Klik profil penulis untuk melihat semua karya dan jejak akademis mereka.',
                position: 'auto',
            },
            {
                emoji: '🔥',
                targetId: 'tour-popular',
                title: 'Publikasi Populer',
                description: 'Publikasi yang paling banyak dibaca dan diunduh. Temukan karya terpopuler sesuai jenis yang kamu pilih.',
                position: 'auto',
            },
        ],

        get currentStep() {
            return this.steps[this.stepIndex];
        },

        init() {
            setTimeout(() => {
                this.active = true;
                this.updateSpotlight();
            }, 900);

            window.addEventListener('resize', () => {
                if (this.active) this.updateSpotlight();
            });
            window.addEventListener('scroll', () => {
                if (this.active) this.recalcPosition();
            }, { passive: true });
        },

        updateSpotlight() {
            const el = document.getElementById(this.currentStep.targetId);
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => this.recalcPosition(), 500);
        },

        recalcPosition() {
            const el = document.getElementById(this.currentStep.targetId);
            if (!el) return;

            const rect = el.getBoundingClientRect();
            const pad  = 8;
            const vh   = window.innerHeight;
            const vw   = window.innerWidth;

            const top    = rect.top - pad;
            const left   = rect.left - pad;
            const width  = rect.width + pad * 2;
            const height = rect.height + pad * 2;
            const bottom = top + height;
            const right  = left + width;

            this.spotlightStyle = `top:${top}px;left:${left}px;width:${width}px;height:${height}px;`;
            this.sr = { top, bottom, left, right, height };

            this.positionTooltip(top, bottom, left, right, width, vh, vw);
        },

        positionTooltip(spTop, spBottom, spLeft, spRight, spWidth, vh, vw) {
            const isMobile  = vw < 640;
            const tooltipW  = isMobile ? vw - 24 : 340;
            const tooltipH  = 185;
            const margin    = 12;
            const arrowSize = 12;
            const pos       = this.currentStep.position;

            let top, left, arrowTop, arrowBottom, arrowLeft;

            left = spLeft + spWidth / 2 - tooltipW / 2;
            left = isMobile ? 12 : Math.max(margin, Math.min(left, vw - tooltipW - margin));

            const spaceBelow = vh - spBottom - margin;
            const spaceAbove = spTop - margin;

            // ✅ ATURAN BARU:
            // - 'bottom': selalu coba bawah dulu, tapi kalau tidak muat → atas
            // - 'auto'  : pilih sisi yang punya LEBIH BANYAK ruang
            // - Kalau tooltip terpotong di bawah (sisa < tooltipH) → paksa ke atas

            let goBelow;

            if (pos === 'bottom') {
                // Coba bawah, tapi kalau ruang di bawah tidak cukup → atas
                goBelow = spaceBelow >= tooltipH + arrowSize;
            } else {
                // 'auto' atau 'top': pilih sisi yang lebih lega
                goBelow = spaceBelow >= spaceAbove && spaceBelow >= tooltipH + arrowSize;
            }

            if (goBelow) {
                top         = spBottom + arrowSize + margin;
                arrowTop    = `-${arrowSize / 2}px`;
                arrowBottom = 'auto';
            } else {
                // Taruh di ATAS spotlight
                top         = spTop - tooltipH - arrowSize - margin;
                arrowTop    = 'auto';
                arrowBottom = `-${arrowSize / 2}px`;
            }

            // ✅ Final safety clamp — tooltip tidak boleh keluar viewport
            // Kalau atas masih terpotong (jarang), geser ke posisi paling atas yang muat
            top = Math.max(margin, Math.min(top, vh - tooltipH - margin));

            arrowLeft = `${(spLeft + spWidth / 2) - left - arrowSize / 2}px`;

            this.tooltipStyle = `top:${top}px;left:${left}px;width:${tooltipW}px;`;
            this.arrowStyle   = `top:${arrowTop};bottom:${arrowBottom ?? 'auto'};left:${arrowLeft};`;
        },

        nextStep() {
            if (this.stepIndex < this.steps.length - 1) {
                this.stepIndex++;
                this.updateSpotlight();
            }
        },

        prevStep() {
            if (this.stepIndex > 0) {
                this.stepIndex--;
                this.updateSpotlight();
            }
        },

        finish() {
            this.active = false;
            fetch('{{ route('tour.complete', ['page' => 'index']) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            });
        },
    }
}
</script>
@endif

@endsection