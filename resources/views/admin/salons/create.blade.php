@extends('layouts.admin')

@section('title', 'ایجاد سالن')

@section('content')

    <div
        x-data="{
        step: 1,

        logoPreview: null,
        coverPreview: null,

        primaryColor: '{{ old('primary_color', '#6757E8') }}',
        secondaryColor: '{{ old('secondary_color', '#37B8C8') }}',

        previewFile(event, type) {
            const file = event.target.files?.[0]

            if (!file) {
                return
            }

            const reader = new FileReader()

            reader.onload = (e) => {
                if (type === 'logo') {
                    this.logoPreview = e.target.result
                }

                if (type === 'cover') {
                    this.coverPreview = e.target.result
                }
            }

            reader.readAsDataURL(file)
        },

        next() {
            if (this.step < 4) {
                this.step++
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                })
            }
        },

        previous() {
            if (this.step > 1) {
                this.step--
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                })
            }
        }
    }"
        class="mx-auto max-w-[1400px] px-4 py-5 pb-24 md:px-6 md:py-7"
    >


        {{-- Header --}}

        <div class="mb-6">

            <a
                href="{{ route('admin.salons.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← بازگشت به سالن‌ها
            </a>

            <h1 class="page-title">
                ایجاد سالن جدید
            </h1>

            <p class="page-subtitle">
                اطلاعات سالن، برندینگ و موقعیت مکانی را تنظیم کنید.
            </p>

        </div>



        {{-- Stepper --}}

        <div class="mb-6 overflow-x-auto">

            <div class="flex min-w-[500px] items-center">

                @foreach([
                    1 => ['title' => 'اطلاعات', 'desc' => 'مشخصات پایه'],
                    2 => ['title' => 'برندینگ', 'desc' => 'لوگو و رنگ'],
                    3 => ['title' => 'موقعیت', 'desc' => 'آدرس و نقشه'],
                    4 => ['title' => 'بررسی', 'desc' => 'ساخت سالن'],
                ] as $number => $item)

                    <div class="flex min-w-0 items-center">

                        <button
                            type="button"
                            @click="step = {{ $number }}"
                            class="flex items-center gap-2"
                        >

                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black transition"
                            :class="
                                step >= {{ $number }}
                                ? 'bg-accent-600 text-white shadow-iris'
                                : 'bg-white text-content-muted border border-border'
"
                        >
                            {{ $number }}
                        </span>


                            <span class="hidden sm:block text-right">

                            <span
                                class="block text-xs font-black"
                                :class="
                                    step >= {{ $number }}
                                    ? 'text-content'
                                    : 'text-content-muted'
