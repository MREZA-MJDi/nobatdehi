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
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#6757E8"
    >

    <title>
        @yield('title', 'NOBAT')
    </title>

    <meta
        name="description"
        content="@yield(
            'meta_description',
            'NOBAT؛ پیدا کن، مقایسه کن و نوبت بگیر.'
        )"
    >

    {{-- =========================================================
        CORE VITE ASSETS
    ========================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/css/salon.css',
    'resources/js/app.js',
    'resources/js/customer.js',
    ])

    {{-- =========================================================
        PAGE-SPECIFIC STYLES
    ========================================================== --}}

    @stack('styles')

    {{-- =========================================================
        PAGE-SPECIFIC HEAD
    ========================================================== --}}

    @stack('head')

</head>


<body
    class="
        min-h-screen
        bg-background
        text-content
        antialiased
    "
>

{{-- =========================================================
    PUBLIC PAGE
========================================================== --}}

<main>
    @yield('content')
</main>


{{-- =========================================================
    PAGE-SPECIFIC SCRIPTS
========================================================== --}}

@stack('scripts')

</body>

</html>
