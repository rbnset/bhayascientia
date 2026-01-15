@props([
'variant' => 'default', // default, primary, warning, success
'size' => 'md', // sm, md, lg
])

@php
$variants = [
'default' => 'bg-gray-100 text-gray-600',
'primary' => 'bg-[#FFECE1] text-[#FF6B18]',
'warning' => 'bg-yellow-100 text-yellow-700',
'success' => 'bg-green-100 text-green-700',
];

$sizes = [
'sm' => 'px-2 py-1 text-[10px] leading-[14px]',
'md' => 'px-3 py-2 text-[10px] leading-[14px] sm:px-4 sm:text-xs sm:leading-[18px]',
'lg' => 'px-4 py-2 text-sm',
];

$classes = $variants[$variant] . ' ' . $sizes[$size];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold w-fit rounded-full {$classes}"]) }}>
    {{ $slot }}
</span>
