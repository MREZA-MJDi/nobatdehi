@extends('layouts.customer')

@section('title', 'ویرایش نوبت')

@section('content')

    @php
        $bookingDate = optional($booking->booking_date)->format('Y-m-d')
            ?? (string) $booking->booking_date;

        $today = now(config('app.timezone', 'Asia/Tehran'))->format('Y-m-d');

        $currentStart = substr((string) $booking->start_time, 0, 5);
    @endphp

    <div class="customer-container py-5 pb-28 sm:py-8">
        <div class="mx-auto w-full max-w-3xl">

            <div class="mb-5">
                <a
                    href="{{ route('customer.dashboard') }}"
                    class="text-xs font-bold text-content-muted transition hover:text-content"
                >
                    ← بازگشت به نوبت‌های من
                </a>

                <span class="mt-5 block text-[10px] font-black tracking-[0.16em] text-accent-600">
                    EDIT BOOKING
                </span>

                <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                    نوبتت را ویرایش کن
                </h1>

                <p class="mt-2 text-xs leading-7 text-content-muted">
                    فقط نوبتی که هنوز در انتظار تأیید است قابل تغییر است.
                    زمان انتخابی در لحظه ثبت دوباره بررسی می‌شود.
                </p>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold leading-7 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-7 text-red-700">
                    <div class="font-black">ویرایش نوبت انجام نشد.</div>

                    <div class="mt-1 space-y-1">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form
                id="customer-booking-edit-form"
                action="{{ route('customer.bookings.update', $booking) }}"
                method="POST"
                class="space-y-4"
                novalidate
            >
                @csrf
                @method('PUT')

                <input type="hidden" name="salon_id" value="{{ $salon->id }}">

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <div class="mb-5">
                        <div class="text-[10px] font-black tracking-[0.14em] text-content-faint">
                            SALON
                        </div>

                        <h2 class="mt-1 text-lg font-black text-content">
                            {{ $salon->name }}
                        </h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black text-content">
                                متخصص
                            </span>

                            <select
                                id="edit-barber"
                                name="barber_id"
                                class="min-h-12 w-full rounded-2xl border border-border bg-surface px-4 text-sm font-bold text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            >
                                @foreach($barbers as $barber)
                                    <option
                                        value="{{ $barber->id }}"
                                        @selected((int) old('barber_id', $booking->barber_id) === (int) $barber->id)
                                    >
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if($barbers->isEmpty())
                                <span class="mt-2 block text-[10px] text-red-600">
                                    هیچ متخصص فعالی در این سالن باقی نمانده است.
                                </span>
                            @endif
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black text-content">
                                خدمت
                            </span>

                            <select
                                id="edit-service"
                                name="service_id"
                                class="min-h-12 w-full rounded-2xl border border-border bg-surface px-4 text-sm font-bold text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            >
                                @foreach($services as $service)
                                    <option
                                        value="{{ $service->id }}"
                                        @selected((int) old('service_id', $booking->service_id) === (int) $service->id)
                                    >
                                        {{ $service->name }} — {{ number_format($service->price) }} تومان / {{ $service->duration_minutes }} دقیقه
                                    </option>
                                @endforeach
                            </select>

                            @if($services->isEmpty())
                                <span class="mt-2 block text-[10px] text-red-600">
                                    هیچ خدمت فعالی در این سالن باقی نمانده است.
                                </span>
                            @endif
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-[11px] font-black text-content">
                                تاریخ
                            </span>

                            <input
                                id="edit-date"
                                type="date"
                                name="booking_date"
                                min="{{ $today }}"
                                value="{{ old('booking_date', $bookingDate) }}"
                                class="min-h-12 w-full rounded-2xl border border-border bg-surface px-4 text-sm font-bold text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                                required
                            >
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <div class="text-[10px] font-black tracking-[0.14em] text-content-faint">
                                TIME
                            </div>

                            <h2 class="mt-1 text-lg font-black text-content">
                                ساعت جدید
                            </h2>
                        </div>

                        <span
                            id="edit-availability-status"
                            class="text-[10px] font-bold text-content-muted"
                            aria-live="polite"
                        >
                            در حال بررسی...
                        </span>
                    </div>

                    <input
                        id="edit-time"
                        type="hidden"
                        name="start_time"
                        value="{{ old('start_time', $currentStart) }}"
                    >

                    <div
                        id="edit-slots"
                        class="grid grid-cols-3 gap-2 sm:grid-cols-4"
                        aria-live="polite"
                    ></div>

                    <div
                        id="edit-availability-message"
                        class="mt-4 rounded-2xl bg-primary-50/60 px-4 py-3 text-[10px] font-bold leading-6 text-content-muted"
                    >
                        زمان‌های آزاد این متخصص و خدمت اینجا نمایش داده می‌شوند.
                    </div>
                </section>

                <section class="rounded-3xl border border-border bg-surface p-5 shadow-soft sm:p-6">
                    <label class="block">
                        <span class="mb-2 block text-[11px] font-black text-content">
                            توضیحات
                        </span>

                        <textarea
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            class="w-full rounded-2xl border border-border bg-surface px-4 py-3 text-sm leading-7 text-content outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-500/15"
                            placeholder="توضیح کوتاه برای سالن..."
                        >{{ old('notes', $booking->notes) }}</textarea>

                        @error('notes')
                            <span class="mt-2 block text-[10px] font-bold text-red-600">
                                {{ $message }}
                            </span>
                        @enderror
                    </label>
                </section>

                <div class="sticky bottom-3 z-10 rounded-2xl border border-border bg-surface/95 p-2 shadow-lg backdrop-blur sm:static sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">
                    <button
                        id="edit-submit"
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-accent-600 px-5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-accent-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        disabled
                    >
                        ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('customer-booking-edit-form');
    if (!form) return;

    const barber = document.getElementById('edit-barber');
    const service = document.getElementById('edit-service');
    const date = document.getElementById('edit-date');
    const time = document.getElementById('edit-time');
    const slots = document.getElementById('edit-slots');
    const submit = document.getElementById('edit-submit');
    const status = document.getElementById('edit-availability-status');
    const message = document.getElementById('edit-availability-message');

    const endpoint = @json(route('customer.bookings.edit-availability', $booking));

    let requestId = 0;
    let controller = null;
    let selectedAvailable = false;

    const setMessage = (text, isError = false) => {
        message.textContent = text;
        message.classList.toggle('bg-red-50', isError);
        message.classList.toggle('text-red-700', isError);
        message.classList.toggle('bg-primary-50/60', !isError);
        message.classList.toggle('text-content-muted', !isError);
    };

    const renderSlots = (items) => {
        slots.innerHTML = '';
        selectedAvailable = false;

        if (!Array.isArray(items) || items.length === 0) {
            submit.disabled = true;
            setMessage('برای این انتخاب، زمان آزادی پیدا نشد.', false);
            return;
        }

        const currentValue = time.value;

        items.forEach((slot) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = slot.start;
            button.dataset.time = slot.start;
            button.disabled = slot.available !== true;

            button.className =
                'min-h-11 rounded-xl border px-3 text-xs font-black transition ' +
                (slot.available === true
                    ? 'border-border bg-surface text-content hover:border-accent-500 hover:bg-accent-50'
                    : 'cursor-not-allowed border-border bg-primary-50/70 text-content-faint line-through');

            if (slot.available === true && slot.start === currentValue) {
                button.classList.add('border-accent-600', 'bg-accent-50', 'text-accent-700');
                selectedAvailable = true;
            }

            button.addEventListener('click', () => {
                time.value = slot.start;
                selectedAvailable = true;

                slots.querySelectorAll('button').forEach((item) => {
                    item.classList.remove('border-accent-600', 'bg-accent-50', 'text-accent-700');
                });

                button.classList.add('border-accent-600', 'bg-accent-50', 'text-accent-700');
                submit.disabled = false;
            });

            slots.appendChild(button);
        });

        if (selectedAvailable) {
            setMessage('زمان انتخاب‌شده هنوز آزاد است.', false);
            submit.disabled = false;
        } else {
            setMessage('زمان قبلی دیگر آزاد نیست؛ یکی از زمان‌های آزاد را انتخاب کن.', false);
            submit.disabled = true;
        }
    };

    const loadAvailability = async () => {
        const currentRequestId = ++requestId;

        controller?.abort();
        controller = new AbortController();

        submit.disabled = true;
        status.textContent = 'در حال بررسی...';
        setMessage('در حال دریافت زمان‌های آزاد...', false);
        slots.innerHTML = '';

        if (!barber.value || !service.value || !date.value) {
            status.textContent = 'انتخاب‌ها کامل نیست';
            setMessage('متخصص، خدمت و تاریخ را کامل انتخاب کن.', true);
            return;
        }

        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('barber_id', barber.value);
        url.searchParams.set('service_id', service.value);
        url.searchParams.set('booking_date', date.value);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (currentRequestId !== requestId) return;

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'AVAILABILITY_FAILED');
            }

            status.textContent = 'به‌روز شد';
            renderSlots(data.slots);
        } catch (error) {
            if (error?.name === 'AbortError') return;

            console.error(error);
            status.textContent = 'قابل بررسی نیست';
            renderSlots([]);
            setMessage(
                error?.message || 'دریافت زمان‌های آزاد انجام نشد. دوباره تلاش کن.',
                true
            );
        }
    };

    [barber, service, date].forEach((control) => {
        control?.addEventListener('change', () => {
            time.value = '';
            loadAvailability();
        });
    });

    form.addEventListener('submit', (event) => {
        if (!selectedAvailable || !time.value) {
            event.preventDefault();
            setMessage('یک زمان آزاد انتخاب کن و دوباره ثبت کن.', true);
        }
    });

    loadAvailability();
})();
</script>
@endpush
