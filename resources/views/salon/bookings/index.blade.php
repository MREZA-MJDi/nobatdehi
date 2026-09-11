@extends('layouts.salon')

@section('title', 'نوبت‌های سالن')

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
    @endphp


    <div class="mx-auto w-full max-w-6xl px-4 py-6 pb-28 sm:px-6 lg:px-8">


        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('salon.dashboard') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← داشبورد سالن
            </a>


            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div>

                    <div class="mb-2 text-[10px] font-black tracking-[0.18em] text-accent-600">
                        BOOKINGS
                    </div>

                    <h1 class="text-2xl font-black text-content sm:text-3xl">
                        نوبت‌های {{ $salon->name }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-xs leading-6 text-content-muted">
                        نوبت‌های مشتریان و رزروهای دستی سالن را از همین صفحه مدیریت کنید.
                    </p>

                </div>


                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="btn btn-accent w-full sm:w-auto"
                >
                    + ثبت نوبت دستی
                </a>

            </div>

        </div>


        {{-- ============================================================
            ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4">

                <div class="mb-2 text-[10px] font-black text-danger-700">
                    خطا در انجام عملیات
                </div>

                <div class="space-y-1">

                    @foreach($errors->all() as $error)

                        <div class="text-[10px] font-bold leading-6 text-danger-700">
                            • {{ $error }}
                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        {{-- ============================================================
            BOOKINGS
        ============================================================= --}}

        @if($bookings->count())

            <div class="space-y-4">

                @foreach($bookings as $booking)

                    @php
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

                        $status = $booking->status;

                        $statusMeta = match ($status) {

                            \App\Enums\BookingStatus::PENDING => [
                                'label' => 'در انتظار',
                                'class' => 'bg-warning-50 text-warning-700',
                                'dot' => 'bg-warning-500',
                            ],

                            \App\Enums\BookingStatus::CONFIRMED => [
                                'label' => 'تأیید شده',
                                'class' => 'bg-success-50 text-success-700',
                                'dot' => 'bg-success-500',
                            ],

                            \App\Enums\BookingStatus::COMPLETED => [
                                'label' => 'تکمیل شده',
                                'class' => 'bg-accent-50 text-accent-700',
                                'dot' => 'bg-accent-600',
                            ],

                            \App\Enums\BookingStatus::CANCELLED => [
                                'label' => 'لغو شده',
                                'class' => 'bg-danger-50 text-danger-700',
                                'dot' => 'bg-danger-500',
                            ],

                        };
                    @endphp


                    <article
                        class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft transition hover:-translate-y-0.5 hover:shadow-card"
                    >

                        {{-- ==================================================
                            TOP
                        =================================================== --}}

                        <div class="p-5 sm:p-6">

                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">


                                {{-- CUSTOMER / SERVICE --}}

                                <div class="flex min-w-0 items-center gap-4">

                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-lg font-black text-accent-700"
                                    >
                                        {{ mb_substr(
                                            $booking->customer?->name ?? 'م',
                                            0,
                                            1
                                        ) }}
                                    </div>


                                    <div class="min-w-0">

                                        <div class="truncate text-sm font-black text-content">
                                            {{ $booking->customer?->name ?? 'مشتری' }}
                                        </div>

                                        <div class="mt-1 truncate text-[10px] font-bold text-content-soft">
                                            {{ $booking->service?->name ?? 'خدمت' }}
                                        </div>

                                        <div class="mt-1 truncate text-[10px] text-content-faint">
                                            آرایشگر:
                                            {{ $booking->barber?->name ?? '—' }}
                                        </div>

                                    </div>

                                </div>


                                {{-- STATUS --}}

                                <div class="flex items-center gap-2">

                                    <span
                                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-[10px] font-black {{ $statusMeta['class'] }}"
                                    >

                                        <span
                                            class="h-1.5 w-1.5 rounded-full {{ $statusMeta['dot'] }}"
                                        ></span>

                                        {{ $statusMeta['label'] }}

                                    </span>

                                </div>

                            </div>


                            {{-- ==================================================
                                FACTS
                            =================================================== --}}

                            <div class="mt-5 grid gap-2 sm:grid-cols-3">


                                {{-- DATE --}}

                                <div class="rounded-2xl bg-primary-50 p-3">

                                    <div class="text-[9px] font-bold text-content-muted">
                                        تاریخ
                                    </div>

                                    <div class="mt-1 text-xs font-black text-content">
                                        {{ $bookingDate }}
                                    </div>

                                </div>


                                {{-- TIME --}}

                                <div class="rounded-2xl bg-primary-50 p-3">

                                    <div class="text-[9px] font-bold text-content-muted">
                                        ساعت
                                    </div>

                                    <div
                                        class="mt-1 text-xs font-black text-content"
                                        dir="ltr"
                                    >
                                        {{ $startTime }}
                                        <span class="mx-1 text-content-faint">
                                            –
                                        </span>
                                        {{ $endTime }}
                                    </div>

                                </div>


                                {{-- PRICE --}}

                                <div class="rounded-2xl bg-primary-50 p-3">

                                    <div class="text-[9px] font-bold text-content-muted">
                                        مبلغ
                                    </div>

                                    <div class="mt-1 text-xs font-black text-content">

                                        {{ $price }}

                                        <span class="text-[9px] font-bold text-content-muted">
                                            تومان
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- ==================================================
                            FOOTER
                        =================================================== --}}

                        <div
                            class="flex flex-col gap-3 border-t border-border bg-primary-50/60 p-4 sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div class="text-[10px] text-content-muted">

                                <span class="font-bold text-content-soft">
                                    نوبت
                                </span>

                                <span class="mx-1">
                                    #
                                </span>

                                <span class="font-black text-content">
                                    {{ strtr(
                                        (string) $booking->id,
                                        $persianDigits
                                    ) }}
                                </span>

                            </div>


                            <a
                                href="{{ route('salon.bookings.show', $booking) }}"
                                class="btn btn-secondary btn-sm w-full sm:w-auto"
                            >
                                مشاهده جزئیات
                                <span class="mr-1">
                                    ←
                                </span>
                            </a>

                        </div>

                    </article>

                @endforeach

            </div>


            {{-- ============================================================
                PAGINATION
            ============================================================= --}}

            @if($bookings->hasPages())

                <div class="mt-6">

                    {{ $bookings->links() }}

                </div>

            @endif


        @else

            {{-- ============================================================
                EMPTY STATE
            ============================================================= --}}

            <section
                class="rounded-3xl border border-border bg-surface p-8 text-center shadow-card sm:p-12"
            >

                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-accent-50 text-xl font-black text-accent-600"
                >
                    ◷
                </div>


                <h2 class="mt-5 text-base font-black text-content">
                    هنوز نوبتی ثبت نشده
                </h2>


                <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-content-muted">
                    هنوز هیچ نوبتی برای سالن ثبت نشده است.
                    می‌توانید اولین نوبت را به‌صورت دستی ایجاد کنید.
                </p>


                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="btn btn-accent mt-5"
                >
                    + ثبت نوبت دستی
                </a>

            </section>

        @endif

    </div>

@endsection

