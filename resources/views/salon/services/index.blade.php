@extends('layouts.salon')

@section('title', 'خدمات سالن')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div class="mx-auto w-full max-w-7xl">

            {{-- Header --}}
            <div class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                            ✦
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                                SERVICES
                            </div>

                            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                خدمات {{ $salon->name }}
                            </h1>
                        </div>
                    </div>

                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                        خدماتی که مشتری هنگام رزرو می‌تواند انتخاب کند.
                    </p>
                </div>

                <a
                    href="{{ route('salon.services.create') }}"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800"
                >
                    <span class="text-lg">+</span>
                    ایجاد خدمت
                </a>

            </div>


            @if($services->count())

                {{-- Stats --}}
                <div class="mb-6 grid gap-3 sm:grid-cols-3">

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                            TOTAL SERVICES
                        </div>

                        <div class="mt-2 text-2xl font-black text-slate-950">
                            {{ $services->total() }}
                        </div>

                        <div class="mt-1 text-xs text-slate-400">
                            مجموع خدمات ثبت‌شده
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm">
                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">
                            ACTIVE
                        </div>

                        <div class="mt-2 text-2xl font-black text-emerald-800">
                            {{ $services->where('is_active', true)->count() }}
                        </div>

                        <div class="mt-1 text-xs text-emerald-700/60">
                            خدمات فعال
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                            PAGE
                        </div>

                        <div class="mt-2 text-2xl font-black text-slate-950">
                            {{ $services->currentPage() }}
                        </div>

                        <div class="mt-1 text-xs text-slate-400">
                            از {{ $services->lastPage() }} صفحه
                        </div>
                    </div>

                </div>


                {{-- Services --}}
                {{-- Services --}}
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

                    @foreach($services as $service)

                        <article
                            class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg"
                        >

                            {{-- Thumbnail --}}
                            <div class="relative overflow-hidden bg-slate-100">

                                <div class="aspect-[16/9] w-full">

                                    @if($service->image_path)

                                        <img
                                            src="{{ Storage::url($service->image_path) }}"
                                            alt="{{ $service->name }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                        >

                                    @else

                                        <div class="flex h-full w-full items-center justify-center bg-slate-950">

                                            <div class="flex flex-col items-center justify-center">

                                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-xl text-white/70">
                                                    ✦
                                                </div>

                                                <div class="mt-3 text-[9px] font-black uppercase tracking-[0.22em] text-white/30">
                                                    NO IMAGE
                                                </div>

                                            </div>

                                        </div>

                                    @endif

                                </div>


                                {{-- Status --}}
                                <div class="absolute right-3 top-3">

                                    @if($service->is_active)

                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/90 px-3 py-1.5 text-[10px] font-black text-emerald-700 shadow-sm backdrop-blur">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                            فعال

                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/90 px-3 py-1.5 text-[10px] font-black text-slate-500 shadow-sm backdrop-blur">

                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>

                            غیرفعال

                        </span>

                                    @endif

                                </div>

                            </div>


                            {{-- Content --}}
                            <div class="p-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div class="min-w-0 flex-1">

                                        <div class="mb-3 flex items-center gap-2">

                                            <div class="h-px w-7 bg-slate-200"></div>

                                            <div class="text-[9px] font-black uppercase tracking-[0.18em] text-slate-300">
                                                SERVICE
                                            </div>

                                        </div>

                                        <h2 class="truncate text-base font-black text-slate-950">
                                            {{ $service->name }}
                                        </h2>

                                    </div>

                                </div>


                                {{-- Meta --}}
                                <div class="mt-4 flex flex-wrap gap-2">

                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                        {{ $service->duration_minutes }}
                        دقیقه
                    </span>

                                </div>


                                {{-- Description --}}
                                @if($service->description)

                                    <p class="mt-4 min-h-[48px] text-xs leading-7 text-slate-500">
                                        {{ $service->description }}
                                    </p>

                                @else

                                    <p class="mt-4 min-h-[48px] text-xs leading-7 text-slate-300">
                                        توضیحی برای این خدمت ثبت نشده است.
                                    </p>

                                @endif

                            </div>


                            {{-- Footer --}}
                            <div class="border-t border-slate-100 bg-slate-50/70 p-5">

                                <div class="flex items-end justify-between gap-4">

                                    <div>

                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            PRICE
                                        </div>

                                        <div class="mt-1 text-lg font-black text-slate-950">

                                            {{ number_format($service->price) }}

                                            <span class="text-xs font-bold text-slate-400">
                                تومان
                            </span>

                                        </div>

                                    </div>


                                    <a
                                        href="{{ route('salon.services.edit', $service) }}"
                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-950 hover:text-white"
                                    >
                                        ویرایش
                                    </a>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

                {{-- Pagination --}}
                @if($services->hasPages())

                    <div class="mt-7">
                        {{ $services->links() }}
                    </div>

                @endif

            @else

                {{-- Empty State --}}
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <div class="relative px-6 py-14 text-center sm:px-10">

                        <div class="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-accent-100/40 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-slate-100 blur-3xl"></div>

                        <div class="relative">

                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-lg">
                                <svg
                                    width="25"
                                    height="25"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M12 5v14" />
                                    <path d="M5 12h14" />
                                </svg>
                            </div>

                            <div class="mt-5 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                                NO SERVICES YET
                            </div>

                            <h2 class="mt-2 text-xl font-black text-slate-950">
                                هنوز خدمتی ثبت نشده
                            </h2>

                            <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-500">
                                اولین خدمت سالن را اضافه کنید تا مشتری‌ها بتوانند آن را هنگام رزرو انتخاب کنند.
                            </p>

                            <div class="mt-6">
                                <a
                                    href="{{ route('salon.services.create') }}"
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800"
                                >
                                    <span class="text-lg">+</span>
                                    ایجاد اولین خدمت
                                </a>
                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>

@endsection
