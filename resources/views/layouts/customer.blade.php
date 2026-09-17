<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('title')
            @yield('title') | RM نوبت‌دهی
        @else
            RM نوبت‌دهی
        @endif
    </title>
    <meta name="description" content="@yield('meta_description', 'پیدا کردن سالن، آرایشگر و رزرو آنلاین نوبت')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="theme-color" content="#6757E8">

    <x-frontend-assets role="customer" />
    @stack('head')
</head>

<body>
<div class="customer-app">
    <header class="customer-header">
        <div class="customer-container">
            <div class="customer-header-inner">
                <a href="{{ route('salons.discover') }}" class="customer-brand" aria-label="NOBAT">
                    <span class="customer-brand-mark">N</span>
                    <span class="customer-brand-copy">
                        <span class="customer-brand-name">NOBAT</span>
                        <span class="customer-brand-caption">پیدا کن. انتخاب کن. نوبت بگیر.</span>
                    </span>
                </a>

                <nav class="customer-nav" aria-label="ناوبری اصلی">
                    <a href="{{ route('salons.discover') }}" @class(['customer-nav-link', 'is-active' => request()->routeIs('salons.discover') && !request()->has('type')])>کشف</a>
                    <a href="{{ route('salons.discover', ['type' => 'salon']) }}" @class(['customer-nav-link', 'is-active' => request()->query('type') === 'salon'])>سالن‌ها</a>
                    <a href="{{ route('salons.discover', ['type' => 'barber']) }}" @class(['customer-nav-link', 'is-active' => request()->query('type') === 'barber'])>متخصص‌ها</a>
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.dashboard') }}" @class(['customer-nav-link', 'is-active' => request()->routeIs('customer.*')])>نوبت‌های من</a>
                        @endif
                    @endauth
                </nav>

                <div class="customer-header-actions">
                    <x-theme-toggle />
                    @guest
                        <a href="{{ route('login') }}" class="customer-btn customer-btn-ghost customer-btn-sm">ورود</a>
                        <a href="{{ route('register') }}" class="customer-btn customer-btn-primary customer-btn-sm">ثبت‌نام</a>
                    @else
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.profile.edit') }}" class="customer-header-user">
                                <span class="customer-header-user-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name ?: 'ک', 0, 1) }}</span>
                                <span class="customer-header-user-name">{{ auth()->user()->name ?: 'حساب من' }}</span>
                            </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="customer-btn customer-btn-ghost customer-btn-sm">خروج</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    <main class="customer-main">
        @foreach (['success' => 'customer-flash-success', 'error' => 'customer-flash-danger', 'status' => 'customer-flash-info'] as $flashKey => $flashClass)
            @if(session($flashKey))
                <div class="customer-container customer-flash-wrap">
                    <div class="customer-flash {{ $flashClass }}" role="{{ $flashKey === 'error' ? 'alert' : 'status' }}">
                        <span aria-hidden="true">{{ $flashKey === 'success' ? '✓' : ($flashKey === 'error' ? '!' : 'i') }}</span>
                        <span>{{ session($flashKey) }}</span>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="customer-page">
            @yield('content')
        </div>
    </main>

    <x-customer-footer />
    <x-navigation.mobile-bottom-nav />
    @stack('scripts')
</div>
</body>
</html>
