@extends('layouts.salon')

@section('title', 'تغییر رمز عبور')

@section('content')
<div class="salon-owner__password-page">
    <section class="salon-owner__password-card" aria-labelledby="initial-password-title">
        <div class="salon-owner__password-hero">
            <div class="salon-owner__password-icon" aria-hidden="true">⌁</div>

            <div class="salon-owner__password-copy">
                <span class="salon-owner__eyebrow">امنیت حساب</span>
                <h1 id="initial-password-title">رمز عبور حساب را تنظیم کنید</h1>
                <p>
                    رمز فعلی، رمز اولیه‌ای است که برای این حساب ساخته شده.
                    برای ورودهای بعدی یک رمز شخصی و امن انتخاب کنید.
                </p>
            </div>
        </div>

        <div class="salon-owner__password-note">
            <span aria-hidden="true">✓</span>
            <div>
                <strong>بعد از ذخیره، وارد پنل سالن می‌شوید.</strong>
                <span>رمز جدید باید حداقل ۸ کاراکتر داشته باشد.</span>
            </div>
        </div>

        @if(session('status'))
            <div class="salon-owner__flash is-info" role="status" aria-live="polite">
                <span aria-hidden="true">i</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="salon-owner__flash is-error" role="alert" aria-live="polite">
                <span aria-hidden="true">!</span>
                <div class="salon-owner__flash-copy">
                    <strong>رمز واردشده نیاز به بررسی دارد</strong>
                    <div>{{ $errors->first() }}</div>
                </div>
            </div>
        @endif

        <form
            action="{{ route('salon.password.update') }}"
            method="POST"
            class="salon-owner__password-form"
        >
            @csrf
            @method('PUT')

            <div class="salon-owner__password-fields">
                <label class="salon-owner__password-field">
                    <span>رمز عبور جدید</span>
                    <input
                        type="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        dir="ltr"
                        required
                        autofocus
                    >
                    <small>حداقل ۸ کاراکتر؛ بهتر است ترکیبی از حروف و عدد باشد.</small>
                </label>

                <label class="salon-owner__password-field">
                    <span>تکرار رمز عبور جدید</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        minlength="8"
                        autocomplete="new-password"
                        dir="ltr"
                        required
                    >
                    <small>رمز را دقیقاً یکسان با کادر بالا وارد کنید.</small>
                </label>
            </div>

            <button type="submit" class="salon-owner__password-submit">
                ذخیره رمز و ورود به پنل
                <span aria-hidden="true">←</span>
            </button>
        </form>
    </section>
</div>
@endsection
