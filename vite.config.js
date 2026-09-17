import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/admin.css',
                'resources/js/admin.js',

                'resources/css/customer.css',
                'resources/js/customer.js',

                'resources/css/salon.css',
                'resources/js/salon.js',
            ],

            refresh: true,
        }),

        tailwindcss(),
    ],
})
