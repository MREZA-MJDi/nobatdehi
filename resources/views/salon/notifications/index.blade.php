@extends('layouts.app')

@section('title', 'اعلان‌ها')

@section('content')

    <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mb-3 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
                >
                    ← داشبورد سالن
                </a>

                <div class="text-[10px] font-black tracking-wider text-accent-600">
                    NOTIFICATIONS
                </div>

                <h1 class="mt-2 text-2xl font-black text-content">
                    اعلان‌های سالن
                </h1>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    اتفاق‌های مهم مربوط به نوبت‌های سالن را اینجا ببینید.
                </p>

            </div>


            @if(auth()->user()->unreadNotifications()->count())

                <form
                    action="{{ route('salon.notifications.read-all') }}"
                    method="POST"
                >

                    @csrf

                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn btn-secondary btn-sm"
                    >
                        خواندن همه
                    </button>

                </form>

            @endif

        </div>


        @if(session('success'))

            <div class="mb-5 alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if($notifications->count())

            <div class="space-y-3">

                @foreach($notifications as $notification)

                    <article
                        class="card p-4 sm:p-5
                        {{ $notification->read_at
                            ? 'opacity-70'
                            : 'border-accent-200 bg-accent-50/30' }}"
                    >

                        <div class="flex items-start gap-3">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-accent-600 shadow-soft">

                                @if(
                                    ($notification->data['type'] ?? null)
                                    === 'booking_created'
                                )

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="4"
                                            y="5"
                                            width="16"
                                            height="16"
                                            rx="2"
                                        />

                                        <path d="M8 3v4" />
                                        <path d="M16 3v4" />
                                        <path d="M4 10h16" />
                                    </svg>

                                @else

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                                        <path d="M10 21h4" />
                                    </svg>

                                @endif

                            </div>


                            <div class="min-w-0 flex-1">

                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                    <h2 class="text-xs font-black text-content">
                                        {{ $notification->data['title'] ?? 'اعلان' }}
                                    </h2>

                                    <time class="text-[9px] text-content-faint">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </time>

                                </div>

                                <p class="mt-2 text-xs leading-6 text-content-muted">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>


                                @if(!$notification->read_at)

                                    <form
                                        action="{{ route('salon.notifications.read', $notification->id) }}"
                                        method="POST"
                                        class="mt-3"
                                    >

                                        @csrf

                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="text-[10px] font-bold text-accent-600"
                                        >
                                            علامت‌گذاری به‌عنوان خوانده‌شده
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            @if($notifications->hasPages())

                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>

            @endif

        @else

            <section class="card p-8 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-content-muted">

                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                        <path d="M10 21h4" />
                    </svg>

                </div>

                <h2 class="mt-4 text-base font-black text-content">
                    اعلان جدیدی ندارید
                </h2>

                <p class="mt-2 text-xs leading-6 text-content-muted">
                    اعلان ثبت نوبت جدید و تغییرات مهم اینجا نمایش داده می‌شود.
                </p>

            </section>

        @endif

    </div>

@endsection
