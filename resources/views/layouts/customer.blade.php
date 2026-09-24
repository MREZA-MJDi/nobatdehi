<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    data-theme="light"
    class="antialiased"
>
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @hasSection('title')
            @yield('title') | RM نوبت‌دهی
        @else
            RM نوبت‌دهی
        @endif
    </title>

    <meta
        name="description"
        content="@yield(
            'meta_description',
            'پیدا کردن سالن، آرایشگر و رزرو آنلاین نوبت'
        )"
    >

    <meta
        name="robots"
        content="@yield(
            'robots',
            'index,follow'
        )"
    >

    <link
        rel="canonical"
        href="@yield(
            'canonical',
            url()->current()
        )"
    >

    <meta
        name="theme-color"
        content="#6757E8"
    >


    {{-- =========================================================
        THEME BOOTSTRAP
        Prevents theme flash before app.js initializes.
    ========================================================== --}}

    <script>
        (() => {
            const key = 'nobatdehi_theme';

            let saved = null;

            try {
                saved = localStorage.getItem(key);
            } catch (error) {
                saved = null;
            }

            document.documentElement.dataset.theme =
                saved === 'dark'
                    ? 'dark'
                    : 'light';
        })();
    </script>


    {{-- =========================================================
        CORE VITE
    ========================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/css/customer.css',
    'resources/js/customer.js',
    ])


    {{-- =========================================================
        PAGE-SPECIFIC HEAD
    ========================================================== --}}

    @stack('head')

</head>


<body>

@php
    $customerShell = trim($__env->yieldContent('customer_shell', 'default'));

    if (! in_array($customerShell, ['default', 'standalone', 'auth'], true)) {
        $customerShell = 'default';
    }
@endphp

@if ($customerShell === 'auth')

    <main class="relative min-h-screen overflow-hidden">

        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-accent-500/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-32 h-[28rem] w-[28rem] rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-accent-400/5 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md">

                <div class="mb-7 text-center">
                    <a href="{{ route('brand.intro') }}" class="group inline-flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-950 text-base font-black text-white shadow-lg transition duration-200 group-hover:-translate-y-1 group-hover:shadow-xl">ن</span>
                        <span class="text-right">
                            <span class="block text-base font-black text-content">نوبت‌دهی</span>
                            <span class="mt-0.5 block text-[10px] font-medium text-content-muted">رزرو آسان، تجربه بهتر</span>
                        </span>
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-success-100 bg-success-50 px-4 py-3 shadow-soft" role="status">
                        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-success-100 text-success-700" aria-hidden="true">✓</div>
                        <div class="text-xs font-bold leading-6 text-success-700">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('status'))
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-accent-100 bg-accent-50 px-4 py-3 shadow-soft" role="status">
                        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700" aria-hidden="true">!</div>
                        <div class="text-xs font-bold leading-6 text-accent-700">{{ session('status') }}</div>
                    </div>
                @endif

                <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 shadow-float backdrop-blur-xl">
                    @yield('content')
                </div>

                <div class="mt-6 text-center">
                    <div class="text-[10px] font-medium text-content-faint">© {{ now()->year }} نوبت‌دهی</div>
                    <div class="mt-1 text-[9px] text-content-faint">رزرو سریع و ساده خدمات موردنظر شما</div>
                </div>

            </div>
        </div>

    </main>

@elseif ($customerShell === 'standalone')

    <main>
        @yield('content')
    </main>

