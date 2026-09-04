<span
    data-slot="animated-gradient-text"
    {{ $attributes->merge([
        'class' => 'animate-gradient inline-block bg-gradient-to-r from-brand-from via-brand-via to-brand-to bg-[length:300%_auto] bg-clip-text text-transparent',
    ]) }}
>
    {{ $slot }}
</span>
