@extends('layouts.auth')

@section('title', 'تأیید ثبت‌نام')

@section('content')

    <div
        x-data="{
            code: '',
            submitting: false,
            resendCooldown: 0,
            resendTimer: null,

            normalize(value) {

                return String(value || '')
                    .replace(/[۰-۹]/g, char =>
                        String(
                            '۰۱۲۳۴۵۶۷۸۹'
                                .indexOf(char)
                        )
                    )
                    .replace(/[٠-٩]/g, char =>
                        String(
                            '٠١٢٣٤٥٦٧٨٩'
                                .indexOf(char)
                        )
                    )
            },

            sanitize() {

                this.code =
                    this.normalize(
                        this.code
                    )
                    .replace(/\D/g, '')
                    .slice(0, 6)

            },

            autoSubmit() {

                this.sanitize()

                if (
                    this.code.length !== 6 ||
                    this.submitting
                ) {
                    return
                }

                this.submitting = true

                this.$nextTick(() => {

                    this.$refs.form.submit()

                })
            },

            startResendCooldown(seconds = 60) {

                this.resendCooldown =
                    Number(seconds) || 60

                clearInterval(
                    this.resendTimer
                )

                this.resendTimer =
                    setInterval(() => {

                        this.resendCooldown--

                        if (
                            this.resendCooldown <= 0
                        ) {

                            clearInterval(
                                this.resendTimer
                            )

                            this.resendCooldown =
                                0
                        }

                    }, 1000)
            },

            init() {

                this.$nextTick(() => {

                    this.$refs.code?.focus()

                })

            }
        }"
        x-init="init()"
        class="card overflow-hidden"
    >

        <div class="p-6 sm:p-7">

            {{-- =========================================================
                BACK
            ========================================================== --}}

            <a
                href="{{ route('register') }}"
                class="mb-6 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600"
            >
                ← تغییر اطلاعات
            </a>


            {{-- =========================================================
                HEADER
            ========================================================== --}}

            <div class="mb-7 text-center">

                <div
                    class="
                        mx-auto
                        mb-4
                        flex
                        h-14
                        w-14
                        items-center
                        justify-center
                        rounded-2xl
                        bg-accent-100
                        text-accent-700
                    "
                >

                    <svg
                        width="23"
                        height="23"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >

                        <path
                            d="M20 11a8 8 0 1 1-2.34-5.66"
                        />

                        <path
                            d="M20 4v7h-7"
                        />

                        <path
                            d="m9 12 2 2 4-4"
                        />

                    </svg>

                </div>


                <div
                    class="
                        mb-2
                        text-[10px]
                        font-black
                        tracking-wider
                        text-accent-600
                    "
                >
                    VERIFY PHONE
                </div>


                <h1
                    class="
                        text-2xl
                        font-black
                        text-content
                    "
                >
                    شماره موبایلت را تأیید کن
                </h1>


                <p
                    class="
                        mt-2
                        text-xs
                        leading-6
                        text-content-muted
                    "
                >

                    کد ۶ رقمی برای شماره

                    <span
                        class="
                            font-bold
                            text-content
                        "
                        dir="ltr"
                    >
                        {{ $phone }}
                    </span>

                    ارسال شد.

                </p>

            </div>


            {{-- =========================================================
                STATUS
            ========================================================== --}}

            @if(session('status'))

                <div
                    class="
                        mb-5
                        rounded-2xl
                        border
                        border-emerald-100
                        bg-emerald-50
                        p-4
                    "
                    role="status"
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                            text-[10px]
                            font-bold
                            leading-6
                            text-emerald-700
                        "
                    >

                        <span
                            class="
                                flex
                                h-6
                                w-6
                                shrink-0
                                items-center
                                justify-center
                                rounded-lg
                                bg-emerald-100
                                text-emerald-700
                            "
                        >
                            ✓
                        </span>

                        {{ session('status') }}

                    </div>

                </div>

            @endif


            {{-- =========================================================
                ERRORS
            ========================================================== --}}

            @if($errors->has('code'))

                <div
                    class="
                        mb-5
                        rounded-2xl
                        border
                        border-red-100
                        bg-red-50
                        p-4
                    "
                    role="alert"
                >

                    @foreach(
                        $errors->get('code')
                        as $error
                    )

                        <div
                            class="
                                text-[10px]
                                font-bold
                                leading-6
                                text-red-700
                            "
                        >
                            {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            {{-- =========================================================
                PHONE / SESSION ERROR
            ========================================================== --}}

            @if($errors->has('phone'))

                <div
                    class="
                        mb-5
                        rounded-2xl
                        border
                        border-amber-200
                        bg-amber-50
                        p-4
                    "
                    role="alert"
                >

                    <div
                        class="
                            text-[10px]
                            font-bold
                            leading-6
                            text-amber-800
                        "
                    >

                        @foreach(
                            $errors->get('phone')
                            as $error
                        )

                            <div>
                                {{ $error }}
                            </div>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- =========================================================
                VERIFY FORM
            ========================================================== --}}

            <form
                action="{{ route(
                    'register.verify.store'
                ) }}"
                method="POST"
                x-ref="form"
                @submit="
                    sanitize();

                    if (code.length !== 6) {
                        $event.preventDefault();
                        return;
                    }

                    submitting = true;
                "
                class="space-y-5"
            >

                @csrf


                {{-- =====================================================
                    OTP
                ====================================================== --}}

                <div class="form-group">

                    <label
                        for="code"
                        class="
                            form-label
                            block
                            text-center
                        "
                    >
                        کد یکبار مصرف
                    </label>


                    <div class="relative">

                        <input
                            id="code"
                            type="tel"
                            name="code"
                            x-ref="code"
                            x-model="code"
                            @input="sanitize()"
                            @keyup="autoSubmit()"
                            @paste="
                                setTimeout(
                                    () => {
                                        sanitize();
                                        autoSubmit();
                                    },
                                    0
                                )
                            "
                            class="
                                h-16
                                w-full
                                rounded-2xl
                                border
                                border-border
                                bg-primary-50
                                px-4
                                text-center
                                text-2xl
                                font-black
                                tracking-[0.65em]
                                text-content
                                outline-none
                                transition

                                focus:border-accent-500
                                focus:bg-white
                                focus:ring-4
                                focus:ring-accent-500/10
                            "
                            :class="
                                submitting
                                    ? 'opacity-70'
                                    : ''
                            "
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            pattern="[0-9۰-۹]{6}"
                            dir="ltr"
                            autofocus
                            required
                        >

                    </div>


                    <div
                        class="
                            mt-2
                            text-center
                            text-[10px]
                            text-content-faint
                        "
                    >
                        کد ۶ رقمی را وارد کن؛ بعدش خودش ادامه می‌دهد.
                    </div>

                </div>


                {{-- =====================================================
                    SUBMIT
                ====================================================== --}}

                <button
                    type="submit"
                    class="
                        btn
                        btn-accent
                        btn-lg
                        w-full
                    "
                    :disabled="
                        code.length !== 6 ||
                        submitting
                    "
                >

                    <span
                        x-show="!submitting"
                    >
                        تکمیل ثبت‌نام
                        →
                    </span>


                    <span
                        x-show="submitting"
                        x-cloak
                    >
                        در حال بررسی...
                    </span>

                </button>

            </form>


            {{-- =========================================================
                RESEND
            ========================================================== --}}

            <div
                class="
                    mt-5
                    border-t
                    border-border
                    pt-5
                    text-center
                "
            >

                <p
                    class="
                        mb-2
                        text-[10px]
                        text-content-faint
                    "
                >
                    کد به دستت نرسید؟
                </p>


                <form
                    action="{{ route(
                        'register.verify.resend'
                    ) }}"
                    method="POST"
                >

                    @csrf


                    <button
                        type="submit"
                        class="
                            text-xs
                            font-bold
                            text-accent-600
                            transition
                            hover:text-accent-700
                            disabled:cursor-not-allowed
                            disabled:opacity-40
                        "
                        :disabled="
                            resendCooldown > 0
                        "
                    >

                        <span
                            x-show="resendCooldown === 0"
                        >
                            ارسال مجدد کد
                        </span>


                        <span
                            x-show="resendCooldown > 0"
                            x-cloak
                        >

                            ارسال دوباره تا

                            <strong
                                dir="ltr"
                                x-text="
                                    resendCooldown
                                "
                            >
                            </strong>

                            ثانیه

                        </span>

                    </button>

                </form>

            </div>

        </div>


        {{-- =============================================================
            FOOTER
        =============================================================== --}}

        <div
            class="
                border-t
                border-border
                bg-primary-50
                px-6
                py-4
                text-center
            "
        >

            <span
                class="
                    text-xs
                    text-content-muted
                "
            >
                شماره اشتباهه؟
            </span>


            <a
                href="{{ route('register') }}"
                class="
                    mr-1
                    text-xs
                    font-black
                    text-accent-600
                "
            >
                برگرد و اصلاحش کن
            </a>

        </div>

    </div>

@endsection
