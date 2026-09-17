import './bootstrap';
import './customer';
import './discover';

import Alpine from 'alpinejs'

window.Alpine = Alpine


/* =========================================================
 | Theme
 | ---------------------------------------------------------
 | Global theme store.
 |
 | Contract:
 | - html[data-theme="light|dark"]
 | - localStorage key: nobatdehi_theme
 | - $store.theme.current
 | - $store.theme.toggle()
 | - $store.theme.set(theme)
 ========================================================= */

const THEME_STORAGE_KEY = 'nobatdehi_theme'

function normalizeTheme(theme) {
    return theme === 'dark'
        ? 'dark'
        : 'light'
}

function getStoredTheme() {
    try {
        const stored = localStorage.getItem(
            THEME_STORAGE_KEY
        )

        if (
            stored === 'dark' ||
            stored === 'light'
        ) {
            return stored
        }
    } catch {
        // localStorage may be unavailable.
    }

    try {
        return window.matchMedia?.(
            '(prefers-color-scheme: dark)'
        )?.matches
            ? 'dark'
            : 'light'
    } catch {
        return 'light'
    }
}

function applyTheme(theme, persist = true) {
    const normalized = normalizeTheme(theme)

    document.documentElement.dataset.theme = normalized

    if (persist) {
        try {
            localStorage.setItem(
                THEME_STORAGE_KEY,
                normalized
            )
        } catch {
            // Ignore storage errors.
        }
    }

    return normalized
}

const initialTheme = getStoredTheme()

/*
 * Apply theme immediately before Alpine starts.
 * This prevents a flash of the wrong theme.
 */
applyTheme(initialTheme, false)

Alpine.store('theme', {

    current: initialTheme,

    toggle() {
        const nextTheme =
            this.current === 'dark'
                ? 'light'
                : 'dark'

        this.current = applyTheme(nextTheme)
    },

    set(theme) {
        this.current = applyTheme(theme)
    },
})


/* =========================================================
 | Toast
 ========================================================= */

function createToastStore() {
    return {
        items: [],

        show(
            message,
            type = 'default',
            duration = 3500
        ) {
            const id = `${Date.now()}-${Math.random()
                .toString(16)
                .slice(2)}`

            this.items.push({
                id,
                message,
                type,
            })

            window.setTimeout(() => {
                this.remove(id)
            }, duration)

            return id
        },

        success(message, duration = 3500) {
            return this.show(
                message,
                'success',
                duration
            )
        },

        error(message, duration = 4500) {
            return this.show(
                message,
                'error',
                duration
            )
        },

        warning(message, duration = 4000) {
            return this.show(
                message,
                'warning',
                duration
            )
        },

        info(message, duration = 3500) {
            return this.show(
                message,
                'info',
                duration
            )
        },

        remove(id) {
            this.items = this.items.filter(
                item => item.id !== id
            )
        },
    }
}

Alpine.store(
    'toast',
    createToastStore()
)

window.toast = (
    message,
    type = 'default',
    duration = 3500
) => {
    return Alpine
        .store('toast')
        .show(
            message,
            type,
            duration
        )
}


/* =========================================================
 | OTP Form
 ========================================================= */

