@extends('layouts.customer')

@section('customer_shell', 'auth')

@section('title', 'ورود')

@section('content')

    <div>

        {{-- Header --}}
        <div class="px-6 pb-2 pt-7 sm:px-8 sm:pt-8">

            <div class="mb-6 flex items-center justify-between">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600"
                    aria-hidden="true"
                >
                    <svg
                        width="21"
                        height="21"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <path d="m10 17 5-5-5-5" />
                        <path d="M15 12H3" />
                    </svg>
                </div>

                <div class="rounded-full bg-primary-50 px-3 py-1.5 text-[9px] font-black text-content-muted">
                    ورود امن
                </div>

            </div>

            <div>

                <div class="mb-2 text-[10px] font-black tracking-[0.18em] text-accent-600">
                    WELCOME BACK
                </div>

                <h1 class="text-2xl font-black tracking-tight text-content sm:text-3xl">
                    خوش برگشتی 👋
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    برای ورود به حساب کاربری، شماره موبایل و رمز عبورت را وارد کن.
                </p>

            </div>

        </div>


        {{-- Form --}}
        <div class="px-6 pb-6 pt-5 sm:px-8 sm:pb-8">

            {{-- Validation errors --}}
            @if ($errors->any())

                <div
                    class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4"
                    role="alert"
                    aria-live="polite"
                >

                    <div class="flex items-center gap-2">

                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-xl bg-white text-danger-600 shadow-sm"
                            aria-hidden="true"
                        >
                            !
                        </span>

                        <span class="text-xs font-black text-danger-700">
                            ورود انجام نشد
                        </span>

                    </div>

                    <div class="mt-2 space-y-1 text-[10px] leading-6 text-danger-700">

                        @foreach ($errors->all() as $error)
                            <div>
                                {{ $error }}
                            </div>
                        @endforeach

                    </div>

                </div>

            @endif


            {{-- Session status --}}
            @if (session('status'))

                <div
                    class="mb-5 rounded-2xl border border-success-100 bg-success-50 p-4"
                    role="status"
                    aria-live="polite"
                >
                    <div class="text-xs font-bold leading-6 text-success-700">
                        {{ session('status') }}
                    </div>
                </div>

            @endif


            <form
                action="{{ route('login.store') }}"
                method="POST"
                class="space-y-5"
            >
                @csrf


                {{-- Phone --}}
                <div>

                    <label
                        for="phone"
                        class="mb-2 block text-xs font-black text-content"
                    >
                        شماره موبایل
                    </label>

                    <div class="relative">

                        <div
                            class="pointer-events-none absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-primary-50 text-content-muted"
                            aria-hidden="true"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="6"
                                    y="3"
                                    width="12"
                                    height="18"
                                    rx="2"
                                />

                                <path d="M10 6h4" />

                                <circle
                                    cx="12"
                                    cy="17"
                                    r="1"
                                />
                            </svg>
                        </div>

                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="0912 123 4567"
                            inputmode="tel"
                            autocomplete="tel"
                            dir="ltr"
                            maxlength="14"
                            autofocus
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-left text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                        >

                    </div>

                    @error('phone')
                    <div class="mt-2 text-[10px] font-bold text-danger-600">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Password --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <label
                            for="password"
                            class="block text-xs font-black text-content"
                        >
                            رمز عبور
                        </label>

                        <a
                            href="{{ route('password.request') }}"
                            class="text-[10px] font-bold text-accent-600 transition hover:text-accent-800"
                        >
                            رمز عبور را فراموش کردی؟
                        </a>

                    </div>

                    <div class="relative">

                        <div
                            class="pointer-events-none absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-primary-50 text-content-muted"
                            aria-hidden="true"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="5"
                                    y="11"
                                    width="14"
                                    height="10"
                                    rx="2"
                                />

                                <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                            </svg>
                        </div>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="رمز عبور خود را وارد کن"
                            autocomplete="current-password"
                            dir="ltr"
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-left text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-surface focus:ring-4 focus:ring-accent-500/10"
                        >

                    </div>

                    @error('password')
                    <div class="mt-2 text-[10px] font-bold text-danger-600">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Remember --}}
                <label class="flex cursor-pointer items-center gap-2.5">

                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                    class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                    >

                    <span class="text-xs font-medium text-content-soft">
                        مرا به خاطر بسپار
                    </span>

                </label>


                {{-- Submit --}}
                <button
                    type="submit"
                    class="group flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-primary-950 px-5 text-sm font-black text-white shadow-lg shadow-primary-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-primary-900 hover:shadow-xl active:translate-y-0"
                >
                    <span>
                        ورود به حساب
                    </span>

                    <span
                        class="transition-transform duration-200 group-hover:-translate-x-1"
                        aria-hidden="true"
                    >
                        ←
                    </span>
                </button>


                {{-- Security note --}}
                <div class="flex items-center justify-center gap-2 pt-1 text-[9px] text-content-faint">

                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <rect
                            x="5"
                            y="11"
                            width="14"
                            height="10"
                            rx="2"
                        />

                        <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                    </svg>

                    <span>
                        اطلاعات شما با امنیت کامل نگهداری می‌شود.
                    </span>

                </div>

            </form>

        </div>


        {{-- Register --}}
        <div class="border-t border-border bg-primary-50/70 px-6 py-5 text-center sm:px-8">

            <span class="text-xs text-content-muted">
                هنوز حساب نداری؟
            </span>

            <a
                href="{{ route('register') }}"
                class="mr-1 text-xs font-black text-accent-600 transition hover:text-accent-800"
            >
                ثبت‌نام کن
            </a>

        </div>

    </div>

@endsection
