@extends('layouts.customer')

@section('title', 'نوبت‌های من')

@section(
    'meta_description',
    'مشاهده و مدیریت نوبت‌های ثبت‌شده شما در NOBAT.'
)

@section('content')

    <div class="customer-container py-5 pb-28 sm:py-8">

        <div class="mx-auto w-full max-w-5xl">

            {{-- =====================================================
                WELCOME
            ====================================================== --}}

            <section
                class="
                    mb-7
                    flex
                    flex-col
                    gap-5
                    rounded-3xl
                    border
                    border-border
                    bg-surface
                    p-5
                    shadow-soft
                    sm:flex-row
                    sm:items-end
                    sm:justify-between
                    sm:p-7
                "
            >

                <div class="min-w-0">

                    <span
                        class="
                            text-[10px]
                            font-black
                            tracking-[0.18em]
                            text-accent-600
                        "
                    >
                        MY NOBAT
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
                        سلام
                        {{ auth()->user()->name ?: 'دوست خوبم' }}
                        👋
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
                        نوبت‌هات اینجا هستند. برای رزرو جدید فقط سالن موردنظرت رو پیدا کن.
                    </p>

                </div>


                <a
                    href="{{ route('salons.discover') }}"
                    class="
                        inline-flex
                        min-h-11
                        shrink-0
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-accent-600
                        px-5
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
                    <span>
                        رزرو نوبت جدید
                    </span>

                    <span
                        class="text-base leading-none"
                        aria-hidden="true"
                    >
                        +
                    </span>
                </a>

            </section>


            {{-- =====================================================
                BOOKINGS
            ====================================================== --}}

            <section>

                <div
                    class="
                        mb-4
                        flex
                        items-end
                        justify-between
                        gap-4
                    "
                >

                    <div>

                        <span
                            class="
                                text-[10px]
                                font-black
                                tracking-[0.16em]
                                text-content-faint
                            "
                        >
                            BOOKINGS
                        </span>

                        <h2
                            class="
                                mt-1
                                text-xl
                                font-black
                                text-content
                                sm:text-2xl
                            "
                        >
                            نوبت‌های من
                        </h2>

                    </div>


                    <a
                        href="{{ route('customer.profile.edit') }}"
                        class="
                            text-xs
                            font-bold
                            text-content-muted
                            transition
                            hover:text-content
                        "
                    >
                        پروفایل
                    </a>

                </div>


                @if($bookings->isNotEmpty())

                    <div class="space-y-3">

                        @foreach($bookings as $booking)

                            <article
                                class="
                                    overflow-hidden
                                    rounded-3xl
                                    border
                                    border-border
                                    bg-surface
                                    shadow-soft
                                    transition
                                    hover:-translate-y-0.5
                                    hover:shadow-md
                                "
                            >

                                <div
                                    class="
                                        flex
                                        flex-col
                                        gap-5
                                        p-5
                                        sm:p-6
                                    "
                                >

                                    {{-- =================================================
                                        TOP ROW
                                    ================================================== --}}

                                    <div
                                        class="
                                            flex
                                            flex-col
                                            gap-4
                                            sm:flex-row
                                            sm:items-start
                                            sm:justify-between
                                        "
                                    >

                                        <div class="min-w-0">

                                            <div
                                                class="
                                                    flex
                                                    flex-wrap
                                                    items-center
                                                    gap-2
                                                "
                                            >

                                                <h3
                                                    class="
                                                        truncate
                                                        text-base
                                                        font-black
                                                        text-content
                                                    "
                                                >
                                                    {{ $booking->salon?->name ?? 'سالن' }}
                                                </h3>


                                                <span
                                                    class="
                                                        booking-status
                                                        booking-status-{{ $booking->status->value }}
                                                        "
                                                >
                                                    {{ $booking->status->label() }}
                                                </span>

                                            </div>


                                            <p
                                                class="
                                                    mt-1.5
                                                    text-xs
                                                    text-content-muted
                                                "
                                            >
                                                {{ $booking->service?->name ?? 'خدمت' }}

                                                @if($booking->barber?->name)
                                                    <span class="mx-1 text-content-faint">
                                                        ·
                                                    </span>

                                                    {{ $booking->barber->name }}
                                                @endif
                                            </p>

                                        </div>


                                        <a
                                            href="{{ route('public.salons.show', $booking->salon) }}"
                                            class="
                                                inline-flex
                                                shrink-0
                                                items-center
                                                gap-1.5
                                                text-[10px]
                                                font-bold
                                                text-content-muted
                                                transition
                                                hover:text-accent-600
                                            "
                                        >
                                            مشاهده سالن
                                            <span aria-hidden="true">
                                                ←
                                            </span>
                                        </a>

                                    </div>


                                    {{-- =================================================
                                        DATE + TIME
                                    ================================================== --}}

                                    <div
                                        class="
                                            grid
                                            gap-3
                                            sm:grid-cols-2
                                        "
                                    >

                                        <div
                                            class="
                                                rounded-2xl
                                                bg-primary-50/60
                                                px-4
                                                py-3
                                            "
                                        >

                                            <div
                                                class="
                                                    text-[10px]
                                                    font-bold
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
                                                {{ jalali_date($booking->booking_date) }}
                                            </div>

                                        </div>


                                        <div
                                            class="
                                                rounded-2xl
                                                bg-primary-50/60
                                                px-4
                                                py-3
                                            "
                                        >

                                            <div
                                                class="
                                                    text-[10px]
                                                    font-bold
                                                    text-content-muted
                                                "
                                            >
                                                ساعت
                                            </div>

                                            <div
                                                class="
                                                    mt-1
                                                    text-sm
                                                    font-black
                                                    text-content
                                                "
                                                dir="ltr"
                                            >
                                                {{ \Illuminate\Support\Str::substr(
                                                    $booking->start_time,
                                                    0,
                                                    5
                                                ) }}
                                            </div>

                                        </div>

                                    </div>


                                    {{-- =================================================
                                        EXTRA DETAILS
                                    ================================================== --}}

                                    <div
                                        class="
                                            flex
                                            flex-wrap
                                            gap-x-5
                                            gap-y-2
                                            border-t
                                            border-border
                                            pt-4
                                            text-[10px]
                                            text-content-muted
                                        "
                                    >

                                        @if($booking->service?->duration_minutes)

                                            <span>
                                                مدت:
                                                {{ $booking->service->duration_minutes }}
                                                دقیقه
                                            </span>

                                        @endif


                                        @if($booking->price !== null)

                                            <span>
                                                مبلغ:
                                                {{ number_format($booking->price) }}
                                                تومان
                                            </span>

                                        @endif

                                    </div>


                                    {{-- =================================================
                                        ACTION
                                    ================================================== --}}

                                    @if(
                                        $booking->status === \App\Enums\BookingStatus::PENDING
                                    )

                                        <div
                                            class="
                                                flex
                                                flex-col
                                                gap-2
                                                border-t
                                                border-border
                                                pt-4
                                                sm:flex-row
                                                sm:items-center
                                                sm:justify-between
                                            "
                                        >

                                            <span
                                                class="
                                                    text-[10px]
                                                    font-bold
                                                    text-content-muted
                                                "
                                            >
                                                در انتظار تأیید سالن
                                            </span>


                                            <div
                                                class="
                                                    flex
                                                    flex-col
                                                    gap-2
                                                    sm:flex-row
                                                "
                                            >

                                                <a
                                                    href="{{ route('customer.bookings.edit', $booking) }}"
                                                    class="
                                                        inline-flex
                                                        min-h-10
                                                        items-center
                                                        justify-center
                                                        rounded-xl
                                                        border
                                                        border-border
                                                        px-4
                                                        text-[10px]
                                                        font-black
                                                        text-content
                                                        transition
                                                        hover:bg-primary-50
                                                    "
                                                >
                                                    ویرایش نوبت
                                                </a>


                                                <form
                                                    action="{{ route('customer.bookings.cancel', $booking) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('از لغو این نوبت مطمئن هستید؟')"
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="
                                                            inline-flex
                                                            min-h-10
                                                            w-full
                                                            items-center
                                                            justify-center
                                                            rounded-xl
                                                            border
                                                            border-red-200
                                                            bg-red-50
                                                            px-4
                                                            text-[10px]
                                                            font-black
                                                            text-red-600
                                                            transition
                                                            hover:bg-red-100
                                                            sm:w-auto
                                                        "
                                                    >
                                                        لغو نوبت
                                                    </button>

                                                </form>

                                            </div>

                                        </div>


                                    @elseif(
                                        $booking->status === \App\Enums\BookingStatus::COMPLETED
                                        && !$booking->review
                                    )

                                        <div
                                            class="
                                                flex
                                                flex-col
                                                gap-3
                                                rounded-2xl
                                                bg-accent-50
                                                p-4
                                                sm:flex-row
                                                sm:items-center
                                                sm:justify-between
                                            "
                                        >

                                            <div>

                                                <div
                                                    class="
                                                        text-xs
                                                        font-black
                                                        text-content
                                                    "
                                                >
                                                    تجربه‌ات چطور بود؟ ⭐
                                                </div>

                                                <div
                                                    class="
                                                        mt-1
                                                        text-[10px]
                                                        text-content-muted
                                                    "
                                                >
                                                    چند ثانیه وقت بگذار و نظرت را ثبت کن.
                                                </div>

                                            </div>


                                            <a
                                                href="{{ route(
                                                    'customer.bookings.review.create',
                                                    $booking
                                                ) }}"
                                                class="
                                                    inline-flex
                                                    min-h-10
                                                    shrink-0
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    bg-content
                                                    px-4
                                                    text-[10px]
                                                    font-black
                                                    text-surface
                                                    transition
                                                    hover:-translate-y-0.5
                                                "
                                            >
                                                ثبت نظر
                                                <span
                                                    class="mr-2"
                                                    aria-hidden="true"
                                                >
                                                    ←
                                                </span>
                                            </a>

                                        </div>


                                    @elseif(
                                        $booking->status === \App\Enums\BookingStatus::COMPLETED
                                        && $booking->review
                                    )

                                        <div
                                            class="
                                                flex
                                                items-center
                                                gap-2
                                                border-t
                                                border-border
                                                pt-4
                                            "
                                        >

                                            <span
                                                class="
                                                    flex
                                                    h-7
                                                    w-7
                                                    items-center
                                                    justify-center
                                                    rounded-full
                                                    bg-success-50
                                                    text-success-700
                                                "
                                                aria-hidden="true"
                                            >
                                                ✓
                                            </span>

                                            <span
                                                class="
                                                    text-[10px]
                                                    font-black
                                                    text-content-muted
                                                "
                                            >
                                                نظر شما ثبت شده است.
                                            </span>

                                        </div>

                                    @endif

                                </div>

                            </article>

                        @endforeach

                    </div>


                    @if($bookings->hasPages())

                        <div class="mt-6">
                            {{ $bookings->links() }}
                        </div>

                    @endif


                @else

                    {{-- =================================================
                        EMPTY STATE
                    ================================================== --}}

                    <section
                        class="
                            rounded-3xl
                            border
                            border-dashed
                            border-border
                            bg-surface
                            px-5
                            py-12
                            text-center
                            shadow-soft
                        "
                    >

                        <div
                            class="
                                mx-auto
                                flex
                                h-14
                                w-14
                                items-center
                                justify-center
                                rounded-2xl
                                bg-accent-50
                                text-xl
                                text-accent-600
                            "
                            aria-hidden="true"
                        >
                            ◷
                        </div>


                        <h3
                            class="
                                mt-4
                                text-base
                                font-black
                                text-content
                            "
                        >
                            هنوز نوبتی نداری
                        </h3>


                        <p
                            class="
                                mx-auto
                                mt-2
                                max-w-sm
                                text-xs
                                leading-7
                                text-content-muted
                            "
                        >
                            یک سالن پیدا کن و اولین نوبتت را همین الان رزرو کن.
                        </p>


                        <a
                            href="{{ route('salons.discover') }}"
                            class="
                                mt-5
                                inline-flex
                                min-h-10
                                items-center
                                justify-center
                                rounded-xl
                                bg-accent-600
                                px-5
                                text-[10px]
                                font-black
                                text-white
                                transition
                                hover:bg-accent-700
                            "
                        >
                            پیدا کردن سالن
                            <span
                                class="mr-2"
                                aria-hidden="true"
                            >
                                ←
                            </span>
                        </a>

                    </section>

                @endif

            </section>

        </div>

    </div>

@endsection
