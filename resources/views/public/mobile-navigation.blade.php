<nav class="discover-mobile-nav" aria-label="ناوبری سریع">
    <a
        href="{{ route('salons.discover') }}#hero"
        class="discover-mobile-item is-active"
    >
        <span class="discover-mobile-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="m16 16 4.5 4.5"/>
            </svg>
        </span>
        <span>کشف</span>
    </a>

    <a
        href="{{ route('salons.discover', ['type' => 'salon']) }}#results"
        class="discover-mobile-item"
    >
        <span class="discover-mobile-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M4 20h16"/>
                <path d="M6 20V9h12v11"/>
                <path d="M8 9V5h8v4"/>
                <path d="M9 13h1M14 13h1M9 16h1M14 16h1"/>
            </svg>
        </span>
        <span>سالن‌ها</span>
    </a>

    <a
        href="{{ route('salons.discover') }}#results"
        class="discover-mobile-main-action"
        aria-label="شروع رزرو نوبت"
    >
        <span class="discover-mobile-main-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>
        </span>
        <span>رزرو</span>
    </a>

    <a
        href="{{ route('salons.discover', ['sort' => 'distance']) }}#results"
        class="discover-mobile-item"
    >
        <span class="discover-mobile-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="8"/>
                <circle cx="12" cy="12" r="2.5"/>
                <path d="M12 4v2M12 18v2M4 12h2M18 12h2"/>
            </svg>
        </span>
        <span>نزدیک من</span>
    </a>

    @auth
        @if(auth()->user()->isSalonOwner())
            <a href="{{ route('salon.dashboard') }}" class="discover-mobile-item">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 20h16"/>
                        <path d="M6 20V9h12v11"/>
                        <path d="M8 9V5h8v4"/>
                        <path d="M9 13h1M14 13h1M9 16h1M14 16h1"/>
                    </svg>
                </span>
                <span>پنل سالن</span>
            </a>
        @elseif(auth()->user()->isCustomer())
            <a href="{{ route('customer.dashboard') }}" class="discover-mobile-item">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 4v16"/>
                        <path d="M6 5h11l-3 4 3 4H6"/>
                    </svg>
                </span>
                <span>نوبت‌ها</span>
            </a>
        @elseif(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.dashboard') }}" class="discover-mobile-item">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="3"/>
                        <path d="M8 8h8M8 12h5M8 16h8"/>
                    </svg>
                </span>
                <span>مدیریت</span>
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" class="discover-mobile-item">
            <span class="discover-mobile-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="3.5"/>
                    <path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6"/>
                </svg>
            </span>
            <span>ورود</span>
        </a>
    @endauth
</nav>
