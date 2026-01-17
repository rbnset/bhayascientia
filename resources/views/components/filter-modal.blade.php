@props([
'selectedType' => 'all',
'categories' => collect([]),
'years' => collect([]),
'topKeywords' => collect([]),
'filterCategory' => null,
'filterYear' => null,
'filterKeyword' => null,
'filterSort' => 'latest',
'searchQuery' => null,
])

{{-- Advanced Filter Modal --}}
<div id="filterModal"
    class="fixed inset-0 z-50 hidden transition-opacity duration-300 opacity-0 bg-black/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="filterModalContent"
            class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">

            {{-- Header --}}
            <div
                class="sticky top-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-5 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Advanced Filter</h2>
                        <p class="text-sm text-white/80">Temukan publikasi yang tepat</p>
                    </div>
                </div>
                <button onclick="closeFilterModal()"
                    class="flex items-center justify-center w-10 h-10 transition-colors bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('publikasi.index') }}" id="advancedFilterForm"
                class="overflow-y-auto max-h-[calc(90vh-180px)]">
                <input type="hidden" name="type" value="{{ $selectedType }}">

                <div class="p-6 space-y-6">

                    {{-- Search Bar --}}
                    <div class="bg-[#F8F9FC] rounded-2xl p-6 border border-[#EEF0F7]">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari Publikasi
                        </label>
                        <input type="text" name="search" value="{{ $searchQuery ?? '' }}"
                            placeholder="Cari berdasarkan judul, abstrak, atau nama penulis..."
                            class="w-full px-4 py-3 border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent outline-none transition-all">
                    </div>

                    {{-- Category Filter --}}
                    @if($categories->count() > 0)
                    <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Kategori
                        </label>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach($categories as $category)
                            <label
                                class="flex items-center gap-2 px-4 py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-colors border border-transparent hover:border-[#FF6B18] group">
                                <input type="radio" name="category" value="{{ $category->slug }}" {{ ($filterCategory
                                    ?? '' )==$category->slug ? 'checked' : '' }}
                                class="w-4 h-4 text-[#FF6B18] focus:ring-[#FF6B18]">
                                <span class="text-sm font-medium text-[#1A1A1A] group-hover:text-[#FF6B18] flex-1">
                                    {{ $category->name }}
                                </span>
                                <span class="text-xs text-[#737373] bg-white px-2 py-0.5 rounded-full">
                                    {{ $category->publications_count }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Year & Sorting (2 columns) --}}
                    <div class="grid gap-6 md:grid-cols-2">

                        {{-- Year Filter --}}
                        @if($years->count() > 0)
                        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
                            <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tahun Publikasi
                            </label>
                            <select name="year"
                                class="w-full px-4 py-3 border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent outline-none transition-all">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ ($filterYear ?? '' )==$year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Sort By --}}
                        <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
                            <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                                Urutkan
                            </label>
                            <select name="sort"
                                class="w-full px-4 py-3 border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent outline-none transition-all">
                                <option value="latest" {{ ($filterSort ?? 'latest' )=='latest' ? 'selected' : '' }}>
                                    Terbaru</option>
                                <option value="popular" {{ ($filterSort ?? '' )=='popular' ? 'selected' : '' }}>
                                    Terpopuler</option>
                                <option value="oldest" {{ ($filterSort ?? '' )=='oldest' ? 'selected' : '' }}>Terlama
                                </option>
                                <option value="title" {{ ($filterSort ?? '' )=='title' ? 'selected' : '' }}>Judul (A-Z)
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Keywords Filter --}}
                    @if($topKeywords->count() > 0)
                    <div class="bg-white rounded-2xl p-6 border border-[#EEF0F7]">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            Keywords Populer
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($topKeywords as $keyword)
                            <label
                                class="inline-flex items-center gap-2 px-4 py-2 bg-[#F8F9FC] rounded-full cursor-pointer hover:bg-[#FFF7F2] transition-colors border border-transparent hover:border-[#FF6B18] group">
                                <input type="radio" name="keyword" value="{{ $keyword->slug }}" {{ ($filterKeyword ?? ''
                                    )==$keyword->slug ? 'checked' : '' }}
                                class="w-4 h-4 text-[#FF6B18] focus:ring-[#FF6B18]">
                                <span class="text-sm font-medium text-[#1A1A1A] group-hover:text-[#FF6B18]">
                                    {{ $keyword->name }}
                                </span>
                                <span class="text-xs text-[#737373]">({{ $keyword->publications_count }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Footer Actions --}}
                <div
                    class="sticky bottom-0 bg-white border-t border-[#EEF0F7] px-6 py-4 flex items-center justify-between gap-4">
                    <button type="button" onclick="resetFilters()"
                        class="px-6 py-3 border-2 border-[#EEF0F7] text-[#737373] font-semibold rounded-xl hover:bg-[#F8F9FC] transition-all">
                        Reset Filter
                    </button>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const content = document.getElementById('filterModalContent');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    const content = document.getElementById('filterModalContent');

    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

function resetFilters() {
    const form = document.getElementById('advancedFilterForm');

    // Clear all inputs
    document.querySelectorAll('input[type="radio"]:checked').forEach(el => el.checked = false);
    const yearSelect = form.querySelector('select[name="year"]');
    const sortSelect = form.querySelector('select[name="sort"]');
    const searchInput = form.querySelector('input[name="search"]');

    if (yearSelect) yearSelect.value = '';
    if (sortSelect) sortSelect.value = 'latest';
    if (searchInput) searchInput.value = '';

    // Submit form to reset
    form.submit();
}

// Close modal on outside click
document.getElementById('filterModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeFilterModal();
    }
});

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFilterModal();
    }
});
</script>
@endpush