// resources/js/salon-posts.js

(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('salonPostForm');

        // فقط روی صفحه Create / Edit پست سالن اجرا شود
        if (!form) {
            return;
        }

        const mediaInput = document.getElementById('mediaInput');
        const mediaDropzone = document.getElementById('mediaDropzone');

        const detectedTypeBadge = document.getElementById('detectedTypeBadge');
        const mediaHelpText = document.getElementById('mediaHelpText');

        const uploadIcon = document.getElementById('uploadIcon');
        const uploadTitle = document.getElementById('uploadTitle');
        const uploadDescription = document.getElementById('uploadDescription');

        const selectedFileName = document.getElementById('selectedFileName');

        const mediaPreviewSection = document.getElementById('mediaPreviewSection');
        const previewContainer = document.getElementById('previewContainer');
        const previewTypeText = document.getElementById('previewTypeText');
        const removeMediaBtn = document.getElementById('removeMediaBtn');

        const thumbnailSection = document.getElementById('thumbnailSection');
        const thumbnailInput = document.getElementById('thumbnailInput');
        const thumbnailFileName = document.getElementById('thumbnailFileName');
        const thumbnailPreview = document.getElementById('thumbnailPreview');

        const performedByOwner = document.getElementById('performedByOwner');
        const barberFieldWrapper = document.getElementById('barberFieldWrapper');
        const barberInput = document.getElementById('barber_id');

        const submitButton = document.getElementById('submitPostBtn');

        const currentType = form.closest('#salonPostEditor')?.dataset.currentType || '';
        const mode = form.closest('#salonPostEditor')?.dataset.mode || 'create';

        let mediaObjectUrl = null;
        let thumbnailObjectUrl = null;
        let isSubmitting = false;

        const TYPE_META = {
            image: {
                label: 'عکس',
                description: 'تصویر JPG / PNG / WEBP',
                icon: '▧',
            },

            gif: {
                label: 'GIF',
                description: 'GIF متحرک',
                icon: 'GIF',
            },

            video: {
                label: 'ویدیو',
                description: 'ویدیوی معمولی',
                icon: '▶',
            },

            reel: {
                label: 'ریلز',
                description: 'ویدیوی عمودی',
                icon: '◎',
            },
        };

        const IMAGE_TYPES = new Set([
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ]);

        const VIDEO_TYPES = new Set([
            'video/mp4',
            'video/webm',
            'video/quicktime',
            'video/x-m4v',
            'video/ogg',
        ]);

        const EXTENSION_TYPE_MAP = {
            jpg: 'image',
            jpeg: 'image',
            png: 'image',
            webp: 'image',

            gif: 'gif',

            mp4: 'video',
            webm: 'video',
            mov: 'video',
            m4v: 'video',
            ogv: 'video',
        };

        const MAX_MEDIA_SIZE = 50 * 1024 * 1024;
        const MAX_THUMBNAIL_SIZE = 5 * 1024 * 1024;

        /**
         * ------------------------------------------------------------
         * Helpers
         * ------------------------------------------------------------
         */

        const getExtension = (file) => {
            if (!file?.name) {
                return '';
            }

            const parts = file.name.toLowerCase().split('.');
            return parts.length > 1 ? parts.pop() : '';
        };

        const formatBytes = (bytes) => {
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB'];
            const index = Math.min(
                Math.floor(Math.log(bytes) / Math.log(1024)),
                units.length - 1
            );

            const value = bytes / Math.pow(1024, index);

            return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
        };

        const escapeHtml = (value) => {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        };

        const revokeMediaObjectUrl = () => {
            if (mediaObjectUrl) {
                URL.revokeObjectURL(mediaObjectUrl);
                mediaObjectUrl = null;
            }
        };

        const revokeThumbnailObjectUrl = () => {
            if (thumbnailObjectUrl) {
                URL.revokeObjectURL(thumbnailObjectUrl);
                thumbnailObjectUrl = null;
            }
        };

        const getSelectedTypeInput = () => {
            return form.querySelector('input[name="type"]:checked');
        };

        const selectType = (type) => {
            const input = form.querySelector(
                `input[name="type"][value="${CSS.escape(type)}"]`
            );

            if (input) {
                input.checked = true;
            }

            updateTypeUI(type);
        };

        const getTypeLabel = (type) => {
            return TYPE_META[type]?.label || type || '—';
        };

        const showError = (message) => {
            let errorBox = document.getElementById('salonPostClientError');

            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.id = 'salonPostClientError';
                errorBox.className =
                    'mb-6 rounded-2xl border border-danger/20 bg-danger/5 px-4 py-4 text-sm leading-7 font-bold text-danger';

                const firstSection = form.querySelector('section');

                if (firstSection) {
                    form.insertBefore(errorBox, firstSection);
                } else {
                    form.prepend(errorBox);
                }
            }

            errorBox.textContent = message;

            errorBox.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
        };

        const clearClientError = () => {
            const errorBox = document.getElementById('salonPostClientError');

            if (errorBox) {
                errorBox.remove();
            }
        };

        /**
         * ------------------------------------------------------------
         * Media detection
         * ------------------------------------------------------------
         */

        const detectBaseType = (file) => {
            if (!file) {
                return null;
            }

            const mime = (file.type || '').toLowerCase();
            const extension = getExtension(file);

            // GIF را قبل از image بررسی می‌کنیم
            if (
                mime === 'image/gif' ||
                extension === 'gif'
            ) {
                return 'gif';
            }

            if (
                IMAGE_TYPES.has(mime) ||
                mime.startsWith('image/')
            ) {
                return 'image';
            }

            if (
                VIDEO_TYPES.has(mime) ||
                mime.startsWith('video/')
            ) {
                return 'video';
            }

            if (EXTENSION_TYPE_MAP[extension]) {
                return EXTENSION_TYPE_MAP[extension];
            }

            return null;
        };

        const detectVideoType = (file) => {
            return new Promise((resolve) => {
                const video = document.createElement('video');

                const objectUrl = URL.createObjectURL(file);

                let finished = false;

                const finish = (type) => {
                    if (finished) {
                        return;
                    }

                    finished = true;

                    URL.revokeObjectURL(objectUrl);

                    video.removeAttribute('src');
                    video.load();

                    resolve(type);
                };

                const fallback = () => {
                    // اگر metadata در دسترس نبود، حداقل video را نگه می‌داریم
                    finish('video');
                };

                video.preload = 'metadata';
                video.muted = true;
                video.playsInline = true;

                video.addEventListener(
                    'loadedmetadata',
                    () => {
                        const width = Number(video.videoWidth || 0);
                        const height = Number(video.videoHeight || 0);

                        // ویدیوی عمودی = Reel
                        // نسبت 1.12 کمک می‌کند ویدیوهای تقریباً مربعی
                        // بی‌دلیل Reel تشخیص داده نشوند.
                        if (
                            width > 0 &&
                            height > 0 &&
                            height > width * 1.12
                        ) {
                            finish('reel');
                            return;
                        }

                        finish('video');
                    },
                    { once: true }
                );

                video.addEventListener(
                    'error',
                    fallback,
                    { once: true }
                );

                video.src = objectUrl;

                // بعضی مرورگرها ممکن است metadata event را دیر بدهند
                setTimeout(() => {
                    if (!finished) {
                        fallback();
                    }
                }, 5000);
            });
        };

        const detectMediaType = async (file) => {
            const baseType = detectBaseType(file);

            if (!baseType) {
                return null;
            }

            if (baseType === 'video') {
                return await detectVideoType(file);
            }

            return baseType;
        };

        /**
         * ------------------------------------------------------------
         * UI
         * ------------------------------------------------------------
         */

        const updateTypeUI = (type) => {
            const meta = TYPE_META[type];

            if (!meta) {
                if (detectedTypeBadge) {
                    detectedTypeBadge.textContent =
                        'هنوز فایلی انتخاب نشده';
                }

                if (previewTypeText) {
                    previewTypeText.textContent = '—';
                }

                return;
            }

            if (detectedTypeBadge) {
                detectedTypeBadge.innerHTML = `
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-primary">
                        ${escapeHtml(meta.icon)}
                    </span>
                    <span>${escapeHtml(meta.label)}</span>
                `;
            }

            if (previewTypeText) {
                previewTypeText.textContent = meta.label;
            }

            if (mediaHelpText) {
                mediaHelpText.textContent = meta.description;
            }

            if (uploadIcon) {
                uploadIcon.textContent = meta.icon;
            }

            if (uploadTitle) {
                uploadTitle.textContent =
                    type === 'image'
                        ? 'تصویر آماده است'
                        : type === 'gif'
                            ? 'GIF آماده است'
                            : type === 'reel'
                                ? 'ریلز آماده است'
                                : 'ویدیو آماده است';
            }

            if (uploadDescription) {
                uploadDescription.innerHTML = `
                    ${escapeHtml(meta.description)}
                    <br>
                    حداکثر حجم 50MB
                `;
            }

            if (thumbnailSection) {
                const showThumbnail =
                    type === 'video' ||
                    type === 'reel';

                thumbnailSection.classList.toggle(
                    'hidden',
                    !showThumbnail
                );
            }
        };

        const setSelectedFileName = (file) => {
            if (!selectedFileName) {
                return;
            }

            if (!file) {
                selectedFileName.textContent = '';
                selectedFileName.classList.add('hidden');
                return;
            }

            selectedFileName.textContent =
                `${file.name} • ${formatBytes(file.size)}`;

            selectedFileName.classList.remove('hidden');
        };

        const createImagePreview = (file) => {
            revokeMediaObjectUrl();

            mediaObjectUrl = URL.createObjectURL(file);

            return `
                <div class="flex min-h-[240px] items-center justify-center bg-black/[0.03] p-3">
                    <img
                        src="${mediaObjectUrl}"
                        alt="${escapeHtml(file.name)}"
                        class="max-h-[560px] w-full rounded-2xl object-contain"
                    >
                </div>
            `;
        };

        const createVideoPreview = (file, type) => {
            revokeMediaObjectUrl();

            mediaObjectUrl = URL.createObjectURL(file);

            return `
                <div class="relative flex min-h-[280px] items-center justify-center bg-black p-2">
                    <video
                        src="${mediaObjectUrl}"
                        controls
                        muted
                        playsinline
                        preload="metadata"
                        class="max-h-[620px] w-full rounded-2xl object-contain"
                    ></video>

                    <div class="pointer-events-none absolute left-4 top-4 rounded-full bg-black/60 px-3 py-1.5 text-[10px] font-black text-white backdrop-blur-md">
                        ${type === 'reel' ? 'ریلز' : 'ویدیو'}
                    </div>
                </div>
            `;
        };

        const renderSelectedMedia = (file, type) => {
            if (!mediaPreviewSection || !previewContainer) {
                return;
            }

            if (!file || !type) {
                mediaPreviewSection.classList.add('hidden');
                previewContainer.innerHTML = '';
                return;
            }

            let html = '';

            if (type === 'image' || type === 'gif') {
                html = createImagePreview(file);
            } else if (type === 'video' || type === 'reel') {
                html = createVideoPreview(file, type);
            }

            previewContainer.innerHTML = html;
            mediaPreviewSection.classList.remove('hidden');

            if (previewTypeText) {
                previewTypeText.textContent = getTypeLabel(type);
            }
        };

        const resetNewMediaPreview = () => {
            revokeMediaObjectUrl();

            if (selectedFileName) {
                selectedFileName.textContent = '';
                selectedFileName.classList.add('hidden');
            }

            /*
             * در Edit، فایل قبلی هنوز روی سرور هست.
             * اگر فایل جدید حذف شد، preview فایل فعلی برگردانده می‌شود.
             */
            const existingMediaUrl =
                form.querySelector('[data-existing-media-url]')?.dataset.existingMediaUrl;

            const existingThumbnailUrl =
                form.querySelector('[data-existing-thumbnail-url]')?.dataset.existingThumbnailUrl;

            const existingType =
                form.closest('#salonPostEditor')?.dataset.currentType || currentType;

            if (
                mode === 'edit' &&
                existingMediaUrl &&
                previewContainer &&
                mediaPreviewSection
            ) {
                if (
                    existingType === 'image' ||
                    existingType === 'gif'
                ) {
                    previewContainer.innerHTML = `
                        <img
                            src="${escapeHtml(existingMediaUrl)}"
                            alt="پست سالن"
                            class="mx-auto max-h-[520px] w-full object-contain"
                        >
                    `;
                } else if (
                    existingType === 'video' ||
                    existingType === 'reel'
                ) {
                    previewContainer.innerHTML = `
                        <video
                            src="${escapeHtml(existingMediaUrl)}"
                            ${existingThumbnailUrl ? `poster="${escapeHtml(existingThumbnailUrl)}"` : ''}
                            controls
                            muted
                            playsinline
                            preload="metadata"
                            class="mx-auto max-h-[560px] w-full object-contain"
                        ></video>
                    `;
                }

                mediaPreviewSection.classList.remove('hidden');

                if (previewTypeText) {
                    previewTypeText.textContent =
                        getTypeLabel(existingType);
                }

                updateTypeUI(existingType);

                return;
            }

            if (previewContainer) {
                previewContainer.innerHTML = '';
            }

            if (mediaPreviewSection) {
                mediaPreviewSection.classList.add('hidden');
            }

            updateTypeUI('');

            if (uploadIcon) {
                uploadIcon.textContent = '+';
            }

            if (uploadTitle) {
                uploadTitle.textContent = 'فایل را انتخاب کن';
            }

            if (uploadDescription) {
                uploadDescription.innerHTML = `
                    JPG, PNG, WEBP, GIF, MP4, WEBM, MOV
                    <br>
                    حداکثر حجم 50MB
                `;
            }
        };

        /**
         * ------------------------------------------------------------
         * Thumbnail
         * ------------------------------------------------------------
         */

        const renderThumbnail = (file) => {
            revokeThumbnailObjectUrl();

            if (!thumbnailPreview) {
                return;
            }

            if (!file) {
                thumbnailPreview.classList.add('hidden');
                thumbnailPreview.innerHTML = '';
                return;
            }

            thumbnailObjectUrl = URL.createObjectURL(file);

            thumbnailPreview.innerHTML = `
                <img
                    src="${thumbnailObjectUrl}"
                    alt="پیش‌نمایش کاور"
                    class="max-h-80 w-full object-contain"
                >
            `;

            thumbnailPreview.classList.remove('hidden');
        };

        const resetThumbnail = () => {
            revokeThumbnailObjectUrl();

            if (thumbnailFileName) {
                thumbnailFileName.textContent = '';
                thumbnailFileName.classList.add('hidden');
            }

            const existingThumbnail =
                form.querySelector('[data-existing-thumbnail-url]')?.dataset.existingThumbnailUrl;

            if (existingThumbnail && thumbnailPreview) {
                thumbnailPreview.innerHTML = `
                    <img
                        src="${escapeHtml(existingThumbnail)}"
                        alt="کاور"
                        class="max-h-80 w-full object-contain"
                    >
                `;

                thumbnailPreview.classList.remove('hidden');
                return;
            }

            if (thumbnailPreview) {
                thumbnailPreview.innerHTML = '';
                thumbnailPreview.classList.add('hidden');
            }
        };

        /**
         * ------------------------------------------------------------
         * Performer
         * ------------------------------------------------------------
         */

        const updatePerformerState = () => {
            if (!performedByOwner || !barberFieldWrapper) {
                return;
            }

            const ownerChecked = performedByOwner.checked;

            barberFieldWrapper.classList.toggle(
                'hidden',
                ownerChecked
            );

            if (barberInput) {
                barberInput.disabled = ownerChecked;

                if (ownerChecked) {
                    barberInput.value = '';
                }
            }
        };

        /**
         * ------------------------------------------------------------
         * Existing media markers
         * ------------------------------------------------------------
         *
         * فرم فعلی Blade این data attributes را ندارد،
         * پس اگر edit باشد از DOM موجود خودش استخراج می‌کنیم.
         */

        const ensureExistingMediaMarkers = () => {
            if (mode !== 'edit') {
                return;
            }

            if (!previewContainer) {
                return;
            }

            if (!form.querySelector('[data-existing-media-url]')) {
                const existingMedia =
                    previewContainer.querySelector('img, video');

                if (existingMedia?.getAttribute('src')) {
                    const marker = document.createElement('span');

                    marker.dataset.existingMediaUrl =
                        existingMedia.getAttribute('src');

                    marker.hidden = true;

                    form.appendChild(marker);
                }
            }

            if (!form.querySelector('[data-existing-thumbnail-url]')) {
                const video = previewContainer.querySelector('video');

                if (video?.getAttribute('poster')) {
                    const marker = document.createElement('span');

                    marker.dataset.existingThumbnailUrl =
                        video.getAttribute('poster');

                    marker.hidden = true;

                    form.appendChild(marker);
                }
            }
        };

        ensureExistingMediaMarkers();

        /**
         * ------------------------------------------------------------
         * Media input
         * ------------------------------------------------------------
         */

        const handleMediaFile = async (file) => {
            clearClientError();

            if (!file) {
                return;
            }

            if (file.size > MAX_MEDIA_SIZE) {
                if (mediaInput) {
                    mediaInput.value = '';
                }

                showError(
                    `حجم فایل ${formatBytes(file.size)} است و حداکثر حجم مجاز 50MB می‌باشد.`
                );

                resetNewMediaPreview();

                return;
            }

            const detectedType = await detectMediaType(file);

            if (!detectedType) {
                if (mediaInput) {
                    mediaInput.value = '';
                }

                showError(
                    'نوع فایل قابل تشخیص نیست. لطفاً یک عکس، GIF یا ویدیوی معتبر انتخاب کنید.'
                );

                resetNewMediaPreview();

                return;
            }

            /*
             * نوع واقعی فایل توسط JS انتخاب می‌شود.
             * سرور هم دوباره آن را validate می‌کند.
             */
            selectType(detectedType);

            setSelectedFileName(file);
            renderSelectedMedia(file, detectedType);

            if (mediaDropzone) {
                mediaDropzone.classList.add(
                    'border-primary/50',
                    'bg-primary/5'
                );
            }

            // برای video / reel کاور فعال شود
            if (
                detectedType === 'video' ||
                detectedType === 'reel'
            ) {
                if (thumbnailSection) {
                    thumbnailSection.classList.remove('hidden');
                }
            } else {
                if (thumbnailSection) {
                    thumbnailSection.classList.add('hidden');
                }
            }
        };

        if (mediaInput) {
            mediaInput.addEventListener('change', async (event) => {
                const file = event.target.files?.[0] || null;

                await handleMediaFile(file);
            });
        }

        /**
         * ------------------------------------------------------------
         * Drag & Drop
         * ------------------------------------------------------------
         */

        if (mediaDropzone) {
            mediaDropzone.addEventListener('dragover', (event) => {
                event.preventDefault();

                mediaDropzone.classList.add(
                    'border-primary',
                    'bg-primary/10'
                );
            });

            mediaDropzone.addEventListener('dragleave', () => {
                mediaDropzone.classList.remove(
                    'border-primary',
                    'bg-primary/10'
                );
            });

            mediaDropzone.addEventListener('drop', async (event) => {
                event.preventDefault();

                mediaDropzone.classList.remove(
                    'border-primary',
                    'bg-primary/10'
                );

                const file = event.dataTransfer?.files?.[0] || null;

                if (!file || !mediaInput) {
                    return;
                }

                /*
                 * FileList قابل ساخت مستقیم در همه جا نیست.
                 * DataTransfer راه مطمئن برای جایگزینی فایل input است.
                 */
                try {
                    const dataTransfer = new DataTransfer();

                    dataTransfer.items.add(file);
                    mediaInput.files = dataTransfer.files;

                    await handleMediaFile(file);
                } catch (error) {
                    console.error(
                        'Salon post drag/drop error:',
                        error
                    );

                    showError(
                        'انتقال فایل انجام نشد. لطفاً از انتخاب معمولی فایل استفاده کن.'
                    );
                }
            });
        }

        /**
         * ------------------------------------------------------------
         * Remove Media
         * ------------------------------------------------------------
         */

        if (removeMediaBtn) {
            removeMediaBtn.addEventListener('click', () => {
                clearClientError();

                if (mediaInput) {
                    mediaInput.value = '';
                }

                resetNewMediaPreview();

                if (mediaDropzone) {
                    mediaDropzone.classList.remove(
                        'border-primary/50',
                        'bg-primary/5'
                    );
                }
            });
        }

        /**
         * ------------------------------------------------------------
         * Thumbnail input
         * ------------------------------------------------------------
         */

        if (thumbnailInput) {
            thumbnailInput.addEventListener('change', (event) => {
                clearClientError();

                const file =
                    event.target.files?.[0] || null;

                if (!file) {
                    return;
                }

                if (file.size > MAX_THUMBNAIL_SIZE) {
                    thumbnailInput.value = '';

                    showError(
                        `حجم کاور ${formatBytes(file.size)} است و حداکثر حجم مجاز 5MB می‌باشد.`
                    );

                    resetThumbnail();

                    return;
                }

                if (!file.type.startsWith('image/')) {
                    thumbnailInput.value = '';

                    showError(
                        'کاور باید یک تصویر معتبر باشد.'
                    );

                    resetThumbnail();

                    return;
                }

                if (thumbnailFileName) {
                    thumbnailFileName.textContent =
                        `${file.name} • ${formatBytes(file.size)}`;

                    thumbnailFileName.classList.remove('hidden');
                }

                renderThumbnail(file);
            });
        }

        /**
         * ------------------------------------------------------------
         * Type radios
         * ------------------------------------------------------------
         */

        form
            .querySelectorAll('.post-type-input')
            .forEach((input) => {
                input.addEventListener('change', () => {
                    const selectedType = getSelectedTypeInput()?.value || '';

                    updateTypeUI(selectedType);
                });
            });

        /**
         * ------------------------------------------------------------
         * Performer toggle
         * ------------------------------------------------------------
         */

        if (performedByOwner) {
            performedByOwner.addEventListener(
                'change',
                updatePerformerState
            );
        }

        /**
         * ------------------------------------------------------------
         * Form validation
         * ------------------------------------------------------------
         */

        form.addEventListener('submit', (event) => {
            clearClientError();

            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            const selectedType =
                getSelectedTypeInput()?.value || '';

            /*
             * Create باید فایل داشته باشد.
             * Edit می‌تواند فایل قبلی را نگه دارد.
             */
            if (
                mode === 'create' &&
                (!mediaInput?.files?.length)
            ) {
                event.preventDefault();

                showError(
                    'لطفاً ابتدا یک فایل رسانه‌ای انتخاب کن.'
                );

                return;
            }

            /*
             * اگر فایل جدید انتخاب شده، type باید با آن هماهنگ باشد.
             */
            const selectedFile =
                mediaInput?.files?.[0] || null;

            if (selectedFile && selectedType) {
                const baseType =
                    detectBaseType(selectedFile);

                const typeIsValid =
                    (baseType === 'image' &&
                        selectedType === 'image') ||
                    (baseType === 'gif' &&
                        selectedType === 'gif') ||
                    (baseType === 'video' &&
                        (
                            selectedType === 'video' ||
                            selectedType === 'reel'
                        ));

                if (!typeIsValid) {
                    event.preventDefault();

                    showError(
                        'نوع محتوا با فایل انتخاب‌شده هماهنگ نیست. لطفاً فایل را دوباره انتخاب کن.'
                    );

                    return;
                }
            }

            if (
                !performedByOwner?.checked &&
                barberInput &&
                !barberInput.value
            ) {
                event.preventDefault();

                showError(
                    'وقتی انجام‌دهنده خودت نیستی، انتخاب آرایشگر الزامی است.'
                );

                barberInput.focus();

                return;
            }

            isSubmitting = true;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add(
                    'cursor-not-allowed',
                    'opacity-70'
                );

                submitButton.dataset.originalText =
                    submitButton.textContent.trim();

                submitButton.textContent =
                    mode === 'edit'
                        ? 'در حال ذخیره...'
                        : 'در حال انتشار...';
            }
        });

        /**
         * ------------------------------------------------------------
         * Initial state
         * ------------------------------------------------------------
         */

        updatePerformerState();

        const initialType =
            getSelectedTypeInput()?.value ||
            currentType ||
            '';

        updateTypeUI(initialType);

        if (thumbnailSection) {
            thumbnailSection.classList.toggle(
                'hidden',
                !(
                    initialType === 'video' ||
                    initialType === 'reel'
                )
            );
        }

        /**
         * ------------------------------------------------------------
         * Existing preview markers
         * ------------------------------------------------------------
         *
         * برای اینکه removeMedia در edit بتواند به فایل قبلی
         * برگردد، URL فعلی از preview استخراج می‌شود.
         */
        if (mode === 'edit') {
            const existingVideo =
                previewContainer?.querySelector('video');

            const existingImage =
                previewContainer?.querySelector('img');

            const existingMediaElement =
                existingVideo || existingImage;

            if (
                existingMediaElement &&
                existingMediaElement.src
            ) {
                const marker =
                    form.querySelector('[data-existing-media-url]');

                if (marker) {
                    marker.dataset.existingMediaUrl =
                        existingMediaElement.src;
                }
            }

            if (
                existingVideo?.poster
            ) {
                const thumbMarker =
                    form.querySelector('[data-existing-thumbnail-url]');

                if (thumbMarker) {
                    thumbMarker.dataset.existingThumbnailUrl =
                        existingVideo.poster;
                }
            }
        }

        /**
         * ------------------------------------------------------------
         * Cleanup
         * ------------------------------------------------------------
         */

        window.addEventListener('beforeunload', () => {
            revokeMediaObjectUrl();
            revokeThumbnailObjectUrl();
        });
    });
})();
