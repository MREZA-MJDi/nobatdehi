<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    class="brand-intro-html"
>
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta
        name="theme-color"
        content="#090908"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <title>
        NOBAT
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        rel="stylesheet"
        href="{{ asset('brand-intro/brand-intro.css') }}"
    >

</head>

<body>

<main
    id="brandIntro"
    class="brand-intro"
    aria-label="NOBAT"
>

    <div
        class="brand-stage"
        id="brandStage"
    >

        {{-- =====================================================
            TOP LEFT EDITORIAL INFO
        ====================================================== --}}

        <div class="brand-meta brand-meta-top">

            <span>
                NOBAT
            </span>

            <i></i>

            <span>
                01
            </span>

        </div>


        {{-- =====================================================
            MAIN BRAND
        ====================================================== --}}

        <div class="brand-composition">

            <div class="brand-word-wrap">

                <div
                    class="kinetic-frame"
                    aria-hidden="true"
                >

                    <span class="line line-left"></span>
                    <span class="line line-right"></span>

                    <span class="line line-top"></span>

                    <span class="line line-bottom-left"></span>
                    <span class="line line-bottom-right"></span>

                </div>


                <span class="brand-word">
                    NOBAT
                </span>

            </div>


            <p
                class="brand-tagline"
                dir="rtl"
            >
                پیدا کن، انتخاب کن، نوبت بگیر.
            </p>

        </div>


        {{-- =====================================================
            BOTTOM RIGHT EDITORIAL INFO
        ====================================================== --}}

        <div class="brand-meta brand-meta-bottom">

            <span>
                DISCOVER
            </span>

            <i></i>

            <span>
                APPOINTMENT
            </span>

        </div>


        {{-- =====================================================
            SKIP / ENTER
        ====================================================== --}}

        <button
            type="button"
            class="brand-enter"
            id="brandEnter"
        >

            <span class="brand-enter-label">
                ورود به NOBAT
            </span>

            <span
                class="brand-enter-arrow"
                aria-hidden="true"
            >
                ←
            </span>

        </button>


        {{-- =====================================================
            PROGRESS
        ====================================================== --}}

        <div
            class="brand-progress"
            aria-hidden="true"
        >

            <span
                id="brandProgressBar"
            ></span>

        </div>

    </div>

</main>


<script>
    window.NOBAT_DISCOVERY_URL =
    @json(route('salons.discover'));
</script>

<script
    src="{{ asset('brand-intro/brand-intro.js') }}"
    defer
></script>

</body>
</html>
