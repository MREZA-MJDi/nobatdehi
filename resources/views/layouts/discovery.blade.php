<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>@yield('title', 'کشف سالن و خدمات | NOBAT')</title>

    <meta
        name="description"
        content="@yield('description', 'سالن‌ها، متخصص‌ها و خدمات موردنظر خودت را در NOBAT پیدا کن.')"
    >

    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="theme-color" content="#0d0f10">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    @vite([
        'resources/css/app.css',
        'resources/css/discovery.css',
        'resources/js/app.js',
        'resources/js/discover.js',
    ])

    @stack('styles')
    @stack('head')
</head>

<body class="min-h-screen antialiased">
    <div class="discover-shell">
        <header class="discover-navbar" data-discover-navbar>
            <div class="discover-navbar-inner">
                <a href="{{ route('salons.discover') }}" class="discover-brand" aria-label="NOBAT">
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
                    <a href="{{ route('salons.discover', ['sort' => 'nearest']) }}#results">نزدیک من</a>
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.dashboard') }}">نوبت‌های من</a>
                        @endif
                    @endauth
                </nav>

                <div class="discover-navbar-actions">
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.profile.edit') }}" class="discover-account-link">
                                <span class="discover-account-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name ?: 'ک', 0, 1) }}</span>
                                <span>{{ auth()->user()->name ?: 'حساب من' }}</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="discover-navbar-ghost">ورود</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="discover-navbar-ghost">ورود</a>
                        <a href="{{ route('register') }}" class="discover-navbar-cta">شروع کن</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <nav class="discover-mobile-nav" aria-label="ناوبری سریع">
            <a href="{{ route('salons.discover') }}#hero" class="discover-mobile-item is-active">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                </span>
                <span>کشف</span>
            </a>

            <a href="{{ route('salons.discover', ['type' => 'salon']) }}#results" class="discover-mobile-item">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M6 20V9h12v11"/><path d="M8 9V5h8v4"/><path d="M9 13h1"/><path d="M14 13h1"/><path d="M9 16h1"/><path d="M14 16h1"/></svg>
                </span>
                <span>سالن‌ها</span>
            </a>

            <a href="{{ route('salons.discover') }}#results" class="discover-mobile-main-action" aria-label="رزرو نوبت">
                <span class="discover-mobile-main-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </span>
                <span>رزرو</span>
            </a>

            <a href="{{ route('salons.discover', ['sort' => 'nearest']) }}#results" class="discover-mobile-item">
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="2.5"/><path d="M12 4v2M12 18v2M4 12h2M18 12h2"/></svg>
                </span>
                <span>نزدیک من</span>
            </a>

            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}" class="discover-mobile-item">
                @else
                    <a href="{{ route('login') }}" class="discover-mobile-item">
                @endif
            @else
                <a href="{{ route('login') }}" class="discover-mobile-item">
            @endauth
                <span class="discover-mobile-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6"/></svg>
                </span>
                <span>حساب</span>
            </a>
        </nav>
    </div>

    @stack('scripts')
</body>
</html>
