@extends('layouts.customer')

@section('title', 'بررسی نهایی نوبت')

@section('content')
    <div class="customer-container confirm-page">
        <div class="confirm-shell">

            <header class="confirm-header">
                <div class="confirm-header-top">
                    <a
                        href="{{ route('public.salons.booking.create', $salon) }}"
                        class="confirm-back"
                    >
                        <span aria-hidden="true">→</span>
                        تغییر انتخاب‌ها
                    </a>

                    <span class="confirm-status-pill">
                        <span aria-hidden="true"></span>
                        آماده ثبت
                    </span>
                </div>

                <div class="confirm-title-block">
                    <span class="confirm-kicker">بررسی نهایی</span>

                    <h1>
                        همه‌چیز آماده‌ست.
                    </h1>

                    <p>
                        اطلاعات نوبتت را یک‌بار بررسی کن. بعد از ثبت، درخواست برای سالن ارسال می‌شود
                        و تا زمان تأیید، نوبتت در وضعیت «در انتظار تأیید» قرار دارد.
                    </p>
                </div>
            </header>

            @if($errors->any())
                <section class="confirm-error" role="alert">
                    <div class="confirm-error-icon" aria-hidden="true">!</div>

                    <div>
                        <strong>ثبت نوبت انجام نشد</strong>

                        <div class="confirm-error-list">
                            @foreach($errors->all() as $error)
                                <span>{{ $error }}</span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <main class="confirm-stack">

                <section class="confirm-summary-card">
                    <div class="confirm-summary-top">
                        <div class="confirm-salon-block">
                            <span>سالن</span>
                            <h2>{{ $salon->name }}</h2>

                            <p>
                                {{ $service->name }}
                                <span aria-hidden="true">·</span>
                                {{ $barber->name }}
                            </p>
                        </div>

                        <span class="confirm-summary-state">
                            پیش از ثبت
                        </span>
                    </div>

                    <div class="confirm-primary-facts">
                        <div class="confirm-primary-fact">
                            <span>تاریخ</span>

                            <strong>
                                {{ jalali_date($pending['booking_date']) }}
                            </strong>

                            <small>
                                تاریخ نوبت
                            </small>
                        </div>

                        <div class="confirm-primary-fact is-accent">
                            <span>ساعت شروع</span>

                            <strong dir="ltr">
                                {{ substr((string) $pending['start_time'], 0, 5) }}
                            </strong>

                            <small>
                                زمان ورود
                            </small>
                        </div>
                    </div>

                    <div class="confirm-secondary-facts">
                        <div>
                            <span>متخصص</span>
                            <strong>{{ $barber->name }}</strong>
                        </div>

                        <div>
                            <span>خدمت</span>
                            <strong>{{ $service->name }}</strong>
                        </div>

                        <div>
                            <span>مدت</span>
                            <strong>{{ $service->duration_minutes }} دقیقه</strong>
                        </div>

                        <div>
                            <span>مبلغ</span>
                            <strong>{{ number_format($service->price) }} تومان</strong>
                        </div>
                    </div>

                    @if(!empty($pending['notes']))
                        <div class="confirm-note">
                            <div class="confirm-note-head">
                                <span>یادداشت تو</span>
                                <small>برای سالن ارسال می‌شود</small>
                            </div>

                            <p>{{ $pending['notes'] }}</p>
                        </div>
                    @endif
                </section>

                <section class="confirm-info-card">
                    <div class="confirm-info-icon" aria-hidden="true">✓</div>

                    <div>
                        <strong>بعد از ثبت چه اتفاقی می‌افتد؟</strong>

                        <p>
                            نوبت ابتدا در وضعیت «در انتظار تأیید» ثبت می‌شود.
                            از بخش «نوبت‌های من» می‌توانی وضعیتش را ببینی و تا قبل از تأیید سالن،
                            آن را ویرایش یا لغو کنی.
                        </p>
                    </div>
                </section>

                <section class="confirm-action-card">
                    <div class="confirm-action-copy">
                        <span>مبلغ نهایی خدمت</span>

                        <strong>
                            {{ number_format($service->price) }}
                            <small>تومان</small>
                        </strong>

                        <p>
                            با ثبت نوبت، اطلاعات فعلی برای سالن ارسال می‌شود.
                        </p>
                    </div>

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
                            class="customer-btn customer-btn-primary customer-btn-lg confirm-submit"
                        >
                            <span>ثبت نهایی نوبت</span>
                            <span aria-hidden="true">✓</span>
                        </button>

                        <a
                            href="{{ route('public.salons.booking.create', $salon) }}"
                            class="confirm-change-link"
                        >
                            ← می‌خواهم انتخابم را تغییر بدهم
                        </a>
                    </form>
                </section>

            </main>
        </div>
    </div>
@endsection
