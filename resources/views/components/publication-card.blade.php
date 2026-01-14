@props(['id'])

<article
    class="group overflow-hidden transition-all duration-300 bg-white border rounded-2xl border-[#EEF0F7] hover:shadow-lg hover:border-[#FF6B18]/20">
    <a href="{{ route('publikasi.show', $id) }}" class="block">
        {{-- Thumbnail --}}
        <div class="aspect-[16/10] bg-gradient-to-br from-[#FF6B18]/10 to-[#FF6B18]/5 relative overflow-hidden">
            <img src="https://placehold.co/600x400/FFF7F2/FF6B18?text=Publication+{{ $id }}"
                alt="Cover publikasi {{ $id }}"
                class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105"
                loading="lazy">
            <div class="absolute top-3 right-3">
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-white/90 backdrop-blur-sm text-[#FF6B18]">
                    Technology
                </span>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-5">
            {{-- Meta --}}
            <div class="flex items-center gap-3 mb-3 text-xs text-[#737373]">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    12 Jan 2026
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    1.2k views
                </span>
            </div>

            {{-- Title --}}
            <h3
                class="mb-2 text-base font-bold transition-colors duration-200 text-[#1A1A1A] line-clamp-2 group-hover:text-[#FF6B18]">
                The Impact of Artificial Intelligence on Modern Healthcare Systems
            </h3>

            {{-- Authors --}}
            <p class="mb-3 text-sm text-[#737373] line-clamp-1">
                Dr. John Doe, Jane Smith, et al.
            </p>

            {{-- Abstract Preview --}}
            <p class="mb-4 text-sm text-[#737373] line-clamp-2">
                This research explores the transformative effects of AI technologies in healthcare delivery,
                patient outcomes, and medical diagnosis accuracy...
            </p>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-[#EEF0F7]">
                <span class="text-xs font-semibold text-[#FF6B18]">Read more →</span>
                <button type="button" class="p-2 transition-colors duration-200 rounded-full hover:bg-[#FFF7F2]"
                    onclick="event.preventDefault(); event.stopPropagation(); toggleFavorite({{ $id }})"
                    aria-label="Tambah ke favorit">
                    <svg class="w-5 h-5 transition-colors text-[#737373] hover:text-[#FF6B18]" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </button>
            </div>
        </div>
    </a>
</article>

@once
@push('scripts')
<script>
    function toggleFavorite(id) {
    console.log('Toggle favorite for publication:', id);
    // TODO: Implement AJAX favorite toggle
}
</script>
@endpush
@endonce
