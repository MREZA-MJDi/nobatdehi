@extends('layouts.auth')

@section('title', 'ثبت‌نام')

@section('content')

    <div class="auth-card">

        <div class="auth-card-body">

        <span class="section-kicker">
            CREATE ACCOUNT
        </span>

            <h1>
                حساب خودت را بساز
            </h1>

            <p class="auth-lead">
                با شماره موبایل ثبت‌نام کن تا نوبت‌هایت همیشه قابل پیگیری باشد.
            </p>


            @if($errors->any())

                <div class="auth-errors">

                    @foreach($errors->all() as $error)

                        <div>
                            {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            <form
                action="{{ route('register.store') }}"
                method="POST"
                class="auth-form"
            >

                @csrf


                <div class="form-field">

                    <label for="name">
                        نام و نام خانوادگی
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        class="customer-input"
                        autocomplete="name"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="phone">
                        شماره موبایل
                    </label>

                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        class="customer-input"
                        dir="ltr"
                        inputmode="tel"
                        autocomplete="tel"
                        placeholder="09121234567"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="password">
                        رمز عبور
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="customer-input"
                        dir="ltr"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="password_confirmation">
                        تکرار رمز عبور
                    </label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="customer-input"
                        dir="ltr"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <label class="auth-check">

                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        {{ old('terms') ? 'checked' : '' }}
                        required
                    >

                    <span>
                    قوانین و شرایط استفاده را می‌پذیرم.
                </span>

                </label>


                <button
                    type="submit"
                    class="customer-btn customer-btn-primary customer-btn-lg"
                >
                    دریافت کد و ثبت‌نام
                    →
                </button>

            </form>

        </div>


        <div class="auth-card-footer">

            قبلاً حساب ساختی؟

            <a href="{{ route('login') }}">
                وارد شو
            </a>

        </div>

    </div>

@endsection
