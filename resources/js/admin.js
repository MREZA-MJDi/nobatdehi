import Alpine from 'alpinejs'
import axios from 'axios'

/* ========================================================================== 
   NOBAT FRONTEND RUNTIME
   Shared foundation used by customer / salon / admin pages.
   Role-specific behaviour lives in the matching entry file.
   ========================================================================== */

window.Alpine = Alpine
window.axios = axios

window.axios.defaults.headers.common[
    'X-Requested-With'
] = 'XMLHttpRequest'

/* ========================================================================== 
   THEME
   ========================================================================== */

const THEME_STORAGE_KEY = 'nobatdehi_theme'

function normalizeTheme(theme) {
    return theme === 'dark' ? 'dark' : 'light'
}

function getInitialTheme() {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY)

        if (stored === 'dark' || stored === 'light') {
            return stored
        }
    } catch (_) {
        // Storage can be unavailable.
    }

    try {
        return window.matchMedia?.(
            '(prefers-color-scheme: dark)'
        )?.matches
            ? 'dark'
            : 'light'
    } catch (_) {
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
        } catch (_) {
            // Ignore storage failures.
        }
    }

    return normalized
}

const initialTheme = getInitialTheme()

applyTheme(initialTheme, false)

Alpine.store('theme', {
    current: initialTheme,

    toggle() {
        this.current = applyTheme(
            this.current === 'dark'
                ? 'light'
                : 'dark'
        )
    },

    set(theme) {
        this.current = applyTheme(theme)
    },
})

/* ========================================================================== 
   TOAST
   ========================================================================== */

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
                'danger',
                duration
            )
        },

        danger(message, duration = 4500) {
            return this.show(
                message,
                'danger',
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
) => Alpine.store('toast').show(
    message,
    type,
    duration
)

/* ========================================================================== 
   OTP FORM
   ========================================================================== */

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
        this.$refs.firstInput?.focus()
    },

    normalize(value) {
        return String(value ?? '')
            .replace(
                /[۰-۹]/g,
                char => '۰۱۲۳۴۵۶۷۸۹'.indexOf(char)
            )
            .replace(/[^\d]/g, '')
            .slice(0, this.digits)
    },

    handleInput(index, event) {
        const value = this.normalize(
            event.target.value
        )

        event.target.value = value

        if (value.length > 0) {
            this.$refs[
                `input-${index + 1}`
            ]?.focus()
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

        pasted.split('').forEach((char, index) => {
            const input = this.$refs[
                `input-${index + 1}`
            ]

            if (input) {
                input.value = char
            }
        })

        this.syncCode()

        this.$refs[
            `input-${Math.min(pasted.length, this.digits)}`
        ]?.focus()
    },

    handleBackspace(index, event) {
        if (
            event.key === 'Backspace' &&
            !event.target.value
        ) {
            this.$refs[
                `input-${index - 1}`
            ]?.focus()
        }
    },

    syncCode() {
        const values = []

        for (let index = 1; index <= this.digits; index++) {
            values.push(
                this.$refs[
                    `input-${index}`
                ]?.value ?? ''
            )
        }

        this.code = values.join('')
    },

    isComplete() {
        return this.code.length === this.digits
    },
}))

/* ========================================================================== 
   MANUAL SALON BOOKING
   ========================================================================== 
   The manual booking Blade keeps the selected availability slot in Alpine
   state. Before the native form is submitted, mirror that exact start time
   into a real form field so Laravel's ManualBookingRequest receives it.
   ========================================================================== */

document.addEventListener('submit', (event) => {
    const form = event.target

    if (!(form instanceof HTMLFormElement)) {
        return
    }

    const manualBookingRoot = form.querySelector(
        '[x-data="salonManualBooking()"]'
    )

    if (!manualBookingRoot) {
        return
    }

    const state = manualBookingRoot._x_dataStack?.[0]
    const selectedTime = String(state?.selectedTime ?? '')
        .trim()
        .slice(0, 5)

    if (!selectedTime) {
        return
    }

    let input = form.querySelector('input[name="start_time"]')

    if (!input) {
        input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'start_time'
        form.appendChild(input)
    }

    input.value = selectedTime
}, false)

/* ========================================================================== 
   ALPINE BOOT
   ========================================================================== 
   This file is loaded before role-specific bundles. Inline Alpine data in
   Blade therefore exists before Alpine scans the DOM.
   ========================================================================== */

Alpine.start()
