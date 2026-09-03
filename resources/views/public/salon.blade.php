@extends('layouts.app')

@section('title', $salon->name)

@section(
    'meta_description',
    $salon->description
        ?: 'اطلاعات، خدمات، آرایشگرها، موقعیت و رزرو نوبت ' . $salon->name
)

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;

        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        $barbers = $salon->relationLoaded('barbers')
            ? $salon->barbers->where('is_active', true)
            : $salon->barbers()
                ->where('is_active', true)
                ->get();

        $services = $salon->relationLoaded('services')
            ? $salon->services->where('is_active', true)
            : $salon->services()
                ->where('is_active', true)
                ->get();

        $workingHours = $salon->relationLoaded('workingHours')
            ? $salon->workingHours
            : $salon->workingHours()
                ->orderBy('day_of_week')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Files
        |--------------------------------------------------------------------------
        */

        $coverUrl = $salon->cover_path
            ? Storage::url($salon->cover_path)
            : null;

        $logoUrl = $salon->logo_path
            ? Storage::url($salon->logo_path)
            : null;

        $qrUrl = $salon->qr_code_path
            ? Storage::url($salon->qr_code_path)
            : null;


        /*
        |--------------------------------------------------------------------------
        | Location
        |--------------------------------------------------------------------------
        */

        $hasLocation =
            $salon->latitude !== null &&
            $salon->longitude !== null;

        $latitude = $hasLocation
            ? (float) $salon->latitude
            : null;

        $longitude = $hasLocation
            ? (float) $salon->longitude
            : null;

        $mapsQuery = $hasLocation
            ? urlencode(
                $latitude . ',' . $longitude
            )
            : null;

        $googleMapsUrl = $hasLocation
            ? 'https://www.google.com/maps/dir/?api=1&destination=' .
                $mapsQuery
            : null;

        $mapsEmbedUrl = $hasLocation
            ? 'https://www.google.com/maps?q=' .
                $mapsQuery .
                '&z=16&output=embed'
            : null;

        $geoUrl = $hasLocation
            ? 'geo:' .
                $latitude .
                ',' .
                $longitude .
                '?q=' .
                $mapsQuery
            : null;


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        $fullAddress = collect([
            $salon->province,
            $salon->city,
            $salon->district,
            $salon->address,
        ])
            ->filter()
            ->join('، ');


        /*
        |--------------------------------------------------------------------------
        | Working Days
        |--------------------------------------------------------------------------
        |
        | Iran:
        | 0 = Saturday
        | 1 = Sunday
        | ...
        | 6 = Friday
        |
        */

        $dayNames = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ];


        /*
        |--------------------------------------------------------------------------
        | Persian digits
        |--------------------------------------------------------------------------
        */

        $persianDigits = [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ];


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        $toPersianDigits = function ($value) use ($persianDigits) {
            return strtr(
                (string) $value,
                $persianDigits
            );
        };

        $formatPrice = function ($price) use ($toPersianDigits) {
            if ($price === null || $price === '') {
                return null;
            }

            return $toPersianDigits(
                number_format((float) $price)
            ) . ' تومان';
        };
    @endphp


    <div
        x-data="{
            mapOpen: false,
            shareOpen: false,

            async shareSalon() {

                const data = {
                    title: @js($salon->name),
                    text: @js(
                        $salon->description
                            ?: 'مشاهده اطلاعات و رزرو نوبت ' . $salon->name
                    ),
                    url: window.location.href
                };

                if (navigator.share) {

                    try {
                        await navigator.share(data);
                    } catch (error) {
                        // User closed the share sheet.
                    }

                    return;
                }

                try {

                    await navigator.clipboard.writeText(
                        window.location.href
                    );

                    toast.success(
                        'لینک سالن کپی شد.'
                    );

                } catch (error) {

                    toast.info(
                        'لینک این صفحه را کپی کنید و برای دیگران بفرستید.'
                    );
                }
            },

            openDirections() {

                const geoUrl = @js($geoUrl);
                const googleUrl = @js($googleMapsUrl);

                if (!geoUrl && !googleUrl) {
                    toast.info(
                        'موقعیت مکانی سالن ثبت نشده است.'
                    );

                    return;
                }

                if (
                    /Android|iPhone|iPad|iPod/i.test(
                        navigator.userAgent
                    ) &&
                    geoUrl
                ) {
                    window.location.href = geoUrl;

                    window.setTimeout(() => {
                        if (googleUrl) {
                            window.location.href = googleUrl;
                        }
                    }, 700);

                    return;
                }

                if (googleUrl) {
                    window.open(
                        googleUrl,
                        '_blank',
                        'noopener,noreferrer'
                    );
                }
            }
        }"
        class="app-container pb-24 md:pb-8"
    >


        {{-- ============================================================
            BACK
        ============================================================= --}}

        <x-navigation.back
            :href="route('salons.discover')"
            label="بازگشت به سالن‌ها"
            class="mb-4"
        />


        {{-- ============================================================
            HERO / BRANDING
        ============================================================= --}}

        <section
            class="card relative overflow-hidden"
        >

            {{-- Cover --}}

            <div class="relative h-56 overflow-hidden sm:h-72 lg:h-80">

                @if($coverUrl)

                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $salon->name }}"
                        class="h-full w-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-black/10"></div>

                @else

                    <div
                        class="h-full w-full"
                        style="
                            background:
                            radial-gradient(
                            circle at 80% 20%,
                        {{ $salon->primary_color ?: '#6757E8' }}99,
                            transparent 42%
                            ),
                            radial-gradient(
                            circle at 15% 85%,
                        {{ $salon->secondary_color ?: '#37B8C8' }}55,
                            transparent 38%
                            ),
                            linear-gradient(
                            135deg,
                            #111319,
                            #2d323e
                            );
                            "
                    ></div>

                @endif


                {{-- Top Actions --}}

                <div class="absolute inset-x-4 top-4 flex items-center justify-between gap-2">

                    <a
                        href="{{ route('salons.discover') }}"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-white/20 bg-black/30 px-3 text-xs font-bold text-white backdrop-blur-xl transition hover:bg-black/45"
                    >

                        <span>
                            →
                        </span>

                        بازگشت

                    </a>


                    <button
                        type="button"
                        @click="shareSalon()"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-black/30 text-white backdrop-blur-xl transition hover:bg-black/45"
                        aria-label="اشتراک‌گذاری سالن"
                    >

                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <path d="m8.6 13.5 6.8 3.9" />
                            <path d="m15.4 6.6-6.8 3.9" />
                        </svg>

                    </button>

                </div>


                {{-- Cover Bottom --}}

                <div class="absolute inset-x-4 bottom-4">

                    <div class="flex flex-wrap items-center gap-2">

                        @if($salon->is_active)

                            <span class="rounded-full bg-green-500/90 px-3 py-1.5 text-[10px] font-bold text-white backdrop-blur">
                                فعال
                            </span>

                        @else

                            <span class="rounded-full bg-red-500/90 px-3 py-1.5 text-[10px] font-bold text-white backdrop-blur">
                                غیرفعال
                            </span>

                        @endif


                        <span class="rounded-full border border-white/20 bg-black/30 px-3 py-1.5 text-[10px] font-bold text-white backdrop-blur-xl">
                            سالن زیبایی
                        </span>


                        @if($salon->code)

                            <code class="rounded-full border border-white/20 bg-black/30 px-3 py-1.5 font-mono text-[9px] text-white/90 backdrop-blur-xl">
                                {{ $salon->code }}
                            </code>

                        @endif

                    </div>

                </div>

            </div>


            {{-- Brand Content --}}

            <div class="relative px-5 pb-5 sm:px-6 sm:pb-6">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">


                    {{-- Logo --}}

                    <div class="-mt-12 flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl border-4 border-surface bg-primary-950 text-2xl font-black text-white shadow-card">

                        @if($logoUrl)

                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $salon->name }}"
                                class="h-full w-full bg-white object-contain"
                            >

                        @else

                            {{ mb_substr($salon->name, 0, 1) }}

                        @endif

                    </div>


                    {{-- Name --}}

                    <div class="min-w-0 flex-1">

                        <h1 class="text-2xl font-black tracking-tight text-content sm:text-3xl">
                            {{ $salon->name }}
                        </h1>


                        @if($salon->description)

                            <p class="mt-2 max-w-3xl text-xs leading-7 text-content-muted sm:text-sm">
                                {{ $salon->description }}
                            </p>

                        @endif


                        @if($fullAddress)

                            <div class="mt-2 flex items-start gap-1.5 text-xs text-content-muted">

                                <svg
                                    class="mt-0.5 shrink-0"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>

                                <span>
                                    {{ $fullAddress }}
                                </span>

                            </div>

                        @endif

                    </div>


                    {{-- Main Booking CTA --}}

                    <div class="sm:shrink-0">

                        <a
                            href="{{ route('public.salons.booking.create', $salon) }}"
                            class="btn btn-accent btn-lg w-full sm:w-auto"
                        >

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="4"
                                    y="5"
                                    width="16"
                                    height="16"
                                    rx="2"
                                />
                                <path d="M8 3v4" />
                                <path d="M16 3v4" />
                                <path d="M4 10h16" />
                            </svg>

                            رزرو نوبت

                        </a>

                    </div>

                </div>


                {{-- Quick Information --}}

                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4">

                    <div class="rounded-2xl bg-primary-50 p-3">

                        <div class="text-[10px] text-content-muted">
                            آرایشگر
                        </div>

                        <div class="mt-1 text-lg font-black text-content">
                            {{ $toPersianDigits($barbers->count()) }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-primary-50 p-3">

                        <div class="text-[10px] text-content-muted">
                            خدمت
                        </div>

                        <div class="mt-1 text-lg font-black text-content">
                            {{ $toPersianDigits($services->count()) }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-primary-50 p-3">

                        <div class="text-[10px] text-content-muted">
                            وضعیت
                        </div>

                        <div class="mt-1 text-sm font-black text-content">
                            {{ $salon->is_active ? 'آماده رزرو' : 'موقتاً بسته' }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-primary-50 p-3">

                        <div class="text-[10px] text-content-muted">
                            کد سالن
                        </div>

                        <code class="mt-1 block truncate font-mono text-xs font-bold text-content">
                            {{ $salon->code ?: '—' }}
                        </code>

                    </div>

                </div>

            </div>

        </section>


        {{-- ============================================================
            CONTENT GRID
        ============================================================= --}}

        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_22rem]">


            {{-- ========================================================
                MAIN COLUMN
            ========================================================= --}}

            <div class="min-w-0 space-y-5">


                {{-- ====================================================
                    SERVICES
                ===================================================== --}}

                <section
                    id="services"
                    class="card p-5 sm:p-6"
                >

                    <div class="mb-5 flex items-end justify-between gap-3">

                        <div>

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                SERVICES
                            </div>

                            <h2 class="mt-1 section-title">
                                خدمات سالن
                            </h2>

                            <p class="mt-1 text-xs text-content-muted">
                                خدمات فعال سالن را ببین و در مرحله رزرو انتخاب کن.
                            </p>

                        </div>


                        <span class="badge badge-neutral">
                            {{ $toPersianDigits($services->count()) }} خدمت
                        </span>

                    </div>


                    @if($services->isNotEmpty())

                        <div class="grid gap-3 sm:grid-cols-2">

                            @foreach($services as $service)

                                <div
                                    class="group rounded-2xl border border-border bg-surface-soft p-4 transition hover:border-accent-200 hover:shadow-soft"
                                >

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">

                                            <h3 class="truncate text-sm font-black text-content">
                                                {{ $service->name }}
                                            </h3>


                                            @if($service->description)

                                                <p class="mt-1 line-clamp-2 text-[10px] leading-6 text-content-muted">
                                                    {{ $service->description }}
                                                </p>

                                            @endif

                                        </div>


                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                            <svg
                                                width="18"
                                                height="18"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M6 3h12" />
                                                <path d="M6 21h12" />
                                                <path d="M6 3c0 4 3 6 6 8-3 2-6 4-6 10" />
                                                <path d="M18 3c0 4-3 6-6 8 3 2 6 4 6 10" />
                                            </svg>

                                        </div>

                                    </div>


                                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-border pt-3">

                                        <div class="text-[10px] text-content-muted">

                                            @if($service->duration_minutes)

                                                {{ $toPersianDigits($service->duration_minutes) }}
                                                دقیقه

                                            @else

                                                زمان متغیر

                                            @endif

                                        </div>


                                        @if($formatPrice($service->price))

                                            <div class="text-xs font-black text-content">
                                                {{ $formatPrice($service->price) }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        </div>


                        <div class="mt-4">

                            <a
                                href="{{ route('public.salons.booking.create', $salon) }}"
                                class="btn btn-accent w-full"
                            >
                                انتخاب خدمت و رزرو نوبت
                            </a>

                        </div>

                    @else

                        <div class="rounded-2xl border border-dashed border-border bg-surface-soft p-8 text-center">

                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 text-content-muted">

                                <svg
                                    width="21"
                                    height="21"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M6 3h12" />
                                    <path d="M6 21h12" />
                                    <path d="M6 3c0 4 3 6 6 8-3 2-6 4-6 10" />
                                    <path d="M18 3c0 4-3 6-6 8 3 2 6 4 6 10" />
                                </svg>

                            </div>

                            <h3 class="mt-3 text-sm font-black text-content">
                                هنوز خدمتی ثبت نشده است
                            </h3>

                            <p class="mt-1 text-xs text-content-muted">
                                خدمات قابل رزرو این سالن هنوز اضافه نشده‌اند.
                            </p>

                        </div>

                    @endif

                </section>


                {{-- ====================================================
                    BARBERS
                ===================================================== --}}

                <section
                    id="barbers"
                    class="card p-5 sm:p-6"
                >

                    <div class="mb-5 flex items-end justify-between gap-3">

                        <div>

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                TEAM
                            </div>

                            <h2 class="mt-1 section-title">
                                آرایشگرهای سالن
                            </h2>

                        </div>


                        <span class="badge badge-neutral">
                            {{ $toPersianDigits($barbers->count()) }} نفر
                        </span>

                    </div>


                    @if($barbers->isNotEmpty())

                        <div class="grid gap-3 sm:grid-cols-2">

                            @foreach($barbers as $barber)

                                <article
                                    class="group overflow-hidden rounded-2xl border border-border bg-surface transition hover:-translate-y-0.5 hover:border-accent-200 hover:shadow-soft"
                                >

                                    <div class="relative h-48 overflow-hidden bg-primary-100">

                                        @if($barber->image_path)

                                            <img
                                                src="{{ Storage::url($barber->image_path) }}"
                                                alt="{{ $barber->name }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >

                                        @else

                                            <div
                                                class="flex h-full w-full items-center justify-center text-3xl font-black text-content-muted"
                                            >
                                                {{ mb_substr($barber->name, 0, 1) }}
                                            </div>

                                        @endif


                                        <div class="absolute inset-x-3 bottom-3">

                                            <span class="inline-flex rounded-full border border-white/20 bg-black/40 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-xl">
                                                فعال
                                            </span>

                                        </div>

                                    </div>


                                    <div class="p-4">

                                        <h3 class="text-sm font-black text-content">
                                            {{ $barber->name }}
                                        </h3>


                                        @if($barber->specialty)

                                            <p class="mt-1 text-[10px] font-bold text-accent-600">
                                                {{ $barber->specialty }}
                                            </p>

                                        @endif


                                        @if($barber->bio)

                                            <p class="mt-2 line-clamp-3 text-[10px] leading-6 text-content-muted">
                                                {{ $barber->bio }}
                                            </p>

                                        @endif


                                        <a
                                            href="{{ route('public.salons.booking.create', $salon) }}"
                                            class="btn btn-secondary btn-sm mt-4 w-full"
                                        >
                                            رزرو با این سالن
                                        </a>

                                    </div>

                                </article>

                            @endforeach

                        </div>

                    @else

                        <div class="rounded-2xl border border-dashed border-border bg-surface-soft p-8 text-center">

                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 text-content-muted">

                                <svg
                                    width="22"
                                    height="22"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                                </svg>

                            </div>

                            <h3 class="mt-3 text-sm font-black text-content">
                                آرایشگر فعالی ثبت نشده است
                            </h3>

                        </div>

                    @endif

                </section>

                {{-- ============================================================
                    PORTFOLIO
                ============================================================= --}}

                @if($salon->portfolioItems->count())

                    <section class="mt-6">

                        <div class="mb-4 flex items-end justify-between gap-3">

                            <div>

                                <div class="text-[10px] font-black tracking-wider text-accent-600">
                                    PORTFOLIO
                                </div>

                                <h2 class="mt-1 text-xl font-black text-content">
                                    نمونه‌کارها
                                </h2>

                            </div>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            @foreach($salon->portfolioItems as $item)

                                <article class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft">

                                    <div class="grid grid-cols-2">

                                        <div class="relative aspect-square overflow-hidden">

                                            <img
                                                src="{{ asset('storage/' . $item->before_image_path) }}"
                                                alt="قبل {{ $item->title }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >

                                            <span class="absolute right-2 top-2 rounded-full bg-black/65 px-2.5 py-1 text-[9px] font-black text-white">
                                قبل
                            </span>

                                        </div>


                                        <div class="relative aspect-square overflow-hidden">

                                            <img
                                                src="{{ asset('storage/' . $item->after_image_path) }}"
                                                alt="بعد {{ $item->title }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >

                                            <span class="absolute right-2 top-2 rounded-full bg-accent-600 px-2.5 py-1 text-[9px] font-black text-white">
                                بعد
                            </span>

                                        </div>

                                    </div>


                                    <div class="p-4">

                                        <div class="text-sm font-black text-content">
                                            {{ $item->title }}
                                        </div>


                                        @if($item->barber)

                                            <div class="mt-1 text-[10px] text-content-muted">
                                                توسط {{ $item->barber->name }}
                                            </div>

                                        @endif


                                        @if($item->service)

                                            <div class="mt-1 text-[10px] text-content-muted">
                                                {{ $item->service->name }}
                                            </div>

                                        @endif

                                    </div>

                                </article>

                            @endforeach

                        </div>

                    </section>

                @endif
                {{-- ====================================================
                    WORKING HOURS
                ===================================================== --}}

                <section
                    id="working-hours"
                    class="card p-5 sm:p-6"
                >

                    <div class="mb-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            WORKING HOURS
                        </div>

                        <h2 class="mt-1 section-title">
                            ساعات کاری
                        </h2>

                        <p class="mt-1 text-xs text-content-muted">
                            زمان فعالیت سالن در طول هفته.
                        </p>

                    </div>


                    @if($workingHours->isNotEmpty())

                        <div class="divide-y divide-border rounded-2xl border border-border overflow-hidden">

                            @foreach($workingHours as $workingHour)

                                <div class="flex items-center justify-between gap-4 bg-surface-soft px-4 py-3">

                                    <div class="text-xs font-bold text-content">

                                        {{ $dayNames[$workingHour->day_of_week] ?? 'روز هفته' }}

                                    </div>


                                    @if($workingHour->is_closed)

                                        <span class="badge badge-danger">
                                            تعطیل
                                        </span>

                                    @elseif(
                                        $workingHour->start_time &&
                                        $workingHour->end_time
                                    )

                                        <span
                                            class="rounded-xl bg-primary-100 px-3 py-1.5 font-mono text-[11px] font-bold text-content"
                                            dir="ltr"
                                        >
                                            {{ substr($workingHour->start_time, 0, 5) }}
                                            —
                                            {{ substr($workingHour->end_time, 0, 5) }}
                                        </span>

                                    @else

                                        <span class="text-[10px] text-content-faint">
                                            تعیین نشده
                                        </span>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="rounded-2xl border border-dashed border-border bg-surface-soft p-6 text-center">

                            <p class="text-xs text-content-muted">
                                ساعات کاری هنوز ثبت نشده است.
                            </p>

                        </div>

                    @endif

                </section>


                {{-- ====================================================
                    LOCATION
                ===================================================== --}}

                <section
                    id="location"
                    class="card overflow-hidden"
                >

                    <div class="p-5 sm:p-6">

                        <div class="mb-5">

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                LOCATION
                            </div>

                            <h2 class="mt-1 section-title">
                                آدرس و موقعیت سالن
                            </h2>

                        </div>


                        @if($fullAddress)

                            <div class="mb-4 flex items-start gap-3 rounded-2xl bg-surface-soft p-4">

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                    <svg
                                        width="19"
                                        height="19"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>

                                </div>


                                <div class="min-w-0">

                                    <div class="text-xs font-black text-content">
                                        آدرس
                                    </div>

                                    <div class="mt-1 text-xs leading-7 text-content-muted">
                                        {{ $fullAddress }}
                                    </div>

                                </div>

                            </div>

                        @endif


                        @if($hasLocation)

                            <div class="overflow-hidden rounded-3xl border border-border">

                                <div class="relative h-[22rem] sm:h-[28rem]">

                                    <iframe
                                        src="{{ $mapsEmbedUrl }}"
                                        title="موقعیت {{ $salon->name }} روی Google Maps"
                                        class="absolute inset-0 h-full w-full border-0"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>


                                    <div class="absolute inset-x-3 bottom-3">

                                        <div class="flex flex-col gap-2 rounded-2xl border border-white/50 bg-white/90 p-3 shadow-float backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">

                                            <div class="min-w-0">

                                                <div class="text-xs font-black text-zinc-900">
                                                    {{ $salon->name }}
                                                </div>

                                                <div class="mt-1 truncate text-[10px] text-zinc-500">
                                                    {{ $fullAddress ?: 'موقعیت ثبت‌شده سالن' }}
                                                </div>

                                            </div>


                                            <button
                                                type="button"
                                                @click="openDirections()"
                                                class="btn btn-primary btn-sm shrink-0"
                                            >
                                                مسیریابی
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">

                                <button
                                    type="button"
                                    @click="openDirections()"
                                    class="btn btn-accent"
                                >

                                    <svg
                                        width="17"
                                        height="17"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M3 11.5 21 3l-8.5 18-2.2-7.3L3 11.5Z" />
                                        <path d="M10.3 13.7 15 9" />
                                    </svg>

                                    مسیریابی

                                </button>


                                <a
                                    href="{{ $googleMapsUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-secondary"
                                >
                                    باز کردن Google Maps ↗
                                </a>

                            </div>

                        @else

                            <div class="rounded-3xl border border-dashed border-border bg-surface-soft p-8 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-content-muted">

                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>

                                </div>

                                <h3 class="mt-4 text-sm font-black text-content">
                                    موقعیت مکانی ثبت نشده است
                                </h3>

                                @if($fullAddress)

                                    <p class="mt-2 text-xs leading-7 text-content-muted">
                                        {{ $fullAddress }}
                                    </p>

                                @endif

                            </div>

                        @endif

                    </div>

                </section>

            </div>


            {{-- ========================================================
                SIDEBAR
            ========================================================= --}}

            <aside class="space-y-5">


                {{-- Booking Card --}}

                <div class="card p-5 lg:sticky lg:top-24">

                    <div class="text-[10px] font-black tracking-wider text-accent-600">
                        BOOKING
                    </div>


                    <h2 class="mt-1 text-lg font-black text-content">
                        آماده رزرو نوبت هستی؟
                    </h2>


                    <p class="mt-2 text-xs leading-6 text-content-muted">
                        آرایشگر، خدمت، تاریخ و ساعت مناسب خودت را انتخاب کن.
                    </p>


                    <a
                        href="{{ route('public.salons.booking.create', $salon) }}"
                        class="btn btn-accent btn-lg mt-4 w-full"
                    >
                        رزرو نوبت
                    </a>


                    @if($salon->phone)

                        <a
                            href="tel:{{ $salon->phone }}"
                            dir="ltr"
                            class="btn btn-secondary mt-2 w-full"
                        >
                            {{ $salon->phone }}
                        </a>

                    @endif

                </div>


                {{-- Contact --}}

                @if(
                    $salon->phone ||
                    $salon->email
                )

                    <div class="card p-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            CONTACT
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            ارتباط با سالن
                        </h2>


                        <div class="mt-4 space-y-2">

                            @if($salon->phone)

                                <a
                                    href="tel:{{ $salon->phone }}"
                                    class="flex items-center gap-3 rounded-2xl bg-primary-50 p-3 transition hover:bg-primary-100"
                                >

                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface text-content-muted">

                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7c.1 1 .3 2 .7 2.9a2 2 0 0 1-.5 2.1L9 10.9a16 16 0 0 0 4.1 4.1l1.2-1.3a2 2 0 0 1 2.1-.5c.9.4 1.9.6 2.9.7A2 2 0 0 1 22 16.9Z" />
                                        </svg>

                                    </span>


                                    <span class="min-w-0">

                                        <span class="block text-[10px] text-content-muted">
                                            تلفن
                                        </span>

                                        <span
                                            dir="ltr"
                                            class="mt-0.5 block text-xs font-bold text-content"
                                        >
                                            {{ $salon->phone }}
                                        </span>

                                    </span>

                                </a>

                            @endif


                            @if($salon->email)

                                <a
                                    href="mailto:{{ $salon->email }}"
                                    class="flex items-center gap-3 rounded-2xl bg-primary-50 p-3 transition hover:bg-primary-100"
                                >

                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface text-content-muted">

                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <rect x="3" y="5" width="18" height="14" rx="2" />
                                            <path d="m3 7 9 6 9-6" />
                                        </svg>

                                    </span>


                                    <span class="min-w-0">

                                        <span class="block text-[10px] text-content-muted">
                                            ایمیل
                                        </span>

                                        <span
                                            dir="ltr"
                                            class="mt-0.5 block truncate text-xs font-bold text-content"
                                        >
                                            {{ $salon->email }}
                                        </span>

                                    </span>

                                </a>

                            @endif

                        </div>

                    </div>

                @endif


                {{-- QR --}}

                @if($qrUrl)

                    <div class="card p-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            QR CODE
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            صفحه سالن را اسکن کن
                        </h2>


                        <div class="mt-4 flex justify-center rounded-2xl bg-white p-4">

                            <img
                                src="{{ $qrUrl }}"
                                alt="QR Code {{ $salon->name }}"
                                class="h-44 w-44 object-contain"
                            >

                        </div>


                        <p class="mt-3 text-center text-[10px] leading-6 text-content-muted">
                            با اسکن این QR، صفحه عمومی این سالن باز می‌شود.
                        </p>

                    </div>

                @endif


                {{-- Brand Colors --}}

                @if(
                    $salon->primary_color ||
                    $salon->secondary_color
                )

                    <div class="card p-5">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            BRAND
                        </div>

                        <h2 class="mt-1 text-base font-black text-content">
                            رنگ‌بندی سالن
                        </h2>


                        <div class="mt-4 space-y-2">

                            @if($salon->primary_color)

                                <div class="flex items-center justify-between gap-3 rounded-xl bg-primary-50 p-3">

                                    <div class="flex items-center gap-2">

                                        <span
                                            class="h-8 w-8 rounded-lg border border-border"
                                            style="background: {{ $salon->primary_color }}"
                                        ></span>

                                        <span class="text-[10px] font-bold text-content">
                                            رنگ اصلی
                                        </span>

                                    </div>


                                    <code
                                        dir="ltr"
                                        class="font-mono text-[10px] text-content-muted"
                                    >
                                        {{ $salon->primary_color }}
                                    </code>

                                </div>

                            @endif


                            @if($salon->secondary_color)

                                <div class="flex items-center justify-between gap-3 rounded-xl bg-primary-50 p-3">

                                    <div class="flex items-center gap-2">

                                        <span
                                            class="h-8 w-8 rounded-lg border border-border"
                                            style="background: {{ $salon->secondary_color }}"
                                        ></span>

                                        <span class="text-[10px] font-bold text-content">
                                            رنگ مکمل
                                        </span>

                                    </div>


                                    <code
                                        dir="ltr"
                                        class="font-mono text-[10px] text-content-muted"
                                    >
                                        {{ $salon->secondary_color }}
                                    </code>

                                </div>

                            @endif

                        </div>

                    </div>

                @endif

            </aside>

        </div>


        {{-- ============================================================
            MOBILE STICKY BOOKING
        ============================================================= --}}

        <div class="fixed inset-x-3 bottom-[calc(4.75rem+env(safe-area-inset-bottom))] z-40 md:hidden">

            <div class="rounded-2xl border border-border bg-surface/95 p-2 shadow-float backdrop-blur-xl">

                <a
                    href="{{ route('public.salons.booking.create', $salon) }}"
                    class="btn btn-accent btn-lg w-full"
                >

                    رزرو نوبت

                    <svg
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m9 18 6-6-6-6" />
                    </svg>

                </a>

            </div>

        </div>


        {{-- ============================================================
            MOBILE MAP MODAL
        ============================================================= --}}

        @if($hasLocation)

            <div
                x-cloak
                x-show="mapOpen"
                x-transition.opacity
                class="fixed inset-0 z-[90] md:hidden"
            >

                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="mapOpen = false"
                ></div>


                <div
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="translate-y-full"
                    x-transition:enter-end="translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0"
                    x-transition:leave-end="translate-y-full"
                    class="absolute inset-x-0 bottom-0 max-h-[90dvh] overflow-hidden rounded-t-[2rem] border border-border bg-surface p-3 shadow-float"
                >

                    <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-primary-300"></div>


                    <div class="mb-3 flex items-center justify-between gap-3 px-2">

                        <div>

                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                LOCATION
                            </div>

                            <h3 class="mt-1 text-sm font-black text-content">
                                {{ $salon->name }}
                            </h3>

                        </div>


                        <button
                            type="button"
                            @click="mapOpen = false"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-content-muted"
                        >
                            ×
                        </button>

                    </div>


                    <div class="overflow-hidden rounded-2xl border border-border">

                        <iframe
                            src="{{ $mapsEmbedUrl }}"
                            title="موقعیت {{ $salon->name }}"
                            class="h-[22rem] w-full border-0"
                            loading="lazy"
                        ></iframe>

                    </div>


                    <button
                        type="button"
                        @click="openDirections()"
                        class="btn btn-accent mt-3 w-full"
                    >
                        باز کردن مسیریابی
                    </button>

                </div>

            </div>

        @endif


    </div>

@endsection

