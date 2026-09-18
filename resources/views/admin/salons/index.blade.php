@extends('layouts.admin')

@section('title', 'سالن‌ها')

@section('content')

    <div class="mx-auto max-w-[1400px] px-4 py-5 md:px-6 md:py-7">

        {{-- Header --}}

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <h1 class="page-title">
                    سالن‌ها
                </h1>

                <p class="page-subtitle">
                    مدیریت سالن‌های ثبت‌شده در پلتفرم
                </p>

            </div>

            <a
                href="{{ route('admin.salons.create') }}"
                class="btn btn-accent w-full sm:w-auto"
            >

                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 5v14M5 12h14" />
                </svg>

                سالن جدید

            </a>

        </div>


        {{-- Desktop Table --}}

        <div class="table-wrap hidden md:block">

            <table class="app-table">

                <thead>

                <tr>

                    <th>
                        سالن
                    </th>

                    <th>
                        کد
                    </th>

                    <th>
                        حساب کنترل‌کننده
                    </th>

                    <th>
                        موقعیت
                    </th>

                    <th>
                        وضعیت
                    </th>

                    <th class="text-left">
                        عملیات
                    </th>

                </tr>

                </thead>


                <tbody>

                @forelse($salons as $salon)

                    <tr>

                        {{-- Salon --}}

                        <td>

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-900 text-sm font-black text-white">

                                    @if($salon->logo_path)

                                        <img
                                            src="{{ Storage::url($salon->logo_path) }}"
                                            alt="{{ $salon->name }}"
                                            class="h-full w-full object-cover"
                                        >

                                    @else

                                        {{ mb_substr($salon->name, 0, 1) }}

                                    @endif

                                </div>


                                <div class="min-w-0">

                                    <div class="truncate text-xs font-black text-content">
                                        {{ $salon->name }}
                                    </div>

                                    <div class="mt-0.5 text-[10px] text-content-muted">
                                        {{ $salon->phone ?: 'بدون شماره تماس' }}
                                    </div>

                                </div>

                            </div>

                        </td>


                        {{-- Code --}}

                        <td>

                            <code
                                class="rounded-lg bg-primary-50 px-2 py-1 text-[10px] font-bold text-primary-700"
                                dir="ltr"
                            >
                                {{ $salon->code }}
                            </code>

                        </td>


                        {{-- Owner --}}

                        <td>

                            @if($salon->owner)

                                <div class="text-xs font-bold text-content">
                                    {{ $salon->owner->name }}
                                </div>

                                @if($salon->owner->phone)

                                    <div
                                        class="mt-0.5 text-[10px] text-content-muted"
                                        dir="ltr"
                                    >
                                        {{ $salon->owner->phone }}
                                    </div>

                                @elseif($salon->owner->email)

                                    <div
                                        class="mt-0.5 text-[10px] text-content-muted"
                                        dir="ltr"
                                    >
                                        {{ $salon->owner->email }}
                                    </div>

                                @endif

                            @else

                                <span class="text-xs text-content-muted">
                                    تعیین نشده
                                </span>

                            @endif

                        </td>


                        {{-- Location --}}

                        <td>

                            <div class="max-w-[180px] truncate text-xs text-content-soft">

                                {{ $salon->city ?: '—' }}

                                @if($salon->district)
                                    ، {{ $salon->district }}
                                @endif

                            </div>

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($salon->is_active)

                                <span class="badge badge-success">
                                    فعال
                                </span>

                            @else

                                <span class="badge badge-danger">
                                    غیرفعال
                                </span>

                            @endif

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="flex justify-end gap-1.5">

                                <a
                                    href="{{ route('admin.salons.show', $salon) }}"
                                    class="btn btn-secondary btn-sm"
                                >
                                    مشاهده
                                </a>

                                <a
                                    href="{{ route('admin.salons.edit', $salon) }}"
                                    class="btn btn-ghost btn-sm"
                                >
                                    ویرایش
                                </a>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            <div class="py-16 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-400">

                                    <svg
                                        width="22"
                                        height="22"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M4 21V8l8-5 8 5v13" />
                                        <path d="M8 21v-6h8v6" />
                                    </svg>

                                </div>

                                <h3 class="mt-4 text-sm font-black">
                                    هنوز سالنی ثبت نشده
                                </h3>

                                <p class="mt-1 text-xs text-content-muted">
                                    اولین سالن را ایجاد کنید.
                                </p>

                                <a
                                    href="{{ route('admin.salons.create') }}"
                                    class="btn btn-accent btn-sm mt-4"
                                >
                                    ایجاد سالن
                                </a>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- Mobile Cards --}}

        <div class="space-y-3 md:hidden">

            @forelse($salons as $salon)

                <article class="card p-4">

                    <div class="flex gap-3">

                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-900 text-base font-black text-white">

                            @if($salon->logo_path)

                                <img
                                    src="{{ Storage::url($salon->logo_path) }}"
                                    alt="{{ $salon->name }}"
                                    class="h-full w-full object-cover"
                                >

                            @else

                                {{ mb_substr($salon->name, 0, 1) }}

                            @endif

                        </div>


                        <div class="min-w-0 flex-1">

                            <div class="flex items-start justify-between gap-2">

                                <div class="min-w-0">

                                    <h2 class="truncate text-sm font-black text-content">
                                        {{ $salon->name }}
                                    </h2>

                                    <p class="mt-0.5 text-[10px] text-content-muted">
                                        {{ $salon->city ?: 'بدون شهر' }}
                                    </p>

                                </div>


                                @if($salon->is_active)

                                    <span class="badge badge-success shrink-0">
                                        فعال
                                    </span>

                                @else

                                    <span class="badge badge-danger shrink-0">
                                        غیرفعال
                                    </span>

                                @endif

                            </div>


                            <div class="mt-3 flex flex-wrap items-center gap-2">

                                <code
                                    class="rounded-lg bg-primary-50 px-2 py-1 text-[9px] font-bold text-primary-700"
                                    dir="ltr"
                                >
                                    {{ $salon->code }}
                                </code>

                                @if($salon->owner)

                                    <span class="text-[10px] text-content-muted">
                                        {{ $salon->owner->name }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>


                    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-border pt-3">

                        <a
                            href="{{ route('admin.salons.show', $salon) }}"
                            class="btn btn-secondary btn-sm"
                        >
                            مشاهده
                        </a>

                        <a
                            href="{{ route('admin.salons.edit', $salon) }}"
                            class="btn btn-primary btn-sm"
                        >
                            ویرایش
                        </a>

                    </div>

                </article>

            @empty

                <div class="empty-state">

                    <div>

                        <div class="empty-icon mx-auto">
                            +
                        </div>

                        <h3 class="mt-4 text-sm font-black">
                            هنوز سالنی ندارید
                        </h3>

                        <p class="mt-1 text-xs text-content-muted">
                            اولین سالن را ایجاد کنید.
                        </p>

                        <a
                            href="{{ route('admin.salons.create') }}"
                            class="btn btn-accent btn-sm mt-4"
                        >
                            ایجاد اولین سالن
                        </a>

                    </div>

                </div>

            @endforelse

        </div>


        {{-- Pagination --}}

        @if($salons->hasPages())

            <div class="mt-5">
                {{ $salons->links() }}
            </div>

        @endif

    </div>

@endsection
