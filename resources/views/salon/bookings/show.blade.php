@extends('layouts.salon')

@section('title', 'جزئیات نوبت')

@php
    use App\Enums\BookingStatus;
    use Carbon\Carbon;

    $status = $booking->status instanceof BookingStatus
        ? $booking->status->value
        : (string) $booking->status;

    $statusMeta = [
        BookingStatus::PENDING->value => [
            'label' => 'در انتظار تأیید',
            'description' => 'این نوبت هنوز توسط سالن تأیید نشده است.',
            'class' => 'bg-amber-50 text-amber-800 border-amber-200',
            'dot' => 'bg-amber-500',
        ],

        BookingStatus::CONFIRMED->value => [
            'label' => 'تأیید شده',
            'description' => 'نوبت توسط سالن تأیید شده و زمان آن رزرو است.',
            'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'dot' => 'bg-emerald-500',
        ],

        BookingStatus::COMPLETED->value => [
            'label' => 'تکمیل شده',
            'description' => 'این نوبت با موفقیت انجام شده است.',
            'class' => 'bg-slate-100 text-slate-800 border-slate-200',
            'dot' => 'bg-slate-500',
        ],

        BookingStatus::CANCELLED->value => [
            'label' => 'لغو شده',
            'description' => 'این نوبت لغو شده و دیگر قابل تغییر نیست.',
            'class' => 'bg-red-50 text-red-800 border-red-200',
            'dot' => 'bg-red-500',
        ],
    ];

    $meta = $statusMeta[$status] ?? [
        'label' => $status,
        'description' => '',
        'class' => 'bg-slate-100 text-slate-800 border-slate-200',
        'dot' => 'bg-slate-500',
    ];

    $bookingDate = $booking->booking_date;

    $startTime = substr(
        (string) $booking->start_time,
        0,
        5
    );

    $endTime = substr(
        (string) $booking->end_time,
        0,
        5
    );

    $customerPhone = $booking->customer?->phone
        ?? $booking->customer_phone;

    /*
    |--------------------------------------------------------------------------
    | Completion availability
    |--------------------------------------------------------------------------
    */
    $bookingEndDateTime = null;

    try {
        $bookingEndDateTime = Carbon::parse(
            $bookingDate->format('Y-m-d') . ' ' . $endTime,
            config('app.timezone')
        );
    } catch (\Throwable $e) {
        $bookingEndDateTime = null;
    }

    $canCompleteNow =
        $status === BookingStatus::CONFIRMED->value
        && (
            !$bookingEndDateTime
            || now(config('app.timezone'))->gte($bookingEndDateTime)
        );
@endphp

