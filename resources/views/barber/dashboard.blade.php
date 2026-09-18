@extends('layouts.app')

@section('title', 'داشبورد آرایشگر')

@section('content')

    <div class="mx-auto max-w-[1200px] px-4 py-6">

        <div class="card p-6">

            <div class="text-xs font-bold text-accent-600">
                BARBER
            </div>

            <h1 class="mt-2 text-2xl font-black text-content">
                داشبورد آرایشگر
            </h1>

            <p class="mt-2 text-sm text-content-muted">
                خوش آمدید
                {{ $user?->name ?? 'آرایشگر' }}
            </p>

        </div>

    </div>

@endsection