@else

    <div class="customer-app">

    <header class="customer-header">

        <div class="customer-container">

            <div class="customer-header-inner">

                {{-- Brand --}}
                <a
                    href="{{ route('brand.intro') }}"
                    class="customer-brand"
                    aria-label="RM نوبت‌دهی"
                >

                    <span class="customer-brand-mark">
                        RM
                    </span>

                    <span class="customer-brand-copy">

                        <span class="customer-brand-name">
                            نوبت‌دهی
                        </span>

                        <span class="customer-brand-caption">
                            پیدا کن. انتخاب کن. نوبت بگیر.
                        </span>

                    </span>

                </a>


                {{-- =================================================
                    DESKTOP NAV
                ================================================== --}}

                <nav
                    class="customer-nav"
                    aria-label="ناوبری اصلی"
                >

                    <a
                        href="{{ route('brand.intro') }}"
                        @class([
                            'customer-nav-link',
                            'is-active' => request()->routeIs('brand.intro'),
                        ])
                    >
                        خانه
                    </a>


                    <a
                        href="{{ route('salons.discover') }}"
                        @class([
                            'customer-nav-link',
                            'is-active' =>
                                request()->routeIs('salons.discover')
                                && !request()->has('type'),
                        ])
                    >
                        کشف
                    </a>


                    <a
                        href="{{ route('salons.discover', ['type' => 'salon']) }}"
                        @class([
                            'customer-nav-link',
                            'is-active' =>
                                request()->query('type') === 'salon',
                        ])
                    >
                        سالن‌ها
                    </a>


                    <a
                        href="{{ route('salons.discover', ['type' => 'barber']) }}"
                        @class([
                            'customer-nav-link',
                            'is-active' =>
                                request()->query('type') === 'barber',
                        ])
                    >
                        آرایشگرها
                    </a>


                    @auth

                        @if(auth()->user()->isCustomer())

                            <a
                                href="{{ route('customer.dashboard') }}"
                                @class([
                                    'customer-nav-link',
                                    'is-active' =>
                                        request()->routeIs('customer.*'),
                                ])
                            >
                                نوبت‌های من
                            </a>

                        @endif

                    @endauth

                </nav>


                {{-- =================================================
                    HEADER ACTIONS
                ================================================== --}}

                <div class="customer-header-actions">

                    <x-theme-toggle />


                    @guest

                        <a
                            href="{{ route('salon.login') }}"
                            class="customer-salon-entry"
                            aria-label="ورود به پنل سالن"
                        >
                            <span>برای سالن‌ها</span>
                            <span aria-hidden="true">→</span>
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="
                                customer-btn
                                customer-btn-ghost
                                customer-btn-sm
                            "
                        >
                            ورود
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="
                                customer-btn
                                customer-btn-primary
                                customer-btn-sm
                            "
                        >
                            ثبت‌نام
                        </a>

                    @else

                        @if(auth()->user()->isCustomer())

                            <a
                                href="{{ route('customer.profile.edit') }}"
                                class="customer-header-user"
                            >

                                <span
                                    class="customer-header-user-avatar"
                                    aria-hidden="true"
                                >
                                    {{
                                        mb_substr(
                                            auth()->user()->name ?: 'ک',
                                            0,
                                            1
                                        )
                                    }}
                                </span>

                                <span class="customer-header-user-name">
                                    {{ auth()->user()->name ?: 'حساب من' }}
                                </span>

                            </a>

                        @endif


                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="
                                    customer-btn
                                    customer-btn-ghost
                                    customer-btn-sm
                                "
                            >
                                خروج
                            </button>

                        </form>

                    @endguest

                </div>

            </div>

        </div>

    </header>

    <main class="customer-main">

        {{-- =====================================================
            FLASH: SUCCESS
        ====================================================== --}}

        @if(session('success'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="
                        customer-flash
                        customer-flash-success
                    "
                    role="status"
                >

                    <span aria-hidden="true">
                        ✓
                    </span>

                    <span>
                        {{ session('success') }}
                    </span>

                </div>

            </div>

        @endif


        {{-- =====================================================
            FLASH: ERROR
        ====================================================== --}}

        @if(session('error'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="
                        customer-flash
                        customer-flash-danger
                    "
                    role="alert"
                >

                    <span aria-hidden="true">
                        !
                    </span>

                    <span>
                        {{ session('error') }}
                    </span>

                </div>

            </div>

        @endif


        {{-- =====================================================
            FLASH: STATUS
        ====================================================== --}}

        @if(session('status'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="
                        customer-flash
                        customer-flash-info
                    "
                    role="status"
                >

                    <span aria-hidden="true">
                        i
                    </span>

                    <span>
                        {{ session('status') }}
                    </span>

                </div>

            </div>

        @endif


        {{-- =====================================================
            PAGE
        ====================================================== --}}

        <div class="customer-page">

            @yield('content')

        </div>

    </main>

    <x-customer-footer />

    <x-navigation.mobile-bottom-nav />

    </div>

@endif

@stack('scripts')

</body>
</html>
