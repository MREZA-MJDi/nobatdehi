@extends('layouts.salon')

@section('title', 'داشبورد سالن')

@section('content')
@php
    $days = [
        0 => 'شنبه',
        1 => 'یکشنبه',
        2 => 'دوشنبه',
        3 => 'سه‌شنبه',
        4 => 'چهارشنبه',
        5 => 'پنجشنبه',
        6 => 'جمعه',
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

    $todaySchedule = $todayIsClosed
        ? 'امروز تعطیل'
        : $todayHours->map(fn ($hour) => $hour['start'] . ' تا ' . $hour['end'])->join(' · ');

    $owner = auth()->user();
@endphp

<div class="salon-page-wrap">

    {{-- Header: one primary action, one public preview --}}
    <section class="salon-dashboard-hero">
        <div class="salon-dashboard-hero-copy">
            <span class="salon-overline">امروز · {{ jalali_date($today) }}</span>
            <h1>سلام {{ $owner->name }} 👋</h1>
            <p>
                {{ $days[($today->dayOfWeek + 1) % 7] }}
                ·
                {{ $todaySchedule }}
            </p>
        </div>

        <div class="salon-dashboard-hero-actions">
            <a href="{{ route('salon.bookings.create') }}" class="salon-btn salon-btn--primary">
                ＋ نوبت دستی
            </a>

            <a href="{{ route('public.salons.show', $salon) }}" target="_blank" rel="noopener" class="salon-btn salon-btn--quiet">
                صفحه عمومی
            </a>
        </div>
    </section>

    {{-- Most important operating block --}}
    <section class="salon-dashboard-command">

        <div class="salon-command-main">
            <span class="salon-overline salon-overline--light">مرکز عملیات امروز</span>

            @if($pendingBookings > 0)
                <h2>{{ $fa($pendingBookings) }} نوبت منتظر رسیدگی است.</h2>
                <p>اول درخواست‌های در انتظار را ببین؛ بعد کارهای تنظیمات و رشد را انجام بده.</p>
                <a href="{{ route('salon.bookings.index', ['status' => 'pending']) }}" class="salon-btn salon-btn--light">
                    نوبت‌های منتظر
                </a>
            @elseif($nextBooking)
                <h2>نوبت بعدی ساعت {{ substr((string) $nextBooking->start_time, 0, 5) }}</h2>
                <p>
                    {{ $nextBooking->customer?->name ?? 'مشتری' }}
                    ·
                    {{ $nextBooking->service?->name ?? 'خدمت' }}
                    ·
                    {{ $nextBooking->barber?->name ?? 'متخصص' }}
                </p>
                <a href="{{ route('salon.bookings.show', $nextBooking) }}" class="salon-btn salon-btn--light">
                    باز کردن نوبت
                </a>
            @else
                <h2>امروز نوبت بعدی ثبت نشده.</h2>
                <p>ساعات کاری و خدمات را آماده نگه دار تا رزرو مشتری بدون اصطکاک انجام شود.</p>
                <a href="{{ route('salon.working-hours.edit') }}" class="salon-btn salon-btn--light">
                    بررسی ساعات کاری
                </a>
            @endif
        </div>

        <div class="salon-command-side">
            <div class="salon-command-status">
                <span>برنامه امروز</span>
                <strong>{{ $todaySchedule }}</strong>
            </div>

            <div class="salon-command-status">
                <span>درآمد امروز</span>
                <strong>{{ number_format($todayRevenue) }} تومان</strong>
            </div>

            <div class="salon-command-status">
                <span>اعلان</span>
                <strong>{{ $unreadNotifications > 0 ? $fa($unreadNotifications) . ' اعلان خوانده‌نشده' : 'همه خوانده شده' }}</strong>
            </div>
        </div>
    </section>

    {{-- Core workspace: bookings + hours + settings --}}
    <section class="salon-workspace-grid">

        <a href="{{ route('salon.bookings.index') }}" class="salon-workspace-card salon-workspace-card--primary">
            <div class="salon-workspace-icon">◷</div>
            <div>
                <span>مرکز نوبت‌ها</span>
                <strong>{{ $fa($todayBookings) }} نوبت فعال امروز</strong>
                <small>{{ $pendingBookings > 0 ? $fa($pendingBookings) . ' مورد نیازمند رسیدگی' : 'درخواستی منتظر نیست' }}</small>
            </div>
            <i>←</i>
        </a>

        <a href="{{ route('salon.working-hours.edit') }}" class="salon-workspace-card">
            <div class="salon-workspace-icon">◴</div>
            <div>
                <span>ساعات کاری</span>
                <strong>{{ $hasWorkingHours ? 'برنامه هفتگی ثبت شده' : 'هنوز تنظیم نشده' }}</strong>
                <small>{{ $todaySchedule }}</small>
            </div>
            <i>←</i>
        </a>

        <a href="{{ route('salon.settings.edit') }}" class="salon-workspace-card">
            <div class="salon-workspace-icon">⚙</div>
            <div>
                <span>تنظیمات سالن</span>
                <strong>اطلاعات و برند سالن</strong>
                <small>تماس، مکان، رنگ‌ها و اطلاعات عمومی</small>
            </div>
            <i>←</i>
        </a>

        <a href="{{ route('salon.notifications.index') }}" class="salon-workspace-card">
            <div class="salon-workspace-icon">◌</div>
            <div>
                <span>اعلان‌ها</span>
                <strong>{{ $unreadNotifications > 0 ? $fa($unreadNotifications) . ' اعلان جدید' : 'اعلان جدیدی نیست' }}</strong>
                <small>رویدادهای مرتبط با نوبت‌ها و حساب</small>
            </div>
            <i>←</i>
        </a>

    </section>

    {{-- Small account overview --}}
    <section class="salon-overview-strip">
        <div>
            <span>تیم فعال</span>
            <strong>{{ $fa($activeBarbers) }}</strong>
            <a href="{{ route('salon.barbers.index') }}">مدیریت تیم</a>
        </div>

        <div>
            <span>خدمات فعال</span>
            <strong>{{ $fa($activeServices) }}</strong>
            <a href="{{ route('salon.services.index') }}">مدیریت خدمات</a>
        </div>

        <div>
            <span>نوبت‌های این ماه</span>
            <strong>{{ $fa($monthBookings) }}</strong>
            <small>{{ number_format($monthRevenue) }} تومان درآمد</small>
        </div>

        <div>
            <span>این هفته</span>
            <strong>{{ number_format($weekRevenue) }} تومان</strong>
            <small>درآمد ثبت‌شده</small>
        </div>
    </section>

    {{-- Upcoming schedule --}}
    <section class="salon-card salon-card--flush">
        <div class="salon-card-head">
            <div>
                <span class="salon-overline">صف رزرو</span>
                <h2>نوبت‌های پیش‌رو</h2>
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
                            <span>
                                {{ $booking->service?->name ?? 'خدمت' }}
                                ·
                                {{ $booking->barber?->name ?? 'متخصص' }}
                            </span>
                        </div>

                        <span class="salon-status salon-status--{{ $tone }}">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="salon-empty-inline">
                <strong>نوبتی در صف نیست.</strong>
                <span>برای مشتری حضوری از «نوبت دستی» استفاده کن.</span>
            </div>
        @endif
    </section>

    {{-- Recent activity --}}
    <section class="salon-card salon-card--flush">
        <div class="salon-card-head">
            <div>
                <span class="salon-overline">فعالیت</span>
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
                            <span>
                                {{ $booking->service?->name ?? 'خدمت' }}
                                ·
                                {{ $booking->barber?->name ?? 'متخصص' }}
                            </span>
                        </div>

                        <div>
                            <small>
                                {{ jalali_date($booking->booking_date) }}
                                ·
                                {{ substr((string) $booking->start_time, 0, 5) }}
                            </small>
                            <span class="salon-status salon-status--{{ $tone }}">{{ $label }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="salon-empty-inline">هنوز فعالیتی ثبت نشده است.</div>
        @endif
    </section>

    {{-- Growth shortcuts: intentionally compact and not duplicated in primary nav --}}
    <section class="salon-secondary-tools">
        <a href="{{ route('salon.barbers.index') }}">
            <span>♙</span>
            <div>
                <strong>تیم</strong>
                <small>{{ $fa($activeBarbers) }} متخصص فعال</small>
            </div>
        </a>

        <a href="{{ route('salon.services.index') }}">
            <span>✦</span>
            <div>
                <strong>خدمات</strong>
                <small>{{ $fa($activeServices) }} خدمت فعال</small>
            </div>
        </a>

        <a href="{{ route('salon.posts.index') }}">
            <span>▤</span>
            <div>
                <strong>محتوا</strong>
                <small>پست‌ها و معرفی خدمات</small>
            </div>
        </a>

        <a href="{{ route('salon.reviews.index') }}">
            <span>♡</span>
            <div>
                <strong>نظرات</strong>
                <small>بازخورد مشتریان</small>
            </div>
        </a>
    </section>

</div>
@endsection
