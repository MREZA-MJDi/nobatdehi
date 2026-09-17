<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | {{ $salon?->name ?? 'پنل سالن' }}@else{{ $salon?->name ?? 'پنل سالن' }}@endif</title>
    <meta name="description" content="@yield('meta_description', 'مدیریت سالن')">
    <x-frontend-assets role="salon" />
    @stack('head')
</head>

<body class="min-h-screen bg-background text-content">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 right-0 z-50 hidden w-72 flex-col border-l border-border bg-surface shadow-sm lg:flex">
        <div class="border-b border-border p-4">
            <a href="{{ route('salon.dashboard') }}" class="flex items-center gap-3 rounded-2xl p-2 hover:bg-primary-50">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-900 font-black text-white">
                    @if($salon->logo_path)<img src="{{ $salon->logo_url }}" alt="{{ $salon->name }}" class="h-full w-full object-cover">@else{{ mb_substr($salon->name, 0, 1) }}@endif
                </div>
                <div class="min-w-0 flex-1"><div class="truncate text-sm font-black">{{ $salon->name }}</div><div class="mt-1 text-[10px] text-content-muted">پنل مدیریت سالن</div></div>
            </a>
        </div>

        <div class="px-4 py-4">
            <div class="rounded-2xl border border-border bg-primary-50/70 p-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div><div class="text-[10px] font-bold text-content-muted">وضعیت سالن</div><div class="mt-1.5 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full {{ $salon->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span><span class="text-xs font-black">{{ $salon->is_active ? 'فعال' : 'غیرفعال' }}</span></div></div>
                    <a href="{{ route('public.salons.show', $salon) }}" target="_blank" rel="noopener noreferrer" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-content-soft shadow-sm hover:text-accent-600" aria-label="صفحه عمومی">↗</a>
                </div>
            </div>
        </div>

        <nav class="min-h-0 flex-1 overflow-y-auto px-3 pb-4" aria-label="ناوبری پنل سالن">
            @php
                $navItems = [
                    ['route' => 'salon.dashboard', 'label' => 'داشبورد', 'icon' => '⌂', 'active' => 'salon.dashboard'],
                    ['route' => 'salon.bookings.index', 'label' => 'نوبت‌ها', 'icon' => '◷', 'active' => 'salon.bookings.*'],
                    ['route' => 'salon.posts.index', 'label' => 'پست‌ها', 'icon' => '◫', 'active' => 'salon.posts.*'],
                    ['route' => 'salon.reviews.index', 'label' => 'نظرات مشتریان', 'icon' => '♡', 'active' => 'salon.reviews.*'],
                    ['route' => 'salon.barbers.index', 'label' => 'آرایشگران', 'icon' => '♙', 'active' => 'salon.barbers.*'],
                    ['route' => 'salon.services.index', 'label' => 'خدمات', 'icon' => '✂', 'active' => 'salon.services.*'],
                    ['route' => 'salon.working-hours.edit', 'label' => 'ساعات کاری', 'icon' => '◷', 'active' => 'salon.working-hours.*'],
                    ['route' => 'salon.notifications.index', 'label' => 'اعلان‌ها', 'icon' => '♧', 'active' => 'salon.notifications.*'],
                    ['route' => 'salon.settings.edit', 'label' => 'تنظیمات سالن', 'icon' => '⚙', 'active' => 'salon.settings.*'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold transition {{ request()->routeIs($item['active']) ? 'bg-accent-50 text-accent-700' : 'text-content-soft hover:bg-primary-50 hover:text-content' }}">
                    <span class="flex h-5 w-5 items-center justify-center text-base">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                    @if($item['route'] === 'salon.notifications.index' && $unreadNotifications > 0)<span class="mr-auto rounded-full bg-accent-600 px-1.5 py-0.5 text-[9px] font-black text-white">{{ $unreadNotifications }}</span>@endif
                </a>
            @endforeach

            <div class="my-4 h-px bg-border"></div>
            <a href="{{ route('public.salons.show', $salon) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-bold text-content-soft hover:bg-primary-50 hover:text-content"><span>↗</span><span>صفحه عمومی سالن</span></a>
        </nav>

        <div class="border-t border-border p-3">
            <div class="mb-2 flex items-center gap-3 rounded-2xl border border-border bg-primary-50/70 p-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-600 text-xs font-black text-white">{{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                <div class="min-w-0 flex-1"><div class="truncate text-xs font-black">{{ auth()->user()->name }}</div><div class="mt-1 truncate text-[10px] text-content-muted">{{ auth()->user()->phone }}</div></div>
            </div>
            <div class="flex items-center gap-2"><x-theme-toggle /><form action="{{ route('logout') }}" method="POST" class="min-w-0 flex-1">@csrf<button type="submit" class="btn btn-ghost btn-sm w-full">خروج از حساب</button></form></div>
        </div>
    </aside>

    <header class="sticky top-0 z-40 border-b border-border bg-surface/95 backdrop-blur-xl lg:hidden">
        <div class="mx-auto flex h-16 max-w-2xl items-center justify-between px-4">
            <a href="{{ route('salon.dashboard') }}" class="flex min-w-0 items-center gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-900 text-xs font-black text-white">@if($salon->logo_path)<img src="{{ $salon->logo_url }}" alt="{{ $salon->name }}" class="h-full w-full object-cover">@else{{ mb_substr($salon->name, 0, 1) }}@endif</div><span class="truncate text-xs font-black">{{ $salon->name }}</span></a>
            <div class="flex items-center gap-2"><x-theme-toggle /><a href="{{ route('salon.notifications.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50">♧@if($unreadNotifications > 0)<span class="absolute -right-1 -top-1 rounded-full bg-accent-600 px-1 text-[8px] text-white">{{ $unreadNotifications }}</span>@endif</a></div>
        </div>
    </header>

    <main class="min-h-screen lg:mr-72">
        @if(session('success'))<div class="px-4 pt-4 sm:px-6 lg:px-8"><div class="mx-auto max-w-7xl"><div class="alert alert-success" role="status">{{ session('success') }}</div></div></div>@endif
        @if(session('error'))<div class="px-4 pt-4 sm:px-6 lg:px-8"><div class="mx-auto max-w-7xl"><div class="alert alert-danger" role="alert">{{ session('error') }}</div></div></div>@endif
        <div class="w-full">@yield('content')</div>
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-[999] border-t border-border bg-surface/95 shadow-float backdrop-blur-xl lg:hidden" aria-label="ناوبری سالن">
        <div class="mx-auto grid max-w-lg grid-cols-5">
            <a href="{{ route('salon.dashboard') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[9px] font-bold {{ request()->routeIs('salon.dashboard') ? 'text-accent-600' : 'text-content-muted' }}">⌂<span>خانه</span></a>
            <a href="{{ route('salon.bookings.index') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[9px] font-bold {{ request()->routeIs('salon.bookings.*') ? 'text-accent-600' : 'text-content-muted' }}">◷<span>نوبت‌ها</span></a>
            <a href="{{ route('salon.barbers.index') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[9px] font-bold {{ request()->routeIs('salon.barbers.*') ? 'text-accent-600' : 'text-content-muted' }}">♙<span>تیم</span></a>
            <a href="{{ route('salon.services.index') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[9px] font-bold {{ request()->routeIs('salon.services.*') ? 'text-accent-600' : 'text-content-muted' }}">✂<span>خدمات</span></a>
            <a href="{{ route('salon.notifications.index') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[9px] font-bold {{ request()->routeIs('salon.notifications.*') ? 'text-accent-600' : 'text-content-muted' }}">♧<span>اعلان‌ها</span></a>
        </div>
    </nav>

    <div class="h-20 lg:hidden" aria-hidden="true"></div>
    @stack('scripts')
</div>
</body>
</html>
