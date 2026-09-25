@extends('layouts.customer')

@section('title', 'ویرایش نوبت')

@section('content')

    @php
        $bookingDate = optional($booking->booking_date)->format('Y-m-d')
            ?? (string) $booking->booking_date;

        $today = now(config('app.timezone', 'Asia/Tehran'))->format('Y-m-d');

        $currentStart = substr((string) $booking->start_time, 0, 5);
    @endphp

    <div class="customer-container py-5 pb-28 sm:py-8">
        <div class="mx-auto w-full max-w-3xl">

            <div class="mb-5">
                <a
                    href="{{ route('customer.dashboard') }}"
                    class="text-xs font-bold text-content-muted transition hover:text-content"
                >
                    ← بازگشت به نوبت‌های من
                </a>

                <span class="mt-5 block text-[10px] font-black tracking-[0.16em] text-accent-600">
                    EDIT BOOKING
                </span>

                <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                    نوبتت را ویرایش کن
                </h1>

                <p class="mt-2 text-xs leading-7 text-content-muted">
                    فقط نوبتی که هنوز در انتظار تأیید است قابل تغییر است.
                    زمان انتخابی در لحظه ثبت دوباره بررسی می‌شود.
                </p>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold leading-7 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-7 text-red-700">
                    <div class="font-black">ویرایش نوبت انجام نشد.</div>

                    <div class="mt-1 space-y-1">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form
                id="customer-booking-edit-form"
                action="{{ route('customer.bookings.update', $booking) }}"
                method="POST"
                class="space-y-4"
                novalidate
            >
                @csrf
                @method('PUT')

                <input type="hidden" name="salon_id" value="{{ $salon->id }}">

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <div class="mb-5">
                        <div class="text-[10px] font-black tracking-[0.14em] text-content-faint">
                            SALON
                        </div>

                        <h2 class="mt-1 text-lg font-black text-content">
                            {{ $salon->name }}
                        </h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black text-content">
                                متخصص
                            </span>

                            <select
                                id="edit-barber"
                                name="barber_id"
                                class="min-h-12 w-full rounded-2xl border border-border bg-surface px-4 text-sm font-bold text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            >
                                @foreach($barbers as $barber)
                                    <option
                                        value="{{ $barber->id }}"
                                        @selected((int) old('barber_id', $booking->barber_id) === (int) $barber->id)
                                    >
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if($barbers->isEmpty())
                                <span class="mt-2 block text-[10px] text-red-600">
                                    هیچ متخصص فعالی در این سالن باقی نمانده است.
                                </span>
                            @endif
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black text-content">
                                خدمت
                            </span>

                            <select
                                id="edit-service"
                                name="service_id"
                                class="min-h-12 w-full rounded-2xl border border-border bg-surface px-4 text-sm font-bold text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            >
                                @foreach($services as $service)
                                    <option
                                        value="{{ $service->id }}"
                                        @selected((int) old('service_id', $booking->service_id) === (int) $service->id)
                                    >
                                        {{ $service->name }} — {{ number_format($service->price) }} تومان / {{ $service->duration_minutes }} دقیقه
                                    </option>
                                @endforeach
                            </select>

                            @if($services->isEmpty())
                                <span class="mt-2 block text-[10px] text-red-600">
                                    هیچ خدمت فعالی در این سالن باقی نمانده است.
                                </span>
                            @endif
                        </label>

                        <div class="sm:col-span-2">
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <div>
                                    <span class="block text-[11px] font-black text-content">
                                        تاریخ
                                    </span>
                                    <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                        تاریخ را به تقویم شمسی انتخاب کن؛ تاریخ ثبت‌شده نوبت همان‌جا مشخص است.
                                    </span>
                                </div>

                                <button
                                    id="edit-today"
                                    type="button"
                                    class="rounded-xl border border-border bg-surface px-3 py-2 text-[10px] font-black text-content-muted transition hover:border-accent-300 hover:bg-accent-50 hover:text-accent-700"
                                >
                                    امروز
                                </button>
                            </div>

                            <input
                                id="edit-date"
                                type="hidden"
                                name="booking_date"
                                value="{{ old('booking_date', $bookingDate) }}"
                                required
                            >

                            <div
                                id="edit-calendar"
                                class="customer-edit-calendar"
                                dir="rtl"
                                data-initial-date="{{ old('booking_date', $bookingDate) }}"
                                data-today="{{ $today }}"
                            >
                                <div class="customer-edit-calendar-head">
                                    <button
                                        id="edit-calendar-prev"
                                        type="button"
                                        class="customer-edit-calendar-nav"
                                        aria-label="ماه قبل"
                                    >
                                        <span aria-hidden="true">→</span>
                                    </button>

                                    <div class="customer-edit-calendar-title">
                                        <strong id="edit-calendar-month">—</strong>
                                        <span>انتخاب روز</span>
                                    </div>

                                    <button
                                        id="edit-calendar-next"
                                        type="button"
                                        class="customer-edit-calendar-nav"
                                        aria-label="ماه بعد"
                                    >
                                        <span aria-hidden="true">←</span>
                                    </button>
                                </div>

                                <div class="customer-edit-calendar-weekdays" aria-hidden="true">
                                    <span>شنبه</span>
                                    <span>یکشنبه</span>
                                    <span>دوشنبه</span>
                                    <span>سه‌شنبه</span>
                                    <span>چهارشنبه</span>
                                    <span>پنجشنبه</span>
                                    <span>جمعه</span>
                                </div>

                                <div
                                    id="edit-calendar-days"
                                    class="customer-edit-calendar-days"
                                    aria-live="polite"
                                ></div>

                                <div class="customer-edit-date-summary">
                                    <span>تاریخ انتخابی</span>
                                    <strong id="edit-selected-date-label">—</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <div class="text-[10px] font-black tracking-[0.14em] text-content-faint">
                                TIME
                            </div>

                            <h2 class="mt-1 text-lg font-black text-content">
                                ساعت جدید
                            </h2>
                        </div>

                        <span
                            id="edit-availability-status"
                            class="text-[10px] font-bold text-content-muted"
                            aria-live="polite"
                        >
                            در حال بررسی...
                        </span>
                    </div>

                    <input
                        id="edit-time"
                        type="hidden"
                        name="start_time"
                        value="{{ old('start_time', $currentStart) }}"
                    >

                    <div class="mb-3 rounded-2xl border border-border bg-primary-50/45 px-4 py-3">
                        <div class="text-[10px] font-black text-content-faint">
                            زمان انتخابی
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-3">
                            <strong
                                id="edit-selected-time"
                                class="text-sm font-black text-content"
                                dir="ltr"
                            >
                                —
                            </strong>
                            <span class="text-[10px] font-bold text-content-muted">
                                یکی از زمان‌های آزاد را انتخاب کن.
                            </span>
                        </div>
                    </div>

                    <div
                        id="edit-slots"
                        class="customer-edit-time-grid"
                        aria-live="polite"
                    ></div>

                    <div
                        id="edit-availability-message"
                        class="mt-4 rounded-2xl bg-primary-50/60 px-4 py-3 text-[10px] font-bold leading-6 text-content-muted"
                    >
                        زمان‌های آزاد این متخصص و خدمت اینجا نمایش داده می‌شوند.
                    </div>
                </section>

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <label class="block">
                        <span class="mb-2 block text-[11px] font-black text-content">
                            توضیحات
                        </span>

                        <textarea
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            class="w-full rounded-2xl border border-border bg-surface px-4 py-3 text-sm leading-7 text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            placeholder="توضیح کوتاه برای سالن..."
                        >{{ old('notes', $booking->notes) }}</textarea>

                        @error('notes')
                            <span class="mt-2 block text-[10px] font-bold text-red-600">
                                {{ $message }}
                            </span>
                        @enderror
                    </label>
                </section>

                <div class="sticky bottom-3 z-10 rounded-2xl border border-border bg-surface/95 p-2 shadow-lg backdrop-blur sm:static sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">
                    <button
                        id="edit-submit"
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-accent-600 px-5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-accent-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        disabled
                    >
                        ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<style>
