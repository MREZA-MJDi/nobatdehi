@extends('layouts.customer')

@section('title', 'ویرایش نوبت')

@section('meta_description', 'ویرایش نوبت در انتظار تأیید در NOBAT.')

@section('content')
    @php
        $initialDate = \Illuminate\Support\Carbon::parse($booking->booking_date)->format('Y-m-d');
        $initialTime = \Illuminate\Support\Str::substr((string) $booking->start_time, 0, 5);
    @endphp

    <div
        class="customer-container py-5 pb-28 sm:py-8"
        data-booking-edit
        data-booking-id="{{ $booking->id }}"
        data-initial-date="{{ $initialDate }}"
        data-initial-time="{{ $initialTime }}"
        data-availability-url="{{ route('customer.bookings.edit-availability', $booking) }}"
    >
        <div class="mx-auto w-full max-w-5xl">
            <a
                href="{{ route('customer.dashboard') }}"
                class="mb-5 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-content"
            >
                <span aria-hidden="true">→</span>
                نوبت‌های من
            </a>

            <div class="mb-6">
                <span class="text-[10px] font-black tracking-[0.18em] text-accent-600">EDIT BOOKING</span>
                <h1 class="mt-2 text-2xl font-black leading-tight text-content sm:text-3xl">نوبتت را تغییر بده</h1>
                <p class="mt-2 max-w-2xl text-xs leading-7 text-content-muted sm:text-sm">
                    خدمت، متخصص، تاریخ و ساعت را تغییر بده. فقط زمان‌های واقعی و قابل رزرو نمایش داده می‌شوند.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4" role="alert">
                    <div class="text-xs font-black text-danger-700">ویرایش نوبت انجام نشد</div>
                    <div class="mt-2 space-y-1 text-[10px] leading-6 text-danger-700">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form
                action="{{ route('customer.bookings.update', $booking) }}"
                method="POST"
                class="grid gap-5 lg:grid-cols-[minmax(0,1.45fr)_minmax(280px,.75fr)]"
                id="bookingEditForm"
            >
                @csrf
                @method('PUT')

                <input type="hidden" name="salon_id" value="{{ $salon->id }}">
                <input type="hidden" name="booking_date" id="editBookingDate" value="{{ old('booking_date', $initialDate) }}">
                <input type="hidden" name="start_time" id="editStartTime" value="{{ old('start_time', $initialTime) }}">

                <section class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft">
                    <div class="border-b border-border bg-primary-50/60 px-5 py-4 sm:px-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-black tracking-[0.16em] text-content-faint">01 — CHOOSE</span>
                                <h2 class="mt-1 text-base font-black text-content sm:text-lg">انتخاب خدمت و متخصص</h2>
                            </div>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-700">در انتظار تأیید</span>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:p-6 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-content">متخصص</span>
                            <select id="editBarber" name="barber_id" required class="h-12 w-full rounded-2xl border border-border bg-surface-soft px-4 text-sm font-bold text-content outline-none transition focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10">
                                @foreach($barbers as $barber)
                                    <option value="{{ $barber->id }}" @selected((string) old('barber_id', $booking->barber_id) === (string) $barber->id)>
                                        {{ $barber->name }}{{ $barber->specialty ? ' — ' . $barber->specialty : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-content">خدمت</span>
                            <select id="editService" name="service_id" required class="h-12 w-full rounded-2xl border border-border bg-surface-soft px-4 text-sm font-bold text-content outline-none transition focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" @selected((string) old('service_id', $booking->service_id) === (string) $service->id)>
                                        {{ $service->name }} — {{ $service->duration_minutes }} دقیقه{{ $service->price !== null ? ' — ' . number_format($service->price) . ' تومان' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft lg:col-span-1">
                    <div class="border-b border-border bg-primary-50/60 px-5 py-4 sm:px-6">
                        <span class="text-[10px] font-black tracking-[0.16em] text-content-faint">02 — WHEN</span>
                        <h2 class="mt-1 text-base font-black text-content sm:text-lg">روز و ساعت جدید</h2>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-[10px] font-bold text-content-muted">تقویم شمسی</div>
                                <div id="editCalTitle" class="mt-1 text-sm font-black text-content">—</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="editPrevMonth" class="flex h-9 w-9 items-center justify-center rounded-xl border border-border bg-surface-soft text-sm font-black text-content transition hover:border-accent-300" aria-label="ماه قبل">›</button>
                                <button type="button" id="editNextMonth" class="flex h-9 w-9 items-center justify-center rounded-xl border border-border bg-surface-soft text-sm font-black text-content transition hover:border-accent-300" aria-label="ماه بعد">‹</button>
                            </div>
                        </div>

                        <div class="mb-2 grid grid-cols-7 text-center text-[9px] font-black text-content-faint">
                            <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                        </div>
                        <div id="editCalDays" class="grid grid-cols-7 gap-1.5"></div>

                        <div class="mt-5 border-t border-border pt-5">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[10px] font-bold text-content-muted">ظرفیت</div>
                                    <div id="editSlotDate" class="mt-1 text-xs font-black text-content">یک روز انتخاب کن</div>
                                </div>
                                <span class="rounded-full bg-success-50 px-3 py-1 text-[9px] font-black text-success-700">زمان‌های زنده</span>
                            </div>
                            <div id="editSlots" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <div class="col-span-full rounded-2xl border border-dashed border-border px-4 py-5 text-center text-xs font-bold text-content-muted">در حال آماده‌سازی…</div>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                    <section class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft">
                        <div class="border-b border-border px-5 py-4">
                            <span class="text-[10px] font-black tracking-[0.16em] text-content-faint">03 — SUMMARY</span>
                            <h2 class="mt-1 text-base font-black text-content">خلاصه تغییرات</h2>
                        </div>
                        <div class="space-y-4 p-5">
                            <div>
                                <div class="text-[10px] font-bold text-content-muted">سالن</div>
                                <div class="mt-1 text-sm font-black text-content">{{ $salon->name }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-content-muted">متخصص</div>
                                <div id="editSummaryBarber" class="mt-1 text-sm font-black text-content">—</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-content-muted">خدمت</div>
                                <div id="editSummaryService" class="mt-1 text-sm font-black text-content">—</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-content-muted">زمان</div>
                                <div id="editSummaryTime" class="mt-1 text-sm font-black text-content" dir="ltr">—</div>
                            </div>

                            <label class="block pt-2">
                                <span class="mb-2 block text-xs font-black text-content">توضیحات</span>
                                <textarea name="notes" rows="4" maxlength="2000" placeholder="مثلاً ترجیح خاصی برای خدمات داری؟" class="w-full rounded-2xl border border-border bg-surface-soft px-4 py-3 text-xs font-medium leading-7 text-content outline-none transition placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10">{{ old('notes', $booking->notes) }}</textarea>
                            </label>

                            <button type="submit" id="editSubmit" disabled class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-accent-600 px-5 text-xs font-black text-white shadow-sm transition hover:bg-accent-700 disabled:cursor-not-allowed disabled:opacity-50">
                                ذخیره تغییرات
                                <span aria-hidden="true">←</span>
                            </button>

                            <a href="{{ route('customer.dashboard') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-2xl border border-border px-4 text-xs font-black text-content-muted transition hover:bg-primary-50 hover:text-content">
                                انصراف
                            </a>
                        </div>
                    </section>
                </aside>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-booking-edit]');
                const form = document.getElementById('bookingEditForm');
                if (!root || !form) return;

                const fa = (value) => String(value ?? '').replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                const pad2 = (value) => String(value).padStart(2, '0');
                const months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];

                function toJalali(gy, gm, gd) {
                    const gDM = [0,31,59,90,120,151,181,212,243,273,304,334];
                    let jy = gy <= 1600 ? 0 : 979;
                    gy -= gy <= 1600 ? 621 : 1600;
                    const gy2 = gm > 2 ? gy + 1 : gy;
                    let days = 365 * gy + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) - 80 + gd + gDM[gm - 1];
                    jy += 33 * Math.floor(days / 12053);
                    days %= 12053;
                    jy += 4 * Math.floor(days / 1461);
                    days %= 1461;
                    if (days > 365) { jy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
                    const jm = days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
                    const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
                    return [jy, jm, jd];
                }

                function toGregorian(jy, jm, jd) {
                    let gy = jy > 979 ? 1600 : 621;
                    let jy0 = jy > 979 ? jy - 979 : jy;
                    let days = 365 * jy0 + Math.floor(jy0 / 33) * 8 + Math.floor(((jy0 % 33) + 3) / 4) + 78 + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
                    gy += 400 * Math.floor(days / 146097);
                    days %= 146097;
                    if (days > 36524) { gy += 100 * Math.floor(--days / 36524); days %= 36524; if (days >= 365) days++; }
                    gy += 4 * Math.floor(days / 1461);
                    days %= 1461;
                    if (days > 365) { gy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
                    const gd = days + 1;
                    const mdays = [31, gy % 4 === 0 && (gy % 100 !== 0 || gy % 400 === 0) ? 29 : 28, 31,30,31,30,31,31,30,31,30,31];
                    let gm = 1, remaining = gd;
                    while (remaining > mdays[gm - 1]) { remaining -= mdays[gm - 1]; gm++; }
                    return [gy, gm, remaining];
                }

                function yearLength(jy) {
                    const a = toGregorian(jy, 1, 1), b = toGregorian(jy + 1, 1, 1);
                    return Math.round((new Date(b[0], b[1]-1, b[2]) - new Date(a[0], a[1]-1, a[2])) / 86400000);
                }
                function monthDays(jy, jm) { return jm <= 6 ? 31 : jm <= 11 ? 30 : yearLength(jy) === 366 ? 30 : 29; }
                function normalize(y, m) { while (m < 1) { m += 12; y--; } while (m > 12) { m -= 12; y++; } return [y,m]; }

                const today = new Date();
                const todayJ = toJalali(today.getFullYear(), today.getMonth()+1, today.getDate());
                const initialG = String(root.dataset.initialDate).split('-').map(Number);
                const initialJ = toJalali(initialG[0], initialG[1], initialG[2]);
                let viewJ = [initialJ[0], initialJ[1]];
                let selected = { jy: initialJ[0], jm: initialJ[1], jd: initialJ[2], gy: initialG[0], gm: initialG[1], gd: initialG[2] };
                let selectedTime = document.getElementById('editStartTime').value || '';

                const calTitle = document.getElementById('editCalTitle');
                const calDays = document.getElementById('editCalDays');
                const slots = document.getElementById('editSlots');
                const dateInput = document.getElementById('editBookingDate');
                const timeInput = document.getElementById('editStartTime');
                const barber = document.getElementById('editBarber');
                const service = document.getElementById('editService');
                const submit = document.getElementById('editSubmit');

                const summary = {
                    barber: document.getElementById('editSummaryBarber'),
                    service: document.getElementById('editSummaryService'),
                    time: document.getElementById('editSummaryTime'),
                };

                function isBeforeToday(y,m,d) {
                    const g = toGregorian(y,m,d);
                    return new Date(g[0],g[1]-1,g[2]) < new Date(today.getFullYear(),today.getMonth(),today.getDate());
                }

                function sameMonth(a,b) { return a[0] === b[0] && a[1] === b[1]; }

                function updateSummary() {
                    summary.barber.textContent = barber.options[barber.selectedIndex]?.text || '—';
                    summary.service.textContent = service.options[service.selectedIndex]?.text || '—';
                    summary.time.textContent = selectedTime ? `${fa(selected.jd)} ${months[selected.jm-1]} — ساعت ${fa(selectedTime)}` : '—';
                    submit.disabled = !(dateInput.value && timeInput.value);
                }

                function renderCalendar() {
                    const [jy,jm] = viewJ;
                    const first = toGregorian(jy,jm,1);
                    const firstDate = new Date(first[0],first[1]-1,first[2]);
                    const offset = (firstDate.getDay()+1)%7;
                    calTitle.textContent = `${months[jm-1]} ${fa(jy)}`;
                    calDays.innerHTML = '';
                    const disabledPrev = sameMonth(viewJ,todayJ);
                    document.getElementById('editPrevMonth').disabled = disabledPrev;
                    document.getElementById('editPrevMonth').classList.toggle('opacity-40',disabledPrev);

                    for (let i=0;i<offset;i++) {
                        const spacer = document.createElement('span');
                        spacer.className='h-10';
                        calDays.appendChild(spacer);
                    }
                    for (let jd=1;jd<=monthDays(jy,jm);jd++) {
                        const b = document.createElement('button');
                        b.type='button';
                        b.textContent=fa(jd);
                        b.className='h-10 rounded-xl border border-border bg-surface-soft text-xs font-black text-content transition hover:border-accent-300 hover:bg-accent-50';
                        const past = isBeforeToday(jy,jm,jd);
                        const active = selected.jy===jy && selected.jm===jm && selected.jd===jd;
                        if (past) { b.disabled=true; b.className+=' opacity-30 cursor-not-allowed'; }
                        if (active) b.className+=' border-accent-500 bg-accent-600 text-white hover:bg-accent-600';
                        if (!past) b.addEventListener('click',()=>selectDay(jy,jm,jd,b));
                        calDays.appendChild(b);
                    }
                }

                function selectDay(jy,jm,jd,b) {
                    const g=toGregorian(jy,jm,jd);
                    selected={jy,jm,jd,gy:g[0],gm:g[1],gd:g[2]};
                    dateInput.value=`${g[0]}-${pad2(g[1])}-${pad2(g[2])}`;
                    selectedTime='';
                    timeInput.value='';
                    calDays.querySelectorAll('button').forEach(x=>x.classList.remove('border-accent-500','bg-accent-600','text-white'));
                    b.classList.add('border-accent-500','bg-accent-600','text-white');
                    updateSummary();
                    fetchSlots();
                }

                async function fetchSlots() {
                    slots.innerHTML='<div class="col-span-full rounded-2xl border border-dashed border-border px-4 py-5 text-center text-xs font-bold text-content-muted">در حال بررسی ظرفیت…</div>';
                    const url = new URL(root.dataset.availabilityUrl, window.location.origin);
                    url.searchParams.set('barber_id', barber.value);
                    url.searchParams.set('service_id', service.value);
                    url.searchParams.set('booking_date', dateInput.value);
                    try {
                        const response=await fetch(url,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});
                        const data=await response.json();
                        if(!response.ok) throw new Error(data.message || Object.values(data.errors||{})[0]?.[0] || 'خطا در دریافت ظرفیت');
                        renderSlots(data.slots||[]);
                    } catch(error) {
                        slots.innerHTML=`<div class="col-span-full rounded-2xl border border-danger-100 bg-danger-50 px-4 py-5 text-center text-xs font-bold text-danger-700">${String(error.message||'خطا')}</div>`;
                    }
                }

                function renderSlots(items) {
                    slots.innerHTML='';
                    if(!items.length){ slots.innerHTML='<div class="col-span-full rounded-2xl border border-dashed border-border px-4 py-5 text-center text-xs font-bold text-content-muted">برای این روز زمان خالی نیست.</div>'; return; }
                    items.forEach(slot=>{
                        const b=document.createElement('button'); b.type='button'; b.textContent=fa(slot.start);
                        const isTaken=slot.available===false;
                        const isCurrent=String(slot.start)===String(selectedTime);
                        b.className='min-h-11 rounded-xl border text-xs font-black transition';
                        if(isTaken){ b.disabled=true; b.classList.add('border-border','bg-primary-50','text-content-faint','line-through'); }
                        else { b.classList.add('border-border','bg-surface-soft','text-content','hover:border-accent-400','hover:bg-accent-50'); }
                        if(slot.status==='pending' && !isTaken){ b.setAttribute('title','در انتظار تأیید یک درخواست دیگر؛ قابل انتخاب است'); b.classList.add('border-amber-200'); }
                        if(isCurrent && !isTaken){ b.classList.add('border-accent-500','bg-accent-600','text-white'); }
                        if(!isTaken) b.addEventListener('click',()=>{ selectedTime=slot.start; timeInput.value=slot.start; renderSlots(items); updateSummary(); });
                        slots.appendChild(b);
                    });
                }

                document.getElementById('editPrevMonth').addEventListener('click',()=>{ if(sameMonth(viewJ,todayJ)) return; viewJ=normalize(viewJ[0],viewJ[1]-1); if(sameMonth(viewJ,todayJ)) selectedTime=''; renderCalendar(); });
                document.getElementById('editNextMonth').addEventListener('click',()=>{ viewJ=normalize(viewJ[0],viewJ[1]+1); renderCalendar(); });
                barber.addEventListener('change',()=>{ selectedTime=''; timeInput.value=''; updateSummary(); fetchSlots(); });
                service.addEventListener('change',()=>{ selectedTime=''; timeInput.value=''; updateSummary(); fetchSlots(); });

                renderCalendar();
                updateSummary();
                fetchSlots();
            })();
        </script>
    @endpush
@endsection
