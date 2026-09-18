@extends('layouts.customer')

@section('customer_shell', 'auth')

@section('title', 'تغییر رمز عبور')

@section('content')

    <div class="card overflow-hidden">

        <div class="p-6 sm:p-7">

            <div class="mb-7">

                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    VERIFY & RESET
                </div>

                <h1 class="text-2xl font-black text-content">
                    رمز عبورت را تغییر بده
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    کد ارسال‌شده به
                    <span class="font-black text-content">
                        {{ $maskedPhone }}
                    </span>
                    را وارد کن.
                </p>

            </div>

            @if(session('status'))
                <div class="mb-5 rounded-2xl border border-green-100 bg-green-50 p-4">
                    <div class="text-xs leading-6 text-green-700">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

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
                action="{{ route('password.update') }}"
                method="POST"
                class="space-y-5"
            >
                @csrf

                <div class="form-group">

                    <label
                        for="code"
                        class="form-label"
                    >
                        کد تأیید
                    </label>

                    <input
                        id="code"
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        class="form-control text-left tracking-[0.35em]"
                        placeholder="۱۲۳۴۵۶"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        dir="ltr"
                        maxlength="6"
                        autofocus
                        required
                    >

                    @error('code')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <div class="form-group">

                    <label
                        for="password"
                        class="form-label"
                    >
                        رمز عبور جدید
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control text-left"
                        placeholder="حداقل ۸ کاراکتر"
                        autocomplete="new-password"
                        dir="ltr"
                        required
                    >

                    @error('password')
                    <div class="form-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <div class="form-group">

                    <label
                        for="password_confirmation"
                        class="form-label"
                    >
                        تکرار رمز عبور
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control text-left"
                        placeholder="رمز عبور را دوباره وارد کن"
                        autocomplete="new-password"
                        dir="ltr"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn btn-accent btn-lg w-full"
                >
                    تغییر رمز عبور
                    →
                </button>

            </form>

            <form
                action="{{ route('password.reset.resend') }}"
                method="POST"
                class="mt-4 text-center"
            >
                @csrf

                <button
                    type="submit"
                    class="text-xs font-black text-accent-600"
                >
                    ارسال مجدد کد
                </button>
            </form>

        </div>

    </div>

@endsection
