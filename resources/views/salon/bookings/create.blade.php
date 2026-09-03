@extends('layouts.salon')

@section('title', 'ثبت نوبت دستی')

@section('content')

    @php
        $todayIso = now(config('app.timezone'))->toDateString();

        $servicesData = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration' => (int) $service->duration_minutes,
            ];
        })->values();

        $barbersData = $barbers->map(function ($barber) {
            return [
                'id' => $barber->id,
                'name' => $barber->name,
            ];
        })->values();

        $customersData = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ];
        })->values();
    @endphp


    <script>
        window.salonBookingConfig = {
            todayIso: @js($todayIso),

            availabilityUrl: @js(
                route('salon.bookings.availability')
            ),

            services: @js($servicesData),

            barbers: @js($barbersData),

            customers: @js($customersData),
        };


        function salonBookingPage() {

            const config =
                window.salonBookingConfig;


            return {

                /*
                |--------------------------------------------------------------------------
                | Data
                |--------------------------------------------------------------------------
                */

                todayIso:
                config.todayIso,

                availabilityUrl:
                config.availabilityUrl,

                services:
                config.services,

                barbers:
                config.barbers,

                customers:
                config.customers,


                /*
                |--------------------------------------------------------------------------
                | Selected
                |--------------------------------------------------------------------------
                */

                customerId: '',

                barberId: '',

                serviceId: '',

                selectedDate: '',

                selectedTime: '',


                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                slots: [],

                loadingSlots: false,

                slotError: '',


                /*
                |--------------------------------------------------------------------------
                | Calendar
                |--------------------------------------------------------------------------
                */

                viewDate: null,

                monthStart: null,

                calendarCells: [],

                monthTitle: '',

                selectedDateLabel: '',


                /*
                |--------------------------------------------------------------------------
                | Customer Search
                |--------------------------------------------------------------------------
                */

                customerSearch: '',


                /*
                |--------------------------------------------------------------------------
                | Init
                |--------------------------------------------------------------------------
                */

                init() {

                    this.viewDate =
                        this.parseIso(
                            this.todayIso
                        );


                    this.monthStart =
                        this.findMonthStart(
                            this.viewDate
                        );


                    this.refreshCalendar();


                    this.selectDateByIso(
                        this.todayIso
                    );

                },


                /*
                |--------------------------------------------------------------------------
                | Date
                |--------------------------------------------------------------------------
                */

                parseIso(iso) {

                    const parts =
                        String(iso)
                            .split('-')
                            .map(Number);


                    if (
                        parts.length !== 3 ||
                        parts.some(
                            value =>
                                Number.isNaN(value)
                        )
                    ) {
                        return new Date();
                    }


                    return new Date(
                        Date.UTC(
                            parts[0],
                            parts[1] - 1,
                            parts[2]
                        )
                    );

                },


                toIso(date) {

                    const year =
                        String(
                            date.getUTCFullYear()
                        ).padStart(
                            4,
                            '0'
                        );


                    const month =
                        String(
                            date.getUTCMonth() + 1
                        ).padStart(
                            2,
                            '0'
                        );


                    const day =
                        String(
                            date.getUTCDate()
                        ).padStart(
                            2,
                            '0'
                        );


                    return `${year}-${month}-${day}`;

                },


                addDays(date, days) {

                    const result =
                        new Date(date);


                    result.setUTCDate(
                        result.getUTCDate() + days
                    );


                    return result;

                },


                persianParts(date) {

                    const formatter =
                        new Intl.DateTimeFormat(
                            'fa-IR-u-ca-persian-nu-latn',
                            {
                                year: 'numeric',
                                month: 'numeric',
                                day: 'numeric',
                                timeZone: 'UTC',
                            }
                        );


                    const parts =
                        formatter.formatToParts(
                            date
                        );


                    const values = {};


                    parts.forEach(
                        part => {

                            if (
                                part.type !== 'literal'
                            ) {

                                values[part.type] =
                                    Number(
                                        part.value
                                    );

                            }

                        }
                    );


                    return values;

                },


                findMonthStart(date) {

                    let current =
                        new Date(date);


                    while (
                        this.persianParts(current).day !== 1
                        ) {

                        current =
                            this.addDays(
                                current,
                                -1
                            );

                    }


                    return current;

                },


                refreshCalendar() {

                    const current =
                        this.persianParts(
                            this.monthStart
                        );


                    const monthFormatter =
                        new Intl.DateTimeFormat(
                            'fa-IR-u-ca-persian',
                            {
                                year: 'numeric',
                                month: 'long',
                                timeZone: 'UTC',
                            }
                        );


                    this.monthTitle =
                        monthFormatter.format(
                            this.monthStart
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Saturday = 0
                    |--------------------------------------------------------------------------
                    */

                    const weekOffset =
                        (
                            this.monthStart.getUTCDay()
                            + 1
                        ) % 7;


                    const cells =
                        Array(
                            weekOffset
                        ).fill(null);


                    let cursor =
                        new Date(
                            this.monthStart
                        );


                    for (
                        let index = 0;
                        index < 42;
                        index++
                    ) {

                        const parts =
                            this.persianParts(
                                cursor
                            );


                        if (
                            parts.year ===
                            current.year &&
                            parts.month ===
                            current.month
                        ) {

                            const iso =
                                this.toIso(
                                    cursor
                                );


                            cells.push({

                                day:
                                parts.day,

                                iso:

                                iso,

                                disabled:
                                    iso <
                                    this.todayIso,

                                isToday:
                                    iso ===
                                    this.todayIso,

                            });

                        } else {

                            cells.push(null);

                        }


                        cursor =
                            this.addDays(
                                cursor,
                                1
                            );

                    }


                    this.calendarCells =
                        cells;

                },


                previousMonth() {

                    let cursor =
                        this.addDays(
                            this.monthStart,
                            -1
                        );


                    cursor =
                        this.findMonthStart(
                            cursor
                        );


                    this.monthStart =
                        cursor;


                    this.refreshCalendar();

                },


                nextMonth() {

                    let cursor =
                        this.addDays(
                            this.monthStart,
                            32
                        );


                    cursor =
                        this.findMonthStart(
                            cursor
                        );


                    this.monthStart =
                        cursor;


                    this.refreshCalendar();

                },


                selectDate(cell) {

                    if (
                        !cell ||
                        cell.disabled
                    ) {
                        return;
                    }


                    this.selectDateByIso(
                        cell.iso
                    );

                },


                selectDateByIso(iso) {

                    const date =
                        this.parseIso(
                            iso
                        );


                    this.selectedDate =
                        iso;


                    this.selectedTime =
                        '';


                    const formatter =
                        new Intl.DateTimeFormat(
                            'fa-IR-u-ca-persian',
                            {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                timeZone: 'UTC',
                            }
                        );


                    this.selectedDateLabel =
                        formatter.format(
                            date
                        );


                    this.loadSlots();

                },


                /*
                |--------------------------------------------------------------------------
                | Barber
                |--------------------------------------------------------------------------
                */

                selectBarber(id) {

                    this.barberId =
                        String(id);


                    this.selectedTime =
                        '';


                    this.loadSlots();

                },


                /*
                |--------------------------------------------------------------------------
                | Service
                |--------------------------------------------------------------------------
                */

                selectService(id) {

                    this.serviceId =
                        String(id);


                    this.selectedTime =
                        '';


                    this.loadSlots();

                },


                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                selectCustomer(id) {

                    this.customerId =
                        String(id);

                },


                /*
                |--------------------------------------------------------------------------
                | Customers
                |--------------------------------------------------------------------------
                */

                get filteredCustomers() {

                    const search =
                        String(
                            this.customerSearch || ''
                        )
                            .trim()
                            .toLowerCase();


                    if (!search) {

                        return this.customers;

                    }


                    return this.customers.filter(
                        customer => {

                            const name =
                                String(
                                    customer.name || ''
                                )
                                    .toLowerCase();


                            const phone =
                                String(
                                    customer.phone || ''
                                )
                                    .toLowerCase();


                            return (
                                name.includes(search) ||
                                phone.includes(search)
                            );

                        }
                    );

                },


                get selectedCustomer() {

                    return this.customers.find(
                        customer =>
                            String(
                                customer.id
                            ) ===
                            String(
                                this.customerId
                            )
                    );

                },


                get selectedBarber() {

                    return this.barbers.find(
                        barber =>
                            String(
                                barber.id
                            ) ===
                            String(
                                this.barberId
                            )
                    );

                },


                get selectedService() {

                    return this.services.find(
                        service =>
                            String(
                                service.id
                            ) ===
                            String(
                                this.serviceId
                            )
                    );

                },


                /*
                |--------------------------------------------------------------------------
                | Service Summary
                |--------------------------------------------------------------------------
                */

                get selectedPrice() {

                    return this.selectedService
                        ? Number(
                            this.selectedService.price
                        )
                        : 0;

                },


                get selectedDuration() {

                    return this.selectedService
                        ? Number(
                            this.selectedService.duration
                        )
                        : 0;

                },


                /*
                |--------------------------------------------------------------------------
                | Time
                |--------------------------------------------------------------------------
                */

                persianNumber(value) {

                    return String(
                        value
                    ).replace(
                        /\d/g,
                        digit =>
                            '۰۱۲۳۴۵۶۷۸۹'[
                                Number(digit)
                                ]
                    );

                },


                toPersianTime(time) {

                    return time
                        ? this.persianNumber(time)
                        : '';

                },


                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                async loadSlots() {

                    this.slots =
                        [];

                    this.slotError =
                        '';

                    this.selectedTime =
                        '';


                    if (
                        !this.barberId ||
                        !this.serviceId ||
                        !this.selectedDate
                    ) {

                        return;

                    }


                    this.loadingSlots =
                        true;


                    try {

                        const url =
                            new URL(
                                this.availabilityUrl,
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
                                    method: 'GET',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },
                                }
                            );


                        if (
                            !response.ok
                        ) {

                            throw new Error(
                                `HTTP ${response.status}`
                            );

                        }


                        const data =
                            await response.json();


                        this.slots =
                            Array.isArray(
                                data.slots
                            )
                                ? data.slots
                                : [];


                    } catch (error) {

                        console.error(
                            'Availability error:',
                            error
                        );


                        this.slotError =
                            'دریافت زمان‌های آزاد با خطا مواجه شد.';

                        this.slots =
                            [];


                    } finally {

                        this.loadingSlots =
                            false;

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | Selected Slot
                |--------------------------------------------------------------------------
                */

                get selectedSlot() {

                    return this.slots.find(
                        slot =>
                            String(
                                slot.start
                            ) ===
                            String(
                                this.selectedTime
                            )
                    );

                },


                /*
                |--------------------------------------------------------------------------
                | Submit
                |--------------------------------------------------------------------------
                */

                get canSubmit() {

                    return Boolean(

                        this.customerId &&

                        this.barberId &&

                        this.serviceId &&

                        this.selectedDate &&

                        this.selectedTime &&

                        this.selectedSlot?.available

                    );

                },


                /*
                |--------------------------------------------------------------------------
                | Formatting
                |--------------------------------------------------------------------------
                */

                formatPrice(value) {

                    return Number(
                        value || 0
                    ).toLocaleString(
                        'fa-IR'
                    );

                },

            };

        }
    </script>


    <div
        x-data="salonBookingPage()"
        x-init="init()"
        dir="rtl"
        class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8"
    >


        {{-- ============================================================
            PAGE HEADER
        ============================================================= --}}

        <div class="mb-7">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                <div>

                    <a
                        href="{{ route('salon.bookings.index') }}"
                        class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                    >
                        ← برگشت به نوبت‌ها
                    </a>


                    <div class="text-[9px] font-black tracking-[0.18em] text-accent-600">
                        MANUAL BOOKING
                    </div>


                    <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                        ثبت نوبت دستی
                    </h1>


                    <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted sm:text-sm">
                        برای مشتری سالن نوبت ثبت کنید. ظرفیت بر اساس آرایشگر، خدمت، ساعات کاری و نوبت‌های موجود محاسبه می‌شود.
                    </p>

                </div>


                <div class="flex items-center gap-3 rounded-2xl border border-border bg-white px-4 py-3 shadow-soft">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                        ◷
                    </div>


                    <div>

                        <div class="text-[9px] font-bold text-content-muted">
                            سالن
                        </div>

                        <div class="mt-1 text-sm font-black text-content">
                            {{ $salon->name }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            VALIDATION ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-6 rounded-3xl border border-red-100 bg-red-50 p-5">

                <div class="mb-2 text-xs font-black text-red-800">
                    ثبت نوبت انجام نشد
                </div>


                <div class="space-y-1">

                    @foreach($errors->all() as $error)

                        <div class="text-[10px] font-bold leading-6 text-red-700">
                            • {{ $error }}
                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        <form
            action="{{ route('salon.bookings.store-manual') }}"
            method="POST"
            class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_350px]"
        >

            @csrf


            <div class="space-y-5">


                {{-- ==================================================
                    CUSTOMER
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۱
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    CUSTOMER
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    انتخاب مشتری
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-5 sm:p-6">

                        @if($customers->count())

                            <div class="relative">

                                <input
                                    type="search"
                                    x-model="customerSearch"
                                    class="form-control pr-11"
                                    placeholder="جستجو با نام یا شماره موبایل..."
                                    autocomplete="off"
                                >


                                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-content-faint">
                                    ⌕
                                </span>

                            </div>


                            <div class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1">

                                <template
                                    x-for="customer in filteredCustomers"
                                    :key="customer.id"
                                >

                                    <button
                                        type="button"
                                        @click="selectCustomer(customer.id)"
                                        class="flex w-full items-center justify-between gap-4 rounded-2xl border p-4 text-right transition"
                                        :class="
                                            String(customerId) === String(customer.id)
                                                ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-500/10'
                                                : 'border-border bg-white hover:border-accent-200'
                                        "
                                    >

                                        <div class="flex min-w-0 items-center gap-3">

                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary-50 text-sm font-black text-content-soft">

                                                <span
                                                    x-text="
                                                        customer.name
                                                            ? customer.name.substring(0, 1)
                                                            : '?'
                                                    "
                                                ></span>

                                            </div>


                                            <div class="min-w-0">

                                                <div
                                                    class="truncate text-sm font-black text-content"
                                                    x-text="customer.name"
                                                ></div>


                                                <div
                                                    class="mt-1 text-[10px] text-content-muted"
                                                    dir="ltr"
                                                    x-text="customer.phone || 'بدون شماره'"
                                                ></div>

                                            </div>

                                        </div>


                                        <div
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border text-xs font-black"
                                            :class="
                                                String(customerId) === String(customer.id)
                                                    ? 'border-accent-500 bg-accent-600 text-white'
                                                    : 'border-border bg-white text-transparent'
                                            "
                                        >
                                            ✓
                                        </div>

                                    </button>

                                </template>


                                <div
                                    x-show="filteredCustomers.length === 0"
                                    x-cloak
                                    class="rounded-2xl bg-primary-50 p-6 text-center"
                                >

                                    <div class="text-xs font-black text-content">
                                        مشتری پیدا نشد
                                    </div>

                                    <div class="mt-1 text-[10px] text-content-muted">
                                        نام یا شماره موبایل را تغییر دهید.
                                    </div>

                                </div>

                            </div>


                            <input
                                type="hidden"
                                name="customer_id"
                                x-model="customerId"
                            >

                        @else

                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">

                                <div class="text-xs font-black text-amber-800">
                                    مشتری قابل انتخاب وجود ندارد.
                                </div>

                                <div class="mt-2 text-[10px] leading-6 text-amber-700">
                                    در حال حاضر مشتری ثبت‌شده‌ای مرتبط با این سالن پیدا نشد.
                                </div>

                            </div>

                        @endif

                    </div>

                </section>


                {{-- ==================================================
                    BARBER
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۲
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    BARBER
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    انتخاب آرایشگر
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-5 sm:p-6">

                        @if($barbers->count())

                            <div class="grid gap-3 sm:grid-cols-2">

                                @foreach($barbers as $barber)

                                    <button
                                        type="button"
                                        @click="selectBarber({{ $barber->id }})"
                                        class="group rounded-2xl border p-4 text-right transition"
                                        :class="
                                            String(barberId) === '{{ $barber->id }}'
                                                ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-500/10'
                                                : 'border-border bg-white hover:border-accent-200'
                                        "
                                    >

                                        <div class="flex items-center gap-3">

                                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-primary-50">

                                                @if($barber->image_path)

                                                    <img
                                                        src="{{ asset('storage/' . $barber->image_path) }}"
                                                        alt="{{ $barber->name }}"
                                                        class="h-full w-full object-cover"
                                                    >

                                                @else

                                                    <div class="flex h-full w-full items-center justify-center text-sm font-black text-content-soft">
                                                        {{ mb_substr($barber->name, 0, 1) }}
                                                    </div>

                                                @endif

                                            </div>


                                            <div class="min-w-0 flex-1">

                                                <div class="truncate text-sm font-black text-content">
                                                    {{ $barber->name }}
                                                </div>


                                                @if($barber->specialty)

                                                    <div class="mt-1 truncate text-[10px] text-content-muted">
                                                        {{ $barber->specialty }}
                                                    </div>

                                                @endif

                                            </div>


                                            <div
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border text-xs font-black"
                                                :class="
                                                    String(barberId) === '{{ $barber->id }}'
                                                        ? 'border-accent-500 bg-accent-600 text-white'
                                                        : 'border-border text-transparent'
                                                "
                                            >
                                                ✓
                                            </div>

                                        </div>

                                    </button>

                                @endforeach

                            </div>


                            <input
                                type="hidden"
                                name="barber_id"
                                x-model="barberId"
                            >

                        @else

                            <div class="rounded-2xl bg-primary-50 p-5 text-center text-xs font-bold text-content-muted">
                                آرایشگر فعالی ثبت نشده است.
                            </div>

                        @endif

                    </div>

                </section>


                {{-- ==================================================
                    SERVICE
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۳
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    SERVICE
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    انتخاب خدمت
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="space-y-2 p-5 sm:p-6">

                        @if($services->count())

                            @foreach($services as $service)

                                <button
                                    type="button"
                                    @click="selectService({{ $service->id }})"
                                    class="flex w-full items-center justify-between gap-4 rounded-2xl border p-4 text-right transition"
                                    :class="
                                        String(serviceId) === '{{ $service->id }}'
                                            ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-500/10'
                                            : 'border-border bg-white hover:border-accent-200'
                                    "
                                >

                                    <div class="min-w-0">

                                        <div class="text-sm font-black text-content">
                                            {{ $service->name }}
                                        </div>


                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] text-content-muted">

                                            <span>
                                                {{ $service->duration_minutes }} دقیقه
                                            </span>

                                            <span class="h-1 w-1 rounded-full bg-content-faint"></span>

                                            <span>
                                                {{ number_format($service->price) }}
                                                تومان
                                            </span>

                                        </div>

                                    </div>


                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border text-xs font-black"
                                        :class="
                                            String(serviceId) === '{{ $service->id }}'
                                                ? 'border-accent-500 bg-accent-600 text-white'
                                                : 'border-border text-transparent'
                                        "
                                    >
                                        ✓
                                    </div>

                                </button>

                            @endforeach

                        @else

                            <div class="rounded-2xl bg-primary-50 p-5 text-center text-xs font-bold text-content-muted">
                                خدمتی برای این سالن ثبت نشده است.
                            </div>

                        @endif

                    </div>


                    <input
                        type="hidden"
                        name="service_id"
                        x-model="serviceId"
                    >

                </section>


                {{-- ==================================================
                    DATE
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۴
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    DATE
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    انتخاب تاریخ
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-5 sm:p-6">

                        <div class="rounded-3xl border border-border bg-primary-50 p-4">

                            <div class="mb-4 flex items-center justify-between">

                                <button
                                    type="button"
                                    @click="previousMonth()"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-content-soft shadow-soft transition hover:bg-primary-100"
                                    aria-label="ماه قبل"
                                >
                                    ‹
                                </button>


                                <div
                                    class="text-sm font-black text-content"
                                    x-text="monthTitle"
                                ></div>


                                <button
                                    type="button"
                                    @click="nextMonth()"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-content-soft shadow-soft transition hover:bg-primary-100"
                                    aria-label="ماه بعد"
                                >
                                    ›
                                </button>

                            </div>


                            <div class="mb-2 grid grid-cols-7 gap-1 text-center">

                                @foreach([
                                    'ش',
                                    'ی',
                                    'د',
                                    'س',
                                    'چ',
                                    'پ',
                                    'ج'
                                ] as $day)

                                    <div class="py-2 text-[10px] font-black text-content-muted">
                                        {{ $day }}
                                    </div>

                                @endforeach

                            </div>


                            <div class="grid grid-cols-7 gap-1">

                                <template
                                    x-for="(cell, index) in calendarCells"
                                    :key="index"
                                >

                                    <div class="aspect-square">

                                        <template x-if="cell">

                                            <button
                                                type="button"
                                                @click="selectDate(cell)"
                                                :disabled="cell.disabled"
                                                class="flex h-full w-full items-center justify-center rounded-xl text-xs font-black transition"
                                                :class="
                                                    cell.disabled
                                                        ? 'cursor-not-allowed text-content-faint'
                                                        : cell.iso === selectedDate
                                                            ? 'bg-accent-600 text-white shadow-iris'
                                                            : cell.isToday
                                                                ? 'bg-white text-accent-600 ring-1 ring-accent-300'
                                                                : 'text-content hover:bg-white'
                                                "
                                            >

                                                <span
                                                    x-text="persianNumber(cell.day)"
                                                ></span>

                                            </button>

                                        </template>

                                    </div>

                                </template>

                            </div>

                        </div>


                        <input
                            type="hidden"
                            name="booking_date"
                            x-model="selectedDate"
                        >


                        <div
                            x-show="selectedDateLabel"
                            x-cloak
                            class="mt-4 rounded-2xl bg-accent-50 p-4"
                        >

                            <div class="text-[9px] font-bold text-accent-600">
                                تاریخ انتخاب‌شده
                            </div>

                            <div
                                class="mt-1 text-xs font-black text-accent-800"
                                x-text="selectedDateLabel"
                            ></div>

                        </div>

                    </div>

                </section>


                {{-- ==================================================
                    TIME
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۵
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    TIME
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    انتخاب ساعت
                                </h2>

                            </div>

                        </div>


                        <p class="mt-3 text-[10px] leading-6 text-content-muted">
                            ساعت‌های رزروشده غیرفعال هستند و مدت خدمت نیز در ظرفیت لحاظ می‌شود.
                        </p>

                    </div>


                    <div class="p-5 sm:p-6">


                        <div
                            x-show="!barberId || !serviceId"
                            x-cloak
                            class="rounded-2xl border border-border bg-primary-50 p-5 text-center"
                        >

                            <div class="text-xs font-black text-content">
                                ابتدا آرایشگر و خدمت را انتخاب کنید.
                            </div>

                        </div>


                        <div
                            x-show="loadingSlots"
                            x-cloak
                            class="rounded-2xl border border-border bg-primary-50 p-5 text-center"
                        >

                            <div class="text-xs font-black text-content">
                                در حال بررسی زمان‌های آزاد...
                            </div>

                            <div class="mt-1 text-[10px] text-content-muted">
                                ظرفیت آرایشگر در حال محاسبه است.
                            </div>

                        </div>


                        <div
                            x-show="slotError"
                            x-cloak
                            class="rounded-2xl border border-red-100 bg-red-50 p-4"
                            x-text="slotError"
                        ></div>


                        <div
                            x-show="
                                !loadingSlots &&
                                !slotError &&
                                barberId &&
                                serviceId &&
                                selectedDate &&
                                slots.length === 0
                            "
                            x-cloak
                            class="rounded-2xl border border-border bg-primary-50 p-6 text-center"
                        >

                            <div class="text-xs font-black text-content">
                                برای این تاریخ ساعت آزادی پیدا نشد.
                            </div>

                            <div class="mt-1 text-[10px] leading-6 text-content-muted">
                                تاریخ دیگری را امتحان کنید.
                            </div>

                        </div>


                        <div
                            x-show="!loadingSlots && slots.length > 0"
                            x-cloak
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4"
                        >

                            <template
                                x-for="slot in slots"
                                :key="slot.start"
                            >

                                <button
                                    type="button"
                                    :disabled="!slot.available"
                                    @click="
                                        if (slot.available) {
                                            selectedTime = slot.start
                                        }
                                    "
                                    class="rounded-2xl border px-3 py-3 text-center transition"
                                    :class="
                                        !slot.available
                                            ? 'cursor-not-allowed border-red-100 bg-red-50 text-red-400'
                                            : selectedTime === slot.start
                                                ? 'border-accent-500 bg-accent-600 text-white shadow-iris'
                                                : 'border-border bg-white text-content hover:border-accent-300'
                                    "
                                >

                                    <div
                                        class="text-xs font-black"
                                        x-text="toPersianTime(slot.start)"
                                    ></div>


                                    <div
                                        class="mt-1 text-[9px] font-bold"
                                        :class="
                                            !slot.available
                                                ? 'text-red-400'
                                                : selectedTime === slot.start
                                                    ? 'text-white/70'
                                                    : 'text-content-faint'
                                        "
                                        x-text="
                                            slot.available
                                                ? 'آزاد'
                                                : 'رزرو شده'
                                        "
                                    ></div>

                                </button>

                            </template>

                        </div>


                        <input
                            type="hidden"
                            name="start_time"
                            x-model="selectedTime"
                        >

                    </div>

                </section>


                {{-- ==================================================
                    NOTES
                =================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="border-b border-border p-5 sm:p-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-sm font-black text-accent-600">
                                ۶
                            </div>


                            <div>

                                <div class="text-[9px] font-black tracking-[0.15em] text-accent-600">
                                    NOTES
                                </div>

                                <h2 class="mt-1 text-base font-black text-content">
                                    توضیحات نوبت
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-5 sm:p-6">

                        <textarea
                            name="notes"
                            class="form-control min-h-32"
                            maxlength="2000"
                            placeholder="توضیحات اختیاری برای این نوبت..."
                        >{{ old('notes') }}</textarea>

                    </div>

                </section>

            </div>


            {{-- ============================================================
                SUMMARY
            ============================================================= --}}

            <aside class="lg:sticky lg:top-6 lg:h-fit">

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="bg-primary-950 p-5 text-white">

                        <div class="text-[9px] font-black tracking-[0.18em] text-accent-300">
                            BOOKING SUMMARY
                        </div>


                        <h2 class="mt-1 text-base font-black">
                            خلاصه نوبت
                        </h2>


                        <p class="mt-2 text-[10px] leading-5 text-white/50">
                            قبل از ثبت، اطلاعات را بررسی کنید.
                        </p>

                    </div>


                    <div class="space-y-5 p-5">


                        {{-- Customer --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                مشتری
                            </div>


                            <div
                                class="mt-2 rounded-2xl bg-primary-50 p-3"
                            >

                                <template x-if="selectedCustomer">

                                    <div>

                                        <div
                                            class="text-sm font-black text-content"
                                            x-text="selectedCustomer.name"
                                        ></div>


                                        <div
                                            class="mt-1 text-[10px] text-content-muted"
                                            dir="ltr"
                                            x-text="selectedCustomer.phone || 'بدون شماره'"
                                        ></div>

                                    </div>

                                </template>


                                <template x-if="!selectedCustomer">

                                    <span class="text-xs font-bold text-content-faint">
                                        انتخاب نشده
                                    </span>

                                </template>

                            </div>

                        </div>


                        {{-- Barber --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                آرایشگر
                            </div>


                            <div class="mt-2 text-sm font-black text-content">
                                <span
                                    x-text="
                                        selectedBarber
                                            ? selectedBarber.name
                                            : 'انتخاب نشده'
                                    "
                                ></span>
                            </div>

                        </div>


                        {{-- Service --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                خدمت
                            </div>


                            <div class="mt-2 text-sm font-black text-content">
                                <span
                                    x-text="
                                        selectedService
                                            ? selectedService.name
                                            : 'انتخاب نشده'
                                    "
                                ></span>
                            </div>

                        </div>


                        {{-- Duration --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                مدت خدمت
                            </div>


                            <div class="mt-2 text-sm font-black text-content">
                                <span
                                    x-text="
                                        selectedDuration
                                            ? persianNumber(selectedDuration) + ' دقیقه'
                                            : '—'
                                    "
                                ></span>
                            </div>

                        </div>


                        {{-- Date --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                تاریخ
                            </div>


                            <div class="mt-2 text-sm font-black text-content">
                                <span
                                    x-text="
                                        selectedDateLabel ||
                                        'انتخاب نشده'
                                    "
                                ></span>
                            </div>

                        </div>


                        {{-- Time --}}

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                ساعت
                            </div>


                            <div class="mt-2 text-sm font-black text-content">
                                <span
                                    dir="ltr"
                                    x-text="
                                        selectedTime
                                            ? toPersianTime(selectedTime)
                                            : 'انتخاب نشده'
                                    "
                                ></span>
                            </div>

                        </div>


                        {{-- Price --}}

                        <div class="border-t border-border pt-5">

                            <div class="flex items-end justify-between gap-4">

                                <span class="text-xs text-content-muted">
                                    مبلغ نوبت
                                </span>


                                <strong
                                    class="text-xl font-black text-content"
                                    x-text="
                                        selectedPrice
                                            ? formatPrice(selectedPrice) + ' تومان'
                                            : '—'
                                    "
                                ></strong>

                            </div>

                        </div>


                        {{-- Confirmed Badge --}}

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">

                            <div class="flex items-center gap-2">

                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                                <span class="text-xs font-black text-emerald-800">
                                    نوبت دستی تأییدشده
                                </span>

                            </div>


                            <p class="mt-2 text-[9px] leading-5 text-emerald-700">
                                این نوبت مستقیماً توسط سالن ثبت می‌شود و وضعیت آن بلافاصله تأییدشده خواهد بود.
                            </p>

                        </div>


                        {{-- Submit --}}

                        <button
                            type="submit"
                            class="btn btn-accent w-full"
                            :disabled="!canSubmit"
                            :class="{
                                'opacity-50 cursor-not-allowed': !canSubmit
                            }"
                        >
                            ثبت و تأیید نوبت
                        </button>


                        <div
                            x-show="!canSubmit"
                            x-cloak
                            class="rounded-2xl bg-amber-50 p-3 text-center"
                        >

                            <div class="text-[9px] font-bold leading-5 text-amber-700">
                                مشتری، آرایشگر، خدمت، تاریخ و یک ساعت آزاد را انتخاب کنید.
                            </div>

                        </div>

                    </div>

                </section>

            </aside>

        </form>

    </div>

@endsection

