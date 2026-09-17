<section
    class="discover-section"
    id="stylists"
>

    <div class="discover-container">

        <div class="discover-section-heading">

            <span class="discover-kicker">
                متخصص‌ها
            </span>

            <h2>
                متخصص مناسب خودت را پیدا کن
            </h2>

            <p>
                تخصص، تجربه و سالن هر متخصص را قبل از رزرو ببین.
            </p>

        </div>


        <div class="discover-stylist-grid">

            @forelse(
                $stylists as $barber
            )

                <a
                    href="{{ route(
                        'public.salons.show',
                        $barber->salon
                    ) }}"
                    class="discover-stylist-card"
                >

                    <div class="discover-stylist-media">

                        @if($barber->image_path)

                            <img
                                src="{{ $resolveImage(
                                    $barber->image_path
                                ) }}"
                                alt="{{ $barber->name }}"
                                loading="lazy"
                            >

                        @else

                            <div class="discover-stylist-placeholder">
                                {{ mb_substr(
                                    trim($barber->name),
                                    0,
                                    1
                                ) }}
                            </div>

                        @endif

                    </div>


                    <div class="discover-stylist-body">

                        <span>
                            متخصص
                        </span>

                        <h3>
                            {{ $barber->name }}
                        </h3>

                        <p>
                            {{ $barber->specialty
                                ?: 'متخصص زیبایی'
                            }}
                        </p>

                        <small>
                            {{ $barber->salon?->name }}
                        </small>


                        <div class="discover-stylist-footer">
                            مشاهده سالن
                            ←
                        </div>

                    </div>

                </a>

            @empty

                <div class="discover-empty-inline">
                    هنوز متخصصی برای نمایش وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
