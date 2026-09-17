import './bootstrap';
import './customer';
import './discover';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_STORAGE_KEY = 'nobatdehi_theme';

function normalizeTheme(theme) {
    return theme === 'dark' ? 'dark' : 'light';
}

function getStoredTheme() {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);

        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
    } catch {
        // localStorage may be unavailable.
    }

    try {
        return window.matchMedia?.('(prefers-color-scheme: dark)')?.matches
            ? 'dark'
            : 'light';
    } catch {
        return 'light';
    }
}

function applyTheme(theme, persist = true) {
    const normalized = normalizeTheme(theme);

    document.documentElement.dataset.theme = normalized;

    if (persist) {
        try {
            localStorage.setItem(THEME_STORAGE_KEY, normalized);
        } catch {
            // Ignore storage errors.
        }
    }

    return normalized;
}

const initialTheme = getStoredTheme();
applyTheme(initialTheme, false);

Alpine.store('theme', {
    current: initialTheme,

    toggle() {
        const nextTheme = this.current === 'dark' ? 'light' : 'dark';
        this.current = applyTheme(nextTheme);
    },

    set(theme) {
        this.current = applyTheme(theme);
    },
});

function createToastStore() {
    return {
        items: [],

        show(message, type = 'default', duration = 3500) {
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;

            this.items.push({
                id,
                message,
                type,
            });

            window.setTimeout(() => {
                this.remove(id);
            }, duration);

            return id;
        },

        success(message, duration = 3500) {
            return this.show(message, 'success', duration);
        },

        error(message, duration = 4500) {
            return this.show(message, 'error', duration);
        },

        warning(message, duration = 4000) {
            return this.show(message, 'warning', duration);
        },

        info(message, duration = 3500) {
            return this.show(message, 'info', duration);
        },

        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        },
    };
}

Alpine.store('toast', createToastStore());

window.toast = (message, type = 'default', duration = 3500) => {
    return Alpine.store('toast').show(message, type, duration);
};

Alpine.data('otpForm', () => ({
    code: '',
    loading: false,
    error: '',
    digits: 6,

    init() {
        this.$nextTick(() => this.focusFirstInput());
    },

    focusFirstInput() {
        this.$refs.firstInput?.focus();
    },

    normalize(value) {
        return String(value ?? '')
            .replace(/[۰-۹]/g, char => '۰۱۲۳۴۵۶۷۸۹'.indexOf(char))
            .replace(/[^\d]/g, '')
            .slice(0, this.digits);
    },

    handleInput(index, event) {
        const value = this.normalize(event.target.value);
        event.target.value = value;

        if (value.length > 0) {
            this.$refs[`input-${index + 1}`]?.focus();
        }

        this.syncCode();
    },

    handlePaste(event) {
        event.preventDefault();

        const pasted = this.normalize(event.clipboardData?.getData('text') ?? '');

        if (!pasted) {
            return;
        }

        pasted.split('').forEach((char, index) => {
            const input = this.$refs[`input-${index + 1}`];

            if (input) {
                input.value = char;
            }
        });

        this.syncCode();
        this.$refs[`input-${Math.min(pasted.length, this.digits)}`]?.focus();
    },

    handleBackspace(index, event) {
        if (event.key === 'Backspace' && !event.target.value) {
            this.$refs[`input-${index - 1}`]?.focus();
        }
    },

    syncCode() {
        const values = [];

        for (let index = 1; index <= this.digits; index++) {
            values.push(this.$refs[`input-${index}`]?.value ?? '');
        }

        this.code = values.join('');
    },

    isComplete() {
        return this.code.length === this.digits;
    },
}));

/*
 |----------------------------------------------------------------------
 | Manual salon booking submission
 |----------------------------------------------------------------------
 | The manual booking page keeps the selected slot in Alpine state. Make
 | sure the native form also receives that exact value as start_time.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const manualBookingRoot = form.querySelector(
        '[x-data="salonManualBooking()"]'
    );

    if (!manualBookingRoot) {
        return;
    }

    const state = manualBookingRoot._x_dataStack?.[0];
    const selectedTime = String(state?.selectedTime ?? '')
        .trim()
        .slice(0, 5);

    if (!selectedTime) {
        return;
    }

    let input = form.querySelector('input[name="start_time"]');

    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'start_time';
        form.appendChild(input);
    }

    input.value = selectedTime;
}, false);

Alpine.start();
