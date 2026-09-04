@props([
'name',
'description',
'href' => null,
'cta' => 'مشاهده بیشتر',
])

<div
    data-slot="bento-card"
    {{ $attributes->merge([
        'class' => 'group relative flex flex-col justify-end overflow-hidden rounded-2xl border bg-card transition-shadow duration-300 hover:shadow-xl hover:shadow-primary/5',
    ]) }}
>
    {{-- Background --}}
    @isset($background)
        <div
            class="absolute inset-0 overflow-hidden transition-transform duration-500 ease-out group-hover:scale-[1.04] motion-reduce:transition-none"
        >
            {{ $background }}
        </div>
    @endisset

    {{-- Gradient overlay --}}
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-card via-card/60 to-transparent"
    ></div>

    {{-- Content --}}
    <div
        @class([
            'pointer-events-none relative z-10 flex flex-col gap-1 p-6 transition-transform duration-300 ease-out motion-reduce:transition-none',
            'group-hover:-translate-y-7' => $href,
        ])
    >
        @isset($icon)
            <div class="mb-2 w-fit text-primary [&>svg]:size-8">
                {{ $icon }}
            </div>
        @endisset

        <h3 class="text-lg font-semibold text-card-foreground">
            {{ $name }}
        </h3>

        <p class="text-sm text-muted-foreground">
            {{ $description }}
        </p>
    </div>

    {{-- CTA --}}
    @if ($href)
        <div
            class="absolute inset-x-0 bottom-0 z-10 translate-y-full p-6 pt-0 transition-transform duration-300 ease-out group-hover:translate-y-0 motion-reduce:translate-y-0"
        >
            <a
                href="{{ $href }}"
                class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
            >
                {{ $cta }}

                <svg
                    class="size-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                </svg>
            </a>
        </div>
    @endif
</div>
