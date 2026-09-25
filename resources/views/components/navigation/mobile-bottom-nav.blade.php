<nav class="mobile-bottom-nav" aria-label="ناوبری موبایل">
    <div class="mobile-bottom-nav-inner">
        @auth
            @if(auth()->user()->isSalonOwner())
                <a href="{{ route('salon.dashboard') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salon.dashboard')]) aria-label="داشبورد">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 13h6V4H4zM14 20h6v-7h-6zM14 10h6V4h-6zM4 20h6v-3H4z"/></svg>
                    </span>
                    <span class="mobile-bottom-label">داشبورد</span>
                </a>

                <a href="{{ route('salon.bookings.index') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salon.bookings.*')]) aria-label="نوبت‌ها">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                    </span>
                    <span class="mobile-bottom-label">نوبت‌ها</span>
                </a>

                <a href="{{ route('salon.bookings.create') }}" class="mobile-bottom-book" aria-label="ثبت نوبت دستی">
                    <span class="mobile-bottom-book-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                </a>

                <a href="{{ route('salon.notifications.index') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salon.notifications.*')]) aria-label="اعلان‌ها">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                    </span>
                    <span class="mobile-bottom-label">اعلان‌ها</span>
                </a>

                <a href="{{ route('salon.settings.edit') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salon.settings.*')]) aria-label="تنظیمات">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.7 1.7-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.1h-2.4v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1-1.7-1.7.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H6.6v-2.4h.2a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L8 7.6 9.7 5.9l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1 1.6v.1h2.4v-.1a1.7 1.7 0 0 0 1-1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1 1.7 1.7-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0-1.6 1h-.1V13h.1a1.7 1.7 0 0 0 1.6 1Z"/></svg>
                    </span>
                    <span class="mobile-bottom-label">تنظیمات</span>
                </a>
            @elseif(auth()->user()->isCustomer())
                <a href="{{ route('customer.dashboard') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('customer.dashboard')]) aria-label="خانه">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V21h13V9.5"/><path d="M9.5 21v-6h5v6"/></svg>
                    </span>
                    <span class="mobile-bottom-label">خانه</span>
                </a>
                <a href="{{ route('salons.discover') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salons.discover')]) aria-label="کشف">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                    </span>
                    <span class="mobile-bottom-label">کشف</span>
                </a>
                <a href="{{ route('salons.discover') }}" class="mobile-bottom-book" aria-label="رزرو نوبت">
                    <span class="mobile-bottom-book-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                </a>
                <a href="{{ route('customer.bookings.index') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('customer.bookings.*')]) aria-label="نوبت‌های من">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                    </span>
                    <span class="mobile-bottom-label">نوبت‌ها</span>
                </a>
                <a href="{{ route('customer.profile.edit') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('customer.profile.*')]) aria-label="حساب">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6"/></svg>
                    </span>
                    <span class="mobile-bottom-label">حساب</span>
                </a>
            @else
                <a href="{{ route('brand.intro') }}" class="mobile-bottom-item" aria-label="خانه">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V21h13V9.5"/></svg>
                    </span>
                    <span class="mobile-bottom-label">خانه</span>
                </a>
                <a href="{{ route('salons.discover') }}" class="mobile-bottom-item" aria-label="کشف">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                    </span>
                    <span class="mobile-bottom-label">کشف</span>
                </a>
                <a href="{{ route('login') }}" class="mobile-bottom-book" aria-label="ورود">
                    <span class="mobile-bottom-book-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                </a>
                <a href="{{ route('login') }}" class="mobile-bottom-item" aria-label="ورود">
                    <span class="mobile-bottom-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6"/></svg>
                    </span>
                    <span class="mobile-bottom-label">ورود</span>
                </a>
            @endif
        @else
            <a href="{{ route('brand.intro') }}" class="mobile-bottom-item" aria-label="خانه">
                <span class="mobile-bottom-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V21h13V9.5"/></svg>
                </span>
                <span class="mobile-bottom-label">خانه</span>
            </a>
            <a href="{{ route('salons.discover') }}" @class(['mobile-bottom-item','is-active'=>request()->routeIs('salons.discover')]) aria-label="کشف">
                <span class="mobile-bottom-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                </span>
                <span class="mobile-bottom-label">کشف</span>
            </a>
            <a href="{{ route('salons.discover') }}" class="mobile-bottom-book" aria-label="رزرو نوبت">
                <span class="mobile-bottom-book-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </span>
            </a>
            <a href="{{ route('login') }}" class="mobile-bottom-item" aria-label="ورود">
                <span class="mobile-bottom-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6"/></svg>
                </span>
                <span class="mobile-bottom-label">ورود</span>
            </a>
        @endauth
    </div>
</nav>
