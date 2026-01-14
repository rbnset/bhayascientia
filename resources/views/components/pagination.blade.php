@props([
'currentPage' => 1,
'totalPages' => 21,
'baseUrl' => null,
])

@php
$baseUrl = $baseUrl ?? request()->url();
$queryParams = request()->except('page');

$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);
@endphp

<nav class="flex items-center justify-center gap-2 mb-8" aria-label="Pagination">
    {{-- Previous Button --}}
    @if($currentPage > 1)
    <a href="{{ $baseUrl }}?{{ http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])) }}"
        class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]"
        aria-label="Halaman sebelumnya">
        ← Previous
    </a>
    @else
    <button
        class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] disabled:opacity-50 disabled:cursor-not-allowed"
        disabled aria-label="Halaman sebelumnya">
        ← Previous
    </button>
    @endif

    {{-- First Page --}}
    @if($startPage > 1)
    <a href="{{ $baseUrl }}?{{ http_build_query(array_merge($queryParams, ['page' => 1])) }}"
        class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]">
        1
    </a>
    @if($startPage > 2)
    <span class="px-2 text-[#737373]">...</span>
    @endif
    @endif

    {{-- Page Numbers --}}
    @for($i = $startPage; $i <= $endPage; $i++) @if($i==$currentPage) <button
        class="px-4 py-2 text-sm font-semibold text-white rounded-xl bg-[#FF6B18]" aria-current="page">
        {{ $i }}
        </button>
        @else
        <a href="{{ $baseUrl }}?{{ http_build_query(array_merge($queryParams, ['page' => $i])) }}"
            class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]">
            {{ $i }}
        </a>
        @endif
        @endfor

        {{-- Last Page --}}
        @if($endPage < $totalPages) @if($endPage < $totalPages - 1) <span class="px-2 text-[#737373]">...</span>
            @endif
            <a href="{{ $baseUrl }}?{{ http_build_query(array_merge($queryParams, ['page' => $totalPages])) }}"
                class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]">
                {{ $totalPages }}
            </a>
            @endif

            {{-- Next Button --}}
            @if($currentPage < $totalPages) <a
                href="{{ $baseUrl }}?{{ http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])) }}"
                class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] hover:border-[#FF6B18] hover:text-[#FF6B18]"
                aria-label="Halaman selanjutnya">
                Next →
                </a>
                @else
                <button
                    class="px-4 py-2 text-sm font-semibold transition-colors duration-200 bg-white border rounded-xl border-[#EEF0F7] disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled aria-label="Halaman selanjutnya">
                    Next →
                </button>
                @endif
</nav>
