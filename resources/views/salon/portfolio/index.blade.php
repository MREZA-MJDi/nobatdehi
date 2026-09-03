@extends('layouts.salon')

@section('title', 'نمونه‌کارهای سالن')

@section('content')

    <div class="mx-auto w-full max-w-7xl px-4 py-6 pb-28 sm:px-6 lg:px-8">

        <div class="mb-7">

            <a
                href="{{ route('salon.dashboard') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← داشبورد سالن
            </a>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="text-[10px] font-black tracking-wider text-accent-600">
                        PORTFOLIO
                    </div>

                    <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                        نمونه‌کارهای {{ $salon->name }}
                    </h1>

                    <p class="mt-2 text-xs leading-6 text-content-muted">
                        نمونه‌کارهای قبل و بعد سالن را مدیریت کنید.
                    </p>

                </div>


                <a
                    href="{{ route('salon.portfolio.create') }}"
                    class="btn btn-accent"
                >
                    + افزودن نمونه‌کار
                </a>

            </div>

        </div>


        @if(session('success'))

            <div class="mb-5 alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if($portfolioItems->count())

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                @foreach($portfolioItems as $item)

                    <article class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft">

                        <div class="grid grid-cols-2">

                            <div class="relative aspect-square overflow-hidden bg-primary-50">

                                <img
                                    src="{{ asset('storage/' . $item->before_image_path) }}"
                                    alt="قبل {{ $item->title }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >

                                <span class="absolute right-3 top-3 rounded-full bg-black/65 px-3 py-1 text-[9px] font-black text-white backdrop-blur">
                                    قبل
                                </span>

                            </div>


                            <div class="relative aspect-square overflow-hidden bg-primary-50">

                                <img
                                    src="{{ asset('storage/' . $item->after_image_path) }}"
                                    alt="بعد {{ $item->title }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >

                                <span class="absolute right-3 top-3 rounded-full bg-accent-600/90 px-3 py-1 text-[9px] font-black text-white">
                                    بعد
                                </span>

                            </div>

                        </div>


                        <div class="p-5">

                            <div class="flex items-start justify-between gap-3">

                                <div class="min-w-0">

                                    <h2 class="truncate text-sm font-black text-content">
                                        {{ $item->title }}
                                    </h2>

                                    @if($item->service)

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            {{ $item->service->name }}
                                        </div>

                                    @endif

                                </div>


                                @if($item->is_active)

                                    <span class="badge badge-success shrink-0">
                                        فعال
                                    </span>

                                @else

                                    <span class="badge shrink-0">
                                        مخفی
                                    </span>

                                @endif

                            </div>


                            @if($item->barber)

                                <div class="mt-3 text-[10px] font-bold text-content-muted">
                                    آرایشگر:
                                    {{ $item->barber->name }}
                                </div>

                            @endif


                            @if($item->description)

                                <p class="mt-3 line-clamp-2 text-[10px] leading-6 text-content-muted">
                                    {{ $item->description }}
                                </p>

                            @endif


                            <div class="mt-5 flex justify-end border-t border-border pt-4">

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


            @if($portfolioItems->hasPages())

                <div class="mt-6">
                    {{ $portfolioItems->links() }}
                </div>

            @endif

        @else

            <section class="rounded-3xl border border-dashed border-border bg-surface p-10 text-center">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-50 text-2xl text-accent-600">
                    ✦
                </div>

                <h2 class="mt-4 text-base font-black text-content">
                    هنوز نمونه‌کاری ثبت نشده
                </h2>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    اولین نمونه‌کار قبل و بعد سالن را اضافه کنید.
                </p>

                <a
                    href="{{ route('salon.portfolio.create') }}"
                    class="btn btn-accent mt-5"
                >
                    افزودن نمونه‌کار
                </a>

            </section>

        @endif

    </div>

@endsection
