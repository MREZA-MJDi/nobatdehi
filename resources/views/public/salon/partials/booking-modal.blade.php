<div
    x-data="salonBookingModal({
        salonId: @js($salon->id),
        salonName: @js($salon->name),

        availabilityUrl: @js(
            route('public.salons.booking.availability', $salon)
        ),

        prepareUrl: @js(
            route('public.salons.booking.prepare', $salon)
        ),

        services: @js(
            $services
                ->where('is_active', true)
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])
                ->values()
                ->map(fn ($service) => [
                    'id' => (int) $service->id,
                    'name' => $service->name,
                    'price' => $service->price !== null
                        ? (float) $service->price
                        : null,
                    'duration' => $service->duration_minutes
                        ? (int) $service->duration_minutes
                        : null,
                ])
        ),

        barbers: @js(
            $barbers
                ->where('is_active', true)
                ->sortBy('name')
                ->values()
                ->map(fn ($barber) => [
                    'id' => (int) $barber->id,
                    'name' => $barber->name,
                    'specialty' => $barber->specialty,
                    'image' => $barber->image_path
                        ? \Illuminate\Support\Facades\Storage::url(
                            $barber->image_path
                        )
                        : null,
                ])
        ),
    })"

    x-on:open-booking.window="open()"

    x-on:open-booking-with-service.window="
        open($event.detail?.serviceId ?? null)
    "

    x-on:keydown.escape.window="
        if (openState) close()
    "

    x-cloak
