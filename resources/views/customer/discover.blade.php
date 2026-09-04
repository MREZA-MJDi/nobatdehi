@extends('layouts.customer')

@section('title', $seoTitle ?? 'کشف سالن و آرایشگر | RM نوبت‌دهی')
@section('meta_description', $seoDescription ?? 'سالن، آرایشگر و خدمات زیبایی مناسب خودت را پیدا کن و نوبت بگیر.')
@section('robots', $robots ?? 'index,follow')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
@endpush

@php
    $mediaUrl = function (?string $path): ?string {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        return \Illuminate\Support\Facades\Storage::url($path);
    };

    $fa = fn ($value) => strtr((string) $value, [
        '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
    ]);

    $salonImage = function ($salon) use ($mediaUrl) {
        if ($salon->cover_path) return $mediaUrl($salon->cover_path);
        if ($salon->logo_path) return $mediaUrl($salon->logo_path);
        $portfolio = $salon->portfolioItems?->first();
        if ($portfolio?->after_image_path) return $mediaUrl($portfolio->after_image_path);
        if ($portfolio?->before_image_path) return $mediaUrl($portfolio->before_image_path);
        return null;
    };
@endphp

<main dir="rtl" class="overflow-hidden">

    {{-- HERO --------------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden border-b border-border/50">
        <x-ui.aurora-background intensity="subtle" />
        <x-ui.grid-pattern
            :width="52"
            :height="52"
            class="fill-transparent stroke-border/60 [mask-image:radial-gradient(ellipse_70%_58%_at_50%_0%,black,transparent)]"
        />
        <x-ui.particles :quantity="30" class="text-primary/30" />

        <div class="relative z-10 mx-auto max-w-7xl px-5 pb-20 pt-24 sm:px-8 lg:pb-28 lg:pt-36">
            <div class="mx-auto max-w-4xl text-center">

                <x-ui.blur-fade direction="down">
                    <span class="inline-flex items-center gap-2 rounded-full border border-border bg-background/75 px-4 py-2 text-sm font-semibold shadow-soft backdrop-blur-xl">
                        <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                        {{ $hasLocation ? 'جستجو بر اساس موقعیت شما' : 'سالن و متخصص مناسب خودت را پیدا کن' }}
                    </span>
                </x-ui.blur-fade>

                <h1 class="mt-7 text-balance text-4xl font-black leading-tight tracking-tight sm:text-6xl lg:text-7xl">
                    <x-ui.text-reveal
                        as="span"
                        text="پیدا کن، انتخاب کن، نوبت بگیر"
                    />
                    <span class="mt-3 block">
                        <x-ui.animated-gradient-text>
                            <x-ui.typewriter
                                :words="[
                                    'نزدیک خودت',
                                    'با بهترین امتیازها',
                                    'با نوبت خالی امروز',
                                    'برای استایل تو',
                                ]"
                                :type-speed="70"
                                :delete-speed="40"
                                :hold-time="1700"
                            />
                        </x-ui.animated-gradient-text>
                    </span>
                </h1>

                <x-ui.blur-fade :delay=".2">
                    <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-muted-foreground sm:text-lg">
                        سالن‌ها، خدمات و متخصص‌های اطراف خودت را ببین، امتیاز و نمونه‌کارها را بررسی کن و بدون تماس تلفنی نوبت بگیر.
                    </p>
                </x-ui.blur-fade>

                {{-- SEARCH ------------------------------------------------ --}}
                <x-ui.blur-fade :delay=".35">
                    <form action="{{ route('salons.discover') }}" method="GET" class="mx-auto mt-9 max-w-4xl">
                        <div class="rounded-3xl border border-border/70 bg-card/95 p-2 shadow-float backdrop-blur-xl">
                            <div class="grid gap-2 lg:grid-cols-[1fr_1fr_auto_auto]">

                                <div class="flex min-h-14 items-center gap-3 rounded-2xl bg-muted px-4 text-right">
                                    <svg class="size-5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <label for="discover-city" class="block text-xs font-semibold text-muted-foreground">شهر</label>
                                        <input id="discover-city" name="city" value="{{ $city }}" list="discover-cities" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-semibold outline-none placeholder:text-muted-foreground/60 focus:ring-0" placeholder="مثلاً تهران">
                                        <datalist id="discover-cities">
                                            @foreach($cities as $availableCity)
                                                <option value="{{ $availableCity }}"></option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>

                                <div class="flex min-h-14 items-center gap-3 rounded-2xl bg-muted px-4 text-right">
                                    <svg class="size-5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <label for="discover-q" class="block text-xs font-semibold text-muted-foreground">خدمت، سالن یا متخصص</label>
                                        <input id="discover-q" name="q" value="{{ $query }}" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-semibold outline-none placeholder:text-muted-foreground/60 focus:ring-0" placeholder="مثلاً رنگ مو یا مانیکور">
                                    </div>
                                </div>

                                <input type="hidden" name="lat" id="discover-lat" value="{{ $latitude }}">
                                <input type="hidden" name="lng" id="discover-lng" value="{{ $longitude }}">

                                <button type="button" id="discover-location-button" class="min-h-14 rounded-2xl border border-border bg-background px-5 text-sm font-bold text-content-soft transition hover:border-primary/40 hover:text-primary">
                                    <span class="inline-flex items-center justify-center gap-2">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M16.9 16.9l2.1 2.1M19.1 4.9L17 7M7 17l-2.1 2.1"/>
                                        </svg>
                                        <span>موقعیت من</span>
                                    </span>
                                </button>

                                <x-ui.shimmer-button type="submit" class="min-h-14 rounded-2xl px-7">
                                    جستجو
                                </x-ui.shimmer-button>
                            </div>
                        </div>
                    </form>
                </x-ui.blur-fade>

                <x-ui.blur-fade :delay=".5">
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-sm text-muted-foreground">
                        <span><b class="text-foreground">{{ $fa($activeSalonCount) }}</b> سالن فعال</span>
                        <span class="hidden size-1 rounded-full bg-border sm:block"></span>
                        <span><b class="text-foreground">{{ $fa($activeBarberCount) }}</b> متخصص فعال</span>
                        <span class="hidden size-1 rounded-full bg-border sm:block"></span>
                        <span><b class="text-foreground">{{ $fa($publishedReviewCount) }}</b> نظر ثبت‌شده</span>
                    </div>
                </x-ui.blur-fade>
            </div>
        </div>
    </section>

    {{-- CATEGORIES --------------------------------------------------- --}}
    @if($categories)
        <section class="py-14 lg:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="mb-7 flex items-end justify-between gap-4">
                    <div>
                        <span class="text-sm font-semibold text-primary">شروع سریع</span>
                        <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">دنبال چه خدمتی هستی؟</h2>
                    </div>
                    <span class="hidden text-sm text-muted-foreground sm:block">پرطرفدارترین جستجوها</span>
                </div>

                <div class="flex gap-4 overflow-x-auto pb-3 scrollbar-hide">
                    @foreach($categories as $category)
                        <x-ui.spotlight-card class="min-w-[220px] shrink-0">
                            <a href="{{ route('salons.discover', array_filter(['q'=>$category['query'],'city'=>$city,'type'=>$type !== 'all' ? $type : null])) }}" class="block p-5">
                                <div class="flex items-start justify-between">
                                    <span class="grid size-12 place-items-center rounded-2xl bg-primary/10 text-2xl text-primary">{{ $category['icon'] }}</span>
                                    <svg class="size-5 rotate-180 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </div>
                                <h3 class="mt-7 text-lg font-black">{{ $category['title'] }}</h3>
                                <p class="mt-1 text-sm leading-6 text-muted-foreground">{{ $category['subtitle'] }}</p>
                            </a>
                        </x-ui.spotlight-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- SALONS ------------------------------------------------------- --}}
    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <span class="text-sm font-semibold text-primary">{{ $hasLocation ? 'نزدیک تو' : 'پیشنهادهای امروز' }}</span>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">{{ $type === 'salon' ? 'سالن‌ها' : 'سالن‌های محبوب' }}</h2>
                    <p class="mt-2 text-sm text-muted-foreground">{{ $fa($salons->total()) }} نتیجه از سالن‌های فعال</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('salons.discover', array_filter(['sort'=>'rating','city'=>$city,'q'=>$query])) }}" class="hidden rounded-xl border border-border px-4 py-2 text-xs font-bold text-muted-foreground hover:border-primary/30 hover:text-primary sm:inline-flex">بیشترین امتیاز</a>
                    <a href="{{ route('salons.discover', array_filter(['sort'=>'newest','city'=>$city,'q'=>$query])) }}" class="hidden rounded-xl border border-border px-4 py-2 text-xs font-bold text-muted-foreground hover:border-primary/30 hover:text-primary sm:inline-flex">جدیدترین</a>
                </div>
            </div>

            @if($salons->count())
                <div class="mt-8 flex gap-5 overflow-x-auto pb-5 scrollbar-hide">
                    @foreach($salons as $salon)
                        @php
                            $image = $salonImage($salon);
                            $rating = $salon->reviews_avg_rating !== null ? number_format((float)$salon->reviews_avg_rating, 1, '.', '') : null;
                        @endphp
                        <x-ui.blur-fade :delay="($loop->index % 4) * .06" class="min-w-[315px] shrink-0 sm:min-w-[360px]">
                            <x-ui.tilt-card class="h-full">
                                <article class="group overflow-hidden rounded-3xl border bg-card shadow-soft transition hover:shadow-card">
                                    <div class="relative aspect-[1.35] overflow-hidden bg-muted">
                                        @if($image)
                                            <img src="{{ $image }}" alt="{{ $salon->name }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                                        @else
                                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-primary-100 via-accent-50 to-cyan-50">
                                                <span class="text-5xl font-black text-primary/30">{{ mb_substr($salon->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                                        @if($hasLocation && isset($salon->distance_km))
                                            <span class="absolute right-3 top-3 rounded-full bg-background/90 px-3 py-1.5 text-xs font-bold backdrop-blur">{{ $fa(number_format((float)$salon->distance_km, 1, '.', '')) }} کیلومتر</span>
                                        @endif
                                        <div class="absolute bottom-4 right-4 left-4 flex items-end justify-between gap-3 text-white">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-lg font-black">{{ $salon->name }}</h3>
                                                <p class="mt-1 truncate text-xs text-white/80">{{ $salon->district ?: $salon->city ?: 'موقعیت ثبت نشده' }}</p>
                                            </div>
                                            @if($rating)
                                                <span class="shrink-0 rounded-xl bg-black/35 px-2.5 py-1.5 text-xs font-bold backdrop-blur">★ {{ $fa($rating) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-5">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="rounded-2xl bg-muted p-3"><span class="block text-[11px] text-muted-foreground">متخصص فعال</span><strong class="mt-1 block">{{ $fa($salon->barbers_count) }}</strong></div>
                                            <div class="rounded-2xl bg-muted p-3"><span class="block text-[11px] text-muted-foreground">خدمت فعال</span><strong class="mt-1 block">{{ $fa($salon->services_count) }}</strong></div>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between gap-4 text-xs text-muted-foreground">
                                            <span>{{ $fa($salon->reviews_count) }} نظر</span>
                                            <span>{{ $salon->city ?: '—' }}</span>
                                        </div>
                                        <div class="mt-5 grid grid-cols-2 gap-2">
                                            <a href="{{ route('public.salons.show', $salon) }}" class="flex h-11 items-center justify-center rounded-xl bg-foreground text-sm font-bold text-background transition hover:scale-[1.01]">مشاهده سالن</a>
                                            <a href="{{ route('public.salons.booking.create', $salon) }}" class="flex h-11 items-center justify-center rounded-xl border border-border bg-background text-sm font-bold hover:border-primary/40 hover:text-primary">رزرو</a>
                                        </div>
                                    </div>
                                </article>
                            </x-ui.tilt-card>
                        </x-ui.blur-fade>
                    @endforeach
                </div>

                @if($salons->hasPages())
                    <div class="mt-4">{{ $salons->onEachSide(1)->links() }}</div>
                @endif
            @else
                <div class="mt-8 rounded-3xl border border-dashed border-border bg-card p-10 text-center">
                    <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-muted text-2xl">⌕</div>
                    <h3 class="mt-4 text-lg font-black">سالنی با این جستجو پیدا نشد</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-muted-foreground">عبارت جستجو یا شهر را تغییر بده و دوباره امتحان کن.</p>
                    <a href="{{ route('salons.discover') }}" class="mt-5 inline-flex rounded-xl bg-foreground px-5 py-3 text-sm font-bold text-background">حذف فیلترها</a>
                </div>
            @endif
        </div>
    </section>

    {{-- POPULAR BARBERS ---------------------------------------------- --}}
    @if($barbers->count() && $type !== 'salon')
        <section class="border-y border-border/50 bg-muted/20 py-14 lg:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div>
                    <span class="text-sm font-semibold text-primary">محبوب بین مشتری‌ها</span>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">محبوب‌ترین متخصص‌ها</h2>
                    <p class="mt-2 text-sm text-muted-foreground">مرتب‌شده بر اساس تعداد نوبت‌های تکمیل‌شده</p>
                </div>

                <div class="mt-8 flex gap-5 overflow-x-auto pb-4 scrollbar-hide">
                    @foreach($barbers as $barber)
                        @php $barberImage = $mediaUrl($barber->image_path); @endphp
                        <article class="min-w-[285px] shrink-0 rounded-2xl border bg-card p-4 transition hover:-translate-y-1 hover:shadow-card">
                            <div class="flex items-center gap-4">
                                <div class="size-16 shrink-0 overflow-hidden rounded-2xl bg-muted">
                                    @if($barberImage)
                                        <img src="{{ $barberImage }}" alt="{{ $barber->name }}" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <div class="flex h-full items-center justify-center text-xl font-black text-primary/50">{{ mb_substr($barber->name, 0, 1) }}</div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate font-black">{{ $barber->name }}</h3>
                                    <p class="mt-1 truncate text-xs text-muted-foreground">{{ $barber->specialty ?: 'متخصص زیبایی' }}</p>
                                    <p class="mt-1 truncate text-xs text-muted-foreground">{{ $barber->salon?->name }}</p>
                                </div>
                            </div>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                @if($barber->salon?->reviews_avg_rating)
                                    <span class="text-sm font-bold">★ {{ $fa(number_format((float)$barber->salon->reviews_avg_rating,1,'.','')) }}</span>
                                @else
                                    <span class="text-xs text-muted-foreground">امتیاز سالن ثبت نشده</span>
                                @endif
                                <span class="text-xs text-muted-foreground">{{ $fa($barber->completed_bookings_count ?? 0) }} رزرو</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- AVAILABLE SLOTS ---------------------------------------------- --}}
    @if($availableSlots->count())
        <section class="py-14 lg:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div>
                    <span class="text-sm font-semibold text-primary">رزرو سریع</span>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">نوبت‌های خالی نزدیک</h2>
                    <p class="mt-2 text-sm text-muted-foreground">اولین زمان واقعی آزاد برای هر سالن از سیستم زمان‌بندی پیدا شده است.</p>
                </div>

                <div class="mt-8 flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                    @foreach($availableSlots as $item)
                        @php
                            $date = $item['date']->copy()->locale('fa');
                            $bookingUrl = route('public.salons.booking.create', $item['salon']) . '?' . http_build_query([
                                'service_id' => $item['service']->id,
                                'barber_id' => $item['barber']->id,
                                'date' => $item['date']->format('Y-m-d'),
                                'start_time' => $item['slot']['start'],
                            ]);
                        @endphp
                        <x-ui.spotlight-card class="min-w-[320px] shrink-0">
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <span class="inline-flex rounded-full bg-success-100 px-2.5 py-1 text-[11px] font-bold text-success-700">نوبت آزاد</span>
                                        <h3 class="mt-3 truncate font-black">{{ $item['salon']->name }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 text-sm font-black text-primary">{{ $fa($item['slot']['start']) }}</span>
                                </div>
                                <div class="mt-5 space-y-3 text-sm">
                                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">روز</span><span class="font-semibold">{{ $date->isoFormat('dddd D MMMM') }}</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">خدمت</span><span class="font-semibold">{{ $item['service']->name }}</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">متخصص</span><span class="font-semibold">{{ $item['barber']->name }}</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">مدت</span><span class="font-semibold">{{ $fa($item['service']->duration_minutes) }} دقیقه</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">قیمت</span><span class="font-black">{{ $fa(number_format($item['service']->price)) }} تومان</span></div>
                                </div>
                                <a href="{{ $bookingUrl }}" class="mt-6 flex h-11 items-center justify-center rounded-xl bg-foreground text-sm font-bold text-background">رزرو این زمان</a>
                            </div>
                        </x-ui.spotlight-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FEATURED ----------------------------------------------------- --}}
    @if($featuredSalons->count())
        <section class="relative overflow-hidden bg-slate-950 py-16 text-white lg:py-24">
            <x-ui.retro-grid :angle="58" :cell-size="64" :opacity=".2" />
            <div class="relative z-10 mx-auto max-w-7xl px-5 sm:px-8">
                <div>
                    <span class="text-sm font-semibold text-white/60">ویترین</span>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-4xl">سالن‌هایی که ارزش دیدن دارند</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-white/60 sm:text-base">کاور سالن، نمونه‌کار، امتیاز و جزئیات را ببین و بعد برای رزرو تصمیم بگیر.</p>
                </div>

                <div class="mt-10 flex gap-5 overflow-x-auto pb-4 scrollbar-hide">
                    @foreach($featuredSalons as $salon)
                        @php
                            $image = $salonImage($salon);
                            $rating = $salon->reviews_avg_rating !== null ? number_format((float)$salon->reviews_avg_rating,1,'.','') : null;
                        @endphp
                        <article class="group min-w-[330px] shrink-0 overflow-hidden rounded-3xl border border-white/10 bg-white/5 sm:min-w-[440px]">
                            <div class="relative aspect-[1.08] overflow-hidden bg-white/5">
                                @if($image)
                                    <img src="{{ $image }}" alt="{{ $salon->name }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                                @else
                                    <div class="flex h-full items-center justify-center"><span class="text-6xl font-black text-white/15">{{ mb_substr($salon->name,0,1) }}</span></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                                <span class="absolute right-4 top-4 rounded-full border border-white/10 bg-black/30 px-3 py-1.5 text-xs font-bold backdrop-blur">{{ $loop->first ? 'منتخب' : 'محبوب' }}</span>
                                <div class="absolute bottom-4 right-4 left-4 flex items-end justify-between gap-4">
                                    <div class="min-w-0"><h3 class="truncate text-xl font-black">{{ $salon->name }}</h3><p class="mt-1 truncate text-sm text-white/70">{{ $salon->district ?: $salon->city ?: '—' }}</p></div>
                                    @if($rating)<span class="shrink-0 rounded-xl bg-black/40 px-3 py-2 text-sm font-black backdrop-blur">★ {{ $fa($rating) }}</span>@endif
                                </div>
                            </div>
                            <div class="p-5">
                                @if($salon->portfolioItems?->first())
                                    <p class="line-clamp-2 text-sm leading-7 text-white/60">{{ $salon->portfolioItems->first()->title ?: 'نمونه‌کارهای این سالن را ببین' }}</p>
                                @else
                                    <p class="text-sm leading-7 text-white/60">خدمات، متخصص‌ها و اطلاعات کامل سالن را ببین.</p>
                                @endif
                                <div class="mt-5 flex items-center justify-between gap-4">
                                    <span class="text-xs text-white/50">{{ $fa($salon->reviews_count) }} نظر</span>
                                    <a href="{{ route('public.salons.show', $salon) }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-black text-slate-950">مشاهده سالن</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- MAP ---------------------------------------------------------- --}}
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
                <div>
                    <span class="text-sm font-semibold text-primary">نقشه سالن‌ها</span>
                    <h2 class="mt-2 text-3xl font-black tracking-tight">سالن‌های اطرافت را روی نقشه ببین</h2>
                    <p class="mt-4 leading-8 text-muted-foreground">روی هر سالن بزن تا اطلاعاتش را ببینی. وقتی موقعیت خودت را بدهی، مرتب‌سازی نزدیک‌ترین سالن‌ها هم فعال می‌شود.</p>

                    @if($mapSalons->count())
                        <div class="mt-8 space-y-3">
                            @foreach($mapSalons->take(6) as $index => $salon)
                                <a href="{{ route('public.salons.show', $salon) }}" data-map-focus="{{ $index }}" class="discover-map-row flex items-center gap-4 rounded-2xl border bg-card p-4 transition hover:border-primary/40 hover:shadow-soft">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-primary/10 text-sm font-black text-primary">{{ $fa($index+1) }}</span>
                                    <span class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $salon->name }}</span><span class="mt-1 block truncate text-xs text-muted-foreground">{{ $salon->district ?: $salon->city ?: 'موقعیت ثبت نشده' }}</span></span>
                                    @if($hasLocation && $salon->distance_km !== null)<span class="text-xs font-bold text-primary">{{ $fa(number_format((float)$salon->distance_km,1,'.','')) }} km</span>@endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="relative overflow-hidden rounded-3xl border bg-muted shadow-float">
                    <div id="discover-map" class="h-[460px] w-full" data-map='@json($mapSalons->values())' data-user-lat="{{ $latitude }}" data-user-lng="{{ $longitude }}"></div>
                    <div class="pointer-events-none absolute bottom-4 right-4 z-[500] rounded-2xl border bg-card/90 p-4 shadow-xl backdrop-blur">
                        <div class="text-sm font-black">{{ $fa($mapSalons->count()) }} سالن روی نقشه</div>
                        <div class="mt-1 text-xs text-muted-foreground">موقعیت‌های ثبت‌شده سالن‌ها</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA ---------------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden border-t border-border/40 py-16 lg:py-24">
        <x-ui.aurora-background intensity="subtle" />
        <x-ui.particles :quantity="24" class="text-primary/30" />
        <div class="relative z-10 mx-auto max-w-4xl px-5 text-center sm:px-8">
            <x-ui.blur-fade>
                <span class="inline-flex rounded-full border border-border bg-card/75 px-4 py-2 text-xs font-bold backdrop-blur">آماده‌ای؟</span>
                <h2 class="mt-6 text-3xl font-black tracking-tight sm:text-5xl">سالن مناسب خودت را پیدا کن، <span class="text-primary">نوبتت را بگیر.</span></h2>
                <p class="mx-auto mt-5 max-w-2xl text-base leading-8 text-muted-foreground">از جستجو تا رزرو، همه‌چیز را یکجا انجام بده.</p>
                <div class="mt-8"><x-ui.shimmer-button type="button" class="h-14 px-10 text-base" onclick="window.location.href='{{ route('salons.discover') }}'">شروع جستجو</x-ui.shimmer-button></div>
            </x-ui.blur-fade>
        </div>
    </section>

    {{-- FOOTER ------------------------------------------------------- --}}
    <footer class="border-t border-border/50 bg-muted/20">
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
            <div class="grid gap-10 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="text-xl font-black">RM نوبت‌دهی</div>
                    <p class="mt-4 max-w-md text-sm leading-7 text-muted-foreground">پلتفرم پیدا کردن سالن‌ها، متخصص‌ها و رزرو آنلاین نوبت؛ ساده، سریع و بدون تماس تلفنی.</p>
                </div>
                <div><h3 class="font-black">کشف</h3><div class="mt-4 space-y-3 text-sm text-muted-foreground"><a class="block hover:text-foreground" href="{{ route('salons.discover') }}">سالن‌ها</a><a class="block hover:text-foreground" href="{{ route('salons.discover', ['type'=>'barber']) }}">آرایشگرها</a><a class="block hover:text-foreground" href="{{ route('salons.discover') }}?sort=rating">محبوب‌ترین‌ها</a></div></div>
                <div><h3 class="font-black">برای سالن‌ها</h3><div class="mt-4 space-y-3 text-sm text-muted-foreground"><a class="block hover:text-foreground" href="{{ route('login') }}">ورود</a><a class="block hover:text-foreground" href="{{ route('register') }}">ثبت‌نام</a><a class="block hover:text-foreground" href="{{ route('salons.discover') }}">مشاهده بازار</a></div></div>
            </div>
            <div class="mt-10 border-t border-border/50 pt-6 text-xs text-muted-foreground">© {{ now()->year }} RM نوبت‌دهی — همه حقوق محفوظ است.</div>
        </div>
    </footer>
