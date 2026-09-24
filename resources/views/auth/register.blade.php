@extends('layouts.customer')

@section('customer_shell', 'auth')

@section('title', 'ثبت‌نام')

@section('content')

    <div>

        {{-- Header --}}
        <div class="px-6 pb-2 pt-7 sm:px-8 sm:pt-8">

            <div class="mb-6 flex items-center justify-between">

                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                    <svg
                        width="21"
                        height="21"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M19 8v6" />
                        <path d="M22 11h-6" />
                    </svg>
                </div>

                <div class="rounded-full bg-primary-50 px-3 py-1.5 text-[9px] font-black text-content-muted">
                    ثبت‌نام امن
                </div>

            </div>

            <div>

                <div class="mb-2 text-[10px] font-black tracking-[0.18em] text-accent-600">
                    CREATE ACCOUNT
                </div>

                <h1 class="text-2xl font-black tracking-tight text-content sm:text-3xl">
                    حساب خودت را بساز
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    با شماره موبایل ثبت‌نام کن تا نوبت‌هایت همیشه قابل پیگیری باشد.
                </p>

            </div>

        </div>


        {{-- Form --}}
        <div class="px-6 pb-6 pt-5 sm:px-8 sm:pb-8">

            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-danger-100 bg-danger-50 p-4">

                    <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-white text-danger-600 shadow-sm">
                        !
                    </span>

                        <span class="text-xs font-black text-danger-700">
                        ثبت‌نام انجام نشد
                    </span>

                    </div>

                    <div class="mt-2 space-y-1 text-[10px] leading-6 text-danger-700">

                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach

                    </div>

                </div>

            @endif


            <form
                action="{{ route('register.store') }}"
                method="POST"
                class="space-y-5"
            >

                @csrf


                {{-- Name --}}
                <div>

                    <label
                        for="name"
                        class="mb-2 block text-xs font-black text-content"
                    >
                        نام و نام خانوادگی
                    </label>

                    <div class="relative">

                        <div
                            class="pointer-events-none absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-primary-50 text-content-muted"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle
                                    cx="12"
                                    cy="8"
                                    r="4"
                                />

                                <path d="M4 21a8 8 0 0 1 16 0" />
                            </svg>
                        </div>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            placeholder="مثلاً محمد رضایی"
                            autocomplete="name"
                            autofocus
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-right text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-white focus:ring-4 focus:ring-accent-500/10"
                        >

                    </div>

                    @error('name')

                    <div class="mt-2 text-[10px] font-bold text-danger-600">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


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
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
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
                            name="phone"
                            type="tel"
                            value="{{ old('phone') }}"
                            placeholder="0912 123 4567"
                            dir="ltr"
                            inputmode="tel"
                            autocomplete="tel"
                            maxlength="14"
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-left text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-white focus:ring-4 focus:ring-accent-500/10"
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

                    <label
                        for="password"
                        class="mb-2 block text-xs font-black text-content"
                    >
                        رمز عبور
                    </label>

                    <div class="relative">

                        <div
                            class="pointer-events-none absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-primary-50 text-content-muted"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
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
                            name="password"
                            type="password"
                            placeholder="حداقل ۸ کاراکتر"
                            dir="ltr"
                            autocomplete="new-password"
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-left text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-white focus:ring-4 focus:ring-accent-500/10"
                        >

                    </div>

                    @error('password')

                    <div class="mt-2 text-[10px] font-bold text-danger-600">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Password Confirmation --}}
                <div>

                    <label
                        for="password_confirmation"
                        class="mb-2 block text-xs font-black text-content"
                    >
                        تکرار رمز عبور
                    </label>

                    <div class="relative">

                        <div
                            class="pointer-events-none absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-primary-50 text-content-muted"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
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
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="رمز عبور را دوباره وارد کن"
                            dir="ltr"
                            autocomplete="new-password"
                            required
                            class="w-full rounded-2xl border border-border bg-surface-soft py-3.5 pl-4 pr-14 text-left text-sm font-bold text-content outline-none transition duration-200 placeholder:text-content-faint focus:border-accent-400 focus:bg-white focus:ring-4 focus:ring-accent-500/10"
                        >

                    </div>

                </div>


                {{-- Terms --}}
                <label class="flex cursor-pointer items-start gap-2.5">

                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        @checked(old('terms'))
                    required
                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-border text-accent-600 focus:ring-accent-500"
                    >

                    <span class="text-xs leading-6 text-content-muted">
                    قوانین و شرایط استفاده را می‌پذیرم.
                </span>

                </label>

                @error('terms')

                <div class="-mt-3 text-[10px] font-bold text-danger-600">
                    {{ $message }}
                </div>

                @enderror


                {{-- Submit --}}
                <button
                    type="submit"
                    class="group flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-primary-950 px-5 text-sm font-black text-white shadow-lg shadow-primary-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-primary-900 hover:shadow-xl active:translate-y-0"
                >

                <span>
                    دریافت کد و ثبت‌نام
                </span>

                    <span class="transition-transform duration-200 group-hover:-translate-x-1">
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

                    اطلاعات شما با امنیت کامل نگهداری می‌شود.

                </div>

            </form>

        </div>


        {{-- Login / Salon entry --}}
        <div class="border-t border-border bg-primary-50/70 px-6 py-5 text-center sm:px-8">

            <div class="text-xs text-content-muted">
                قبلاً حساب ساختی؟

                <a
                    href="{{ route('login') }}"
                    class="mr-1 font-black text-accent-600 transition hover:text-accent-800"
                >
                    وارد شو
                </a>
            </div>

            <a
                href="{{ route('salon.login') }}"
                class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-black text-content-muted transition hover:text-accent-600"
            >
                صاحب سالن هستی؟ ورود به پنل سالن
                <span aria-hidden="true">←</span>
            </a>

        </div>

    </div>


@endsection
