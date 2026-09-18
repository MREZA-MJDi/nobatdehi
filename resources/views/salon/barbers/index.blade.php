@extends('layouts.salon')

@section('title', 'آرایشگرهای سالن')

@section('content')

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8">

        <div class="mx-auto w-full max-w-7xl">

            {{-- ====================================================
                PAGE HEADER
            ===================================================== --}}

            <div class="mb-6">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                    <div class="min-w-0">

                        <a
                            href="{{ route('salon.dashboard') }}"
                            class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                        >
                            <span class="text-sm">←</span>
                            داشبورد سالن
                        </a>


                        <div class="flex items-center gap-2">

                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-accent-50 text-accent-600 dark:bg-accent-900/20"
                            >
                                ♙
                            </span>

                            <span class="text-[10px] font-black tracking-[0.18em] text-accent-600">
                                BARBERS
                            </span>

                        </div>


                        <h1 class="mt-3 text-2xl font-black tracking-tight text-content sm:text-3xl">
                            آرایشگرهای {{ $salon->name }}
                        </h1>


                        <p class="mt-2 max-w-xl text-xs leading-7 text-content-muted">
                            پروفایل، وضعیت فعالیت و اطلاعات آرایشگرهای سالن را مدیریت کنید.
                        </p>

                    </div>


                    <div class="shrink-0">

                        <a
                            href="{{ route('salon.barbers.create') }}"
                            class="btn btn-accent w-full sm:w-auto"
                        >
                            <span class="text-base">+</span>
                            افزودن آرایشگر
                        </a>

                    </div>

                </div>

            </div>


            {{-- ====================================================
                ERRORS
            ===================================================== --}}

            @if($errors->any())

                <div
                    class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-950/20"
                >

                    <div class="mb-2 text-xs font-black text-red-700 dark:text-red-400">
                        خطا در اطلاعات
                    </div>

                    <div class="space-y-1">

                        @foreach($errors->all() as $error)

                            <div class="text-[10px] font-bold leading-6 text-red-700 dark:text-red-400">
                                • {{ $error }}
                            </div>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- ====================================================
                BARBERS
            ===================================================== --}}

            @if($barbers->count())

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

                    @foreach($barbers as $barber)

                        <article
                            class="group overflow-hidden rounded-3xl border border-border bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-float dark:bg-primary-950"
                        >

                            {{-- ------------------------------------
                                IMAGE
                            ------------------------------------- --}}

                            <div class="relative aspect-[4/3] overflow-hidden bg-primary-50 dark:bg-primary-900">

                                @if($barber->image_path)

                                    <img
                                        src="{{ asset('storage/' . $barber->image_path) }}"
                                        alt="{{ $barber->name }}"
                                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                        loading="lazy"
                                    >

                                    {{-- Image gradient --}}

                                    <div
                                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-black/10"
                                    ></div>

                                @else

                                    <div class="flex h-full items-center justify-center">

                                        <div
                                            class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-3xl font-black text-content-muted shadow-soft dark:bg-primary-800"
                                        >
                                            {{ mb_substr($barber->name, 0, 1) }}
                                        </div>

                                    </div>

                                @endif


                                {{-- Status --}}

                                <div class="absolute inset-x-4 top-4 flex items-center justify-between gap-2">

                                    @if($barber->is_active)

                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-black/30 px-3 py-1.5 text-[10px] font-black text-white backdrop-blur-xl"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                                            فعال
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-black/30 px-3 py-1.5 text-[10px] font-black text-white backdrop-blur-xl"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                            غیرفعال
                                        </span>

                                    @endif

                                </div>


                                {{-- Name on image --}}

                                <div class="absolute inset-x-4 bottom-4">

                                    <h2 class="truncate text-lg font-black text-white drop-shadow-sm">
                                        {{ $barber->name }}
                                    </h2>

                                    @if($barber->specialty)

                                        <div class="mt-1 truncate text-[10px] font-medium text-white/80">
                                            {{ $barber->specialty }}
                                        </div>

                                    @endif

                                </div>

                            </div>


                            {{-- ------------------------------------
                                CONTENT
                            ------------------------------------- --}}

                            <div class="p-5">

                                @if($barber->bio)

                                    <p class="line-clamp-2 text-xs leading-7 text-content-muted">
                                        {{ $barber->bio }}
                                    </p>

                                @else

                                    <p class="text-xs leading-7 text-content-faint">
                                        توضیحی برای پروفایل این آرایشگر ثبت نشده است.
                                    </p>

                                @endif


                                {{-- Info --}}

                                <div class="mt-5 grid grid-cols-2 gap-2">

                                    @if($barber->phone)

                                        <div
                                            class="rounded-2xl border border-border bg-primary-50/60 px-3 py-3 dark:bg-primary-900/50"
                                        >

                                            <div class="text-[9px] font-bold text-content-faint">
                                                شماره تماس
                                            </div>

                                            <div
                                                class="mt-1.5 truncate text-[10px] font-black text-content"
                                                dir="ltr"
                                            >
                                                {{ $barber->phone }}
                                            </div>

                                        </div>

                                    @else

                                        <div
                                            class="rounded-2xl border border-border bg-primary-50/60 px-3 py-3 dark:bg-primary-900/50"
                                        >

                                            <div class="text-[9px] font-bold text-content-faint">
                                                شماره تماس
                                            </div>

                                            <div class="mt-1.5 text-[10px] font-bold text-content-muted">
                                                ثبت نشده
                                            </div>

                                        </div>

                                    @endif


                                    <div
                                        class="rounded-2xl border border-border bg-primary-50/60 px-3 py-3 dark:bg-primary-900/50"
                                    >

                                        <div class="text-[9px] font-bold text-content-faint">
                                            وضعیت پروفایل
                                        </div>

                                        <div
                                            class="mt-1.5 text-[10px] font-black
                                            {{ $barber->is_active
                                                ? 'text-green-600'
                                                : 'text-red-500' }}"
                                        >
                                            {{ $barber->is_active ? 'فعال' : 'غیرفعال' }}
                                        </div>

                                    </div>

                                </div>


                                {{-- Actions --}}

                                <div class="mt-5 flex items-center justify-between border-t border-border pt-4">

                                    <div>

                                        <div class="text-[9px] font-bold text-content-faint">
                                            پروفایل آرایشگر
                                        </div>

                                        <div class="mt-1 text-[10px] font-black text-content-muted">
                                            مدیریت اطلاعات
                                        </div>

                                    </div>


                                    <a
                                        href="{{ route('salon.barbers.edit', $barber) }}"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        ویرایش
                                    </a>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- =================================================
                    PAGINATION
                ================================================== --}}

                @if($barbers->hasPages())

                    <div class="mt-7">
                        {{ $barbers->links() }}
                    </div>

                @endif


            @else

                {{-- =================================================
                    EMPTY STATE
                ================================================== --}}

                <section
                    class="relative overflow-hidden rounded-3xl border border-border bg-white p-8 text-center shadow-sm sm:p-12 dark:bg-primary-950"
                >

                    {{-- Decorative background --}}

                    <div
                        class="pointer-events-none absolute -left-16 -top-16 h-40 w-40 rounded-full bg-accent-100/50 blur-3xl dark:bg-accent-900/20"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-16 -right-16 h-40 w-40 rounded-full bg-primary-100/70 blur-3xl dark:bg-primary-900/30"
                    ></div>


                    <div class="relative">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-accent-50 text-accent-600 dark:bg-accent-900/20"
                        >

                            <svg
                                width="28"
                                height="28"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >

                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3.5"
                                />

                                <path
                                    d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6"
                                    stroke-linecap="round"
                                />

                            </svg>

                        </div>


                        <div class="mx-auto mt-5 max-w-md">

                            <h2 class="text-lg font-black text-content">
                                هنوز آرایشگری ثبت نشده
                            </h2>

                            <p class="mt-2 text-xs leading-7 text-content-muted">
                                اولین آرایشگر سالن را اضافه کنید تا بتوانید پروفایل،
                                خدمات و وضعیت فعالیت او را مدیریت کنید.
                            </p>

                        </div>


                        <div class="mt-6">

                            <a
                                href="{{ route('salon.barbers.create') }}"
                                class="btn btn-accent"
                            >
                                <span class="text-base">+</span>
                                افزودن اولین آرایشگر
                            </a>

                        </div>

                    </div>

                </section>

            @endif

        </div>

    </div>

@endsection
