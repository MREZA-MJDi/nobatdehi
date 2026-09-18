<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
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
            @yield('title') | {{ $salon?->name ?? 'پنل سالن' }}
        @else
            {{ $salon?->name ?? 'پنل سالن' }}
        @endif
    </title>


    <meta
        name="description"
        content="@yield(
            'meta_description',
            'مدیریت سالن'
        )"
    >


    <meta
        name="theme-color"
        content="#6757E8"
    >


    {{-- ============================================================
        THEME
    ============================================================= --}}

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


    {{-- ============================================================
        CORE VITE ASSETS
    ============================================================= --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])


    {{-- ============================================================
        PAGE-SPECIFIC HEAD
    ============================================================= --}}

    @stack('head')

</head>


<body
    class="
        min-h-screen
        bg-background
        text-content
        antialiased
    "
>

<div class="min-h-screen">


    {{-- ============================================================
        DESKTOP SIDEBAR
    ============================================================= --}}

    <aside
        class="
            fixed
            inset-y-0
            right-0
            z-50
            hidden
            w-[280px]
            flex-col
            border-l
            border-border
            bg-white
            shadow-sm
            lg:flex
            dark:bg-primary-950
        "
    >

        {{-- ========================================================
            BRAND
        ========================================================= --}}

        <div class="shrink-0 border-b border-border px-4 py-4">

            <a
                href="{{ route('salon.dashboard') }}"
                class="
                    group
                    flex
                    items-center
                    gap-3
                    rounded-2xl
                    p-2
                    transition
                    hover:bg-primary-50
                    dark:hover:bg-primary-900/60
                "
            >

                <div
                    class="
                        flex
                        h-11
                        w-11
                        shrink-0
                        items-center
                        justify-center
                        overflow-hidden
                        rounded-2xl
                        bg-primary-900
                        text-sm
                        font-black
                        text-white
                        shadow-sm
                    "
                >

                    @if($salon->logo_path)

                        <img
                            src="{{ $salon->logo_url }}"
                            alt="{{ $salon->name }}"
                            class="h-full w-full object-cover"
                        >

                    @else

                        {{ mb_substr($salon->name, 0, 1) }}

                    @endif

                </div>


                <div class="min-w-0 flex-1">

                    <div
                        class="
                            truncate
                            text-sm
                            font-black
                            text-content
                        "
                    >
                        {{ $salon->name }}
                    </div>

                    <div
                        class="
                            mt-1
                            text-[10px]
                            font-medium
                            text-content-muted
                        "
                    >
                        پنل مدیریت سالن
                    </div>

                </div>


                <span
                    class="
                        text-lg
                        text-content-faint
                        transition
                        group-hover:-translate-x-0.5
                        group-hover:text-accent-600
                    "
                    aria-hidden="true"
                >
                    ←
                </span>

            </a>

        </div>


        {{-- ========================================================
            SALON STATUS
        ========================================================= --}}

        <div class="shrink-0 px-4 py-4">

            <div
                class="
                    rounded-2xl
                    border
                    border-border
                    bg-primary-50/70
                    p-3.5
                    dark:bg-primary-900/50
                "
            >

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-3
                    "
                >

                    <div class="min-w-0">

                        <div
                            class="
                                text-[10px]
                                font-bold
                                text-content-muted
                            "
                        >
                            وضعیت سالن
                        </div>

                        <div class="mt-1.5 flex items-center gap-2">

                            <span
                                class="
                                    h-2.5
                                    w-2.5
                                    shrink-0
                                    rounded-full

                                    {{ $salon->is_active
                                        ? 'bg-green-500 shadow-[0_0_0_4px_rgba(34,197,94,0.10)]'
                                        : 'bg-red-500 shadow-[0_0_0_4px_rgba(239,68,68,0.10)]'
                                    }}
                                    "
                                aria-hidden="true"
                            ></span>

                            <span
                                class="
                                    text-xs
                                    font-black
                                    text-content
                                "
                            >
                                {{ $salon->is_active ? 'فعال' : 'غیرفعال' }}
                            </span>

                        </div>

                    </div>


                    <a
                        href="{{ route('public.salons.show', $salon) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="
                            flex
                            h-9
                            w-9
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-white
                            text-sm
                            text-content-soft
                            shadow-sm
                            ring-1
                            ring-black/5
                            transition
                            hover:-translate-y-0.5
                            hover:text-accent-600
                            dark:bg-primary-800
                            dark:ring-white/5
                        "
                        aria-label="مشاهده صفحه عمومی سالن"
                    >
                        ↗
                    </a>

                </div>

            </div>

        </div>


        {{-- ========================================================
            NAVIGATION
        ========================================================= --}}

        <nav
            class="min-h-0 flex-1 overflow-y-auto px-3 pb-4"
            aria-label="ناوبری پنل سالن"
        >

            <div
                class="
                    mb-2
                    px-3
                    text-[10px]
                    font-black
                    tracking-wide
                    text-content-faint
                "
            >
                اصلی
            </div>


            {{-- Dashboard --}}
            <a
                href="{{ route('salon.dashboard') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.dashboard')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ⌂
                </span>

                <span>
                    داشبورد
                </span>
            </a>


            {{-- Bookings --}}
            <a
                href="{{ route('salon.bookings.index') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.bookings.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ◷
                </span>

                <span>
                    نوبت‌ها
                </span>
            </a>


            <div class="my-4 h-px bg-border"></div>


            <div
                class="
                    mb-2
                    px-3
                    text-[10px]
                    font-black
                    tracking-wide
                    text-content-faint
                "
            >
                مدیریت
            </div>


            {{-- Portfolio --}}
            {{-- Posts --}}
            <a
                href="{{ route('salon.posts.index') }}"
                class="
        group
        mb-1
        flex
        items-center
        gap-3
        rounded-xl
        px-3
        py-3
        text-xs
        font-bold
        transition

        {{ request()->routeIs('salon.posts.*')
            ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
            : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
        }}
                    "
            >
    <span
        class="flex h-5 w-5 items-center justify-center text-base"
        aria-hidden="true"
    >
        ◫
    </span>

                <span>
        پست‌ها
    </span>
            </a>

            {{-- Reviews --}}
            <a
                href="{{ route('salon.reviews.index') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.reviews.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ♡
                </span>

                <span>
                    نظرات مشتریان
                </span>
            </a>


            {{-- Barbers --}}
            <a
                href="{{ route('salon.barbers.index') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.barbers.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ♙
                </span>

                <span>
                    آرایشگران
                </span>
            </a>


            {{-- Services --}}
            <a
                href="{{ route('salon.services.index') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.services.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ✂
                </span>

                <span>
                    خدمات
                </span>
            </a>


            {{-- Working hours --}}
            <a
                href="{{ route('salon.working-hours.edit') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.working-hours.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ◷
                </span>

                <span>
                    ساعات کاری
                </span>
            </a>


            {{-- Notifications --}}
            <a
                href="{{ route('salon.notifications.index') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.notifications.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >

                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ♧
                </span>

                <span>
                    اعلان‌ها
                </span>


                @if($unreadNotifications > 0)

                    <span
                        class="
                            mr-auto
                            flex
                            h-5
                            min-w-5
                            items-center
                            justify-center
                            rounded-full
                            bg-accent-600
                            px-1.5
                            text-[9px]
                            font-black
                            text-white
                        "
                    >
                        {{ $unreadNotifications }}
                    </span>

                @endif

            </a>


            <div class="my-4 h-px bg-border"></div>


            {{-- Salon --}}
            <div
                class="
                    mb-2
                    px-3
                    text-[10px]
                    font-black
                    tracking-wide
                    text-content-faint
                "
            >
                سالن
            </div>


            {{-- Settings --}}
            <a
                href="{{ route('salon.settings.edit') }}"
                class="
                    group
                    mb-1
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    transition

                    {{ request()->routeIs('salon.settings.*')
                        ? 'bg-accent-50 text-accent-700 dark:bg-accent-900/20 dark:text-accent-300'
                        : 'text-content-soft hover:bg-primary-50 hover:text-content dark:hover:bg-primary-900/60'
                    }}
                    "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ⚙
                </span>

                <span>
                    تنظیمات سالن
                </span>
            </a>


            <div class="my-4 h-px bg-border"></div>


            {{-- Public page --}}
            <div
                class="
                    mb-2
                    px-3
                    text-[10px]
                    font-black
                    tracking-wide
                    text-content-faint
                "
            >
                صفحه سالن
            </div>


            <a
                href="{{ route('public.salons.show', $salon) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="
                    group
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    px-3
                    py-3
                    text-xs
                    font-bold
                    text-content-soft
                    transition
                    hover:bg-primary-50
                    hover:text-content
                    dark:hover:bg-primary-900/60
                "
            >
                <span
                    class="flex h-5 w-5 items-center justify-center text-base"
                    aria-hidden="true"
                >
                    ↗
                </span>

                <span>
                    صفحه عمومی سالن
                </span>
            </a>

        </nav>


        {{-- ========================================================
            ACCOUNT
        ========================================================= --}}

        <div class="shrink-0 border-t border-border p-3">

            <div
                class="
                    mb-2
                    flex
                    items-center
                    gap-3
                    rounded-2xl
                    border
                    border-border
                    bg-primary-50/70
                    p-3
                    dark:bg-primary-900/50
                "
            >

                <div
                    class="
                        flex
                        h-9
                        w-9
                        shrink-0
                        items-center
                        justify-center
                        rounded-full
                        bg-accent-600
                        text-xs
                        font-black
                        text-white
                    "
                    aria-hidden="true"
                >
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>


                <div class="min-w-0 flex-1">

                    <div
                        class="
                            truncate
                            text-xs
                            font-black
                            text-content
                        "
                    >
                        {{ auth()->user()->name }}
                    </div>

                    <div
                        class="
                            mt-1
                            truncate
                            text-[10px]
                            text-content-muted
                        "
                    >
                        {{ auth()->user()->phone }}
                    </div>

                </div>

            </div>


            <div class="flex items-center gap-2">

                <div class="shrink-0">
                    <x-theme-toggle />
                </div>


                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="min-w-0 flex-1"
                >

                    @csrf

                    <button
                        type="submit"
                        class="
                            flex
                            w-full
                            items-center
                            justify-center
                            rounded-xl
                            px-3
                            py-2.5
                            text-xs
                            font-bold
                            text-content-muted
                            transition
                            hover:bg-red-50
                            hover:text-red-600
                            dark:hover:bg-red-950/30
                        "
                    >
                        خروج از حساب
                    </button>

                </form>

            </div>

        </div>

    </aside>


    {{-- ============================================================
        MOBILE HEADER
    ============================================================= --}}

    <header
        class="
            sticky
            top-0
            z-40
            border-b
            border-border
            bg-white/90
            backdrop-blur-xl
            lg:hidden
            dark:bg-primary-950/90
        "
    >

        <div
            class="
                mx-auto
                flex
                h-16
                max-w-2xl
                items-center
                justify-between
                px-4
            "
        >

            <a
                href="{{ route('salon.dashboard') }}"
                class="flex min-w-0 items-center gap-3"
            >

                <div
                    class="
                        flex
                        h-10
                        w-10
                        shrink-0
                        items-center
                        justify-center
                        overflow-hidden
                        rounded-xl
                        bg-primary-900
                        text-xs
                        font-black
                        text-white
                        shadow-sm
                    "
                >

                    @if($salon->logo_path)

                        <img
                            src="{{ $salon->logo_url }}"
                            alt="{{ $salon->name }}"
                            class="h-full w-full object-cover"
                        >

                    @else

                        {{ mb_substr($salon->name, 0, 1) }}

                    @endif

                </div>


                <div class="min-w-0">

                    <div
                        class="
                            truncate
                            text-xs
                            font-black
                            text-content
                        "
                    >
                        {{ $salon->name }}
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-[9px]
                            text-content-muted
                        "
                    >
                        پنل مدیریت سالن
                    </div>

                </div>

            </a>


            <div class="flex shrink-0 items-center gap-2">

                <a
                    href="{{ route('salon.notifications.index') }}"
                    class="
                        relative
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        bg-primary-50
                        text-content-soft
                        transition
                        hover:text-accent-600
                        dark:bg-primary-900/70
                    "
                    aria-label="اعلان‌ها"
                >

                    <span aria-hidden="true">
                        ♧
                    </span>


                    @if($unreadNotifications > 0)

                        <span
                            class="
                                absolute
                                -right-1
                                -top-1
                                flex
                                h-4
                                min-w-4
                                items-center
                                justify-center
                                rounded-full
                                bg-accent-600
                                px-1
                                text-[8px]
                                font-black
                                text-white
                            "
                        >
                            {{ $unreadNotifications }}
                        </span>

                    @endif

                </a>


                <a
                    href="{{ route('public.salons.show', $salon) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        bg-primary-50
                        text-sm
                        text-content-soft
                        transition
                        hover:text-accent-600
                        dark:bg-primary-900/70
                    "
                    aria-label="صفحه عمومی سالن"
                >
                    ↗
                </a>

            </div>

        </div>

    </header>


    {{-- ============================================================
        MAIN CONTENT
    ============================================================= --}}

    <main
        class="
            min-h-screen
            lg:mr-[280px]
        "
    >

        {{-- ========================================================
            FLASH: SUCCESS
        ========================================================= --}}

        @if(session('success'))

            <div class="px-4 pt-4 sm:px-6 lg:px-8">

                <div class="mx-auto w-full max-w-7xl">

                    <div
                        class="alert alert-success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>

                </div>

            </div>

        @endif


        {{-- ========================================================
            FLASH: ERROR
        ========================================================= --}}

        @if(session('error'))

            <div class="px-4 pt-4 sm:px-6 lg:px-8">

                <div class="mx-auto w-full max-w-7xl">

                    <div
                        class="alert alert-danger"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>

                </div>

            </div>

        @endif


        <div class="w-full">

            @yield('content')

        </div>

    </main>


    {{-- ============================================================
        MOBILE BOTTOM NAV
    ============================================================= --}}

    <nav
        class="
            fixed
            inset-x-0
            bottom-0
            z-[999]
            border-t
            border-border
            bg-white/95
            pb-[env(safe-area-inset-bottom)]
            shadow-float
            backdrop-blur-2xl
            lg:hidden
            dark:bg-primary-950/95
        "
        aria-label="ناوبری سالن"
    >

        <div
            class="
                mx-auto
                grid
                max-w-lg
                grid-cols-5
            "
        >

            {{-- Dashboard --}}
            <a
                href="{{ route('salon.dashboard') }}"
                class="
                    relative
                    flex
                    min-h-[4.25rem]
                    flex-col
                    items-center
                    justify-center
                    gap-1
                    text-[9px]
                    font-bold
                    transition

                    {{ request()->routeIs('salon.dashboard')
                        ? 'text-accent-600'
                        : 'text-content-muted'
                    }}
                    "
            >

                @if(request()->routeIs('salon.dashboard'))
                    <span
                        class="
                            absolute
                            inset-x-6
                            top-0
                            h-0.5
                            rounded-full
                            bg-accent-600
                        "
                        aria-hidden="true"
                    ></span>
                @endif

                <span
                    class="text-lg leading-none"
                    aria-hidden="true"
                >
                    ⌂
                </span>

                خانه

            </a>


            {{-- Bookings --}}
            <a
                href="{{ route('salon.bookings.index') }}"
                class="
                    relative
                    flex
                    min-h-[4.25rem]
                    flex-col
                    items-center
                    justify-center
                    gap-1
                    text-[9px]
                    font-bold
                    transition

                    {{ request()->routeIs('salon.bookings.*')
                        ? 'text-accent-600'
                        : 'text-content-muted'
                    }}
                    "
            >

                @if(request()->routeIs('salon.bookings.*'))
                    <span
                        class="
                            absolute
                            inset-x-6
                            top-0
                            h-0.5
                            rounded-full
                            bg-accent-600
                        "
                        aria-hidden="true"
                    ></span>
                @endif

                <span
                    class="text-lg leading-none"
                    aria-hidden="true"
                >
                    ◷
                </span>

                نوبت‌ها

            </a>


            {{-- Barbers --}}
            <a
                href="{{ route('salon.barbers.index') }}"
                class="
                    relative
                    flex
                    min-h-[4.25rem]
                    flex-col
                    items-center
                    justify-center
                    gap-1
                    text-[9px]
                    font-bold
                    transition

                    {{ request()->routeIs('salon.barbers.*')
                        ? 'text-accent-600'
                        : 'text-content-muted'
                    }}
                    "
            >

                @if(request()->routeIs('salon.barbers.*'))
                    <span
                        class="
                            absolute
                            inset-x-6
                            top-0
                            h-0.5
                            rounded-full
                            bg-accent-600
                        "
                        aria-hidden="true"
                    ></span>
                @endif

                <span
                    class="text-lg leading-none"
                    aria-hidden="true"
                >
                    ♙
                </span>

                تیم

            </a>


            {{-- Services --}}
            <a
                href="{{ route('salon.services.index') }}"
                class="
                    relative
                    flex
                    min-h-[4.25rem]
                    flex-col
                    items-center
                    justify-center
                    gap-1
                    text-[9px]
                    font-bold
                    transition

                    {{ request()->routeIs('salon.services.*')
                        ? 'text-accent-600'
                        : 'text-content-muted'
                    }}
                    "
            >

                @if(request()->routeIs('salon.services.*'))
                    <span
                        class="
                            absolute
                            inset-x-6
                            top-0
                            h-0.5
                            rounded-full
                            bg-accent-600
                        "
                        aria-hidden="true"
                    ></span>
                @endif

                <span
                    class="text-lg leading-none"
                    aria-hidden="true"
                >
                    ✂
                </span>

                خدمات

            </a>


            {{-- Notifications --}}
            <a
                href="{{ route('salon.notifications.index') }}"
                class="
                    relative
                    flex
                    min-h-[4.25rem]
                    flex-col
                    items-center
                    justify-center
                    gap-1
                    text-[9px]
                    font-bold
                    transition

                    {{ request()->routeIs('salon.notifications.*')
                        ? 'text-accent-600'
                        : 'text-content-muted'
                    }}
                    "
            >

                @if(request()->routeIs('salon.notifications.*'))
                    <span
                        class="
                            absolute
                            inset-x-6
                            top-0
                            h-0.5
                            rounded-full
                            bg-accent-600
                        "
                        aria-hidden="true"
                    ></span>
                @endif

                <span
                    class="
                        relative
                        text-lg
                        leading-none
                    "
                >

                    <span aria-hidden="true">
                        ♧
                    </span>

                    @if($unreadNotifications > 0)

                        <span
                            class="
                                absolute
                                -right-2
                                -top-1
                                flex
                                h-3.5
                                min-w-3.5
                                items-center
                                justify-center
                                rounded-full
                                bg-accent-600
                                px-1
                                text-[7px]
                                font-black
                                text-white
                            "
                        >
                            {{ $unreadNotifications }}
                        </span>

                    @endif

                </span>

                اعلان‌ها

            </a>

        </div>

    </nav>


    {{-- ============================================================
        MOBILE NAV SPACING
    ============================================================= --}}

    <div
        class="h-[5.5rem] lg:hidden"
        aria-hidden="true"
    ></div>


    {{-- ============================================================
        PAGE SCRIPTS
    ============================================================= --}}

    @stack('scripts')

</div>

</body>

</html>
