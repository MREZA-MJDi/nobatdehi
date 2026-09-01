@extends('layouts.admin')

@section('title', $salon->name)

@section('content')

    <div class="mx-auto max-w-[1400px] px-4 py-5 pb-24 md:px-6 md:py-7">


        {{-- Header --}}

        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">

                <a
                    href="{{ route('admin.salons.index') }}"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-border bg-white text-content-muted transition hover:bg-primary-50 hover:text-content"
                    aria-label="بازگشت"
                >
                    ←
                </a>

                <div>

                    <div class="mb-1 text-[10px] font-bold text-accent-600">
                        SALON MANAGEMENT
                    </div>

                    <h1 class="text-xl font-black text-content md:text-2xl">
                        {{ $salon->name }}
                    </h1>

                </div>

            </div>


            <div class="flex gap-2">

                <a
                    href="{{ route('salons.show', $salon->code) }}"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-secondary btn-sm"
                >
                    مشاهده عمومی
                </a>

                <a
                    href="{{ route('admin.salons.edit', $salon) }}"
                    class="btn btn-primary btn-sm"
                >
                    ویرایش
                </a>

            </div>

        </div>



        {{-- Hero --}}

        <section class="card overflow-hidden">


            {{-- Cover --}}

            <div class="relative h-52 overflow-hidden bg-primary-950 md:h-64">

                @if($salon->cover_url)

                    <img
                        src="{{ $salon->cover_url }}"
                        alt="{{ $salon->name }}"
                        class="h-full w-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-primary-950/70 via-primary-950/10 to-transparent"></div>

                @else

                    <div
                        class="absolute inset-0"
                        style="
                        background:
                            radial-gradient(circle at 75% 20%, rgba(103,87,232,.35), transparent 20rem),
                            radial-gradient(circle at 20% 80%, rgba(55,184,200,.18), transparent 18rem),
                            linear-gradient(135deg,#171a24,#2d323e);
                    "
                    ></div>

                @endif


                <div class="absolute right-5 top-5">

                    @if($salon->is_active)

                        <span class="badge bg-green-100 text-green-700">
                        ● فعال
                    </span>

                    @else

                        <span class="badge bg-red-100 text-red-700">
                        ● غیرفعال
                    </span>

                    @endif

                </div>

            </div>



            {{-- Profile --}}

            <div class="relative px-5 pb-6 md:px-7">

                <div class="-mt-12 flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-primary-900 text-2xl font-black text-white shadow-card">

                    @if($salon->logo_url)

                        <img
                            src="{{ $salon->logo_url }}"
                            alt="{{ $salon->name }}"
                            class="h-full w-full object-cover"
                        >

                    @else

                        {{ mb_substr($salon->name, 0, 1) }}

                    @endif

                </div>


                <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                    <div>

                        <h2 class="text-2xl font-black text-content">
                            {{ $salon->name }}
                        </h2>

                        <p class="mt-1 text-xs text-content-muted">
                            {{ $salon->description ?: 'بدون توضیحات' }}
                        </p>


                        <div class="mt-3 flex flex-wrap gap-2">

                            <code
                                class="rounded-lg bg-primary-50 px-2.5 py-1.5 text-[10px] font-bold text-primary-700"
                                dir="ltr"
                            >
                                {{ $salon->code }}
                            </code>


                            @if($salon->city)

                                <span class="badge badge-neutral">
                                📍 {{ $salon->city }}
                            </span>

                            @endif


                            @if($salon->phone)

                                <span class="badge badge-neutral">
                                {{ $salon->phone }}
                            </span>

                            @endif

                        </div>

                    </div>


                    @if($salon->manager?->user)

                        <div class="flex items-center gap-3 rounded-2xl border border-border bg-primary-50 p-3 lg:min-w-64">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-900 text-sm font-black text-white">
                                {{ mb_substr($salon->manager->user->name, 0, 1) }}
                            </div>

                            <div>

                                <div class="text-[9px] font-bold text-content-muted">
                                    مدیر سالن
                                </div>

                                <div class="mt-0.5 text-xs font-black">
                                    {{ $salon->manager->user->name }}
                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </section>



        {{-- ============================================================
            QUICK MANAGEMENT
        ============================================================= --}}

        <section class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">

            <div class="card p-4">

                <div class="text-[10px] font-bold text-content-muted">
                    آرایشگرها
                </div>

                <div class="mt-2 text-2xl font-black">
                    {{ $salon->barbers->count() }}
                </div>

            </div>


            <div class="card p-4">

                <div class="text-[10px] font-bold text-content-muted">
                    موقعیت
                </div>

                <div class="mt-2 text-sm font-black">

                    @if($salon->latitude && $salon->longitude)
                        ثبت شده ✓
                    @else
                        ثبت نشده
                    @endif

                </div>

            </div>


            <div class="card p-4">

                <div class="text-[10px] font-bold text-content-muted">
                    لوگو
                </div>

                <div class="mt-2 text-sm font-black">
                    {{ $salon->logo_path ? 'ثبت شده ✓' : 'ثبت نشده' }}
                </div>

            </div>


            <div class="card p-4">

                <div class="text-[10px] font-bold text-content-muted">
                    QR
                </div>

                <div class="mt-2 text-sm font-black">
                    {{ $salon->qr_code_path ? 'موجود ✓' : 'در دسترس نیست' }}
                </div>

            </div>

        </section>



        {{-- ============================================================
            MANAGEMENT GRID
        ============================================================= --}}

        <section class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_350px]">


            {{-- Left --}}

            <div class="space-y-5">


                {{-- Barbers --}}

                <section class="card p-5">

                    <div class="mb-5 flex items-center justify-between gap-3">

                        <div>

                            <h2 class="section-title">
                                آرایشگران سالن
                            </h2>

                            <p class="page-subtitle">
                                آرایشگران متصل به این سالن
                            </p>

                        </div>


                        <span class="badge badge-neutral">
                        {{ $salon->barbers->count() }} نفر
                    </span>

                    </div>


                    @if($salon->barbers->count())

                        <div class="grid gap-2 sm:grid-cols-2">

                            @foreach($salon->barbers as $barber)

                                <div class="flex items-center gap-3 rounded-xl border border-border bg-primary-50 p-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-900 text-xs font-black text-white">
                                        {{ mb_substr($barber->user?->name ?? '؟', 0, 1) }}
                                    </div>

                                    <div class="min-w-0">

                                        <div class="truncate text-xs font-black">
                                            {{ $barber->user?->name ?? 'بدون نام' }}
                                        </div>

                                        <div class="mt-0.5 truncate text-[10px] text-content-muted">
                                            {{ $barber->specialty ?: 'بدون تخصص ثبت‌شده' }}
                                        </div>

                                    </div>


                                    @if($salon->manager_barber_id == $barber->id)

                                        <span class="mr-auto badge badge-accent">
                                        مدیر
                                    </span>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="empty-state">

                            <div>

                                <div class="empty-icon mx-auto">
                                    +
                                </div>

                                <p class="mt-3 text-xs font-bold">
                                    هنوز آرایشگری به سالن متصل نشده.
                                </p>

                            </div>

                        </div>

                    @endif

                </section>



                {{-- Address --}}

                <section class="card overflow-hidden">

                    <div class="p-5">

                        <div class="mb-4">

                            <h2 class="section-title">
                                آدرس و موقعیت
                            </h2>

                        </div>


                        <div class="rounded-2xl bg-primary-50 p-4">

                            <div class="flex items-start gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>

                                </div>


                                <div>

                                    <div class="text-xs font-black">
                                        {{ $salon->city ?: 'شهر ثبت نشده' }}
                                        @if($salon->district)
                                            ، {{ $salon->district }}
                                        @endif
                                    </div>

                                    <div class="mt-1 text-xs leading-7 text-content-muted">
                                        {{ $salon->address ?: 'آدرس ثبت نشده' }}
                                    </div>

                                    @if($salon->latitude && $salon->longitude)

                                        <div class="mt-2 font-mono text-[9px] text-content-faint" dir="ltr">
                                            {{ $salon->latitude }} , {{ $salon->longitude }}
                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="h-64 bg-[#e8ebf1]">

                        <div
                            class="relative h-full"
                            style="
                            background-image:
                                linear-gradient(rgba(255,255,255,.55) 1px, transparent 1px),
                                linear-gradient(90deg, rgba(255,255,255,.55) 1px, transparent 1px);
                            background-size: 36px 36px;
                        "
                        >

                            <div class="absolute left-[15%] top-[42%] h-3 w-[80%] rotate-[-10deg] rounded-full bg-white"></div>

                            <div class="absolute left-[45%] top-[5%] h-[90%] w-3 rotate-[14deg] rounded-full bg-white"></div>


                            @if($salon->latitude && $salon->longitude)

                                <div class="absolute right-[42%] top-[38%] flex h-12 w-12 items-center justify-center rounded-2xl border-4 border-white bg-accent-600 text-white shadow-iris">

                                    📍

                                </div>

                            @else

                                <div class="absolute inset-0 flex items-center justify-center">

                                <span class="rounded-xl bg-white/90 px-4 py-3 text-xs font-bold text-content-muted shadow-soft">
                                    موقعیت روی نقشه ثبت نشده
                                </span>

                                </div>

                            @endif

                        </div>

                    </div>

                </section>

            </div>



            {{-- Right --}}

            <aside class="space-y-4">


                {{-- Public Link --}}

                <section class="card p-5">

                    <div class="mb-4">

                        <h2 class="text-sm font-black">
                            صفحه عمومی سالن
                        </h2>

                        <p class="mt-1 text-[10px] text-content-muted">
                            مقصد QR و لینک عمومی سالن
                        </p>

                    </div>


                    <div class="rounded-xl bg-primary-50 p-3">

                        <div class="text-[9px] font-bold text-content-muted">
                            PUBLIC URL
                        </div>

                        <div
                            class="mt-2 break-all font-mono text-[10px] font-bold text-content"
                            dir="ltr"
                        >
                            {{ $salon->public_url }}
                        </div>

                    </div>


                    <a
                        href="{{ $salon->public_url }}"
                        target="_blank"
                        rel="noopener"
                        class="btn btn-accent mt-3 w-full"
                    >
                        باز کردن صفحه عمومی
                    </a>

                </section>



                {{-- QR --}}

                <section class="card p-5">

                    <div class="mb-4">

                        <h2 class="text-sm font-black">
                            QR سالن
                        </h2>

                        <p class="mt-1 text-[10px] text-content-muted">
                            QR فعلی سالن از سیستم موجود
                        </p>

                    </div>


                    @if($salon->qr_code_url)

                        <div class="rounded-2xl border border-border bg-white p-4">

                            <img
                                src="{{ $salon->qr_code_url }}"
                                alt="QR {{ $salon->name }}"
                                class="mx-auto h-48 w-48 object-contain"
                            >

                        </div>

                        <a
                            href="{{ $salon->qr_code_url }}"
                            download
                            class="btn btn-secondary mt-3 w-full"
                        >
                            دریافت QR
                        </a>

                    @else

                        <div class="rounded-2xl bg-primary-50 p-6 text-center">

                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">
                                QR
                            </div>

                            <p class="mt-3 text-[10px] leading-6 text-content-muted">
                                مسیر QR فعلی از سیستم BarberBook هنوز برای این سالن ثبت نشده است.
                            </p>

                        </div>

                    @endif

                </section>



                {{-- Branding --}}

                <section class="card p-5">

                    <h2 class="text-sm font-black">
                        Branding
                    </h2>


                    <div class="mt-4 space-y-3">

                        <div class="flex items-center justify-between">

                        <span class="text-[10px] text-content-muted">
                            Primary
                        </span>

                            <span class="flex items-center gap-2">

                            <span
                                class="h-6 w-6 rounded-lg border border-border"
                                style="background: {{ $salon->primary_color ?: '#6757E8' }}"
                            ></span>

                            <code class="text-[9px]" dir="ltr">
                                {{ $salon->primary_color ?: '#6757E8' }}
                            </code>

                        </span>

                        </div>


                        <div class="flex items-center justify-between">

                        <span class="text-[10px] text-content-muted">
                            Secondary
                        </span>

                            <span class="flex items-center gap-2">

                            <span
                                class="h-6 w-6 rounded-lg border border-border"
                                style="background: {{ $salon->secondary_color ?: '#37B8C8' }}"
                            ></span>

                            <code class="text-[9px]" dir="ltr">
                                {{ $salon->secondary_color ?: '#37B8C8' }}
                            </code>

                        </span>

                        </div>

                    </div>


                    <a
                        href="{{ route('admin.salons.edit', $salon) }}"
                        class="btn btn-secondary mt-4 w-full"
                    >
                        ویرایش Branding
                    </a>

                </section>



                {{-- Danger --}}

                <section class="rounded-2xl border border-red-100 bg-red-50 p-5">

                    <h2 class="text-xs font-black text-red-800">
                        حذف سالن
                    </h2>

                    <p class="mt-1 text-[10px] leading-6 text-red-600">
                        این عملیات سالن را Soft Delete می‌کند.
                    </p>


                    <form
                        action="{{ route('admin.salons.destroy', $salon) }}"
                        method="POST"
                        class="mt-3"
                        onsubmit="return confirm('آیا از حذف این سالن مطمئن هستید؟')"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="btn w-full bg-red-600 text-white hover:bg-red-700"
                        >
                            حذف سالن
                        </button>

                    </form>

                </section>

            </aside>

        </section>

    </div>

@endsection
