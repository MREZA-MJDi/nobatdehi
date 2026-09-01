@extends('layouts.auth')

@section('title', 'تأیید ورود')

@section('content')

    <div
        x-data="otpForm()"
        class="card overflow-hidden"
    >

        <div class="p-6 sm:p-7">

            <a
                href="{{ route('login') }}"
                class="mb-6 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← تغییر شماره
            </a>


            <div class="mb-7 text-center">

                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-100 text-accent-700">

                    <svg
                        width="23"
                        height="23"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect
                            x="5"
                            y="3"
                            width="14"
                            height="18"
                            rx="2"
                        />

                        <path d="M9 7h6" />

                        <path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01" />
                    </svg>

                </div>


                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    VERIFY PHONE
                </div>


                <h1 class="text-2xl font-black text-content">
                    کد تأیید را وارد کن
                </h1>


                <p class="mt-2 text-xs leading-6 text-content-muted">
                    کد ۶ رقمی برای شماره
                    <span
                        class="font-bold text-content"
                        dir="ltr"
                    >
                    {{ $phone }}
                </span>
                    ارسال شد.
                </p>

            </div>


            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                    @foreach($errors->all() as $error)

                        <div class="text-[10px] font-bold leading-6 text-red-700">
                            • {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            <form
                action="{{ route('login.verify.store') }}"
                method="POST"
                x-ref="form"
                class="space-y-5"
            >

                @csrf


                <div class="form-group">

                    <label
                        for="code"
                        class="form-label text-center"
                    >
                        کد یکبار مصرف
                    </label>


                    <input
                        id="code"
                        type="tel"
                        name="code"
                        x-ref="code"
                        x-model="code"
                        @input="sanitize()"
                        @keyup="autoSubmit()"
                        class="h-16 w-full rounded-2xl border border-border bg-primary-50 text-center text-2xl font-black tracking-[0.65em] text-content outline-none transition focus:border-accent-500 focus:bg-white focus:ring-4 focus:ring-accent-500/10"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        dir="ltr"
                        autofocus
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-accent btn-lg w-full"
                    :disabled="code.length !== 6"
                >
                    ورود
                    →
                </button>

            </form>


            <form
                action="{{ route('login.resend') }}"
                method="POST"
                class="mt-4 text-center"
            >

                @csrf

                <button
                    type="submit"
                    class="text-xs font-bold text-accent-600 hover:text-accent-700"
                >
                    ارسال مجدد کد
                </button>

            </form>

        </div>

    </div>

@endsection
