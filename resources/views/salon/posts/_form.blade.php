@php
    $isEdit = ($mode ?? 'create') === 'edit';

    $postModel = $post ?? null;

    $currentType = $postModel?->type instanceof \App\Enums\PostType
        ? $postModel->type->value
        : (string) ($postModel?->type ?? '');

    $oldType = old(
        'type',
        $currentType
    );

    $oldPerformedByOwner = old(
        'performed_by_owner',
        $postModel?->performed_by_owner ?? true
    );

    $oldIsActive = old(
        'is_active',
        $postModel?->is_active ?? true
    );

    $mediaUrl = $postModel?->media_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url(
            $postModel->media_path
        )
        : null;

    $thumbnailUrl = $postModel?->thumbnail_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url(
            $postModel->thumbnail_path
        )
        : null;
@endphp

<div
    id="salonPostEditor"
    class="mx-auto w-full max-w-5xl pb-28"
    data-mode="{{ $isEdit ? 'edit' : 'create' }}"
    data-current-type="{{ $currentType }}"
    dir="rtl"
>

    {{-- ==========================================================
         HEADER
    =========================================================== --}}
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <div class="mb-2 text-[10px] font-black uppercase tracking-[0.22em] text-content-faint">
                SALON CONTENT
            </div>

            <h1 class="text-2xl font-black tracking-tight text-content sm:text-3xl">
                {{ $isEdit ? 'ویرایش پست' : 'افزودن پست' }}
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-7 text-content-muted">
                عکس، GIF، ویدیو یا ریلز خودت را آپلود کن.
                نوع فایل توسط سیستم تشخیص داده می‌شود و قبل از ذخیره دوباره اعتبارسنجی خواهد شد.
            </p>

        </div>

        <a
            href="{{ route('salon.posts.index') }}"
            class="btn btn-secondary shrink-0"
        >
            ← بازگشت به پست‌ها
        </a>

    </div>


    {{-- ==========================================================
         ERRORS
    =========================================================== --}}
    @if ($errors->any())

        <div class="alert alert-danger mb-6">

            <div class="font-black">
                اطلاعات واردشده نیاز به بررسی دارد.
            </div>

            <ul class="mt-2 list-disc space-y-1 pr-5 text-sm">

                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- ==========================================================
         FORM
    =========================================================== --}}
    <form
        id="salonPostForm"
        action="{{ $isEdit
            ? route('salon.posts.update', $postModel)
            : route('salon.posts.store')
        }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif


        {{-- ======================================================
             TYPE
        ======================================================= --}}
        <section class="ui-card overflow-hidden">

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                    <div>

                        <h2 class="text-base font-black text-content">
                            نوع محتوا
                        </h2>

                        <p class="mt-1 text-sm text-content-muted">
                            با انتخاب فایل، سیستم نوع مناسب را به صورت خودکار پیشنهاد می‌دهد.
                        </p>

                    </div>


                    <div
                        id="detectedTypeBadge"
                        class="inline-flex w-fit items-center gap-2 rounded-full bg-surface-soft px-3 py-1.5 text-[10px] font-black text-content-muted"
                    >
                        هنوز فایلی انتخاب نشده
                    </div>

                </div>

            </div>


            <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">

                @foreach ($types as $type)

                    @php
                        $typeValue = $type->value;

                        $meta = match ($typeValue) {

                            'image' => [
                                'label' => 'عکس',
                                'description' => 'JPG / PNG / WEBP',
                                'icon' => '▧',
                            ],

                            'gif' => [
                                'label' => 'GIF',
                                'description' => 'GIF متحرک',
                                'icon' => 'GIF',
                            ],

                            'video' => [
                                'label' => 'ویدیو',
                                'description' => 'ویدیوی معمولی',
                                'icon' => '▶',
                            ],

                            'reel' => [
                                'label' => 'ریلز',
                                'description' => 'ویدیوی عمودی',
                                'icon' => '◎',
                            ],

                            default => [
                                'label' => $typeValue,
                                'description' => '',
                                'icon' => '•',
                            ],
                        };
                    @endphp


                    <label class="block cursor-pointer">

                        <input
                            type="radio"
                            name="type"
                            value="{{ $typeValue }}"
                            class="post-type-input peer sr-only"
                            @checked($oldType === $typeValue)
                        >

                        <div
                            class="h-full rounded-2xl border border-border bg-surface p-4 transition
                                   hover:border-primary/40 hover:bg-surface-soft
                                   peer-checked:border-primary
                                   peer-checked:bg-primary/5
                                   peer-checked:ring-2
                                   peer-checked:ring-primary/10"
                        >

                            <div class="flex items-start gap-3">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-soft text-sm font-black text-content"
                                >
                                    {{ $meta['icon'] }}
                                </div>

                                <div class="min-w-0">

                                    <div class="font-black text-content">
                                        {{ $meta['label'] }}
                                    </div>

                                    <div class="mt-1 text-xs text-content-muted">
                                        {{ $meta['description'] }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </label>

                @endforeach

            </div>

            <div class="border-t border-border bg-surface-soft/40 px-5 py-4 sm:px-6">

                <div class="flex items-start gap-3 text-xs leading-6 text-content-muted">

                    <span class="mt-0.5 text-primary">
                        ✦
                    </span>

                    <p>
                        نوع عکس، GIF و ویدیو از روی فایل تشخیص داده می‌شود.
                        برای ویدیوهای عمودی، سیستم «ریلز» را پیشنهاد می‌دهد.
                        انتخاب دستی همچنان امکان‌پذیر است.
                    </p>

                </div>

            </div>

        </section>


        {{-- ======================================================
             MEDIA
        ======================================================= --}}
        <section class="ui-card overflow-hidden">

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                    <div>

                        <h2 class="text-base font-black text-content">
                            فایل رسانه
                        </h2>

                        <p
                            id="mediaHelpText"
                            class="mt-1 text-sm leading-6 text-content-muted"
                        >
                            یک فایل انتخاب کن؛ سیستم نوع و پیش‌نمایش آن را مشخص می‌کند.
                        </p>

                    </div>

                    <div class="inline-flex w-fit rounded-full bg-surface-soft px-3 py-1.5 text-[10px] font-black text-content-muted">
                        حداکثر 50MB
                    </div>

                </div>

            </div>


            <div class="space-y-5 p-5 sm:p-6">

                <input
                    id="mediaInput"
                    name="media"
                    type="file"
                    class="hidden"
                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                >


                <label
                    for="mediaInput"
                    id="mediaDropzone"
                    class="group flex min-h-[240px] cursor-pointer items-center justify-center rounded-3xl border-2 border-dashed border-border bg-surface-soft/60 p-6 text-center transition hover:border-primary/50 hover:bg-primary/5"
                >

                    <div class="w-full max-w-lg">

                        <div
                            id="uploadIcon"
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-surface text-2xl font-black text-primary shadow-sm"
                        >
                            +
                        </div>


                        <div
                            id="uploadTitle"
                            class="mt-4 text-sm font-black text-content"
                        >
                            فایل را انتخاب کن
                        </div>


                        <div
                            id="uploadDescription"
                            class="mt-2 text-xs leading-6 text-content-muted"
                        >
                            JPG, PNG, WEBP, GIF, MP4, WEBM, MOV
                            <br>
                            حداکثر حجم 50MB
                        </div>


                        <div
                            id="selectedFileName"
                            class="mx-auto mt-4 hidden max-w-md truncate rounded-xl bg-surface px-4 py-2.5 text-xs font-bold text-content"
                        ></div>

                    </div>

                </label>


                @error('media')
                <div class="text-sm font-bold text-danger">
                    {{ $message }}
                </div>
                @enderror


                {{-- Preview --}}
                <div
                    id="mediaPreviewSection"
                    class="{{ $mediaUrl ? '' : 'hidden' }}"
                >

                    <div class="mb-3 flex items-center justify-between gap-3">

                        <div>

                            <div class="text-sm font-black text-content">
                                پیش‌نمایش
                            </div>

                            <div
                                id="previewTypeText"
                                class="mt-1 text-xs text-content-muted"
                            >
                                {{ $currentType ?: '—' }}
                            </div>

                        </div>


                        <button
                            type="button"
                            id="removeMediaBtn"
                            class="btn btn-ghost btn-sm"
                        >
                            حذف فایل جدید
                        </button>

                    </div>


                    <div
                        id="previewContainer"
                        class="overflow-hidden rounded-3xl border border-border bg-black/5"
                    >

                        @if($mediaUrl)

                            @if(in_array($currentType, ['image', 'gif'], true))

                                <img
                                    src="{{ $mediaUrl }}"
                                    alt="{{ $postModel?->title ?: 'پست سالن' }}"
                                    class="mx-auto max-h-[520px] w-full object-contain"
                                >

                            @elseif(in_array($currentType, ['video', 'reel'], true))

                                <video
                                    src="{{ $mediaUrl }}"
                                    @if($thumbnailUrl)
                                    poster="{{ $thumbnailUrl }}"
                                    @endif
                                    controls
                                    muted
                                    playsinline
                                    preload="metadata"
                                    class="mx-auto max-h-[560px] w-full object-contain"
                                ></video>

                            @endif

                        @endif

                    </div>

                </div>


                {{-- Existing file notice --}}
                @if($isEdit && $mediaUrl)

                    <div class="rounded-2xl border border-border bg-surface-soft px-4 py-3">

                        <div class="text-xs font-bold text-content">
                            فایل فعلی
                        </div>

                        <div class="mt-1 truncate text-[11px] text-content-muted">
                            {{ $postModel->media_path }}
                        </div>

                    </div>

                @endif

            </div>

        </section>


        {{-- ======================================================
             THUMBNAIL
        ======================================================= --}}
        <section
            id="thumbnailSection"
            class="{{ in_array($oldType, ['video', 'reel'], true) ? '' : 'hidden' }} ui-card overflow-hidden"
        >

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <h2 class="text-base font-black text-content">
                    کاور ویدیو
                </h2>

                <p class="mt-1 text-sm leading-6 text-content-muted">
                    برای ویدیو و ریلز یک کاور انتخاب کن. این بخش اختیاری است.
                </p>

            </div>


            <div class="space-y-4 p-5 sm:p-6">

                <input
                    id="thumbnailInput"
                    name="thumbnail"
                    type="file"
                    class="hidden"
                    accept="image/jpeg,image/png,image/webp"
                >


                <label
                    for="thumbnailInput"
                    class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border-2 border-dashed border-border bg-surface-soft p-4 transition hover:border-primary/50 hover:bg-primary/5"
                >

                    <div class="min-w-0">

                        <div class="text-sm font-black text-content">
                            انتخاب تصویر کاور
                        </div>

                        <div class="mt-1 text-xs text-content-muted">
                            JPG / PNG / WEBP — حداکثر 5MB
                        </div>

                        <div
                            id="thumbnailFileName"
                            class="mt-2 hidden truncate text-xs font-bold text-primary"
                        ></div>

                    </div>

                    <div class="shrink-0 rounded-xl bg-surface px-4 py-2 text-xs font-black text-content">
                        انتخاب
                    </div>

                </label>


                @error('thumbnail')
                <div class="text-sm font-bold text-danger">
                    {{ $message }}
                </div>
                @enderror


                <div
                    id="thumbnailPreview"
                    class="{{ $thumbnailUrl ? '' : 'hidden' }} overflow-hidden rounded-2xl border border-border bg-black/5"
                >

                    @if($thumbnailUrl)

                        <img
                            src="{{ $thumbnailUrl }}"
                            alt="کاور"
                            class="max-h-80 w-full object-contain"
                        >

                    @endif

                </div>

            </div>

        </section>


        {{-- ======================================================
             DETAILS
        ======================================================= --}}
        <section class="ui-card overflow-hidden">

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <h2 class="text-base font-black text-content">
                    اطلاعات پست
                </h2>

                <p class="mt-1 text-sm text-content-muted">
                    اطلاعاتی که در صفحه عمومی کنار محتوا نمایش داده می‌شود.
                </p>

            </div>


            <div class="grid gap-5 p-5 sm:p-6 md:grid-cols-2">

                <div>

                    <label
                        for="title"
                        class="form-label"
                    >
                        عنوان
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $postModel?->title) }}"
                        maxlength="150"
                        placeholder="مثلاً مدل جدید کوتاهی مو"
                        class="form-control"
                    >

                </div>


                <div>

                    <label
                        for="service_id"
                        class="form-label"
                    >
                        خدمت مرتبط
                    </label>

                    <select
                        id="service_id"
                        name="service_id"
                        class="form-control"
                    >

                        <option value="">
                            بدون خدمت
                        </option>

                        @foreach($services as $service)

                            <option
                                value="{{ $service->id }}"
                                @selected(
                                (string) old(
                            'service_id',
                            $postModel?->service_id
                            ) === (string) $service->id
                            )
                            >
                            {{ $service->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="md:col-span-2">

                    <label
                        for="caption"
                        class="form-label"
                    >
                        کپشن
                    </label>

                    <textarea
                        id="caption"
                        name="caption"
                        rows="5"
                        maxlength="5000"
                        placeholder="توضیح کوتاه درباره این نمونه‌کار..."
                        class="form-control min-h-[140px] resize-y"
                    >{{ old('caption', $postModel?->caption) }}</textarea>

                    <div class="mt-2 text-[11px] text-content-muted">
                        حداکثر ۵۰۰۰ کاراکتر
                    </div>

                </div>

            </div>

        </section>


        {{-- ======================================================
             PERFORMER
        ======================================================= --}}
        <section class="ui-card overflow-hidden">

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <h2 class="text-base font-black text-content">
                    انجام‌دهنده
                </h2>

                <p class="mt-1 text-sm text-content-muted">
                    مشخص کن نمونه‌کار توسط خودت انجام شده یا یکی از اعضای تیم.
                </p>

            </div>


            <div class="space-y-5 p-5 sm:p-6">

                <input
                    type="hidden"
                    name="performed_by_owner"
                    value="0"
                >


                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-border bg-surface-soft p-4">

                    <input
                        id="performedByOwner"
                        type="checkbox"
                        name="performed_by_owner"
                        value="1"
                        class="h-5 w-5 rounded border-border text-primary focus:ring-primary"
                        @checked((bool) $oldPerformedByOwner)
                    >

                    <div>

                        <div class="text-sm font-black text-content">
                            توسط خودم انجام شده
                        </div>

                        <div class="mt-1 text-xs text-content-muted">
                            اگر صاحب سالن این کار را انجام داده، فعالش کن.
                        </div>

                    </div>

                </label>


                <div id="barberFieldWrapper">

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
                            انتخاب آرایشگر
                        </option>

                        @foreach($barbers as $barber)

                            <option
                                value="{{ $barber->id }}"
                                @selected(
                                (string) old(
                            'barber_id',
                            $postModel?->barber_id
                            ) === (string) $barber->id
                            )
                            >
                            {{ $barber->name }}

                            @if($barber->specialty)
                                — {{ $barber->specialty }}
                                @endif

                                </option>

                                @endforeach

                    </select>

                </div>

            </div>

        </section>


        {{-- ======================================================
             PUBLISHING
        ======================================================= --}}
        <section class="ui-card overflow-hidden">

            <div class="border-b border-border px-5 py-5 sm:px-6">

                <h2 class="text-base font-black text-content">
                    انتشار
                </h2>

            </div>


            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">

                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-border bg-surface-soft p-4">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="h-5 w-5 rounded border-border text-primary focus:ring-primary"
                        @checked((bool) $oldIsActive)
                    >

                    <div>

                        <div class="text-sm font-black text-content">
                            منتشر شود
                        </div>

                        <div class="mt-1 text-xs text-content-muted">
                            این محتوا در صفحه عمومی سالن نمایش داده شود.
                        </div>

                    </div>

                </label>


                <div>

                    <label
                        for="sort_order"
                        class="form-label"
                    >
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        max="4294967295"
                        value="{{ old('sort_order', $postModel?->sort_order ?? 0) }}"
                        class="form-control"
                    >

                </div>

            </div>

        </section>


        {{-- ======================================================
             SUBMIT
        ======================================================= --}}
        <div class="sticky bottom-3 z-20">

            <div class="flex flex-col gap-3 rounded-3xl border border-border bg-white/95 p-3 shadow-xl backdrop-blur-xl dark:bg-primary-950/95 sm:flex-row sm:items-center sm:justify-between sm:p-4">

                <div class="px-2 text-xs leading-6 text-content-muted">

                    <span class="font-black text-content">
                        آماده انتشار؟
                    </span>

                    فایل قبل از ذخیره توسط سرور دوباره بررسی می‌شود.

                </div>


                <div class="flex gap-2">

                    <a
                        href="{{ route('salon.posts.index') }}"
                        class="btn btn-secondary"
                    >
                        انصراف
                    </a>

                    <button
                        type="submit"
                        id="submitPostBtn"
                        class="btn btn-accent"
                    >
                        {{ $isEdit ? 'ذخیره تغییرات' : 'انتشار پست' }}
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

@vite('resources/js/salon-posts.js')