.customer-edit-calendar {
    overflow: hidden;
    border: 1px solid var(--color-border);
    border-radius: 1.5rem;
    background: var(--color-surface);
}

.customer-edit-calendar-head {
    display: grid;
    grid-template-columns: 2.7rem minmax(0, 1fr) 2.7rem;
    align-items: center;
    gap: .6rem;
    padding: .9rem;
    border-bottom: 1px solid var(--color-border);
    background: color-mix(in srgb, var(--color-primary-50) 42%, var(--color-surface));
}

.customer-edit-calendar-nav {
    display: grid;
    width: 2.7rem;
    height: 2.7rem;
    place-items: center;
    border: 1px solid var(--color-border);
    border-radius: .9rem;
    background: var(--color-surface);
    color: var(--color-content);
    font-size: .9rem;
    font-weight: 900;
    transition: transform .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease;
}

.customer-edit-calendar-nav:hover:not(:disabled) {
    transform: translateY(-1px);
    border-color: var(--color-accent-300);
    background: var(--color-accent-50);
    color: var(--color-accent-700);
}

.customer-edit-calendar-nav:disabled {
    opacity: .35;
    cursor: not-allowed;
}

.customer-edit-calendar-title {
    display: flex;
    min-width: 0;
    flex-direction: column;
    align-items: center;
    gap: .15rem;
}

