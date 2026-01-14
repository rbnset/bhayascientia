@props(['categories' => ['Technology', 'Science', 'Health', 'Education', 'Engineering', 'Business']])

{{-- Modal Backdrop --}}
<div id="filterModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm" onclick="toggleFilterModal()">
    {{-- Modal Content --}}
    <div class="fixed inset-x-4 top-1/2 -translate-y-1/2 max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
        onclick="event.stopPropagation()">
        {{-- Modal Header --}}
        <div class="sticky top-0 z-10 flex items-center justify-between p-6 bg-white border-b border-[#EEF0F7]">
            <h2 class="text-2xl font-bold text-[#1A1A1A] flex items-center gap-2">
                <svg class="w-6 h-6 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                Advanced Filter
            </h2>
            <button type="button" onclick="toggleFilterModal()"
                class="p-2 transition-colors rounded-full hover:bg-[#F8F9FC]" aria-label="Close modal">
                <svg class="w-6 h-6 text-[#737373]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <form action="{{ route('publikasi') }}" method="GET" class="p-6 space-y-6">

            {{-- Search Keywords --}}
            <div class="space-y-2">
                <label for="modal_search" class="block text-sm font-bold text-[#1A1A1A]">
                    Kata Kunci
                </label>
                <input type="text" id="modal_search" name="search" value="{{ request('search') }}"
                    placeholder="Cari berdasarkan judul, penulis, atau kata kunci..."
                    class="w-full px-4 py-3 transition-all duration-200 bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
            </div>

            {{-- Category --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-[#1A1A1A]">
                    Kategori
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($categories as $category)
                    <label
                        class="flex items-center gap-3 p-3 border rounded-xl border-[#EEF0F7] cursor-pointer hover:border-[#FF6B18] transition-colors">
                        <input type="checkbox" name="categories[]" value="{{ strtolower($category) }}"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]" {{
                            in_array(strtolower($category), (array)request('categories', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-[#1A1A1A]">{{ $category }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Year Range --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="year_from" class="block text-sm font-bold text-[#1A1A1A]">
                        Dari Tahun
                    </label>
                    <select id="year_from" name="year_from"
                        class="w-full px-4 py-3 transition-all duration-200 bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
                        <option value="">Pilih tahun</option>
                        @for ($y = date('Y'); $y >= 2000; $y--)
                        <option value="{{ $y }}" {{ request('year_from')==$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="year_to" class="block text-sm font-bold text-[#1A1A1A]">
                        Sampai Tahun
                    </label>
                    <select id="year_to" name="year_to"
                        class="w-full px-4 py-3 transition-all duration-200 bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
                        <option value="">Pilih tahun</option>
                        @for ($y = date('Y'); $y >= 2000; $y--)
                        <option value="{{ $y }}" {{ request('year_to')==$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Author --}}
            <div class="space-y-2">
                <label for="author" class="block text-sm font-bold text-[#1A1A1A]">
                    Penulis
                </label>
                <input type="text" id="author" name="author" value="{{ request('author') }}"
                    placeholder="Nama penulis..."
                    class="w-full px-4 py-3 transition-all duration-200 bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
            </div>

            {{-- Sort --}}
            <div class="space-y-2">
                <label for="sort" class="block text-sm font-bold text-[#1A1A1A]">
                    Urutkan Berdasarkan
                </label>
                <select id="sort" name="sort"
                    class="w-full px-4 py-3 transition-all duration-200 bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
                    <option value="latest" {{ request('sort', 'latest' )=='latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="popular" {{ request('sort')=='popular' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="title" {{ request('sort')=='title' ? 'selected' : '' }}>Judul (A-Z)</option>
                    <option value="citations" {{ request('sort')=='citations' ? 'selected' : '' }}>Paling Banyak Dikutip
                    </option>
                </select>
            </div>

            {{-- Publication Type --}}
            <div class="space-y-3">
                <label class="block text-sm font-bold text-[#1A1A1A]">
                    Tipe Publikasi
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label
                        class="flex items-center gap-3 p-3 border rounded-xl border-[#EEF0F7] cursor-pointer hover:border-[#FF6B18] transition-colors">
                        <input type="checkbox" name="type[]" value="journal"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]" {{
                            in_array('journal', (array)request('type', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-[#1A1A1A]">Journal Article</span>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border rounded-xl border-[#EEF0F7] cursor-pointer hover:border-[#FF6B18] transition-colors">
                        <input type="checkbox" name="type[]" value="conference"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]" {{
                            in_array('conference', (array)request('type', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-[#1A1A1A]">Conference Paper</span>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border rounded-xl border-[#EEF0F7] cursor-pointer hover:border-[#FF6B18] transition-colors">
                        <input type="checkbox" name="type[]" value="thesis"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]" {{
                            in_array('thesis', (array)request('type', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-[#1A1A1A]">Thesis</span>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 border rounded-xl border-[#EEF0F7] cursor-pointer hover:border-[#FF6B18] transition-colors">
                        <input type="checkbox" name="type[]" value="report"
                            class="w-5 h-5 text-[#FF6B18] rounded focus:ring-2 focus:ring-[#FF6B18]" {{
                            in_array('report', (array)request('type', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-[#1A1A1A]">Research Report</span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 pt-6 border-t sm:flex-row border-[#EEF0F7]">
                <button type="submit"
                    class="flex-1 px-6 py-4 font-bold text-white transition-all duration-200 rounded-xl bg-[#FF6B18] hover:-translate-y-1 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:ring-offset-2">
                    Terapkan Filter
                </button>
                <a href="{{ route('publikasi') }}"
                    class="px-6 py-4 font-bold text-center transition-all duration-200 bg-white border rounded-xl text-[#1A1A1A] border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:ring-offset-2">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleFilterModal() {
    const modal = document.getElementById('filterModal');
    const isHidden = modal.classList.contains('hidden');

    if (isHidden) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('filterModal');
        if (!modal.classList.contains('hidden')) {
            toggleFilterModal();
        }
    }
});
</script>
@endpush
