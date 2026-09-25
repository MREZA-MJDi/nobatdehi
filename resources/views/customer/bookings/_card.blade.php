<article class="customer-booking-card">
    <div class="customer-booking-card-main">

        <div class="customer-booking-card-head">
            <div class="min-w-0">
                <div class="customer-card-kicker">
                    {{ $booking->booking_date ? jalali_date($booking->booking_date) : 'بدون تاریخ' }}
                </div>

                <h3 class="customer-booking-title">
                    {{ $booking->salon?->name ?? 'سالن' }}
                </h3>

                <p class="customer-booking-subtitle">
                    {{ $booking->service?->name ?? 'خدمت' }}
                    @if($booking->barber?->name)
                        <span aria-hidden="true">·</span>
                        {{ $booking->barber->name }}
                    @endif
                </p>
            </div>

            <span class="booking-status booking-status-{{ $booking->status->value }}">
                {{ $booking->status->label() }}
            </span>
        </div>

        <div class="customer-booking-meta-grid">
            <div class="customer-booking-meta">
                <span>ساعت</span>
                <strong dir="ltr">
                    {{ substr((string) $booking->start_time, 0, 5) }}
                    @if($booking->end_time)
                        <small>تا {{ substr((string) $booking->end_time, 0, 5) }}</small>
                    @endif
                </strong>
            </div>

            <div class="customer-booking-meta">
                <span>مبلغ</span>
                <strong>
                    {{ $booking->price !== null ? number_format($booking->price) . ' تومان' : '—' }}
                </strong>
            </div>

            <div class="customer-booking-meta">
                <span>مدت</span>
                <strong>
                    {{ $booking->service?->duration_minutes ? $booking->service->duration_minutes . ' دقیقه' : '—' }}
                </strong>
            </div>
        </div>

        <div class="customer-booking-actions">

            <a
                href="{{ route('customer.bookings.show', $booking) }}"
                class="customer-btn customer-btn-secondary"
            >
                جزئیات نوبت
                <span aria-hidden="true">←</span>
            </a>

            <a
                href="{{ route('public.salons.show', $booking->salon) }}"
                class="customer-action-link"
            >
                مشاهده سالن
            </a>

            @if($booking->_customer_can_edit)
                <a
                    href="{{ route('customer.bookings.edit', $booking) }}"
                    class="customer-btn customer-btn-secondary"
                >
                    ویرایش
                </a>

                <form
                    action="{{ route('customer.bookings.cancel', $booking) }}"
                    method="POST"
                    class="contents"
                    data-customer-cancel-form
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="customer-btn customer-btn-danger"
                    >
                        لغو نوبت
                    </button>
                </form>
            @elseif($booking->_customer_can_review)
                <a
                    href="{{ route('customer.bookings.review.create', $booking) }}"
                    class="customer-btn customer-btn-primary"
                >
                    امتیاز و نظر
                    <span aria-hidden="true">★</span>
                </a>
            @elseif($booking->_customer_has_review)
                <span class="customer-booking-reviewed">
                    ✓ نظر ثبت شده
                </span>
            @endif

        </div>
    </div>
</article>
