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
// ✅ Normalize semua ke Collection — tanpa use statement
$categories = ($categories instanceof \Illuminate\Support\Collection)
? $categories : collect((array) ($categories ?? []));

$years = ($years instanceof \Illuminate\Support\Collection)
? $years : collect((array) ($years ?? []));

$topKeywords = ($topKeywords instanceof \Illuminate\Support\Collection)
? $topKeywords : collect((array) ($topKeywords ?? []));
@endphp

{{-- Advanced Search & Filter Modal --}}
<div id="publicationSearchModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-md z-[60] hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-start justify-center min-h-screen p-3 pt-12 sm:p-4 sm:pt-20">
        <div id="publicationSearchContent"
            class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] sm:max-h-[85vh] overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col">

            {{-- Header --}}
            <div
                class="flex-shrink-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-4 sm:px-6 py-4 sm:py-5 flex items-start sm:items-center justify-between gap-3">
                <div class="flex items-start flex-1 min-w-0 gap-2 sm:items-center sm:gap-3">
                    <div
                        class="flex items-center justify-center flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl">
                        <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-bold leading-tight text-white sm:text-xl">Cari & Filter Publikasi</h2>
                        <p class="text-xs sm:text-sm text-white/90 leading-tight mt-0.5">Temukan publikasi yang Anda
                            butuhkan</p>
                    </div>
                </div>
                <button onclick="closePublicationSearch()"
                    class="flex items-center justify-center flex-shrink-0 transition-all duration-300 w-9 h-9 sm:w-10 sm:h-10 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl hover:rotate-90">
                    <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('publikasi.search') }}" id="publicationSearchForm"
                class="flex flex-col flex-1 min-h-0">
                <input type="hidden" name="type" value="{{ $selectedType }}">

                {{-- Scrollable Content --}}
                <div class="flex-1 p-4 space-y-4 overflow-y-auto sm:p-6 sm:space-y-6">

                    {{-- Search Bar --}}
                    <div
                        class="bg-gradient-to-br from-[#FFF7F2] to-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-[#FF6B18]/20 shadow-sm">
                        <label
                            class="block text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2 sm:mb-3 flex items-start gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="leading-tight">Pencarian Cepat</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="search" id="searchInput" value="{{ $searchQuery ?? '' }}"
                                placeholder="Cari judul, abstrak, penulis..." autocomplete="off"
                                class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 border border-[#EEF0F7] rounded-xl focus:ring-1 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all text-sm sm:text-base font-medium">
                            <svg class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-[#737373] pointer-events-none"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            @if($searchQuery)
                            <button type="button" onclick="document.getElementById('searchInput').value = ''"
                                class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-5 h-5 sm:w-6 sm:h-6 bg-[#FF6B18] text-white rounded-full flex items-center justify-center hover:bg-[#E64627] transition-colors">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>
                        <p class="mt-2 text-[10px] sm:text-xs text-[#737373] leading-relaxed">
                            💡 Tips: Gunakan kata kunci spesifik untuk hasil lebih akurat
                        </p>
                    </div>

                    {{-- Divider --}}
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#EEF0F7]"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span
                                class="px-3 sm:px-4 text-[10px] sm:text-xs font-bold text-[#737373] bg-white uppercase tracking-wide">
                                Filter Lanjutan
                            </span>
                        </div>
                    </div>

                    {{-- Category & Year Row --}}
                    <div class="grid gap-4 sm:gap-6 md:grid-cols-2">

                        {{-- Category Filter --}}
                        @if($categories->count() > 0)
                        <div
                            class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                            <label
                                class="block text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2 sm:mb-3 flex items-start gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span class="leading-tight">Kategori</span>
                            </label>
                            <select name="category"
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-[#EEF0F7] rounded-xl focus:ring-1 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all text-sm sm:text-base font-medium">
                                <option value="">Semua Kategori</option>
                                {{-- ✅ Satu loop saja, support array & Eloquent object --}}
                                @foreach($categories as $category)
                                @php
                                $catSlug = is_array($category) ? ($category['slug'] ?? '') : ($category->slug ?? '');
                                $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
                                $catCount = is_array($category) ? ($category['publications_count'] ?? 0) :
                                ($category->publications_count ?? 0);
                                @endphp
                                <option value="{{ $catSlug }}" {{ ($filterCategory ?? '' )==$catSlug ? 'selected' : ''
                                    }}>
                                    {{ $catName }}{{ $catCount > 0 ? ' ('.$catCount.')' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Year Filter --}}
                        @if($years->count() > 0)
                        <div
                            class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                            <label
                                class="block text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2 sm:mb-3 flex items-start gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="leading-tight">Tahun Publikasi</span>
                            </label>
                            <select name="year"
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-[#EEF0F7] rounded-xl focus:ring-1 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all text-sm sm:text-base font-medium">
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
                    <div
                        class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                        <label
                            class="block text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2 sm:mb-3 flex items-start gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            <span class="leading-tight">Urutkan Berdasarkan</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-4">
                            @foreach([
                            ['value' => 'latest', 'label' => '🕐 Terbaru'],
                            ['value' => 'popular', 'label' => '🔥 Populer'],
                            ['value' => 'oldest', 'label' => '📅 Terlama'],
                            ['value' => 'title', 'label' => '🔤 A-Z'],
                            ] as $sortOption)
                            <label
                                class="relative flex items-center justify-center gap-1.5 px-2.5 sm:px-3 py-2.5 sm:py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-all border border-transparent has-[:checked]:border-[#FF6B18] has-[:checked]:bg-[#FFF7F2]">
                                <input type="radio" name="sort" value="{{ $sortOption['value'] }}" {{ ($filterSort
                                    ?? 'latest' )==$sortOption['value'] ? 'checked' : '' }} class="sr-only">
                                <span
                                    class="text-xs sm:text-sm font-semibold text-[#1A1A1A] text-center leading-tight whitespace-nowrap">
                                    {{ $sortOption['label'] }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Keywords --}}
                    @if($topKeywords->count() > 0)
                    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-[#EEF0F7]">
                        <label
                            class="block text-xs sm:text-sm font-bold text-[#1A1A1A] mb-2 sm:mb-3 flex items-start gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <span class="flex-1 leading-tight">Keywords Populer</span>
                            <span class="text-[10px] sm:text-xs font-normal text-[#737373]">Opsional</span>
                        </label>
                        <div class="flex flex-wrap gap-2 overflow-y-auto max-h-40 sm:max-h-48">
                            {{-- ✅ Satu loop saja, support array & Eloquent object --}}
                            @foreach($topKeywords->take(15) as $keyword)
                            @php
                            $kwSlug = is_array($keyword) ? ($keyword['slug'] ?? '') : ($keyword->slug ?? '');
                            $kwName = is_array($keyword) ? ($keyword['name'] ?? '') : ($keyword->name ?? '');
                            $kwCount = is_array($keyword) ? ($keyword['publications_count'] ?? 0) :
                            ($keyword->publications_count ?? 0);
                            @endphp
                            <label
                                class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 bg-[#F8F9FC] rounded-full cursor-pointer hover:bg-[#FFF7F2] transition-all border border-transparent hover:border-[#FF6B18] has-[:checked]:bg-[#FF6B18] has-[:checked]:text-white has-[:checked]:border-[#FF6B18]">
                                <input type="radio" name="keyword" value="{{ $kwSlug }}" {{ ($filterKeyword ?? ''
                                    )==$kwSlug ? 'checked' : '' }} class="sr-only">
                                <span class="text-xs font-medium leading-tight sm:text-sm">{{ $kwName }}</span>
                                @if($kwCount > 0)
                                <span class="text-[10px] sm:text-xs opacity-75 leading-tight">({{ $kwCount }})</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Footer Actions --}}
                <div
                    class="flex-shrink-0 bg-white border-t border-[#EEF0F7] px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                    <button type="button" onclick="resetPublicationSearch()"
                        class="order-2 sm:order-1 px-4 sm:px-6 py-2.5 sm:py-3 border border-[#EEF0F7] text-[#737373] text-sm sm:text-base font-semibold rounded-xl hover:bg-[#F8F9FC] hover:border-[#FF6B18] transition-all flex items-center justify-center gap-2">
                        <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset</span>
                    </button>
                    <button type="submit"
                        class="order-1 sm:order-2 flex-1 px-5 sm:px-8 py-2.5 sm:py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-xl hover:shadow-xl transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5">
                        <svg class="flex-shrink-0 w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cari Sekarang</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openPublicationSearch() {
    const modal   = document.getElementById('publicationSearchModal');
    const content = document.getElementById('publicationSearchContent');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
        document.getElementById('searchInput')?.focus();
    }, 10);
}

function closePublicationSearch() {
    const modal   = document.getElementById('publicationSearchModal');
    const content = document.getElementById('publicationSearchContent');

    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

function resetPublicationSearch() {
    const form = document.getElementById('publicationSearchForm');
    form.querySelectorAll('input[type="radio"]:checked').forEach(el => el.checked = false);
    form.querySelectorAll('select').forEach(el => el.value = '');
    const searchInput = form.querySelector('input[name="search"]');
    if (searchInput) searchInput.value = '';

    const latestRadio = form.querySelector('input[name="sort"][value="latest"]');
    if (latestRadio) latestRadio.checked = true;
}

document.getElementById('publicationSearchModal')?.addEventListener('click', function(e) {
    if (e.target === this) closePublicationSearch();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePublicationSearch();
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        openPublicationSearch();
    }
});

document.getElementById('searchInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('publicationSearchForm').submit();
    }
});
</script>
@endpush