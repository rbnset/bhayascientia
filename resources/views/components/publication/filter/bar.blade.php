@props([
'activeType' => 'all',
])

<section id="publication-filter" {{ $attributes->merge(['class' => 'mt-8 sm:mt-10']) }}>
    <div class="bg-white p-4 sm:p-5 rounded-[22px] ring-1 ring-[#EEF0F7]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            {{-- Left: Section Header --}}
            <x-ui.section-header title="Pilih Jenis Publikasi" subtitle="Buku, Jurnal, atau Opini" />

            {{-- Right: Badge + Type Selector --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <x-ui.badge variant="primary">
                    FILTER
                </x-ui.badge>

                <x-publication.filter.type-selector :activeType="$activeType" />
            </div>
        </div>
    </div>
</section>
