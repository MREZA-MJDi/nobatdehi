@php
    $isBookings = request()->routeIs('salon.bookings.*');
    $isNotifications = request()->routeIs('salon.notifications.*');
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
            <span class="salon-mobile-nav__icon">
                ◷
                @if(($pendingBookings ?? 0) > 0)
                    <b>{{ min($pendingBookings, 9) }}</b>
                @endif
            </span>
            <span>نوبت‌ها</span>
        </a>

        <a href="{{ route('salon.bookings.create') }}" class="salon-mobile-nav__primary" aria-label="ثبت نوبت دستی">
            <span>＋</span>
        </a>

        <a href="{{ route('salon.notifications.index') }}" class="salon-mobile-nav__item {{ $isNotifications ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">
                ◌
                @if(($unreadNotifications ?? 0) > 0)
                    <b>{{ min($unreadNotifications, 9) }}</b>
                @endif
            </span>
            <span>اعلان‌ها</span>
        </a>

        <a href="{{ route('salon.settings.edit') }}" class="salon-mobile-nav__item {{ $isSettings ? 'is-active' : '' }}">
            <span class="salon-mobile-nav__icon">⚙</span>
            <span>تنظیمات</span>
        </a>
    </div>
</nav>
