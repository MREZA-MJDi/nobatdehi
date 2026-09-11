@extends('layouts.salon')

@section('title', 'ثبت نوبت دستی')

@php
    $todayIso = now(config('app.timezone'))->toDateString();

    $servicesData = $services->map(function ($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'duration' => (int) $service->duration_minutes,
        ];
    })->values();

    $barbersData = $barbers->map(function ($barber) {
        return [
            'id' => $barber->id,
            'name' => $barber->name,
        ];
    })->values();

    $customersData = $customers->map(function ($customer) {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ];
    })->values();
@endphp

@section('content')

    <script>
        window.salonBookingConfig = {
            todayIso: @js($todayIso),
            availabilityUrl: @js(route('salon.bookings.availability')),
            services: @js($servicesData),
            barbers: @js($barbersData),
            customers: @js($customersData),
        };

        function salonBookingPage() {
            return {
                customerId: @js(old('customer_id', '')),
                barberId: @js(old('barber_id', '')),
                serviceId: @js(old('service_id', '')),
                selectedDate: @js(old('booking_date', '')),
                selectedTime: @js(old('start_time', '')),

                customerSearch: '',

                slots: [],
                loadingSlots: false,
                slotError: '',

                init() {
                    if (!this.selectedDate) {
                        this.selectedDate = window.salonBookingConfig.todayIso;
                    }

                    this.$nextTick(() => {
                        if (this.barberId && this.serviceId && this.selectedDate) {
                            this.loadSlots(true);
                        }
                    });
                },

                parseIso(value) {
                    const [year, month, day] = String(value).split('-').map(Number);

                    if (!year || !month || !day) {
                        return null;
                    }

                    return new Date(Date.UTC(year, month - 1, day));
                },

                toIso(date) {
                    const year = date.getUTCFullYear();
                    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
                    const day = String(date.getUTCDate()).padStart(2, '0');

                    return `${year}-${month}-${day}`;
                },

                persianDate(value) {
                    if (!value) {
                        return '';
                    }

                    const date = this.parseIso(value);

                    if (!date) {
                        return '';
                    }

                    return new Intl.DateTimeFormat(
                        'fa-IR-u-ca-persian-nu-latn',
                        {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            weekday: 'long',
                            timeZone: 'UTC'
                        }
                    ).format(date);
                },

                persianDigits(value) {
                    return String(value ?? '').replace(/\d/g, digit => {
                        return '۰۱۲۳۴۵۶۷۸۹'[digit];
                    });
                },

                formatPersianDate(value) {
                    return this.persianDigits(this.persianDate(value));
                },

                formatTime(value) {
                    if (!value) {
                        return '';
                    }

                    return this.persianDigits(String(value).slice(0, 5));
                },

                selectDate(event) {
                    const value = event.target.value;

                    if (!value) {
                        this.selectedDate = '';
                        this.selectedTime = '';
                        this.slots = [];
                        return;
                    }

                    this.selectedDate = value;
                    this.selectedTime = '';

                    this.loadSlots();
                },

                selectBarber(id) {
                    this.barberId = String(id);
                    this.selectedTime = '';
                    this.loadSlots();
                },

                selectService(id) {
                    this.serviceId = String(id);
                    this.selectedTime = '';
                    this.loadSlots();
                },

                selectCustomer(id) {
                    this.customerId = String(id);
                },

                async loadSlots(restoreOldTime = false) {
                    if (!this.barberId || !this.serviceId || !this.selectedDate) {
                        this.slots = [];
                        this.slotError = '';
                        return;
                    }

                    this.loadingSlots = true;
                    this.slotError = '';

                    if (!restoreOldTime) {
                        this.selectedTime = '';
                    }

                    try {
                        const url = new URL(
                            window.salonBookingConfig.availabilityUrl,
                            window.location.origin
                        );

                        url.searchParams.set('barber_id', this.barberId);
                        url.searchParams.set('service_id', this.serviceId);
                        url.searchParams.set('booking_date', this.selectedDate);

                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(
                                data.message || 'خطا در دریافت زمان‌های آزاد.'
                            );
                        }

                        this.slots = Array.isArray(data.slots)
                            ? data.slots
                            : [];

                        if (restoreOldTime && this.selectedTime) {
                            const exists = this.slots.some(slot => {
                                const time = typeof slot === 'string'
                                    ? slot
                                    : slot.time;

                                return String(time).slice(0, 5) === String(this.selectedTime).slice(0, 5)
                                    && (typeof slot === 'string' || slot.available !== false);
                            });

                            if (!exists) {
                                this.selectedTime = '';
                            }
                        }
                    } catch (error) {
                        this.slots = [];
                        this.slotError = error.message || 'خطا در دریافت زمان‌های آزاد.';
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                get filteredCustomers() {
                    const query = this.customerSearch.trim().toLowerCase();

                    if (!query) {
                        return window.salonBookingConfig.customers;
                    }

                    return window.salonBookingConfig.customers.filter(customer => {
                        return String(customer.name || '')
                                .toLowerCase()
                                .includes(query)
                            || String(customer.phone || '')
                                .toLowerCase()
                                .includes(query);
                    });
                },

                get selectedCustomer() {
                    return window.salonBookingConfig.customers.find(
                        customer => String(customer.id) === String(this.customerId)
                    );
                },

                get selectedBarber() {
                    return window.salonBookingConfig.barbers.find(
                        barber => String(barber.id) === String(this.barberId)
                    );
                },

                get selectedService() {
                    return window.salonBookingConfig.services.find(
                        service => String(service.id) === String(this.serviceId)
                    );
                },

                get selectedPrice() {
                    return this.selectedService?.price || 0;
                },

                get selectedDuration() {
                    return this.selectedService?.duration || 0;
                },

                get selectedSlot() {
                    return this.slots.find(slot => {
                        const time = typeof slot === 'string'
                            ? slot
                            : slot.time;

                        return String(time).slice(0, 5) === String(this.selectedTime).slice(0, 5);
                    });
                },

                get canSubmit() {
                    return Boolean(
                        this.customerId &&
                        this.barberId &&
                        this.serviceId &&
                        this.selectedDate &&
                        this.selectedTime &&
                        this.selectedSlot &&
                        (typeof this.selectedSlot === 'string' || this.selectedSlot.available !== false)
                    );
                },

                get hasAvailableSlots() {
                    return this.slots.some(slot => {
                        return typeof slot === 'string'
                            || slot.available !== false;
                    });
                },

                formatPrice(price) {
                    return this.persianDigits(
                        new Intl.NumberFormat('fa-IR').format(Number(price || 0))
                    ) + ' تومان';
                },

                initials(name) {
                    if (!name) {
                        return '؟';
                    }

                    return name
                        .trim()
                        .split(/\s+/)
                        .slice(0, 2)
                        .map(part => part.charAt(0))
                        .join('')
                        .toUpperCase();
                }
            }
        }
    </script>

    <div class="px-4 py-5 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div
            x-data="salonBookingPage()"
            x-init="init()"
            class="mx-auto w-full max-w-7xl"
        >

            {{-- Header --}}
            <div class="mb-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <a
                            href="{{ route('salon.bookings.index') }}"
                            class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                        >
                            <span class="text-lg">→</span>
                            بازگشت به نوبت‌ها
                        </a>

                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                                ✦
                            </div>

                            <div>
                                <div class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">
                                    MANUAL BOOKING
                                </div>

                                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                    ثبت نوبت دستی
                                </h1>
                            </div>
                        </div>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            برای مشتری سالن نوبت ثبت کنید. زمان‌های قابل رزرو بر اساس
                            آرایشگر، خدمت، ساعات کاری و نوبت‌های موجود نمایش داده می‌شوند.
                        </p>
                    </div>

                    <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ auth()->user()->name ?? 'سالن' }}
                    </div>
                </div>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                    <div class="mb-2 flex items-center gap-2 font-black">
                        <span>⚠</span>
                        اطلاعات را بررسی کنید
                    </div>

                    <ul class="space-y-1 text-sm leading-6">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('salon.bookings.store-manual') }}"
                method="POST"
            >
                @csrf

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_350px]">

                    {{-- Main --}}
                    <div class="space-y-6">

                        {{-- Customer --}}
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                        ۱
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            CUSTOMER
                                        </div>
                                        <h2 class="mt-1 text-lg font-black text-slate-950">
                                            انتخاب مشتری
                                        </h2>
                                    </div>

                                    <template x-if="selectedCustomer">
                                        <div class="mr-auto hidden rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 sm:block">
                                            انتخاب شده
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                <div class="relative">
                                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    ⌕
                                </span>

                                    <input
                                        type="text"
                                        x-model="customerSearch"
                                        placeholder="جستجوی نام یا شماره موبایل..."
                                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-11 pl-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                    >
                                </div>

                                <div
                                    class="mt-4 max-h-72 space-y-2 overflow-y-auto pr-1"
                                    x-show="filteredCustomers.length"
                                >
                                    <template x-for="customer in filteredCustomers" :key="customer.id">
                                        <button
                                            type="button"
                                            @click="selectCustomer(customer.id)"
                                            class="group flex w-full items-center gap-3 rounded-2xl border p-3 text-right transition"
                                            :class="String(customer.id) === String(customerId)
                                            ? 'border-slate-950 bg-slate-950 text-white shadow-lg'
                                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'"
                                        >
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-black"
                                                :class="String(customer.id) === String(customerId)
                                                ? 'bg-white/15 text-white'
                                                : 'bg-slate-100 text-slate-700'"
                                                x-text="initials(customer.name)"
                                            ></div>

                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="truncate text-sm font-black"
                                                    x-text="customer.name"
                                                ></div>

                                                <div
                                                    class="mt-1 text-xs"
                                                    :class="String(customer.id) === String(customerId)
                                                    ? 'text-white/60'
                                                    : 'text-slate-400'"
                                                    x-text="customer.phone || 'بدون شماره'"
                                                ></div>
                                            </div>

                                            <div
                                                x-show="String(customer.id) === String(customerId)"
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-xs"
                                            >
                                                ✓
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <div
                                    x-show="!filteredCustomers.length"
                                    class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center"
                                >
                                    <div class="text-2xl">⌕</div>
                                    <div class="mt-2 text-sm font-bold text-slate-700">
                                        مشتری پیدا نشد
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        عبارت جستجو را تغییر دهید.
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    name="customer_id"
                                    :value="customerId"
                                >
                            </div>
                        </section>

                        {{-- Barber --}}
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                        ۲
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            BARBER
                                        </div>
                                        <h2 class="mt-1 text-lg font-black text-slate-950">
                                            انتخاب آرایشگر
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                @if ($barbers->count())
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach ($barbers as $barber)
                                            <button
                                                type="button"
                                                @click="selectBarber({{ $barber->id }})"
                                                class="group relative overflow-hidden rounded-2xl border p-3 text-right transition"
                                                :class="String(barberId) === '{{ $barber->id }}'
                                                ? 'border-slate-950 bg-slate-950 text-white shadow-lg'
                                                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'"
                                            >
                                                <div class="flex items-center gap-3">
                                                    @if ($barber->image_path)
                                                        <img
                                                            src="{{ Storage::url($barber->image_path) }}"
                                                            alt="{{ $barber->name }}"
                                                            class="h-14 w-14 shrink-0 rounded-xl object-cover"
                                                        >
                                                    @else
                                                        <div
                                                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl text-lg font-black"
                                                            :class="String(barberId) === '{{ $barber->id }}'
                                                            ? 'bg-white/10 text-white'
                                                            : 'bg-slate-100 text-slate-600'"
                                                        >
                                                            {{ mb_substr($barber->name, 0, 1) }}
                                                        </div>
                                                    @endif

                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate text-sm font-black">
                                                            {{ $barber->name }}
                                                        </div>

                                                        <div
                                                            class="mt-1 text-xs"
                                                            :class="String(barberId) === '{{ $barber->id }}'
                                                            ? 'text-white/60'
                                                            : 'text-slate-400'"
                                                        >
                                                            {{ $barber->is_active ? 'فعال' : 'غیرفعال' }}
                                                        </div>
                                                    </div>

                                                    <div
                                                        x-show="String(barberId) === '{{ $barber->id }}'"
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs"
                                                    >
                                                        ✓
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                        <div class="text-sm font-black text-slate-700">
                                            آرایشگری ثبت نشده است
                                        </div>
                                        <div class="mt-1 text-xs text-slate-400">
                                            ابتدا یک آرایشگر فعال اضافه کنید.
                                        </div>
                                    </div>
                                @endif

                                <input
                                    type="hidden"
                                    name="barber_id"
                                    :value="barberId"
                                >
                            </div>
                        </section>

                        {{-- Service --}}
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                        ۳
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            SERVICE
                                        </div>
                                        <h2 class="mt-1 text-lg font-black text-slate-950">
                                            انتخاب خدمت
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                @if ($services->count())
                                    <div class="space-y-2">
                                        @foreach ($services as $service)
                                            <button
                                                type="button"
                                                @click="selectService({{ $service->id }})"
                                                class="flex w-full items-center gap-4 rounded-2xl border p-4 text-right transition"
                                                :class="String(serviceId) === '{{ $service->id }}'
                                                ? 'border-slate-950 bg-slate-950 text-white shadow-lg'
                                                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'"
                                            >
                                                <div
                                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg"
                                                    :class="String(serviceId) === '{{ $service->id }}'
                                                    ? 'bg-white/10'
                                                    : 'bg-slate-100'"
                                                >
                                                    ✦
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-black">
                                                        {{ $service->name }}
                                                    </div>

                                                    <div
                                                        class="mt-1 text-xs"
                                                        :class="String(serviceId) === '{{ $service->id }}'
                                                        ? 'text-white/60'
                                                        : 'text-slate-400'"
                                                    >
                                                        {{ $service->duration_minutes }} دقیقه
                                                    </div>
                                                </div>

                                                <div class="text-left">
                                                    <div class="text-sm font-black">
                                                        {{ number_format($service->price) }}
                                                        تومان
                                                    </div>

                                                    <div
                                                        x-show="String(serviceId) === '{{ $service->id }}'"
                                                        class="mt-1 text-[10px] font-bold text-emerald-300"
                                                    >
                                                        انتخاب شده
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                        <div class="text-sm font-black text-slate-700">
                                            خدمتی ثبت نشده است
                                        </div>
                                    </div>
                                @endif

                                <input
                                    type="hidden"
                                    name="service_id"
                                    :value="serviceId"
                                >
                            </div>
                        </section>

                        {{-- Date & Time --}}
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                                        ۴
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            DATE & TIME
                                        </div>
                                        <h2 class="mt-1 text-lg font-black text-slate-950">
                                            تاریخ و ساعت نوبت
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">

                                {{-- Date Picker --}}
                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-800">
                                        تاریخ نوبت
                                    </label>

                                    <div class="relative">
                                        <input
                                            type="date"
                                            min="{{ $todayIso }}"
                                            x-model="selectedDate"
                                            @change="selectDate($event)"
                                            name="booking_date"
                                            class="h-14 w-full cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                        >
                                    </div>

                                    <template x-if="selectedDate">
                                        <div class="mt-3 flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3">
                                            <span class="text-slate-400">◷</span>

                                            <span
                                                class="text-sm font-bold text-slate-700"
                                                x-text="formatPersianDate(selectedDate)"
                                            ></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Time Picker --}}
                                <div class="mt-6">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <label class="block text-sm font-black text-slate-800">
                                            ساعت نوبت
                                        </label>

                                        <template x-if="slots.length">
                                        <span class="text-xs font-bold text-slate-400">
                                            زمان‌های آزاد
                                        </span>
                                        </template>
                                    </div>

                                    {{-- Missing dependency --}}
                                    <template x-if="!barberId || !serviceId || !selectedDate">
                                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                                            <div class="text-xl">◷</div>

                                            <div class="mt-2 text-sm font-black text-slate-700">
                                                ابتدا آرایشگر، خدمت و تاریخ را انتخاب کنید
                                            </div>

                                            <div class="mt-1 text-xs leading-6 text-slate-400">
                                                سپس ساعت‌های قابل رزرو برای شما نمایش داده می‌شود.
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Loading --}}
                                    <template x-if="barberId && serviceId && selectedDate && loadingSlots">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-7 text-center">
                                            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-slate-200 border-t-slate-900"></div>

                                            <div class="mt-3 text-sm font-bold text-slate-500">
                                                در حال دریافت زمان‌های آزاد...
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Error --}}
                                    <template x-if="slotError && !loadingSlots">
                                        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold leading-6 text-red-700">
                                            <div class="flex items-start gap-2">
                                                <span>⚠</span>
                                                <span x-text="slotError"></span>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Available times --}}
                                    <template x-if="!loadingSlots && !slotError && barberId && serviceId && selectedDate && hasAvailableSlots">
                                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                            <template x-for="(slot, index) in slots" :key="index">
                                                <template x-if="typeof slot === 'string' || slot.available !== false">
                                                    <button
                                                        type="button"
                                                        @click="selectedTime = typeof slot === 'string' ? slot : slot.time"
                                                        class="h-12 rounded-xl border text-sm font-black transition"
                                                        :class="String(selectedTime).slice(0, 5) === String(typeof slot === 'string' ? slot : slot.time).slice(0, 5)
                                                        ? 'border-slate-950 bg-slate-950 text-white shadow-md'
                                                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50'"
                                                        x-text="formatTime(typeof slot === 'string' ? slot : slot.time)"
                                                    ></button>
                                                </template>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- No slots --}}
                                    <template x-if="!loadingSlots && !slotError && barberId && serviceId && selectedDate && !hasAvailableSlots">
                                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center">
                                            <div class="text-xl">◷</div>

                                            <div class="mt-2 text-sm font-black text-amber-800">
                                                برای این تاریخ زمان آزادی وجود ندارد
                                            </div>

                                            <div class="mt-1 text-xs leading-6 text-amber-700/70">
                                                تاریخ دیگری را انتخاب کنید یا آرایشگر دیگری را امتحان کنید.
                                            </div>
                                        </div>
                                    </template>

                                    <input
                                        type="hidden"
                                        name="start_time"
                                        :value="selectedTime"
                                    >
                                </div>
                            </div>
                        </section>

                        {{-- Notes --}}
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-sm font-black text-slate-700">
                                        ۵
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            NOTES
                                        </div>
                                        <h2 class="mt-1 text-lg font-black text-slate-950">
                                            توضیحات
                                        </h2>
                                    </div>

                                    <span class="mr-auto text-xs font-bold text-slate-400">
                                    اختیاری
                                </span>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                            <textarea
                                name="notes"
                                rows="5"
                                placeholder="مثلاً: مشتری درخواست کرده ریش هم مرتب شود..."
                                class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-medium leading-7 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >{{ old('notes') }}</textarea>
                            </div>
                        </section>

                    </div>

                    {{-- Summary --}}
                    <aside class="lg:sticky lg:top-6 lg:h-fit">
                        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">

                            <div class="bg-slate-950 px-5 py-6 text-white">
                                <div class="text-[10px] font-black uppercase tracking-[0.22em] text-white/40">
                                    BOOKING SUMMARY
                                </div>

                                <div class="mt-1 text-xl font-black">
                                    خلاصه نوبت
                                </div>

                                <div class="mt-4 flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full"
                                        :class="canSubmit ? 'bg-emerald-400' : 'bg-amber-400'"
                                    ></div>

                                    <span
                                        class="text-xs font-bold text-white/60"
                                        x-text="canSubmit ? 'اطلاعات کامل است' : 'در انتظار تکمیل اطلاعات'"
                                    ></span>
                                </div>
                            </div>

                            <div class="space-y-3 p-5">

                                {{-- Customer --}}
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                        مشتری
                                    </div>

                                    <div
                                        class="mt-2 text-sm font-black text-slate-900"
                                        x-text="selectedCustomer?.name || 'انتخاب نشده'"
                                    ></div>

                                    <template x-if="selectedCustomer?.phone">
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                            x-text="selectedCustomer.phone"
                                        ></div>
                                    </template>
                                </div>

                                {{-- Barber --}}
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            آرایشگر
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-black text-slate-900"
                                            x-text="selectedBarber?.name || 'انتخاب نشده'"
                                        ></div>
                                    </div>

                                    <span class="text-lg">✦</span>
                                </div>

                                {{-- Service --}}
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            خدمت
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-black text-slate-900"
                                            x-text="selectedService?.name || 'انتخاب نشده'"
                                        ></div>
                                    </div>

                                    <div
                                        class="text-left text-xs font-bold text-slate-400"
                                        x-text="selectedDuration ? persianDigits(selectedDuration) + ' دقیقه' : '—'"
                                    ></div>
                                </div>

                                {{-- Date --}}
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            تاریخ
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-black text-slate-900"
                                            x-text="selectedDate ? formatPersianDate(selectedDate) : 'انتخاب نشده'"
                                        ></div>
                                    </div>

                                    <span class="text-lg">◫</span>
                                </div>

                                {{-- Time --}}
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            ساعت
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-black text-slate-900"
                                            x-text="selectedTime ? formatTime(selectedTime) : 'انتخاب نشده'"
                                        ></div>
                                    </div>

                                    <span class="text-lg">◷</span>
                                </div>

                                {{-- Price --}}
                                <div class="mt-2 rounded-2xl bg-slate-950 p-4 text-white">
                                    <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-white/50">
                                        مبلغ نوبت
                                    </span>

                                        <span
                                            class="text-lg font-black"
                                            x-text="selectedPrice ? formatPrice(selectedPrice) : '—'"
                                        ></span>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="flex items-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs text-emerald-700">
                                        ✓
                                    </div>

                                    <div>
                                        <div class="text-xs font-black text-emerald-800">
                                            نوبت دستی تأییدشده
                                        </div>

                                        <div class="mt-0.5 text-[10px] font-medium text-emerald-700/70">
                                            پس از ثبت، نوبت در لیست نوبت‌های سالن قرار می‌گیرد.
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <button
                                    type="submit"
                                    :disabled="!canSubmit"
                                    class="mt-2 flex h-14 w-full items-center justify-center gap-2 rounded-2xl text-sm font-black transition"
                                    :class="canSubmit
                                    ? 'bg-slate-950 text-white shadow-lg shadow-slate-900/15 hover:-translate-y-0.5 hover:bg-slate-800'
                                    : 'cursor-not-allowed bg-slate-100 text-slate-400'"
                                >
                                    <span>ثبت نوبت</span>
                                    <span class="text-lg">←</span>
                                </button>

                                <div
                                    x-show="!canSubmit"
                                    class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-center text-[11px] font-bold leading-6 text-amber-700"
                                >
                                    برای ثبت نوبت، مشتری، آرایشگر، خدمت، تاریخ و ساعت را انتخاب کنید.
                                </div>
                            </div>
                        </div>
                    </aside>

                </div>
            </form>
        </div>
    </div>

@endsection
