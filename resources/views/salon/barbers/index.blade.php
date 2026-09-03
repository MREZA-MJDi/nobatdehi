@extends('layouts.salon')

@section('title', 'آرایشگرهای سالن')

@section('content')

    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                >
                    ← داشبورد سالن
                </a>

                <div class="text-[10px] font-black tracking-wider text-accent-600">
                    BARBERS
                </div>

                <h1 class="mt-2 text-2xl font-black text-content">
                    آرایشگرهای {{ $salon->name }}
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    پروفایل آرایشگرهای سالن را مدیریت کنید.
                </p>

            </div>


            <a
                href="{{ route('salon.barbers.create') }}"
                class="btn btn-accent"
            >
                + افزودن آرایشگر
            </a>

        </div>


        {{-- Errors --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-red-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        @if($barbers->count())

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                @foreach($barbers as $barber)

                    <article class="card overflow-hidden">

                        <div class="aspect-[4/3] overflow-hidden bg-primary-50">

                            @if($barber->image_path)

                                <img
                                    src="{{ asset('storage/' . $barber->image_path) }}"
                                    alt="{{ $barber->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >

                            @else

                                <div class="flex h-full items-center justify-center">

                                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white text-2xl font-black text-content-muted shadow-soft">
                                        {{ mb_substr($barber->name, 0, 1) }}
                                    </div>

                                </div>

                            @endif

                        </div>


                        <div class="p-5">

                            <div class="flex items-start justify-between gap-3">

                                <div class="min-w-0">

                                    <h2 class="truncate text-base font-black text-content">
                                        {{ $barber->name }}
                                    </h2>

                                    @if($barber->specialty)

                                        <div class="mt-1 truncate text-[10px] text-content-muted">
                                            {{ $barber->specialty }}
                                        </div>

                                    @endif

                                </div>


                                @if($barber->is_active)

                                    <span class="badge badge-success shrink-0">
                                        فعال
                                    </span>

                                @else

                                    <span class="badge shrink-0">
                                        غیرفعال
                                    </span>

                                @endif

                            </div>


                            @if($barber->bio)

                                <p class="mt-4 line-clamp-3 text-xs leading-6 text-content-muted">
                                    {{ $barber->bio }}
                                </p>

                            @endif


                            @if($barber->phone)

                                <div
                                    class="mt-4 text-xs font-bold text-content"
                                    dir="ltr"
                                >
                                    {{ $barber->phone }}
                                </div>

                            @endif


                            <div class="mt-5 flex items-center justify-between border-t border-border pt-4">

                                <span class="text-[10px] text-content-faint">
                                    پروفایل آرایشگر
                                </span>

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


            @if($barbers->hasPages())

                <div class="mt-6">
                    {{ $barbers->links() }}
                </div>

            @endif

        @else

            <section class="card p-8 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                    <svg
                        width="24"
                        height="24"
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

                        <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                    </svg>

                </div>


                <h2 class="mt-4 text-base font-black text-content">
                    هنوز آرایشگری ثبت نشده
                </h2>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    اولین آرایشگر سالن را اضافه کنید.
                </p>

                <div class="mt-5">

                    <a
                        href="{{ route('salon.barbers.create') }}"
                        class="btn btn-accent"
                    >
                        افزودن اولین آرایشگر
                    </a>

                </div>

            </section>

        @endif

    </div>

@endsection
