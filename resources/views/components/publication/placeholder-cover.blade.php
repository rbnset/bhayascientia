@props([
'title' => '',
'initials' => '',
'category' => '',
'publicationType' => '',
'authorDisplay' => '',
'slug' => '',
])

@php
// Generate gradient berdasarkan publication type
$typeGradients = [
'Buku' => ['from' => '10B981', 'to' => '059669'],
'Jurnal' => ['from' => 'FF6B18', 'to' => 'E64627'],
'Opini' => ['from' => '3B82F6', 'to' => '1D4ED8'],
'Artikel' => ['from' => 'F59E0B', 'to' => 'D97706'],
'Penelitian' => ['from' => '8B5CF6', 'to' => '6D28D9'],
'Skripsi' => ['from' => 'EC4899', 'to' => 'BE185D'],
'Tesis' => ['from' => '06B6D4', 'to' => '0891B2'],
'Disertasi' => ['from' => 'EF4444', 'to' => 'DC2626'],
'Makalah' => ['from' => '14B8A6', 'to' => '0F766E'],
'Laporan' => ['from' => 'A855F7', 'to' => '7C3AED'],
'default' => ['from' => 'A3A6AE', 'to' => '6B7280'],
];

$gradient = $typeGradients[$publicationType] ?? $typeGradients['default'];
@endphp

{{-- ✅ Main container dengan inline gradient style --}}
<div class="relative flex flex-col w-full h-full text-center text-white"
    style="background: linear-gradient(135deg, #{{ $gradient['from'] }} 0%, #{{ $gradient['to'] }} 100%);">

    {{-- Decorative Pattern Background --}}
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="pattern-{{ $slug }}" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="1.5" fill="white" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#pattern-{{ $slug }})" />
        </svg>
    </div>

    {{-- Gradient Overlay --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-white/10"></div>

    {{-- Content Container --}}
    <div class="relative z-10 flex flex-col justify-between h-full p-3 sm:p-4">

        {{-- TOP SECTION: Logo & Type --}}
        <div class="flex items-start justify-between">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <div
                    class="flex items-center justify-center w-8 h-8 border rounded-lg sm:w-10 sm:h-10 bg-white/20 backdrop-blur-sm border-white/30">
                    <svg class="w-5 h-5 text-white sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-4.41 0-8-3.59-8-8V8.3l8-3.99 8 3.99V12c0 4.41-3.59 8-8 8z" />
                        <path d="M12 6L7 8.5v5.3c0 3.11 2.14 6.01 5 6.7 2.86-.69 5-3.59 5-6.7V8.5L12 6z" />
                    </svg>
                </div>
            </div>

            {{-- Publication Type Badge --}}
            <div class="px-2.5 py-1 bg-white/25 backdrop-blur-sm rounded-full border border-white/40">
                <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wide">{{ $publicationType }}</span>
            </div>
        </div>

        {{-- MIDDLE SECTION: Main Content --}}
        <div class="flex flex-col items-center justify-center flex-1 my-2 space-y-2 sm:space-y-3">
            {{-- Large Initial/Monogram --}}
            <div
                class="text-4xl font-black leading-none tracking-tight sm:text-5xl md:text-6xl opacity-95 drop-shadow-lg">
                {{ $initials }}
            </div>

            {{-- Decorative Line --}}
            <div class="flex items-center justify-center gap-2">
                <div class="h-[2px] w-6 sm:w-8 bg-white/60 rounded"></div>
                <svg class="w-2.5 h-2.5 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="h-[2px] w-6 sm:w-8 bg-white/60 rounded"></div>
            </div>

            {{-- Title --}}
            <div class="text-[10px] sm:text-xs font-bold leading-tight opacity-90 line-clamp-3 px-2 drop-shadow">
                {{ $title }}
            </div>
        </div>

        {{-- BOTTOM SECTION: Category & Author --}}
        <div class="space-y-1.5 sm:space-y-2">
            {{-- Category --}}
            <div class="flex items-center justify-center gap-1.5">
                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white/80 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="text-[9px] sm:text-[10px] font-semibold opacity-90">{{ $category }}</span>
            </div>

            {{-- Author --}}
            <div class="flex items-center justify-center gap-1.5 px-2">
                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white/80 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[9px] sm:text-[10px] font-semibold opacity-90 truncate">{{ $authorDisplay }}</span>
            </div>

            {{-- Bottom decorative line --}}
            <div class="h-[2px] w-16 sm:w-20 mx-auto bg-white/40 rounded mt-2"></div>
        </div>
    </div>

    {{-- Corner Decorations --}}
    <div class="absolute top-0 right-0 w-16 h-16 pointer-events-none sm:w-20 sm:h-20 opacity-10">
        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0L100 0L100 100C55.8172 100 20 64.1828 20 20C20 8.9543 8.9543 0 0 0Z" fill="white" />
        </svg>
    </div>
    <div class="absolute bottom-0 left-0 w-16 h-16 rotate-180 pointer-events-none sm:w-20 sm:h-20 opacity-10">
        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0L100 0L100 100C55.8172 100 20 64.1828 20 20C20 8.9543 8.9543 0 0 0Z" fill="white" />
        </svg>
    </div>
</div>