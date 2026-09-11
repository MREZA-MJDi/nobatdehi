@extends('layouts.customer')

@section('title', 'اعلان‌ها')

@section(
    'meta_description',
    'اعلان‌ها و آخرین وضعیت نوبت‌های شما در NOBAT.'
)

@section('content')

    <div class="customer-container py-5 pb-28 sm:py-8">

        <div class="mx-auto w-full max-w-3xl">

            {{-- =====================================================
                HEADER
            ====================================================== --}}

            <div
                class="
                    mb-6
                    flex
                    flex-col
                    gap-4
                    sm:flex-row
                    sm:items-end
                    sm:justify-between
                "
            >

                <div>

                    <span
                        class="
                            text-[10px]
                            font-black
                            tracking-[0.18em]
                            text-accent-600
                        "
                    >
                        NOTIFICATIONS
                    </span>

                    <h1
                        class="
                            mt-2
                            text-2xl
                            font-black
                            text-content
                            sm:text-3xl
                        "
                    >
                        اعلان‌ها
                    </h1>

                    <p
                        class="
                            mt-2
                            text-xs
                            leading-7
                            text-content-muted
                        "
                    >
                        تغییرات نوبت و پیام‌های مهمت اینجا نمایش داده می‌شوند.
                    </p>

                </div>


                @php
                    $unreadCount = $notifications
                        ->getCollection()
                        ->whereNull('read_at')
                        ->count();
                @endphp


                @if($unreadCount > 0)

                    <form
                        action="{{ route('customer.notifications.read-all') }}"
                        method="POST"
                    >

                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="
                                inline-flex
                                min-h-10
                                items-center
                                justify-center
                                rounded-xl
                                border
                                border-border
                                bg-surface
                                px-4
                                text-[10px]
                                font-black
                                text-content
                                transition
                                hover:bg-primary-50
                            "
                        >
                            همه را خوانده‌شده کن
                        </button>

                    </form>

                @endif

            </div>


            {{-- =====================================================
                UNREAD SUMMARY
            ====================================================== --}}

            @if($unreadCount > 0)

                <div
                    class="
                        mb-4
                        flex
                        items-center
                        gap-3
                        rounded-2xl
                        border
                        border-accent-100
                        bg-accent-50
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            flex
                            h-8
                            w-8
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-accent-600
                            text-xs
                            font-black
                            text-white
                        "
                    >
                        {{ $unreadCount }}
                    </span>

                    <span
                        class="
                            text-[10px]
                            font-bold
                            text-content
                        "
                    >
                        اعلان خوانده‌نشده داری.
                    </span>

                </div>

            @endif


            {{-- =====================================================
                NOTIFICATION LIST
            ====================================================== --}}

            @if($notifications->isNotEmpty())

                <section
                    class="space-y-3"
                    aria-label="فهرست اعلان‌ها"
                >

                    @foreach($notifications as $notification)

                        @php
                            $data = is_array($notification->data)
                                ? $notification->data
                                : [];

                            $title = $data['title']
                                ?? 'اعلان جدید';

                            $message = $data['message']
                                ?? 'یک پیام جدید برای شما وجود دارد.';

                            $isUnread = is_null(
                                $notification->read_at
                            );
                        @endphp


                        <article
                            class="
                                relative
                                overflow-hidden
                                rounded-3xl
                                border
                                bg-surface
                                p-4
                                shadow-soft
                                transition
                                sm:p-5

                                {{ $isUnread
                                    ? 'border-accent-200'
                                    : 'border-border'
                                }}
                                "
                        >

                            @if($isUnread)

                                <span
                                    class="
                                        absolute
                                        inset-y-0
                                        right-0
                                        w-1
                                        bg-accent-600
                                    "
                                    aria-hidden="true"
                                ></span>

                            @endif


                            <div
                                class="
                                    flex
                                    items-start
                                    gap-3
                                    sm:gap-4
                                "
                            >

                                {{-- Icon --}}
                                <div
                                    class="
                                        flex
                                        h-10
                                        w-10
                                        shrink-0
                                        items-center
                                        justify-center
                                        rounded-2xl
                                        text-sm
                                        font-black

                                        {{ $isUnread
                                            ? 'bg-accent-50 text-accent-700'
                                            : 'bg-primary-50 text-content-muted'
                                        }}
                                        "
                                    aria-hidden="true"
                                >
                                    {{ $isUnread ? '•' : '✓' }}
                                </div>


                                {{-- Content --}}
                                <div class="min-w-0 flex-1">

                                    <div
                                        class="
                                            flex
                                            flex-col
                                            gap-1.5
                                            sm:flex-row
                                            sm:items-start
                                            sm:justify-between
                                        "
                                    >

                                        <h2
                                            class="
                                                text-sm
                                                font-black
                                                leading-6
                                                text-content
                                            "
                                        >
                                            {{ $title }}
                                        </h2>


                                        <time
                                            datetime="{{ $notification->created_at?->toIso8601String() }}"
                                            class="
                                                shrink-0
                                                text-[9px]
                                                text-content-faint
                                            "
                                        >
                                            {{ $notification->created_at?->diffForHumans() }}
                                        </time>

                                    </div>


                                    <p
                                        class="
                                            mt-2
                                            text-xs
                                            leading-7
                                            text-content-muted
                                        "
                                    >
                                        {{ $message }}
                                    </p>


                                    @if($isUnread)

                                        <form
                                            action="{{ route(
                                                'customer.notifications.read',
                                                $notification
                                            ) }}"
                                            method="POST"
                                            class="mt-3"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="
                                                    inline-flex
                                                    min-h-9
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    bg-accent-600
                                                    px-3.5
                                                    text-[10px]
                                                    font-black
                                                    text-white
                                                    transition
                                                    hover:bg-accent-700
                                                "
                                            >
                                                خواندم
                                            </button>

                                        </form>

                                    @else

                                        <div
                                            class="
                                                mt-3
                                                text-[9px]
                                                font-bold
                                                text-content-faint
                                            "
                                        >
                                            خوانده شده ✓
                                        </div>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </section>


                {{-- =================================================
                    PAGINATION
                ================================================== --}}

                @if($notifications->hasPages())

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>

                @endif


            @else

                {{-- =================================================
                    EMPTY STATE
                ================================================== --}}

                <section
                    class="
                        rounded-3xl
                        border
                        border-dashed
                        border-border
                        bg-surface
                        px-5
                        py-14
                        text-center
                        shadow-soft
                    "
                >

                    <div
                        class="
                            mx-auto
                            flex
                            h-14
                            w-14
                            items-center
                            justify-center
                            rounded-2xl
                            bg-primary-50
                            text-lg
                            text-content-muted
                        "
                        aria-hidden="true"
                    >
                        ✓
                    </div>


                    <h2
                        class="
                            mt-4
                            text-base
                            font-black
                            text-content
                        "
                    >
                        فعلاً اعلانی نداری
                    </h2>


                    <p
                        class="
                            mx-auto
                            mt-2
                            max-w-sm
                            text-xs
                            leading-7
                            text-content-muted
                        "
                    >
                        وقتی وضعیت نوبتت تغییر کند یا پیام مهمی داشته باشی، اینجا بهت خبر می‌دهیم.
                    </p>


                    <a
                        href="{{ route('salons.discover') }}"
                        class="
                            mt-5
                            inline-flex
                            min-h-10
                            items-center
                            justify-center
                            gap-2
                            rounded-xl
                            bg-accent-600
                            px-5
                            text-[10px]
                            font-black
                            text-white
                            transition
                            hover:bg-accent-700
                        "
                    >
                        پیدا کردن سالن

                        <span aria-hidden="true">
                            ←
                        </span>
                    </a>

                </section>

            @endif

        </div>

    </div>

@endsection
