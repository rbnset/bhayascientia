{{-- resources/views/components/publication/search/bar.blade.php --}}
@props([
'action' => null,
'method' => 'GET',
'placeholder' => 'Cari publikasi berdasarkan judul, penulis, atau kata kunci...',
'value' => '',
])

<div class="flex gap-3">
    {{-- Search Input --}}
    <form action="{{ $action ?? route('publikasi.index') }}" method="{{ $method }}" class="flex-1">
        <div class="relative">
            <input type="text" name="search" value="{{ $value }}" placeholder="{{ $placeholder }}"
                class="w-full px-5 py-4 pl-12 text-sm transition-all duration-200 bg-white border rounded-2xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
            <svg class="absolute w-5 h-5 text-[#737373] left-4 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </form>

    {{-- Filter Button --}}
    <button type="button" onclick="toggleFilterModal()"
        class="flex items-center gap-2 px-6 py-4 font-bold text-white transition-all duration-200 rounded-2xl bg-[#FF6B18] hover:-translate-y-[1px] hover:shadow-[0_10px_20px_0_#FF6B1880] shrink-0 focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:ring-offset-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        <span class="hidden sm:inline">Filter</span>
    </button>
</div>
