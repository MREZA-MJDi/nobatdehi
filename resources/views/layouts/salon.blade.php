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
            @yield('title') | {{ $salon?->name ?? 'پنل سالن' }}
        @else
            {{ $salon?->name ?? 'پنل سالن' }}
        @endif
    </title>

    <meta
        name="description"
        content="@yield('meta_description', 'مدیریت سالن')"
    >

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

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

</head>

<body>

<div class="min-h-screen bg-background">

    {{-- ============================================================
        DESKTOP SIDEBAR
    ============================================================= --}}

    <aside
        class="fixed inset-y-0 right-0 z-40 hidden w-72 flex-col border-l border-border bg-white lg:flex dark:bg-primary-950"
    >

        {{-- Brand --}}
        <div class="border-b border-border p-4">

            <a
                href="{{ route('salon.dashboard') }}"
                class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-primary-50 dark:hover:bg-primary-900"
            >

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-900 text-sm font-black text-white"
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

                    <div class="truncate text-sm font-black text-content">
                        {{ $salon->name }}
                    </div>

                    <div class="mt-1 text-[10px] text-content-muted">
                        پنل مدیریت سالن
                    </div>

                </div>

            </a>

        </div>


        {{-- Salon Status --}}
        <div class="border-b border-border p-4">

            <div class="rounded-2xl bg-primary-50 p-4 dark:bg-primary-900/60">

                <div class="flex items-center justify-between gap-3">

                    <div>

                        <div class="text-[10px] font-bold text-content-muted">
                            وضعیت سالن
                        </div>

                        <div class="mt-1 flex items-center gap-2">

                            <span
                                class="h-2.5 w-2.5 rounded-full
                                {{ $salon->is_active
                                    ? 'bg-green-500'
                                    : 'bg-red-500' }}"
                            ></span>

                            <span class="text-xs font-black text-content">

                                {{ $salon->is_active
                                    ? 'فعال'
                                    : 'غیرفعال' }}

                            </span>

                        </div>

                    </div>

                    <a
                        href="{{ route('public.salons.show', $salon) }}"
                        target="_blank"
                        rel="noopener"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-content-soft shadow-soft transition hover:text-accent-600 dark:bg-primary-800"
                        aria-label="مشاهده صفحه عمومی"
                    >
                        ↗
                    </a>

                </div>

            </div>

        </div>


        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto p-3">

            <div class="mb-2 px-3 text-[10px] font-black text-content-faint">
                اصلی
            </div>


            <a
                href="{{ route('salon.dashboard') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.dashboard')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">⌂</span>

                داشبورد

            </a>


            <a
                href="{{ route('salon.bookings.index') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.bookings.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">◷</span>

                نوبت‌ها

            </a>


            <div class="my-4 h-px bg-border"></div>


            <div class="mb-2 px-3 text-[10px] font-black text-content-faint">
                مدیریت
            </div>

            <a
                href="{{ route('salon.portfolio.index') }}"
                class="..."
            >
                نمونه‌کارها
            </a>
            <a
                href="{{ route('salon.reviews.index') }}"
                class="..."
            >
                نظرات مشتریان
            </a>
            <a
                href="{{ route('salon.barbers.index') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.barbers.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">♙</span>

                آرایشگران

            </a>


            <a
                href="{{ route('salon.services.index') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.services.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">✂</span>

                خدمات

            </a>


            <a
                href="{{ route('salon.working-hours.edit') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.working-hours.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">◷</span>

                ساعات کاری

            </a>


            <a
                href="{{ route('salon.notifications.index') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('salon.notifications.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <span class="text-base">♧</span>

                اعلان‌ها

                @if($unreadNotifications > 0)

                    <span class="mr-auto rounded-full bg-accent-600 px-2 py-0.5 text-[9px] font-black text-white">
                        {{ $unreadNotifications }}
                    </span>

                @endif

            </a>


            <div class="my-4 h-px bg-border"></div>


            <div class="mb-2 px-3 text-[10px] font-black text-content-faint">
                صفحه سالن
            </div>


            <a
                href="{{ route('public.salons.show', $salon) }}"
                target="_blank"
                rel="noopener"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold text-content-soft transition hover:bg-primary-50"
            >

                <span class="text-base">↗</span>

                صفحه عمومی سالن

            </a>

        </nav>


        {{-- Account --}}
        <div class="border-t border-border p-3">

            <div class="mb-2 flex items-center gap-3 rounded-2xl bg-primary-50 p-3 dark:bg-primary-900/60">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-black text-white">
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>

                <div class="min-w-0">

                    <div class="truncate text-xs font-black text-content">
                        {{ auth()->user()->name }}
                    </div>

                    <div class="mt-1 truncate text-[10px] text-content-muted">
                        {{ auth()->user()->phone }}
                    </div>

                </div>

            </div>


            <div class="flex items-center gap-2">

                <x-theme-toggle />

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="flex-1"
                >

                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-xl px-3 py-2 text-xs font-bold text-content-muted transition hover:bg-red-50 hover:text-red-600"
                    >
                        خروج
                    </button>

                </form>

            </div>

        </div>

    </aside>


    {{-- ============================================================
        MOBILE HEADER
    ============================================================= --}}

    <header class="sticky top-0 z-40 border-b border-border bg-white/90 backdrop-blur-xl lg:hidden dark:bg-primary-950/90">

        <div class="flex h-16 items-center justify-between px-4">

            <div class="flex min-w-0 items-center gap-3">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-900 text-xs font-black text-white"
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

                    <div class="truncate text-xs font-black">
                        {{ $salon->name }}
                    </div>

                    <div class="text-[9px] text-content-muted">
                        پنل سالن
                    </div>

                </div>

            </div>


            <div class="flex items-center gap-2">

                <a
                    href="{{ route('salon.notifications.index') }}"
                    class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-content-soft"
                    aria-label="اعلان‌ها"
                >
                    ♧

                    @if($unreadNotifications > 0)

                        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent-600 px-1 text-[8px] font-black text-white">
                            {{ $unreadNotifications }}
                        </span>

                    @endif

                </a>

                <a
                    href="{{ route('public.salons.show', $salon) }}"
                    target="_blank"
                    rel="noopener"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-content-soft"
                    aria-label="صفحه عمومی"
                >
                    ↗
                </a>

            </div>

        </div>

    </header>


    {{-- ============================================================
        MAIN
    ============================================================= --}}

    <main class="min-h-screen lg:mr-72">

        @if(session('success'))

            <div class="px-4 pt-4 sm:px-6">

                <div class="mx-auto max-w-7xl">

                    <div
                        class="alert alert-success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>

                </div>

            </div>

        @endif


        @if(session('error'))

            <div class="px-4 pt-4 sm:px-6">

                <div class="mx-auto max-w-7xl">

                    <div
                        class="alert alert-danger"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>

                </div>

            </div>

        @endif


        @yield('content')

    </main>


    {{-- ============================================================
        MOBILE BOTTOM BAR
    ============================================================= --}}

    <nav
        class="fixed inset-x-0 bottom-0 z-[999] border-t border-border bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-float backdrop-blur-2xl lg:hidden dark:bg-primary-950/95"
        aria-label="ناوبری سالن"
    >

        <div class="mx-auto grid max-w-lg grid-cols-5">

            <a
                href="{{ route('salon.dashboard') }}"
                class="flex min-h-[4rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('salon.dashboard')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >
                <span class="text-lg">⌂</span>
                خانه
            </a>


            <a
                href="{{ route('salon.bookings.index') }}"
                class="flex min-h-[4rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('salon.bookings.*')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >
                <span class="text-lg">◷</span>
                نوبت‌ها
            </a>


            <a
                href="{{ route('salon.barbers.index') }}"
                class="flex min-h-[4rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('salon.barbers.*')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >
                <span class="text-lg">♙</span>
                تیم
            </a>


            <a
                href="{{ route('salon.services.index') }}"
                class="flex min-h-[4rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('salon.services.*')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >
                <span class="text-lg">✂</span>
                خدمات
            </a>


            <a
                href="{{ route('salon.notifications.index') }}"
                class="relative flex min-h-[4rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('salon.notifications.*')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >

                <span class="relative text-lg">

                    ♧

                    @if($unreadNotifications > 0)

                        <span class="absolute -right-2 -top-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-accent-600 px-1 text-[7px] font-black text-white">
                            {{ $unreadNotifications }}
                        </span>

                    @endif

                </span>

                اعلان‌ها

            </a>

        </div>

    </nav>


    <div class="h-20 lg:hidden"></div>

</div>

</body>

</html>
