<header class="discover-navbar" data-discover-navbar>
    <div class="discover-navbar-inner">
        <a
            href="{{ route('salons.discover') }}"
            class="discover-brand"
            aria-label="NOBAT"
        >
            <span class="discover-brand-mark">N</span>
            <span class="discover-brand-copy">
                <strong>NOBAT</strong>
                <small>پیدا کن · انتخاب کن · نوبت بگیر</small>
            </span>
        </a>

        <nav class="discover-desktop-nav" aria-label="ناوبری اصلی">
            <a href="{{ route('salons.discover') }}#hero" class="is-active">کشف</a>
            <a href="{{ route('salons.discover', ['type' => 'salon']) }}#results">سالن‌ها</a>
            <a href="{{ route('salons.discover', ['type' => 'barber']) }}#results">متخصص‌ها</a>
            <a href="{{ route('salons.discover', ['sort' => 'distance']) }}#results">نزدیک من</a>

            @auth
                @if(auth()->user()->isSalonOwner())
                    <a href="{{ route('salon.dashboard') }}">داشبورد سالن</a>
                @elseif(auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}">نوبت‌های من</a>
                @elseif(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}">مدیریت</a>
                @endif
            @endauth
        </nav>

        <div class="discover-navbar-actions">
            @auth
                @if(auth()->user()->isSalonOwner())
                    <a href="{{ route('salon.dashboard') }}" class="discover-account-link">
                        <span class="discover-account-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name ?: 'س', 0, 1) }}</span>
                        <span>داشبورد سالن</span>
                    </a>
                @elseif(auth()->user()->isCustomer())
                    <a href="{{ route('customer.profile.edit') }}" class="discover-account-link">
                        <span class="discover-account-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name ?: 'ک', 0, 1) }}</span>
                        <span>{{ auth()->user()->name ?: 'حساب من' }}</span>
                    </a>
                @elseif(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="discover-account-link">
                        <span class="discover-account-avatar" aria-hidden="true">A</span>
                        <span>مدیریت</span>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="discover-navbar-ghost">ورود</a>
                <a href="{{ route('register') }}" class="discover-navbar-cta">شروع کن</a>
            @endauth
        </div>
    </div>
</header>
