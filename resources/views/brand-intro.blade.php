<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta name="theme-color" content="#090908">

    <title>NOBAT</title>

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

    <div class="brand-stage" id="brandStage">

        <div class="editorial-index">
            <span>01</span>
            <i></i>
            <span>RMCO</span>
        </div>


        <div class="brand-composition">

            <div class="brand-word-wrap">

                <span class="brand-word">
                    NOBAT
                </span>

                <div
                    class="kinetic-frame"
                    aria-hidden="true"
                >
                    <span class="line line-left"></span>
                    <span class="line line-right"></span>

                    <span class="line line-bottom-left"></span>
                    <span class="line line-bottom-right"></span>
                </div>

            </div>


            <div
                class="brand-tagline"
                dir="rtl"
            >
                با ما چرخه نوبت‌دهی تغییر کرد
            </div>

        </div>


        <div class="editorial-corner">

            <span>APPOINTMENT</span>

            <span>01 — 26</span>

        </div>

    </div>

</main>


<script>
    window.NOBAT_DISCOVERY_URL = @json(route('salons.discover'));
</script>

<script src="{{ asset('brand-intro/brand-intro.js') }}"></script>

</body>
</html>
