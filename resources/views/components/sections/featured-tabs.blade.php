@props([
'badge' => 'Fitur Unggulan',
'title' => 'Fitur inti untuk penulis',
'description' => 'Bantu naskah lebih rapi, sitasi lebih konsisten, dan progres lebih mudah dipantau dari satu alur
kerja.',

'tabs' => [
[
'step' => 'Fitur 1',
'label' => 'Workspace',
'icon' => 'assets/images/icons/crown.svg',
'title' => 'Workspace penulisan yang rapi',
'description' => 'Susun outline sampai final draft dengan struktur yang lebih tertata dan enak dibaca.',
'features' => [
'Struktur tulisan lebih mudah diikuti.',
'Checklist rapih untuk mengurangi revisi berulang.',
'Fokus ke isi, bukan beresin format.',
'Siap submit tanpa bolak-balik file.',
],
'image' => 'assets/images/thumbnails/image.png',
'ctaText' => 'Mulai menulis',
'ctaHref' => '#',
],
[
'step' => 'Fitur 2',
'label' => 'Sitasi',
'icon' => 'assets/images/icons/note-2.svg',
'title' => 'Sitasi lebih konsisten',
'description' => 'Bantu menjaga rujukan tetap konsisten agar naskah lebih kredibel dan minim koreksi kecil.',
'features' => [
'Kurangi typo rujukan dan inkonsistensi format.',
'Lebih mudah cek daftar pustaka.',
'Mengurangi revisi repetitif dari editor.',
'Konten lebih rapi saat dibaca.',
],
'image' => 'assets/images/thumbnails/image.png',
'ctaText' => 'Cek sitasi',
'ctaHref' => '#',
],
[
'step' => 'Fitur 3',
'label' => 'Tracking',
'icon' => 'assets/images/icons/device-message.svg',
'title' => 'Pantau progres & catatan',
'description' => 'Status naskah lebih jelas, catatan revisi lebih terarah, dan komunikasinya tidak tercecer.',
'features' => [
'Status mudah dipahami tanpa tebak-tebakan.',
'Catatan revisi terkumpul rapi.',
'Progres terlihat jelas dari awal sampai akhir.',
'Lebih tenang karena tahu next step.',
],
'image' => 'assets/images/thumbnails/image.png',
'ctaText' => 'Lihat progres',
'ctaHref' => '#',
],
[
'step' => 'Fitur 4',
'label' => 'Publikasi',
'icon' => 'assets/images/icons/lock.svg',
'title' => 'Publikasi lebih terstruktur',
'description' => 'Saat naskah diterima, publikasi siap ditampilkan dengan format yang lebih konsisten.',
'features' => [
'Tampil sebagai portofolio publikasi.',
'Lebih mudah dibagikan melalui tautan.',
'Format tampil konsisten untuk pembaca.',
'Memperkuat kredibilitas karya.',
],
'image' => 'assets/images/thumbnails/image.png',
'ctaText' => 'Lihat publikasi',
'ctaHref' => 'publicationPage.html',
],
],

'checkIcon' => 'assets/images/icons/ic_check.svg',
])

@php
$tabs = is_array($tabs) ? $tabs : [];
$first = $tabs[0] ?? null;
$uid = 'featuredTabs_' . substr(md5(json_encode($tabs)), 0, 8);
@endphp