.customer-edit-calendar-title strong {
    color: var(--color-content);
    font-size: .88rem;
    font-weight: 950;
}

.customer-edit-calendar-title span {
    color: var(--color-content-faint);
    font-size: .53rem;
    font-weight: 800;
}

.customer-edit-calendar-weekdays,
.customer-edit-calendar-days {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: .35rem;
    padding-inline: .8rem;
}

.customer-edit-calendar-weekdays {
    padding-top: .8rem;
    padding-bottom: .35rem;
}

.customer-edit-calendar-weekdays span {
    text-align: center;
    color: var(--color-content-faint);
    font-size: .52rem;
    font-weight: 900;
}

.customer-edit-calendar-days {
    padding-bottom: .8rem;
}

.customer-edit-calendar-empty {
    min-height: 3.1rem;
}

.customer-edit-calendar-day {
    min-height: 3.15rem;
    border: 1px solid var(--color-border);
    border-radius: .85rem;
    background: var(--color-surface);
    color: var(--color-content);
    transition: transform .16s ease, border-color .16s ease, background-color .16s ease, color .16s ease, box-shadow .16s ease;
}

.customer-edit-calendar-day:hover:not(:disabled) {
    transform: translateY(-1px);
    border-color: var(--color-accent-300);
    background: var(--color-accent-50);
}

.customer-edit-calendar-day.is-today {
    border-color: var(--color-accent-200);
}

.customer-edit-calendar-day.is-selected {
    border-color: var(--color-accent-600);
    background: var(--color-accent-600);
    color: #fff;
    box-shadow: 0 8px 20px color-mix(in srgb, var(--color-accent-600) 18%, transparent);
}

.customer-edit-calendar-day.is-disabled {
    border-color: var(--color-border);
    background: var(--color-primary-50);
    color: var(--color-content-faint);
    opacity: .55;
    cursor: not-allowed;
}

.customer-edit-calendar-day .jalali-day {
    display: block;
    font-size: .82rem;
    font-weight: 950;
    line-height: 1;
}

.customer-edit-calendar-day .jalali-date {
    display: block;
    margin-top: .25rem;
    color: inherit;
    opacity: .62;
    font-size: .46rem;
    font-weight: 750;
    direction: ltr;
}

