@extends('layouts.salon')

@section('title', 'نمونه‌کارهای سالن')

@section('content')

    <div class="px-4 py-5 pb-28 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div class="mx-auto w-full max-w-7xl">

            {{-- Header --}}
            <div class="mb-7">
                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                >
                    <span class="text-lg">→</span>
                    داشبورد سالن
                </a>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                                ✦
                            </div>

                            <div>
                                <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                                    PORTFOLIO
                                </div>

                                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                    نمونه‌کارهای {{ $salon->name }}
                                </h1>
                            </div>
                        </div>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            نمونه‌کارهای قبل و بعد سالن را مدیریت کنید تا کیفیت خدمات و نتیجه کار به مشتری‌ها نمایش داده شود.
                        </p>
                    </div>

                    <a
                        href="{{ route('salon.portfolio.create') }}"
                        class="btn btn-accent shrink-0"
                    >
                        + افزودن نمونه‌کار
                    </a>
                </div>
            </div>


            {{-- Success --}}
            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700">
                            ✓
                        </div>

                        <div class="text-xs font-bold leading-6 text-emerald-800">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif


            @if($portfolioItems->count())

                {{-- Portfolio Grid --}}
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

                    @foreach($portfolioItems as $item)

                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">

                            {{-- Before / After --}}
                            <div class="grid grid-cols-2 overflow-hidden">

                                {{-- Before --}}
                                <div class="relative aspect-square overflow-hidden bg-slate-100">
                                    <img
                                        src="{{ Storage::url($item->before_image_path) }}"
                                        alt="قبل {{ $item->title }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >

                                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/45 to-transparent"></div>

                                    <span class="absolute right-3 top-3 rounded-full bg-black/70 px-3 py-1.5 text-[9px] font-black text-white backdrop-blur">
                                    قبل
                                </span>
                                </div>


                                {{-- After --}}
                                <div class="relative aspect-square overflow-hidden bg-slate-100">
                                    <img
                                        src="{{ Storage::url($item->after_image_path) }}"
                                        alt="بعد {{ $item->title }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >

                                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/45 to-transparent"></div>

                                    <span class="absolute right-3 top-3 rounded-full bg-slate-950/85 px-3 py-1.5 text-[9px] font-black text-white backdrop-blur">
                                    بعد
                                </span>
                                </div>

                            </div>


                            {{-- Content --}}
                            <div class="p-5">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0">
                                        <h2 class="truncate text-sm font-black text-slate-950">
                                            {{ $item->title }}
                                        </h2>

                                        @if($item->service)
                                            <div class="mt-1.5 flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                                                <span>خدمت:</span>
                                                <span class="truncate text-slate-600">
                                                {{ $item->service->name }}
                                            </span>
                                            </div>
                                        @endif
                                    </div>


                                    @if($item->is_active)
                                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-[9px] font-black text-emerald-700">
                                        فعال
                                    </span>
                                    @else
                                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black text-slate-500">
                                        مخفی
                                    </span>
                                    @endif

                                </div>


                                {{-- Barber --}}
                                @if($item->barber)
                                    <div class="mt-4 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-[10px] font-black text-slate-500 shadow-sm">
                                            آ
                                        </div>

                                        <div class="min-w-0">
                                            <div class="text-[9px] font-bold text-slate-400">
                                                آرایشگر
                                            </div>

                                            <div class="truncate text-[10px] font-black text-slate-700">
                                                {{ $item->barber->name }}
                                            </div>
                                        </div>
                                    </div>
                                @endif


                                {{-- Description --}}
                                @if($item->description)
                                    <p class="mt-4 line-clamp-2 text-[10px] leading-6 text-slate-500">
                                        {{ $item->description }}
                                    </p>
                                @endif


                                {{-- Actions --}}
                                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">

                                    <div class="text-[9px] font-bold text-slate-400">
                                        نمونه‌کار قبل و بعد
                                    </div>

                                    <a
                                        href="{{ route('salon.portfolio.edit', $item) }}"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        ویرایش
                                    </a>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- Pagination --}}
                @if($portfolioItems->hasPages())
                    <div class="mt-7">
                        {{ $portfolioItems->links() }}
                    </div>
                @endif


            @else

                {{-- Empty State --}}
                <section class="relative overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm sm:p-14">

                    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-slate-100 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-16 h-44 w-44 rounded-full bg-slate-100 blur-3xl"></div>

                    <div class="relative">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-950 text-2xl text-white shadow-lg">
                            ✦
                        </div>

                        <h2 class="mt-5 text-base font-black text-slate-950">
                            هنوز نمونه‌کاری ثبت نشده
                        </h2>

                        <p class="mx-auto mt-2 max-w-md text-xs leading-7 text-slate-500">
                            اولین نمونه‌کار قبل و بعد سالن را اضافه کنید تا مشتری‌ها نتیجه خدمات شما را بهتر ببینند.
                        </p>

                        <a
                            href="{{ route('salon.portfolio.create') }}"
                            class="btn btn-accent mt-6"
                        >
                            افزودن اولین نمونه‌کار
                        </a>
                    </div>

                </section>

            @endif

        </div>
    </div>

@endsection
