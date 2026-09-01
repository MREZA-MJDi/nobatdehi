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
            @yield('title') | مدیریت نوبت‌دهی
        @else
            مدیریت نوبت‌دهی
        @endif
    </title>

    <meta
        name="description"
        content="@yield('meta_description', 'مدیریت سیستم نوبت‌دهی')"
    >

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

</head>


<body class="bg-background text-content">

<div class="min-h-screen">


    {{-- ============================================================
        DESKTOP ADMIN SIDEBAR
    ============================================================= --}}

    <aside
        class="dashboard-sidebar fixed inset-y-0 right-0 z-40 hidden flex-col md:flex"
    >

        {{-- Brand --}}

        <div class="flex h-[4.5rem] items-center border-b border-border px-5">

            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3"
            >

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-900 text-sm font-black text-white shadow-soft">
                    ن
                </span>

                <div>

                    <div class="text-sm font-black text-content">
                        نوبت‌دهی
                    </div>

                    <div class="mt-0.5 text-[10px] font-medium text-content-muted">
                        Super Admin
                    </div>

                </div>

            </a>

        </div>


        {{-- Navigation --}}

        <nav class="flex-1 overflow-y-auto p-3">

            <div class="mb-2 px-3 text-[10px] font-bold text-content-faint">
                مدیریت
            </div>


            <a
                href="{{ route('admin.dashboard') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('admin.dashboard')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect x="4" y="4" width="7" height="7" rx="1.5" />
                    <rect x="13" y="4" width="7" height="7" rx="1.5" />
                    <rect x="4" y="13" width="7" height="7" rx="1.5" />
                    <rect x="13" y="13" width="7" height="7" rx="1.5" />
                </svg>

                داشبورد

            </a>


            <a
                href="{{ route('admin.salons.index') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition
                {{ request()->routeIs('admin.salons.*')
                    ? 'bg-accent-50 text-accent-700'
                    : 'text-content-soft hover:bg-primary-50' }}"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 21V8l8-5 8 5v13" />
                    <path d="M8 21v-6h8v6" />
                    <path d="M8 10h2M14 10h2M8 13h2M14 13h2" />
                </svg>

                سالن‌ها

            </a>


            <a
                href="{{ route('admin.salons.create') }}"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold text-content-soft transition hover:bg-primary-50"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="8" />
                    <path d="M12 8v8M8 12h8" />
                </svg>

                ایجاد سالن

            </a>


            <div class="my-4 h-px bg-border"></div>


            <div class="mb-2 px-3 text-[10px] font-bold text-content-faint">
                سیستم
            </div>


            <a
                href="{{ url('/') }}"
                target="_blank"
                rel="noopener"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold text-content-soft transition hover:bg-primary-50"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M14 4h6v6" />
                    <path d="M10 14 20 4" />
                    <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4" />
                </svg>

                مشاهده سایت

            </a>

        </nav>


        {{-- Admin Identity --}}

        <div class="border-t border-border p-3">

            <div class="flex items-center gap-3 rounded-xl bg-primary-50 p-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-900 text-xs font-black text-white">
                    {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>

                <div class="min-w-0">

                    <div class="truncate text-xs font-bold text-content">
                        {{ auth()->user()->name ?? 'Super Admin' }}
                    </div>

                    <div class="mt-0.5 truncate text-[10px] text-content-muted">
                        {{ auth()->user()->email ?? '' }}
                    </div>

                </div>

            </div>

        </div>

    </aside>



    {{-- ============================================================
        MOBILE ADMIN HEADER
    ============================================================= --}}

    <header
        class="sticky top-0 z-40 border-b border-border bg-white/90 backdrop-blur-xl md:hidden"
    >

        <div class="flex h-14 items-center justify-between px-4">

            <div class="flex items-center gap-2.5">

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-900 text-xs font-black text-white">
                    ن
                </div>

                <div>

                    <div class="text-xs font-black text-content">
                        مدیریت
                    </div>

                    <div class="text-[9px] text-content-muted">
                        Super Admin
                    </div>

                </div>

            </div>


            <a
                href="{{ url('/') }}"
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-content-soft"
                aria-label="مشاهده سایت"
            >

                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M14 4h6v6" />
                    <path d="M10 14 20 4" />
                    <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4" />
                </svg>

            </a>

        </div>

    </header>



    {{-- ============================================================
        MAIN
    ============================================================= --}}

    <main class="min-h-screen md:mr-[17rem]">

        @if(session('success'))

            <div class="px-4 pt-4 md:px-6">

                <div class="mx-auto max-w-[1400px]">

                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>

                </div>

            </div>

        @endif


        @if(session('error'))

            <div class="px-4 pt-4 md:px-6">

                <div class="mx-auto max-w-[1400px]">

                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>

                </div>

            </div>

        @endif


        @yield('content')

    </main>



    {{-- ============================================================
        MOBILE BOTTOM NAV
    ============================================================= --}}

    <nav
        class="fixed inset-x-0 bottom-0 z-[999] border-t border-border bg-white/94 pb-[env(safe-area-inset-bottom)] shadow-float backdrop-blur-2xl md:hidden"
    >

        <div class="mx-auto grid max-w-lg grid-cols-4">

            <a
                href="{{ route('admin.dashboard') }}"
                class="flex min-h-[3.7rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('admin.dashboard')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >

                <span
                    class="flex h-8 w-8 items-center justify-center rounded-xl
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-accent-100'
                        : '' }}"
                >

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect x="4" y="4" width="7" height="7" rx="1.5" />
                        <rect x="13" y="4" width="7" height="7" rx="1.5" />
                        <rect x="4" y="13" width="7" height="7" rx="1.5" />
                        <rect x="13" y="13" width="7" height="7" rx="1.5" />
                    </svg>

                </span>

                داشبورد

            </a>


            <a
                href="{{ route('admin.salons.index') }}"
                class="flex min-h-[3.7rem] flex-col items-center justify-center gap-1 text-[9px] font-bold
                {{ request()->routeIs('admin.salons.*')
                    ? 'text-accent-600'
                    : 'text-content-muted' }}"
            >

                <span
                    class="flex h-8 w-8 items-center justify-center rounded-xl
                    {{ request()->routeIs('admin.salons.*')
                        ? 'bg-accent-100'
                        : '' }}"
                >

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 21V8l8-5 8 5v13" />
                        <path d="M8 21v-6h8v6" />
                        <path d="M8 10h2M14 10h2M8 13h2M14 13h2" />
                    </svg>

                </span>

                سالن‌ها

            </a>


            <a
                href="{{ route('admin.salons.create') }}"
                class="flex min-h-[3.7rem] flex-col items-center justify-center gap-1 text-[9px] font-bold text-content-muted"
            >

                <span class="-mt-5 flex h-11 w-11 items-center justify-center rounded-2xl border-4 border-background bg-accent-600 text-white shadow-iris">

                    <svg
                        width="19"
                        height="19"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 5v14M5 12h14" />
                    </svg>

                </span>

                ایجاد سالن

            </a>


            <a
                href="{{ url('/') }}"
                class="flex min-h-[3.7rem] flex-col items-center justify-center gap-1 text-[9px] font-bold text-content-muted"
            >

                <span class="flex h-8 w-8 items-center justify-center rounded-xl">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M14 4h6v6" />
                        <path d="M10 14 20 4" />
                        <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4" />
                    </svg>

                </span>

                سایت

            </a>

        </div>

    </nav>



    {{-- Mobile bottom spacing --}}

    <div class="h-20 md:hidden"></div>

</div>

</body>

</html>
