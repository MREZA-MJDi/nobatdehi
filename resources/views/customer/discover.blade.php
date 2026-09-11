@extends('layouts.discovery')

@section('title', 'NOBAT — کشف و رزرو نوبت')

@section(
    'description',
    'سالن، خدمات و استایلیست موردنظرت را پیدا کن و آنلاین نوبت بگیر.'
)

@section('content')

    <div class="discover-page" dir="rtl">

        @include('customer.discover.partials.header')

        <main>

            @include('customer.discover.sections.hero')

            @include('customer.discover.sections.services')

            @include('customer.discover.sections.salons')

            @include('customer.discover.sections.stylists')

            @include('customer.discover.sections.featured')

            @include('customer.discover.sections.final-cta')

        </main>

    </div>

@endsection
