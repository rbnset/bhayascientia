@props([
'badge' => 'Fitur pengembangan',
'title' => 'Fitur berikutnya',
'description' => 'Roadmap berikutnya fokus pada pembelajaran, event, konsultasi dan artikel untuk memperluas akses
publik terhadap gagasan akademik yang bertanggung jawab.',

'cards' => [
[
'key' => 'LMS',
'title' => 'LMS',
'subtitle' => 'Kelas, modul, kuis, sertifikat',
'image' => 'assets/images/thumbnails/lms.jpg',
'aria' => 'Minta notifikasi LMS (demo)',
],
[
'key' => 'Event',
'title' => 'Event',
'subtitle' => 'Webinar, seminar, workshop',
'image' => 'assets/images/thumbnails/event.jpg',
'aria' => 'Minta notifikasi Event (demo)',
],
[
'key' => 'Konsultasi',
'title' => 'Konsultasi',
'subtitle' => 'Rapikan struktur & sitasi',
'image' => 'assets/images/thumbnails/konsultasi.jpg',
'aria' => 'Minta notifikasi Konsultasi (demo)',
],
[
'key' => 'Artikel',
'title' => 'Artikel',
'subtitle' => 'Insight, studi kasus, komunitas',
'image' => 'assets/images/thumbnails/blog.jpg',
'aria' => 'Minta notifikasi Artikel (demo)',
],
],

'iconStar' => 'assets/images/icons/star.svg',
'iconCrown' => 'assets/images/icons/crown.svg',
'iconArrow' => 'assets/images/icons/sign_right.svg',

'toastText' => 'Notifikasi tersimpan (demo).',
])

@php
$cards = is_array($cards) ? $cards : [];
$uid = 'comingSoon_' . substr(md5(json_encode($cards)), 0, 8);
@endphp

<section class="pt-12 mt-10 sm:mt-12" data-coming-soon="{{ $uid }}">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="text-center">
            <p class="px-4 py-2 text-xs font-bold inline-flex items-center rounded-full bg-[#FFECE1] text-[#FF6B18]">
                {{ $badge }}
            </p>

            <h2 class="mt-4 text-2xl font-bold sm:text-3xl text-[#111827]">
                {{ $title }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm sm:text-base sm:leading-[24px] mx-auto leading-[21px] text-[#6B7280]">
                {{ $description }}
            </p>
        </div>

        {{-- Cards --}}
        <div class="mt-8">
            <div class="relative">
                {{-- hint gradient mobile --}}
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 w-10 bg-gradient-to-r from-[#F8F9FC] to-transparent lg:hidden">
                </div>
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-[#F8F9FC] to-transparent lg:hidden">
                </div>

                <div id="roadmapCards"
                    class="flex gap-5 pb-4 overflow-x-auto lg:pb-0 lg:overflow-x-hidden overscroll-x-contain scroll-smooth snap-x snap-mandatory"
                    aria-label="Roadmap fitur berikutnya" data-roadmap-wrap>
                    @foreach ($cards as $i => $c)
                    <article
                        class="card snap-start group rounded-3xl sm:h-[475px] sm:w-[320px] lg:shrink relative h-[360px] w-[260px] shrink-0 overflow-clip transition-all duration-300 border border-transparent"
                        data-card="{{ $i + 1 }}" data-key="{{ $c['key'] ?? ($i + 1) }}" tabindex="0">
                        <img src="{{ asset($c['image'] ?? '') }}" alt="" class="object-cover w-full h-full"
                            loading="lazy" />

                        <div
                            class="card-info left-6 right-6 bottom-6 gap-2 rounded-3xl bg-white p-6 ease-in-out absolute hidden items-center transition-all duration-300 border border-[#EEF0F7]">
                            <div class="gap-1 flex w-[260px] flex-col">
                                <div class="flex items-center">
                                    <img src="{{ asset($iconStar) }}" alt="" class="-mt-[3px] mr-[3px] h-auto w-[18px]"
                                        aria-hidden="true" />
                                    <p class="mr-1 font-semibold leading-6 text-[16px] text-[#FF6B18]">Segera</p>
                                    <p class="leading-6 text-[16px] text-[#6B7280]">(Roadmap)</p>
                                </div>

                                <h3 class="text-lg font-semibold leading-[27px] text-[#111827]">
                                    {{ $c['title'] ?? '' }}
                                </h3>

                                <div class="gap-1.5 flex">
                                    <img src="{{ asset($iconCrown) }}" alt="" class="w-6 h-6" aria-hidden="true" />
                                    <p class="leading-6 text-[16px] text-[#6B7280]">
                                        {{ $c['subtitle'] ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <button type="button" data-coming="{{ $c['key'] ?? '' }}"
                                class="h-9 w-9 rounded-xl flex shrink-0 items-center justify-center bg-[#FF6B18] transition-all duration-300 hover:shadow-[0_10px_20px_0_#FF6B1880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B18]"
                                aria-label="{{ $c['aria'] ?? 'Minta notifikasi (demo)' }}">
                                <img src="{{ asset($iconArrow) }}" alt="" aria-hidden="true" />
                            </button>
                        </div>
                    </article>
                    @endforeach
                </div>

                <p class="mt-2 text-xs lg:hidden text-[#6B7280]">Geser untuk melihat fitur lainnya.</p>
            </div>

            {{-- Toast --}}
            <div id="toastComingSoon"
                class="bottom-6 max-w-md rounded-2xl bg-white px-4 py-3 text-sm font-semibold shadow-sm pointer-events-none fixed left-1/2 z-[999] hidden w-[92%] -translate-x-1/2 border border-[#EEF0F7] text-[#111827]"
                role="status" aria-live="polite">
                {{ $toastText }}
            </div>
        </div>
    </div>

    {{-- Data untuk JS --}}
    <script type="application/json" data-coming-soon-data="{{ $uid }}">
        {!! json_encode([
      'uid' => $uid,
      'toastText' => $toastText,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</section>
