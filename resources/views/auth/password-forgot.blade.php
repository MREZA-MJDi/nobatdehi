@extends('layouts.customer')

@section('customer_shell', 'auth')

@section('title', 'فراموشی رمز عبور')

@section('content')

    <div class="card overflow-hidden">

        <div class="p-6 sm:p-7">

            <div class="mb-7">
                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    RESET PASSWORD
                </div>

                <h1 class="text-2xl font-black text-content">
                    رمز عبورت یادت رفته؟
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    شماره موبایلت را وارد کن تا کد تأیید برایت ارسال کنیم.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">
                    <div class="text-xs font-black text-red-800">
                        انجام نشد
                    </div>

                    <div class="mt-1 space-y-1 text-[10px] leading-6 text-red-700">
                        @foreach($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form
                action="{{ route('password.email') }}"
                method="POST"
                class="space-y-5"
            >
                @csrf

                <div class="form-group">

                    <label
                        for="phone"
                        class="form-label"
                    >
                        شماره موبایل
                    </label>

                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        class="form-control text-left"
                        placeholder="0912 123 4567"
                        inputmode="tel"
                        autocomplete="tel"
                        dir="ltr"
                        maxlength="14"
                        autofocus
                        required
                    >

                    @error('phone')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="btn btn-accent btn-lg w-full"
                >
                    ارسال کد تأیید
                    →
                </button>

            </form>

        </div>

        <div class="border-t border-border bg-primary-50 px-6 py-4 text-center">

            <a
                href="{{ route('login') }}"
                class="text-xs font-black text-accent-600"
            >
                ← برگشت به ورود
            </a>

        </div>

    </div>

@endsection
