@extends('layouts.salon')

@section('title', 'نوبت‌های سالن')

@php
    use App\Enums\BookingStatus;

    $statusMeta = [
        BookingStatus::PENDING->value => [
            'label' => 'در انتظار',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
        ],

        BookingStatus::CONFIRMED->value => [
            'label' => 'تأیید شده',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
        ],

        BookingStatus::COMPLETED->value => [
            'label' => 'تکمیل شده',
            'class' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-500',
        ],

        BookingStatus::CANCELLED->value => [
            'label' => 'لغو شده',
            'class' => 'bg-red-50 text-red-700 border-red-200',
            'dot' => 'bg-red-500',
        ],
    ];

    $selectedStatus = request('status', '');
    $selectedDate = request('date', '');
    $search = request('search', '');

    $stats = $stats ?? [
        'today' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];
@endphp

@section('content')

    <div
        class="min-h-full bg-slate-50"
        dir="rtl"
    >

        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

            {{-- ============================================================
                 HEADER
            ============================================================= --}}
            <div class="mb-7">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                    <div>

                        <div class="flex items-center gap-4">

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                                ◷
                            </div>

                            <div>

                                <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                                    SALON BOOKINGS
                                </div>

                                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                    نوبت‌های سالن
                                </h1>

                            </div>

                        </div>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            تمام نوبت‌های سالن را مدیریت کنید، وضعیت رزروها را بررسی کنید و برای مشتریان نوبت دستی ثبت کنید.
                        </p>

                    </div>


                    <a
                        href="{{ route('salon.bookings.create') }}"
                        class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-slate-800 sm:w-auto"
                    >
                        <span class="text-lg">＋</span>
                        ثبت نوبت دستی
                    </a>

                </div>

            </div>


            {{-- ============================================================
                 FLASH MESSAGES
            ============================================================= --}}
            @if (session('success'))

                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800 shadow-sm">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        ✓
                    </div>

                    <div class="text-sm font-bold leading-6">
                        {{ session('success') }}
                    </div>

                </div>

            @endif


            @if (session('error'))

                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-800 shadow-sm">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                        !
                    </div>

                    <div class="text-sm font-bold leading-6">
                        {{ session('error') }}
                    </div>

                </div>

            @endif


            {{-- ============================================================
                 STATS
            ============================================================= --}}
            <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">

                {{-- Today --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                TODAY
                            </div>

                            <div class="mt-2 text-3xl font-black text-slate-950">
                                {{ number_format($stats['today'] ?? 0) }}
                            </div>

                            <div class="mt-1 text-xs font-bold text-slate-400">
                                نوبت امروز
                            </div>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-lg">
                            ◷
                        </div>

                    </div>

                </div>


                {{-- Pending --}}
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-600">
                                PENDING
                            </div>

                            <div class="mt-2 text-3xl font-black text-amber-900">
                                {{ number_format($stats['pending'] ?? 0) }}
                            </div>

                            <div class="mt-1 text-xs font-bold text-amber-700/70">
                                در انتظار تأیید
                            </div>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/70 text-lg">
                            !
                        </div>

                    </div>

                </div>


                {{-- Confirmed --}}
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">
                                CONFIRMED
                            </div>

                            <div class="mt-2 text-3xl font-black text-emerald-900">
                                {{ number_format($stats['confirmed'] ?? 0) }}
                            </div>

                            <div class="mt-1 text-xs font-bold text-emerald-700/70">
                                تأیید شده
                            </div>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/70 text-lg">
                            ✓
                        </div>

                    </div>

                </div>


                {{-- Completed --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                COMPLETED
                            </div>

                            <div class="mt-2 text-3xl font-black text-slate-950">
                                {{ number_format($stats['completed'] ?? 0) }}
                            </div>

                            <div class="mt-1 text-xs font-bold text-slate-400">
                                تکمیل شده
                            </div>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-lg">
                            ✓
                        </div>

                    </div>

                </div>


                {{-- Cancelled --}}
                <div class="rounded-3xl border border-red-200 bg-red-50 p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-red-500">
                                CANCELLED
                            </div>

                            <div class="mt-2 text-3xl font-black text-red-900">
                                {{ number_format($stats['cancelled'] ?? 0) }}
                            </div>

                            <div class="mt-1 text-xs font-bold text-red-700/70">
                                لغو شده
                            </div>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/70 text-lg">
                            ×
                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                 FILTER BAR
            ============================================================= --}}
            <section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

                    <div class="flex items-center justify-between gap-3">

                        <div>

                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                FILTERS
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                جستجو و فیلتر نوبت‌ها
                            </h2>

                        </div>

                        @if ($search || $selectedStatus || $selectedDate)

                            <a
                                href="{{ route('salon.bookings.index') }}"
                                class="text-xs font-black text-slate-400 transition hover:text-slate-900"
                            >
                                پاک کردن فیلترها
                            </a>

                        @endif

                    </div>

                </div>


                <form
                    action="{{ route('salon.bookings.index') }}"
                    method="GET"
                    class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_1fr_1fr_auto] sm:p-6"
                >

                    {{-- Search --}}
                    <div>

                        <label
                            for="search"
                            class="mb-2 block text-xs font-black text-slate-700"
                        >
                            جستجو
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="نام مشتری، موبایل یا..."
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        >

                    </div>


                    {{-- Status --}}
                    <div>

                        <label
                            for="status"
                            class="mb-2 block text-xs font-black text-slate-700"
                        >
                            وضعیت
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        >

                            <option value="">
                                همه وضعیت‌ها
                            </option>

                            @foreach ($statusMeta as $value => $meta)

                                <option
                                    value="{{ $value }}"
                                    @selected($selectedStatus === $value)
                                >
                                    {{ $meta['label'] }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Date --}}
                    <div>

                        <label
                            for="date"
                            class="mb-2 block text-xs font-black text-slate-700"
                        >
                            تاریخ
                        </label>

                        <input
                            id="date"
                            name="date"
                            type="date"
                            value="{{ $selectedDate }}"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        >

                    </div>


                    {{-- Submit --}}
                    <div class="flex items-end">

                        <button
                            type="submit"
                            class="h-12 w-full rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-slate-800"
                        >
                            اعمال فیلتر
                        </button>

                    </div>

                </form>

            </section>


            {{-- ============================================================
                 BOOKINGS
            ============================================================= --}}
            <section>

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div>

                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            BOOKING LIST
                        </div>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            لیست نوبت‌ها
                        </h2>

                    </div>

                    @if (isset($bookings))
                        <div class="text-xs font-bold text-slate-400">
                            {{ number_format($bookings->total()) }}
                            نوبت
                        </div>
                    @endif

                </div>


                @if ($bookings->count())

                    <div class="space-y-3">

                        @foreach ($bookings as $booking)

                            @php
                                $status = $booking->status instanceof BookingStatus
                                    ? $booking->status->value
                                    : (string) $booking->status;

                                $meta = $statusMeta[$status] ?? [
                                    'label' => $status,
                                    'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    'dot' => 'bg-slate-500',
                                ];

                                $bookingDate = $booking->booking_date;
                                $startTime = substr((string) $booking->start_time, 0, 5);
                                $endTime = substr((string) $booking->end_time, 0, 5);
                            @endphp

                            <a
                                href="{{ route('salon.bookings.show', $booking) }}"
                                class="group block rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg sm:p-5"
                            >

                                <div class="grid gap-5 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:items-center">

                                    {{-- Customer --}}
                                    <div class="flex min-w-0 items-center gap-3">

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                            {{ mb_substr($booking->customer?->name ?? '؟', 0, 1) }}
                                        </div>

                                        <div class="min-w-0">

                                            <div class="flex flex-wrap items-center gap-2">

                                                <h3 class="truncate text-sm font-black text-slate-950">
                                                    {{ $booking->customer?->name ?? 'مشتری حذف شده' }}
                                                </h3>

                                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-black {{ $meta['class'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                                {{ $meta['label'] }}
                                            </span>

                                            </div>

                                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-400">

                                                @if ($booking->customer?->phone)

                                                    <span>
                                                    {{ $booking->customer->phone }}
                                                </span>

                                                @endif

                                                @if ($booking->service)

                                                    <span>
                                                    {{ $booking->service->name }}
                                                </span>

                                                @endif

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Date --}}
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3">

                                        <div class="text-[10px] font-black text-slate-400">
                                            تاریخ
                                        </div>

                                        <div class="mt-1 text-sm font-black text-slate-900">
                                            {{ jalali_date($bookingDate) }}
                                        </div>

                                    </div>


                                    {{-- Time --}}
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3">

                                        <div class="text-[10px] font-black text-slate-400">
                                            زمان
                                        </div>

                                        <div class="mt-1 text-sm font-black text-slate-900">
                                            {{ $startTime }}
                                            <span class="font-bold text-slate-300">
                                            تا
                                        </span>
                                            {{ $endTime }}
                                        </div>

                                        @if ($booking->barber)

                                            <div class="mt-1 text-[11px] text-slate-400">
                                                {{ $booking->barber->name }}
                                            </div>

                                        @endif

                                    </div>


                                    {{-- Action --}}
                                    <div class="flex items-center justify-between gap-3 lg:justify-end">

                                        <div class="text-left">

                                            <div class="text-sm font-black text-slate-900">
                                                {{ number_format((int) $booking->price) }}
                                            </div>

                                            <div class="text-[10px] text-slate-400">
                                                تومان
                                            </div>

                                        </div>

                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition group-hover:bg-slate-950 group-hover:text-white">
                                            ←
                                        </div>

                                    </div>

                                </div>

                            </a>

                        @endforeach

                    </div>


                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $bookings->links() }}
                    </div>

                @else

                    <div class="rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                            ◷
                        </div>

                        <h3 class="mt-5 text-lg font-black text-slate-900">
                            نوبتی پیدا نشد
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-400">
                            با فیلترهای فعلی نوبتی برای نمایش وجود ندارد.
                            می‌توانید فیلترها را پاک کنید یا یک نوبت دستی جدید ایجاد کنید.
                        </p>

                        <a
                            href="{{ route('salon.bookings.create') }}"
                            class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-slate-800"
                        >
                            ثبت نوبت دستی
                        </a>

                    </div>

                @endif

            </section>

        </div>

    </div>

@endsection
