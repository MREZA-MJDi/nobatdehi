document.addEventListener('DOMContentLoaded', () => {
    /*
    |--------------------------------------------------------------------------
    | Blur Fade
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-blur-fade]')
        .forEach((element) => {

            const reduced =
                window.matchMedia(
                    '(prefers-reduced-motion: reduce)'
                ).matches;

            if (reduced) {
                element.style.opacity = '1';
                element.style.filter = 'none';
                return;
            }

            element.style.opacity = '0';
            element.style.filter = 'blur(8px)';
            element.style.transform =
                'translate3d(0, 14px, 0)';

            const observer =
                new IntersectionObserver(
                    (entries) => {

                        entries.forEach(
                            (entry) => {

                                if (
                                    !entry.isIntersecting
                                ) {
                                    return;
                                }

                                const delay =
                                    Number(
                                        element.dataset
                                            .blurFadeDelay
                                        || 0
                                    );

                                element.style.transition = [
                                    'opacity .55s cubic-bezier(.22,.61,.36,1)',
                                    'filter .55s cubic-bezier(.22,.61,.36,1)',
                                    'transform .55s cubic-bezier(.22,.61,.36,1)',
                                ].join(',');

                                element.style.transitionDelay =
                                    `${delay}s`;

                                element.style.opacity =
                                    '1';

                                element.style.filter =
                                    'blur(0)';

                                element.style.transform =
                                    'translate3d(0,0,0)';

                                observer.unobserve(
                                    element
                                );
                            }
                        );
                    },
                    {
                        rootMargin:
                            '0px 0px -10% 0px',
                    }
                );

            observer.observe(element);
        });


    /*
    |--------------------------------------------------------------------------
    | Spotlight
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-spotlight-card]')
        .forEach((card) => {

            card.addEventListener(
                'pointermove',
                (event) => {

                    const rect =
                        card.getBoundingClientRect();

                    card.style.setProperty(
                        '--spot-x',
                        `${event.clientX - rect.left}px`
                    );

                    card.style.setProperty(
                        '--spot-y',
                        `${event.clientY - rect.top}px`
                    );
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Number Ticker
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-number-ticker]'
        )
        .forEach((element) => {

            const value =
                Number(
                    element.dataset
                        .numberTickerValue
                    || 0
                );

            const reduced =
                window.matchMedia(
                    '(prefers-reduced-motion: reduce)'
                ).matches;

            if (reduced) {
                element.querySelector('strong')
                    .textContent =
                    new Intl.NumberFormat(
                        'fa-IR'
                    ).format(value);

                return;
            }

            let started = false;


            const start =
                () => {

                    if (started) {
                        return;
                    }

                    started = true;


                    const output =
                        element.querySelector(
                            'strong'
                        );

                    if (!output) {
                        return;
                    }


                    const duration = 900;
                    const startedAt =
                        performance.now();


                    const frame =
                        (now) => {

                            const progress =
                                Math.min(
                                    1,
                                    (
                                        now -
                                        startedAt
                                    ) / duration
                                );

                            const eased =
                                1 -
                                Math.pow(
                                    1 -
                                    progress,
                                    3
                                );

                            const current =
                                Math.round(
                                    value *
                                    eased
                                );

                            output.textContent =
                                new Intl.NumberFormat(
                                    'fa-IR'
                                ).format(
                                    current
                                );

                            if (progress < 1) {
                                requestAnimationFrame(
                                    frame
                                );
                            }
                        };


                    requestAnimationFrame(
                        frame
                    );
                };


            const observer =
                new IntersectionObserver(
                    (entries) => {

                        if (
                            entries.some(
                                entry =>
                                    entry.isIntersecting
                            )
                        ) {

                            start();

                            observer.disconnect();
                        }
                    },
                    {
                        rootMargin:
                            '0px 0px -10% 0px',
                    }
                );

            observer.observe(
                element
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Tilt
    |--------------------------------------------------------------------------
    */

    const hoverSupported =
        window.matchMedia(
            '(hover: hover) and (pointer: fine)'
        ).matches;

    const reduced =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;


    if (
        hoverSupported &&
        !reduced
    ) {

        document
            .querySelectorAll(
                '[data-tilt-card]'
            )
            .forEach((card) => {

                const maxTilt =
                    Number(
                        card.dataset
                            .tiltMax
                        || 6
                    );

                card.addEventListener(
                    'pointermove',
                    (event) => {

                        const rect =
                            card.getBoundingClientRect();

                        const px =
                            (
                                event.clientX -
                                rect.left
                            ) /
                            rect.width -
                            .5;

                        const py =
                            (
                                event.clientY -
                                rect.top
                            ) /
                            rect.height -
                            .5;

                        card.style.transform =
                            `
                                perspective(800px)
                                rotateX(${(-py * maxTilt * 2).toFixed(2)}deg)
                                rotateY(${(px * maxTilt * 2).toFixed(2)}deg)
                                translateZ(0)
                            `;
                    }
                );


                card.addEventListener(
                    'pointerleave',
                    () => {

                        card.style.transform =
                            'perspective(800px) rotateX(0deg) rotateY(0deg)';
                    }
                );
            });
    }
});