"
                            >
                                {{ $item['title'] }}
                            </span>

                            <span class="mt-0.5 block text-[9px] text-content-faint">
                                {{ $item['desc'] }}
                            </span>

                        </span>

                        </button>


                        @if($number < 4)

                            <span class="mx-3 h-px w-8 bg-border sm:w-16"></span>

                        @endif

                    </div>

                @endforeach

            </div>

        </div>



        <form
            action="{{ route('admin.salons.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- ========================================================
                STEP 1
            ========================================================= --}}

            <div
                x-show="step === 1"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_350px]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <h2 class="section-title">
                            اطلاعات پایه
                        </h2>

                        <p class="page-subtitle">
                            مشخصات اصلی سالن را وارد کنید.
                        </p>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        <div class="form-group sm:col-span-2">

                            <label class="form-label">
                                نام سالن
                                <span class="text-danger-600">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control"
                                placeholder="مثلاً سالن نوبان"
                                required
                            >

                            @error('name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                شماره تماس
                            </label>

                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="form-control"
                                placeholder="021..."
                                dir="ltr"
                            >

                            @error('phone')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                ایمیل
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control"
                                placeholder="salon@example.com"
                                dir="ltr"
                            >

                            @error('email')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="form-group sm:col-span-2">

                            <label class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                placeholder="توضیح کوتاهی درباره سالن..."
                            >{{ old('description') }}</textarea>

                            @error('description')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="form-group sm:col-span-2">

                            <label class="form-label">
                                مدیر سالن
                            </label>

                            <select
                                name="manager_barber_id"
                                class="form-control"
                            >

                                <option value="">
                                    هنوز انتخاب نشده
                                </option>

                                @foreach($barbers as $barber)

                                    <option
                                        value="{{ $barber->id }}"
                                        @selected(old('manager_barber_id') == $barber->id)
                                    >
                                    {{ $barber->user?->name ?? 'بدون نام' }}
                                    @if($barber->user?->email)
                                        — {{ $barber->user->email }}
                                        @endif
                                        </option>

                                        @endforeach

                            </select>

                            <div class="form-help">
                                این آرایشگر مسئول مدیریت این سالن خواهد بود.
                            </div>

                            @error('manager_barber_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- Info Card --}}

                <aside class="card h-fit bg-primary-950 p-5 text-white">

                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-accent-300">

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 2 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>

                    </div>

                    <h3 class="text-base font-black">
                        شناسه سالن خودکار ساخته می‌شود
                    </h3>

                    <p class="mt-2 text-xs leading-7 text-primary-300">
                        بعد از ساخت سالن یک کد عمومی یکتا مانند
                        <span
                            class="font-mono text-accent-300"
                            dir="ltr"
                        >
                        SALON-X8K92
                    </span>
                        برای آن ثبت می‌شود.
                    </p>

                    <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3 text-[10px] leading-6 text-primary-300">
                        این کد پایه لینک عمومی و QR سالن است و بعداً تغییر نمی‌کند.
                    </div>

                </aside>

            </div>



            {{-- ========================================================
                STEP 2
            ========================================================= --}}

            <div
                x-show="step === 2"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <h2 class="section-title">
                            برندینگ سالن
                        </h2>

                        <p class="page-subtitle">
                            ظاهر عمومی سالن را تعیین کنید.
                        </p>

                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Logo --}}

                        <div class="form-group">

                            <label class="form-label">
                                لوگو
                            </label>

                            <label class="group relative flex min-h-48 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-border bg-primary-50 transition hover:border-accent-300">

                                <template x-if="logoPreview">

                                    <img
                                        :src="logoPreview"
                                        alt="پیش‌نمایش لوگو"
                                        class="absolute inset-0 h-full w-full object-contain p-8"
                                    >

                                </template>


                                <template x-if="!logoPreview">

                                    <div class="text-center">

                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">

                                            <svg
                                                width="22"
                                                height="22"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <path d="m21 15-5-5L5 21" />
                                            </svg>

                                        </div>

                                        <div class="mt-3 text-xs font-bold text-content">
                                            انتخاب لوگو
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            JPG / PNG / WEBP
                                        </div>

                                    </div>

                                </template>


                                <input
                                    type="file"
                                    name="logo"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="previewFile($event, 'logo')"
                                >

                            </label>

                            @error('logo')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        {{-- Cover --}}

                        <div class="form-group">

                            <label class="form-label">
                                تصویر اصلی / Cover
                            </label>

                            <label class="group relative flex min-h-48 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-border bg-primary-50 transition hover:border-accent-300">

                                <template x-if="coverPreview">

                                    <img
                                        :src="coverPreview"
                                        alt="پیش‌نمایش Cover"
                                        class="absolute inset-0 h-full w-full object-cover"
                                    >

                                </template>


                                <template x-if="!coverPreview">

                                    <div class="text-center">

                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-content-muted shadow-soft">

                                            <svg
                                                width="22"
                                                height="22"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                                <path d="m3 16 5-5 4 4 3-3 6 6" />
                                            </svg>

                                        </div>

                                        <div class="mt-3 text-xs font-bold text-content">
                                            انتخاب Cover
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            تصویر افقی با کیفیت بالا
                                        </div>

                                    </div>

                                </template>


                                <input
                                    type="file"
                                    name="cover"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="previewFile($event, 'cover')"
                                >

                            </label>

                            @error('cover')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                        </div>


                        {{-- Colors --}}

                        <div class="form-group">

                            <label class="form-label">
                                رنگ اصلی
                            </label>

                            <div class="flex items-center gap-2">

                                <input
                                    type="color"
                                    name="primary_color"
                                    x-model="primaryColor"
                                    class="h-12 w-14 cursor-pointer rounded-xl border border-border bg-white p-1"
                                >

                                <input
                                    type="text"
                                    x-model="primaryColor"
                                    class="form-control"
                                    maxlength="7"
                                    dir="ltr"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >

                            </div>

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                رنگ مکمل
                            </label>

                            <div class="flex items-center gap-2">

                                <input
                                    type="color"
                                    name="secondary_color"
                                    x-model="secondaryColor"
                                    class="h-12 w-14 cursor-pointer rounded-xl border border-border bg-white p-1"
                                >

                                <input
                                    type="text"
                                    x-model="secondaryColor"
                                    class="form-control"
                                    maxlength="7"
                                    dir="ltr"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >

                            </div>

                        </div>

                    </div>

                </section>


                {{-- Live Preview --}}

                <aside class="card h-fit overflow-hidden">

                    <div
                        class="h-36 bg-primary-900 transition"
                        :style="coverPreview
                        ? `background-image:url('${coverPreview}');background-size:cover;background-position:center`
                        : ''"
                    ></div>

                    <div class="relative px-5 pb-5">

                        <div class="-mt-10 flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-primary-900 text-xl font-black text-white shadow-card">

                            <template x-if="logoPreview">

                                <img
                                    :src="logoPreview"
                                    alt=""
                                    class="h-full w-full object-contain"
                                >

                            </template>

                            <template x-if="!logoPreview">
                            <span>
                                ن
                            </span>
                            </template>

                        </div>


                        <h3 class="mt-3 text-lg font-black">
                            <span x-text="$el.closest('aside') ? (document.querySelector('[name=name]')?.value || 'نام سالن') : ''"></span>
                        </h3>

                        <p class="mt-1 text-xs text-content-muted">
                            سالن زیبایی و مراقبت
                        </p>


                        <div class="mt-4 flex gap-2">

                        <span
                            class="rounded-lg px-3 py-2 text-[10px] font-bold text-white"
                            :style="`background:${primaryColor}`"
                        >
                            رزرو نوبت
                        </span>

                            <span
                                class="rounded-lg px-3 py-2 text-[10px] font-bold"
                                :style="`background:${secondaryColor};color:white`"
                            >
                            خدمات
                        </span>

                        </div>

                    </div>

                </aside>

            </div>



            {{-- ========================================================
                STEP 3
            ========================================================= --}}

            <div
                x-show="step === 3"
                x-transition
                x-cloak
                class="grid gap-5 lg:grid-cols-[380px_minmax(0,1fr)]"
            >

                <section class="card p-5 md:p-6">

                    <div class="mb-6">

                        <h2 class="section-title">
                            آدرس و موقعیت
                        </h2>

                        <p class="page-subtitle">
                            موقعیت سالن را ثبت کنید تا در Map و Discover نمایش داده شود.
                        </p>

                    </div>


                    <div class="space-y-5">

                        <div class="form-group">

                            <label class="form-label">
                                استان
                            </label>

                            <input
                                type="text"
                                name="province"
                                value="{{ old('province') }}"
                                class="form-control"
                                placeholder="تهران"
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                شهر
                            </label>

                            <input
                                type="text"
                                name="city"
                                value="{{ old('city') }}"
                                class="form-control"
                                placeholder="تهران"
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                منطقه / محله
                            </label>

                            <input
                                type="text"
                                name="district"
                                value="{{ old('district') }}"
                                class="form-control"
                                placeholder="سعادت‌آباد"
                            >

                        </div>


                        <div class="form-group">

                            <label class="form-label">
                                آدرس کامل
                            </label>

                            <textarea
                                name="address"
                                class="form-control"
                                placeholder="خیابان، بلوار، پلاک..."
                            >{{ old('address') }}</textarea>

                        </div>


                        <div class="grid grid-cols-2 gap-3">

                            <div class="form-group">

                                <label class="form-label">
                                    Latitude
                                </label>

                                <input
                                    type="number"
                                    step="0.0000001"
                                    name="latitude"
                                    value="{{ old('latitude') }}"
                                    class="form-control"
                                    placeholder="35.7219"
                                    dir="ltr"
                                >

                            </div>


                            <div class="form-group">

                                <label class="form-label">
                                    Longitude
                                </label>

                                <input
                                    type="number"
                                    step="0.0000001"
                                    name="longitude"
                                    value="{{ old('longitude') }}"
                                    class="form-control"
                                    placeholder="51.3347"
                                    dir="ltr"
                                >

                            </div>

                        </div>

                    </div>

                </section>


                {{-- Map Placeholder --}}

                <section class="card overflow-hidden">

                    <div class="flex items-center justify-between border-b border-border px-5 py-4">

                        <div>

                            <h3 class="text-sm font-black">
                                انتخاب موقعیت
                            </h3>

                            <p class="mt-1 text-[10px] text-content-muted">
                                فعلاً مختصات را وارد کنید؛ Map Picker واقعی را در مرحله بعد وصل می‌کنیم.
                            </p>

                        </div>


                        <span class="badge badge-neutral">
                        Map
                    </span>

                    </div>


                    <div class="relative min-h-[420px] overflow-hidden bg-[#e8ebf1]">

                        <div
                            class="absolute inset-0"
                            style="
                            background-image:
                                linear-gradient(rgba(255,255,255,.55) 1px, transparent 1px),
                                linear-gradient(90deg, rgba(255,255,255,.55) 1px, transparent 1px);
                            background-size: 36px 36px;
                        "
                        ></div>


                        <div class="absolute left-[8%] top-[42%] h-3 w-[90%] rotate-[-11deg] rounded-full bg-white shadow-sm"></div>

                        <div class="absolute left-[42%] top-[5%] h-[95%] w-3 rotate-[16deg] rounded-full bg-white shadow-sm"></div>


                        <div class="absolute right-[36%] top-[38%] flex h-12 w-12 items-center justify-center rounded-2xl border-4 border-white bg-accent-600 text-white shadow-iris">

                            <svg
                                width="19"
                                height="19"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>

                        </div>


                        <div class="absolute inset-x-4 bottom-4">

                            <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-float backdrop-blur-xl">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle cx="12" cy="12" r="8" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>

                                    </div>

                                    <div>

                                        <div class="text-xs font-black">
                                            موقعیت سالن
                                        </div>

                                        <div class="mt-0.5 text-[10px] text-content-muted">
                                            Latitude و Longitude را در فرم وارد کنید.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>



            {{-- ========================================================
                STEP 4
            ========================================================= --}}

            <div
                x-show="step === 4"
                x-transition
                x-cloak
            >

                <section class="card overflow-hidden">

                    <div class="bg-primary-950 p-6 text-white">

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <div class="mb-1 text-[10px] font-bold text-accent-300">
                                    FINAL REVIEW
                                </div>

                                <h2 class="text-xl font-black">
                                    همه‌چیز آماده است
                                </h2>

                                <p class="mt-1 text-xs text-primary-300">
                                    اطلاعات را بررسی کنید و سالن را ایجاد کنید.
                                </p>

                            </div>


                            <span class="rounded-xl bg-white/10 px-3 py-2 font-mono text-[10px] text-accent-200">
                            CODE → AUTO
                        </span>

                        </div>

                    </div>


                    <div class="grid gap-6 p-5 md:grid-cols-2 md:p-6">

                        <div class="space-y-4">

                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    نام سالن
                                </div>

                                <div class="mt-1 text-sm font-black">
                                    <span x-text="document.querySelector('[name=name]')?.value || '—'"></span>
                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    تلفن
                                </div>

                                <div class="mt-1 text-sm font-bold" dir="ltr">
                                    <span x-text="document.querySelector('[name=phone]')?.value || '—'"></span>
                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    مدیر سالن
                                </div>

                                <div class="mt-1 text-sm font-bold">
                                <span x-text="
                                    document.querySelector('[name=manager_barber_id] option:checked')?.textContent?.trim() || 'تعیین نشده'
                                "></span>
                                </div>

                            </div>

                        </div>


                        <div class="space-y-4">

                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    آدرس
                                </div>

                                <div class="mt-1 text-sm font-bold leading-7">
                                <span x-text="
                                    [
                                        document.querySelector('[name=city]')?.value,
                                        document.querySelector('[name=district]')?.value,
                                        document.querySelector('[name=address]')?.value
                                    ].filter(Boolean).join('، ') || 'ثبت نشده'
                                "></span>
                                </div>

                            </div>


                            <div>

                                <div class="text-[10px] font-bold text-content-muted">
                                    رنگ برند
                                </div>

                                <div class="mt-2 flex items-center gap-2">

                                <span
                                    class="h-7 w-7 rounded-lg border border-border"
                                    :style="`background:${primaryColor}`"
                                ></span>

                                    <code
                                        class="text-xs"
                                        dir="ltr"
                                        x-text="primaryColor"
                                    ></code>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="border-t border-border bg-primary-50 p-5">

                        <label class="flex cursor-pointer items-start gap-3">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                                class="mt-1 h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                            >

                            <span>

                            <span class="block text-xs font-black">
                                سالن فعال باشد
                            </span>

                            <span class="mt-1 block text-[10px] leading-6 text-content-muted">
                                سالن فعال در Discover و صفحه عمومی قابل مشاهده خواهد بود.
                            </span>

                        </span>

                        </label>

                    </div>

                </section>

            </div>



            {{-- ========================================================
                ACTIONS
            ========================================================= --}}

            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">

                <button
                    type="button"
                    x-show="step > 1"
                    x-cloak
                    @click="previous()"
                    class="btn btn-secondary"
                >
                    ← مرحله قبل
                </button>


                <div class="flex flex-1 justify-end gap-2">

                    <a
                        href="{{ route('admin.salons.index') }}"
                        class="btn btn-ghost"
                    >
                        انصراف
                    </a>


                    <button
                        type="button"
                        x-show="step < 4"
                        @click="next()"
                        class="btn btn-primary"
                    >
                        مرحله بعد →
                    </button>


                    <button
                        type="submit"
                        x-show="step === 4"
                        x-cloak
                        class="btn btn-accent min-w-40"
                    >

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m5 12 4 4L19 6" />
                        </svg>

                        ایجاد سالن

                    </button>

                </div>

            </div>

        </form>

    </div>

@endsection
