@extends('layouts.customer')

@section('title', 'سالن‌های مورد علاقه')

@section('content')
    <div class="customer-container py-6 pb-28 sm:py-10">

        <div class="mx-auto w-full max-w-6xl">

            <div class="customer-page-heading">
                <div>
                    <span class="customer-eyebrow">SAVED PLACES</span>
                    <h1 class="customer-page-title">سالن‌های مورد علاقه</h1>
                    <p class="customer-page-lead">
                        سالن‌هایی که دوست داری سریع‌تر بهشان برگردی، اینجا نگه داشته می‌شوند.
                    </p>
                </div>

                <a
                    href="{{ route('salons.discover') }}"
                    class="customer-btn customer-btn-primary"
                >
                    کشف سالن جدید
                    <span aria-hidden="true">←</span>
                </a>
            </div>

            @if($favorites->isNotEmpty())
                <div class="customer-favorite-grid">
                    @foreach($favorites as $salon)
                        <article class="customer-favorite-card">
                            <div class="customer-favorite-cover">
                                @if($salon->cover_path)
                                    <img
                                        src="{{ asset('storage/'.$salon->cover_path) }}"
                                        alt="{{ $salon->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="customer-favorite-cover-empty">
                                        {{ mb_substr($salon->name ?: 'س', 0, 1) }}
                                    </div>
                                @endif

                                <form
                                    action="{{ route('customer.favorites.destroy', $salon) }}"
                                    method="POST"
                                    class="customer-favorite-remove"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" aria-label="حذف {{ $salon->name }} از علاقه‌مندی‌ها">
                                        ♥
                                    </button>
                                </form>
                            </div>

                            <div class="customer-favorite-body">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="customer-card-kicker">
                                            {{ $salon->city ?: 'سالن زیبایی' }}
                                        </span>

                                        <h2 class="customer-favorite-name">
                                            {{ $salon->name }}
                                        </h2>
                                    </div>

                                    @if($salon->reviews_avg_rating !== null)
                                        <span class="customer-rating-chip">
                                            ★ {{ number_format((float) $salon->reviews_avg_rating, 1) }}
                                        </span>
                                    @endif
                                </div>

                                <p class="customer-favorite-meta">
                                    {{ $salon->services_count }} خدمت فعال
                                    @if($salon->district)
                                        <span>·</span>
                                        {{ $salon->district }}
                                    @endif
                                </p>

                                <div class="mt-5 flex gap-2">
                                    <a
                                        href="{{ route('public.salons.show', $salon) }}"
                                        class="customer-btn customer-btn-secondary flex-1"
                                    >
                                        مشاهده سالن
                                    </a>

                                    <a
                                        href="{{ route('public.salons.booking.create', $salon) }}"
                                        class="customer-btn customer-btn-primary flex-1"
                                    >
                                        رزرو
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($favorites->hasPages())
                    <div class="mt-7">
                        {{ $favorites->links() }}
                    </div>
                @endif
            @else
                <section class="customer-empty-panel">
                    <div class="customer-empty-icon">♥</div>
                    <h2>هنوز سالنی ذخیره نکردی</h2>
                    <p>
                        وقتی یک سالن را دوست داشتی، آن را ذخیره کن تا برای رزرو بعدی دم دستت باشد.
                    </p>
                    <a
                        href="{{ route('salons.discover') }}"
                        class="customer-btn customer-btn-primary"
                    >
                        شروع کشف سالن‌ها
                    </a>
                </section>
            @endif

        </div>
    </div>
@endsection
