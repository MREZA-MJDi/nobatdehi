@extends('layouts.salon')

@section('title', 'افزودن آرایشگر')

@section('content')

    <div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('salon.barbers.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به آرایشگرها
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                NEW BARBER
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                افزودن آرایشگر
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                پروفایل آرایشگر را برای {{ $salon->name }} ایجاد کنید.
            </p>

        </div>


        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-red-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        <form
            action="{{ route('salon.barbers.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="card p-5 sm:p-6"
        >

            @csrf


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


                {{-- Image --}}

                <div class="form-group sm:col-span-2">

                    <label
                        for="image"
                        class="form-label"
                    >
                        تصویر آرایشگر
                    </label>

                    <input
                        id="image"
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <div class="form-help">
                        JPG / PNG / WEBP — حداکثر ۴ مگابایت
                    </div>

                    @error('image')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Bio --}}

                <div class="form-group sm:col-span-2">

                    <label
                        for="bio"
                        class="form-label"
                    >
                        معرفی کوتاه
                    </label>

                    <textarea
                        id="bio"
                        name="bio"
                        class="form-control min-h-32"
                        maxlength="5000"
                        placeholder="تخصص، سابقه یا توضیح کوتاهی درباره آرایشگر..."
                    >{{ old('bio') }}</textarea>

                    @error('bio')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Status --}}

                <div class="form-group sm:col-span-2">

                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-border bg-primary-50 p-4">

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
                        class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                        >

                        <span>

                            <span class="block text-xs font-black text-content">
                                آرایشگر فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                آرایشگر فعال در صفحه سالن قابل انتخاب برای رزرو خواهد بود.
                            </span>

                        </span>

                    </label>

                    @error('is_active')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>

            </div>


            <div class="mt-6 flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="btn btn-ghost"
                >
                    انصراف
                </a>

                <button
                    type="submit"
                    class="btn btn-accent"
                >
                    ذخیره آرایشگر
                </button>

            </div>

        </form>

    </div>

@endsection
