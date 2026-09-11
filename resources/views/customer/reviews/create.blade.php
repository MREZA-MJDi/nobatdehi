@extends('layouts.customer')

@section('title', 'ثبت نظر')

@section(
'meta_description',
'تجربه خود از نوبت و خدمات سالن را ثبت کنید.'
)

@section('content')


    <div class="customer-container py-5 pb-28 sm:py-8">

        <div class="mx-auto w-full max-w-2xl">

            {{-- =====================================================
                BACK
            ====================================================== --}}

            <a
                href="{{ route('customer.dashboard') }}"
                class="mb-5 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-content"
            >
                <span aria-hidden="true">→</span>
                نوبت‌های من
            </a>


            {{-- =====================================================
                HEADER
            ====================================================== --}}

            <div class="mb-6">

            <span
                class="
                    text-[10px]
                    font-black
                    tracking-[0.18em]
                    text-accent-600
                "
            >
                YOUR EXPERIENCE
            </span>


                <h1
                    class="
                    mt-2
                    text-2xl
                    font-black
                    leading-tight
                    text-content
                    sm:text-3xl
                "
                >
                    تجربه‌ات چطور بود؟
                </h1>


                <p
                    class="
                    mt-2
                    max-w-xl
                    text-xs
                    leading-7
                    text-content-muted
                "
                >
                    امتیازت را انتخاب کن و اگر دوست داشتی، چند کلمه درباره تجربه‌ات بنویس.
                </p>

            </div>


            {{-- =====================================================
                BOOKING CONTEXT
            ====================================================== --}}

            <section
                class="
                mb-5
                overflow-hidden
                rounded-3xl
                border
                border-border
                bg-surface
                shadow-soft
            "
            >

                <div
                    class="
                    border-b
                    border-border
                    bg-primary-50/60
                    px-5
                    py-4
                "
                >

                <span
                    class="
                        text-[10px]
                        font-black
                        tracking-wide
                        text-content-faint
                    "
                >
                    این نظر برای
                </span>

                </div>


                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">

                    {{-- Salon --}}
                    <div class="min-w-0">

                        <div
                            class="
                            text-[10px]
                            font-medium
                            text-content-muted
                        "
                        >
                            سالن
                        </div>

                        <div
                            class="
                            mt-1
                            truncate
                            text-sm
                            font-black
                            text-content
                        "
                        >
                            {{ $booking->salon?->name ?? '—' }}
                        </div>

                    </div>


                    {{-- Service --}}
                    <div class="min-w-0">

                        <div
                            class="
                            text-[10px]
                            font-medium
                            text-content-muted
                        "
                        >
                            خدمت
                        </div>

                        <div
                            class="
                            mt-1
                            truncate
                            text-sm
                            font-black
                            text-content
                        "
                        >
                            {{ $booking->service?->name ?? '—' }}
                        </div>

                    </div>


                    {{-- Barber --}}
                    <div class="min-w-0">

                        <div
                            class="
                            text-[10px]
                            font-medium
                            text-content-muted
                        "
                        >
                            متخصص
                        </div>

                        <div
                            class="
                            mt-1
                            truncate
                            text-sm
                            font-black
                            text-content
                        "
                        >
                            {{ $booking->barber?->name ?? '—' }}
                        </div>

                    </div>


                    {{-- Date --}}
                    <div class="min-w-0">

                        <div
                            class="
                            text-[10px]
                            font-medium
                            text-content-muted
                        "
                        >
                            تاریخ
                        </div>

                        <div
                            class="
                            mt-1
                            text-sm
                            font-black
                            text-content
                        "
                        >
                            {{ $booking->booking_date
                                ? jalali_date($booking->booking_date)
                                : '—'
                            }}
                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                REVIEW FORM
            ====================================================== --}}

            <form
                action="{{ route('customer.reviews.store', $booking) }}"
                method="POST"
                class="
                overflow-hidden
                rounded-3xl
                border
                border-border
                bg-surface
                shadow-soft
            "
            >

                @csrf


                <div class="p-5 sm:p-7">

                    {{-- =================================================
                        RATING
                    ================================================== --}}

                    <fieldset>

                        <legend
                            class="
                            text-sm
                            font-black
                            text-content
                        "
                        >
                            امتیازت چنده؟
                        </legend>


                        <p
                            class="
                            mt-1.5
                            text-[10px]
                            leading-6
                            text-content-muted
                        "
                        >
                            از ۱ تا ۵ ستاره، تجربه‌ات را امتیاز بده.
                        </p>


                        <div class="mt-5">

                            <div
                                class="
                                flex
                                items-center
                                justify-center
                                gap-1
                                sm:gap-2
                            "
                                dir="ltr"
                            >

                                @for($star = 1; $star <= 5; $star++)

                                    <label
                                        class="
                                        group
                                        cursor-pointer
                                    "
                                    >

                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $star }}"
                                            class="peer sr-only"

                                            {{ (string) old('rating') === (string) $star
                                                ? 'checked'
                                                : ''
                                            }}
                                        >


                                        <span
                                            class="
                                            block
                                            rounded-xl
                                            px-1
                                            py-1
                                            text-4xl
                                            leading-none
                                            text-content-faint
                                            transition-all
                                            duration-150

                                            group-hover:scale-110
                                            group-hover:text-warning-500

                                            peer-checked:scale-110
                                            peer-checked:text-warning-500

                                            peer-focus-visible:rounded-xl
                                            peer-focus-visible:ring-2
                                            peer-focus-visible:ring-accent-500/30

                                            sm:text-5xl
                                        "
                                            aria-hidden="true"
                                        >
                                        ★
                                    </span>


                                        <span class="sr-only">
                                        {{ $star }}
                                        از ۵
                                    </span>

                                    </label>

                                @endfor

                            </div>

                        </div>


                        @error('rating')

                        <p
                            class="
                                mt-3
                                text-center
                                text-[10px]
                                font-bold
                                text-red-600
                            "
                        >
                            {{ $message }}
                        </p>

                        @enderror

                    </fieldset>


                    {{-- =================================================
                        COMMENT
                    ================================================== --}}

                    <div class="mt-8">

                        <div
                            class="
                            flex
                            items-center
                            justify-between
                            gap-3
                        "
                        >

                            <label
                                for="review-comment"
                                class="
                                text-sm
                                font-black
                                text-content
                            "
                            >
                                چیزی هست که دوست داری بگی؟
                            </label>


                            <span
                                class="
                                shrink-0
                                text-[10px]
                                font-medium
                                text-content-faint
                            "
                            >
                            اختیاری
                        </span>

                        </div>


                        <p
                            class="
                            mt-1.5
                            text-[10px]
                            leading-6
                            text-content-muted
                        "
                        >
                            درباره کیفیت کار، رفتار متخصص یا تجربه کلیت بنویس.
                        </p>


                        <textarea
                            id="review-comment"
                            name="comment"
                            rows="5"
                            maxlength="2000"
                            class="
                            customer-input
                            mt-3
                            min-h-32
                            w-full
                            resize-y
                        "
                            placeholder="مثلاً: برخورد متخصص خیلی خوب بود و از نتیجه کار راضی بودم..."
                        >{{ old('comment') }}</textarea>


                        <div
                            class="
                            mt-1.5
                            text-left
                            text-[9px]
                            text-content-faint
                        "
                            dir="ltr"
                        >
                            حداکثر ۲۰۰۰ کاراکتر
                        </div>


                        @error('comment')

                        <p
                            class="
                                mt-2
                                text-[10px]
                                font-bold
                                text-red-600
                            "
                        >
                            {{ $message }}
                        </p>

                        @enderror

                    </div>


                    {{-- =================================================
                        HELPFUL NOTE
                    ================================================== --}}

                    <div
                        class="
                        mt-6
                        flex
                        items-start
                        gap-3
                        rounded-2xl
                        border
                        border-accent-100
                        bg-accent-50/70
                        px-4
                        py-3
                    "
                    >

                    <span
                        class="
                            mt-0.5
                            flex
                            h-7
                            w-7
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-surface
                            text-accent-600
                            shadow-sm
                        "
                        aria-hidden="true"
                    >
                        ✓
                    </span>


                        <p
                            class="
                            text-[10px]
                            leading-6
                            text-content-muted
                        "
                        >
                            نظرت به بقیه مشتری‌ها کمک می‌کند انتخاب بهتری داشته باشند.
                        </p>

                    </div>

                </div>


                {{-- =================================================
                    ACTIONS
                ================================================== --}}

                <div
                    class="
                    flex
                    flex-col-reverse
                    gap-3
                    border-t
                    border-border
                    bg-primary-50/40
                    p-5
                    sm:flex-row
                    sm:items-center
                    sm:justify-end
                "
                >

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="
                        inline-flex
                        min-h-11
                        items-center
                        justify-center
                        rounded-xl
                        px-5
                        text-xs
                        font-black
                        text-content-muted
                        transition
                        hover:bg-surface
                        hover:text-content
                    "
                    >
                        انصراف
                    </a>


                    <button
                        type="submit"
                        class="
                        inline-flex
                        min-h-11
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-accent-600
                        px-6
                        text-xs
                        font-black
                        text-white
                        shadow-sm
                        transition
                        hover:-translate-y-0.5
                        hover:bg-accent-700
                        hover:shadow-md
                        focus:outline-none
                        focus:ring-2
                        focus:ring-accent-500/30
                    "
                    >
                        ثبت نظر

                        <span aria-hidden="true">
                        ←
                    </span>
                    </button>

                </div>

            </form>

        </div>

    </div>


@endsection
