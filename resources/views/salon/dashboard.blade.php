@extends('layouts.app')

@section('title', 'داشبورد سالن')

@section('content')

    @php
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
    @endphp


    <div class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8">


        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                SALON PANEL
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <h1 class="text-2xl font-black text-content">
                        سلام،
                        {{ auth()->user()->name }}
                        👋
                    </h1>

                    <p class="mt-2 text-xs leading-6 text-content-muted">
                        اینجا می‌توانید {{ $salon->name }} را مدیریت کنید.
                    </p>

                </div>


                <a
                    href="{{ route('public.salons.show', $salon) }}"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-secondary"
                >
                    مشاهده صفحه سالن
                </a>

            </div>

        </div>


        {{-- ============================================================
            NOTIFICATION ALERT
        ============================================================= --}}

        @if($unreadNotifications > 0)

            <a
                href="{{ route('salon.notifications.index') }}"
                class="mb-5 flex items-center justify-between gap-4 rounded-2xl border border-accent-100 bg-accent-50 p-4 transition hover:border-accent-200"
            >

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-accent-600 shadow-soft">

                        <svg
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                            <path d="M10 21h4" />
                        </svg>

                    </div>

                    <div>

                        <div class="text-xs font-black text-content">
                            اعلان جدید داری
                        </div>

                        <div class="mt-1 text-[10px] text-content-muted">
                            {{ $notificationsText }}
                            اعلان خوانده‌نشده
                        </div>

                    </div>

                </div>


                <span class="text-sm font-black text-accent-600">
                    ←
                </span>

            </a>

        @endif


        {{-- ============================================================
            TODAY SUMMARY
        ============================================================= --}}

        <section class="mb-6 grid gap-3 sm:grid-cols-3">


            {{-- Today --}}

            <a
                href="{{ route('salon.bookings.index') }}"
                class="card p-4 transition hover:-translate-y-0.5"
            >

                <div class="flex items-center justify-between">

                    <div>

                        <div class="text-[10px] font-bold text-content-muted">
                            نوبت‌های امروز
                        </div>

                        <div class="mt-2 text-2xl font-black text-content">
                            {{ $todayBookingsText }}
                        </div>

                    </div>


                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="4"
                                y="5"
                                width="16"
                                height="16"
                                rx="2"
                            />

                            <path d="M8 3v4" />
                            <path d="M16 3v4" />
                            <path d="M4 10h16" />
                        </svg>

                    </div>

                </div>

            </a>


            {{-- Pending --}}

            <a
                href="{{ route('salon.bookings.index') }}"
                class="card p-4 transition hover:-translate-y-0.5"
            >

                <div class="flex items-center justify-between">

                    <div>

                        <div class="text-[10px] font-bold text-content-muted">
                            در انتظار بررسی
                        </div>

                        <div class="mt-2 text-2xl font-black text-content">
                            {{ $pendingBookingsText }}
                        </div>

                    </div>


                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">

                        <svg
                            width="21"
                            height="21"
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

                </div>

            </a>


            {{-- Notifications --}}

            <a
                href="{{ route('salon.notifications.index') }}"
                class="card p-4 transition hover:-translate-y-0.5"
            >

                <div class="flex items-center justify-between">

                    <div>

                        <div class="text-[10px] font-bold text-content-muted">
                            اعلان‌ها
                        </div>

                        <div class="mt-2 text-2xl font-black text-content">
                            {{ $notificationsText }}
                        </div>

                    </div>


                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-content-soft">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                            <path d="M10 21h4" />
                        </svg>

                    </div>

                </div>

            </a>

        </section>


        {{-- ============================================================
            MANAGEMENT GRID
        ============================================================= --}}

        <section>

            <div class="mb-4">

                <div class="text-[10px] font-black tracking-wider text-content-faint">
                    MANAGEMENT
                </div>

                <h2 class="mt-1 text-base font-black text-content">
                    مدیریت سالن
                </h2>

            </div>


            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">


                {{-- Barbers --}}

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="card group p-5 transition hover:-translate-y-0.5"
                >

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle
                                cx="12"
                                cy="8"
                                r="3.5"
                            />

                            <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                        </svg>

                    </div>


                    <div class="mt-4 flex items-end justify-between gap-3">

                        <div>

                            <h3 class="text-sm font-black text-content">
                                آرایشگرها
                            </h3>

                            <p class="mt-1 text-[10px] leading-5 text-content-muted">
                                مدیریت اعضای سالن
                            </p>

                        </div>

                        <span class="text-lg font-black text-content">
                            {{ $barbersText }}
                        </span>

                    </div>

                </a>


                {{-- Services --}}

                <a
                    href="{{ route('salon.services.index') }}"
                    class="card group p-5 transition hover:-translate-y-0.5"
                >

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 4h12" />
                            <path d="M6 8h12" />
                            <path d="M8 4v16" />
                            <path d="M16 4v16" />
                        </svg>

                    </div>


                    <div class="mt-4 flex items-end justify-between gap-3">

                        <div>

                            <h3 class="text-sm font-black text-content">
                                خدمات
                            </h3>

                            <p class="mt-1 text-[10px] leading-5 text-content-muted">
                                قیمت و مدت خدمات
                            </p>

                        </div>

                        <span class="text-lg font-black text-content">
                            {{ $servicesText }}
                        </span>

                    </div>

                </a>


                {{-- Working Hours --}}

                <a
                    href="{{ route('salon.working-hours.edit') }}"
                    class="card group p-5 transition hover:-translate-y-0.5"
                >

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                        <svg
                            width="21"
                            height="21"
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


                    <div class="mt-4">

                        <h3 class="text-sm font-black text-content">
                            ساعات کاری
                        </h3>

                        <p class="mt-1 text-[10px] leading-5 text-content-muted">
                            برنامه هفتگی سالن
                        </p>

                    </div>

                </a>


                {{-- Bookings --}}

                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="card group p-5 transition hover:-translate-y-0.5"
                >

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="4"
                                y="5"
                                width="16"
                                height="16"
                                rx="2"
                            />

                            <path d="M8 3v4" />
                            <path d="M16 3v4" />
                            <path d="M4 10h16" />
                        </svg>

                    </div>


                    <div class="mt-4">

                        <h3 class="text-sm font-black text-content">
                            نوبت‌ها
                        </h3>

                        <p class="mt-1 text-[10px] leading-5 text-content-muted">
                            مشاهده و مدیریت رزروها
                        </p>

                    </div>

                </a>

            </div>

        </section>


        {{-- ============================================================
            SALON STATUS
        ============================================================= --}}

        <section class="mt-5 card overflow-hidden">

            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-content-soft">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 21V8l8-5 8 5v13" />
                            <path d="M8 21v-6h8v6" />
                        </svg>

                    </div>

                    <div>

                        <div class="text-sm font-black text-content">
                            {{ $salon->name }}
                        </div>

                        <div class="mt-1 text-[10px] text-content-muted">
                            وضعیت صفحه عمومی سالن
                        </div>

                    </div>

                </div>


                <div class="flex items-center gap-3">

                    @if($salon->is_active)

                        <span class="badge badge-success">
                            فعال و قابل مشاهده
                        </span>

                    @else

                        <span class="badge">
                            غیرفعال
                        </span>

                    @endif


                    <a
                        href="{{ route('public.salons.show', $salon) }}"
                        target="_blank"
                        rel="noopener"
                        class="btn btn-secondary btn-sm"
                    >
                        مشاهده سالن
                    </a>

                </div>

            </div>

        </section>

    </div>

@endsection
