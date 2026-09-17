(() => {
    'use strict';

    const intro = document.getElementById('brandIntro');
    const enterButton = document.getElementById('brandEnter');

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

    const INTRO_TIME = 4200;
    const EXIT_TIME = 320;


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
        enterButton.addEventListener('click', goToDiscover);
    }


    /*
    |--------------------------------------------------------------------------
    | CLICK ANYWHERE
    |--------------------------------------------------------------------------
    */

    intro.addEventListener('click', (event) => {
        if (
            event.target.closest('#brandEnter')
        ) {
            return;
        }

        goToDiscover();
    });


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