@section('content')

    <div
        class="min-h-full bg-slate-50"
        dir="rtl"
    >

        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">


            {{-- ============================================================
                 HEADER
            ============================================================= --}}
            <div class="mb-7">

                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                >
                    <span class="text-lg">→</span>
                    بازگشت به نوبت‌ها
                </a>


                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                    <div>

                        <div class="flex items-center gap-4">

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                                #{{ $booking->id }}
                            </div>

                            <div>

                                <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                                    BOOKING DETAILS
                                </div>

                                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                    جزئیات نوبت
                                </h1>

                            </div>

                        </div>

                        <p class="mt-3 text-sm leading-7 text-slate-500">
                            اطلاعات کامل مشتری، خدمت، زمان‌بندی و وضعیت این نوبت.
                        </p>

                    </div>


                    <div class="inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-xs font-black {{ $meta['class'] }}">

                        <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>

                        {{ $meta['label'] }}

                    </div>

                </div>

            </div>


            {{-- ============================================================
                 FLASH
            ============================================================= --}}
            @if (session('success'))

                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        ✓
                    </div>

                    <div class="text-sm font-bold leading-6">
                        {{ session('success') }}
                    </div>

                </div>

            @endif


            @if (session('error'))

                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-800">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                        !
                    </div>

                    <div class="text-sm font-bold leading-6">
                        {{ session('error') }}
                    </div>

                </div>

            @endif


            {{-- ============================================================
                 STATUS BANNER
            ============================================================= --}}
            <div class="mb-6 rounded-3xl border px-5 py-5 {{ $meta['class'] }}">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <span class="h-2.5 w-2.5 rounded-full {{ $meta['dot'] }}"></span>

                            <span class="text-sm font-black">
                            {{ $meta['label'] }}
                        </span>

                        </div>

                        <p class="mt-2 text-xs leading-6 opacity-75">
                            {{ $meta['description'] }}
                        </p>

                    </div>


                    <div class="text-left">

                        <div class="text-[10px] font-black opacity-50">
                            شماره نوبت
                        </div>

                        <div class="mt-1 text-lg font-black">
                            #{{ $booking->id }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                 MAIN GRID
            ============================================================= --}}
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">


                {{-- ========================================================
                     MAIN INFORMATION
                ========================================================= --}}
                <div class="space-y-6">


                    {{-- CUSTOMER --}}
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

                            <div class="flex items-center gap-4">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                    ۱
                                </div>

                                <div>

                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                        CUSTOMER
                                    </div>

                                    <h2 class="mt-1 text-lg font-black text-slate-950">
                                        اطلاعات مشتری
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="p-5 sm:p-6">

                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-slate-950 text-xl font-black text-white">
                                    {{ mb_substr($booking->customer?->name ?? $booking->customer_name ?? '؟', 0, 1) }}
                                </div>


                                <div class="min-w-0 flex-1">

                                    <div class="text-lg font-black text-slate-950">
                                        {{ $booking->customer?->name ?? $booking->customer_name ?? 'مشتری' }}
                                    </div>

                                    @if ($booking->customer?->phone ?? $booking->customer_phone)

                                        <div class="mt-1 text-sm text-slate-400">
                                            {{ $booking->customer?->phone ?? $booking->customer_phone }}
                                        </div>

                                    @endif

                                </div>


                                @if ($customerPhone)

                                    <a
                                        href="tel:{{ $customerPhone }}"
                                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                    >
                                        تماس با مشتری
                                    </a>

                                @endif

                            </div>

                        </div>

                    </section>


                    {{-- SERVICE --}}
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

                            <div class="flex items-center gap-4">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                    ۲
                                </div>

                                <div>

                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                        SERVICE
                                    </div>

                                    <h2 class="mt-1 text-lg font-black text-slate-950">
                                        اطلاعات خدمت
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">

                            {{-- Service --}}
                            <div class="rounded-2xl bg-slate-50 p-4">

                                <div class="text-[10px] font-black text-slate-400">
                                    خدمت
                                </div>

                                <div class="mt-2 text-sm font-black text-slate-950">
                                    {{ $booking->service?->name ?? 'خدمت حذف شده' }}
                                </div>

                            </div>


                            {{-- Barber --}}
                            <div class="rounded-2xl bg-slate-50 p-4">

                                <div class="text-[10px] font-black text-slate-400">
                                    آرایشگر
                                </div>

                                <div class="mt-2 text-sm font-black text-slate-950">
                                    {{ $booking->barber?->name ?? 'آرایشگر حذف شده' }}
                                </div>

                            </div>


                            {{-- Price --}}
                            <div class="rounded-2xl bg-slate-50 p-4">

                                <div class="text-[10px] font-black text-slate-400">
                                    مبلغ
                                </div>

                                <div class="mt-2 text-sm font-black text-slate-950">
                                    {{ number_format((int) $booking->price) }}
                                    تومان
                                </div>

                            </div>


                            {{-- Date --}}
                            <div class="rounded-2xl bg-slate-50 p-4">

                                <div class="text-[10px] font-black text-slate-400">
                                    تاریخ
                                </div>

                                <div class="mt-2 text-sm font-black text-slate-950">
                                    {{ jalali_date($bookingDate) }}
                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- APPOINTMENT --}}
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

                            <div class="flex items-center gap-4">

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                    ۳
                                </div>

                                <div>

                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                        APPOINTMENT
                                    </div>

                                    <h2 class="mt-1 text-lg font-black text-slate-950">
                                        زمان نوبت
                                    </h2>

                                </div>

                            </div>

                        </div>


                        <div class="grid gap-3 p-5 sm:grid-cols-3 sm:p-6">

                            <div class="rounded-2xl bg-slate-950 p-5 text-white">

                                <div class="text-[10px] font-black text-white/40">
                                    تاریخ
                                </div>

                                <div class="mt-2 text-base font-black">
                                    {{ jalali_date($bookingDate) }}
                                </div>

                            </div>


                            <div class="rounded-2xl bg-slate-50 p-5">

                                <div class="text-[10px] font-black text-slate-400">
                                    شروع
                                </div>

                                <div class="mt-2 text-xl font-black text-slate-950">
                                    {{ $startTime }}
                                </div>

                            </div>


                            <div class="rounded-2xl bg-slate-50 p-5">

                                <div class="text-[10px] font-black text-slate-400">
                                    پایان
                                </div>

                                <div class="mt-2 text-xl font-black text-slate-950">
                                    {{ $endTime }}
                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- NOTES --}}
                    @if ($booking->notes)

                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

                                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                    NOTES
                                </div>

                                <h2 class="mt-1 text-lg font-black text-slate-950">
                                    توضیحات نوبت
                                </h2>

                            </div>

                            <div class="p-5 sm:p-6">

                                <div class="rounded-2xl bg-slate-50 p-4 text-sm leading-8 text-slate-700">
                                    {{ $booking->notes }}
                                </div>

                            </div>

                        </section>

                    @endif

                </div>


                {{-- ========================================================
                     SIDEBAR ACTIONS
                ========================================================= --}}
                <aside class="lg:sticky lg:top-6 lg:h-fit">

                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">


                        {{-- Summary header --}}
                        <div class="bg-slate-950 px-5 py-6 text-white">

                            <div class="text-[10px] font-black uppercase tracking-[0.22em] text-white/40">
                                BOOKING ACTIONS
                            </div>

                            <div class="mt-1 text-xl font-black">
                                مدیریت نوبت
                            </div>

                        </div>


                        <div class="space-y-3 p-5">


                            {{-- =================================================
                                 PENDING
                            ================================================== --}}
                            @if ($status === BookingStatus::PENDING->value)

                                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">

                                    <div class="text-xs font-black text-amber-800">
                                        این نوبت در انتظار تأیید است.
                                    </div>

                                    <div class="mt-1 text-[11px] leading-6 text-amber-700/70">
                                        می‌توانید آن را تأیید یا لغو کنید.
                                    </div>

                                </div>


                                <form
                                    action="{{ route('salon.bookings.status', $booking) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="{{ BookingStatus::CONFIRMED->value }}"
                                    >

                                    <button
                                        type="submit"
                                        class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700"
                                    >
                                        <span>✓</span>
                                        تأیید نوبت
                                    </button>

                                </form>


                                <form
                                    action="{{ route('salon.bookings.status', $booking) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از لغو این نوبت مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="{{ BookingStatus::CANCELLED->value }}"
                                    >

                                    <button
                                        type="submit"
                                        class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-100"
                                    >
                                        <span>×</span>
                                        لغو نوبت
                                    </button>

                                </form>


                                {{-- =================================================
                                     CONFIRMED
                                ================================================== --}}
                            @elseif ($status === BookingStatus::CONFIRMED->value)

                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">

                                    <div class="text-xs font-black text-emerald-800">
                                        نوبت تأیید شده است.
                                    </div>

                                    <div class="mt-1 text-[11px] leading-6 text-emerald-700/70">
                                        پس از پایان زمان خدمت می‌توانید آن را تکمیل کنید.
                                    </div>

                                </div>


                                @if ($canCompleteNow)

                                    <form
                                        action="{{ route('salon.bookings.status', $booking) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="{{ BookingStatus::COMPLETED->value }}"
                                        >

                                        <button
                                            type="submit"
                                            class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800"
                                        >
                                            <span>✓</span>
                                            تکمیل نوبت
                                        </button>

                                    </form>

                                @else

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">

                                        <div class="text-xs font-black text-slate-600">
                                            هنوز زمان تکمیل نوبت نرسیده
                                        </div>

                                        <div class="mt-1 text-[11px] leading-6 text-slate-400">
                                            بعد از ساعت پایان خدمت، گزینه تکمیل فعال می‌شود.
                                        </div>

                                    </div>

                                @endif


                                <form
                                    action="{{ route('salon.bookings.status', $booking) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از لغو این نوبت مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="{{ BookingStatus::CANCELLED->value }}"
                                    >

                                    <button
                                        type="submit"
                                        class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-100"
                                    >
                                        <span>×</span>
                                        لغو نوبت
                                    </button>

                                </form>


                                {{-- =================================================
                                     FINAL STATUS
                                ================================================== --}}
                            @else

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">

                                    @if ($status === BookingStatus::COMPLETED->value)

                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg text-white">
                                            ✓
                                        </div>

                                        <div class="mt-3 text-sm font-black text-slate-900">
                                            این نوبت تکمیل شده است
                                        </div>

                                        <div class="mt-1 text-xs leading-6 text-slate-400">
                                            وضعیت نوبت نهایی شده و دیگر قابل تغییر نیست.
                                        </div>

                                    @elseif ($status === BookingStatus::CANCELLED->value)

                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-lg text-red-700">
                                            ×
                                        </div>

                                        <div class="mt-3 text-sm font-black text-slate-900">
                                            این نوبت لغو شده است
                                        </div>

                                        <div class="mt-1 text-xs leading-6 text-slate-400">
                                            وضعیت نوبت نهایی شده و دیگر قابل تغییر نیست.
                                        </div>

                                    @endif

                                </div>

                            @endif


                            {{-- Back --}}
                            <a
                                href="{{ route('salon.bookings.index') }}"
                                class="flex h-12 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                بازگشت به لیست
                            </a>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </div>

@endsection
