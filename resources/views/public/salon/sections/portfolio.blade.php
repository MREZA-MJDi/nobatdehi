<section
    id="portfolio"
    class="salon-section salon-portfolio"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                PORTFOLIO
            </span>

            <h2>
                کارهایی که
                <span>حرف می‌زنند.</span>
            </h2>

            <p>
                نمونه‌کارهای واقعی این سالن را ببین و سبک کاری تیم را بشناس.
            </p>
        </div>

        @if($portfolioItems->isNotEmpty())
            <div class="salon-portfolio-count">
                <strong>
                    {{ number_format($portfolioItems->count()) }}
                </strong>

                <span>
                    نمونه‌کار
                </span>
            </div>
        @endif

    </div>


    @if($portfolioItems->isNotEmpty())

        <div class="salon-portfolio-grid">

            @foreach($portfolioItems as $index => $item)

                @php
                    $beforeUrl = $item->before_image_path
                        ? \Illuminate\Support\Facades\Storage::url(
                            $item->before_image_path
                        )
                        : null;

                    $afterUrl = $item->after_image_path
                        ? \Illuminate\Support\Facades\Storage::url(
                            $item->after_image_path
                        )
                        : null;

                    $fallbackUrl = $afterUrl ?: $beforeUrl;

                    $isFeatured = $index === 0;
                @endphp


                <article
                    class="salon-portfolio-card {{ $isFeatured ? 'is-featured' : '' }}"
                >

                    <div class="salon-portfolio-visual">

                        {{-- =================================================
                            Featured / Before After
                        ================================================== --}}

                        @if($beforeUrl && $afterUrl)

                            <div class="salon-portfolio-split">

                                <div class="salon-portfolio-half">

                                    <img
                                        src="{{ $beforeUrl }}"
                                        alt="قبل {{ $item->title }}"
                                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                        decoding="async"
                                    >

                                    <span class="salon-portfolio-badge">
                                        قبل
                                    </span>

                                </div>


                                <div class="salon-portfolio-half">

                                    <img
                                        src="{{ $afterUrl }}"
                                        alt="بعد {{ $item->title }}"
                                        loading="lazy"
                                        decoding="async"
                                    >

                                    <span class="salon-portfolio-badge is-after">
                                        بعد
                                    </span>

                                </div>


                                <div
                                    class="salon-portfolio-divider"
                                    aria-hidden="true"
                                ></div>

                            </div>

                        @elseif($fallbackUrl)

                            <img
                                src="{{ $fallbackUrl }}"
                                alt="{{ $item->title }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                decoding="async"
                            >

                        @else

                            <div
                                class="salon-portfolio-placeholder"
                                aria-hidden="true"
                            >
                                <span>
                                    {{ mb_substr(
                                        trim($item->title ?: $salon->name),
                                        0,
                                        1
                                    ) }}
                                </span>
                            </div>

                        @endif


                        {{-- =================================================
                            Overlay
                        ================================================== --}}

                        <div class="salon-portfolio-overlay">
                            <div class="salon-portfolio-overlay-line"></div>
                        </div>


                        {{-- =================================================
                            Index
                        ================================================== --}}

                        <span class="salon-portfolio-index">
                            {{ sprintf('%02d', $index + 1) }}
                        </span>

                    </div>


                    {{-- =====================================================
                        Content
                    ====================================================== --}}

                    <div class="salon-portfolio-content">

                        <div class="salon-portfolio-content-main">

                            <span class="salon-portfolio-type">
                                NOBAT / WORK
                            </span>

                            <h3>
                                {{ $item->title }}
                            </h3>


                            @if($item->barber || $item->service)

                                <div class="salon-portfolio-meta">

                                    @if($item->barber)

                                        <span>
                                            {{ $item->barber->name }}
                                        </span>

                                    @endif


                                    @if($item->barber && $item->service)

                                        <span
                                            class="salon-portfolio-meta-dot"
                                            aria-hidden="true"
                                        >
                                            •
                                        </span>

                                    @endif


                                    @if($item->service)

                                        <span>
                                            {{ $item->service->name }}
                                        </span>

                                    @endif

                                </div>

                            @endif

                        </div>


                        <span
                            class="salon-portfolio-arrow"
                            aria-hidden="true"
                        >
                            ↗
                        </span>

                    </div>

                </article>

            @endforeach

        </div>


    @else

        <div class="salon-empty-state">

            <div class="salon-empty-icon" aria-hidden="true">
                ✦
            </div>

            <div>
                <h3>
                    هنوز نمونه‌کاری ثبت نشده
                </h3>

                <p>
                    به‌زودی نمونه‌کارهای این سالن اینجا نمایش داده می‌شوند.
                </p>
            </div>

        </div>

    @endif

</section>
