@extends('layouts.salon')

@section('title', 'نظرات مشتریان')

@section('content')

    @php

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


        $reviewsCountText =
            strtr(
                (string) $reviewsCount,
                $persianDigits
            );


        $publishedCountText =
            strtr(
                (string) $publishedCount,
                $persianDigits
            );


        $pendingCountText =
            strtr(
                (string) $pendingCount,
                $persianDigits
            );


        $averageRatingText =
            $averageRating !== null
                ? strtr(
                    number_format(
                        (float) $averageRating,
                        1
                    ),
                    $persianDigits
                )
                : '۰';


        $ratingLabels = [
            5 => '۵ ستاره',
            4 => '۴ ستاره',
            3 => '۳ ستاره',
            2 => '۲ ستاره',
            1 => '۱ ستاره',
        ];

    @endphp


    <div
        class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:py-8"
        dir="rtl"
    >


        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-7">

            <a
                href="{{ route('salon.dashboard') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به داشبورد
            </a>


            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                <div>

                    <div class="text-[9px] font-black tracking-[0.18em] text-accent-600">
                        CUSTOMER REVIEWS
                    </div>


                    <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                        نظرات مشتریان
                    </h1>


                    <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted sm:text-sm">
                        بازخورد مشتریان را بررسی کنید و مشخص کنید کدام نظر در صفحه عمومی سالن نمایش داده شود.
                    </p>

                </div>


                <div class="rounded-2xl border border-border bg-white px-4 py-3 shadow-soft">

                    <div class="text-[9px] font-bold text-content-muted">
                        سالن
                    </div>


                    <div class="mt-1 text-sm font-black text-content">
                        {{ $salon->name }}
                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            SUCCESS MESSAGE
        ============================================================= --}}

        @if(session('success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                class="mb-6 rounded-3xl border border-emerald-100 bg-emerald-50 p-4"
            >

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="text-xs font-black text-emerald-800">
                            انجام شد
                        </div>

                        <div class="mt-1 text-[10px] font-bold leading-6 text-emerald-700">
                            {{ session('success') }}
                        </div>

                    </div>


                    <button
                        type="button"
                        @click="show = false"
                        class="text-sm font-black text-emerald-600"
                    >
                        ×
                    </button>

                </div>

            </div>

        @endif


        {{-- ============================================================
            STATS
        ============================================================= --}}

        <section class="mb-8">

            <div class="mb-4">

                <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                    OVERVIEW
                </div>

                <h2 class="mt-1 text-base font-black text-content">
                    وضعیت نظرات
                </h2>

            </div>


            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">


                {{-- Average --}}

                <div class="rounded-3xl border border-border bg-white p-5 shadow-soft">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                میانگین امتیاز
                            </div>


                            <div class="mt-3 flex items-end gap-2">

                                <div class="text-3xl font-black text-content">
                                    {{ $averageRatingText }}
                                </div>

                                <div class="pb-1 text-lg text-amber-400">
                                    ★
                                </div>

                            </div>

                        </div>


                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                            ★
                        </div>

                    </div>

                </div>


                {{-- All --}}

                <a
                    href="{{ route('salon.reviews.index', ['status' => 'all']) }}"
                    class="rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:border-accent-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                کل نظرات
                            </div>


                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $reviewsCountText }}
                            </div>

                        </div>


                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                            ♧
                        </div>

                    </div>

                </a>


                {{-- Published --}}

                <a
                    href="{{ route('salon.reviews.index', ['status' => 'published']) }}"
                    class="rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:border-emerald-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                منتشرشده
                            </div>


                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $publishedCountText }}
                            </div>

                        </div>


                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            ✓
                        </div>

                    </div>

                </a>


                {{-- Pending --}}

                <a
                    href="{{ route('salon.reviews.index', ['status' => 'pending']) }}"
                    class="rounded-3xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:border-amber-200"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-[10px] font-bold text-content-muted">
                                در انتظار انتشار
                            </div>


                            <div class="mt-3 text-3xl font-black text-content">
                                {{ $pendingCountText }}
                            </div>

                        </div>


                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            ⌛
                        </div>

                    </div>

                </a>

            </div>

        </section>


        {{-- ============================================================
            RATING DISTRIBUTION
        ============================================================= --}}

        <section class="mb-8 grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">


            <div class="rounded-3xl border border-border bg-white p-5 shadow-soft sm:p-6">

                <div class="mb-5">

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        RATING
                    </div>

                    <h2 class="mt-1 text-base font-black text-content">
                        توزیع امتیازها
                    </h2>

                </div>


                <div class="space-y-4">

                    @foreach($ratingDistribution as $rating => $count)

                        @php

                            $percentage =
                                $reviewsCount > 0
                                    ? round(
                                        ($count / $reviewsCount) * 100
                                    )
                                    : 0;

                            $countText =
                                strtr(
                                    (string) $count,
                                    $persianDigits
                                );

                        @endphp


                        <div>

                            <div class="mb-2 flex items-center justify-between gap-4">

                                <div class="flex items-center gap-2">

                                    <span class="text-[10px] font-black text-content">
                                        {{ $ratingLabels[$rating] }}
                                    </span>

                                    <span class="text-amber-400">
                                        ★
                                    </span>

                                </div>


                                <span class="text-[10px] font-bold text-content-muted">
                                    {{ $countText }}
                                </span>

                            </div>


                            <div class="h-2 overflow-hidden rounded-full bg-primary-50">

                                <div
                                    class="h-full rounded-full bg-accent-500 transition-all"
                                    style="width: {{ $percentage }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- Filter --}}

            <div class="rounded-3xl border border-border bg-primary-950 p-5 text-white shadow-soft sm:p-6">

                <div class="text-[9px] font-black tracking-[0.18em] text-accent-300">
                    FILTER
                </div>


                <h2 class="mt-2 text-base font-black">
                    نمایش نظرات
                </h2>


                <p class="mt-2 text-[10px] leading-6 text-white/55">
                    برای مدیریت سریع، وضعیت نمایش نظرات را انتخاب کنید.
                </p>


                <div class="mt-5 space-y-2">


                    <a
                        href="{{ route('salon.reviews.index', ['status' => 'all']) }}"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-xs font-black transition {{ $status === 'all' ? 'bg-white text-primary-950' : 'bg-white/5 text-white hover:bg-white/10' }}"
                    >

                        <span>
                            همه نظرات
                        </span>

                        <span>
                            {{ $reviewsCountText }}
                        </span>

                    </a>


                    <a
                        href="{{ route('salon.reviews.index', ['status' => 'published']) }}"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-xs font-black transition {{ $status === 'published' ? 'bg-white text-primary-950' : 'bg-white/5 text-white hover:bg-white/10' }}"
                    >

                        <span>
                            منتشرشده
                        </span>

                        <span>
                            {{ $publishedCountText }}
                        </span>

                    </a>


                    <a
                        href="{{ route('salon.reviews.index', ['status' => 'pending']) }}"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-xs font-black transition {{ $status === 'pending' ? 'bg-white text-primary-950' : 'bg-white/5 text-white hover:bg-white/10' }}"
                    >

                        <span>
                            در انتظار انتشار
                        </span>

                        <span>
                            {{ $pendingCountText }}
                        </span>

                    </a>

                </div>

            </div>

        </section>


        {{-- ============================================================
            REVIEWS
        ============================================================= --}}

        <section>

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="text-[9px] font-black tracking-[0.18em] text-content-faint">
                        CUSTOMER FEEDBACK
                    </div>

                    <h2 class="mt-1 text-base font-black text-content">
                        نظرات مشتریان
                    </h2>

                </div>


                <div class="text-[10px] font-bold text-content-muted">
                    @if($status === 'published')
                        فقط نظرات منتشرشده
                    @elseif($status === 'pending')
                        فقط نظرات در انتظار انتشار
                    @else
                        همه نظرات
                    @endif
                </div>

            </div>


            @if($reviews->count())

                <div class="space-y-4">

                    @foreach($reviews as $review)

                        @php

                            $customerName =
                                $review->customer?->name
                                    ?: 'مشتری';


                            $serviceName =
                                $review->booking?->service?->name
                                    ?: 'خدمت';


                            $barberName =
                                $review->booking?->barber?->name
                                    ?: null;


                            $reviewDate =
                                $review->created_at
                                    ? $review->created_at->format('Y/m/d')
                                    : null;

                        @endphp


                        <article class="overflow-hidden rounded-3xl border border-border bg-white shadow-soft">


                            {{-- Top --}}

                            <div class="flex flex-col gap-4 border-b border-border p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">

                                <div class="flex min-w-0 items-center gap-3">


                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-600">

                                        {{ mb_substr($customerName, 0, 1) }}

                                    </div>


                                    <div class="min-w-0">

                                        <div class="truncate text-sm font-black text-content">
                                            {{ $customerName }}
                                        </div>


                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] text-content-muted">

                                            @if($reviewDate)

                                                <span dir="ltr">
                                                    {{ strtr($reviewDate, $persianDigits) }}
                                                </span>

                                            @endif


                                            @if($serviceName)

                                                <span class="h-1 w-1 rounded-full bg-content-faint"></span>

                                                <span>
                                                    {{ $serviceName }}
                                                </span>

                                            @endif


                                            @if($barberName)

                                                <span class="h-1 w-1 rounded-full bg-content-faint"></span>

                                                <span>
                                                    {{ $barberName }}
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </div>


                                {{-- Status --}}

                                @if($review->is_published)

                                    <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-[9px] font-black text-emerald-700">

                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        منتشرشده

                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2 text-[9px] font-black text-amber-700">

                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>

                                        در انتظار انتشار

                                    </span>

                                @endif

                            </div>


                            {{-- Body --}}

                            <div class="p-5 sm:p-6">

                                <div class="mb-4 flex items-center gap-1">

                                    @for($star = 1; $star <= 5; $star++)

                                        <span
                                            class="text-lg leading-none {{ $star <= (int) $review->rating ? 'text-amber-400' : 'text-content-faint' }}"
                                        >
                                            ★
                                        </span>

                                    @endfor


                                    <span class="mr-2 text-[10px] font-black text-content-muted">
                                        {{ strtr((string) $review->rating, $persianDigits) }}
                                        از ۵
                                    </span>

                                </div>


                                @if($review->comment)

                                    <p class="text-sm leading-8 text-content">
                                        {{ $review->comment }}
                                    </p>

                                @else

                                    <p class="text-xs italic text-content-faint">
                                        مشتری متنی برای نظر ثبت نکرده است.
                                    </p>

                                @endif

                            </div>


                            {{-- Footer --}}

                            <div class="flex flex-col gap-3 border-t border-border bg-primary-50 p-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="text-[10px] text-content-muted">

                                    @if($review->booking)

                                        <span>
                                            نوبت:
                                        </span>

                                        <span class="font-bold text-content-soft">
                                            #{{ strtr((string) $review->booking->id, $persianDigits) }}
                                        </span>

                                    @endif

                                </div>


                                <form
                                    action="{{ route('salon.reviews.publish', $review) }}"
                                    method="POST"
                                >

                                    @csrf

                                    @method('PATCH')


                                    @if($review->is_published)

                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-xl border border-red-100 bg-white px-4 py-2.5 text-[10px] font-black text-red-600 transition hover:bg-red-50 sm:w-auto"
                                        >
                                            مخفی کردن از صفحه عمومی
                                        </button>

                                    @else

                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-xl bg-accent-600 px-4 py-2.5 text-[10px] font-black text-white transition hover:opacity-90 sm:w-auto"
                                        >
                                            انتشار در صفحه سالن
                                        </button>

                                    @endif

                                </form>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- Pagination --}}

                @if($reviews->hasPages())

                    <div class="mt-6">

                        {{ $reviews->links() }}

                    </div>

                @endif


            @else

                <div class="rounded-3xl border border-border bg-white p-10 text-center shadow-soft">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-xl text-content-faint">
                        ★
                    </div>


                    <div class="mt-5 text-sm font-black text-content">
                        هنوز نظری وجود ندارد.
                    </div>


                    <div class="mt-2 text-[10px] leading-6 text-content-muted">
                        با دریافت اولین بازخورد مشتری، نظرات اینجا نمایش داده می‌شوند.
                    </div>

                </div>

            @endif

        </section>


    </div>

@endsection

