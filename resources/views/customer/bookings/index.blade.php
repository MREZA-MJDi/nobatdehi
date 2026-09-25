@extends('layouts.customer')

@section('title', 'نوبت‌های من')

@section('content')
    @php
        $filters = [
            'all' => 'همه',
            'pending' => 'در انتظار',
            'confirmed' => 'تأیید شده',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
        ];
    @endphp

    <div class="customer-container py-6 pb-28 sm:py-10">
        <div class="mx-auto w-full max-w-6xl">
            <div class="customer-page-heading">
                <div>
                    <span class="customer-eyebrow">MY BOOKINGS</span>
                    <h1 class="customer-page-title">نوبت‌های من</h1>
                    <p class="customer-page-lead">هر نوبت، با وضعیت و اقدام‌های مخصوص خودش.</p>
                </div>

                <a href="{{ route('salons.discover') }}" class="customer-btn customer-btn-primary">
                    رزرو جدید <span aria-hidden="true">←</span>
                </a>
            </div>

            <nav class="customer-filter-tabs" aria-label="فیلتر نوبت‌ها">
                @foreach($filters as $key => $label)
                    <a
                        href="{{ $key === 'all' ? route('customer.bookings.index') : route('customer.bookings.index', ['status' => $key]) }}"
                        @class(['is-active' => $status === $key])
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @if($bookings->isNotEmpty())
                <div class="mt-5 space-y-3">
                    @foreach($bookings as $booking)
                        @include('customer.bookings._card', ['booking' => $booking])
                    @endforeach
                </div>

                @if($bookings->hasPages())
                    <div class="mt-7">{{ $bookings->links() }}</div>
                @endif
            @else
                <section class="customer-empty-panel mt-5">
                    <div class="customer-empty-icon">◷</div>
                    <h2>در این بخش نوبتی نیست</h2>
                    <p>{{ $status === 'all' ? 'هنوز رزروی ثبت نکرده‌ای.' : 'برای این وضعیت چیزی پیدا نکردیم.' }}</p>
                    <a href="{{ route('salons.discover') }}" class="customer-btn customer-btn-primary">
                        {{ $status === 'all' ? 'اولین رزرو را ثبت کن' : 'کشف سالن‌ها' }}
                    </a>
                </section>
            @endif
        </div>
    </div>
@endsection
