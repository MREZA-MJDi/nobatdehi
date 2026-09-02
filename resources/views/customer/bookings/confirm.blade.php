@extends('layouts.app')

@section('title', 'تأیید نوبت')

@section('content')

    @php
        $dateFormatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            config('app.timezone'),
            IntlDateFormatter::TRADITIONAL
        );

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

        $bookingDate = $dateFormatter->format(
            \Carbon\Carbon::createFromFormat(
                'Y-m-d',
                $pending['booking_date']
            )
        );

        $startTime = strtr(
            $pending['start_time'],
            $persianDigits
        );

        $endTime = strtr(
            $pending['end_time'] ?? '',
            $persianDigits
        );
    @endphp


    <div class="mx-auto w-full max-w-2xl px-4 py-6 pb-28 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('public.salons.booking.create', $salon) }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← تغییر انتخاب‌ها
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                CONFIRM BOOKING
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                تأیید نهایی نوبت
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                اطلاعات را بررسی کنید و نوبت را نهایی کنید.
            </p>

        </div>


        <section class="card overflow-hidden">

            <div class="bg-primary-950 p-5 text-white sm:p-6">

                <div class="text-[10px] font-bold text-accent-300">
                    {{ $salon->name }}
                </div>

                <h2 class="mt-1 text-lg font-black">
                    خلاصه رزرو
                </h2>

            </div>


            <div class="space-y-5 p-5 sm:p-6">

                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <div class="text-[10px] text-content-muted">
                            آرایشگر
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ $barber->name }}
                        </div>
                    </div>


                    <div>
                        <div class="text-[10px] text-content-muted">
                            خدمت
                        </div>

                        <div class="mt-1 text-sm font-black">
                            {{ $service->name }}
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
                        </div>
                    </div>

                </div>


                <div class="flex items-center justify-between border-t border-border pt-5">

                    <span class="text-xs text-content-muted">
                        مبلغ
                    </span>

                    <strong class="text-lg font-black text-content">
                        {{ number_format($service->price) }}
                        تومان
                    </strong>

                </div>


                <form
                    action="{{ route('customer.bookings.store') }}"
                    method="POST"
                    class="space-y-4"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="salon_id"
                        value="{{ $pending['salon_id'] }}"
                    >

                    <input
                        type="hidden"
                        name="barber_id"
                        value="{{ $pending['barber_id'] }}"
                    >

                    <input
                        type="hidden"
                        name="service_id"
                        value="{{ $pending['service_id'] }}"
                    >

                    <input
                        type="hidden"
                        name="booking_date"
                        value="{{ $pending['booking_date'] }}"
                    >

                    <input
                        type="hidden"
                        name="start_time"
                        value="{{ $pending['start_time'] }}"
                    >

                    @if(!empty($pending['notes']))

                        <input
                            type="hidden"
                            name="notes"
                            value="{{ $pending['notes'] }}"
                        >

                    @endif


                    @if($errors->any())

                        <div class="rounded-2xl border border-red-100 bg-red-50 p-4">

                            @foreach($errors->all() as $error)

                                <div class="text-[10px] font-bold leading-6 text-red-700">
                                    • {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    @endif


                    <button
                        type="submit"
                        class="btn btn-accent w-full"
                    >
                        تأیید و ثبت نوبت
                    </button>

                </form>

            </div>

        </section>

    </div>

@endsection
