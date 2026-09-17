@extends('layouts.salon')

@section('title', 'جزئیات نوبت')

@php
    use App\Enums\BookingStatus;
    use Carbon\Carbon;

    $status = $booking->status instanceof BookingStatus
        ? $booking->status->value
        : (string) $booking->status;

    $statusMeta = [
        'pending' => [
            'label' => 'در انتظار تأیید',
            'description' => 'درخواست هنوز تأیید نهایی نشده است.',
            'tone' => 'amber',
        ],
        'confirmed' => [
            'label' => 'تأیید شده',
            'description' => 'این زمان اکنون توسط سالن رزرو نهایی شده است.',
            'tone' => 'emerald',
        ],
        'completed' => [
            'label' => 'تکمیل شده',
            'description' => 'خدمت انجام شده و وضعیت نهایی است.',
            'tone' => 'slate',
        ],
        'cancelled' => [
            'label' => 'لغو شده',
            'description' => 'این درخواست دیگر در صف فعال نیست.',
            'tone' => 'red',
        ],
    ];

    $meta = $statusMeta[$status] ?? [
        'label' => $status,
        'description' => '',
        'tone' => 'slate',
    ];

    $bookingDate = $booking->booking_date;
    $startTime = substr((string) $booking->start_time, 0, 5);
    $endTime = substr((string) $booking->end_time, 0, 5);
    $earlierRequests = $priority['earlier'] ?? collect();
    $queuePosition = $priority['position'] ?? null;
    $pendingCount = $priority['pending_count'] ?? null;

    try {
        $bookingEndDateTime = Carbon::parse(
            $bookingDate->format('Y-m-d') . ' ' . $endTime,
            config('app.timezone')
        );
    } catch (\Throwable) {
        $bookingEndDateTime = null;
    }

    $canCompleteNow =
        $status === 'confirmed'
        && (
            !$bookingEndDateTime
            || now(config('app.timezone'))->gte($bookingEndDateTime)
        );
@endphp

