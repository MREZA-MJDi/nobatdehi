/**
 * NOBAT — Public Salon Interactions
 */

(function () {
    'use strict';

    const root = document.getElementById('salonPage');

    if (!root) {
        return;
    }

    /* ==========================================================
       CONFIG
    ========================================================== */

    const SALON_ID = root.dataset.salonId;
    const IS_AUTH = root.dataset.isAuth === '1';
    const LOGIN_URL = root.dataset.loginUrl;
    const CSRF = root.dataset.csrf;
    const TODAY_ISO =
        root.dataset.today ||
        new Date().toISOString().slice(0, 10);

    const ROUTES = {
        availability: root.dataset.availabilityUrl,
        prepare: root.dataset.prepareUrl,
    };

    /* ==========================================================
       HELPERS
    ========================================================== */

    const fa = (value) => {
        return String(value ?? '').replace(
            /\d/g,
            (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]
        );
    };

    const pad2 = (value) => {
        return String(value).padStart(2, '0');
    };

    const fmtYMD = (y, m, d) => {
        return `${y}-${pad2(m)}-${pad2(d)}`;
    };

    const PERSIAN_MONTHS = [
        'فروردین',
        'اردیبهشت',
        'خرداد',
        'تیر',
        'مرداد',
        'شهریور',
        'مهر',
        'آبان',
        'آذر',
        'دی',
        'بهمن',
        'اسفند',
    ];

    const POST_LABELS = {
        image: 'عکس',
        gif: 'GIF',
        video: 'ویدیو',
        reel: 'ریلز',
    };

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    /* ==========================================================
       TOAST
    ========================================================== */

    const toast = document.getElementById('salonToast');

    let toastTimer = null;

    function showToast(message) {
        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimer);

        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2600);
    }

    /* ==========================================================
       REVEAL
    ========================================================== */

    const revealElements = root.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('in');
                    revealObserver.unobserve(entry.target);
                });
            },
            {
                threshold: 0.08,
            }
        );

        revealElements.forEach((element) => {
            element.classList.add('reveal-ready');
            revealObserver.observe(element);
        });
    } else {
        revealElements.forEach((element) => {
            element.classList.add('in');
        });
    }

    /* ==========================================================
       GALLERY
    ========================================================== */

    const galleryGrid = document.getElementById('galleryGrid');
    const galleryEmpty = document.getElementById('galleryEmpty');
    const galleryTabs = Array.from(root.querySelectorAll('.tab'));
    const galleryTiles = galleryGrid
        ? Array.from(
            galleryGrid.querySelectorAll('[data-gallery-item]')
        )
        : [];

    let currentGalleryFilter = 'all';

    function getVisibleGalleryTiles() {
        return galleryTiles.filter(
            (tile) => !tile.hidden
        );
    }

    function stopGalleryVideos() {
        galleryTiles.forEach((tile) => {
            const video = tile.querySelector('video');

            if (!video) {
                return;
            }

            video.pause();

            try {
                video.currentTime = 0;
            } catch (_) {
                // Ignore media reset errors.
            }
        });
    }

    function applyGalleryFilter(filter) {
        currentGalleryFilter = filter;

        let visibleCount = 0;

        galleryTabs.forEach((tab) => {
            const active = tab.dataset.filter === filter;

            tab.classList.toggle('active', active);
            tab.setAttribute(
                'aria-selected',
                active ? 'true' : 'false'
            );
        });

        galleryTiles.forEach((tile) => {
            const matches =
                filter === 'all' ||
                tile.dataset.type === filter;

            tile.hidden = !matches;

            if (matches) {
                visibleCount++;
            }
        });

        if (galleryEmpty) {
            galleryEmpty.hidden = visibleCount !== 0;
        }

        stopGalleryVideos();
    }

    galleryTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            applyGalleryFilter(
                tab.dataset.filter || 'all'
            );
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Gallery video hover preview
    |--------------------------------------------------------------------------
    */

    galleryTiles.forEach((tile) => {
        const video = tile.querySelector('video');

        if (video) {
            tile.addEventListener('mouseenter', () => {
                video.play().catch(() => {});
            });

            tile.addEventListener('mouseleave', () => {
                video.pause();

                try {
                    video.currentTime = 0;
                } catch (_) {
                    // Ignore.
                }
            });
        }
    });

    /* ==========================================================
       LIGHTBOX
    ========================================================== */

    const lightbox = document.getElementById(
        'mediaLightbox'
    );

    const lightboxMedia = document.getElementById(
        'lightboxMedia'
    );

    const lightboxTitle = document.getElementById(
        'lightboxTitle'
    );

    const lightboxMeta = document.getElementById(
        'lightboxMeta'
    );

    const lightboxCaption = document.getElementById(
        'lightboxCaption'
    );

    const lightboxType = document.getElementById(
        'lightboxType'
    );

    const lightboxClose = document.getElementById(
        'lightboxClose'
    );

    const lightboxPrev = document.getElementById(
        'lightboxPrev'
    );

    const lightboxNext = document.getElementById(
        'lightboxNext'
    );

    let lightboxItems = [];
    let lightboxIndex = 0;

    function formatMediaTime(seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) {
            return '00:00';
        }

        const total = Math.floor(seconds);
        const minutes = Math.floor(total / 60);
        const remaining = total % 60;

        return `${pad2(minutes)}:${pad2(remaining)}`;
    }

    function buildCustomVideoPlayer(src, poster, title) {
        const player = document.createElement('div');
        player.className = 'custom-video-player';
        player.setAttribute('data-video-player', '');

        const video = document.createElement('video');
        video.src = src;
        video.playsInline = true;
        video.autoplay = true;
        video.muted = true;
        video.preload = 'metadata';
        video.setAttribute('aria-label', title || 'ویدیوی نمونه‌کار');

        if (poster) {
            video.poster = poster;
        }

        const centerPlay = document.createElement('button');
        centerPlay.type = 'button';
        centerPlay.className = 'cvp-center-play';
        centerPlay.setAttribute('aria-label', 'پخش ویدیو');
        centerPlay.innerHTML = '<span>▶</span>';

        const chrome = document.createElement('div');
        chrome.className = 'cvp-chrome';

        const progress = document.createElement('input');
        progress.type = 'range';
        progress.min = '0';
        progress.max = '1000';
        progress.value = '0';
        progress.step = '1';
        progress.className = 'cvp-progress';
        progress.setAttribute('aria-label', 'موقعیت ویدیو');

        const controls = document.createElement('div');
        controls.className = 'cvp-controls';

        const playButton = document.createElement('button');
        playButton.type = 'button';
        playButton.className = 'cvp-button';
        playButton.setAttribute('aria-label', 'پخش');
        playButton.textContent = '▶';

        const time = document.createElement('span');
        time.className = 'cvp-time';
        time.textContent = '00:00 / 00:00';

        const muteButton = document.createElement('button');
        muteButton.type = 'button';
        muteButton.className = 'cvp-button';
        muteButton.setAttribute('aria-label', 'روشن کردن صدا');
        muteButton.textContent = '🔇';

        const speedButton = document.createElement('button');
        speedButton.type = 'button';
        speedButton.className = 'cvp-button cvp-speed';
        speedButton.setAttribute('aria-label', 'سرعت پخش');
        speedButton.textContent = '1×';

        const fullscreenButton = document.createElement('button');
        fullscreenButton.type = 'button';
        fullscreenButton.className = 'cvp-button';
        fullscreenButton.setAttribute('aria-label', 'تمام صفحه');
        fullscreenButton.textContent = '⛶';

        const left = document.createElement('div');
        left.className = 'cvp-control-group';
        left.append(playButton, time);

        const right = document.createElement('div');
        right.className = 'cvp-control-group';
        right.append(muteButton, speedButton, fullscreenButton);

        controls.append(left, right);
        chrome.append(progress, controls);
        player.append(video, centerPlay, chrome);

        const speeds = [1, 1.25, 1.5, 2];
        let speedIndex = 0;

        const syncPlaybackUI = () => {
            const playing = !video.paused && !video.ended;

            playButton.textContent = playing ? '❚❚' : '▶';
            playButton.setAttribute(
                'aria-label',
                playing ? 'توقف موقت' : 'پخش'
            );

            centerPlay.classList.toggle('is-hidden', playing);
            player.classList.toggle('is-playing', playing);
        };

        const syncTime = () => {
            const duration = Number.isFinite(video.duration)
                ? video.duration
                : 0;

            time.textContent =
                `${formatMediaTime(video.currentTime)} / ${formatMediaTime(duration)}`;

            progress.value =
                duration > 0
                    ? String(Math.round((video.currentTime / duration) * 1000))
                    : '0';
        };

        const togglePlayback = () => {
            if (video.paused || video.ended) {
                video.play().catch(() => {});
            } else {
                video.pause();
            }
        };

        playButton.addEventListener('click', togglePlayback);
        centerPlay.addEventListener('click', togglePlayback);
        video.addEventListener('click', togglePlayback);

        video.addEventListener('play', syncPlaybackUI);
        video.addEventListener('pause', syncPlaybackUI);

        video.addEventListener('ended', () => {
            video.currentTime = 0;
            syncTime();
            syncPlaybackUI();
        });

        video.addEventListener('timeupdate', syncTime);

        video.addEventListener('loadedmetadata', () => {
            player.classList.toggle(
                'is-portrait',
                video.videoHeight > video.videoWidth
            );

            syncTime();
        });

        progress.addEventListener('input', () => {
            const duration = Number.isFinite(video.duration)
                ? video.duration
                : 0;

            if (duration > 0) {
                video.currentTime =
                    (Number(progress.value) / 1000) * duration;
            }
        });

        muteButton.addEventListener('click', () => {
            video.muted = !video.muted;
            muteButton.textContent = video.muted ? '🔇' : '🔊';
            muteButton.setAttribute(
                'aria-label',
                video.muted ? 'روشن کردن صدا' : 'بی‌صدا کردن'
            );
        });

        speedButton.addEventListener('click', () => {
            speedIndex = (speedIndex + 1) % speeds.length;
            const speed = speeds[speedIndex];

            video.playbackRate = speed;
            speedButton.textContent = `${speed}×`;
        });

        fullscreenButton.addEventListener('click', async () => {
            try {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                } else if (player.requestFullscreen) {
                    await player.requestFullscreen();
                } else {
                    video.webkitEnterFullscreen?.();
                }
            } catch (_) {
                // Browser may block fullscreen.
            }
        });

        video.addEventListener('dblclick', () => {
            fullscreenButton.click();
        });

        syncPlaybackUI();
        syncTime();

        window.setTimeout(() => {
            video.play().catch(() => {});
        }, 0);

        return player;
    }

    function buildLightboxMedia(tile) {
        if (!lightboxMedia) {
            return;
        }

        lightboxMedia.innerHTML = '';

        const src = tile.dataset.src || '';
        const poster = tile.dataset.poster || '';
        const type = tile.dataset.type || 'image';
        const title = tile.dataset.title || 'رسانه سالن';

        if (!src) {
            const fallback = document.createElement('div');

            fallback.className = 'lightbox-fallback';
            fallback.textContent = 'فایل رسانه‌ای پیدا نشد.';

            lightboxMedia.appendChild(fallback);

            return;
        }

        if (type === 'image' || type === 'gif') {
            const image = document.createElement('img');

            image.src = src;
            image.alt = title;
            image.decoding = 'async';

            lightboxMedia.appendChild(image);

            return;
        }

        lightboxMedia.appendChild(
            buildCustomVideoPlayer(
                src,
                poster,
                title
            )
        );
    }

    function updateLightbox() {
        if (!lightbox || !lightboxItems.length) {
            return;
        }

        const tile = lightboxItems[lightboxIndex];

        buildLightboxMedia(tile);

        if (lightboxType) {
            lightboxType.textContent =
                POST_LABELS[tile.dataset.type]
                || 'رسانه';
        }

        if (lightboxTitle) {
            lightboxTitle.textContent =
                tile.dataset.title || '';
        }

        if (lightboxMeta) {
            lightboxMeta.textContent =
                tile.dataset.meta || '';
        }

        if (lightboxCaption) {
            lightboxCaption.textContent =
                tile.dataset.caption || '';
        }

        const multiple =
            lightboxItems.length > 1;

        if (lightboxPrev) {
            lightboxPrev.disabled = !multiple;

            lightboxPrev.hidden = !multiple;
        }

        if (lightboxNext) {
            lightboxNext.disabled = !multiple;

            lightboxNext.hidden = !multiple;
        }
    }

    function openLightbox(tile) {
        if (!lightbox || !tile) {
            return;
        }

        lightboxItems = getVisibleGalleryTiles();

        lightboxIndex =
            lightboxItems.indexOf(tile);

        if (lightboxIndex < 0) {
            return;
        }

        updateLightbox();

        lightbox.hidden = false;

        requestAnimationFrame(() => {
            lightbox.classList.add('open');
        });

        lightbox.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'salon-lightbox-open'
        );
    }

    function closeLightbox() {
        if (!lightbox) {
            return;
        }

        const mediaVideo =
            lightboxMedia?.querySelector('video');

        if (mediaVideo) {
            mediaVideo.pause();
            mediaVideo.removeAttribute('src');
            mediaVideo.load();
        }

        lightbox.classList.remove('open');

        lightbox.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'salon-lightbox-open'
        );

        setTimeout(() => {
            if (!lightbox.classList.contains('open')) {
                lightbox.hidden = true;
            }
        }, 220);
    }

    function moveLightbox(direction) {
        if (lightboxItems.length < 2) {
            return;
        }

        lightboxIndex =
            (lightboxIndex + direction + lightboxItems.length)
            % lightboxItems.length;

        updateLightbox();
    }

    galleryTiles.forEach((tile) => {
        tile.addEventListener('click', () => {
            openLightbox(tile);
        });

        tile.addEventListener('keydown', (event) => {
            if (
                event.key !== 'Enter' &&
                event.key !== ' '
            ) {
                return;
            }

            event.preventDefault();

            openLightbox(tile);
        });
    });

    lightboxClose?.addEventListener(
        'click',
        closeLightbox
    );

    lightboxPrev?.addEventListener(
        'click',
        () => moveLightbox(-1)
    );

    lightboxNext?.addEventListener(
        'click',
        () => moveLightbox(1)
    );

    lightbox?.addEventListener('click', (event) => {
        if (
            event.target === lightbox ||
            event.target.classList.contains(
                'lightbox-backdrop'
            )
        ) {
            closeLightbox();
        }
    });

    /* ==========================================================
       SHARE
    ========================================================== */

    root
        .querySelectorAll('[data-share]')
        .forEach((button) => {
            button.addEventListener(
                'click',
                async () => {
                    const shareData = {
                        title: root.dataset.salonName,
                        url: window.location.href,
                    };

                    if (navigator.share) {
                        try {
                            await navigator.share(
                                shareData
                            );
                        } catch (_) {
                            // User cancelled.
                        }

                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(
                            window.location.href
                        );

                        showToast(
                            'لینک سالن کپی شد.'
                        );
                    } catch (_) {
                        showToast(
                            'کپی لینک انجام نشد.'
                        );
                    }
                }
            );
        });

    /* ==========================================================
       BOOKING
    ========================================================== */

    const modal =
        document.getElementById('bookingModal');

    if (modal) {
        const modalMain =
            document.getElementById('modalMain');

        const successBox =
            document.getElementById('successBox');

        const calDays =
            document.getElementById('calDays');

        const calTitle =
            document.getElementById('calTitle');

        const slotsEl =
            document.getElementById('slots');

        const slotDateEl =
            document.getElementById('slotDate');

        const slotScheduleEl =
            document.getElementById('slotSchedule');

        const confirmBtn =
            document.getElementById('confirmBtn');

        const sumBarber =
            document.getElementById('sumBarber');

        const sumService =
            document.getElementById('sumService');

        const sumTime =
            document.getElementById('sumTime');

        const successText =
            document.getElementById('successText');

        const filterBarber =
            document.getElementById('filterBarber');

        const filterService =
            document.getElementById('filterService');

        const today = new Date(TODAY_ISO + 'T12:00:00');

        /*
        |--------------------------------------------------------------------------
        | Gregorian <-> Jalali
        |--------------------------------------------------------------------------
        */

        function toJalali(gy, gm, gd) {
            const gDM = [
                0,
                31,
                59,
                90,
                120,
                151,
                181,
                212,
                243,
                273,
                304,
                334,
            ];

            let jy = gy <= 1600 ? 0 : 979;

            gy -= gy <= 1600 ? 621 : 1600;

            const gy2 = gm > 2
                ? gy + 1
                : gy;

            let days =
                365 * gy +
                Math.floor((gy2 + 3) / 4) -
                Math.floor((gy2 + 99) / 100) +
                Math.floor((gy2 + 399) / 400) -
                80 +
                gd +
                gDM[gm - 1];

            jy +=
                33 *
                Math.floor(days / 12053);

            days %= 12053;

            jy +=
                4 *
                Math.floor(days / 1461);

            days %= 1461;

            if (days > 365) {
                jy += Math.floor(
                    (days - 1) / 365
                );

                days =
                    (days - 1) % 365;
            }

            const jm =
                days < 186
                    ? 1 +
                    Math.floor(days / 31)
                    : 7 +
                    Math.floor(
                        (days - 186) / 30
                    );

            const jd =
                1 +
                (
                    days < 186
                        ? days % 31
                        : (days - 186) % 30
                );

            return [jy, jm, jd];
        }

        function toGregorian(jy, jm, jd) {
            let gy;

            if (jy > 979) {
                gy = 1600;
                jy -= 979;
            } else {
                gy = 621;
            }

            let days =
                365 * jy +
                Math.floor(jy / 33) * 8 +
                Math.floor(
                    (jy % 33 + 3) / 4
                ) +
                78 +
                jd +
                (
                    jm < 7
                        ? (jm - 1) * 31
                        : (jm - 7) * 30 + 186
                );

            gy +=
                400 *
                Math.floor(days / 146097);

            days %= 146097;

            if (days > 36524) {
                gy +=
                    100 *
                    Math.floor(
                        --days / 36524
                    );

                days %= 36524;

                if (days >= 365) {
                    days++;
                }
            }

            gy +=
                4 *
                Math.floor(days / 1461);

            days %= 1461;

            if (days > 365) {
                gy +=
                    Math.floor(
                        (days - 1) / 365
                    );

                days =
                    (days - 1) % 365;
            }

            const gd = days + 1;

            const monthDays = [
                31,
                (
                    gy % 4 === 0 &&
                    (
                        gy % 100 !== 0 ||
                        gy % 400 === 0
                    )
                ) ? 29 : 28,
                31,
                30,
                31,
                30,
                31,
                31,
                30,
                31,
                30,
                31,
            ];

            let gm = 1;
            let remaining = gd;

            while (
                remaining > monthDays[gm - 1]
                ) {
                remaining -=
                    monthDays[gm - 1];

                gm++;
            }

            return [
                gy,
                gm,
                remaining,
            ];
        }

        function jalaliYearLength(jy) {
            const current =
                toGregorian(
                    jy,
                    1,
                    1
                );

            const next =
                toGregorian(
                    jy + 1,
                    1,
                    1
                );

            const currentDate =
                new Date(
                    current[0],
                    current[1] - 1,
                    current[2]
                );

            const nextDate =
                new Date(
                    next[0],
                    next[1] - 1,
                    next[2]
                );

            return Math.round(
                (
                    nextDate - currentDate
                ) /
                86400000
            );
        }

        function daysInJalaliMonth(jy, jm) {
            if (jm <= 6) {
                return 31;
            }

            if (jm <= 11) {
                return 30;
            }

            return jalaliYearLength(jy) === 366
                ? 30
                : 29;
        }

        const todayJalali =
            toJalali(
                today.getFullYear(),
                today.getMonth() + 1,
                today.getDate()
            );

        let viewJy = todayJalali[0];
        let viewJm = todayJalali[1];

        let selectedDay = null;
        let selectedSlot = null;

        /* ======================================================
           MODAL OPEN / CLOSE
        ====================================================== */

        function updateModalAria(open) {
            modal.setAttribute(
                'aria-hidden',
                open ? 'false' : 'true'
            );
        }

        function openBooking(options = {}) {
            if (
                options.barberId &&
                filterBarber?.querySelector(
                    `option[value="${CSS.escape(options.barberId)}"]`
                )
            ) {
                filterBarber.value =
                    options.barberId;
            }

            if (
                options.serviceId &&
                filterService?.querySelector(
                    `option[value="${CSS.escape(options.serviceId)}"]`
                )
            ) {
                filterService.value =
                    options.serviceId;
            }

            selectedDay = null;
            selectedSlot = null;

            confirmBtn.disabled = true;
            sumTime.textContent = '—';

            if (slotScheduleEl) {
                slotScheduleEl.textContent = '';
            }

            modalMain.style.display = 'block';
            successBox.classList.remove('show');

            modal.classList.add('open');

            updateModalAria(true);

            document.body.classList.add(
                'salon-modal-open'
            );

            updateSummary();

            renderCalendar();
        }

        function closeBooking() {
            modal.classList.remove('open');

            updateModalAria(false);

            document.body.classList.remove(
                'salon-modal-open'
            );

            setTimeout(() => {
                selectedDay = null;
                selectedSlot = null;

                confirmBtn.disabled = true;

                sumTime.textContent = '—';

                modalMain.style.display = 'block';

                successBox.classList.remove(
                    'show'
                );
            }, 220);
        }

        root
            .querySelectorAll('[data-open-booking]')
            .forEach((button) => {
                button.addEventListener(
                    'click',
                    () => {
                        openBooking({
                            barberId:
                                button.dataset.barberId
                                || null,

                            serviceId:
                                button.dataset.serviceId
                                || null,
                        });
                    }
                );
            });

        root
            .querySelectorAll('[data-close-booking]')
            .forEach((button) => {
                button.addEventListener(
                    'click',
                    closeBooking
                );
            });

        modal.addEventListener(
            'click',
            (event) => {
                if (
                    event.target === modal
                ) {
                    closeBooking();
                }
            }
        );

        /* ======================================================
           SUMMARY
        ====================================================== */

        function updateSummary() {
            if (sumBarber) {
                sumBarber.textContent =
                    filterBarber
                        ?.options[
                        filterBarber
                            .selectedIndex
                        ]
                        ?.text
                    ?? '—';
            }

            if (sumService) {
                sumService.textContent =
                    filterService
                        ?.options[
                        filterService
                            .selectedIndex
                        ]
                        ?.text
                    ?? '—';
            }
        }

        filterBarber?.addEventListener(
            'change',
            () => {
                updateSummary();

                selectedSlot = null;

                confirmBtn.disabled = true;
                sumTime.textContent = '—';

                if (selectedDay) {
                    fetchSlots();
                }
            }
        );

        filterService?.addEventListener(
            'change',
            () => {
                updateSummary();

                selectedSlot = null;

                confirmBtn.disabled = true;
                sumTime.textContent = '—';

                if (selectedDay) {
                    fetchSlots();
                }
            }
        );

        /* ======================================================
           CALENDAR
        ====================================================== */

        function normalizeJalaliMonth(
            year,
            month
        ) {
            while (month < 1) {
                month += 12;
                year--;
            }

            while (month > 12) {
                month -= 12;
                year++;
            }

            return [
                year,
                month,
            ];
        }

        root
            .querySelectorAll('[data-cal-shift]')
            .forEach((button) => {
                button.addEventListener(
                    'click',
                    () => {
                        const direction =
                            parseInt(
                                button.dataset.calShift,
                                10
                            );

                        [
                            viewJy,
                            viewJm,
                        ] = normalizeJalaliMonth(
                            viewJy,
                            viewJm + direction
                        );

                        selectedDay = null;
                        selectedSlot = null;

                        confirmBtn.disabled =
                            true;

                        sumTime.textContent =
                            '—';

                        slotsEl.innerHTML =
                            '<div class="slots-msg">یک روز انتخاب کن</div>';

                        renderCalendar();
                    }
                );
            });

        function renderCalendar() {
            const days =
                daysInJalaliMonth(
                    viewJy,
                    viewJm
                );

            const firstGregorian =
                toGregorian(
                    viewJy,
                    viewJm,
                    1
                );

            const firstDate =
                new Date(
                    firstGregorian[0],
                    firstGregorian[1] - 1,
                    firstGregorian[2]
                );

            const offset =
                (
                    firstDate.getDay() + 1
                ) % 7;

            calTitle.textContent =
                PERSIAN_MONTHS[
                viewJm - 1
                    ] +
                ' ' +
                fa(viewJy);

            calDays.innerHTML = '';

            for (
                let index = 0;
                index < offset;
                index++
            ) {
                const empty =
                    document.createElement(
                        'div'
                    );

                empty.className =
                    'day empty';

                empty.setAttribute(
                    'aria-hidden',
                    'true'
                );

                calDays.appendChild(
                    empty
                );
            }

            const todayDate =
                new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    today.getDate()
                );

            for (
                let jd = 1;
                jd <= days;
                jd++
            ) {
                const button =
                    document.createElement(
                        'button'
                    );

                button.type = 'button';

                button.className =
                    'day';

                const gregorian =
                    toGregorian(
                        viewJy,
                        viewJm,
                        jd
                    );

                const date =
                    new Date(
                        gregorian[0],
                        gregorian[1] - 1,
                        gregorian[2]
                    );

                const isPast =
                    date < todayDate;

                button.textContent =
                    fa(jd);

                button.dataset.gregorian =
                    fmtYMD(
                        gregorian[0],
                        gregorian[1],
                        gregorian[2]
                    );

                if (isPast) {
                    button.classList.add(
                        'off'
                    );

                    button.disabled = true;
                } else {
                    button.addEventListener(
                        'click',
                        () => {
                            selectDay(
                                {
                                    jy: viewJy,
                                    jm: viewJm,
                                    jd,

                                    gy:
                                        gregorian[0],

                                    gm:
                                        gregorian[1],

                                    gd:
                                        gregorian[2],
                                },

                                button
                            );
                        }
                    );
                }

                if (
                    selectedDay &&
                    selectedDay.jy === viewJy &&
                    selectedDay.jm === viewJm &&
                    selectedDay.jd === jd
                ) {
                    button.classList.add(
                        'sel'
                    );
                }

                calDays.appendChild(
                    button
                );
            }
        }

        function selectDay(
            day,
            element
        ) {
            selectedDay = day;
            selectedSlot = null;

            confirmBtn.disabled =
                true;

            sumTime.textContent = '—';

            calDays
                .querySelectorAll('.day')
                .forEach((item) => {
                    item.classList.remove(
                        'sel'
                    );
                });

            element.classList.add('sel');

            fetchSlots();
        }

        /* ======================================================
           AVAILABILITY
        ====================================================== */

        async function fetchSlots() {
            if (!selectedDay) {
                return;
            }

            const apiDate =
                fmtYMD(
                    selectedDay.gy,
                    selectedDay.gm,
                    selectedDay.gd
                );

            slotDateEl.textContent =
                fa(selectedDay.jd) +
                ' ' +
                PERSIAN_MONTHS[
                selectedDay.jm - 1
                    ];

            slotsEl.innerHTML =
                '<div class="slots-msg">در حال بررسی ظرفیت...</div>';

            const url =
                new URL(
                    ROUTES.availability,
                    window.location.origin
                );

            url.searchParams.set(
                'barber_id',
                filterBarber.value
            );

            url.searchParams.set(
                'service_id',
                filterService.value
            );

            url.searchParams.set(
                'booking_date',
                apiDate
            );

            try {
                const response =
                    await fetch(
                        url.toString(),
                        {
                            headers: {
                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                const contentType =
                    response.headers.get(
                        'content-type'
                    ) || '';

                const data =
                    contentType.includes(
                        'application/json'
                    )
                        ? await response.json()
                        : null;

                if (!response.ok) {
                    throw new Error(
                        data?.message ||
                        Object.values(
                            data?.errors || {}
                        )[0]?.[0] ||
                        'خطا در دریافت زمان‌های خالی'
                    );
                }

                const schedule = data?.schedule || null;

                if (slotScheduleEl) {
                    if (schedule?.status === 'closed') {
                        slotScheduleEl.textContent = 'امروز سالن تعطیل است';
                    } else if (schedule?.status === 'not_configured') {
                        slotScheduleEl.textContent = 'ساعات کاری این روز تنظیم نشده';
                    } else if (Array.isArray(schedule?.intervals) && schedule.intervals.length) {
                        slotScheduleEl.textContent = schedule.intervals
                            .map((range) => fa(range.start) + ' تا ' + fa(range.end))
                            .join('  •  ');
                    } else {
                        slotScheduleEl.textContent = '';
                    }
                }

                renderSlots(
                    data?.slots || []
                );
            } catch (error) {
                slotsEl.innerHTML = `
                    <div class="slots-msg error">
                        ${escapeHtml(
                    error.message ||
                    'خطا در دریافت زمان‌ها'
                )}
                    </div>
                `;
            }
        }

        function renderSlots(slots) {
            slotsEl.innerHTML = '';

            if (!slots.length) {
                slotsEl.innerHTML =
                    '<div class="slots-msg">برای این روز زمانی خالی نیست.</div>';

                return;
            }

            slots.forEach((slot) => {
                const button =
                    document.createElement(
                        'button'
                    );

                button.type = 'button';

                button.textContent =
                    fa(slot.start);

                const available =
                    Boolean(
                        slot.available
                    );

                if (!available) {
                    button.className =
                        'slot taken';

                    button.disabled = true;
                } else {
                    button.className =
                        'slot';

                    button.addEventListener(
                        'click',
                        () => {
                            pickSlot(
                                slot.start,
                                button
                            );
                        }
                    );
                }

                slotsEl.appendChild(
                    button
                );
            });
        }

        function pickSlot(
            time,
            element
        ) {
            slotsEl
                .querySelectorAll('.slot')
                .forEach((slot) => {
                    slot.classList.remove(
                        'sel'
                    );
                });

            element.classList.add('sel');

            selectedSlot = time;

            sumTime.textContent =
                fa(selectedDay.jd) +
                ' ' +
                PERSIAN_MONTHS[
                selectedDay.jm - 1
                    ] +
                ' — ساعت ' +
                fa(time);

            confirmBtn.disabled = false;
        }

        /* ======================================================
           CONFIRM
        ====================================================== */

        confirmBtn.addEventListener(
            'click',
            confirmBooking
        );

        async function confirmBooking() {
            if (
                !selectedDay ||
                !selectedSlot
            ) {
                return;
            }

            const originalText =
                confirmBtn.textContent;

            confirmBtn.disabled = true;

            confirmBtn.textContent =
                'در حال آماده‌سازی...';

            const bookingDate =
                fmtYMD(
                    selectedDay.gy,
                    selectedDay.gm,
                    selectedDay.gd
                );

            try {
                const response =
                    await fetch(
                        ROUTES.prepare,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                Accept:
                                    'application/json',

                                'X-CSRF-TOKEN':
                                CSRF,

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            body: JSON.stringify({
                                salon_id:
                                SALON_ID,

                                barber_id:
                                filterBarber.value,

                                service_id:
                                filterService.value,

                                booking_date:
                                bookingDate,

                                start_time:
                                selectedSlot,
                            }),
                        }
                    );

                const contentType =
                    response.headers.get(
                        'content-type'
                    ) || '';

                const data =
                    contentType.includes(
                        'application/json'
                    )
                        ? await response.json()
                        : null;

                /*
                |--------------------------------------------------------------------------
                | Backend may redirect guests to Login after saving the
                | pending booking in session. Handle redirect before
                | treating HTTP 401/403 as a generic error.
                |--------------------------------------------------------------------------
                */

                if (data?.redirect && (data?.requires_auth || response.ok)) {
                    window.location.assign(
                        data.redirect
                    );

                    return;
                }

                if (!response.ok) {
                    throw new Error(
                        data?.message ||
                        Object.values(
                            data?.errors || {}
                        )[0]?.[0] ||
                        'خطا در آماده‌سازی رزرو'
                    );
                }

                if (data?.redirect) {
                    window.location.assign(
                        data.redirect
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Graceful fallback
                |--------------------------------------------------------------------------
                */

                modalMain.style.display =
                    'none';

                successText.textContent =
                    data?.message ||
                    'رزرو شما آماده تأیید است.';

                successBox.classList.add(
                    'show'
                );
            } catch (error) {
                showToast(
                    error.message ||
                    'مشکلی در رزرو پیش آمد.'
                );

                confirmBtn.disabled =
                    false;
            } finally {
                confirmBtn.textContent =
                    originalText;
            }
        }

        /* ======================================================
           ESC
        ====================================================== */

        document.addEventListener(
            'keydown',
            (event) => {
                if (
                    event.key === 'Escape'
                ) {
                    if (
                        lightbox?.classList.contains(
                            'open'
                        )
                    ) {
                        closeLightbox();

                        return;
                    }

                    if (
                        modal.classList.contains(
                            'open'
                        )
                    ) {
                        closeBooking();
                    }
                }

                if (
                    lightbox?.classList.contains(
                        'open'
                    )
                ) {
                    if (
                        event.key === 'ArrowLeft'
                    ) {
                        moveLightbox(-1);
                    }

                    if (
                        event.key === 'ArrowRight'
                    ) {
                        moveLightbox(1);
                    }
                }
            }
        );
    }

    /* ==========================================================
       INITIAL STATE
    ========================================================== */

    applyGalleryFilter('all');
})();


/* ==========================================================================
   HERO SLIDER
   Existing public components — kept isolated from salon page.
   ========================================================================== */

(function initHeroSlider() {
    const slider =
        document.getElementById('heroSlider');

    if (!slider) {
        return;
    }

    const slides =
        Array.from(
            slider.querySelectorAll(
                '.hero__slide'
            )
        );

    const dots =
        Array.from(
            document.querySelectorAll(
                '.hero__dot'
            )
        );

    if (slides.length < 2) {
        return;
    }

    let current = 0;
    let timer = null;

    const INTERVAL = 5500;

    function goTo(index) {
        current =
            (index + slides.length) %
            slides.length;

        slides.forEach(
            (slide, index) => {
                slide.classList.toggle(
                    'is-active',
                    index === current
                );
            }
        );

        dots.forEach(
            (dot, index) => {
                dot.classList.toggle(
                    'is-active',
                    index === current
                );
            }
        );
    }

    function next() {
        goTo(current + 1);
    }

    function start() {
        stop();

        timer = setInterval(
            next,
            INTERVAL
        );
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    dots.forEach((dot) => {
        dot.addEventListener(
            'click',
            () => {
                goTo(
                    parseInt(
                        dot.dataset.slide,
                        10
                    )
                );

                start();
            }
        );
    });

    slider.addEventListener(
        'mouseenter',
        stop
    );

    slider.addEventListener(
        'mouseleave',
        start
    );

    document.addEventListener(
        'visibilitychange',
        () => {
            document.hidden
                ? stop()
                : start();
        }
    );

    let touchX = null;

    slider.addEventListener(
        'touchstart',
        (event) => {
            touchX =
                event.touches[0].clientX;
        },
        {
            passive: true,
        }
    );

    slider.addEventListener(
        'touchend',
        (event) => {
            if (touchX === null) {
                return;
            }

            const delta =
                event.changedTouches[0].clientX -
                touchX;

            if (
                Math.abs(delta) > 40
            ) {
                delta > 0
                    ? goTo(current - 1)
                    : goTo(current + 1);
            }

            touchX = null;

            start();
        },
        {
            passive: true,
        }
    );

    start();
})();
