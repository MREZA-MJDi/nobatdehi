<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدیریت نوبت‌دهی')</title>
    <meta name="description" content="@yield('meta_description', 'پنل مدیریت نوبت‌دهی')">
    <x-frontend-assets role="admin" />
    @vite('resources/css/experience-enhancements.css')
    @stack('head')
</head>
<body class="admin-portal">
<div class="min-h-screen bg-background text-content">
    <header class="sticky top-0 z-40 border-b border-border bg-surface/95 backdrop-blur-xl">
        <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary-950 font-black text-white shadow-soft">N</span>
                <span class="hidden sm:block"><span class="block text-sm font-black text-content">NOBAT</span><span class="block text-[9px] text-content-muted">پنل مدیریت · RM / CO</span></span>
            </a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="ناوبری مدیریت">
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 text-xs font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-primary-100 text-content' : 'text-content-muted hover:bg-primary-50 hover:text-content' }}">داشبورد</a>
                <a href="{{ route('admin.salons.index') }}" class="rounded-xl px-3 py-2 text-xs font-bold {{ request()->routeIs('admin.salons.*') ? 'bg-primary-100 text-content' : 'text-content-muted hover:bg-primary-50 hover:text-content' }}">سالن‌ها</a>
                <a href="{{ route('admin.salons.create') }}" class="rounded-xl px-3 py-2 text-xs font-bold text-content-muted hover:bg-primary-50 hover:text-content">ایجاد سالن</a>
                <a href="{{ route('salons.discover') }}" class="rounded-xl px-3 py-2 text-xs font-bold text-content-muted hover:bg-primary-50 hover:text-content">مشاهده سایت</a>
            </nav>
            <div class="mr-auto flex items-center gap-2">
                <x-theme-toggle />
                <span class="hidden text-xs font-bold text-content-muted lg:block">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="btn btn-ghost btn-sm">خروج</button></form>
            </div>
        </div>
    </header>
    <main class="mx-auto w-full max-w-7xl px-4 py-5 pb-24 sm:px-6 lg:px-8">
        @if(session('success'))<div class="mb-4"><div class="alert alert-success" role="status">{{ session('success') }}</div></div>@endif
        @if(session('error'))<div class="mb-4"><div class="alert alert-danger" role="alert">{{ session('error') }}</div></div>@endif
        @yield('content')
    </main>
    <nav class="fixed inset-x-3 bottom-3 z-50 md:hidden" aria-label="ناوبری مدیریت">
        <div class="grid grid-cols-4 gap-1 rounded-2xl border border-border bg-surface p-2 shadow-float">
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-primary-100 text-content' : 'text-content-muted' }}">⌂<span>داشبورد</span></a>
            <a href="{{ route('admin.salons.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold {{ request()->routeIs('admin.salons.*') ? 'bg-primary-100 text-content' : 'text-content-muted' }}">▦<span>سالن‌ها</span></a>
            <a href="{{ route('admin.salons.create') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold text-content-muted">＋<span>ایجاد</span></a>
            <a href="{{ route('salons.discover') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[9px] font-bold text-content-muted">↗<span>سایت</span></a>
        </div>
    </nav>
</div>
@stack('scripts')
</body>
</html>
