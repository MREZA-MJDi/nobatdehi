@extends('layouts.salon')

@section('title', 'جزئیات نوبت')

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

        $bookingDate = strtr(
            $booking->booking_date->format('Y/m/d'),
            $persianDigits
        );

        $startTime = strtr(
            substr($booking->start_time, 0, 5),
            $persianDigits
        );

        $endTime = strtr(
            substr($booking->end_time, 0, 5),
            $persianDigits
        );

        $price = strtr(
            number_format($booking->price),
            $persianDigits
        );

        $duration = $booking->service?->duration_minutes;

        $durationText = $duration
            ? strtr((string) $duration, $persianDigits) . ' دقیقه'
            : '—';

        $status = $booking->status;

        $statusMeta = match ($status) {
            \App\Enums\BookingStatus::PENDING => [
                'label' => 'در انتظار',
                'description' => 'این نوبت هنوز توسط سالن تأیید نشده است.',
                'class' => 'bg-warning-50 text-warning-700',
                'dot' => 'bg-warning-500',
            ],

            \App\Enums\BookingStatus::CONFIRMED => [
                'label' => 'تأیید شده',
                'description' => 'نوبت توسط سالن تأیید شده است.',
                'class' => 'bg-success-50 text-success-700',
                'dot' => 'bg-success-500',
            ],

            \App\Enums\BookingStatus::COMPLETED => [
                'label' => 'تکمیل شده',
                'description' => 'این خدمت انجام شده است.',
                'class' => 'bg-accent-50 text-accent-700',
                'dot' => 'bg-accent-600',
            ],

            \App\Enums\BookingStatus::CANCELLED => [
                'label' => 'لغو شده',
                'description' => 'این نوبت دیگر قابل اجرا نیست.',
                'class' => 'bg-danger-50 text-danger-700',
                'dot' => 'bg-danger-500',
            ],
        };

        $allowedStatuses = match ($status) {
            \App\Enums\BookingStatus::PENDING => [
                \App\Enums\BookingStatus::CONFIRMED,
                \App\Enums\BookingStatus::CANCELLED,
            ],

            \App\Enums\BookingStatus::CONFIRMED => [
                \App\Enums\BookingStatus::COMPLETED,
                \App\Enums\BookingStatus::CANCELLED,
            ],

            \App\Enums\BookingStatus::COMPLETED,
            \App\Enums\BookingStatus::CANCELLED => [],
        };

        $statusActionMeta = [
            \App\Enums\BookingStatus::CONFIRMED->value => [
                'title' => 'تأیید نوبت',
                'description' => 'نوبت را برای مشتری قطعی کنید.',
                'class' => 'bg-success-50 text-success-700 border-success-100',
            ],

            \App\Enums\BookingStatus::COMPLETED->value => [
                'title' => 'ثبت به‌عنوان تکمیل‌شده',
                'description' => 'پس از انجام خدمت، نوبت را تکمیل کنید.',
                'class' => 'bg-accent-50 text-accent-700 border-accent-100',
            ],

            \App\Enums\BookingStatus::CANCELLED->value => [
                'title' => 'لغو نوبت',
                'description' => 'این نوبت را لغو کنید.',
                'class' => 'bg-danger-50 text-danger-700 border-danger-100',
            ],
        ];
    @endphp


    <div class="mx-auto w-full max-w-6xl px-4 py-6 pb-28 sm:px-6 lg:px-8">


        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('salon.bookings.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به نوبت‌ها
            </a>


            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div>

                    <div class="mb-2 text-[10px] font-black tracking-[0.18em] text-accent-600">
                        BOOKING DETAILS
                    </div>

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-black text-content sm:text-3xl">
                            جزئیات نوبت
                        </h1>

                        <span class="rounded-xl bg-primary-100 px-3 py-1.5 text-[10px] font-black text-content-soft">
                            #{{ strtr((string) $booking->id, $persianDigits) }}
                        </span>

                    </div>

                    <p class="mt-2 max-w-2xl text-xs leading-6 text-content-muted">
                        اطلاعات کامل این نوبت و اقدام‌های قابل انجام را از همین صفحه مدیریت کنید.
                    </p>

                </div>


                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="btn btn-secondary w-full sm:w-auto"
                >
                    همه نوبت‌ها
                </a>

            </div>

        </div>


        {{-- ============================================================
            STATUS HERO
        ============================================================= --}}

        <section class="mb-5 overflow-hidden rounded-3xl border border-border bg-surface shadow-card">

            <div class="border-b border-border bg-primary-50/70 p-5 sm:p-6">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">


                    <div class="flex items-center gap-4">

                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $statusMeta['class'] }}"
                        >

                            <span class="relative flex h-3 w-3">

                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-50 {{ $statusMeta['dot'] }}"
                                ></span>

                                <span
                                    class="relative inline-flex h-3 w-3 rounded-full {{ $statusMeta['dot'] }}"
                                ></span>

                            </span>

                        </div>


                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                وضعیت نوبت
                            </div>

                            <div class="mt-1 text-lg font-black text-content">
                                {{ $statusMeta['label'] }}
                            </div>

                            <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                {{ $statusMeta['description'] }}
                            </div>

                        </div>

                    </div>


                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:min-w-[360px]">

                        <div class="rounded-2xl bg-white p-3 shadow-soft">

                            <div class="text-[9px] text-content-muted">
                                تاریخ
                            </div>

                            <div class="mt-1 text-xs font-black text-content">
                                {{ $bookingDate }}
                            </div>

                        </div>


                        <div class="rounded-2xl bg-white p-3 shadow-soft">

                            <div class="text-[9px] text-content-muted">
                                ساعت
                            </div>

                            <div
                                class="mt-1 text-xs font-black text-content"
                                dir="ltr"
                            >
                                {{ $startTime }}
                                –
                                {{ $endTime }}
                            </div>

                        </div>


                        <div class="col-span-2 rounded-2xl bg-white p-3 shadow-soft sm:col-span-1">

                            <div class="text-[9px] text-content-muted">
                                مبلغ
                            </div>

                            <div class="mt-1 text-xs font-black text-content">
                                {{ $price }}
                                تومان
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Quick facts --}}

            <div class="grid gap-px bg-border sm:grid-cols-3">

                <div class="bg-surface p-4">

                    <div class="text-[9px] font-bold text-content-muted">
                        مشتری
                    </div>

                    <div class="mt-1 truncate text-xs font-black text-content">
                        {{ $booking->customer?->name ?? 'مشتری' }}
                    </div>

                </div>


                <div class="bg-surface p-4">

                    <div class="text-[9px] font-bold text-content-muted">
                        آرایشگر
                    </div>

                    <div class="mt-1 truncate text-xs font-black text-content">
                        {{ $booking->barber?->name ?? '—' }}
                    </div>

                </div>


                <div class="bg-surface p-4">

                    <div class="text-[9px] font-bold text-content-muted">
                        خدمت
                    </div>

                    <div class="mt-1 truncate text-xs font-black text-content">
                        {{ $booking->service?->name ?? '—' }}
                    </div>

                </div>

            </div>

        </section>


        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">


            {{-- ========================================================
                MAIN INFO
            ========================================================= --}}

            <div class="space-y-5">


                {{-- CUSTOMER --}}

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">

                    <div class="mb-5 flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                CUSTOMER
                            </div>

                            <h2 class="mt-1 text-base font-black text-content">
                                اطلاعات مشتری
                            </h2>

                        </div>

                    </div>


                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-lg font-black text-accent-700">

                            {{ mb_substr(
                                $booking->customer?->name ?? 'م',
                                0,
                                1
                            ) }}

                        </div>


                        <div class="min-w-0">

                            <div class="text-sm font-black text-content">

                                {{ $booking->customer?->name ?? 'مشتری' }}

                            </div>


                            @if($booking->customer?->phone)

                                <div class="mt-1 flex items-center gap-2">

                                    <span class="text-[10px] text-content-muted">
                                        موبایل
                                    </span>

                                    <span
                                        class="text-xs font-bold text-content-soft"
                                        dir="ltr"
                                    >
                                        {{ $booking->customer->phone }}
                                    </span>

                                </div>

                            @endif

                        </div>

                    </div>

                </section>


                {{-- SERVICE --}}

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            SERVICE
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            اطلاعات خدمت
                        </h2>

                    </div>


                    <div class="rounded-2xl bg-primary-50 p-4">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                            <div class="min-w-0">

                                <div class="text-sm font-black text-content">
                                    {{ $booking->service?->name ?? 'خدمت' }}
                                </div>

                                <div class="mt-1 text-[10px] text-content-muted">
                                    ارائه توسط
                                    {{ $booking->barber?->name ?? 'آرایشگر' }}
                                </div>

                            </div>


                            <div class="flex shrink-0 gap-2">

                                <div class="rounded-xl bg-white px-3 py-2 text-center shadow-soft">

                                    <div class="text-[9px] text-content-muted">
                                        مدت
                                    </div>

                                    <div class="mt-0.5 text-xs font-black text-content">
                                        {{ $durationText }}
                                    </div>

                                </div>


                                <div class="rounded-xl bg-white px-3 py-2 text-center shadow-soft">

                                    <div class="text-[9px] text-content-muted">
                                        مبلغ
                                    </div>

                                    <div class="mt-0.5 text-xs font-black text-content">

                                        {{ $price }}

                                        <span class="text-[9px] font-bold text-content-muted">
                                            تومان
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- NOTES --}}

                @if($booking->notes)

                    <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">

                        <div class="mb-4">

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                NOTES
                            </div>

                            <h2 class="mt-1 text-base font-black text-content">
                                توضیحات نوبت
                            </h2>

                        </div>


                        <div class="rounded-2xl border border-border bg-primary-50 p-4 text-xs leading-7 text-content">

                            {{ $booking->notes }}

                        </div>

                    </section>

                @endif


                {{-- TIME --}}

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">

                    <div class="mb-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            APPOINTMENT
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            زمان نوبت
                        </h2>

                    </div>


                    <div class="relative rounded-2xl bg-primary-950 p-5 text-white overflow-hidden">

                        <div class="absolute -left-10 -top-10 h-28 w-28 rounded-full bg-accent-600/20 blur-2xl"></div>

                        <div class="relative">

                            <div class="text-[10px] text-white/55">
                                زمان رزرو
                            </div>

                            <div class="mt-2 text-2xl font-black" dir="ltr">
                                {{ $startTime }}
                                <span class="mx-1 text-white/40">
                                    –
                                </span>
                                {{ $endTime }}
                            </div>

                            <div class="mt-2 text-xs font-bold text-white/65">
                                {{ $bookingDate }}
                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- ========================================================
                ACTION SIDEBAR
            ========================================================= --}}

            <aside class="lg:sticky lg:top-6 lg:h-fit">

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-card">

                    <div class="mb-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            ACTIONS
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            مدیریت نوبت
                        </h2>

                        <p class="mt-1 text-[10px] leading-5 text-content-muted">
                            فقط اقدام‌هایی که برای وضعیت فعلی مجاز هستند نمایش داده می‌شوند.
                        </p>

                    </div>


                    @if(count($allowedStatuses))

                        <div class="space-y-3">

                            @foreach($allowedStatuses as $nextStatus)

                                @php
                                    $action = $statusActionMeta[
                                        $nextStatus->value
                                    ];
                                @endphp


                                <form
                                    action="{{ route(
                                        'salon.bookings.status',
                                        $booking
                                    ) }}"
                                    method="POST"
                                >

                                    @csrf

                                    @method('PATCH')


                                    <button
                                        type="submit"
                                        name="status"
                                        value="{{ $nextStatus->value }}"
                                        class="group w-full rounded-2xl border p-4 text-right transition hover:-translate-y-0.5 hover:shadow-soft {{ $action['class'] }}"
                                    >

                                        <div class="flex items-center justify-between gap-4">

                                            <div class="min-w-0">

                                                <div class="text-xs font-black">
                                                    {{ $action['title'] }}
                                                </div>

                                                <div class="mt-1 text-[10px] leading-5 opacity-75">
                                                    {{ $action['description'] }}
                                                </div>

                                            </div>


                                            <span class="shrink-0 text-lg font-black transition group-hover:-translate-x-1">
                                                ←
                                            </span>

                                        </div>

                                    </button>

                                </form>

                            @endforeach

                        </div>

                    @else

                        <div class="rounded-2xl bg-primary-50 p-5 text-center">

                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-content-soft shadow-soft">
                                ✓
                            </div>

                            <div class="mt-3 text-xs font-black text-content">
                                این نوبت نهایی شده است
                            </div>

                            <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                برای وضعیت فعلی اقدام دیگری تعریف نشده است.
                            </div>

                        </div>

                    @endif


                    <div class="my-5 h-px bg-border"></div>


                    <a
                        href="{{ route('salon.bookings.index') }}"
                        class="flex w-full items-center justify-center rounded-2xl border border-border bg-white px-4 py-3 text-xs font-black text-content transition hover:border-accent-200 hover:bg-primary-50"
                    >
                        بازگشت به لیست نوبت‌ها
                    </a>

                </section>


                {{-- BOOKING META --}}

                <section class="mt-4 rounded-3xl border border-border bg-primary-50 p-5">

                    <div class="text-[10px] font-black text-content-faint">
                        اطلاعات ثبت
                    </div>

                    <div class="mt-3 space-y-3">

                        <div class="flex items-center justify-between gap-3">

                            <span class="text-[10px] text-content-muted">
                                شناسه نوبت
                            </span>

                            <span class="text-[10px] font-black text-content">
                                #{{ $booking->id }}
                            </span>

                        </div>


                        <div class="flex items-center justify-between gap-3">

                            <span class="text-[10px] text-content-muted">
                                وضعیت
                            </span>

                            <span class="text-[10px] font-black text-content">
                                {{ $statusMeta['label'] }}
                            </span>

                        </div>

                    </div>

                </section>

            </aside>

        </div>

    </div>

@endsection