Alpine.data('otpForm', () => ({

    code: '',
    loading: false,
    error: '',
    digits: 6,

    init() {
        this.$nextTick(() => {
            this.focusFirstInput()
        })
    },

    focusFirstInput() {
        const first = this.$refs.firstInput

        if (first) {
            first.focus()
        }
    },

    normalize(value) {
        return String(value ?? '')
            .replace(
                /[۰-۹]/g,
                char =>
                    '۰۱۲۳۴۵۶۷۸۹'.indexOf(char)
            )
            .replace(
                /[^\d]/g,
                ''
            )
            .slice(
                0,
                this.digits
            )
    },

    handleInput(index, event) {

        const value =
            this.normalize(
                event.target.value
            )

        event.target.value = value

        if (value.length > 0) {

            const next =
                this.$refs[
                    `input-${index + 1}`
                    ]

            if (next) {
                next.focus()
            }
        }

        this.syncCode()
    },

    handlePaste(event) {

        event.preventDefault()

        const pasted =
            this.normalize(
                event.clipboardData
                    ?.getData('text') ?? ''
            )

        if (!pasted) {
            return
        }

        const chars =
            pasted.split('')

        chars.forEach(
            (char, index) => {

                const input =
                    this.$refs[
                        `input-${index + 1}`
                        ]

                if (input) {
                    input.value = char
                }
            }
        )

        this.syncCode()

        const target =
            this.$refs[
                `input-${Math.min(
                    chars.length,
                    this.digits
                )}`
                ]

        if (target) {
            target.focus()
        }
    },

    handleBackspace(index, event) {

        if (
            event.key === 'Backspace' &&
            !event.target.value
        ) {

            const previous =
                this.$refs[
                    `input-${index - 1}`
                    ]

            if (previous) {
                previous.focus()
            }
        }
    },

    syncCode() {

        const values = []

        for (
            let index = 1;
            index <= this.digits;
            index++
        ) {

            const input =
                this.$refs[
                    `input-${index}`
                    ]

            values.push(
                input?.value ?? ''
            )
        }

        this.code =
            values.join('')
    },

    isComplete() {
        return (
            this.code.length ===
            this.digits
        )
    },
}))


/* =========================================================
 | Salon Booking Modal
 |
 | Used by:
 | resources/views/public/salon/partials/booking-modal.blade.php
 ========================================================= */

/* =========================================================
 | Salon Booking Modal
 ========================================================= */

