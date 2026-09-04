@extends('layouts.customer')

@section('title', 'نوبت‌های من')

@section('content')

    <div class="customer-container account-page">

        <div class="account-header">

            <div>

            <span class="section-kicker">
                MY RM
            </span>

                <h1>
                    سلام {{ auth()->user()->name ?: 'دوست خوب RM' }}
                </h1>

                <p>
                    اینجا همه نوبت‌ها و تجربه‌های تو جمع شده.
                </p>

            </div>


            <a
                href="{{ route('salons.discover') }}"
                class="customer-btn customer-btn-primary"
            >
                رزرو جدید
                +
            </a>

        </div>


        <div class="account-stat-grid">

            <div class="account-stat-card">
                <span>در پیش رو</span>
                <strong>{{ $upcomingCount }}</strong>
            </div>

            <div class="account-stat-card">
                <span>تکمیل‌شده</span>
                <strong>{{ $completedCount }}</strong>
            </div>

            <div class="account-stat-card">
                <span>لغوشده</span>
                <strong>{{ $cancelledCount }}</strong>
            </div>

        </div>


        <section class="account-section">

            <div class="customer-section-heading">

                <div>
                <span>
                    BOOKINGS
                </span>

                    <h2>
                        نوبت‌های من
                    </h2>
                </div>

                <a href="{{ route('customer.profile.edit') }}">
                    پروفایل
                </a>

            </div>


            @if($bookings->count())

                <div class="account-booking-list">

                    @foreach($bookings as $booking)

                        <article class="account-booking-card">

                            <div class="account-booking-date">

                            <span>
                                {{ $booking->booking_date->format('Y/m/d') }}
                            </span>

                                <strong dir="ltr">
                                    {{ $booking->start_time }}
                                </strong>

                            </div>


                            <div class="account-booking-main">

                                <strong>
                                    {{ $booking->salon->name }}
                                </strong>

                                <span>
                                {{ $booking->service?->name }}
                                ·
                                {{ $booking->barber?->name }}
                            </span>

                            </div>


                            <div>

                            <span
                                class="booking-status booking-status-{{ $booking->status->value }}"
                            >
                                {{ $booking->status->label() }}
                            </span>

                                @if(
                                    $booking->status->value === 'completed' &&
                                    !$booking->review
                                )

                                    <a
                                        href="{{ route(
                                        'customer.bookings.review.create',
                                        $booking
                                    ) }}"
                                        class="booking-review-link"
                                    >
                                        ثبت نظر
                                    </a>

                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>


                <div class="discover-pagination">
                    {{ $bookings->links() }}
                </div>

            @else

                <div class="discover-empty">

                    <div class="discover-empty-icon">
                        ○
                    </div>

                    <h3>
                        هنوز نوبتی نداری
                    </h3>

                    <p>
                        یک سالن خوب پیدا کن و اولین نوبتت را ثبت کن.
                    </p>

                    <a
                        href="{{ route('salons.discover') }}"
                        class="customer-btn customer-btn-primary"
                    >
                        کشف سالن‌ها
                    </a>

                </div>

            @endif

        </section>

    </div>

@endsection
