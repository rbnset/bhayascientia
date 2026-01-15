@props([
'title',
'subtitle' => null,
'titleSize' => 'default', // sm, default, lg
])

@php
$titleSizes = [
'sm' => 'text-base sm:text-lg',
'default' => 'text-[18px] leading-[26px] sm:text-[20px] sm:leading-[28px]',
'lg' => 'text-xl sm:text-2xl',
];
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <h2 class="font-bold text-[#111827] {{ $titleSizes[$titleSize] }}">
        {{ $title }}
    </h2>
    @if($subtitle)
    <p class="mt-1 text-[11px] leading-[16px] sm:text-sm sm:leading-[21px] text-[#A3A6AE]">
        {{ $subtitle }}
    </p>
    @endif
</div>
