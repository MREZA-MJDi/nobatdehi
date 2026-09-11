@extends('layouts.salon')

@section('title', 'تنظیمات سالن')

@section('content')
    @php
        $days = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ];

        $hoursByDay = $salon->workingHours->keyBy('day_of_week');
    @endphp

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <div class="mx-auto w-full max-w-5xl">

            {{-- =========================================================
                HEADER
            ========================================================== --}}
            <div class="mb-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 1.7-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-2.4v-.2a1.7 1.7 0 0 0-1.03-1.56 1.7 1.7 0 0 0-1.88.34l-.06.06-1.7-1.7.06-.06A1.7 1.7 0 0 0 8.46 15a1.7 1.7 0 0 0-1.56-1.03H6.7v-2.4h.2A1.7 1.7 0 0 0 8.46 10a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.7-1.7.06.06a1.7 1.7 0 0 0 1.88.34 1.7 1.7 0 0 0 1.03-1.56V5h2.4v.2a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 1.7 1.7-.06.06A1.7 1.7 0 0 0 19.4 10c.26.63.88 1.03 1.56 1.03h.2v2.4h-.2A1.7 1.7 0 0 0 19.4 15Z"/>
                                </svg>
                            </div>

                            <div>
                                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-violet-500">
                                    SALON STUDIO
                                </div>
                                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                                    سالن من
                                </h1>
                            </div>
                        </div>

                        <p class="max-w-2xl text-sm leading-7 text-slate-500">
                            اطلاعاتی که اینجا وارد می‌کنید در صفحه سالن شما به مشتری‌ها نمایش داده می‌شود.
                            لازم نیست تنظیمات پیچیده‌ای بلد باشید؛ فقط اطلاعات سالن را کامل کنید.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($salon->is_active)
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                سالن فعال است
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-200">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                سالن غیرفعال است
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- =========================================================
                FLASH
            ========================================================== --}}
            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <div class="mb-2 flex items-center gap-2 text-sm font-black text-rose-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v4m0 4h.01M10.3 3.8 2.6 17a2 2 0 0 0 1.73 3h15.34A2 2 0 0 0 21.4 17L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                        </svg>
                        بعضی از اطلاعات نیاز به اصلاح دارند.
                    </div>

                    <ul class="space-y-1 text-xs leading-6 text-rose-600">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('salon.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                x-data
            >
                @csrf
                @method('PUT')

                {{-- =====================================================
                    1. INTRO
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 10v6m0-9h.01"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-slate-900">
                                    معرفی سالن
                                </h2>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    مشتری وقتی وارد صفحه سالن شما می‌شود، این اطلاعات را می‌بیند.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-5 sm:p-7">

                        {{-- Salon name --}}
                        <div>
                            <label for="name" class="mb-2 block text-sm font-bold text-slate-800">
                                اسم سالن
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $salon->name) }}"
                                required
                                placeholder="مثلاً سالن زیبایی رز"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100"
                            >

                            @error('name')
                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <label for="description" class="block text-sm font-bold text-slate-800">
                                    درباره سالن
                                </label>

                                <span class="text-[11px] font-medium text-slate-400">
                                    اختیاری
                                </span>
                            </div>

                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                placeholder="مثلاً: سالن زیبایی رز با تیمی حرفه‌ای در زمینه رنگ، کوتاهی، میکاپ و خدمات زیبایی..."
                                class="block w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100"
                            >{{ old('description', $salon->description) }}</textarea>

                            <p class="mt-2 text-xs leading-6 text-slate-400">
                                یک معرفی کوتاه و صمیمی بنویسید تا مشتری با فضای سالن شما آشنا شود.
                            </p>

                            @error('description')
                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contact --}}
                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>
                                <label for="phone" class="mb-2 block text-sm font-bold text-slate-800">
                                    شماره تماس سالن
                                </label>

                                <input
                                    id="phone"
                                    type="text"
                                    name="phone"
                                    dir="ltr"
                                    value="{{ old('phone', $salon->phone) }}"
                                    placeholder="09xxxxxxxxx"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100"
                                >

                                <p class="mt-2 text-xs text-slate-400">
                                    این شماره برای تماس مشتری با سالن است.
                                </p>

                                @error('phone')
                                <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-bold text-slate-800">
                                    ایمیل سالن
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    dir="ltr"
                                    value="{{ old('email', $salon->email) }}"
                                    placeholder="hello@example.com"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100"
                                >

                                @error('email')
                                <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>
                </section>


                {{-- =====================================================
                    2. BRANDING
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fuchsia-50 text-fuchsia-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m21 15-5-5L5 21"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-slate-900">
                                    ظاهر سالن
                                </h2>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    لوگو و عکس اصلی سالن را انتخاب کنید تا صفحه شما حرفه‌ای‌تر دیده شود.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 p-5 sm:p-7 lg:grid-cols-2">

                        {{-- Logo --}}
                        <div
                            x-data="{
                                preview: null,
                                remove: {{ old('remove_logo') ? 'true' : 'false' }}
                                }"
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <div class="mb-4">
                                <h3 class="text-sm font-black text-slate-900">
                                    لوگوی سالن
                                </h3>
                                <p class="mt-1 text-xs leading-6 text-slate-400">
                                    بهتر است تصویر مربعی و با کیفیت باشد.
                                </p>
                            </div>

                            <div class="flex items-center gap-4">

                                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                                    @if($salon->logo_path)
                                        <img
                                            x-show="!preview && !remove"
                                            src="{{ Storage::url($salon->logo_path) }}"
                                            alt="{{ $salon->name }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @endif

                                    <template x-if="preview && !remove">
                                        <img
                                            :src="preview"
                                            alt="پیش‌نمایش لوگو"
                                            class="h-full w-full object-cover"
                                        >
                                    </template>

                                    <div
                                        x-show="!preview && !remove && {{ $salon->logo_path ? 'false' : 'true' }}"
                                        class="flex h-full w-full items-center justify-center text-slate-300"
                                    >
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/>
                                        </svg>
                                    </div>

                                    <div
                                        x-show="remove"
                                        class="absolute inset-0 flex items-center justify-center bg-slate-900/80 text-xs font-bold text-white"
                                    >
                                        حذف می‌شود
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-violet-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
                                        </svg>

                                        تغییر لوگو

                                        <input
                                            type="file"
                                            name="logo"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    preview = URL.createObjectURL(file);
                                                    remove = false;
                                                }
                                            "
                                        >
                                    </label>

                                    @if($salon->logo_path)
                                        <button
                                            type="button"
                                            @click="remove = !remove; if(remove) preview = null"
                                            class="mr-2 mt-2 text-xs font-bold text-rose-500 hover:text-rose-700"
                                        >
                                            <span x-show="!remove">حذف لوگو</span>
                                            <span x-show="remove">لغو حذف</span>
                                        </button>
                                    @endif

                                    <p class="mt-2 text-[11px] text-slate-400">
                                        JPG، PNG یا WEBP — حداکثر ۵ مگابایت
                                    </p>
                                </div>
                            </div>
                        </div>


                        {{-- Cover --}}
                        <div
                            x-data="{
                                preview: null,
                                remove: {{ old('remove_cover') ? 'true' : 'false' }}
                                }"
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <div class="mb-4">
                                <h3 class="text-sm font-black text-slate-900">
                                    عکس اصلی سالن
                                </h3>
                                <p class="mt-1 text-xs leading-6 text-slate-400">
                                    این تصویر بالای صفحه سالن نمایش داده می‌شود.
                                </p>
                            </div>

                            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <div class="aspect-[16/7]">

                                    @if($salon->cover_path)
                                        <img
                                            x-show="!preview && !remove"
                                            src="{{ Storage::url($salon->cover_path) }}"
                                            alt="{{ $salon->name }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @endif

                                    <template x-if="preview && !remove">
                                        <img
                                            :src="preview"
                                            alt="پیش‌نمایش کاور"
                                            class="h-full w-full object-cover"
                                        >
                                    </template>

                                    <div
                                        x-show="!preview && !remove && {{ $salon->cover_path ? 'false' : 'true' }}"
                                        class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-300"
                                    >
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/>
                                        </svg>
                                    </div>

                                    <div
                                        x-show="remove"
                                        class="absolute inset-0 flex items-center justify-center bg-slate-900/80 text-xs font-bold text-white"
                                    >
                                        این تصویر حذف می‌شود
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-white p-3">
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-violet-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
                                        </svg>

                                        تغییر عکس

                                        <input
                                            type="file"
                                            name="cover"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    preview = URL.createObjectURL(file);
                                                    remove = false;
                                                }
                                            "
                                        >
                                    </label>

                                    @if($salon->cover_path)
                                        <button
                                            type="button"
                                            @click="remove = !remove; if(remove) preview = null"
                                            class="text-xs font-bold text-rose-500 hover:text-rose-700"
                                        >
                                            <span x-show="!remove">حذف عکس</span>
                                            <span x-show="remove">لغو حذف</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <p class="mt-2 text-[11px] text-slate-400">
                                JPG، PNG یا WEBP — حداکثر ۱۰ مگابایت
                            </p>
                        </div>

                    </div>
                </section>


                {{-- =====================================================
                    3. LOCATION
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-slate-900">
                                    آدرس سالن
                                </h2>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    کاری کنیم مشتری بدون دردسر شما را پیدا کند.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-5 sm:p-7">

                        <div class="grid gap-5 sm:grid-cols-3">

                            <div>
                                <label for="province" class="mb-2 block text-sm font-bold text-slate-800">
                                    استان
                                </label>

                                <input
                                    id="province"
                                    type="text"
                                    name="province"
                                    value="{{ old('province', $salon->province) }}"
                                    placeholder="مثلاً تهران"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                >
                            </div>

                            <div>
                                <label for="city" class="mb-2 block text-sm font-bold text-slate-800">
                                    شهر
                                </label>

                                <input
                                    id="city"
                                    type="text"
                                    name="city"
                                    value="{{ old('city', $salon->city) }}"
                                    placeholder="مثلاً تهران"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                >
                            </div>

                            <div>
                                <label for="district" class="mb-2 block text-sm font-bold text-slate-800">
                                    منطقه / محله
                                </label>

                                <input
                                    id="district"
                                    type="text"
                                    name="district"
                                    value="{{ old('district', $salon->district) }}"
                                    placeholder="مثلاً سعادت‌آباد"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                >
                            </div>

                        </div>

                        <div>
                            <label for="address" class="mb-2 block text-sm font-bold text-slate-800">
                                آدرس کامل
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                placeholder="مثلاً تهران، سعادت‌آباد، خیابان سرو غربی، پلاک ۱۲"
                                class="block w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                            >{{ old('address', $salon->address) }}</textarea>

                            <p class="mt-2 text-xs text-slate-400">
                                آدرسی را بنویسید که مشتری بتواند با خواندن آن سالن را پیدا کند.
                            </p>
                        </div>

                        {{-- Advanced location --}}
                        <details class="group rounded-2xl border border-slate-200 bg-slate-50">
                            <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-4">
                                <div>
                                    <div class="text-sm font-bold text-slate-700">
                                        تنظیمات پیشرفته موقعیت
                                    </div>
                                    <div class="mt-1 text-[11px] text-slate-400">
                                        فقط اگر مختصات دقیق سالن را دارید تغییر دهید.
                                    </div>
                                </div>

                                <svg class="h-5 w-5 text-slate-400 transition group-open:rotate-180"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                            </summary>

                            <div class="grid gap-5 border-t border-slate-200 p-4 sm:grid-cols-2">

                                <div>
                                    <label for="latitude" class="mb-2 block text-xs font-bold text-slate-600">
                                        Latitude
                                    </label>

                                    <input
                                        id="latitude"
                                        type="text"
                                        name="latitude"
                                        dir="ltr"
                                        value="{{ old('latitude', $salon->latitude) }}"
                                        placeholder="35.7219"
                                        class="block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-left text-sm text-slate-800 outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100"
                                    >
                                </div>

                                <div>
                                    <label for="longitude" class="mb-2 block text-xs font-bold text-slate-600">
                                        Longitude
                                    </label>

                                    <input
                                        id="longitude"
                                        type="text"
                                        name="longitude"
                                        dir="ltr"
                                        value="{{ old('longitude', $salon->longitude) }}"
                                        placeholder="51.3347"
                                        class="block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-left text-sm text-slate-800 outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100"
                                    >
                                </div>

                            </div>
                        </details>

                    </div>
                </section>


                {{-- =====================================================
                    4. WORKING HOURS
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="8.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-slate-900">
                                    ساعات کاری
                                </h2>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    مشخص کنید هر روز چه ساعتی مشتری می‌تواند برای شما نوبت بگیرد.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">

                        <div class="space-y-3">

                            @foreach($days as $day => $dayName)

                                @php
                                    $hour = $hoursByDay->get($day);

                                    $defaultClosed = $day === 6;

                                    $closed = old(
                                        "working_hours.$day.is_closed",
                                        $hour?->is_closed ?? $defaultClosed
                                    );

                                    $startTime = old(
                                        "working_hours.$day.start_time",
                                        $hour?->start_time
                                            ? substr($hour->start_time, 0, 5)
                                            : '09:00'
                                    );

                                    $endTime = old(
                                        "working_hours.$day.end_time",
                                        $hour?->end_time
                                            ? substr($hour->end_time, 0, 5)
                                            : '21:00'
                                    );
                                @endphp

                                <div
                                    x-data="{ closed: {{ $closed ? 'true' : 'false' }} }"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition"
                                    :class="closed ? 'opacity-75' : ''"
                                >

                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                        {{-- Day --}}
                                        <div class="flex items-center gap-3 sm:w-32">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-black"
                                                :class="closed
                                                    ? 'bg-slate-200 text-slate-500'
                                                    : 'bg-emerald-100 text-emerald-700'"
                                            >
                                                {{ mb_substr($dayName, 0, 1) }}
                                            </div>

                                            <div>
                                                <div class="text-sm font-black text-slate-800">
                                                    {{ $dayName }}
                                                </div>

                                                <div
                                                    class="mt-0.5 text-[10px] font-bold"
                                                    :class="closed ? 'text-slate-400' : 'text-emerald-600'"
                                                    x-text="closed ? 'تعطیل' : 'باز است'"
                                                ></div>
                                            </div>
                                        </div>


                                        {{-- Time --}}
                                        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">

                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-400">
                                                    از
                                                </span>

                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][start_time]"
                                                    value="{{ $startTime }}"
                                                    :disabled="closed"
                                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                                >
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-400">
                                                    تا
                                                </span>

                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][end_time]"
                                                    value="{{ $endTime }}"
                                                    :disabled="closed"
                                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                                >
                                            </div>

                                        </div>


                                        {{-- Toggle --}}
                                        <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3 sm:w-28 sm:border-0 sm:pt-0">

                                            <span class="text-xs font-bold text-slate-500 sm:hidden">
                                                وضعیت
                                            </span>

                                            <input
                                                type="hidden"
                                                name="working_hours[{{ $day }}][is_closed]"
                                                value="0"
                                            >

                                            <button
                                                type="button"
                                                @click="closed = !closed"
                                                class="relative h-7 w-12 shrink-0 rounded-full transition focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                                :class="closed ? 'bg-slate-300' : 'bg-emerald-500'"
                                                :aria-pressed="!closed"
                                            >
                                                <span
                                                    class="absolute top-1 h-5 w-5 rounded-full bg-white shadow-sm transition"
                                                    :class="closed ? 'right-1' : 'right-6'"
                                                ></span>
                                            </button>

                                            <input
                                                type="checkbox"
                                                name="working_hours[{{ $day }}][is_closed]"
                                                value="1"
                                                class="sr-only"
                                                :checked="closed"
                                            >
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path stroke-linecap="round" d="M12 11v5m0-8h.01"/>
                                </svg>

                                <p class="text-xs leading-6 text-amber-700">
                                    زمان‌هایی که اینجا مشخص می‌کنید روی امکان رزرو مشتری تأثیر می‌گذارد.
                                </p>
                            </div>
                        </div>

                    </div>
                </section>


                {{-- =====================================================
                    5. BRAND COLORS
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">

                    <details class="group">

                        <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-5 sm:px-7">

                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="8.5"/>
                                        <path stroke-linecap="round" d="M12 3.5v17M3.5 12h17"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-sm font-black text-slate-900">
                                        ظاهر و رنگ‌بندی
                                    </h2>

                                    <p class="mt-1 text-xs text-slate-400">
                                        اگر دوست دارید رنگ برند سالن را تغییر دهید.
                                    </p>
                                </div>
                            </div>

                            <svg class="h-5 w-5 text-slate-400 transition group-open:rotate-180"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>

                        </summary>

                        <div class="border-t border-slate-100 p-5 sm:p-7">

                            <div class="grid gap-5 sm:grid-cols-2">

                                {{-- Primary --}}
                                <div
                                    x-data="{
                                        value: @js(old('primary_color', $salon->primary_color ?: '#6757E8'))
                                    }"
                                >
                                    <label class="mb-2 block text-sm font-bold text-slate-800">
                                        رنگ اصلی
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <input
                                            type="color"
                                            x-model="value"
                                            class="h-12 w-14 cursor-pointer rounded-xl border border-slate-200 bg-white p-1"
                                        >

                                        <input
                                            type="text"
                                            name="primary_color"
                                            x-model="value"
                                            dir="ltr"
                                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-bold text-slate-800 outline-none focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100"
                                        >
                                    </div>
                                </div>

                                {{-- Secondary --}}
                                <div
                                    x-data="{
                                        value: @js(old('secondary_color', $salon->secondary_color ?: '#37B8C8'))
                                    }"
                                >
                                    <label class="mb-2 block text-sm font-bold text-slate-800">
                                        رنگ دوم
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <input
                                            type="color"
                                            x-model="value"
                                            class="h-12 w-14 cursor-pointer rounded-xl border border-slate-200 bg-white p-1"
                                        >

                                        <input
                                            type="text"
                                            name="secondary_color"
                                            x-model="value"
                                            dir="ltr"
                                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-bold text-slate-800 outline-none focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                                        >
                                    </div>
                                </div>

                            </div>

                            <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs leading-6 text-slate-400">
                                    اگر در مورد رنگ‌ها مطمئن نیستید، این بخش را تغییر ندهید.
                                    رنگ فعلی سالن کاملاً قابل استفاده است.
                                </p>
                            </div>

                        </div>

                    </details>
                </section>


                {{-- =====================================================
                    SAVE
                ====================================================== --}}
                <div class="sticky bottom-4 z-20">

                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white/95 p-3 shadow-xl shadow-slate-200/50 backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:p-4">

                        <div class="hidden items-center gap-3 px-2 sm:flex">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                            </div>

                            <div>
                                <div class="text-xs font-black text-slate-700">
                                    همه چیز آماده است؟
                                </div>

                                <div class="text-[11px] text-slate-400">
                                    تغییرات را ذخیره کنید.
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 sm:mr-auto">

                            <a
                                href="{{ route('salon.dashboard') }}"
                                class="flex-1 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-50 sm:flex-none"
                            >
                                انصراف
                            </a>

                            <button
                                type="submit"
                                class="flex-1 rounded-2xl bg-violet-600 px-7 py-3.5 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-700 hover:shadow-xl hover:shadow-violet-200 focus:outline-none focus:ring-4 focus:ring-violet-100 sm:flex-none"
                            >
                                ذخیره تغییرات
                            </button>

                        </div>

                    </div>
                </div>

            </form>

            {{-- Bottom hint --}}
            <div class="pb-10 pt-6 text-center">
                <p class="text-[11px] leading-6 text-slate-400">
                    اطلاعات این صفحه فقط مربوط به پروفایل و صفحه عمومی سالن شماست.
                </p>
            </div>

        </div>
    </div>
@endsection
