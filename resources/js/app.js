import './bootstrap'

import Alpine from 'alpinejs'

window.Alpine = Alpine


/*
|--------------------------------------------------------------------------
| Toast
|--------------------------------------------------------------------------
*/

Alpine.store('toast', {
    items: [],

    show(message, type = 'success', duration = 3500) {
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
        this.items = this.items.filter(
            item => item.id !== id
        )
    },

    success(message) {
        this.show(message, 'success')
    },

    error(message) {
        this.show(message, 'danger')
    },

    warning(message) {
        this.show(message, 'warning')
    },

    info(message) {
        this.show(message, 'info')
    },
})


window.toast = {
    success(message) {
        Alpine.store('toast').success(message)
    },

    error(message) {
        Alpine.store('toast').error(message)
    },

    warning(message) {
        Alpine.store('toast').warning(message)
    },

    info(message) {
        Alpine.store('toast').info(message)
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
        this.code = this.code
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


Alpine.start()
