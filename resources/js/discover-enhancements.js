/* NOBAT Discover — resilient filter/location interactions */
(() => {
    'use strict';

    const page = document.querySelector('.discover-page');
    if (!page) return;

    const form = page.querySelector('.discover-results form');
    const locationButton = page.querySelector('#discoverUseLocation');
    const province = form?.querySelector('select[name="province"]');
    const city = form?.querySelector('select[name="city"]');

    /*
     * The filter markup historically carried a visible type select and a
     * hidden type input at the same time. Keep the visible control authoritative
     * without forcing a risky Blade rewrite of the large results template.
     */
    if (form) {
        form.addEventListener('submit', () => {
            const visibleType = form.querySelector('select[name="type"]');
            const hiddenTypes = form.querySelectorAll('input[name="type"]');

            hiddenTypes.forEach((input) => {
                input.disabled = Boolean(visibleType);
            });
        });
    }

    /*
     * Province -> city UX: submit only when the province actually changes.
     * The old runtime listened for IDs that no longer exist in the current view.
     */
    province?.addEventListener('change', () => {
        if (!province.value || !form) return;
        if (city) city.disabled = true;
        form.submit();
    });

    /*
     * Real location action. The Discover view exposes an ID; older JS was
     * looking for a data attribute, so the button previously did nothing.
     */
    locationButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            window.toast?.('مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.', 'warning');
            return;
        }

        const original = locationButton.innerHTML;
        locationButton.disabled = true;
        locationButton.setAttribute('aria-busy', 'true');
        locationButton.innerHTML = '<span aria-hidden="true">…</span><span>در حال پیدا کردن...</span>';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const url = new URL(window.location.href);

                url.searchParams.set('lat', String(position.coords.latitude));
                url.searchParams.set('lng', String(position.coords.longitude));
                url.searchParams.set('radius', '15');
                url.searchParams.set('sort', 'distance');
                url.hash = 'results';

                window.location.assign(url.toString());
            },
            (error) => {
                locationButton.disabled = false;
                locationButton.setAttribute('aria-busy', 'false');
                locationButton.innerHTML = original;

                const message = error?.code === 1
                    ? 'برای نمایش سالن‌های نزدیک، دسترسی به موقعیت مکانی را فعال کن.'
                    : 'دریافت موقعیت مکانی انجام نشد. دوباره امتحان کن.';

                window.toast?.(message, 'warning');
            },
            {
                enableHighAccuracy: false,
                timeout: 8000,
                maximumAge: 300000,
            }
        );
    });
})();