Alpine.data(
    'salonBookingModal',
    config => ({
        salonId: Number(config?.salonId ?? 0),

        salonName: config?.salonName ?? '',

        availabilityUrl:
            config?.availabilityUrl ?? '',

        prepareUrl:
            config?.prepareUrl ?? '',

        services:
            Array.isArray(config?.services)
                ? config.services
                : [],

        barbers:
            Array.isArray(config?.barbers)
                ? config.barbers
                : [],

        openState: false,

        step: 1,

        loadingSlots: false,

        submitting: false,

        formError: '',

        availabilityError: '',

        selectedServiceId: null,

        selectedBarberId: null,

        selectedDate: null,

        selectedTime: null,

        notes: '',

        slots: [],

        calendarAnchor: null,

        todayGregorian: null,

        init() {
            this.todayGregorian =
                this.startOfDay(new Date())

            this.calendarAnchor =
                new Date(this.todayGregorian)

            this.buildCalendar()
        },

        open(serviceId = null) {
            this.reset()

            this.openState = true

            this.lockBody()

            if (serviceId) {
                this.selectService(serviceId)
            }
        },

        close() {
            this.openState = false

            this.loadingSlots = false

            this.submitting = false

            this.unlockBody()
        },

        reset() {
            this.step = 1

            this.loadingSlots = false

            this.submitting = false

            this.formError = ''

            this.availabilityError = ''

            this.selectedServiceId = null

            this.selectedBarberId = null

            this.selectedDate = null

            this.selectedTime = null

            this.notes = ''

            this.slots = []

            this.todayGregorian =
                this.startOfDay(new Date())

            this.calendarAnchor =
                new Date(this.todayGregorian)

            this.buildCalendar()
        },

        lockBody() {
            document.documentElement.classList.add(
                'salon-booking-open'
            )

            document.body.classList.add(
                'salon-booking-open'
            )
        },

        unlockBody() {
            document.documentElement.classList.remove(
                'salon-booking-open'
            )

            document.body.classList.remove(
                'salon-booking-open'
            )
        },

        startOfDay(date) {
            const result = new Date(date)

            result.setHours(
                0,
                0,
                0,
                0
            )

            return result
        },

        addDays(date, amount) {
            const result = new Date(date)

            result.setDate(
                result.getDate() + amount
            )

            return this.startOfDay(result)
        },

        toIsoDate(date) {
            const year =
                date.getFullYear()

            const month =
                String(
                    date.getMonth() + 1
                ).padStart(2, '0')

            const day =
                String(
                    date.getDate()
                ).padStart(2, '0')

            return `${year}-${month}-${day}`
        },

        formatTime(value) {
            return value
                ? String(value).slice(0, 5)
                : ''
        },

        findService(id) {
            return (
                this.services.find(
                    service =>
                        Number(service.id) ===
                        Number(id)
                ) ?? null
            )
        },

        get selectedService() {
            return this.findService(
                this.selectedServiceId
            )
        },

        findBarber(id) {
            return (
                this.barbers.find(
                    barber =>
                        Number(barber.id) ===
                        Number(id)
                ) ?? null
            )
        },

        get selectedBarber() {
            return this.findBarber(
                this.selectedBarberId
            )
        },

        selectService(id) {
            const service =
                this.findService(id)

            if (!service) {
                return
            }

            this.selectedServiceId =
                Number(service.id)

            this.selectedBarberId = null

            this.selectedDate = null

            this.selectedTime = null

            this.slots = []

            this.formError = ''

            this.availabilityError = ''

            if (this.barbers.length === 1) {
                this.selectedBarberId =
                    Number(this.barbers[0].id)
            }
        },

        selectBarber(id) {
            const barber =
                this.findBarber(id)

            if (!barber) {
                return
            }

            this.selectedBarberId =
                Number(barber.id)

            this.selectedDate = null

            this.selectedTime = null

            this.slots = []

            this.formError = ''

            this.availabilityError = ''
        },

        persianFormatter:
            new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian',
                {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                }
            ),

        persianMonthFormatter:
            new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian',
                {
                    year: 'numeric',
                    month: 'long',
                }
            ),

        persianParts(date) {
            const parts =
                this.persianFormatter
                    .formatToParts(date)

            const result = {}

            for (const part of parts) {
                if (
                    part.type === 'year' ||
                    part.type === 'month' ||
                    part.type === 'day'
                ) {
                    result[part.type] =
                        Number(
                            String(part.value)
                                .replace(
                                    /[^\d]/g,
                                    ''
                                )
                        )
                }
            }

            return result
        },

        findPersianMonthStart(date) {
            let cursor =
                this.startOfDay(date)

            for (
                let index = 0;
                index < 40;
                index++
            ) {
                const parts =
                    this.persianParts(cursor)

                if (parts.day === 1) {
                    return cursor
                }

                cursor =
                    this.addDays(
                        cursor,
                        -1
                    )
            }

            return cursor
        },

        findPersianMonthEnd(monthStart) {
            let cursor =
                this.cloneDate(monthStart)

            const startParts =
                this.persianParts(monthStart)

            for (
                let index = 0;
                index < 40;
                index++
            ) {
                const next =
                    this.addDays(
                        cursor,
                        1
                    )

                const nextParts =
                    this.persianParts(next)

                if (
                    nextParts.year !==
                    startParts.year ||
                    nextParts.month !==
                    startParts.month
                ) {
                    return cursor
                }

                cursor = next
            }

            return cursor
        },

        cloneDate(date) {
            return new Date(
                date.getTime()
            )
        },

        getCalendarGrid() {
            const monthStart =
                this.findPersianMonthStart(
                    this.calendarAnchor
                )

            const leadingDays =
                (
                    monthStart.getDay() + 1
                ) % 7

            const firstCell =
                this.addDays(
                    monthStart,
                    -leadingDays
                )

            return Array.from(
                { length: 42 },
                (_, index) =>
                    this.addDays(
                        firstCell,
                        index
                    )
            )
        },

        buildCalendar() {
            this.calendarAnchor =
                this.findPersianMonthStart(
                    this.calendarAnchor
                )
        },

        get calendarDays() {
            return this.getCalendarGrid().map(
                date => {
                    const parts =
                        this.persianParts(date)

                    const value =
                        this.toIsoDate(date)

                    const disabled =
                        this.isBeforeToday(date)

                    return {
                        key: value,

                        value,

                        day: parts.day,

                        currentMonth:
                            this.isCurrentPersianMonth(
                                date
                            ),

                        isToday:
                            this.isToday(date),

                        disabled,

                        hasBookings: false,

                        isFull: false,
                    }
                }
            )
        },

        get jalaliMonthTitle() {
            return this.persianMonthFormatter.format(
                this.calendarAnchor
            )
        },

        isCurrentPersianMonth(date) {
            const anchor =
                this.persianParts(
                    this.calendarAnchor
                )

            const current =
                this.persianParts(date)

            return (
                anchor.year === current.year &&
                anchor.month === current.month
            )
        },

        isToday(date) {
            return this.sameDate(
                date,
                this.todayGregorian
            )
        },

        sameDate(first, second) {
            if (!first || !second) {
                return false
            }

            return (
                first.getFullYear() ===
                second.getFullYear() &&
                first.getMonth() ===
                second.getMonth() &&
                first.getDate() ===
                second.getDate()
            )
        },

        isBeforeToday(date) {
            return (
                date.getTime() <
                this.todayGregorian.getTime()
            )
        },

        previousJalaliMonth() {
            const current =
                this.findPersianMonthStart(
                    this.calendarAnchor
                )

            this.calendarAnchor =
                this.addDays(
                    current,
                    -20
                )

            this.calendarAnchor =
                this.findPersianMonthStart(
                    this.calendarAnchor
                )

            this.selectedDate = null

            this.selectedTime = null

            this.slots = []

            this.availabilityError = ''
        },

        nextJalaliMonth() {
            const current =
                this.findPersianMonthStart(
                    this.calendarAnchor
                )

            const next =
                this.addDays(
                    current,
                    40
                )

            this.calendarAnchor =
                this.findPersianMonthStart(
                    next
                )

            this.selectedDate = null

            this.selectedTime = null

            this.slots = []

            this.availabilityError = ''
        },

        selectCalendarDate(day) {
            if (
                !day ||
                day.disabled
            ) {
                return
            }

            this.selectedDate =
                day.value

            this.selectedTime = null

            this.formError = ''

            this.availabilityError = ''

            this.loadAvailability()
        },

        get formattedSelectedDate() {
            if (!this.selectedDate) {
                return ''
            }

            const date =
                this.selectedDateObject()

            if (!date) {
                return ''
            }

            return new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian',
                {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                }
            ).format(date)
        },

        selectedDateObject() {
            if (!this.selectedDate) {
                return null
            }

            const [
                year,
                month,
                day
            ] =
                this.selectedDate
                    .split('-')
                    .map(Number)

            return new Date(
                year,
                month - 1,
                day
            )
        },

        get availableSlots() {
            return this.slots.filter(
                slot => slot.available
            )
        },

        get bookedSlots() {
            return this.slots.filter(
                slot => !slot.available
            )
        },

        get formattedPrice() {
            const price =
                this.selectedService?.price

            if (
                price === null ||
                price === undefined
            ) {
                return '—'
            }

            return new Intl.NumberFormat(
                'fa-IR'
            ).format(
                Number(price)
            )
        },

        get canContinue() {
            if (this.step === 1) {
                return Boolean(
                    this.selectedServiceId &&
                    this.selectedBarberId
                )
            }

            if (this.step === 2) {
                return Boolean(
                    this.selectedDate &&
                    this.selectedTime
                )
            }

            if (this.step === 3) {
                return Boolean(
                    this.selectedServiceId &&
                    this.selectedBarberId &&
                    this.selectedDate &&
                    this.selectedTime
                )
            }

            return false
        },

        async loadAvailability() {
            if (
                !this.selectedServiceId ||
                !this.selectedBarberId ||
                !this.selectedDate ||
                !this.availabilityUrl
            ) {
                return
            }

            this.loadingSlots = true

            this.availabilityError = ''

            this.formError = ''

            this.slots = []

            try {
                const params =
                    new URLSearchParams({
                        barber_id:
                            String(
                                this.selectedBarberId
                            ),

                        service_id:
                            String(
                                this.selectedServiceId
                            ),

                        booking_date:
                        this.selectedDate,
                    })

                const response =
                    await fetch(
                        `${this.availabilityUrl}?${params.toString()}`,
                        {
                            method: 'GET',

                            headers: {
                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            credentials:
                                'same-origin',

                            cache: 'no-store',
                        }
                    )

                if (!response.ok) {
                    throw new Error(
                        'دریافت زمان‌های آزاد انجام نشد.'
                    )
                }

                const payload =
                    await response.json()

                this.slots =
                    Array.isArray(
                        payload?.slots
                    )
                        ? payload.slots.map(
                            slot => ({
                                start:
                                    this.formatTime(
                                        slot.start
                                    ),

                                end:
                                    this.formatTime(
                                        slot.end
                                    ),

                                available:
                                    Boolean(
                                        slot.available
                                    ),

                                status:
                                    slot.status ??
                                    (
                                        slot.available
                                            ? 'available'
                                            : 'booked'
                                    ),

                                label:
                                    slot.label ??
                                    (
                                        slot.available
                                            ? 'آزاد'
                                            : 'رزرو شده'
                                    ),
                            })
                        )
                        : []

            } catch (error) {
                console.error(
                    '[NOBAT] Availability:',
                    error
                )

                this.availabilityError =
                    error?.message ||
                    'ارتباط با سرور برقرار نشد.'
            } finally {
                this.loadingSlots = false
            }
        },

        selectTime(slot) {
            if (
                !slot ||
                !slot.available
            ) {
                return
            }

            this.selectedTime =
                slot.start

            this.formError = ''

            this.step = 3
        },

        nextStep() {
            this.formError = ''

            if (this.step === 1) {
                if (
                    !this.selectedServiceId ||
                    !this.selectedBarberId
                ) {
                    this.formError =
                        'خدمت و متخصص را انتخاب کن.'

                    return
                }

                this.step = 2

                return
            }

            if (this.step === 2) {
                if (
                    !this.selectedDate
                ) {
                    this.formError =
                        'یک تاریخ را انتخاب کن.'

                    return
                }

                if (
                    !this.selectedTime
                ) {
                    this.formError =
                        'یک ساعت را انتخاب کن.'

                    return
                }

                this.step = 3
            }
        },

        previousStep() {
            this.formError = ''

            if (this.step <= 1) {
                return
            }

            this.step -= 1
        },

        submit() {
            if (this.submitting) {
                return
            }

            this.formError = ''

            if (
                !this.selectedServiceId ||
                !this.selectedBarberId
            ) {
                this.step = 1

                this.formError =
                    'اطلاعات خدمت و متخصص کامل نیست.'

                return
            }

            if (
                !this.selectedDate ||
                !this.selectedTime
            ) {
                this.step = 2

                this.formError =
                    'تاریخ و ساعت را انتخاب کن.'

                return
            }

            const selectedSlot =
                this.slots.find(
                    slot =>
                        slot.start ===
                        this.selectedTime
                )

            if (
                !selectedSlot ||
                !selectedSlot.available
            ) {
                this.step = 2

                this.formError =
                    'این زمان دیگر قابل رزرو نیست.'

                this.loadAvailability()

                return
            }

            const form =
                this.$refs.prepareForm

            if (!form) {
                this.formError =
                    'فرم رزرو پیدا نشد.'

                return
            }

            this.submitting = true

            form.submit()
        },
    })
)
jalaliParts(iso) {
    const date = new Date(
        `${iso}T12:00:00Z`
    );

    const parts = new Intl.DateTimeFormat(
        'fa-IR-u-ca-persian-nu-latn',
        {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            timeZone: 'UTC',
        }
    ).formatToParts(date);

    const result = {};

    parts.forEach(part => {
        if (
            part.type === 'year' ||
            part.type === 'month' ||
            part.type === 'day'
        ) {
            result[part.type] = Number(
                part.value
            );
        }
    });

    return result;
},

