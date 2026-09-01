@extends('layouts.admin')

@section('title', 'ویرایش ' . $salon->name)

@section('content')

    <div
        x-data="{
        primaryColor: '{{ old('primary_color', $salon->primary_color ?: '#6757E8') }}',
        secondaryColor: '{{ old('secondary_color', $salon->secondary_color ?: '#37B8C8') }}',


    logoPreview: '{{ $salon->logo_url ?: '' }}',
    coverPreview: '{{ $salon->cover_url ?: '' }}',

    previewImage(event, type) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.onload = (e) => {
            if (type === 'logo') {
                this.logoPreview = e.target.result;
            }

            if (type === 'cover') {
                this.coverPreview = e.target.result;
            }
        };

        reader.readAsDataURL(file);
    }
}"
        class="mx-auto max-w-[1400px] px-4 py-5 pb-24 md:px-6 md:py-7"
        ```

    >

        ```
        {{-- ============================================================
            PAGE HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('admin.salons.show', $salon) }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به سالن
            </a>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="mb-1 text-[10px] font-bold text-accent-600">
                        SALON SETTINGS
                    </div>

                    <h1 class="page-title">
                        ویرایش سالن
                    </h1>

                    <p class="page-subtitle">
                        اطلاعات، برندینگ، مدیر و موقعیت سالن را مدیریت کنید.
                    </p>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    @if($salon->is_active)

                        <span class="badge badge-success">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                    فعال
                </span>

                    @else

                        <span class="badge badge-danger">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    غیرفعال
                </span>

                    @endif


                    <code
                        class="rounded-xl bg-primary-100 px-3 py-2 text-[10px] font-bold text-primary-700"
                        dir="ltr"
                    >
                        {{ $salon->code }}
                    </code>

                </div>

            </div>

        </div>



        {{-- ============================================================
            VALIDATION ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        !
                    </div>

                    <div>

                        <div class="text-xs font-black text-red-800">
                            اطلاعات نیاز به بررسی دارند
                        </div>

                        <ul class="mt-2 space-y-1 text-[10px] leading-6 text-red-700">

                            @foreach($errors->all() as $error)

                                <li>
                                    • {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>

        @endif



        {{-- ============================================================
            FORM
        ============================================================= --}}

        <form
            action="{{ route('admin.salons.update', $salon) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            @method('PUT')


            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">


                {{-- ====================================================
                    MAIN CONTENT
                ===================================================== --}}

                <div class="space-y-5">


                    {{-- ==================================================
                        BASIC INFORMATION
                    =================================================== --}}

                    <section class="card p-5 md:p-6">

                        <div class="mb-6">

                            <div class="mb-1 text-[10px] font-bold text-accent-600">
                                01
                            </div>

                            <h2 class="section-title">
                                اطلاعات پایه
                            </h2>

                            <p class="page-subtitle">
                                اطلاعات اصلی این سالن را مدیریت کنید.
                            </p>

                        </div>


                        <div class="grid gap-5 sm:grid-cols-2">


                            {{-- Salon Name --}}

                            <div class="form-group sm:col-span-2">

                                <label
                                    for="name"
                                    class="form-label"
                                >
                                    نام سالن
                                    <span class="text-danger-600">*</span>
                                </label>

                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $salon->name) }}"
                                    class="form-control"
                                    placeholder="مثلاً سالن نوبان"
                                    maxlength="120"
                                    required
                                >

                                @error('name')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- Phone --}}

                            <div class="form-group">

                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    شماره تماس
                                </label>

                                <input
                                    id="phone"
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $salon->phone) }}"
                                    class="form-control"
                                    placeholder="021..."
                                    dir="ltr"
                                    maxlength="30"
                                >

                                @error('phone')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- Email --}}

                            <div class="form-group">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    ایمیل
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $salon->email) }}"
                                    class="form-control"
                                    placeholder="salon@example.com"
                                    dir="ltr"
                                    maxlength="190"
                                >

                                @error('email')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- Description --}}

                            <div class="form-group sm:col-span-2">

                                <label
                                    for="description"
                                    class="form-label"
                                >
                                    توضیحات سالن
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    class="form-control"
                                    maxlength="5000"
                                    placeholder="توضیح کوتاهی درباره سالن..."
                                >{{ old('description', $salon->description) }}</textarea>

                                <div class="form-help">
                                    این متن در صفحه عمومی سالن نمایش داده می‌شود.
                                </div>

                                @error('description')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- Manager Barber --}}

                            <div class="form-group sm:col-span-2">

                                <label
                                    for="manager_barber_id"
                                    class="form-label"
                                >
                                    مدیر سالن
                                </label>

                                <select
                                    id="manager_barber_id"
                                    name="manager_barber_id"
                                    class="form-control"
                                >

                                    <option value="">
                                        بدون مدیر
                                    </option>

                                    @foreach($barbers as $barber)

                                        <option
                                            value="{{ $barber->id }}"
                                            @selected(
                                            old(
                                        'manager_barber_id',
                                        $salon->manager_barber_id
                                        ) == $barber->id
                                        )
                                        >
                                        {{ $barber->user?->name ?? 'بدون نام' }}

                                        @if($barber->user?->email)
                                            — {{ $barber->user->email }}
                                            @endif
                                            </option>

                                            @endforeach

                                </select>

                                <div class="form-help">
                                    این آرایشگر مدیر این سالن خواهد بود و بعداً می‌تواند پنل مدیریتی سالن را داشته باشد.
                                </div>

                                @error('manager_barber_id')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>

                    </section>



                    {{-- ==================================================
                        BRANDING
                    =================================================== --}}

                    <section class="card p-5 md:p-6">

                        <div class="mb-6">

                            <div class="mb-1 text-[10px] font-bold text-accent-600">
                                02
                            </div>

                            <h2 class="section-title">
                                برندینگ سالن
                            </h2>

                            <p class="page-subtitle">
                                لوگو، تصویر اصلی و رنگ‌های اختصاصی سالن.
                            </p>

                        </div>


                        <div class="grid gap-5 md:grid-cols-2">


                            {{-- ==================================================
                                LOGO
                            =================================================== --}}

                            <div class="form-group">

                                <label class="form-label">
                                    لوگو
                                </label>


                                <div class="relative overflow-hidden rounded-2xl border border-border bg-primary-50">

                                    <div class="flex min-h-56 items-center justify-center">

                                        <template x-if="logoPreview">

                                            <img
                                                :src="logoPreview"
                                                alt="Logo preview"
                                                class="max-h-48 max-w-full object-contain p-8"
                                            >

                                        </template>


                                        <template x-if="!logoPreview">

                                            <div class="text-center">

                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">

                                                    <svg
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                    >
                                                        <rect x="3" y="3" width="18" height="18" rx="3" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <path d="m21 15-5-5L5 21" />
                                                    </svg>

                                                </div>

                                                <div class="mt-3 text-xs font-bold text-content">
                                                    لوگویی وجود ندارد
                                                </div>

                                            </div>

                                        </template>

                                    </div>


                                    <label class="absolute bottom-3 right-3 cursor-pointer">

                                <span class="btn btn-primary btn-sm">
                                    تغییر لوگو
                                </span>

                                        <input
                                            type="file"
                                            name="logo"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="previewImage($event, 'logo')"
                                        >

                                    </label>

                                </div>


                                @if($salon->logo_path)

                                    <label class="mt-2 flex cursor-pointer items-center gap-2 text-[10px] font-bold text-danger-600">

                                        <input
                                            type="checkbox"
                                            name="remove_logo"
                                            value="1"
                                            class="h-4 w-4 rounded border-border text-danger-600 focus:ring-danger-500"
                                        >

                                        حذف لوگوی فعلی

                                    </label>

                                @endif


                                <div class="form-help">
                                    فرمت‌های JPG، PNG و WEBP — حداکثر ۴ مگابایت
                                </div>


                                @error('logo')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>



                            {{-- ==================================================
                                COVER
                            =================================================== --}}

                            <div class="form-group">

                                <label class="form-label">
                                    تصویر اصلی / Cover
                                </label>


                                <div class="relative overflow-hidden rounded-2xl border border-border bg-primary-50">

                                    <div class="min-h-56">

                                        <template x-if="coverPreview">

                                            <img
                                                :src="coverPreview"
                                                alt="Cover preview"
                                                class="h-56 w-full object-cover"
                                            >

                                        </template>


                                        <template x-if="!coverPreview">

                                            <div class="flex min-h-56 items-center justify-center">

                                                <div class="text-center">

                                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">

                                                        <svg
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.7"
                                                        >
                                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                                            <path d="m3 16 5-5 4 4 3-3 6 6" />
                                                        </svg>

                                                    </div>

                                                    <div class="mt-3 text-xs font-bold text-content">
                                                        تصویر اصلی وجود ندارد
                                                    </div>

                                                </div>

                                            </div>

                                        </template>

                                    </div>


                                    <label class="absolute bottom-3 right-3 cursor-pointer">

                                <span class="btn btn-primary btn-sm">
                                    تغییر Cover
                                </span>

                                        <input
                                            type="file"
                                            name="cover"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="previewImage($event, 'cover')"
                                        >

                                    </label>

                                </div>


                                @if($salon->cover_path)

                                    <label class="mt-2 flex cursor-pointer items-center gap-2 text-[10px] font-bold text-danger-600">

                                        <input
                                            type="checkbox"
                                            name="remove_cover"
                                            value="1"
                                            class="h-4 w-4 rounded border-border text-danger-600 focus:ring-danger-500"
                                        >

                                        حذف Cover فعلی

                                    </label>

                                @endif


                                <div class="form-help">
                                    فرمت‌های JPG، PNG و WEBP — حداکثر ۸ مگابایت
                                </div>


                                @error('cover')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>



                            {{-- Primary Color --}}

                            <div class="form-group">

                                <label class="form-label">
                                    رنگ اصلی
                                </label>

                                <div class="flex items-center gap-2">

                                    <input
                                        type="color"
                                        x-model="primaryColor"
                                        class="h-12 w-14 shrink-0 cursor-pointer rounded-xl border border-border bg-white p-1"
                                    >

                                    <input
                                        type="text"
                                        name="primary_color"
                                        x-model="primaryColor"
                                        class="form-control"
                                        dir="ltr"
                                        maxlength="7"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                    >

                                </div>

                                <div class="form-help">
                                    برای CTA، لینک‌ها و حالت‌های فعال استفاده می‌شود.
                                </div>

                                @error('primary_color')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>



                            {{-- Secondary Color --}}

                            <div class="form-group">

                                <label class="form-label">
                                    رنگ مکمل
                                </label>

                                <div class="flex items-center gap-2">

                                    <input
                                        type="color"
                                        x-model="secondaryColor"
                                        class="h-12 w-14 shrink-0 cursor-pointer rounded-xl border border-border bg-white p-1"
                                    >

                                    <input
                                        type="text"
                                        name="secondary_color"
                                        x-model="secondaryColor"
                                        class="form-control"
                                        dir="ltr"
                                        maxlength="7"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                    >

                                </div>

                                <div class="form-help">
                                    برای عناصر مکمل و جزئیات بصری استفاده می‌شود.
                                </div>

                                @error('secondary_color')
                                <div class="form-error">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>

                    </section>



                    {{-- ==================================================
                        ADDRESS / LOCATION
                    =================================================== --}}

                    <section class="card p-5 md:p-6">

                        <div class="mb-6">

                            <div class="mb-1 text-[10px] font-bold text-accent-600">
                                03
                            </div>

                            <h2 class="section-title">
                                آدرس و موقعیت
                            </h2>

                            <p class="page-subtitle">
                                اطلاعاتی که در Discover و صفحه عمومی سالن نمایش داده می‌شود.
                            </p>

                        </div>


                        <div class="grid gap-5 sm:grid-cols-3">


                            {{-- Province --}}

                            <div class="form-group">

                                <label
                                    for="province"
                                    class="form-label"
                                >
                                    استان
                                </label>

                                <input
                                    id="province"
                                    type="text"
                                    name="province"
                                    value="{{ old('province', $salon->province) }}"
                                    class="form-control"
                                    maxlength="100"
                                    placeholder="تهران"
                                >

                                @error('province')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- City --}}

                            <div class="form-group">

                                <label
                                    for="city"
                                    class="form-label"
                                >
                                    شهر
                                </label>

                                <input
                                    id="city"
                                    type="text"
                                    name="city"
                                    value="{{ old('city', $salon->city) }}"
                                    class="form-control"
                                    maxlength="100"
                                    placeholder="تهران"
                                >

                                @error('city')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- District --}}

                            <div class="form-group">

                                <label
                                    for="district"
                                    class="form-label"
                                >
                                    محله / منطقه
                                </label>

                                <input
                                    id="district"
                                    type="text"
                                    name="district"
                                    value="{{ old('district', $salon->district) }}"
                                    class="form-control"
                                    maxlength="100"
                                    placeholder="سعادت‌آباد"
                                >

                                @error('district')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- Full Address --}}

                            <div class="form-group sm:col-span-3">

                                <label
                                    for="address"
                                    class="form-label"
                                >
                                    آدرس کامل
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    class="form-control"
                                    maxlength="1000"
                                    placeholder="خیابان، بلوار، پلاک..."
                                >{{ old('address', $salon->address) }}</textarea>

                                @error('address')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- Latitude --}}

                            <div class="form-group">

                                <label
                                    for="latitude"
                                    class="form-label"
                                >
                                    Latitude
                                </label>

                                <input
                                    id="latitude"
                                    type="number"
                                    step="0.0000001"
                                    name="latitude"
                                    value="{{ old('latitude', $salon->latitude) }}"
                                    class="form-control"
                                    dir="ltr"
                                    placeholder="35.7219"
                                >

                                @error('latitude')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- Longitude --}}

                            <div class="form-group">

                                <label
                                    for="longitude"
                                    class="form-label"
                                >
                                    Longitude
                                </label>

                                <input
                                    id="longitude"
                                    type="number"
                                    step="0.0000001"
                                    name="longitude"
                                    value="{{ old('longitude', $salon->longitude) }}"
                                    class="form-control"
                                    dir="ltr"
                                    placeholder="51.3347"
                                >

                                @error('longitude')
                                <div class="form-error">{{ $message }}</div>
                                @enderror

                            </div>


                            {{-- Location Status --}}

                            <div class="flex items-end">

                                <div class="w-full rounded-xl bg-primary-50 p-3">

                                    <div class="flex items-center gap-2">

                                        @if($salon->latitude && $salon->longitude)

                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100 text-green-700">
                                        ✓
                                    </span>

                                            <div>

                                                <div class="text-[10px] font-black text-content">
                                                    موقعیت ثبت شده
                                                </div>

                                                <div class="mt-0.5 font-mono text-[8px] text-content-muted" dir="ltr">
                                                    {{ $salon->latitude }}, {{ $salon->longitude }}
                                                </div>

                                            </div>

                                        @else

                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                        !
                                    </span>

                                            <div>

                                                <div class="text-[10px] font-black text-content">
                                                    موقعیت ثبت نشده
                                                </div>

                                                <div class="mt-0.5 text-[8px] text-content-muted">
                                                    Latitude و Longitude را وارد کنید.
                                                </div>

                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>

                </div>



                {{-- ====================================================
                    SIDEBAR
                ===================================================== --}}

                <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">


                    {{-- ==================================================
                        LIVE BRAND PREVIEW
                    =================================================== --}}

                    <section class="card overflow-hidden">

                        <div class="border-b border-border px-5 py-4">

                            <div class="text-[10px] font-bold text-accent-600">
                                LIVE PREVIEW
                            </div>

                            <h2 class="mt-1 text-sm font-black">
                                پیش‌نمایش سالن
                            </h2>

                        </div>


                        <div class="overflow-hidden">

                            {{-- Cover --}}

                            <div class="relative h-32 overflow-hidden">

                                <template x-if="coverPreview">

                                    <img
                                        :src="coverPreview"
                                        alt=""
                                        class="h-full w-full object-cover"
                                    >

                                </template>


                                <template x-if="!coverPreview">

                                    <div
                                        class="h-full w-full"
                                        :style="
                                    `background:
                                    radial-gradient(circle at 80% 20%, ${primaryColor}66, transparent 45%),
                                    linear-gradient(135deg, #171a24, #2d323e);`
                                "
                                    ></div>

                                </template>

                            </div>


                            <div class="relative px-5 pb-5">


                                {{-- Logo --}}

                                <div class="-mt-8 flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-primary-900 text-lg font-black text-white shadow-card">

                                    <template x-if="logoPreview">

                                        <img
                                            :src="logoPreview"
                                            alt=""
                                            class="h-full w-full object-contain"
                                        >

                                    </template>


                                    <template x-if="!logoPreview">

                                <span>
                                    ن
                                </span>

                                    </template>

                                </div>


                                {{-- Name --}}

                                <h3 class="mt-3 truncate text-base font-black text-content">

                                    {{ $salon->name }}

                                </h3>


                                <p class="mt-0.5 text-[10px] text-content-muted">
                                    سالن زیبایی
                                </p>


                                <div class="mt-4 flex gap-2">

                            <span
                                class="flex-1 rounded-xl px-3 py-2 text-center text-[9px] font-bold text-white"
                                :style="`background:${primaryColor}`"
                            >
                                رزرو نوبت
                            </span>

                                    <span
                                        class="flex-1 rounded-xl px-3 py-2 text-center text-[9px] font-bold text-white"
                                        :style="`background:${secondaryColor}`"
                                    >
                                خدمات
                            </span>

                                </div>

                            </div>

                        </div>

                    </section>



                    {{-- ==================================================
                        PUBLIC CODE
                    =================================================== --}}

                    <section class="card bg-primary-950 p-5 text-white">

                        <div class="flex items-start justify-between gap-3">

                            <div>

                                <div class="text-[9px] font-bold text-accent-300">
                                    PUBLIC CODE
                                </div>

                                <code
                                    class="mt-2 block break-all text-sm font-black"
                                    dir="ltr"
                                >
                                    {{ $salon->code }}
                                </code>

                            </div>


                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent-300">

                                <svg
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect x="4" y="4" width="16" height="16" rx="2" />
                                    <path d="M8 8h3v3H8z" />
                                    <path d="M13 13h3v3h-3z" />
                                </svg>

                            </div>

                        </div>


                        <p class="mt-3 text-[10px] leading-6 text-primary-300">
                            این کد برای صفحه عمومی و QR سالن استفاده می‌شود و در ویرایش قابل تغییر نیست.
                        </p>


                        <a
                            href="{{ route('salons.show', $salon->code) }}"
                            target="_blank"
                            rel="noopener"
                            class="btn mt-4 w-full border border-white/10 bg-white/10 text-white hover:bg-white/15"
                        >
                            مشاهده صفحه عمومی
                        </a>

                    </section>



                    {{-- ==================================================
                        STATUS
                    =================================================== --}}

                    <section class="card p-5">

                        <div class="flex items-start justify-between gap-3">

                            <div>

                                <h2 class="text-sm font-black">
                                    وضعیت سالن
                                </h2>

                                <p class="mt-1 text-[10px] text-content-muted">
                                    فعال یا غیرفعال بودن صفحه عمومی
                                </p>

                            </div>


                            @if($salon->is_active)

                                <span class="badge badge-success">
                            فعال
                        </span>

                            @else

                                <span class="badge badge-danger">
                            غیرفعال
                        </span>

                            @endif

                        </div>


                        <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-primary-50 p-3">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $salon->is_active))
                            class="mt-1 h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                            >

                            <span>

                        <span class="block text-xs font-black text-content">
                            سالن فعال باشد
                        </span>

                        <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                            در حالت فعال، سالن در سایت عمومی و Discover قابل مشاهده است.
                        </span>

                    </span>

                        </label>

                    </section>



                    {{-- ==================================================
                        SAVE
                    =================================================== --}}

                    <div class="space-y-2">

                        <button
                            type="submit"
                            class="btn btn-accent w-full"
                        >

                            <svg
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m5 12 4 4L19 6" />
                            </svg>

                            ذخیره تغییرات

                        </button>


                        <a
                            href="{{ route('admin.salons.show', $salon) }}"
                            class="btn btn-secondary w-full"
                        >
                            انصراف
                        </a>

                    </div>

                </aside>

            </div>

        </form>


    </div>

@endsection
