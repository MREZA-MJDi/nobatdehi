@extends('layouts.app')

@section('title', 'ثبت نظر')

@section('content')

    <div class="mx-auto w-full max-w-2xl px-4 py-6 pb-28 sm:px-6 lg:px-8">

        <div class="mb-6">

            <a
                href="{{ route('customer.dashboard') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← داشبورد
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                REVIEW
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                نظر شما درباره این نوبت
            </h1>

        </div>


        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4">

                @foreach($errors->all() as $error)

                    <div class="text-[10px] font-bold leading-6 text-danger-700">
                        • {{ $error }}
                    </div>

                @endforeach

            </div>

        @endif


        <form
            action="{{ route('customer.bookings.review.store', $booking) }}"
            method="POST"
            class="card p-5 sm:p-6"
        >

            @csrf


            <div class="rounded-2xl bg-primary-50 p-4">

                <div class="text-sm font-black text-content">
                    {{ $booking->salon->name }}
                </div>

                <div class="mt-1 text-[10px] text-content-muted">
                    {{ $booking->service?->name }}
                    —
                    {{ $booking->barber?->name }}
                </div>

            </div>


            <div class="mt-6">

                <div class="text-xs font-black text-content">
                    امتیاز شما
                </div>

                <div
                    x-data="{ rating: {{ old('rating', $booking->review?->rating ?? 0) }} }"
                    class="mt-4"
                >

                    <input
                        type="hidden"
                        name="rating"
                        :value="rating"
                    >

                    <div class="flex flex-row-reverse justify-end gap-1">

                        @for($star = 5; $star >= 1; $star--)

                            <button
                                type="button"
                                @click="rating = {{ $star }}"
                                class="text-4xl leading-none transition"
                                :class="
                                    rating >= {{ $star }}
                                    ? 'text-warning-500'
                                    : 'text-primary-300'
"
                                aria-label="امتیاز {{ $star }} از ۵"
                            >
                                ★
                            </button>

                        @endfor

                    </div>

                </div>

            </div>


            <div class="mt-6">

                <label
                    for="comment"
                    class="form-label"
                >
                    متن نظر
                </label>

                <textarea
                    id="comment"
                    name="comment"
                    class="form-control min-h-36"
                    maxlength="2000"
                    placeholder="تجربه‌تان از کیفیت کار و برخورد سالن را بنویسید..."
                >{{ old('comment', $booking->review?->comment) }}</textarea>

            </div>


            <div class="mt-6 flex justify-end border-t border-border pt-5">

                <button
                    type="submit"
                    class="btn btn-accent w-full sm:w-auto"
                >
                    ثبت نظر
                </button>

            </div>

        </form>

    </div>

@endsection
