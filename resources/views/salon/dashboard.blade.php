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

    $money = fn ($value) => number_format((int) $value) . ' تومان';

    $status = fn ($value) => match (
        $value instanceof \App\Enums\BookingStatus
            ? $value->value
            : (string) $value
    ) {
        'pending' => ['در انتظار', 'pending'],
        'confirmed' => ['تأیید شده', 'confirmed'],
        'completed' => ['انجام شده', 'completed'],
        'cancelled' => ['لغو شده', 'cancelled'],
        default => ['نامشخص', 'default'],
    };

    $todaySchedule = $todayIsClosed
        ? 'امروز تعطیل'
        : $todayHours->map(
            fn ($hour) => $hour['start'] . ' تا ' . $hour['end']
        )->join(' · ');

    $weeklyTotal = (int) $weeklyRevenueChart->sum('value');
    $monthlyTotal = (int) $monthlyRevenueChart->sum('value');

    $owner = auth()->user();
@endphp

<div class="salon-page-wrap salon-dashboard-page">

    <section class="salon-dashboard-hero">
        <div class="salon-dashboard-hero-copy">
            <span class="salon-overline">
                امروز · {{ jalali_date($today) }}
            </span>

            <h1>
                سلام {{ $owner->name }} 👋
            </h1>

            <p>
                {{ $days[($today->dayOfWeek + 1) % 7] }}
                ·
                {{ $todaySchedule }}
                ·
                امروز {{ $money($todayRevenue) }} درآمد ثبت شده.
            </p>
        </div>

        <div class="salon-dashboard-hero-actions">
            <a
                href="{{ route('salon.bookings.create') }}"
                class="salon-btn salon-btn--primary"
            >
                ＋ نوبت دستی
            </a>

            <a
                href="{{ route('public.salons.show', $salon) }}"
                target="_blank"
                rel="noopener"
                class="salon-btn salon-btn--quiet"
            >
                صفحه عمومی
            </a>
        </div>
    </section>


    <section class="salon-dashboard-command">

        <div class="salon-command-main">
            <span class="salon-overline salon-overline--light">
                مرکز عملیات امروز
            </span>

            @if($pendingBookings > 0)

                <h2>
                    {{ $fa($pendingBookings) }}
                    نوبت منتظر رسیدگی است.
                </h2>

                <p>
                    اول درخواست‌های در انتظار را بررسی کن تا برنامه امروز مرتب بماند.
                </p>

                <a
                    href="{{ route('salon.bookings.index', ['status' => 'pending']) }}"
                    class="salon-btn salon-btn--light"
                >
                    نوبت‌های منتظر
                </a>

            @elseif($nextBooking)

                <h2>
                    نوبت بعدی ساعت
                    {{ substr((string) $nextBooking->start_time, 0, 5) }}
                </h2>

                <p>
                    {{ $nextBooking->customer?->name ?? 'مشتری' }}
                    ·
                    {{ $nextBooking->service?->name ?? 'خدمت' }}
                    ·
                    {{ $nextBooking->barber?->name ?? 'متخصص' }}
                </p>

                <a
                    href="{{ route('salon.bookings.show', $nextBooking) }}"
                    class="salon-btn salon-btn--light"
                >
                    باز کردن نوبت
                </a>

            @else

                <h2>
                    امروز نوبت بعدی ثبت نشده.
                </h2>

                <p>
                    ساعات کاری و خدماتت آماده‌اند؛ برای مشتری حضوری می‌توانی همین حالا نوبت دستی ثبت کنی.
                </p>

                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="salon-btn salon-btn--light"
                >
                    ثبت نوبت دستی
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
                <strong>{{ $money($todayRevenue) }}</strong>
            </div>

            <div class="salon-command-status">
                <span>اعلان</span>
                <strong>
                    {{ $unreadNotifications > 0
                        ? $fa($unreadNotifications) . ' اعلان خوانده‌نشده'
                        : 'همه خوانده شده' }}
                </strong>
            </div>
        </div>
    </section>


    <section class="salon-workspace-grid">

        <a
            href="{{ route('salon.bookings.index') }}"
            class="salon-workspace-card salon-workspace-card--primary"
        >
            <div class="salon-workspace-icon">◷</div>

            <div>
                <span>مرکز نوبت‌ها</span>
                <strong>{{ $fa($todayBookings) }} نوبت فعال امروز</strong>
                <small>
                    {{ $pendingBookings > 0
                        ? $fa($pendingBookings) . ' مورد نیازمند رسیدگی'
                        : 'درخواستی منتظر نیست' }}
                </small>
            </div>

            <i>←</i>
        </a>

        <a
            href="{{ route('salon.working-hours.edit') }}"
            class="salon-workspace-card"
        >
            <div class="salon-workspace-icon">◴</div>

            <div>
                <span>ساعات کاری</span>
                <strong>
                    {{ $hasWorkingHours
                        ? 'برنامه هفتگی ثبت شده'
                        : 'هنوز تنظیم نشده' }}
                </strong>
                <small>{{ $todaySchedule }}</small>
            </div>

            <i>←</i>
        </a>

        <a
            href="{{ route('salon.settings.edit') }}"
            class="salon-workspace-card"
        >
            <div class="salon-workspace-icon">⚙</div>

            <div>
                <span>تنظیمات سالن</span>
                <strong>اطلاعات و برند سالن</strong>
                <small>تماس، مکان، اطلاعات عمومی و ظاهر</small>
            </div>

            <i>←</i>
        </a>

        <a
            href="{{ route('salon.notifications.index') }}"
            class="salon-workspace-card"
        >
            <div class="salon-workspace-icon">◌</div>

            <div>
                <span>اعلان‌ها</span>
                <strong>
                    {{ $unreadNotifications > 0
                        ? $fa($unreadNotifications) . ' اعلان جدید'
                        : 'اعلان جدیدی نیست' }}
                </strong>
                <small>رویدادهای مرتبط با نوبت‌ها و حساب</small>
            </div>

            <i>←</i>
        </a>
    </section>


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
            <small>{{ $money($monthRevenue) }} درآمد</small>
        </div>

        <div>
            <span>این هفته</span>
            <strong>{{ $money($weekRevenue) }}</strong>
            <small>درآمد ثبت‌شده</small>
        </div>
    </section>


    {{-- Revenue analytics --}}
    <section class="salon-revenue-card">

        <header class="salon-revenue-head">
            <div>
                <span class="salon-overline">تحلیل درآمد</span>

                <h2>
                    درآمد هفته و ماه
                </h2>

                <p>
                    فقط نوبت‌های تأییدشده و انجام‌شده در این نمودار محاسبه شده‌اند.
                </p>
            </div>

            <div class="salon-revenue-highlight">
                <span>این ماه</span>
                <strong>{{ $money($monthRevenue) }}</strong>
            </div>
        </header>

        <div class="salon-revenue-grid">

            <div class="salon-revenue-chart-card">
                <div class="salon-chart-card-head">
                    <div>
                        <span>۷ روز اخیرِ هفته جاری</span>
                        <strong>{{ $money($weeklyTotal) }}</strong>
                    </div>

                    <span class="salon-chart-badge">هفتگی</span>
                </div>

                <div class="salon-chart-bars salon-chart-bars--weekly">
                    @foreach($weeklyRevenueChart as $index => $point)
                        @php
                            $height = $weeklyRevenueMax > 0
                                ? max(5, round(($point['value'] / $weeklyRevenueMax) * 100))
                                : 5;
                        @endphp

                        <div class="salon-chart-column">
                            <div class="salon-chart-value">
                                {{ $point['value'] > 0 ? $money($point['value']) : '—' }}
                            </div>

                            <div class="salon-chart-track">
                                <div
                                    class="salon-chart-fill"
                                    style="height: {{ $height }}%;"
                                    title="{{ $money($point['value']) }}"
                                ></div>
                            </div>

                            <span>
                                {{ $days[$index] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>


            <div class="salon-revenue-chart-card">

                <div class="salon-chart-card-head">
                    <div>
                        <span>۶ ماه اخیر</span>
                        <strong>{{ $money($monthlyTotal) }}</strong>
                    </div>

                    <span class="salon-chart-badge">ماهانه</span>
                </div>

                <div class="salon-chart-bars salon-chart-bars--monthly">
                    @foreach($monthlyRevenueChart as $point)
                        @php
                            $height = $monthlyRevenueMax > 0
                                ? max(5, round(($point['value'] / $monthlyRevenueMax) * 100))
                                : 5;

                            $monthLabel = jalali_date(
                                \Carbon\Carbon::parse($point['date'])
                            );
                        @endphp

                        <div class="salon-chart-column">
                            <div class="salon-chart-value">
                                {{ $point['value'] > 0 ? $money($point['value']) : '—' }}
                            </div>

                            <div class="salon-chart-track">
                                <div
                                    class="salon-chart-fill"
                                    style="height: {{ $height }}%;"
                                    title="{{ $money($point['value']) }}"
                                ></div>
                            </div>

                            <span title="{{ $monthLabel }}">
                                {{ $monthLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>


    <section class="salon-dashboard-columns">

        <section class="salon-card salon-card--flush">
            <div class="salon-card-head">
                <div>
                    <span class="salon-overline">صف رزرو</span>
                    <h2>نوبت‌های پیش‌رو</h2>
                </div>

                <a href="{{ route('salon.bookings.index') }}">
                    همه نوبت‌ها ←
                </a>
            </div>

            @if($upcomingBookings->isNotEmpty())

                <div class="salon-booking-list">
                    @foreach($upcomingBookings as $booking)
                        @php([$label, $tone] = $status($booking->status))

                        <a
                            class="salon-booking-row"
                            href="{{ route('salon.bookings.show', $booking) }}"
                        >
                            <div class="salon-booking-time">
                                <strong>
                                    {{ substr((string) $booking->start_time, 0, 5) }}
                                </strong>

                                <span>
                                    {{ jalali_date($booking->booking_date) }}
                                </span>
                            </div>

                            <div class="salon-booking-main">
                                <strong>
                                    {{ $booking->customer?->name ?? 'مشتری حذف شده' }}
                                </strong>

                                <span>
                                    {{ $booking->service?->name ?? 'خدمت' }}
                                    ·
                                    {{ $booking->barber?->name ?? 'متخصص' }}
                                </span>
                            </div>

                            <span class="salon-status salon-status--{{ $tone }}">
                                {{ $label }}
                            </span>
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

                        <a
                            href="{{ route('salon.bookings.show', $booking) }}"
                            class="salon-recent-item"
                        >
                            <div>
                                <strong>
                                    {{ $booking->customer?->name ?? 'مشتری حذف شده' }}
                                </strong>

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

                                <span class="salon-status salon-status--{{ $tone }}">
                                    {{ $label }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

            @else

                <div class="salon-empty-inline">
                    هنوز فعالیتی ثبت نشده است.
                </div>

            @endif
        </section>

    </section>


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
