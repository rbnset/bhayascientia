@props([
'authors' => collect(),
'title' => 'Jelajahi Karya Masterpiece<br />dari Para Penulis Terbaik',
'badge' => 'PENULIS TERBAIK',
'selectedType' => null
])

<section id="best-authors" class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-12 sm:mt-16 lg:mt-20"
    aria-labelledby="best-authors-heading">
    {{-- Header --}}
    <div class="flex flex-col gap-3 mb-8 sm:gap-4 sm:mb-10">
        <div class="flex flex-col items-start gap-3 sm:gap-4">
            <span
                class="inline-flex items-center font-bold text-xs sm:text-sm rounded-full bg-[#FFECE1] px-4 sm:px-5 py-2 leading-none text-[#FF6B18] shadow-sm">
                {{ $badge }}
            </span>

            <h2 id="best-authors-heading"
                class="font-bold text-[22px] leading-[32px] sm:text-[26px] sm:leading-[39px] lg:text-[28px] lg:leading-[42px] text-[#1A1D29]">
                {!! $title !!}
            </h2>
        </div>

        @if($selectedType && $selectedType !== 'all')
        <p class="text-sm text-[#6B7280]">
            Menampilkan penulis terbaik untuk kategori:
            <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType) }}</span>
        </p>
        @endif
    </div>

    {{-- Authors Grid --}}
    @if($authors->isNotEmpty())
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 sm:gap-4 lg:gap-5">
        @foreach($authors as $authorData)
        @php
        // Jika $authorData adalah array (dari Action), convert ke object
        if (is_array($authorData)) {
        // Buat mock object untuk compatibility
        $authorObj = (object) [
        'id' => $authorData['id'] ?? null,
        'name' => $authorData['name'] ?? 'Unknown',
        'photo_url' => $authorData['avatar'] ?? '',
        'initials' => $authorData['initials'] ?? 'UN',
        'affiliation' => $authorData['specialty'] ?? null,
        'short_bio' => null,
        'publications_count' => $authorData['publication_count'] ?? 0,
        ];
        $profileUrl = $authorData['profile_url'] ?? '#';
        $verified = $authorData['verified'] ?? false;
        } else {
        // Jika sudah model Author
        $authorObj = $authorData;
        $profileUrl = route('author.show', $authorObj->id);
        $verified = $authorObj->user_id !== null;
        }
        @endphp

        {{-- ✅ GUNAKAN NAMESPACE publication. --}}
        <x-publication.author-card :author="$authorObj" :profileUrl="$profileUrl" :verified="$verified" />
        @endforeach
    </div>
    @else
    {{-- Empty State --}}
    <div class="bg-gradient-to-br from-[#FAFBFC] to-white rounded-[24px] ring-1 ring-[#EEF0F7] p-8 sm:p-12 text-center">
        <div class="max-w-md mx-auto">
            <div
                class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-5 bg-[#FFF5ED] rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-[#FF6B18]" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg sm:text-xl text-[#1A1D29] mb-2">Belum Ada Penulis</h3>
            <p class="text-sm sm:text-base text-[#A3A6AE]">
                Belum ada penulis yang mempublikasikan
                <span class="font-bold text-[#FF6B18]">{{ ucfirst($selectedType ?? 'kategori ini') }}</span>
            </p>
        </div>
    </div>
    @endif
</section>