>
    {{-- ============================================================
        OVERLAY
    ============================================================= --}}

    <div
        x-show="openState"
        x-transition.opacity
        class="salon-booking-overlay"
        @click.self="close()"
        aria-hidden="true"
    ></div>


    {{-- ============================================================
        MODAL
    ============================================================= --}}

    <section
        x-show="openState"

        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-[.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"

        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-[.98]"

        class="salon-booking-modal"

        role="dialog"
        aria-modal="true"
        aria-labelledby="salon-booking-title"

        tabindex="-1"
    >

        {{-- ========================================================
            HEADER
        ========================================================= --}}

        <header class="salon-booking-modal-header">

            <div class="salon-booking-modal-title">

                <span class="salon-booking-modal-kicker">
                    NOBAT / BOOKING
                </span>

                <h2 id="salon-booking-title">
                    رزرو نوبت
                </h2>

                <p>
                    {{ $salon->name }}
                </p>

            </div>


            <button
                type="button"
                class="salon-booking-modal-close"
                @click="close()"
                aria-label="بستن پنجره رزرو"
            >
                <span aria-hidden="true">×</span>
            </button>

        </header>


        {{-- ========================================================
            PROGRESS
        ========================================================= --}}

        <div
            class="salon-booking-progress"
            aria-label="مراحل رزرو"
        >

            <template
                x-for="item in progressSteps"
                :key="item.id"
            >

                <div
                    class="salon-booking-progress-item"

                    :class="{
                        'is-active': step === item.id,
                        'is-complete': step > item.id
                    }"
                >

                    <span
                        class="salon-booking-progress-number"

                        x-text="
                            step > item.id
                                ? '✓'
                                : item.number
                        "
                    ></span>

                    <span
                        class="salon-booking-progress-label"
                        x-text="item.label"
                    ></span>

                </div>

            </template>

        </div>


        {{-- ========================================================
            BODY
        ========================================================= --}}

        <div class="salon-booking-modal-body">


            {{-- ====================================================
                STEP 1 — SERVICE
            ===================================================== --}}

            <section
                x-show="step === 1"
                x-transition.opacity
                class="salon-booking-step"
            >

                <div class="salon-booking-step-heading">

                    <span class="salon-booking-step-number">
                        01
                    </span>

                    <div>

                        <h3>
                            چه خدمتی می‌خوای؟
                        </h3>

                        <p>
                            اول خدمت موردنظرت را انتخاب کن.
                        </p>

                    </div>

                </div>


                @if($services->where('is_active', true)->isNotEmpty())

                    <div class="salon-booking-service-options">

                        @foreach(
                            $services
                                ->where('is_active', true)
                                ->sortBy([
                                    ['sort_order', 'asc'],
                                    ['name', 'asc'],
                                ])
                            as $service
                        )

                            <button
                                type="button"
                                class="salon-booking-service-option"

                                :class="{
                                    'is-selected':
                                        Number(selectedServiceId) ===
                                        {{ (int) $service->id }}
                                    }"

                                @click="
                                    selectService(
                                        {{ (int) $service->id }}
                                    )
                                "
                            >

                                <div
                                    class="salon-booking-service-option-left"
                                >

                                    <span
                                        class="salon-booking-service-option-icon"
                                        aria-hidden="true"
                                    >
                                        ✦
                                    </span>

                                    <span>

                                        <strong>
                                            {{ $service->name }}
                                        </strong>

                                        <small>

                                            @if($service->duration_minutes)
                                                {{ number_format((int) $service->duration_minutes) }}
                                                دقیقه
                                            @else
                                                زمان متغیر
                                            @endif

                                        </small>

                                    </span>

                                </div>


                                <div
                                    class="salon-booking-service-option-right"
                                >

                                    @if($service->price !== null)

                                        <strong>
                                            {{ number_format((float) $service->price) }}
                                        </strong>

                                        <small>
                                            تومان
                                        </small>

                                    @else

                                        <strong>
                                            —
                                        </strong>

                                    @endif


                                    <span
                                        class="salon-booking-check"
                                        aria-hidden="true"
                                    >
                                        ✓
                                    </span>

                                </div>

                            </button>

                        @endforeach

                    </div>

                @else

                    <div class="salon-booking-empty">

                        <span
                            class="salon-booking-empty-icon"
                            aria-hidden="true"
                        >
                            ✦
                        </span>

                        <strong>
                            فعلاً خدمتی برای رزرو وجود ندارد.
                        </strong>

                        <span>
                            لطفاً بعداً دوباره سر بزن.
                        </span>

                    </div>

                @endif

            </section>


            {{-- ====================================================
                STEP 2 — BARBER
            ===================================================== --}}

            <section
                x-show="step === 2"
                x-transition.opacity
                class="salon-booking-step"
            >

                <div class="salon-booking-step-heading">

                    <span class="salon-booking-step-number">
                        02
                    </span>

                    <div>

                        <h3>
                            متخصصت رو انتخاب کن
                        </h3>

                        <p>
                            متخصصی که می‌خواهی نوبتت را با او بگیری انتخاب کن.
                        </p>

                    </div>

                </div>


                @if($barbers->where('is_active', true)->isNotEmpty())

                    <div class="salon-booking-barber-grid">

                        @foreach(
                            $barbers
                                ->where('is_active', true)
                                ->sortBy('name')
                            as $barber
                        )

                            <button
                                type="button"
                                class="salon-booking-barber-option"

                                :class="{
                                    'is-selected':
                                        Number(selectedBarberId) ===
                                        {{ (int) $barber->id }}
                                    }"

                                @click="
                                    selectBarber(
                                        {{ (int) $barber->id }}
                                    )
                                "
                            >

                                <span
                                    class="salon-booking-barber-avatar"
                                >

                                    @if($barber->image_path)

                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($barber->image_path) }}"
                                            alt="{{ $barber->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <span>
                                            {{ mb_substr(
                                                trim($barber->name),
                                                0,
                                                1
                                            ) }}
                                        </span>

                                    @endif

                                </span>


                                <span
                                    class="salon-booking-barber-info"
                                >

                                    <strong>
                                        {{ $barber->name }}
                                    </strong>

                                    <small>
                                        {{ $barber->specialty ?: 'متخصص سالن' }}
                                    </small>

                                </span>


                                <span
                                    class="salon-booking-check"
                                    aria-hidden="true"
                                >
                                    ✓
                                </span>

                            </button>

                        @endforeach

                    </div>

                @else

                    <div class="salon-booking-empty">

                        <span
                            class="salon-booking-empty-icon"
                            aria-hidden="true"
                        >
                            ◉
                        </span>

                        <strong>
                            فعلاً متخصص فعالی برای رزرو وجود ندارد.
                        </strong>

                        <span>
                            لطفاً بعداً دوباره امتحان کن.
                        </span>

                    </div>

                @endif

            </section>


            {{-- ====================================================
                STEP 3 — JALALI CALENDAR
            ===================================================== --}}

            <section
                x-show="step === 3"
                x-transition.opacity
                class="salon-booking-step"
            >

                <div class="salon-booking-step-heading">

                    <span class="salon-booking-step-number">
                        03
                    </span>

                    <div>

                        <h3>
                            چه روزی میای؟
                        </h3>

                        <p>
                            تاریخ موردنظرت را از تقویم شمسی انتخاب کن.
                        </p>

                    </div>

                </div>


                <div class="salon-jalali-calendar">

                    {{-- Calendar header --}}
                    <div class="salon-jalali-calendar-header">

                        <button
                            type="button"
                            class="salon-jalali-month-button"

                            @click="previousJalaliMonth()"

                            aria-label="ماه قبل"
                        >
                            <span aria-hidden="true">
                                →
                            </span>
                        </button>


                        <div class="salon-jalali-month-title">

                            <strong
                                x-text="jalaliMonthTitle"
                            ></strong>

                            <span>
                                تقویم شمسی
                            </span>

                        </div>


                        <button
                            type="button"
                            class="salon-jalali-month-button"

                            @click="nextJalaliMonth()"

                            aria-label="ماه بعد"
                        >
                            <span aria-hidden="true">
                                ←
                            </span>
                        </button>

                    </div>


                    {{-- Weekdays --}}
                    <div
                        class="salon-jalali-weekdays"
                        aria-hidden="true"
                    >

                        <span>ش</span>
                        <span>ی</span>
                        <span>د</span>
                        <span>س</span>
                        <span>چ</span>
                        <span>پ</span>
                        <span>ج</span>

                    </div>


                    {{-- Days --}}
                    <div class="salon-jalali-days">

                        <template
                            x-for="day in calendarDays"
                            :key="day.key"
                        >

                            <button
                                type="button"

                                class="salon-jalali-day"

                                :class="{
                                    'is-outside':
                                        !day.currentMonth,

                                    'is-today':
                                        day.isToday,

                                    'is-selected':
                                        day.value === selectedDate,

                                    'is-disabled':
                                        day.disabled
                                }"

                                :disabled="day.disabled"

                                @click="selectCalendarDate(day)"

                                :aria-pressed="
                                    day.value === selectedDate
                                "

                                :aria-label="
                                    `${day.day} ${jalaliMonthTitle}`
                                "
                            >

                                <span
                                    x-text="day.day"
                                ></span>

                                <small
                                    x-show="day.isToday"
                                >
                                    امروز
                                </small>

                            </button>

                        </template>

                    </div>

                </div>


                {{-- Selected date --}}
                <div
                    x-show="selectedDate"
                    x-transition
                    class="salon-booking-selected-date"
                >

                    <span>
                        تاریخ انتخاب‌شده
                    </span>

                    <strong
                        x-text="formattedSelectedDate"
                    ></strong>

                </div>

            </section>


            {{-- ====================================================
                STEP 4 — TIME
            ===================================================== --}}

            <section
                x-show="step === 4"
                x-transition.opacity
                class="salon-booking-step"
            >

                <div class="salon-booking-step-heading">

                    <span class="salon-booking-step-number">
                        04
                    </span>

                    <div>

                        <h3>
                            ساعتت رو انتخاب کن
                        </h3>

                        <p>
                            زمان‌های آزاد این متخصص برای تاریخ انتخابی را ببین.
                        </p>

                    </div>

                </div>


                {{-- Context --}}
                <div class="salon-booking-time-context">

                    <div>

                        <span>
                            تاریخ
                        </span>

                        <strong
                            x-text="formattedSelectedDate"
                        ></strong>

                    </div>


                    <div>

                        <span>
                            متخصص
                        </span>

                        <strong
                            x-text="selectedBarber?.name ?? '—'"
                        ></strong>

                    </div>

                </div>


                {{-- Loading --}}
                <div
                    x-show="loadingSlots"
                    x-transition.opacity
                    class="salon-booking-loading"
                >

                    <div
                        class="salon-booking-spinner"
                        aria-hidden="true"
                    ></div>

                    <span>
                        در حال بررسی ساعت‌های آزاد...
                    </span>

                </div>


                {{-- Availability error --}}
                <div
                    x-show="availabilityError"
                    x-transition.opacity
                    class="salon-booking-error"
                    role="alert"
                >
                    <span
                        x-text="availabilityError"
                    ></span>
                </div>


                {{-- Empty --}}
                <div
                    x-show="
                        !loadingSlots &&
                        !availabilityError &&
                        slots.length === 0
                    "
                    x-transition.opacity
                    class="salon-booking-empty"
                >

                    <span
                        class="salon-booking-empty-icon"
                        aria-hidden="true"
                    >
                        ◷
                    </span>

                    <strong>
                        برای این روز زمانی پیدا نشد.
                    </strong>

                    <span>
                        یک تاریخ دیگر را امتحان کن.
                    </span>

                </div>


                {{-- Slots --}}
                <div
                    x-show="
                        !loadingSlots &&
                        !availabilityError &&
                        slots.length > 0
                    "
                    class="salon-booking-time-picker"
                >

                    {{-- Available --}}
                    <div
                        x-show="availableSlots.length > 0"
                        class="salon-booking-time-section"
                    >

                        <span
                            class="salon-booking-time-section-label"
                        >
                            ساعت‌های قابل رزرو
                        </span>


                        <div
                            class="salon-booking-slot-grid"
                        >

                            <template
                                x-for="slot in availableSlots"
                                :key="`available-${slot.start}`"
                            >

                                <button
                                    type="button"

                                    class="salon-booking-time-slot"

                                    :class="{
                                        'is-selected':
                                            selectedTime === slot.start
                                    }"

                                    @click="selectTime(slot)"
                                >

                                    <span
                                        dir="ltr"
                                        x-text="slot.start"
                                    ></span>

                                    <small>
                                        آزاد
                                    </small>

                                </button>

                            </template>

                        </div>

                    </div>


                    {{-- Booked --}}
                    <div
                        x-show="bookedSlots.length > 0"
                        class="salon-booking-time-section"
                    >

                        <span
                            class="salon-booking-time-section-label is-muted"
                        >
                            زمان‌های رزرو شده
                        </span>


                        <div
                            class="salon-booking-slot-grid"
                        >

                            <template
                                x-for="slot in bookedSlots"
                                :key="`booked-${slot.start}`"
                            >

                                <button
                                    type="button"

                                    disabled

                                    class="
                                        salon-booking-time-slot
                                        is-booked
                                    "
                                >

                                    <span
                                        dir="ltr"
                                        x-text="slot.start"
                                    ></span>

                                    <small>
                                        رزرو شده
                                    </small>

                                </button>

                            </template>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ====================================================
                STEP 5 — SUMMARY
            ===================================================== --}}

            <section
                x-show="step === 5"
                x-transition.opacity
                class="salon-booking-step"
            >

                <div class="salon-booking-step-heading">

                    <span class="salon-booking-step-number">
                        05
                    </span>

                    <div>

                        <h3>
                            نوبتت آماده‌ست.
                        </h3>

                        <p>
                            جزئیات را یک بار بررسی کن.
                        </p>

                    </div>

                </div>


                <div class="salon-booking-summary">

                    <div class="salon-booking-summary-hero">

                        <span>
                            NOBAT / RESERVATION
                        </span>

                        <strong>
                            {{ $salon->name }}
                        </strong>

                    </div>


                    <div
                        class="salon-booking-summary-row"
                    >

                        <span>
                            خدمت
                        </span>

                        <strong
                            x-text="
                                selectedService?.name ?? '—'
                            "
                        ></strong>

                    </div>


                    <div
                        class="salon-booking-summary-row"
                    >

                        <span>
                            متخصص
                        </span>

                        <strong
                            x-text="
                                selectedBarber?.name ?? '—'
                            "
                        ></strong>

                    </div>


                    <div
                        class="salon-booking-summary-row"
                    >

                        <span>
                            تاریخ
                        </span>

                        <strong
                            x-text="formattedSelectedDate"
                        ></strong>

                    </div>


                    <div
                        class="salon-booking-summary-row"
                    >

                        <span>
                            ساعت
                        </span>

                        <strong
                            dir="ltr"
                            x-text="
                                selectedTime ?? '—'
                            "
                        ></strong>

                    </div>


                    <div
                        class="salon-booking-summary-price"
                    >

                        <span>
                            مبلغ خدمت
                        </span>

                        <strong>

                            <span
                                x-text="formattedPrice"
                            ></span>

                            <span>
                                تومان
                            </span>

                        </strong>

                    </div>

                </div>


                {{-- Notes --}}
                <div class="salon-booking-notes">

                    <label for="booking-notes">

                        توضیحات

                        <span>
                            اختیاری
                        </span>

                    </label>


                    <textarea
                        id="booking-notes"

                        x-model="notes"

                        maxlength="2000"

                        rows="3"

                        placeholder="مثلاً درخواست خاص یا توضیحی برای سالن..."
                    ></textarea>


                    <div class="salon-booking-notes-counter">

                        <span>
                            <span x-text="notes.length"></span>
                            / 2000
                        </span>

                    </div>

                </div>


                {{-- Final note --}}
                <div class="salon-booking-final-note">

                    <span aria-hidden="true">
                        ✓
                    </span>

                    <p>
                        قبل از ثبت درخواست، موجود بودن این ساعت دوباره بررسی می‌شود.
                    </p>

                </div>

            </section>


            {{-- ====================================================
                GLOBAL ERROR
            ===================================================== --}}

            <div
                x-show="formError"
                x-transition.opacity
                class="salon-booking-error"
                role="alert"
            >

                <span
                    x-text="formError"
                ></span>

            </div>

        </div>


        {{-- ========================================================
            FOOTER
        ========================================================= --}}

        <footer class="salon-booking-modal-footer">

            {{-- Back --}}
            <button
                type="button"

                class="salon-booking-back-button"

                x-show="step > 1"
                x-transition.opacity

                @click="previousStep()"
            >

                <span aria-hidden="true">
                    →
                </span>

                برگشت

            </button>


            <div
                class="salon-booking-footer-spacer"
                aria-hidden="true"
            ></div>


            {{-- Close --}}
            <button
                type="button"

                class="salon-booking-cancel-button"

                @click="close()"
            >
                بستن
            </button>


            {{-- Next / Submit --}}
            <button
                type="button"

                class="salon-booking-next-button"

                :disabled="
                    !canContinue ||
                    loadingSlots ||
                    submitting
                "

                @click="nextStep()"
            >

                <span
                    x-show="!submitting"
                    x-text="
                        step === 5
                            ? 'ثبت درخواست نوبت'
                            : 'ادامه'
                    "
                ></span>


                <span x-show="submitting">
                    در حال ارسال...
                </span>


                <span
                    aria-hidden="true"
                    x-show="!submitting"
                >
                    ←
                </span>

            </button>

        </footer>

    </section>


    {{-- ============================================================
        PREPARE FORM
    ============================================================= --}}

    <form
        x-ref="prepareForm"

        :action="prepareUrl"

        method="POST"

        class="hidden"
    >

        @csrf

        <input
            type="hidden"
            name="salon_id"
            :value="salonId"
        >

        <input
            type="hidden"
            name="barber_id"
            :value="selectedBarberId"
        >

        <input
            type="hidden"
            name="service_id"
            :value="selectedServiceId"
        >

        <input
            type="hidden"
            name="booking_date"
            :value="selectedDate"
        >

        <input
            type="hidden"
            name="start_time"
            :value="selectedTime"
        >

        <input
            type="hidden"
            name="notes"
            :value="notes"
        >

    </form>

</div>
