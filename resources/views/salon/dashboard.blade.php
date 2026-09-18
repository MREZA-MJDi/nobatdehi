@extends('layouts.salon')

@section('title', 'داشبورد سالن')

@section('content')
@php
    $days = [
        0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه', 3 => 'سه‌شنبه',
        4 => 'چهارشنبه', 5 => 'پنجشنبه', 6 => 'جمعه',
    ];

    $fa = fn ($value) => strtr((string) $value, [
        '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴',
        '5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
    ]);

    $status = fn ($value) => match ($value instanceof \App\Enums\BookingStatus ? $value->value : (string) $value) {
        'pending' => ['در انتظار', 'pending'],
        'confirmed' => ['تأیید شده', 'confirmed'],
        'completed' => ['انجام شده', 'completed'],
        'cancelled' => ['لغو شده', 'cancelled'],
        default => ['نامشخص', 'default'],
    };
@endphp

<div class="salon-page-wrap">
    <section class="salon-dashboard-hero">
        <div>
            <span class="salon-overline">داشبورد سالن</span>
            <h1>سلام {{ auth()->user()->name }} 👋</h1>
            <p>
                امروز {{ $days[($today->dayOfWeek + 1) % 7] }}،
                {{ jalali_date($today) }} است. مهم‌ترین کارهای سالن همین‌جا جلوی شماست.
            </p>
        </div>

        <div class="salon-dashboard-hero-actions">
            <a href="{{ route('salon.bookings.create') }}" class="salon-btn salon-btn--primary">＋ نوبت دستی</a>
            <a href="{{ route('public.salons.show', $salon) }}" target="_blank" rel="noopener" class="salon-btn salon-btn--quiet">مشاهده صفحه</a>
        </div>
    </section>

    <section class="salon-dashboard-priority">
        <div class="salon-priority-copy">
            <span class="salon-overline salon-overline--light">اولویت امروز</span>

            @if($pendingBookings > 0)
                <h2>{{ $fa($pendingBookings) }} نوبت منتظر رسیدگی است.</h2>
                <p>برای این مورد لازم نیست وارد چند صفحه شوید؛ درخواست‌های منتظر را مستقیم بررسی کنید.</p>
                <a href="{{ route('salon.bookings.index', ['status' => 'pending']) }}" class="salon-btn salon-btn--light">بررسی نوبت‌های منتظر ←</a>
            @elseif($nextBooking)
                <h2>نوبت بعدی ساعت {{ substr((string) $nextBooking->start_time, 0, 5) }} است.</h2>
                <p>{{ $nextBooking->customer?->name ?? 'مشتری' }} · {{ $nextBooking->service?->name ?? 'خدمت' }}</p>
                <a href="{{ route('salon.bookings.show', $nextBooking) }}" class="salon-btn salon-btn--light">باز کردن نوبت ←</a>
            @else
                <h2>برای امروز کار فوری ندارید.</h2>
                <p>صفحه سالن، خدمات و ساعات کاری را کامل نگه دارید تا مسیر رزرو همیشه آماده باشد.</p>
                <a href="{{ route('salon.settings.edit') }}" class="salon-btn salon-btn--light">باز کردن تنظیمات ←</a>
            @endif
        </div>

        <div class="salon-priority-meta">
            <div>
                <span>برنامه امروز</span>
                <strong>{{ $todayIsClosed ? 'تعطیل' : $todayHours->map(fn($h) => $h['start'].' تا '.$h['end'])->join(' · ') }}</strong>
            </div>
            <div>
                <span>درآمد ثبت‌شده امروز</span>
                <strong>{{ number_format($todayRevenue) }} تومان</strong>
            </div>
            <div>
                <span>اعلان‌های نخوانده</span>
                <strong>{{ $fa($unreadNotifications) }}</strong>
            </div>
        </div>
    </section>

    <section class="salon-stat-grid" aria-label="خلاصه وضعیت سالن">
        <a href="{{ route('salon.bookings.index') }}" class="salon-stat-card">
            <span>نوبت فعال امروز</span>
            <strong>{{ $fa($todayBookings) }}</strong>
            <small>در انتظار یا تأیید شده</small>
        </a>
        <a href="{{ route('salon.bookings.index', ['status' => 'confirmed']) }}" class="salon-stat-card">
            <span>تأیید امروز</span>
            <strong>{{ $fa($confirmedToday) }}</strong>
            <small>رزروهای نهایی‌شده</small>
        </a>
        <a href="{{ route('salon.barbers.index') }}" class="salon-stat-card">
            <span>تیم فعال</span>
            <strong>{{ $fa($activeBarbers) }}</strong>
            <small>متخصص آماده رزرو</small>
        </a>
        <a href="{{ route('salon.services.index') }}" class="salon-stat-card">
            <span>خدمات فعال</span>
            <strong>{{ $fa($activeServices) }}</strong>
            <small>قابل انتخاب برای مشتری</small>
        </a>
    </section>

    <div class="salon-dashboard-grid">
        <section class="salon-card">
            <div class="salon-card-head">
                <div>
                    <span class="salon-overline">قرارهای نزدیک</span>
                    <h2>نوبت‌های آینده</h2>
                </div>
                <a href="{{ route('salon.bookings.index') }}">همه نوبت‌ها ←</a>
            </div>

            @if($upcomingBookings->isNotEmpty())
                <div class="salon-booking-list">
                    @foreach($upcomingBookings as $booking)
                        @php([$label, $tone] = $status($booking->status))
                        <a class="salon-booking-row" href="{{ route('salon.bookings.show', $booking) }}">
                            <div class="salon-booking-time">
                                <strong>{{ substr((string) $booking->start_time, 0, 5) }}</strong>
                                <span>{{ jalali_date($booking->booking_date) }}</span>
                            </div>
                            <div class="salon-booking-main">
                                <strong>{{ $booking->customer?->name ?? 'مشتری حذف شده' }}</strong>
                                <span>{{ $booking->service?->name ?? 'خدمت' }} · {{ $booking->barber?->name ?? 'متخصص' }}</span>
                            </div>
                            <span class="salon-status salon-status--{{ $tone }}">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="salon-empty-inline">
                    <strong>نوبت آینده‌ای ندارید.</strong>
                    <span>برای مشتری حضوری، نوبت دستی ثبت کنید.</span>
                </div>
            @endif
        </section>

        <section class="salon-card">
            <div class="salon-card-head">
                <div>
                    <span class="salon-overline">کارهای سریع</span>
                    <h2>کارهای روزمره</h2>
                </div>
            </div>

            <div class="salon-action-list">
                <a href="{{ route('salon.bookings.create') }}"><span>＋</span><div><strong>ثبت نوبت دستی</strong><small>رزرو مشتری حضوری</small></div></a>
                <a href="{{ route('salon.services.create') }}"><span>✦</span><div><strong>افزودن خدمت</strong><small>قیمت، مدت و وضعیت</small></div></a>
                <a href="{{ route('salon.barbers.create') }}"><span>♙</span><div><strong>افزودن عضو تیم</strong><small>پروفایل و شماره تماس</small></div></a>
                <a href="{{ route('salon.settings.edit') }}"><span>⚙</span><div><strong>تکمیل تنظیمات</strong><small>برند، مکان، تماس و ساعات</small></div></a>
            </div>
        </section>
    </div>

    <section class="salon-card salon-card--flush">
        <div class="salon-card-head">
            <div>
                <span class="salon-overline">آخرین فعالیت</span>
                <h2>آخرین نوبت‌ها</h2>
            </div>
        </div>

        @if($recentBookings->isNotEmpty())
            <div class="salon-recent-grid">
                @foreach($recentBookings as $booking)
                    @php([$label, $tone] = $status($booking->status))
                    <a href="{{ route('salon.bookings.show', $booking) }}" class="salon-recent-item">
                        <div>
                            <strong>{{ $booking->customer?->name ?? 'مشتری حذف شده' }}</strong>
                            <span>{{ $booking->service?->name ?? 'خدمت' }} · {{ $booking->barber?->name ?? 'متخصص' }}</span>
                        </div>
                        <div>
                            <small>{{ jalali_date($booking->booking_date) }} · {{ substr((string) $booking->start_time, 0, 5) }}</small>
                            <span class="salon-status salon-status--{{ $tone }}">{{ $label }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="salon-empty-inline">هنوز نوبتی ثبت نشده است.</div>
        @endif
    </section>
</div>
@endsection
