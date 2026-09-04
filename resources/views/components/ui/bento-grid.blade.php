<div
    data-slot="bento-grid"
    {{ $attributes->merge([
        'class' => 'grid w-full auto-rows-[22rem] grid-cols-1 gap-4 md:grid-cols-3',
    ]) }}
>
    {{ $slot }}
</div>
