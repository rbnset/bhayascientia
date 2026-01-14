@props([
'subItems' => [],
'bottomItems' => [],
'maxWidth' => 'max-w-[640px]',
])

{{-- ===================== SUB MENU (tablet+ / >=640px) ===================== --}}
<nav aria-label="Sub menu publikasi" class="hidden mt-4 sm:block sm:mt-5">
    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 md:gap-4">
        @foreach ($subItems as $item)
        <a href="{{ $item['href'] }}" aria-current="{{ !empty($item['active']) ? 'page' : 'false' }}"
            @class([ 'py-2.5 px-[18px] gap-2 font-semibold text-sm bg-white inline-flex items-center rounded-full border transition-all duration-300'
            , 'border-[#EEF0F7] hover:border-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18]/20'=> empty($item['active']),
            'border-[#FF6B18] ring-2 ring-[#FF6B18]/20 text-[#FF6B18]' => !empty($item['active']),
            ])
            >
            <span class="flex w-5 h-5 shrink-0">
                <img src="{{ asset($item['icon']) }}" alt="" class="w-full h-full" aria-hidden="true">
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
    </div>
</nav>

{{-- ===================== BOTTOM NAV (mobile only / <640px)=====================--}} <nav
    class="fixed left-0 right-0 z-40 px-4 bottom-5 sm:hidden" aria-label="Navigasi bawah">
    <div class="mx-auto w-full {{ $maxWidth }}">
        <div
            class="grid grid-flow-col auto-cols-auto items-center justify-between rounded-full bg-[#2A2A2A] p-2 px-[22px] shadow-lg">
            @foreach ($bottomItems as $item)
            @php($active = !empty($item['active']))
            @php($badge = (int) ($item['badge'] ?? 0))

            @if ($active)
            <a href="{{ $item['href'] }}" class="flex shrink-0 -mx-[14px]" aria-current="page">
                <div class="flex items-center gap-[10px] rounded-full bg-[#E64627] px-4 py-3">
                    <img src="{{ asset($item['iconActive'] ?? $item['icon']) }}" class="w-6 h-6" alt=""
                        aria-hidden="true">
                    <span class="text-sm font-bold leading-[21px] text-white">{{ $item['label'] }}</span>
                </div>
            </a>
            @else
            <a href="{{ $item['href'] }}" class="relative flex items-center justify-center w-full mx-auto"
                aria-current="false">
                <img src="{{ asset($item['icon']) }}" class="w-6 h-6" alt="{{ $item['label'] }}">

                @if ($badge > 0)
                <span
                    class="absolute -right-1 -top-2 grid h-5 w-5 place-items-center rounded-full bg-red-600 text-xs font-bold text-white ring-2 ring-[#2A2A2A]">
                    {{ $badge }}
                </span>
                @endif
            </a>
            @endif
            @endforeach
        </div>
    </div>
    </nav>