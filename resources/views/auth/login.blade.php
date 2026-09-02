@extends('layouts.auth')

@section('title', 'ورود')

@section('content')

    <div class="card overflow-hidden">

        <div class="p-6 sm:p-7">

            <div class="mb-7">

                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    WELCOME BACK
                </div>

                <h1 class="text-2xl font-black text-content">
                    خوش برگشتی 👋
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    برای ورود شماره موبایل و رمز عبورت را وارد کن.
                </p>

            </div>


            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                    <div class="text-xs font-black text-red-800">
                        ورود انجام نشد
                    </div>

                    <div class="mt-1 space-y-1 text-[10px] leading-6 text-red-700">

                        @foreach($errors->all() as $error)

                            <div>
                                • {{ $error }}
                            </div>

                        @endforeach

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

                <div class="form-group">

                    <label
                        for="phone"
                        class="form-label"
                    >
                        شماره موبایل
                    </label>

                    <div class="relative">

                        <span class="pointer-events-none absolute right-3 top-1/2 flex -translate-y-1/2 items-center text-content-muted">

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

                        </span>


                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-control pr-11 text-left"
                            placeholder="0912 123 4567"
                            inputmode="tel"
                            autocomplete="tel"
                            dir="ltr"
                            maxlength="14"
                            autofocus
                            required
                        >

                    </div>


                    @error('phone')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Password --}}

                <div class="form-group">

                    <label
                        for="password"
                        class="form-label"
                    >
                        رمز عبور
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control text-left"
                        placeholder="حداقل ۸ کاراکتر"
                        autocomplete="current-password"
                        dir="ltr"
                        required
                    >

                    @error('password')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Remember --}}

                <label class="flex cursor-pointer items-center gap-2">

                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        class="h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                        @checked(old('remember'))
                    >

                    <span class="text-xs text-content-soft">
                        مرا به خاطر بسپار
                    </span>

                </label>


                <button
                    type="submit"
                    class="btn btn-accent btn-lg w-full"
                >
                    ورود
                    →
                </button>

            </form>

        </div>


        <div class="border-t border-border bg-primary-50 px-6 py-4 text-center">

            <span class="text-xs text-content-muted">
                هنوز حساب نداری؟
            </span>

            <a
                href="{{ route('register') }}"
                class="mr-1 text-xs font-black text-accent-600"
            >
                ثبت‌نام کن
            </a>

        </div>

    </div>

@endsection
