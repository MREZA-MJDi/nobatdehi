@extends('layouts.customer')

@section('title', 'تأیید نوبت')

@section('content')

    <div class="customer-container confirm-page">

        <div class="confirm-header">

            <a
                href="{{ route(
                'public.salons.booking.create',
                $salon
            ) }}"
                class="back-link"
            >
                ← تغییر انتخاب‌ها
            </a>

            <span class="section-kicker">
            FINAL CHECK
        </span>

            <h1>
                نوبتت آماده ثبت است.
            </h1>

            <p>
                اطلاعات را یک‌بار بررسی کن و بعد نوبت را قطعی کن.
            </p>

        </div>


        <div class="confirm-layout">

            <section class="confirm-card">

                <div class="confirm-card-hero">

                <span>
                    {{ $salon->name }}
                </span>

                    <strong>
                        {{ $service->name }}
                    </strong>

                    <small>
                        {{ $barber->name }}
                    </small>

                </div>


                <div class="confirm-details">

                    <div>
                        <span>تاریخ</span>
                        <strong>
                            {{ $pending['booking_date'] }}
                        </strong>
                    </div>

                    <div>
                        <span>ساعت</span>
                        <strong dir="ltr">
                            {{ $pending['start_time'] }}
                        </strong>
                    </div>

                    <div>
                        <span>مدت</span>
                        <strong>
                            {{ $service->duration_minutes }}
                            دقیقه
                        </strong>
                    </div>

                    <div>
                        <span>مبلغ</span>
                        <strong>
                            {{ number_format($service->price) }}
                            تومان
                        </strong>
                    </div>

                </div>


                @if(!empty($pending['notes']))

                    <div class="confirm-notes">

                    <span>
                        توضیحات
                    </span>

                        <p>
                            {{ $pending['notes'] }}
                        </p>

                    </div>

                @endif

            </section>


            <aside class="confirm-side">

                <div class="confirm-side-inner">

                <span class="section-kicker">
                    READY TO BOOK
                </span>

                    <h2>
                        ثبت نهایی
                    </h2>

                    <p>
                        با ثبت این فرم، زمان انتخاب‌شده برای شما ایجاد می‌شود.
                    </p>


                    <form
                        action="{{ route(
                        'customer.bookings.store'
                    ) }}"
                        method="POST"
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

                        <input
                            type="hidden"
                            name="notes"
                            value="{{ $pending['notes'] ?? '' }}"
                        >


                        @if($errors->any())

                            <div class="booking-errors">

                                @foreach($errors->all() as $error)

                                    <div>
                                        {{ $error }}
                                    </div>

                                @endforeach

                            </div>

                        @endif


                        <button
                            type="submit"
                            class="customer-btn customer-btn-primary customer-btn-lg w-full"
                        >
                            تأیید و ثبت نوبت
                            ✓
                        </button>

                    </form>

                </div>

            </aside>

        </div>

    </div>

@endsection
