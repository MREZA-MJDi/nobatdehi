(() => {
    'use strict';

    const root = document.getElementById('salonPage');

    if (!root) return;

    const fa = (value) => String(value ?? '').replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    const pad2 = (value) => String(value).padStart(2, '0');
    const months = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];

    const POST_LABELS = {
        image: 'عکس',
        gif: 'GIF',
        video: 'ویدیو',
        reel: 'ریلز',
    };

    const today = new Date();

    /* ======================================================================
       HELPERS
       ====================================================================== */

    function toJalali(gy, gm, gd) {
        const gDM = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        let jy = gy <= 1600 ? 0 : 979;
        gy -= gy <= 1600 ? 621 : 1600;
        const gy2 = gm > 2 ? gy + 1 : gy;
        let days = 365 * gy
            + Math.floor((gy2 + 3) / 4)
            - Math.floor((gy2 + 99) / 100)
            + Math.floor((gy2 + 399) / 400)
            - 80 + gd + gDM[gm - 1];

        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const jm = days < 186
            ? 1 + Math.floor(days / 31)
            : 7 + Math.floor((days - 186) / 30);
        const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);

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

        let days = 365 * jy
            + Math.floor(jy / 33) * 8
            + Math.floor((jy % 33 + 3) / 4)
            + 78 + jd
            + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);

        gy += 400 * Math.floor(days / 146097);
        days %= 146097;

        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;

            if (days >= 365) days++;
        }

        gy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const gd = days + 1;
        const monthDays = [
            31,
            gy % 4 === 0 && (gy % 100 !== 0 || gy % 400 === 0) ? 29 : 28,
            31, 30, 31, 30, 31, 31, 30, 31, 30, 31,
        ];

        let gm = 1;
        let remaining = gd;

        while (remaining > monthDays[gm - 1]) {
            remaining -= monthDays[gm - 1];
            gm++;
        }

        return [gy, gm, remaining];
    }

    function jalaliYearLength(jy) {
        const current = toGregorian(jy, 1, 1);
        const next = toGregorian(jy + 1, 1, 1);
        const a = new Date(current[0], current[1] - 1, current[2]);
        const b = new Date(next[0], next[1] - 1, next[2]);
        return Math.round((b - a) / 86400000);
    }

    function daysInJalaliMonth(jy, jm) {
        if (jm <= 6) return 31;
        if (jm <= 11) return 30;
        return jalaliYearLength(jy) === 366 ? 30 : 29;
    }

    function normalizeMonth(year, month) {
        while (month < 1) {
            month += 12;
            year--;
        }

        while (month > 12) {
            month -= 12;
            year++;
        }

        return [year, month];
    }

    function sameMonth(a, b) {
        return a[0] === b[0] && a[1] === b[1];
    }

    function formatDate(gy, gm, gd) {
        return `${gy}-${pad2(gm)}-${pad2(gd)}`;
    }

    function isBeforeToday(gy, gm, gd) {
        const candidate = new Date(gy, gm - 1, gd);
        const current = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        return candidate < current;
    }

    function parseJsonSafe(response) {
        const type = response.headers.get('content-type') || '';
        if (!type.includes('application/json')) return null;
        return response.json();
    }

    /* ======================================================================
       REVEAL
       ====================================================================== */

    const revealElements = root.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('in');
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.08 }
        );

        revealElements.forEach((element) => {
            element.classList.add('reveal-ready');
            observer.observe(element);
        });
    } else {
        revealElements.forEach((element) => element.classList.add('in'));
    }

    /* ======================================================================
       GALLERY
       ====================================================================== */

    const galleryGrid = document.getElementById('galleryGrid');
    const galleryEmpty = document.getElementById('galleryEmpty');
    const galleryTabs = Array.from(root.querySelectorAll('.tab'));
    const galleryTiles = galleryGrid
        ? Array.from(galleryGrid.querySelectorAll('[data-gallery-item]'))
        : [];

    let galleryVisible = galleryTiles.slice();

    function stopInlineVideos() {
        galleryTiles.forEach((tile) => {
            const video = tile.querySelector('video');
            if (!video) return;
            video.pause();
            try { video.currentTime = 0; } catch (_) {}
        });
    }

    function applyGalleryFilter(filter = 'all') {
        galleryVisible = [];

        galleryTabs.forEach((tab) => {
            const active = tab.dataset.filter === filter;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.setAttribute('tabindex', active ? '0' : '-1');
        });

        galleryTiles.forEach((tile) => {
            const matches = filter === 'all' || tile.dataset.type === filter;
            tile.hidden = !matches;
            if (matches) galleryVisible.push(tile);
        });

        if (galleryEmpty) galleryEmpty.hidden = galleryVisible.length > 0;
        stopInlineVideos();
    }

    galleryTabs.forEach((tab) => {
        tab.addEventListener('click', () => applyGalleryFilter(tab.dataset.filter || 'all'));

        tab.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            event.preventDefault();

            const index = galleryTabs.indexOf(tab);
            const nextIndex = event.key === 'ArrowLeft'
                ? Math.min(index + 1, galleryTabs.length - 1)
                : Math.max(index - 1, 0);

            galleryTabs[nextIndex]?.focus();
        });
    });

    galleryTiles.forEach((tile) => {
        const video = tile.querySelector('video');

        if (video) {
            tile.addEventListener('mouseenter', () => video.play().catch(() => {}));
            tile.addEventListener('mouseleave', () => {
                video.pause();
                try { video.currentTime = 0; } catch (_) {}
            });
        }
    });

    const lightbox = document.getElementById('mediaLightbox');
    const lightboxMedia = document.getElementById('lightboxMedia');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxMeta = document.getElementById('lightboxMeta');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const lightboxType = document.getElementById('lightboxType');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');
    const lightboxDialog = lightbox?.querySelector('.lightbox-dialog');

    let lightboxIndex = 0;
    let lightboxPreviousFocus = null;

    function renderLightboxMedia(tile) {
        if (!lightboxMedia) return;

        lightboxMedia.innerHTML = '';

        const src = tile.dataset.src || '';
        const poster = tile.dataset.poster || '';
        const type = tile.dataset.type || 'image';

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
            image.alt = tile.dataset.title || 'رسانه سالن';
            image.decoding = 'async';
            lightboxMedia.appendChild(image);
            return;
        }

        const video = document.createElement('video');
        video.src = src;
        video.controls = true;
        video.autoplay = true;
        video.muted = true;
        video.playsInline = true;
        video.preload = 'metadata';
        if (poster) video.poster = poster;
        lightboxMedia.appendChild(video);
        video.play().catch(() => {});
    }

    function updateLightbox() {
        const tile = galleryVisible[lightboxIndex];
        if (!tile) return;

        renderLightboxMedia(tile);

        if (lightboxType) lightboxType.textContent = POST_LABELS[tile.dataset.type] || 'رسانه';
        if (lightboxTitle) lightboxTitle.textContent = tile.dataset.title || '';
        if (lightboxMeta) lightboxMeta.textContent = tile.dataset.meta || '';
        if (lightboxCaption) lightboxCaption.textContent = tile.dataset.caption || '';

        const multiple = galleryVisible.length > 1;
        if (lightboxPrev) lightboxPrev.hidden = !multiple;
        if (lightboxNext) lightboxNext.hidden = !multiple;
    }

    function openLightbox(tile) {
        if (!lightbox || !tile) return;

        const index = galleryVisible.indexOf(tile);
        if (index < 0) return;

        lightboxIndex = index;
        lightboxPreviousFocus = document.activeElement;
        updateLightbox();

        lightbox.hidden = false;
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('salon-lightbox-open');

        requestAnimationFrame(() => {
            lightbox.classList.add('open');
            lightboxClose?.focus();
        });
    }

    function closeLightbox() {
        if (!lightbox) return;

        const video = lightboxMedia?.querySelector('video');
        if (video) {
            video.pause();
            video.removeAttribute('src');
            video.load();
        }

        lightbox.classList.remove('open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('salon-lightbox-open');

        window.setTimeout(() => {
            if (!lightbox.classList.contains('open')) lightbox.hidden = true;
            lightboxPreviousFocus?.focus?.();
            lightboxPreviousFocus = null;
        }, 220);
    }

    function moveLightbox(direction) {
        if (galleryVisible.length < 2) return;
        lightboxIndex = (lightboxIndex + direction + galleryVisible.length) % galleryVisible.length;
        updateLightbox();
    }

    galleryTiles.forEach((tile) => {
        tile.addEventListener('click', () => openLightbox(tile));
        tile.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            openLightbox(tile);
        });
    });

    lightboxClose?.addEventListener('click', closeLightbox);
    lightboxPrev?.addEventListener('click', () => moveLightbox(-1));
    lightboxNext?.addEventListener('click', () => moveLightbox(1));

    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox || event.target.classList.contains('lightbox-backdrop')) {
            closeLightbox();
        }
    });

    lightbox?.addEventListener('keydown', (event) => {
        if (!lightbox.classList.contains('open')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeLightbox();
            return;
        }

        if (event.key === 'ArrowLeft') moveLightbox(-1);
        if (event.key === 'ArrowRight') moveLightbox(1);

        if (event.key !== 'Tab' || !lightboxDialog) return;

        const focusables = Array.from(lightboxDialog.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        ));

        if (!focusables.length) return;

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    applyGalleryFilter('all');

    /* ======================================================================
       SHARE
       ====================================================================== */

    const toast = document.getElementById('salonToast');
    let toastTimer = null;

    function showToast(message) {
        if (!toast) {
            window.toast?.(message, 'danger');
            return;
        }

        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.remove('show'), 2600);
    }

    root.querySelectorAll('[data-share]').forEach((button) => {
        button.addEventListener('click', async () => {
            const shareData = {
                title: root.dataset.salonName || 'NOBAT',
                url: window.location.href,
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                } catch (_) {
                    // User cancelled sharing.
                }
                return;
            }

            try {
                await navigator.clipboard.writeText(window.location.href);
                showToast('لینک سالن کپی شد.');
            } catch (_) {
                showToast('کپی لینک انجام نشد.');
            }
        });
    });

    /* ======================================================================
       BOOKING
       ====================================================================== */

    const modal = document.getElementById('bookingModal');

    if (!modal) return;

    const modalMain = document.getElementById('modalMain');
    const successBox = document.getElementById('successBox');
    const calDays = document.getElementById('calDays');
    const calTitle = document.getElementById('calTitle');
    const slotsEl = document.getElementById('slots');
    const slotDateEl = document.getElementById('slotDate');
    const confirmBtn = document.getElementById('confirmBtn');
    const sumBarber = document.getElementById('sumBarber');
    const sumService = document.getElementById('sumService');
    const sumTime = document.getElementById('sumTime');
    const successText = document.getElementById('successText');
    const filterBarber = document.getElementById('filterBarber');
    const filterService = document.getElementById('filterService');
    const monthButtons = Array.from(root.querySelectorAll('[data-cal-shift]'));

    const todayJalali = toJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
    let viewJy = todayJalali[0];
    let viewJm = todayJalali[1];
    let selectedDay = null;
    let selectedSlot = null;
    let slotsRequest = null;
    let previousFocus = null;

    function updateSummary() {
        if (sumBarber) sumBarber.textContent = filterBarber?.options[filterBarber.selectedIndex]?.text || '—';
        if (sumService) sumService.textContent = filterService?.options[filterService.selectedIndex]?.text || '—';
        if (sumTime) {
            sumTime.textContent = selectedDay && selectedSlot
                ? `${fa(selectedDay.jd)} ${months[selectedDay.jm - 1]} — ساعت ${fa(selectedSlot)}`
                : '—';
        }
    }

    function resetSlotSelection() {
        selectedSlot = null;
        if (confirmBtn) confirmBtn.disabled = true;
        if (sumTime) sumTime.textContent = '—';
    }

    function updateMonthNavigation() {
        const isCurrentMonth = sameMonth([viewJy, viewJm], todayJalali);
        if (monthButtons[0]) {
            monthButtons[0].disabled = isCurrentMonth;
            monthButtons[0].setAttribute('aria-disabled', isCurrentMonth ? 'true' : 'false');
        }
    }

    function renderCalendar() {
        const days = daysInJalaliMonth(viewJy, viewJm);
        const first = toGregorian(viewJy, viewJm, 1);
        const firstDate = new Date(first[0], first[1] - 1, first[2]);
        const offset = (firstDate.getDay() + 1) % 7;

        calTitle.textContent = `${months[viewJm - 1]} ${fa(viewJy)}`;
        calDays.innerHTML = '';
        updateMonthNavigation();

        for (let index = 0; index < offset; index++) {
            const empty = document.createElement('span');
            empty.setAttribute('aria-hidden', 'true');
            empty.className = 'day empty';
            calDays.appendChild(empty);
        }

        for (let jd = 1; jd <= days; jd++) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'day';
            button.textContent = fa(jd);

            const gregorian = toGregorian(viewJy, viewJm, jd);
            const past = isBeforeToday(gregorian[0], gregorian[1], gregorian[2]);
            const active = selectedDay
                && selectedDay.jy === viewJy
                && selectedDay.jm === viewJm
                && selectedDay.jd === jd;

            button.dataset.gregorian = formatDate(gregorian[0], gregorian[1], gregorian[2]);

            if (past) {
                button.classList.add('off');
                button.disabled = true;
            } else {
                button.addEventListener('click', () => selectDay({
                    jy: viewJy,
                    jm: viewJm,
                    jd,
                    gy: gregorian[0],
                    gm: gregorian[1],
                    gd: gregorian[2],
                }, button));
            }

            if (active) button.classList.add('sel');
            calDays.appendChild(button);
        }
    }

    function selectDay(day, button) {
        selectedDay = day;
        resetSlotSelection();

        calDays.querySelectorAll('.day').forEach((item) => item.classList.remove('sel'));
        button.classList.add('sel');

        if (slotDateEl) slotDateEl.textContent = `${fa(day.jd)} ${months[day.jm - 1]}`;
        fetchSlots();
    }

    async function fetchSlots() {
        if (!selectedDay || !filterBarber?.value || !filterService?.value) return;

        slotsRequest?.abort?.();
        slotsRequest = new AbortController();

        const bookingDate = formatDate(selectedDay.gy, selectedDay.gm, selectedDay.gd);
        const url = new URL(root.dataset.availabilityUrl, window.location.origin);
        url.searchParams.set('barber_id', filterBarber.value);
        url.searchParams.set('service_id', filterService.value);
        url.searchParams.set('booking_date', bookingDate);

        slotsEl.innerHTML = '<div class="slots-msg">در حال بررسی ظرفیت…</div>';

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: slotsRequest.signal,
            });

            const data = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(
                    data?.message
                    || Object.values(data?.errors || {})[0]?.[0]
                    || 'خطا در دریافت زمان‌های خالی'
                );
            }

            renderSlots(Array.isArray(data?.slots) ? data.slots : []);
        } catch (error) {
            if (error?.name === 'AbortError') return;

            slotsEl.innerHTML = `<div class="slots-msg error">${String(error?.message || 'خطا در دریافت زمان‌ها')}</div>`;
        }
    }

    function renderSlots(items) {
        slotsEl.innerHTML = '';

        if (!items.length) {
            slotsEl.innerHTML = '<div class="slots-msg">برای این روز زمانی خالی نیست.</div>';
            return;
        }

        items.forEach((slot) => {
            const button = document.createElement('button');
            const available = slot.available !== false;
            const status = slot.status || (available ? 'available' : 'confirmed');

            button.type = 'button';
            button.className = `slot${status === 'pending' ? ' pending' : ''}${available && String(slot.start) === String(selectedSlot) ? ' sel' : ''}`;
            button.disabled = !available;
            button.setAttribute('aria-label', available
                ? `${fa(slot.start)}${status === 'pending' ? ' — یک درخواست در انتظار تأیید نیز وجود دارد' : ''}`
                : `${fa(slot.start)} — زمان رزرو شده است`);

            const time = document.createElement('strong');
            time.textContent = fa(slot.start);
            button.appendChild(time);

            if (status === 'pending' && available) {
                const hint = document.createElement('small');
                hint.textContent = 'در انتظار تأیید';
                button.appendChild(hint);
                button.title = 'این زمان فعلاً قابل انتخاب است اما درخواست دیگری برای آن در انتظار تأیید است.';
            } else if (!available) {
                const hint = document.createElement('small');
                hint.textContent = 'تکمیل شده';
                button.appendChild(hint);
            }

            if (available) {
                button.addEventListener('click', () => {
                    selectedSlot = String(slot.start);
                    renderSlots(items);
                    updateSummary();
                });
            }

            slotsEl.appendChild(button);
        });
    }

    function openBooking(options = {}) {
        if (options.barberId && filterBarber?.querySelector(`option[value="${CSS.escape(String(options.barberId))}"]`)) {
            filterBarber.value = String(options.barberId);
        }

        if (options.serviceId && filterService?.querySelector(`option[value="${CSS.escape(String(options.serviceId))}"]`)) {
            filterService.value = String(options.serviceId);
        }

        previousFocus = document.activeElement;
        selectedSlot = null;
        selectedDay = null;
        resetSlotSelection();
        updateSummary();

        viewJy = todayJalali[0];
        viewJm = todayJalali[1];
        modalMain.style.display = 'block';
        successBox?.classList.remove('show');
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('salon-modal-open');
        renderCalendar();

        requestAnimationFrame(() => modal.querySelector('.modal-close')?.focus());
    }

    function closeBooking() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('salon-modal-open');
        slotsRequest?.abort?.();

        window.setTimeout(() => {
            if (modal.classList.contains('open')) return;
            selectedDay = null;
            selectedSlot = null;
            resetSlotSelection();
            modalMain.style.display = 'block';
            successBox?.classList.remove('show');
            previousFocus?.focus?.();
            previousFocus = null;
        }, 220);
    }

    root.querySelectorAll('[data-open-booking]').forEach((button) => {
        button.addEventListener('click', () => openBooking({
            barberId: button.dataset.barberId || null,
            serviceId: button.dataset.serviceId || null,
        }));
    });

    root.querySelectorAll('[data-close-booking]').forEach((button) => {
        button.addEventListener('click', closeBooking);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeBooking();
    });

    filterBarber?.addEventListener('change', () => {
        resetSlotSelection();
        updateSummary();
        if (selectedDay) fetchSlots();
    });

    filterService?.addEventListener('change', () => {
        resetSlotSelection();
        updateSummary();
        if (selectedDay) fetchSlots();
    });

    monthButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            const direction = parseInt(button.dataset.calShift || '0', 10);
            [viewJy, viewJm] = normalizeMonth(viewJy, viewJm + direction);
            selectedDay = null;
            resetSlotSelection();
            slotsEl.innerHTML = '<div class="slots-msg">یک روز انتخاب کن</div>';
            renderCalendar();
        });
    });

    modal.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('open')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeBooking();
            return;
        }

        if (event.key !== 'Tab') return;

        const focusables = Array.from(modal.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [href], [tabindex]:not([tabindex="-1"])'
        ));

        if (!focusables.length) return;

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    confirmBtn?.addEventListener('click', async () => {
        if (!selectedDay || !selectedSlot || !filterBarber?.value || !filterService?.value) return;

        confirmBtn.disabled = true;
        const originalText = confirmBtn.textContent;
        confirmBtn.textContent = 'در حال آماده‌سازی…';

        const bookingDate = formatDate(selectedDay.gy, selectedDay.gm, selectedDay.gd);

        try {
            const response = await fetch(root.dataset.prepareUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': root.dataset.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    salon_id: Number(root.dataset.salonId),
                    barber_id: Number(filterBarber.value),
                    service_id: Number(filterService.value),
                    booking_date: bookingDate,
                    start_time: selectedSlot,
                }),
            });

            const data = await parseJsonSafe(response);

            if (response.status === 401 && data?.redirect) {
                window.location.assign(data.redirect);
                return;
            }

            if (!response.ok) {
                throw new Error(
                    data?.message
                    || Object.values(data?.errors || {})[0]?.[0]
                    || 'خطا در آماده‌سازی رزرو'
                );
            }

            if (data?.redirect) {
                window.location.assign(data.redirect);
                return;
            }

            modalMain.style.display = 'none';
            if (successText) successText.textContent = data?.message || 'رزرو شما آماده تأیید است.';
            successBox?.classList.add('show');
        } catch (error) {
            showToast(error?.message || 'مشکلی در رزرو پیش آمد.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });
})();
