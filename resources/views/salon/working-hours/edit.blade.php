@extends('layouts.app')

@section('title', 'ساعات کاری')

@section('content')

    @php
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
    @endphp


    <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <div class="mb-3 flex flex-wrap items-center gap-2">

                <span class="badge badge-accent">
                    SALON SETTINGS
                </span>

                <span class="text-[10px] text-content-faint">
                    {{ $salon->name }}
                </span>

            </div>

            <h1 class="text-2xl font-black text-content">
                ساعات کاری سالن
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                زمان فعالیت سالن را از شنبه تا جمعه تنظیم کنید.
            </p>

        </div>


        {{-- ============================================================
            ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-black text-red-600">
                        !
                    </div>

                    <div class="min-w-0">

                        <div class="text-xs font-black text-red-800">
                            اطلاعات ساعات کاری نیاز به بررسی دارند
                        </div>

                        <div class="mt-2 space-y-1 text-[10px] leading-6 text-red-700">

                            @foreach($errors->all() as $error)

                                <div>
                                    • {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- ============================================================
            FORM
        ============================================================= --}}

        <form
            action=""
            method="POST"
            class="space-y-4"
        >

            @csrf

            @method('PUT')


            @foreach($days as $dayNumber => $day)

                @php
                    $hour = $hours->get($dayNumber);

                    $oldClosed = old(
                        "hours.$dayNumber.is_closed",
                        $hour?->is_closed ?? false
                    );

                    $startValue = old(
                        "hours.$dayNumber.start_time",
                        $hour?->start_time
                    );

                    $endValue = old(
                        "hours.$dayNumber.end_time",
                        $hour?->end_time
                    );

                    if ($startValue) {
                        $startValue = substr(
                            $startValue,
                            0,
                            5
                        );
                    }

                    if ($endValue) {
                        $endValue = substr(
                            $endValue,
                            0,
                            5
                        );
                    }
                @endphp


                <section
                    class="card overflow-hidden"
                    x-data="{
                        closed: {{ $oldClosed ? 'true' : 'false' }}
                        }"
                >

                    {{-- ==================================================
                        DAY HEADER
                    =================================================== --}}

                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-700">

                                {{ $day['short'] }}

                            </div>

                            <div>

                                <h2 class="text-sm font-black text-content">
                                    {{ $day['name'] }}
                                </h2>

                                <p
                                    class="mt-1 text-[10px] text-content-muted"
                                    x-text="closed ? 'سالن در این روز تعطیل است' : 'سالن در این روز فعال است'"
                                ></p>

                            </div>

                        </div>


                        {{-- Closed Toggle --}}

                        <label class="flex cursor-pointer items-center gap-3">

                            <input
                                type="hidden"
                                name="hours[{{ $dayNumber }}][is_closed]"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="hours[{{ $dayNumber }}][is_closed]"
                                value="1"
                                x-model="closed"
                                class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                            >

                            <span class="text-xs font-bold text-content">
                                تعطیل
                            </span>

                        </label>

                    </div>


                    {{-- ==================================================
                        TIME AREA
                    =================================================== --}}

                    <div
                        class="border-t border-border bg-primary-50 p-4 sm:p-5"
                        :class="closed ? 'opacity-60' : ''"
                    >

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
                                    class="form-control text-center"
                                    dir="rtl"
                                    :disabled="closed"
                                >

                                    <option value="">
                                        انتخاب ساعت
                                    </option>

                                    @for($hourIndex = 0; $hourIndex < 24; $hourIndex++)

                                        @for($minute = 0; $minute < 60; $minute += 15)

                                            @php
                                                $time = sprintf(
                                                    '%02d:%02d',
                                                    $hourIndex,
                                                    $minute
                                                );

                                                $displayTime = strtr(
                                                    $time,
                                                    $persianDigits
                                                );
                                            @endphp

                                            <option
                                                value="{{ $time }}"
                                                @selected($startValue === $time)
                                            >
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
                                    class="form-control text-center"
                                    dir="rtl"
                                    :disabled="closed"
                                >

                                    <option value="">
                                        انتخاب ساعت
                                    </option>

                                    @for($hourIndex = 0; $hourIndex < 24; $hourIndex++)

                                        @for($minute = 0; $minute < 60; $minute += 15)

                                            @php
                                                $time = sprintf(
                                                    '%02d:%02d',
                                                    $hourIndex,
                                                    $minute
                                                );

                                                $displayTime = strtr(
                                                    $time,
                                                    $persianDigits
                                                );
                                            @endphp

                                            <option
                                                value="{{ $time }}"
                                                @selected($endValue === $time)
                                            >
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


                        {{-- Closed Message --}}

                        <div
                            x-show="closed"
                            x-cloak
                            class="mt-4 rounded-2xl border border-border bg-white px-4 py-3 text-xs font-bold text-content-muted"
                        >
                            در این روز نوبتی برای مشتری‌ها نمایش داده نمی‌شود.
                        </div>

                    </div>

                </section>

            @endforeach


            {{-- ============================================================
                INFO
            ============================================================= --}}

            <div class="rounded-3xl border border-accent-100 bg-accent-50 p-4 sm:p-5">

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

                        <div class="text-xs font-black text-content">
                            این زمان‌بندی مبنای رزرو است
                        </div>

                        <p class="mt-1 text-[10px] leading-6 text-content-muted">
                            بعداً هنگام رزرو، فقط ساعت‌هایی نمایش داده می‌شوند که داخل ساعات کاری سالن باشند و با نوبت‌های قبلی تداخل نداشته باشند.
                        </p>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                ACTIONS
            ============================================================= --}}

            <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">

                <button
                    type="submit"
                    class="btn btn-accent w-full sm:w-auto"
                >
                    ذخیره ساعات کاری
                </button>

            </div>

        </form>

    </div>

@endsection
