import './bootstrap'

import Alpine from 'alpinejs'

window.Alpine = Alpine

/* =========================================================
 | Theme
 ========================================================= */

const THEME_STORAGE_KEY = 'nobatdehi_theme'

function applyTheme(theme) {
    const normalized = theme === 'dark' ? 'dark' : 'light'

    document.documentElement.dataset.theme = normalized

    try {
        localStorage.setItem(THEME_STORAGE_KEY, normalized)
    } catch {
        // localStorage may be unavailable in some browser contexts.
    }
}

function getStoredTheme() {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY)

        if (stored === 'dark' || stored === 'light') {
            return stored
        }
    } catch {
        // Ignore storage errors.
    }

    return window.matchMedia?.('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light'
}

applyTheme(getStoredTheme())

Alpine.store('theme', {
    current: getStoredTheme(),

    init() {
        this.current = getStoredTheme()
        applyTheme(this.current)
    },

    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark'
        applyTheme(this.current)
    },

    set(theme) {
        this.current = theme === 'dark' ? 'dark' : 'light'
        applyTheme(this.current)
    },
})


/* =========================================================
 | Toast
 ========================================================= */

function createToastStore() {
    return {
        items: [],

        show(message, type = 'default', duration = 3500) {
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`

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
            return this.show(message, 'success', duration)
        },

        error(message, duration = 4500) {
            return this.show(message, 'error', duration)
        },

        warning(message, duration = 4000) {
            return this.show(message, 'warning', duration)
        },

        info(message, duration = 3500) {
            return this.show(message, 'info', duration)
        },

        remove(id) {
            this.items = this.items.filter(item => item.id !== id)
        },
    }
}

Alpine.store('toast', createToastStore())

window.toast = (message, type = 'default', duration = 3500) => {
    return Alpine.store('toast').show(message, type, duration)
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
            .replace(/[۰-۹]/g, char => '۰۱۲۳۴۵۶۷۸۹'.indexOf(char))
            .replace(/[^\d]/g, '')
            .slice(0, this.digits)
    },

    handleInput(index, event) {
        const value = this.normalize(event.target.value)

        event.target.value = value

        if (value.length > 0) {
            const next = this.$refs[`input-${index + 1}`]

            if (next) {
                next.focus()
            }
        }

        this.syncCode()
    },

    handlePaste(event) {
        event.preventDefault()

        const pasted = this.normalize(
            event.clipboardData?.getData('text') ?? ''
        )

        if (!pasted) {
            return
        }

        const chars = pasted.split('')

        chars.forEach((char, index) => {
            const input = this.$refs[`input-${index + 1}`]

            if (input) {
                input.value = char
            }
        })

        this.syncCode()

        const target = this.$refs[`input-${Math.min(chars.length, this.digits)}`]

        if (target) {
            target.focus()
        }
    },

    handleBackspace(index, event) {
        if (
            event.key === 'Backspace' &&
            !event.target.value
        ) {
            const previous = this.$refs[`input-${index - 1}`]

            if (previous) {
                previous.focus()
            }
        }
    },

    syncCode() {
        const values = []

        for (let index = 1; index <= this.digits; index++) {
            const input = this.$refs[`input-${index}`]

            values.push(input?.value ?? '')
        }

        this.code = values.join('')
    },

    isComplete() {
        return this.code.length === this.digits
    },
}))


/* =========================================================
 | Salon Booking Modal
 |
 | Used by:
 | resources/views/public/salon/partials/booking-modal.blade.php
 ========================================================= */

Alpine.data('salonBookingModal', config => ({
    /* -----------------------------------------------------
     | Config
     ----------------------------------------------------- */

    salonId: Number(config?.salonId ?? 0),
    salonName: config?.salonName ?? '',
    availabilityUrl: config?.availabilityUrl ?? '',
    prepareUrl: config?.prepareUrl ?? '',

    services: Array.isArray(config?.services)
        ? config.services
        : [],

    barbers: Array.isArray(config?.barbers)
        ? config.barbers
        : [],


    /* -----------------------------------------------------
     | UI state
     ----------------------------------------------------- */

    openState: false,
    step: 1,
    loading: false,
    submitting: false,
    error: '',

    selectedServiceId: null,
    selectedBarberId: null,

    selectedDate: null,
    selectedTime: null,

    notes: '',

    slots: [],

    calendarAnchor: null,

    todayGregorian: null,


    /* -----------------------------------------------------
     | Lifecycle
     ----------------------------------------------------- */

    init() {
        this.todayGregorian = this.startOfDay(new Date())
        this.calendarAnchor = new Date(this.todayGregorian)

        this.buildCalendar()

        this.boundOpen = event => {
            const serviceId = event?.detail?.serviceId ?? null

            this.open(serviceId)
        }

        this.boundEscape = event => {
            if (event.key === 'Escape' && this.openState) {
                this.close()
            }
        }

        window.addEventListener('open-booking', this.boundOpen)
        window.addEventListener('open-booking-with-service', this.boundOpen)
        document.addEventListener('keydown', this.boundEscape)
    },

    destroy() {
        window.removeEventListener('open-booking', this.boundOpen)
        window.removeEventListener(
            'open-booking-with-service',
            this.boundOpen
        )

        document.removeEventListener('keydown', this.boundEscape)

        this.unlockBody()
    },


    /* -----------------------------------------------------
     | Modal
     ----------------------------------------------------- */

    open(serviceId = null) {
        this.reset()

        this.openState = true

        this.lockBody()

        this.$nextTick(() => {
            const firstServiceId = serviceId
                ? Number(serviceId)
                : null

            if (firstServiceId && this.findService(firstServiceId)) {
                this.selectService(firstServiceId)
            }
        })
    },

    reset() {
        this.step = 1
        this.loading = false
        this.submitting = false
        this.error = ''

        this.selectedServiceId = null
        this.selectedBarberId = null

        this.selectedDate = null
        this.selectedTime = null

        this.notes = ''

        this.slots = []

        this.todayGregorian = this.startOfDay(new Date())
        this.calendarAnchor = new Date(this.todayGregorian)

        this.buildCalendar()
    },

    close() {
        this.openState = false
        this.loading = false
        this.submitting = false
        this.error = ''

        this.unlockBody()
    },

    lockBody() {
        document.documentElement.classList.add('salon-booking-open')
        document.body.classList.add('salon-booking-open')
    },

    unlockBody() {
        document.documentElement.classList.remove('salon-booking-open')
        document.body.classList.remove('salon-booking-open')
    },


    /* -----------------------------------------------------
     | Helpers
     ----------------------------------------------------- */

    startOfDay(date) {
        const result = new Date(date)

        result.setHours(0, 0, 0, 0)

        return result
    },

    cloneDate(date) {
        return new Date(date.getTime())
    },

    addDays(date, amount) {
        const result = new Date(date)

        result.setDate(result.getDate() + amount)

        return this.startOfDay(result)
    },

    toIsoDate(date) {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')

        return `${year}-${month}-${day}`
    },

    formatTime(time) {
        if (!time) {
            return ''
        }

        return String(time).slice(0, 5)
    },

    formatPrice(price) {
        if (price === null || price === undefined || price === '') {
            return '—'
        }

        const numeric = Number(price)

        if (!Number.isFinite(numeric)) {
            return String(price)
        }

        return new Intl.NumberFormat('fa-IR').format(numeric)
    },

    escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;')
    },


    /* -----------------------------------------------------
     | Services
     ----------------------------------------------------- */

    findService(id) {
        return this.services.find(
            service => Number(service.id) === Number(id)
        ) ?? null
    },

    selectedService() {
        return this.findService(this.selectedServiceId)
    },

    selectService(id) {
        const service = this.findService(id)

        if (!service) {
            return
        }

        this.selectedServiceId = Number(service.id)

        this.selectedBarberId = null
        this.selectedDate = null
        this.selectedTime = null

        this.slots = []
        this.error = ''

        this.step = 2
    },


    /* -----------------------------------------------------
     | Barbers
     ----------------------------------------------------- */

    findBarber(id) {
        return this.barbers.find(
            barber => Number(barber.id) === Number(id)
        ) ?? null
    },

    selectedBarber() {
        return this.findBarber(this.selectedBarberId)
    },

    selectBarber(id) {
        const barber = this.findBarber(id)

        if (!barber) {
            return
        }

        this.selectedBarberId = Number(barber.id)

        this.selectedDate = null
        this.selectedTime = null

        this.slots = []
        this.error = ''

        this.step = 3

        this.$nextTick(() => {
            this.buildCalendar()
        })
    },


    /* -----------------------------------------------------
     | Persian / Jalali calendar
     ----------------------------------------------------- */

    persianFormatter: new Intl.DateTimeFormat(
        'fa-IR-u-ca-persian',
        {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        }
    ),

    persianMonthFormatter: new Intl.DateTimeFormat(
        'fa-IR-u-ca-persian',
        {
            year: 'numeric',
            month: 'long',
        }
    ),

    persianParts(date) {
        const parts = this.persianFormatter.formatToParts(date)

        const result = {}

        for (const part of parts) {
            if (
                part.type === 'year' ||
                part.type === 'month' ||
                part.type === 'day'
            ) {
                result[part.type] = Number(
                    String(part.value).replace(/[^\d]/g, '')
                )
            }
        }

        return result
    },

    persianMonthLabel(date) {
        return this.persianMonthFormatter.format(date)
    },

    persianWeekdayLabel(date) {
        return new Intl.DateTimeFormat(
            'fa-IR-u-ca-persian',
            {
                weekday: 'short',
            }
        ).format(date)
    },

    findPersianMonthStart(anchor) {
        let cursor = this.startOfDay(anchor)

        for (let i = 0; i < 45; i++) {
            const parts = this.persianParts(cursor)

            if (parts.day === 1) {
                return cursor
            }

            cursor = this.addDays(cursor, -1)
        }

        return this.startOfDay(anchor)
    },

    findPersianMonthEnd(monthStart) {
        let cursor = this.cloneDate(monthStart)

        for (let i = 0; i < 40; i++) {
            const next = this.addDays(cursor, 1)

            const currentParts = this.persianParts(cursor)
            const nextParts = this.persianParts(next)

            if (
                nextParts.month !== currentParts.month ||
                nextParts.year !== currentParts.year
            ) {
                return cursor
            }

            cursor = next
        }

        return cursor
    },

    getCalendarGrid() {
        const monthStart = this.findPersianMonthStart(this.calendarAnchor)
        const monthStartWeekday = monthStart.getDay()

        // JS: Sunday=0 ... Saturday=6
        // Our Persian calendar starts Saturday.
        const leadingDays = (monthStartWeekday + 1) % 7

        const firstCell = this.addDays(monthStart, -leadingDays)

        const cells = []

        for (let i = 0; i < 42; i++) {
            cells.push(this.addDays(firstCell, i))
        }

        return cells
    },

    buildCalendar() {
        const monthStart = this.findPersianMonthStart(this.calendarAnchor)

        this.calendarAnchor = monthStart
    },

    previousMonth() {
        const currentStart = this.findPersianMonthStart(
            this.calendarAnchor
        )

        this.calendarAnchor = this.addDays(
            currentStart,
            -20
        )

        this.calendarAnchor = this.findPersianMonthStart(
            this.calendarAnchor
        )

        this.selectedDate = null
        this.selectedTime = null
        this.slots = []
        this.error = ''
    },

    nextMonth() {
        const currentEnd = this.findPersianMonthEnd(
            this.calendarAnchor
        )

        this.calendarAnchor = this.addDays(
            currentEnd,
            12
        )

        this.calendarAnchor = this.findPersianMonthStart(
            this.calendarAnchor
        )

        this.selectedDate = null
        this.selectedTime = null
        this.slots = []
        this.error = ''
    },

    isSameDate(first, second) {
        if (!first || !second) {
            return false
        }

        return (
            first.getFullYear() === second.getFullYear() &&
            first.getMonth() === second.getMonth() &&
            first.getDate() === second.getDate()
        )
    },

    isToday(date) {
        return this.isSameDate(date, this.todayGregorian)
    },

    isBeforeToday(date) {
        return date.getTime() < this.todayGregorian.getTime()
    },

    isCurrentPersianMonth(date) {
        const anchorParts = this.persianParts(this.calendarAnchor)
        const dateParts = this.persianParts(date)

        return (
            anchorParts.year === dateParts.year &&
            anchorParts.month === dateParts.month
        )
    },

    selectCalendarDate(date) {
        if (!date) {
            return
        }

        const normalized = this.startOfDay(date)

        if (this.isBeforeToday(normalized)) {
            return
        }

        this.selectedDate = this.toIsoDate(normalized)
        this.selectedTime = null
        this.error = ''

        this.loadAvailability()
    },

    selectedDateObject() {
        if (!this.selectedDate) {
            return null
        }

        const [year, month, day] = this.selectedDate
            .split('-')
            .map(Number)

        return new Date(year, month - 1, day)
    },

    selectedPersianDate() {
        const date = this.selectedDateObject()

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


    /* -----------------------------------------------------
     | Availability
     ----------------------------------------------------- */

    async loadAvailability() {
        if (
            !this.selectedBarberId ||
            !this.selectedServiceId ||
            !this.selectedDate ||
            !this.availabilityUrl
        ) {
            return
        }

        this.loading = true
        this.error = ''
        this.selectedTime = null
        this.slots = []

        try {
            const params = new URLSearchParams({
                barber_id: String(this.selectedBarberId),
                service_id: String(this.selectedServiceId),
                booking_date: this.selectedDate,
            })

            const response = await fetch(
                `${this.availabilityUrl}?${params.toString()}`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    cache: 'no-store',
                }
            )

            if (!response.ok) {
                let message = 'دریافت زمان‌های نوبت انجام نشد.'

                try {
                    const payload = await response.json()

                    if (payload?.message) {
                        message = payload.message
                    }

                    if (payload?.errors) {
                        const firstError = Object.values(
                            payload.errors
                        )[0]?.[0]

                        if (firstError) {
                            message = firstError
                        }
                    }
                } catch {
                    // Response was not JSON.
                }

                throw new Error(message)
            }

            const payload = await response.json()

            this.slots = Array.isArray(payload?.slots)
                ? payload.slots.map(slot => ({
                    start: this.formatTime(slot.start),
                    end: this.formatTime(slot.end),
                    available: Boolean(slot.available),
                    status: slot.status ?? (
                        slot.available
                            ? 'available'
                            : 'booked'
                    ),
                    label: slot.label ?? (
                        slot.available
                            ? 'آزاد'
                            : 'رزرو شده'
                    ),
                }))
                : []

            if (this.slots.length === 0) {
                this.error = 'برای این روز، زمانی برای رزرو وجود ندارد.'
            }
        } catch (error) {
            console.error('[NOBAT] Booking availability error:', error)

            this.error = error?.message
                || 'ارتباط با سرور برای دریافت زمان‌ها برقرار نشد.'
        } finally {
            this.loading = false
        }
    },

    availableSlots() {
        return this.slots.filter(slot => slot.available)
    },

    bookedSlots() {
        return this.slots.filter(slot => !slot.available)
    },

    selectTime(slot) {
        if (!slot || !slot.available) {
            return
        }

        this.selectedTime = slot.start
        this.error = ''

        this.step = 5
    },


    /* -----------------------------------------------------
     | Navigation
     ----------------------------------------------------- */

    canContinue() {
        switch (this.step) {
            case 1:
                return Boolean(this.selectedServiceId)

            case 2:
                return Boolean(this.selectedBarberId)

            case 3:
                return Boolean(this.selectedDate)

            case 4:
                return Boolean(this.selectedTime)

            case 5:
                return (
                    Boolean(this.selectedServiceId) &&
                    Boolean(this.selectedBarberId) &&
                    Boolean(this.selectedDate) &&
                    Boolean(this.selectedTime)
                )

            default:
                return false
        }
    },

    async nextStep() {
        this.error = ''

        if (this.step === 1) {
            if (!this.selectedServiceId) {
                this.error = 'اول یک خدمت را انتخاب کن.'
                return
            }

            this.step = 2
            return
        }

        if (this.step === 2) {
            if (!this.selectedBarberId) {
                this.error = 'متخصص موردنظرت را انتخاب کن.'
                return
            }

            this.step = 3
            return
        }

        if (this.step === 3) {
            if (!this.selectedDate) {
                this.error = 'یک روز را انتخاب کن.'
                return
            }

            await this.loadAvailability()

            if (this.availableSlots().length === 0) {
                return
            }

            this.step = 4
            return
        }

        if (this.step === 4) {
            if (!this.selectedTime) {
                this.error = 'یک ساعت را انتخاب کن.'
                return
            }

            this.step = 5
        }
    },

    previousStep() {
        this.error = ''

        if (this.step <= 1) {
            return
        }

        this.step -= 1
    },


    /* -----------------------------------------------------
     | Summary
     ----------------------------------------------------- */

    summary() {
        const service = this.selectedService()
        const barber = this.selectedBarber()

        return {
            service,
            barber,
            date: this.selectedPersianDate(),
            time: this.selectedTime,
        }
    },


    /* -----------------------------------------------------
     | Submit legacy prepare flow
     |
     | Current backend flow:
     | prepare -> login if guest -> confirm/store
     |
     | We keep this contract until the direct booking API
     | replaces it.
     ----------------------------------------------------- */

    async submit() {
        if (this.submitting) {
            return
        }

        this.error = ''

        if (!this.selectedServiceId) {
            this.error = 'خدمت انتخاب نشده است.'
            this.step = 1
            return
        }

        if (!this.selectedBarberId) {
            this.error = 'متخصص انتخاب نشده است.'
            this.step = 2
            return
        }

        if (!this.selectedDate) {
            this.error = 'تاریخ انتخاب نشده است.'
            this.step = 3
            return
        }

        if (!this.selectedTime) {
            this.error = 'ساعت انتخاب نشده است.'
            this.step = 4
            return
        }

        const selectedSlot = this.slots.find(
            slot => (
                slot.start === this.selectedTime
            )
        )

        if (!selectedSlot?.available) {
            this.error = 'این ساعت دیگر قابل رزرو نیست. لطفاً زمان دیگری را انتخاب کن.'

            await this.loadAvailability()

            this.step = 4

            return
        }

        const form = this.$refs.prepareForm

        if (!form) {
            this.error = 'فرم رزرو پیدا نشد.'
            return
        }

        this.submitting = true

        try {
            this.setFormValue(
                form,
                'salon_id',
                this.salonId
            )

            this.setFormValue(
                form,
                'barber_id',
                this.selectedBarberId
            )

            this.setFormValue(
                form,
                'service_id',
                this.selectedServiceId
            )

            this.setFormValue(
                form,
                'booking_date',
                this.selectedDate
            )

            this.setFormValue(
                form,
                'start_time',
                this.selectedTime
            )

            this.setFormValue(
                form,
                'notes',
                this.notes
            )

            form.submit()
        } catch (error) {
            console.error('[NOBAT] Booking submit error:', error)

            this.error = 'ارسال درخواست رزرو انجام نشد.'
            this.submitting = false
        }
    },

    setFormValue(form, name, value) {
        const input = form.elements.namedItem(name)

        if (input) {
            input.value = value ?? ''
            return
        }

        const hidden = document.createElement('input')

        hidden.type = 'hidden'
        hidden.name = name
        hidden.value = value ?? ''

        form.appendChild(hidden)
    },
}))


/* =========================================================
 | Global Alpine boot
 ========================================================= */

Alpine.store('theme').init()

Alpine.start()
