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
            'ورود و ثبت‌نام در نوبت‌دهی'
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
    class="min-h-screen bg-primary-50"
>


    {{-- ============================================================
        AUTH HEADER
    ============================================================= --}}

    <header class="border-b border-border bg-surface/90 backdrop-blur-xl">

        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">


            {{-- Brand --}}

            <a
                href="{{ route('home') }}"
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


            {{-- Actions --}}

            <div class="flex items-center gap-2">

                <x-theme-toggle />


                <a
                    href="{{ route('home') }}"
                    class="btn btn-ghost btn-sm"
                >
                    بازگشت به سایت
                </a>

            </div>

        </div>

    </header>


    {{-- ============================================================
        MAIN
    ============================================================= --}}

    <main class="flex min-h-[calc(100vh-4rem)] items-start justify-center px-4 py-8 pb-10 sm:items-center sm:py-12">

        <div class="w-full max-w-md">


            {{-- ====================================================
                GLOBAL FLASH
            ===================================================== --}}

            @if(session('success'))

                <div class="mb-4">

                    <div
                        class="alert alert-success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>

                </div>

            @endif


            @if(session('error'))

                <div class="mb-4">

                    <div
                        class="alert alert-danger"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>

                </div>

            @endif


            @yield('content')

        </div>

    </main>


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
