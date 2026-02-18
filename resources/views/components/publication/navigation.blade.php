{{-- resources/views/components/publication/navigation.blade.php --}}
@props([
'items' => [],
'maxWidth' => 'max-w-[640px]',
])

@php
$filteredItems = collect($items)->filter(function ($item) {
if (isset($item['auth']) && $item['auth'] === true) {
return auth()->check();
}
return true;
})->values()->all();

$totalItems = count($filteredItems);

$hasActiveMenu = false;
foreach ($filteredItems as $item) {
$activeRoutes = isset($item['active']) ? (array) $item['active'] : [];
try {
if (request()->routeIs($activeRoutes)) {
$hasActiveMenu = true;
break;
}
} catch (\Exception $e) {}
}
@endphp

{{-- =====================================================================
DESKTOP SUB MENU (sm+)
===================================================================== --}}
<nav aria-label="Sub menu publikasi" class="hidden mt-4 sm:block sm:mt-5">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">

            @foreach ($filteredItems as $index => $item)
            @php
            $activeRoutes = isset($item['active']) ? (array) $item['active'] : [];
            $isActive = false;
            try { $isActive = request()->routeIs($activeRoutes); } catch (\Exception $e) {}
            if (!$hasActiveMenu && $index === 0) $isActive = true;

            $badgeValue = 0;
            if (isset($item['badge'])) {
            try {
            $badgeValue = is_callable($item['badge'])
            ? (int) call_user_func($item['badge'])
            : (int) $item['badge'];
            } catch (\Exception $e) {}
            }

            try { $itemUrl = route($item['href']); } catch (\Exception $e) { continue; }
            $iconSrc = $item['icon'] ?? null;
            @endphp

            <a href="{{ $itemUrl }}" aria-current="{{ $isActive ? 'page' : 'false' }}" class="
                    relative inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5
                    text-sm font-semibold rounded-full border bg-white
                    outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]/50
                    transition-all duration-200 ease-out group select-none
                    {{ $isActive
                        ? 'border-[#FF6B18] text-[#FF6B18] ring-2 ring-[#FF6B18]/15 shadow-sm shadow-[#FF6B18]/10'
                        : 'border-[#EEF0F7] text-[#737373] hover:border-[#FF6B18] hover:text-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18]/10 hover:-translate-y-0.5 hover:shadow-sm active:translate-y-0'
                    }}
                ">
                @if($iconSrc)
                <span class="flex w-[18px] h-[18px] sm:w-5 sm:h-5 shrink-0
                    transition-transform duration-200 ease-out
                    {{ $isActive ? 'scale-110' : 'group-hover:scale-110' }}">
                    <img src="{{ asset($iconSrc) }}" alt="" class="object-contain w-full h-full" aria-hidden="true"
                        onerror="this.style.display='none'">
                </span>
                @endif

                <span class="leading-none whitespace-nowrap">{{ $item['label'] }}</span>

                @if($badgeValue > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5
                    bg-[#FF6B18] text-white text-[11px] font-bold rounded-full leading-none">
                    {{ $badgeValue > 99 ? '99+' : $badgeValue }}
                </span>
                @endif

                @if(!empty($item['new']))
                <span class="absolute -top-1.5 -right-1.5 px-1.5 py-[3px]
                    bg-gradient-to-r from-[#FF6B18] to-[#E64627]
                    text-white text-[9px] font-black rounded-full uppercase leading-none
                    shadow-sm shadow-[#FF6B18]/30 animate-[pulse_2s_ease-in-out_infinite]">
                    NEW
                </span>
                @endif
            </a>
            @endforeach

        </div>
    </div>
</nav>

{{-- =====================================================================
MOBILE BOTTOM NAV (< sm)=====================================================================--}} <nav
    class="fixed left-0 right-0 z-50 px-4 bottom-4 sm:hidden" aria-label="Navigasi bawah">
    <div class="mx-auto w-full {{ $maxWidth }}">

        {{-- ✅ Container: padding p-1.5 = jarak antara pill aktif dengan tepi container --}}
        <div class="relative flex items-center justify-between
            rounded-full bg-[#1E1E1E]/95 backdrop-blur-md
            p-1.5
            shadow-[0_8px_32px_rgba(0,0,0,0.35),0_2px_8px_rgba(0,0,0,0.2)]
            border border-white/5">

            @foreach ($filteredItems as $index => $item)
            @php
            $activeRoutes = isset($item['active']) ? (array) $item['active'] : [];
            $isActive = false;
            try { $isActive = request()->routeIs($activeRoutes); } catch (\Exception $e) {}
            if (!$hasActiveMenu && $index === 0) $isActive = true;

            $badgeValue = 0;
            if (isset($item['badge'])) {
            try {
            $badgeValue = is_callable($item['badge'])
            ? (int) call_user_func($item['badge'])
            : (int) $item['badge'];
            } catch (\Exception $e) {}
            }

            try { $itemUrl = route($item['href']); } catch (\Exception $e) { continue; }

            $iconSrc = $isActive
            ? ($item['icon'] ?? null)
            : ($item['iconWhite'] ?? $item['icon'] ?? null);

            $isFirst = $index === 0;
            $isLast = $index === $totalItems - 1;
            @endphp

            @if($isActive)
            {{-- ✅ Active pill — rounded penuh semua sisi, jarak dari tepi = p-1.5 container --}}
            <a href="{{ $itemUrl }}" aria-current="page" class="flex items-center gap-2
                    rounded-full
                    bg-gradient-to-r from-[#FF6B18] to-[#E64627]
                    px-4 py-2.5
                    shadow-[0_4px_16px_rgba(255,107,24,0.4)]
                    transition-all duration-300 ease-out
                    active:scale-95 select-none shrink-0">

                @if($iconSrc)
                <img src="{{ asset($iconSrc) }}" class="w-5 h-5 shrink-0" alt="" aria-hidden="true"
                    onerror="this.style.display='none'">
                @endif

                <span class="text-sm font-bold leading-none text-white whitespace-nowrap">
                    {{ $item['label'] }}
                </span>

            </a>

            @else
            {{-- ✅ Inactive icon — flex-1 agar terdistribusi merata --}}
            <a href="{{ $itemUrl }}" aria-current="false" aria-label="{{ $item['label'] }}"
                class="relative flex items-center justify-center flex-1 h-10 transition-all duration-200 ease-out rounded-full select-none hover:bg-white/10 active:scale-90 active:bg-white/15">

                @if($iconSrc)
                <img src="{{ asset($iconSrc) }}" class="w-5 h-5 transition-transform duration-200"
                    alt="{{ $item['label'] }}" onerror="this.style.display='none'">
                @else
                <span class="text-sm font-bold leading-none text-white/50">
                    {{ mb_strtoupper(mb_substr($item['label'], 0, 1)) }}
                </span>
                @endif

                @if($badgeValue > 0)
                <span class="absolute -top-0.5 right-2
                    inline-flex items-center justify-center
                    min-w-[18px] h-[18px] px-1
                    bg-red-500 text-white text-[9px] font-bold
                    rounded-full leading-none ring-2 ring-[#1E1E1E]">
                    {{ $badgeValue > 99 ? '99+' : $badgeValue }}
                </span>
                @endif

                @if(!empty($item['new']))
                <span class="absolute top-0.5 right-2
                    w-2 h-2 rounded-full bg-[#FF6B18]
                    ring-2 ring-[#1E1E1E]
                    animate-[pulse_2s_ease-in-out_infinite]">
                </span>
                @endif

            </a>
            @endif

            @endforeach

        </div>
    </div>
    </nav>

    {{-- Safe area spacer --}}
    <div class="h-20 sm:hidden" aria-hidden="true"></div>