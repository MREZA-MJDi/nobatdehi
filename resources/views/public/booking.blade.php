@extends('layouts.app')

@section('title', 'رزرو نوبت')

@section('content')

    @php
        $barberData = $barbers->map(function ($barber) {
            return [
                'id' => $barber->id,
                'name' => $barber->name,
            ];
        })->values();

        $serviceData = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration' => (int) $service->duration_minutes,
            ];
        })->values();
    @endphp


    <div
        x-data="bookingPage()"
        x-init="init()"
        class="mx-auto w-full max-w-5xl px-4 py-6 pb-28 sm:px-6 lg:px-8"
    >

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← بازگشت به سالن
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                BOOK APPOINTMENT
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                رزرو نوبت
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                برای {{ $salon->name }} آرایشگر، خدمت، تاریخ و ساعت را انتخاب کنید.
            </p>

        </div>


        {{-- ============================================================
            ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-red-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        {{-- ============================================================
            BOOKING FORM
        ============================================================= --}}

        <form
            action="{{ route('public.salons.booking.prepare', $salon) }}"
            method="POST"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]"
        >

            @csrf

            <div class="space-y-5">


                {{-- ==================================================
                    STEP 1 — BARBER
                =================================================== --}}

                <section class="card p-5 sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black text-accent-600">
                            مرحله ۱
                        </div>

                        <h2 class="mt-1 text-base font-black">
                            آرایشگر را انتخاب کنید
                        </h2>

                    </div>


                    @if($barbers->count())

                        <div class="grid gap-3 sm:grid-cols-2">

                            @foreach($barbers as $barber)

                                <button
                                    type="button"
                                    @click="selectBarber({{ $barber->id }})"
                                    class="rounded-2xl border p-4 text-right transition"
                                    :class="
                                        barberId === {{ $barber->id }}
                                        ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-500/10'
                                        : 'border-border bg-white hover:border-accent-200'
"
                                >

                                    <div class="flex items-center gap-3">

                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-primary-100">

                                            @if($barber->image_path)

                                                <img
                                                    src="{{ asset('storage/' . $barber->image_path) }}"
                                                    alt="{{ $barber->name }}"
                                                    class="h-full w-full object-cover"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-sm font-black text-content-muted">
                                                    {{ mb_substr($barber->name, 0, 1) }}
                                                </div>

                                            @endif

                                        </div>


                                        <div class="min-w-0">

                                            <div class="truncate text-sm font-black text-content">
                                                {{ $barber->name }}
                                            </div>

                                            @if($barber->specialty)

                                                <div class="mt-1 truncate text-[10px] text-content-muted">
                                                    {{ $barber->specialty }}
                                                </div>

                                            @endif

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

                        <div class="rounded-2xl border border-border bg-primary-50 p-5 text-center">

                            <div class="text-xs font-black text-content">
                                برای این سالن هنوز آرایشگری ثبت نشده است.
                            </div>

                        </div>

                    @endif

                </section>


                {{-- ==================================================
                    STEP 2 — SERVICE
                =================================================== --}}

                <section class="card p-5 sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black text-accent-600">
                            مرحله ۲
                        </div>

                        <h2 class="mt-1 text-base font-black">
                            خدمت را انتخاب کنید
                        </h2>

                    </div>


                    @if($services->count())

                        <div class="grid gap-3">

                            @foreach($services as $service)

                                <button
                                    type="button"
                                    @click="selectService({{ $service->id }})"
                                    class="flex items-center justify-between gap-4 rounded-2xl border p-4 text-right transition"
                                    :class="
                                        serviceId === {{ $service->id }}
                                        ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-500/10'
                                        : 'border-border bg-white hover:border-accent-200'
"
                                >

                                    <div class="min-w-0">

                                        <div class="text-sm font-black text-content">
                                            {{ $service->name }}
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            {{ $service->duration_minutes }} دقیقه
                                        </div>

                                    </div>


                                    <div class="shrink-0 text-sm font-black text-content">

                                        {{ number_format($service->price) }}

                                        <span class="text-[10px] font-bold text-content-muted">
                                            تومان
                                        </span>

                                    </div>

                                </button>

                            @endforeach

                        </div>


                        <input
                            type="hidden"
                            name="service_id"
                            x-model="serviceId"
                        >

                    @else

                        <div class="rounded-2xl border border-border bg-primary-50 p-5 text-center">

                            <div class="text-xs font-black text-content">
                                برای این سالن هنوز خدمتی ثبت نشده است.
                            </div>

                        </div>

                    @endif

                </section>


                {{-- ==================================================
                    STEP 3 — DATE + TIME
                =================================================== --}}

                <section class="card p-5 sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black text-accent-600">
                            مرحله ۳
                        </div>

                        <h2 class="mt-1 text-base font-black">
                            تاریخ و ساعت
                        </h2>

                    </div>


                    {{-- Persian Calendar --}}

                    <div class="rounded-3xl border border-border bg-primary-50 p-4">

                        <div class="mb-4 flex items-center justify-between">

                            <button
                                type="button"
                                @click="previousMonth()"
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-content-soft shadow-soft"
                            >
                                ‹
                            </button>


                            <div class="text-sm font-black text-content">
                                <span x-text="monthTitle"></span>
                            </div>


                            <button
                                type="button"
                                @click="nextMonth()"
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-content-soft shadow-soft"
                            >
                                ›
                            </button>

                        </div>


                        <div class="mb-2 grid grid-cols-7 gap-1 text-center">

                            <template
                                x-for="day in weekDays"
                                :key="day"
                            >

                                <div class="py-2 text-[10px] font-black text-content-muted">
                                    <span x-text="day"></span>
                                </div>

                            </template>

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
                                            class="flex h-full w-full items-center justify-center rounded-xl text-xs font-bold transition"
                                            :disabled="cell.disabled"
                                            :class="
                                                cell.disabled
                                                    ? 'cursor-not-allowed text-content-faint'
                                                    : cell.iso === selectedDate
                                                        ? 'bg-accent-600 text-white shadow-iris'
                                                        : 'text-content hover:bg-white'
                                            "
                                            @click="selectDate(cell)"
                                        >

                                            <span x-text="cell.day"></span>

                                        </button>

                                    </template>

                                </div>

                            </template>

                        </div>

                    </div>


                    <input
                        type="hidden"
                        name="salon_id"
                        value="{{ $salon->id }}"
                    >

                    <input
                        type="hidden"
                        name="booking_date"
                        x-model="selectedDate"
                    >


                    {{-- Time Picker --}}

                    <div class="mt-5">

                        <div class="mb-3 flex items-center justify-between">

                            <div>

                                <div class="text-xs font-black">
                                    ساعت‌های آزاد
                                </div>

                                <div class="mt-1 text-[10px] text-content-muted">
                                    زمان‌های رزروشده برای این آرایشگر قابل انتخاب نیستند.
                                </div>

                            </div>

                            <span
                                class="badge badge-neutral"
                                x-text="selectedDateLabel"
                            ></span>

                        </div>


                        {{-- Loading --}}

                        <div
                            x-show="loadingSlots"
                            x-cloak
                            class="rounded-2xl bg-primary-50 p-4 text-center text-[10px] font-bold text-content-muted"
                        >
                            در حال دریافت زمان‌های آزاد...
                        </div>


                        {{-- No slots --}}

                        <div
                            x-show="!loadingSlots && slots.length === 0"
                            x-cloak
                            class="rounded-2xl border border-border bg-primary-50 p-5 text-center"
                        >

                            <div class="text-xs font-black text-content">
                                برای این تاریخ زمانی پیدا نشد.
                            </div>

                            <div class="mt-1 text-[10px] leading-6 text-content-muted">
                                آرایشگر، خدمت یا تاریخ دیگری انتخاب کنید.
                            </div>

                        </div>


                        {{-- Slots --}}

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
                                    class="rounded-2xl border px-3 py-3 text-center text-xs font-black transition"
                                    :class="
                                        !slot.available
                                            ? 'cursor-not-allowed border-red-100 bg-red-50 text-red-400'
                                            : selectedTime === slot.start
                                                ? 'border-accent-500 bg-accent-600 text-white shadow-iris'
                                                : 'border-border bg-white text-content hover:border-accent-300'
                                    "
                                >

                                    <div
                                        x-text="toPersianTime(slot.start)"
                                    ></div>

                                    <div
                                        x-show="!slot.available"
                                        class="mt-1 text-[9px] font-bold"
                                    >
                                        رزرو شده
                                    </div>

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
                    STEP 4 — NOTES
                =================================================== --}}

                <section class="card p-5 sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black text-accent-600">
                            مرحله ۴
                        </div>

                        <h2 class="mt-1 text-base font-black">
                            توضیحات نوبت
                        </h2>

                    </div>


                    <textarea
                        name="notes"
                        class="form-control min-h-28"
                        maxlength="2000"
                        placeholder="توضیح خاصی برای سالن دارید؟"
                    >{{ old('notes') }}</textarea>

                </section>

            </div>


            {{-- ============================================================
                SUMMARY
            ============================================================= --}}

            <aside class="lg:sticky lg:top-6 lg:h-fit">

                <section class="card overflow-hidden">

                    <div class="bg-primary-950 p-5 text-white">

                        <div class="text-[10px] font-black tracking-wider text-accent-300">
                            BOOKING SUMMARY
                        </div>

                        <h2 class="mt-1 text-base font-black">
                            خلاصه نوبت
                        </h2>

                    </div>


                    <div class="space-y-4 p-5">

                        <div>

                            <div class="text-[10px] text-content-muted">
                                سالن
                            </div>

                            <div class="mt-1 text-sm font-black">
                                {{ $salon->name }}
                            </div>

                        </div>


                        <div>

                            <div class="text-[10px] text-content-muted">
                                آرایشگر
                            </div>

                            <div
                                class="mt-1 text-sm font-black"
                                x-text="selectedBarberName || 'انتخاب نشده'"
                            ></div>

                        </div>


                        <div>

                            <div class="text-[10px] text-content-muted">
                                خدمت
                            </div>

                            <div
                                class="mt-1 text-sm font-black"
                                x-text="selectedServiceName || 'انتخاب نشده'"
                            ></div>

                        </div>


                        <div>

                            <div class="text-[10px] text-content-muted">
                                مدت خدمت
                            </div>

                            <div
                                class="mt-1 text-sm font-black"
                                x-text="
                                    selectedDuration
                                        ? persianNumber(selectedDuration) + ' دقیقه'
                                        : '—'
                                "
                            ></div>

                        </div>


                        <div>

                            <div class="text-[10px] text-content-muted">
                                تاریخ
                            </div>

                            <div
                                class="mt-1 text-sm font-black"
                                x-text="selectedDateLabel || 'انتخاب نشده'"
                            ></div>

                        </div>


                        <div>

                            <div class="text-[10px] text-content-muted">
                                ساعت
                            </div>

                            <div
                                class="mt-1 text-sm font-black"
                                dir="ltr"
                                x-text="
                                    selectedTime
                                        ? toPersianTime(selectedTime)
                                        : 'انتخاب نشده'
                                "
                            ></div>

                        </div>


                        <div class="border-t border-border pt-4">

                            <div class="flex items-center justify-between">

                                <span class="text-xs text-content-muted">
                                    مبلغ
                                </span>

                                <strong
                                    class="text-lg font-black text-content"
                                    x-text="
                                        selectedPrice
                                            ? Number(selectedPrice).toLocaleString('fa-IR') + ' تومان'
                                            : '—'
                                    "
                                ></strong>

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-accent w-full"
                            :disabled="
                                !barberId ||
                                !serviceId ||
                                !selectedDate ||
                                !selectedTime
                            "
                        >
                            تأیید و رزرو نوبت
                        </button>


                        @guest

                            <p class="text-center text-[10px] leading-6 text-content-muted">
                                برای ثبت نهایی نوبت باید وارد حساب کاربری شوید.
                            </p>

                        @endguest

                    </div>

                </section>

            </aside>

        </form>

    </div>


    {{-- ============================================================
        BOOKING JAVASCRIPT
    ============================================================= --}}

    <script>

        const barberData = @js($barberData);

        const serviceData = @js($serviceData);


        function bookingPage() {

            return {

                barberId: null,

                serviceId: null,

                selectedDate: @js(
                    old(
                        'booking_date',
                        now()->toDateString()
        )
        ),

            selectedTime: @js(
                old('start_time')
            ),

            slots: [],

                loadingSlots: false,

                viewDate: new Date(),

                weekDays: [
                'ش',
                'ی',
                'د',
                'س',
                'چ',
                'پ',
                'ج'
            ],

                monthTitle: '',

                calendarCells: [],

                selectedBarberName: '',

                selectedServiceName: '',

                selectedPrice: 0,

                selectedDuration: 0,


                persianFormatter: new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian-nu-arabext',
                {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric'
                }
            ),


                monthFormatter: new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian-nu-arabext',
                {
                    year: 'numeric',
                    month: 'long'
                }
            ),


                init() {

                this.renderCalendar();


                const firstBarber =
                    barberData[0] ?? null;

                const firstService =
                    serviceData[0] ?? null;


                const oldBarberId =
                @js(old('barber_id'));

                const oldServiceId =
                @js(old('service_id'));


                if (oldBarberId) {

                    this.selectBarber(
                        Number(oldBarberId),
                        false
                    );

                } else if (firstBarber) {

                    this.selectBarber(
                        Number(firstBarber.id),
                        false
                    );

                }


                if (oldServiceId) {

                    this.selectService(
                        Number(oldServiceId),
                        false
                    );

                } else if (firstService) {

                    this.selectService(
                        Number(firstService.id),
                        false
                    );

                }


                this.syncViewDateToSelectedDate();

                this.loadSlots();

            },


            normalizeDigits(value) {

                return String(value)

                    .replace(
                        /[۰-۹]/g,
                        digit =>
                            '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)
                    )

                    .replace(
                        /[٠-٩]/g,
                        digit =>
                            '٠١٢٣٤٥٦٧٨٩'.indexOf(digit)
                    );

            },


            persianNumber(value) {

                return String(value).replace(
                    /\d/g,
                    digit =>
                        '۰۱۲۳۴۵۶۷۸۹'[digit]
                );

            },


            toPersianTime(time) {

                if (!time) {
                    return '';
                }

                return this.persianNumber(time);

            },


            dateParts(date) {

                const parts =
                    this.persianFormatter
                        .formatToParts(date);


                const get = (type) => {

                    return this.normalizeDigits(
                        parts.find(
                            part =>
                                part.type === type
                        )?.value ?? ''
                    );

                };


                return {

                    year: Number(
                        get('year')
                    ),

                    month: Number(
                        get('month')
                    ),

                    day: Number(
                        get('day')
                    ),

                };

            },


            monthKey(date) {

                const parts =
                    this.dateParts(date);

                return `${parts.year}-${parts.month}`;

            },


            addDays(date, days) {

                const result =
                    new Date(date);

                result.setDate(
                    result.getDate() + days
                );

                return result;

            },


            startOfPersianMonth(date) {

                let current =
                    new Date(date);


                while (
                    this.dateParts(current).day !== 1
                    ) {

                    current =
                        this.addDays(
                            current,
                            -1
                        );

                }


                return current;

            },


            daysInPersianMonth(start) {

                const key =
                    this.monthKey(start);


                let current =
                    this.addDays(
                        start,
                        1
                    );


                let count = 1;


                while (
                    this.monthKey(current) === key
                    ) {

                    count++;

                    current =
                        this.addDays(
                            current,
                            1
                        );

                }


                return count;

            },


            iranianWeekday(date) {

                return (
                    date.getDay() + 1
                ) % 7;

            },


            formatDateISO(date) {

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


            renderCalendar() {

                const start =
                    this.startOfPersianMonth(
                        this.viewDate
                    );


                this.monthTitle =
                    this.monthFormatter.format(
                        start
                    );


                const totalDays =
                    this.daysInPersianMonth(
                        start
                    );


                const leading =
                    this.iranianWeekday(
                        start
                    );


                const cells = [];


                for (
                    let i = 0;
                    i < leading;
                    i++
                ) {

                    cells.push(null);

                }


                for (
                    let day = 0;
                    day < totalDays;
                    day++
                ) {

                    const current =
                        this.addDays(
                            start,
                            day
                        );


                    const iso =
                        this.formatDateISO(
                            current
                        );


                    const today =
                        this.formatDateISO(
                            new Date()
                        );


                    cells.push({

                        day:
                            this.persianNumber(
                                this.dateParts(
                                    current
                                ).day
                            ),

                        iso,

                        disabled:
                            iso < today,

                    });

                }


                while (
                    cells.length % 7 !== 0
                    ) {

                    cells.push(null);

                }


                this.calendarCells =
                    cells;

            },


            syncViewDateToSelectedDate() {

                if (!this.selectedDate) {
                    return;
                }


                const parsed =
                    new Date(
                        `${this.selectedDate}T00:00:00`
                    );


                if (
                    !Number.isNaN(
                        parsed.getTime()
                    )
                ) {

                    this.viewDate =
                        parsed;

                    this.renderCalendar();

                }

            },


            previousMonth() {

                const current =
                    this.startOfPersianMonth(
                        this.viewDate
                    );


                this.viewDate =
                    this.addDays(
                        current,
                        -1
                    );


                this.renderCalendar();

            },


            nextMonth() {

                const current =
                    this.startOfPersianMonth(
                        this.viewDate
                    );


                const total =
                    this.daysInPersianMonth(
                        current
                    );


                this.viewDate =
                    this.addDays(
                        current,
                        total
                    );


                this.renderCalendar();

            },


            selectBarber(
                id,
                load = true
            ) {

                this.barberId =
                    Number(id);


                const item =
                    barberData.find(
                        barber =>
                            Number(barber.id) ===
                            Number(id)
                    );


                this.selectedBarberName =
                    item?.name ?? '';


                this.selectedTime =
                    null;


                if (load) {
                    this.loadSlots();
                }

            },


            selectService(
                id,
                load = true
            ) {

                this.serviceId =
                    Number(id);


                const item =
                    serviceData.find(
                        service =>
                            Number(service.id) ===
                            Number(id)
                    );


                this.selectedServiceName =
                    item?.name ?? '';


                this.selectedPrice =
                    Number(
                        item?.price ?? 0
                    );


                this.selectedDuration =
                    Number(
                        item?.duration ?? 0
                    );


                this.selectedTime =
                    null;


                if (load) {
                    this.loadSlots();
                }

            },


            selectDate(cell) {

                if (
                    !cell ||
                    cell.disabled
                ) {
                    return;
                }


                this.selectedDate =
                    cell.iso;


                this.selectedTime =
                    null;


                this.loadSlots();

            },


            get selectedDateLabel() {

                if (!this.selectedDate) {
                    return '';
                }


                const date =
                    new Date(
                        `${this.selectedDate}T00:00:00`
                    );


                if (
                    Number.isNaN(
                        date.getTime()
                    )
                ) {
                    return '';
                }


                return this.persianFormatter.format(
                    date
                );

            },


            async loadSlots() {

                if (
                    !this.barberId ||
                    !this.serviceId ||
                    !this.selectedDate
                ) {

                    this.slots = [];

                    return;

                }


                this.loadingSlots =
                    true;


                try {

                    const url =
                        new URL(
                            "{{ route('public.salons.booking.availability', $salon) }}",
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
                                        'XMLHttpRequest'
                                }
                            }
                        );


                    if (!response.ok) {

                        throw new Error(
                            `Availability failed: ${response.status}`
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


                } finally {

                    this.loadingSlots =
                        false;

                }

            }

        };

        }

    </script>

@endsection
