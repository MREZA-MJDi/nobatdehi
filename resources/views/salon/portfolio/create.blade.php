@extends('layouts.salon')

@section('title', 'افزودن نمونه‌کار')

@section('content')

    <div class="px-4 py-5 pb-28 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div class="mx-auto w-full max-w-4xl">

            {{-- Header --}}
            <div class="mb-7">
                <a
                    href="{{ route('salon.portfolio.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                >
                    <span class="text-lg">→</span>
                    نمونه‌کارها
                </a>

                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                        ✦
                    </div>

                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                            NEW PORTFOLIO
                        </div>

                        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                            افزودن نمونه‌کار
                        </h1>
                    </div>
                </div>

                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                    عکس قبل و بعد را برای نمایش در صفحه عمومی سالن ثبت کنید.
                </p>
            </div>


            {{-- Errors --}}
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                    <div class="mb-2 flex items-center gap-2 text-sm font-black text-red-800">
                        <span>⚠</span>
                        اطلاعات را بررسی کنید
                    </div>

                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-xs font-bold leading-6 text-red-700">
                                • {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- Form --}}
            <form
                action="{{ route('salon.portfolio.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
            >
                @csrf


                {{-- Basic Information --}}
                <div class="border-b border-slate-100 p-5 sm:p-7">

                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-sm font-black text-white">
                            ۱
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                BASIC INFORMATION
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                اطلاعات نمونه‌کار
                            </h2>
                        </div>
                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Title --}}
                        <div class="form-group sm:col-span-2">
                            <label for="title" class="form-label">
                                عنوان نمونه‌کار
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
                                autofocus
                            >

                            @error('title')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Barber --}}
                        <div class="form-group">
                            <label for="barber_id" class="form-label">
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

                            <div class="form-help">
                                در صورت انتخاب آرایشگر، این نمونه‌کار به پروفایل او متصل می‌شود.
                            </div>

                            @error('barber_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Service --}}
                        <div class="form-group">
                            <label for="service_id" class="form-label">
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

                            <div class="form-help">
                                برای مشخص کردن خدمتی که این نتیجه مربوط به آن است.
                            </div>

                            @error('service_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>


                {{-- Images --}}
                <div class="border-b border-slate-100 p-5 sm:p-7">

                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-700">
                            ۲
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                BEFORE / AFTER
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                تصاویر نمونه‌کار
                            </h2>
                        </div>
                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Before Image --}}
                        <div class="form-group">
                            <label for="before_image" class="form-label">
                                عکس قبل
                            </label>

                            <label
                                for="before_image"
                                class="group flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-5 text-center transition hover:border-slate-400 hover:bg-white"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl shadow-sm transition group-hover:scale-105">
                                    ↑
                                </div>

                                <div class="mt-4 text-xs font-black text-slate-800">
                                    انتخاب عکس قبل
                                </div>

                                <div class="mt-1 text-[10px] leading-5 text-slate-400">
                                    JPG / PNG / WEBP
                                </div>

                                <div class="mt-1 text-[9px] font-bold text-slate-400">
                                    حداکثر ۵ مگابایت
                                </div>
                            </label>

                            <input
                                id="before_image"
                                type="file"
                                name="before_image"
                                class="sr-only"
                                accept="image/jpeg,image/png,image/webp"
                                required
                            >

                            @error('before_image')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- After Image --}}
                        <div class="form-group">
                            <label for="after_image" class="form-label">
                                عکس بعد
                            </label>

                            <label
                                for="after_image"
                                class="group flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-5 text-center transition hover:border-slate-400 hover:bg-white"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-sm transition group-hover:scale-105">
                                    ↑
                                </div>

                                <div class="mt-4 text-xs font-black text-slate-800">
                                    انتخاب عکس بعد
                                </div>

                                <div class="mt-1 text-[10px] leading-5 text-slate-400">
                                    JPG / PNG / WEBP
                                </div>

                                <div class="mt-1 text-[9px] font-bold text-slate-400">
                                    حداکثر ۵ مگابایت
                                </div>
                            </label>

                            <input
                                id="after_image"
                                type="file"
                                name="after_image"
                                class="sr-only"
                                accept="image/jpeg,image/png,image/webp"
                                required
                            >

                            @error('after_image')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>


                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-xs shadow-sm">
                                ℹ
                            </div>

                            <div class="text-[10px] leading-6 text-slate-500">
                                برای نتیجه بهتر، عکس‌های قبل و بعد را با نسبت تصویر مشابه و کیفیت مناسب انتخاب کنید.
                            </div>
                        </div>
                    </div>

                </div>


                {{-- Description & Settings --}}
                <div class="border-b border-slate-100 p-5 sm:p-7">

                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-700">
                            ۳
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                DETAILS
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                توضیحات و تنظیمات
                            </h2>
                        </div>
                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Description --}}
                        <div class="form-group sm:col-span-2">
                            <label for="description" class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                class="form-control min-h-36 resize-y"
                                maxlength="5000"
                                placeholder="توضیح کوتاهی درباره این نمونه‌کار، نوع کار یا نتیجه نهایی..."
                            >{{ old('description') }}</textarea>

                            <div class="form-help">
                                توضیحات کوتاه و واضح به مشتری کمک می‌کند نتیجه کار را بهتر درک کند.
                            </div>

                            @error('description')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Sort Order --}}
                        <div class="form-group">
                            <label for="sort_order" class="form-label">
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

                            <div class="form-help">
                                عدد کمتر، نمایش بالاتر در لیست نمونه‌کارها.
                            </div>

                            @error('sort_order')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Status --}}
                        <div class="form-group">
                            <label class="form-label">
                                وضعیت
                            </label>

                            <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 transition hover:bg-white">
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
                                class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-500"
                                >

                                <span class="text-xs font-bold text-slate-800">
                                نمونه‌کار فعال باشد
                            </span>
                            </label>

                            <div class="form-help">
                                نمونه‌کار فعال در صفحه عمومی سالن نمایش داده می‌شود.
                            </div>

                            @error('is_active')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                    <div class="text-[11px] leading-6 text-slate-400">
                        بعد از ثبت، نمونه‌کار در مدیریت سالن ذخیره می‌شود و در صورت فعال بودن برای مشتری‌ها قابل مشاهده خواهد بود.
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row">

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

                </div>

            </form>


            {{-- Bottom Hint --}}
            <div class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm">
                    ✦
                </div>

                <div class="text-[10px] leading-6 text-slate-400">
                    نمونه‌کارهای واقعی و باکیفیت می‌توانند به مشتری در انتخاب خدمت و آرایشگر مناسب کمک کنند.
                </div>
            </div>

        </div>
    </div>

@endsection
