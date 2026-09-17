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
        content="@hasSection('discover_page') #0d0f10 @else #0b0c0d @endif"
    >

    <title>
        @yield('title', 'NOBAT')
    </title>

    @hasSection('description')
        <meta
            name="description"
            content="@yield('description')"
        >
    @else
        <meta
            name="description"
            content="@yield('meta_description', 'NOBAT؛ پیدا کن، مقایسه کن و نوبت بگیر.')"
        >
    @endif

    <meta
        name="robots"
        content="@yield('robots', 'index,follow')"
    >

    <link
        rel="canonical"
        href="@yield('canonical', url()->current())"
    >

    @hasSection('discover_page')
        @vite([
            'resources/css/app.css',
            'resources/css/discovery.css',
            'resources/js/app.js',
            'resources/js/discover.js',
        ])
    @else
        @vite([
            'resources/css/app.css',
            'resources/css/public-salon.css',
            'resources/js/app.js',
            'resources/js/customer.js',
        ])
    @endif

    @stack('styles')
    @stack('head')
</head>

<body class="min-h-screen bg-black text-white antialiased">
    @hasSection('discover_page')
        <div class="discover-shell">
            @include('public.navigation')

            <main>
                @yield('content')
            </main>

            @include('public.mobile-navigation')
        </div>
    @else
        <main>
            @yield('content')
        </main>
    @endif

    @stack('scripts')
</body>
</html>