.customer-edit-calendar-day.is-selected .jalali-date {
    opacity: .82;
}

.customer-edit-date-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin: 0 .8rem .8rem;
    padding: .75rem .85rem;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    background: color-mix(in srgb, var(--color-primary-50) 45%, var(--color-surface));
}

.customer-edit-date-summary span {
    color: var(--color-content-muted);
    font-size: .55rem;
    font-weight: 800;
}

.customer-edit-date-summary strong {
    color: var(--color-content);
    font-size: .7rem;
    font-weight: 950;
}

.customer-edit-time-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .55rem;
}

.customer-edit-time {
    min-height: 2.8rem;
    border: 1px solid var(--color-border);
    border-radius: .9rem;
    background: var(--color-surface);
    color: var(--color-content);
    font-size: .72rem;
    font-weight: 950;
    direction: ltr;
    transition: transform .16s ease, border-color .16s ease, background-color .16s ease, color .16s ease, box-shadow .16s ease;
}

.customer-edit-time:hover:not(:disabled) {
    transform: translateY(-1px);
    border-color: var(--color-accent-300);
    background: var(--color-accent-50);
    color: var(--color-accent-700);
}

.customer-edit-time.is-selected {
    border-color: var(--color-accent-600);
    background: var(--color-accent-600);
    color: #fff;
    box-shadow: 0 8px 20px color-mix(in srgb, var(--color-accent-600) 17%, transparent);
}

.customer-edit-time.is-unavailable {
    color: var(--color-content-faint);
    background: var(--color-primary-50);
    text-decoration: line-through;
    cursor: not-allowed;
    opacity: .65;
}

.customer-edit-picker-empty {
    grid-column: 1 / -1;
    padding: 1rem;
    border: 1px dashed var(--color-border);
    border-radius: 1rem;
    color: var(--color-content-muted);
    background: var(--color-primary-50/60);
    font-size: .58rem;
    font-weight: 800;
    line-height: 1.9;
    text-align: center;
}

@media (min-width: 560px) {
    .customer-edit-time-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 480px) {
    .customer-edit-calendar-weekdays,
    .customer-edit-calendar-days {
        gap: .25rem;
        padding-inline: .55rem;
    }

    .customer-edit-calendar-day {
        min-height: 2.75rem;
        border-radius: .72rem;
    }

    .customer-edit-calendar-day .jalali-day {
        font-size: .74rem;
    }

    .customer-edit-calendar-day .jalali-date {
        font-size: .4rem;
    }
}
</style>

