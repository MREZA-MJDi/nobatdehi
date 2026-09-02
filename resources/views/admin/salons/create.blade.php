@extends('layouts.admin')

@section('title', 'ایجاد سالن')

@section('content')


    <div
        x-data="{
        step: 1,

        logoPreview: null,
        coverPreview: null,

        primaryColor: '{{ old('primary_color', '#6757E8') }}',
        secondaryColor: '{{ old('secondary_color', '#37B8C8') }}',

        previewFile(event, type) {
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
        },

        next() {
            if (this.step < 4) {
                this.step++;

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        },

        previous() {
            if (this.step > 1) {
                this.step--;

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }
    }"
        class="mx-auto max-w-[1400px] px-4 py-5 pb-24 md:px-6 md:py-7"
    >

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('admin.salons.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به سالن‌ها
            </a>

            <h1 class="page-title">
                ایجاد سالن جدید
            </h1>

            <p class="page-subtitle">
                اطلاعات سالن، برندینگ و موقعیت مکانی را تنظیم کنید.
            </p>

        </div>


        {{-- ============================================================
            VALIDATION ERRORS
        ============================================================= --}}

        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-black text-red-600">
                        !
                    </div>

                    <div>

                        <div class="text-xs font-black text-red-800">
                            اطلاعات نیاز به بررسی دارند
                        </div>

                        <div class="mt-2 space-y-1 text-[10px] leading-6 text-red-700">

                            @foreach($errors->all() as $error)

                                <div>
                                    • {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- ============================================================
            STEPPER
        ============================================================= --}}

        <div class="mb-6 overflow-x-auto">

            <div class="flex min-w-[520px] items-center">

                @foreach([
                    1 => ['title' => 'اطلاعات', 'desc' => 'مشخصات پایه'],
                    2 => ['title' => 'برندینگ', 'desc' => 'لوگو و رنگ'],
                    3 => ['title' => 'موقعیت', 'desc' => 'آدرس و نقشه'],
                    4 => ['title' => 'بررسی', 'desc' => 'ساخت سالن'],
                ] as $number => $item)

                    <div class="flex min-w-0 items-center">

                        <button
                            type="button"
                            @click="step = {{ $number }}"
                            class="flex items-center gap-2"
                        >

                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black transition"
                            :class="
                                step >= {{ $number }}
                                ? 'bg-accent-600 text-white shadow-iris'
                                : 'border border-border bg-white text-content-muted'
"
                        >
                            {{ $number }}
                        </span>

                            <span class="hidden text-right sm:block">

                            <span
                                class="block text-xs font-black"
                                :class="
                                    step >= {{ $number }}
                                    ? 'text-content'
                                    : 'text-content-muted'
"
                            >
                                {{ $item['title'] }}
                            </span>

                            <span class="mt-0.5 block text-[9px] text-content-faint">
                                {{ $item['desc'] }}
                            </span>

                        </span>

                        </button>

                        @if($number < 4)

                            <span class="mx-3 h-px w-8 bg-border sm:w-16"></span>

                        @endif

                    </div>

                @endforeach

            </div>

        </div>


        {{-- ============================================================
            MAIN FORM
        ============================================================= --}}

        <form
            action="{{ route('admin.salons.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- ========================================================
                STEP 1
            ========================================================= --}}

            <div
                x-show="step === 1"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_350px]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <div class="mb-1 text-[10px] font-bold text-accent-600">
                            01
                        </div>

                        <h2 class="section-title">
                            اطلاعات پایه
                        </h2>

                        <p class="page-subtitle">
                            مشخصات اصلی سالن را وارد کنید.
                        </p>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">


                        {{-- Name --}}

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
                                value="{{ old('name') }}"
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
                                value="{{ old('phone') }}"
                                class="form-control"
                                placeholder="021..."
                                maxlength="30"
                                dir="ltr"
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
                                value="{{ old('email') }}"
                                class="form-control"
                                placeholder="salon@example.com"
                                maxlength="190"
                                dir="ltr"
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
                            >{{ old('description') }}</textarea>

                            @error('description')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>


                        {{-- Owner --}}

                        <div class="form-group sm:col-span-2">

                            <label
                                for="owner_id"
                                class="form-label"
                            >
                                حساب کنترل‌کننده
                            </label>

                            <select
                                id="owner_id"
                                name="owner_id"
                                class="form-control"
                            >

                                <option value="">
                                    انتخاب حساب
                                </option>

                                @foreach(($owners ?? collect()) as $owner)

                                    <option
                                        value="{{ $owner->id }}"
                                        @selected(
                                        old('owner_id') == $owner->id
                                    )
                                    >
                                    {{ $owner->name }}

                                    @if($owner->phone)
                                        — {{ $owner->phone }}
                                        @endif

                                        </option>

                                        @endforeach

                            </select>

                            <div class="form-help">
                                حسابی که برای کنترل و مدیریت این سالن در نظر گرفته می‌شود.
                            </div>

                            @error('owner_id')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- Info Card --}}

                <aside class="card h-fit bg-primary-950 p-5 text-white">

                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-accent-300">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 2 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>

                    </div>

                    <h3 class="text-base font-black">
                        شناسه سالن خودکار ساخته می‌شود
                    </h3>

                    <p class="mt-2 text-xs leading-7 text-primary-300">
                        بعد از ساخت سالن، یک
                        <span
                            class="font-mono text-accent-300"
                            dir="ltr"
                        >
                        slug
                    </span>
                        و یک کد عمومی یکتا برای سالن ساخته می‌شود.
                    </p>

                    <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3 text-[10px] leading-6 text-primary-300">
                        slug برای URL عمومی سالن استفاده می‌شود و کد عمومی سالن نیز به‌صورت یکتا نگهداری می‌شود.
                    </div>

                </aside>

            </div>


            {{-- ========================================================
                STEP 2
            ========================================================= --}}

            <div
                x-show="step === 2"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <div class="mb-1 text-[10px] font-bold text-accent-600">
                            02
                        </div>

                        <h2 class="section-title">
                            برندینگ سالن
                        </h2>

                        <p class="page-subtitle">
                            لوگو، تصویر اصلی و رنگ‌های اختصاصی سالن را انتخاب کنید.
                        </p>

                    </div>


                    <div class="grid gap-6 md:grid-cols-2">


                        {{-- ==================================================
                            LOGO
                        =================================================== --}}

                        <div class="form-group">

                            <label class="form-label">
                                لوگو
                            </label>

                            <label class="group relative flex min-h-56 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-border bg-primary-50 transition hover:border-accent-300">

                                <template x-if="logoPreview">

                                    <img
                                        :src="logoPreview"
                                        alt="پیش‌نمایش لوگو"
                                        class="absolute inset-0 h-full w-full object-contain p-8"
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
                                                <rect
                                                    x="3"
                                                    y="3"
                                                    width="18"
                                                    height="18"
                                                    rx="3"
                                                />

                                                <circle
                                                    cx="8.5"
                                                    cy="8.5"
                                                    r="1.5"
                                                />

                                                <path d="m21 15-5-5L5 21" />
                                            </svg>

                                        </div>

                                        <div class="mt-3 text-xs font-bold text-content">
                                            انتخاب لوگو
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            JPG / PNG / WEBP — حداکثر ۴ مگابایت
                                        </div>

                                    </div>

                                </template>

                                <input
                                    type="file"
                                    name="logo"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="previewFile($event, 'logo')"
                                >

                            </label>

                            @error('logo')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>


                        {{-- ==================================================
                            COVER
                        =================================================== --}}

                        <div class="form-group md:col-span-2">

                            <div class="mb-2 flex items-center justify-between gap-3">

                                <label class="form-label mb-0">
                                    تصویر اصلی / Cover
                                </label>

                                <span class="text-[10px] text-content-muted">
                                پیشنهاد: تصویر افقی و باکیفیت
                            </span>

                            </div>


                            <label class="group relative flex min-h-[18rem] cursor-pointer items-center justify-center overflow-hidden rounded-3xl border-2 border-dashed border-border bg-primary-50 transition hover:border-accent-300 md:min-h-[22rem]">

                                <template x-if="coverPreview">

                                    <img
                                        :src="coverPreview"
                                        alt="پیش‌نمایش Cover"
                                        class="absolute inset-0 h-full w-full object-cover"
                                    >

                                </template>


                                <template x-if="!coverPreview">

                                    <div class="text-center">

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">

                                            <svg
                                                width="28"
                                                height="28"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <rect
                                                    x="3"
                                                    y="3"
                                                    width="18"
                                                    height="18"
                                                    rx="3"
                                                />

                                                <path d="m3 16 5-5 4 4 3-3 6 6" />
                                            </svg>

                                        </div>

                                        <div class="mt-4 text-sm font-black text-content">
                                            انتخاب تصویر Cover
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            JPG / PNG / WEBP — حداکثر ۸ مگابایت
                                        </div>

                                    </div>

                                </template>


                                <input
                                    type="file"
                                    name="cover"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="previewFile($event, 'cover')"
                                >

                            </label>

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
                                    maxlength="7"
                                    dir="ltr"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >

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
                                    maxlength="7"
                                    dir="ltr"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >

                            </div>

                            @error('secondary_color')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- Live Preview --}}

                <aside class="card h-fit overflow-hidden">

                    <div class="border-b border-border px-5 py-4">

                        <div class="text-[10px] font-bold text-accent-600">
                            LIVE PREVIEW
                        </div>

                        <h2 class="mt-1 text-sm font-black">
                            پیش‌نمایش سالن
                        </h2>

                    </div>


                    <div>

                        <div class="relative h-40 overflow-hidden">

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


                            <h3 class="mt-3 truncate text-base font-black text-content">

                            <span
                                x-text="document.querySelector('[name=name]')?.value || 'نام سالن'"
                            ></span>

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

                </aside>

            </div>


            {{-- ========================================================
                STEP 3
            ========================================================= --}}

            <div
                x-show="step === 3"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[380px_minmax(0,1fr)]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <div class="mb-1 text-[10px] font-bold text-accent-600">
                            03
                        </div>

                        <h2 class="section-title">
                            آدرس و موقعیت
                        </h2>

                        <p class="page-subtitle">
                            موقعیت سالن را برای صفحه عمومی ثبت کنید.
                        </p>

                    </div>


                    <div class="space-y-5">


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
                                value="{{ old('province') }}"
                                class="form-control"
                                maxlength="100"
                                placeholder="تهران"
                            >

                            @error('province')

                            <div class="form-error">
                                {{ $message }}
                            </div>

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
                                value="{{ old('city') }}"
                                class="form-control"
                                maxlength="100"
                                placeholder="تهران"
                            >

                            @error('city')

                            <div class="form-error">
                                {{ $message }}
                            </div>

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
                                value="{{ old('district') }}"
                                class="form-control"
                                maxlength="100"
                                placeholder="سعادت‌آباد"
                            >

                            @error('district')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>


                        {{-- Address --}}

                        <div class="form-group">

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
                            >{{ old('address') }}</textarea>

                            @error('address')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>


                        {{-- Coordinates --}}

                        <div class="grid grid-cols-2 gap-3">

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
                                    value="{{ old('latitude') }}"
                                    class="form-control"
                                    placeholder="35.7219"
                                    dir="ltr"
                                >

                                @error('latitude')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                                @enderror

                            </div>


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
                                    value="{{ old('longitude') }}"
                                    class="form-control"
                                    placeholder="51.3347"
                                    dir="ltr"
                                >

                                @error('longitude')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                                @enderror

                            </div>

                        </div>

                    </div>

                </section>


                {{-- Map Preview --}}

                <section class="card overflow-hidden">

                    <div class="flex items-center justify-between border-b border-border px-5 py-4">

                        <div>

                            <h3 class="text-sm font-black">
                                انتخاب موقعیت
                            </h3>

                            <p class="mt-1 text-[10px] text-content-muted">
                                فعلاً مختصات را وارد کنید؛ Map Picker واقعی بعداً متصل می‌شود.
                            </p>

                        </div>

                        <span class="badge badge-neutral">
                        Map
                    </span>

                    </div>


                    <div class="relative min-h-[420px] overflow-hidden bg-[#e8ebf1]">

                        <div
                            class="absolute inset-0"
                            style="
                            background-image:
                                linear-gradient(rgba(255,255,255,.55) 1px, transparent 1px),
                                linear-gradient(90deg, rgba(255,255,255,.55) 1px, transparent 1px);
                            background-size: 36px 36px;
                        "
                        ></div>


                        <div class="absolute left-[8%] top-[42%] h-3 w-[90%] rotate-[-11deg] rounded-full bg-white shadow-sm"></div>

                        <div class="absolute left-[42%] top-[5%] h-[95%] w-3 rotate-[16deg] rounded-full bg-white shadow-sm"></div>


                        <div class="absolute right-[36%] top-[38%] flex h-12 w-12 items-center justify-center rounded-2xl border-4 border-white bg-accent-600 text-white shadow-iris">

                            <svg
                                width="19"
                                height="19"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>

                        </div>


                        <div class="absolute inset-x-4 bottom-4">

                            <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-float backdrop-blur-xl">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle cx="12" cy="12" r="8" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>

                                    </div>

                                    <div>

                                        <div class="text-xs font-black">
                                            موقعیت سالن
                                        </div>

                                        <div class="mt-0.5 text-[10px] text-content-muted">
                                            Latitude و Longitude را وارد کنید.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- ========================================================
                STEP 4
            ========================================================= --}}

            <div
                x-show="step === 4"
                x-transition
                x-cloak
            >

                <section class="card overflow-hidden">

                    <div class="bg-primary-950 p-6 text-white">

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <div class="mb-1 text-[10px] font-bold text-accent-300">
                                    FINAL REVIEW
                                </div>

                                <h2 class="text-xl font-black">
                                    همه‌چیز آماده است
                                </h2>

                                <p class="mt-1 text-xs text-primary-300">
                                    اطلاعات را بررسی کنید و سالن را ایجاد کنید.
                                </p>

                            </div>


                            <span class="rounded-xl bg-white/10 px-3 py-2 font-mono text-[10px] text-accent-200">
                            SLUG → AUTO
                        </span>

                        </div>

                    </div>


                    <div class="grid gap-6 p-5 md:grid-cols-2 md:p-6">


                        {{-- Left Review --}}

                        <div class="space-y-4">

                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    نام سالن
                                </div>

                                <div class="mt-1 text-sm font-black">
                                <span
                                    x-text="document.querySelector('[name=name]')?.value || '—'"
                                ></span>
                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    تلفن
                                </div>

                                <div
                                    class="mt-1 text-sm font-bold"
                                    dir="ltr"
                                >
                                <span
                                    x-text="document.querySelector('[name=phone]')?.value || '—'"
                                ></span>
                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    حساب کنترل‌کننده
                                </div>

                                <div class="mt-1 text-sm font-bold">

                                <span
                                    x-text="
                                        document.querySelector('[name=owner_id] option:checked')?.textContent?.trim()
                                        || 'تعیین نشده'
                                    "
                                ></span>

                                </div>

                            </div>

                        </div>


                        {{-- Right Review --}}

                        <div class="space-y-4">

                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    آدرس
                                </div>

                                <div class="mt-1 text-sm font-bold leading-7">

                                <span
                                    x-text="
                                        [
                                            document.querySelector('[name=province]')?.value,
                                            document.querySelector('[name=city]')?.value,
                                            document.querySelector('[name=district]')?.value,
                                            document.querySelector('[name=address]')?.value
                                        ].filter(Boolean).join('، ') || 'ثبت نشده'
                                    "
                                ></span>

                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    رنگ برند
                                </div>

                                <div class="mt-2 flex items-center gap-2">

                                <span
                                    class="h-7 w-7 rounded-lg border border-border"
                                    :style="`background:${primaryColor}`"
                                ></span>

                                    <code
                                        class="text-xs"
                                        dir="ltr"
                                        x-text="primaryColor"
                                    ></code>

                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    تصویر Cover
                                </div>

                                <div class="mt-2 text-sm font-bold">

                                    <span x-text="coverPreview ? 'انتخاب شده ✓' : 'ثبت نشده'"></span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Active Status --}}

                    <div class="border-t border-border bg-primary-50 p-5">

                        <label class="flex cursor-pointer items-start gap-3">

                            <input
                                type="hidden"
                                name="is_active"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', true))
                            class="mt-1 h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                            >

                            <span>

                            <span class="block text-xs font-black">
                                سالن فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                سالن فعال در Discover و صفحه عمومی قابل مشاهده خواهد بود.
                            </span>

                        </span>

                        </label>

                        @error('is_active')

                        <div class="form-error">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>

                </section>

            </div>


            {{-- ========================================================
                ACTIONS
            ========================================================= --}}

            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">


                {{-- Previous --}}

                <button
                    type="button"
                    x-show="step > 1"
                    x-cloak
                    @click="previous()"
                    class="btn btn-secondary"
                >
                    ← مرحله قبل
                </button>


                {{-- Right Actions --}}

                <div class="flex flex-1 justify-end gap-2">

                    <a
                        href="{{ route('admin.salons.index') }}"
                        class="btn btn-ghost"
                    >
                        انصراف
                    </a>


                    {{-- Next --}}

                    <button
                        type="button"
                        x-show="step < 4"
                        x-cloak
                        @click="next()"
                        class="btn btn-primary"
                    >
                        مرحله بعد →

                    </button>


                    {{-- Submit --}}

                    <button
                        type="submit"
                        x-show="step === 4"
                        x-cloak
                        class="btn btn-accent min-w-44"
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

                        ایجاد سالن

                    </button>

                </div>

            </div>

        </form>

    </div>


@endsection
