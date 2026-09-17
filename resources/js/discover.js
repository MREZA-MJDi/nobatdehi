(() => {
    'use strict';

    const page = document.querySelector('.discover-page');

    if (!page) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Smooth anchors
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        '.discover-page a[href*="#"]'
    ).forEach((link) => {
        link.addEventListener('click', (event) => {
            const url = new URL(
                link.href,
                window.location.origin
            );

            if (
                url.pathname !== window.location.pathname ||
                !url.hash
            ) {
                return;
            }

            const target = document.querySelector(
                url.hash
            );

            if (!target) {
                return;
            }

            event.preventDefault();

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });

            history.replaceState(
                null,
                '',
                url.hash
            );
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Province -> City
    |--------------------------------------------------------------------------
    */

    const province = document.querySelector(
        '#discover-filter-province'
    );

    const city = document.querySelector(
        '#discover-filter-city'
    );

    if (province && city) {
        province.addEventListener(
            'change',
            () => {
                if (!province.value) {
                    return;
                }

                city.disabled = true;
                city.form?.submit();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Geolocation
    |--------------------------------------------------------------------------
    */

    const locationButton = document.querySelector(
        '[data-discover-location]'
    );

    if (locationButton) {
        locationButton.addEventListener(
            'click',
            () => {
                if (!navigator.geolocation) {
                    window.alert(
                        'مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.'
                    );

                    return;
                }

                locationButton.disabled = true;
                locationButton.dataset.loading = 'true';
                locationButton.textContent = 'در حال پیدا کردن...';

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const url = new URL(
                            window.location.href
                        );

                        url.searchParams.set(
                            'lat',
                            position.coords.latitude
                        );

                        url.searchParams.set(
                            'lng',
                            position.coords.longitude
                        );

                        url.searchParams.set(
                            'radius',
                            '15'
                        );

                        url.searchParams.set(
                            'sort',
                            'distance'
                        );

                        url.hash = 'results';

                        window.location.href =
                            url.toString();
                    },

                    () => {
                        locationButton.disabled = false;
                        locationButton.dataset.loading = 'false';
                        locationButton.textContent = 'نزدیک من';

                        window.alert(
                            'دسترسی به موقعیت مکانی انجام نشد.'
                        );
                    },

                    {
                        enableHighAccuracy: false,
                        timeout: 8000,
                        maximumAge: 300000,
                    }
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search enter UX
    |--------------------------------------------------------------------------
    */

    const heroSearch = document.querySelector(
        '.discover-hero-search input[name="q"]'
    );

    if (heroSearch) {
        heroSearch.addEventListener(
            'focus',
            () => {
                page.classList.add(
                    'discover-search-focused'
                );
            }
        );

        heroSearch.addEventListener(
            'blur',
            () => {
                page.classList.remove(
                    'discover-search-focused'
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll reveal
    |--------------------------------------------------------------------------
    */

    const revealItems = document.querySelectorAll(
        '.discover-result-card, ' +
        '.discover-salon-card, ' +
        '.discover-service-card, ' +
        '.discover-stylist-card'
    );

    if (
        'IntersectionObserver' in window &&
        revealItems.length
    ) {
        const observer =
            new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (
                            !entry.isIntersecting
                        ) {
                            return;
                        }

                        entry.target.classList.add(
                            'is-visible'
                        );

                        observer.unobserve(
                            entry.target
                        );
                    });
                },
                {
                    threshold: 0.08,
                    rootMargin: '0px 0px -40px 0px',
                }
            );

        revealItems.forEach((item) => {
            observer.observe(item);
        });
    }
})();
