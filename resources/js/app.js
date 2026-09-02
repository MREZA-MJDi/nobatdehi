import './bootstrap'

import Alpine from 'alpinejs'

window.Alpine = Alpine


/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

const THEME_KEY = 'nobatdehi_theme'

function getSavedTheme() {
    return localStorage.getItem(THEME_KEY) === 'dark'
        ? 'dark'
        : 'light'
}

function applyTheme(theme, animate = false) {
    if (animate) {
        document.documentElement.classList.add(
            'theme-transition'
        )
    }

    document.documentElement.dataset.theme = theme

    localStorage.setItem(
        THEME_KEY,
        theme
    )

    window.setTimeout(() => {
        document.documentElement.classList.remove(
            'theme-transition'
        )
    }, 250)
}

Alpine.store('theme', {

    current: getSavedTheme(),

    init() {
        applyTheme(this.current)
    },

    toggle() {
        this.current =
            this.current === 'dark'
                ? 'light'
                : 'dark'

        applyTheme(
            this.current,
            true
        )
    },

    set(theme) {
        if (
            theme !== 'light' &&
            theme !== 'dark'
        ) {
            return
        }

        this.current = theme

        applyTheme(
            theme,
            true
        )
    },

})


/*
|--------------------------------------------------------------------------
| Toast
|--------------------------------------------------------------------------
*/

Alpine.store('toast', {

    items: [],

    show(
        message,
        type = 'success',
        duration = 3500
    ) {

        const id =
            `${Date.now()}-${Math.random()
                .toString(36)
                .slice(2)}`

        this.items.push({
            id,
            message,
            type,
        })

        window.setTimeout(() => {
            this.remove(id)
        }, duration)

    },

    remove(id) {

        this.items =
            this.items.filter(
                item => item.id !== id
            )

    },

    success(message) {
        this.show(
            message,
            'success'
        )
    },

    error(message) {
        this.show(
            message,
            'danger'
        )
    },

    warning(message) {
        this.show(
            message,
            'warning'
        )
    },

    info(message) {
        this.show(
            message,
            'info'
        )
    },

})


window.toast = {

    success(message) {
        Alpine.store('toast')
            .success(message)
    },

    error(message) {
        Alpine.store('toast')
            .error(message)
    },

    warning(message) {
        Alpine.store('toast')
            .warning(message)
    },

    info(message) {
        Alpine.store('toast')
            .info(message)
    },

}


/*
|--------------------------------------------------------------------------
| OTP
|--------------------------------------------------------------------------
*/

Alpine.data('otpForm', () => ({

    code: '',

    sanitize() {

        this.code =
            this.code
                .replace(/\D/g, '')
                .slice(0, 6)

    },

    autoSubmit() {

        this.sanitize()

        if (this.code.length === 6) {

            this.$nextTick(() => {
                this.$refs.form.submit()
            })

        }

    },

}))


/*
|--------------------------------------------------------------------------
| Start
|--------------------------------------------------------------------------
*/

Alpine.store('theme').init()

Alpine.start()
