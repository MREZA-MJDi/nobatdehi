<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    class="antialiased"
>
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#0b0c0d"
    >

    <title>@yield('title', 'NOBAT')</title>

    <meta
        name="description"
        content="@yield('meta_description', 'NOBAT؛ پیدا کن، مقایسه کن و نوبت بگیر.')"
    >

    @vite([
        'resources/css/app.css',
        'resources/css/public-salon.css',
        'resources/js/app.js',
        'resources/js/customer.js',
    ])

    @stack('styles')
    @stack('head')
</head>

<body class="min-h-screen bg-black text-white antialiased">
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
