@extends('layouts.customer')

@section('title', 'پروفایل من')

@section('content')

    <div class="customer-container account-page">

        <div class="account-header">

            <div>

            <span class="section-kicker">
                PROFILE
            </span>

                <h1>
                    پروفایل من
                </h1>

                <p>
                    اطلاعات حساب مشتری را مدیریت کن.
                </p>

            </div>

        </div>


        <div class="profile-layout">

            <aside class="profile-card">

                <div class="profile-avatar">
                    {{
                        mb_substr(
                            auth()->user()->name ?: 'ک',
                            0,
                            1
                        )
                    }}
                </div>

                <strong>
                    {{ auth()->user()->name ?: 'مشتری RM' }}
                </strong>

                <span>
                {{ auth()->user()->phone }}
            </span>

            </aside>


            <section class="profile-form-card">

                <form
                    action="{{ route(
                    'customer.profile.update'
                ) }}"
                    method="POST"
                    class="profile-form"
                >

                    @csrf
                    @method('PATCH')


                    <div class="form-field">

                        <label for="name">
                            نام و نام خانوادگی
                        </label>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old(
                            'name',
                            auth()->user()->name
                        ) }}"
                            class="customer-input"
                        >

                        @error('name')
                        <small class="field-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    <div class="form-field">

                        <label for="email">
                            ایمیل
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old(
                            'email',
                            auth()->user()->email
                        ) }}"
                            class="customer-input"
                            dir="ltr"
                        >

                        @error('email')
                        <small class="field-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    <div class="profile-phone">

                    <span>
                        شماره موبایل
                    </span>

                        <strong dir="ltr">
                            {{ auth()->user()->phone }}
                        </strong>

                        <small>
                            شماره موبایل فعلاً به‌عنوان شناسه ورود استفاده می‌شود.
                        </small>

                    </div>


                    <button
                        type="submit"
                        class="customer-btn customer-btn-primary customer-btn-lg"
                    >
                        ذخیره تغییرات
                    </button>

                </form>

            </section>

        </div>

    </div>

@endsection