<section id="featured" class="pt-6 mt-10 sm:mt-12" data-featured-tabs="{{ $uid }}">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex flex-col gap-8">
            {{-- Heading --}}
            <div class="flex flex-col gap-3 text-center">
                <p
                    class="text-xs font-bold px-4 py-2 mx-auto inline-flex w-fit items-center justify-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                    {{ $badge }}
                </p>

                <h2 class="text-2xl sm:text-3xl font-bold text-[#111827]">
                    {{ $title }}
                </h2>

                <p class="text-sm sm:text-base max-w-2xl mx-auto leading-[22px] text-[#6B7280]">
                    {{ $description }}
                </p>
            </div>

            {{-- Tabs --}}
            <div class="w-full">
                <div class="pb-3 overflow-x-auto overscroll-x-contain">
                    <div class="gap-4 sm:gap-6 flex w-max min-w-full justify-start border-b border-[#E7EBEA]"
                        role="tablist" aria-label="Fitur unggulan">
                        @foreach ($tabs as $i => $tab)
                        <button type="button" class="tab-menu group sm:min-w-[220px] py-3 rounded-2xl flex min-w-[200px] cursor-pointer flex-col justify-between
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F8F9FC]
                       active:scale-[0.99] transition-transform duration-200"
                            aria-label="{{ $tab['step'] }}: {{ $tab['label'] }}"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}" role="tab" id="{{ $uid }}_tab_{{ $i }}"
                            aria-controls="{{ $uid }}_panel" tabindex="{{ $i === 0 ? '0' : '-1' }}"
                            data-tab-index="{{ $i }}">
                            <div class="flex items-center gap-4">
                                <div class="tab-icon-container h-11 w-11 sm:h-[50px] sm:w-[50px] flex shrink-0 items-center justify-center rounded-full
                           {{ $i === 0 ? 'bg-[#FF6B18]' : 'bg-[#EEF0F7]' }}
                           transition-colors group-hover:bg-[#FF6B18]">
                                    <img src="{{ asset($tab['icon']) }}" class="w-6 h-6" alt="" aria-hidden="true" />
                                </div>

                                <div class="leading-tight text-left">
                                    <h3
                                        class="{{ $i === 0 ? 'font-semibold' : 'font-medium' }} text-base sm:text-[20px] text-[#111827]">
                                        {{ $tab['step'] }}
                                    </h3>
                                    <span class="mt-1 text-xs sm:text-sm font-semibold block text-[#6B7280]">
                                        {{ $tab['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 tab-indicator">
                                <div
                                    class="h-[3px] w-full rounded-full {{ $i === 0 ? 'bg-[#111827]' : 'bg-transparent' }} transition-all duration-300">
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Panel --}}
            <div class="flex flex-col gap-6 tab-content lg:flex-row lg:items-center lg:gap-10" role="tabpanel"
                id="{{ $uid }}_panel" aria-labelledby="{{ $uid }}_tab_0" tabindex="0">
                <div
                    class="tab-img lg:w-[450px] sm:h-[360px] lg:h-[470px] h-[240px] w-full shrink-0 overflow-hidden rounded-[26px] bg-[#F4F6FB] border border-[#EEF0F7]">
                    <img src="{{ $first ? asset($first['image']) : '' }}" alt="Ilustrasi fitur"
                        class="object-cover w-full h-full" loading="lazy" />
                </div>

                <div class="flex flex-col gap-6">
                    <div class="gap-2.5 flex flex-col">
                        <h4
                            class="tab-title text-xl sm:text-2xl lg:text-[32px] lg:leading-[46px] font-bold text-[#111827]">
                            {{ $first['title'] ?? '' }}
                        </h4>

                        <p class="tab-description text-sm sm:text-base leading-7 text-[#6B7280]">
                            {{ $first['description'] ?? '' }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-4 tab-features">
                        @foreach (($first['features'] ?? []) as $f)
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-full bg-[#FF6B18]">
                                <img src="{{ asset($checkIcon) }}" alt="" class="w-4 h-4" aria-hidden="true" />
                            </div>
                            <p class="text-sm sm:text-base leading-6 font-semibold text-[#111827]">
                                {{ $f }}
                            </p>
                        </div>
                        @endforeach
                    </div>

                    <a href="{{ $first['ctaHref'] ?? '#' }}" class="tab-cta px-5 py-3 text-sm font-bold hover:text-white w-fit rounded-full border border-[#111827] transition-all duration-300
                   hover:border-[#FF6B18] hover:bg-[#FF6B18] hover:ring-2 hover:ring-[#FF6B18]
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]">
                        {{ $first['ctaText'] ?? 'Pelajari' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- data untuk JS --}}
    <script type="application/json" data-featured-tabs-data="{{ $uid }}">
        {!! json_encode([
      'tabs' => array_map(function ($t) {
        return [
          'title' => $t['title'] ?? '',
          'description' => $t['description'] ?? '',
          'features' => $t['features'] ?? [],
          'image' => asset($t['image'] ?? ''),
          'ctaText' => $t['ctaText'] ?? 'Pelajari',
          'ctaHref' => $t['ctaHref'] ?? '#',
        ];
      }, $tabs),
      'checkIcon' => asset($checkIcon),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</section>
