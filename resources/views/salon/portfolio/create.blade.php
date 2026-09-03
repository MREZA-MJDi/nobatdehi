@extends('layouts.salon')

@section('title', 'افزودن نمونه‌کار')

@section('content')

    <div class="mx-auto w-full max-w-3xl px-4 py-6 pb-28 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('salon.portfolio.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← نمونه‌کارها
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                NEW PORTFOLIO
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                افزودن نمونه‌کار
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                عکس قبل و بعد را برای نمایش در صفحه سالن ثبت کنید.
            </p>

        </div>


        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-danger-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        <form
            action="{{ route('salon.portfolio.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="rounded-3xl border border-border bg-surface p-5 shadow-card sm:p-6"
        >

            @csrf


            <div class="grid gap-5 sm:grid-cols-2">

                <div class="form-group sm:col-span-2">

                    <label
                        for="title"
                        class="form-label"
                    >
                        عنوان نمونه‌کار *
                    </label>

                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        class="form-control"
                        maxlength="150"
                        placeholder="مثلاً رنگ و احیای مو"
                        required
                    >

                    @error('title')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                <div class="form-group">

                    <label
                        for="barber_id"
                        class="form-label"
                    >
                        آرایشگر
                    </label>

                    <select
                        id="barber_id"
                        name="barber_id"
                        class="form-control"
                    >

                        <option value="">
                            کل سالن
                        </option>

                        @foreach($barbers as $barber)

                            <option
                                value="{{ $barber->id }}"
                                @selected(old('barber_id') == $barber->id)
                            >
                            {{ $barber->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="form-group">

                    <label
                        for="service_id"
                        class="form-label"
                    >
                        خدمت
                    </label>

                    <select
                        id="service_id"
                        name="service_id"
                        class="form-control"
                    >

                        <option value="">
                            بدون اتصال به خدمت
                        </option>

                        @foreach($services as $service)

                            <option
                                value="{{ $service->id }}"
                                @selected(old('service_id') == $service->id)
                            >
                            {{ $service->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="form-group">

                    <label
                        for="before_image"
                        class="form-label"
                    >
                        عکس قبل *
                    </label>

                    <input
                        id="before_image"
                        type="file"
                        name="before_image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <div class="form-help">
                        JPG / PNG / WEBP — حداکثر ۵ مگابایت
                    </div>

                </div>


                <div class="form-group">

                    <label
                        for="after_image"
                        class="form-label"
                    >
                        عکس بعد *
                    </label>

                    <input
                        id="after_image"
                        type="file"
                        name="after_image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <div class="form-help">
                        JPG / PNG / WEBP — حداکثر ۵ مگابایت
                    </div>

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
                        placeholder="توضیح کوتاهی درباره این نمونه‌کار..."
                    >{{ old('description') }}</textarea>

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
                    >

                </div>


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
                                نمونه‌کار فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] text-content-muted">
                                نمونه‌کار فعال در صفحه عمومی سالن نمایش داده می‌شود.
                            </span>

                        </span>

                    </label>

                </div>

            </div>


            <div class="mt-6 flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('salon.portfolio.index') }}"
                    class="btn btn-ghost"
                >
                    انصراف
                </a>

                <button
                    type="submit"
                    class="btn btn-accent"
                >
                    ذخیره نمونه‌کار
                </button>

            </div>

        </form>

    </div>

@endsection
