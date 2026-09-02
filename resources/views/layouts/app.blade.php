<!DOCTYPE html>

<html lang="fa" dir="rtl">

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
            @yield('title') | نوبت‌دهی
        @else
            نوبت‌دهی
        @endif
    </title>

    <meta
        name="description"
        content="@yield(
        'meta_description',
        'پیدا کردن سالن و آرایشگر و رزرو نوبت'
    )"
    >

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])


</head>

<body>

<div id="app" class="page-shell">


    {{-- ============================================================
        DESKTOP NAVBAR
        در موبایل توسط CSS کاملاً مخفی می‌شود.
    ============================================================= --}}

    <header class="app-navbar">

        <div class="app-container navbar-inner">

            {{-- Brand --}}

            <a
                href="{{ url('/') }}"
                class="brand"
                aria-label="صفحه اصلی"
            >

            <span class="brand-mark">
                ن
            </span>

                <span class="brand-text">

                <span class="brand-name">
                    نوبت‌دهی
                </span>

                <span class="brand-caption">
                    رزرو آسان، تجربه بهتر
                </span>

            </span>

            </a>


            {{-- Desktop Navigation --}}

            <nav
                class="navbar-links"
                aria-label="منوی اصلی"
            >

                <a
                    href="{{ route('salons.discover') }}"
                    class="navbar-link {{ request()->routeIs('salons.discover') ? 'is-active' : '' }}"
                >
                    کشف
                </a>

                <a
                    href="#"
                    class="navbar-link"
                >
                    نوبت‌های من
                </a>

                <a
                    href="#"
                    class="navbar-link"
                >
                    علاقه‌مندی‌ها
                </a>

                <a
                    href="#"
                    class="navbar-link"
                >
                    درباره ما
                </a>

            </nav>


            {{-- Desktop Actions --}}

            <div class="hidden items-center gap-2 md:flex">

                @guest

                    <a
                        href="{{ route('login') }}"
                        class="btn btn-ghost btn-sm"
                    >
                        ورود
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="btn btn-primary btn-sm"
                    >
                        ثبت‌نام
                    </a>

                @else

                    @if(auth()->user()->isSuperAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="btn btn-secondary btn-sm"
                        >
                            پنل مدیریت
                        </a>

                    @elseif(auth()->user()->isBarber())

                        <a
                            href="{{ route('barber.dashboard') }}"
                            class="btn btn-secondary btn-sm"
                        >
                            پنل آرایشگر
                        </a>

                    @else

                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="btn btn-secondary btn-sm"
                        >
                            حساب من
                        </a>

                    @endif

                @endguest

            </div>

        </div>

    </header>



    {{-- ============================================================
        MAIN CONTENT
    ============================================================= --}}

    <main class="page-content">

        {{-- Success --}}

        @if(session('success'))

            <div class="app-container mb-4">

                <div
                    class="alert alert-success"
                    role="status"
                >
                    {{ session('success') }}
                </div>

            </div>

        @endif


        {{-- Error --}}

        @if(session('error'))

            <div class="app-container mb-4">

                <div
                    class="alert alert-danger"
                    role="alert"
                >
                    {{ session('error') }}
                </div>

            </div>

        @endif


        @yield('content')

    </main>



    {{-- ============================================================
        MOBILE APP BOTTOM NAVIGATION
        فقط موبایل
        بدون Navbar
        بدون Sidebar
    ============================================================= --}}

    <nav
        class="mobile-bottom-nav"
        aria-label="ناوبری اصلی"
    >

        <div class="mobile-bottom-nav-inner">


            {{-- ====================================================
                Home
            ===================================================== --}}

            <a
                href="{{ url('/') }}"
                class="bottom-nav-item {{ request()->is('/') ? 'is-active' : '' }}"
            >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5.5 9.5V21h13V9.5" />
                </svg>

            </span>

                <span class="bottom-nav-label">
                خانه
            </span>

            </a>



            {{-- ====================================================
                Discover
            ===================================================== --}}

            <a
                href="{{ route('salons.discover') }}"
                class="bottom-nav-item {{ request()->routeIs('salons.discover') ? 'is-active' : '' }}"
            >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="6.5"
                    />

                    <path d="m16 16 4.5 4.5" />
                </svg>

            </span>

                <span class="bottom-nav-label">
                کشف
            </span>

            </a>



            {{-- ====================================================
                Bookings
            ===================================================== --}}

            <a
                href="#"
                class="bottom-nav-item"
            >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >

                    <rect
                        x="4"
                        y="5"
                        width="16"
                        height="16"
                        rx="2"
                    />

                    <path d="M8 3v4" />
                    <path d="M16 3v4" />
                    <path d="M4 10h16" />

                </svg>

            </span>

                <span class="bottom-nav-label">
                نوبت
            </span>

            </a>



            {{-- ====================================================
                Favorites
            ===================================================== --}}

            <a
                href="#"
                class="bottom-nav-item"
            >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path
                        d="
                            M20.8 8.9
                            c0 5.5-8.8 10.2-8.8 10.2
                            S3.2 14.4 3.2 8.9
                            A4.7 4.7 0 0 1 8 4.2
                            c1.6 0 3.1.8 4 2
                            .9-1.2 2.4-2 4-2
                            a4.7 4.7 0 0 1 4.8 4.7Z
                        "
                    />
                </svg>

            </span>

                <span class="bottom-nav-label">
                علاقه‌مندی
            </span>

            </a>



            {{-- ====================================================
                Profile
            ===================================================== --}}

            <a
                href="#"
                class="bottom-nav-item"
            >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >

                    <circle
                        cx="12"
                        cy="8"
                        r="3.5"
                    />

                    <path
                        d="
                            M5 21
                            c.7-4
                            3.1-6
                            7-6
                            s6.3 2
                            7 6
                        "
                    />

                </svg>

            </span>

                <span class="bottom-nav-label">
                من
            </span>

            </a>

        </div>

    </nav>



    {{-- ============================================================
        TOAST SYSTEM
    ============================================================= --}}

    <div
        class="toast-stack"
        x-data
        aria-live="polite"
        aria-atomic="true"
    >

        <template
            x-for="item in $store.toast.items"
            :key="item.id"
        >

            <div
                x-cloak
                x-transition
                class="toast"
            >

                {{-- Icon --}}

                <div
                    class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                    :class="{
                    'bg-green-100 text-green-700':
                        item.type === 'success',

                    'bg-red-100 text-red-700':
                        item.type === 'danger',

                    'bg-amber-100 text-amber-700':
                        item.type === 'warning',

                    'bg-blue-100 text-blue-700':
                        item.type === 'info',
                }"
                >

                    {{-- Success --}}

                    <svg
                        x-show="item.type === 'success'"
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m5 12 4 4L19 6" />
                    </svg>


                    {{-- Danger --}}

                    <svg
                        x-show="item.type === 'danger'"
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>


                    {{-- Warning --}}

                    <svg
                        x-show="item.type === 'warning'"
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                        <path
                            d="
                            m10.3 3.7-8 14
                            A2 2 0 0 0 4 20.7
                            h16
                            a2 2 0 0 0 1.7-3
                            l-8-14
                            a2 2 0 0 0-3.4 0Z
                        "
                        />
                    </svg>


                    {{-- Info --}}

                    <svg
                        x-show="item.type === 'info'"
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

                        <path d="M12 10v6" />
                        <path d="M12 7h.01" />
                    </svg>

                </div>


                {{-- Message --}}

                <div
                    class="toast-message"
                    x-text="item.message"
                ></div>


                {{-- Close --}}

                <button
                    type="button"
                    class="text-zinc-400 transition hover:text-zinc-700"
                    @click="$store.toast.remove(item.id)"
                    aria-label="بستن"
                >
                    ×
                </button>

            </div>

        </template>

    </div>


</div>

</body>
</html>
