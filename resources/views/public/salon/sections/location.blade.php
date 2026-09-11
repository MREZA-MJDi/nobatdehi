<section
    id="location"
    class="salon-section salon-location"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                LOCATION
            </span>

            <h2>
                اینجا
                <span>کجاست؟</span>
            </h2>

            <p>
                آدرس سالن را ببین و مسیر رسیدن را راحت پیدا کن.
            </p>
        </div>

    </div>


    @if($fullAddress || $hasLocation)

        <div class="salon-location-card">

            {{-- =====================================================
                INFO
            ====================================================== --}}

            <div class="salon-location-info">

                <span class="salon-location-label">
                    NOBAT / LOCATION
                </span>


                <div class="salon-location-icon" aria-hidden="true">

                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>

                </div>


                <h3>
                    {{ $salon->name }}
                </h3>


                @if($fullAddress)

                    <p class="salon-location-address">
                        {{ $fullAddress }}
                    </p>

                @else

                    <p class="salon-location-address">
                        موقعیت دقیق سالن ثبت شده است.
                    </p>

                @endif


                @if($hasLocation)

                    <div class="salon-location-coordinates">

                        <span>
                            موقعیت ثبت‌شده
                        </span>

                        <span dir="ltr">
                            {{ number_format($latitude, 5) }},
                            {{ number_format($longitude, 5) }}
                        </span>

                    </div>

                @endif


                {{-- Actions --}}

                <div class="salon-location-actions">

                    @if($hasLocation)

                        <button
                            type="button"
                            class="salon-location-primary-button"
                            @click="openDirections()"
                        >

                            <span aria-hidden="true">
                                ↗
                            </span>

                            مسیریابی

                        </button>

                    @endif


                    @if($hasLocation)

                        <a
                            href="{{ $googleMapsUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="salon-location-secondary-button"
                        >
                            باز کردن نقشه
                            <span aria-hidden="true">↗</span>
                        </a>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                MAP
            ====================================================== --}}

            @if($hasLocation)

                <div class="salon-location-map">

                    <iframe
                        src="{{ $mapsEmbedUrl }}"
                        title="موقعیت {{ $salon->name }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>


                    <div class="salon-location-map-badge">

                        <span
                            class="salon-location-map-dot"
                            aria-hidden="true"
                        ></span>

                        <span>
                            موقعیت سالن
                        </span>

                    </div>

                </div>

            @else

                <div class="salon-location-no-map">

                    <div
                        class="salon-location-no-map-icon"
                        aria-hidden="true"
                    >
                        ⌖
                    </div>

                    <strong>
                        نقشه هنوز در دسترس نیست
                    </strong>

                    <span>
                        موقعیت دقیق این سالن هنوز ثبت نشده است.
                    </span>

                </div>

            @endif

        </div>


    @else

        <div class="salon-empty-state">

            <div
                class="salon-empty-icon"
                aria-hidden="true"
            >
                ⌖
            </div>

            <div>

                <h3>
                    موقعیت سالن ثبت نشده
                </h3>

                <p>
                    این سالن هنوز آدرس یا موقعیت مکانی خود را ثبت نکرده است.
                </p>

            </div>

        </div>

    @endif

</section>
