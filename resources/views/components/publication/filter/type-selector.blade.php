@props([
'activeType' => 'all',
])

@php
$types = [
['value' => 'all', 'label' => 'Semua', 'active' => $activeType === 'all'],
['value' => 'book', 'label' => 'Buku', 'active' => $activeType === 'book'],
['value' => 'journal', 'label' => 'Jurnal', 'active' => $activeType === 'journal'],
['value' => 'opinion', 'label' => 'Opini', 'active' => $activeType === 'opinion'],
];
@endphp

<x-ui.segmented-control id="pubTabs" :items="$types" aria-label="Jenis publikasi" @change="handleTypeChange" />

@push('scripts')
<script>
    function handleTypeChange(event) {
    const value = event.target.dataset.value;
    // Handle filter change logic
    console.log('Selected type:', value);
}
</script>
@endpush
