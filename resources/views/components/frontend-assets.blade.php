@props([
    'role' => 'customer',
])

@php
    $assets = match ($role) {
        'admin' => [
            'resources/css/app.css',
            'resources/js/app.js',
        ],

        'salon' => [
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/js/salon.js',
        ],

        'public' => [
            'resources/css/app.css',
            'resources/css/public-salon.css',
            'resources/js/app.js',
            'resources/js/customer.js',
        ],

        default => [
            'resources/css/app.css',
            'resources/css/customer.css',
            'resources/js/app.js',
            'resources/js/customer.js',
        ],
    };
@endphp

{{--
    Theme must be applied before styles paint to avoid a visible flash.
    Shared runtime is initialized by app.js.
--}}
<script>
    (() => {
        const key = 'nobatdehi_theme';

        try {
            const saved = localStorage.getItem(key);
            document.documentElement.dataset.theme =
                saved === 'dark' ? 'dark' : 'light';
        } catch (_) {
            document.documentElement.dataset.theme = 'light';
        }
    })();
</script>

@vite($assets)
