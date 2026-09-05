@extends('layouts.customer')

@section('title', 'رزرو نوبت ' . $salon->name)

@section('content')

    @php
        $barberPayload = $barbers
            ->map(fn ($barber) => [
                'id' => $barber->id,
                'name' => $barber->name,
                'specialty' => $barber->specialty,
                'image' => $barber->image_path
                    ? \Illuminate\Support\Facades\Storage::url($barber->image_path)
                    : null,
            ])
            ->values();

        $servicePayload = $services
            ->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (int) $service->price,
                'duration' => (int) $service->duration_minutes,
            ])
            ->values();
    @endphp

    <div
        class="customer-container booking-page"
        x-data="bookingPage()"
        x-init="init()"
    >

        {{-- Header --}}
        <div class="booking-page-header">

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="back-link"
            >
                → بازگشت به سالن
            </a>

            <span class="section-kicker">
                BOOK APPOINTMENT
            </span>

            <h1>
                رزرو نوبت
            </h1>

            <p>
                متخصص، خدمت، تاریخ و ساعت مناسب خودت را انتخاب کن.
            </p>

        </div>


        <form
            action="{{ route('public.salons.booking.prepare', $salon) }}"
            method="POST"
            class="booking-layout"
            @submit="handleSubmit($event)"
        >

            @csrf

            <input
                type="hidden"
                name="salon_id"
                value="{{ $salon->id }}"
            >

            <input
                type="hidden"
                name="barber_id"
                x-model="barberId"
            >

            <input
                type="hidden"
                name="service_id"
                x-model="serviceId"
            >

            <input
                type="hidden"
                name="booking_date"
                x-model="date"
            >

            <input
                type="hidden"
                name="start_time"
                x-model="time"
            >


            <div class="booking-main">

                {{-- ============================================= --}}
                {{-- 01 BARBER --}}
                {{-- ============================================= --}}

                <section class="booking-card">

                    <div class="booking-card-heading">

                        <span>01</span>

                        <div>
                            <strong>متخصص</strong>

                            <small>
                                چه کسی قرار است خدماتت را انجام دهد؟
                            </small>
                        </div>

                    </div>


                    @if($barbers->count())

                        <div class="booking-choice-grid">

                            @foreach($barbers as $barber)

                                <button
                                    type="button"
                                    class="booking-choice"
                                    @click="selectBarber('{{ $barber->id }}')"
                                    :class="String(barberId) === '{{ $barber->id }}' ? 'is-selected' : ''"
                                >

                                    <span class="booking-choice-avatar">

                                        @if($barber->image_path)

                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::url($barber->image_path) }}"
                                                alt="{{ $barber->name }}"
                                                loading="lazy"
                                            >

                                        @else

                                            {{ mb_substr($barber->name, 0, 1) }}

                                        @endif

                                    </span>


                                    <span>

                                        <strong>
                                            {{ $barber->name }}
                                        </strong>

                                        <small>
                                            {{ $barber->specialty ?: 'متخصص زیبایی' }}
                                        </small>

                                    </span>

                                </button>

                            @endforeach

                        </div>

                    @else

                        <div class="booking-state booking-state-danger">
                            این سالن هنوز متخصص فعالی ثبت نکرده است.
                        </div>

                    @endif

                </section>


                {{-- ============================================= --}}
                {{-- 02 SERVICE --}}
                {{-- ============================================= --}}

                <section class="booking-card">

                    <div class="booking-card-heading">

                        <span>02</span>

                        <div>
                            <strong>خدمت</strong>

                            <small>
                                چیزی که قرار است رزرو کنی
                            </small>
                        </div>

                    </div>


                    @if($services->count())

                        <div class="booking-service-list">

                            @foreach($services as $service)

                                <button
                                    type="button"
                                    class="booking-service"
                                    @click="selectService('{{ $service->id }}')"
                                    :class="String(serviceId) === '{{ $service->id }}' ? 'is-selected' : ''"
                                >

                                    <span>

                                        <strong>
                                            {{ $service->name }}
                                        </strong>

                                        <small>
                                            {{ $service->duration_minutes }} دقیقه
                                        </small>

                                    </span>


                                    <strong>

                                        {{ number_format($service->price) }}

                                        <small>
                                            تومان
                                        </small>

                                    </strong>

                                </button>

                            @endforeach

                        </div>

                    @else

                        <div class="booking-state booking-state-danger">
                            این سالن هنوز خدمتی ثبت نکرده است.
                        </div>

                    @endif

                </section>


                {{-- ============================================= --}}
                {{-- 03 DATE --}}
                {{-- ============================================= --}}

                <section class="booking-card booking-date-card">

                    <div class="booking-card-heading">

                        <span>03</span>

                        <div>
                            <strong>تاریخ</strong>

                            <small>
                                روز موردنظرت را انتخاب کن
                            </small>
                        </div>

                    </div>


                    {{-- Selected date --}}
                    <div class="booking-selected-date">

                        <div>

                            <span>
                                تاریخ انتخابی
                            </span>

                            <strong x-text="selectedDateLabel">
                                —
                            </strong>

                        </div>

                        <button
                            type="button"
                            @click="goToday()"
                        >
                            امروز
                        </button>

                    </div>


                    {{-- Calendar --}}
                    <div class="booking-calendar">

                        <div class="booking-calendar-header">

                            <button
                                type="button"
                                class="booking-calendar-nav"
                                @click="previousWeek()"
                                aria-label="هفته قبل"
                            >
                                →
                            </button>


                            <div class="booking-calendar-month">

                                <strong x-text="monthLabel">
                                    —
                                </strong>

                                <small>
                                    انتخاب روز
                                </small>

                            </div>


                            <button
                                type="button"
                                class="booking-calendar-nav"
                                @click="nextWeek()"
                                aria-label="هفته بعد"
                            >
                                ←
                            </button>

                        </div>


                        <div
                            class="booking-calendar-days"
                            @touchstart="touchStart($event)"
                            @touchend="touchEnd($event)"
                        >

                            <template
                                x-for="day in calendarDays"
                                :key="day.gregorian"
                            >

                                <button
                                    type="button"
                                    class="booking-calendar-day"
                                    :class="{
                                        'is-selected':
                                            day.gregorian === date,

                                        'is-today':
                                            day.isToday,

                                        'is-disabled':
                                            day.disabled
                                    }"
                                    :disabled="day.disabled"
                                    @click="selectDate(day.gregorian)"
                                >

                                    <span
                                        class="booking-calendar-weekday"
                                        x-text="day.weekday"
                                    ></span>

                                    <strong
                                        x-text="day.jalaliDay"
                                    ></strong>

                                    <small
                                        x-text="day.gregorianDay"
                                    ></small>

                                </button>

                            </template>

                        </div>

                    </div>

                </section>


                {{-- ============================================= --}}
                {{-- 04 TIME --}}
                {{-- ============================================= --}}

                <section class="booking-card booking-time-card">

                    <div class="booking-card-heading">

                        <span>04</span>

                        <div>

                            <strong>
                                ساعت
                            </strong>

                            <small>
                                فقط زمان‌های واقعاً آزاد نمایش داده می‌شوند
                            </small>

                        </div>

                    </div>


                    {{-- Nothing selected --}}
                    <div
                        x-show="!barberId || !serviceId"
                        x-cloak
                        class="booking-state"
                    >
                        ابتدا متخصص و خدمت را انتخاب کن.
                    </div>


                    {{-- Loading --}}
                    <div
                        x-show="loading"
                        x-cloak
                        class="booking-slot-loading"
                    >

                        <div class="booking-skeleton-row">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <div class="booking-skeleton-row">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <p>
                            در حال بررسی زمان‌های آزاد...
                        </p>

                    </div>


                    {{-- Error --}}
                    <div
                        x-show="!loading && availabilityError"
                        x-cloak
                        class="booking-state booking-state-danger"
                    >
                        <strong>
                            دریافت زمان‌ها انجام نشد.
                        </strong>

                        <small>
                            اتصال را بررسی کن و دوباره تلاش کن.
                        </small>

                        <button
                            type="button"
                            class="customer-btn customer-btn-secondary"
                            @click="loadSlots()"
                        >
                            تلاش دوباره
                        </button>
                    </div>


                    {{-- Closed / no slots --}}
                    <div
                        x-show="
                            !loading &&
                            !availabilityError &&
                            barberId &&
                            serviceId &&
                            slots.length === 0
                        "
                        x-cloak
                        class="booking-state booking-state-closed"
                    >

                        <span class="booking-state-icon">
                            ×
                        </span>

                        <div>

                            <strong>
                                زمانی برای این روز موجود نیست
                            </strong>

                            <small>
                                ممکن است سالن در این روز تعطیل باشد یا تمام زمان‌ها رزرو شده باشند.
                            </small>

                        </div>

                    </div>


                    {{-- Slots --}}
                    <div
                        x-show="
                            !loading &&
                            !availabilityError &&
                            slots.length > 0
                        "
                        x-cloak
                    >

                        <div class="booking-time-grid">

                            <template
                                x-for="slot in availableSlots"
                                :key="slot.start"
                            >

                                <button
                                    type="button"
                                    class="booking-time"
                                    :class="{
                                        'is-selected':
                                            time === slot.start
                                    }"
                                    @click="selectTime(slot.start)"
                                >

                                    <span
                                        dir="ltr"
                                        x-text="toPersianDigits(slot.start)"
                                    ></span>

                                </button>

                            </template>

                        </div>


                        <div
                            x-show="availableSlots.length === 0"
                            class="booking-state"
                            x-cloak
                        >
                            برای این روز زمان آزادی باقی نمانده است.
                        </div>

                    </div>

                </section>


                {{-- ============================================= --}}
                {{-- 05 NOTES --}}
                {{-- ============================================= --}}

                <section class="booking-card">

                    <div class="booking-card-heading">

                        <span>05</span>

                        <div>

                            <strong>
                                توضیحات
                            </strong>

                            <small>
                                اختیاری
                            </small>

                        </div>

                    </div>


                    <textarea
                        name="notes"
                        class="customer-textarea"
                        maxlength="2000"
                        placeholder="مثلاً: برای رنگ، موی خیلی حساس دارم..."
                    >{{ old('notes') }}</textarea>

                </section>

            </div>


            {{-- ============================================= --}}
            {{-- SUMMARY --}}
            {{-- ============================================= --}}

            <aside class="booking-summary">

                <div class="booking-summary-inner">

                    <span class="section-kicker">
                        YOUR BOOKING
                    </span>


                    <h2>
                        خلاصه انتخاب
                    </h2>


                    <div class="booking-summary-item">

                        <span>
                            سالن
                        </span>

                        <strong>
                            {{ $salon->name }}
                        </strong>

                    </div>


                    <div class="booking-summary-item">

                        <span>
                            متخصص
                        </span>

                        <strong
                            x-text="selectedBarber?.name || 'انتخاب نشده'"
                        >
                            انتخاب نشده
                        </strong>

                    </div>


                    <div class="booking-summary-item">

                        <span>
                            خدمت
                        </span>

                        <strong
                            x-text="selectedService?.name || 'انتخاب نشده'"
                        >
                            انتخاب نشده
                        </strong>

                    </div>


                    <div class="booking-summary-item">

                        <span>
                            تاریخ
                        </span>

                        <strong
                            x-text="selectedDateLabel || 'انتخاب نشده'"
                        >
                            انتخاب نشده
                        </strong>

                    </div>


                    <div class="booking-summary-item">

                        <span>
                            ساعت
                        </span>

                        <strong
                            dir="ltr"
                            x-text="time ? toPersianDigits(time) : '--:--'"
                        >
                            --:--
                        </strong>

                    </div>


                    <div
                        class="booking-summary-item"
                        x-show="selectedService"
                        x-cloak
                    >

                        <span>
                            مدت
                        </span>

                        <strong
                            x-text="
                                selectedService
                                    ? toPersianDigits(selectedService.duration) + ' دقیقه'
                                    : ''
                            "
                        ></strong>

                    </div>


                    <div class="booking-summary-total">

                        <span>
                            مبلغ
                        </span>

                        <strong>

                            <span
                                x-text="
                                    selectedService
                                        ? formatPrice(selectedService.price)
                                        : '۰'
                                "
                            >
                                ۰
                            </span>

                            تومان

                        </strong>

                    </div>


                    @if($errors->any())

                        <div class="booking-errors">

                            @foreach($errors->all() as $error)

                                <div>
                                    {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    @endif


                    <button
                        type="submit"
                        class="customer-btn customer-btn-primary customer-btn-lg booking-submit"
                        :disabled="!canSubmit"
                    >

                        <span>
                            ادامه و تأیید نوبت
                        </span>

                        <span>
                            ←
                        </span>

                    </button>


                    <p class="booking-summary-note">
                        قبل از ثبت نهایی، اطلاعات نوبت دوباره بررسی می‌شود.
                    </p>

                </div>

            </aside>


            {{-- ============================================= --}}
            {{-- MOBILE STICKY CTA --}}
            {{-- ============================================= --}}

            <div class="booking-mobile-cta">

                <div>

                    <span>
                        <template x-if="time">
                            <span>
                                <span x-text="selectedDateLabel"></span>
                                ·
                                <b
                                    dir="ltr"
                                    x-text="toPersianDigits(time)"
                                ></b>
                            </span>
                        </template>

                        <template x-if="!time">
                            <span>
                                یک زمان را انتخاب کن
                            </span>
                        </template>
                    </span>


                    <strong
                        x-text="
                            selectedService
                                ? formatPrice(selectedService.price) + ' تومان'
                                : ''
                        "
                    ></strong>

                </div>


                <button
                    type="submit"
                    class="customer-btn customer-btn-primary customer-btn-lg"
                    :disabled="!canSubmit"
                >
                    ادامه
                    ←
                </button>

            </div>

        </form>

    </div>


    @push('scripts')

        <script>
            function bookingPage() {
                return {

                    barberId: '',
                    serviceId: '',

                    date: '{{ old('booking_date', now()->format('Y-m-d')) }}',

                    time: '',

                    slots: [],

                    loading: false,

                    availabilityError: false,

                    weekOffset: 0,

                    touchStartX: 0,

                    barbers: @js($barberPayload),

                    services: @js($servicePayload),


                    init() {

                        this.$nextTick(() => {

                            this.ensureDateNotPast();

                            this.updateCalendar();

                            this.loadSlots();

                        });

                    },


                    get selectedBarber() {

                        return this.barbers.find(
                            item =>
                                String(item.id) ===
                                String(this.barberId)
                        );

                    },


                    get selectedService() {

                        return this.services.find(
                            item =>
                                String(item.id) ===
                                String(this.serviceId)
                        );

                    },


                    get availableSlots() {

                        return this.slots.filter(
                            slot => slot.available
                        );

                    },


                    get canSubmit() {

                        return Boolean(
                            this.barberId &&
                            this.serviceId &&
                            this.date &&
                            this.time
                        );

                    },


                    get selectedDateLabel() {

                        if (!this.date) {
                            return '';
                        }

                        const parts =
                            this.gregorianToJalali(
                                this.date
                            );

                        const weekdays = [
                            'شنبه',
                            'یکشنبه',
                            'دوشنبه',
                            'سه‌شنبه',
                            'چهارشنبه',
                            'پنجشنبه',
                            'جمعه',
                        ];

                        const dateObject =
                            this.parseDate(
                                this.date
                            );

                        const weekday =
                            weekdays[
                            (dateObject.getDay() + 1) % 7
                                ];

                        return `${weekday} ${this.toPersianDigits(parts[2])} ${this.jalaliMonthName(parts[1])} ${this.toPersianDigits(parts[0])}`;

                    },


                    get monthLabel() {

                        if (!this.date) {
                            return '';
                        }

                        const parts =
                            this.gregorianToJalali(
                                this.date
                            );

                        return `${this.jalaliMonthName(parts[1])} ${this.toPersianDigits(parts[0])}`;

                    },


                    get calendarDays() {

                        const center =
                            this.parseDate(
                                this.date
                            );

                        center.setDate(
                            center.getDate() +
                            (this.weekOffset * 7)
                        );

                        const days = [];

                        for (
                            let i = -3;
                            i <= 3;
                            i++
                        ) {

                            const date =
                                new Date(center);

                            date.setDate(
                                center.getDate() + i
                            );

                            const iso =
                                this.formatDate(date);

                            const today =
                                this.formatDate(
                                    new Date()
                                );

                            const jalali =
                                this.gregorianToJalali(
                                    iso
                                );

                            const weekdays = [
                                'شنبه',
                                'یکشنبه',
                                'دوشنبه',
                                'سه‌شنبه',
                                'چهارشنبه',
                                'پنجشنبه',
                                'جمعه',
                            ];

                            days.push({

                                gregorian: iso,

                                jalaliDay:
                                    jalali[2],

                                gregorianDay:
                                    date.getDate(),

                                weekday:
                                    weekdays[
                                        date.getDay() === 6
                                            ? 0
                                            : date.getDay() + 1
                                        ],

                                isToday:
                                    iso === today,

                                disabled:
                                    iso < today,

                            });

                        }

                        return days;

                    },


                    updateCalendar() {

                        this.$nextTick(() => {});

                    },


                    parseDate(value) {

                        const [
                            year,
                            month,
                            day
                        ] =
                            value
                                .split('-')
                                .map(Number);

                        return new Date(
                            year,
                            month - 1,
                            day
                        );

                    },


                    formatDate(date) {

                        const year =
                            date.getFullYear();

                        const month =
                            String(
                                date.getMonth() + 1
                            ).padStart(2, '0');

                        const day =
                            String(
                                date.getDate()
                            ).padStart(2, '0');

                        return `${year}-${month}-${day}`;

                    },


                    ensureDateNotPast() {

                        const today =
                            this.formatDate(
                                new Date()
                            );

                        if (this.date < today) {
                            this.date = today;
                        }

                    },


                    selectBarber(id) {

                        this.barberId =
                            String(id);

                        this.time = '';

                        this.loadSlots();

                    },


                    selectService(id) {

                        this.serviceId =
                            String(id);

                        this.time = '';

                        this.loadSlots();

                    },


                    selectDate(date) {

                        this.date = date;

                        this.time = '';

                        this.weekOffset = 0;

                        this.loadSlots();

                    },


                    selectTime(time) {

                        this.time = time;

                        this.$nextTick(() => {

                            const selected =
                                document.querySelector(
                                    '.booking-time.is-selected'
                                );

                            if (selected) {

                                selected.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'nearest'
                                });

                            }

                        });

                    },


                    goToday() {

                        this.date =
                            this.formatDate(
                                new Date()
                            );

                        this.weekOffset = 0;

                        this.time = '';

                        this.loadSlots();

                    },


                    previousWeek() {

                        if (this.weekOffset <= 0) {
                            return;
                        }

                        this.weekOffset--;

                    },


                    nextWeek() {

                        this.weekOffset++;

                    },


                    touchStart(event) {

                        this.touchStartX =
                            event.changedTouches[0].screenX;

                    },


                    touchEnd(event) {

                        const end =
                            event.changedTouches[0].screenX;

                        const distance =
                            end - this.touchStartX;

                        if (Math.abs(distance) < 50) {
                            return;
                        }

                        if (distance > 0) {
                            this.previousWeek();
                        } else {
                            this.nextWeek();
                        }

                    },


                    async loadSlots() {

                        if (
                            !this.barberId ||
                            !this.serviceId ||
                            !this.date
                        ) {

                            this.slots = [];

                            return;

                        }


                        this.loading = true;

                        this.availabilityError = false;

                        this.time = '';


                        try {

                            const url =
                                '{{ route('public.salons.booking.availability', $salon) }}' +
                                '?barber_id=' +
                                encodeURIComponent(
                                    this.barberId
                                ) +
                                '&service_id=' +
                                encodeURIComponent(
                                    this.serviceId
                                ) +
                                '&booking_date=' +
                                encodeURIComponent(
                                    this.date
                                );


                            const response =
                                await fetch(
                                    url,
                                    {
                                        headers: {
                                            'Accept':
                                                'application/json',

                                            'X-Requested-With':
                                                'XMLHttpRequest',
                                        },
                                    }
                                );


                            if (!response.ok) {
                                throw new Error(
                                    'Availability request failed'
                                );
                            }


                            const data =
                                await response.json();


                            this.slots =
                                Array.isArray(data.slots)
                                    ? data.slots
                                    : [];


                        } catch (error) {

                            console.error(
                                'Booking availability error:',
                                error
                            );

                            this.slots = [];

                            this.availabilityError = true;

                        } finally {

                            this.loading = false;

                        }

                    },


                    handleSubmit(event) {

                        if (!this.canSubmit) {

                            event.preventDefault();

                            if (!this.barberId) {
                                this.scrollToSection(1);
                                return;
                            }

                            if (!this.serviceId) {
                                this.scrollToSection(2);
                                return;
                            }

                            if (!this.time) {
                                this.scrollToTime();
                                return;
                            }

                        }

                    },


                    scrollToSection(number) {

                        const cards =
                            document.querySelectorAll(
                                '.booking-main .booking-card'
                            );

                        const card =
                            cards[number - 1];

                        if (card) {

                            card.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                        }

                    },


                    scrollToTime() {

                        const card =
                            document.querySelector(
                                '.booking-time-card'
                            );

                        if (card) {

                            card.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                        }

                    },


                    formatPrice(value) {

                        return new Intl.NumberFormat(
                            'fa-IR'
                        ).format(
                            Number(value || 0)
                        );

                    },


                    toPersianDigits(value) {

                        return String(value)
                            .replace(
                                /\d/g,
                                digit =>
                                    '۰۱۲۳۴۵۶۷۸۹'[
                                        digit
                                        ]
                            );

                    },


                    jalaliMonthName(month) {

                        const months = [
                            'فروردین',
                            'اردیبهشت',
                            'خرداد',
                            'تیر',
                            'مرداد',
                            'شهریور',
                            'مهر',
                            'آبان',
                            'آذر',
                            'دی',
                            'بهمن',
                            'اسفند',
                        ];

                        return months[
                        Number(month) - 1
                            ];

                    },


                    gregorianToJalali(gy, gm, gd) {

                        if (
                            typeof gy === 'string'
                        ) {

                            const parts =
                                gy
                                    .split('-')
                                    .map(Number);

                            gy = parts[0];
                            gm = parts[1];
                            gd = parts[2];

                        }


                        const gdm =
                            [
                                0,
                                31,
                                59,
                                90,
                                120,
                                151,
                                181,
                                212,
                                243,
                                273,
                                304,
                                334
                            ];


                        let jy;

                        if (gy > 1600) {

                            jy = 979;

                            gy -= 1600;

                        } else {

                            jy = 0;

                            gy -= 621;

                        }


                        const gy2 =
                            gm > 2
                                ? gy + 1
                                : gy;


                        let days =
                            (
                                365 * gy
                            ) +
                            Math.floor(
                                (gy2 + 3) / 4
                            ) -
                            Math.floor(
                                (gy2 + 99) / 100
                            ) +
                            Math.floor(
                                (gy2 + 399) / 400
                            ) -
                            80 +
                            gd +
                            gdm[gm - 1];


                        jy +=
                            33 *
                            Math.floor(
                                days / 12053
                            );

                        days %= 12053;

                        jy +=
                            4 *
                            Math.floor(
                                days / 1461
                            );

                        days %= 1461;


                        if (days > 365) {

                            jy +=
                                Math.floor(
                                    (days - 1) / 365
                                );

                            days =
                                (days - 1) % 365;

                        }


                        let jm;

                        let jd;


                        if (days < 186) {

                            jm =
                                1 +
                                Math.floor(
                                    days / 31
                                );

                            jd =
                                1 +
                                (days % 31);

                        } else {

                            jm =
                                7 +
                                Math.floor(
                                    (days - 186) / 30
                                );

                            jd =
                                1 +
                                (
                                    (days - 186) % 30
                                );

                        }


                        return [
                            jy,
                            jm,
                            jd
                        ];

                    },

                };
            }
        </script>

    @endpush

@endsection
