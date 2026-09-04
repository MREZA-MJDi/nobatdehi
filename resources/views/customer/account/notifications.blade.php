@extends('layouts.customer')

@section('title', 'اعلان‌ها')

@section('content')

    <div class="customer-container account-page">

        <div class="account-header">

            <div>

            <span class="section-kicker">
                NOTIFICATIONS
            </span>

                <h1>
                    اعلان‌های من
                </h1>

                <p>
                    وضعیت نوبت‌ها و پیام‌های RM را اینجا ببین.
                </p>

            </div>


            <form
                action="{{ route(
                'customer.notifications.read-all'
            ) }}"
                method="POST"
            >

                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="customer-btn customer-btn-secondary"
                >
                    همه را خوانده‌شده کن
                </button>

            </form>

        </div>


        <section class="notification-list">

            @forelse($notifications as $notification)

                @php
                    $data = $notification->data ?? [];
                    $title = $data['title'] ?? 'اعلان جدید';
                    $message = $data['message'] ?? 'یک پیام جدید برای شما وجود دارد.';
                @endphp


                <article
                    class="notification-card
                    {{ is_null($notification->read_at)
                        ? 'is-unread'
                        : ''
                    }}"
                >

                    <div class="notification-icon">
                        {{ is_null($notification->read_at) ? '•' : '✓' }}
                    </div>


                    <div class="notification-content">

                        <strong>
                            {{ $title }}
                        </strong>

                        <p>
                            {{ $message }}
                        </p>

                        <small>
                            {{ $notification->created_at->diffForHumans() }}
                        </small>

                    </div>


                    @if(is_null($notification->read_at))

                        <form
                            action="{{ route(
                            'customer.notifications.read',
                            $notification
                        ) }}"
                            method="POST"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="notification-read"
                            >
                                خواندم
                            </button>

                        </form>

                    @endif

                </article>

            @empty

                <div class="discover-empty">

                    <div class="discover-empty-icon">
                        ✓
                    </div>

                    <h3>
                        اعلان جدیدی نداری
                    </h3>

                    <p>
                        وقتی نوبتت تغییر کند یا پیامی برسد، اینجا می‌بینی.
                    </p>

                </div>

            @endforelse

        </section>


        <div class="discover-pagination">
            {{ $notifications->links() }}
        </div>

    </div>

@endsection
