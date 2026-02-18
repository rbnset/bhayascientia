{{-- resources/views/components/publication/navigation.blade.php --}}
@props([
'items' => [],
'maxWidth' => 'max-w-[640px]',
])

@php
// ✅ Filter menu berdasarkan auth requirement
$filteredItems = collect($items)->filter(function ($item) {
if (isset($item['auth']) && $item['auth'] === true) {
return auth()->check();
}
return true;
})->values()->all();

// ✅ Cek apakah ada menu yang aktif
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

{{-- ===================== SUB MENU (Desktop - tablet+) ===================== --}}
<nav aria-label="Sub menu publikasi" class="hidden mt-4 sm:block sm:mt-5">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">

            @foreach ($filteredItems as $index => $item)
            @php
            $activeRoutes = isset($item['active']) ? (array) $item['active'] : [];

            // ✅ Cek aktif dengan try-catch aman
            $isActive = false;
            try {
            $isActive = request()->routeIs($activeRoutes);
            } catch (\Exception $e) {}

            // DEFAULT: Jika tidak ada menu aktif, set item pertama (index 0) aktif
            if (!$hasActiveMenu && $index === 0) {
            $isActive = true;
            }

            // ✅ Handle badge — execute Closure jika ada
            $badgeValue = 0;
            if (isset($item['badge'])) {
            try {
            $badgeValue = is_callable($item['badge'])
            ? (int) call_user_func($item['badge'])
            : (int) $item['badge'];
            } catch (\Exception $e) {
            $badgeValue = 0;
            }
            }

            // ✅ Generate URL dengan error handling — skip jika route tidak ada
            try {
            $itemUrl = route($item['href']);
            } catch (\Exception $e) {
            continue;
            }

            // Desktop: selalu pakai icon dark
            $iconSrc = $item['icon'] ?? null;
            @endphp

            <a href="{{ $itemUrl }}" aria-current="{{ $isActive ? 'page' : 'false' }}"
                @class([ 'relative py-2.5 px-5 gap-2.5 font-semibold text-sm bg-white inline-flex items-center rounded-full border transition-all duration-300 group'
                , 'border-[#EEF0F7] text-[#737373] hover:border-[#FF6B18] hover:text-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18]/20 hover:-translate-y-0.5'=>
                !$isActive,
                'border-[#FF6B18] ring-2 ring-[#FF6B18]/20 text-[#FF6B18] shadow-sm' => $isActive,
                ])>

                {{-- Icon --}}
                @if($iconSrc)
                <span class="flex w-5 h-5 transition-transform duration-200 shrink-0 group-hover:scale-110">
                    <img src="{{ asset($iconSrc) }}" alt="" class="object-contain w-full h-full" aria-hidden="true"
                        onerror="this.style.display='none'">
                </span>
                @endif

                {{-- Label --}}
                <span class="whitespace-nowrap">{{ $item['label'] }}</span>

                {{-- Badge Count --}}
                @if($badgeValue > 0)
                <span
                    class="ml-0.5 px-2 py-0.5 bg-[#FF6B18] text-white text-xs font-bold rounded-full min-w-[20px] text-center">
                    {{ $badgeValue > 99 ? '99+' : $badgeValue }}
                </span>
                @endif

                {{-- NEW Badge --}}
                @if(!empty($item['new']))
                <span class="absolute -top-1 -right-1 px-1.5 py-0.5 bg-gradient-to-r from-[#FF6B18]
                    to-[#E64627] text-white text-[10px] font-bold rounded-full uppercase leading-none">
                    New
                </span>
                @endif

            </a>

            @endforeach

        </div>
    </div>
</nav>

{{-- ===================== BOTTOM NAV (Mobile only) ===================== --}}
<nav class="fixed left-0 right-0 z-40 px-4 bottom-5 sm:hidden" aria-label="Navigasi bawah">
    <div class="mx-auto w-full {{ $maxWidth }}">
        <div class="grid grid-flow-col auto-cols-fr items-center justify-between rounded-full
            bg-[#2A2A2A] p-2 px-4 shadow-[0_10px_40px_0_rgba(0,0,0,0.3)]">

            @foreach ($filteredItems as $index => $item)
            @php
            $activeRoutes = isset($item['active']) ? (array) $item['active'] : [];

            $isActive = false;
            try {
            $isActive = request()->routeIs($activeRoutes);
            } catch (\Exception $e) {}

            if (!$hasActiveMenu && $index === 0) {
            $isActive = true;
            }

            $badgeValue = 0;
            if (isset($item['badge'])) {
            try {
            $badgeValue = is_callable($item['badge'])
            ? (int) call_user_func($item['badge'])
            : (int) $item['badge'];
            } catch (\Exception $e) {
            $badgeValue = 0;
            }
            }

            try {
            $itemUrl = route($item['href']);
            } catch (\Exception $e) {
            continue;
            }

            // Mobile: aktif = dark icon, tidak aktif = white icon
            $iconSrc = $isActive
            ? ($item['icon'] ?? null)
            : ($item['iconWhite'] ?? $item['icon'] ?? null);
            @endphp

            @if($isActive)
            {{-- ✅ Active Item — Expanded dengan background --}}
            <a href="{{ $itemUrl }}" class="flex items-center justify-center shrink-0" aria-current="page">
                <div class="flex items-center gap-2.5 rounded-full bg-[#E64627] px-4 py-3 shadow-lg">
                    @if($iconSrc)
                    <img src="{{ asset($iconSrc) }}" class="flex-shrink-0 w-6 h-6" alt="" aria-hidden="true"
                        onerror="this.style.display='none'">
                    @endif
                    <span class="text-sm font-bold leading-none text-white whitespace-nowrap">
                        {{ $item['label'] }}
                    </span>
                </div>
            </a>

            @else
            {{-- ✅ Inactive Item — Icon only --}}
            <a href="{{ $itemUrl }}"
                class="relative flex items-center justify-center w-full p-2 mx-auto transition-all duration-200 rounded-full hover:bg-white/10 active:scale-95"
                aria-current="false" aria-label="{{ $item['label'] }}">

                @if($iconSrc)
                <img src="{{ asset($iconSrc) }}" class="w-6 h-6" alt="{{ $item['label'] }}"
                    onerror="this.style.display='none'">
                @else
                {{-- Fallback jika icon tidak ada --}}
                <span class="text-xs font-bold text-white/60">
                    {{ mb_strtoupper(mb_substr($item['label'], 0, 1)) }}
                </span>
                @endif

                {{-- Badge Count --}}
                @if($badgeValue > 0)
                <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-[20px] px-1
                    place-items-center rounded-full bg-red-500 text-[10px] font-bold
                    text-white ring-2 ring-[#2A2A2A]">
                    {{ $badgeValue > 99 ? '99+' : $badgeValue }}
                </span>
                @endif

                {{-- NEW Dot Indicator --}}
                @if(!empty($item['new']))
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-[#FF6B18]
                    rounded-full ring-2 ring-[#2A2A2A]"></span>
                @endif

            </a>
            @endif

            @endforeach

        </div>
    </div>
</nav>