@section('content')
    <div class="min-h-full bg-slate-50" dir="rtl">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a
                        href="{{ route('salon.bookings.index') }}"
                        class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                    >
                        <span class="text-lg">→</span>
                        بازگشت به نوبت‌ها
                    </a>

                    <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                        BOOKING #{{ $booking->id }}
                    </div>

                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        مدیریت نوبت
                    </h1>

                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        مشتری، خدمت، زمان، اولویت درخواست و وضعیت مالی این نوبت در یک نما.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 shadow-sm">
                    <span class="h-2 w-2 rounded-full {{ $meta['tone'] === 'emerald' ? 'bg-emerald-500' : ($meta['tone'] === 'amber' ? 'bg-amber-500' : ($meta['tone'] === 'red' ? 'bg-red-500' : 'bg-slate-500')) }}"></span>
                    {{ $meta['label'] }}
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-bold leading-7 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm font-bold leading-7 text-red-800">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($status === BookingStatus::PENDING->value && $earlierRequests->isNotEmpty())
                <section class="mb-6 overflow-hidden rounded-3xl border border-amber-200 bg-amber-50 shadow-sm">
                    <div class="border-b border-amber-200 px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-700/60">
                                    PRIORITY QUEUE
                                </div>
                                <h2 class="mt-1 text-lg font-black text-amber-950">
                                    این درخواست نفر اول نیست
                                </h2>
                            </div>

                            <div class="rounded-full bg-white/80 px-3 py-1.5 text-xs font-black text-amber-800">
                                جایگاه {{ $queuePosition }} از {{ $pendingCount }}
                            </div>
                        </div>

                        <p class="mt-3 text-sm leading-7 text-amber-800/80">
                            یک یا چند مشتری زودتر برای این بازه درخواست داده‌اند. تأیید این نوبت بدون تغییر صریح اولویت مجاز نیست.
                        </p>
                    </div>

                    <div class="divide-y divide-amber-200/70">
                        @foreach ($earlierRequests as $index => $earlier)
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-sm font-black text-amber-900">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-amber-950">
                                            {{ $earlier->customer?->name ?? 'مشتری دیگر' }}
                                        </div>
                                        <div class="mt-1 text-xs text-amber-800/65">
                                            {{ substr((string) $earlier->start_time, 0, 5) }} تا {{ substr((string) $earlier->end_time, 0, 5) }}
                                            · ثبت {{ $earlier->created_at?->format('Y/m/d H:i') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs font-bold text-amber-800/70">
                                    درخواست زودتر · #{{ $earlier->id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif ($status === BookingStatus::PENDING->value)
                <section class="mb-6 rounded-3xl border border-slate-200 bg-white px-5 py-5 shadow-sm sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                PRIORITY QUEUE
                            </div>
                            <h2 class="mt-1 text-lg font-black text-slate-950">
                                این درخواست فعلاً نفر اول است
                            </h2>
                        </div>
                        <div class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                            جایگاه ۱ از {{ $pendingCount }}
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        با تأیید این نوبت، زمان برای سایر درخواست‌های هم‌پوشان بسته می‌شود.
                    </p>
                </section>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="space-y-6">
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">CUSTOMER</div>
                            <h2 class="mt-1 text-lg font-black text-slate-950">اطلاعات مشتری</h2>
                        </div>
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:px-6">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl bg-slate-950 text-xl font-black text-white">
                                {{ mb_substr($booking->customer?->name ?? '؟', 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-lg font-black text-slate-950">
                                    {{ $booking->customer?->name ?? 'مشتری حذف شده' }}
                                </div>
                                @if ($booking->customer?->phone)
                                    <div class="mt-1 text-sm text-slate-400">{{ $booking->customer->phone }}</div>
                                @endif
                            </div>
                            @if ($booking->customer?->phone)
                                <a
                                    href="tel:{{ $booking->customer->phone }}"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                                >
                                    تماس با مشتری
                                </a>
                            @endif
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">SERVICE</div>
                            <h2 class="mt-1 text-lg font-black text-slate-950">خدمت</h2>
                        </div>
                        <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">نام خدمت</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ $booking->service?->name ?? 'حذف شده' }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">متخصص</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ $booking->barber?->name ?? 'حذف شده' }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">مدت</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ (int) ($booking->service?->duration_minutes ?? 0) }} دقیقه</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">قیمت</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ number_format((int) $booking->price) }} تومان</div>
                            </div>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">APPOINTMENT</div>
                            <h2 class="mt-1 text-lg font-black text-slate-950">زمان نوبت</h2>
                        </div>
                        <div class="grid gap-3 p-5 sm:grid-cols-3 sm:p-6">
                            <div class="rounded-2xl bg-slate-950 p-5 text-white">
                                <div class="text-[10px] font-black text-white/40">تاریخ</div>
                                <div class="mt-2 text-base font-black">{{ jalali_date($bookingDate) }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-5">
                                <div class="text-[10px] font-black text-slate-400">شروع</div>
                                <div class="mt-2 text-xl font-black text-slate-950">{{ $startTime }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-5">
                                <div class="text-[10px] font-black text-slate-400">پایان</div>
                                <div class="mt-2 text-xl font-black text-slate-950">{{ $endTime }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">COMMISSION SNAPSHOT</div>
                            <h2 class="mt-1 text-lg font-black text-slate-950">سهم رزرو</h2>
                        </div>
                        <div class="grid gap-3 p-5 sm:grid-cols-3 sm:p-6">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">نرخ</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ number_format((float) $booking->commission_rate, 2) }}٪</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">مبلغ محاسبه‌شده</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ number_format((int) $booking->commission_amount) }} تومان</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-[10px] font-black text-slate-400">گیرنده فعلی</div>
                                <div class="mt-2 text-sm font-black text-slate-950">{{ $booking->commission_recipient === 'salon' ? 'سالن' : $booking->commission_recipient }}</div>
                            </div>
                        </div>
                        <div class="px-5 pb-5 text-xs leading-6 text-slate-400 sm:px-6 sm:pb-6">
                            این اعداد در لحظه ایجاد نوبت snapshot شده‌اند تا تغییرات آینده روی سوابق قبلی اثر نگذارد.
                        </div>
                    </section>

                    @if ($booking->notes || $booking->status_note)
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">NOTES</div>
                                <h2 class="mt-1 text-lg font-black text-slate-950">یادداشت‌ها</h2>
                            </div>
                            <div class="space-y-3 p-5 sm:p-6">
                                @if ($booking->notes)
                                    <div class="rounded-2xl bg-slate-50 p-4 text-sm leading-8 text-slate-700">
                                        {{ $booking->notes }}
                                    </div>
                                @endif
                                @if ($booking->status_note)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm leading-8 text-slate-600">
                                        <div class="mb-1 text-[10px] font-black text-slate-400">SYSTEM NOTE</div>
                                        {{ $booking->status_note }}
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-6 lg:h-fit">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                        <div class="bg-slate-950 px-5 py-6 text-white">
                            <div class="text-[10px] font-black uppercase tracking-[0.22em] text-white/40">BOOKING ACTIONS</div>
                            <div class="mt-1 text-xl font-black">عملیات</div>
                        </div>

                        <div class="space-y-3 p-5">
                            @if ($status === BookingStatus::PENDING->value)
                                @if ($earlierRequests->isEmpty())
                                    <form action="{{ route('salon.bookings.status', $booking) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ BookingStatus::CONFIRMED->value }}">
                                        <button
                                            type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700"
                                        >
                                            <span>✓</span>
                                            تأیید نوبت
                                        </button>
                                    </form>
                                @else
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                        <div class="text-xs font-black text-amber-950">قبل از تأیید، اولویت را بررسی کنید.</div>
                                        <div class="mt-1 text-[11px] leading-6 text-amber-800/75">
                                            {{ $earlierRequests->count() }} درخواست زودتر وجود دارد. تغییر اولویت باید آگاهانه ثبت شود.
                                        </div>
                                    </div>

                                    <form
                                        action="{{ route('salon.bookings.status', $booking) }}"
                                        method="POST"
                                        onsubmit="return confirm('این نوبت اولویت اول نیست. آیا مطمئن هستید که می‌خواهید با تغییر اولویت آن را تأیید کنید؟');"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ BookingStatus::CONFIRMED->value }}">
                                        <input type="hidden" name="override_priority" value="1">

                                        <label class="block">
                                            <span class="mb-2 block text-xs font-black text-slate-600">دلیل تغییر اولویت</span>
                                            <textarea
                                                name="priority_override_reason"
                                                rows="3"
                                                required
                                                maxlength="1000"
                                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-800 outline-none transition focus:border-slate-400 focus:bg-white"
                                                placeholder="مثلاً هماهنگی تلفنی با مشتری یا تصمیم مدیر سالن"
                                            ></textarea>
                                        </label>

                                        <button
                                            type="submit"
                                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white transition hover:bg-slate-800"
                                        >
                                            تأیید با تغییر اولویت
                                        </button>
                                    </form>
                                @endif

                                <form
                                    action="{{ route('salon.bookings.status', $booking) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از لغو این نوبت مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ BookingStatus::CANCELLED->value }}">
                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-100"
                                    >
                                        <span>×</span>
                                        لغو نوبت
                                    </button>
                                </form>
                            @elseif ($status === BookingStatus::CONFIRMED->value)
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                                    <div class="text-xs font-black text-emerald-900">نوبت تأیید شده است.</div>
                                    @if ($booking->confirmed_at)
                                        <div class="mt-1 text-[11px] leading-6 text-emerald-800/70">
                                            تأیید در {{ $booking->confirmed_at->format('Y/m/d H:i') }}
                                            @if ($booking->confirmer)
                                                · توسط {{ $booking->confirmer->name }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if ($booking->priority_overridden)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="text-xs font-black text-slate-700">اولویت تغییر داده شده</div>
                                        @if ($booking->priority_override_reason)
                                            <div class="mt-1 text-[11px] leading-6 text-slate-500">{{ $booking->priority_override_reason }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if ($canCompleteNow)
                                    <form action="{{ route('salon.bookings.status', $booking) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ BookingStatus::COMPLETED->value }}">
                                        <button
                                            type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white transition hover:bg-slate-800"
                                        >
                                            <span>✓</span>
                                            تکمیل نوبت
                                        </button>
                                    </form>
                                @else
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                                        <div class="text-xs font-black text-slate-600">هنوز زمان تکمیل نوبت نرسیده</div>
                                        <div class="mt-1 text-[11px] leading-6 text-slate-400">بعد از پایان زمان خدمت، گزینه تکمیل فعال می‌شود.</div>
                                    </div>
                                @endif

                                <form
                                    action="{{ route('salon.bookings.status', $booking) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از لغو این نوبت مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ BookingStatus::CANCELLED->value }}">
                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-100"
                                    >
                                        <span>×</span>
                                        لغو نوبت
                                    </button>
                                </form>
                            @else
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg text-white">
                                        {{ $status === BookingStatus::COMPLETED->value ? '✓' : '×' }}
                                    </div>
                                    <div class="mt-3 text-sm font-black text-slate-900">{{ $meta['label'] }}</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-400">{{ $meta['description'] }}</div>
                                </div>
                            @endif

                            <a
                                href="{{ route('salon.bookings.index') }}"
                                class="flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                بازگشت به لیست
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
