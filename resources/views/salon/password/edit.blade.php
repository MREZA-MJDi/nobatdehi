@extends('layouts.salon')

@section('title', 'تغییر رمز عبور')

@section('content')
<div class="salon-page-wrap salon-password-page">
    <section class="salon-card salon-password-card">
        <div class="salon-password-icon" aria-hidden="true">⌘</div>

        <div class="salon-overline">امنیت حساب</div>

        <h1>رمز عبور اولیه را تغییر بده</h1>

        <p>
            این رمز توسط مدیر NOBAT برای حساب شما ساخته شده است.
            برای ادامه استفاده از پنل، یک رمز شخصی و امن انتخاب کنید.
        </p>

        @if(session('status'))
            <div class="salon-password-status" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="salon-password-error" role="alert">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form
            action="{{ route('salon.password.update') }}"
            method="POST"
            class="salon-password-form"
        >
            @csrf
            @method('PUT')

            <label>
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
            </label>

            <label>
                <span>تکرار رمز عبور جدید</span>
                <input
                    type="password"
                    name="password_confirmation"
                    minlength="8"
                    autocomplete="new-password"
                    dir="ltr"
                    required
                >
            </label>

            <button type="submit" class="salon-btn salon-btn--primary">
                ذخیره و ورود به پنل ←
            </button>
        </form>
    </section>
</div>
@endsection
