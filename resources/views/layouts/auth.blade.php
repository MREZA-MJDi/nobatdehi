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
        @yield('title', 'ورود') | نوبت‌دهی
    </title>

    <meta
        name="description"
        content="@yield(
            'meta_description',
            'ورود و ثبت‌نام در سامانه نوبت‌دهی'
        )"
    >

    <meta
        name="theme-color"
        content="#6757E8"
    >

    {{-- =========================================================
        CORE VITE ASSETS
    ========================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    {{-- =========================================================
        PAGE-SPECIFIC HEAD
    ========================================================== --}}

    @stack('head')

</head>


<body class="min-h-screen">

<div class="relative min-h-screen overflow-hidden">

    {{-- =====================================================
        BACKGROUND DECORATIONS
    ====================================================== --}}

    <div
        class="
                pointer-events-none
                absolute
                inset-0
                overflow-hidden
            "
        aria-hidden="true"
    >

        <div
            class="
                    absolute
                    -right-32
                    -top-32
                    h-96
                    w-96
                    rounded-full
                    bg-accent-500/10
                    blur-3xl
                "
        ></div>


        <div
            class="
                    absolute
                    -bottom-40
                    -left-32
                    h-[28rem]
                    w-[28rem]
                    rounded-full
                    bg-cyan-400/10
                    blur-3xl
                "
        ></div>


        <div
            class="
                    absolute
                    left-1/2
                    top-1/2
                    h-72
                    w-72
                    -translate-x-1/2
                    -translate-y-1/2
                    rounded-full
                    bg-accent-400/5
                    blur-3xl
                "
        ></div>

    </div>


    {{-- =====================================================
        MAIN
    ====================================================== --}}

    <main
        class="
                relative
                flex
                min-h-screen
                items-center
                justify-center
                px-4
                py-8
                sm:px-6
            "
    >

        <div class="w-full max-w-md">

            {{-- =================================================
                BRAND
            ================================================== --}}

            <div class="mb-7 text-center">

                <a
                    href="{{ route('brand.intro') }}"
                    class="group inline-flex items-center gap-3"
                >

                        <span
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-2xl
                                bg-primary-950
                                text-base
                                font-black
                                text-white
                                shadow-lg
                                transition
                                duration-200
                                group-hover:-translate-y-1
                                group-hover:shadow-xl
                            "
                        >
                            ن
                        </span>


                    <span class="text-right">

                            <span
                                class="
                                    block
                                    text-base
                                    font-black
                                    text-content
                                "
                            >
                                نوبت‌دهی
                            </span>


                            <span
                                class="
                                    mt-0.5
                                    block
                                    text-[10px]
                                    font-medium
                                    text-content-muted
                                "
                            >
                                رزرو آسان، تجربه بهتر
                            </span>

                        </span>

                </a>

            </div>


            {{-- =================================================
                SUCCESS FLASH
            ================================================== --}}

            @if(session('success'))

                <div
                    class="
                            mb-4
                            flex
                            items-start
                            gap-3
                            rounded-2xl
                            border
                            border-success-100
                            bg-success-50
                            px-4
                            py-3
                            shadow-soft
                        "
                    role="status"
                >

                    <div
                        class="
                                mt-0.5
                                flex
                                h-7
                                w-7
                                shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                bg-success-100
                                text-success-700
                            "
                        aria-hidden="true"
                    >
                        ✓
                    </div>


                    <div
                        class="
                                text-xs
                                font-bold
                                leading-6
                                text-success-700
                            "
                    >
                        {{ session('success') }}
                    </div>

                </div>

            @endif


            {{-- =================================================
                STATUS FLASH
            ================================================== --}}

            @if(session('status'))

                <div
                    class="
                            mb-4
                            flex
                            items-start
                            gap-3
                            rounded-2xl
                            border
                            border-accent-100
                            bg-accent-50
                            px-4
                            py-3
                            shadow-soft
                        "
                    role="status"
                >

                    <div
                        class="
                                mt-0.5
                                flex
                                h-7
                                w-7
                                shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                bg-accent-100
                                text-accent-700
                            "
                        aria-hidden="true"
                    >
                        !
                    </div>


                    <div
                        class="
                                text-xs
                                font-bold
                                leading-6
                                text-accent-700
                            "
                    >
                        {{ session('status') }}
                    </div>

                </div>

            @endif


            {{-- =================================================
                PAGE CONTENT
            ================================================== --}}

            <div
                class="
                        overflow-hidden
                        rounded-[2rem]
                        border
                        border-white/80
                        bg-white/90
                        shadow-float
                        backdrop-blur-xl
                    "
            >

                @yield('content')

            </div>


            {{-- =================================================
                FOOTER
            ================================================== --}}

            <div class="mt-6 text-center">

                <div
                    class="
                            text-[10px]
                            font-medium
                            text-content-faint
                        "
                >
                    © {{ now()->year }} نوبت‌دهی
                </div>


                <div
                    class="
                            mt-1
                            text-[9px]
                            text-content-faint
                        "
                >
                    رزرو سریع و ساده خدمات موردنظر شما
                </div>

            </div>

        </div>

    </main>

</div>


{{-- =========================================================
    PAGE SCRIPTS
========================================================== --}}

@stack('scripts')

</body>

</html>
