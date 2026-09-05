@extends('layouts.salon')

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

        $hoursData = [];

        $oldHours = old('hours');

        foreach ($days as $dayNumber => $day) {
            $existingRows = $hours->get($dayNumber, collect());

            if (is_array($oldHours) && array_key_exists($dayNumber, $oldHours)) {
                $oldDay = $oldHours[$dayNumber];

                $intervals = [];

                foreach (($oldDay['intervals'] ?? []) as $interval) {
                    $intervals[] = [
                        'start' => $interval['start_time'] ?? '',
                        'end' => $interval['end_time'] ?? '',
                    ];
                }

                $hoursData[$dayNumber] = [
                    'closed' => filter_var(
                        $oldDay['is_closed'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    ),
                    'intervals' => $intervals,
                ];

                continue;
            }

            $isClosed = false;
            $intervals = [];

            foreach ($existingRows as $row) {
                if ($row->is_closed) {
                    $isClosed = true;
                    continue;
                }

                if (
                    !$row->start_time ||
                    !$row->end_time
                ) {
                    continue;
                }

                $intervals[] = [
                    'start' => substr(
                        (string) $row->start_time,
                        0,
                        5
                    ),

                    'end' => substr(
                        (string) $row->end_time,
                        0,
                        5
                    ),
                ];
            }

            if ($existingRows->isEmpty()) {
                $isClosed = false;
            }

            if ($isClosed) {
                $intervals = [];
            }

            $hoursData[$dayNumber] = [
                'closed' => $isClosed,
                'intervals' => $intervals,
            ];
        }
    @endphp

    <script>
        function workingHoursPage() {
            return {
                hours: @js($hoursData),

                copyModalOpen: false,

                copySourceDay: null,

                copyTargets: {
                    0: false,
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: false,
                    6: false,
                },

                defaultSchedule() {
                    return {
                        0: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        1: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        2: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        3: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        4: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        5: {
                            closed: false,
                            intervals: [
                                {
                                    start: '09:00',
                                    end: '22:00'
                                }
                            ]
                        },

                        6: {
                            closed: true,
                            intervals: []
                        }
                    };
                },

                applyDefault() {
                    if (
                        !confirm(
                            'برنامه پیش‌فرض روی ساعات فعلی اعمال شود؟'
                        )
                    ) {
                        return;
                    }

                    const defaults = this.defaultSchedule();

                    Object.entries(defaults).forEach(
                        ([day, value]) => {
                            this.hours[day] = JSON.parse(
                                JSON.stringify(value)
                            );
                        }
                    );
                },

                openCopyModal(day) {
                    this.copySourceDay = Number(day);

                    Object.keys(this.copyTargets).forEach(
                        dayNumber => {
                            this.copyTargets[dayNumber] = false;
                        }
                    );

                    this.copyModalOpen = true;
                },

                closeCopyModal() {
                    this.copyModalOpen = false;
                    this.copySourceDay = null;
                },

                selectWorkingDays() {
                    Object.keys(this.copyTargets).forEach(
                        dayNumber => {
                            const number = Number(dayNumber);

                            this.copyTargets[dayNumber] =
                                number >= 1 &&
                                number <= 5 &&
                                number !== this.copySourceDay;
                        }
                    );
                },

                applyCopy() {
                    if (this.copySourceDay === null) {
                        return;
                    }

                    const targets = Object.entries(
                        this.copyTargets
                    )
                        .filter(
                            ([day, selected]) =>
                                selected &&
                                Number(day) !== this.copySourceDay
                        )
                        .map(
                            ([day]) => Number(day)
                        );

                    if (!targets.length) {
                        alert(
                            'حداقل یک روز را انتخاب کن.'
                        );

                        return;
                    }

                    const source =
                        this.hours[this.copySourceDay];

                    targets.forEach(day => {
                        this.hours[day] =
                            JSON.parse(
                                JSON.stringify(source)
                            );
                    });

                    this.closeCopyModal();
                },

                addInterval(day) {
                    if (
                        this.hours[day].closed
                    ) {
                        this.hours[day].closed = false;
                    }

                    this.hours[day].intervals.push({
                        start: '',
                        end: ''
                    });
                },

                removeInterval(day, index) {
                    this.hours[day].intervals.splice(
                        index,
                        1
                    );

                    if (
                        this.hours[day].intervals.length === 0
                    ) {
                        this.hours[day].closed = true;
                    }
                },

                closeDay(day) {
                    this.hours[day].closed = true;
                    this.hours[day].intervals = [];
                },

                openDay(day) {
                    this.hours[day].closed = false;

                    if (
                        this.hours[day].intervals.length === 0
                    ) {
                        this.hours[day].intervals.push({
                            start: '09:00',
                            end: '22:00'
                        });
                    }
                },

                hasIntervals(day) {
                    return (
                        !this.hours[day].closed &&
                        this.hours[day].intervals.length > 0
                    );
                }
            };
        }
    </script>

    <div
        x-data="workingHoursPage()"
        dir="rtl"
        class="mx-auto w-full max-w-5xl px-4 py-6 pb-32 sm:px-6 lg:px-8 lg:py-8"
    >

        {{-- Header --}}
        <div class="mb-6">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="mb-2 text-[10px] font-black text-accent-600">
                        {{ $salon->name }}
                    </div>

                    <h1 class="text-2xl font-black text-content sm:text-3xl">
                        ساعات کاری
                    </h1>

                    <p class="mt-2 text-xs leading-6 text-content-muted sm:text-sm">
                        فقط ساعت‌هایی را وارد کن که واقعاً نوبت می‌پذیری.
                    </p>

                </div>

                <a
                    href="{{ route('salon.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-border bg-white px-4 py-3 text-xs font-black text-content transition hover:border-accent-200 hover:text-accent-600"
                >
                    ← داشبورد
                </a>

            </div>

        </div>


        {{-- Success --}}
        @if(session('success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
            >

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white font-black text-emerald-600">
                            ✓
                        </div>

                        <div>
                            <div class="text-xs font-black text-emerald-800">
                                ذخیره شد
                            </div>

                            <div class="mt-1 text-[10px] text-emerald-700">
                                {{ session('success') }}
                            </div>
                        </div>

                    </div>

                    <button
                        type="button"
                        @click="show = false"
                        class="text-lg font-black text-emerald-600"
                    >
                        ×
                    </button>

                </div>

            </div>

        @endif


        {{-- Errors --}}
        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                <div class="text-xs font-black text-red-800">
                    لطفاً ساعات کاری را بررسی کن
                </div>

                <div class="mt-2 space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="text-[10px] font-bold leading-6 text-red-700">
                            • {{ $error }}
                        </div>
                    @endforeach
                </div>

            </div>

        @endif


        {{-- Quick Actions --}}
        <div class="mb-5 grid gap-3 sm:grid-cols-2">

            <button
                type="button"
                @click="applyDefault()"
                class="rounded-2xl border border-border bg-white p-4 text-right shadow-soft transition hover:border-accent-200 hover:-translate-y-0.5"
            >

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                        ⚡
                    </div>

                    <div>
                        <div class="text-xs font-black text-content">
                            برنامه پیشنهادی
                        </div>

                        <div class="mt-1 text-[10px] text-content-muted">
                            شنبه تا پنجشنبه ۰۹ تا ۲۲
                            · جمعه تعطیل
                        </div>
                    </div>

                </div>

            </button>


            <div class="rounded-2xl border border-border bg-primary-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="text-lg">
                        💡
                    </div>

                    <div>
                        <div class="text-xs font-black text-content">
                            بین دو بازه رزرو نمی‌شود
                        </div>

                        <div class="mt-1 text-[10px] leading-5 text-content-muted">
                            مثلاً ۰۹ تا ۱۳ و ۱۴ تا ۲۲ یعنی بین ۱۳ تا ۱۴ نوبتی نمایش داده نمی‌شود.
                        </div>
                    </div>

                </div>

            </div>

        </div>


        {{-- Main Form --}}
        <form
            action="{{ route('salon.working-hours.update') }}"
            method="POST"
            class="space-y-3"
        >

            @csrf
            @method('PUT')


            @foreach($days as $dayNumber => $day)

                <section
                    class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft"
                >

                    {{-- Day Header --}}
                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-700">
                                {{ $day['short'] }}
                            </div>

                            <div>

                                <div class="text-sm font-black text-content sm:text-base">
                                    {{ $day['name'] }}
                                </div>

                                <div
                                    class="mt-1 text-[10px] font-bold"
                                    :class="
                                        hours[{{ $dayNumber }}].closed
                                            ? 'text-content-faint'
                                            : 'text-emerald-600'
                                    "
                                    x-text="
                                        hours[{{ $dayNumber }}].closed
                                            ? 'تعطیل'
                                            : 'باز و آماده رزرو'
                                    "
                                ></div>

                            </div>

                        </div>


                        {{-- Close/Open --}}
                        <button
                            type="button"
                            @click="
                                hours[{{ $dayNumber }}].closed
                                    ? openDay({{ $dayNumber }})
                                    : closeDay({{ $dayNumber }})
                            "
                            class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-[10px] font-black transition"
                            :class="
                                hours[{{ $dayNumber }}].closed
                                    ? 'bg-primary-100 text-content'
                                    : 'bg-emerald-50 text-emerald-700'
                            "
                        >

                            <span
                                x-text="
                                    hours[{{ $dayNumber }}].closed
                                        ? 'باز کردن روز'
                                        : 'تعطیل کردن'
                                "
                            ></span>

                        </button>

                    </div>


                    {{-- Day Content --}}
                    <div
                        x-show="!hours[{{ $dayNumber }}].closed"
                        x-transition
                        class="border-t border-border bg-primary-50/60 p-4 sm:p-5"
                    >

                        <input
                            type="hidden"
                            name="hours[{{ $dayNumber }}][day_of_week]"
                            value="{{ $dayNumber }}"
                        >

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
                            class="hidden"
                        >


                        <div class="space-y-2">

                            <template
                                x-for="(interval, intervalIndex) in hours[{{ $dayNumber }}].intervals"
                                :key="intervalIndex"
                            >

                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-white p-3 sm:flex-row sm:items-end">

                                    <div class="grid flex-1 grid-cols-2 gap-2">

                                        {{-- Start --}}
                                        <div>

                                            <label class="mb-1.5 block text-[9px] font-black text-content-muted">
                                                از
                                            </label>

                                            <select
                                                :name="`hours[{{ $dayNumber }}][intervals][${intervalIndex}][start_time]`"
                                                x-model="interval.start"
                                                class="form-control h-11 text-center"
                                                dir="rtl"
                                            >

                                                <option value="">
                                                    انتخاب
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

                                                        <option value="{{ $time }}">
                                                            {{ $displayTime }}
                                                        </option>

                                                    @endfor

                                                @endfor

                                            </select>

                                        </div>


                                        {{-- End --}}
                                        <div>

                                            <label class="mb-1.5 block text-[9px] font-black text-content-muted">
                                                تا
                                            </label>

                                            <select
                                                :name="`hours[{{ $dayNumber }}][intervals][${intervalIndex}][end_time]`"
                                                x-model="interval.end"
                                                class="form-control h-11 text-center"
                                                dir="rtl"
                                            >

                                                <option value="">
                                                    انتخاب
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

                                                        <option value="{{ $time }}">
                                                            {{ $displayTime }}
                                                        </option>

                                                    @endfor

                                                @endfor

                                            </select>

                                        </div>

                                    </div>


                                    {{-- Delete --}}
                                    <button
                                        type="button"
                                        @click="
                                            removeInterval(
                                                {{ $dayNumber }},
                                                intervalIndex
                                            )
                                        "
                                        class="flex h-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100"
                                    >
                                        حذف
                                    </button>

                                </div>

                            </template>


                            {{-- Add interval --}}
                            <button
                                type="button"
                                @click="addInterval({{ $dayNumber }})"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-dashed border-accent-200 bg-white py-3 text-[10px] font-black text-accent-600 transition hover:bg-accent-50"
                            >
                                <span class="text-base">+</span>
                                افزودن بازه
                            </button>

                        </div>


                        {{-- Copy --}}
                        <div class="mt-3 border-t border-border pt-3">

                            <button
                                type="button"
                                @click="openCopyModal({{ $dayNumber }})"
                                class="text-[10px] font-black text-content-muted transition hover:text-accent-600"
                            >
                                کپی این ساعت برای روزهای دیگر ←
                            </button>

                        </div>

                    </div>


                    {{-- Closed Content --}}
                    <div
                        x-show="hours[{{ $dayNumber }}].closed"
                        x-transition
                        class="border-t border-border bg-primary-50/50 px-4 py-5 sm:px-5"
                    >

                        <input
                            type="hidden"
                            name="hours[{{ $dayNumber }}][day_of_week]"
                            value="{{ $dayNumber }}"
                        >

                        <input
                            type="hidden"
                            name="hours[{{ $dayNumber }}][is_closed]"
                            value="1"
                        >

                        <div class="flex items-center justify-between gap-4">

                            <div>

                                <div class="text-xs font-black text-content">
                                    این روز تعطیل است
                                </div>

                                <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                    مشتری در این روز هیچ زمان قابل رزروی نمی‌بیند.
                                </div>

                            </div>

                            <button
                                type="button"
                                @click="openDay({{ $dayNumber }})"
                                class="rounded-xl bg-white px-4 py-2.5 text-[10px] font-black text-content shadow-sm"
                            >
                                باز کردن
                            </button>

                        </div>

                    </div>

                </section>

            @endforeach


            {{-- Save --}}
            <div class="sticky bottom-4 z-20 pt-3">

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-accent-600 px-5 py-4 text-sm font-black text-white shadow-lg transition hover:bg-accent-700"
                >
                    ذخیره ساعات کاری
                    <span>✓</span>
                </button>

            </div>

        </form>


        {{-- Copy Modal --}}
        <div
            x-show="copyModalOpen"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-3 sm:items-center"
            @keydown.escape.window="closeCopyModal()"
        >

            <div
                @click.outside="closeCopyModal()"
                x-transition
                class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
            >

                <div class="p-5 sm:p-6">

                    <div class="mb-5 flex items-start justify-between gap-4">

                        <div>

                            <div class="text-base font-black text-content">
                                اعمال این ساعت برای...
                            </div>

                            <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                ساعت‌های انتخاب‌شده روی روزهای مقصد کپی می‌شوند.
                            </div>

                        </div>

                        <button
                            type="button"
                            @click="closeCopyModal()"
                            class="text-lg font-black text-content-faint"
                        >
                            ×
                        </button>

                    </div>


                    <div class="mb-4 rounded-2xl bg-primary-50 p-3">

                        <div class="text-[9px] font-black text-content-muted">
                            ساعت فعلی
                        </div>

                        <div class="mt-2 space-y-1">

                            <template
                                x-for="interval in (
                                    copySourceDay !== null
                                        ? hours[copySourceDay].intervals
                                        : []
                                )"
                                :key="interval.start + interval.end"
                            >

                                <div
                                    class="text-xs font-black text-content"
                                    dir="ltr"
                                >
                                    <span x-text="interval.start"></span>
                                    <span class="px-1">—</span>
                                    <span x-text="interval.end"></span>
                                </div>

                            </template>

                            <div
                                x-show="
                                    copySourceDay !== null &&
                                    hours[copySourceDay].closed
                                "
                                class="text-xs font-black text-content"
                            >
                                تعطیل
                            </div>

                        </div>

                    </div>


                    <button
                        type="button"
                        @click="selectWorkingDays()"
                        class="mb-4 w-full rounded-xl bg-primary-50 py-2.5 text-[10px] font-black text-content transition hover:bg-primary-100"
                    >
                        انتخاب روزهای کاری
                    </button>


                    <div class="space-y-2">

                        @foreach($days as $dayNumber => $day)

                            <label
                                class="flex cursor-pointer items-center justify-between rounded-2xl border border-border bg-white px-4 py-3 transition hover:bg-primary-50"
                                x-show="copySourceDay !== {{ $dayNumber }}"
                            >

                                <span class="text-xs font-black text-content">
                                    {{ $day['name'] }}
                                </span>

                                <input
                                    type="checkbox"
                                    x-model="copyTargets[{{ $dayNumber }}]"
                                    class="h-5 w-5 rounded border-border text-accent-600 focus:ring-accent-500"
                                >

                            </label>

                        @endforeach

                    </div>


                    <div class="mt-5 grid grid-cols-2 gap-2">

                        <button
                            type="button"
                            @click="closeCopyModal()"
                            class="rounded-2xl border border-border bg-white py-3 text-xs font-black text-content"
                        >
                            انصراف
                        </button>

                        <button
                            type="button"
                            @click="applyCopy()"
                            class="rounded-2xl bg-accent-600 py-3 text-xs font-black text-white"
                        >
                            اعمال
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
