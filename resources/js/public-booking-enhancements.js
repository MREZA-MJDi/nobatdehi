/* NOBAT — Public booking hardening
 * Patches the live Alpine booking component after initialization.
 * Prevents stale availability responses from winning the race and keeps
 * the submit state tied to the currently loaded availability set.
 */
(() => {
    'use strict';

    const install = () => {
        const host = document.querySelector('.nobat-booking[x-data*="bookingPage"]');
        const alpine = window.Alpine;

        if (!host || !alpine?.$data) {
            return false;
        }

        let state;
        try {
            state = alpine.$data(host);
        } catch (_) {
            return false;
        }

        if (!state || state.__nobatBookingHardened) {
            return Boolean(state);
        }

        const availabilityMeta = document.querySelector('meta[name="nobat-booking-availability-url"]');
        const serverDateMeta = document.querySelector('meta[name="nobat-app-date"]');
        const availabilityUrl = availabilityMeta?.content || '';
        const serverToday = serverDateMeta?.content || '';

        if (!availabilityUrl) {
            return false;
        }

        state.__nobatBookingHardened = true;
        state.__nobatAvailabilityRequest = 0;
        state.__nobatAvailabilityController = null;

        state.ensureDateNotPast = function () {
            if (serverToday && (!this.date || this.date < serverToday)) {
                this.date = serverToday;
            }
        };

        state.goToday = function () {
            if (serverToday) {
                this.date = serverToday;
            }
            this.weekOffset = 0;
            this.time = '';
            this.loadSlots();
        };

        state.loadSlots = async function () {
            if (!this.barberId || !this.serviceId || !this.date) {
                this.slots = [];
                this.time = '';
                return;
            }

            if (serverToday && this.date < serverToday) {
                this.date = serverToday;
            }

            const requestId = ++this.__nobatAvailabilityRequest;

            this.__nobatAvailabilityController?.abort();
            const controller = new AbortController();
            this.__nobatAvailabilityController = controller;

            this.loading = true;
            this.availabilityError = false;
            this.time = '';
            this.slots = [];

            const url = new URL(availabilityUrl, window.location.origin);
            url.searchParams.set('barber_id', String(this.barberId));
            url.searchParams.set('service_id', String(this.serviceId));
            url.searchParams.set('booking_date', String(this.date));

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                const contentType = response.headers.get('content-type') || '';
                const data = contentType.includes('application/json')
                    ? await response.json()
                    : null;

                if (!response.ok) {
                    const firstValidationError = Object.values(data?.errors || {})[0]?.[0];
                    throw new Error(
                        data?.message ||
                        firstValidationError ||
                        'دریافت زمان‌های خالی انجام نشد.'
                    );
                }

                if (requestId !== this.__nobatAvailabilityRequest) {
                    return;
                }

                this.slots = Array.isArray(data?.slots) ? data.slots : [];
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                if (requestId !== this.__nobatAvailabilityRequest) {
                    return;
                }

                console.error('NOBAT booking availability error:', error);
                this.slots = [];
                this.time = '';
                this.availabilityError = true;
            } finally {
                if (requestId === this.__nobatAvailabilityRequest) {
                    this.loading = false;
                    if (this.__nobatAvailabilityController === controller) {
                        this.__nobatAvailabilityController = null;
                    }
                }
            }
        };

        /* A slot selected before a refresh must never survive that refresh. */
        const originalSelectTime = state.selectTime?.bind(state);
        if (originalSelectTime) {
            state.selectTime = function (time) {
                const valid = this.slots.some(
                    (slot) => slot.available && String(slot.start) === String(time)
                );

                if (!valid) {
                    this.time = '';
                    return;
                }

                return originalSelectTime(time);
            };
        }

        /* Re-run the server-today guard after Alpine has painted its initial state. */
        queueMicrotask(() => {
            try {
                state.ensureDateNotPast();
            } catch (_) {}
        });

        return true;
    };

    let attempts = 0;
    const retry = () => {
        if (install() || attempts++ > 60) return;
        window.setTimeout(retry, 50);
    };

    retry();
})();
