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

                <div class="mb-4 flex items-center justify-between">
                    <a
                        href="{{ route('brand.intro') }}"
                        class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-white/80 bg-white/80 px-3 py-2 text-xs font-black text-content-muted shadow-sm transition hover:-translate-y-0.5 hover:text-content"
                    >
                        <span aria-hidden="true">→</span>
                        بازگشت به سایت
                    </a>

                    @if (($entry ?? 'customer') === 'salon')
                        <a
                            href="{{ route('salons.discover') }}"
                            class="text-[10px] font-bold text-content-faint transition hover:text-accent-600"
                        >
                            کشف سالن‌ها
                        </a>
                    @endif
                </div>

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
        <div class="customer-container py-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <a
                    href="{{ route('brand.intro') }}"
                    data-customer-back
                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-border bg-white px-3 py-2 text-xs font-black text-content shadow-sm transition hover:-translate-y-0.5 hover:border-accent-300 hover:text-accent-600"
                >
                    <span aria-hidden="true">←</span>
                    <span>بازگشت</span>
                </a>

                <a
                    href="{{ route('salons.discover') }}"
                    class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-primary-950 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-primary-900"
                >
                    کشف سالن‌ها
                </a>
            </div>
        </div>

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
                            href="{{ route('login', ['entry' => 'salon']) }}"
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

        @php
            $customerIsAuthenticated = auth()->check() && auth()->user()->isCustomer();
        @endphp

        <div class="customer-container pt-4">
            <div class="flex items-center justify-between gap-3">
                <a
                    href="{{ $customerIsAuthenticated ? route('customer.dashboard') : route('brand.intro') }}"
                    data-customer-back
                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-border bg-white/90 px-3 py-2 text-xs font-black text-content shadow-sm transition hover:-translate-y-0.5 hover:border-accent-300 hover:text-accent-600"
                >
                    <span aria-hidden="true">←</span>
                    <span>بازگشت</span>
                </a>

                <div class="flex items-center gap-2">
                    @if($customerIsAuthenticated && !request()->routeIs('customer.dashboard'))
                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-primary-950 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-primary-900"
                        >
                            داشبورد من
                        </a>
                    @endif

                    @unless(request()->routeIs('brand.intro'))
                        <a
                            href="{{ route('brand.intro') }}"
                            class="hidden min-h-10 items-center gap-2 rounded-xl border border-border bg-white/90 px-3 py-2 text-xs font-black text-content sm:inline-flex"
                        >
                            خانه سایت
                        </a>
                    @endunless
                </div>
            </div>
        </div>

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
            FLASH: VALIDATION ERRORS
        ====================================================== --}}

        @if($errors->any())

            <div class="customer-container customer-flash-wrap">

                <div
                    class="customer-flash customer-flash-danger"
                    role="alert"
                    aria-live="polite"
                >

                    <span aria-hidden="true">
                        !
                    </span>

                    <div class="min-w-0">
                        <div class="font-black">
                            بعضی اطلاعات نیاز به بررسی دارد.
                        </div>

                        @if($errors->count() > 1)
                            <div class="mt-1 space-y-0.5 text-[10px] font-medium">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-1 text-[10px] font-medium">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </div>

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

<script>
    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-customer-back]');

        if (!link || event.defaultPrevented) {
            return;
        }

        const previousIsSameOrigin =
            document.referrer !== ''
            && new URL(document.referrer, window.location.href).origin === window.location.origin;

        if (window.history.length > 1 && previousIsSameOrigin) {
            event.preventDefault();
            window.history.back();
        }
    });
</script>

@stack('scripts')

</body>
</html>
