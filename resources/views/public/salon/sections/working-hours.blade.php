<section
    id="working-hours"
    class="salon-section salon-hours"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                HOURS / OPEN
            </span>

            <h2>
                کی
                <span>بازیم؟</span>
            </h2>

            <p>
                ساعت فعالیت سالن را ببین تا زمان مناسب برای مراجعه را راحت‌تر پیدا کنی.
            </p>
        </div>

    </div>


    @if($workingHours->isNotEmpty())

        <div class="salon-hours-card">

            <div class="salon-hours-timeline">

                @php
                    $dayNames = [
                        0 => 'شنبه',
                        1 => 'یکشنبه',
                        2 => 'دوشنبه',
                        3 => 'سه‌شنبه',
                        4 => 'چهارشنبه',
                        5 => 'پنجشنبه',
                        6 => 'جمعه',
                    ];
                @endphp


                @foreach($workingHours as $workingHour)

                    @php
                        $isClosed = (bool) $workingHour->is_closed;

                        $startTime = $workingHour->start_time
                            ? substr((string) $workingHour->start_time, 0, 5)
                            : null;

                        $endTime = $workingHour->end_time
                            ? substr((string) $workingHour->end_time, 0, 5)
                            : null;

                        $isConfigured =
                            !$isClosed &&
                            $startTime &&
                            $endTime;
                    @endphp


                    <div
                        class="
                            salon-hours-row
                            {{ $isClosed ? 'is-closed' : '' }}
                            "
                    >

                        {{-- Day --}}
                        <div class="salon-hours-day">

                            <span class="salon-hours-day-index">
                                {{ sprintf('%02d', $loop->iteration) }}
                            </span>

                            <strong>
                                {{ $dayNames[$workingHour->day_of_week] ?? 'روز هفته' }}
                            </strong>

                        </div>


                        {{-- Time --}}
                        <div class="salon-hours-time">

                            @if($isClosed)

                                <span class="salon-hours-status is-closed">
                                    تعطیل
                                </span>

                            @elseif($isConfigured)

                                <span
                                    class="salon-hours-open-dot"
                                    aria-hidden="true"
                                ></span>

                                <strong dir="ltr">
                                    {{ $startTime }}
                                    <span aria-hidden="true">—</span>
                                    {{ $endTime }}
                                </strong>

                            @else

                                <span class="salon-hours-status">
                                    ساعت ثبت نشده
                                </span>

                            @endif

                        </div>


                        {{-- Visual marker --}}
                        <div
                            class="salon-hours-marker"
                            aria-hidden="true"
                        >
                            @if($isClosed)
                                ×
                            @elseif($isConfigured)
                                ✓
                            @else
                                •
                            @endif
                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Booking hint --}}
            @if($salon->is_active && $services->isNotEmpty())

                <div class="salon-hours-cta">

                    <div class="salon-hours-cta-copy">

                        <span class="salon-hours-cta-icon" aria-hidden="true">
                            ⏱
                        </span>

                        <div>
                            <strong>
                                دنبال زمان خالی هستی؟
                            </strong>

                            <span>
                                زمان‌های قابل رزرو برای خدمت و متخصصت را ببین.
                            </span>
                        </div>

                    </div>


                    <button
                        type="button"
                        class="salon-hours-cta-button"
                        @click="
                            window.dispatchEvent(
                                new CustomEvent('open-booking')
                            )
                        "
                    >
                        مشاهده زمان‌های آزاد

                        <span aria-hidden="true">
                            ←
                        </span>
                    </button>

                </div>

            @endif

        </div>


    @else

        <div class="salon-empty-state">

            <div
                class="salon-empty-icon"
                aria-hidden="true"
            >
                ◷
            </div>

            <div>

                <h3>
                    ساعات کاری ثبت نشده
                </h3>

                <p>
                    این سالن هنوز برنامه کاری خود را ثبت نکرده است.
                </p>

            </div>

        </div>

    @endif

</section>
