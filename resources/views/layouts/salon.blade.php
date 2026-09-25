<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') | {{ $salon?->name ?? 'پنل سالن' }}
        @else
            {{ $salon?->name ?? 'پنل سالن' }}
        @endif
    </title>

    <meta name="description" content="@yield('meta_description', 'پنل مدیریت سالن NOBAT')">
    <meta name="theme-color" content="#4f46e5">

    <script>
        (() => {
            const key = 'nobatdehi_theme';
            let saved = null;
            try { saved = localStorage.getItem(key); } catch {}
            document.documentElement.dataset.theme = saved === 'dark' ? 'dark' : 'light';
        })();
    </script>

    <style>
        :root {
            --owner-primary: var(--app-primary, #4f46e5);
            --owner-secondary: var(--app-brand-to, #06b6d4);
        }
    </style>

    @vite([
        'resources/css/app.css',
        'resources/css/salon-owner.css',
        'resources/js/app.js',
    ])

    @stack('head')
</head>

<body class="salon-owner">
@php($unreadSalonNotifications = auth()->user()->unreadNotifications()->count())
<div class="salon-owner__shell">

    <aside class="salon-owner__sidebar">
        <div class="salon-owner__brand">
            <a href="{{ route('salon.dashboard') }}" class="salon-owner__brand-link">
                <span class="salon-owner__brand-mark">
                    @if($salon?->logo_path)
                        <img src="{{ $salon->logo_url }}" alt="{{ $salon->name }}">
                    @else
                        {{ mb_substr($salon?->name ?? 'ن', 0, 1) }}
                    @endif
                </span>
                <span class="salon-owner__brand-copy">
                    <strong>{{ $salon?->name ?? 'NOBAT' }}</strong>
                    <small>مدیریت سالن</small>
                </span>
            </a>
        </div>

        <div class="salon-owner__status">
            <span class="salon-owner__status-dot {{ $salon?->is_active ? 'is-on' : 'is-off' }}"></span>
            <div>
                <strong>{{ $salon?->is_active ? 'سالن فعال است' : 'سالن غیرفعال است' }}</strong>
                <span>{{ $salon?->city ?: 'تنظیمات سالن را کامل کنید' }}</span>
            </div>
            <a href="{{ route('public.salons.show', $salon) }}" target="_blank" rel="noopener" aria-label="مشاهده صفحه عمومی">↗</a>
        </div>

        <nav class="salon-owner__nav" aria-label="ناوبری مدیریت سالن">
            <div class="salon-owner__nav-label">مدیریت</div>

            <a href="{{ route('salon.dashboard') }}" class="{{ request()->routeIs('salon.dashboard') ? 'is-active' : '' }}">
                <span>⌂</span><strong>خانه</strong>
            </a>

            <a href="{{ route('salon.bookings.index') }}" class="{{ request()->routeIs('salon.bookings.*') ? 'is-active' : '' }}">
                <span>◷</span><strong>نوبت‌ها</strong>
            </a>

            <a href="{{ route('salon.barbers.index') }}" class="{{ request()->routeIs('salon.barbers.*') ? 'is-active' : '' }}">
                <span>♙</span><strong>تیم</strong>
            </a>

            <a href="{{ route('salon.services.index') }}" class="{{ request()->routeIs('salon.services.*') ? 'is-active' : '' }}">
                <span>✦</span><strong>خدمات</strong>
            </a>

            <div class="salon-owner__nav-label">رشد</div>

            <a href="{{ route('salon.posts.index') }}" class="{{ request()->routeIs('salon.posts.*') ? 'is-active' : '' }}">
                <span>▤</span><strong>محتوا</strong>
            </a>

            <a href="{{ route('salon.reviews.index') }}" class="{{ request()->routeIs('salon.reviews.*') ? 'is-active' : '' }}">
                <span>♡</span><strong>نظرات</strong>
            </a>

            <a href="{{ route('salon.working-hours.edit') }}" class="{{ request()->routeIs('salon.working-hours.*') ? 'is-active' : '' }}">
                <span>◴</span><strong>ساعات کاری</strong>
            </a>

            <a href="{{ route('salon.settings.edit') }}" class="{{ request()->routeIs('salon.settings.*') ? 'is-active' : '' }}">
                <span>⚙</span><strong>تنظیمات</strong>
            </a>
        </nav>

        <div class="salon-owner__sidebar-footer">
            <a href="{{ route('salons.discover') }}" target="_blank" rel="noopener">مشاهده سایت ↗</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">خروج</button>
            </form>
        </div>
    </aside>

    <div class="salon-owner__content">
        <header class="salon-owner__topbar">
            <div class="salon-owner__topbar-title">
                <span>پنل سالن</span>
                <strong>{{ $salon?->name }}</strong>
            </div>

            <div class="salon-owner__topbar-actions">
                <a href="{{ route('salon.notifications.index') }}" class="salon-owner__icon-button salon-owner__notification-link" aria-label="اعلان‌ها">
                    ◌
                    @if($unreadSalonNotifications > 0)
                        <span class="salon-owner__notification-badge">{{ $unreadSalonNotifications > 99 ? '۹۹+' : $unreadSalonNotifications }}</span>
                    @endif
                </a>
                <x-theme-toggle />
            </div>
        </header>

        <main class="salon-owner__main">
            <div class="salon-owner__page-tools">
                <a
                    href="{{ route('salon.dashboard') }}"
                    data-owner-back
                    class="salon-owner__back-link"
                    aria-label="بازگشت به صفحه قبل"
                >
                    <span aria-hidden="true">←</span>
                    <span>بازگشت</span>
                </a>

                @unless(request()->routeIs('salon.dashboard'))
                    <a
                        href="{{ route('salon.dashboard') }}"
                        class="salon-owner__home-link"
                    >
                        داشبورد
                    </a>
                @endunless
            </div>

            @if(session('success'))
                <div class="salon-owner__flash is-success" role="status" aria-live="polite">
                    <span aria-hidden="true">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="salon-owner__flash is-info" role="status" aria-live="polite">
                    <span aria-hidden="true">i</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="salon-owner__flash is-error" role="alert" aria-live="assertive">
                    <span aria-hidden="true">!</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="salon-owner__flash is-error" role="alert" aria-live="polite">
                    <span aria-hidden="true">!</span>
                    <div class="salon-owner__flash-copy">
                        <strong>اطلاعات نیاز به بررسی دارد</strong>
                        <div>
                            {{ $errors->count() > 1 ? $errors->first() . ' (و ' . ($errors->count() - 1) . ' مورد دیگر)' : $errors->first() }}
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<x-salon.mobile-bottom-nav />

<script>
    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-owner-back]');

        if (!link || event.defaultPrevented) {
            return;
        }

        if (window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    });
</script>

@stack('scripts')
</body>
</html>
