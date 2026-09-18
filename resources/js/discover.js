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

    document.querySelectorAll('.discover-page a[href*="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const url = new URL(link.href, window.location.origin);

            if (url.pathname !== window.location.pathname || !url.hash) {
                return;
            }

            const target = document.querySelector(url.hash);

            if (!target) {
                return;
            }

            event.preventDefault();

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });

            history.replaceState(null, '', url.hash);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Province -> City
    |--------------------------------------------------------------------------
    */

    const province = document.querySelector('#discover-filter-province');
    const city = document.querySelector('#discover-filter-city');

    if (province && city) {
        province.addEventListener('change', () => {
            if (!province.value) {
                return;
            }

            city.disabled = true;
            city.form?.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Nearby location modal
    |--------------------------------------------------------------------------
    */

    const locationModal = document.querySelector('#discoverLocationModal');
    const locationAllow = document.querySelector('#discoverLocationAllow');
    const locationMapState = document.querySelector('#discoverLocationState');
    const locationMapFrame = document.querySelector('#discoverLocationMapFrame');
    const locationTriggers = document.querySelectorAll('[data-discover-nearby], [data-discover-location], #discoverUseLocation');

    const closeLocationModal = () => {
        if (!locationModal) return;
        locationModal.classList.remove('is-open');
        locationModal.setAttribute('aria-hidden', 'true');
        window.setTimeout(() => {
            if (!locationModal.classList.contains('is-open')) locationModal.hidden = true;
        }, 180);
    };

    const openLocationModal = () => {
        if (!locationModal) return;

        if (locationAllow) {
            locationAllow.disabled = false;
            locationAllow.textContent = 'اجازه موقعیت و پیدا کردن نزدیک‌ترین‌ها';
        }

        if (locationMapFrame) {
            locationMapFrame.hidden = true;
            locationMapFrame.src = '';
        }

        if (locationMapState) {
            locationMapState.style.background = '';
            locationMapState.innerHTML =
                '<span class="discover-location-map-pin">⌖</span>' +
                '<strong>موقعیت خودت را مشخص کن</strong>' +
                '<small>برای نمایش گزینه‌های اطراف، اجازه موقعیت مکانی مرورگر را بده.</small>';
        }

        locationModal.hidden = false;
        requestAnimationFrame(() => locationModal.classList.add('is-open'));
        locationModal.setAttribute('aria-hidden', 'false');
    };

    const analyzeNearby = () => {
        if (!navigator.geolocation) {
            if (locationMapState) locationMapState.textContent = 'مرورگر شما موقعیت مکانی را پشتیبانی نمی‌کند.';
            return;
        }

        if (locationAllow) {
            locationAllow.disabled = true;
            locationAllow.textContent = 'در حال پیدا کردن موقعیت...';
        }

        if (locationMapState) {
            locationMapState.innerHTML = '<span class="discover-location-map-pin">⌖</span><strong>در حال پیدا کردن موقعیت تو...</strong><small>بعد از پیدا شدن موقعیت، نزدیک‌ترین سالن‌ها را سریع مرتب می‌کنیم.</small>';
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = Number(position.coords.latitude);
                const lng = Number(position.coords.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                if (locationMapFrame) {
                    locationMapFrame.src = 'https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=14&output=embed';
                    locationMapFrame.hidden = false;
                }

                if (locationMapState) {
                    locationMapState.innerHTML = '<span class="discover-location-map-pin">✓</span><strong>موقعیت پیدا شد</strong><small>در حال مرتب‌سازی سالن‌های نزدیک...</small>';
                    locationMapState.style.background = 'rgba(8, 12, 10, .52)';
                }

                const url = new URL(window.location.href);
                url.searchParams.set('lat', lat.toFixed(7));
                url.searchParams.set('lng', lng.toFixed(7));
                url.searchParams.set('radius', '15');
                url.searchParams.set('sort', 'distance');
                url.hash = 'results';
                window.setTimeout(() => window.location.assign(url.toString()), 650);
            },
            () => {
                if (locationAllow) {
                    locationAllow.disabled = false;
                    locationAllow.textContent = 'اجازه موقعیت و پیدا کردن نزدیک‌ترین‌ها';
                }
                if (locationMapState) {
                    locationMapState.innerHTML = '<span class="discover-location-map-pin">!</span><strong>موقعیت مکانی در دسترس نبود</strong><small>اجازه Location را بده یا از جستجوی شهر و منطقه استفاده کن.</small>';
                }
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 120000 }
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
        if (event.key === 'Escape' && locationModal?.classList.contains('is-open')) closeLocationModal();
    });

    /*
    |--------------------------------------------------------------------------
    | Hero search focus
    |--------------------------------------------------------------------------
    */

    const heroSearch = document.querySelector(
        '.discovery-search-field input[name="q"]'
    );

    if (heroSearch) {
        heroSearch.addEventListener('focus', () => {
            page.classList.add('discover-search-focused');
        });

        heroSearch.addEventListener('blur', () => {
            page.classList.remove('discover-search-focused');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Hero slider
    |--------------------------------------------------------------------------
    | Cross-fade salon cover images every four seconds.
    */

    const heroSlider = page.querySelector('[data-discover-hero-slider]');

    if (heroSlider) {
        const slides = Array.from(
            heroSlider.querySelectorAll('img[data-hero-name]')
        );

        const badge = heroSlider.querySelector('[data-hero-badge]');
        const badgeText = heroSlider.querySelector('[data-hero-badge-text]');

        if (slides.length > 1) {
            // Preload remaining hero images so the 4s transition never waits
            // for a lazy request after the next salon is selected.
            slides.slice(1).forEach((slide) => {
                const preload = new Image();
                preload.src = slide.currentSrc || slide.src;
            });

            let current = 0;

            const showSlide = (nextIndex) => {
                const previous = slides[current];
                const next = slides[nextIndex];

                if (!next || next === previous) {
                    return;
                }

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
                if (document.visibilityState !== 'visible') {
                    return;
                }

                showSlide((current + 1) % slides.length);
            }, 4000);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll reveal
    |--------------------------------------------------------------------------
    */

    const revealItems = document.querySelectorAll(
        '.discover-result-card, .discover-salon-card, .discover-service-card, .discover-stylist-card'
    );

    if ('IntersectionObserver' in window && revealItems.length) {
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
                rootMargin: '0px 0px -40px 0px',
            }
        );

        revealItems.forEach((item) => observer.observe(item));
    }
})();
