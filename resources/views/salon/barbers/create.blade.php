@extends('layouts.salon')

@section('title', 'افزودن آرایشگر')

@section('content')

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8">

        <div class="mx-auto w-full max-w-4xl">

            {{-- ====================================================
                PAGE HEADER
            ===================================================== --}}

            <div class="mb-6">

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                >
                    <span class="text-sm">←</span>
                    بازگشت به آرایشگرها
                </a>


                <div class="flex items-center gap-2">

                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-accent-50 text-accent-600 dark:bg-accent-900/20"
                    >
                        +
                    </span>

                    <span class="text-[10px] font-black tracking-[0.18em] text-accent-600">
                        NEW BARBER
                    </span>

                </div>


                <h1 class="mt-3 text-2xl font-black tracking-tight text-content sm:text-3xl">
                    افزودن آرایشگر
                </h1>


                <p class="mt-2 max-w-xl text-xs leading-7 text-content-muted">
                    پروفایل آرایشگر جدید را برای {{ $salon->name }} ایجاد کنید.
                </p>

            </div>


            {{-- ====================================================
                VALIDATION ERRORS
            ===================================================== --}}

            @if($errors->any())

                <div
                    class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-950/20"
                >

                    <div class="mb-2 text-xs font-black text-red-700 dark:text-red-400">
                        اطلاعات واردشده نیاز به بررسی دارد
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
                FORM
            ===================================================== --}}

            <form
                action="{{ route('salon.barbers.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-3xl border border-border bg-white shadow-sm dark:bg-primary-950"
            >

                @csrf


                {{-- =================================================
                    FORM BODY
                ================================================== --}}

                <div class="p-5 sm:p-7">


                    {{-- ------------------------------------------------
                        BASIC INFORMATION
                    ------------------------------------------------- --}}

                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm text-content-soft dark:bg-primary-900"
                            >
                                ۱
                            </div>

                            <div>

                                <h2 class="text-sm font-black text-content">
                                    اطلاعات اصلی
                                </h2>

                                <p class="mt-0.5 text-[10px] text-content-muted">
                                    مشخصات پایه آرایشگر را وارد کنید.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Name --}}

                        <div class="form-group sm:col-span-2">

                            <label
                                for="name"
                                class="form-label"
                            >
                                نام آرایشگر
                                <span class="text-danger-600">*</span>
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control"
                                maxlength="150"
                                placeholder="مثلاً سارا احمدی"
                                autocomplete="name"
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
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="form-control"
                                maxlength="30"
                                inputmode="tel"
                                dir="ltr"
                                placeholder="0912 123 4567"
                                autocomplete="tel"
                            >

                            @error('phone')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>


                        {{-- Specialty --}}

                        <div class="form-group">

                            <label
                                for="specialty"
                                class="form-label"
                            >
                                تخصص
                            </label>

                            <input
                                id="specialty"
                                type="text"
                                name="specialty"
                                value="{{ old('specialty') }}"
                                class="form-control"
                                maxlength="150"
                                placeholder="مثلاً رنگ و لایت"
                            >

                            @error('specialty')

                            <div class="form-error">
                                {{ $message }}
                            </div>

                            @enderror

                        </div>

                    </div>


                    {{-- ------------------------------------------------
                        IMAGE
                    ------------------------------------------------- --}}

                    <div class="my-7 h-px bg-border"></div>


                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm text-content-soft dark:bg-primary-900"
                            >
                                ۲
                            </div>

                            <div>

                                <h2 class="text-sm font-black text-content">
                                    تصویر پروفایل
                                </h2>

                                <p class="mt-0.5 text-[10px] text-content-muted">
                                    یک تصویر مناسب برای نمایش در پروفایل آرایشگر انتخاب کنید.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="form-group">

                        <label
                            for="image"
                            class="form-label"
                        >
                            تصویر آرایشگر
                        </label>


                        <label
                            for="image"
                            class="group flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-border bg-primary-50/60 px-5 py-8 text-center transition hover:border-accent-300 hover:bg-accent-50/40 dark:bg-primary-900/40 dark:hover:border-accent-700"
                        >

                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-content-muted shadow-sm ring-1 ring-black/5 transition group-hover:scale-105 group-hover:text-accent-600 dark:bg-primary-800 dark:ring-white/5"
                            >

                                <svg
                                    width="25"
                                    height="25"
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

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m21 15-4.5-4.5L7 20"
                                    />

                                </svg>

                            </div>


                            <div class="mt-4 text-xs font-black text-content">
                                انتخاب تصویر آرایشگر
                            </div>


                            <div class="mt-1.5 text-[10px] leading-6 text-content-muted">
                                برای انتخاب تصویر کلیک کنید
                            </div>


                            <div class="mt-3 rounded-full bg-white px-3 py-1.5 text-[9px] font-bold text-content-faint shadow-sm dark:bg-primary-800">
                                JPG / PNG / WEBP · حداکثر ۴ مگابایت
                            </div>


                            <input
                                id="image"
                                type="file"
                                name="image"
                                class="sr-only"
                                accept="image/jpeg,image/png,image/webp"
                            >

                        </label>


                        @error('image')

                        <div class="form-error">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>


                    {{-- ------------------------------------------------
                        BIO
                    ------------------------------------------------- --}}

                    <div class="my-7 h-px bg-border"></div>


                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm text-content-soft dark:bg-primary-900"
                            >
                                ۳
                            </div>

                            <div>

                                <h2 class="text-sm font-black text-content">
                                    معرفی آرایشگر
                                </h2>

                                <p class="mt-0.5 text-[10px] text-content-muted">
                                    توضیح کوتاهی درباره تخصص و سابقه آرایشگر بنویسید.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="form-group">

                        <label
                            for="bio"
                            class="form-label"
                        >
                            معرفی کوتاه
                        </label>

                        <textarea
                            id="bio"
                            name="bio"
                            class="form-control min-h-36"
                            maxlength="5000"
                            placeholder="مثلاً بیش از ۵ سال سابقه در زمینه رنگ و لایت، کوتاهی و استایل مو..."
                        >{{ old('bio') }}</textarea>


                        <div class="form-help">
                            این توضیح می‌تواند در پروفایل عمومی آرایشگر نمایش داده شود.
                        </div>


                        @error('bio')

                        <div class="form-error">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>


                    {{-- ------------------------------------------------
                        STATUS
                    ------------------------------------------------- --}}

                    <div class="my-7 h-px bg-border"></div>


                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm text-content-soft dark:bg-primary-900"
                            >
                                ۴
                            </div>

                            <div>

                                <h2 class="text-sm font-black text-content">
                                    وضعیت فعالیت
                                </h2>

                                <p class="mt-0.5 text-[10px] text-content-muted">
                                    مشخص کنید آرایشگر در سیستم قابل رزرو باشد یا خیر.
                                </p>

                            </div>

                        </div>

                    </div>


                    <label
                        class="group flex cursor-pointer items-center gap-4 rounded-2xl border border-border bg-primary-50/60 p-4 transition hover:border-accent-200 hover:bg-accent-50/40 dark:bg-primary-900/40 dark:hover:border-accent-800"
                    >

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
                        class="h-5 w-5 rounded border-border text-accent-600 focus:ring-accent-500"
                        >


                        <span class="min-w-0">

                            <span class="block text-xs font-black text-content">
                                آرایشگر فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                آرایشگر فعال در صفحه سالن قابل انتخاب برای رزرو نوبت خواهد بود.
                            </span>

                        </span>

                    </label>


                    @error('is_active')

                    <div class="form-error mt-2">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- =================================================
                    ACTIONS
                ================================================== --}}

                <div
                    class="flex flex-col-reverse gap-2 border-t border-border bg-primary-50/50 px-5 py-4 sm:flex-row sm:justify-end sm:px-7 dark:bg-primary-900/30"
                >

                    <a
                        href="{{ route('salon.barbers.index') }}"
                        class="btn btn-ghost w-full sm:w-auto"
                    >
                        انصراف
                    </a>


                    <button
                        type="submit"
                        class="btn btn-accent w-full sm:w-auto"
                    >
                        ذخیره آرایشگر
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
