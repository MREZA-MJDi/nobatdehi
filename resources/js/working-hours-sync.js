/* NOBAT — keep every salon-facing schedule view aligned with the same source of truth. */
(() => {
    'use strict';

    const isRelevantPage =
        document.body?.classList.contains('salon-portal') &&
        /\/salon\/(settings|working-hours)(?:\/|$)/.test(window.location.pathname);

    if (!isRelevantPage) {
        return;
    }

    const fetchSchedule = async () => {
        const response = await fetch(window.location.href, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
            ? await response.json()
            : null;

        if (!response.ok || !data?.ok || !data.schedule) {
            throw new Error('Working hours sync failed');
        }

        return data.schedule;
    };

    const applySchedule = (schedule) => {
        const workingHoursHost = document.querySelector('[x-data*="workingHoursPage"]');

        if (workingHoursHost && window.Alpine?.$data) {
            try {
                const state = window.Alpine.$data(workingHoursHost);

                if (state) {
                    state.hours = JSON.parse(JSON.stringify(schedule));
                }
            } catch (error) {
                console.error('NOBAT working-hours state sync error:', error);
            }
        }

        /* Settings contains a legacy compact editor. Keep it visually/data-wise
           identical to the dedicated Working Hours screen. */
        if (!window.Alpine?.$data) {
            return;
        }

        Object.entries(schedule).forEach(([day, dayData]) => {
            const start = document.querySelector(
                `[name="working_hours[${day}][start_time]"]`
            );
            const end = document.querySelector(
                `[name="working_hours[${day}][end_time]"]`
            );
            const closedInput = document.querySelector(
                `[name="working_hours[${day}][is_closed]"][type="checkbox"]`
            );

            if (!start || !end) {
                return;
            }

            const interval = dayData?.intervals?.[0];
            start.value = interval?.start || '';
            end.value = interval?.end || '';

            const row = start.closest('[x-data]');

            if (row) {
                try {
                    const state = window.Alpine.$data(row);
                    if (state) {
                        state.closed = Boolean(dayData?.closed ?? true);
                    }
                } catch (error) {
                    console.error('NOBAT settings hours state sync error:', error);
                }
            }

            if (closedInput) {
                closedInput.checked = Boolean(dayData?.closed ?? true);
            }
        });
    };

    const install = async () => {
        try {
            const schedule = await fetchSchedule();
            applySchedule(schedule);
        } catch (error) {
            console.warn('NOBAT could not synchronize working hours:', error);
        }
    };

    let attempts = 0;
    const retry = () => {
        if (window.Alpine?.$data || attempts++ > 60) {
            install();
            return;
        }

        window.setTimeout(retry, 50);
    };

    retry();
})();
