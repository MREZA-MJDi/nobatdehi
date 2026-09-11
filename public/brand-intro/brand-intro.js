(() => {

    const intro = document.getElementById("brandIntro");
    const stage = document.getElementById("brandStage");

    if (!intro || !stage) {
        return;
    }


    /* =====================================================
       CONFIG
    ===================================================== */

    const INTRO_DURATION = 4250;
    const COLLAPSE_DURATION = 1050;

    const MAX_INTENSITY = 2.2;


    /* =====================================================
       STATE
    ===================================================== */

    let impactLevel = 0;

    let lastImpact = 0;

    let redirectStarted = false;

    const activePointers = new Set();


    /* =====================================================
       LUXURY IMPACT
    ===================================================== */

    function triggerLuxuryImpact() {

        const now = performance.now();

        /*
         * جلوگیری از ثبت چند Pointer Event
         * در یک فریم
         */

        if (now - lastImpact < 45) {
            return;
        }

        lastImpact = now;


        /*
         * شدت بر اساس:
         *
         * 1. تعداد ضربه‌های پشت‌سرهم
         * 2. Multi-touch
         */

        impactLevel = Math.min(
            impactLevel + 0.28,
            MAX_INTENSITY
        );


        const touchBoost = Math.min(
            activePointers.size * 0.18,
            0.7
        );


        const intensity = Math.min(
            1 + impactLevel + touchBoost,
            MAX_INTENSITY
        );


        /*
         * Desktop / Mobile یک Motion واحد
         * ولی شدت دینامیک
         */

        const keyframes = [

            {
                transform:
                    "translate3d(0,0,0) rotate(0deg) scale(1)"
            },

            /*
             * IMPACT
             */

            {
                transform:
                    `translate3d(${-9 * intensity}px, ${5 * intensity}px, 0)
                     rotate(${-0.32 * intensity}deg)
                     scale(${1 + 0.002 * intensity})`
            },

            /*
             * RECOIL
             */

            {
                transform:
                    `translate3d(${15 * intensity}px, ${-8 * intensity}px, 0)
                     rotate(${0.48 * intensity}deg)
                     scale(${1 - 0.003 * intensity})`
            },

            /*
             * SECONDARY HIT
             */

            {
                transform:
                    `translate3d(${-13 * intensity}px, ${-5 * intensity}px, 0)
                     rotate(${-0.52 * intensity}deg)
                     scale(${1 + 0.004 * intensity})`
            },

            /*
             * SETTLE
             */

            {
                transform:
                    `translate3d(${8 * intensity}px, ${6 * intensity}px, 0)
                     rotate(${0.32 * intensity}deg)
                     scale(${1 - 0.002 * intensity})`
            },

            {
                transform:
                    `translate3d(${-4 * intensity}px, ${-3 * intensity}px, 0)
                     rotate(${-0.14 * intensity}deg)
                     scale(1)`
            },

            {
                transform:
                    "translate3d(0,0,0) rotate(0deg) scale(1)"
            }
        ];


        const animation = stage.animate(
            keyframes,
            {
                duration:
                    720 + (intensity * 80),

                easing:
                    "cubic-bezier(0.22, 1, 0.36, 1)",

                fill: "none"
            }
        );


        /*
         * بعد از مدتی intensity طبیعی پایین میاد.
         */

        window.setTimeout(() => {

            impactLevel = Math.max(
                0,
                impactLevel - 0.16
            );

        }, 500);


        animation.finished
            .catch(() => {});
    }


    /* =====================================================
       POINTER DOWN
    ===================================================== */

    intro.addEventListener(
        "pointerdown",
        (event) => {

            activePointers.add(
                event.pointerId
            );

            triggerLuxuryImpact();

        },
        {
            passive: true
        }
    );


    /* =====================================================
       POINTER UP
    ===================================================== */

    intro.addEventListener(
        "pointerup",
        (event) => {

            activePointers.delete(
                event.pointerId
            );

        },
        {
            passive: true
        }
    );


    intro.addEventListener(
        "pointercancel",
        (event) => {

            activePointers.delete(
                event.pointerId
            );

        },
        {
            passive: true
        }
    );


    intro.addEventListener(
        "pointerleave",
        (event) => {

            activePointers.delete(
                event.pointerId
            );

        },
        {
            passive: true
        }
    );


    /* =====================================================
       REDIRECT
    ===================================================== */

    function redirectToDiscovery() {

        /*
         * جلوگیری از اجرای چندباره Redirect
         */

        if (redirectStarted) {
            return;
        }

        redirectStarted = true;


        /*
         * Route واقعی Laravel
         */

        const destination =
            window.NOBAT_DISCOVERY_URL;


        /*
         * اگر URL از Blade دریافت نشده بود،
         * یک fallback داشته باشیم.
         */

        if (!destination) {

            console.error(
                "NOBAT_DISCOVERY_URL is not defined."
            );

            window.location.replace(
                "/salons/discover"
            );

            return;
        }


        /*
         * Redirect نهایی
         *
         * replace باعث می‌شود کاربر با Back
         * دوباره به Intro برنگردد.
         */

        window.location.replace(
            destination
        );
    }


    /* =====================================================
       COLLAPSE
    ===================================================== */

    window.setTimeout(() => {

        /*
         * شروع Collapse
         */

        intro.classList.add(
            "is-collapsing"
        );


        /*
         * صبر می‌کنیم Collapse کامل شود
         * سپس وارد Discovery می‌شویم.
         */

        window.setTimeout(() => {

            redirectToDiscovery();

        }, COLLAPSE_DURATION);

    }, INTRO_DURATION);


})();
