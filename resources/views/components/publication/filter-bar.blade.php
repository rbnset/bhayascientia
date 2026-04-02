@props([
'title' => 'Pilih Jenis Publikasi',
'helper' => 'Buku, Jurnal, atau Opini',
'types' => [],
'selectedType' => null,
'filterSort' => 'latest',
'hasActiveFilters' => false,
])

@php
$typeCount = count($types);
$isAll = !$selectedType || $selectedType === 'all';
@endphp

<section id="publication-filter" class="mt-8 sm:mt-10" aria-label="Filter publikasi">
    <div class="bg-white p-4 sm:p-5 rounded-[22px] ring-1 ring-[#EEF0F7] shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            {{-- Title + helper --}}
            <div class="min-w-0">
                <h2 class="font-bold sm:text-[20px] sm:leading-[28px] text-[18px] leading-[26px] text-[#111827]">
                    {{ $title }}
                </h2>
                <p class="mt-1 sm:text-sm sm:leading-[21px] text-[11px] leading-[16px] text-[#A3A6AE]">
                    {{ $helper }}
                </p>
            </div>

            {{-- Filter + type selector --}}
            <div class="flex flex-wrap items-center justify-start gap-2 sm:gap-3 sm:justify-end">

                <span
                    class="px-3 py-2 sm:px-4 sm:py-2 font-bold sm:text-xs sm:leading-[18px] w-fit rounded-full bg-[#FFECE1] text-[10px] leading-[14px] text-[#FF6B18]"
                    aria-hidden="true">
                    FILTER
                </span>

                @if($typeCount > 4)
                {{-- Dropdown jika lebih dari 4 type --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-bold rounded-full bg-white ring-1 ring-[#EEF0F7] shadow-sm hover:bg-[#F4F6FB] hover:ring-[#FF6B18] transition-all"
                        aria-haspopup="true" :aria-expanded="open">
                        @php
                        $active = $isAll ? null : collect($types)->firstWhere('slug', $selectedType);
                        @endphp
                        <span class="truncate max-w-[130px] sm:max-w-[170px]">
                            {{ $active?->name ?? 'Semua Publikasi' }}
                        </span>
                        <span
                            class="hidden sm:inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-[#FFECE1] text-[#FF6B18]">
                            {{ $typeCount }}
                        </span>
                        <svg class="w-4 h-4 text-[#FF6B18] transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition @click.away="open = false"
                        class="absolute right-0 mt-2 w-52 sm:w-64 bg-white rounded-2xl ring-1 ring-[#EEF0F7] shadow-xl z-20 overflow-hidden"
                        style="display:none;">
                        <div class="overflow-y-auto max-h-64">
                            {{-- Opsi Semua --}}
                            <a href="{{ route('publikasi.index') }}"
                                class="flex items-center justify-between px-4 py-2.5 text-xs sm:text-sm font-semibold {{ $isAll ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#1A1A1A] hover:bg-[#F4F6FB]' }}"
                                @click="open = false">
                                <span>Semua Publikasi</span>
                                @if($isAll)
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                @endif
                            </a>

                            @if($typeCount > 0)
                            <div class="mx-4 h-px bg-[#EEF0F7] my-1"></div>
                            @endif

                            @foreach($types as $type)
                            @php $isActive = $selectedType === $type->slug; @endphp
                            <a href="{{ route('publikasi.index', ['type' => $type->slug]) }}"
                                class="flex items-center justify-between px-4 py-2.5 text-xs sm:text-sm font-semibold {{ $isActive ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#1A1A1A] hover:bg-[#F4F6FB]' }}"
                                @click="open = false">
                                <span class="truncate">{{ $type->name }}</span>
                                @if($isActive)
                                <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                {{-- Tab layout jika ≤ 4 type --}}
                <div id="pubTabs" role="tablist" aria-label="Jenis publikasi"
                    class="inline-flex flex-wrap gap-2 bg-white p-1.5 rounded-full ring-1 ring-[#EEF0F7] shadow-sm">

                    {{-- Tab: Semua --}}
                    <a href="{{ route('publikasi.index') }}" role="tab" id="tab-all"
                        class="pub-tab group relative px-4 sm:px-5 py-2.5 text-xs sm:text-sm font-bold rounded-full transition-all duration-200 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white {{ $isAll ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#1A1A1A]' }}"
                        aria-selected="{{ $isAll ? 'true' : 'false' }}" tabindex="{{ $isAll ? '0' : '-1' }}">
                        Semua
                        @if($isAll)
                        <span
                            class="active-dot absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1 w-1.5 h-1.5 bg-[#FF6B18] rounded-full animate-pulse"></span>
                        @endif
                        <span
                            class="absolute inset-x-0 -bottom-1 h-0.5 bg-[#FF6B18] scale-x-0 group-hover:scale-x-100 transition-transform duration-200 rounded-full"></span>
                    </a>

                    @foreach($types as $type)
                    @php $isActive = $selectedType === $type->slug; @endphp
                    <a href="{{ route('publikasi.index', ['type' => $type->slug]) }}" role="tab"
                        id="tab-{{ $type->slug }}" data-type="{{ $type->slug }}"
                        class="pub-tab group relative px-4 sm:px-5 py-2.5 text-xs sm:text-sm font-bold rounded-full transition-all duration-200 hover:bg-[#F4F6FB] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-white {{ $isActive ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#1A1A1A]' }}"
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
                @endif
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush