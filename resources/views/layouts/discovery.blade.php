<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>@yield('title', 'کشف سالن و خدمات | NOBAT')</title>

    <meta
        name="description"
        content="@yield('description', 'سالن‌ها، متخصص‌ها و خدمات موردنظر خودت را در NOBAT پیدا کن.')"
    >

    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="theme-color" content="#f5f4f1">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    @vite([
        'resources/css/app.css',
        'resources/css/discovery.css',
        'resources/js/app.js',
        'resources/js/discover.js',
    ])

    @stack('styles')
    @stack('head')
</head>

<body class="min-h-screen antialiased">
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
