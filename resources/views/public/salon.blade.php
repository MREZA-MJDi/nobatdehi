@extends('layouts.public')

@section('title', $salon->name)

@section(
    'meta_description',
    $salon->description
        ?: 'اطلاعات، خدمات، متخصص‌ها، نمونه‌کارها و رزرو نوبت ' . $salon->name
)

@push('styles')
    @vite('resources/css/salon.css')
@endpush

@section('content')

    @php
        $barbers = $salon->barbers;
        $services = $salon->services;
        $portfolioItems = $salon->portfolioItems;
        $reviews = $salon->reviews;

        $workingHours = $salon->workingHours;

        $hasLocation =
            $salon->latitude !== null &&
            $salon->longitude !== null;

        $fullAddress = collect([
            $salon->province,
            $salon->city,
            $salon->district,
            $salon->address,
        ])
            ->filter()
            ->unique()
            ->join('، ');

        $salonInitial = mb_substr(
            trim($salon->name),
            0,
            1
        );
    @endphp


    <div
        class="salon-page"
        dir="rtl"

        style="
            --salon-primary: {{ $salon->primary_color ?: '#6757E8' }};
            --salon-secondary: {{ $salon->secondary_color ?: '#37B8C8' }};
            "

        x-data="{

            openDirections() {

                const latitude = @js(
                    $hasLocation
                        ? (float) $salon->latitude
                        : null
                );

                const longitude = @js(
                    $hasLocation
                        ? (float) $salon->longitude
                        : null
                );


                if (
                    latitude === null ||
                    longitude === null
                ) {
                    return;
                }


                const destination =
                    `${latitude},${longitude}`;


                const googleUrl =
                    `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`;


                const geoUrl =
                    `geo:${destination}?q=${encodeURIComponent(destination)}`;


                if (
                    /Android|iPhone|iPad|iPod/i.test(
                        navigator.userAgent
                    )
                ) {

                    window.location.href = geoUrl;


                    window.setTimeout(
                        () => {

                            window.location.href =
                                googleUrl;

                        },
                        700
                    );

                    return;
                }


                window.open(
                    googleUrl,
                    '_blank',
                    'noopener,noreferrer'
                );

            }

        }"
    >

        {{-- =========================================================
            HERO
        ========================================================== --}}

        @include(
            'public.salon.sections.hero'
        )


        {{-- =========================================================
            MAIN CONTENT
        ========================================================== --}}

        <main class="salon-main">

            <div class="salon-container">

                <div class="salon-layout">


                    {{-- =================================================
                        MAIN CONTENT
                    ================================================== --}}

                    <div class="salon-content">

                        @include(
                            'public.salon.sections.portfolio'
                        )

                        @include(
                            'public.salon.sections.services'
                        )

                        @include(
                            'public.salon.sections.team'
                        )

                        @include(
                            'public.salon.sections.reviews'
                        )

                        @include(
                            'public.salon.sections.working-hours'
                        )

                        @include(
                            'public.salon.sections.location'
                        )

                    </div>


                    {{-- =================================================
                        BOOKING SIDEBAR
                    ================================================== --}}

                    <aside class="salon-sidebar">

                        @include(
                            'public.salon.sections.booking-card'
                        )

                    </aside>

                </div>

            </div>

        </main>


        {{-- =========================================================
            MOBILE BOOKING
        ========================================================== --}}

        @include(
            'public.salon.partials.mobile-booking'
        )


        {{-- =========================================================
            BOOKING MODAL
        ========================================================== --}}

        @include(
            'public.salon.partials.booking-modal'
        )

    </div>

@endsection
