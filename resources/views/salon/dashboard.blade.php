@extends('layouts.salon')

@section('title', 'داشبورد سالن')

@section('content')

    @php
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
        | Stats
        |--------------------------------------------------------------------------
        */

        $todayBookingsText = strtr(
            (string) $todayBookings,
            $persianDigits
        );

        $pendingBookingsText = strtr(
            (string) $pendingBookings,
            $persianDigits
        );

        $barbersText = strtr(
            (string) $salon->barbers_count,
            $persianDigits
        );

        $servicesText = strtr(
            (string) $salon->services_count,
            $persianDigits
        );

        $notificationsText = strtr(
            (string) $unreadNotifications,
            $persianDigits
        );


        /*
        |--------------------------------------------------------------------------
        | Days
        |--------------------------------------------------------------------------
        */

        $days = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ];
    @endphp


    <div class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8">


        {{-- ============================================================
            HERO HEADER
        ============================================================= --}}

        <section class="mb-7 overflow-hidden rounded-[2rem] border border-border bg-white shadow-soft">

            <div class="relative overflow-hidden bg-primary-950 px-5 py-7 text-white sm:px-7 sm:py-8">

                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-accent-500/10 blur-3xl"></div>
                <div class="absolute -left-16 -bottom-20 h-48 w-48 rounded-full bg-white/5 blur-3xl"></div>


                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                    <div class="min-w-0">

                        <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>

                            <span class="text-[9px] font-black tracking-wider text-white/75">
                                SALON PANEL
                            </span>

                        </div>


                        <h1 class="text-2xl font-black sm:text-3xl">
                            سلام،
                            {{ auth()->user()->name }}
                            👋
                        </h1>


                        <p class="mt-3 max-w-2xl text-xs leading-7 text-white/65 sm:text-sm">
                            از اینجا می‌توانید
                            <span class="font-black text-white">
                                {{ $salon->name }}
                            </span>
                            را مدیریت کنید، نوبت‌ها را بررسی کنید و صفحه عمومی سالن را به‌روز نگه دارید.
                        </p>

                    </div>


                    <div class="flex flex-wrap items-center gap-3">

                        <a
                            href="{{ route('public.salons.show', $salon) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-xs font-black text-primary-950 transition hover:-translate-y-0.5"
                        >
                            مشاهده صفحه سالن
                        </a>


                        <a
                            href="{{ route('salon.bookings.create') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-xs font-black text-white transition hover:bg-white/10"
                        >
                            + رزرو دستی
                        </a>

                    </div>

                </div>

            </div>


            {{-- Salon mini profile --}}

            <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">

                <div class="flex items-center gap-3">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-50 text-sm font-black text-content-soft">

                        @if($salon->logo_path)

                            <img
                                src="{{ $salon->logo_url }}"
                                alt="{{ $salon->name }}"
                                class="h-full w-full object-cover"
                            >

                        @else

                            {{ mb_substr($salon->name, 0, 1) }}

                        @endif

                    </div>


                    <div class="min-w-0">

                        <div class="truncate text-sm font-black text-content">
                            {{ $salon->name }}
                        </div>

                        <div class="mt-1 text-[10px] text-content-muted">
                            پنل مدیریت سالن
                        </div>

                    </div>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    @if($salon->is_active)

                        <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-[10px] font-black text-emerald-700">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                            سالن فعال است

                        </span>

                    @else

                        <span class="rounded-xl bg-red-50 px-3 py-2 text-[10px] font-black text-red-700">
                            سالن غیرفعال است
                        </span>

                    @endif

                </div>

            </div>

        </section>


        {{-- ============================================================
            NOTIFICATIONS
        ============================================================= --}}

        @if($unreadNotifications > 0)

            <a
                href="{{ route('salon.notifications.index') }}"
                class="mb-7 flex items-center justify-between gap-4 rounded-2xl border border-accent-100 bg-accent-50 p-4 transition hover:-translate-y-0.5 hover:border-accent-200"
            >

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-accent-600 shadow-soft">
                        ♧
                    </div>

                    <div>

                        <div class="text-xs font-black text-content">
                            اعلان جدید داری
                        </div>

                        <div class="mt-1 text-[10px] leading-5 text-content-muted">
                            {{ $notificationsText }}
                            اعلان خوانده‌نشده برای شما وجود دارد.
                        </div>

                    </div>

                </div>


                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-sm font-black text-accent-600 shadow-soft">
                    ←
                </span>

            </a>

        @endif


        {{-- ============================================================
            STATS
        ============================================================= --}}

        <section class="mb-8">

            <div class="mb-4">

                <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                    OVERVIEW
                </div>

                <h2 class="mt-1 text-base font-black text-content">
                    وضعیت امروز سالن
                </h2>

            </div>


            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">


                {{-- Today bookings --}}

                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-accent-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                نوبت‌های امروز
                            </div>

                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $todayBookingsText }}
                            </div>

                            <div class="mt-2 text-[9px] font-bold text-accent-600">
                                مشاهده نوبت‌ها ←
                            </div>

                        </div>


                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                            ◷
                        </div>

                    </div>

                </a>


                {{-- Pending --}}

                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-amber-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                در انتظار بررسی
                            </div>

                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $pendingBookingsText }}
                            </div>

                            <div class="mt-2 text-[9px] font-bold text-amber-600">
                                بررسی درخواست‌ها ←
                            </div>

                        </div>


                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            ⌛
                        </div>

                    </div>

                </a>


                {{-- Barbers --}}

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-sky-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                اعضای تیم
                            </div>

                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $barbersText }}
                            </div>

                            <div class="mt-2 text-[9px] font-bold text-content-soft">
                                مدیریت آرایشگران ←
                            </div>

                        </div>


                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary-50 text-content-soft">
                            ♙
                        </div>

                    </div>

                </a>


                {{-- Notifications --}}

                <a
                    href="{{ route('salon.notifications.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-violet-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                اعلان‌های جدید
                            </div>

                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $notificationsText }}
                            </div>

                            <div class="mt-2 text-[9px] font-bold text-content-soft">
                                مشاهده اعلان‌ها ←
                            </div>

                        </div>


                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary-50 text-content-soft">
                            ♧
                        </div>

                    </div>

                </a>

            </div>

        </section>


        {{-- ============================================================
            QUICK ACTIONS
        ============================================================= --}}

        <section class="mb-8">

            <div class="mb-4 flex items-end justify-between gap-4">

                <div>

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        MANAGEMENT
                    </div>

                    <h2 class="mt-1 text-base font-black text-content">
                        مدیریت سریع
                    </h2>

                </div>

            </div>


            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">


                {{-- Bookings --}}

                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-accent-200"
                >

                    <div class="flex items-center justify-between">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                            ◷
                        </div>

                        <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                            ←
                        </span>

                    </div>


                    <h3 class="mt-4 text-sm font-black text-content">
                        نوبت‌ها
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-content-muted">
                        مشاهده و مدیریت رزروهای مشتریان
                    </p>

                </a>


                {{-- Barbers --}}

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-sky-200"
                >

                    <div class="flex items-center justify-between">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                            ♙
                        </div>

                        <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                            ←
                        </span>

                    </div>


                    <h3 class="mt-4 text-sm font-black text-content">
                        آرایشگران
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-content-muted">
                        مدیریت اعضای تیم و اطلاعات آن‌ها
                    </p>

                </a>


                {{-- Services --}}

                <a
                    href="{{ route('salon.services.index') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-amber-200"
                >

                    <div class="flex items-center justify-between">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            ✂
                        </div>

                        <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                            ←
                        </span>

                    </div>


                    <h3 class="mt-4 text-sm font-black text-content">
                        خدمات
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-content-muted">
                        قیمت، مدت و خدمات قابل رزرو
                    </p>

                </a>


                {{-- Working Hours --}}

                <a
                    href="{{ route('salon.working-hours.edit') }}"
                    class="group rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-emerald-200"
                >

                    <div class="flex items-center justify-between">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            ◷
                        </div>

                        <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                            ←
                        </span>

                    </div>


                    <h3 class="mt-4 text-sm font-black text-content">
                        ساعات کاری
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-content-muted">
                        تنظیم برنامه هفتگی سالن
                    </p>

                </a>

            </div>

        </section>


        {{-- ============================================================
            SECONDARY MANAGEMENT
        ============================================================= --}}

        <section class="mb-8 grid gap-4 lg:grid-cols-3">


            {{-- Portfolio --}}

            <a
                href="{{ route('salon.portfolio.index') }}"
                class="group overflow-hidden rounded-3xl border border-border bg-white shadow-soft transition hover:-translate-y-1 hover:border-violet-200 lg:col-span-1"
            >

                <div class="bg-gradient-to-br from-primary-950 to-primary-900 p-6 text-white">

                    <div class="flex items-start justify-between gap-4">

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white">

                            <svg
                                width="22"
                                height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="16"
                                    rx="2"
                                />

                                <circle
                                    cx="8.5"
                                    cy="9"
                                    r="1.5"
                                />

                                <path
                                    d="m5 17 4.5-4.5 3 3 2.5-2.5 4 4"
                                />

                            </svg>

                        </div>


                        <span class="text-lg text-white/50 transition group-hover:-translate-x-1">
                            ←
                        </span>

                    </div>


                    <div class="mt-8">

                        <div class="text-[9px] font-black tracking-[0.18em] text-white/40">
                            PORTFOLIO
                        </div>

                        <h3 class="mt-2 text-lg font-black">
                            نمونه‌کارهای سالن
                        </h3>

                        <p class="mt-2 text-[10px] leading-6 text-white/60">
                            کارهای قبل و بعد، مدل مو، رنگ، میکاپ و سایر نمونه‌کارها را مدیریت کنید.
                        </p>

                    </div>

                </div>


                <div class="flex items-center justify-between px-6 py-4">

                    <span class="text-[10px] font-bold text-content-muted">
                        مدیریت نمونه‌کارها
                    </span>

                    <span class="text-sm font-black text-accent-600">
                        ورود ←
                    </span>

                </div>

            </a>


            {{-- Reviews --}}

            <a
                href="{{ route('salon.reviews.index') }}"
                class="group rounded-3xl border border-border bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:border-accent-200"
            >

                <div class="flex items-start justify-between gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                        ★
                    </div>

                    <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                        ←
                    </span>

                </div>


                <div class="mt-8">

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        REVIEWS
                    </div>

                    <h3 class="mt-2 text-lg font-black text-content">
                        نظرات مشتریان
                    </h3>

                    <p class="mt-2 text-[10px] leading-6 text-content-muted">
                        بررسی، انتشار و مدیریت بازخورد مشتریان.
                    </p>

                </div>

            </a>


            {{-- Notifications --}}

            <a
                href="{{ route('salon.notifications.index') }}"
                class="group rounded-3xl border border-border bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:border-amber-200"
            >

                <div class="flex items-start justify-between gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        ♧
                    </div>

                    <span class="text-lg text-content-faint transition group-hover:-translate-x-1">
                        ←
                    </span>

                </div>


                <div class="mt-8">

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        NOTIFICATIONS
                    </div>

                    <h3 class="mt-2 text-lg font-black text-content">
                        اعلان‌ها
                    </h3>

                    <p class="mt-2 text-[10px] leading-6 text-content-muted">
                        درخواست‌های جدید و پیام‌های مهم سیستم را ببینید.
                    </p>

                </div>

            </a>

        </section>


        {{-- ============================================================
            WORKING HOURS
        ============================================================= --}}

        <section class="mb-8 overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

            <div class="flex flex-col gap-3 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>

                    <div class="text-[9px] font-black tracking-[0.18em] text-accent-600">
                        SCHEDULE
                    </div>

                    <h2 class="mt-1 text-base font-black text-content">
                        برنامه هفتگی سالن
                    </h2>

                </div>


                <a
                    href="{{ route('salon.working-hours.edit') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-primary-50 px-3 py-2 text-[10px] font-black text-content-soft transition hover:bg-primary-100"
                >
                    ویرایش ساعات کاری
                </a>

            </div>


            <div class="grid gap-px bg-border sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">

                @foreach($days as $dayNumber => $dayName)

                    @php
                        $hour = $salon->workingHours
                            ->firstWhere(
                                'day_of_week',
                                $dayNumber
                            );

                        $isClosed =
                            $hour?->is_closed ?? false;

                        $start =
                            $hour?->start_time
                                ? substr(
                                    $hour->start_time,
                                    0,
                                    5
                                )
                                : null;

                        $end =
                            $hour?->end_time
                                ? substr(
                                    $hour->end_time,
                                    0,
                                    5
                                )
                                : null;
                    @endphp


                    <div class="bg-white p-4">

                        <div class="flex items-center justify-between gap-2">

                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary-50 text-[10px] font-black text-content-soft">
                                {{ mb_substr($dayName, 0, 1) }}
                            </div>


                            @if($isClosed)

                                <span class="rounded-lg bg-red-50 px-2 py-1 text-[9px] font-black text-red-600">
                                    تعطیل
                                </span>

                            @elseif($start && $end)

                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                            @else

                                <span class="h-2 w-2 rounded-full bg-amber-400"></span>

                            @endif

                        </div>


                        <div class="mt-4 text-xs font-black text-content">
                            {{ $dayName }}
                        </div>


                        <div class="mt-2 text-[10px] font-bold text-content-muted" dir="ltr">

                            @if($isClosed)

                                تعطیل

                            @elseif($start && $end)

                                {{ strtr($start, $persianDigits) }}
                                -
                                {{ strtr($end, $persianDigits) }}

                            @else

                                تنظیم نشده

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        </section>


        {{-- ============================================================
            SALON STATUS
        ============================================================= --}}

        <section class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">

            <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-50 text-sm font-black text-content-soft">

                        @if($salon->logo_path)

                            <img
                                src="{{ $salon->logo_url }}"
                                alt="{{ $salon->name }}"
                                class="h-full w-full object-cover"
                            >

                        @else

                            {{ mb_substr($salon->name, 0, 1) }}

                        @endif

                    </div>


                    <div>

                        <div class="text-sm font-black text-content">
                            {{ $salon->name }}
                        </div>

                        <div class="mt-1 text-[10px] text-content-muted">
                            صفحه عمومی سالن
                        </div>

                    </div>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    @if($salon->is_active)

                        <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-[10px] font-black text-emerald-700">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                            فعال و قابل مشاهده

                        </span>

                    @else

                        <span class="rounded-xl bg-red-50 px-3 py-2 text-[10px] font-black text-red-700">
                            غیرفعال
                        </span>

                    @endif


                    <a
                        href="{{ route('public.salons.show', $salon) }}"
                        target="_blank"
                        rel="noopener"
                        class="btn btn-secondary btn-sm"
                    >
                        مشاهده صفحه سالن
                    </a>

                </div>

            </div>

        </section>

    </div>

@endsection

