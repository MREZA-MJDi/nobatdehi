<nav
    class="mobile-bottom-nav"
    aria-label="ناوبری موبایل"
>

    <div class="mobile-bottom-nav-inner">

        {{-- ========================================================
            HOME
        ========================================================= --}}

        <a
            href="{{ route('home') }}"
            class="bottom-nav-item {{ request()->routeIs('home') ? 'is-active' : '' }}"
        >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5.5 9.5V21h13V9.5" />
                </svg>

            </span>


            <span class="bottom-nav-label">
                خانه
            </span>

        </a>


        {{-- ========================================================
            DISCOVER
        ========================================================= --}}

        <a
            href="{{ route('salons.discover') }}"
            class="bottom-nav-item {{ request()->routeIs('salons.discover') ? 'is-active' : '' }}"
        >

            <span class="bottom-nav-icon">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="6.5"
                    />

                    <path d="m16 16 4.5 4.5" />
                </svg>

            </span>


            <span class="bottom-nav-label">
                کشف
            </span>

        </a>


        @auth

            {{-- ====================================================
                CUSTOMER
            ===================================================== --}}

            @if(auth()->user()->isCustomer())

                <a
                    href="{{ route('salons.discover') }}"
                    class="bottom-nav-item {{ request()->routeIs('public.salons.booking.*') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="4"
                                y="5"
                                width="16"
                                height="16"
                                rx="2"
                            />

                            <path d="M8 3v4" />
                            <path d="M16 3v4" />
                            <path d="M4 10h16" />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        رزرو
                    </span>

                </a>


                <a
                    href="{{ route('customer.dashboard') }}"
                    class="bottom-nav-item {{ request()->routeIs('customer.*') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="8"
                                r="3.5"
                            />

                            <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        من
                    </span>

                </a>


                {{-- ====================================================
                    SALON OWNER
                ===================================================== --}}

            @elseif(auth()->user()->isSalonOwner())

                <a
                    href="{{ route('salon.dashboard') }}"
                    class="bottom-nav-item {{ request()->routeIs('salon.dashboard') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="4"
                                y="4"
                                width="7"
                                height="7"
                                rx="1.5"
                            />

                            <rect
                                x="13"
                                y="4"
                                width="7"
                                height="7"
                                rx="1.5"
                            />

                            <rect
                                x="4"
                                y="13"
                                width="7"
                                height="7"
                                rx="1.5"
                            />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        پنل سالن
                    </span>

                </a>


                <a
                    href="{{ route('salon.bookings.index') }}"
                    class="bottom-nav-item {{ request()->routeIs('salon.bookings.*') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="4"
                                y="5"
                                width="16"
                                height="16"
                                rx="2"
                            />

                            <path d="M8 3v4" />
                            <path d="M16 3v4" />
                            <path d="M4 10h16" />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        نوبت‌ها
                    </span>

                </a>


                {{-- ====================================================
                    SUPER ADMIN
                ===================================================== --}}

            @elseif(auth()->user()->isSuperAdmin())

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="4"
                                y="4"
                                width="7"
                                height="7"
                                rx="1.5"
                            />

                            <rect
                                x="13"
                                y="4"
                                width="7"
                                height="7"
                                rx="1.5"
                            />

                            <rect
                                x="4"
                                y="13"
                                width="7"
                                height="7"
                                rx="1.5"
                            />

                            <rect
                                x="13"
                                y="13"
                                width="7"
                                height="7"
                                rx="1.5"
                            />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        مدیریت
                    </span>

                </a>


                <a
                    href="{{ route('admin.salons.index') }}"
                    class="bottom-nav-item {{ request()->routeIs('admin.salons.*') ? 'is-active' : '' }}"
                >

                    <span class="bottom-nav-icon">

                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path d="M3 10h18" />
                            <path d="M5 10v10" />
                            <path d="M19 10v10" />
                            <path d="M3 20h18" />
                            <path d="M4 10 6 4h12l2 6" />
                            <path d="M9 20v-6h6v6" />
                        </svg>

                    </span>


                    <span class="bottom-nav-label">
                        سالن‌ها
                    </span>

                </a>

            @endif


        @else

            {{-- ====================================================
                GUEST
            ===================================================== --}}

            <a
                href="{{ route('login') }}"
                class="bottom-nav-item {{ request()->routeIs('login*') ? 'is-active' : '' }}"
            >

                <span class="bottom-nav-icon">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M10 17l5-5-5-5" />
                        <path d="M15 12H3" />
                        <path d="M21 3v18" />
                    </svg>

                </span>


                <span class="bottom-nav-label">
                    ورود
                </span>

            </a>


            <a
                href="{{ route('register') }}"
                class="bottom-nav-item {{ request()->routeIs('register*') ? 'is-active' : '' }}"
            >

                <span class="bottom-nav-icon">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="8"
                            r="3.5"
                        />

                        <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />

                        <path d="M19 13v6" />
                        <path d="M16 16h6" />
                    </svg>

                </span>


                <span class="bottom-nav-label">
                    ثبت‌نام
                </span>

            </a>

        @endauth

    </div>

</nav>
