<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ورود') | نوبت‌دهی</title>
    <meta name="description" content="@yield('meta_description', 'ورود و ثبت‌نام در سامانه نوبت‌دهی')">
    <meta name="theme-color" content="#6757E8">

    <x-frontend-assets role="customer" />
    @stack('head')
</head>

<body class="min-h-screen">
<main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-accent-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-32 h-[28rem] w-[28rem] rounded-full bg-cyan-400/10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="mb-7 text-center">
            <a href="{{ route('salons.discover') }}" class="group inline-flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-950 text-base font-black text-white shadow-lg transition group-hover:-translate-y-1">N</span>
                <span class="text-right">
                    <span class="block text-base font-black text-content">NOBAT</span>
                    <span class="mt-0.5 block text-[10px] font-medium text-content-muted">پیدا کن. انتخاب کن. نوبت بگیر.</span>
                </span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 flex items-start gap-3 rounded-2xl border border-success-100 bg-success-50 px-4 py-3 shadow-soft" role="status">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-success-100 text-success-700">✓</span>
                <span class="text-xs font-bold leading-6 text-success-700">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 flex items-start gap-3 rounded-2xl border border-accent-100 bg-accent-50 px-4 py-3 shadow-soft" role="status">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">!</span>
                <span class="text-xs font-bold leading-6 text-accent-700">{{ session('status') }}</span>
            </div>
        @endif

        <section class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 shadow-float backdrop-blur-xl">
            @yield('content')
        </section>

        <div class="mt-6 text-center">
            <div class="text-[10px] font-medium text-content-faint">© {{ now()->year }} NOBAT · RM / CO</div>
            <div class="mt-1 text-[9px] text-content-faint">رزرو سریع و ساده خدمات موردنظر شما</div>
        </div>
    </div>
</main>

@stack('scripts')
</body>
</html>
