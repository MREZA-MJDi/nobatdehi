<section
    id="services"
    class="salon-section salon-services"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                SERVICES / MENU
            </span>

            <h2>
                چیزی که
                <span>می‌خوای، اینجاست.</span>
            </h2>

            <p>
                خدمات سالن را ببین، زمان و قیمت را مقایسه کن و بعد نوبتت را انتخاب کن.
            </p>
        </div>


        <div class="salon-services-count">

            <strong>
                {{ number_format($services->count()) }}
            </strong>

            <span>
                خدمت فعال
            </span>

        </div>

    </div>


    @if($services->isNotEmpty())

        <div class="salon-services-list">

            @foreach($services as $service)

                <article
                    class="salon-service-card"
                >

                    {{-- =================================================
                        NUMBER / VISUAL
                    ================================================== --}}

                    <div class="salon-service-card-index">

                        <span>
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                    </div>


                    {{-- =================================================
                        MAIN
                    ================================================== --}}

                    <div class="salon-service-card-main">

                        <div class="salon-service-card-heading">

                            <div>

                                <h3>
                                    {{ $service->name }}
                                </h3>

                                @if($service->description)

                                    <p>
                                        {{ \Illuminate\Support\Str::limit(
                                            strip_tags($service->description),
                                            150
                                        ) }}
                                    </p>

                                @endif

                            </div>


                            @if($service->duration_minutes)

                                <span class="salon-service-duration">
                                    {{ number_format($service->duration_minutes) }}
                                    دقیقه
                                </span>

                            @endif

                        </div>


                        <div class="salon-service-card-footer">

                            <div class="salon-service-price">

                                <span>
                                    قیمت
                                </span>

                                @if($service->price !== null)

                                    <strong>
                                        {{ number_format((float) $service->price) }}
                                        <small>
                                            تومان
                                        </small>
                                    </strong>

                                @else

                                    <strong class="is-variable">
                                        متغیر
                                    </strong>

                                @endif

                            </div>


                            @if($salon->is_active)

                                <button
                                    type="button"
                                    class="salon-service-book-button"
                                    @click="
                                        window.dispatchEvent(
                                            new CustomEvent(
                                                'open-booking-with-service',
                                                {
                                                    detail: {
                                                        serviceId: {{ $service->id }}
                                        }
                                    }
                                )
                            )
"
                                >
                                    انتخاب و رزرو

                                    <span aria-hidden="true">
                                        ←
                                    </span>
                                </button>

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </div>


    @else

        <div class="salon-empty-state">

            <div
                class="salon-empty-icon"
                aria-hidden="true"
            >
                ✦
            </div>

            <div>

                <h3>
                    هنوز خدمتی ثبت نشده
                </h3>

                <p>
                    این سالن هنوز خدمات فعال خود را برای رزرو آنلاین ثبت نکرده است.
                </p>

            </div>

        </div>

    @endif

</section>
