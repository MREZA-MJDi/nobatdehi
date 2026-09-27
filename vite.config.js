import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/css/customer.css',
                'resources/js/customer.js',

                'resources/css/discovery.css',
                'resources/js/discover.js',

                'resources/css/salon.css',
                'resources/js/salon.js',

                'resources/css/salon-owner.css',
                'resources/js/salon-dashboard.js',

                'resources/css/salon-dashboard.css',
                'resources/js/salon-posts.js',
            ],

            refresh: true,
        }),

        tailwindcss(),
    ],

    server: {
        // Keep the dev process transparent: runtime/compiler errors should stay
        // visible in the terminal instead of being hidden by Vite's screen clear.
        clearScreen: false,

        warmup: {
            clientFiles: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/customer.css',
                'resources/js/customer.js',
                'resources/css/discovery.css',
                'resources/js/discover.js',
                'resources/css/salon.css',
                'resources/js/salon.js',
                'resources/css/salon-owner.css',
                'resources/js/salon-dashboard.js',
                'resources/css/salon-dashboard.css',
                'resources/js/salon-posts.js',
            ],
        },
    },
})
