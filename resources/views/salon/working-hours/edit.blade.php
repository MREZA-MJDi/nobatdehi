@extends('layouts.salon')

@section('title', 'ساعات کاری')

@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Days
        |--------------------------------------------------------------------------
        */

        $days = [
            0 => [
                'name' => 'شنبه',
                'short' => 'ش',
            ],

            1 => [
                'name' => 'یکشنبه',
                'short' => 'ی',
            ],

            2 => [
                'name' => 'دوشنبه',
                'short' => 'د',
            ],

            3 => [
                'name' => 'سه‌شنبه',
                'short' => 'س',
            ],

            4 => [
                'name' => 'چهارشنبه',
                'short' => 'چ',
            ],

            5 => [
                'name' => 'پنجشنبه',
                'short' => 'پ',
            ],

            6 => [
                'name' => 'جمعه',
                'short' => 'ج',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Persian Digits
        |--------------------------------------------------------------------------
        */

        $persianDigits = [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ];


        /*
        |--------------------------------------------------------------------------
        | Existing / Old Data For Alpine
        |--------------------------------------------------------------------------
        */

        $hoursData = [];

        foreach ($days as $dayNumber => $day) {

            $hour =
                $hours->get(
                    $dayNumber
                );


            $closed =
                old(
                    "hours.$dayNumber.is_closed",
                    $hour?->is_closed ?? false
                );


            $start =
                old(
                    "hours.$dayNumber.start_time",
                    $hour?->start_time
                );


            $end =
                old(
                    "hours.$dayNumber.end_time",
                    $hour?->end_time
                );


            if ($start) {

                $start =
                    substr(
                        $start,
                        0,
                        5
                    );

            }


            if ($end) {

                $end =
                    substr(
                        $end,
                        0,
                        5
                    );

            }


            $hoursData[$dayNumber] = [

                'closed' =>
                    (bool) $closed,

                'start' =>
                    $start ?: '',

                'end' =>
                    $end ?: '',

            ];
        }

    @endphp


    <script>

        function workingHoursPage() {

            return {

                /*
                |--------------------------------------------------------------------------
                | Current Form State
                |--------------------------------------------------------------------------
                */

                hours: @js($hoursData),


                /*
                |--------------------------------------------------------------------------
                | Default Schedule
                |--------------------------------------------------------------------------
                */

                defaultSchedule: {

                    0: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    1: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    2: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    3: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    4: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    5: {
                        closed: false,
                        start: '09:00',
                        end: '22:00',
                    },

                    6: {
                        closed: true,
                        start: '',
                        end: '',
                    },

                },


                /*
                |--------------------------------------------------------------------------
                | Apply Default Schedule
                |--------------------------------------------------------------------------
                */

                applyDefault() {

                    if (
                        !confirm(
                            'برنامه پیش‌فرض روی ساعات فعلی اعمال شود؟ تغییرات بعداً با دکمه ذخیره ثبت می‌شوند.'
                        )
                    ) {
                        return;
                    }


                    Object.entries(
                        this.defaultSchedule
                    ).forEach(
                        ([day, value]) => {

                            this.hours[day].closed =
                                value.closed;

                            this.hours[day].start =
                                value.start;

                            this.hours[day].end =
                                value.end;

                        }
                    );

                },


                /*
                |--------------------------------------------------------------------------
                | Clear One Day
                |--------------------------------------------------------------------------
                */

                closeDay(day) {

                    this.hours[day].closed =
                        true;

                    this.hours[day].start =
                        '';

                    this.hours[day].end =
                        '';

                },


                /*
                |--------------------------------------------------------------------------
                | Open One Day
                |--------------------------------------------------------------------------
                */

                openDay(day) {

                    this.hours[day].closed =
                        false;

                },

            };

        }

    </script>


    <div
        x-data="workingHoursPage()"
        dir="rtl"
        class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8"
    >

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-7">

            <div class="mb-3 flex flex-wrap items-center gap-2">

                <span class="inline-flex items-center rounded-xl bg-accent-50 px-3 py-1.5 text-[9px] font-black tracking-[0.12em] text-accent-700">
                    SALON SETTINGS
                </span>


                <span class="text-[10px] font-bold text-content-faint">
                    {{ $salon->name }}
                </span>

            </div>


            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div>

                    <h1 class="text-2xl font-black text-content sm:text-3xl">
                        ساعات کاری سالن
                    </h1>


                    <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted sm:text-sm">
                        برنامه هفتگی سالن را مشخص کنید. این ساعات مبنای نمایش زمان‌های قابل رزرو برای مشتریان خواهد بود.
                    </p>

                </div>


                <a
                    href="{{ route('salon.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-border bg-white px-4 py-3 text-xs font-black text-content transition hover:border-accent-200 hover:text-accent-600"
                >
                    ← بازگشت به داشبورد
                </a>

            </div>

        </div>


        {{-- ============================================================
            SUCCESS MESSAGE
        ============================================================= --}}

        @if(session('success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                class="mb-6 rounded-3xl border border-emerald-100 bg-emerald-50 p-4"
            >

                <div class="flex items-start justify-between gap-4">

                    <div class="flex items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-sm font-black text-emerald-600 shadow-soft">
                            ✓
                        </div>


                        <div>

                            <div class="text-xs font-black text-emerald-800">
                                تغییرات ذخیره شد
                            </div>


                            <div class="mt-1 text-[10px] font-bold leading-6 text-emerald-700">
                                {{ session('success') }}
                            </div>

                        </div>

                    </div>


                    <button
                        type="button"
                        @click="show = false"
                        class="text-sm font-black text-emerald-600"
                    >
                        ×
                    </button>

                </div>

            </div>

        @endif


        {{-- ============================================================
            ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-6 rounded-3xl border border-red-100 bg-red-50 p-5">

                <div class="flex items-start gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-sm font-black text-red-600">
                        !
                    </div>


                    <div class="min-w-0">

                        <div class="text-xs font-black text-red-800">
                            اطلاعات ساعات کاری نیاز به بررسی دارند
                        </div>


                        <div class="mt-2 space-y-1">

                            @foreach($errors->all() as $error)

                                <div class="text-[10px] font-bold leading-6 text-red-700">
                                    • {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- ============================================================
            MAIN GRID
        ============================================================= --}}

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_330px]">


            {{-- ========================================================
                MAIN FORM
            ========================================================= --}}

            <form
                action="{{ route('salon.working-hours.update') }}"
                method="POST"
                class="order-1 space-y-4"
            >

                @csrf

                @method('PUT')


                @foreach($days as $dayNumber => $day)

                    <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                        {{-- Day Header --}}

                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                            <div class="flex items-center gap-3">

                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-700">
                                    {{ $day['short'] }}
                                </div>


                                <div class="min-w-0">

                                    <h2 class="text-sm font-black text-content sm:text-base">
                                        {{ $day['name'] }}
                                    </h2>


                                    <p
                                        class="mt-1 text-[10px] leading-5 text-content-muted"
                                        x-text="
                                            hours[{{ $dayNumber }}].closed
                                                ? 'این روز تعطیل است'
                                                : 'این روز برای رزرو فعال است'
                                        "
                                    ></p>

                                </div>

                            </div>


                            {{-- Closed Switch --}}

                            <div class="flex items-center justify-between gap-3 rounded-2xl bg-primary-50 px-4 py-3 sm:min-w-[150px]">

                                <div>

                                    <div class="text-[10px] font-black text-content">
                                        تعطیل
                                    </div>

                                    <div
                                        class="mt-1 text-[9px] text-content-muted"
                                        x-text="
                                            hours[{{ $dayNumber }}].closed
                                                ? 'رزرو ندارد'
                                                : 'فعال'
                                        "
                                    ></div>

                                </div>


                                <label class="relative inline-flex cursor-pointer items-center">

                                    <input
                                        type="hidden"
                                        name="hours[{{ $dayNumber }}][is_closed]"
                                        value="0"
                                    >


                                    <input
                                        type="checkbox"
                                        name="hours[{{ $dayNumber }}][is_closed]"
                                        value="1"
                                        x-model="hours[{{ $dayNumber }}].closed"
                                        class="peer sr-only"
                                    >


                                    <span class="h-6 w-11 rounded-full bg-content-faint/30 transition peer-checked:bg-accent-600"></span>


                                    <span class="absolute right-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:-translate-x-5"></span>

                                </label>

                            </div>

                        </div>


                        {{-- Day Time Area --}}

                        <div class="border-t border-border bg-primary-50/70 p-5 sm:p-6">

                            <input
                                type="hidden"
                                name="hours[{{ $dayNumber }}][day_of_week]"
                                value="{{ $dayNumber }}"
                            >


                            <div class="grid gap-4 sm:grid-cols-2">

                                {{-- Start --}}

                                <div class="form-group">

                                    <label
                                        for="start-{{ $dayNumber }}"
                                        class="form-label"
                                    >
                                        ساعت شروع
                                    </label>


                                    <select
                                        id="start-{{ $dayNumber }}"
                                        name="hours[{{ $dayNumber }}][start_time]"
                                        x-model="hours[{{ $dayNumber }}].start"
                                        :disabled="hours[{{ $dayNumber }}].closed"
                                        class="form-control text-center disabled:cursor-not-allowed disabled:bg-primary-100 disabled:text-content-faint"
                                        dir="rtl"
                                    >

                                        <option value="">
                                            انتخاب ساعت
                                        </option>


                                        @for($hourIndex = 0; $hourIndex < 24; $hourIndex++)

                                            @for($minute = 0; $minute < 60; $minute += 15)

                                                @php

                                                    $time =
                                                        sprintf(
                                                            '%02d:%02d',
                                                            $hourIndex,
                                                            $minute
                                                        );

                                                    $displayTime =
                                                        strtr(
                                                            $time,
                                                            $persianDigits
                                                        );

                                                @endphp


                                                <option value="{{ $time }}">
                                                    {{ $displayTime }}
                                                </option>

                                            @endfor

                                        @endfor

                                    </select>


                                    @error("hours.$dayNumber.start_time")

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                    @enderror

                                </div>


                                {{-- End --}}

                                <div class="form-group">

                                    <label
                                        for="end-{{ $dayNumber }}"
                                        class="form-label"
                                    >
                                        ساعت پایان
                                    </label>


                                    <select
                                        id="end-{{ $dayNumber }}"
                                        name="hours[{{ $dayNumber }}][end_time]"
                                        x-model="hours[{{ $dayNumber }}].end"
                                        :disabled="hours[{{ $dayNumber }}].closed"
                                        class="form-control text-center disabled:cursor-not-allowed disabled:bg-primary-100 disabled:text-content-faint"
                                        dir="rtl"
                                    >

                                        <option value="">
                                            انتخاب ساعت
                                        </option>


                                        @for($hourIndex = 0; $hourIndex < 24; $hourIndex++)

                                            @for($minute = 0; $minute < 60; $minute += 15)

                                                @php

                                                    $time =
                                                        sprintf(
                                                            '%02d:%02d',
                                                            $hourIndex,
                                                            $minute
                                                        );

                                                    $displayTime =
                                                        strtr(
                                                            $time,
                                                            $persianDigits
                                                        );

                                                @endphp


                                                <option value="{{ $time }}">
                                                    {{ $displayTime }}
                                                </option>

                                            @endfor

                                        @endfor

                                    </select>


                                    @error("hours.$dayNumber.end_time")

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                    @enderror

                                </div>

                            </div>


                            {{-- Status --}}

                            <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-border bg-white p-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-xl"
                                        :class="
                                            hours[{{ $dayNumber }}].closed
                                                ? 'bg-red-50 text-red-500'
                                                : 'bg-emerald-50 text-emerald-600'
                                        "
                                    >
                                        <span
                                            x-text="
                                                hours[{{ $dayNumber }}].closed
                                                    ? '×'
                                                    : '✓'
                                            "
                                        ></span>
                                    </div>


                                    <div>

                                        <div
                                            class="text-xs font-black text-content"
                                            x-text="
                                                hours[{{ $dayNumber }}].closed
                                                    ? 'سالن تعطیل است'
                                                    : 'ساعات کاری فعال است'
                                            "
                                        ></div>


                                        <div class="mt-1 text-[9px] leading-5 text-content-muted">

                                            <span
                                                x-show="!hours[{{ $dayNumber }}].closed"
                                            >
                                                زمان‌های آزاد مشتری بر اساس همین بازه محاسبه می‌شوند.
                                            </span>


                                            <span
                                                x-show="hours[{{ $dayNumber }}].closed"
                                                x-cloak
                                            >
                                                مشتریان برای این روز امکان دریافت نوبت نخواهند داشت.
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    x-show="!hours[{{ $dayNumber }}].closed"
                                    @click="
                                        hours[{{ $dayNumber }}].closed = true;
                                        hours[{{ $dayNumber }}].start = '';
                                        hours[{{ $dayNumber }}].end = '';
                                    "
                                    class="self-start rounded-xl bg-primary-50 px-3 py-2 text-[9px] font-black text-content-soft transition hover:bg-red-50 hover:text-red-600 sm:self-auto"
                                >
                                    بستن این روز
                                </button>


                                <button
                                    type="button"
                                    x-show="hours[{{ $dayNumber }}].closed"
                                    x-cloak
                                    @click="
                                        hours[{{ $dayNumber }}].closed = false;
                                    "
                                    class="self-start rounded-xl bg-accent-50 px-3 py-2 text-[9px] font-black text-accent-700 transition hover:bg-accent-100 sm:self-auto"
                                >
                                    فعال کردن روز
                                </button>

                            </div>

                        </div>

                    </section>

                @endforeach


                {{-- ====================================================
                    BOTTOM INFO
                ===================================================== --}}

                <section class="rounded-3xl border border-accent-100 bg-accent-50 p-5 sm:p-6">

                    <div class="flex items-start gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-accent-600 shadow-soft">

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="8"
                                />

                                <path d="M12 8v4l2.5 1.5" />

                            </svg>

                        </div>


                        <div>

                            <div class="text-xs font-black text-accent-900">
                                این برنامه مبنای نوبت‌گیری است
                            </div>


                            <p class="mt-1 text-[10px] leading-6 text-accent-800/75">
                                بعد از ذخیره، سیستم بر اساس روز کاری، ساعت شروع و پایان، مدت خدمت و نوبت‌های قبلی، زمان‌های قابل رزرو را برای مشتری محاسبه می‌کند.
                            </p>

                        </div>

                    </div>

                </section>


                {{-- Save --}}

                <div class="sticky bottom-4 z-20 flex justify-end">

                    <div class="w-full rounded-2xl border border-border bg-white/95 p-2 shadow-soft backdrop-blur sm:w-auto">

                        <button
                            type="submit"
                            class="btn btn-accent w-full sm:min-w-[220px]"
                        >
                            ذخیره ساعات کاری
                        </button>

                    </div>

                </div>

            </form>


            {{-- ========================================================
                SIDEBAR
            ========================================================= --}}

            <aside class="order-2 space-y-5 lg:sticky lg:top-6 lg:order-2">


                {{-- ====================================================
                    QUICK DEFAULT
                ===================================================== --}}

                <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

                    <div class="bg-primary-950 p-5 text-white sm:p-6">

                        <div class="text-[9px] font-black tracking-[0.18em] text-accent-300">
                            QUICK SETUP
                        </div>


                        <h2 class="mt-2 text-base font-black">
                            برنامه پیشنهادی سالن
                        </h2>


                        <p class="mt-2 text-[10px] leading-6 text-white/55">
                            اگر معمولاً از یک برنامه ثابت استفاده می‌کنید، با یک کلیک آن را روی فرم اعمال کنید.
                        </p>

                    </div>


                    <div class="p-5 sm:p-6">


                        {{-- Preview --}}

                        <div class="overflow-hidden rounded-2xl border border-accent-100 bg-accent-50">

                            <div class="border-b border-accent-100 px-4 py-3">

                                <div class="text-[9px] font-black text-accent-700">
                                    DEFAULT SCHEDULE
                                </div>

                            </div>


                            <div class="divide-y divide-accent-100">

                                <div class="flex items-center justify-between gap-3 px-4 py-3">

                                    <span class="text-[10px] font-black text-accent-900">
                                        شنبه تا پنجشنبه
                                    </span>


                                    <span
                                        class="rounded-lg bg-white px-2 py-1 text-[9px] font-black text-accent-700"
                                        dir="ltr"
                                    >
                                        09:00 - 22:00
                                    </span>

                                </div>


                                <div class="flex items-center justify-between gap-3 px-4 py-3">

                                    <span class="text-[10px] font-black text-accent-900">
                                        جمعه
                                    </span>


                                    <span class="rounded-lg bg-white px-2 py-1 text-[9px] font-black text-accent-700">
                                        تعطیل
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- No Break --}}

                        <div class="mt-4 rounded-2xl bg-primary-50 p-4">

                            <div class="flex items-center gap-2">

                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-content-soft">
                                    ◷
                                </span>


                                <div>

                                    <div class="text-[10px] font-black text-content">
                                        بدون استراحت
                                    </div>

                                    <div class="mt-1 text-[9px] leading-5 text-content-muted">
                                        در نسخه پیش‌فرض زمان استراحت تعریف نشده است.
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- Apply Default --}}

                        <button
                            type="button"
                            @click="applyDefault()"
                            class="btn btn-accent mt-4 w-full"
                        >
                            اعمال برنامه پیش‌فرض
                        </button>


                        <p class="mt-3 text-center text-[9px] leading-5 text-content-faint">
                            این دکمه فقط مقادیر فرم را تغییر می‌دهد؛ برای ذخیره نهایی باید دکمه «ذخیره ساعات کاری» را بزنید.
                        </p>

                    </div>

                </section>


                {{-- ====================================================
                    WORKING LOGIC
                ===================================================== --}}

                <section class="rounded-3xl border border-border bg-white p-5 shadow-soft sm:p-6">

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        BOOKING LOGIC
                    </div>


                    <h2 class="mt-2 text-base font-black text-content">
                        سیستم چطور زمان آزاد را حساب می‌کند؟
                    </h2>


                    <div class="mt-5 space-y-3">


                        <div class="flex items-start gap-3">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-[10px] font-black text-content-soft">
                                ۱
                            </span>


                            <div>

                                <div class="text-[10px] font-black text-content">
                                    ساعات کاری
                                </div>


                                <div class="mt-1 text-[9px] leading-5 text-content-muted">
                                    فقط داخل ساعت شروع و پایان سالن زمان ساخته می‌شود.
                                </div>

                            </div>

                        </div>


                        <div class="flex items-start gap-3">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-[10px] font-black text-content-soft">
                                ۲
                            </span>


                            <div>

                                <div class="text-[10px] font-black text-content">
                                    مدت خدمت
                                </div>


                                <div class="mt-1 text-[9px] leading-5 text-content-muted">
                                    مثلاً خدمت ۶۰ دقیقه‌ای یعنی زمان موردنیاز همان ۶۰ دقیقه در نظر گرفته می‌شود.
                                </div>

                            </div>

                        </div>


                        <div class="flex items-start gap-3">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-[10px] font-black text-content-soft">
                                ۳
                            </span>


                            <div>

                                <div class="text-[10px] font-black text-content">
                                    نوبت‌های قبلی
                                </div>


                                <div class="mt-1 text-[9px] leading-5 text-content-muted">
                                    زمان‌هایی که با نوبت قبلی تداخل دارند، برای مشتری بسته می‌شوند.
                                </div>

                            </div>

                        </div>


                        <div class="flex items-start gap-3">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-[10px] font-black text-content-soft">
                                ۴
                            </span>


                            <div>

                                <div class="text-[10px] font-black text-content">
                                    روز تعطیل
                                </div>


                                <div class="mt-1 text-[9px] leading-5 text-content-muted">
                                    در روز تعطیل هیچ زمانی برای رزرو نمایش داده نمی‌شود.
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- ====================================================
                    Current Status
                ===================================================== --}}

                <section class="rounded-3xl border border-border bg-primary-50 p-5">

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        CURRENT STATUS
                    </div>


                    <div class="mt-3 flex items-center gap-2">

                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>


                        <span class="text-xs font-black text-content">
                            تنظیمات قابل ویرایش است
                        </span>

                    </div>


                    <p class="mt-2 text-[9px] leading-5 text-content-muted">
                        هر تغییر تا قبل از زدن دکمه ذخیره فقط روی فرم باقی می‌ماند.
                    </p>

                </section>

            </aside>

        </div>

    </div>

@endsection

