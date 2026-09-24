<nav
    class="mobile-bottom-nav"
    aria-label="ناوبری موبایل"
>
    <div class="mobile-bottom-nav-inner">

        {{-- Home --}}
        <a
            href="{{ route('brand.intro') }}"
            @class([
                'mobile-bottom-item',
                'is-active' => request()->routeIs('brand.intro'),
            ])
            aria-label="خانه"
        >
            <span
                class="mobile-bottom-icon"
                aria-hidden="true"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5.5 9.5V21h13V9.5" />
                    <path d="M9.5 21v-6h5v6" />
                </svg>
            </span>

            <span class="mobile-bottom-label">
                خانه
            </span>
        </a>


        {{-- Discover --}}
        <a
            href="{{ route('salons.discover') }}"
            @class([
                'mobile-bottom-item',
                'is-active' => request()->routeIs('salons.discover'),
            ])
            aria-label="کشف"
        >
            <span
                class="mobile-bottom-icon"
                aria-hidden="true"
            >
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="6.5" />
                    <path d="m16 16 4.5 4.5" />
                </svg>
            </span>

            <span class="mobile-bottom-label">
                کشف
            </span>
        </a>


        {{-- Primary booking action --}}
        <a
            href="{{ route('salons.discover') }}"
            class="mobile-bottom-book"
            aria-label="رزرو نوبت"
        >
            <span
                class="mobile-bottom-book-icon"
                aria-hidden="true"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
            </span>
        </a>


        {{-- Nearby --}}
        <a
            href="{{ route('salons.discover', ['nearby' => '1']) }}"
            data-discover-location
            @class([
                'mobile-bottom-item',
                'is-active' =>
                    request()->routeIs('salons.discover') &&
                    (request('nearby') === '1' || request('sort') === 'distance'),
            ])
            aria-label="نزدیک من"
        >
            <span
                class="mobile-bottom-icon"
                aria-hidden="true"
            >
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8" />
                    <circle cx="12" cy="12" r="2.5" />
                    <path d="M12 4v2" />
                    <path d="M12 18v2" />
                    <path d="M4 12h2" />
                    <path d="M18 12h2" />
                </svg>
            </span>

            <span class="mobile-bottom-label">
                نزدیک من
            </span>
        </a>


        {{-- Account --}}
        @auth

            @if(auth()->user()->isCustomer())

                <a
                    href="{{ route('customer.dashboard') }}"
                    @class([
                        'mobile-bottom-item',
                        'is-active' =>
                            request()->routeIs('customer.*'),
                    ])
                    aria-label="حساب"
                >
                    <span
                        class="mobile-bottom-icon"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.5" />
                            <path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6" />
                        </svg>
                    </span>

                    <span class="mobile-bottom-label">
                        حساب
                    </span>
                </a>

            @else

                <a
                    href="{{ route('brand.intro') }}"
                    class="mobile-bottom-item"
                    aria-label="حساب"
                >
                    <span
                        class="mobile-bottom-icon"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.5" />
                            <path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6" />
                        </svg>
                    </span>

                    <span class="mobile-bottom-label">
                        حساب
                    </span>
                </a>

            @endif

        @else

            <a
                href="{{ route('login') }}"
                class="mobile-bottom-item"
                aria-label="ورود"
            >
                <span
                    class="mobile-bottom-icon"
                    aria-hidden="true"
                >
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3.5" />
                        <path d="M5 21c.8-4 3.2-6 7-6s6.2 2 7 6" />
                    </svg>
                </span>

                <span class="mobile-bottom-label">
                    حساب
                </span>
            </a>

        @endauth

    </div>
</nav>
