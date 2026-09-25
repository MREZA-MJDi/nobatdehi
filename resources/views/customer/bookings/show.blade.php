@extends('layouts.customer')

@section('title', 'جزئیات نوبت')

@section('content')
    <div class="customer-container py-6 pb-28 sm:py-10">
        <div class="mx-auto w-full max-w-4xl">
            <a href="{{ route('customer.bookings.index') }}" class="mb-5 inline-flex items-center gap-2 text-xs font-black text-content-muted transition hover:text-content">
                <span aria-hidden="true">→</span>
                نوبت‌های من
            </a>

            <section class="customer-detail-hero">
                <div class="min-w-0">
                    <span class="customer-eyebrow">BOOKING DETAILS</span>
                    <h1 class="customer-page-title truncate">{{ $booking->salon?->name ?? 'جزئیات نوبت' }}</h1>
                    <p class="customer-page-lead">
                        {{ $booking->service?->name ?? 'خدمت' }}
                        @if($booking->barber?->name)
                            <span aria-hidden="true">·</span> {{ $booking->barber->name }}
                        @endif
                    </p>
                </div>
                <span class="booking-status booking-status-{{ $booking->status->value }}">{{ $booking->status->label() }}</span>
            </section>

            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
                <section class="customer-detail-card">
                    <div class="customer-detail-grid">
                        <div><span>تاریخ</span><strong>{{ $booking->booking_date ? jalali_date($booking->booking_date) : '—' }}</strong></div>
                        <div><span>ساعت</span><strong dir="ltr">{{ substr((string) $booking->start_time, 0, 5) }}@if($booking->end_time) <small>تا {{ substr((string) $booking->end_time, 0, 5) }}</small>@endif</strong></div>
                        <div><span>متخصص</span><strong>{{ $booking->barber?->name ?? '—' }}</strong></div>
                        <div><span>خدمت</span><strong>{{ $booking->service?->name ?? '—' }}</strong></div>
                        <div><span>مدت</span><strong>{{ $booking->service?->duration_minutes ? $booking->service->duration_minutes.' دقیقه' : '—' }}</strong></div>
                        <div><span>مبلغ</span><strong>{{ $booking->price !== null ? number_format($booking->price).' تومان' : '—' }}</strong></div>
                    </div>

                    @if(filled($booking->notes))
                        <div class="customer-detail-note">
                            <span>یادداشت تو</span>
                            <p>{{ $booking->notes }}</p>
                        </div>
                    @endif
                </section>

                <aside class="space-y-4">
                    <section class="customer-detail-side">
                        <span class="customer-eyebrow">SALON</span>
                        <h2>{{ $booking->salon?->name ?? 'سالن' }}</h2>
                        @if($booking->salon?->address)<p>{{ $booking->salon->address }}</p>@endif

                        <div class="mt-4 space-y-2">
                            <a href="{{ route('public.salons.show', $booking->salon) }}" class="customer-btn customer-btn-secondary w-full">صفحه سالن</a>
                            @if($booking->salon?->phone)
                                <a href="tel:{{ $booking->salon->phone }}" class="customer-btn customer-btn-secondary w-full">تماس با سالن</a>
                            @endif
                        </div>
                    </section>

                    <section class="customer-detail-side">
                        @if($customerActions['can_edit'])
                            <span class="customer-card-kicker">ACTION</span>
                            <h2>هنوز قابل مدیریت است</h2>
                            <p>تا وقتی سالن تأییدش نکرده، می‌توانی زمان نوبت را عوض کنی یا لغوش کنی.</p>

                            <a href="{{ route('customer.bookings.edit', $booking) }}" class="customer-btn customer-btn-primary w-full">ویرایش نوبت</a>

                            <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST" class="mt-2" data-customer-cancel-form>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="customer-btn customer-btn-danger w-full">لغو نوبت</button>
                            </form>
                        @elseif($customerActions['can_review'])
                            <span class="customer-card-kicker">YOUR EXPERIENCE</span>
                            <h2>تجربه‌ات را ثبت کن</h2>
                            <p>امتیاز بده و در صورت تمایل چند کلمه درباره تجربه‌ات بنویس.</p>
                            <a href="{{ route('customer.bookings.review.create', $booking) }}" class="customer-btn customer-btn-primary w-full">ثبت امتیاز و نظر</a>
                        @elseif($customerActions['has_review'])
                            <span class="customer-card-kicker">REVIEW</span>
                            <h2>نظر ثبت شده ✓</h2>
                            <p>امتیاز و نظر این نوبت قبلاً ثبت شده است.</p>
                        @else
                            <span class="customer-card-kicker">STATUS</span>
                            <h2>{{ $booking->status->label() }}</h2>
                            <p>وضعیت فعلی نوبتت اینجاست و جزئیاتش همیشه در دسترس توست.</p>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
