<nav class="mobile-bottom-nav" aria-label="ناوبری موبایل">
    <div class="mx-auto flex h-[72px] max-w-lg items-center justify-around px-3" dir="rtl">
        <a href="{{ route('home') }}" @class(['group flex min-w-[64px] flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2', 'text-primary' => request()->routeIs('home'), 'text-muted-foreground' => !request()->routeIs('home')])>
            <span @class(['flex size-9 items-center justify-center rounded-xl', 'bg-primary/10' => request()->routeIs('home')])>
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 10 9-7 9 7"/><path d="M5 9v11h14V9"/></svg>
            </span>
            <span class="text-[10px] font-semibold">خانه</span>
        </a>

        <a href="{{ route('salons.discover') }}" @class(['group flex min-w-[64px] flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2', 'text-primary' => request()->routeIs('salons.discover'), 'text-muted-foreground' => !request()->routeIs('salons.discover')])>
            <span @class(['flex size-9 items-center justify-center rounded-xl', 'bg-primary/10' => request()->routeIs('salons.discover')])>
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 5 5"/></svg>
            </span>
            <span class="text-[10px] font-semibold">کشف</span>
        </a>

        <a href="{{ route('salons.discover') }}" class="mobile-bottom-nav-book -mt-7 flex size-14 shrink-0 items-center justify-center rounded-full border-4 border-background bg-primary text-primary-foreground shadow-xl shadow-primary/20 transition hover:scale-105 active:scale-95" aria-label="شروع رزرو">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="3"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M12 13v5"/><path d="M9.5 15.5h5"/></svg>
        </a>

        <a href="{{ route('salons.discover') }}?sort=nearest" class="group flex min-w-[64px] flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-muted-foreground">
            <span class="flex size-9 items-center justify-center rounded-xl">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.3"/></svg>
            </span>
            <span class="text-[10px] font-semibold">نزدیک من</span>
        </a>

        @guest
            <a href="{{ route('login') }}" class="group flex min-w-[64px] flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-muted-foreground">
                @else
                    <a href="{{ route('home') }}" class="group flex min-w-[64px] flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-muted-foreground">
                        @endguest
                        <span class="flex size-9 items-center justify-center rounded-xl">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            </span>
                        <span class="text-[10px] font-semibold">حساب</span>
                    </a>
    </div>
</nav>
