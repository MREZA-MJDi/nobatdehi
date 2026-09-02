@extends('layouts.app')

@section('title', $salon->name)

@section('content')

    <div class="container py-4">

        {{-- Header / Branding --}}
        <div class="card mb-4">

            @if($salon->cover_path)
                <div>
                    <img
                        src="{{ Storage::url($salon->cover_path) }}"
                        alt="{{ $salon->name }}"
                        style="
                        width: 100%;
                        height: 260px;
                        object-fit: cover;
                    "
                    >
                </div>
            @endif

            <div class="card-body">

                <div class="d-flex align-items-center gap-3">

                    @if($salon->logo_path)
                        <img
                            src="{{ Storage::url($salon->logo_path) }}"
                            alt="{{ $salon->name }}"
                            width="100"
                            height="100"
                            style="
                            object-fit: cover;
                            border-radius: 16px;
                        "
                        >
                    @endif

                    <div>
                        <h1 class="mb-2">
                            {{ $salon->name }}
                        </h1>

                        @if($salon->description)
                            <p class="text-muted mb-0">
                                {{ $salon->description }}
                            </p>
                        @endif
                    </div>

                </div>

            </div>
        </div>


        {{-- Contact --}}
        <div class="card mb-4">
            <div class="card-body">

                <h2 class="h5 mb-3">
                    اطلاعات سالن
                </h2>

                @if($salon->phone)
                    <p class="mb-2">
                        <strong>تلفن:</strong>
                        {{ $salon->phone }}
                    </p>
                @endif

                @if($salon->email)
                    <p class="mb-2">
                        <strong>ایمیل:</strong>
                        {{ $salon->email }}
                    </p>
                @endif

                @if(
                    $salon->province ||
                    $salon->city ||
                    $salon->district
                )
                    <p class="mb-2">
                        <strong>موقعیت:</strong>

                        {{ $salon->province }}

                        @if($salon->city)
                            - {{ $salon->city }}
                        @endif

                        @if($salon->district)
                            - {{ $salon->district }}
                        @endif
                    </p>
                @endif

                @if($salon->address)
                    <p class="mb-0">
                        <strong>آدرس:</strong>
                        {{ $salon->address }}
                    </p>
                @endif

            </div>
        </div>


        {{-- Barbers --}}
        <div class="card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h2 class="h5 mb-0">
                        نیروهای سالن
                    </h2>

                    <span>
                    {{ $salon->barbers->count() }} نفر
                </span>

                </div>


                @if($salon->barbers->isEmpty())

                    <div class="text-center py-5">
                        <p class="text-muted mb-0">
                            در حال حاضر نیروی فعالی برای این سالن ثبت نشده است.
                        </p>
                    </div>

                @else

                    <div class="row g-4">

                        @foreach($salon->barbers as $barber)

                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="card h-100">

                                    @if($barber->image_path)
                                        <img
                                            src="{{ Storage::url($barber->image_path) }}"
                                            alt="{{ $barber->name }}"
                                            style="
                                            width: 100%;
                                            height: 220px;
                                            object-fit: cover;
                                        "
                                        >
                                    @endif

                                    <div class="card-body">

                                        <h3 class="h6 mb-2">
                                            {{ $barber->name }}
                                        </h3>

                                        @if($barber->specialty)
                                            <p class="text-muted mb-2">
                                                {{ $barber->specialty }}
                                            </p>
                                        @endif

                                        @if($barber->bio)
                                            <p class="small mb-0">
                                                {{ $barber->bio }}
                                            </p>
                                        @endif

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>


        {{-- Booking CTA --}}
        <div class="text-center mt-4">

            <a
                href="#"
                class="btn btn-primary"
            >
                رزرو نوبت
            </a>

        </div>

    </div>

@endsection
