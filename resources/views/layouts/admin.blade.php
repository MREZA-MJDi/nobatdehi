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
            @yield('title') | پنل مدیریت نوبت‌دهی
        @else
            پنل مدیریت نوبت‌دهی
        @endif
    </title>


    <meta
        name="description"
        content="@yield(
            'meta_description',
            'پنل مدیریت نوبت‌دهی'
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
        ADMIN NAVBAR
    ============================================================= --}}

    <header class="app-navbar">

        <div class="app-container navbar-inner">


            {{-- Brand --}}

            <a
                href="{{ route('admin.dashboard') }}"
                class="brand"
                aria-label="داشبورد مدیریت"
            >

                <span class="brand-mark">
                    ن
                </span>

                <span class="brand-text">

                    <span class="brand-name">
                        نوبت‌دهی
                    </span>

                    <span class="brand-caption">
                        پنل مدیریت
                    </span>

                </span>

            </a>


            {{-- Desktop Navigation --}}

            <nav
                class="hidden items-center gap-1 md:flex"
                aria-label="ناوبری مدیریت"
            >

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="rounded-xl px-3 py-2 text-xs font-bold transition
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-primary-100 text-content'
                        : 'text-content-muted hover:bg-primary-50 hover:text-content'
                    }}"
                >
                    داشبورد
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


                <a
                    href="{{ route('admin.salons.create') }}"
                    class="rounded-xl px-3 py-2 text-xs font-bold text-content-muted transition hover:bg-primary-50 hover:text-content"
                >
                    ایجاد سالن
                </a>


                <a
                    href="{{ route('home') }}"
                    class="rounded-xl px-3 py-2 text-xs font-bold text-content-muted transition hover:bg-primary-50 hover:text-content"
                >
                    مشاهده سایت
                </a>

            </nav>


            {{-- Actions --}}

            <div class="ml-auto flex items-center gap-2">

                <x-theme-toggle />


                <span class="hidden text-xs font-bold text-content-muted lg:block">
                    {{ auth()->user()->name }}
                </span>


                <form
                    action="{{ route('logout') }}"
                    method="POST"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-ghost btn-sm"
                    >
                        خروج
                    </button>

                </form>

            </div>

        </div>

    </header>


    {{-- ============================================================
        MAIN
    ============================================================= --}}

    <main class="page-content pb-8">


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
        MOBILE ADMIN NAV
    ============================================================= --}}

    <nav
        class="fixed inset-x-3 bottom-3 z-50 md:hidden"
        aria-label="ناوبری مدیریت"
    >

        <div class="grid grid-cols-4 gap-1 rounded-2xl border border-border bg-surface p-2 shadow-float">

            <a
                href="{{ route('admin.dashboard') }}"
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold transition
                {{ request()->routeIs('admin.dashboard')
                    ? 'bg-primary-100 text-content'
                    : 'text-content-muted'
                }}"
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
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold transition
                {{ request()->routeIs('admin.salons.index')
                    ? 'bg-primary-100 text-content'
                    : 'text-content-muted'
                }}"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M3 10h18" />
                    <path d="M5 10v10" />
                    <path d="M19 10v10" />
                    <path d="M3 20h18" />
                    <path d="M4 10 6 4h12l2 6" />
                    <path d="M9 20v-6h6v6" />
                </svg>

                سالن‌ها

            </a>


            <a
                href="{{ route('admin.salons.create') }}"
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold text-content-muted transition"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="8"
                    />

                    <path d="M12 8v8" />
                    <path d="M8 12h8" />
                </svg>

                ایجاد

            </a>


            <a
                href="{{ route('home') }}"
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold text-content-muted transition"
            >

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5.5 9.5V21h13V9.5" />
                </svg>

                سایت

            </a>

        </div>

    </nav>


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
                        <circle cx="12" cy="12" r="9" />
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
                        <circle cx="12" cy="12" r="9" />
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
