@extends('layouts.customer')

@section('title', 'بررسی نهایی نوبت')

@section('content')

    <div class="customer-container confirm-page">

        <div class="mx-auto w-full max-w-6xl">

            <div class="confirm-header">

                <div class="confirm-header-top">
                    <a
                        href="{{ route('public.salons.booking.create', $salon) }}"
                        class="confirm-back"
                    >
                        <span aria-hidden="true">→</span>
                        تغییر انتخاب‌ها
                    </a>

                    <span class="confirm-pending-badge">
                        <span class="confirm-pending-dot" aria-hidden="true"></span>
                        آماده ثبت
                    </span>
                </div>

                <div class="confirm-title-block">
                    <span class="section-kicker">FINAL REVIEW</span>

                    <h1>
                        قبل از ثبت، یه بار همه‌چیز را چک کن.
                    </h1>

                    <p>
                        این همان نوبتی است که می‌خواهی برای سالن ثبت شود.
                        اگر چیزی اشتباه است، از «تغییر انتخاب‌ها» برگرد و اصلاحش کن.
                    </p>
                </div>

                <div class="confirm-steps" aria-label="مراحل رزرو">

                    <div class="confirm-step is-done">
                        <span>01</span>
                        <strong>انتخاب</strong>
                    </div>

                    <div class="confirm-step-line is-done" aria-hidden="true"></div>

                    <div class="confirm-step is-done">
                        <span>02</span>
                        <strong>بررسی نهایی</strong>
                    </div>

                    <div class="confirm-step-line" aria-hidden="true"></div>

                    <div class="confirm-step">
                        <span>03</span>
                        <strong>ثبت نوبت</strong>
                    </div>

                </div>

            </div>


            <div class="confirm-layout">

                <section class="confirm-card">

                    <div class="confirm-card-hero">

                        <div class="confirm-card-identity">

                            <div>
                                <span class="confirm-card-eyebrow">
                                    SALON
                                </span>

                                <h2>
                                    {{ $salon->name }}
                                </h2>

                                <p>
                                    {{ $service->name }}
                                    <span aria-hidden="true">·</span>
                                    {{ $barber->name }}
                                </p>
                            </div>

                            <span class="confirm-status-badge">
                                پیش از ثبت
                            </span>

                        </div>

                        <div class="confirm-card-note">
                            زمان انتخاب‌شده فعلاً برای بررسی نهایی تو نگه داشته شده است.
                        </div>

                    </div>


                    <div class="confirm-details">

                        <div class="confirm-detail emphasis">
                            <span>تاریخ نوبت</span>

                            <strong>
                                {{ jalali_date($pending['booking_date']) }}
                            </strong>

                            <small>
                                تاریخ انتخاب‌شده
                            </small>
                        </div>

                        <div class="confirm-detail emphasis">
                            <span>ساعت</span>

                            <strong dir="ltr">
                                {{ substr((string) $pending['start_time'], 0, 5) }}
                            </strong>

                            <small>
                                زمان شروع
                            </small>
                        </div>

                        <div class="confirm-detail">
                            <span>مدت خدمت</span>

                            <strong>
                                {{ $service->duration_minutes }}
                                دقیقه
                            </strong>

                            <small>
                                مدت تقریبی
                            </small>
                        </div>

                        <div class="confirm-detail">
                            <span>مبلغ</span>

                            <strong>
                                {{ number_format($service->price) }}
                                <small>تومان</small>
                            </strong>

                            <small>
                                مبلغ خدمت
                            </small>
                        </div>

                    </div>


                    @if(!empty($pending['notes']))

                        <div class="confirm-notes">

                            <div>
                                <span class="confirm-notes-kicker">
                                    YOUR NOTE
                                </span>

                                <h3>
                                    توضیحی که برای سالن گذاشتی
                                </h3>
                            </div>

                            <p>
                                {{ $pending['notes'] }}
                            </p>

                        </div>

                    @endif


                    <div class="confirm-management-note">

                        <div class="confirm-management-icon" aria-hidden="true">
                            ↻
                        </div>

                        <div>
                            <strong>
                                بعد از ثبت هم کنترل نوبت دست توست.
                            </strong>

                            <p>
                                نوبت جدید ابتدا در وضعیت «در انتظار تأیید» قرار می‌گیرد.
                                تا وقتی سالن آن را تأیید نکرده، از بخش «نوبت‌های من»
                                می‌توانی زمان، متخصص یا خدمت را ویرایش کنی یا نوبت را لغو کنی.
                            </p>
                        </div>

                    </div>

                </section>


                <aside class="confirm-side">

                    <div class="confirm-side-inner">

                        <span class="section-kicker">
                            آماده ثبت
                        </span>

                        <div class="confirm-side-title-row">
                            <h2>
                                ثبت نهایی
                            </h2>

                            <span class="confirm-side-price">
                                {{ number_format($service->price) }}
                                <small>تومان</small>
                            </span>
                        </div>

                        <p class="confirm-side-lead">
                            با فشردن این دکمه، این انتخاب به‌عنوان نوبت واقعی ثبت می‌شود
                            و برای سالن ارسال خواهد شد.
                        </p>


                        @if($errors->any())

                            <div class="booking-errors" role="alert">

                                <strong>
                                    ثبت انجام نشد
                                </strong>

                                @foreach($errors->all() as $error)
                                    <div>
                                        {{ $error }}
                                    </div>
                                @endforeach

                            </div>

                        @endif


                        <form
                            action="{{ route('customer.bookings.store') }}"
                            method="POST"
                            class="confirm-form"
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

                            <button
                                type="submit"
                                class="customer-btn customer-btn-primary customer-btn-lg w-full confirm-submit"
                            >
                                <span>
                                    تأیید و ثبت نوبت
                                </span>
                                <span aria-hidden="true">✓</span>
                            </button>

                        </form>


                        <a
                            href="{{ route('public.salons.booking.create', $salon) }}"
                            class="confirm-secondary-action"
                        >
                            ← می‌خوام انتخابم را تغییر بدهم
                        </a>


                        <div class="confirm-trust-row">

                            <div>
                                <span aria-hidden="true">✓</span>
                                اطلاعات نوبت قبل از ثبت قابل بررسی است.
                            </div>

                            <div>
                                <span aria-hidden="true">✓</span>
                                وضعیت نوبت بعد از ثبت قابل پیگیری است.
                            </div>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </div>

@endsection
