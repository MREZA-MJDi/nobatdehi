@extends('layouts.admin')

@section('title', 'داشبورد')

@section('content')

    <div class="mx-auto max-w-[1400px] px-4 py-5 md:px-6 md:py-7">


        {{-- Header --}}

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <div class="mb-1 text-xs font-bold text-accent-600">
                    Super Admin
                </div>

                <h1 class="page-title">
                    داشبورد مدیریت
                </h1>

                <p class="page-subtitle">
                    وضعیت کلی پلتفرم و سالن‌های ثبت‌شده
                </p>

            </div>


            <a
                href="{{ route('admin.salons.create') }}"
                class="btn btn-accent w-full sm:w-auto"
            >

                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 5v14M5 12h14" />
                </svg>

                ایجاد سالن جدید

            </a>

        </div>



        {{-- Stats --}}

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="card p-4 md:p-5">

                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 21V8l8-5 8 5v13" />
                        <path d="M8 21v-6h8v6" />
                    </svg>

                </div>

                <div class="text-2xl font-black text-content">
                    {{ number_format($salonCount) }}
                </div>

                <div class="mt-1 text-xs text-content-muted">
                    کل سالن‌ها
                </div>

            </div>


            <div class="card p-4 md:p-5">

                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-700">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="m5 12 4 4L19 6" />
                    </svg>

                </div>

                <div class="text-2xl font-black text-content">
                    {{ number_format($activeSalonCount) }}
                </div>

                <div class="mt-1 text-xs text-content-muted">
                    سالن فعال
                </div>

            </div>


            <div class="card p-4 md:p-5">

                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 text-primary-700">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="8" r="3.5" />
                        <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                    </svg>

                </div>

                <div class="text-2xl font-black text-content">
                    {{ number_format($barberCount) }}
                </div>

                <div class="mt-1 text-xs text-content-muted">
                    آرایشگرها
                </div>

            </div>


            <div class="card p-4 md:p-5">

                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>

                </div>

                <div class="text-2xl font-black text-content">
                    {{ number_format($customerCount) }}
                </div>

                <div class="mt-1 text-xs text-content-muted">
                    مشتری‌ها
                </div>

            </div>

        </div>



        {{-- Main Action --}}

        <div class="mt-5 grid gap-4 lg:grid-cols-2">

            <a
                href="{{ route('admin.salons.index') }}"
                class="group rounded-2xl border border-border bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-accent-200 hover:shadow-card"
            >

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="mb-2 text-[10px] font-bold uppercase tracking-wider text-accent-600">
                            Salon Management
                        </div>

                        <h2 class="text-lg font-black text-content">
                            مدیریت سالن‌ها
                        </h2>

                        <p class="mt-1 text-xs leading-6 text-content-muted">
                            ایجاد، ویرایش، فعال‌سازی و مدیریت اطلاعات سالن‌ها.
                        </p>

                    </div>


                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700 transition group-hover:scale-105">

                    <svg
                        width="19"
                        height="19"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 21V8l8-5 8 5v13" />
                        <path d="M8 21v-6h8v6" />
                    </svg>

                </span>

                </div>


                <div class="mt-5 flex items-center gap-2 text-xs font-bold text-accent-700">

                    مشاهده سالن‌ها

                    <span class="transition group-hover:-translate-x-1">
                    ←
                </span>

                </div>

            </a>


            <a
                href="{{ route('admin.salons.create') }}"
                class="group rounded-2xl border border-primary-200 bg-primary-950 p-5 text-white shadow-soft transition hover:-translate-y-1 hover:shadow-card"
            >

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="mb-2 text-[10px] font-bold uppercase tracking-wider text-accent-300">
                            New Salon
                        </div>

                        <h2 class="text-lg font-black">
                            ساخت سالن جدید
                        </h2>

                        <p class="mt-1 text-xs leading-6 text-primary-300">
                            اطلاعات، برندینگ، لوگو، Cover و موقعیت سالن را تعریف کنید.
                        </p>

                    </div>


                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent-300">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 5v14M5 12h14" />
                    </svg>

                </span>

                </div>


                <div class="mt-5 text-xs font-bold text-white">
                    شروع ساخت سالن ←
                </div>

            </a>

        </div>

    </div>

@endsection
