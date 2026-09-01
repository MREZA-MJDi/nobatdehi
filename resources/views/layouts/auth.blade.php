<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'ورود') | نوبت‌دهی
    </title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

</head>


<body>

<div class="min-h-screen">

    <main class="flex min-h-[100dvh] items-center justify-center px-4 py-8">

        <div class="w-full max-w-md">

            {{-- Brand --}}

            <a
                href="{{ url('/') }}"
                class="mx-auto mb-7 flex w-fit items-center gap-3"
            >

                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-950 text-sm font-black text-white shadow-soft">
                    ن
                </span>

                <span>

                    <span class="block text-sm font-black text-content">
                        نوبت‌دهی
                    </span>

                    <span class="mt-0.5 block text-[10px] text-content-muted">
                        رزرو آسان، تجربه بهتر
                    </span>

                </span>

            </a>


            @if(session('success'))

                <div class="mb-4 rounded-2xl border border-green-100 bg-green-50 px-4 py-3 text-xs font-bold text-green-700">
                    {{ session('success') }}
                </div>

            @endif


            @if(session('status'))

                <div class="mb-4 rounded-2xl border border-accent-100 bg-accent-50 px-4 py-3 text-xs font-bold text-accent-700">
                    {{ session('status') }}
                </div>

            @endif


            @yield('content')


            <div class="mt-6 text-center text-[10px] text-content-faint">
                © {{ now()->year }} نوبت‌دهی
            </div>

        </div>

    </main>

</div>

</body>

</html>
