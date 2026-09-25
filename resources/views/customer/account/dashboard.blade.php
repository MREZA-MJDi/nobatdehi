@extends('layouts.customer')

@section('title', 'داشبورد من')

@section('meta_description', 'مدیریت نوبت‌ها، سالن‌های مورد علاقه و فعالیت حساب مشتری.')

@section('content')
    <div class="customer-container py-6 pb-28 sm:py-10">
        <div class="mx-auto w-full max-w-6xl">

            <section class="customer-dashboard-hero">
                <div class="customer-dashboard-hero-grid">
                    <div class="customer-dashboard-intro">
                        <span class="customer-eyebrow">YOUR SPACE</span>
                        <h1 class="customer-dashboard-title">
                            سلام، {{ auth()->user()->name ?: 'دوست خوبم' }}
                            <span aria-hidden="true">👋</span>
                        </h1>
                        <p class="customer-dashboard-lead">
                            همه‌چیز درباره نوبت‌ها و سالن‌هایی که دنبال می‌کنی، همین‌جاست.
                        </p>

                        <div class="customer-dashboard-actions">
                            <a href="{{ route('salons.discover') }}" class="customer-btn customer-btn-primary customer-btn-lg">
                                یک نوبت جدید بگیر
                                <span aria-hidden="true">←</span>
                            </a>
                            <a href="{{ route('customer.bookings.index') }}" class="customer-btn customer-btn-secondary customer-btn-lg">
                                همه نوبت‌ها
                            </a>
                        </div>
                    </div>

                    <div class="customer-dashboard-visual" aria-hidden="true">
                        <div class="customer-dashboard-orbit customer-dashboard-orbit-one"></div>
                        <div class="customer-dashboard-orbit customer-dashboard-orbit-two"></div>
                        <div class="customer-dashboard-visual-copy">
                            <span>BOOK SMART</span>
                            <strong>وقتت، انتخابت، کنترلش با تو.</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="customer-stats-grid mt-5">
                <a href="{{ route('customer.bookings.index') }}" class="customer-stat-card">
                    <span>همه نوبت‌ها</span>
                    <strong>{{ number_format($stats['total']) }}</strong>
                    <small>تاریخچه رزروها</small>
                </a>

                <a href="{{ route('customer.bookings.index', ['status' => 'pending']) }}" class="customer-stat-card">
                    <span>در انتظار</span>
                    <strong>{{ number_format($stats['pending']) }}</strong>
                    <small>قابل پیگیری و ویرایش</small>
                </a>

                <a href="{{ route('customer.bookings.index', ['status' => 'completed']) }}" class="customer-stat-card">
                    <span>تکمیل‌شده</span>
                    <strong>{{ number_format($stats['completed']) }}</strong>
                    <small>برای امتیاز و نظر</small>
                </a>

                <a href="{{ route('customer.favorites.index') }}" class="customer-stat-card">
                    <span>علاقه‌مندی‌ها</span>
                    <strong>{{ number_format($stats['favorites']) }}</strong>
                    <small>سالن‌های ذخیره‌شده</small>
                </a>

                @if($stats['unread'] > 0)
                    <a href="{{ route('customer.notifications.index') }}" class="customer-stat-card customer-stat-card-alert">
                        <span>اعلان جدید</span>
                        <strong>{{ number_format($stats['unread']) }}</strong>
                        <small>برای بررسی</small>
                    </a>
                @endif
            </section>

            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="min-w-0">
                    <div class="customer-section-heading">
                        <div>
                            <span>UP NEXT</span>
                            <h2>نوبت بعدی</h2>
                            <p>مهم‌ترین اتفاق بعدی حساب تو.</p>
                        </div>
                        <a href="{{ route('customer.bookings.index') }}">
                            مشاهده همه <span aria-hidden="true">←</span>
                        </a>
                    </div>

                    @if($upcoming)
                        <div class="customer-next-booking">
                            <div class="customer-next-booking-top">
                                <div class="min-w-0">
                                    <span class="customer-card-kicker">
                                        {{ $upcoming->booking_date ? jalali_date($upcoming->booking_date) : '—' }}
                                    </span>
                                    <h3>{{ $upcoming->salon?->name ?? 'سالن' }}</h3>
                                    <p>
                                        {{ $upcoming->service?->name ?? 'خدمت' }}
                                        @if($upcoming->barber?->name)
                                            <span aria-hidden="true">·</span>
                                            {{ $upcoming->barber->name }}
                                        @endif
                                    </p>
                                </div>

                                <span class="booking-status booking-status-{{ $upcoming->status->value }}">
                                    {{ $upcoming->status->label() }}
                                </span>
                            </div>

                            <div class="customer-next-booking-details">
                                <div>
                                    <span>ساعت</span>
                                    <strong dir="ltr">{{ substr((string) $upcoming->start_time, 0, 5) }}</strong>
                                </div>
                                <div>
                                    <span>مبلغ</span>
                                    <strong>
                                        {{ $upcoming->price !== null ? number_format($upcoming->price).' تومان' : '—' }}
                                    </strong>
                                </div>
                                <div>
                                    <span>وضعیت</span>
                                    <strong>{{ $upcoming->status->label() }}</strong>
                                </div>
                            </div>

                            <div class="customer-next-booking-footer">
                                <a href="{{ route('customer.bookings.show', $upcoming) }}" class="customer-btn customer-btn-primary">
                                    مشاهده نوبت
                                </a>

                                @if($upcomingActions['can_edit'])
                                    <a href="{{ route('customer.bookings.edit', $upcoming) }}" class="customer-btn customer-btn-secondary">
                                        ویرایش
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="customer-empty-panel customer-empty-panel-compact">
                            <div class="customer-empty-icon">◷</div>
                            <h3>هنوز نوبت آینده‌ای نداری</h3>
                            <p>یک سالن پیدا کن و وقت بعدی‌ات را همین حالا ثبت کن.</p>
                            <a href="{{ route('salons.discover') }}" class="customer-btn customer-btn-primary">
                                پیدا کردن سالن
                            </a>
                        </div>
                    @endif
                </section>

                <aside class="space-y-5">
                    <section class="customer-quick-panel">
                        <span class="customer-eyebrow">QUICK ACCESS</span>
                        <h2>میانبرهای تو</h2>

                        <div class="customer-quick-list">
                            <a href="{{ route('customer.favorites.index') }}">
                                <span>♥</span>
                                <div>
                                    <strong>سالن‌های مورد علاقه</strong>
                                    <small>{{ $stats['favorites'] }} سالن ذخیره شده</small>
                                </div>
                                <span aria-hidden="true">←</span>
                            </a>

                            <a href="{{ route('customer.notifications.index') }}">
                                <span>◌</span>
                                <div>
                                    <strong>اعلان‌ها</strong>
                                    <small>{{ $stats['unread'] ? $stats['unread'].' اعلان خوانده‌نشده' : 'همه‌چیز به‌روز است' }}</small>
                                </div>
                                <span aria-hidden="true">←</span>
                            </a>

                            <a href="{{ route('customer.profile.edit') }}">
                                <span>◎</span>
                                <div>
                                    <strong>پروفایل</strong>
                                    <small>اطلاعات حساب و شماره تماس</small>
                                </div>
                                <span aria-hidden="true">←</span>
                            </a>
                        </div>
                    </section>

                    @if($favoriteSalons->isNotEmpty())
                        <section class="customer-quick-panel">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <span class="customer-eyebrow">SAVED</span>
                                    <h2>سالن‌های مورد علاقه</h2>
                                </div>
                                <a href="{{ route('customer.favorites.index') }}" class="text-[10px] font-black text-content-muted hover:text-content">
                                    همه
                                </a>
                            </div>

                            <div class="customer-mini-salon-list">
                                @foreach($favoriteSalons as $salon)
                                    <a href="{{ route('public.salons.show', $salon) }}" class="customer-mini-salon">
                                        <span class="customer-mini-salon-mark">
                                            @if($salon->logo_path)
                                                <img src="{{ asset('storage/'.$salon->logo_path) }}" alt="" loading="lazy">
                                            @else
                                                {{ mb_substr($salon->name ?: 'س', 0, 1) }}
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <strong>{{ $salon->name }}</strong>
                                            <small>
                                                @if($salon->reviews_avg_rating !== null)
                                                    ★ {{ number_format((float) $salon->reviews_avg_rating, 1) }} ·
                                                @endif
                                                {{ $salon->services_count }} خدمت
                                            </small>
                                        </span>
                                        <span aria-hidden="true">←</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </aside>
            </div>

            <section class="mt-10">
                <div class="customer-section-heading">
                    <div>
                        <span>RECENT ACTIVITY</span>
                        <h2>آخرین نوبت‌ها</h2>
                        <p>آخرین تغییرات و رزروهای تو.</p>
                    </div>
                </div>

                @if($recentBookings->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($recentBookings as $booking)
                            @include('customer.bookings._card', ['booking' => $booking])
                        @endforeach
                    </div>
                @else
                    <div class="customer-empty-panel">
                        <div class="customer-empty-icon">+</div>
                        <h3>شروع کن</h3>
                        <p>هنوز هیچ نوبتی ثبت نکردی. اولین تجربه‌ات را بساز.</p>
                        <a href="{{ route('salons.discover') }}" class="customer-btn customer-btn-primary">
                            کشف سالن‌ها
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
