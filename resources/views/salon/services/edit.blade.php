@extends('layouts.salon')

@section('title', 'ویرایش خدمت')

@section('content')

    <div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="mb-6">

            <a
                href="{{ route('salon.services.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به خدمات
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                EDIT SERVICE
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                ویرایش خدمت
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                اطلاعات خدمت «{{ $service->name }}» را برای
                {{ $salon->name }}
                ویرایش کنید.
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

                    <div class="min-w-0">

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
            UPDATE FORM
        ============================================================= --}}

        <form
            id="service-update-form"
            action="{{ route('salon.services.update', $service) }}"
            method="POST"
            class="card p-5 sm:p-6"
        >

            @csrf

            @method('PUT')


            <div class="grid gap-5 sm:grid-cols-2">

                {{-- ====================================================
                    NAME
                ===================================================== --}}

                <div class="form-group sm:col-span-2">

                    <label
                        for="name"
                        class="form-label"
                    >
                        نام خدمت
                        <span class="text-danger-600">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $service->name) }}"
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


                {{-- ====================================================
                    DURATION
                ===================================================== --}}

                <div class="form-group">

                    <label
                        for="duration_minutes"
                        class="form-label"
                    >
                        مدت زمان خدمت
                        <span class="text-danger-600">*</span>
                    </label>

                    <select
                        id="duration_minutes"
                        name="duration_minutes"
                        class="form-control"
                        required
                    >

                        @foreach([
                            15,
                            30,
                            45,
                            60,
                            75,
                            90,
                            120,
                            150,
                            180,
                            240,
                            300,
                            360
                        ] as $duration)

                            <option
                                value="{{ $duration }}"
                                @selected(
                                old(
                            'duration_minutes',
                            $service->duration_minutes
                            ) == $duration
                            )
                            >
                            {{ $duration }} دقیقه
                            </option>

                        @endforeach

                    </select>

                    <div class="form-help">
                        مدت زمان خدمت در ساخت زمان‌های آزاد نوبت استفاده می‌شود.
                    </div>

                    @error('duration_minutes')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- ====================================================
                    PRICE
                ===================================================== --}}

                <div class="form-group">

                    <label
                        for="price"
                        class="form-label"
                    >
                        قیمت
                        <span class="text-danger-600">*</span>
                    </label>

                    <div class="relative">

                        <input
                            id="price"
                            type="number"
                            name="price"
                            value="{{ old('price', $service->price) }}"
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


                {{-- ====================================================
                    SORT ORDER
                ===================================================== --}}

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
                        value="{{ old('sort_order', $service->sort_order) }}"
                        class="form-control"
                        min="0"
                        max="9999"
                        inputmode="numeric"
                    >

                    <div class="form-help">
                        عدد کمتر یعنی نمایش بالاتر.
                    </div>

                    @error('sort_order')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- ====================================================
                    STATUS
                ===================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        وضعیت خدمت
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
                            @checked(
                            old(
                        'is_active',
                        $service->is_active
                        )
                        )
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


                {{-- ====================================================
                    DESCRIPTION
                ===================================================== --}}

                <div class="form-group sm:col-span-2">

                    <label
                        for="description"
                        class="form-label"
                    >
                        توضیحات خدمت
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control min-h-32"
                        maxlength="5000"
                        placeholder="توضیح کوتاهی درباره خدمت..."
                    >{{ old('description', $service->description) }}</textarea>

                    @error('description')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>

            </div>


            {{-- ============================================================
                CURRENT INFO
            ============================================================= --}}

            <div class="mt-6 rounded-2xl border border-border bg-primary-50 p-4">

                <div class="grid gap-4 sm:grid-cols-3">

                    <div>

                        <div class="text-[10px] text-content-muted">
                            سالن
                        </div>

                        <div class="mt-1 truncate text-xs font-bold text-content">
                            {{ $salon->name }}
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            مدت فعلی
                        </div>

                        <div class="mt-1 text-xs font-bold text-content">
                            {{ $service->duration_minutes }} دقیقه
                        </div>

                    </div>


                    <div>

                        <div class="text-[10px] text-content-muted">
                            قیمت فعلی
                        </div>

                        <div class="mt-1 text-xs font-bold text-content">
                            {{ number_format($service->price) }}
                            تومان
                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                UPDATE ACTIONS
            ============================================================= --}}

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-border pt-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <button
                        type="submit"
                        form="service-delete-form"
                        class="btn btn-ghost text-danger-600 transition hover:bg-danger-50"
                    >
                        حذف خدمت
                    </button>

                </div>


                <div class="flex flex-col gap-2 sm:flex-row">

                    <a
                        href="{{ route('salon.services.index') }}"
                        class="btn btn-ghost"
                    >
                        انصراف
                    </a>

                    <button
                        type="submit"
                        form="service-update-form"
                        class="btn btn-accent"
                    >
                        ذخیره تغییرات
                    </button>

                </div>

            </div>

        </form>


        {{-- ============================================================
            DELETE FORM
            Separate form to avoid nested forms.
        ============================================================= --}}

        <form
            id="service-delete-form"
            action="{{ route('salon.services.destroy', $service) }}"
            method="POST"
            class="hidden"
            onsubmit="return confirm('آیا از حذف این خدمت مطمئن هستید؟');"
        >

            @csrf

            @method('DELETE')

        </form>

    </div>

@endsection
