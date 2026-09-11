@props([
'eyebrow',
'title',
'description' => null,
'link' => null,
'linkText' => 'مشاهده همه',
])

<div class="discover-section-head reveal-item">

    <div class="discover-section-head-main">

        @if($eyebrow)
            <span class="discover-eyebrow">
                {{ $eyebrow }}
            </span>
        @endif

        <h2>
            {{ $title }}
        </h2>

    </div>


    @if($description)

        <p>
            {{ $description }}
        </p>

    @endif


    @if($link)

        <a
            href="{{ $link }}"
            class="discover-text-link"
        >
            {{ $linkText }}

            <span aria-hidden="true">
                ←
            </span>
        </a>

    @endif

</div>
