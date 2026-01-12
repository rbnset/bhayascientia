@props([
'class' => '',
'reverseOnMobile' => false,
])

<section {{ $attributes->merge(['class' => 'mt-10 sm:mt-12 ' . $class]) }}>
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="grid items-center grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-12">
            @if ($reverseOnMobile)
            <div class="order-2 lg:order-1">
                {{ $text ?? '' }}
            </div>

            <div class="order-1 lg:order-2">
                {{ $media ?? '' }}
            </div>
            @else
            <div class="order-1">
                {{ $text ?? '' }}
            </div>

            <div class="order-2">
                {{ $media ?? '' }}
            </div>
            @endif
        </div>
    </div>
</section>
