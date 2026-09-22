@extends('layouts.salon')

@section('title', 'نوبت‌ها')

@section('content')
@php
    use App\Enums\BookingStatus;

    $fa = fn ($value) => strtr((string) $value, [
        '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴',
        '5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
    ]);

    $statusMeta = [
        BookingStatus::PENDING->value => ['در انتظار', 'pending'],
        BookingStatus::CONFIRMED->value => ['تأیید شده', 'confirmed'],
        BookingStatus::COMPLETED->value => ['انجام شده', 'completed'],
        BookingStatus::CANCELLED->value => ['لغو شده', 'cancelled'],
    ];
@endphp

<div class="salon-page-wrap">
    <section class="salon-page-header">
        <div>
            <span class="salon-overline">نوبت‌ها</span>
            <h1>برنامه رزرو سالن</h1>
            <p>جست‌وجو، فیلتر و رسیدگی به نوبت‌ها از یک صفحه. اطلاعات مهم جلوتر، جزئیات در دسترس.</p>
        </div>
        <a href="{{ route('salon.bookings.create') }}" class="salon-btn salon-btn--primary">＋ ثبت نوبت دستی</a>
    </section>

    <form method="GET" action="{{ route('salon.bookings.index') }}" class="salon-filter-bar">
        <label class="salon-filter-field salon-filter-field--search">
            <span>جست‌وجو</span>
            <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="نام مشتری، شماره، خدمت یا متخصص">
        </label>

        <label class="salon-filter-field">
            <span>وضعیت</span>
            <select name="status">
                <option value="">همه وضعیت‌ها</option>
                @foreach($statusMeta as $value => [$label])
                    <option value="{{ $value }}" @selected(($status ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="salon-filter-field">
            <span>تاریخ شمسی</span>
            <input type="text" name="date" value="{{ $dateInput ?? '' }}" inputmode="numeric" placeholder="۱۴۰۵/۰۷/۰۱" autocomplete="off">
        </label>

        <button class="salon-btn salon-btn--dark" type="submit">اعمال</button>

        @if(($search ?? '') !== '' || ($status ?? '') !== '' || ($dateInput ?? '') !== '')
            <a href="{{ route('salon.bookings.index') }}" class="salon-btn salon-btn--quiet">پاک کردن</a>
        @endif
    </form>

    @if(!empty($dateError))
        <div class="salon-owner__flash is-error">{{ $dateError }}</div>
    @endif

    <div class="salon-booking-summary">
        <a href="{{ route('salon.bookings.index') }}"><strong>{{ $fa($stats['all'] ?? 0) }}</strong><span>کل</span></a>
        <a href="{{ route('salon.bookings.index', ['status' => 'pending']) }}"><strong>{{ $fa($stats['pending'] ?? 0) }}</strong><span>در انتظار</span></a>
        <a href="{{ route('salon.bookings.index', ['status' => 'confirmed']) }}"><strong>{{ $fa($stats['confirmed'] ?? 0) }}</strong><span>تأیید شده</span></a>
        <div class="salon-booking-summary__today">امروز {{ jalali_date(now(config('app.timezone'))) }} · <strong>{{ $fa($todayBookingsCount ?? 0) }}</strong> نوبت فعال</div>
    </div>

    @if($bookings->isNotEmpty())
        <section class="salon-card salon-card--flush">
            <div class="salon-card-head">
                <div>
                    <span class="salon-overline">فهرست</span>
                    <h2>{{ $bookings->total() }} نتیجه</h2>
                </div>
            </div>

            <div class="salon-bookings-table">
                @foreach($bookings as $booking)
                    @php([$statusLabel, $statusTone] = $statusMeta[$booking->status->value] ?? ['نامشخص', 'default'])

                    <article class="salon-booking-item">
                        <div class="salon-booking-item__date">
                            <strong>{{ substr((string) $booking->start_time, 0, 5) }}</strong>
                            <span>{{ jalali_date($booking->booking_date) }}</span>
                        </div>

                        <div class="salon-booking-item__customer">
                            <strong>{{ $booking->customer?->name ?? $booking->customer_name ?? 'مشتری' }}</strong>
                            <span>{{ $booking->customer?->phone ?? $booking->customer_phone ?? 'شماره ثبت نشده' }}</span>
                        </div>

                        <div class="salon-booking-item__service">
                            <strong>{{ $booking->service?->name ?? 'خدمت حذف شده' }}</strong>
                            <span>{{ $booking->barber?->name ?? 'متخصص حذف شده' }}</span>
                        </div>

                        <span class="salon-status salon-status--{{ $statusTone }}">{{ $statusLabel }}</span>

                        <div class="salon-booking-item__actions">
                            @if($booking->status === BookingStatus::PENDING)
                                <form action="{{ route('salon.bookings.status', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button class="salon-mini-action salon-mini-action--success" type="submit">تأیید</button>
                                </form>
                                <form action="{{ route('salon.bookings.status', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="salon-mini-action" type="submit">لغو</button>
                                </form>
                            @elseif($booking->status === BookingStatus::CONFIRMED)
                                <form action="{{ route('salon.bookings.status', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button class="salon-mini-action salon-mini-action--success" type="submit">تکمیل</button>
                                </form>
                            @endif

                            <a href="{{ route('salon.bookings.show', $booking) }}" class="salon-mini-action salon-mini-action--link">جزئیات</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        @if($bookings->hasPages())
            <div class="salon-pagination">{{ $bookings->links() }}</div>
        @endif
    @else
        <section class="salon-empty-state">
            <div class="salon-empty-state__icon">◷</div>
            <h2>هیچ نوبتی با این فیلتر پیدا نشد.</h2>
            <p>فیلترها را ساده‌تر کنید یا از دکمه ثبت نوبت دستی استفاده کنید.</p>
            <a href="{{ route('salon.bookings.index') }}" class="salon-btn salon-btn--quiet">نمایش همه</a>
        </section>
    @endif
</div>
@endsection
