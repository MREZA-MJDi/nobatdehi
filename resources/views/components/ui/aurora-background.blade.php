@props([
'intensity' => 'medium',
])

@php
    $intensityClass = match ($intensity) {
        'subtle' => 'opacity-25 dark:opacity-20',
        'vivid' => 'opacity-60 dark:opacity-45',
        default => 'opacity-40 dark:opacity-30',
    };
@endphp

<div
    aria-hidden="true"
    {{ $attributes->merge([
        'class' => "pointer-events-none absolute inset-0 overflow-hidden {$intensityClass}",
    ]) }}
>
    <div
        class="animate-aurora-1 absolute -top-1/4 left-[10%] size-[44rem] rounded-full bg-brand-from blur-[120px] will-change-transform"
    ></div>

    <div
        class="animate-aurora-2 absolute top-[5%] right-[5%] size-[38rem] rounded-full bg-brand-via blur-[130px] will-change-transform"
    ></div>

    <div
        class="animate-aurora-3 absolute -bottom-1/4 left-[35%] size-[40rem] rounded-full bg-brand-to blur-[140px] will-change-transform"
    ></div>
</div>
