/**
 * Salon page — Booking & UI interactions
 * Reads config from #salonPage data attributes.
 */
(function () {
    'use strict';

    const root = document.getElementById('salonPage');
    if (!root) return;

    /* ═══════════ CONFIG ═══════════ */
    const SALON_ID  = root.dataset.salonId;
    const IS_AUTH   = root.dataset.isAuth === '1';
    const LOGIN_URL = root.dataset.loginUrl;
    const CSRF      = root.dataset.csrf;
    const ROUTES    = {
        availability: root.dataset.availabilityUrl,
        prepare:      root.dataset.prepareUrl,
    };

    /* ═══════════ HELPERS ═══════════ */
    const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);

    const PERSIAN_MONTHS = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];

    function toJalali(gy, gm, gd) {
        const gDM = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        let jy = gy <= 1600 ? 0 : 979;
        gy -= gy <= 1600 ? 621 : 1600;
        const gy2 = gm > 2 ? gy + 1 : gy;
        let days = 365 * gy +
            Math.floor((gy2 + 3) / 4) -
            Math.floor((gy2 + 99) / 100) +
            Math.floor((gy2 + 399) / 400) -
            80 + gd + gDM[gm - 1];
        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;
        if (days > 365) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }
        const jm = days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
        const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
        return [jy, jm, jd];
    }

    const pad2 = (n) => String(n).padStart(2, '0');
    const fmtYMD = (y, m, d) => `${y}-${pad2(m)}-${pad2(d)}`;

    /* ═══════════ REVEAL ON SCROLL ═══════════ */
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            });
        },
        { threshold: 0.12 }
    );
    root.querySelectorAll('.reveal').forEach((el) => io.observe(el));

    /* ═══════════ GALLERY TABS ═══════════ */
    root.querySelectorAll('.tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            root.querySelectorAll('.tab').forEach((x) => x.classList.remove('active'));
            tab.classList.add('active');
            const f = tab.dataset.filter;
            root.querySelectorAll('#galleryGrid .tile').forEach((tile) => {
                tile.style.display =
                    f === 'all' || tile.dataset.type === f ? '' : 'none';
            });
        });
    });

    /* ═══════════ SHARE ═══════════ */
    root.querySelectorAll('[data-share]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (navigator.share) {
                navigator.share({
                    title: root.dataset.salonName,
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href)
                    .then(() => alert('لینک کپی شد'))
                    .catch(() => {});
            }
        });
    });

    /* ═══════════ MODAL ELEMENTS ═══════════ */
    const modal      = document.getElementById('bookingModal');
    const modalMain  = document.getElementById('modalMain');
    const successBox = document.getElementById('successBox');
    const calDays    = document.getElementById('calDays');
    const calTitle   = document.getElementById('calTitle');
    const slotsEl    = document.getElementById('slots');
    const slotDateEl = document.getElementById('slotDate');
    const confirmBtn = document.getElementById('confirmBtn');
    const sumBarber  = document.getElementById('sumBarber');
    const sumService = document.getElementById('sumService');
    const sumTime    = document.getElementById('sumTime');
    const successTxt = document.getElementById('successText');

    const filterBarber  = document.getElementById('filterBarber');
    const filterService = document.getElementById('filterService');

    if (!modal) return;

    const today = new Date();
    let viewYear  = today.getFullYear();
    let viewMonth = today.getMonth();
    let selDay    = null;
    let selSlot   = null;

    /* ═══════════ OPEN / CLOSE ═══════════ */
    function openBooking(barberId) {
        if (barberId) filterBarber.value = barberId;

        modalMain.style.display = 'block';
        successBox.classList.remove('show');
        modal.classList.add('open');
        document.body.classList.add('salon-modal-open');

        updateSummary();
        renderCalendar();
    }

    function closeBooking() {
        modal.classList.remove('open');
        document.body.classList.remove('salon-modal-open');

        setTimeout(() => {
            selSlot = null;
            selDay = null;
            confirmBtn.disabled = true;
            sumTime.textContent = '—';
            modalMain.style.display = 'block';
            successBox.classList.remove('show');
        }, 300);
    }

    root.querySelectorAll('[data-open-booking]').forEach((btn) => {
        btn.addEventListener('click', () => {
            openBooking(btn.dataset.barberId || null);
        });
    });

    root.querySelectorAll('[data-close-booking]').forEach((btn) => {
        btn.addEventListener('click', closeBooking);
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeBooking();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeBooking();
    });

    /* ═══════════ SUMMARY ═══════════ */
    function updateSummary() {
        sumBarber.textContent =
            filterBarber.options[filterBarber.selectedIndex]?.text ?? '—';
        sumService.textContent =
            filterService.options[filterService.selectedIndex]?.text ?? '—';
    }

    filterBarber.addEventListener('change', () => {
        updateSummary();
        if (selDay) fetchSlots();
    });

    filterService.addEventListener('change', () => {
        updateSummary();
        if (selDay) fetchSlots();
    });

    /* ═══════════ CALENDAR ═══════════ */
    root.querySelectorAll('[data-cal-shift]').forEach((btn) => {
        btn.addEventListener('click', () => {
            shiftMonth(parseInt(btn.dataset.calShift, 10));
        });
    });

    function shiftMonth(dir) {
        viewMonth += dir;
        if (viewMonth > 11) { viewMonth = 0; viewYear++; }
        if (viewMonth < 0)  { viewMonth = 11; viewYear--; }
        renderCalendar();
    }

    function renderCalendar() {
        const [jy, jm] = toJalali(viewYear, viewMonth + 1, 15);
        calTitle.textContent = PERSIAN_MONTHS[jm - 1] + ' ' + fa(jy);

        calDays.innerHTML = '';

        const firstOfMonth = new Date(viewYear, viewMonth, 1);
        const daysInMonth  = new Date(viewYear, viewMonth + 1, 0).getDate();
        const startOffset  = (firstOfMonth.getDay() + 1) % 7;

        for (let i = 0; i < startOffset; i++) {
            const e = document.createElement('div');
            e.className = 'day empty';
            calDays.appendChild(e);
        }

        const todayMidnight = new Date(
            today.getFullYear(), today.getMonth(), today.getDate()
        );

        for (let d = 1; d <= daysInMonth; d++) {
            const el = document.createElement('div');
            const thisDate = new Date(viewYear, viewMonth, d);
            const isPast = thisDate < todayMidnight;

            const jd = toJalali(viewYear, viewMonth + 1, d)[2];
            el.textContent = fa(jd);

            if (isPast) {
                el.className = 'day off';
            } else {
                el.className = 'day';
                el.addEventListener('click', () => selectDay(d, el));

                if (
                    selDay &&
                    selDay.y === viewYear &&
                    selDay.m === viewMonth &&
                    selDay.d === d
                ) {
                    el.classList.add('sel');
                }
            }
            calDays.appendChild(el);
        }
    }

    function selectDay(d, el) {
        selDay = { y: viewYear, m: viewMonth, d };
        selSlot = null;
        confirmBtn.disabled = true;
        sumTime.textContent = '—';

        calDays.querySelectorAll('.day').forEach((x) => x.classList.remove('sel'));
        el.classList.add('sel');

        fetchSlots();
    }

    /* ═══════════ FETCH AVAILABILITY ═══════════ */
    async function fetchSlots() {
        if (!selDay) return;

        const date = fmtYMD(selDay.y, selDay.m + 1, selDay.d);
        const [, jm, jd] = toJalali(selDay.y, selDay.m + 1, selDay.d);
        slotDateEl.textContent = fa(jd) + ' ' + PERSIAN_MONTHS[jm - 1];

        slotsEl.innerHTML = '<div class="slots-msg">در حال بارگذاری…</div>';

        const url = new URL(ROUTES.availability, window.location.origin);
        url.searchParams.set('barber_id', filterBarber.value);
        url.searchParams.set('service_id', filterService.value);
        url.searchParams.set('booking_date', date);

        try {
            const res = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(
                    data.message ||
                    Object.values(data.errors || {})[0]?.[0] ||
                    'خطا در دریافت ساعت‌ها'
                );
            }

            renderSlots(data.slots || []);
        } catch (err) {
            slotsEl.innerHTML =
                `<div class="slots-msg error">${err.message || 'خطا در دریافت ساعت‌ها'}</div>`;
        }
    }

    function renderSlots(slots) {
        slotsEl.innerHTML = '';

        if (!slots.length) {
            slotsEl.innerHTML = '<div class="slots-msg">این روز ساعت خالی نداره</div>';
            return;
        }

        slots.forEach((s) => {
            const el = document.createElement('div');
            el.textContent = s.start;

            if (!s.available) {
                el.className = 'slot taken';
            } else {
                el.className = 'slot';
                el.addEventListener('click', () => pickSlot(s.start, el));
            }
            slotsEl.appendChild(el);
        });
    }

    function pickSlot(time, el) {
        slotsEl.querySelectorAll('.slot').forEach((x) => x.classList.remove('sel'));
        el.classList.add('sel');
        selSlot = time;

        const [, jm, jd] = toJalali(selDay.y, selDay.m + 1, selDay.d);
        sumTime.textContent =
            fa(jd) + ' ' + PERSIAN_MONTHS[jm - 1] + ' — ساعت ' + time;
        confirmBtn.disabled = false;
    }

    /* ═══════════ CONFIRM ═══════════ */
    confirmBtn.addEventListener('click', confirmBooking);

    async function confirmBooking() {
        if (!selSlot || !selDay) return;

        if (!IS_AUTH) {
            window.location.href = LOGIN_URL;
            return;
        }

        const btn = confirmBtn;
        btn.disabled = true;
        btn.textContent = 'در حال ثبت…';

        const date = fmtYMD(selDay.y, selDay.m + 1, selDay.d);

        try {
            const res = await fetch(ROUTES.prepare, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    salon_id:     SALON_ID,
                    barber_id:    filterBarber.value,
                    service_id:   filterService.value,
                    booking_date: date,
                    start_time:   selSlot,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(
                    data.message ||
                    Object.values(data.errors || {})[0]?.[0] ||
                    'خطا در ثبت رزرو'
                );
            }

            const [, jm, jd] = toJalali(selDay.y, selDay.m + 1, selDay.d);
            successTxt.textContent =
                'نوبت شما برای ' + fa(jd) + ' ' + PERSIAN_MONTHS[jm - 1] +
                ' ساعت ' + selSlot + ' ثبت شد. ' +
                (data.message || 'برای تأیید به سالن ارسال شد.');

            modalMain.style.display = 'none';
            successBox.classList.add('show');
        } catch (err) {
            alert(err.message || 'مشکلی پیش اومد');
            btn.disabled = false;
        } finally {
            btn.textContent = 'تأیید رزرو';
        }
    }
})();
/* ==========================================================================
   HERO SLIDER
   ========================================================================== */
(function initHeroSlider() {
    const slider = document.getElementById('heroSlider');
    if (!slider) return;

    const slides = Array.from(slider.querySelectorAll('.hero__slide'));
    const dots   = Array.from(document.querySelectorAll('.hero__dot'));
    if (slides.length < 2) return;

    let current = 0;
    let timer   = null;
    const INTERVAL = 5500;

    function goTo(i) {
        current = (i + slides.length) % slides.length;

        slides.forEach((s, idx) => s.classList.toggle('is-active', idx === current));
        dots.forEach((d, idx) => d.classList.toggle('is-active', idx === current));
    }

    function next() { goTo(current + 1); }

    function start() {
        stop();
        timer = setInterval(next, INTERVAL);
    }
    function stop() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    // Dots click
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.slide, 10));
            start(); // ریست تایمر
        });
    });

    // Pause on hover
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    // Pause when tab hidden
    document.addEventListener('visibilitychange', () => {
        document.hidden ? stop() : start();
    });

    // Touch swipe
    let touchX = null;
    slider.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend', e => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 40) dx > 0 ? goTo(current - 1) : goTo(current + 1);
        touchX = null;
        start();
    }, { passive: true });

    start();
})();
