<!DOCTYPE html>
<html
    lang="en"
    dir="ltr"
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
        content="#080809"
    >

    <meta
        name="color-scheme"
        content="dark"
    >

    <meta
        name="robots"
        content="index,follow"
    >

    <meta
        name="description"
        content="NOBAT — discover salons, choose your service, and book your appointment."
    >

    <link
        rel="canonical"
        href="{{ route('salons.discover') }}"
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

        <div class="editorial-index" aria-hidden="true">

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

            <div class="brand-kicker">
                <span>BEAUTY APPOINTMENTS</span>
                <span class="brand-kicker-rule"></span>
                <span>EST. 2026</span>
            </div>

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


            <p class="brand-tagline">
                DISCOVER. CHOOSE. BOOK.
            </p>

            <p class="brand-subtitle">
                Find salons, services, and available time slots — all in one place.
            </p>

        </div>


        {{-- =====================================================
            BOTTOM RIGHT EDITORIAL INFO
        ====================================================== --}}

        <div class="editorial-corner" aria-hidden="true">

            <span>
                DISCOVER
            </span>

            <i></i>

            <span>
                BOOK
            </span>

            <i></i>

            <span>
                EXPERIENCE
            </span>

        </div>


        {{-- =====================================================
            SKIP / ENTER
        ====================================================== --}}

        <button
            type="button"
            class="brand-enter"
            id="brandEnter"
            aria-label="Explore NOBAT"
        >

            <span class="brand-enter-label">
                Explore NOBAT
            </span>

            <span
                class="brand-enter-arrow"
                aria-hidden="true"
            >
                ↗
            </span>

        </button>


        {{-- =====================================================
            PROGRESS
        ====================================================== --}}

        <div
            class="brand-progress"
            aria-hidden="true"
        >
            <span id="brandProgressBar"></span>
        </div>

        <div class="brand-footer-meta" aria-hidden="true">
            <span>CLIENTS</span>
            <i></i>
            <span>SALONS</span>
            <i></i>
            <span>APPOINTMENTS</span>
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
