<section
    id="reviews"
    class="salon-section salon-reviews"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                SOCIAL PROOF
            </span>

            <h2>
                مشتری‌ها
                <span>چی می‌گن؟</span>
            </h2>

            <p>
                تجربه واقعی مشتری‌هایی که این سالن را انتخاب کرده‌اند.
            </p>
        </div>


        @if($reviews->isNotEmpty())

            <div class="salon-reviews-summary">

                @php
                    $reviewRatings = $reviews
                        ->map(function ($review) {
                            return data_get($review, 'rating')
                                ?? data_get($review, 'score');
                        })
                        ->filter(fn ($rating) => is_numeric($rating));

                    $averageRating = $reviewRatings->isNotEmpty()
                        ? round($reviewRatings->avg(), 1)
                        : null;
                @endphp


                @if($averageRating)

                    <div class="salon-reviews-score">

                        <strong>
                            {{ number_format($averageRating, 1) }}
                        </strong>

                        <span aria-hidden="true">
                            ★
                        </span>

                    </div>

                    <div class="salon-reviews-score-copy">

                        <div class="salon-reviews-stars" aria-label="امتیاز {{ $averageRating }} از ۵">
                            ★★★★★
                        </div>

                        <span>
                            بر اساس
                            {{ number_format($reviews->count()) }}
                            نظر
                        </span>

                    </div>

                @else

                    <div class="salon-reviews-score-copy">

                        <strong>
                            {{ number_format($reviews->count()) }}
                        </strong>

                        <span>
                            نظر ثبت شده
                        </span>

                    </div>

                @endif

            </div>

        @endif

    </div>


    @if($reviews->isNotEmpty())

        <div class="salon-reviews-grid">

            @foreach($reviews as $review)

                @php
                    $reviewName =
                        data_get($review, 'customer.name')
                        ?: data_get($review, 'user.name')
                        ?: 'مشتری NOBAT';

                    $reviewText =
                        data_get($review, 'comment')
                        ?: data_get($review, 'body')
                        ?: data_get($review, 'content')
                        ?: data_get($review, 'text');

                    $reviewRating =
                        data_get($review, 'rating')
                        ?? data_get($review, 'score');

                    $reviewService =
                        data_get($review, 'booking.service.name');

                    $reviewInitial = mb_substr(
                        trim($reviewName),
                        0,
                        1
                    );
                @endphp


                @if($reviewText)

                    <article
                        class="
                            salon-review-card
                            {{ $loop->first ? 'is-featured' : '' }}
                            "
                    >

                        {{-- =================================================
                            FEATURE MARK
                        ================================================== --}}

                        @if($loop->first)

                            <div class="salon-review-featured-mark">
                                تجربه مشتری
                            </div>

                        @endif


                        {{-- =================================================
                            TOP
                        ================================================== --}}

                        <div class="salon-review-top">

                            <div class="salon-review-author">

                                <div
                                    class="salon-review-avatar"
                                    aria-hidden="true"
                                >
                                    {{ $reviewInitial }}
                                </div>


                                <div class="salon-review-author-copy">

                                    <strong>
                                        {{ $reviewName }}
                                    </strong>

                                    <span>
                                        مشتری سالن
                                    </span>

                                </div>

                            </div>


                            @if(is_numeric($reviewRating))

                                <div class="salon-review-rating">

                                    <span aria-hidden="true">
                                        ★
                                    </span>

                                    <strong>
                                        {{ number_format((float) $reviewRating, 1) }}
                                    </strong>

                                </div>

                            @endif

                        </div>


                        {{-- =================================================
                            QUOTE
                        ================================================== --}}

                        <div class="salon-review-quote-mark" aria-hidden="true">
                            “
                        </div>

                        <blockquote class="salon-review-text">
                            {{ $reviewText }}
                        </blockquote>


                        {{-- =================================================
                            SERVICE CONTEXT
                        ================================================== --}}

                        @if($reviewService)

                            <div class="salon-review-service">

                                <span>
                                    خدمت
                                </span>

                                <strong>
                                    {{ $reviewService }}
                                </strong>

                            </div>

                        @endif


                        {{-- =================================================
                            FOOTER
                        ================================================== --}}

                        <div class="salon-review-footer">

                            <span>
                                تجربه ثبت‌شده در NOBAT
                            </span>

                            <span aria-hidden="true">
                                ↗
                            </span>

                        </div>

                    </article>

                @endif

            @endforeach

        </div>


    @else

        <div class="salon-empty-state salon-reviews-empty">

            <div
                class="salon-empty-icon"
                aria-hidden="true"
            >
                ★
            </div>

            <div>

                <h3>
                    هنوز نظری ثبت نشده
                </h3>

                <p>
                    با ثبت اولین تجربه مشتری، این بخش جان می‌گیرد.
                </p>

            </div>

        </div>

    @endif

</section>
