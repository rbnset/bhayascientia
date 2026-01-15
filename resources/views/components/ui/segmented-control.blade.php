@props([
'id' => 'segmentedControl',
'items' => [], // [['value' => 'all', 'label' => 'Semua', 'active' => true], ...]
'ariaLabel' => 'Options',
])

<div id="{{ $id }}" role="tablist" aria-label="{{ $ariaLabel }}" {{ $attributes->merge(['class' => 'inline-flex
    items-center gap-1 bg-white p-1 rounded-full ring-1 ring-[#EEF0F7]']) }}
    >
    @foreach($items as $item)
    <button type="button" role="tab" aria-selected="{{ $item['active'] ?? false ? 'true' : 'false' }}"
        data-value="{{ $item['value'] }}" class="px-4 py-2 text-xs font-bold transition-all duration-200 rounded-full
                   {{ ($item['active'] ?? false)
                      ? 'bg-[#FF6B18] text-white'
                      : 'text-[#737373] hover:text-[#111827]' }}">
        {{ $item['label'] }}
    </button>
    @endforeach
</div>
