@props([
'badge' => 'Testimoni',
'title' => 'Kata penulis & pembaca',
'description' => 'Cerita singkat dari mereka yang pernah ikut menulis, review, atau membaca publikasi di BHAYACIENTIA.',
'items' => [
[
'stars' => 5,
'text' => 'Alurnya jelas. Dari daftar author sampai revisi, semuanya kebaca dan tidak bikin panik.',
'name' => 'Dita A.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo.png',
],
[
'stars' => 5,
'text' => 'Format dan struktur tulisan jadi lebih rapi. Checklistnya membantu banget buat memastikan sitasi konsisten.',
'name' => 'Raka P.',
'role' => 'Editor',
'avatar' => 'assets/images/photos/photo1.png',
],
[
'stars' => 5,
'text' => 'Reviewnya detail tapi tetap sopan. Revisi jadi terasa terarah, bukan sekadar “benerin ini-itu”.',
'name' => 'Bagas S.',
'role' => 'Reviewer',
'avatar' => 'assets/images/photos/photo2.png',
],
[
'stars' => 5,
'text' => 'Naskah jadi lebih rapi karena ada alur review yang konsisten dan tidak “loncat-loncat”.',
'name' => 'Nisa K.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo3.png',
],
[
'stars' => 5,
'text' => 'Progress naskah bisa dipantau, jadi komunikasinya lebih enak dan tidak tercecer di chat.',
'name' => 'Dita A.',
'role' => 'Author',
'avatar' => 'assets/images/photos/photo.png',
],
[
'stars' => 5,
'text' => 'Cara penyajiannya ringkas tapi padat. Cocok buat pembaca yang pengin cepat nangkep inti argumen.',
'name' => 'Sinta R.',
'role' => 'Pembaca',
'avatar' => 'assets/images/photos/photo2.png',
],
],
// agar 3 kolom desktop tidak identik
'columns' => [
[0, 3, 4],
[1, 5, 2],
[2, 1, 5],
],
])

@php
$starsText = fn ($n) => str_repeat('★', max(1, min(5, (int) $n)));
@endphp

<section id="testimoni" class="pt-12 mt-10 sm:mt-12">
    <div class="mx-auto max-w-[1130px] px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18]">
                {{ $badge }}
            </p>
            <h2 class="mt-3 text-2xl font-bold text-[#111827] sm:text-3xl">
                {{ $title }}
            </h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-[22px] text-[#6B7280] sm:text-base">
                {{ $description }}
            </p>
        </div>

        <div class="relative mt-8">
            {{-- gradient top/bottom (desktop only) --}}
            <div class="testi-fade testi-fade-top"></div>
            <div class="testi-fade testi-fade-bottom"></div>

            {{-- MOBILE: swipe horizontal --}}
            <div class="pb-2 -mx-4 overflow-x-auto overscroll-x-contain lg:hidden sm:-mx-6">
                <div class="flex gap-4 px-4 w-max sm:px-6">
                    @foreach (array_slice($items, 0, 3) as $t)
                    <article class="w-[280px] shrink-0 rounded-2xl border border-[#EEF0F7] bg-white p-5 sm:w-[340px]">
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-bold text-[#FF6B18]">{{ $starsText($t['stars'] ?? 5) }}</span>
                            <span class="sr-only">Rating {{ $t['stars'] ?? 5 }} dari 5</span>
                        </div>

                        <p class="mt-3 text-sm font-semibold leading-7 text-[#111827] sm:text-base">
                            {{ $t['text'] }}
                        </p>

                        <div class="flex items-center gap-3 mt-4">
                            <div class="h-11 w-11 overflow-hidden rounded-full border border-[#EEF0F7] bg-[#F4F6FB]">
                                <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}"
                                    class="object-cover w-full h-full">
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-[#111827]">{{ $t['name'] }}</p>
                                <p class="truncate text-xs font-semibold text-[#6B7280]">{{ $t['role'] }}</p>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>

            {{-- DESKTOP: 3 kolom auto-scroll loop (CSS only) --}}
            <div id="testiScroller" class="hidden h-[560px] grid-cols-3 gap-6 overflow-hidden lg:grid" tabindex="0">
                @foreach ($columns as $colIndex => $indices)
                @php
                $dir = $colIndex === 1 ? 'testi-down' : 'testi-up'; // kolom tengah turun
                $colItems = array_values(array_filter(array_map(fn($i) => $items[$i] ?? null, $indices)));
                @endphp

                <div class="testi-col">
                    <div class="testi-track {{ $dir }}">
                        {{-- SET A --}}
                        @foreach ($colItems as $t)
                        <article class="testi-card">
                            <div class="testi-stars">{{ $starsText($t['stars'] ?? 5) }}</div>
                            <p class="testi-text">{{ $t['text'] }}</p>
                            <div class="testi-user">
                                <div class="testi-avatar">
                                    <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}">
                                </div>
                                <div class="testi-meta">
                                    <p class="testi-name">{{ $t['name'] }}</p>
                                    <p class="testi-role">{{ $t['role'] }}</p>
                                </div>
                            </div>
                        </article>
                        @endforeach

                        {{-- SET B (duplikat otomatis) --}}
                        @foreach ($colItems as $t)
                        <article class="testi-card">
                            <div class="testi-stars">{{ $starsText($t['stars'] ?? 5) }}</div>
                            <p class="testi-text">{{ $t['text'] }}</p>
                            <div class="testi-user">
                                <div class="testi-avatar">
                                    <img src="{{ asset($t['avatar']) }}" alt="Foto {{ $t['name'] }}">
                                </div>
                                <div class="testi-meta">
                                    <p class="testi-name">{{ $t['name'] }}</p>
                                    <p class="testi-role">{{ $t['role'] }}</p>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
