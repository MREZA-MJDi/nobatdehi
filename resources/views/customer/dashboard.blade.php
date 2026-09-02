@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')

    <div class="app-container py-6">

        <div class="card p-6">

            <div class="mb-2 text-xs font-bold text-accent-600">
                CUSTOMER
            </div>

            <h1 class="text-2xl font-black text-content">
                خوش آمدید
                {{ $user?->name ?? '' }}
            </h1>

            <p class="mt-2 text-sm text-content-muted">
                داشبورد مشتری در مرحله بعد کامل می‌شود.
            </p>

        </div>

    </div>

@endsection
