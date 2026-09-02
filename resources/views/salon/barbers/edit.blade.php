@extends('layouts.app')

@section('title', 'ویرایش آرایشگر')

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
                EDIT BARBER
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                ویرایش آرایشگر
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                پروفایل «{{ $barber->name }}» را ویرایش کنید.
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
            id="barber-update-form"
            action="{{ route('salon.barbers.update', $barber) }}"
            method="POST"
            enctype="multipart/form-data"
            class="card p-5 sm:p-6"
        >

            @csrf

            @method('PUT')


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
                        value="{{ old('name', $barber->name) }}"
                        class="form-control"
                        maxlength="150"
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
                        value="{{ old('phone', $barber->phone) }}"
                        class="form-control"
                        maxlength="30"
                        inputmode="tel"
                        dir="ltr"
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
                        value="{{ old('specialty', $barber->specialty) }}"
                        class="form-control"
                        maxlength="150"
                    >

                    @error('specialty')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Current Image --}}

                @if($barber->image_path)

                    <div class="form-group sm:col-span-2">

                        <label class="form-label">
                            تصویر فعلی
                        </label>

                        <div class="flex flex-col gap-4 rounded-2xl border border-border bg-primary-50 p-4 sm:flex-row sm:items-center">

                            <img
                                src="{{ asset('storage/' . $barber->image_path) }}"
                                alt="{{ $barber->name }}"
                                class="h-24 w-24 rounded-2xl object-cover"
                            >

                            <label class="flex cursor-pointer items-center gap-2">

                                <input
                                    type="hidden"
                                    name="remove_image"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="remove_image"
                                    value="1"
                                    class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                                >

                                <span class="text-xs font-bold text-content">
                                    حذف تصویر فعلی
                                </span>

                            </label>

                        </div>

                    </div>

                @endif


                {{-- New Image --}}

                <div class="form-group sm:col-span-2">

                    <label
                        for="image"
                        class="form-label"
                    >
                        تصویر جدید
                    </label>

                    <input
                        id="image"
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <div class="form-help">
                        برای جایگزینی تصویر فعلی، تصویر جدید انتخاب کنید.
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
                    >{{ old('bio', $barber->bio) }}</textarea>

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
                            @checked(
                            old(
                        'is_active',
                        $barber->is_active
                        )
                        )
                        class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                        >

                        <span>

                            <span class="block text-xs font-black text-content">
                                آرایشگر فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                آرایشگر غیرفعال در رزروهای جدید نمایش داده نمی‌شود.
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


            {{-- Actions --}}

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-border pt-5 sm:flex-row sm:items-center sm:justify-between">

                <button
                    type="submit"
                    form="barber-delete-form"
                    class="btn btn-ghost text-danger-600 transition hover:bg-danger-50"
                >
                    حذف آرایشگر
                </button>


                <div class="flex flex-col gap-2 sm:flex-row">

                    <a
                        href="{{ route('salon.barbers.index') }}"
                        class="btn btn-ghost"
                    >
                        انصراف
                    </a>

                    <button
                        type="submit"
                        form="barber-update-form"
                        class="btn btn-accent"
                    >
                        ذخیره تغییرات
                    </button>

                </div>

            </div>

        </form>


        {{-- Delete Form --}}

        <form
            id="barber-delete-form"
            action="{{ route('salon.barbers.destroy', $barber) }}"
            method="POST"
            class="hidden"
            onsubmit="return confirm('آیا از حذف این آرایشگر مطمئن هستید؟');"
        >

            @csrf

            @method('DELETE')

        </form>

    </div>

@endsection
