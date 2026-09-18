@extends('layouts.salon')

@section('title', 'ایجاد خدمت')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div class="mx-auto w-full max-w-4xl">

            {{-- Header --}}
            <div class="mb-7">
                <a
                    href="{{ route('salon.services.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                >
                    <span class="text-lg">→</span>
                    بازگشت به خدمات
                </a>

                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                        ✦
                    </div>

                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                            NEW SERVICE
                        </div>

                        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                            ایجاد خدمت
                        </h1>
                    </div>
                </div>

                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                    خدمت جدید برای {{ $salon->name }} ثبت کنید تا مشتری‌ها هنگام رزرو بتوانند آن را انتخاب کنند.
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
                action="{{ route('salon.services.store') }}"
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
                                اطلاعات اصلی خدمت
                            </h2>
                        </div>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Name --}}
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
                                autofocus
                            >

                            @error('name')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- Duration --}}
                        <div class="form-group">

                            <label
                                for="duration_minutes"
                                class="form-label"
                            >
                                مدت زمان
                            </label>

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

                            <div class="form-help">
                                مدت انجام خدمت برای محاسبه زمان‌های آزاد رزرو استفاده می‌شود.
                            </div>

                            @error('duration_minutes')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- Price --}}
                        <div
                            class="form-group"
                            x-data="{
                            rawPrice: @js(old('price', '')),

                            get formattedPrice() {
                                if (!this.rawPrice) {
                                    return '';
                                }

                                const digits = String(this.rawPrice)
                                    .replace(/[^0-9]/g, '');

                                if (!digits) {
                                    return '';
                                }

                                return Number(digits).toLocaleString('en-US');
                            },

                            updatePrice(value) {
                                this.rawPrice = String(value)
                                    .replace(/[^0-9]/g, '');
                            }
                        }"
                        >

                            <label
                                for="price_display"
                                class="form-label"
                            >
                                قیمت خدمت
                            </label>

                            <div class="relative">

                                <input
                                    id="price_display"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    class="form-control pl-20 text-left"
                                    placeholder="مثلاً 500,000"
                                    :value="formattedPrice"
                                    @input="updatePrice($event.target.value)"
                                    required
                                >

                                <input
                                    type="hidden"
                                    name="price"
                                    :value="rawPrice"
                                >

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">
                                تومان
                            </span>

                            </div>

                            <div class="mt-2 flex items-center justify-between gap-3">

                                <div class="text-[10px] leading-5 text-slate-400">
                                    مبلغ را به تومان وارد کنید.
                                </div>

                                <div class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-600">
                                    تومان
                                </div>

                            </div>

                            @error('price')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- Sort Order --}}
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

                            <div class="form-help">
                                عدد کمتر، نمایش بالاتر در لیست خدمات.
                            </div>

                            @error('sort_order')
                            <div class="form-error">
                                {{ $message }}
                            </div>
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
                                این خدمت فعال باشد
                            </span>

                            </label>

                            @error('is_active')
                            <div class="form-error">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                </div>

                {{-- Service Image --}}
                <div class="border-b border-slate-100 p-5 sm:p-7">

                    <div class="mb-6 flex items-center gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-700">
                            ۲
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                SERVICE IMAGE
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                تصویر خدمت
                            </h2>
                        </div>

                        <span class="mr-auto text-[10px] font-bold text-slate-400">
            اختیاری
        </span>

                    </div>


                    <div
                        x-data="{
            preview: null,

            selectImage(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.preview = null;
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    this.preview = null;
                    return;
                }

                const reader = new FileReader();

                reader.onload = (e) => {
                    this.preview = e.target.result;
                };

                reader.readAsDataURL(file);
            },

            removeImage() {
                this.preview = null;
                this.$refs.image.value = '';
            }
        }"
                    >

                        <input
                            x-ref="image"
                            id="image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            @change="selectImage"
                        >


                        {{-- Upload Box --}}
                        <div
                            x-show="!preview"
                            class="relative overflow-hidden rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-slate-300 hover:bg-white"
                        >

                            <label
                                for="image"
                                class="flex min-h-64 cursor-pointer flex-col items-center justify-center px-6 py-10 text-center"
                            >

                                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl text-slate-700 shadow-sm ring-1 ring-slate-200">
                                    +
                                </div>

                                <div class="text-sm font-black text-slate-900">
                                    تصویر خدمت را اضافه کنید
                                </div>

                                <div class="mt-2 max-w-sm text-xs leading-6 text-slate-400">
                                    یک تصویر واضح از این خدمت انتخاب کنید تا مشتری هنگام رزرو بتواند آن را بهتر بشناسد.
                                </div>

                                <div class="mt-4 rounded-xl bg-white px-3 py-2 text-[10px] font-black text-slate-500 ring-1 ring-slate-200">
                                    JPG / JPEG / PNG / WEBP
                                    <span class="mx-1 text-slate-300">•</span>
                                    حداکثر ۵MB
                                </div>

                            </label>

                        </div>


                        {{-- Preview --}}
                        <div
                            x-show="preview"
                            x-cloak
                            class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-950"
                        >

                            <div class="aspect-[16/9] w-full">

                                <img
                                    :src="preview"
                                    alt="پیش‌نمایش تصویر خدمت"
                                    class="h-full w-full object-cover"
                                >

                            </div>


                            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 pt-12">

                                <div class="text-xs font-bold text-white">
                                    تصویر انتخاب شد
                                </div>

                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="rounded-xl bg-white/10 px-3 py-2 text-[11px] font-black text-white backdrop-blur transition hover:bg-white/20"
                                >
                                    حذف تصویر
                                </button>

                            </div>

                        </div>


                        @error('image')
                        <div class="form-error mt-3">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>
                {{-- Description --}}
                <div class="border-b border-slate-100 p-5 sm:p-7">

                    <div class="mb-6 flex items-center gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-700">
                            ۲
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                DESCRIPTION
                            </div>

                            <h2 class="mt-1 text-base font-black text-slate-950">
                                معرفی خدمت
                            </h2>
                        </div>

                        <span class="mr-auto text-[10px] font-bold text-slate-400">
                        اختیاری
                    </span>

                    </div>


                    <div class="form-group">

                        <label
                            for="description"
                            class="form-label"
                        >
                            توضیحات
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control min-h-36 resize-y"
                            maxlength="5000"
                            placeholder="توضیح کوتاهی درباره خدمت، نحوه انجام یا ویژگی‌های آن..."
                        >{{ old('description') }}</textarea>

                        <div class="form-help">
                            توضیحات کوتاه و واضح به مشتری کمک می‌کند خدمت مناسب را راحت‌تر انتخاب کند.
                        </div>

                        @error('description')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                    <div class="text-[11px] leading-6 text-slate-400">
                        بعد از ثبت، این خدمت در لیست خدمات سالن قرار می‌گیرد.
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row">

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

                </div>

            </form>

        </div>
    </div>

@endsection
