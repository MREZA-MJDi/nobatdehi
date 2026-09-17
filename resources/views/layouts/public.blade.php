<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    class="antialiased"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="@if(request()->routeIs('salons.discover')) #0d0f10 @else #0b0c0d @endif">

    <title>@yield('title', 'NOBAT')</title>

    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @else
        <meta name="description" content="@yield('meta_description', 'NOBAT؛ پیدا کن، مقایسه کن و نوبت بگیر.')">
    @endif

    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    @if(request()->routeIs('salons.discover'))
        @vite([
            'resources/css/app.css',
            'resources/css/discovery.css',
            'resources/css/discover-enhancements.css',
            'resources/js/app.js',
            'resources/js/discover.js',
            'resources/js/discover-enhancements.js',
        ])
    @elseif(request()->routeIs('public.salons.booking.create'))
        @vite([
            'resources/css/app.css',
            'resources/css/customer.css',
            'resources/js/app.js',
            'resources/js/customer.js',
        ])
    @else
        @vite([
            'resources/css/app.css',
            'resources/css/salon.css',
            'resources/css/public-salon-enhancements.css',
            'resources/js/app.js',
            'resources/js/public-salon.js',
        ])
    @endif

    @stack('styles')
    @stack('head')
</head>

<body class="min-h-screen antialiased">
    @if(request()->routeIs('salons.discover'))
        <div class="discover-shell">
            @include('public.navigation')
            <main>
                @yield('content')
            </main>
            <x-discover-footer />
            @include('public.mobile-navigation')
        </div>
    @elseif(request()->routeIs('public.salons.booking.create'))
        <main class="public-booking-shell">
            @yield('content')
        </main>
    @else
        <main>
            @yield('content')
        </main>
    @endif

    @stack('scripts')
</body>
</html>
