@extends('layouts.auth')

@section('title', 'ثبت‌نام')

@section('content')

    <div class="card overflow-hidden">

        <div class="p-6 sm:p-7">

            <div class="mb-7">

                <div class="mb-2 text-[10px] font-black tracking-wider text-accent-600">
                    CREATE ACCOUNT
                </div>

                <h1 class="text-2xl font-black text-content">
                    حساب خودت را بساز
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    با شماره موبایل ثبت‌نام کن؛ همین شماره برای نوبت‌ها استفاده می‌شود.
                </p>

            </div>


            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4">

                    <div class="text-xs font-black text-red-800">
                        فرم را بررسی کن
                    </div>

                    <div class="mt-1 text-[10px] leading-6 text-red-700">

                        @foreach($errors->all() as $error)
                            <div>• {{ $error }}</div>
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


                <div class="form-group">

                    <label
                        for="name"
                        class="form-label"
                    >
                        نام و نام خانوادگی
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control"
                        autocomplete="name"
                        autofocus
                        required
                    >

                    @error('name')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


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
                        required
                    >

                    <div class="form-help">
                        برای ورود و دریافت پیام‌های نوبت از این شماره استفاده می‌کنیم.
                    </div>

                    @error('phone')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                <label class="flex cursor-pointer items-start gap-2">

                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        class="mt-1 h-4 w-4 rounded border-border text-accent-600 focus:ring-accent-500"
                        required
                    >

                    <span class="text-[10px] leading-6 text-content-muted">
                    قوانین و شرایط استفاده از نوبت‌دهی را می‌پذیرم.
                </span>

                </label>


                @error('terms')

                <div class="form-error">
                    {{ $message }}
                </div>

                @enderror


                <button
                    type="submit"
                    class="btn btn-accent btn-lg w-full"
                >
                    دریافت کد ثبت‌نام
                    →
                </button>

            </form>

        </div>


        <div class="border-t border-border bg-primary-50 px-6 py-4 text-center">

        <span class="text-xs text-content-muted">
            قبلاً حساب ساختی؟
        </span>

            <a
                href="{{ route('login') }}"
                class="mr-1 text-xs font-black text-accent-600"
            >
                وارد شو
            </a>

        </div>

    </div>

@endsection
