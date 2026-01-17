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

{{-- Advanced Search & Filter Modal --}}
<div id="publicationSearchModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-md z-[60] hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-start justify-center min-h-screen p-4 pt-20">
        <div id="publicationSearchContent"
            class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden transform scale-95 transition-transform duration-300">

            {{-- Header --}}
            <div
                class="sticky top-0 bg-gradient-to-r from-[#FF6B18] to-[#E64627] px-6 py-5 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Cari & Filter Publikasi</h2>
                        <p class="text-sm text-white/90">Temukan publikasi yang Anda butuhkan</p>
                    </div>
                </div>
                <button onclick="closePublicationSearch()"
                    class="flex items-center justify-center w-10 h-10 transition-all duration-300 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl hover:rotate-90">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('publikasi.search') }}" id="publicationSearchForm"
                class="overflow-y-auto max-h-[calc(85vh-160px)]">
                <input type="hidden" name="type" value="{{ $selectedType }}">

                <div class="p-6 space-y-6">

                    {{-- ✅ SEARCH BAR - Priority #1 --}}
                    <div
                        class="bg-gradient-to-br from-[#FFF7F2] to-white rounded-2xl p-6 border-2 border-[#FF6B18]/20 shadow-sm">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Pencarian Cepat
                        </label>
                        <div class="relative">
                            <input type="text" name="search" id="searchInput" value="{{ $searchQuery ?? '' }}"
                                placeholder="Cari berdasarkan judul, abstrak, atau nama penulis..." autocomplete="off"
                                class="w-full pl-12 pr-4 py-4 border-2 border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all text-base font-medium">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#737373]" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            @if($searchQuery)
                            <button type="button" onclick="document.getElementById('searchInput').value = ''"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#FF6B18] text-white rounded-full flex items-center justify-center hover:bg-[#E64627] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-[#737373]">
                            💡 Tips: Gunakan kata kunci spesifik untuk hasil lebih akurat
                        </p>
                    </div>

                    {{-- Divider --}}
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#EEF0F7]"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-4 text-xs font-bold text-[#737373] bg-white uppercase tracking-wide">
                                Filter Lanjutan
                            </span>
                        </div>
                    </div>

                    {{-- Category & Year Row --}}
                    <div class="grid gap-6 md:grid-cols-2">

                        {{-- Category Filter --}}
                        @if($categories->count() > 0)
                        <div
                            class="bg-white rounded-2xl p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                            <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Kategori
                            </label>
                            <select name="category"
                                class="w-full px-4 py-3 border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all font-medium">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->slug }}" {{ ($filterCategory ?? '' )==$category->slug ?
                                    'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->publications_count }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Year Filter --}}
                        @if($years->count() > 0)
                        <div
                            class="bg-white rounded-2xl p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                            <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tahun Publikasi
                            </label>
                            <select name="year"
                                class="w-full px-4 py-3 border border-[#EEF0F7] rounded-xl focus:ring-2 focus:ring-[#FF6B18] focus:border-[#FF6B18] outline-none transition-all font-medium">
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
                        class="bg-white rounded-2xl p-5 border border-[#EEF0F7] hover:border-[#FF6B18]/30 transition-colors">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            Urutkan Berdasarkan
                        </label>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                            <label
                                class="relative flex items-center gap-2 px-4 py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-all border-2 border-transparent has-[:checked]:border-[#FF6B18] has-[:checked]:bg-[#FFF7F2]">
                                <input type="radio" name="sort" value="latest" {{ ($filterSort ?? 'latest' )=='latest'
                                    ? 'checked' : '' }} class="sr-only">
                                <span class="text-sm font-semibold text-[#1A1A1A]">🕐 Terbaru</span>
                            </label>
                            <label
                                class="relative flex items-center gap-2 px-4 py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-all border-2 border-transparent has-[:checked]:border-[#FF6B18] has-[:checked]:bg-[#FFF7F2]">
                                <input type="radio" name="sort" value="popular" {{ ($filterSort ?? '' )=='popular'
                                    ? 'checked' : '' }} class="sr-only">
                                <span class="text-sm font-semibold text-[#1A1A1A]">🔥 Populer</span>
                            </label>
                            <label
                                class="relative flex items-center gap-2 px-4 py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-all border-2 border-transparent has-[:checked]:border-[#FF6B18] has-[:checked]:bg-[#FFF7F2]">
                                <input type="radio" name="sort" value="oldest" {{ ($filterSort ?? '' )=='oldest'
                                    ? 'checked' : '' }} class="sr-only">
                                <span class="text-sm font-semibold text-[#1A1A1A]">📅 Terlama</span>
                            </label>
                            <label
                                class="relative flex items-center gap-2 px-4 py-3 bg-[#F8F9FC] rounded-xl cursor-pointer hover:bg-[#FFF7F2] transition-all border-2 border-transparent has-[:checked]:border-[#FF6B18] has-[:checked]:bg-[#FFF7F2]">
                                <input type="radio" name="sort" value="title" {{ ($filterSort ?? '' )=='title'
                                    ? 'checked' : '' }} class="sr-only">
                                <span class="text-sm font-semibold text-[#1A1A1A]">🔤 A-Z</span>
                            </label>
                        </div>
                    </div>

                    {{-- Keywords --}}
                    @if($topKeywords->count() > 0)
                    <div class="bg-white rounded-2xl p-5 border border-[#EEF0F7]">
                        <label class="block text-sm font-bold text-[#1A1A1A] mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            Keywords Populer
                            <span class="ml-auto text-xs font-normal text-[#737373]">Opsional</span>
                        </label>
                        <div class="flex flex-wrap gap-2 pr-2 overflow-y-auto max-h-40">
                            @foreach($topKeywords->take(15) as $keyword)
                            <label
                                class="inline-flex items-center gap-2 px-3 py-2 bg-[#F8F9FC] rounded-full cursor-pointer hover:bg-[#FFF7F2] transition-all border border-transparent hover:border-[#FF6B18] has-[:checked]:bg-[#FF6B18] has-[:checked]:text-white has-[:checked]:border-[#FF6B18]">
                                <input type="radio" name="keyword" value="{{ $keyword->slug }}" {{ ($filterKeyword ?? ''
                                    )==$keyword->slug ? 'checked' : '' }} class="sr-only">
                                <span class="text-sm font-medium">{{ $keyword->name }}</span>
                                <span class="text-xs opacity-75">({{ $keyword->publications_count }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Footer Actions --}}
                <div
                    class="sticky bottom-0 bg-white border-t-2 border-[#EEF0F7] px-6 py-4 flex items-center justify-between gap-4">
                    <button type="button" onclick="resetPublicationSearch()"
                        class="px-6 py-3 border-2 border-[#EEF0F7] text-[#737373] font-semibold rounded-xl hover:bg-[#F8F9FC] hover:border-[#FF6B18] transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </button>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white font-bold rounded-xl hover:shadow-xl transition-all flex items-center gap-2 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari Sekarang
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function openPublicationSearch() {
    const modal = document.getElementById('publicationSearchModal');
    const content = document.getElementById('publicationSearchContent');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');

        // Auto-focus search input
        document.getElementById('searchInput')?.focus();
    }, 10);
}

function closePublicationSearch() {
    const modal = document.getElementById('publicationSearchModal');
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

    // Clear all inputs
    form.querySelectorAll('input[type="radio"]:checked').forEach(el => el.checked = false);
    form.querySelectorAll('select').forEach(el => el.value = '');
    form.querySelector('input[name="search"]').value = '';

    // Set default sort to 'latest'
    const latestRadio = form.querySelector('input[name="sort"][value="latest"]');
    if (latestRadio) latestRadio.checked = true;
}

// Close on outside click
document.getElementById('publicationSearchModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePublicationSearch();
    }
});

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePublicationSearch();
    }
});

// Quick submit with Enter key on search input
document.getElementById('searchInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('publicationSearchForm').submit();
    }
});
</script>
@endpush