<script>
(() => {
    const form = document.getElementById('customer-booking-edit-form');
    if (!form) return;

    const barber = document.getElementById('edit-barber');
    const service = document.getElementById('edit-service');
    const date = document.getElementById('edit-date');
    const time = document.getElementById('edit-time');
    const slots = document.getElementById('edit-slots');
    const submit = document.getElementById('edit-submit');
    const status = document.getElementById('edit-availability-status');
    const message = document.getElementById('edit-availability-message');
    const calendar = document.getElementById('edit-calendar');
    const calendarDays = document.getElementById('edit-calendar-days');
    const calendarMonth = document.getElementById('edit-calendar-month');
    const prevMonth = document.getElementById('edit-calendar-prev');
    const nextMonth = document.getElementById('edit-calendar-next');
    const todayButton = document.getElementById('edit-today');
    const selectedDateLabel = document.getElementById('edit-selected-date-label');
    const selectedTimeLabel = document.getElementById('edit-selected-time');

    const endpoint = @json(route('customer.bookings.edit-availability', $booking));
    const initialDate = @json(old('booking_date', $bookingDate));
    const initialTime = @json(old('start_time', $currentStart));
    const todayIso = @json($today);

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

    const weekdays = [
        'شنبه',
        'یکشنبه',
        'دوشنبه',
        'سه‌شنبه',
        'چهارشنبه',
        'پنجشنبه',
        'جمعه',
    ];

    const fa = (value) => String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);
    const pad2 = (value) => String(value).padStart(2, '0');
    const fmtYmd = (year, month, day) => `${year}-${pad2(month)}-${pad2(day)}`;

    const parseIso = (iso) => {
        const [year, month, day] = String(iso).split('-').map(Number);
        return { year, month, day };
    };

    const localDate = (iso) => {
        const { year, month, day } = parseIso(iso);
        return new Date(year, month - 1, day, 12, 0, 0);
    };

    function toJalali(gy, gm, gd) {
        const gDM = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        let jy = gy <= 1600 ? 0 : 979;
        gy -= gy <= 1600 ? 621 : 1600;

        const gy2 = gm > 2 ? gy + 1 : gy;

        let days =
            365 * gy +
            Math.floor((gy2 + 3) / 4) -
            Math.floor((gy2 + 99) / 100) +
            Math.floor((gy2 + 399) / 400) -
            80 +
            gd +
            gDM[gm - 1];

        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const jm = days < 186
            ? 1 + Math.floor(days / 31)
            : 7 + Math.floor((days - 186) / 30);

        const jd = 1 + (
            days < 186
                ? days % 31
                : (days - 186) % 30
        );

        return [jy, jm, jd];
    }

    function toGregorian(jy, jm, jd) {
        let gy;

        if (jy > 979) {
            gy = 1600;
            jy -= 979;
        } else {
            gy = 621;
        }

        let days =
            365 * jy +
            Math.floor(jy / 33) * 8 +
            Math.floor((jy % 33 + 3) / 4) +
            78 +
            jd +
            (jm < 7
                ? (jm - 1) * 31
                : (jm - 7) * 30 + 186);

        gy += 400 * Math.floor(days / 146097);
        days %= 146097;

        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;

            if (days >= 365) {
                days++;
            }
        }

        gy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const gd = days + 1;

        const monthDays = [
            31,
            (gy % 4 === 0 && (gy % 100 !== 0 || gy % 400 === 0)) ? 29 : 28,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31,
        ];

        let gm = 1;
        let remaining = gd;

        while (remaining > monthDays[gm - 1]) {
            remaining -= monthDays[gm - 1];
            gm++;
        }

        return [gy, gm, remaining];
    }

    function jalaliYearLength(jy) {
        const current = toGregorian(jy, 1, 1);
        const next = toGregorian(jy + 1, 1, 1);

        return Math.round(
            (localDate(fmtYmd(...next)) - localDate(fmtYmd(...current))) / 86400000
        );
    }

    function daysInJalaliMonth(jy, jm) {
        if (jm <= 6) return 31;
        if (jm <= 11) return 30;
        return jalaliYearLength(jy) === 366 ? 30 : 29;
    }

    const today = parseIso(todayIso);
    const todayJalali = toJalali(today.year, today.month, today.day);
    const initialJalali = toJalali(
        ...Object.values(parseIso(initialDate))
    );

    let viewJy = initialJalali[0];
    let viewJm = initialJalali[1];
    let selectedGregorian = date.value || initialDate;
    let selectedAvailable = false;
    let requestId = 0;
    let controller = null;

    const compareMonth = (ay, am, by, bm) => {
        if (ay !== by) return ay - by;
        return am - bm;
    };

    const normalizeMonth = (year, month) => {
        while (month < 1) {
            month += 12;
            year--;
        }

        while (month > 12) {
            month -= 12;
            year++;
        }

        return [year, month];
    };

    const selectedJalaliLabel = (iso) => {
        const parts = parseIso(iso);
        const [jy, jm, jd] = toJalali(parts.year, parts.month, parts.day);

        return `${fa(jd)} ${months[jm - 1]} ${fa(jy)}`;
    };

    const setMessage = (text, isError = false) => {
        message.textContent = text;
        message.classList.toggle('bg-red-50', isError);
        message.classList.toggle('text-red-700', isError);
        message.classList.toggle('bg-primary-50/60', !isError);
        message.classList.toggle('text-content-muted', !isError);
    };

    const updateDateLabels = () => {
        selectedDateLabel.textContent = selectedGregorian
            ? selectedJalaliLabel(selectedGregorian)
            : '—';
    };

    const clearTimeSelection = () => {
        time.value = '';
        selectedTimeLabel.textContent = '—';
        selectedAvailable = false;
        slots.querySelectorAll('.customer-edit-time').forEach((item) => {
            item.classList.remove('is-selected');
        });
        submit.disabled = true;
    };

    const renderCalendar = () => {
        const days = daysInJalaliMonth(viewJy, viewJm);
        const firstGregorian = toGregorian(viewJy, viewJm, 1);
        const firstDate = new Date(
            firstGregorian[0],
            firstGregorian[1] - 1,
            firstGregorian[2],
            12
        );

        const offset = (firstDate.getDay() + 1) % 7;

        calendarMonth.textContent = `${months[viewJm - 1]} ${fa(viewJy)}`;
        calendarDays.innerHTML = '';

        for (let index = 0; index < offset; index++) {
            const empty = document.createElement('div');
            empty.className = 'customer-edit-calendar-empty';
            empty.setAttribute('aria-hidden', 'true');
            calendarDays.appendChild(empty);
        }

        const todayDate = localDate(todayIso);

        for (let jd = 1; jd <= days; jd++) {
            const [gy, gm, gd] = toGregorian(viewJy, viewJm, jd);
            const iso = fmtYmd(gy, gm, gd);
            const dayDate = localDate(iso);
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'customer-edit-calendar-day';
            button.dataset.date = iso;
            button.disabled = dayDate < todayDate;

            const jalaliDay = document.createElement('span');
            jalaliDay.className = 'jalali-day';
            jalaliDay.textContent = fa(jd);

            const gregorianDay = document.createElement('span');
            gregorianDay.className = 'jalali-date';
            gregorianDay.textContent = `${gm}/${gd}`;

            button.append(jalaliDay, gregorianDay);

            if (iso === todayIso) {
                button.classList.add('is-today');
            }

            if (iso === selectedGregorian) {
                button.classList.add('is-selected');
            }

            if (button.disabled) {
                button.classList.add('is-disabled');
                button.title = 'تاریخ گذشته';
                button.setAttribute('aria-label', `${selectedJalaliLabel(iso)} - گذشته`);
            } else {
                button.setAttribute('aria-label', selectedJalaliLabel(iso));
                button.addEventListener('click', () => {
                    selectedGregorian = iso;
                    date.value = iso;
                    updateDateLabels();
                    clearTimeSelection();
                    renderCalendar();
                    loadAvailability();
                });
            }

            calendarDays.appendChild(button);
        }

        prevMonth.disabled =
            compareMonth(viewJy, viewJm, todayJalali[0], todayJalali[1]) <= 0;
    };

    prevMonth.addEventListener('click', () => {
        const [year, month] = normalizeMonth(viewJy, viewJm - 1);

        if (compareMonth(year, month, todayJalali[0], todayJalali[1]) < 0) {
            return;
        }

        viewJy = year;
        viewJm = month;
        renderCalendar();
    });

    nextMonth.addEventListener('click', () => {
        [viewJy, viewJm] = normalizeMonth(viewJy, viewJm + 1);
        renderCalendar();
    });

    todayButton.addEventListener('click', () => {
        const todayDate = fmtYmd(today.year, today.month, today.day);
        const [jy, jm] = todayJalali;

        viewJy = jy;
        viewJm = jm;
        selectedGregorian = todayDate;
        date.value = todayDate;
        updateDateLabels();
        clearTimeSelection();
        renderCalendar();
        loadAvailability();
    });

    const renderSlots = (items) => {
        slots.innerHTML = '';
        selectedAvailable = false;

        if (!Array.isArray(items) || items.length === 0) {
            submit.disabled = true;
            selectedTimeLabel.textContent = '—';
            slots.innerHTML = '<div class="customer-edit-picker-empty">برای این تاریخ، متخصص و خدمت انتخاب‌شده زمان آزادی ندارند.</div>';
            setMessage('برای این انتخاب، زمان آزادی پیدا نشد.', false);
            return;
        }

        const currentValue = time.value;

        items.forEach((slot) => {
            const available = slot.available === true;
            const button = document.createElement('button');

            button.type = 'button';
            button.textContent = fa(slot.start);
            button.className = 'customer-edit-time';
            button.disabled = !available;

            if (!available) {
                button.classList.add('is-unavailable');
                button.title = 'این زمان در دسترس نیست';
            }

            if (available && slot.start === currentValue) {
                button.classList.add('is-selected');
                selectedAvailable = true;
                selectedTimeLabel.textContent = fa(slot.start);
            }

            if (available) {
                button.addEventListener('click', () => {
                    time.value = slot.start;
                    selectedAvailable = true;
                    selectedTimeLabel.textContent = fa(slot.start);

                    slots
                        .querySelectorAll('.customer-edit-time')
                        .forEach((item) => item.classList.remove('is-selected'));

                    button.classList.add('is-selected');
                    submit.disabled = false;
                    setMessage('این زمان در حال حاضر آزاد است و برای ثبت دوباره بررسی می‌شود.', false);
                });
            }

            slots.appendChild(button);
        });

        if (selectedAvailable) {
            setMessage('زمان نوبت فعلی هنوز آزاد است.', false);
            submit.disabled = false;
        } else {
            setMessage('زمان قبلی دیگر آزاد نیست؛ یکی از زمان‌های آزاد را انتخاب کن.', false);
            submit.disabled = true;
        }
    };

    const loadAvailability = async () => {
        const currentRequestId = ++requestId;

        controller?.abort();
        controller = new AbortController();

        submit.disabled = true;
        status.textContent = 'در حال بررسی...';
        setMessage('در حال دریافت زمان‌های آزاد...', false);
        slots.innerHTML = '<div class="customer-edit-picker-empty">در حال بارگذاری زمان‌های آزاد...</div>';

        if (!barber.value || !service.value || !date.value) {
            status.textContent = 'انتخاب‌ها کامل نیست';
            setMessage('متخصص، خدمت و تاریخ را کامل انتخاب کن.', true);
            return;
        }

        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('barber_id', barber.value);
        url.searchParams.set('service_id', service.value);
        url.searchParams.set('booking_date', date.value);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (currentRequestId !== requestId) return;

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'دریافت زمان‌های آزاد انجام نشد.');
            }

            status.textContent = 'به‌روز شد';
            renderSlots(data.slots);
        } catch (error) {
            if (error?.name === 'AbortError') return;

            console.error(error);
            status.textContent = 'قابل بررسی نیست';
            renderSlots([]);
            setMessage(
                error?.message || 'دریافت زمان‌های آزاد انجام نشد. دوباره تلاش کن.',
                true
            );
        }
    };

    [barber, service].forEach((control) => {
        control?.addEventListener('change', () => {
            clearTimeSelection();
            loadAvailability();
        });
    });

    form.addEventListener('submit', (event) => {
        if (!selectedAvailable || !time.value || !date.value) {
            event.preventDefault();
            setMessage('تاریخ و یک زمان آزاد را انتخاب کن و دوباره ثبت کن.', true);
            return;
        }
    });

    updateDateLabels();
    renderCalendar();
    time.value = initialTime;
    selectedTimeLabel.textContent = initialTime ? fa(initialTime) : '—';
    loadAvailability();
})();
</script>
@endpush