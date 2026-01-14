@props([
'subItems' => [],
'bottomItems' => [],
'maxWidth' => 'max-w-[640px]',
])

{{-- ===================== SUB MENU (tablet+ / >=640px) ===================== --}}
<nav aria-label="Sub menu publikasi" class="hidden mt-4 sm:block sm:mt-5">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
            @foreach ($subItems as $item)
            <a href="{{ $item['href'] }}" aria-current="{{ !empty($item['active']) ? 'page' : 'false' }}"
                @class([ 'relative py-2.5 px-5 gap-2.5 font-semibold text-sm bg-white inline-flex items-center rounded-full border transition-all duration-300 group'
                , 'border-[#EEF0F7] hover:border-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18]/20 hover:-translate-y-0.5'=>
                empty($item['active']),
                'border-[#FF6B18] ring-2 ring-[#FF6B18]/20 text-[#FF6B18] shadow-sm' => !empty($item['active']),
                ])>
                <span class="flex w-5 h-5 transition-transform duration-200 shrink-0 group-hover:scale-110">
                    <img src="{{ asset($item['icon']) }}" alt="" class="object-contain w-full h-full"
                        aria-hidden="true">
                </span>
                <span class="whitespace-nowrap">{{ $item['label'] }}</span>

                @if(!empty($item['badge']) && $item['badge'] > 0)
                <span
                    class="ml-0.5 px-2 py-0.5 bg-[#FF6B18] text-white text-xs font-bold rounded-full min-w-[20px] text-center">
                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                </span>
                @endif

                @if(!empty($item['new']))
                <span
                    class="absolute -top-1 -right-1 px-1.5 py-0.5 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-[10px] font-bold rounded-full uppercase">
                    New
                </span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</nav>

{{-- ===================== BOTTOM NAV (mobile only / <640px)=====================--}} <nav
    class="fixed left-0 right-0 z-40 px-4 bottom-5 sm:hidden" aria-label="Navigasi bawah">
    <div class="mx-auto w-full {{ $maxWidth }}">
        <div
            class="grid grid-flow-col auto-cols-auto items-center justify-between rounded-full bg-[#2A2A2A] p-2 px-4 shadow-[0_10px_40px_0_rgba(0,0,0,0.3)]">
            @foreach ($bottomItems as $item)
            @php($active = !empty($item['active']))
            @php($badge = (int) ($item['badge'] ?? 0))

            @if ($active)
            {{-- Active Item - Expanded --}}
            <a href="{{ $item['href'] }}" class="flex items-center -mx-3 shrink-0" aria-current="page">
                <div class="flex items-center gap-2.5 rounded-full bg-[#E64627] px-4 py-3 shadow-lg">
                    <img src="{{ asset($item['iconActive'] ?? $item['icon']) }}" class="w-6 h-6" alt=""
                        aria-hidden="true">
                    <span class="text-sm font-bold leading-none text-white">
                        {{ $item['label'] }}
                    </span>
                </div>
            </a>
            @else
            {{-- Inactive Item - Icon Only --}}
            <a href="{{ $item['href'] }}"
                class="relative flex items-center justify-center w-full p-2 mx-auto transition-all duration-200 rounded-full hover:bg-white/10 active:scale-95"
                aria-current="false" aria-label="{{ $item['label'] }}">
                <img src="{{ asset($item['icon']) }}" class="w-6 h-6" alt="{{ $item['label'] }}">

                @if ($badge > 0)
                <span
                    class="absolute -right-0.5 -top-0.5 grid h-5 min-w-[20px] px-1 place-items-center rounded-full bg-red-600 text-[10px] font-bold text-white ring-2 ring-[#2A2A2A]">
                    {{ $badge > 99 ? '99+' : $badge }}
                </span>
                @endif

                @if(!empty($item['new']))
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-[#FF6B18] rounded-full ring-2 ring-[#2A2A2A]"></span>
                @endif
            </a>
            @endif
            @endforeach
        </div>
    </div>
    </nav>
