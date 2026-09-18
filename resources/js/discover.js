(() => {
    'use strict';

    const page = document.querySelector('.discover-page');

    if (!page) return;

    const setLoading = (loading) => {
        page.classList.toggle('discover-results-loading', loading);
    };

    const notifyError = (message) => {
        if (typeof window.toast === 'function') {
            window.toast(message, 'error');
        } else {
            window.alert(message);
        }
    };

    const requestDiscover = async (url, { push = true, scroll = true } = {}) => {
        const nextUrl = new URL(url, window.location.origin);
        nextUrl.hash = 'results';

        setLoading(true);

        try {
            const response = await fetch(nextUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('DISCOVER_REQUEST_FAILED');
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const freshResults = parsed.querySelector('#results');
            const currentResults = page.querySelector('#results');

            if (!freshResults || !currentResults) {
                throw new Error('DISCOVER_RESULTS_NOT_FOUND');
            }

            currentResults.replaceWith(freshResults);

            if (push) {
                window.history.pushState({}, '', nextUrl.toString());
            }

            bindResultInteractions();

            if (scroll) {
                requestAnimationFrame(() => {
                    page.querySelector('#results')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                });
            }

            return true;
        } catch (error) {
            console.error(error);
            notifyError('نتایج دریافت نشد. اتصال را بررسی کن و دوباره تلاش کن.');
            return false;
        } finally {
            setLoading(false);
        }
    };

    const formToUrl = (form) => {
        const url = new URL(form.action || window.location.href, window.location.origin);
        const data = new FormData(form);

        url.search = '';

        for (const [key, value] of data.entries()) {
            const stringValue = String(value).trim();

            if (stringValue !== '') {
                url.searchParams.append(key, stringValue);
            }
        }

        return url;
    };

    const submitDiscoverForm = (form) => {
        if (!form) return Promise.resolve(false);
        return requestDiscover(formToUrl(form));
    };

    const bindResultInteractions = () => {
        const resultForm = page.querySelector('#results form[action*="salons/discover"]');

        if (resultForm && !resultForm.dataset.discoverBound) {
            resultForm.dataset.discoverBound = '1';

            resultForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitDiscoverForm(resultForm);
            });
        }

        page.querySelectorAll('#results a[href*="salons/discover"]').forEach((link) => {
            if (link.dataset.discoverBound) return;

            link.dataset.discoverBound = '1';

            link.addEventListener('click', (event) => {
                const url = new URL(link.href, window.location.origin);

                if (url.pathname !== window.location.pathname) return;

                event.preventDefault();
                requestDiscover(url);
            });
        });

        const province = page.querySelector('#discover-filter-province');
        const city = page.querySelector('#discover-filter-city');

        if (province && city && !province.dataset.discoverBound) {
            province.dataset.discoverBound = '1';

            province.addEventListener('change', () => {
                city.disabled = true;

                submitDiscoverForm(province.form).finally(() => {
                    const freshCity = page.querySelector('#discover-filter-city');

                    if (freshCity) {
                        freshCity.disabled = false;
                    }
                });
            });
        }
    };

    page.querySelectorAll('form[action*="salons/discover"]').forEach((form) => {
        if (form.closest('#results')) return;

        if (form.dataset.discoverBound) return;

        form.dataset.discoverBound = '1';

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            submitDiscoverForm(form);
        });
    });

    bindResultInteractions();

    /*
     |--------------------------------------------------------------------------
     | Same-page anchors
     |--------------------------------------------------------------------------
     */

    page.querySelectorAll('a[href*="#"]').forEach((link) => {
        if (link.dataset.anchorBound) return;

        link.dataset.anchorBound = '1';

        link.addEventListener('click', (event) => {
            const url = new URL(link.href, window.location.origin);

            if (url.pathname !== window.location.pathname || !url.hash) return;
            if (url.search && url.search !== window.location.search) return;

            const target = page.querySelector(url.hash);

            if (!target) return;

            event.preventDefault();

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });

            window.history.replaceState({}, '', url.hash);
        });
    });

    /*
     |--------------------------------------------------------------------------
     | Nearby location
     |--------------------------------------------------------------------------
     */

    const locationModal = page.querySelector('#discoverLocationModal');
    const locationAllow = page.querySelector('#discoverLocationAllow');
    const locationMapState = page.querySelector('#discoverLocationState');
    const locationMapFrame = page.querySelector('#discoverLocationMapFrame');
    const locationTriggers = page.querySelectorAll(
        '[data-discover-nearby], [data-discover-location], #discoverUseLocation'
    );

    const closeLocationModal = () => {
        if (!locationModal) return;

        locationModal.classList.remove('is-open');
        locationModal.setAttribute('aria-hidden', 'true');

        window.setTimeout(() => {
            if (!locationModal.classList.contains('is-open')) {
                locationModal.hidden = true;
            }
        }, 180);
    };

    const openLocationModal = () => {
        if (!locationModal) return;

        locationModal.hidden = false;

        requestAnimationFrame(() => {
            locationModal.classList.add('is-open');
        });

        locationModal.setAttribute('aria-hidden', 'false');
    };

    const applyNearbyLocation = async (lat, lng) => {
        const url = new URL(window.location.href);

        url.searchParams.set('lat', lat.toFixed(7));
        url.searchParams.set('lng', lng.toFixed(7));
        url.searchParams.set('radius', '15');
        url.searchParams.set('sort', 'distance');

        const success = await requestDiscover(url);

        if (success) {
            closeLocationModal();
        }

        return success;
    };

    const analyzeNearby = () => {
        if (!navigator.geolocation) {
            if (locationMapState) {
                locationMapState.textContent = 'مرورگر شما موقعیت مکانی را پشتیبانی نمی‌کند.';
            }

            return;
        }

        if (locationAllow) {
            locationAllow.disabled = true;
            locationAllow.textContent = 'در حال پیدا کردن موقعیت...';
        }

        if (locationMapState) {
            locationMapState.innerHTML =
                '<span class="discover-location-map-pin">⌖</span>' +
                '<strong>در حال پیدا کردن موقعیت تو...</strong>' +
                '<small>نتیجه‌ها همین‌جا به‌روزرسانی می‌شوند.</small>';
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = Number(position.coords.latitude);
                const lng = Number(position.coords.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                if (locationMapFrame) {
                    locationMapFrame.src =
                        'https://www.google.com/maps?q=' +
                        encodeURIComponent(lat + ',' + lng) +
                        '&z=14&output=embed';
                    locationMapFrame.hidden = false;
                }

                if (locationMapState) {
                    locationMapState.innerHTML =
                        '<span class="discover-location-map-pin">✓</span>' +
                        '<strong>موقعیت پیدا شد</strong>' +
                        '<small>در حال مرتب‌سازی نزدیک‌ترین سالن‌ها...</small>';
                }

                const success = await applyNearbyLocation(lat, lng);

                if (!success && locationAllow) {
                    locationAllow.disabled = false;
                    locationAllow.textContent = 'تلاش دوباره';
                }
            },
            () => {
                if (locationAllow) {
                    locationAllow.disabled = false;
                    locationAllow.textContent = 'اجازه موقعیت و پیدا کردن نزدیک‌ترین‌ها';
                }

                if (locationMapState) {
                    locationMapState.innerHTML =
                        '<span class="discover-location-map-pin">!</span>' +
                        '<strong>موقعیت مکانی در دسترس نبود</strong>' +
                        '<small>اجازه Location را بده یا شهر و منطقه را جست‌وجو کن.</small>';
                }
            },
            {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 120000,
            }
        );
    };

    locationTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openLocationModal();
        });
    });

    locationAllow?.addEventListener('click', analyzeNearby);

    locationModal?.querySelectorAll('[data-discover-location-close]').forEach((button) => {
        button.addEventListener('click', closeLocationModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && locationModal?.classList.contains('is-open')) {
            closeLocationModal();
        }
    });

    /*
     |--------------------------------------------------------------------------
     | Hero search focus
     |--------------------------------------------------------------------------
     */

    const heroSearch = page.querySelector(
        '.discover-search-field input[name="q"]'
    );

    heroSearch?.addEventListener('focus', () => {
        page.classList.add('discover-search-focused');
    });

    heroSearch?.addEventListener('blur', () => {
        page.classList.remove('discover-search-focused');
    });

    /*
     |--------------------------------------------------------------------------
     | Hero salon slider
     |--------------------------------------------------------------------------
     */

    const heroSlider = page.querySelector('[data-discover-hero-slider]');

    if (heroSlider) {
        const slides = Array.from(
            heroSlider.querySelectorAll('img[data-hero-name]')
        );

        const badge = heroSlider.querySelector('[data-hero-badge]');
        const badgeText = heroSlider.querySelector('[data-hero-badge-text]');

        if (slides.length > 1) {
            slides.slice(1).forEach((slide) => {
                const preload = new Image();
                preload.src = slide.currentSrc || slide.src;
            });

            let current = 0;

            const showSlide = (nextIndex) => {
                const previous = slides[current];
                const next = slides[nextIndex];

                if (!next || next === previous) return;

                previous?.style.setProperty('opacity', '0');
                next.style.setProperty('opacity', '1');
                current = nextIndex;

                if (badgeText) {
                    badgeText.textContent = next.dataset.heroName || 'NOBAT';
                }

                if (badge && next.dataset.heroUrl) {
                    badge.href = next.dataset.heroUrl;
                }
            };

            window.setInterval(() => {
                if (document.visibilityState === 'visible') {
                    showSlide((current + 1) % slides.length);
                }
            }, 4500);
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Scroll reveal
     |--------------------------------------------------------------------------
     */

    const observeReveals = () => {
        const items = page.querySelectorAll(
            '.discover-result-card, .discover-salon-card, .discover-service-card, .discover-stylist-card'
        );

        if (!('IntersectionObserver' in window) || !items.length) return;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px',
            }
        );

        items.forEach((item) => observer.observe(item));
    };

    observeReveals();

    window.addEventListener('popstate', () => {
        requestDiscover(window.location.href, {
            push: false,
            scroll: false,
        });
    });
})();
