@extends('layouts.salon')

@section('title', 'پست‌های سالن')

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <div class="px-4 py-5 pb-28 sm:px-6 sm:py-7 lg:px-8" dir="rtl">
        <div class="mx-auto w-full max-w-7xl">

            {{-- Header --}}
            <div class="mb-7">
                <a
                    href="{{ route('salon.dashboard') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-900"
                >
                    <span class="text-lg">→</span>
                    داشبورد سالن
                </a>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-xl text-white shadow-lg">
                                ✦
                            </div>

                            <div class="min-w-0">
                                <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                                    SALON POSTS
                                </div>

                                <h1 class="mt-1 truncate text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                    محتوای {{ $salon->name }}
                                </h1>
                            </div>
                        </div>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            عکس‌ها، ویدیوها و ریلزهای سالن را مدیریت کنید تا نمونه‌کارها و محتوای شما در معرض دید مشتری‌ها قرار بگیرد.
                        </p>
                    </div>

                    <a
                        href="{{ route('salon.posts.create') }}"
                        class="btn btn-accent shrink-0"
                    >
                        + افزودن پست
                    </a>
                </div>
            </div>


            {{-- Success --}}
            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700">
                            ✓
                        </div>

                        <div class="text-xs font-bold leading-6 text-emerald-800">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif


            {{-- Stats --}}
            @if($posts->count())
                @php
                    $publishedCount = $posts->where('is_active', true)->count();
                    $hiddenCount = $posts->where('is_active', false)->count();
                @endphp

                <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-[9px] font-black text-slate-400">
                            کل پست‌ها
                        </div>

                        <div class="mt-2 text-xl font-black text-slate-950">
                            {{ $posts->total() }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm">
                        <div class="text-[9px] font-black text-emerald-600">
                            منتشر شده
                        </div>

                        <div class="mt-2 text-xl font-black text-emerald-800">
                            {{ $publishedCount }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="text-[9px] font-black text-slate-500">
                            مخفی
                        </div>

                        <div class="mt-2 text-xl font-black text-slate-700">
                            {{ $hiddenCount }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-[9px] font-black text-slate-400">
                            بازدید این صفحه
                        </div>

                        <div class="mt-2 text-xl font-black text-slate-950">
                            {{ $posts->sum('views_count') }}
                        </div>
                    </div>
                </div>
            @endif


            {{-- Posts --}}
            @if($posts->count())

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

                    @foreach($posts as $post)

                        @php
                            $mediaUrl = Storage::url($post->media_path);

                            $typeValue = $post->type instanceof \App\Enums\PostType
                                ? $post->type->value
                                : (string) $post->type;

                            $typeLabel = match ($typeValue) {
                                'image' => 'عکس',
                                'video' => 'ویدیو',
                                'gif' => 'GIF',
                                'reel' => 'ریلز',
                                default => $typeValue,
                            };

                            $typeIcon = match ($typeValue) {
                                'image' => '▧',
                                'video' => '▶',
                                'gif' => 'GIF',
                                'reel' => '◎',
                                default => '•',
                            };

                            $posterUrl = $post->thumbnail_path
                                ? Storage::url($post->thumbnail_path)
                                : (
                                    $typeValue === 'image'
                                        ? $mediaUrl
                                        : null
                                );
                        @endphp

                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                            {{-- Media --}}
                            <div class="relative aspect-[4/4.5] overflow-hidden bg-slate-950">

                                @if($typeValue === 'image' && $posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        alt="{{ $post->title ?: 'پست سالن' }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @elseif($typeValue === 'video' && $posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        alt="{{ $post->title ?: 'ویدیو سالن' }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @elseif($typeValue === 'reel' && $posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        alt="{{ $post->title ?: 'ریلز سالن' }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @elseif($typeValue === 'gif' && $posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        alt="{{ $post->title ?: 'GIF سالن' }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-slate-900">
                                        <div class="flex flex-col items-center gap-3 text-white">
                                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl border border-white/10 bg-white/10 text-lg font-black backdrop-blur">
                                                {{ $typeIcon }}
                                            </div>

                                            <span class="text-xs font-black">
                                            {{ $typeLabel }}
                                        </span>
                                        </div>
                                    </div>
                                @endif


                                {{-- Top gradient --}}
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-black/55 to-transparent"></div>

                                {{-- Bottom gradient --}}
                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/75 to-transparent"></div>


                                {{-- Type --}}
                                <div class="absolute right-3 top-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/65 px-3 py-1.5 text-[9px] font-black text-white backdrop-blur-md">
                                    <span>{{ $typeIcon }}</span>
                                    {{ $typeLabel }}
                                </span>
                                </div>


                                {{-- Status --}}
                                <div class="absolute left-3 top-3">
                                    @if($post->is_active)
                                        <span class="rounded-full bg-emerald-400/90 px-3 py-1.5 text-[9px] font-black text-emerald-950 backdrop-blur-md">
                                        منتشر
                                    </span>
                                    @else
                                        <span class="rounded-full bg-white/90 px-3 py-1.5 text-[9px] font-black text-slate-700 backdrop-blur-md">
                                        مخفی
                                    </span>
                                    @endif
                                </div>


                                {{-- Bottom meta --}}
                                <div class="absolute inset-x-0 bottom-0 p-4 text-white">

                                    <div class="flex items-end justify-between gap-3">

                                        <div class="min-w-0">
                                            @if($post->title)
                                                <h2 class="truncate text-sm font-black">
                                                    {{ $post->title }}
                                                </h2>
                                            @endif

                                            @if($post->barber)
                                                <div class="mt-1 text-[9px] font-bold text-white/70">
                                                    توسط {{ $post->barber->name }}
                                                </div>
                                            @elseif($post->performed_by_owner)
                                                <div class="mt-1 text-[9px] font-bold text-white/70">
                                                    توسط صاحب سالن
                                                </div>
                                            @endif
                                        </div>

                                        @if($post->service)
                                            <span class="shrink-0 rounded-full bg-white/10 px-2.5 py-1 text-[8px] font-bold text-white/85 backdrop-blur">
                                            {{ $post->service->name }}
                                        </span>
                                        @endif

                                    </div>
                                </div>

                            </div>


                            {{-- Body --}}
                            <div class="p-5">

                                @if($post->caption)
                                    <p class="line-clamp-2 text-[10px] leading-6 text-slate-500">
                                        {{ $post->caption }}
                                    </p>
                                @else
                                    <p class="text-[10px] leading-6 text-slate-400">
                                        بدون کپشن
                                    </p>
                                @endif


                                {{-- Stats --}}
                                <div class="mt-4 grid grid-cols-3 divide-x divide-x-reverse divide-slate-100 rounded-2xl bg-slate-50">

                                    <div class="px-2 py-3 text-center">
                                        <div class="text-[8px] font-bold text-slate-400">
                                            بازدید
                                        </div>

                                        <div class="mt-1 text-[11px] font-black text-slate-800">
                                            {{ number_format($post->views_count) }}
                                        </div>
                                    </div>

                                    <div class="px-2 py-3 text-center">
                                        <div class="text-[8px] font-bold text-slate-400">
                                            لایک
                                        </div>

                                        <div class="mt-1 text-[11px] font-black text-slate-800">
                                            {{ number_format($post->likes_count) }}
                                        </div>
                                    </div>

                                    <div class="px-2 py-3 text-center">
                                        <div class="text-[8px] font-bold text-slate-400">
                                            کامنت
                                        </div>

                                        <div class="mt-1 text-[11px] font-black text-slate-800">
                                            {{ number_format($post->comments_count) }}
                                        </div>
                                    </div>

                                </div>


                                {{-- Actions --}}
                                <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">

                                    <a
                                        href="{{ route('salon.posts.edit', $post) }}"
                                        class="btn btn-secondary btn-sm flex-1"
                                    >
                                        ویرایش
                                    </a>


                                    <form
                                        action="{{ route('salon.posts.toggle', $post) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-ghost btn-sm"
                                            title="{{ $post->is_active ? 'مخفی کردن' : 'انتشار' }}"
                                        >
                                            {{ $post->is_active ? 'مخفی' : 'انتشار' }}
                                        </button>
                                    </form>


                                    <form
                                        action="{{ route('salon.posts.destroy', $post) }}"
                                        method="POST"
                                        onsubmit="return confirm('این پست حذف شود؟');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-ghost btn-sm text-red-500 hover:text-red-700"
                                            title="حذف پست"
                                        >
                                            حذف
                                        </button>
                                    </form>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- Pagination --}}
                @if($posts->hasPages())
                    <div class="mt-7">
                        {{ $posts->links() }}
                    </div>
                @endif


            @else

                {{-- Empty --}}
                <section class="relative overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm sm:p-16">

                    <div class="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-slate-100 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-20 h-52 w-52 rounded-full bg-slate-100 blur-3xl"></div>

                    <div class="relative mx-auto max-w-lg">

                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-slate-950 text-2xl text-white shadow-xl">
                            ✦
                        </div>

                        <div class="mt-6 text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">
                            YOUR CONTENT
                        </div>

                        <h2 class="mt-2 text-xl font-black tracking-tight text-slate-950">
                            هنوز پستی منتشر نکرده‌اید
                        </h2>

                        <p class="mt-3 text-xs leading-7 text-slate-500">
                            اولین عکس، ویدیو یا ریلز سالن را اضافه کنید تا محتوای شما در صفحه عمومی سالن نمایش داده شود.
                        </p>

                        <a
                            href="{{ route('salon.posts.create') }}"
                            class="btn btn-accent mt-7"
                        >
                            افزودن اولین پست
                        </a>

                    </div>

                </section>

            @endif

        </div>
    </div>


@endsection
