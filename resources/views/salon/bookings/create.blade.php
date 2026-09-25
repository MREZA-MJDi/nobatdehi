@extends('layouts.salon')

@section('title', 'ثبت نوبت دستی')

@php
    $todayIso = now(
        config('app.timezone', 'Asia/Tehran')
    )->toDateString();

    $servicesData = $services
        ->map(fn ($service) => [
            'id' => (int) $service->id,
            'name' => $service->name,
            'price' => (int) $service->price,
            'duration' => (int) $service->duration_minutes,
        ])
        ->values();

    $barbersData = $barbers
        ->map(fn ($barber) => [
            'id' => (int) $barber->id,
            'name' => $barber->name,
        ])
        ->values();

@endphp


@section('content')

    <script>
        function salonManualBooking() {
            return {
                todayIso: @js($todayIso),

                barbers: @js($barbersData),
                services: @js($servicesData),
                dataEndpoint: @js(route('salon.bookings.manual-data')),
                loadingBootstrap: false,
                bootstrapError: '',

                customerName: @js(old('customer_name', '')),
                customerPhone: @js(old('customer_phone', '')),
                manualConfirmed: @js((bool) old('manual_confirmed', true)),

                barberId: @js(old('barber_id', '')),
                serviceId: @js(old('service_id', '')),

                selectedDate: @js(old('booking_date', $todayIso)),
                selectedTime: @js(old('start_time', '')),


                calendarOpen: false,
                calendarAnchorIso: '',

                schedule: {
                    day_name: '',
                    status: 'not_configured',
                    intervals: [],
                    breaks: [],
                },

                slots: [],
                loadingSlots: false,
                slotError: '',
                submitError: '',
                submitting: false,

                abortController: null,

                weekDays: [
                    'ش',
                    'ی',
                    'د',
                    'س',
                    'چ',
                    'پ',
                    'ج',
                ],

                jalaliMonths: [
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
                ],

                async init() {
                    if (
                        !this.selectedDate ||
                        this.selectedDate < this.todayIso
                    ) {
                        this.selectedDate =
                            this.todayIso;
                    }

                    this.initCalendar();

                    await this.loadBootstrapData();

                    this.$nextTick(() => {
                        if (
                            this.barberId &&
                            this.serviceId &&
                            this.selectedDate
                        ) {
                            this.loadSlots(true);
                        }
                    });
                },

                async loadBootstrapData() {
                    if (!this.dataEndpoint) return;

                    this.loadingBootstrap = true;
                    this.bootstrapError = '';

                    try {
                        const response = await fetch(
                            this.dataEndpoint,
                            {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                cache: 'no-store',
                            }
                        );

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload?.ok !== true) {
                            throw new Error(
                                payload?.message ||
                                'دریافت اطلاعات نوبت دستی ناموفق بود.'
                            );
                        }

                        const data = payload.data || {};

                        this.barbers = Array.isArray(data.barbers)
                            ? data.barbers
                            : [];

                        this.services = Array.isArray(data.services)
                            ? data.services
                            : [];
                    } catch (error) {
                        console.error(error);
                        this.bootstrapError =
                            error?.message ||
                            'خطا در دریافت اطلاعات نوبت دستی.';
                    } finally {
                        this.loadingBootstrap = false;
                    }
                },

                persianDigits(value) {
                    return String(value ?? '')
                        .replace(
                            /\d/g,
                            digit => '۰۱۲۳۴۵۶۷۸۹'[digit]
                        );
                },

                parseIso(value) {
                    const [
                        year,
                        month,
                        day
                    ] = String(value)
                        .split('-')
                        .map(Number);

                    if (
                        !year ||
                        !month ||
                        !day
                    ) {
                        return null;
                    }

                    return new Date(
                        Date.UTC(
                            year,
                            month - 1,
                            day,
                            12
                        )
                    );
                },

                toIso(date) {
                    return [
                        date.getUTCFullYear(),
                        String(
                            date.getUTCMonth() + 1
                        ).padStart(2, '0'),
                        String(
                            date.getUTCDate()
                        ).padStart(2, '0'),
                    ].join('-');
                },

                addDays(iso, amount) {
                    const date =
                        this.parseIso(iso);

                    if (!date) {
                        return '';
                    }

                    date.setUTCDate(
                        date.getUTCDate() + amount
                    );

                    return this.toIso(date);
                },

                jalaliParts(iso) {
                    const date =
                        this.parseIso(iso);

                    if (!date) {
                        return null;
                    }

                    const parts =
                        new Intl.DateTimeFormat(
                            'fa-IR-u-ca-persian-nu-latn',
                            {
                                year: 'numeric',
                                month: 'numeric',
                                day: 'numeric',
                                timeZone: 'UTC',
                            }
                        ).formatToParts(date);

                    const result = {};

                    parts.forEach(part => {
                        if (
                            ['year', 'month', 'day']
                                .includes(part.type)
                        ) {
                            result[part.type] =
                                Number(part.value);
                        }
                    });

                    return result;
                },

                jalaliWeekday(iso) {
                    const date =
                        this.parseIso(iso);

                    if (!date) {
                        return 0;
                    }

                    return (
                        date.getUTCDay() + 1
                    ) % 7;
                },

                jalaliDate(iso) {
                    if (!iso) {
                        return '';
                    }

                    const parts =
                        this.jalaliParts(iso);

                    if (!parts) {
                        return '';
                    }

                    const weekdays = [
                        'شنبه',
                        'یکشنبه',
                        'دوشنبه',
                        'سه‌شنبه',
                        'چهارشنبه',
                        'پنجشنبه',
                        'جمعه',
                    ];

                    return (
                        `${weekdays[this.jalaliWeekday(iso)]} ` +
                        `${this.jalaliMonths[parts.month - 1]} ` +
                        `${this.persianDigits(parts.day)} ` +
                        `${this.persianDigits(parts.year)}`
                    );
                },

                findMonthStart(iso) {
                    const target =
                        this.jalaliParts(iso);

                    if (!target) {
                        return this.todayIso;
                    }

                    let cursor = iso;

                    for (let i = 0; i < 370; i++) {
                        const current =
                            this.jalaliParts(cursor);

                        if (
                            current &&
                            current.year === target.year &&
                            current.month === target.month &&
                            current.day === 1
                        ) {
                            return cursor;
                        }

                        cursor =
                            this.addDays(
                                cursor,
                                -1
                            );
                    }

                    return iso;
                },

                initCalendar() {
                    this.calendarAnchorIso =
                        this.findMonthStart(
                            this.selectedDate ||
                            this.todayIso
                        );
                },

                calendarTitle() {
                    if (!this.calendarAnchorIso) {
                        return '';
                    }

                    const parts =
                        this.jalaliParts(
                            this.calendarAnchorIso
                        );

                    return this.jalaliMonths[
                    parts.month - 1
                        ];
                },

                calendarYear() {
                    if (!this.calendarAnchorIso) {
                        return '';
                    }

                    return this.persianDigits(
                        this.jalaliParts(
                            this.calendarAnchorIso
                        ).year
                    );
                },

                calendarCells() {
                    if (!this.calendarAnchorIso) {
                        return [];
                    }

                    const first =
                        this.jalaliParts(
                            this.calendarAnchorIso
                        );

                    const offset =
                        this.jalaliWeekday(
                            this.calendarAnchorIso
                        );

                    const cells = [];

                    for (
                        let i = 0;
                        i < offset;
                        i++
                    ) {
                        cells.push(null);
                    }

                    let cursor =
                        this.calendarAnchorIso;

                    for (
                        let i = 0;
                        i < 31;
                        i++
                    ) {
                        const current =
                            this.jalaliParts(cursor);

                        if (
                            !current ||
                            current.year !== first.year ||
                            current.month !== first.month
                        ) {
                            break;
                        }

                        cells.push({
                            iso: cursor,
                            day: current.day,

                            today:
                                cursor === this.todayIso,

                            selected:
                                cursor === this.selectedDate,

                            past:
                                cursor < this.todayIso,
                        });

                        cursor =
                            this.addDays(
                                cursor,
                                1
                            );
                    }

                    return cells;
                },

                previousMonth() {
                    const previous =
                        this.addDays(
                            this.calendarAnchorIso,
                            -1
                        );

                    this.calendarAnchorIso =
                        this.findMonthStart(
                            previous
                        );
                },

                nextMonth() {
                    const next =
                        this.addDays(
                            this.calendarAnchorIso,
                            32
                        );

                    this.calendarAnchorIso =
                        this.findMonthStart(
                            next
                        );
                },

                openCalendar() {
                    this.initCalendar();

                    this.calendarOpen = true;
                },

                selectDate(day) {
                    if (
                        !day ||
                        day.past
                    ) {
                        return;
                    }

                    this.selectedDate =
                        day.iso;

                    this.selectedTime = '';

                    this.calendarOpen = false;

                    this.loadSlots();
                },

                goToday() {
                    this.selectedDate =
                        this.todayIso;

                    this.selectedTime = '';

                    this.initCalendar();

                    this.calendarOpen = false;

                    this.loadSlots();
                },

                get quickDates() {
                    return Array.from(
                        { length: 7 },
                        (_, index) =>
                            this.addDays(
                                this.todayIso,
                                index
                            )
                    );
                },

                quickDate(iso) {
                    this.selectedDate = iso;
                    this.selectedTime = '';
                    this.loadSlots();
                },

                dateShort(iso) {
                    const date =
                        this.jalaliParts(iso);

                    if (!date) {
                        return '';
                    }

                    return (
                        `${this.persianDigits(date.day)} ` +
                        `${this.jalaliMonths[date.month - 1].slice(0, 3)}`
                    );
                },

                dateWeekday(iso) {
                    const names = [
                        'ش',
                        'ی',
                        'د',
                        'س',
                        'چ',
                        'پ',
                        'ج',
                    ];

                    return names[
                        this.jalaliWeekday(iso)
                        ];
                },

                get customerReady() {
                    const nameReady =
                        String(this.customerName || '').trim().length >= 2;

                    const phone =
                        this.normalizePhone(this.customerPhone);

                    return (
                        nameReady &&
                        /^09\d{9}$/.test(phone)
                    );
                },

                get submitBlockReason() {
                    if (!this.manualConfirmed) {
                        return 'تأیید ثبت نوبت دستی را فعال کنید.';
                    }

                    if (String(this.customerName || '').trim().length < 2) {
                        return 'نام و نام خانوادگی مشتری را وارد کنید.';
                    }

                    if (!this.customerReady) {
                        return 'شماره موبایل مشتری را کامل و معتبر وارد کنید.';
                    }

                    if (!this.barberId) {
                        return 'یک آرایشگر انتخاب کنید.';
                    }

                    if (!this.serviceId) {
                        return 'یک خدمت انتخاب کنید.';
                    }

                    if (!this.selectedDate) {
                        return 'تاریخ نوبت را انتخاب کنید.';
                    }

                    if (!this.selectedTime) {
                        return 'یک ساعت آزاد انتخاب کنید.';
                    }

                    if (!this.selectedSlot || !this.slotAvailable(this.selectedSlot)) {
                        return 'این ساعت دیگر در دسترس نیست؛ یک ساعت آزاد دیگر انتخاب کنید.';
                    }

                    return '';
                },

                normalizePhone(value) {
                    return String(value || '')
                        .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                        .replace(/[\s\-\(\)]/g, '');
                },

                get selectedBarber() {
                    return this.barbers.find(
                        barber =>
                            String(barber.id) ===
                            String(this.barberId)
                    ) || null;
                },

                get selectedService() {
                    return this.services.find(
                        service =>
                            String(service.id) ===
                            String(this.serviceId)
                    ) || null;
                },

                selectBarber(id) {
                    this.barberId =
                        String(id);

                    this.selectedTime = '';

                    this.loadSlots();
                },

                selectService(id) {
                    this.serviceId =
                        String(id);

                    this.selectedTime = '';

                    this.loadSlots();
                },

                slotStart(slot) {
                    return typeof slot === 'string'
                        ? slot
                        : slot?.start || '';
                },

                slotEnd(slot) {
                    return typeof slot === 'string'
                        ? ''
                        : slot?.end || '';
                },

                slotAvailable(slot) {
                    return (
                        typeof slot === 'string' ||
                        slot?.available !== false
                    );
                },

                formatTime(value) {
                    return this.persianDigits(
                        String(value || '')
                            .slice(0, 5)
                    );
                },

                formatPrice(value) {
                    return (
                        new Intl.NumberFormat(
                            'fa-IR'
                        ).format(
                            Number(value || 0)
                        )
                    ) + ' تومان';
                },

                initials(name) {
                    if (!name) {
                        return '؟';
                    }

                    return name
                        .trim()
                        .split(/\s+/)
                        .slice(0, 2)
                        .map(
                            part =>
                                part.charAt(0)
                        )
                        .join('')
                        .toUpperCase();
                },

                get availableSlots() {
                    return this.slots.filter(
                        slot =>
                            this.slotAvailable(slot)
                    );
                },

                get selectedSlot() {
                    return this.slots.find(
                        slot =>
                            this.slotStart(slot)
                                .slice(0, 5) ===
                            String(
                                this.selectedTime
                            ).slice(0, 5)
                    ) || null;
                },

                get canSubmit() {
                    return Boolean(
                        this.manualConfirmed &&
                        this.customerReady &&
                        this.barberId &&
                        this.serviceId &&
                        this.selectedDate &&
                        this.selectedTime &&
                        this.selectedSlot &&
                        this.slotAvailable(
                            this.selectedSlot
                        )
                    );
                },

                async loadSlots(
                    restoreOldTime = false
                ) {
                    if (
                        !this.barberId ||
                        !this.serviceId ||
                        !this.selectedDate
                    ) {
                        this.slots = [];
                        this.schedule = {
                            day_name: '',
                            status: 'not_configured',
                            intervals: [],
                            breaks: [],
                        };
                        this.slotError = '';
                        return;
                    }

                    if (
                        this.selectedDate <
                        this.todayIso
                    ) {
                        this.selectedDate =
                            this.todayIso;
                    }

                    if (this.abortController) {
                        this.abortController.abort();
                    }

                    this.abortController =
                        new AbortController();

                    this.loadingSlots = true;
                    this.slotError = '';

                    if (!restoreOldTime) {
                        this.selectedTime = '';
                    }

                    try {
                        const url = new URL(
                            @js(route('salon.bookings.availability')),
                            window.location.origin
                        );

                        url.searchParams.set(
                            'barber_id',
                            this.barberId
                        );

                        url.searchParams.set(
                            'service_id',
                            this.serviceId
                        );

                        url.searchParams.set(
                            'booking_date',
                            this.selectedDate
                        );

                        const response =
                            await fetch(
                                url.toString(),
                                {
                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },

                                    signal:
                                    this.abortController.signal,
                                }
                            );

                        const data =
                            await response
                                .json()
                                .catch(
                                    () => ({})
                                );

                        if (!response.ok) {
                            throw new Error(
                                data.message ||
                                'دریافت زمان‌های آزاد ناموفق بود.'
                            );
                        }

                        this.schedule =
                            data.schedule || {
                                day_name: '',
                                status:
                                    'not_configured',
                                intervals: [],
                                breaks: [],
                            };

                        this.slots =
                            Array.isArray(
                                data.slots
                            )
                                ? data.slots
                                : [];

                        if (
                            restoreOldTime &&
                            this.selectedTime
                        ) {
                            const exists =
                                this.slots.some(
                                    slot =>
                                        this.slotStart(
                                            slot
                                        ).slice(0, 5) ===
                                        String(
                                            this.selectedTime
                                        ).slice(0, 5)
                                        &&
                                        this.slotAvailable(
                                            slot
                                        )
                                );

                            if (!exists) {
                                this.selectedTime = '';
                            }
                        }

                    } catch (error) {
                        if (
                            error?.name ===
                            'AbortError'
                        ) {
                            return;
                        }

                        this.slots = [];
                        this.selectedTime = '';

                        this.slotError =
                            error?.message ||
                            'خطا در دریافت زمان‌های آزاد.';
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                submitForm(event) {
                    if (!this.canSubmit) {
                        event.preventDefault();
                        this.submitError = this.submitBlockReason || 'اطلاعات نوبت را کامل کنید.';
                        return;
                    }

                    this.submitError = '';
                    this.submitting = true;
                },
            };
        }
    </script>


    <div
        x-data="salonManualBooking()" data-manual-booking-endpoint="{{ route('salon.bookings.manual-data') }}"
        x-init="init()"
        dir="rtl"
        class="salon-manual-booking mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8"
    >


        {{-- HEADER --}}
        <header class="salon-manual-booking-header mb-6">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <a
                        href="{{ route('salon.bookings.index') }}"
                        class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
                    >
                        ← بازگشت به نوبت‌ها
                    </a>

                    <div class="text-[10px] font-black tracking-[0.2em] text-accent-600">
                        MANUAL BOOKING
                    </div>

                    <h1 class="mt-1 text-2xl font-black text-content sm:text-3xl">
                        ثبت نوبت دستی
                    </h1>

                    <p class="mt-2 max-w-2xl text-xs leading-6 text-content-muted sm:text-sm">
                        نام و موبایل مشتری را ثبت کنید؛ زمان نهایی از همان availability موتور اصلی سیستم محاسبه می‌شود و هیچ حساب مشتری ساخته نمی‌شود.
                    </p>

                </div>

            </div>

        </header>


        {{-- ERRORS --}}
        @if($errors->any())

            <div class="mb-6 rounded-2xl border border-danger-100 bg-danger-50 p-4">

                <div class="text-xs font-black text-danger-800">
                    ثبت نوبت انجام نشد
                </div>

                <div class="mt-2 space-y-1">

                    @foreach($errors->all() as $error)

                        <div class="text-[10px] font-bold leading-5 text-danger-700">
                            • {{ $error }}
                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        <form
            action="{{ route('salon.bookings.store-manual') }}"
            method="POST"
            @submit="submitForm($event)"
        >

            @csrf

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">


                {{-- MAIN --}}
                <main class="space-y-5">


                    {{-- CUSTOMER --}}
                    <section class="rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="border-b border-border p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-content text-sm font-black text-background">
                                    ۱
                                </div>

                                <div>
                                    <div class="text-[10px] font-black tracking-[0.2em] text-content-faint">
                                        WALK-IN
                                    </div>

                                    <h2 class="mt-1 text-base font-black text-content">
                                        اطلاعات مشتری
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="mb-4 rounded-2xl border border-accent-200 bg-accent-50/70 p-4 dark:border-accent-800/30 dark:bg-accent-900/10">
                                <div class="text-xs font-black text-content">
                                    نوبت دستی
                                </div>

                                <p class="mt-1 text-[10px] leading-6 text-content-muted">
                                    فقط نام و شماره موبایل مشتری ثبت می‌شود. این نوبت هیچ حساب کاربری برای مشتری ایجاد یا انتخاب نمی‌کند.
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-black text-content-muted">
                                        نام و نام خانوادگی
                                    </span>

                                    <input
                                        type="text"
                                        name="customer_name"
                                        x-model="customerName"
                                        autocomplete="name"
                                        placeholder="مثلاً علی رضایی"
                                        class="h-12 w-full rounded-xl border border-border bg-background px-4 text-xs font-bold text-content outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-500/10"
                                    >

                                    @error('customer_name')
                                        <span class="mt-2 block text-[10px] font-bold text-danger-600">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-black text-content-muted">
                                        شماره موبایل
                                    </span>

                                    <input
                                        type="tel"
                                        name="customer_phone"
                                        x-model="customerPhone"
                                        inputmode="tel"
                                        dir="ltr"
                                        autocomplete="tel"
                                        placeholder="۰۹۱۲۱۲۳۴۵۶۷"
                                        class="h-12 w-full rounded-xl border border-border bg-background px-4 text-xs font-bold text-content outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-500/10"
                                    >

                                    @error('customer_phone')
                                        <span class="mt-2 block text-[10px] font-bold text-danger-600">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </label>
                            </div>

                            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-border bg-background p-4">
                                <input
                                    type="checkbox"
                                    name="manual_confirmed"
                                    value="1"
                                    x-model="manualConfirmed"
                                    class="mt-0.5 h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500/20"
                                >

                                <span>
                                    <span class="block text-xs font-black text-content">
                                        ثبت این نوبت به‌صورت دستی را تأیید می‌کنم
                                    </span>

                                    <span class="mt-1 block text-[10px] leading-5 text-content-muted">
                                        مشتری این نوبت حساب NOBAT ندارد و اطلاعات بالا فقط برای سوابق سالن ثبت می‌شود.
                                    </span>
                                </span>
                            </label>

                            @error('manual_confirmed')
                                <div class="mt-2 text-[10px] font-bold text-danger-600">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </section>


                    {{-- BARBER --}}
                    <section class="rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="border-b border-border p-5">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-content text-sm font-black text-background">
                                    ۲
                                </div>

                                <div>

                                    <div class="text-[10px] font-black tracking-[0.2em] text-content-faint">
                                        BARBER
                                    </div>

                                    <h2 class="mt-1 text-base font-black text-content">
                                        آرایشگر
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="p-5">

                            @if($barbers->count())

                                <div class="grid gap-2 sm:grid-cols-2">

                                    @foreach($barbers as $barber)

                                        <button
                                            type="button"
                                            @click="selectBarber({{ $barber->id }})"
                                            class="flex items-center gap-3 rounded-2xl border p-3 text-right transition"
                                            :class="String(barberId) === '{{ $barber->id }}'
                                            ? 'border-content bg-content text-background shadow-lg'
                                            : 'border-border bg-surface hover:border-content-faint hover:bg-background'"
                                        >

                                            @if($barber->image_path)

                                                <img
                                                    src="{{ Storage::url($barber->image_path) }}"
                                                    alt="{{ $barber->name }}"
                                                    class="h-12 w-12 shrink-0 rounded-xl object-cover"
                                                >

                                            @else

                                                <div
                                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xs font-black"
                                                    :class="String(barberId) === '{{ $barber->id }}'
                                                    ? 'bg-white/10 text-white'
                                                    : 'bg-background-soft text-content-muted'"
                                                >
                                                    {{ mb_substr($barber->name, 0, 1) }}
                                                </div>

                                            @endif

                                            <div class="min-w-0 flex-1">

                                                <div class="truncate text-xs font-black">
                                                    {{ $barber->name }}
                                                </div>

                                                <div class="mt-1 text-[10px] opacity-60">
                                                    فعال
                                                </div>

                                            </div>

                                            <span
                                                x-show="String(barberId) === '{{ $barber->id }}'"
                                                x-cloak
                                                class="text-sm"
                                            >
                                            ✓
                                        </span>

                                        </button>

                                    @endforeach

                                </div>

                            @else

                                <div class="rounded-2xl border border-dashed border-border bg-background p-7 text-center text-xs font-bold text-content-muted">
                                    آرایشگر فعالی ثبت نشده است.
                                </div>

                            @endif

                            <input
                                type="hidden"
                                name="barber_id"
                                :value="barberId"
                            >

                        </div>

                    </section>


                    {{-- SERVICE --}}
                    <section class="rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="border-b border-border p-5">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-content text-sm font-black text-background">
                                    ۳
                                </div>

                                <div>

                                    <div class="text-[10px] font-black tracking-[0.2em] text-content-faint">
                                        SERVICE
                                    </div>

                                    <h2 class="mt-1 text-base font-black text-content">
                                        خدمت
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="p-5">

                            @if($services->count())

                                <div class="space-y-2">

                                    @foreach($services as $service)

                                        <button
                                            type="button"
                                            @click="selectService({{ $service->id }})"
                                            class="flex w-full items-center gap-3 rounded-2xl border p-3 text-right transition"
                                            :class="String(serviceId) === '{{ $service->id }}'
                                            ? 'border-content bg-content text-background shadow-lg'
                                            : 'border-border bg-surface hover:border-content-faint hover:bg-background'"
                                        >

                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                                :class="String(serviceId) === '{{ $service->id }}'
                                                ? 'bg-white/10'
                                                : 'bg-background-soft'"
                                            >
                                                ✦
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <div class="truncate text-xs font-black">
                                                    {{ $service->name }}
                                                </div>

                                                <div class="mt-1 text-[10px] opacity-60">
                                                    {{ $service->duration_minutes }} دقیقه
                                                </div>

                                            </div>

                                            <div class="shrink-0 text-left">

                                                <div class="text-xs font-black">
                                                    {{ number_format($service->price) }}
                                                </div>

                                                <div class="text-[9px] opacity-50">
                                                    تومان
                                                </div>

                                            </div>

                                        </button>

                                    @endforeach

                                </div>

                            @else

                                <div class="rounded-2xl border border-dashed border-border bg-background p-7 text-center text-xs font-bold text-content-muted">
                                    خدمت فعالی ثبت نشده است.
                                </div>

                            @endif

                            <input
                                type="hidden"
                                name="service_id"
                                :value="serviceId"
                            >

                        </div>

                    </section>


                    {{-- DATE / TIME --}}
                    <section class="rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="border-b border-border p-5">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-content text-sm font-black text-background">
                                    ۴
                                </div>

                                <div>

                                    <div class="text-[10px] font-black tracking-[0.2em] text-content-faint">
                                        SCHEDULE
                                    </div>

                                    <h2 class="mt-1 text-base font-black text-content">
                                        تاریخ و ساعت
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="p-5">


                            {{-- Persian Date Picker --}}
                            <div class="relative">

                                <button
                                    type="button"
                                    @click="openCalendar()"
                                    class="flex min-h-[70px] w-full items-center justify-between rounded-2xl border border-border bg-background px-4 text-right transition hover:border-accent-400"
                                >

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600 dark:bg-accent-900/20">
                                            ◫
                                        </div>

                                        <div>

                                            <div class="text-[10px] font-black text-content-faint">
                                                تاریخ نوبت
                                            </div>

                                            <div
                                                class="mt-1 text-sm font-black text-content"
                                                x-text="jalaliDate(selectedDate)"
                                            ></div>

                                        </div>

                                    </div>

                                    <span class="text-content-faint">
                                    ⌄
                                </span>

                                </button>


                                <div
                                    x-show="calendarOpen"
                                    x-cloak
                                    @click.outside="calendarOpen = false"
                                    x-transition
                                    class="absolute inset-x-0 top-full z-50 mt-2 overflow-hidden rounded-3xl border border-border bg-surface shadow-2xl"
                                >

                                    <div class="border-b border-border p-4">

                                        <div class="flex items-center justify-between">

                                            <button
                                                type="button"
                                                @click="previousMonth()"
                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-background text-content"
                                            >
                                                →
                                            </button>

                                            <div class="text-center">

                                                <div
                                                    class="text-sm font-black text-content"
                                                    x-text="calendarTitle()"
                                                ></div>

                                                <div
                                                    class="mt-1 text-[10px] text-content-faint"
                                                    x-text="calendarYear()"
                                                ></div>

                                            </div>

                                            <button
                                                type="button"
                                                @click="nextMonth()"
                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-background text-content"
                                            >
                                                ←
                                            </button>

                                        </div>

                                    </div>


                                    <div class="grid grid-cols-7 gap-1 px-3 pt-3">

                                        <template
                                            x-for="day in weekDays"
                                            :key="day"
                                        >

                                            <div
                                                class="py-2 text-center text-[10px] font-black text-content-faint"
                                                x-text="day"
                                            ></div>

                                        </template>

                                    </div>


                                    <div class="grid grid-cols-7 gap-1 p-3">

                                        <template
                                            x-for="(day, index) in calendarCells()"
                                            :key="index"
                                        >

                                            <div class="aspect-square">

                                                <template x-if="day">

                                                    <button
                                                        type="button"
                                                        :disabled="day.past"
                                                        @click="selectDate(day)"
                                                        class="flex h-full w-full items-center justify-center rounded-xl text-xs font-black transition"
                                                        :class="
                                                        day.past
                                                            ? 'cursor-not-allowed text-content-faint/20'
                                                            : day.selected
                                                                ? 'bg-accent-600 text-white shadow-lg'
                                                                : day.today
                                                                    ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                                                                    : 'text-content hover:bg-background'
                                                    "
                                                    >
                                                    <span
                                                        x-text="persianDigits(day.day)"
                                                    ></span>
                                                    </button>

                                                </template>

                                            </div>

                                        </template>

                                    </div>


                                    <div class="border-t border-border p-3">

                                        <button
                                            type="button"
                                            @click="goToday()"
                                            class="w-full rounded-xl bg-background py-3 text-xs font-black text-content transition hover:bg-accent-50"
                                        >
                                            انتخاب امروز
                                        </button>

                                    </div>

                                </div>

                            </div>


                            <input
                                type="hidden"
                                name="booking_date"
                                x-model="selectedDate"
                            >

                            <input
                                type="hidden"
                                name="start_time"
                                x-model="selectedTime"
                            >


                            {{-- Quick dates --}}
                            <div class="salon-manual-quick-dates mt-4 flex min-w-0 gap-2 overflow-x-auto pb-1">

                                <template
                                    x-for="date in quickDates"
                                    :key="date"
                                >

                                    <button
                                        type="button"
                                        @click="quickDate(date)"
                                        class="min-w-[76px] rounded-xl border px-3 py-3 text-center transition"
                                        :class="String(selectedDate) === String(date)
                                        ? 'border-content bg-content text-background'
                                        : 'border-border bg-background text-content'"
                                    >

                                        <div
                                            class="text-[10px] font-black"
                                            x-text="dateWeekday(date)"
                                        ></div>

                                        <div
                                            class="mt-1 text-[10px] font-bold"
                                            x-text="dateShort(date)"
                                        ></div>

                                    </button>

                                </template>

                            </div>


                            {{-- Working Hours --}}
                            <template x-if="schedule.status === 'open'">

                                <div class="mt-5 rounded-2xl border border-border bg-background p-4">

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                        <div>

                                            <div class="text-[9px] font-black tracking-[0.16em] text-content-faint">
                                                SALON HOURS
                                            </div>

                                            <div class="mt-1 text-xs font-black text-content">
                                                ساعات کاری
                                                <span
                                                    class="text-accent-600"
                                                    x-text="schedule.day_name"
                                                ></span>
                                            </div>

                                        </div>

                                        <div class="flex flex-wrap gap-2">

                                            <template
                                                x-if="schedule.breaks && schedule.breaks.length"
                                            >
                                                <div class="w-full basis-full rounded-2xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800/40 dark:bg-amber-900/10">
                                                    <div class="text-[9px] font-black text-amber-800 dark:text-amber-300">
                                                        زمان‌های استراحت
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap gap-2 text-[9px] font-bold text-amber-900 dark:text-amber-200">
                                                        <template x-for="(breakItem, breakIndex) in schedule.breaks" :key="breakItem.start + '-' + breakItem.end + '-' + breakIndex">
                                                            <span>
                                                                <span x-text="formatTime(breakItem.start)"></span>
                                                                <span class="mx-1">تا</span>
                                                                <span x-text="formatTime(breakItem.end)"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="mt-1 text-[8px] font-semibold text-amber-700 dark:text-amber-400">
                                                        این بازه‌ها برای نوبت دستی هم قابل رزرو نیستند.
                                                    </div>
                                                </div>
                                            </template>

                                            <template
                                                x-for="(range, index) in schedule.intervals"
                                                :key="index"
                                            >

                                            <span class="rounded-xl border border-border bg-surface px-3 py-2 text-[10px] font-black text-content">

                                                <span
                                                    x-text="formatTime(range.start)"
                                                ></span>

                                                <span class="mx-1 text-content-faint">
                                                    تا
                                                </span>

                                                <span
                                                    x-text="formatTime(range.end)"
                                                ></span>

                                            </span>

                                            </template>

                                        </div>

                                    </div>

                                </div>

                            </template>


                            <template x-if="schedule.status === 'closed'">

                                <div class="mt-5 rounded-2xl border border-danger-200 bg-danger-50 p-4 dark:border-danger-800/40 dark:bg-danger-900/10">

                                    <div class="flex items-start gap-3">

                                        <div class="text-lg">
                                            ◷
                                        </div>

                                        <div>

                                            <div class="text-xs font-black text-danger-700 dark:text-danger-300">
                                                سالن در این روز تعطیل است
                                            </div>

                                            <div class="mt-1 text-[10px] leading-5 text-danger-600/70">
                                                <span x-text="schedule.day_name"></span>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </template>


                            <template x-if="schedule.status === 'not_configured'">

                                <div class="mt-5 rounded-2xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-800/40 dark:bg-warning-900/10">

                                    <div class="flex items-start gap-3">

                                        <div class="text-lg">
                                            ⚠
                                        </div>

                                        <div>

                                            <div class="text-xs font-black text-warning-800 dark:text-warning-300">
                                                ساعت کاری این روز تنظیم نشده است
                                            </div>

                                            <div class="mt-1 text-[10px] leading-5 text-warning-700/70">
                                                برنامه کاری
                                                <span
                                                    class="font-black"
                                                    x-text="schedule.day_name"
                                                ></span>
                                                تنظیم نشده است.
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </template>


                            {{-- Slots --}}
                            <div class="mt-6">

                                <div class="flex items-end justify-between gap-3">

                                    <div>

                                        <div class="text-sm font-black text-content">
                                            زمان قابل رزرو
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            بر اساس خدمت و مدت آن
                                        </div>

                                    </div>

                                    <div
                                        x-show="availableSlots.length"
                                        x-cloak
                                        class="rounded-full bg-success-50 px-3 py-1.5 text-[10px] font-black text-success-700"
                                    >
                                        <span x-text="persianDigits(availableSlots.length)"></span>
                                        زمان آزاد
                                    </div>

                                </div>


                                <template x-if="!barberId || !serviceId">

                                    <div class="mt-4 rounded-2xl border border-dashed border-border bg-background p-7 text-center">

                                        <div class="text-xl">
                                            ◷
                                        </div>

                                        <div class="mt-2 text-xs font-black text-content">
                                            ابتدا آرایشگر و خدمت را انتخاب کنید.
                                        </div>

                                    </div>

                                </template>


                                <template x-if="loadingSlots">

                                    <div class="mt-4 rounded-2xl border border-border bg-background p-8 text-center">

                                        <div class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-border border-t-content"></div>

                                        <div class="mt-3 text-xs font-bold text-content-muted">
                                            در حال محاسبه availability...
                                        </div>

                                    </div>

                                </template>


                                <template x-if="slotError && !loadingSlots">

                                    <div class="mt-4 rounded-2xl border border-danger-200 bg-danger-50 p-4">

                                        <div class="text-xs font-black text-danger-700">
                                            خطا در دریافت زمان‌ها
                                        </div>

                                        <div
                                            class="mt-1 text-[10px] leading-5 text-danger-600"
                                            x-text="slotError"
                                        ></div>

                                        <button
                                            type="button"
                                            @click="loadSlots()"
                                            class="mt-3 rounded-xl bg-surface px-4 py-2 text-[10px] font-black text-content"
                                        >
                                            تلاش دوباره
                                        </button>

                                    </div>

                                </template>


                                <template
                                    x-if="
                                    !loadingSlots &&
                                    !slotError &&
                                    barberId &&
                                    serviceId &&
                                    schedule.status === 'open' &&
                                    availableSlots.length
                                "
                                >

                                    <div class="salon-manual-slot-grid mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6">

                                        <template
                                            x-for="slot in slots"
                                            :key="slotStart(slot)"
                                        >

                                            <button
                                                type="button"
                                                x-show="slotAvailable(slot)"
                                                @click="selectedTime = slotStart(slot)"
                                                class="rounded-xl border px-2 py-3 text-center transition"
                                                :class="
                                                selectedTime.slice(0,5) === slotStart(slot).slice(0,5)
                                                    ? 'border-content bg-content text-background shadow-lg'
                                                    : 'border-border bg-background text-content hover:border-content-faint'
                                            "
                                            >

                                                <div
                                                    class="text-sm font-black"
                                                    x-text="formatTime(slotStart(slot))"
                                                ></div>

                                                <div
                                                    class="mt-1 text-[9px] opacity-50"
                                                    x-text="slotEnd(slot) ? 'تا ' + formatTime(slotEnd(slot)) : ''"
                                                ></div>

                                            </button>

                                        </template>

                                    </div>

                                </template>


                                <template
                                    x-if="
                                    !loadingSlots &&
                                    !slotError &&
                                    barberId &&
                                    serviceId &&
                                    schedule.status === 'open' &&
                                    !availableSlots.length
                                "
                                >

                                    <div class="mt-4 rounded-2xl border border-warning-200 bg-warning-50 p-5 text-center">

                                        <div class="text-xs font-black text-warning-800">
                                            زمان آزادی باقی نمانده است
                                        </div>

                                        <div class="mt-1 text-[10px] text-warning-700/70">
                                            ممکن است تمام ظرفیت‌ها رزرو شده باشند.
                                        </div>

                                    </div>

                                </template>

                            </div>

                        </div>

                    </section>


                    {{-- NOTES --}}
                    <section class="rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="border-b border-border p-5">

                            <div class="flex items-center justify-between">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-background-soft text-sm font-black text-content-muted">
                                        ۵
                                    </div>

                                    <div>

                                        <div class="text-[10px] font-black tracking-[0.2em] text-content-faint">
                                            NOTES
                                        </div>

                                        <h2 class="mt-1 text-base font-black text-content">
                                            توضیحات
                                        </h2>

                                    </div>

                                </div>

                                <span class="rounded-full bg-background px-3 py-1.5 text-[10px] font-black text-content-faint">
                                اختیاری
                            </span>

                            </div>

                        </div>


                        <div class="p-5">

                        <textarea
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            placeholder="مثلاً مشتری درخواست کرده..."
                            class="w-full resize-none rounded-2xl border border-border bg-background p-4 text-xs leading-7 text-content outline-none placeholder:text-content-faint focus:border-accent-500 focus:ring-4 focus:ring-accent-500/10"
                        >{{ old('notes') }}</textarea>

                        </div>

                    </section>

                </main>


                {{-- SUMMARY --}}
                <aside class="xl:sticky xl:top-6 xl:h-fit">

                    <section class="overflow-hidden rounded-3xl border border-border bg-surface shadow-card">

                        <div class="bg-content p-5 text-background">

                            <div class="text-[10px] font-black tracking-[0.2em] opacity-50">
                                BOOKING SUMMARY
                            </div>

                            <h2 class="mt-1 text-lg font-black">
                                خلاصه نوبت
                            </h2>

                        </div>


                        <div class="space-y-3 p-5">

                            <div class="rounded-2xl bg-background p-4">

                                <div class="text-[9px] text-content-faint">
                                    مشتری
                                </div>

                                <div
                                    class="mt-1 text-sm font-black text-content"
                                    x-text="selectedCustomer?.name || 'انتخاب نشده'"
                                ></div>

                                <div
                                    x-show="selectedCustomer?.phone"
                                    x-cloak
                                    class="mt-1 text-[10px] text-content-muted"
                                    x-text="selectedCustomer?.phone"
                                ></div>

                            </div>


                            <div class="rounded-2xl border border-border p-4">

                                <div class="text-[9px] text-content-faint">
                                    آرایشگر
                                </div>

                                <div
                                    class="mt-1 text-sm font-black text-content"
                                    x-text="selectedBarber?.name || 'انتخاب نشده'"
                                ></div>

                            </div>


                            <div class="rounded-2xl border border-border p-4">

                                <div class="text-[9px] text-content-faint">
                                    خدمت
                                </div>

                                <div
                                    class="mt-1 text-sm font-black text-content"
                                    x-text="selectedService?.name || 'انتخاب نشده'"
                                ></div>

                                <div
                                    x-show="selectedService"
                                    x-cloak
                                    class="mt-1 text-[10px] text-content-muted"
                                    x-text="selectedService ? persianDigits(selectedService.duration) + ' دقیقه' : ''"
                                ></div>

                            </div>


                            <div class="rounded-2xl border border-border p-4">

                                <div class="text-[9px] text-content-faint">
                                    تاریخ
                                </div>

                                <div
                                    class="mt-1 text-sm font-black text-content"
                                    x-text="jalaliDate(selectedDate)"
                                ></div>

                            </div>


                            <div class="rounded-2xl border border-border p-4">

                                <div class="text-[9px] text-content-faint">
                                    ساعت
                                </div>

                                <div
                                    class="mt-1 text-sm font-black text-content"
                                    x-text="selectedTime ? formatTime(selectedTime) : 'انتخاب نشده'"
                                ></div>

                                <div
                                    x-show="selectedSlot?.end"
                                    x-cloak
                                    class="mt-1 text-[10px] text-content-faint"
                                    x-text="selectedSlot?.end ? 'پایان خدمت: ' + formatTime(selectedSlot.end) : ''"
                                ></div>

                            </div>


                            <div class="rounded-2xl bg-content p-4 text-background">

                                <div class="flex items-center justify-between gap-4">

                                <span class="text-[10px] font-bold opacity-50">
                                    مبلغ
                                </span>

                                    <span
                                        class="text-base font-black"
                                        x-text="selectedService ? formatPrice(selectedService.price) : '—'"
                                    ></span>

                                </div>

                            </div>


                            <div
                                class="rounded-2xl border p-4"
                                :class="
                                canSubmit
                                    ? 'border-success-200 bg-success-50'
                                    : 'border-border bg-background'
                            "
                            >

                                <div class="flex items-start gap-3">

                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black"
                                        :class="
                                        canSubmit
                                            ? 'bg-success-100 text-success-700'
                                            : 'bg-background-soft text-content-faint'
                                    "
                                        x-text="canSubmit ? '✓' : '…'"
                                    ></div>

                                    <div>

                                        <div
                                            class="text-xs font-black"
                                            :class="
                                            canSubmit
                                                ? 'text-success-700'
                                                : 'text-content'
                                        "
                                            x-text="canSubmit ? 'آماده ثبت نهایی' : 'اطلاعات ناقص است'"
                                        ></div>

                                        <div
                                            class="mt-1 text-[10px] leading-5"
                                            :class="
                                            canSubmit
                                                ? 'text-success-700/70'
                                                : 'text-content-faint'
                                        "
                                            x-text="
                                            canSubmit
                                                ? 'این نوبت به صورت تأییدشده ثبت می‌شود.'
                                                : 'مشتری، آرایشگر، خدمت، تاریخ و ساعت را کامل کنید.'
                                        "
                                        ></div>

                                    </div>

                                </div>

                                <div
                                    x-show="submitError"
                                    x-cloak
                                    x-text="submitError"
                                    class="mt-3 rounded-xl border border-danger-200 bg-danger-50 px-3 py-2 text-[10px] font-bold leading-5 text-danger-700"
                                    role="alert"
                                ></div>

                            </div>


                            <button
                                type="submit"
                                :disabled="submitting"
                                class="flex h-14 w-full items-center justify-center rounded-2xl text-sm font-black transition"
                                :class="
                                canSubmit && !submitting
                                    ? 'bg-accent-600 text-white shadow-lg shadow-accent-600/20 hover:-translate-y-0.5 hover:bg-accent-700'
                                    : 'border border-border bg-background-soft text-content-muted hover:border-accent-300 hover:text-content'
                            "
                            >
                            <span
                                x-text="submitting ? 'در حال ثبت...' : 'ثبت و تأیید نوبت'"
                            ></span>
                            </button>

                            <div class="pb-1 text-center text-[9px] leading-5 text-content-faint">
                                ثبت دستی بدون مرحله تأیید مجدد انجام می‌شود.
                            </div>

                        </div>

                    </section>

                </aside>

            </div>


        </form>

    </div>

@endsection
