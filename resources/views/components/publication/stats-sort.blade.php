{{-- resources/views/components/publication/stats-sort.blade.php --}}
@props([
'currentPage' => 1,
'perPage' => 12,
'total' => 0,
'sortOptions' => [],
'currentSort' => 'latest',
])

@php
$from = ($currentPage - 1) * $perPage + 1;
$to = min($currentPage * $perPage, $total);

$defaultSortOptions = [
'latest' => 'Terbaru',
'popular' => 'Terpopuler',
'title' => 'Judul (A-Z)',
];

$options = !empty($sortOptions) ? $sortOptions : $defaultSortOptions;
@endphp

<div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-[#737373]">
        Menampilkan <span class="font-semibold text-[#1A1A1A]">{{ $from }}-{{ $to }}</span> dari
        <span class="font-semibold text-[#1A1A1A]">{{ $total }}</span> publikasi
    </p>

    <select onchange="window.location.href=this.value"
        class="px-4 py-2 text-sm font-medium bg-white border rounded-xl border-[#EEF0F7] focus:outline-none focus:ring-2 focus:ring-[#FF6B18] focus:border-transparent">
        @foreach($options as $value => $label)
        <option value="{{ route('publikasi.index', array_merge(request()->except('sort'), ['sort' => $value])) }}" {{
            $currentSort===$value ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
</div>
