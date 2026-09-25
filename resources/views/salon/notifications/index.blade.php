@extends('layouts.salon')

@section('title', 'اعلان‌ها')

@section('content')
@php
    $fa = fn ($value) => strtr((string) $value, [
        '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴',
        '5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
    ]);
@endphp

<div class="salon-page-wrap">
    <section class="salon-page-header">
        <div>
            <span class="salon-overline">صندوق ورودی</span>
            <h1>اعلان‌ها</h1>
            <p>اینجا فقط پیام‌هایی را می‌بینید که لازم است واقعاً درباره‌شان کاری انجام دهید.</p>
        </div>

        @if(($unreadNotifications ?? 0) > 0)
            <form action="{{ route('salon.notifications.read-all') }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="salon-btn salon-btn--quiet">خواندن همه · {{ $fa($unreadNotifications) }}</button>
            </form>
        @endif
    </section>

    @if($notifications->isNotEmpty())
        <section class="salon-card salon-card--flush">
            @foreach($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? 'default';
                    $title = $notification->data['title'] ?? 'اعلان جدید';
                    $message = $notification->data['message'] ?? '';
                    $bookingId = $notification->data['booking_id'] ?? null;
                    $status = $notification->data['status'] ?? null;
                    $isUnread = is_null($notification->read_at);
                    $statusLabel = match ($status) {
                        'pending' => 'در انتظار',
                        'confirmed' => 'تأیید شده',
                        'cancelled' => 'لغو شده',
                        'completed' => 'انجام شده',
                        default => null,
                    };
                @endphp

                <article class="salon-notification {{ $isUnread ? 'is-unread' : '' }}">
                    <div class="salon-notification__icon">{{ $type === 'booking_created' ? '◷' : '✓' }}</div>

                    <div class="salon-notification__body">
                        <div class="salon-notification__top">
                            <strong>{{ $title }}</strong>
                            <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                        </div>

                        @if($message)
                            <p>{{ $message }}</p>
                        @endif

                        <div class="salon-notification__meta">
                            @if($bookingId)
                                <span>نوبت #{{ $fa($bookingId) }}</span>
                            @endif
                            @if($statusLabel)
                                <span>{{ $statusLabel }}</span>
                            @endif
                            @if($isUnread)
                                <span class="is-new">جدید</span>
                            @endif
                        </div>
                    </div>

                    <div class="salon-notification__actions">
                        @if($bookingId)
                            <a href="{{ route('salon.bookings.show', $bookingId) }}" class="salon-mini-action salon-mini-action--link">مشاهده نوبت</a>
                        @endif
                        @if($isUnread)
                            <form action="{{ route('salon.notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="salon-mini-action">خوانده شد</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        @if($notifications->hasPages())
            <div class="salon-pagination">{{ $notifications->links() }}</div>
        @endif
    @else
        <section class="salon-empty-state">
            <div class="salon-empty-state__icon">◌</div>
            <h2>فعلاً اعلانی نیست.</h2>
            <p>وقتی نوبت جدیدی ثبت شود یا وضعیت نوبتی تغییر کند، پیامش همین‌جا نمایش داده می‌شود.</p>
        </section>
    @endif
</div>
@endsection
