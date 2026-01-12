@props([
'badge' => 'Cara kerja',
'title' => '3 langkah, naskah siap tayang.',
'description' => 'Daftar, submit, revisi. Naskah diterima? kami publikasikan otomatis di BHAYACIENTIA.',
'steps' => [
[
'title' => 'Daftar akun',
'desc' => 'Buat akun untuk mulai submit naskah jurnal, buku, atau opini.',
'icon' => 'assets/images/icons/crown.svg',
],
[
'title' => 'Submit & revisi',
'desc' => 'Kirim naskahmu. Kami review, kamu revisi sampai lebih rapi dan siap dibaca.',
'icon' => 'assets/images/icons/crown.svg',
],
[
'title' => 'Terbit otomatis',
'desc' => 'Jika diterima, naskah akan tayang otomatis di platform kami sebagai portofolio publikasi awalmu.',
'icon' => 'assets/images/icons/crown.svg',
],
],
'arrowTop' => 'assets/images/icons/arrow-top.svg',
'arrowBottom' => 'assets/images/icons/arrow-bottom.svg',
])

<section class="mt-6 sm:mt-10" data-steps-section>
    <div class="mx-auto max-w-[1130px] px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 lg:pt-12">
        <div class="text-center">
            <p class="inline-flex items-center rounded-full bg-[#FFECE1] px-4 py-2 text-xs font-bold text-[#FF6B18]">
                {{ $badge }}
            </p>

            <h2 class="mt-3 text-2xl font-bold text-[#111827] sm:text-3xl">
                {{ $title }}
            </h2>

            <p class="mx-auto mt-2 max-w-2xl text-sm leading-[21px] text-[#6B7280] sm:text-base sm:leading-[24px]">
                {{ $description }}
            </p>
        </div>

        <div class="w-full mt-6">
            {{-- Arrow Top (desktop only) --}}
            <div class="hidden lg:block">
                <img src="{{ asset($arrowTop) }}" alt="" class="mb-3 ml-10 select-none" aria-hidden="true">
            </div>

            {{-- Steps --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 lg:gap-10">
                @foreach ($steps as $step)
                <article class="group step-card rounded-2xl border border-[#EEF0F4] bg-white p-4 sm:p-5" data-step-item
                    style="--stagger: {{ $loop->index }};" tabindex="0">
                    <div class="flex items-center gap-3">
                        <div class="step-icon flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6B18]">
                            <img src="{{ asset($step['icon']) }}" alt="" class="w-5 h-5" aria-hidden="true">
                        </div>

                        <h3 class="text-[18px] font-semibold leading-[26px] text-[#111827]">
                            {{ $step['title'] }}
                        </h3>
                    </div>

                    <p class="mt-3 max-w-[52ch] text-[14px] font-medium leading-6 text-[#6B7280] sm:text-[15px]">
                        {{ $step['desc'] }}
                    </p>

                </article>
                @endforeach
            </div>

            {{-- Arrow Bottom (desktop only) --}}
            <div class="hidden lg:block">
                <img src="{{ asset($arrowBottom) }}" alt="" class="mt-3 ml-[560px] select-none" aria-hidden="true">
            </div>
        </div>
    </div>
</section>
