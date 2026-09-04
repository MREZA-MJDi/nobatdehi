@extends('layouts.customer')

@section('title', 'ثبت نظر')

@section('content')

    <div class="customer-container review-page">

        <div class="account-header">

            <div>

            <span class="section-kicker">
                REVIEW
            </span>

                <h1>
                    تجربه‌ات را ثبت کن
                </h1>

                <p>
                    نظر واقعی تو به انتخاب بهتر بقیه کمک می‌کند.
                </p>

            </div>

        </div>


        <form
            action="{{ route(
            'customer.bookings.review.store',
            $booking
        ) }}"
            method="POST"
            class="review-form-card"
            x-data="{
            rating: {{ old(
                'rating',
                $booking->review?->rating ?? 0
            ) }}
                }"
        >

            @csrf


            <div class="review-salon-summary">

                <strong>
                    {{ $booking->salon->name }}
                </strong>

                <span>
                {{ $booking->service?->name }}
                ·
                {{ $booking->barber?->name }}
            </span>

            </div>


            <div class="review-stars">

                <label>
                    امتیاز
                </label>

                <div>

                    @for($star = 1; $star <= 5; $star++)

                        <button
                            type="button"
                            @click="rating = {{ $star }}"
                            :class="
                            rating >= {{ $star }}
                                ? 'is-active'
                                : ''
                        "
                            aria-label="امتیاز {{ $star }} از ۵"
                        >
                            ★
                        </button>

                    @endfor

                </div>


                <input
                    type="hidden"
                    name="rating"
                    x-model="rating"
                >

            </div>


            <div class="form-field">

                <label for="comment">
                    متن نظر
                </label>

                <textarea
                    id="comment"
                    name="comment"
                    class="customer-textarea"
                    maxlength="2000"
                    placeholder="کیفیت کار، برخورد و تجربه‌ات را بنویس..."
                >{{ old(
                'comment',
                $booking->review?->comment
            ) }}</textarea>

            </div>


            @if($errors->any())

                <div class="booking-errors">

                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach

                </div>

            @endif


            <button
                type="submit"
                class="customer-btn customer-btn-primary customer-btn-lg"
            >
                ثبت نظر
            </button>

        </form>

    </div>

@endsection
