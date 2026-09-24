(() => {
    'use strict';

    const intro = document.getElementById('brandIntro');
    const enterButton = document.getElementById('brandEnter');
    const progressBar = document.getElementById('brandProgressBar');

    if (!intro) {
        return;
    }

    const destination =
        window.NOBAT_DISCOVERY_URL ||
        '/salons/discover';

    let redirected = false;

    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    const reducedMotion =
        window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        ).matches ?? false;

    const INTRO_TIME =
        reducedMotion
            ? 900
            : 2400;

    const EXIT_TIME = 320;


    /*
    |--------------------------------------------------------------------------
    | PROGRESS
    |--------------------------------------------------------------------------
    */

    intro.style.setProperty(
        '--intro-duration',
        `${INTRO_TIME}ms`
    );

    if (progressBar) {
        progressBar.style.animationDuration =
            `${INTRO_TIME}ms`;
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    function goToDiscover() {
        if (redirected) {
            return;
        }

        redirected = true;

        if (enterButton) {
            enterButton.disabled = true;
        }

        intro.classList.add('is-exiting');

        window.setTimeout(() => {
            window.location.assign(destination);
        }, EXIT_TIME);
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    if (enterButton) {
        enterButton.addEventListener(
            'click',
            goToDiscover
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KEYBOARD
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Enter' ||
            event.key === ' '
        ) {
            event.preventDefault();

            goToDiscover();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | AUTO ENTER
    |--------------------------------------------------------------------------
    */

    window.setTimeout(
        goToDiscover,
        INTRO_TIME
    );

})();
