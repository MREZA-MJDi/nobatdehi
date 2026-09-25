@extends('layouts.customer')

@section('title', 'امتیاز دادن به ' . $salon->name)

@section('meta_description', 'ثبت امتیاز برای ' . $salon->name)

@section('content')

    <div class="customer-container py-6 pb-28 sm:py-10">

        <div class="mx-auto w-full max-w-xl">

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="mb-5 inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-content"
            >
                <span aria-hidden="true">→</span>
                {{ $salon->name }}
            </a>

            <section class="overflow-hidden rounded-3xl border border-border bg-surface shadow-soft">

                <div class="border-b border-border bg-primary-50/60 px-5 py-5 sm:px-7">

                    <div class="text-[10px] font-black tracking-[0.18em] text-accent-600">
                        RATE THE PLACE
                    </div>

                    <h1 class="mt-2 text-2xl font-black leading-tight text-content sm:text-3xl">
                        نظرت درباره این سالن چیه؟
                    </h1>

                    <p class="mt-2 text-xs leading-7 text-content-muted">
                        حتی اگر هنوز از این سالن نوبت نگرفتی، می‌توانی به تجربه کلیت از این مکان امتیاز بدهی.
                        نوشتن توضیح اختیاری است.
                    </p>

                </div>

                <form
                    action="{{ route('customer.salons.review.store', $salon) }}"
                    method="POST"
                    class="p-5 sm:p-7"
                >
                    @csrf

                    @if($errors->any())
                        <div
                            class="mb-6 rounded-2xl border border-danger-100 bg-danger-50 p-4"
                            role="alert"
                            aria-live="polite"
                        >
                            <div class="text-xs font-black text-danger-700">
                                ثبت امتیاز انجام نشد
                            </div>

                            <div class="mt-2 space-y-1 text-[10px] font-bold leading-6 text-danger-700">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <fieldset>
                        <legend class="text-sm font-black text-content">
                            امتیاز شما
                        </legend>

                        <p class="mt-1.5 text-[10px] leading-6 text-content-muted">
                            حداقل یک ستاره کافی است.
                        </p>

                        <div
                            class="mt-5 flex items-center justify-center gap-1 sm:gap-2"
                            dir="ltr"
                        >
                            @for($star = 1; $star <= 5; $star++)
                                <label class="group cursor-pointer">
                                    <input
                                        type="radio"
                                        name="rating"
                                        value="{{ $star }}"
                                        class="peer sr-only"
                                        {{ (string) old('rating', 0) === (string) $star ? 'checked' : '' }}
                                    >

                                    <span
                                        class="block rounded-xl px-1 py-1 text-4xl leading-none text-content-faint transition-all duration-150 group-hover:scale-110 group-hover:text-warning-500 peer-checked:scale-110 peer-checked:text-warning-500 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/30 sm:text-5xl"
                                        aria-hidden="true"
                                    >
                                        ★
                                    </span>

                                    <span class="sr-only">
                                        {{ $star }} از ۵
                                    </span>
                                </label>
                            @endfor
                        </div>

                        @error('rating')
                            <p class="mt-3 text-center text-[10px] font-bold text-danger-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </fieldset>

                    <div class="mt-8">
                        <div class="flex items-center justify-between gap-3">
                            <label
                                for="salon-review-comment"
                                class="text-sm font-black text-content"
                            >
                                توضیح کوتاه
                            </label>

                            <span class="text-[10px] text-content-faint">
                                اختیاری
                            </span>
                        </div>

                        <p class="mt-1.5 text-[10px] leading-6 text-content-muted">
                            اگر تجربه‌ای داری، درباره کیفیت خدمات، برخورد یا فضای سالن بنویس.
                        </p>

                        <textarea
                            id="salon-review-comment"
                            name="comment"
                            rows="5"
                            maxlength="2000"
                            class="customer-input mt-3 min-h-32 w-full resize-y"
                            placeholder="مثلاً: فضای سالن خیلی مرتب بود..."
                        >{{ old('comment') }}</textarea>

                        @error('comment')
                            <p class="mt-2 text-[10px] font-bold text-danger-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="mt-6 rounded-2xl border border-accent-100 bg-accent-50/70 px-4 py-3 text-[10px] leading-6 text-content-muted">
                        امتیاز شما روی میانگین امتیاز این سالن اثر می‌گذارد.
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('public.salons.show', $salon) }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-xs font-black text-content-muted transition hover:bg-primary-50 hover:text-content"
                        >
                            انصراف
                        </a>

                        <button
                            type="submit"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-accent-600 px-6 text-xs font-black text-white transition hover:-translate-y-0.5 hover:bg-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-500/30"
                        >
                            ثبت امتیاز
                            <span class="mr-2" aria-hidden="true">✓</span>
                        </button>
                    </div>
                </form>
            </section>

        </div>

    </div>

@endsection
