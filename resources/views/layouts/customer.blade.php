<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    data-theme="light"
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


    @vite([
    'resources/css/app.css',
    'resources/css/customer.css',
    'resources/js/app.js',
    'resources/js/customer.js',
    ])


    @stack('head')

</head>


<body>

<div class="customer-app">

    <header class="customer-header">

        <div class="customer-container">

            <div class="customer-header-inner">

                <a
                    href="{{ route('home') }}"
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


                <nav
                    class="customer-nav"
                    aria-label="ناوبری اصلی"
                >

                    <a
                        href="{{ route('home') }}"
                        @class([
                            'customer-nav-link',
                            'is-active' => request()->routeIs('home'),
                        ])
                    >
                        خانه
                    </a>


                    <a
                        href="{{ route('salons.discover') }}"
                        @class([
                            'customer-nav-link',
                            'is-active' => request()->routeIs('salons.discover'),
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


                <div class="customer-header-actions">

                    <x-theme-toggle />


                    @guest

                        <a
                            href="{{ route('login') }}"
                            class="customer-btn customer-btn-ghost customer-btn-sm"
                        >
                            ورود
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="customer-btn customer-btn-primary customer-btn-sm"
                        >
                            ثبت‌نام
                        </a>

                    @else

                        @if(auth()->user()->isCustomer())

                            <a
                                href="{{ route('customer.profile.edit') }}"
                                class="customer-header-user"
                            >

                                <span class="customer-header-user-avatar">
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
                                class="customer-btn customer-btn-ghost customer-btn-sm"
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

        @if(session('success'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="customer-flash customer-flash-success"
                    role="status"
                >
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>

            </div>

        @endif


        @if(session('error'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="customer-flash customer-flash-danger"
                    role="alert"
                >
                    <span>!</span>
                    <span>{{ session('error') }}</span>
                </div>

            </div>

        @endif


        @if(session('status'))

            <div class="customer-container customer-flash-wrap">

                <div
                    class="customer-flash customer-flash-info"
                    role="status"
                >
                    <span>i</span>
                    <span>{{ session('status') }}</span>
                </div>

            </div>

        @endif


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
