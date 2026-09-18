@php
    $isBookings = request()->routeIs('salon.bookings.*');
    $isWorkingHours = request()->routeIs('salon.working-hours.*');
    $isSettings = request()->routeIs('salon.settings.*');
    $isDashboard = request()->routeIs('salon.dashboard');
@endphp

<nav class="salon-mobile-nav" aria-label="ناوبری اصلی سالن">
    <div class="salon-mobile-nav__inner">
        <a href="{{ route('salon.dashboard') }}" class="salon-mobile-nav__item {{ $isDashboard ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">⌂</span>
            <span>خانه</span>
        </a>

        <a href="{{ route('salon.bookings.index') }}" class="salon-mobile-nav__item {{ $isBookings ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">◷</span>
            <span>نوبت‌ها</span>
        </a>

        <a href="{{ route('salon.bookings.create') }}" class="salon-mobile-nav__primary" aria-label="ثبت نوبت دستی">
            <span>＋</span>
        </a>

        <a href="{{ route('salon.working-hours.edit') }}" class="salon-mobile-nav__item {{ $isWorkingHours ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">◴</span>
            <span>ساعات</span>
        </a>

        <a href="{{ route('salon.settings.edit') }}" class="salon-mobile-nav__item {{ $isSettings ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">⚙</span>
            <span>تنظیمات</span>
        </a>
    </div>
</nav>