jalaliWeekday(iso) {
    const date = new Date(
        `${iso}T12:00:00Z`
    );

    /*
     * JS:
     * Sunday = 0
     *
     * App:
     * Saturday = 0
     */

    return (
        date.getUTCDay() + 1
    ) % 7;
},

addGregorianDays(iso, amount) {
    const date = new Date(
        `${iso}T12:00:00Z`
    );

    date.setUTCDate(
        date.getUTCDate() + amount
    );

    const year =
        date.getUTCFullYear();

    const month = String(
        date.getUTCMonth() + 1
    ).padStart(2, '0');

    const day = String(
        date.getUTCDate()
    ).padStart(2, '0');

    return `${year}-${month}-${day}`;
},

findJalaliMonthStart(iso) {
    const target = this.jalaliParts(iso);

    let cursor = iso;

    for (let i = 0; i < 370; i++) {
        const current =
            this.jalaliParts(cursor);

        if (
            current.year === target.year &&
            current.month === target.month &&
            current.day === 1
        ) {
            return cursor;
        }

        cursor =
            this.addGregorianDays(
                cursor,
                -1
            );
    }

    return iso;
},

initCalendar() {
    const iso =
        this.selectedDate ||
        this.todayIso;

    this.calendarFirstIso =
        this.findJalaliMonthStart(
            iso
        );
},

