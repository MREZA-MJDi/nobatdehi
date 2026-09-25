@extends('layouts.customer')

@section('title', 'نوبت با موفقیت ثبت شد')

@section('content')

    <div class="customer-container py-8 pb-28 sm:py-14">

        <div class="mx-auto w-full max-w-2xl">

            <section class="overflow-hidden rounded-[2rem] border border-border bg-surface shadow-soft">

                <div class="px-6 py-8 text-center sm:px-10 sm:py-11">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-success-50 text-2xl text-success-700"
                        aria-hidden="true"
                    >
                        ✓
                    </div>

                    <div class="mt-6 text-[10px] font-black tracking-[0.18em] text-accent-600">
                        BOOKING CONFIRMED
                    </div>

                    <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                        نوبتت ثبت شد.
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-xs leading-7 text-content-muted">
                        انتخابت با موفقیت ثبت شده و از اینجا می‌توانی وضعیت نوبتت را پیگیری کنی.
                    </p>

                    <div class="mx-auto mt-7 grid max-w-lg gap-3 text-right sm:grid-cols-2">

                        <div class="rounded-2xl bg-primary-50/70 px-4 py-4">
                            <div class="text-[10px] font-bold text-content-muted">سالن</div>
                            <div class="mt-1 text-sm font-black text-content">{{ $booking->salon?->name ?? '—' }}</div>
                        </div>

                        <div class="rounded-2xl bg-primary-50/70 px-4 py-4">
                            <div class="text-[10px] font-bold text-content-muted">خدمت</div>
                            <div class="mt-1 text-sm font-black text-content">{{ $booking->service?->name ?? '—' }}</div>
                        </div>

                        <div class="rounded-2xl bg-primary-50/70 px-4 py-4">
                            <div class="text-[10px] font-bold text-content-muted">متخصص</div>
                            <div class="mt-1 text-sm font-black text-content">{{ $booking->barber?->name ?? '—' }}</div>
                        </div>

                        <div class="rounded-2xl bg-primary-50/70 px-4 py-4">
                            <div class="text-[10px] font-bold text-content-muted">زمان</div>
                            <div class="mt-1 text-sm font-black text-content">
                                {{ $booking->booking_date ? jalali_date($booking->booking_date) : '—' }}
                                ·
                                <span dir="ltr">{{ \\Illuminate\\Support\\Str::substr($booking->start_time, 0, 5) }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="mx-auto mt-6 max-w-lg rounded-2xl border border-accent-100 bg-accent-50/70 px-4 py-3 text-[10px] font-bold leading-6 text-content-muted">
                        وضعیت نوبت:
                        <span class="text-content">{{ $booking->status->label() }}</span>
                    </div>

                </div>

                <div class="flex flex-col gap-3 border-t border-border bg-primary-50/40 p-5 sm:flex-row sm:justify-center">

                    <a
                        href="{{ route('customer.bookings.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-accent-600 px-6 text-xs font-black text-white transition hover:-translate-y-0.5 hover:bg-accent-700"
                    >
                        مشاهده نوبت‌های من
                    </a>

                    <a
                        href="{{ route('public.salons.show', $booking->salon) }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border px-6 text-xs font-black text-content transition hover:bg-surface"
                    >
                        بازگشت به سالن
                    </a>

                </div>

            </section>

        </div>

    </div>

@endsection
