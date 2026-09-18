@extends('layouts.salon')

@section('title', 'ویرایش آرایشگر')

@section('content')

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <div class="mx-auto w-full max-w-4xl">

            {{-- Header --}}
            <div class="mb-7">

                <a
                    href="{{ route('salon.barbers.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
                >
                    <span class="text-base">←</span>
                    بازگشت به آرایشگرها
                </a>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                    <div>

                        <div class="mb-3 flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536M4 20h4l10.732-10.732a2.5 2.5 0 00-3.536-3.536L4.464 16.464A2.5 2.5 0 004 18.232V20z"
                                    />
                                </svg>
                            </div>

                            <div class="text-[10px] font-black tracking-[0.18em] text-accent-600">
                                EDIT BARBER
                            </div>

                        </div>

                        <h1 class="text-2xl font-black text-content sm:text-3xl">
                            ویرایش آرایشگر
                        </h1>

                        <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted">
                            اطلاعات و پروفایل «{{ $barber->name }}» را به‌روزرسانی کنید.
                        </p>

                    </div>

                    {{-- Current status --}}
                    <div class="shrink-0">

                        @if($barber->is_active)

                            <span class="badge bg-success-50 text-success-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                فعال
                            </span>

                        @else

                            <span class="badge bg-primary-100 text-content-muted">
                                <span class="h-1.5 w-1.5 rounded-full bg-content-muted"></span>
                                غیرفعال
                            </span>

                        @endif

                    </div>

                </div>

            </div>


            {{-- Errors --}}
            @if($errors->any())

                <div class="mb-6 rounded-3xl border border-danger-100 bg-danger-50 p-5">

                    <div class="mb-2 flex items-center gap-2 text-xs font-black text-danger-700">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86l-8.1 14a2 2 0 001.73 3h16.16a2 2 0 001.73-3l-8.1-14a2 2 0 00-3.42 0z"
                            />
                        </svg>

                        لطفاً موارد زیر را بررسی کنید:
                    </div>

                    <div class="space-y-1">

                        @foreach($errors->all() as $error)

                            <div class="text-[10px] font-bold leading-6 text-danger-700">
                                • {{ $error }}
                            </div>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- Main Form --}}
            <form
                id="barber-update-form"
                action="{{ route('salon.barbers.update', $barber) }}"
                method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-3xl border border-border bg-white shadow-sm dark:bg-primary-900"
            >

                @csrf
                @method('PUT')


                {{-- Section 1 --}}
                <div class="border-b border-border p-5 sm:p-7">

                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-content">
                                <span class="text-xs font-black">۱</span>
                            </div>

                            <div>
                                <h2 class="text-sm font-black text-content">
                                    اطلاعات اصلی
                                </h2>

                                <p class="mt-1 text-[10px] text-content-muted">
                                    اطلاعات پایه آرایشگر را ویرایش کنید.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Name --}}
                        <div class="form-group sm:col-span-2">

                            <label for="name" class="form-label">
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
                                autocomplete="name"
                            >

                            @error('name')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- Phone --}}
                        <div class="form-group">

                            <label for="phone" class="form-label">
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

                            <label for="specialty" class="form-label">
                                تخصص
                            </label>

                            <input
                                id="specialty"
                                type="text"
                                name="specialty"
                                value="{{ old('specialty', $barber->specialty) }}"
                                class="form-control"
                                maxlength="150"
                                placeholder="مثلاً: اصلاح و استایل مو"
                            >

                            @error('specialty')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Section 2 --}}
                <div class="border-b border-border p-5 sm:p-7">

                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-content">
                                <span class="text-xs font-black">۲</span>
                            </div>

                            <div>
                                <h2 class="text-sm font-black text-content">
                                    تصویر پروفایل
                                </h2>

                                <p class="mt-1 text-[10px] text-content-muted">
                                    تصویر آرایشگر در پروفایل و لیست تیم نمایش داده می‌شود.
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Current image --}}
                    @if($barber->image_path)

                        <div class="mb-5 rounded-3xl border border-border bg-primary-50 p-4 sm:p-5">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                                <div class="relative shrink-0">

                                    <img
                                        src="{{ Storage::url($barber->image_path) }}"
                                        alt="{{ $barber->name }}"
                                        class="h-28 w-28 rounded-2xl object-cover shadow-sm ring-1 ring-border"
                                    >

                                </div>

                                <div class="min-w-0 flex-1">

                                    <div class="text-xs font-black text-content">
                                        تصویر فعلی
                                    </div>

                                    <p class="mt-1 text-[10px] leading-6 text-content-muted">
                                        در صورت انتخاب تصویر جدید، تصویر فعلی جایگزین خواهد شد.
                                    </p>


                                    <label class="mt-4 inline-flex cursor-pointer items-center gap-3">

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

                                        <span class="text-xs font-bold text-danger-600">
                                            حذف تصویر فعلی
                                        </span>

                                    </label>

                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- New image --}}
                    <div class="form-group">

                        <label for="image" class="form-label">
                            {{ $barber->image_path ? 'جایگزینی تصویر' : 'تصویر آرایشگر' }}
                        </label>

                        <label
                            for="image"
                            class="group flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-border bg-primary-50 px-5 py-8 text-center transition hover:border-accent-400 hover:bg-accent-50/40"
                        >

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-accent-600 shadow-sm ring-1 ring-border transition group-hover:scale-105">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14a2 2 0 002-2v-3a2 2 0 00-2-2h-1"
                                    />
                                </svg>

                            </div>

                            <span class="mt-4 text-xs font-black text-content">
                                انتخاب تصویر جدید
                            </span>

                            <span class="mt-1 text-[10px] leading-6 text-content-muted">
                                JPG، PNG یا WEBP
                            </span>

                            <input
                                id="image"
                                type="file"
                                name="image"
                                class="sr-only"
                                accept="image/jpeg,image/png,image/webp"
                            >

                        </label>

                        <div class="form-help mt-2">
                            برای بهترین نتیجه از تصویر مربعی یا پرتره با کیفیت مناسب استفاده کنید.
                        </div>

                        @error('image')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>


                {{-- Section 3 --}}
                <div class="border-b border-border p-5 sm:p-7">

                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-content">
                                <span class="text-xs font-black">۳</span>
                            </div>

                            <div>
                                <h2 class="text-sm font-black text-content">
                                    معرفی آرایشگر
                                </h2>

                                <p class="mt-1 text-[10px] text-content-muted">
                                    یک معرفی کوتاه و حرفه‌ای برای نمایش در پروفایل بنویسید.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="bio" class="form-label">
                            معرفی کوتاه
                        </label>

                        <textarea
                            id="bio"
                            name="bio"
                            class="form-control min-h-36 resize-y"
                            maxlength="5000"
                            placeholder="مثلاً چند سال سابقه دارید، در چه خدماتی تخصص دارید و چه سبک کاری را دنبال می‌کنید..."
                        >{{ old('bio', $barber->bio) }}</textarea>

                        <div class="form-help">
                            این متن می‌تواند در پروفایل آرایشگر و صفحه عمومی سالن نمایش داده شود.
                        </div>

                        @error('bio')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>


                {{-- Section 4 --}}
                <div class="p-5 sm:p-7">

                    <div class="mb-6">

                        <div class="flex items-center gap-3">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-content">
                                <span class="text-xs font-black">۴</span>
                            </div>

                            <div>
                                <h2 class="text-sm font-black text-content">
                                    وضعیت فعالیت
                                </h2>

                                <p class="mt-1 text-[10px] text-content-muted">
                                    مشخص کنید این آرایشگر در رزروهای جدید فعال باشد یا خیر.
                                </p>
                            </div>

                        </div>

                    </div>


                    <label class="flex cursor-pointer items-start gap-4 rounded-3xl border border-border bg-primary-50 p-4 transition hover:border-accent-300 hover:bg-accent-50/40 sm:p-5">

                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $barber->is_active))
                        class="mt-1 h-5 w-5 shrink-0 rounded border-border text-accent-600 focus:ring-accent-500"
                        >

                        <span class="min-w-0">

                            <span class="block text-xs font-black text-content">
                                آرایشگر فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                آرایشگر فعال در انتخاب آرایشگر و رزروهای جدید نمایش داده می‌شود.
                                در صورت غیرفعال بودن، رزرو جدید برای او ایجاد نخواهد شد.
                            </span>

                        </span>

                    </label>

                    @error('is_active')
                    <div class="form-error mt-2">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Actions --}}
                <div class="flex flex-col gap-4 border-t border-border bg-primary-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                    {{-- Delete --}}
                    <button
                        type="submit"
                        form="barber-delete-form"
                        class="btn btn-ghost text-danger-600 transition hover:bg-danger-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a1 1 0 01-1 1H8a1 1 0 01-1-1V7m3 4v6m4-6v6"
                            />
                        </svg>

                        حذف آرایشگر
                    </button>


                    {{-- Main actions --}}
                    <div class="flex flex-col gap-2 sm:flex-row">

                        <a
                            href="{{ route('salon.barbers.index') }}"
                            class="btn btn-ghost justify-center"
                        >
                            انصراف
                        </a>

                        <button
                            type="submit"
                            form="barber-update-form"
                            class="btn btn-accent justify-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12l4 4L19 6"
                                />
                            </svg>

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


            {{-- Bottom hint --}}
            <div class="mt-5 flex items-start gap-3 rounded-2xl border border-border bg-primary-50 px-4 py-3">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mt-0.5 h-4 w-4 shrink-0 text-content-muted"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path
                        stroke-linecap="round"
                        d="M12 11v5m0-8h.01"
                    />
                </svg>

                <p class="text-[10px] leading-6 text-content-muted">
                    تغییرات پس از ذخیره در پروفایل سالن و بخش انتخاب آرایشگر اعمال می‌شود.
                </p>

            </div>

        </div>
    </div>

@endsection
