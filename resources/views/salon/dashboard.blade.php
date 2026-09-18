@extends('layouts.salon')

@section('title', 'داشبورد سالن')

@push('head')
    @vite('resources/css/salon-dashboard.css')
@endpush

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

    $money = fn ($value) =>
        number_format((int) $value) . ' تومان';

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
            fn ($hour) =>
                $hour['start'] . ' تا ' . $hour['end']
        )->join(' · ');
@endphp

<div
    class="nd-dashboard"
    data-dashboard
    data-dashboard-endpoint="{{ route('salon.dashboard.data') }}"
    data-today="{{ $today->toDateString() }}"
    data-stale-label="آخرین اطلاعات دریافت‌شده از سرور"
>

    <section class="nd-dashboard-hero">
        <div class="nd-dashboard-hero-copy">
            <div class="nd-dashboard-eyebrow">
                <span class="nd-dot"></span>
                <span>مرکز مدیریت سالن</span>
            </div>

            <h1>
                سلام {{ auth()->user()->name }} 👋
            </h1>

            <p>
                {{ $days[($today->dayOfWeek + 1) % 7] }}
                ·
                {{ jalali_date($today) }}
                ·
                {{ $todaySchedule }}
            </p>
        </div>

        <div class="nd-dashboard-hero-actions">
            <a
                href="{{ route('salon.bookings.create') }}"
                class="nd-btn nd-btn-primary"
            >
                <span>＋</span>
                نوبت دستی
            </a>

            <button
                type="button"
                class="nd-btn nd-btn-ghost"
                data-dashboard-refresh
            >
                <span class="nd-refresh-icon">↻</span>
                به‌روزرسانی
            </button>

            <a
                href="{{ route('public.salons.show', $salon) }}"
                target="_blank"
                rel="noopener"
                class="nd-btn nd-btn-ghost"
            >
                مشاهده صفحه عمومی
                <span>↗</span>
            </a>
        </div>
    </section>


    <section class="nd-dashboard-alert">

        <div class="nd-dashboard-alert-main">

            <div class="nd-kicker">وضعیت عملیات امروز</div>

            @if($pendingBookings > 0)
                <h2>
                    {{ $fa($pendingBookings) }}
                    نوبت منتظر رسیدگی است.
                </h2>

                <p>
                    درخواست‌های معطل را بررسی کن تا برنامه‌ی سالن مرتب بماند.
                </p>

                <a
                    href="{{ route('salon.bookings.index', ['status' => 'pending']) }}"
                    class="nd-alert-action"
                >
                    بررسی نوبت‌های منتظر
                    <span>←</span>
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
                    class="nd-alert-action"
                >
                    باز کردن نوبت
                    <span>←</span>
                </a>
            @else
                <h2>
                    امروز نوبت بعدی ثبت نشده.
                </h2>

                <p>
                    برای مشتری حضوری یا ثبت رزرو از قبل، می‌توانی همین حالا نوبت دستی ثبت کنی.
                </p>

                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="nd-alert-action"
                >
                    ثبت نوبت دستی
                    <span>←</span>
                </a>
            @endif
        </div>

        <div class="nd-dashboard-alert-meta">
            <div>
                <span>امروز</span>
                <strong data-dashboard-metric="todayBookings">
                    {{ $fa($todayBookings) }}
                </strong>
                <small>نوبت فعال</small>
            </div>

            <div>
                <span>درآمد امروز</span>
                <strong data-dashboard-money="todayRevenue">
                    {{ $money($todayRevenue) }}
                </strong>
                <small>تأیید + انجام‌شده</small>
            </div>

            <div>
                <span>اعلان</span>
                <strong data-dashboard-metric="unreadNotifications">
                    {{ $unreadNotifications > 0 ? $fa($unreadNotifications) : '۰' }}
                </strong>
                <small>خوانده‌نشده</small>
            </div>
        </div>
    </section>


    <section class="nd-kpi-grid">

        <a
            href="{{ route('salon.bookings.index') }}"
            class="nd-kpi nd-kpi-featured"
        >
            <span class="nd-kpi-icon">◷</span>
            <span class="nd-kpi-label">نوبت‌های امروز</span>
            <strong data-dashboard-metric="todayBookings">
                {{ $fa($todayBookings) }}
            </strong>
            <small>
                {{ $pendingBookings > 0
                    ? $fa($pendingBookings) . ' در انتظار رسیدگی'
                    : 'مورد منتظر ندارید' }}
            </small>
        </a>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">✓</span>
            <span class="nd-kpi-label">تأیید شده امروز</span>
            <strong data-dashboard-metric="confirmedToday">
                {{ $fa($confirmedToday) }}
            </strong>
            <small>نوبت‌های تأیید شده</small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">◆</span>
            <span class="nd-kpi-label">انجام شده امروز</span>
            <strong data-dashboard-metric="completedToday">
                {{ $fa($completedToday) }}
            </strong>
            <small>نوبت‌های تکمیل‌شده</small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">⌁</span>
            <span class="nd-kpi-label">لغو شده امروز</span>
            <strong data-dashboard-metric="cancelledToday">
                {{ $fa($cancelledToday) }}
            </strong>
            <small>لغوهای ثبت‌شده</small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">₮</span>
            <span class="nd-kpi-label">درآمد این هفته</span>
            <strong data-dashboard-money="weekRevenue">
                {{ $money($weekRevenue) }}
            </strong>
            <small>از شنبه تا امروز</small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">▣</span>
            <span class="nd-kpi-label">درآمد این ماه</span>
            <strong data-dashboard-money="monthRevenue">
                {{ $money($monthRevenue) }}
            </strong>
            <small data-dashboard-metric="monthBookings">
                {{ $fa($monthBookings) }} نوبت این ماه
            </small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">♙</span>
            <span class="nd-kpi-label">تیم فعال</span>
            <strong data-dashboard-metric="activeBarbers">
                {{ $fa($activeBarbers) }}
            </strong>
            <small>متخصص فعال</small>
        </div>

        <div class="nd-kpi">
            <span class="nd-kpi-icon">✦</span>
            <span class="nd-kpi-label">خدمات فعال</span>
            <strong data-dashboard-metric="activeServices">
                {{ $fa($activeServices) }}
            </strong>
            <small>خدمت قابل رزرو</small>
        </div>

    </section>


    <section class="nd-revenue">

        <header class="nd-section-head">
            <div>
                <span class="nd-section-kicker">تحلیل مالی</span>
                <h2>درآمد سالن</h2>
                <p>
                    فقط نوبت‌های تأییدشده و انجام‌شده در محاسبه درآمد قرار می‌گیرند.
                </p>
            </div>

            <div class="nd-chart-controls" role="tablist" aria-label="بازه نمودار">
                <button
                    type="button"
                    class="is-active"
                    data-chart-tab="weekly"
                    role="tab"
                    aria-selected="true"
                >
                    هفته
                </button>

                <button
                    type="button"
                    data-chart-tab="monthly"
                    role="tab"
                    aria-selected="false"
                >
                    ماه
                </button>
            </div>
        </header>

        <div class="nd-chart-panel is-active" data-chart-panel="weekly">
            <div class="nd-chart-summary">
                <div>
                    <span>جمع هفته جاری</span>
                    <strong>{{ $money($weekRevenue) }}</strong>
                </div>

                <span>۷ روز</span>
            </div>

            <div class="nd-bars nd-bars-weekly">
                @foreach($weeklyRevenueChart as $index => $point)
                    @php
                        $height = $weeklyRevenueMax > 0
                            ? max(
                                4,
                                round(
                                    ($point['value'] / $weeklyRevenueMax) * 100
                                )
                            )
                            : 4;
                    @endphp

                    <div class="nd-bar-column">
                        <span class="nd-bar-value">
                            {{ $point['value'] > 0
                                ? $money($point['value'])
                                : '—' }}
                        </span>

                        <div class="nd-bar-track">
                            <div
                                class="nd-bar-fill"
                                data-bar-value="{{ $point['value'] }}"
                                style="height: {{ $height }}%;"
                            ></div>
                        </div>

                        <span class="nd-bar-label">
                            {{ $days[$index] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>


        <div class="nd-chart-panel" data-chart-panel="monthly" hidden>
            <div class="nd-chart-summary">
                <div>
                    <span>۶ ماه اخیر</span>
                    <strong>{{ $money($monthlyTotal) }}</strong>
                </div>

                <span>ماهانه</span>
            </div>

            <div class="nd-bars nd-bars-monthly">
                @foreach($monthlyRevenueChart as $point)
                    @php
                        $height = $monthlyRevenueMax > 0
                            ? max(
                                4,
                                round(
                                    ($point['value'] / $monthlyRevenueMax) * 100
                                )
                            )
                            : 4;

                        $monthLabel = substr(
                            jalali_date(
                                \Carbon\Carbon::parse($point['date'])
                            ),
                            0,
                            7
                        );
                    @endphp

                    <div class="nd-bar-column">
                        <span class="nd-bar-value">
                            {{ $point['value'] > 0
                                ? $money($point['value'])
                                : '—' }}
                        </span>

                        <div class="nd-bar-track">
                            <div
                                class="nd-bar-fill"
                                data-bar-value="{{ $point['value'] }}"
                                style="height: {{ $height }}%;"
                            ></div>
                        </div>

                        <span
                            class="nd-bar-label"
                            title="{{ $monthLabel }}"
                        >
                            {{ $monthLabel }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="nd-chart-note">
            آخرین بروزرسانی:
            <strong data-dashboard-updated>همین حالا</strong>
            <span class="nd-api-state" data-dashboard-api-state>متصل</span>
        </div>
    </section>


    <section class="nd-main-grid">

        <section class="nd-panel">
            <header class="nd-section-head nd-panel-head">
                <div>
                    <span class="nd-section-kicker">برنامه</span>
                    <h2>نوبت‌های پیش‌رو</h2>
                </div>

                <a href="{{ route('salon.bookings.index') }}">
                    همه نوبت‌ها
                    <span>←</span>
                </a>
            </header>

            <div
                class="nd-booking-list"
                data-dashboard-upcoming
            >
                @forelse($upcomingBookings as $booking)
                    @php([$label, $tone] = $status($booking->status))

                    <a
                        class="nd-booking-row"
                        href="{{ route('salon.bookings.show', $booking) }}"
                    >
                        <div class="nd-booking-time">
                            <strong>
                                {{ substr((string) $booking->start_time, 0, 5) }}
                            </strong>

                            <span>
                                {{ jalali_date($booking->booking_date) }}
                            </span>
                        </div>

                        <div class="nd-booking-main">
                            <strong>
                                {{ $booking->customer?->name ?? 'مشتری' }}
                            </strong>

                            <span>
                                {{ $booking->service?->name ?? 'خدمت' }}
                                ·
                                {{ $booking->barber?->name ?? 'متخصص' }}
                            </span>
                        </div>

                        <span class="nd-status nd-status--{{ $tone }}">
                            {{ $label }}
                        </span>
                    </a>
                @empty
                    <div class="nd-empty">
                        <strong>هنوز نوبت پیش‌رویی ندارید.</strong>
                        <span>
                            برای رزرو حضوری از «نوبت دستی» استفاده کنید.
                        </span>
                    </div>
                @endforelse
            </div>
        </section>


        <section class="nd-panel">
            <header class="nd-section-head nd-panel-head">
                <div>
                    <span class="nd-section-kicker">دسترسی سریع</span>
                    <h2>کارهای پرتکرار</h2>
                </div>
            </header>

            <div class="nd-quick-grid">

                <a
                    href="{{ route('salon.bookings.create') }}"
                    class="nd-quick nd-quick-primary"
                >
                    <span>＋</span>
                    <div>
                        <strong>نوبت دستی</strong>
                        <small>برای مشتری حضوری</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.bookings.index', ['status' => 'pending']) }}"
                    class="nd-quick"
                >
                    <span>◷</span>
                    <div>
                        <strong>درخواست‌های منتظر</strong>
                        <small>
                            {{ $fa($pendingBookings) }} مورد
                        </small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.working-hours.edit') }}"
                    class="nd-quick"
                >
                    <span>◴</span>
                    <div>
                        <strong>ساعات کاری</strong>
                        <small>{{ $todaySchedule }}</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.settings.edit') }}"
                    class="nd-quick"
                >
                    <span>⚙</span>
                    <div>
                        <strong>تنظیمات سالن</strong>
                        <small>اطلاعات و ظاهر سالن</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="nd-quick"
                >
                    <span>♙</span>
                    <div>
                        <strong>تیم</strong>
                        <small>{{ $fa($activeBarbers) }} متخصص فعال</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.services.index') }}"
                    class="nd-quick"
                >
                    <span>✦</span>
                    <div>
                        <strong>خدمات</strong>
                        <small>{{ $fa($activeServices) }} خدمت فعال</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.posts.index') }}"
                    class="nd-quick"
                >
                    <span>▤</span>
                    <div>
                        <strong>محتوا</strong>
                        <small>معرفی و پست‌های سالن</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.reviews.index') }}"
                    class="nd-quick"
                >
                    <span>♡</span>
                    <div>
                        <strong>نظرات</strong>
                        <small>بازخورد مشتریان</small>
                    </div>
                    <i>←</i>
                </a>

                <a
                    href="{{ route('salon.notifications.index') }}"
                    class="nd-quick"
                >
                    <span>◌</span>
                    <div>
                        <strong>اعلان‌ها</strong>
                        <small>
                            {{ $unreadNotifications > 0
                                ? $fa($unreadNotifications) . ' جدید'
                                : 'همه خوانده شده' }}
                        </small>
                    </div>
                    <i>←</i>
                </a>

            </div>
        </section>

    </section>


    <section class="nd-dashboard-footer-grid">

        <div class="nd-health-card">
            <div>
                <span class="nd-section-kicker">آمادگی سالن</span>
                <h2>وضعیت امروز</h2>
            </div>

            <div class="nd-health-list">
                <div class="{{ $hasWorkingHours ? 'is-ready' : '' }}">
                    <span class="nd-health-icon">
                        {{ $hasWorkingHours ? '✓' : '!' }}
                    </span>
                    <div>
                        <strong>ساعات کاری</strong>
                        <small>
                            {{ $hasWorkingHours
                                ? 'برنامه هفتگی فعال است'
                                : 'نیاز به تنظیم دارد' }}
                        </small>
                    </div>
                </div>

                <div class="{{ $activeBarbers > 0 ? 'is-ready' : '' }}">
                    <span class="nd-health-icon">
                        {{ $activeBarbers > 0 ? '✓' : '!' }}
                    </span>
                    <div>
                        <strong>تیم</strong>
                        <small>
                            {{ $activeBarbers > 0
                                ? $fa($activeBarbers) . ' متخصص فعال'
                                : 'متخصص فعالی ثبت نشده' }}
                        </small>
                    </div>
                </div>

                <div class="{{ $activeServices > 0 ? 'is-ready' : '' }}">
                    <span class="nd-health-icon">
                        {{ $activeServices > 0 ? '✓' : '!' }}
                    </span>
                    <div>
                        <strong>خدمات</strong>
                        <small>
                            {{ $activeServices > 0
                                ? $fa($activeServices) . ' خدمت فعال'
                                : 'خدمت فعالی ثبت نشده' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <a
            href="{{ route('salon.notifications.index') }}"
            class="nd-notification-card"
        >
            <span class="nd-section-kicker">صندوق ورودی</span>

            <strong>
                {{ $unreadNotifications > 0
                    ? $fa($unreadNotifications) . ' اعلان جدید دارید'
                    : 'اعلان جدیدی ندارید' }}
            </strong>

            <span>
                مشاهده اعلان‌ها
                <b>←</b>
            </span>
        </a>

    </section>

</div>
@endsection

@push('scripts')
    @vite('resources/js/salon-dashboard.js')
@endpush