calendarTitle() {
    if (!this.calendarFirstIso) {
        return '';
    }

    const parts =
        this.jalaliParts(
            this.calendarFirstIso
        );

    return this.jalaliMonths[
    parts.month - 1
        ];
},

calendarYear() {
    if (!this.calendarFirstIso) {
        return '';
    }

    return this.persianDigits(
        this.jalaliParts(
            this.calendarFirstIso
        ).year
    );
},

calendarCells() {
    if (!this.calendarFirstIso) {
        return [];
    }

    const first =
        this.jalaliParts(
            this.calendarFirstIso
        );

    const offset =
        this.jalaliWeekday(
            this.calendarFirstIso
        );

    const cells = [];

    for (let i = 0; i < offset; i++) {
        cells.push(null);
    }

    let cursor =
        this.calendarFirstIso;

    for (let i = 0; i < 31; i++) {
        const current =
            this.jalaliParts(cursor);

        if (
            current.year !== first.year ||
            current.month !== first.month
        ) {
            break;
        }

        cells.push({
            iso: cursor,

            year: current.year,

            month: current.month,

            day: current.day,

            today:
                cursor === this.todayIso,

            selected:
                cursor === this.selectedDate,

            past:
                cursor < this.todayIso,
        });

        cursor =
            this.addGregorianDays(
                cursor,
                1
            );
    }

    return cells;
},

