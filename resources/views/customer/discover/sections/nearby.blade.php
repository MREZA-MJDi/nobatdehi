<section
    class="discovery-section"
    id="nearby"
>

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    LOCATION
                </span>

                <h2 class="discovery-section-title">
                    سالن‌ها در اطراف تو
                </h2>

                <p class="discovery-section-description">
                    سالن‌های نزدیک را سریع‌تر پیدا کن.
                </p>

            </div>

            <a
                href="{{ route('salons.discover') }}"
                class="discovery-section-link"
            >
                تغییر موقعیت
                <span>←</span>
            </a>

        </div>


        <div class="discovery-nearby-layout">

            <div class="discovery-nearby-list">

                @forelse(
                    $salons->take(5)
                    as $salon
                )

                    <a
                        href="{{ route(
                            'public.salons.show',
                            $salon
                        ) }}"
                        class="discovery-nearby-item"
                    >

                        <div class="discovery-nearby-item-image">

                            @if($salon->cover_url ?? null)

                                <img
                                    src="{{ $salon->cover_url }}"
                                    alt="{{ $salon->name }}"
                                    loading="lazy"
                                >

                            @elseif($salon->cover_path)

                                <img
                                    src="{{ $resolveImage(
                                        $salon->cover_path
                                    ) }}"
                                    alt="{{ $salon->name }}"
                                    loading="lazy"
                                >

                            @endif

                        </div>


                        <div class="discovery-nearby-item-content">

                            <div class="discovery-nearby-item-name">
                                {{ $salon->name }}
                            </div>

                            <div class="discovery-nearby-item-meta">

                                ★

                                {{
                                    isset($salon->rating)
                                        ? number_format(
                                            (float) $salon->rating,
                                            1
                                        )
                                        : '—'
                                }}

                                ·

                                {{
                                    collect([
                                        $salon->district,
                                        $salon->city,
                                    ])
                                    ->filter()
                                    ->implode('، ')
                                    ?: 'ایران'
                                }}

                            </div>

                        </div>


                        <span class="discovery-nearby-arrow">
                            ←
                        </span>

                    </a>

                @empty

                    <div class="discovery-empty">
                        هنوز سالنی برای نمایش وجود ندارد.
                    </div>

                @endforelse

            </div>


            {{-- VISUAL MAP --}}
            <div class="discovery-map">

                <div class="discovery-map-grid"></div>

                <div class="discovery-map-overlay"></div>

                <span class="discovery-map-pin"></span>

                <span class="discovery-map-pin"></span>

                <span class="discovery-map-pin"></span>

                <span class="discovery-map-pin"></span>

                <span class="discovery-map-center"></span>

            </div>

        </div>

    </div>

</section>
