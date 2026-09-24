(() => {
    'use strict';

    const page = document.querySelector('.discover-page');

    if (!page) return;

    let activeRequestController = null;
    let activeRequestId = 0;

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

    const requestDiscover = async (
        url,
        {
            push = true,
            scroll = true,
            openFilterResult = false,
        } = {}
    ) => {
        const nextUrl = new URL(
            url,
            window.location.origin
        );

        nextUrl.hash = '';

        // A new filter/search state always starts from page one.
        if (!nextUrl.searchParams.has('page')) {
            nextUrl.searchParams.delete('page');
        }

        const normalizedUrl = nextUrl.toString();

        if (
            normalizedUrl === window.location.href &&
            !openFilterResult
        ) {
            if (scroll) {
                requestAnimationFrame(() => {
                    page
                        .querySelector('#results')
                        ?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                });
            }

            return true;
        }

        const requestId = ++activeRequestId;
        const requestUrl = normalizedUrl;

        /*
        |--------------------------------------------------------------------------
        | Cancel stale requests
        |--------------------------------------------------------------------------
        |
        | A fast filter interaction can otherwise leave multiple requests
        | running at once. Only the latest request is allowed to update DOM,
        | history or loading state.
        |--------------------------------------------------------------------------
        */

        activeRequestController?.abort();

        const controller = new AbortController();
        activeRequestController = controller;

        setLoading(true);

        try {
            const response = await fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (
                controller.signal.aborted ||
                requestId !== activeRequestId
            ) {
                return false;
            }

            if (!response.ok) {
                let message = 'نتایج دریافت نشد. دوباره تلاش کن.';

                try {
                    const payload = await response.clone().json();

                    if (
                        typeof payload?.message === 'string' &&
                        payload.message.trim() !== ''
                    ) {
                        message = payload.message.trim();
                    }

                    const validationMessage = Object
                        .values(payload?.errors ?? {})
                        .flat()
                        .find(
                            (value) =>
                                typeof value === 'string' &&
                                value.trim() !== ''
                        );

                    if (validationMessage) {
                        message = validationMessage.trim();
                    }
                } catch {
                    // The server may return an HTML error page for a non-AJAX
                    // edge case. Keep the generic fallback in that situation.
                }

                const error = new Error(message);
                error.code = 'DISCOVER_REQUEST_FAILED';
                error.status = response.status;

                throw error;
            }

            const html = await response.text();

            if (
                controller.signal.aborted ||
                requestId !== activeRequestId
            ) {
                return false;
            }

            const parsed = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const freshDynamic =
                parsed.querySelector(
                    '#discoverDynamicContent'
                );

            const currentDynamic =
                page.querySelector(
                    '#discoverDynamicContent'
                );

            const freshResultsBody =
                freshDynamic?.querySelector(
                    '#discoverResultsBody'
                )?.cloneNode(true);

            if (
                !freshDynamic ||
                !currentDynamic
            ) {
                throw new Error(
                    'DISCOVER_DYNAMIC_CONTENT_NOT_FOUND'
                );
            }

            currentDynamic.replaceWith(
                freshDynamic
            );

            if (
                requestId !== activeRequestId
            ) {
                return false;
            }

            if (
                push &&
                window.location.href !== normalizedUrl
            ) {
                window.history.pushState(
                    {},
                    '',
                    normalizedUrl
                );
            }

            syncHeroSearchInputs(
                nextUrl
            );

            bindResultInteractions();
            bindFilterPanel();

            if (openFilterResult && freshResultsBody) {
                const filterResults =
                    page.querySelector('#discoverFilterResults');

                if (filterResults) {
                    filterResults.innerHTML = '';
                    filterResults.setAttribute('aria-busy', 'false');
                    filterResults.appendChild(
                        freshResultsBody
                    );
                }

                openFilterPanel();
            }

            observeReveals();

            if (
                scroll &&
                requestId === activeRequestId
            ) {
                requestAnimationFrame(() => {
                    page
                        .querySelector('#results')
                        ?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                });
            }

            return true;
        } catch (error) {
            if (
                error?.name === 'AbortError' ||
                controller.signal.aborted ||
                requestId !== activeRequestId
            ) {
                return false;
            }

            console.error(error);

            notifyError(
                error?.code === 'DISCOVER_REQUEST_FAILED' &&
                typeof error?.message === 'string' &&
                error.message.trim() !== ''
                    ? error.message
                    : 'نتایج دریافت نشد. اتصال را بررسی کن و دوباره تلاش کن.'
            );

            return false;
        } finally {
            if (
                requestId === activeRequestId
            ) {
                setLoading(false);

                if (
                    activeRequestController === controller
                ) {
                    activeRequestController = null;
                }
            }
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

    const submitDiscoverForm = (
        form,
        options = {}
    ) => {
        if (!form) return Promise.resolve(false);

        return requestDiscover(
            formToUrl(form),
            options
        );
    };

    const syncHeroSearchInputs = (url) => {
        const heroQuery = page.querySelector(
            '.discover-search-field input[name="q"]'
        );

        const heroLocation = page.querySelector(
            '.discover-search-field input[name="location"]'
        );

        if (heroQuery) {
            heroQuery.value = url.searchParams.get('q') || '';
        }

        if (heroLocation) {
            heroLocation.value = url.searchParams.get('location') || '';
        }
    };

    const bindResultInteractions = () => {
        const resultForm = page.querySelector('#results form[action*="salons/discover"]');

        if (resultForm && !resultForm.dataset.discoverBound) {
            resultForm.dataset.discoverBound = '1';

            resultForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitDiscoverForm(resultForm, {
                    scroll: true,
                });
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

                submitDiscoverForm(
                    province.form,
                    {
                        scroll: true,
                    }
                ).finally(() => {
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

            submitDiscoverForm(
                form,
                {
                    scroll: true,
                }
            );
        });
    });

    const bindFilterPanel = () => {
        const panel = page.querySelector('#discoverFiltersPanel');
        const openButton = page.querySelector('#discoverFiltersOpen');
        const backdrop = page.querySelector('#discoverFiltersBackdrop');
        const closeButtons = page.querySelectorAll('[data-close-discover-filters]');

        if (!panel || !openButton || openButton.dataset.filtersBound) {
            return;
        }

        openButton.dataset.filtersBound = '1';

        const setOpen = (open) => {
            panel.classList.toggle('is-open', open);
            openButton.setAttribute('aria-expanded', open ? 'true' : 'false');

            if (backdrop) {
                backdrop.hidden = !open;
            }

            document.body.classList.toggle('discover-filters-open', open);

            if (!open) {
                openButton.focus({ preventScroll: true });
            }
        };

        openButton.addEventListener('click', () => setOpen(true));

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setOpen(false));
        });

        backdrop?.addEventListener('click', () => setOpen(false));

        panel.querySelector('form')?.addEventListener('submit', () => {
            setOpen(false);
        });

        const onEscape = (event) => {
            if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                setOpen(false);
            }
        };

        document.addEventListener('keydown', onEscape);
    };

    bindFilterPanel();

    /*
     |--------------------------------------------------------------------------
     | Search shortcuts
     |--------------------------------------------------------------------------
     */

    page.querySelectorAll(
        '.discover-quick-link, .discover-category-card, .discover-service-card'
    ).forEach((link) => {
        if (link.dataset.discoverSearchLinkBound) return;

        link.dataset.discoverSearchLinkBound = '1';

        link.addEventListener('click', (event) => {
            const url = new URL(
                link.href,
                window.location.origin
            );

            if (
                url.pathname !== window.location.pathname
                || ! url.search
            ) {
                return;
            }

            event.preventDefault();

            requestDiscover(
                url,
                {
                    scroll: true,
                }
            );
        });
    });

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

            window.history.replaceState({}, '', url.href);
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

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    if (locationAllow) {
                        locationAllow.disabled = false;
                        locationAllow.textContent = 'تلاش دوباره';
                    }

                    if (locationMapState) {
                        locationMapState.innerHTML =
                            '<span class="discover-location-map-pin">!</span>' +
                            '<strong>مختصات موقعیت معتبر نبود</strong>' +
                            '<small>دوباره تلاش کن یا شهر و منطقه را به‌صورت دستی جست‌وجو کن.</small>';
                    }

                    return;
                }

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
            (error) => {
                if (locationAllow) {
                    locationAllow.disabled = false;
                    locationAllow.textContent = 'تلاش دوباره';
                }

                let title = 'موقعیت مکانی در دسترس نبود';
                let detail = 'اجازه Location را بده یا شهر و منطقه را جست‌وجو کن.';

                switch (error?.code) {
                    case 1:
                        title = 'اجازه موقعیت مکانی داده نشد';
                        detail = 'اجازه Location را برای این سایت فعال کن و دوباره امتحان کن.';
                        break;

                    case 2:
                        title = 'موقعیت فعلی پیدا نشد';
                        detail = 'اینترنت و سرویس Location دستگاه را بررسی کن و دوباره تلاش کن.';
                        break;

                    case 3:
                        title = 'پیدا کردن موقعیت طول کشید';
                        detail = 'دوباره تلاش کن یا شهر و منطقه را به‌صورت دستی جست‌وجو کن.';
                        break;
                }

                if (locationMapState) {
                    locationMapState.innerHTML =
                        '<span class="discover-location-map-pin">!</span>' +
                        '<strong>' +
                        title +
                        '</strong>' +
                        '<small>' +
                        detail +
                        '</small>';
                }
            },
            {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 120000,
            }
        );
    };

    page.addEventListener('click', (event) => {
        const trigger = event.target.closest(
            '[data-discover-nearby], [data-discover-location], #discoverUseLocation'
        );

        if (!trigger || !page.contains(trigger)) {
            return;
        }

        event.preventDefault();

        openLocationModal();

        if (
            trigger.matches('#discoverUseLocation, [data-discover-location]')
        ) {
            analyzeNearby();
        }
    });

    locationAllow?.addEventListener('click', analyzeNearby);

    locationModal?.querySelectorAll('[data-discover-location-close]').forEach((button) => {
        button.addEventListener('click', closeLocationModal);
    });

    if (
        locationModal &&
        new URL(window.location.href).searchParams.get('nearby') === '1'
    ) {
        openLocationModal();

        const cleanUrl = new URL(window.location.href);
        cleanUrl.searchParams.delete('nearby');
        window.history.replaceState({}, '', cleanUrl.toString());
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;

        if (locationModal?.classList.contains('is-open')) {
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
            '.discover-result-card, .discover-salon-card, .discover-service-card, .discover-stylist-card, .discover-team-card'
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
