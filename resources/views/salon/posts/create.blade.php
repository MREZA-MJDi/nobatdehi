@extends('layouts.salon')

@section('title', 'افزودن پست')

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;

        $oldType = old('type', \App\Enums\PostType::PHOTO->value);
        $oldPerformedByOwner = old('performed_by_owner', '1');
        $oldIsActive = old('is_active', '1');

        $typeMeta = [
            \App\Enums\PostType::PHOTO->value => [
                'label' => 'عکس',
                'description' => 'تصویر ثابت',
                'icon' => '▧',
            ],
            \App\Enums\PostType::VIDEO->value => [
                'label' => 'ویدیو',
                'description' => 'ویدیو معمولی',
                'icon' => '▶',
            ],
            \App\Enums\PostType::GIF->value => [
                'label' => 'GIF',
                'description' => 'تصویر متحرک',
                'icon' => 'GIF',
            ],
            \App\Enums\PostType::REEL->value => [
                'label' => 'ریلز',
                'description' => 'ویدیوی عمودی',
                'icon' => '▮',
            ],
        ];
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="mb-1 text-sm font-medium text-content-muted">
                    مدیریت محتوا
                </div>

                <h1 class="text-2xl font-black tracking-tight text-content sm:text-3xl">
                    افزودن پست
                </h1>

                <p class="mt-2 text-sm leading-6 text-content-muted">
                    نمونه‌کار، عکس، ویدیو یا ریلز سالن خودت را منتشر کن.
                </p>
            </div>

            <a
                href="{{ route('salon.posts.index') }}"
                class="btn btn-secondary"
            >
                ← بازگشت به پست‌ها
            </a>

        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="font-bold">
                    اطلاعات واردشده نیاز به بررسی دارد.
                </div>

                <ul class="mt-2 list-disc space-y-1 pr-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="postCreateForm"
            action="{{ route('salon.posts.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf

            {{-- =========================
                 TYPE
            ========================== --}}
            <section class="ui-card overflow-hidden">

                <div class="border-b border-border px-5 py-5 sm:px-6">
                    <h2 class="text-base font-black text-content">
                        نوع پست
                    </h2>

                    <p class="mt-1 text-sm text-content-muted">
                        نوع محتوایی که می‌خواهی منتشر کنی را انتخاب کن.
                    </p>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">

                    @foreach ($types as $type)
                        @php
                            $typeValue = $type->value;
                            $meta = $typeMeta[$typeValue] ?? [
                                'label' => $typeValue,
                                'description' => '',
                                'icon' => '•',
                            ];
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
                                class="rounded-2xl border border-border bg-surface p-4 transition
                                       hover:border-primary/40 hover:bg-surface-soft
                                       peer-checked:border-primary
                                       peer-checked:bg-primary/5
                                       peer-checked:ring-2
                                       peer-checked:ring-primary/10"
                            >
                                <div class="flex items-start gap-3">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center
                                               rounded-xl bg-surface-soft text-sm font-black
                                               text-content"
                                    >
                                        {{ $meta['icon'] }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="font-bold text-content">
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

                @error('type')
                <div class="px-5 pb-5 text-sm text-danger sm:px-6">
                    {{ $message }}
                </div>
                @enderror

            </section>

            {{-- =========================
                 MEDIA
            ========================== --}}
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
                                یک عکس انتخاب کن. بعد از انتخاب می‌توانی آن را برش بدهی.
                            </p>
                        </div>

                        <div
                            id="mediaSizeBadge"
                            class="inline-flex w-fit rounded-full bg-surface-soft px-3 py-1 text-xs font-bold text-content-muted"
                        >
                            حداکثر 50MB
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-5 sm:p-6">

                    {{-- hidden original file input --}}
                    <input
                        id="mediaInput"
                        name="media"
                        type="file"
                        class="hidden"
                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                    >

                    {{-- uploader --}}
                    <label
                        for="mediaInput"
                        id="mediaDropzone"
                        class="group flex min-h-[220px] cursor-pointer items-center justify-center
                               rounded-3xl border-2 border-dashed border-border
                               bg-surface-soft/70 p-6 text-center transition
                               hover:border-primary/50 hover:bg-primary/5"
                    >
                        <div class="max-w-md">

                            <div
                                id="uploadIcon"
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl
                                       bg-surface text-2xl font-black text-primary
                                       shadow-sm"
                            >
                                +
                            </div>

                            <div class="mt-4 text-sm font-black text-content">
                                انتخاب فایل
                            </div>

                            <div class="mt-2 text-xs leading-6 text-content-muted">
                                JPG, PNG, WEBP, GIF, MP4, WEBM, MOV
                                <br>
                                حداکثر حجم: 50MB
                            </div>

                            <div
                                id="selectedFileName"
                                class="mt-4 hidden truncate rounded-xl bg-surface px-3 py-2
                                       text-xs font-semibold text-content"
                            ></div>

                        </div>
                    </label>

                    @error('media')
                    <div class="text-sm text-danger">
                        {{ $message }}
                    </div>
                    @enderror

                    {{-- Preview --}}
                    <div
                        id="mediaPreviewSection"
                        class="hidden"
                    >
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-black text-content">
                                    پیش‌نمایش
                                </div>

                                <div
                                    id="previewTypeText"
                                    class="mt-1 text-xs text-content-muted"
                                ></div>
                            </div>

                            <button
                                type="button"
                                id="removeMediaBtn"
                                class="btn btn-ghost btn-sm"
                            >
                                حذف فایل
                            </button>
                        </div>

                        <div
                            id="previewContainer"
                            class="overflow-hidden rounded-3xl border border-border bg-black/5"
                        ></div>
                    </div>

                    {{-- =========================
                         CROP
                    ========================== --}}
                    <div
                        id="cropSection"
                        class="hidden overflow-hidden rounded-3xl border border-border bg-surface-soft"
                    >
                        <div class="border-b border-border px-4 py-4 sm:px-5">
                            <div class="text-sm font-black text-content">
                                برش عکس
                            </div>

                            <p class="mt-1 text-xs leading-6 text-content-muted">
                                ناحیه‌ای که می‌خواهی در پست نمایش داده شود را مشخص کن.
                            </p>
                        </div>

                        <div class="p-4 sm:p-5">

                            <div class="overflow-hidden rounded-2xl bg-black/5">
                                <div
                                    id="cropViewport"
                                    class="relative mx-auto flex min-h-[280px] max-h-[500px] items-center justify-center overflow-hidden"
                                >
                                    <img
                                        id="cropImage"
                                        src=""
                                        alt="Crop"
                                        class="block max-h-[500px] max-w-full"
                                    >
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                <div class="text-xs text-content-muted">
                                    برش اختیاری است.
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        id="resetCropBtn"
                                        class="btn btn-ghost btn-sm"
                                    >
                                        بازنشانی
                                    </button>

                                    <button
                                        type="button"
                                        id="applyCropBtn"
                                        class="btn btn-accent btn-sm"
                                    >
                                        اعمال برش
                                    </button>
                                </div>

                            </div>

                            <div
                                id="cropAppliedMessage"
                                class="mt-3 hidden rounded-xl border border-success/20 bg-success/10 px-3 py-2 text-xs font-bold text-success"
                            >
                                برش روی فایل اعمال شد.
                            </div>

                        </div>
                    </div>

                    {{-- =========================
                         THUMBNAIL
                    ========================== --}}
                    <div
                        id="thumbnailSection"
                        class="hidden overflow-hidden rounded-3xl border border-border bg-surface-soft"
                    >
                        <div class="border-b border-border px-4 py-4 sm:px-5">
                            <div class="text-sm font-black text-content">
                                کاور
                            </div>

                            <p class="mt-1 text-xs leading-6 text-content-muted">
                                برای ویدیو، ریلز یا GIF یک تصویر کاور انتخاب کن. این بخش اختیاری است.
                            </p>
                        </div>

                        <div class="space-y-4 p-4 sm:p-5">

                            <input
                                id="thumbnailInput"
                                name="thumbnail"
                                type="file"
                                class="hidden"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <label
                                for="thumbnailInput"
                                class="flex cursor-pointer items-center justify-center rounded-2xl
                                       border-2 border-dashed border-border bg-surface p-5
                                       text-center transition hover:border-primary/50 hover:bg-primary/5"
                            >
                                <div>
                                    <div class="text-sm font-black text-content">
                                        انتخاب کاور
                                    </div>

                                    <div class="mt-1 text-xs text-content-muted">
                                        JPG / PNG / WEBP — حداکثر 5MB
                                    </div>

                                    <div
                                        id="thumbnailFileName"
                                        class="mt-3 hidden text-xs font-bold text-primary"
                                    ></div>
                                </div>
                            </label>

                            @error('thumbnail')
                            <div class="text-sm text-danger">
                                {{ $message }}
                            </div>
                            @enderror

                            <div
                                id="thumbnailPreview"
                                class="hidden overflow-hidden rounded-2xl border border-border bg-black/5"
                            ></div>

                        </div>
                    </div>

                </div>
            </section>

            {{-- =========================
                 PERFORMER
            ========================== --}}
            <section class="ui-card overflow-hidden">

                <div class="border-b border-border px-5 py-5 sm:px-6">
                    <h2 class="text-base font-black text-content">
                        انجام‌دهنده
                    </h2>

                    <p class="mt-1 text-sm text-content-muted">
                        مشخص کن این کار توسط خودت انجام شده یا یکی از آرایشگرها.
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
                            @checked((string) $oldPerformedByOwner === '1')
                        >

                        <div>
                            <div class="text-sm font-bold text-content">
                                انجام شده توسط خودم
                            </div>

                            <div class="mt-1 text-xs text-content-muted">
                                اگر این کار را خودت انجام داده‌ای، این گزینه را فعال کن.
                            </div>
                        </div>
                    </label>

                    <div id="barberFieldWrapper">
                        <label for="barber_id" class="form-label">
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

                            @foreach ($barbers as $barber)
                                <option
                                    value="{{ $barber->id }}"
                                    @selected((string) old('barber_id') === (string) $barber->id)
                                >
                                {{ $barber->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-help">
                            فقط آرایشگرهای فعال سالن نمایش داده می‌شوند.
                        </div>

                        @error('barber_id')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- =========================
                 SERVICE
            ========================== --}}
            <section class="ui-card overflow-hidden">

                <div class="border-b border-border px-5 py-5 sm:px-6">
                    <h2 class="text-base font-black text-content">
                        اطلاعات کار
                    </h2>

                    <p class="mt-1 text-sm text-content-muted">
                        اتصال پست به خدمت و اطلاعات تکمیلی اختیاری است.
                    </p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">

                    {{-- Service --}}
                    <div>
                        <label for="service_id" class="form-label">
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

                            @foreach ($services as $service)
                                <option
                                    value="{{ $service->id }}"
                                    @selected((string) old('service_id') === (string) $service->id)
                                >
                                {{ $service->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('service_id')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Title --}}
                    <div>
                        <label for="title" class="form-label">
                            عنوان
                            <span class="font-normal text-content-faint">
                                (اختیاری)
                            </span>
                        </label>

                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
                            maxlength="150"
                            class="form-control"
                            placeholder="مثلاً مدل موی کلاسیک مردانه"
                        >

                        @error('title')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Caption --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label for="caption" class="form-label">
                                توضیحات
                                <span class="font-normal text-content-faint">
                                    (اختیاری)
                                </span>
                            </label>

                            <span
                                id="captionCounter"
                                class="text-xs font-bold text-content-faint"
                            >
                                0 / 5000
                            </span>
                        </div>

                        <textarea
                            id="caption"
                            name="caption"
                            rows="6"
                            maxlength="5000"
                            class="form-control resize-y"
                            placeholder="توضیح کوتاهی درباره این کار بنویس..."
                        >{{ old('caption') }}</textarea>

                        @error('caption')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="sort_order" class="form-label">
                            ترتیب نمایش
                        </label>

                        <input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            max="4294967295"
                            value="{{ old('sort_order', 0) }}"
                            class="form-control"
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

                    {{-- Active --}}
                    <div>
                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-border bg-surface-soft p-4">
                            <div>
                                <div class="text-sm font-bold text-content">
                                    انتشار پست
                                </div>

                                <div class="mt-1 text-xs leading-6 text-content-muted">
                                    اگر خاموش باشد، پست ذخیره می‌شود ولی در بخش عمومی نمایش داده نمی‌شود.
                                </div>
                            </div>

                            <div class="shrink-0">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    class="h-5 w-5 rounded border-border text-primary focus:ring-primary"
                                    @checked((string) $oldIsActive === '1')
                                >
                            </div>
                        </label>

                        @error('is_active')
                        <div class="form-error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- =========================
                 SUBMIT
            ========================== --}}
            <div class="sticky bottom-4 z-20">
                <div
                    class="flex flex-col gap-3 rounded-3xl border border-border
                           bg-surface/95 p-4 shadow-card backdrop-blur
                           sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-xs leading-6 text-content-muted">
                        قبل از ذخیره، نوع فایل و پیش‌نمایش را بررسی کن.
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">

                        <a
                            href="{{ route('salon.posts.index') }}"
                            class="btn btn-secondary"
                        >
                            انصراف
                        </a>

                        <button
                            id="submitButton"
                            type="submit"
                            class="btn btn-accent"
                        >
                            <span id="submitButtonText">
                                ذخیره پست
                            </span>

                            <span
                                id="submitSpinner"
                                class="hidden"
                            >
                                در حال ذخیره...
                            </span>
                        </button>

                    </div>
                </div>
            </div>

        </form>

    </div>