</main>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const latInput = document.getElementById('discover-lat');
            const lngInput = document.getElementById('discover-lng');
            const locationButton = document.getElementById('discover-location-button');

            if (locationButton && latInput && lngInput && 'geolocation' in navigator) {
                locationButton.addEventListener('click', () => {
                    const original = locationButton.innerHTML;
                    locationButton.disabled = true;
                    locationButton.innerHTML = '<span class="inline-flex items-center gap-2"><span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span><span>در حال یافتن...</span></span>';
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            latInput.value = position.coords.latitude;
                            lngInput.value = position.coords.longitude;
                            const form = locationButton.closest('form');
                            if (form) {
                                const sort = document.createElement('input');
                                sort.type = 'hidden';
                                sort.name = 'sort';
                                sort.value = 'nearest';
                                form.appendChild(sort);
                                form.submit();
                            }
                        },
                        () => {
                            locationButton.innerHTML = original;
                            locationButton.disabled = false;
                            alert('دسترسی به موقعیت مکانی امکان‌پذیر نشد.');
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
                    );
                });
            }

            const mapEl = document.getElementById('discover-map');
            if (mapEl && window.L) {
                const salons = JSON.parse(mapEl.dataset.map || '[]');
                const userLat = parseFloat(mapEl.dataset.userLat || '');
                const userLng = parseFloat(mapEl.dataset.userLng || '');

                let center = [35.7219, 51.3347];
                if (Number.isFinite(userLat) && Number.isFinite(userLng)) center = [userLat, userLng];
                else if (salons.length && salons[0].latitude && salons[0].longitude) center = [Number(salons[0].latitude), Number(salons[0].longitude)];

                const map = L.map(mapEl, { scrollWheelZoom: false }).setView(center, Number.isFinite(userLat) ? 13 : 11);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const markers = [];
                salons.forEach((salon, index) => {
                    if (salon.latitude === null || salon.longitude === null) return;
                    const marker = L.marker([Number(salon.latitude), Number(salon.longitude)]).addTo(map);
                    const url = `/salons/${encodeURIComponent(salon.slug)}`;
                    marker.bindPopup(`<strong>${salon.name ?? ''}</strong><br><a href="${url}">مشاهده سالن</a>`);
                    markers[index] = marker;
                });

                if (Number.isFinite(userLat) && Number.isFinite(userLng)) {
                    L.circleMarker([userLat, userLng], { radius: 8, color: '#4f46e5', fillColor: '#4f46e5', fillOpacity: .35 }).addTo(map).bindPopup('موقعیت شما');
                }

                document.querySelectorAll('[data-map-focus]').forEach((row) => {
                    row.addEventListener('click', (event) => {
                        event.preventDefault();
                        const index = Number(row.dataset.mapFocus);
                        const salon = salons[index];
                        if (!salon || salon.latitude === null || salon.longitude === null) return;
                        map.setView([Number(salon.latitude), Number(salon.longitude)], 15);
                        markers[index]?.openPopup();
                    });
                });

                setTimeout(() => map.invalidateSize(), 250);
            }
        });
    </script>
@endpush
