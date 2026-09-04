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

    {{-- ==========================================================
        THEME
    =========================================================== --}}

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


    {{-- ==========================================================
        ASSETS
    =========================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/css/customer.css',
    'resources/js/app.js',
    'resources/js/customer.js',
    ])

    @stack('head')

</head>


<body class="min-h-screen">

{{-- ==========================================================
    APP
=========================================================== --}}

<div
    id="app"
    class="min-h-screen"
>

    {{-- ======================================================
        DESKTOP HEADER
    ======================================================= --}}

    <header
        id="site-header"
        style="
        position: relative !important;
        inset: auto !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        left: auto !important;
        float: none !important;
        order: 0 !important;
        transform: none !important;
        width: 100% !important;
    "
        class="z-50 w-full border-b border-border bg-background/95 backdrop-blur-xl"
    >
        <div class="mx-auto flex min-h-16 w-full max-w-[90rem] items-center gap-6 px-5 sm:px-8">

            {{-- Brand --}}
            <a
                href="{{ route('home') }}"
                class="flex shrink-0 items-center gap-3"
            >
            <span
                class="grid size-10 place-items-center rounded-xl bg-primary text-sm font-black text-primary-foreground"
            >
                ن
            </span>

                <span class="hidden flex-col sm:flex">
                <span class="text-sm font-black">
                    نوبت‌دهی
                </span>

                <span class="text-[10px] text-muted-foreground">
                    رزرو آسان، تجربه بهتر
                </span>
            </span>
            </a>


            {{-- Desktop navigation --}}
            <nav
                class="hidden flex-1 items-center justify-center gap-1 md:flex"
                aria-label="ناوبری اصلی"
            >

                <a
                    href="{{ route('home') }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    خانه
                </a>

                <a
                    href="{{ route('salons.discover') }}"
                    class="rounded-xl bg-primary/10 px-4 py-2 text-xs font-bold text-primary"
                >
                    کشف
                </a>

                <a
                    href="{{ route('salons.discover', ['type' => 'salon']) }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    سالن‌ها
                </a>

                <a
                    href="{{ route('salons.discover', ['type' => 'barber']) }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    آرایشگرها
                </a>

            </nav>


            {{-- Actions --}}
            <div class="flex shrink-0 items-center gap-2">

                <x-theme-toggle />

                @guest

                    <a
                        href="{{ route('login') }}"
                        class="hidden rounded-xl px-4 py-2 text-xs font-bold text-muted-foreground hover:bg-muted hover:text-foreground sm:inline-flex"
                    >
                        ورود
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="hidden rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground sm:inline-flex"
                    >
                        ثبت‌نام
                    </a>

                @else

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="hidden sm:block"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl px-4 py-2 text-xs font-bold text-muted-foreground hover:bg-muted hover:text-foreground"
                        >
                            خروج
                        </button>
                    </form>

                @endguest

            </div>

        </div>
    </header>

    {{-- ==========================================================
        MAIN
    =========================================================== --}}

    <main class="relative min-h-[60vh]">

        {{-- ======================================================
            FLASH MESSAGES
        ======================================================= --}}

        @if(session('success'))

            <div class="mx-auto w-full max-w-[90rem] px-5 pt-4 sm:px-8">

                <div
                    class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700"
                    role="status"
                    aria-live="polite"
                >
                    {{ session('success') }}
                </div>

            </div>

        @endif


        @if(session('error'))

            <div class="mx-auto w-full max-w-[90rem] px-5 pt-4 sm:px-8">

                <div
                    class="rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700"
                    role="alert"
                    aria-live="assertive"
                >
                    {{ session('error') }}
                </div>

            </div>

        @endif


        @if(session('status'))

            <div class="mx-auto w-full max-w-[90rem] px-5 pt-4 sm:px-8">

                <div
                    class="rounded-xl border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-700"
                    role="status"
                    aria-live="polite"
                >
                    {{ session('status') }}
                </div>

            </div>

        @endif


        {{-- ======================================================
            PAGE CONTENT
        ======================================================= --}}

        <div class="customer-page">
            @yield('content')
        </div>

    </main>


    {{-- ==========================================================
        MOBILE NAV
    =========================================================== --}}

    <x-navigation.mobile-bottom-nav />


    {{-- ==========================================================
        TOAST
    =========================================================== --}}

    <div
        class="fixed bottom-4 left-4 z-[200] w-[min(24rem,calc(100%-2rem))]"
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
                class="mb-2 flex items-center gap-3 rounded-2xl border border-border bg-surface/95 p-3 shadow-float backdrop-blur-xl"
            >

                <div
                    class="grid size-8 shrink-0 place-items-center rounded-full bg-primary/10 text-sm font-black text-primary"
                    :class="{
                            'bg-success-100 text-success-700':
                                item.type === 'success',

                            'bg-danger-100 text-danger-700':
                                item.type === 'danger',

                            'bg-warning-100 text-warning-700':
                                item.type === 'warning',

                            'bg-info-100 text-info-700':
                                item.type === 'info'
                        }"
                >

                        <span
                            x-text="
                                item.type === 'success'
                                    ? '✓'
                                    : item.type === 'danger'
                                        ? '!'
                                        : item.type === 'warning'
                                            ? '!'
                                            : 'i'
                            "
                        ></span>

                </div>


                <div
                    class="min-w-0 flex-1 text-sm text-foreground"
                    x-text="item.message"
                ></div>


                <button
                    type="button"
                    class="text-lg leading-none text-muted-foreground hover:text-foreground"
                    @click="$store.toast.remove(item.id)"
                    aria-label="بستن"
                >
                    ×
                </button>

            </div>

        </template>

    </div>

</div>


@stack('scripts')

</body>
</html>