@endsection

@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const form = document.getElementById('postCreateForm');

            const mediaInput = document.getElementById('mediaInput');
            const thumbnailInput = document.getElementById('thumbnailInput');

            const mediaDropzone = document.getElementById('mediaDropzone');

            const selectedFileName = document.getElementById('selectedFileName');

            const mediaPreviewSection = document.getElementById('mediaPreviewSection');
            const previewContainer = document.getElementById('previewContainer');
            const previewTypeText = document.getElementById('previewTypeText');

            const removeMediaBtn = document.getElementById('removeMediaBtn');

            const cropSection = document.getElementById('cropSection');
            const cropImage = document.getElementById('cropImage');
            const applyCropBtn = document.getElementById('applyCropBtn');
            const resetCropBtn = document.getElementById('resetCropBtn');
            const cropAppliedMessage = document.getElementById('cropAppliedMessage');

            const thumbnailSection = document.getElementById('thumbnailSection');
            const thumbnailFileName = document.getElementById('thumbnailFileName');
            const thumbnailPreview = document.getElementById('thumbnailPreview');

            const typeInputs = document.querySelectorAll('.post-type-input');

            const performedByOwner = document.getElementById('performedByOwner');
            const barberFieldWrapper = document.getElementById('barberFieldWrapper');
            const barberInput = document.getElementById('barber_id');

            const caption = document.getElementById('caption');
            const captionCounter = document.getElementById('captionCounter');

            const submitButton = document.getElementById('submitButton');
            const submitButtonText = document.getElementById('submitButtonText');
            const submitSpinner = document.getElementById('submitSpinner');

            let currentMediaObjectUrl = null;
            let currentCropObjectUrl = null;

            let originalMediaFile = null;
            let croppedMediaFile = null;

            const MAX_MEDIA_SIZE = 50 * 1024 * 1024;
            const MAX_THUMBNAIL_SIZE = 5 * 1024 * 1024;

            const typeLabels = {
                photo: 'عکس',
                video: 'ویدیو',
                gif: 'GIF',
                reel: 'ریلز',
            };

            const getSelectedType = () => {
                const checked = document.querySelector('.post-type-input:checked');

                return checked
                    ? checked.value
                    : 'photo';
            };

            const isPhoto = () => getSelectedType() === 'photo';
            const isGif = () => getSelectedType() === 'gif';
            const isVideo = () => getSelectedType() === 'video';
            const isReel = () => getSelectedType() === 'reel';

            const revokeMediaObjectUrl = () => {
                if (currentMediaObjectUrl) {
                    URL.revokeObjectURL(currentMediaObjectUrl);
                    currentMediaObjectUrl = null;
                }
            };

            const revokeCropObjectUrl = () => {
                if (currentCropObjectUrl) {
                    URL.revokeObjectURL(currentCropObjectUrl);
                    currentCropObjectUrl = null;
                }
            };

            const resetPreview = () => {
                revokeMediaObjectUrl();
                revokeCropObjectUrl();

                previewContainer.innerHTML = '';

                mediaPreviewSection.classList.add('hidden');
                cropSection.classList.add('hidden');

                cropAppliedMessage.classList.add('hidden');

                selectedFileName.textContent = '';
                selectedFileName.classList.add('hidden');

                cropImage.removeAttribute('src');

                originalMediaFile = null;
                croppedMediaFile = null;
            };

            const setMediaInputFile = (file) => {

                try {

                    const dataTransfer = new DataTransfer();

                    dataTransfer.items.add(file);

                    mediaInput.files = dataTransfer.files;

                } catch (error) {

                    console.warn('Unable to replace input file:', error);

                }

            };

            const formatFileSize = (bytes) => {

                if (bytes < 1024 * 1024) {
                    return `${(bytes / 1024).toFixed(1)} KB`;
                }

                return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
            };

            const validateMediaFile = (file, type) => {

                if (!file) {
                    return false;
                }

                if (file.size > MAX_MEDIA_SIZE) {
                    alert(
                        `حجم فایل زیاد است.\n\nحداکثر حجم مجاز 50MB است.\nحجم فایل شما: ${formatFileSize(file.size)}`
                    );

                    return false;
                }

                const fileName = file.name.toLowerCase();

                const photoExtensions = [
                    '.jpg',
                    '.jpeg',
                    '.png',
                    '.webp',
                ];

                const gifExtensions = [
                    '.gif',
                ];

                const videoExtensions = [
                    '.mp4',
                    '.webm',
                    '.mov',
                ];

                const hasExtension = (extensions) => {
                    return extensions.some(extension => fileName.endsWith(extension));
                };

                if (type === 'photo' && !hasExtension(photoExtensions)) {

                    alert(
                        'برای نوع «عکس» فقط JPG، JPEG، PNG یا WEBP مجاز است.'
                    );

                    return false;
                }

                if (type === 'gif' && !hasExtension(gifExtensions)) {

                    alert(
                        'برای GIF فقط فایل .gif انتخاب کن.'
                    );

                    return false;
                }

                if (
                    (type === 'video' || type === 'reel') &&
                    !hasExtension(videoExtensions)
                ) {

                    alert(
                        'برای ویدیو و ریلز فقط MP4، WEBM یا MOV مجاز است.'
                    );

                    return false;
                }

                return true;
            };

            const renderImagePreview = (file, type) => {

                revokeMediaObjectUrl();

                currentMediaObjectUrl = URL.createObjectURL(file);

                const img = document.createElement('img');

                img.src = currentMediaObjectUrl;
                img.alt = 'Media preview';

                img.className =
                    'block max-h-[620px] w-full object-contain bg-black';

                previewContainer.innerHTML = '';
                previewContainer.appendChild(img);

                mediaPreviewSection.classList.remove('hidden');

                previewTypeText.textContent =
                    `${typeLabels[type] || type} — ${formatFileSize(file.size)}`;
            };

            const renderVideoPreview = (file, type) => {

                revokeMediaObjectUrl();

                currentMediaObjectUrl = URL.createObjectURL(file);

                const video = document.createElement('video');

                video.src = currentMediaObjectUrl;

                video.controls = true;
                video.playsInline = true;
                video.preload = 'metadata';

                video.className =
                    'block max-h-[620px] w-full bg-black object-contain';

                previewContainer.innerHTML = '';
                previewContainer.appendChild(video);

                mediaPreviewSection.classList.remove('hidden');

                previewTypeText.textContent =
                    `${typeLabels[type] || type} — ${formatFileSize(file.size)}`;
            };

            const setupCrop = (file) => {

                revokeCropObjectUrl();

                currentCropObjectUrl = URL.createObjectURL(file);

                cropImage.src = currentCropObjectUrl;

                cropSection.classList.remove('hidden');
                cropAppliedMessage.classList.add('hidden');
            };

            const renderMediaPreview = (file) => {

                const type = getSelectedType();

                if (!file) {
                    return;
                }

                if (!validateMediaFile(file, type)) {
                    resetPreview();
                    mediaInput.value = '';
                    return;
                }

                originalMediaFile = file;
                croppedMediaFile = null;

                selectedFileName.textContent =
                    `${file.name} • ${formatFileSize(file.size)}`;

                selectedFileName.classList.remove('hidden');

                if (isPhoto()) {

                    renderImagePreview(file, type);

                    setupCrop(file);

                    thumbnailSection.classList.add('hidden');

                    return;
                }

                if (isGif()) {

                    renderImagePreview(file, type);

                    cropSection.classList.add('hidden');

                    thumbnailSection.classList.remove('hidden');

                    return;
                }

                if (isVideo() || isReel()) {

                    renderVideoPreview(file, type);

                    cropSection.classList.add('hidden');

                    thumbnailSection.classList.remove('hidden');

                    return;
                }

            };

            const handleMediaChange = () => {

                const file = mediaInput.files?.[0];

                if (!file) {
                    resetPreview();
                    return;
                }

                renderMediaPreview(file);
            };

            const handleTypeChange = () => {

                const type = getSelectedType();

                const existingFile = mediaInput.files?.[0];

                if (type === 'photo') {

                    document.getElementById('mediaHelpText').textContent =
                        'یک عکس انتخاب کن. بعد از انتخاب می‌توانی آن را برش بدهی.';

                } else if (type === 'gif') {

                    document.getElementById('mediaHelpText').textContent =
                        'یک GIF متحرک انتخاب کن. برای GIF برش فعال نیست تا انیمیشن خراب نشود.';

                } else if (type === 'video') {

                    document.getElementById('mediaHelpText').textContent =
                        'یک ویدیوی MP4، WEBM یا MOV انتخاب کن. حداکثر حجم 50MB است.';

                } else if (type === 'reel') {

                    document.getElementById('mediaHelpText').textContent =
                        'ویدیوی ریلز را انتخاب کن. حداکثر حجم 50MB است.';

                }

                if (existingFile) {
                    renderMediaPreview(existingFile);
                }

            };

            const applyCrop = () => {

                if (!originalMediaFile) {
                    return;
                }

                if (!isPhoto()) {
                    return;
                }

                const image = new Image();

                image.onload = () => {

                    const viewportWidth = cropImage.clientWidth;
                    const viewportHeight = cropImage.clientHeight;

                    if (!viewportWidth || !viewportHeight) {

                        alert('ابتدا صبر کن تا تصویر کامل نمایش داده شود.');

                        return;
                    }

                    const naturalWidth = image.naturalWidth;
                    const naturalHeight = image.naturalHeight;

                    /*
                     * برای اینکه crop همیشه کار کند، یک برش مرکزی
                     * متناسب با نسبت 4:5 انجام می‌دهیم.
                     *
                     * در صورت نیاز بعداً می‌توانیم drag/zoom واقعی
                     * با CropperJS هم اضافه کنیم.
                     */

                    const targetRatio = 4 / 5;

                    let sourceWidth = naturalWidth;
                    let sourceHeight = naturalHeight;

                    if (naturalWidth / naturalHeight > targetRatio) {

                        sourceHeight = naturalHeight;

                        sourceWidth =
                            Math.round(naturalHeight * targetRatio);

                    } else {

                        sourceWidth = naturalWidth;

                        sourceHeight =
                            Math.round(naturalWidth / targetRatio);
                    }

                    const sourceX =
                        Math.round((naturalWidth - sourceWidth) / 2);

                    const sourceY =
                        Math.round((naturalHeight - sourceHeight) / 2);

                    const canvas = document.createElement('canvas');

                    const maxOutputWidth = 1600;

                    const scale =
                        Math.min(
                            1,
                            maxOutputWidth / sourceWidth
                        );

                    canvas.width =
                        Math.max(
                            1,
                            Math.round(sourceWidth * scale)
                        );

                    canvas.height =
                        Math.max(
                            1,
                            Math.round(sourceHeight * scale)
                        );

                    const context = canvas.getContext('2d');

                    if (!context) {

                        alert('امکان پردازش تصویر وجود ندارد.');

                        return;
                    }

                    context.imageSmoothingEnabled = true;
                    context.imageSmoothingQuality = 'high';

                    context.drawImage(
                        image,
                        sourceX,
                        sourceY,
                        sourceWidth,
                        sourceHeight,
                        0,
                        0,
                        canvas.width,
                        canvas.height
                    );

                    canvas.toBlob(
                        (blob) => {

                            if (!blob) {

                                alert('برش تصویر انجام نشد.');

                                return;
                            }

                            const croppedFile = new File(
                                [blob],
                                'cropped-photo.jpg',
                                {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                }
                            );

                            croppedMediaFile = croppedFile;

                            setMediaInputFile(croppedFile);

                            renderImagePreview(
                                croppedFile,
                                'photo'
                            );

                            cropAppliedMessage.classList.remove('hidden');

                        },
                        'image/jpeg',
                        0.92
                    );
                };

                image.onerror = () => {

                    alert('تصویر برای برش قابل پردازش نیست.');

                };

                image.src = cropImage.src;

            };

            const resetCrop = () => {

                if (!originalMediaFile) {
                    return;
                }

                croppedMediaFile = null;

                setMediaInputFile(originalMediaFile);

                renderImagePreview(
                    originalMediaFile,
                    'photo'
                );

                cropAppliedMessage.classList.add('hidden');

            };

            const handleThumbnailChange = () => {

                const file = thumbnailInput.files?.[0];

                thumbnailPreview.innerHTML = '';
                thumbnailFileName.textContent = '';

                thumbnailFileName.classList.add('hidden');
                thumbnailPreview.classList.add('hidden');

                if (!file) {
                    return;
                }

                if (file.size > MAX_THUMBNAIL_SIZE) {

                    alert(
                        `حجم کاور نباید بیشتر از 5MB باشد.\nحجم فایل شما: ${formatFileSize(file.size)}`
                    );

                    thumbnailInput.value = '';

                    return;
                }

                const fileName = file.name.toLowerCase();

                const allowed =
                    fileName.endsWith('.jpg') ||
                    fileName.endsWith('.jpeg') ||
                    fileName.endsWith('.png') ||
                    fileName.endsWith('.webp');

                if (!allowed) {

                    alert(
                        'فرمت کاور باید JPG، JPEG، PNG یا WEBP باشد.'
                    );

                    thumbnailInput.value = '';

                    return;
                }

                thumbnailFileName.textContent =
                    `${file.name} • ${formatFileSize(file.size)}`;

                thumbnailFileName.classList.remove('hidden');

                const objectUrl = URL.createObjectURL(file);

                const img = document.createElement('img');

                img.src = objectUrl;
                img.alt = 'Thumbnail preview';

                img.className =
                    'block max-h-[420px] w-full object-contain bg-black';

                img.onload = () => {
                    URL.revokeObjectURL(objectUrl);
                };

                thumbnailPreview.appendChild(img);

                thumbnailPreview.classList.remove('hidden');

            };

            const syncPerformer = () => {

                const ownerIsSelected =
                    performedByOwner.checked;

                if (ownerIsSelected) {

                    barberFieldWrapper.classList.add('opacity-50');

                    barberInput.value = '';
                    barberInput.disabled = true;

                } else {

                    barberFieldWrapper.classList.remove('opacity-50');

                    barberInput.disabled = false;

                }

            };

            const updateCaptionCounter = () => {

                const length = caption.value.length;

                captionCounter.textContent =
                    `${length.toLocaleString('fa-IR')} / ۵۰۰۰`;

            };

            const clearMedia = () => {

                resetPreview();

                mediaInput.value = '';

                thumbnailInput.value = '';

                thumbnailSection.classList.add('hidden');

                thumbnailPreview.innerHTML = '';
                thumbnailPreview.classList.add('hidden');

                thumbnailFileName.textContent = '';
                thumbnailFileName.classList.add('hidden');
            };

            typeInputs.forEach((input) => {

                input.addEventListener(
                    'change',
                    handleTypeChange
                );

            });

            mediaInput.addEventListener(
                'change',
                handleMediaChange
            );

            thumbnailInput.addEventListener(
                'change',
                handleThumbnailChange
            );

            applyCropBtn.addEventListener(
                'click',
                applyCrop
            );

            resetCropBtn.addEventListener(
                'click',
                resetCrop
            );

            removeMediaBtn.addEventListener(
                'click',
                clearMedia
            );

            performedByOwner.addEventListener(
                'change',
                syncPerformer
            );

            caption.addEventListener(
                'input',
                updateCaptionCounter
            );

            form.addEventListener(
                'submit',
                (event) => {

                    const file = mediaInput.files?.[0];

                    if (!file) {

                        event.preventDefault();

                        alert(
                            'لطفاً فایل رسانه را انتخاب کن.'
                        );

                        return;
                    }

                    const type = getSelectedType();

                    if (!validateMediaFile(file, type)) {

                        event.preventDefault();

                        return;
                    }

                    if (
                        !performedByOwner.checked &&
                        !barberInput.value
                    ) {

                        event.preventDefault();

                        barberInput.focus();

                        alert(
                            'لطفاً آرایشگر انجام‌دهنده را انتخاب کن.'
                        );

                        return;
                    }

                    submitButton.disabled = true;

                    submitButtonText.classList.add('hidden');

                    submitSpinner.classList.remove('hidden');

                }
            );

            /*
             * Initial state
             */

            syncPerformer();

            updateCaptionCounter();

            handleTypeChange();

        });
    </script>

@endpush
