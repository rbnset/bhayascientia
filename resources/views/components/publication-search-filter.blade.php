@props([
'selectedType' => 'all',
'categories' => null,
'years' => null,
'topKeywords' => null,
'filterCategory' => null,
'filterYear' => null,
'filterKeyword' => null,
'filterSort' => 'latest',
'searchQuery' => null,
])

@php
$categories = ($categories instanceof \Illuminate\Support\Collection)
? $categories : collect((array) ($categories ?? []));
$years = ($years instanceof \Illuminate\Support\Collection)
? $years : collect((array) ($years ?? []));
$topKeywords = ($topKeywords instanceof \Illuminate\Support\Collection)
? $topKeywords : collect((array) ($topKeywords ?? []));

// Normalize filterKeyword jadi array selalu
$activeKeywords = collect();
if (!empty($filterKeyword)) {
$activeKeywords = collect(is_array($filterKeyword) ? $filterKeyword : [$filterKeyword]);
}
@endphp

{{-- Advanced Search & Filter Modal --}}
<div id="publicationSearchModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-md z-[60] hidden opacity-0 transition-opacity duration-300"
    role="dialog" aria-modal="true" aria-labelledby="searchModalTitle">

    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="publicationSearchContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col">

            {{-- Header --}}
            <div
                class="flex-shrink-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-5 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center min-w-0 gap-3">
                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-white/20 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="searchModalTitle" class="text-base font-bold leading-tight text-white sm:text-lg">
                            Cari & Filter Publikasi
                        </h2>
                        <p class="text-xs leading-tight text-white/80">Temukan publikasi yang Anda butuhkan</p>
                    </div>
                </div>
                <button type="button" onclick="closePublicationSearch()"
                    class="flex items-center justify-center flex-shrink-0 transition-all duration-300 w-9 h-9 bg-white/20 hover:bg-white/30 rounded-xl hover:rotate-90">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form method="GET" action="{{ route('publikasi.search') }}" id="publicationSearchForm"
                class="flex flex-col flex-1 min-h-0">
                <input type="hidden" name="type" value="{{ $selectedType }}">

                {{-- Hidden inputs keyword (dikontrol JS, support multi) --}}
                <div id="keywordInputsContainer">
                    @foreach($activeKeywords as $kw)
                    <input type="hidden" name="keyword[]" value="{{ $kw }}">
                    @endforeach
                </div>

                {{-- Scrollable Body --}}
                <div class="flex-1 p-5 space-y-5 overflow-y-auto">

                    {{-- Search Bar --}}
                    <div class="bg-[#FFF7F2] rounded-xl p-4 border border-[#FF6B18]/20">
                        <p class="text-xs sm:text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Pencarian Cepat
                        </p>
                        <div class="relative">
                            <input type="text" name="search" id="searchInput" value="{{ $searchQuery ?? '' }}"
                                placeholder="Cari judul, abstrak, penulis..." autocomplete="off"
                                class="w-full pl-11 pr-10 py-3 border border-[#EEF0F7] rounded-xl text-sm font-medium outline-none transition-all focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 bg-white">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#737373] pointer-events-none"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button type="button" id="clearSearchBtn"
                                onclick="document.getElementById('searchInput').value=''; this.classList.add('hidden')"
                                class="{{ $searchQuery ? '' : 'hidden' }} absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#FF6B18] text-white rounded-full flex items-center justify-center hover:bg-[#E64627] transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-2 text-[11px] text-[#737373]">💡 Tips: Gunakan kata kunci spesifik untuk hasil
                            lebih akurat</p>
                    </div>

                    {{-- Divider --}}
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#EEF0F7]"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-3 text-[10px] font-bold text-[#737373] bg-white uppercase tracking-widest">
                                Filter Lanjutan
                            </span>
                        </div>
                    </div>

                    {{-- Category & Year --}}
                    <div class="grid gap-4 sm:grid-cols-2">

                        @if($categories->count() > 0)
                        <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                            <p class="text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2.5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Kategori
                            </p>
                            <select name="category"
                                class="w-full px-3 py-2.5 border border-[#EEF0F7] rounded-xl text-sm font-medium outline-none transition-all focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 bg-white">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                @php
                                $catSlug = is_array($category) ? ($category['slug'] ?? '') : ($category->slug ?? '');
                                $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
                                $catCount = is_array($category) ? ($category['publications_count'] ?? 0) :
                                ($category->publications_count ?? 0);
                                @endphp
                                <option value="{{ $catSlug }}" {{ ($filterCategory ?? '' )==$catSlug ? 'selected' : ''
                                    }}>
                                    {{ $catName }}{{ $catCount > 0 ? ' (' . $catCount . ')' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($years->count() > 0)
                        <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                            <p class="text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2.5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tahun Publikasi
                            </p>
                            <select name="year"
                                class="w-full px-3 py-2.5 border border-[#EEF0F7] rounded-xl text-sm font-medium outline-none transition-all focus:border-[#FF6B18] focus:ring-2 focus:ring-[#FF6B18]/20 bg-white">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ ($filterYear ?? '' )==$year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                    </div>

                    {{-- Sort --}}
                    <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                        <p class="text-xs sm:text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            Urutkan Berdasarkan
                        </p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach([
                            ['value' => 'latest', 'label' => '🕐 Terbaru'],
                            ['value' => 'popular', 'label' => '🔥 Populer'],
                            ['value' => 'oldest', 'label' => '📅 Terlama'],
                            ['value' => 'title', 'label' => '🔤 A-Z'],
                            ] as $sortOption)
                            @php $isSortActive = ($filterSort ?? 'latest') == $sortOption['value']; @endphp
                            <button type="button" data-sort-btn data-sort-value="{{ $sortOption['value'] }}"
                                class="sort-chip {{ $isSortActive ? 'sort-chip--active' : '' }}">
                                {{ $sortOption['label'] }}
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="sort" id="sortInput" value="{{ $filterSort ?? 'latest' }}">
                    </div>

                    {{-- Keywords --}}
                    @if($topKeywords->count() > 0)
                    <div class="bg-white rounded-xl p-4 border border-[#EEF0F7]">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs sm:text-sm font-bold text-[#1A1A1A] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Keywords Populer
                            </p>
                            <span class="text-[11px] text-[#737373]">Pilih satu atau lebih</span>
                        </div>

                        {{-- Keyword chips --}}
                        <div class="flex flex-wrap gap-2" id="keywordChipsContainer">
                            @foreach($topKeywords->take(20) as $keyword)
                            @php
                            $kwSlug = is_array($keyword) ? ($keyword['slug'] ?? '') : ($keyword->slug ?? '');
                            $kwName = is_array($keyword) ? ($keyword['name'] ?? '') : ($keyword->name ?? '');
                            $kwCount = is_array($keyword) ? ($keyword['publications_count'] ?? 0) :
                            ($keyword->publications_count ?? 0);
                            $isActive = $activeKeywords->contains($kwSlug);
                            @endphp
                            <button type="button" data-keyword-btn data-keyword-slug="{{ $kwSlug }}"
                                data-keyword-name="{{ $kwName }}"
                                class="keyword-chip {{ $isActive ? 'keyword-chip--active' : '' }}">
                                {{ $kwName }}
                                @if($kwCount > 0)
                                <span class="keyword-chip__count">({{ $kwCount }})</span>
                                @endif
                            </button>
                            @endforeach
                        </div>

                        {{-- Active keywords indicator --}}
                        <div id="keywordActiveIndicator"
                            class="{{ $activeKeywords->isNotEmpty() ? '' : 'hidden' }} mt-3">
                            <div class="flex flex-wrap items-start gap-2">
                                <span
                                    class="text-[11px] text-[#FF6B18] font-bold flex items-center gap-1 flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Terpilih:
                                </span>
                                <div id="keywordActiveBadges" class="flex flex-wrap gap-1.5">
                                    @foreach($activeKeywords as $kw)
                                    @php
                                    $kwObj = $topKeywords->first(fn($k) =>
                                    (is_array($k) ? $k['slug'] : $k->slug) === $kw
                                    );
                                    $kwLabel = $kwObj
                                    ? (is_array($kwObj) ? $kwObj['name'] : $kwObj->name)
                                    : $kw;
                                    @endphp
                                    <span
                                        class="active-kw-badge inline-flex items-center gap-1 px-2 py-0.5 bg-[#FF6B18]/10 text-[#FF6B18] rounded-full text-[10px] font-semibold"
                                        data-badge-slug="{{ $kw }}">
                                        {{ $kwLabel }}
                                        <button type="button" data-remove-slug="{{ $kw }}"
                                            class="remove-kw-btn hover:text-[#E64627] leading-none font-black text-xs ml-0.5">✕</button>
                                    </span>
                                    @endforeach
                                </div>
                                <button type="button" onclick="clearKeyword()"
                                    class="text-[11px] text-[#FF6B18] underline hover:no-underline font-semibold flex-shrink-0 mt-0.5">
                                    Hapus semua
                                </button>
                            </div>
                        </div>

                    </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 bg-white border-t border-[#EEF0F7] px-5 py-4 flex flex-col sm:flex-row gap-3">
                    <button type="button" onclick="resetPublicationSearch()"
                        class="sm:w-auto px-5 py-3 border border-[#EEF0F7] text-[#737373] text-sm font-semibold rounded-xl hover:bg-[#F8F9FC] hover:border-[#FF6B18] hover:text-[#FF6B18] transition-all flex items-center justify-center gap-2 order-2 sm:order-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm font-bold rounded-xl hover:shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 order-1 sm:order-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari Sekarang
                        <span id="activeFilterCount"
                            class="{{ ($activeKeywords->isNotEmpty() || $filterCategory || $filterYear || $searchQuery) ? '' : 'hidden' }} inline-flex items-center justify-center w-5 h-5 bg-white/30 text-white text-[10px] font-black rounded-full"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
    /* ─── Keyword Chips ──────────────────────────────────────── */
    .keyword-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 7px 14px;
        border-radius: 9999px;
        cursor: pointer;
        user-select: none;
        border: 1.5px solid #EEF0F7;
        background-color: #F8F9FC;
        color: #1A1A1A;
        font-size: 0.8125rem;
        font-weight: 500;
        line-height: 1;
        white-space: nowrap;
        transition: all 0.18s ease;
        outline: none;
    }

    .keyword-chip:hover {
        border-color: #FF6B18;
        background-color: #FFF7F2;
        color: #FF6B18;
    }

    .keyword-chip:focus-visible {
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.25);
    }

    .keyword-chip--active {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 2px 10px rgba(255, 107, 24, 0.4);
    }

    .keyword-chip--active:hover {
        background: linear-gradient(135deg, #E64627 0%, #FF6B18 100%);
        color: #ffffff;
        border-color: transparent;
    }

    .keyword-chip__count {
        font-size: 0.72rem;
        opacity: 0.72;
    }

    /* ─── Sort Chips ─────────────────────────────────────────── */
    .sort-chip {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 8px;
        border-radius: 10px;
        cursor: pointer;
        user-select: none;
        border: 1.5px solid #EEF0F7;
        background-color: #F8F9FC;
        color: #1A1A1A;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
        transition: all 0.18s ease;
        outline: none;
        width: 100%;
    }

    .sort-chip:hover {
        border-color: #FF6B18;
        background-color: #FFF7F2;
        color: #FF6B18;
    }

    .sort-chip:focus-visible {
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.25);
    }

    .sort-chip--active {
        border-color: #FF6B18;
        background-color: #FFF7F2;
        color: #FF6B18;
        font-weight: 700;
    }
</style>
@endPushOnce

@push('scripts')
<script>
    (function () {
    'use strict';

    // ─── State ───────────────────────────────────────────────
    // Seed dari server (keyword yang sudah aktif)
    const selectedKeywords = new Map(); // slug → name

    document.querySelectorAll('[data-keyword-btn]').forEach(btn => {
        if (btn.classList.contains('keyword-chip--active')) {
            selectedKeywords.set(btn.dataset.keywordSlug, btn.dataset.keywordName);
        }
    });

    // ─── Sync hidden inputs + badges ─────────────────────────
    function syncKeywords() {
        const container = document.getElementById('keywordInputsContainer');
        const indicator = document.getElementById('keywordActiveIndicator');
        const badgesEl  = document.getElementById('keywordActiveBadges');
        const countEl   = document.getElementById('activeFilterCount');

        // Rebuild hidden inputs
        container.innerHTML = '';
        selectedKeywords.forEach((name, slug) => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'keyword[]';
            inp.value = slug;
            container.appendChild(inp);
        });

        // Rebuild badges
        if (selectedKeywords.size > 0) {
            indicator.classList.remove('hidden');
            badgesEl.innerHTML = '';

            selectedKeywords.forEach((name, slug) => {
                const badge = document.createElement('span');
                badge.className = 'active-kw-badge inline-flex items-center gap-1 px-2 py-0.5 bg-[#FF6B18]/10 text-[#FF6B18] rounded-full text-[10px] font-semibold';
                badge.dataset.badgeSlug = slug;
                badge.innerHTML = `${name}
                    <button type="button" data-remove-slug="${slug}"
                        class="remove-kw-btn hover:text-[#E64627] font-black text-xs ml-0.5 leading-none">✕</button>`;
                badgesEl.appendChild(badge);
            });

            // Bind remove buttons
            badgesEl.querySelectorAll('.remove-kw-btn').forEach(removeBtn => {
                removeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    removeKeyword(this.dataset.removeSlug);
                });
            });
        } else {
            indicator.classList.add('hidden');
            badgesEl.innerHTML = '';
        }

        // Update counter badge di tombol submit
        updateFilterCount();
    }

    function updateFilterCount() {
        const countEl  = document.getElementById('activeFilterCount');
        if (!countEl) return;
        const total = selectedKeywords.size
            + (document.querySelector('select[name="category"]')?.value ? 1 : 0)
            + (document.querySelector('select[name="year"]')?.value ? 1 : 0)
            + (document.getElementById('searchInput')?.value.trim() ? 1 : 0);

        if (total > 0) {
            countEl.textContent = total;
            countEl.classList.remove('hidden');
        } else {
            countEl.classList.add('hidden');
        }
    }

    function removeKeyword(slug) {
        selectedKeywords.delete(slug);
        const chip = document.querySelector(`[data-keyword-btn][data-keyword-slug="${slug}"]`);
        if (chip) chip.classList.remove('keyword-chip--active');
        syncKeywords();
    }

    // ─── Keyword chip click ───────────────────────────────────
    function initKeywordChips() {
        document.querySelectorAll('[data-keyword-btn]').forEach(btn => {
            btn.addEventListener('click', function () {
                const slug = this.dataset.keywordSlug;
                const name = this.dataset.keywordName;

                if (selectedKeywords.has(slug)) {
                    // Toggle off
                    selectedKeywords.delete(slug);
                    this.classList.remove('keyword-chip--active');
                } else {
                    // Toggle on
                    selectedKeywords.set(slug, name);
                    this.classList.add('keyword-chip--active');
                }

                syncKeywords();
            });
        });
    }

    window.clearKeyword = function () {
        selectedKeywords.clear();
        document.querySelectorAll('[data-keyword-btn]').forEach(b => {
            b.classList.remove('keyword-chip--active');
        });
        syncKeywords();
    };

    // ─── Sort chips ───────────────────────────────────────────
    function initSortChips() {
        const sortInput = document.getElementById('sortInput');
        document.querySelectorAll('[data-sort-btn]').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('[data-sort-btn]').forEach(b => {
                    b.classList.remove('sort-chip--active');
                });
                this.classList.add('sort-chip--active');
                sortInput.value = this.dataset.sortValue;
            });
        });
    }

    // ─── Reset ────────────────────────────────────────────────
    window.resetPublicationSearch = function () {
        const form = document.getElementById('publicationSearchForm');
        form.querySelectorAll('select').forEach(el => el.value = '');

        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.value = '';
            document.getElementById('clearSearchBtn')?.classList.add('hidden');
        }

        // Reset sort ke latest
        document.getElementById('sortInput').value = 'latest';
        document.querySelectorAll('[data-sort-btn]').forEach(b => {
            b.classList.remove('sort-chip--active');
            if (b.dataset.sortValue === 'latest') b.classList.add('sort-chip--active');
        });

        // Reset keywords
        clearKeyword();
        updateFilterCount();
    };

    // ─── Modal open/close ─────────────────────────────────────
    window.openPublicationSearch = function () {
        const modal   = document.getElementById('publicationSearchModal');
        const content = document.getElementById('publicationSearchContent');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
            document.getElementById('searchInput')?.focus();
        }));
    };

    window.closePublicationSearch = function () {
        const modal   = document.getElementById('publicationSearchModal');
        const content = document.getElementById('publicationSearchContent');
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    };

    // ─── Overlay + keyboard ───────────────────────────────────
    document.getElementById('publicationSearchModal')?.addEventListener('click', function (e) {
        if (e.target === this) closePublicationSearch();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePublicationSearch();
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openPublicationSearch();
        }
    });

    // ─── Search input ─────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    searchInput?.addEventListener('input', function () {
        const clearBtn = document.getElementById('clearSearchBtn');
        this.value.length > 0
            ? clearBtn?.classList.remove('hidden')
            : clearBtn?.classList.add('hidden');
        updateFilterCount();
    });

    searchInput?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('publicationSearchForm').submit();
        }
    });

    document.querySelectorAll('select[name="category"], select[name="year"]').forEach(sel => {
        sel.addEventListener('change', updateFilterCount);
    });

    // ─── Init ─────────────────────────────────────────────────
    initSortChips();
    initKeywordChips();
    updateFilterCount();
    console.log('✅ Publication Search Modal v4 — multi-keyword initialized');
})();
</script>
@endpush