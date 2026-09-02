<!DOCTYPE html>

<html
    lang="fa"
    dir="rtl"
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


    {{-- ============================================================
        THEME
    ============================================================= --}}

    <script>
        (() => {
            const key = 'nobatdehi_theme';
            const saved = localStorage.getItem(key);

            document.documentElement.dataset.theme =
                saved === 'dark'
                    ? 'dark'
                    : 'light';
        })();
    </script>


    {{-- ============================================================
        ASSETS
    ============================================================= --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

</head>


<body>

<div
    id="app"
    class="page-shell"
>


    {{-- ============================================================
        HEADER
    ============================================================= --}}

    <header class="app-navbar">

        <div class="app-container navbar-inner">


            {{-- Brand --}}

            <a
                href="{{ route('home') }}"
                class="brand"
                aria-label="صفحه اصلی نوبت‌دهی"
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


            {{-- ====================================================
                DESKTOP NAVIGATION
            ===================================================== --}}

            <nav
                class="hidden items-center gap-1 md:flex"
                aria-label="ناوبری اصلی"
            >

                <a
                    href="{{ route('home') }}"
                    class="rounded-xl px-3 py-2 text-xs font-bold transition
                    {{ request()->routeIs('home')
                        ? 'bg-primary-100 text-content'
                        : 'text-content-muted hover:bg-primary-50 hover:text-content'
                    }}"
                >
                    خانه
                </a>


                <a
                    href="{{ route('salons.discover') }}"
                    class="rounded-xl px-3 py-2 text-xs font-bold transition
                    {{ request()->routeIs('salons.discover')
                        ? 'bg-primary-100 text-content'
                        : 'text-content-muted hover:bg-primary-50 hover:text-content'
                    }}"
                >
                    کشف
                </a>


                @auth

                    @if(auth()->user()->isCustomer())

                        <a
                            href="{{ route('salons.discover') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold text-content-muted transition hover:bg-primary-50 hover:text-content"
                        >
                            رزرو نوبت
                        </a>


                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('customer.*')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            حساب من
                        </a>


                    @elseif(auth()->user()->isSalonOwner())

                        <a
                            href="{{ route('salon.dashboard') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('salon.dashboard')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            پنل سالن
                        </a>


                        <a
                            href="{{ route('salon.bookings.index') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('salon.bookings.*')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            نوبت‌ها
                        </a>


                        <a
                            href="{{ route('salon.notifications.index') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('salon.notifications.*')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            اعلان‌ها
                        </a>


                    @elseif(auth()->user()->isSuperAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('admin.dashboard')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            مدیریت
                        </a>


                        <a
                            href="{{ route('admin.salons.index') }}"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                            {{ request()->routeIs('admin.salons.*')
                                ? 'bg-primary-100 text-content'
                                : 'text-content-muted hover:bg-primary-50 hover:text-content'
                            }}"
                        >
                            سالن‌ها
                        </a>

                    @endif

                @endauth

            </nav>


            {{-- ====================================================
                HEADER ACTIONS
            ===================================================== --}}

            <div class="ml-auto flex items-center gap-2">

                <x-theme-toggle />


                @guest

                    <a
                        href="{{ route('login') }}"
                        class="btn btn-ghost btn-sm hidden sm:inline-flex"
                    >
                        ورود
                    </a>


                    <a
                        href="{{ route('register') }}"
                        class="btn btn-primary btn-sm hidden sm:inline-flex"
                    >
                        ثبت‌نام
                    </a>


                    <a
                        href="{{ route('login') }}"
                        class="btn btn-primary btn-sm sm:hidden"
                    >
                        ورود
                    </a>

                @else

                    @if(auth()->user()->isSuperAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="btn btn-secondary btn-sm hidden sm:inline-flex"
                        >
                            پنل مدیریت
                        </a>


                    @elseif(auth()->user()->isSalonOwner())

                        <a
                            href="{{ route('salon.dashboard') }}"
                            class="btn btn-secondary btn-sm hidden sm:inline-flex"
                        >
                            پنل سالن
                        </a>


                    @elseif(auth()->user()->isCustomer())

                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="btn btn-secondary btn-sm hidden sm:inline-flex"
                        >
                            حساب من
                        </a>

                    @endif


                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="hidden sm:block"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="btn btn-ghost btn-sm"
                        >
                            خروج
                        </button>

                    </form>

                @endguest

            </div>

        </div>

    </header>


    {{-- ============================================================
        MAIN
    ============================================================= --}}

    <main class="page-content pb-24 md:pb-0">


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
        MOBILE BOTTOM NAV
    ============================================================= --}}

    <x-navigation.mobile-bottom-nav />


    {{-- ============================================================
        TOAST
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
                            item.type === 'info'
                    }"
                >

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
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />
                    </svg>


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
                        <path d="m10.3 3.7-8 14A2 2 0 0 0 4 20.7h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z" />
                    </svg>


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


                <div
                    class="toast-message"
                    x-text="item.message"
                ></div>


                <button
                    type="button"
                    class="text-content-faint transition hover:text-content"
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
