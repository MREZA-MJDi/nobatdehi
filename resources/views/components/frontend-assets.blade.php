@props([
    'role' => 'customer',
])

@php
    $assets = match ($role) {
        'admin' => [
            'resources/css/admin.css',
            'resources/js/admin.js',
        ],

        'salon' => [
            'resources/css/admin.css',
            'resources/js/admin.js',
            'resources/css/salon.css',
            'resources/js/salon.js',
        ],

        'public' => [
            'resources/css/admin.css',
            'resources/js/admin.js',
            'resources/css/customer.css',
            'resources/js/customer.js',
            'resources/css/salon.css',
        ],

        default => [
            'resources/css/admin.css',
            'resources/js/admin.js',
            'resources/css/customer.css',
            'resources/js/customer.js',
        ],
    };
@endphp

{{--
    Theme must be applied before styles paint to avoid a visible flash.
    Runtime stores and Alpine are initialized by admin.js.
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
