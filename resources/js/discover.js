(() => {
    'use strict';

    const page = document.querySelector('.discover-page');

    if (!page) {
        return;
    }

    const prefersReducedMotion = window.matchMedia?.(
        '(prefers-reduced-motion: reduce)'
    )?.matches;

    const scrollToTarget = (target) => {
        if (!target) {
            return;
        }

        target.scrollIntoView({
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
            block: 'start',
        });
    };

    /* ----------------------------------------------------------------------
       Same-page navigation
       ---------------------------------------------------------------------- */

    page.querySelectorAll('a[href*="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const url = new URL(link.href, window.location.origin);

            if (
                url.pathname !== window.location.pathname ||
                !url.hash ||
                url.search !== window.location.search
            ) {
                return;
            }

            const target = page.querySelector(url.hash);

            if (!target) {
                return;
            }

            event.preventDefault();
            scrollToTarget(target);

            if (!prefersReducedMotion) {
                history.replaceState(null, '', url.hash);
            }
        });
    });

    /* ----------------------------------------------------------------------
       Location / nearby
       ---------------------------------------------------------------------- */

    const locationButton =
        page.querySelector('#discoverUseLocation') ||
        page.querySelector('[data-discover-location]');

    if (locationButton) {
        locationButton.addEventListener('click', () => {
            if (!navigator.geolocation) {
                window.toast?.(
                    'مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.',
                    'warning'
                );
                return;
            }

            const originalLabel = locationButton.textContent.trim();

            locationButton.disabled = true;
            locationButton.setAttribute('aria-busy', 'true');
            locationButton.textContent = 'در حال پیدا کردن موقعیت…';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const url = new URL(window.location.href);

                    url.searchParams.set(
                        'lat',
                        String(position.coords.latitude)
                    );

                    url.searchParams.set(
                        'lng',
                        String(position.coords.longitude)
                    );

                    url.searchParams.set('radius', '15');
                    url.searchParams.set('sort', 'distance');
                    url.hash = 'results';

                    window.location.assign(url.toString());
                },
                () => {
                    locationButton.disabled = false;
                    locationButton.removeAttribute('aria-busy');
                    locationButton.textContent = originalLabel || 'نزدیک من';

                    window.toast?.(
                        'دسترسی به موقعیت مکانی انجام نشد.',
                        'warning'
                    );
                },
                {
                    enableHighAccuracy: false,
                    timeout: 8000,
                    maximumAge: 300000,
                }
            );
        });
    }

    /* ----------------------------------------------------------------------
       Location filters
       ---------------------------------------------------------------------- */

    const filterForm = page.querySelector('.discover-results__filter form');
    const province =
        page.querySelector('#discoverProvince') ||
        page.querySelector('select[name="province"]');
    const city = page.querySelector('select[name="city"]');

    if (province && city && filterForm) {
        province.addEventListener('change', () => {
            city.disabled = true;
            filterForm.submit();
        });
    }

    /* ----------------------------------------------------------------------
       Search focus state
       ---------------------------------------------------------------------- */

    const heroSearch = page.querySelector(
        '.discover-hero-search input[name="q"]'
    );

    if (heroSearch) {
        heroSearch.addEventListener('focus', () => {
            page.classList.add('discover-search-focused');
        });

        heroSearch.addEventListener('blur', () => {
            page.classList.remove('discover-search-focused');
        });
    }

    /* ----------------------------------------------------------------------
       Progressive reveal
       ---------------------------------------------------------------------- */

    const revealItems = page.querySelectorAll(
        '.discover-result-card, .discover-salon-card, ' +
        '.discover-service-card, .discover-stylist-card'
    );

    if (
        !prefersReducedMotion &&
        'IntersectionObserver' in window &&
        revealItems.length
    ) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.08,
                rootMargin: '0px 0px -48px 0px',
            }
        );

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => {
            item.classList.add('is-visible');
        });
    }
})();
