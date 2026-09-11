document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Shared state
    |--------------------------------------------------------------------------
    */

    const prefersReducedMotion =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches


    const hoverSupported =
        window.matchMedia(
            '(hover: hover) and (pointer: fine)'
        ).matches


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const formatPersianNumber = (value) => {

        return new Intl.NumberFormat(
            'fa-IR'
        ).format(
            Number(value) || 0
        )

    }


    const showToast = (
        message,
        type = 'error'
    ) => {

        const toast =
            window.toast

        if (
            toast &&
            typeof toast[type] === 'function'
        ) {

            toast[type](message)

            return
        }


        window.alert(message)

    }


    const revealElement = (element) => {

        const delay =
            Number(
                element.dataset.blurFadeDelay || 0
            )


        element.style.transition = [
            'opacity .55s cubic-bezier(.22,.61,.36,1)',
            'filter .55s cubic-bezier(.22,.61,.36,1)',
            'transform .55s cubic-bezier(.22,.61,.36,1)',
        ].join(',')


        element.style.transitionDelay =
            `${delay}s`


        element.style.opacity = '1'
        element.style.filter = 'blur(0)'
        element.style.transform =
            'translate3d(0,0,0)'

    }


    /*
    |--------------------------------------------------------------------------
    | Blur Fade
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-blur-fade]')
        .forEach((element) => {

            if (prefersReducedMotion) {

                element.style.opacity = '1'
                element.style.filter = 'none'
                element.style.transform = 'none'

                return
            }


            element.style.opacity = '0'
            element.style.filter = 'blur(8px)'
            element.style.transform =
                'translate3d(0,14px,0)'


            if (
                !('IntersectionObserver' in window)
            ) {

                revealElement(element)

                return
            }


            const observer =
                new IntersectionObserver(
                    (entries, currentObserver) => {

                        entries.forEach((entry) => {

                            if (
                                !entry.isIntersecting
                            ) {
                                return
                            }


                            revealElement(element)

                            currentObserver.unobserve(
                                element
                            )

                        })

                    },
                    {
                        rootMargin:
                            '0px 0px -10% 0px',
                    }
                )


            observer.observe(element)

        })


    /*
    |--------------------------------------------------------------------------
    | Spotlight
    |--------------------------------------------------------------------------
    */

    if (!prefersReducedMotion) {

        document
            .querySelectorAll('[data-spotlight-card]')
            .forEach((card) => {

                card.addEventListener(
                    'pointermove',
                    (event) => {

                        const rect =
                            card.getBoundingClientRect()


                        card.style.setProperty(
                            '--spot-x',
                            `${event.clientX - rect.left}px`
                        )


                        card.style.setProperty(
                            '--spot-y',
                            `${event.clientY - rect.top}px`
                        )

                    },
                    {
                        passive: true,
                    }
                )

            })

    }


    /*
    |--------------------------------------------------------------------------
    | Number Ticker
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-number-ticker]')
        .forEach((element) => {

            const output =
                element.querySelector('strong')


            if (!output) {
                return
            }


            const value =
                Number(
                    element.dataset.numberTickerValue || 0
                )


            if (
                prefersReducedMotion ||
                !('IntersectionObserver' in window)
            ) {

                output.textContent =
                    formatPersianNumber(value)

                return
            }


            let started = false


            const start = () => {

                if (started) {
                    return
                }


                started = true


                const duration = 900
                const startedAt =
                    performance.now()


                const frame = (now) => {

                    const progress =
                        Math.min(
                            1,
                            (
                                now -
                                startedAt
                            ) / duration
                        )


                    const eased =
                        1 -
                        Math.pow(
                            1 - progress,
                            3
                        )


                    const current =
                        Math.round(
                            value * eased
                        )


                    output.textContent =
                        formatPersianNumber(
                            current
                        )


                    if (
                        progress < 1
                    ) {

                        requestAnimationFrame(
                            frame
                        )

                    }

                }


                requestAnimationFrame(
                    frame
                )

            }


            const observer =
                new IntersectionObserver(
                    (entries, currentObserver) => {

                        const visible =
                            entries.some(
                                entry =>
                                    entry.isIntersecting
                            )


                        if (!visible) {
                            return
                        }


                        start()

                        currentObserver.disconnect()

                    },
                    {
                        rootMargin:
                            '0px 0px -10% 0px',
                    }
                )


            observer.observe(element)

        })


    /*
    |--------------------------------------------------------------------------
    | Tilt
    |--------------------------------------------------------------------------
    */

    if (
        hoverSupported &&
        !prefersReducedMotion
    ) {

        document
            .querySelectorAll('[data-tilt-card]')
            .forEach((card) => {

                const maxTilt =
                    Number(
                        card.dataset.tiltMax || 6
                    )


                let frameId = null
                let pointerX = 0
                let pointerY = 0


                const reset = () => {

                    if (
                        frameId !== null
                    ) {

                        cancelAnimationFrame(
                            frameId
                        )

                        frameId = null

                    }


                    card.style.transform =
                        'perspective(800px) rotateX(0deg) rotateY(0deg) translateZ(0)'

                }


                const render = () => {

                    frameId = null


                    const rect =
                        card.getBoundingClientRect()


                    if (
                        !rect.width ||
                        !rect.height
                    ) {
                        return
                    }


                    const px =
                        (
                            pointerX -
                            rect.left
                        ) /
                        rect.width -
                        0.5


                    const py =
                        (
                            pointerY -
                            rect.top
                        ) /
                        rect.height -
                        0.5


                    const rotateX =
                        -py *
                        maxTilt *
                        2


                    const rotateY =
                        px *
                        maxTilt *
                        2


                    card.style.transform =
                        `
perspective(800px)
rotateX(${rotateX.toFixed(2)}deg)
rotateY(${rotateY.toFixed(2)}deg)
translateZ(0)
    `

                }


                const queueRender = () => {

                    if (
                        frameId !== null
                    ) {
                        return
                    }


                    frameId =
                        requestAnimationFrame(
                            render
                        )

                }


                card.addEventListener(
                    'pointermove',
                    (event) => {

                        pointerX =
                            event.clientX

                        pointerY =
                            event.clientY

                        queueRender()

                    },
                    {
                        passive: true,
                    }
                )


                card.addEventListener(
                    'pointerleave',
                    reset
                )


                card.addEventListener(
                    'pointercancel',
                    reset
                )

            })

    }


    /*
    |--------------------------------------------------------------------------
    | Discover Location
    |--------------------------------------------------------------------------
    */

    const locationButton =
        document.querySelector(
            '#discover-location'
        )


    const latitudeInput =
        document.querySelector(
            '#discover-latitude'
        )


    const longitudeInput =
        document.querySelector(
            '#discover-longitude'
        )


    if (
        !locationButton ||
        !latitudeInput ||
        !longitudeInput
    ) {

        return

    }


    locationButton.addEventListener(
        'click',
        () => {

            if (
                !navigator.geolocation
            ) {

                showToast(
                    'مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.',
                    'error'
                )

                return

            }


            if (
                locationButton.disabled
            ) {
                return
            }


            locationButton.disabled = true

            locationButton.classList.add(
                'is-loading'
            )


            navigator.geolocation.getCurrentPosition(

                (position) => {

                    const latitude =
                        Number(
                            position.coords.latitude
                        )


                    const longitude =
                        Number(
                            position.coords.longitude
                        )


                    if (
                        !Number.isFinite(latitude) ||
                        !Number.isFinite(longitude)
                    ) {

                        locationButton.disabled =
                            false

                        locationButton.classList.remove(
                            'is-loading'
                        )

                        showToast(
                            'مختصات موقعیت مکانی معتبر نیست.',
                            'error'
                        )

                        return
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Save coordinates
                    |--------------------------------------------------------------------------
                    */

                    latitudeInput.value =
                        latitude

                    longitudeInput.value =
                        longitude


                    /*
                    |--------------------------------------------------------------------------
                    | Find form
                    |--------------------------------------------------------------------------
                    */

                    const form =
                        locationButton.closest(
                            'form'
                        )


                    if (!form) {

                        locationButton.disabled =
                            false

                        locationButton.classList.remove(
                            'is-loading'
                        )

                        showToast(
                            'فرم جستجوی سالن پیدا نشد.',
                            'error'
                        )

                        return
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Set nearest sorting
                    |--------------------------------------------------------------------------
                    */

                    let sortInput =
                        form.querySelector(
                            'input[name="sort"]'
                        )


                    if (!sortInput) {

                        sortInput =
                            document.createElement(
                                'input'
                            )


                        sortInput.type =
                            'hidden'


                        sortInput.name =
                            'sort'


                        form.appendChild(
                            sortInput
                        )

                    }


                    sortInput.value =
                        'nearest'


                    /*
                    |--------------------------------------------------------------------------
                    | Submit
                    |--------------------------------------------------------------------------
                    */

                    form.submit()

                },


                (error) => {

                    locationButton.disabled =
                        false


                    locationButton.classList.remove(
                        'is-loading'
                    )


                    let message =
                        'دسترسی به موقعیت مکانی فعال نشد.'


                    switch (error.code) {

                        case error.PERMISSION_DENIED:

                            message =
                                'برای پیدا کردن سالن‌های نزدیک، اجازه دسترسی به موقعیت مکانی را فعال کن.'

                            break


                        case error.POSITION_UNAVAILABLE:

                            message =
                                'موقعیت مکانی شما در دسترس نیست.'

                            break


                        case error.TIMEOUT:

                            message =
                                'دریافت موقعیت مکانی طول کشید. دوباره امتحان کن.'

                            break

                    }


                    showToast(
                        message,
                        'error'
                    )

                },


                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 300000,
                }

            )

        }
    )

})
