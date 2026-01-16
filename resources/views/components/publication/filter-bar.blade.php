@props([
'title' => 'Pilih Jenis Publikasi',
'helper' => 'Buku, Jurnal, atau Opini',
'types' => [],
'selectedType' => null
])

<section id="publication-filter" class="mt-8 sm:mt-10" aria-label="Filter publikasi">
    <div class="bg-white p-4 sm:p-5 rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0">
                <h2 class="font-bold sm:text-[20px] sm:leading-[28px] text-[18px] leading-[26px] text-[#111827]">
                    {{ $title }}
                </h2>
                <p class="mt-1 sm:text-sm sm:leading-[21px] text-[11px] leading-[16px] text-[#A3A6AE]">
                    {{ $helper }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <span
                    class="px-3 py-2 sm:px-4 sm:py-2 font-bold sm:text-xs sm:leading-[18px] w-fit rounded-full bg-[#FFECE1] text-[10px] leading-[14px] text-[#FF6B18]"
                    aria-hidden="true">
                    FILTER
                </span>

                <div id="pubTabs" role="tablist" aria-label="Jenis publikasi"
                    class="gap-1 bg-white p-1.5 inline-flex items-center rounded-full ring-1 ring-[#EEF0F7] shadow-sm">
                    @foreach($types as $index => $type)
                    @php
                    $isActive = $selectedType === $type->slug || (!$selectedType && $index === 0);
                    @endphp
                    {{-- ✅ Ganti button jadi link (a tag) --}}
                    <a href="{{ route('publikasi.index', ['type' => $type->slug]) }}" role="tab"
                        id="tab-{{ $type->slug }}" data-type="{{ $type->slug }}"
                        class="pub-tab group relative px-5 py-2.5 text-sm font-bold rounded-full transition-all duration-200 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white {{ $isActive ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#1A1A1A]' }}"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}" tabindex="{{ $isActive ? '0' : '-1' }}">

                        {{ $type->name }}

                        @if($isActive)
                        <span
                            class="active-dot absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1 w-1.5 h-1.5 bg-[#FF6B18] rounded-full animate-pulse"></span>
                        @endif

                        <span
                            class="absolute inset-x-0 -bottom-1 h-0.5 bg-[#FF6B18] scale-x-0 group-hover:scale-x-100 transition-transform duration-200 rounded-full"></span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>