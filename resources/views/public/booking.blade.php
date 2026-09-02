@extends('layouts.app')

@section('title', 'رزرو نوبت')

@section('content')

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
                href="{{ url('/salons/' . $salon->slug) }}"
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
            action="{{ request()->url() }}"
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
                        :value="barberId"
                    >

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
                        :value="serviceId"
                    >

                </section>


                {{-- ==================================================
                    STEP 3 — DATE
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

                            <template x-for="day in weekDays" :key="day">

                                <div class="py-2 text-[10px] font-black text-content-muted">
                                    <span x-text="day"></span>
                                </div>

                            </template>

                        </div>


                        <div class="grid grid-cols-7 gap-1">

                            <template x-for="(cell, index) in calendarCells" :key="index">

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
                                    مدت خدمت از قبل در زمان‌بندی محاسبه شده است.
                                </div>

                            </div>

                            <span
                                class="badge badge-neutral"
                                x-text="selectedDateLabel"
                            ></span>

                        </div>


                        <div
                            x-show="loadingSlots"
                            x-cloak
                            class="rounded-2xl bg-primary-50 p-4 text-center text-[10px] font-bold text-content-muted"
                        >
                            در حال دریافت زمان‌های آزاد...
                        </div>


                        <div
                            x-show="!loadingSlots && slots.length === 0"
                            x-cloak
                            class="rounded-2xl border border-border bg-primary-50 p-5 text-center"
                        >

                            <div class="text-xs font-black text-content">
                                برای این تاریخ زمانی پیدا نشد.
                            </div>

                            <div class="mt-1 text-[10px] leading-6 text-content-muted">
                                تاریخ دیگری انتخاب کنید.
                            </div>

                        </div>


                        <div
                            x-show="!loadingSlots && slots.length"
                            x-cloak
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4"
                        >

                            <template
                                x-for="slot in slots"
                                :key="slot.start"
                            >

                                <button
                                    type="button"
                                    @click="selectedTime = slot.start"
                                    class="rounded-2xl border px-3 py-3 text-center text-xs font-black transition"
                                    :class="
                                        selectedTime === slot.start
                                            ? 'border-accent-500 bg-accent-600 text-white shadow-iris'
                                            : 'border-border bg-white text-content hover:border-accent-300'
                                    "
                                >

                                    <span
                                        x-text="toPersianTime(slot.start)"
                                    ></span>

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
                                x-text="selectedTime ? toPersianTime(selectedTime) : 'انتخاب نشده'"
                            ></div>

                        </div>


                        <div class="border-t border-border pt-4">

                            <div class="flex items-center justify-between">

                                <span class="text-xs text-content-muted">
                                    مبلغ
                                </span>

                                <strong
                                    class="text-lg font-black text-content"
                                    x-text="selectedPrice ? persianNumber(selectedPrice) + ' تومان' : '—'"
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


    <script>
        function bookingPage() {
            const barberData = @json(
                $barbers->map(fn ($barber) => [
                    'id' => $barber->id,
                    'name' => $barber->name,
                ])->values()
            );

            const serviceData = @json(
                $services->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                ])->values()
            );

            return {
                barberId: null,
                serviceId: null,

                selectedDate: @js(
                    old(
                        'booking_date',
                        now()->toDateString()
        )
        ),

            selectedTime: null,

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

                if (firstBarber) {
                    this.selectBarber(
                        firstBarber.id,
                        false
                    );
                }

                if (firstService) {
                    this.selectService(
                        firstService.id,
                        false
                    );
                }

                this.syncViewDateToSelectedDate();

                this.loadSlots();
            },

            normalizeDigits(value) {
                return String(value)
                    .replace(/[۰-۹]/g, digit =>
                        '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)
                    )
                    .replace(/[٠-٩]/g, digit =>
                        '٠١٢٣٤٥٦٧٨٩'.indexOf(digit)
                    );
            },

            persianNumber(value) {
                return String(value)
                    .replace(/\d/g, digit =>
                        '۰۱۲۳۴۵۶۷۸۹'[digit]
                    );
            },

            toPersianTime(time) {
                return this.persianNumber(
                    time
                );
            },

            dateParts(date) {
                const parts =
                    this.persianFormatter
                        .formatToParts(date);

                const get = (type) =>
                    this.normalizeDigits(
                        parts.find(
                            part =>
                                part.type === type
                        )?.value ?? ''
                    );

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
                    this.dateParts(
                        current
                    ).day !== 1
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
                    this.monthKey(
                        current
                    ) === key
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
                /*
                Sunday = 0
                Saturday = 6

                Iranian:
                Saturday = 0
                ...
                Friday = 6
                */

                return (
                    date.getDay() + 1
                ) % 7;
            },

            renderCalendar() {
                const start =
                    this.startOfPersianMonth(
                        this.viewDate
                    );

                const parts =
                    this.dateParts(start);

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
                        current
                            .toISOString()
                            .slice(0, 10);

                    const today =
                        new Date()
                            .toISOString()
                            .slice(0, 10);

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
                this.barberId = id;

                const item =
                    barberData.find(
                        barber =>
                            barber.id === id
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
                this.serviceId = id;

                const item =
                    serviceData.find(
                        service =>
                            service.id === id
                    );

                this.selectedServiceName =
                    item?.name ?? '';

                this.selectedPrice =
                    item?.price ?? 0;

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
                if (
                    !this.selectedDate
                ) {
                    return '';
                }

                return this.persianFormatter
                    .format(
                        new Date(
                            `${this.selectedDate}T00:00:00`
                        )
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
                            window.location.href
                        );

                    url.search = '';

                    url.searchParams.set(
                        'availability',
                        '1'
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
                                    Accept:
                                        'application/json'
                                }
                            }
                        );

                    if (
                        !response.ok
                    ) {
                        throw new Error(
                            'Availability request failed'
                        );
                    }

                    const data =
                        await response.json();

                    this.slots =
                        data.slots ?? [];

                } catch (error) {

                    this.slots = [];

                } finally {

                    this.loadingSlots =
                        false;
                }
            }
        }
        }
    </script>

@endsection
