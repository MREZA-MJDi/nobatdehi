@extends('layouts.salon')

@section('title', 'اعلان‌ها')

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
        | Counts
        |--------------------------------------------------------------------------
        */

        $totalNotifications =
            $notifications->total();

        $totalNotificationsText =
            strtr(
                (string) $totalNotifications,
                $persianDigits
            );


        $unreadNotificationsText =
            strtr(
                (string) $unreadNotifications,
                $persianDigits
            );


        /*
        |--------------------------------------------------------------------------
        | Notification Helpers
        |--------------------------------------------------------------------------
        */

        $getNotificationType = function ($notification): string {

            return (string) (
                $notification->data['type']
                    ?? ''
            );
        };


        $getNotificationTitle = function ($notification): string {

            return (string) (
                $notification->data['title']
                    ?? 'اعلان جدید'
            );
        };


        $getNotificationMessage = function ($notification): string {

            return (string) (
                $notification->data['message']
                    ?? 'یک اعلان جدید برای شما ثبت شده است.'
            );
        };


        $getNotificationIcon = function ($notification): string {

            $type =
                $notification->data['type']
                    ?? 'default';


            return match ($type) {

                'booking_created' =>
                    '◷',

                'booking_status_changed' =>
                    '✓',

                default =>
                    '♧',
            };
        };


        $getNotificationTone = function ($notification): string {

            $type =
                $notification->data['type']
                    ?? 'default';


            return match ($type) {

                'booking_created' =>
                    'booking',

                'booking_status_changed' =>
                    'status',

                default =>
                    'default',
            };
        };
    @endphp


    <div
        class="mx-auto w-full max-w-6xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8"
        dir="rtl"
    >


        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <section class="mb-7">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <a
                        href="{{ route('salon.dashboard') }}"
                        class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                    >
                        ← بازگشت به داشبورد
                    </a>


                    <div class="text-[9px] font-black tracking-[0.18em] text-accent-600">
                        NOTIFICATIONS
                    </div>


                    <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                        اعلان‌ها
                    </h1>


                    <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted sm:text-sm">
                        درخواست‌های نوبت، تغییر وضعیت و سایر پیام‌های مهم سالن را از اینجا مدیریت کنید.
                    </p>

                </div>


                @if($unreadNotifications > 0)

                    <form
                        action="{{ route('salon.notifications.read-all') }}"
                        method="POST"
                    >

                        @csrf

                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-border bg-white px-4 py-3 text-xs font-black text-content transition hover:-translate-y-0.5 hover:border-accent-200 hover:text-accent-600"
                        >
                            علامت‌گذاری همه به‌عنوان خوانده‌شده
                        </button>

                    </form>

                @endif

            </div>

        </section>


        {{-- ============================================================
            SUCCESS
        ============================================================= --}}

        @if(session('success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                class="mb-6 rounded-3xl border border-emerald-100 bg-emerald-50 p-4"
            >

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="text-xs font-black text-emerald-800">
                            انجام شد
                        </div>


                        <div class="mt-1 text-[10px] font-bold leading-6 text-emerald-700">
                            {{ session('success') }}
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
            OVERVIEW
        ============================================================= --}}

        <section class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">


            {{-- Total --}}

            <div class="rounded-3xl border border-border bg-white p-5 shadow-soft">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="text-[10px] font-bold text-content-muted">
                            کل اعلان‌ها
                        </div>


                        <div class="mt-3 text-3xl font-black text-content">
                            {{ $totalNotificationsText }}
                        </div>

                    </div>


                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-content-soft">
                        ♧
                    </div>

                </div>

            </div>


            {{-- Unread --}}

            <div class="rounded-3xl border border-accent-100 bg-accent-50 p-5 shadow-soft">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="text-[10px] font-bold text-accent-700">
                            خوانده‌نشده
                        </div>


                        <div class="mt-3 text-3xl font-black text-accent-800">
                            {{ $unreadNotificationsText }}
                        </div>

                    </div>


                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-accent-600 shadow-soft">
                        ●
                    </div>

                </div>

            </div>


            {{-- Status --}}

            <div class="rounded-3xl border border-border bg-primary-950 p-5 text-white shadow-soft">

                <div class="text-[9px] font-black tracking-[0.18em] text-accent-300">
                    SALON PANEL
                </div>


                <div class="mt-3 text-sm font-black">
                    وضعیت اعلان‌ها
                </div>


                <div class="mt-2 text-[10px] leading-6 text-white/55">
                    اعلان‌های جدید را بررسی کنید تا درخواست‌های مشتریان از دست نرود.
                </div>

            </div>

        </section>


        {{-- ============================================================
            NOTIFICATION LIST
        ============================================================= --}}

        @if($notifications->count())

            <section>

                <div class="mb-4 flex items-end justify-between gap-4">

                    <div>

                        <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                            INBOX
                        </div>


                        <h2 class="mt-1 text-base font-black text-content">
                            صندوق اعلان‌ها
                        </h2>

                    </div>


                    <div class="text-[10px] font-bold text-content-muted">
                        {{ $totalNotificationsText }}
                        اعلان
                    </div>

                </div>


                <div class="space-y-3">

                    @foreach($notifications as $notification)

                        @php

                            $type =
                                $getNotificationType(
                                    $notification
                                );


                            $title =
                                $getNotificationTitle(
                                    $notification
                                );


                            $message =
                                $getNotificationMessage(
                                    $notification
                                );


                            $icon =
                                $getNotificationIcon(
                                    $notification
                                );


                            $tone =
                                $getNotificationTone(
                                    $notification
                                );


                            $bookingId =
                                $notification->data['booking_id']
                                    ?? null;


                            $status =
                                $notification->data['status']
                                    ?? null;


                            $isUnread =
                                is_null(
                                    $notification->read_at
                                );


                            $createdAt =
                                $notification->created_at
                                    ? $notification
                                        ->created_at
                                        ->format('Y/m/d - H:i')
                                    : null;


                            $createdAtText =
                                $createdAt
                                    ? strtr(
                                        $createdAt,
                                        $persianDigits
                                    )
                                    : null;

                        @endphp


                        <article
                            class="group overflow-hidden rounded-3xl border bg-white shadow-soft transition hover:-translate-y-0.5
                            {{ $isUnread
                                ? 'border-accent-200'
                                : 'border-border'
                            }}"
                        >

                            <div class="flex gap-4 p-4 sm:p-5">


                                {{-- Icon --}}

                                <div class="shrink-0">

                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-black
                                        @if($tone === 'booking')
                                            bg-accent-50 text-accent-600
                                        @elseif($tone === 'status')
                                            bg-emerald-50 text-emerald-600
                                        @else
                                            bg-primary-50 text-content-soft
                                        @endif"
                                    >
                                        {{ $icon }}
                                    </div>

                                </div>


                                {{-- Content --}}

                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                        <div class="min-w-0">

                                            <div class="flex flex-wrap items-center gap-2">

                                                <h3 class="text-sm font-black text-content">
                                                    {{ $title }}
                                                </h3>


                                                @if($isUnread)

                                                    <span class="inline-flex items-center gap-1 rounded-full bg-accent-50 px-2 py-1 text-[8px] font-black text-accent-700">

                                                        <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>

                                                        جدید

                                                    </span>

                                                @else

                                                    <span class="rounded-full bg-primary-50 px-2 py-1 text-[8px] font-bold text-content-faint">
                                                        خوانده‌شده
                                                    </span>

                                                @endif

                                            </div>


                                            <p class="mt-2 text-xs leading-7 text-content-muted">
                                                {{ $message }}
                                            </p>

                                        </div>


                                        @if($createdAtText)

                                            <div class="shrink-0 text-[9px] font-bold text-content-faint">
                                                {{ $createdAtText }}
                                            </div>

                                        @endif

                                    </div>


                                    {{-- Booking Meta --}}

                                    @if($bookingId || $status)

                                        <div class="mt-4 flex flex-wrap items-center gap-2">

                                            @if($bookingId)

                                                <span class="rounded-xl bg-primary-50 px-3 py-2 text-[9px] font-black text-content-soft">

                                                    نوبت #
                                                    {{ strtr((string) $bookingId, $persianDigits) }}

                                                </span>

                                            @endif


                                            @if($status)

                                                @php

                                                    $statusLabel =
                                                        match ($status) {

                                                            'pending' =>
                                                                'در انتظار بررسی',

                                                            'confirmed' =>
                                                                'تأیید شده',

                                                            'cancelled' =>
                                                                'لغو شده',

                                                            'completed' =>
                                                                'انجام شده',

                                                            default =>
                                                                $status,
                                                        };

                                                @endphp


                                                <span class="rounded-xl bg-primary-50 px-3 py-2 text-[9px] font-black text-content-soft">

                                                    {{ $statusLabel }}

                                                </span>

                                            @endif

                                        </div>

                                    @endif

                                </div>

                            </div>


                            {{-- Actions --}}

                            <div class="flex flex-col gap-2 border-t border-border bg-primary-50 p-3 sm:flex-row sm:items-center sm:justify-between">

                                <div class="text-[9px] text-content-faint">

                                    @if($isUnread)

                                        این اعلان هنوز خوانده نشده است.

                                    @else

                                        این اعلان قبلاً خوانده شده است.

                                    @endif

                                </div>


                                <div class="flex flex-wrap gap-2">


                                    @if($isUnread)

                                        <form
                                            action="{{ route('salon.notifications.read', $notification->id) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-4 py-2.5 text-[9px] font-black text-content-soft shadow-sm transition hover:text-accent-600 sm:w-auto"
                                            >
                                                علامت به‌عنوان خوانده‌شده
                                            </button>

                                        </form>

                                    @endif


                                    @if($bookingId)

                                        <a
                                            href="{{ route('salon.bookings.show', $bookingId) }}"
                                            class="inline-flex w-full items-center justify-center rounded-xl bg-accent-600 px-4 py-2.5 text-[9px] font-black text-white transition hover:opacity-90 sm:w-auto"
                                        >
                                            مشاهده نوبت
                                            ←
                                        </a>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- ========================================================
                    PAGINATION
                ========================================================= --}}

                @if($notifications->hasPages())

                    <div class="mt-6 rounded-2xl border border-border bg-white p-4 shadow-soft">

                        {{ $notifications->links() }}

                    </div>

                @endif

            </section>

        @else

            {{-- ============================================================
                EMPTY STATE
            ============================================================= --}}

            <section class="rounded-3xl border border-border bg-white p-10 text-center shadow-soft sm:p-14">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary-50 text-2xl text-content-faint">
                    ♧
                </div>


                <div class="mt-5 text-sm font-black text-content">
                    هنوز اعلانی ندارید
                </div>


                <p class="mx-auto mt-2 max-w-md text-[10px] leading-7 text-content-muted">
                    وقتی مشتری درخواست نوبت ثبت کند یا وضعیت یک نوبت تغییر کند، اعلان آن در این بخش نمایش داده می‌شود.
                </p>


                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mt-6 inline-flex items-center justify-center rounded-2xl bg-primary-950 px-5 py-3 text-[10px] font-black text-white transition hover:opacity-90"
                >
                    بازگشت به داشبورد
                </a>

            </section>

        @endif

    </div>

@endsection

