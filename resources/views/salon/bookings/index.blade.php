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

    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                >
                    ← داشبورد سالن
                </a>

                <div class="text-[10px] font-black tracking-wider text-accent-600">
                    BOOKINGS
                </div>

                <h1 class="mt-2 text-2xl font-black text-content">
                    نوبت‌های {{ $salon->name }}
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    نوبت‌های مشتریان و رزروهای دستی سالن را مدیریت کنید.
                </p>

            </div>

            <a
                href="{{ route('salon.bookings.create') }}"
                class="btn btn-accent"
            >
                + ثبت نوبت دستی
            </a>

        </div>


        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-red-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        @if($bookings->count())

            <div class="space-y-3">

                @foreach($bookings as $booking)

                    @php
                        $bookingDate = strtr(
                            $booking->booking_date->format('Y/m/d'),
                            $persianDigits
                        );

                        $startTime = strtr(
                            substr(
                                $booking->start_time,
                                0,
                                5
                            ),
                            $persianDigits
                        );

                        $endTime = strtr(
                            substr(
                                $booking->end_time,
                                0,
                                5
                            ),
                            $persianDigits
                        );
                    @endphp

                    <article class="card p-4 sm:p-5">

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            <div class="min-w-0">

                                <div class="flex items-start gap-3">

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-700">

                                        {{ mb_substr($booking->customer?->name ?? 'م', 0, 1) }}

                                    </div>

                                    <div class="min-w-0">

                                        <div class="truncate text-sm font-black text-content">
                                            {{ $booking->customer?->name ?? 'مشتری' }}
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            {{ $booking->service?->name ?? 'خدمت' }}
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-faint">
                                            آرایشگر:
                                            {{ $booking->barber?->name ?? '—' }}
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="flex flex-wrap items-center gap-2">

                                <span class="badge">
                                    {{ $bookingDate }}
                                </span>

                                <span class="rounded-xl bg-primary-50 px-3 py-2 font-bold text-content">
                                    <span dir="ltr">
                                        {{ $startTime }}
                                        –
                                        {{ $endTime }}
                                    </span>
                                </span>

                            </div>


                            <div>

                                <div class="text-[10px] text-content-muted">
                                    مبلغ
                                </div>

                                <div class="mt-1 text-sm font-black text-content">
                                    {{ strtr(number_format($booking->price), $persianDigits) }}
                                    تومان
                                </div>

                            </div>


                            <div>

                                @switch($booking->status->value)

                                    @case('pending')

                                    <span class="badge bg-amber-50 text-amber-700">
                                            در انتظار
                                        </span>

                                    @break

                                    @case('confirmed')

                                    <span class="badge badge-success">
                                            تأیید شده
                                        </span>

                                    @break

                                    @case('completed')

                                    <span class="badge bg-accent-50 text-accent-700">
                                            تکمیل شده
                                        </span>

                                    @break

                                    @case('cancelled')

                                    <span class="badge bg-red-50 text-red-700">
                                            لغو شده
                                        </span>

                                    @break

                                @endswitch

                            </div>


                            <div class="shrink-0">

                                <a
                                    href="{{ route('salon.bookings.show', $booking) }}"
                                    class="btn btn-secondary btn-sm"
                                >
                                    جزئیات
                                </a>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            @if($bookings->hasPages())

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>

            @endif

        @else

            <section class="card p-8 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                    ◷
                </div>

                <h2 class="mt-4 text-base font-black text-content">
                    هنوز نوبتی ثبت نشده
                </h2>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    می‌توانید اولین نوبت را به‌صورت دستی ثبت کنید.
                </p>

                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="btn btn-accent mt-5"
                >
                    ثبت نوبت دستی
                </a>

            </section>

        @endif

    </div>

@endsection
