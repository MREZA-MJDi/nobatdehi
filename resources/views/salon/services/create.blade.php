@extends('layouts.app')

@section('title', 'ایجاد خدمت')

@section('content')

    <div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('salon.services.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← بازگشت به خدمات
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                NEW SERVICE
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                ایجاد خدمت
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                خدمت جدید برای {{ $salon->name }} ثبت کنید.
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
            action="{{ route('salon.services.store') }}"
            method="POST"
            class="card p-5 sm:p-6"
        >

            @csrf


            <div class="grid gap-5 sm:grid-cols-2">

                <div class="form-group sm:col-span-2">

                    <label
                        for="name"
                        class="form-label"
                    >
                        نام خدمت
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control"
                        placeholder="مثلاً اصلاح و براشینگ"
                        maxlength="150"
                        required
                    >

                    @error('name')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group">

                    <label
                        for="duration_minutes"
                        class="form-label"
                    >
                        مدت زمان
                    </label>

                    <div class="relative">

                        <select
                            id="duration_minutes"
                            name="duration_minutes"
                            class="form-control"
                            required
                        >

                            @foreach([15, 30, 45, 60, 75, 90, 120, 150, 180, 240, 300, 360] as $duration)

                                <option
                                    value="{{ $duration }}"
                                    @selected(old('duration_minutes', 60) == $duration)
                                >
                                {{ $duration }} دقیقه
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="form-help">
                        مدت انجام خدمت برای محاسبه زمان‌های آزاد رزرو استفاده می‌شود.
                    </div>

                    @error('duration_minutes')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group">

                    <label
                        for="price"
                        class="form-label"
                    >
                        قیمت
                    </label>

                    <div class="relative">

                        <input
                            id="price"
                            type="number"
                            name="price"
                            value="{{ old('price', 0) }}"
                            class="form-control pl-16"
                            min="0"
                            step="1000"
                            inputmode="numeric"
                            required
                        >

                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-content-muted">
                            تومان
                        </span>

                    </div>

                    @error('price')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group">

                    <label
                        for="sort_order"
                        class="form-label"
                    >
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort_order"
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', 0) }}"
                        class="form-control"
                        min="0"
                        max="9999"
                        inputmode="numeric"
                    >

                    @error('sort_order')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group">

                    <label class="form-label">
                        وضعیت
                    </label>

                    <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border border-border bg-primary-50 px-4">

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

                        <span class="text-xs font-bold text-content">
                            این خدمت فعال باشد
                        </span>

                    </label>

                    @error('is_active')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group sm:col-span-2">

                    <label
                        for="description"
                        class="form-label"
                    >
                        توضیحات
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control min-h-32"
                        maxlength="5000"
                        placeholder="توضیح کوتاهی درباره خدمت..."
                    >{{ old('description') }}</textarea>

                    @error('description')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

            </div>


            <div class="mt-6 flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('salon.services.index') }}"
                    class="btn btn-ghost"
                >
                    انصراف
                </a>

                <button
                    type="submit"
                    class="btn btn-accent"
                >
                    ذخیره خدمت
                </button>

            </div>

        </form>

    </div>

@endsection
