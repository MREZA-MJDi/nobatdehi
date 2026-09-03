@extends('layouts.app')

@section('title', 'نظرات مشتریان')

@section('content')

    <div class="mx-auto w-full max-w-6xl px-4 py-6 pb-28 sm:px-6 lg:px-8">

        <div class="mb-7">

            <a
                href="{{ route('salon.dashboard') }}"
                class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-content-muted hover:text-accent-600"
            >
                ← داشبورد سالن
            </a>

            <div class="text-[10px] font-black tracking-wider text-accent-600">
                REVIEWS
            </div>

            <h1 class="mt-2 text-2xl font-black text-content sm:text-3xl">
                نظرات مشتریان
            </h1>

            <p class="mt-2 text-xs leading-6 text-content-muted">
                بازخورد مشتریان {{ $salon->name }} را مشاهده و مدیریت کنید.
            </p>

        </div>


        @if(session('success'))

            <div class="mb-5 alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        <section class="mb-6 grid gap-4 sm:grid-cols-2">

            <div class="rounded-3xl border border-border bg-surface p-5 shadow-soft">

                <div class="text-[10px] text-content-muted">
                    امتیاز میانگین
                </div>

                <div class="mt-2 flex items-end gap-3">

                    <strong class="text-4xl font-black text-content">
                        {{ $averageRating ? number_format($averageRating, 1) : '—' }}
                    </strong>

                    <span class="mb-1 text-warning-500">
                        ★★★★★
                    </span>

                </div>

            </div>


            <div class="rounded-3xl border border-border bg-surface p-5 shadow-soft">

                <div class="text-[10px] text-content-muted">
                    تعداد نظرات
                </div>

                <div class="mt-2 text-4xl font-black text-content">
                    {{ $reviewsCount }}
                </div>

            </div>

        </section>


        @if($reviews->count())

            <div class="space-y-3">

                @foreach($reviews as $review)

                    <article class="rounded-3xl border border-border bg-surface p-5 shadow-soft">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-50 text-sm font-black text-accent-700">
                                        {{ mb_substr($review->customer?->name ?? 'م', 0, 1) }}
                                    </div>

                                    <div>

                                        <div class="text-sm font-black text-content">
                                            {{ $review->customer?->name ?? 'مشتری' }}
                                        </div>

                                        <div class="mt-1 text-[10px] text-content-muted">
                                            {{ $review->booking?->service?->name ?? 'خدمت' }}
                                        </div>

                                    </div>

                                </div>


                                <div class="mt-4 flex items-center gap-1 text-warning-500">

                                    @for($star = 1; $star <= 5; $star++)

                                        <span>
                                            {{ $star <= $review->rating ? '★' : '☆' }}
                                        </span>

                                    @endfor

                                    <span class="mr-2 text-[10px] font-bold text-content-muted">
                                        {{ $review->rating }}/۵
                                    </span>

                                </div>


                                @if($review->comment)

                                    <p class="mt-3 text-xs leading-7 text-content">
                                        {{ $review->comment }}
                                    </p>

                                @endif

                            </div>


                            <div class="shrink-0">

                                <form
                                    action="{{ route('salon.reviews.publish', $review) }}"
                                    method="POST"
                                >

                                    @csrf

                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="rounded-xl border border-border px-3 py-2 text-[10px] font-black transition hover:border-accent-200 hover:bg-accent-50"
                                    >
                                        {{ $review->is_published
                                            ? 'پنهان کردن'
                                            : 'انتشار' }}
                                    </button>

                                </form>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            @if($reviews->hasPages())

                <div class="mt-6">
                    {{ $reviews->links() }}
                </div>

            @endif

        @else

            <section class="rounded-3xl border border-dashed border-border bg-surface p-10 text-center">

                <div class="text-3xl text-accent-600">
                    ★
                </div>

                <h2 class="mt-4 text-base font-black text-content">
                    هنوز نظری ثبت نشده
                </h2>

                <p class="mt-2 text-xs text-content-muted">
                    پس از تکمیل نوبت‌ها، نظر مشتریان اینجا نمایش داده می‌شود.
                </p>

            </section>

        @endif

    </div>

@endsection
