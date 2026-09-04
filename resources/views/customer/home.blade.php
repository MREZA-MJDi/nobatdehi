<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    data-theme="dark"
>
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta
        name="theme-color"
        content="#08090d"
    >

    <title>{{ $seoTitle }}</title>

    <meta
        name="description"
        content="{{ $seoDescription }}"
    >

    <link
        rel="canonical"
        href="{{ url('/') }}"
    >

    <meta
        name="robots"
        content="index,follow"
    >

    {{-- ==========================================================
        THEME
    =========================================================== --}}

    <script>
        (() => {
            document.documentElement.dataset.theme = 'dark';
        })();
    </script>


    {{-- ==========================================================
        GLOBAL ASSETS
    =========================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/css/customer.css',
    'resources/js/app.js',
    'resources/js/customer.js',
    ])


    {{-- ==========================================================
        HOME-ONLY STYLES
    =========================================================== --}}

    <style>
        .rm-home {
            --rm-purple: #7c5cff;
            --rm-violet: #9f7aea;
            --rm-cyan: #22d3ee;

            position: relative;
            min-height: 100svh;
            overflow: hidden;
            isolation: isolate;

            background:
                radial-gradient(
                    circle at 50% 42%,
                    rgba(124, 92, 255, .11),
                    transparent 24rem
                ),
                radial-gradient(
                    circle at 84% 12%,
                    rgba(34, 211, 238, .06),
                    transparent 20rem
                ),
                #08090d;

            color: #fff;
        }


        /* ======================================================
           AMBIENT BACKGROUND
        ======================================================= */

        .rm-home::before {
            content: "";

            position: absolute;
            inset: 0;

            pointer-events: none;

            background:
                radial-gradient(
                    circle at center,
                    rgba(255,255,255,.025),
                    transparent 40%
                );
        }


        .rm-home-grid {
            position: absolute;
            inset: -25%;

            pointer-events: none;

            opacity: .22;

            background-image:
                linear-gradient(
                    rgba(255,255,255,.035) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,.035) 1px,
                    transparent 1px
                );

            background-size:
                52px 52px;

            mask-image:
                radial-gradient(
                    ellipse at center,
                    black 10%,
                    transparent 72%
                );

            -webkit-mask-image:
                radial-gradient(
                    ellipse at center,
                    black 10%,
                    transparent 72%
                );

            animation:
                rm-home-grid 18s
                linear infinite;
        }


        @keyframes rm-home-grid {
            from {
                transform:
                    translate3d(0, 0, 0);
            }

            to {
                transform:
                    translate3d(52px, 52px, 0);
            }
        }


        /* ======================================================
           ORBS
        ======================================================= */

        .rm-home-orb {
            position: absolute;

            border-radius: 999px;

            pointer-events: none;

            filter: blur(90px);

            opacity: .5;

            will-change: transform;
        }


        .rm-home-orb-one {
            width: 28rem;
            height: 28rem;

            top: -10rem;
            right: -6rem;

            background:
                rgba(124, 92, 255, .17);

            animation:
                rm-home-orb-one
                12s
                ease-in-out
                infinite;
        }


        .rm-home-orb-two {
            width: 24rem;
            height: 24rem;

            bottom: -8rem;
            left: -4rem;

            background:
                rgba(34, 211, 238, .10);

            animation:
                rm-home-orb-two
                15s
                ease-in-out
                infinite;
        }


        .rm-home-orb-three {
            width: 20rem;
            height: 20rem;

            top: 38%;
            left: 45%;

            background:
                rgba(159, 122, 234, .08);

            animation:
                rm-home-orb-three
                10s
                ease-in-out
                infinite;
        }


        @keyframes rm-home-orb-one {
            0%,
            100% {
                transform:
                    translate3d(0,0,0)
                    scale(1);
            }

            50% {
                transform:
                    translate3d(-40px,50px,0)
                    scale(1.08);
            }
        }


        @keyframes rm-home-orb-two {
            0%,
            100% {
                transform:
                    translate3d(0,0,0)
                    scale(1);
            }

            50% {
                transform:
                    translate3d(60px,-40px,0)
                    scale(1.12);
            }
        }


        @keyframes rm-home-orb-three {
            0%,
            100% {
                transform:
                    translate3d(0,0,0)
                    scale(1);
            }

            50% {
                transform:
                    translate3d(-20px,30px,0)
                    scale(1.06);
            }
        }


        /* ======================================================
           NOISE
        ======================================================= */

        .rm-home-noise {
            position: absolute;
            inset: 0;

            pointer-events: none;

            opacity: .035;

            background-image:
                url("data:image/svg+xml,%3Csvg viewBox='0 0 160 160' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.7'/%3E%3C/svg%3E");

            mix-blend-mode: soft-light;
        }


        /* ======================================================
           TOP BRAND
        ======================================================= */

        .rm-home-topline {
            position: absolute;

            top: 1.5rem;
            right: 1.5rem;
            left: 1.5rem;

            z-index: 5;

            display: flex;

            align-items: center;
            justify-content: space-between;

            color:
                rgba(255,255,255,.45);

            font-size: .65rem;

            font-weight: 700;

            letter-spacing:
                .18em;

            text-transform:
                uppercase;
        }


        /* ======================================================
           STAGE
        ======================================================= */

        .rm-home-stage {
            position: relative;

            z-index: 5;

            display: flex;

            min-height: 100svh;

            flex-direction: column;

            align-items: center;
            justify-content: center;

            padding:
                6rem
                1.5rem
                7rem;

            text-align: center;
        }


        /* ======================================================
           ORBIT
        ======================================================= */

        .rm-home-orbit {
            position: absolute;

            top: 50%;
            left: 50%;

            width: min(76vw, 34rem);
            aspect-ratio: 1;

            border:
                1px solid
                rgba(255,255,255,.07);

            border-radius: 50%;

            transform:
                translate(-50%, -50%);

            animation:
                rm-home-orbit
                18s
                linear
                infinite;
        }


        .rm-home-orbit::before,
        .rm-home-orbit::after {
            content: "";

            position: absolute;

            inset: 12%;

            border-radius: 50%;

            border:
                1px dashed
                rgba(124,92,255,.10);
        }


        .rm-home-orbit::after {
            inset: 25%;

            border-style: solid;

            border-color:
                rgba(34,211,238,.08);
        }


        @keyframes rm-home-orbit {
            from {
                transform:
                    translate(-50%, -50%)
                    rotate(0deg);
            }

            to {
                transform:
                    translate(-50%, -50%)
                    rotate(360deg);
            }
        }


        .rm-home-orbit-node {
            position: absolute;

            width: .42rem;
            height: .42rem;

            border-radius: 50%;

            box-shadow:
                0 0 20px currentColor;
        }


        .rm-home-orbit-node-one {
            top: 3%;
            left: 50%;

            color:
                var(--rm-purple);

            background:
                currentColor;
        }


        .rm-home-orbit-node-two {
            bottom: 18%;
            right: 11%;

            color:
                var(--rm-cyan);

            background:
                currentColor;
        }


        .rm-home-orbit-node-three {
            bottom: 24%;
            left: 12%;

            color:
                var(--rm-violet);

            background:
                currentColor;
        }


        /* ======================================================
           RM MARK
        ======================================================= */

        .rm-home-mark {
            position: relative;

            display: flex;

            align-items: center;
            justify-content: center;

            width: 7.2rem;
            height: 7.2rem;

            margin-bottom: 2rem;

            border:
                1px solid
                rgba(255,255,255,.10);

            border-radius:
                2rem;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.08),
                    rgba(255,255,255,.025)
                );

            box-shadow:
                0 25px 80px
                rgba(0,0,0,.35),

                inset 0 1px 0
                rgba(255,255,255,.08);

            backdrop-filter:
                blur(18px);

            animation:
                rm-home-mark
                2.2s
                cubic-bezier(.22,.61,.36,1)
                both;
        }


        .rm-home-mark::before {
            content: "";

            position: absolute;

            inset: -1rem;

            border-radius: 2.6rem;

            border:
                1px solid
                rgba(124,92,255,.10);

            animation:
                rm-home-mark-ring
                2.4s
                ease-out
                infinite;
        }


        @keyframes rm-home-mark {
            from {
                opacity: 0;
                transform:
                    scale(.72)
                    translateY(14px);
                filter: blur(10px);
            }

            to {
                opacity: 1;
                transform:
                    scale(1)
                    translateY(0);
                filter: blur(0);
            }
        }


        @keyframes rm-home-mark-ring {
            0% {
                opacity: 0;
                transform:
                    scale(.82);
            }

            45% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform:
                    scale(1.12);
            }
        }


        .rm-home-mark-r,
        .rm-home-mark-m {
            font-size: 2.3rem;
            font-weight: 900;

            line-height: 1;
        }


        .rm-home-mark-r {
            color: #fff;
        }


        .rm-home-mark-m {
            margin-right: -.18rem;

            color:
                var(--rm-purple);

            text-shadow:
                0 0 28px
                rgba(124,92,255,.35);
        }


        /* ======================================================
           BRAND
        ======================================================= */

        .rm-home-brand {
            display: flex;

            flex-direction: column;

            align-items: center;

            animation:
                rm-home-brand
                2.2s
                .12s
                cubic-bezier(.22,.61,.36,1)
                both;
        }


        @keyframes rm-home-brand {
            from {
                opacity: 0;
                transform:
                    translateY(16px);
                filter: blur(8px);
            }

            to {
                opacity: 1;
                transform:
                    translateY(0);
                filter: blur(0);
            }
        }


        .rm-home-brand-small {
            color:
                rgba(255,255,255,.42);

            font-size: .62rem;

            font-weight: 700;

            letter-spacing:
                .24em;
        }


        .rm-home-brand-main {
            margin-top: .35rem;

            font-size:
                clamp(3rem, 10vw, 5.5rem);

            font-weight: 900;

            line-height: .9;

            letter-spacing:
                -.06em;
        }


        .rm-home-brand-sub {
            margin-top: .8rem;

            color:
                rgba(255,255,255,.76);

            font-size:
                clamp(.85rem, 2vw, 1rem);

            font-weight: 600;
        }


        /* ======================================================
           STORY
        ======================================================= */

        .rm-home-story {
            display: flex;

            max-width:
                34rem;

            margin-top:
                2.4rem;

            flex-direction: column;

            align-items: center;

            gap: .15rem;
        }


        .rm-home-line {
            margin: 0;

            color:
                rgba(255,255,255,.62);

            font-size:
                clamp(1rem, 2.6vw, 1.25rem);

            line-height: 1.9;

            animation:
                rm-home-line
                .8s
                cubic-bezier(.22,.61,.36,1)
                both;
        }


        .rm-home-line-one {
            animation-delay: .35s;
        }


        .rm-home-line-two {
            animation-delay: .52s;
        }


        .rm-home-line-three {
            animation-delay: .69s;
        }


        .rm-home-line-final {
            margin-top: .4rem;

            color: #fff;

            font-size:
                clamp(1.15rem, 3vw, 1.55rem);

            font-weight: 850;

            animation-delay: .9s;
        }


        @keyframes rm-home-line {
            from {
                opacity: 0;
                transform:
                    translateY(12px);
                filter:
                    blur(8px);
            }

            to {
                opacity: 1;
                transform:
                    translateY(0);
                filter:
                    blur(0);
            }
        }


        /* ======================================================
           PROGRESS
        ======================================================= */

        .rm-home-progress {
            width:
                min(16rem, 60vw);

            height: 2px;

            margin-top:
                2.6rem;

            overflow: hidden;

            border-radius:
                999px;

            background:
                rgba(255,255,255,.08);
        }


        .rm-home-progress-fill {
            display: block;

            width: 100%;
            height: 100%;

            transform-origin:
                right center;

            background:
                linear-gradient(
                    90deg,
                    var(--rm-cyan),
                    var(--rm-purple)
                );

            animation:
                rm-home-progress
                3s
                linear
                forwards;
        }


        @keyframes rm-home-progress {
            from {
                transform:
                    scaleX(0);
            }

            to {
                transform:
                    scaleX(1);
            }
        }


        /* ======================================================
           BOTTOM LABEL
        ======================================================= */

        .rm-home-bottom-label {
            display: flex;

            margin-top:
                1.4rem;

            align-items: center;

            gap: .7rem;

            color:
                rgba(255,255,255,.34);

            font-size: .68rem;

            font-weight: 650;
        }


        .rm-home-bottom-label span {
            display: inline-flex;

            align-items: center;

            gap: .7rem;
        }


        .rm-home-bottom-label span:not(:last-child)::after {
            content: "•";

            color:
                rgba(255,255,255,.16);
        }


        /* ======================================================
           SKIP
        ======================================================= */

        .rm-home-skip {
            position: absolute;

            left: 1.5rem;
            bottom: 1.5rem;

            z-index: 20;

            display: inline-flex;

            min-height: 2.5rem;

            align-items: center;
            justify-content: center;

            border:
                1px solid
                rgba(255,255,255,.10);

            border-radius:
                999px;

            padding:
                .55rem 1rem;

            background:
                rgba(255,255,255,.04);

            color:
                rgba(255,255,255,.62);

            font-size: .72rem;

            font-weight: 700;

            backdrop-filter:
                blur(16px);

            transition:
                transform .2s ease,
                background-color .2s ease,
                color .2s ease,
                border-color .2s ease;
        }


        .rm-home-skip:hover {
            transform:
                translateY(-2px);

            border-color:
                rgba(124,92,255,.25);

            background:
                rgba(124,92,255,.10);

            color:
                #fff;
        }


        /* ======================================================
           TIMER
        ======================================================= */

        .rm-home-timer {
            position: absolute;

            right: 1.5rem;
            bottom: 1.5rem;

            z-index: 20;

            display: grid;

            width: 2.5rem;
            height: 2.5rem;

            place-items: center;

            border:
                1px solid
                rgba(255,255,255,.08);

            border-radius: 50%;

            color:
                rgba(255,255,255,.55);

            font-size: .72rem;

            font-weight: 800;
        }


        /* ======================================================
           MOBILE
        ======================================================= */

        @media (max-width: 640px) {

            .rm-home-stage {
                padding-inline:
                    1rem;
            }

            .rm-home-topline {
                top: 1.1rem;
                right: 1rem;
                left: 1rem;
            }

            .rm-home-mark {
                width: 6.2rem;
                height: 6.2rem;
            }

            .rm-home-bottom-label {
                flex-wrap: wrap;
                justify-content: center;
            }

            .rm-home-skip {
                left: 1rem;
                bottom: 1rem;
            }

            .rm-home-timer {
                right: 1rem;
                bottom: 1rem;
            }
        }


        /* ======================================================
           REDUCED MOTION
        ======================================================= */

        @media (prefers-reduced-motion: reduce) {

            .rm-home-grid,
            .rm-home-orb,
            .rm-home-orbit,
            .rm-home-mark,
            .rm-home-mark::before,
            .rm-home-brand,
            .rm-home-line,
            .rm-home-progress-fill {
                animation: none !important;
            }

            .rm-home-mark,
            .rm-home-brand,
            .rm-home-line {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        }
    </style>

</head>


<body>

<main
    id="rm-home"
    class="rm-home"
    aria-label="RM نوبت‌دهی"
>

    {{-- ======================================================
        BACKGROUND
    ======================================================= --}}

    <div
        class="rm-home-grid"
        aria-hidden="true"
    ></div>

    <div
        class="rm-home-noise"
        aria-hidden="true"
    ></div>

    <div
        class="rm-home-orb rm-home-orb-one"
        aria-hidden="true"
    ></div>

    <div
        class="rm-home-orb rm-home-orb-two"
        aria-hidden="true"
    ></div>

    <div
        class="rm-home-orb rm-home-orb-three"
        aria-hidden="true"
    ></div>


    {{-- ======================================================
        TOP BRAND
    ======================================================= --}}

    <div class="rm-home-topline">

            <span>
                RM
            </span>

        <span>
                NOBATDEHI
            </span>

    </div>


    {{-- ======================================================
        MAIN STAGE
    ======================================================= --}}

    <section class="rm-home-stage">

        {{-- Orbit --}}
        <div
            class="rm-home-orbit"
            aria-hidden="true"
        >

                <span
                    class="rm-home-orbit-node rm-home-orbit-node-one"
                ></span>

            <span
                class="rm-home-orbit-node rm-home-orbit-node-two"
            ></span>

            <span
                class="rm-home-orbit-node rm-home-orbit-node-three"
            ></span>

        </div>


        {{-- RM Mark --}}
        <div
            class="rm-home-mark"
            aria-hidden="true"
        >

                <span class="rm-home-mark-r">
                    R
                </span>

            <span class="rm-home-mark-m">
                    M
                </span>

        </div>


        {{-- Brand --}}
        <div class="rm-home-brand">

                <span class="rm-home-brand-small">
                    FIND YOUR NEXT
                </span>

            <span class="rm-home-brand-main">
                    RM
                </span>

            <span class="rm-home-brand-sub">
                    نوبت‌دهی
                </span>

        </div>


        {{-- Story --}}
        <div class="rm-home-story">

            <p class="rm-home-line rm-home-line-one">
                بزن بریم.
            </p>

            <p class="rm-home-line rm-home-line-two">
                هر آرایشگاهی که خواستی.
            </p>

            <p class="rm-home-line rm-home-line-three">
                هر وقت که خواستی.
            </p>

            <strong class="rm-home-line rm-home-line-final">
                نوبتش با RM.
            </strong>

        </div>


        {{-- Progress --}}
        <div
            class="rm-home-progress"
            aria-hidden="true"
        >
            <span class="rm-home-progress-fill"></span>
        </div>


        {{-- Bottom Label --}}
        <div class="rm-home-bottom-label">

                <span>
                    پیدا کن.
                </span>

            <span>
                    انتخاب کن.
                </span>

            <span>
                    نوبت بگیر.
                </span>

        </div>

    </section>


    {{-- ======================================================
        SKIP
    ======================================================= --}}

    <a
        href="{{ $discoverUrl }}"
        class="rm-home-skip"
        id="rm-home-skip"
    >
        بزن بریم
    </a>


    {{-- ======================================================
        TIMER
    ======================================================= --}}

    <div
        class="rm-home-timer"
        aria-hidden="true"
    >
            <span id="rm-home-timer">
                3
            </span>
    </div>

</main>


{{-- ==========================================================
    REDIRECT SCRIPT
=========================================================== --}}

<script>
    (() => {

        const duration = 3000;

        const discoverUrl =
            @json($discoverUrl);

        const root =
            document.getElementById('rm-home');

        const timer =
            document.getElementById('rm-home-timer');

        const skip =
            document.getElementById('rm-home-skip');


        if (!root) {
            return;
        }


        const reducedMotion =
            window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches;


        if (reducedMotion) {

            window.setTimeout(() => {
                window.location.replace(
                    discoverUrl
                );
            }, 650);

            return;
        }


        const startedAt =
            performance.now();


        let rafId = null;


        const tick = (now) => {

            const elapsed =
                now - startedAt;

            const remaining =
                Math.max(
                    0,
                    duration - elapsed
                );

            const seconds =
                Math.max(
                    1,
                    Math.ceil(
                        remaining / 1000
                    )
                );


            if (timer) {
                timer.textContent =
                    String(seconds);
            }


            if (elapsed < duration) {

                rafId =
                    requestAnimationFrame(
                        tick
                    );

                return;
            }


            window.location.replace(
                discoverUrl
            );
        };


        rafId =
            requestAnimationFrame(tick);


        if (skip) {

            skip.addEventListener(
                'click',
                () => {

                    if (rafId) {
                        cancelAnimationFrame(
                            rafId
                        );
                    }

                },
                { once: true }
            );

        }

    })();
</script>

</body>
</html>
