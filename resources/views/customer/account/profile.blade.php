@extends('layouts.customer')

@section('title', 'پروفایل من')

@section(
    'meta_description',
    'مدیریت اطلاعات حساب کاربری در NOBAT.'
)

@section('content')

    <div class="customer-container py-5 pb-28 sm:py-8">

        <div class="mx-auto w-full max-w-4xl">

            {{-- =====================================================
                HEADER
            ====================================================== --}}

            <div class="mb-6">

                <a
                    href="{{ route('customer.dashboard') }}"
                    class="mb-5 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-content"
                >
                    <span aria-hidden="true">→</span>
                    برگشت به نوبت‌های من
                </a>


                <div
                    class="
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
                            relative
                            overflow-hidden
                            bg-content
                            px-5
                            py-7
                            text-surface
                            sm:px-7
                            sm:py-8
                        "
                    >

                        <div
                            class="
                                pointer-events-none
                                absolute
                                -left-20
                                -top-20
                                h-56
                                w-56
                                rounded-full
                                bg-white/10
                                blur-3xl
                            "
                            aria-hidden="true"
                        ></div>


                        <div
                            class="
                                pointer-events-none
                                absolute
                                -bottom-28
                                right-1/3
                                h-64
                                w-64
                                rounded-full
                                bg-accent-500/20
                                blur-3xl
                            "
                            aria-hidden="true"
                        ></div>


                        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">

                            {{-- Avatar --}}
                            <div
                                class="
                                    flex
                                    h-20
                                    w-20
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-[1.75rem]
                                    bg-white/10
                                    text-2xl
                                    font-black
                                    text-white
                                    ring-1
                                    ring-white/15
                                    backdrop-blur
                                    sm:h-24
                                    sm:w-24
                                    sm:text-3xl
                                "
                            >
                                {{
                                    mb_substr(
                                        trim($user->name ?: 'کاربر'),
                                        0,
                                        1
                                    )
                                }}
                            </div>


                            <div class="min-w-0">

                                <div
                                    class="
                                        text-[10px]
                                        font-black
                                        tracking-[0.18em]
                                        text-white/45
                                    "
                                >
                                    MY PROFILE
                                </div>


                                <h1
                                    class="
                                        mt-2
                                        truncate
                                        text-2xl
                                        font-black
                                        sm:text-3xl
                                    "
                                >
                                    {{ $user->name ?: 'حساب من' }}
                                </h1>


                                <p
                                    class="
                                        mt-2
                                        text-xs
                                        leading-6
                                        text-white/60
                                    "
                                >
                                    اطلاعات شخصی‌ات را اینجا مدیریت کن.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        ACCOUNT META
                    ================================================== --}}

                    <div
                        class="
                            grid
                            gap-px
                            bg-border
                            sm:grid-cols-2
                        "
                    >

                        <div
                            class="
                                bg-surface
                                px-5
                                py-4
                                sm:px-6
                            "
                        >

                            <div
                                class="
                                    text-[10px]
                                    font-bold
                                    text-content-muted
                                "
                            >
                                شماره موبایل
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
                                {{ $user->phone ?: 'ثبت نشده' }}
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-[9px]
                                    text-content-faint
                                "
                            >
                                شماره تماس برای رزروهای شما استفاده می‌شود.
                            </div>

                        </div>


                        <div
                            class="
                                bg-surface
                                px-5
                                py-4
                                sm:px-6
                            "
                        >

                            <div
                                class="
                                    text-[10px]
                                    font-bold
                                    text-content-muted
                                "
                            >
                                عضویت
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-black
                                    text-content
                                "
                            >
                                {{ $user->created_at?->format('Y/m/d') ?? '—' }}
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-[9px]
                                    text-content-faint
                                "
                            >
                                تاریخ ساخت حساب کاربری
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                MAIN GRID
            ====================================================== --}}

            <div
                class="
                    grid
                    gap-5
                    lg:grid-cols-[minmax(0,1fr)_280px]
                "
            >

                {{-- =================================================
                    EDIT FORM
                ================================================== --}}

                <section
                    class="
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
                            px-5
                            py-5
                            sm:px-6
                        "
                    >

                        <div
                            class="
                                text-[10px]
                                font-black
                                tracking-[0.16em]
                                text-content-faint
                            "
                        >
                            PERSONAL INFO
                        </div>

                        <h2
                            class="
                                mt-1.5
                                text-lg
                                font-black
                                text-content
                            "
                        >
                            اطلاعات حساب
                        </h2>

                        <p
                            class="
                                mt-1.5
                                text-xs
                                leading-6
                                text-content-muted
                            "
                        >
                            نام و ایمیلی که در حساب نوبت‌دهی‌ات نمایش داده می‌شود را مدیریت کن.
                        </p>

                    </div>


                    <form
                        action="{{ route('customer.profile.update') }}"
                        method="POST"
                        class="p-5 sm:p-6"
                    >

                        @csrf
                        @method('PUT')


                        {{-- Name --}}
                        <div>

                            <label
                                for="profile-name"
                                class="
                                    block
                                    text-xs
                                    font-black
                                    text-content
                                "
                            >
                                نام و نام خانوادگی
                            </label>


                            <p
                                class="
                                    mt-1
                                    text-[10px]
                                    text-content-muted
                                "
                            >
                                این نام کنار نوبت‌های شما نمایش داده می‌شود.
                            </p>


                            <input
                                id="profile-name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                autocomplete="name"
                                maxlength="120"
                                required
                                class="
                                    customer-input
                                    mt-3
                                    w-full
                                "
                                placeholder="مثلاً علی رضایی"
                            >


                            @error('name')

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


                        {{-- Email --}}
                        <div class="mt-6">

                            <label
                                for="profile-email"
                                class="
                                    block
                                    text-xs
                                    font-black
                                    text-content
                                "
                            >
                                ایمیل
                            </label>


                            <p
                                class="
                                    mt-1
                                    text-[10px]
                                    text-content-muted
                                "
                            >
                                اختیاری است و برای اطلاعات حساب استفاده می‌شود.
                            </p>


                            <input
                                id="profile-email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user->email) }}"
                                autocomplete="email"
                                maxlength="190"
                                class="
                                    customer-input
                                    mt-3
                                    w-full
                                "
                                placeholder="example@email.com"
                                dir="ltr"
                            >


                            @error('email')

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


                        {{-- Phone --}}
                        <div class="mt-6">

                            <label
                                for="profile-phone"
                                class="
                                    block
                                    text-xs
                                    font-black
                                    text-content
                                "
                            >
                                شماره موبایل
                            </label>


                            <div
                                class="
                                    relative
                                    mt-3
                                "
                            >

                                <input
                                    id="profile-phone"
                                    type="text"
                                    value="{{ $user->phone ?: 'ثبت نشده' }}"
                                    readonly
                                    aria-describedby="profile-phone-help"
                                    class="
                                        customer-input
                                        w-full
                                        cursor-not-allowed
                                        bg-primary-50
                                        text-content-muted
                                    "
                                    dir="ltr"
                                >

                                <span
                                    class="
                                        pointer-events-none
                                        absolute
                                        left-3
                                        top-1/2
                                        -translate-y-1/2
                                        rounded-lg
                                        bg-surface
                                        px-2
                                        py-1
                                        text-[9px]
                                        font-black
                                        text-content-faint
                                        shadow-sm
                                    "
                                >
                                    تایید شده
                                </span>

                            </div>


                            <p
                                id="profile-phone-help"
                                class="
                                    mt-2
                                    text-[9px]
                                    leading-5
                                    text-content-faint
                                "
                            >
                                برای تغییر شماره موبایل باید فرآیند تأیید شماره انجام شود.
                            </p>

                        </div>


                        {{-- Submit --}}
                        <div
                            class="
                                mt-7
                                flex
                                flex-col-reverse
                                gap-3
                                border-t
                                border-border
                                pt-5
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
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
                                    px-4
                                    text-xs
                                    font-black
                                    text-content-muted
                                    transition
                                    hover:bg-primary-50
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
                                ذخیره تغییرات

                                <span aria-hidden="true">
                                    ✓
                                </span>
                            </button>

                        </div>

                    </form>

                </section>


                {{-- =================================================
                    SIDE INFORMATION
                ================================================== --}}

                <aside class="space-y-5">

                    {{-- Account identity --}}
                    <section
                        class="
                            rounded-3xl
                            border
                            border-border
                            bg-surface
                            p-5
                            shadow-soft
                        "
                    >

                        <div
                            class="
                                flex
                                h-11
                                w-11
                                items-center
                                justify-center
                                rounded-2xl
                                bg-accent-50
                                text-base
                                font-black
                                text-accent-700
                            "
                            aria-hidden="true"
                        >
                            ✓
                        </div>


                        <h2
                            class="
                                mt-4
                                text-sm
                                font-black
                                text-content
                            "
                        >
                            حساب شخصی تو
                        </h2>


                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-6
                                text-content-muted
                            "
                        >
                            اطلاعات این صفحه فقط برای مدیریت حساب خودت استفاده می‌شود.
                        </p>

                    </section>


                    {{-- Booking shortcut --}}
                    <section
                        class="
                            rounded-3xl
                            border
                            border-border
                            bg-surface
                            p-5
                            shadow-soft
                        "
                    >

                        <div
                            class="
                                text-[10px]
                                font-black
                                tracking-[0.14em]
                                text-content-faint
                            "
                        >
                            NOBAT
                        </div>


                        <h2
                            class="
                                mt-2
                                text-sm
                                font-black
                                text-content
                            "
                        >
                            دنبال نوبت جدیدی؟
                        </h2>


                        <p
                            class="
                                mt-2
                                text-[10px]
                                leading-6
                                text-content-muted
                            "
                        >
                            یک سالن پیدا کن و نوبت بعدی‌ات را رزرو کن.
                        </p>


                        <a
                            href="{{ route('salons.discover') }}"
                            class="
                                mt-4
                                inline-flex
                                min-h-10
                                w-full
                                items-center
                                justify-center
                                gap-2
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
                            کشف سالن‌ها

                            <span aria-hidden="true">
                                ←
                            </span>
                        </a>

                    </section>

                </aside>

            </div>

        </div>

    </div>

@endsection
