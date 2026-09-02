@extends('layouts.app')

@section('title', 'خدمات سالن')

@section('content')

    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    SERVICES
                </div>

                <h1 class="text-2xl font-black text-content">
                    خدمات {{ $salon->name }}
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    خدماتی که مشتری هنگام رزرو می‌تواند انتخاب کند.
                </p>

            </div>

            <a
                href="{{ route('salon.services.create') }}"
                class="btn btn-accent"
            >
                + ایجاد خدمت
            </a>

        </div>


        @if($services->count())

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                @foreach($services as $service)

                    <article class="card p-5">

                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0">

                                <h2 class="truncate text-sm font-black text-content">
                                    {{ $service->name }}
                                </h2>

                                <div class="mt-2 flex flex-wrap gap-2">

                                    <span class="badge badge-neutral">
                                        {{ $service->duration_minutes }} دقیقه
                                    </span>

                                    @if($service->is_active)

                                        <span class="badge badge-success">
                                            فعال
                                        </span>

                                    @else

                                        <span class="badge">
                                            غیرفعال
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        @if($service->description)

                            <p class="mt-4 text-xs leading-6 text-content-muted">
                                {{ $service->description }}
                            </p>

                        @endif


                        <div class="mt-5 flex items-center justify-between border-t border-border pt-4">

                            <div>

                                <div class="text-[10px] text-content-muted">
                                    قیمت
                                </div>

                                <div class="mt-1 text-sm font-black text-content">
                                    {{ number_format($service->price) }}
                                    تومان
                                </div>

                            </div>


                            <a
                                href="{{ route('salon.services.edit', $service) }}"
                                class="btn btn-secondary btn-sm"
                            >
                                ویرایش
                            </a>

                        </div>

                    </article>

                @endforeach

            </div>


            @if($services->hasPages())

                <div class="mt-6">
                    {{ $services->links() }}
                </div>

            @endif

        @else

            <div class="card p-8 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">

                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>

                </div>

                <h2 class="mt-4 text-base font-black text-content">
                    هنوز خدمتی ثبت نشده
                </h2>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    اولین خدمت سالن را اضافه کنید تا مشتری‌ها بتوانند آن را هنگام رزرو انتخاب کنند.
                </p>

                <div class="mt-5">
                    <a
                        href="{{ route('salon.services.create') }}"
                        class="btn btn-accent"
                    >
                        ایجاد اولین خدمت
                    </a>
                </div>

            </div>

        @endif

    </div>

@endsection
