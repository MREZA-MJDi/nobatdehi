@extends('layouts.app')

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

        $dateFormatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            config('app.timezone'),
            IntlDateFormatter::TRADITIONAL
        );

        $bookingDate = $dateFormatter->format(
            $booking->booking_date
        );

        $startTime = strtr(
            substr($booking->start_time, 0, 5),
            $persianDigits
        );

        $endTime = strtr(
            substr($booking->end_time, 0, 5),
            $persianDigits
        );
    @endphp


    <div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('salon.bookings.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← بازگشت به نوبت‌ها
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                BOOKING
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                جزئیات نوبت
            </h1>

        </div>


        @if(session('success'))

            <div class="mb-5 alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-red-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        <div class="space-y-4">


            {{-- Customer --}}

            <section class="card p-5 sm:p-6">

                <div class="mb-5 text-[10px] font-black text-accent-600">
                    CUSTOMER
                </div>

                <div class="flex items-center gap-3">

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-lg font-black text-accent-700">
                        {{ mb_substr($booking->customer?->name ?? 'م', 0, 1) }}
                    </div>

                    <div class="min-w-0">

                        <div class="text-base font-black text-content">
                            {{ $booking->customer?->name ?? 'مشتری' }}
                        </div>

                        @if($booking->customer?->phone)

                            <div
                                class="mt-1 text-xs font-bold text-content-muted"
                                dir="ltr"
                            >
                                {{ $booking->customer->phone }}
                            </div>

                        @endif

                    </div>

                </div>

            </section>


            {{-- Booking Information --}}

            <section class="card p-5 sm:p-6">

                <div class="mb-5 text-[10px] font-black text-accent-600">
                    BOOKING INFORMATION
                </div>

                <div class="grid gap-5 sm:grid-cols-2">

                    <div>

                        <div class="text-[10px] text-content-muted">
                            آرایشگر
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ $booking->barber?->name ?? '—' }}
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            خدمت
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ $booking->service?->name ?? '—' }}
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            تاریخ
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ $bookingDate }}
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            ساعت
                        </div>

                        <div
                            class="mt-1 text-sm font-black"
                            dir="ltr"
                        >
                            {{ $startTime }}
                            –
                            {{ $endTime }}
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            مبلغ
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ number_format($booking->price) }}
                            تومان
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            وضعیت فعلی
                        </div>

                        <div class="mt-2">

                            <span class="badge badge-neutral">
                                {{ $booking->status->label() }}
                            </span>

                        </div>

                    </div>

                </div>


                @if($booking->notes)

                    <div class="mt-5 border-t border-border pt-5">

                        <div class="text-[10px] text-content-muted">
                            توضیحات مشتری
                        </div>

                        <div class="mt-2 text-xs leading-7 text-content">
                            {{ $booking->notes }}
                        </div>

                    </div>

                @endif

            </section>


            {{-- Status --}}

            <section class="card p-5 sm:p-6">

                <div class="mb-5">

                    <div class="text-[10px] font-black text-accent-600">
                        STATUS
                    </div>

                    <h2 class="mt-1 text-base font-black">
                        تغییر وضعیت نوبت
                    </h2>

                </div>


                <form
                    action="{{ route('salon.bookings.status', $booking) }}"
                    method="POST"
                >

                    @csrf

                    @method('PATCH')


                    <div class="grid gap-3 sm:grid-cols-2">

                        @foreach(\App\Enums\BookingStatus::cases() as $status)

                            <button
                                type="submit"
                                name="status"
                                value="{{ $status->value }}"
                                class="rounded-2xl border p-4 text-right transition
                                {{ $booking->status === $status
                                    ? 'border-accent-500 bg-accent-50'
                                    : 'border-border bg-white hover:border-accent-200' }}"
                            >

                                <div class="text-xs font-black text-content">
                                    {{ $status->label() }}
                                </div>

                                <div class="mt-1 text-[10px] text-content-muted">

                                    @switch($status)

                                        @case(\App\Enums\BookingStatus::PENDING)
                                        انتظار بررسی
                                        @break

                                        @case(\App\Enums\BookingStatus::CONFIRMED)
                                        نوبت توسط سالن تأیید شده
                                        @break

                                        @case(\App\Enums\BookingStatus::COMPLETED)
                                        خدمت انجام شده
                                        @break

                                        @case(\App\Enums\BookingStatus::CANCELLED)
                                        نوبت لغو شده
                                        @break

                                    @endswitch

                                </div>

                            </button>

                        @endforeach

                    </div>

                </form>

            </section>

        </div>

    </div>

@endsection
