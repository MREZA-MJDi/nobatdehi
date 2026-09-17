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
                'resources/css/discover-enhancements.css',
                'resources/js/discover.js',
                'resources/css/salon.css',
                'resources/css/public-salon-enhancements.css',
                'resources/js/public-salon.js',
                'resources/js/salon.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
})
