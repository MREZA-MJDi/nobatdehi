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
                'resources/js/discover.js',

                'resources/css/discovery.css',
                'resources/css/salon.css',
            ],

            refresh: true,
        }),

        tailwindcss(),
    ],
})
