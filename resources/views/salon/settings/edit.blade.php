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

        $hoursByDay = $salon
            ->workingHours
            ->keyBy('day_of_week');
    @endphp

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8" data-settings-page>
        <div class="mx-auto w-full max-w-5xl">

            {{-- =========================================================
                HEADER
            ========================================================== --}}
            <div class="mb-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                    <div>
                        <div class="mb-3 flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600"
                                aria-hidden="true"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 1.7-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-2.4v-.2a1.7 1.7 0 0 0-1.03-1.56 1.7 1.7 0 0 0-1.88.34l-.06.06-1.7-1.7.06-.06A1.7 1.7 0 0 0 8.46 15a1.7 1.7 0 0 0-1.56-1.03H6.7v-2.4h.2A1.7 1.7 0 0 0 8.46 10a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.7-1.7.06.06a1.7 1.7 0 0 0 1.88.34 1.7 1.7 0 0 0 1.03-1.56V5h2.4v.2a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 1.7 1.7-.06.06A1.7 1.7 0 0 0 19.4 10c.26.63.88 1.03 1.56 1.03h.2v2.4h-.2A1.7 1.7 0 0 0 19.4 15Z"/>
                                </svg>
                            </div>

                            <div>
                                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-accent-500">
                                    SALON STUDIO
                                </div>

                                <h1 class="mt-1 text-2xl font-black tracking-tight text-content sm:text-3xl">
                                    سالن من
                                </h1>
                            </div>

                        </div>

                        <p class="max-w-2xl text-sm leading-7 text-content-muted">
                            اطلاعاتی که اینجا وارد می‌کنید در صفحه سالن شما به مشتری‌ها نمایش داده می‌شود.
                            فقط اطلاعات سالن را کامل کنید و ظاهر صفحه را مطابق برند خود تنظیم کنید.
                        </p>
                    </div>


                    {{-- Active state --}}
                    <div class="flex items-center gap-2">
                        @if ($salon->is_active)

                            <span class="inline-flex items-center gap-2 rounded-full bg-success-50 px-3 py-2 text-xs font-bold text-success-700 ring-1 ring-inset ring-success-200">
                                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                                سالن فعال است
                            </span>

                        @else

                            <span class="inline-flex items-center gap-2 rounded-full bg-danger-50 px-3 py-2 text-xs font-bold text-danger-700 ring-1 ring-inset ring-danger-200">
                                <span class="h-2 w-2 rounded-full bg-danger-500"></span>
                                سالن غیرفعال است
                            </span>

                        @endif
                    </div>

                </div>
            </div>


            {{-- =========================================================
                FLASH SUCCESS
            ========================================================== --}}
            @if (session('success'))

                <div
                    class="mb-6 rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-semibold text-success-700"
                    role="status"
                    aria-live="polite"
                >
                    <div class="flex items-center gap-2">

                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m5 12 4 4L19 6"/>
                        </svg>

                        <span>
                            {{ session('success') }}
                        </span>

                    </div>
                </div>

            @endif


            {{-- =========================================================
                FLASH ERRORS
            ========================================================== --}}
            @if ($errors->any())

                <div
                    class="mb-6 rounded-2xl border border-danger-200 bg-danger-50 p-4"
                    role="alert"
                    aria-live="polite"
                >
                    <div class="mb-2 flex items-center gap-2 text-sm font-black text-danger-700">

                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 9v4m0 4h.01M10.3 3.8 2.6 17a2 2 0 0 0 1.73 3h15.34A2 2 0 0 0 21.4 17L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                        </svg>

                        بعضی از اطلاعات نیاز به اصلاح دارند.

                    </div>

                    <ul class="space-y-1 text-xs leading-6 text-danger-600">
                        @foreach ($errors->all() as $error)
                            <li>
                                • {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>

            @endif


            {{-- =========================================================
                ACCOUNT SETTINGS
            ========================================================== --}}
            <section id="settings-account" class="mb-8 grid gap-4 lg:grid-cols-2 scroll-mt-24">
                <article class="rounded-3xl border border-border bg-surface p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-accent-600" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                                <path d="M8 4v4M16 10v4M10 16v4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-accent-600">ACCOUNT</div>
                            <h2 class="mt-1 text-base font-black text-content">شماره موبایل حساب</h2>
                            <p class="mt-1 text-xs leading-6 text-content-muted">
                                این شماره برای ورود به پنل سالن استفاده می‌شود؛ مستقل از شماره عمومی سالن است.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('salon.settings.phone.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="salon-owner-phone" class="mb-2 block text-xs font-black text-content">شماره موبایل کاربری</label>
                            <input
                                id="salon-owner-phone"
                                type="tel"
                                name="phone"
                                value="{{ old('phone', auth()->user()->phone) }}"
                                inputmode="numeric"
                                autocomplete="tel"
                                dir="ltr"
                                class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-semibold text-content outline-none transition focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10"
                                placeholder="0912..."
                                required
                            >
                            @error('phone')
                                <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salon-owner-phone-password" class="mb-2 block text-xs font-black text-content">رمز عبور فعلی</label>
                            <input
                                id="salon-owner-phone-password"
                                type="password"
                                name="phone_current_password"
                                autocomplete="current-password"
                                dir="ltr"
                                class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-semibold text-content outline-none transition focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10"
                                placeholder="برای تأیید تغییر شماره"
                                required
                            >
                            @error('phone_current_password')
                                <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-accent-600 px-4 py-3 text-sm font-black text-white shadow-sm transition hover:bg-accent-700 focus:outline-none focus:ring-4 focus:ring-accent-500/20">
                            تغییر شماره موبایل
                        </button>
                    </form>
                </article>

                <article class="rounded-3xl border border-border bg-surface p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-warning-50 text-warning-700" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="10" width="16" height="10" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-[0.18em] text-warning-700">SECURITY</div>
                            <h2 class="mt-1 text-base font-black text-content">تغییر رمز عبور</h2>
                            <p class="mt-1 text-xs leading-6 text-content-muted">
                                یک رمز حداقل ۸ کاراکتری انتخاب کنید. تغییر رمز از همین صفحه انجام می‌شود.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('salon.settings.password.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="salon-current-password" class="mb-2 block text-xs font-black text-content">رمز عبور فعلی</label>
                            <input
                                id="salon-current-password"
                                type="password"
                                name="security_current_password"
                                autocomplete="current-password"
                                dir="ltr"
                                class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-semibold text-content outline-none transition focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10"
                                required
                            >
                            @error('security_current_password')
                                <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="salon-new-password" class="mb-2 block text-xs font-black text-content">رمز عبور جدید</label>
                                <input
                                    id="salon-new-password"
                                    type="password"
                                    name="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    dir="ltr"
                                    class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-semibold text-content outline-none transition focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10"
                                    required
                                >
                                @error('password')
                                    <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="salon-password-confirmation" class="mb-2 block text-xs font-black text-content">تکرار رمز جدید</label>
                                <input
                                    id="salon-password-confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    minlength="8"
                                    autocomplete="new-password"
                                    dir="ltr"
                                    class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-semibold text-content outline-none transition focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-sm font-black text-content transition hover:bg-surface focus:outline-none focus:ring-4 focus:ring-accent-500/20">
                            ذخیره رمز عبور جدید
                        </button>
                    </form>
                </article>
            </section>



            <nav
                class="mb-6 sticky top-3 z-20 overflow-x-auto rounded-2xl border border-border bg-surface/95 p-1.5 shadow-sm backdrop-blur"
                data-settings-tabs
                aria-label="بخش‌های تنظیمات"
            >
                <div class="flex min-w-max gap-1">
                    <a href="#settings-account" data-settings-tab="settings-account" class="settings-tab is-active">حساب و امنیت</a>
                    <a href="#settings-intro" data-settings-tab="settings-intro" class="settings-tab">معرفی سالن</a>
                    <a href="#settings-branding" data-settings-tab="settings-branding" class="settings-tab">ظاهر و برند</a>
                    <a href="#settings-location" data-settings-tab="settings-location" class="settings-tab">موقعیت</a>
                    <a href="#settings-hours" data-settings-tab="settings-hours" class="settings-tab">ساعات کاری</a>
                </div>
            </nav>

            {{-- =========================================================
                MAIN FORM / ALPINE APP
            ========================================================== --}}
            <form
                action="{{ route('salon.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"

                x-data="{
                    /* =====================================================
                       Crop state
                    ====================================================== */

                    cropOpen: false,
                    cropType: null,

                    cropSrc: '',
                    cropFile: null,

                    cropX: 0,
                    cropY: 0,

                    cropScale: 1,
                    cropBaseScale: 1,
                    cropZoom: 100,

                    cropViewportWidth: 0,
                    cropViewportHeight: 0,

                    cropDragging: false,
                    cropPointerX: 0,
                    cropPointerY: 0,

                    cropError: '',

                    cropObjectUrl: null,


                    /* =====================================================
                       Preview state
                    ====================================================== */

                    logoPreview: null,
                    coverPreview: null,

                    logoPreviewUrl: null,
                    coverPreviewUrl: null,

                    logoRemoved: {{ old('remove_logo') ? 'true' : 'false' }},
                    coverRemoved: {{ old('remove_cover') ? 'true' : 'false' }},


                    /* =====================================================
                       Helpers
                    ====================================================== */

                    revokeUrl(url) {
                        if (!url) {
                            return
                        }

                        try {
                            URL.revokeObjectURL(url)
                        } catch {
                            // Ignore invalid blob URLs.
                        }
                    },


                    /* =====================================================
                       Open cropper
                    ====================================================== */

                    openCrop(type, event) {
                        const input = event?.target
                        const file = input?.files?.[0]

                        if (!file) {
                            return
                        }

                        this.cropError = ''

                        const rules = {
                            logo: {
                                maxSize: 5 * 1024 * 1024,
                                label: 'لوگو',
                            },

                            cover: {
                                maxSize: 10 * 1024 * 1024,
                                label: 'کاور',
                            },
                        }

                        const rule =
                            rules[type]

                        if (!rule) {
                            input.value = ''
                            return
                        }


                        /* -------------------------------------------------
                           File type
                        -------------------------------------------------- */

                        const allowedTypes = [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ]

                        if (
                            !allowedTypes.includes(
                                file.type
                            )
                        ) {
                            this.cropError =
                                `${rule.label} فقط باید JPG، PNG یا WEBP باشد.`

                            input.value = ''

                            return
                        }


                        /* -------------------------------------------------
                           File size
                        -------------------------------------------------- */

                        if (
                            file.size >
                            rule.maxSize
                        ) {
                            this.cropError =
                                `حجم ${rule.label} بیشتر از حد مجاز است.`

                            input.value = ''

                            return
                        }


                        /* -------------------------------------------------
                           Reset previous crop
                        -------------------------------------------------- */

                        if (this.cropObjectUrl) {
                            this.revokeUrl(
                                this.cropObjectUrl
                            )
                        }

                        this.cropType = type
                        this.cropFile = file

                        this.cropSrc =
                            URL.createObjectURL(
                                file
                            )

                        this.cropObjectUrl =
                            this.cropSrc

                        this.cropX = 0
                        this.cropY = 0

                        this.cropScale = 1
                        this.cropBaseScale = 1
                        this.cropZoom = 100

                        this.cropDragging = false

                        this.cropOpen = true

                        this.$nextTick(() => {
                            this.initializeCrop()
                        })
                    },


                    /* =====================================================
                       Initialize crop
                    ====================================================== */

                    initializeCrop() {
                        const viewport =
                            this.$refs.cropViewport

                        const image =
                            this.$refs.cropImage

                        if (
                            !viewport ||
                            !image
                        ) {
                            return
                        }

                        const viewportWidth =
                            viewport.clientWidth

                        const viewportHeight =
                            viewport.clientHeight

                        const naturalWidth =
                            image.naturalWidth

                        const naturalHeight =
                            image.naturalHeight

                        if (
                            !viewportWidth ||
                            !viewportHeight ||
                            !naturalWidth ||
                            !naturalHeight
                        ) {
                            return
                        }

                        this.cropViewportWidth =
                            viewportWidth

                        this.cropViewportHeight =
                            viewportHeight


                        /* -------------------------------------------------
                           Cover the crop viewport
                        -------------------------------------------------- */

                        const coverScale =
                            Math.max(
                                viewportWidth /
                                    naturalWidth,

                                viewportHeight /
                                    naturalHeight
                            )

                        this.cropBaseScale =
                            coverScale

                        this.cropScale =
                            coverScale

                        this.cropZoom = 100

                        this.centerCrop()
                    },


                    /* =====================================================
                       Center image
                    ====================================================== */

                    centerCrop() {
                        const image =
                            this.$refs.cropImage

                        if (
                            !image ||
                            !this.cropViewportWidth ||
                            !this.cropViewportHeight
                        ) {
                            return
                        }

                        const width =
                            image.naturalWidth *
                            this.cropScale

                        const height =
                            image.naturalHeight *
                            this.cropScale

                        this.cropX =
                            (
                                this.cropViewportWidth -
                                width
                            ) / 2

                        this.cropY =
                            (
                                this.cropViewportHeight -
                                height
                            ) / 2

                        this.constrainCrop()
                    },


                    /* =====================================================
                       Keep image inside viewport
                    ====================================================== */

                    constrainCrop() {
                        const image =
                            this.$refs.cropImage

                        if (!image) {
                            return
                        }

                        const width =
                            image.naturalWidth *
                            this.cropScale

                        const height =
                            image.naturalHeight *
                            this.cropScale

                        const minX =
                            this.cropViewportWidth -
                            width

                        const minY =
                            this.cropViewportHeight -
                            height

                        this.cropX =
                            Math.min(
                                0,
                                Math.max(
                                    minX,
                                    this.cropX
                                )
                            )

                        this.cropY =
                            Math.min(
                                0,
                                Math.max(
                                    minY,
                                    this.cropY
                                )
                            )
                    },


                    /* =====================================================
                       Zoom
                    ====================================================== */

                    setZoom(value) {
                        const nextZoom =
                            Math.max(
                                100,
                                Math.min(
                                    300,
                                    Number(value)
                                )
                            )

                        const image =
                            this.$refs.cropImage

                        if (
                            !image ||
                            !this.cropViewportWidth ||
                            !this.cropViewportHeight
                        ) {
                            return
                        }

                        const oldScale =
                            this.cropScale

                        if (!oldScale) {
                            return
                        }

                        const newScale =
                            this.cropBaseScale *
                            (nextZoom / 100)


                        /*
                         * Keep viewport center anchored
                         * while zooming.
                         */

                        const centerSourceX =
                            (
                                this.cropViewportWidth / 2 -
                                this.cropX
                            ) / oldScale

                        const centerSourceY =
                            (
                                this.cropViewportHeight / 2 -
                                this.cropY
                            ) / oldScale

                        this.cropZoom =
                            nextZoom

                        this.cropScale =
                            newScale

                        this.cropX =
                            (
                                this.cropViewportWidth / 2
                            ) -
                            (
                                centerSourceX *
                                newScale
                            )

                        this.cropY =
                            (
                                this.cropViewportHeight / 2
                            ) -
                            (
                                centerSourceY *
                                newScale
                            )

                        this.constrainCrop()
                    },


                    /* =====================================================
                       Pointer drag
                    ====================================================== */

                    startCropDrag(event) {
                        if (!this.cropOpen) {
                            return
                        }

                        this.cropDragging = true

                        this.cropPointerX =
                            event.clientX

                        this.cropPointerY =
                            event.clientY

                        event.currentTarget
                            .setPointerCapture?.(
                                event.pointerId
                            )
                    },

                    dragCrop(event) {
                        if (
                            !this.cropDragging
                        ) {
                            return
                        }

                        const deltaX =
                            event.clientX -
                            this.cropPointerX

                        const deltaY =
                            event.clientY -
                            this.cropPointerY

                        this.cropPointerX =
                            event.clientX

                        this.cropPointerY =
                            event.clientY

                        this.cropX +=
                            deltaX

                        this.cropY +=
                            deltaY

                        this.constrainCrop()
                    },

                    endCropDrag() {
                        this.cropDragging =
                            false
                    },


                    /* =====================================================
                       Cancel crop
                    ====================================================== */

                    cancelCrop() {
                        if (
                            this.cropObjectUrl
                        ) {
                            this.revokeUrl(
                                this.cropObjectUrl
                            )
                        }

                        this.cropOpen = false

                        this.cropType = null

                        this.cropSrc = ''
                        this.cropFile = null

                        this.cropObjectUrl =
                            null

                        this.cropDragging =
                            false

                        this.cropError = ''
                    },


                    /* =====================================================
                       Confirm crop
                    ====================================================== */

                    async confirmCrop() {
                        this.cropError = ''

                        const image =
                            this.$refs.cropImage

                        if (
                            !image ||
                            !this.cropFile ||
                            !this.cropViewportWidth ||
                            !this.cropViewportHeight
                        ) {
                            this.cropError =
                                'تصویر برای برش آماده نیست.'

                            return
                        }


                        /* -------------------------------------------------
                           Source crop coordinates
                        -------------------------------------------------- */

                        const sourceX =
                            Math.max(
                                0,
                                -this.cropX /
                                    this.cropScale
                            )

                        const sourceY =
                            Math.max(
                                0,
                                -this.cropY /
                                    this.cropScale
                            )

                        const sourceWidth =
                            this.cropViewportWidth /
                            this.cropScale

                        const sourceHeight =
                            this.cropViewportHeight /
                            this.cropScale


                        /* -------------------------------------------------
                           Output dimensions
                        -------------------------------------------------- */

                        const outputWidth =
                            this.cropType === 'logo'
                                ? 800
                                : 1600

                        const outputHeight =
                            this.cropType === 'logo'
                                ? 800
                                : 700


                        /* -------------------------------------------------
                           Canvas
                        -------------------------------------------------- */

                        const canvas =
                            document.createElement(
                                'canvas'
                            )

                        canvas.width =
                            outputWidth

                        canvas.height =
                            outputHeight

                        const context =
                            canvas.getContext(
                                '2d'
                            )

                        if (!context) {
                            this.cropError =
                                'امکان پردازش تصویر وجود ندارد.'

                            return
                        }

                        context.imageSmoothingEnabled =
                            true

                        context.imageSmoothingQuality =
                            'high'


                        /* -------------------------------------------------
                           Draw
                        -------------------------------------------------- */

                        context.drawImage(
                            image,

                            sourceX,
                            sourceY,
                            sourceWidth,
                            sourceHeight,

                            0,
                            0,
                            outputWidth,
                            outputHeight
                        )


                        /* -------------------------------------------------
                           Output format
                        -------------------------------------------------- */

                        const outputType =
                            [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ].includes(
                                this.cropFile.type
                            )
                                ? this.cropFile.type
                                : 'image/jpeg'


                        /* -------------------------------------------------
                           Canvas -> Blob
                        -------------------------------------------------- */

                        const blob =
                            await new Promise(
                                resolve => {
                                    canvas.toBlob(
                                        resolve,
                                        outputType,
                                        0.92
                                    )
                                }
                            )

                        if (!blob) {
                            this.cropError =
                                'ساخت فایل نهایی تصویر انجام نشد.'

                            return
                        }


                        /* -------------------------------------------------
                           File name
                        -------------------------------------------------- */

                        const extensionMap = {
                            'image/jpeg': 'jpg',
                            'image/png': 'png',
                            'image/webp': 'webp',
                        }

                        const extension =
                            extensionMap[
                                outputType
                            ] ?? 'jpg'

                        const fileName =
                            this.cropType === 'logo'
                                ? `salon-logo-${Date.now()}.${extension}`
                                : `salon-cover-${Date.now()}.${extension}`


                        /* -------------------------------------------------
                           New File
                        -------------------------------------------------- */

                        const processedFile =
                            new File(
                                [blob],
                                fileName,
                                {
                                    type: outputType,
                                    lastModified:
                                        Date.now(),
                                }
                            )


                        /* -------------------------------------------------
                           Replace input file
                        -------------------------------------------------- */

                        const dataTransfer =
                            new DataTransfer()

                        dataTransfer.items.add(
                            processedFile
                        )

                        const input =
                            this.cropType === 'logo'
                                ? this.$refs.logoInput
                                : this.$refs.coverInput

                        if (!input) {
                            this.cropError =
                                'فیلد تصویر پیدا نشد.'

                            return
                        }

                        input.files =
                            dataTransfer.files


                        /* -------------------------------------------------
                           Preview
                        -------------------------------------------------- */

                        const previewUrl =
                            URL.createObjectURL(
                                processedFile
                            )

                        if (
                            this.cropType ===
                            'logo'
                        ) {

                            this.revokeUrl(
                                this.logoPreviewUrl
                            )

                            this.logoPreviewUrl =
                                previewUrl

                            this.logoPreview =
                                previewUrl

                            this.logoRemoved =
                                false

                        } else {

                            this.revokeUrl(
                                this.coverPreviewUrl
                            )

                            this.coverPreviewUrl =
                                previewUrl

                            this.coverPreview =
                                previewUrl

                            this.coverRemoved =
                                false
                        }


                        /* -------------------------------------------------
                           Close crop
                        -------------------------------------------------- */

                        if (this.cropObjectUrl) {
                            this.revokeUrl(
                                this.cropObjectUrl
                            )
                        }

                        this.cropObjectUrl =
                            null

                        this.cropSrc = ''

                        this.cropFile = null

                        this.cropType = null

                        this.cropOpen = false

                        this.cropDragging =
                            false
                    },


                    /* =====================================================
                       Remove / restore image
                    ====================================================== */

                    toggleRemove(type) {

                        if (
                            type === 'logo'
                        ) {

                            this.logoRemoved =
                                !this.logoRemoved

                            if (
                                this.logoRemoved
                            ) {

                                this.logoPreview =
                                    null

                                this.revokeUrl(
                                    this.logoPreviewUrl
                                )

                                this.logoPreviewUrl =
                                    null

                                if (
                                    this.$refs.logoInput
                                ) {
                                    this.$refs
                                        .logoInput
                                        .value = ''
                                }

                            }

                            return
                        }


                        this.coverRemoved =
                            !this.coverRemoved

                        if (
                            this.coverRemoved
                        ) {

                            this.coverPreview =
                                null

                            this.revokeUrl(
                                this.coverPreviewUrl
                            )

                            this.coverPreviewUrl =
                                null

                            if (
                                this.$refs.coverInput
                            ) {
                                this.$refs
                                    .coverInput
                                    .value = ''
                            }
                        }
                    },


                    /* =====================================================
                       Color preview helpers
                    ====================================================== */

                    primaryColor:
                        @js(
                            old(
                                'primary_color',
                                $salon->primary_color ?: '#6757E8'
                            )
                        ),

                    secondaryColor:
                        @js(
                            old(
                                'secondary_color',
                                $salon->secondary_color ?: '#37B8C8'
                            )
                        ),

                    syncBrandColors() {
                        document.documentElement.style
                            .setProperty(
                                '--preview-salon-primary',
                                this.primaryColor
                            )

                        document.documentElement.style
                            .setProperty(
                                '--preview-salon-secondary',
                                this.secondaryColor
                            )
                    },


                    /* =====================================================
                       Cleanup
                    ====================================================== */

                    destroy() {
                        this.revokeUrl(
                            this.cropObjectUrl
                        )

                        this.revokeUrl(
                            this.logoPreviewUrl
                        )

                        this.revokeUrl(
                            this.coverPreviewUrl
                        )
                    }
                }"
                @submit="syncBrandColors()"
            >
                @csrf
                @method('PUT')


                {{-- =====================================================
                    1. INTRO
                ====================================================== --}}
                <section id="settings-intro" class="mb-6 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm scroll-mt-24">

                    <div class="border-b border-border px-5 py-5 sm:px-7">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/>
                                    <path d="M12 10v6m0-9h.01"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-content">
                                    معرفی سالن
                                </h2>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    مشتری وقتی وارد صفحه سالن شما می‌شود، این اطلاعات را می‌بیند.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="space-y-6 p-5 sm:p-7">

                        {{-- Name --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-sm font-bold text-content"
                            >
                                اسم سالن
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $salon->name) }}"
                                required
                                placeholder="مثلاً سالن زیبایی رز"
                                class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                            >

                            @error('name')
                            <p class="mt-2 text-xs font-semibold text-danger-600">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        {{-- Description --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between gap-3">

                                <label
                                    for="description"
                                    class="block text-sm font-bold text-content"
                                >
                                    درباره سالن
                                </label>

                                <span class="text-[11px] font-medium text-content-faint">
                                    اختیاری
                                </span>

                            </div>

                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                placeholder="مثلاً: سالن زیبایی رز با تیمی حرفه‌ای..."
                                class="block w-full resize-none rounded-2xl border border-border bg-surface-soft px-4 py-4 text-sm leading-7 text-content outline-none transition placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                            >{{ old('description', $salon->description) }}</textarea>

                            <p class="mt-2 text-xs leading-6 text-content-faint">
                                یک معرفی کوتاه و صمیمی بنویسید تا مشتری با فضای سالن شما آشنا شود.
                            </p>

                            @error('description')
                            <p class="mt-2 text-xs font-semibold text-danger-600">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        {{-- Contact --}}
                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>

                                <label
                                    for="phone"
                                    class="mb-2 block text-sm font-bold text-content"
                                >
                                    شماره تماس سالن
                                </label>

                                <input
                                    id="phone"
                                    type="text"
                                    name="phone"
                                    dir="ltr"
                                    value="{{ old('phone', $salon->phone) }}"
                                    placeholder="09xxxxxxxxx"
                                    class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-left text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                                >

                                <p class="mt-2 text-xs text-content-faint">
                                    این شماره برای تماس مشتری با سالن است.
                                </p>

                                @error('phone')
                                <p class="mt-2 text-xs font-semibold text-danger-600">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>


                            <div>

                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-bold text-content"
                                >
                                    ایمیل سالن
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    dir="ltr"
                                    value="{{ old('email', $salon->email) }}"
                                    placeholder="hello@example.com"
                                    class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-left text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                                >

                                @error('email')
                                <p class="mt-2 text-xs font-semibold text-danger-600">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =====================================================
                    2. BRANDING
                ====================================================== --}}
                <section id="settings-branding" class="mb-6 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm">

                    <div class="border-b border-border px-5 py-5 sm:px-7">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="m21 15-5-5L5 21"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-content">
                                    ظاهر سالن
                                </h2>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    لوگو و تصویر اصلی را انتخاب کنید، قبل از ذخیره برش دهید و ظاهر صفحه را از قبل ببینید.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="grid gap-6 p-5 sm:p-7 lg:grid-cols-2">

                        {{-- =================================================
                            LOGO
                        ================================================== --}}
                        <div class="rounded-3xl border border-border bg-surface-soft p-5">

                            <div class="mb-5">

                                <h3 class="text-sm font-black text-content">
                                    لوگوی سالن
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    لوگو همیشه به‌صورت مربع نمایش داده می‌شود.
                                </p>

                            </div>


                            <div class="flex flex-col gap-5">

                                {{-- Preview --}}
                                <div class="flex items-center justify-center">

                                    <div class="relative h-36 w-36 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm">

                                        @if ($salon->logo_path)

                                            <img
                                                x-show="!logoPreview && !logoRemoved"
                                                x-cloak
                                                src="{{ Storage::url($salon->logo_path) }}"
                                                alt="{{ $salon->name }}"
                                                class="h-full w-full object-cover"
                                            >

                                        @endif


                                        <template x-if="logoPreview && !logoRemoved">

                                            <img
                                                :src="logoPreview"
                                                alt="پیش‌نمایش لوگو"
                                                class="h-full w-full object-cover"
                                            >

                                        </template>


                                        <div
                                            x-show="!logoPreview && !logoRemoved && {{ $salon->logo_path ? 'false' : 'true' }}"
                                            x-cloak
                                            class="flex h-full w-full items-center justify-center bg-surface-soft text-content-faint"
                                        >
                                            <svg
                                                class="h-10 w-10"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <path d="m21 15-5-5L5 21"/>
                                            </svg>
                                        </div>


                                        <div
                                            x-show="logoRemoved"
                                            x-cloak
                                            class="absolute inset-0 flex items-center justify-center bg-slate-950/80 text-xs font-black text-white"
                                        >
                                            لوگو حذف می‌شود
                                        </div>

                                    </div>

                                </div>


                                {{-- Controls --}}
                                <div class="flex flex-wrap items-center justify-center gap-2">

                                    <label
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-accent-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-accent-700"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M12 16V4"/>
                                            <path d="m7 9 5-5 5 5"/>
                                            <path d="M5 20h14"/>
                                        </svg>

                                        انتخاب و برش لوگو

                                        <input
                                            x-ref="logoInput"
                                            type="file"
                                            name="logo"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="openCrop('logo', $event)"
                                        >
                                    </label>


                                    @if ($salon->logo_path)

                                        <button
                                            type="button"
                                            @click="toggleRemove('logo')"
                                            class="rounded-xl px-3 py-2.5 text-xs font-bold text-danger-600 transition hover:bg-danger-50"
                                        >
                                            <span
                                                x-show="!logoRemoved"
                                                x-cloak
                                            >
                                                حذف لوگو
                                            </span>

                                            <span
                                                x-show="logoRemoved"
                                                x-cloak
                                            >
                                                لغو حذف
                                            </span>
                                        </button>

                                    @endif

                                </div>


                                <div class="rounded-2xl border border-border bg-surface p-3 text-center">

                                    <div class="text-[11px] font-black text-content-soft">
                                        برش نهایی: ۱:۱
                                    </div>

                                    <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                        JPG، PNG یا WEBP — حداکثر ۵ مگابایت
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            COVER
                        ================================================== --}}
                        <div class="rounded-3xl border border-border bg-surface-soft p-5">

                            <div class="mb-5">

                                <h3 class="text-sm font-black text-content">
                                    عکس اصلی سالن
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    کاور با نسبت ثابت عریض ذخیره می‌شود تا صفحه عمومی همیشه یکدست باشد.
                                </p>

                            </div>


                            <div class="flex flex-col gap-5">

                                {{-- Preview --}}
                                <div class="relative overflow-hidden rounded-2xl border border-border bg-surface">

                                    <div class="aspect-[16/7]">

                                        @if ($salon->cover_path)

                                            <img
                                                x-show="!coverPreview && !coverRemoved"
                                                x-cloak
                                                src="{{ Storage::url($salon->cover_path) }}"
                                                alt="{{ $salon->name }}"
                                                class="h-full w-full object-cover"
                                            >

                                        @endif


                                        <template x-if="coverPreview && !coverRemoved">

                                            <img
                                                :src="coverPreview"
                                                alt="پیش‌نمایش کاور"
                                                class="h-full w-full object-cover"
                                            >

                                        </template>


                                        <div
                                            x-show="!coverPreview && !coverRemoved && {{ $salon->cover_path ? 'false' : 'true' }}"
                                            x-cloak
                                            class="flex h-full w-full items-center justify-center bg-surface-soft text-content-faint"
                                        >
                                            <svg
                                                class="h-10 w-10"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <path d="m21 15-5-5L5 21"/>
                                            </svg>
                                        </div>


                                        <div
                                            x-show="coverRemoved"
                                            x-cloak
                                            class="absolute inset-0 flex items-center justify-center bg-slate-950/80 text-xs font-black text-white"
                                        >
                                            عکس کاور حذف می‌شود
                                        </div>

                                    </div>

                                </div>


                                {{-- Controls --}}
                                <div class="flex flex-wrap items-center justify-center gap-2">

                                    <label
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-accent-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-accent-700"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M12 16V4"/>
                                            <path d="m7 9 5-5 5 5"/>
                                            <path d="M5 20h14"/>
                                        </svg>

                                        انتخاب و برش کاور

                                        <input
                                            x-ref="coverInput"
                                            type="file"
                                            name="cover"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="openCrop('cover', $event)"
                                        >
                                    </label>


                                    @if ($salon->cover_path)

                                        <button
                                            type="button"
                                            @click="toggleRemove('cover')"
                                            class="rounded-xl px-3 py-2.5 text-xs font-bold text-danger-600 transition hover:bg-danger-50"
                                        >
                                            <span
                                                x-show="!coverRemoved"
                                                x-cloak
                                            >
                                                حذف کاور
                                            </span>

                                            <span
                                                x-show="coverRemoved"
                                                x-cloak
                                            >
                                                لغو حذف
                                            </span>
                                        </button>

                                    @endif

                                </div>


                                <div class="rounded-2xl border border-border bg-surface p-3 text-center">

                                    <div class="text-[11px] font-black text-content-soft">
                                        برش نهایی: ۱۶:۷
                                    </div>

                                    <div class="mt-1 text-[10px] leading-5 text-content-muted">
                                        JPG، PNG یا WEBP — حداکثر ۱۰ مگابایت
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        BRAND MINI PREVIEW
                    ================================================== --}}
                    <div class="border-t border-border px-5 py-5 sm:px-7">

                        <div class="mb-4 flex items-center justify-between gap-3">

                            <div>
                                <h3 class="text-sm font-black text-content">
                                    پیش‌نمایش برند
                                </h3>

                                <p class="mt-1 text-xs text-content-muted">
                                    فقط برای دیدن ترکیب رنگی قبل از ذخیره.
                                </p>
                            </div>

                            <div class="text-[10px] font-bold text-content-faint">
                                Live Preview
                            </div>

                        </div>


                        <div
                            class="overflow-hidden rounded-3xl border border-border bg-surface"
                            :style="{
                                '--preview-salon-primary': primaryColor,
                                '--preview-salon-secondary': secondaryColor
                            }"
                        >

                            <div
                                class="h-2"
                                :style="{
                                    background: `linear-gradient(90deg, ${primaryColor}, ${secondaryColor})`
                                }"
                            ></div>

                            <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-sm"
                                        :style="{
                                            backgroundColor: primaryColor
                                        }"
                                    >
                                        RM
                                    </div>

                                    <div>
                                        <div
                                            class="text-sm font-black"
                                            :style="{
                                                color: primaryColor
                                            }"
                                        >
                                            {{ $salon->name }}
                                        </div>

                                        <div class="mt-1 text-[11px] text-content-muted">
                                            نمونه نمایش رنگ برند سالن
                                        </div>
                                    </div>

                                </div>


                                <button
                                    type="button"
                                    class="rounded-xl px-4 py-2.5 text-xs font-black text-white shadow-sm"
                                    :style="{
                                        backgroundColor: primaryColor
                                    }"
                                >
                                    رزرو نوبت
                                </button>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =====================================================
                    3. LOCATION
                ====================================================== --}}
                <section id="settings-location" class="mb-6 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm">

                    <div class="border-b border-border px-5 py-5 sm:px-7">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info-50 text-info-600">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-content">
                                    آدرس سالن
                                </h2>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    کاری کنیم مشتری بدون دردسر شما را پیدا کند.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="space-y-6 p-5 sm:p-7">

                        <div class="grid gap-5 sm:grid-cols-3">

                            <div>

                                <label
                                    for="province"
                                    class="mb-2 block text-sm font-bold text-content"
                                >
                                    استان
                                </label>

                                <input
                                    id="province"
                                    type="text"
                                    name="province"
                                    value="{{ old('province', $salon->province) }}"
                                    placeholder="مثلاً تهران"
                                    class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-info-400 focus:bg-surface focus:ring-4 focus:ring-info-500/10"
                                >

                                @error('province')
                                <p class="mt-2 text-xs font-semibold text-danger-600">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>


                            <div>

                                <label
                                    for="city"
                                    class="mb-2 block text-sm font-bold text-content"
                                >
                                    شهر
                                </label>

                                <input
                                    id="city"
                                    type="text"
                                    name="city"
                                    value="{{ old('city', $salon->city) }}"
                                    placeholder="مثلاً تهران"
                                    class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-info-400 focus:bg-surface focus:ring-4 focus:ring-info-500/10"
                                >

                                @error('city')
                                <p class="mt-2 text-xs font-semibold text-danger-600">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>


                            <div>

                                <label
                                    for="district"
                                    class="mb-2 block text-sm font-bold text-content"
                                >
                                    منطقه / محله
                                </label>

                                <input
                                    id="district"
                                    type="text"
                                    name="district"
                                    value="{{ old('district', $salon->district) }}"
                                    placeholder="مثلاً سعادت‌آباد"
                                    class="block w-full rounded-2xl border border-border bg-surface-soft px-4 py-3.5 text-sm font-semibold text-content outline-none transition placeholder:text-content-faint focus:border-info-400 focus:bg-surface focus:ring-4 focus:ring-info-500/10"
                                >

                                @error('district')
                                <p class="mt-2 text-xs font-semibold text-danger-600">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>

                        </div>


                        <div>

                            <label
                                for="address"
                                class="mb-2 block text-sm font-bold text-content"
                            >
                                آدرس کامل
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                placeholder="مثلاً تهران، سعادت‌آباد، خیابان سرو غربی، پلاک ۱۲"
                                class="block w-full resize-none rounded-2xl border border-border bg-surface-soft px-4 py-4 text-sm leading-7 text-content outline-none transition placeholder:text-content-faint focus:border-info-400 focus:bg-surface focus:ring-4 focus:ring-info-500/10"
                            >{{ old('address', $salon->address) }}</textarea>

                            @error('address')
                            <p class="mt-2 text-xs font-semibold text-danger-600">
                                {{ $message }}
                            </p>
                            @enderror

                            <p class="mt-2 text-xs text-content-faint">
                                آدرسی را بنویسید که مشتری بتواند با خواندن آن سالن را پیدا کند.
                            </p>

                        </div>


                        {{-- Advanced --}}
                        <details class="group rounded-2xl border border-border bg-surface-soft">

                            <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-4">

                                <div>
                                    <div class="text-sm font-bold text-content-soft">
                                        تنظیمات پیشرفته موقعیت
                                    </div>

                                    <div class="mt-1 text-[11px] text-content-faint">
                                        فقط اگر مختصات دقیق سالن را دارید تغییر دهید.
                                    </div>
                                </div>

                                <svg
                                    class="h-5 w-5 text-content-muted transition group-open:rotate-180"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>

                            </summary>


                            <div class="grid gap-5 border-t border-border p-4 sm:grid-cols-2">

                                <div>

                                    <label
                                        for="latitude"
                                        class="mb-2 block text-xs font-bold text-content-soft"
                                    >
                                        Latitude
                                    </label>

                                    <input
                                        id="latitude"
                                        type="text"
                                        name="latitude"
                                        dir="ltr"
                                        value="{{ old('latitude', $salon->latitude) }}"
                                        placeholder="35.7219"
                                        class="block w-full rounded-xl border border-border bg-surface px-3.5 py-3 text-left text-sm text-content outline-none focus:border-info-400 focus:ring-4 focus:ring-info-500/10"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="longitude"
                                        class="mb-2 block text-xs font-bold text-content-soft"
                                    >
                                        Longitude
                                    </label>

                                    <input
                                        id="longitude"
                                        type="text"
                                        name="longitude"
                                        dir="ltr"
                                        value="{{ old('longitude', $salon->longitude) }}"
                                        placeholder="51.3347"
                                        class="block w-full rounded-xl border border-border bg-surface px-3.5 py-3 text-left text-sm text-content outline-none focus:border-info-400 focus:ring-4 focus:ring-info-500/10"
                                    >

                                </div>

                            </div>

                        </details>

                    </div>

                </section>


                {{-- =====================================================
                    4. WORKING HOURS
                ====================================================== --}}
                <section id="settings-hours" class="mb-6 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm">

                    <div class="border-b border-border px-5 py-5 sm:px-7">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="8.5"/>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 7v5l3 2"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-black text-content">
                                    ساعات کاری
                                </h2>

                                <p class="mt-1 text-xs leading-6 text-content-muted">
                                    مشخص کنید هر روز چه ساعتی مشتری می‌تواند برای شما نوبت بگیرد.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="p-4 sm:p-6">

                        <div class="space-y-3">

                            @foreach ($days as $day => $dayName)

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
                                    x-data="{
                                        closed: {{ $closed ? 'true' : 'false' }}
                                        }"
                                    class="rounded-2xl border border-border bg-surface-soft p-4 transition"
                                    :class="closed ? 'opacity-75' : ''"
                                >

                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                        {{-- Day --}}
                                        <div class="flex items-center gap-3 sm:w-32">

                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-black"
                                                :class="
                                                    closed
                                                        ? 'bg-slate-200 text-slate-500'
                                                        : 'bg-success-100 text-success-700'
                                                "
                                            >
                                                {{ mb_substr($dayName, 0, 1) }}
                                            </div>

                                            <div>

                                                <div class="text-sm font-black text-content-soft">
                                                    {{ $dayName }}
                                                </div>

                                                <div
                                                    class="mt-0.5 text-[10px] font-bold"
                                                    :class="
                                                        closed
                                                            ? 'text-content-faint'
                                                            : 'text-success-600'
                                                    "
                                                    x-text="
                                                        closed
                                                            ? 'تعطیل'
                                                            : 'باز است'
                                                    "
                                                ></div>

                                            </div>

                                        </div>


                                        {{-- Times --}}
                                        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">

                                            <div class="flex items-center gap-2">

                                                <span class="text-[11px] font-bold text-content-faint">
                                                    از
                                                </span>

                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][start_time]"
                                                    value="{{ $startTime }}"
                                                    :disabled="closed"
                                                    class="rounded-xl border border-border bg-surface px-3 py-2.5 text-sm font-bold text-content-soft outline-none transition focus:border-warning-400 focus:ring-4 focus:ring-warning-500/10 disabled:cursor-not-allowed disabled:bg-surface-soft disabled:text-content-faint"
                                                >

                                            </div>


                                            <div class="flex items-center gap-2">

                                                <span class="text-[11px] font-bold text-content-faint">
                                                    تا
                                                </span>

                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][end_time]"
                                                    value="{{ $endTime }}"
                                                    :disabled="closed"
                                                    class="rounded-xl border border-border bg-surface px-3 py-2.5 text-sm font-bold text-content-soft outline-none transition focus:border-warning-400 focus:ring-4 focus:ring-warning-500/10 disabled:cursor-not-allowed disabled:bg-surface-soft disabled:text-content-faint"
                                                >

                                            </div>

                                        </div>


                                        {{-- Toggle --}}
                                        <div class="flex items-center justify-between gap-3 border-t border-border pt-3 sm:w-28 sm:border-0 sm:pt-0">

                                            <span class="text-xs font-bold text-content-muted sm:hidden">
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
                                                class="relative h-7 w-12 shrink-0 rounded-full transition focus:outline-none focus:ring-4 focus:ring-success-500/20"
                                                :class="
                                                    closed
                                                        ? 'bg-slate-300'
                                                        : 'bg-success-500'
                                                "
                                                :aria-pressed="!closed"
                                                :aria-label="'وضعیت ' + @js($dayName)"
                                            >
                                                <span
                                                    class="absolute top-1 h-5 w-5 rounded-full bg-white shadow-sm transition"
                                                    :class="
                                                        closed
                                                            ? 'right-1'
                                                            : 'right-6'
                                                    "
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


                        <div class="mt-5 rounded-2xl border border-warning-100 bg-warning-50 px-4 py-3">

                            <div class="flex gap-3">

                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-warning-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path
                                        stroke-linecap="round"
                                        d="M12 11v5m0-8h.01"
                                    />
                                </svg>

                                <p class="text-xs leading-6 text-warning-700">
                                    زمان‌هایی که اینجا مشخص می‌کنید روی امکان رزرو مشتری تأثیر می‌گذارد.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =====================================================
                    5. BRAND COLORS
                ====================================================== --}}
                <section class="mb-6 overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm">

                    <details class="group">

                        <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-5 sm:px-7">

                            <div class="flex items-center gap-4">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="12" cy="12" r="8.5"/>
                                        <path
                                            stroke-linecap="round"
                                            d="M12 3.5v17M3.5 12h17"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-sm font-black text-content">
                                        ظاهر و رنگ‌بندی
                                    </h2>

                                    <p class="mt-1 text-xs text-content-muted">
                                        رنگ اصلی و دوم برند سالن را تنظیم کنید.
                                    </p>
                                </div>

                            </div>


                            <svg
                                class="h-5 w-5 text-content-muted transition group-open:rotate-180"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="m6 9 6 6 6-6"/>
                            </svg>

                        </summary>


                        <div class="border-t border-border p-5 sm:p-7">

                            <div class="grid gap-5 sm:grid-cols-2">

                                {{-- Primary --}}
                                <div>

                                    <label
                                        for="primary_color"
                                        class="mb-2 block text-sm font-bold text-content"
                                    >
                                        رنگ اصلی
                                    </label>

                                    <div class="flex items-center gap-3">

                                        <input
                                            id="primary_color_picker"
                                            type="color"
                                            :value="primaryColor"
                                            @input="
                                                primaryColor = $event.target.value;
                                            "
                                            class="h-12 w-14 cursor-pointer rounded-xl border border-border bg-surface p-1"
                                        >

                                        <input
                                            id="primary_color"
                                            type="text"
                                            name="primary_color"
                                            x-model="primaryColor"
                                            dir="ltr"
                                            maxlength="9"
                                            placeholder="#6757E8"
                                            class="flex-1 rounded-xl border border-border bg-surface-soft px-4 py-3 text-left text-sm font-bold text-content outline-none focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                                        >

                                    </div>

                                    @error('primary_color')
                                    <p class="mt-2 text-xs font-semibold text-danger-600">
                                        {{ $message }}
                                    </p>
                                    @enderror

                                </div>


                                {{-- Secondary --}}
                                <div>

                                    <label
                                        for="secondary_color"
                                        class="mb-2 block text-sm font-bold text-content"
                                    >
                                        رنگ دوم
                                    </label>

                                    <div class="flex items-center gap-3">

                                        <input
                                            id="secondary_color_picker"
                                            type="color"
                                            :value="secondaryColor"
                                            @input="
                                                secondaryColor = $event.target.value;
                                            "
                                            class="h-12 w-14 cursor-pointer rounded-xl border border-border bg-surface p-1"
                                        >

                                        <input
                                            id="secondary_color"
                                            type="text"
                                            name="secondary_color"
                                            x-model="secondaryColor"
                                            dir="ltr"
                                            maxlength="9"
                                            placeholder="#37B8C8"
                                            class="flex-1 rounded-xl border border-border bg-surface-soft px-4 py-3 text-left text-sm font-bold text-content outline-none focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                                        >

                                    </div>

                                    @error('secondary_color')
                                    <p class="mt-2 text-xs font-semibold text-danger-600">
                                        {{ $message }}
                                    </p>
                                    @enderror

                                </div>

                            </div>


                            {{-- Color preview --}}
                            <div class="mt-6">

                                <div class="mb-3 flex items-center justify-between">

                                    <span class="text-xs font-black text-content-soft">
                                        نمای رنگ‌ها
                                    </span>

                                    <span
                                        class="text-[10px] font-bold text-content-faint"
                                        x-text="primaryColor + ' / ' + secondaryColor"
                                    ></span>

                                </div>


                                <div class="overflow-hidden rounded-2xl border border-border">

                                    <div
                                        class="h-3"
                                        :style="{
                                            background: `linear-gradient(90deg, ${primaryColor}, ${secondaryColor})`
                                        }"
                                    ></div>

                                    <div class="grid grid-cols-2 gap-px bg-border">

                                        <div
                                            class="h-20"
                                            :style="{
                                                backgroundColor: primaryColor
                                            }"
                                        ></div>

                                        <div
                                            class="h-20"
                                            :style="{
                                                backgroundColor: secondaryColor
                                            }"
                                        ></div>

                                    </div>

                                </div>

                            </div>


                            <div class="mt-5 rounded-2xl bg-surface-soft p-4">

                                <p class="text-xs leading-6 text-content-muted">
                                    رنگ‌ها در صفحه عمومی سالن روی دکمه‌ها، وضعیت‌ها، المان‌های برند و بخش‌های مختلف استفاده خواهند شد.
                                </p>

                            </div>

                        </div>

                    </details>

                </section>


                {{-- =====================================================
                    SAVE
                ====================================================== --}}
                <div class="sticky bottom-4 z-30">

                    <div class="flex flex-col gap-3 rounded-3xl border border-border bg-surface/95 p-3 shadow-float backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:p-4">

                        <div class="hidden items-center gap-3 px-2 sm:flex">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-success-50 text-success-600">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>
                            </div>

                            <div>

                                <div class="text-xs font-black text-content-soft">
                                    همه چیز آماده است؟
                                </div>

                                <div class="text-[11px] text-content-faint">
                                    تغییرات را ذخیره کنید.
                                </div>

                            </div>

                        </div>


                        <div class="flex gap-2 sm:mr-auto">

                            <a
                                href="{{ route('salon.dashboard') }}"
                                class="flex-1 rounded-2xl border border-border bg-surface px-5 py-3.5 text-center text-sm font-bold text-content-soft transition hover:bg-surface-soft sm:flex-none"
                            >
                                انصراف
                            </a>


                            <button
                                type="submit"
                                class="flex-1 rounded-2xl bg-accent-600 px-7 py-3.5 text-sm font-black text-white shadow-lg shadow-accent-600/20 transition hover:bg-accent-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-accent-500/20 sm:flex-none"
                            >
                                ذخیره تغییرات
                            </button>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    CROP MODAL
                ====================================================== --}}
                <div
                    x-show="cropOpen"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm sm:p-5"
                    role="dialog"
                    aria-modal="true"
                    aria-label="ویرایش تصویر"
                    @keydown.escape.window="cancelCrop()"
                >

                    <div
                        x-show="cropOpen"
                        x-transition.scale.origin.center
                        class="w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white/10 bg-surface shadow-float"
                        @click.stop
                    >

                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between border-b border-border px-5 py-4 sm:px-6">

                            <div>

                                <div class="text-[10px] font-black uppercase tracking-[0.18em] text-accent-600">
                                    IMAGE EDITOR
                                </div>

                                <h3 class="mt-1 text-base font-black text-content">
                                    <span
                                        x-text="
                                            cropType === 'logo'
                                                ? 'برش لوگو'
                                                : 'برش عکس اصلی'
                                        "
                                    ></span>
                                </h3>

                            </div>


                            <button
                                type="button"
                                @click="cancelCrop()"
                                class="flex h-10 w-10 items-center justify-center rounded-xl text-content-muted transition hover:bg-surface-soft hover:text-content"
                                aria-label="بستن"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 6 12 12"/>
                                    <path d="m18 6-12 12"/>
                                </svg>
                            </button>

                        </div>


                        {{-- Crop Area --}}
                        <div class="bg-slate-950 p-3 sm:p-6">

                            <div
                                class="mx-auto overflow-hidden rounded-2xl bg-slate-900"
                                :class="
                                    cropType === 'logo'
                                        ? 'aspect-square max-w-[560px]'
                                        : 'aspect-[16/7] max-w-[900px]'
                                "
                            >

                                <div
                                    x-ref="cropViewport"
                                    class="relative h-full w-full select-none overflow-hidden touch-none"
                                    :class="cropDragging ? 'cursor-grabbing' : 'cursor-grab'"
                                    @pointerdown="startCropDrag($event)"
                                    @pointermove="dragCrop($event)"
                                    @pointerup="endCropDrag()"
                                    @pointercancel="endCropDrag()"
                                    @pointerleave="endCropDrag()"
                                >

                                    <img
                                        x-ref="cropImage"
                                        :src="cropSrc"
                                        alt="تصویر در حال ویرایش"
                                        draggable="false"
                                        class="absolute max-w-none"
                                        @load="initializeCrop()"
                                        :style="`
                                            width: ${$refs.cropImage?.naturalWidth * cropScale || 0}px;
                                            height: ${$refs.cropImage?.naturalHeight * cropScale || 0}px;
                                            transform: translate(${cropX}px, ${cropY}px);
                                            transform-origin: top left;
                                        `"
                                    >


                                    {{-- Grid --}}
                                    <div class="pointer-events-none absolute inset-0">

                                        <div class="absolute inset-y-0 left-1/3 w-px bg-white/20"></div>
                                        <div class="absolute inset-y-0 left-2/3 w-px bg-white/20"></div>

                                        <div class="absolute inset-x-0 top-1/3 h-px bg-white/20"></div>
                                        <div class="absolute inset-x-0 top-2/3 h-px bg-white/20"></div>

                                    </div>


                                    {{-- Border --}}
                                    <div class="pointer-events-none absolute inset-0 border-2 border-white/90"></div>


                                    {{-- Corners --}}
                                    <span class="pointer-events-none absolute left-0 top-0 h-6 w-6 border-l-2 border-t-2 border-white"></span>
                                    <span class="pointer-events-none absolute right-0 top-0 h-6 w-6 border-r-2 border-t-2 border-white"></span>
                                    <span class="pointer-events-none absolute bottom-0 left-0 h-6 w-6 border-b-2 border-l-2 border-white"></span>
                                    <span class="pointer-events-none absolute bottom-0 right-0 h-6 w-6 border-b-2 border-r-2 border-white"></span>

                                </div>

                            </div>


                            <div class="mx-auto mt-3 max-w-2xl text-center text-[11px] leading-6 text-white/60">
                                تصویر را با موس یا لمس جابه‌جا کنید و با نوار پایین بزرگ‌نمایی را تنظیم کنید.
                            </div>

                        </div>


                        {{-- Modal Controls --}}
                        <div class="border-t border-border px-5 py-4 sm:px-6">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-center gap-3">

                                    <span class="text-xs font-bold text-content-muted">
                                        بزرگ‌نمایی
                                    </span>

                                    <input
                                        type="range"
                                        min="100"
                                        max="300"
                                        step="1"
                                        :value="cropZoom"
                                        @input="setZoom($event.target.value)"
                                        class="w-36 accent-accent-600 sm:w-48"
                                    >

                                    <span
                                        class="w-12 text-center text-xs font-black text-content"
                                        x-text="`${Math.round(cropZoom)}٪`"
                                    ></span>

                                </div>


                                <div
                                    x-show="cropError"
                                    x-cloak
                                    class="text-xs font-bold text-danger-600 sm:ml-auto"
                                >
                                    <span x-text="cropError"></span>
                                </div>


                                <div class="flex gap-2">

                                    <button
                                        type="button"
                                        @click="cancelCrop()"
                                        class="rounded-xl border border-border bg-surface px-4 py-2.5 text-xs font-bold text-content-soft transition hover:bg-surface-soft"
                                    >
                                        انصراف
                                    </button>

                                    <button
                                        type="button"
                                        @click="confirmCrop()"
                                        class="rounded-xl bg-accent-600 px-5 py-2.5 text-xs font-black text-white transition hover:bg-accent-700 focus:outline-none focus:ring-4 focus:ring-accent-500/20"
                                    >
                                        تأیید برش
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </form>


            {{-- =========================================================
                BOTTOM HINT
            ========================================================== --}}
            <div class="pb-10 pt-6 text-center">

                <p class="text-[11px] leading-6 text-content-faint">
                    اطلاعات این صفحه مربوط به پروفایل، برند، موقعیت و ساعات کاری سالن شماست.
                </p>

            </div>

        </div>
    </div>


@push('scripts')
<script>
(() => {
    const page = document.querySelector('[data-settings-page]');
    if (!page) return;

    const tabs = [...page.querySelectorAll('[data-settings-tab]')];
    const sections = tabs
        .map(tab => document.getElementById(tab.dataset.settingsTab))
        .filter(Boolean);

    const activate = id => {
        tabs.forEach(tab => {
            const active = tab.dataset.settingsTab === id;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-current', active ? 'location' : 'false');
        });
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => activate(tab.dataset.settingsTab));
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            const visible = entries
                .filter(entry => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
            if (visible) activate(visible.target.id);
        }, { rootMargin: '-24% 0px -60% 0px', threshold: [0.05, 0.25, 0.6] });

        sections.forEach(section => observer.observe(section));
    }
})();
</script>
@endpush

@endsection