openCalendar() {
    if (!this.calendarFirstIso) {
        this.initCalendar();
    }

    this.calendarOpen = true;
},

selectJalaliDate(day) {
    if (!day || day.past) {
        return;
    }

    this.selectedDate =
        day.iso;

    this.selectedTime = '';

    this.calendarOpen = false;

    this.loadSlots();
},

nextJalaliMonth() {
    if (!this.calendarFirstIso) {
        this.initCalendar();
    }

    let cursor =
        this.addGregorianDays(
            this.calendarFirstIso,
            32
        );

    this.calendarFirstIso =
        this.findJalaliMonthStart(
            cursor
        );
},

previousJalaliMonth() {
    if (!this.calendarFirstIso) {
        this.initCalendar();
    }

    let cursor =
        this.addGregorianDays(
            this.calendarFirstIso,
            -1
        );

    this.calendarFirstIso =
        this.findJalaliMonthStart(
            cursor
        );
},

goToToday() {
    this.selectedDate =
        this.todayIso;

    this.selectedTime = '';

    this.initCalendar();

    this.calendarOpen = false;

    this.loadSlots();
},

jalaliDate(value) {
    if (!value) {
        return '';
    }

    const date =
        this.jalaliParts(value);

    if (!date) {
        return '';
    }

    const weekdays = [
        'شنبه',
        'یکشنبه',
        'دوشنبه',
        'سه‌شنبه',
        'چهارشنبه',
        'پنجشنبه',
        'جمعه',
    ];

    return (
        `${weekdays[this.jalaliWeekday(value)]} ` +
        `${this.jalaliMonths[date.month - 1]} ` +
        `${this.persianDigits(date.day)} ` +
        `${this.persianDigits(date.year)}`
    );
},
/* =========================================================
 | Global Alpine boot
 ========================================================= */

Alpine.start()
