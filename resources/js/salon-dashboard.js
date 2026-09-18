(() => {
    'use strict';

    const root = document.querySelector('[data-dashboard]');
    if (!root) return;

    const endpoint = root.dataset.dashboardEndpoint;
    if (!endpoint) return;

    const faDigits = (value) => String(value ?? '').replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
    const money = (value) => new Intl.NumberFormat('fa-IR').format(Number(value || 0)) + ' تومان';

    const setMetric = (name, value) => {
        root.querySelectorAll('[data-dashboard-metric="' + name + '"]').forEach(node => {
            node.textContent = faDigits(value);
        });
    };

    const setMoney = (name, value) => {
        root.querySelectorAll('[data-dashboard-money="' + name + '"]').forEach(node => {
            node.textContent = money(value);
        });
    };

    const statusMap = {
        pending: ['در انتظار', 'pending'],
        confirmed: ['تأیید شده', 'confirmed'],
        completed: ['انجام شده', 'completed'],
        cancelled: ['لغو شده', 'cancelled'],
    };

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[ch]));

    const jalaliDate = iso => {
        const date = new Date(String(iso || '') + 'T12:00:00Z');
        if (Number.isNaN(date.getTime())) return '';
        return new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', {
            year: 'numeric', month: '2-digit', day: '2-digit', timeZone: 'UTC'
        }).format(date).replace(/\u200e/g, '');
    };

    const setApiState = (ok, label) => {
        const node = root.querySelector('[data-dashboard-api-state]');
        if (!node) return;
        node.textContent = label;
        node.classList.toggle('is-error', !ok);
    };

    const updateClock = () => {
        const node = root.querySelector('[data-dashboard-updated]');
        if (!node) return;
        node.textContent = new Intl.DateTimeFormat('fa-IR', { hour: '2-digit', minute: '2-digit' }).format(new Date());
    };

    const renderBars = (name, points) => {
        const panel = root.querySelector('[data-chart-panel="' + name + '"]');
        const bars = panel && panel.querySelector('.nd-bars');
        if (!bars) return;

        const items = Array.isArray(points) ? points : [];
        const max = Math.max(1, ...items.map(item => Number(item.value || 0)));
        bars.innerHTML = '';

        items.forEach(item => {
            const value = Number(item.value || 0);
            const column = document.createElement('div');
            column.className = 'nd-bar-column';

            const valueNode = document.createElement('span');
            valueNode.className = 'nd-bar-value';
            valueNode.textContent = value > 0 ? money(value) : '—';

            const track = document.createElement('div');
            track.className = 'nd-bar-track';

            const fill = document.createElement('div');
            fill.className = 'nd-bar-fill';
            fill.style.height = Math.max(4, Math.round(value / max * 100)) + '%';
            fill.title = money(value);

            const label = document.createElement('span');
            label.className = 'nd-bar-label';
            label.textContent = item.label || jalaliDate(item.date);

            track.appendChild(fill);
            column.appendChild(valueNode);
            column.appendChild(track);
            column.appendChild(label);
            bars.appendChild(column);
        });
    };

    const renderUpcoming = bookings => {
        const list = root.querySelector('[data-dashboard-upcoming]');
        if (!list) return;

        const items = Array.isArray(bookings) ? bookings : [];
        if (!items.length) {
            list.innerHTML = '<div class="nd-empty"><strong>هنوز نوبت پیش‌رویی ندارید.</strong><span>برای رزرو حضوری از «نوبت دستی» استفاده کنید.</span></div>';
            return;
        }

        list.innerHTML = items.map(booking => {
            const pair = statusMap[booking.status] || ['نامشخص', 'default'];
            return '<a class="nd-booking-row" href="/salon/bookings/' + encodeURIComponent(booking.id) + '">' +
                '<div class="nd-booking-time"><strong>' + escapeHtml(booking.startTime || '—') + '</strong><span>' + escapeHtml(jalaliDate(booking.date)) + '</span></div>' +
                '<div class="nd-booking-main"><strong>' + escapeHtml(booking.customer || 'مشتری') + '</strong><span>' + escapeHtml(booking.service || 'خدمت') + ' · ' + escapeHtml(booking.barber || 'متخصص') + '</span></div>' +
                '<span class="nd-status nd-status--' + pair[1] + '">' + pair[0] + '</span></a>';
        }).join('');
    };

    const updateAlert = data => {
        const main = root.querySelector('.nd-dashboard-alert-main');
        if (!main) return;
        const heading = main.querySelector('h2');
        const desc = main.querySelector('p');
        const action = main.querySelector('.nd-alert-action');
        const pending = Number(data.metrics?.pendingBookings || 0);
        const next = Array.isArray(data.upcomingBookings) ? data.upcomingBookings[0] : null;

        if (pending > 0) {
            if (heading) heading.textContent = faDigits(pending) + ' نوبت منتظر رسیدگی است.';
            if (desc) desc.textContent = 'درخواست‌های معطل را بررسی کن تا برنامه سالن مرتب بماند.';
            if (action) { action.href = '/salon/bookings?status=pending'; action.innerHTML = 'بررسی نوبت‌های منتظر <span>←</span>'; }
            return;
        }

        if (next) {
            if (heading) heading.textContent = 'نوبت بعدی ساعت ' + (next.startTime || '—');
            if (desc) desc.textContent = (next.customer || 'مشتری') + ' · ' + (next.service || 'خدمت') + ' · ' + (next.barber || 'متخصص');
            if (action) { action.href = '/salon/bookings/' + encodeURIComponent(next.id); action.innerHTML = 'باز کردن نوبت <span>←</span>'; }
            return;
        }

        if (heading) heading.textContent = 'امروز نوبت بعدی ثبت نشده.';
        if (desc) desc.textContent = 'برای مشتری حضوری یا رزرو از قبل، می‌توانی نوبت دستی ثبت کنی.';
        if (action) { action.href = '/salon/bookings/create'; action.innerHTML = 'ثبت نوبت دستی <span>←</span>'; }
    };

    const refresh = async () => {
        if (root.classList.contains('is-refreshing')) return;
        root.classList.add('is-refreshing');
        setApiState(true, 'در حال بروزرسانی...');

        try {
            const response = await fetch(endpoint, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', cache: 'no-store'
            });
            const payload = await response.json();
            if (!response.ok || payload?.ok !== true) throw new Error('DASHBOARD_API_FAILED');

            const data = payload.data || {};
            const metrics = data.metrics || {};

            ['todayBookings','pendingBookings','confirmedToday','completedToday','cancelledToday','monthBookings','activeBarbers','activeServices','unreadNotifications'].forEach(name => setMetric(name, metrics[name] || 0));
            ['todayRevenue','weekRevenue','monthRevenue'].forEach(name => setMoney(name, metrics[name] || 0));

            renderBars('weekly', data.revenue?.weekly || []);
            renderBars('monthly', data.revenue?.monthly || []);

            const week = (data.revenue?.weekly || []).reduce((sum, point) => sum + Number(point.value || 0), 0);
            const month = (data.revenue?.monthly || []).reduce((sum, point) => sum + Number(point.value || 0), 0);
            const weeklyStrong = root.querySelector('[data-chart-panel="weekly"] .nd-chart-summary strong');
            const monthlyStrong = root.querySelector('[data-chart-panel="monthly"] .nd-chart-summary strong');
            if (weeklyStrong) weeklyStrong.textContent = money(week);
            if (monthlyStrong) monthlyStrong.textContent = money(month);

            renderUpcoming(data.upcomingBookings || []);
            updateAlert(data);
            updateClock();
            setApiState(true, 'متصل');
            root.classList.remove('is-error');
        } catch (error) {
            console.error(error);
            setApiState(false, 'ارتباط لحظه‌ای برقرار نشد');
            root.classList.add('is-error');
        } finally {
            window.setTimeout(() => root.classList.remove('is-refreshing'), 350);
        }
    };

    root.querySelectorAll('[data-chart-tab]').forEach(button => {
        button.addEventListener('click', () => {
            const name = button.dataset.chartTab;
            root.querySelectorAll('[data-chart-tab]').forEach(item => {
                const active = item.dataset.chartTab === name;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            root.querySelectorAll('[data-chart-panel]').forEach(panel => {
                panel.hidden = panel.dataset.chartPanel !== name;
            });
        });
    });

    root.querySelector('[data-dashboard-refresh]')?.addEventListener('click', refresh);
    refresh();
    window.setInterval(refresh, 60000);
})();