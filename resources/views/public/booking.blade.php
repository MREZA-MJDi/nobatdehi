@extends('layouts.customer')

@section('title', 'رزرو نوبت ' . $salon->name)

@section('content')

    @php

        $barberPayload =
            $barbers->map(
                fn ($barber) => [
                    'id' =>
                        $barber->id,

                    'name' =>
                        $barber->name,
                ]
            )->values();

        $servicePayload =
            $services->map(
                fn ($service) => [
                    'id' =>
                        $service->id,

                    'name' =>
                        $service->name,

                    'price' =>
                        (int) $service->price,

                    'duration' =>
                        (int) $service->duration_minutes,
                ]
            )->values();

    @endphp


    <div
        class="customer-container booking-page"
        x-data="{
        barberId: '',
        serviceId: '',
        date: '{{ old('booking_date', now()->format('Y-m-d')) }}',
        time: '',
        slots: [],
        loading: false,

        barbers: @js($barberPayload),
        services: @js($servicePayload),

        async loadSlots() {

            if (
                !this.barberId ||
                !this.serviceId ||
                !this.date
            ) {
                this.slots = [];
                return;
            }

            this.loading = true;
            this.time = '';

            try {

                const url =
                    '{{ route(
                        'public.salons.booking.availability',
                        $salon
                    ) }}' +
                    '?barber_id=' +
                    encodeURIComponent(this.barberId) +
                    '&service_id=' +
                    encodeURIComponent(this.serviceId) +
                    '&booking_date=' +
                    encodeURIComponent(this.date);

                const response =
                    await fetch(
                        url,
                        {
                            headers: {
                                'Accept': 'application/json',
                            }
                        }
                    );

                if (!response.ok) {
                    throw new Error();
                }

                const data =
                    await response.json();

                this.slots =
                    Array.isArray(data.slots)
                        ? data.slots
                        : [];

            } catch (error) {

                this.slots = [];

            } finally {

                this.loading = false;
            }
        },

        get selectedService() {

            return this.services.find(
                item =>
                    String(item.id) ===
                    String(this.serviceId)
            );
        }
    }"
        x-init="
        $watch(
            'barberId',
            () => loadSlots()
        );

        $watch(
            'serviceId',
            () => loadSlots()
        );

        $watch(
            'date',
            () => loadSlots()
        );
    "
    >


        <div class="booking-page-header">

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="back-link"
            >
                → بازگشت به سالن
            </a>


            <span class="section-kicker">
            BOOK APPOINTMENT
        </span>


            <h1>
                رزرو نوبت در {{ $salon->name }}
            </h1>


            <p>
                متخصص، خدمت، تاریخ و ساعت مناسب خودت را انتخاب کن.
            </p>

        </div>


        <form
            action="{{ route(
            'public.salons.booking.prepare',
            $salon
        ) }}"
            method="POST"
            class="booking-layout"
        >

            @csrf


            <div class="booking-main">


                {{-- Barber --}}
                <section class="booking-card">

                    <div class="booking-card-heading">

                    <span>
                        01
                    </span>

                        <div>

                            <strong>
                                متخصص
                            </strong>

                            <small>
                                چه کسی قرار است خدماتت را انجام دهد؟
                            </small>

                        </div>

                    </div>


                    @if($barbers->count())

                        <div class="booking-choice-grid">

                            @foreach($barbers as $barber)

                                <button
                                    type="button"
                                    class="booking-choice"
                                    @click="barberId = '{{ $barber->id }}'"
                                    :class="
                                    String(barberId) === '{{ $barber->id }}'
                                        ? 'is-selected'
                                        : ''
                                "
                                >

                                <span class="booking-choice-avatar">

                                    @if($barber->image_path)

                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url(
                                                $barber->image_path
                                            ) }}"
                                            alt=""
                                        >

                                    @else

                                        {{ mb_substr(
                                            $barber->name,
                                            0,
                                            1
                                        ) }}

                                    @endif

                                </span>


                                    <span>

                                    <strong>
                                        {{ $barber->name }}
                                    </strong>

                                    <small>
                                        {{ $barber->specialty ?: 'متخصص زیبایی' }}
                                    </small>

                                </span>

                                </button>

                            @endforeach

                        </div>

                    @else

                        <div class="booking-empty">
                            این سالن هنوز متخصص فعالی ثبت نکرده است.
                        </div>

                    @endif


                    <input
                        type="hidden"
                        name="barber_id"
                        x-model="barberId"
                    >

                </section>


                {{-- Service --}}
                <section class="booking-card">

                    <div class="booking-card-heading">

                    <span>
                        02
                    </span>

                        <div>

                            <strong>
                                خدمت
                            </strong>

                            <small>
                                چیزی که قرار است رزرو کنی
                            </small>

                        </div>

                    </div>


                    @if($services->count())

                        <div class="booking-service-list">

                            @foreach($services as $service)

                                <button
                                    type="button"
                                    class="booking-service"
                                    @click="serviceId = '{{ $service->id }}'"
                                    :class="
                                    String(serviceId) === '{{ $service->id }}'
                                        ? 'is-selected'
                                        : ''
                                "
                                >

                                <span>

                                    <strong>
                                        {{ $service->name }}
                                    </strong>

                                    <small>
                                        {{ $service->duration_minutes }} دقیقه
                                    </small>

                                </span>


                                    <strong>
                                        {{ number_format($service->price) }}
                                        <small>
                                            تومان
                                        </small>
                                    </strong>

                                </button>

                            @endforeach

                        </div>

                    @else

                        <div class="booking-empty">
                            این سالن هنوز خدمتی ثبت نکرده است.
                        </div>

                    @endif


                    <input
                        type="hidden"
                        name="service_id"
                        x-model="serviceId"
                    >

                </section>


                {{-- Date --}}
                <section class="booking-card">

                    <div class="booking-card-heading">

                    <span>
                        03
                    </span>

                        <div>

                            <strong>
                                تاریخ
                            </strong>

                            <small>
                                روز موردنظرت را انتخاب کن
                            </small>

                        </div>

                    </div>


                    <input
                        type="date"
                        name="booking_date"
                        x-model="date"
                        min="{{ now()->format('Y-m-d') }}"
                        class="booking-date-input"
                    >

                </section>


                {{-- Time --}}
                <section class="booking-card">

                    <div class="booking-card-heading">

                    <span>
                        04
                    </span>

                        <div>

                            <strong>
                                ساعت
                            </strong>

                            <small>
                                زمان آزاد متخصص
                            </small>

                        </div>

                    </div>


                    <div
                        x-show="loading"
                        class="booking-state"
                        x-cloak
                    >
                        در حال دریافت زمان‌های آزاد...
                    </div>


                    <div
                        x-show="
                        !loading &&
                        barberId &&
                        serviceId &&
                        slots.length === 0
                    "
                        class="booking-state"
                        x-cloak
                    >
                        برای این انتخاب زمانی پیدا نشد.
                    </div>


                    <div class="booking-time-grid">

                        <template
                            x-for="slot in slots"
                            :key="slot.start"
                        >

                            <button
                                type="button"
                                class="booking-time"
                                :disabled="!slot.available"
                                @click="
                                if (slot.available) {
                                    time = slot.start
                                }
                            "
                                :class="{
                                'is-selected':
                                    time === slot.start,

                                'is-booked':
                                    !slot.available
                            }"
                            >

                            <span
                                dir="ltr"
                                x-text="slot.start"
                            ></span>

                                <small
                                    x-show="!slot.available"
                                >
                                    رزرو شده
                                </small>

                            </button>

                        </template>

                    </div>


                    <input
                        type="hidden"
                        name="start_time"
                        x-model="time"
                    >

                </section>


                {{-- Notes --}}
                <section class="booking-card">

                    <div class="booking-card-heading">

                    <span>
                        05
                    </span>

                        <div>

                            <strong>
                                توضیحات
                            </strong>

                            <small>
                                اختیاری
                            </small>

                        </div>

                    </div>


                    <textarea
                        name="notes"
                        class="customer-textarea"
                        maxlength="2000"
                        placeholder="مثلاً: برای رنگ، موی خیلی حساس دارم..."
                    >{{ old('notes') }}</textarea>

                </section>

            </div>


            {{-- Summary --}}
            <aside class="booking-summary">

                <div class="booking-summary-inner">

                <span class="section-kicker">
                    YOUR BOOKING
                </span>


                    <h2>
                        خلاصه انتخاب
                    </h2>


                    <div class="booking-summary-item">

                    <span>
                        سالن
                    </span>

                        <strong>
                            {{ $salon->name }}
                        </strong>

                    </div>


                    <div class="booking-summary-item">

                    <span>
                        متخصص
                    </span>

                        <strong
                            x-text="
                            barbers.find(
                                item =>
                                    String(item.id) ===
                                    String(barberId)
                            )?.name || 'انتخاب نشده'
                        "
                        ></strong>

                    </div>


                    <div class="booking-summary-item">

                    <span>
                        خدمت
                    </span>

                        <strong
                            x-text="
                            selectedService?.name || 'انتخاب نشده'
                        "
                        ></strong>

                    </div>


                    <div class="booking-summary-item">

                    <span>
                        ساعت
                    </span>

                        <strong
                            dir="ltr"
                            x-text="time || '--:--'"
                        ></strong>

                    </div>


                    <div class="booking-summary-total">

                    <span>
                        مبلغ
                    </span>

                        <strong>

                        <span
                            x-text="
                                selectedService
                                    ? new Intl.NumberFormat('fa-IR')
                                        .format(
                                            selectedService.price
                                        )
                                    : '۰'
                            "
                        ></span>

                            تومان

                        </strong>

                    </div>


                    @if($errors->any())

                        <div class="booking-errors">

                            @foreach($errors->all() as $error)

                                <div>
                                    {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    @endif


                    <button
                        type="submit"
                        class="customer-btn customer-btn-primary customer-btn-lg booking-submit"
                        :disabled="
                        !barberId ||
                        !serviceId ||
                        !date ||
                        !time
                    "
                    >
                        ادامه و بررسی نهایی
                        ←
                    </button>


                    <p class="booking-summary-note">
                        در مرحله بعد، قبل از ثبت نهایی همه‌چیز را دوباره بررسی می‌کنی.
                    </p>

                </div>

            </aside>

        </form>

    </div>

@endsection
