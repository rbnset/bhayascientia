@props([
'author',
'publicationCount' => null,
'verified' => false,
])

@php
$authorArray = is_array($author) ? $author : (array) $author;

// Extract data
$id = $authorArray['id'] ?? null;
$userId = $authorArray['user_id'] ?? null;
$name = $authorArray['name'] ?? 'Unknown';
$avatar = $authorArray['photo_url'] ?? $authorArray['avatar'] ?? $authorArray['profile_photo_path'] ?? null;
$initials = $authorArray['initials'] ?? strtoupper(substr($name, 0, 2));
$specialty = $authorArray['affiliation'] ?? $authorArray['short_bio'] ?? $authorArray['specialty'] ?? null;
$publicationCount = $publicationCount ?? ($authorArray['publications_count'] ?? $authorArray['publication_count'] ?? 0);
$verified = $verified || ($authorArray['verified'] ?? false) || !empty($userId);

// ✅ Generate profile URL dengan ID (bukan username)
$profileIdentifier = $userId ?? $id;
$profileUrl = $profileIdentifier ? route('author.profile', $profileIdentifier) : null;

// ✅ Generate avatar URL - Method yang TERBUKTI BEKERJA
if ($avatar) {
// Check if avatar is full URL or storage path
if (filter_var($avatar, FILTER_VALIDATE_URL)) {
$avatarUrl = $avatar;
} else {
// Remove 'public/' prefix if exists
$cleanAvatar = str_starts_with($avatar, 'public/') ? substr($avatar, 7) : $avatar;
$avatarUrl = asset('storage/' . $cleanAvatar);
}
} else {
// Use UI Avatars as fallback
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($name) .
"&background=FF6B18&color=fff&size=160&bold=true&font-size=0.4&length=2";
}
@endphp

@if($profileUrl)
<a href="{{ $profileUrl }}" class="block group" aria-label="Lihat profil {{ $name }}"
    title="View {{ $name }}'s profile">
    @else
    <div class="block opacity-75 cursor-not-allowed group"
        onclick="event.preventDefault(); alert('Profile not available');">
        @endif
        <div
            class="relative bg-white flex flex-col items-center rounded-[20px] ring-1 ring-[#EEF0F7] p-5 sm:p-6 transition-all duration-300 hover:ring-2 hover:ring-[#FF6B18] hover:shadow-[0_8px_30px_rgba(255,107,24,0.12)] hover:-translate-y-1">

            {{-- Avatar dengan Background Image - METHOD YANG BEKERJA --}}
            <div class="relative mb-4">
                <div class="rounded-full ring-2 ring-white shadow-md group-hover:ring-[#FF6B18] transition-all duration-300"
                    style="width: 80px; height: 80px; background-image: url('{{ $avatarUrl }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #f3f4f6;">
                </div>

                {{-- Verified Badge --}}
                @if($verified)
                <div
                    class="absolute -bottom-1 -right-1 bg-[#FF6B18] rounded-full p-1.5 shadow-md ring-2 ring-white z-10">
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
                    class="font-bold text-base leading-tight text-[#1A1D29] line-clamp-2 group-hover:text-[#FF6B18] transition-colors duration-300 min-h-[2.5rem] flex items-center justify-center px-2">
                    {{ $name }}
                </h3>

                @if($specialty)
                <p class="text-xs text-[#6B7280] line-clamp-2 mb-1 min-h-[2rem] flex items-center justify-center px-2">
                    {{ $specialty }}
                </p>
                @endif

                <div class="flex items-center justify-center gap-2 pt-2 mt-2 border-t border-gray-100">
                    <svg class="w-4 h-4 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm text-[#6B7280]">
                        <span class="font-bold text-[#1A1D29]">{{ number_format($publicationCount) }}</span> Works
                    </span>
                </div>
            </div>

            {{-- Hover Arrow Indicator --}}
            @if($profileUrl)
            <div class="absolute transition-opacity duration-300 opacity-0 top-4 right-4 group-hover:opacity-100">
                <svg class="w-5 h-5 text-[#FF6B18]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
            @endif
        </div>
        @if($profileUrl)
</a>
@else
</div>
@endif