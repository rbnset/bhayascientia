@props([
'filters' => [], // ['search' => 'keyword', 'category' => 'science', ...]
'clearAllRoute' => null,
])

@if(count($filters) > 0)
<div class="flex flex-wrap items-center gap-3 mb-6">
    <span class="text-sm font-semibold text-[#737373]">Filter aktif:</span>

    @foreach($filters as $key => $value)
    @if($value)
    <span
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-full bg-[#FFF7F2] text-[#FF6B18]">
        @if($key === 'search')
        "{{ $value }}"
        @else
        {{ ucfirst($key) }}: {{ $value }}
        @endif

        <a href="{{ request()->fullUrlWithQuery([$key => null]) }}"
            class="p-0.5 transition-colors rounded-full hover:bg-[#FF6B18] hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </span>
    @endif
    @endforeach

    <a href="{{ $clearAllRoute ?? route('publikasi') }}" class="text-sm font-semibold text-[#FF6B18] hover:underline">
        Hapus semua filter
    </a>
</div>
@endif
