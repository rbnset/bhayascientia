@props([
'author',
'publicationCount' => null,
'profileUrl' => '#',
'verified' => false,
])

@php
$authorArray = is_array($author) ? $author : (array) $author;

$name = $authorArray['name'] ?? 'Unknown';
$avatar = $authorArray['photo_url'] ?? $authorArray['avatar'] ?? null;
$initials = $authorArray['initials'] ?? 'UN';
$specialty = $authorArray['affiliation'] ?? $authorArray['short_bio'] ?? $authorArray['specialty'] ?? null;
$publicationCount = $publicationCount ?? ($authorArray['publications_count'] ?? 0);

if (!$avatar) {
$avatar = "https://ui-avatars.com/api/?name=" . urlencode($initials) .
"&background=FF6B18&color=fff&size=128&bold=true&font-size=0.5&length=2";
}
@endphp

<a href="{{ $profileUrl }}" class="block group" aria-label="Lihat profil {{ $name }}">
    <div
        class="relative bg-white flex flex-col items-center rounded-[20px] ring-1 ring-[#EEF0F7] p-5 sm:p-6 transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18] hover:shadow-[0_8px_30px_rgba(255,107,24,0.12)] hover:-translate-y-1">

        {{-- Avatar dengan Background Image --}}
        <div class="relative mb-4">
            <div class="rounded-full ring-2 ring-white shadow-md group-hover:ring-[#FF6B18] transition-all duration-300"
                style="width: 70px; height: 70px; background-image: url('{{ $avatar }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #f3f4f6;">
            </div>

            {{-- Verified Badge --}}
            @if($verified)
            <div class="absolute -bottom-1 -right-1 bg-[#FF6B18] rounded-full p-1.5 shadow-md ring-2 ring-white z-10">
                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            @endif
        </div>

        {{-- Author Info --}}
        <div class="flex flex-col w-full gap-1 text-center">
            <h3
                class="font-semibold text-[15px] sm:text-base leading-tight text-[#1A1D29] line-clamp-1 group-hover:text-[#FF6B18] transition-colors duration-300">
                {{ $name }}
            </h3>

            @if($specialty)
            <p class="text-xs text-[#6B7280] line-clamp-1 mb-1">
                {{ $specialty }}
            </p>
            @endif

            <p class="text-sm leading-[21px] text-[#A3A6AE] font-medium">
                <span class="font-bold text-[#1A1D29]">{{ $publicationCount }}</span> Publikasi
            </p>
        </div>
    </div>
</a>