<header class="discovery-header">

    <div class="discovery-container discovery-header-inner">

        {{-- =========================================================
            LOGO
        ========================================================== --}}
        <a
            href="{{ route('salons.discover') }}"
            class="discovery-logo"
            aria-label="NOBAT"
        >
            <span class="discovery-logo-mark">
                N
            </span>

            <span class="discovery-logo-word">
                NOBAT
            </span>
        </a>


        {{-- =========================================================
            NAVIGATION
        ========================================================== --}}
        <nav
            class="discovery-nav"
            id="discoveryNav"
            aria-label="ناوبری اصلی"
        >

            <a
                href="{{ route('salons.discover') }}"
                class="discovery-nav-link"
            >
                سالن‌ها
            </a>

            <a
                href="{{ route('salons.discover') }}#services"
                class="discovery-nav-link"
            >
                خدمات
            </a>

            <a
                href="{{ route('salons.discover') }}#stylists"
                class="discovery-nav-link"
            >
                متخصص‌ها
            </a>

            <a
                href="{{ route('salons.discover') }}#nearby"
                class="discovery-nav-link"
            >
                نزدیک شما
            </a>

        </nav>


        {{-- =========================================================
            ACTIONS
        ========================================================== --}}
        <div class="discovery-header-actions">

            @auth

                {{-- CUSTOMER --}}
                @if(auth()->user()->role === \App\Enums\UserRole::CUSTOMER)

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="discovery-header-link"
                    >
                        حساب من
                    </a>

                    {{-- SALON OWNER --}}
                @elseif(auth()->user()->role === \App\Enums\UserRole::SALON_OWNER)

                    <a
                        href="{{ route('salon.dashboard') }}"
                        class="discovery-header-link"
                    >
                        پنل سالن
                    </a>

                    {{-- SUPER ADMIN --}}
                @elseif(auth()->user()->role === \App\Enums\UserRole::SUPER_ADMIN)

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="discovery-header-link"
                    >
                        مدیریت
                    </a>

                @endif


                {{-- LOGOUT --}}
                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="discovery-logout-form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="discovery-header-link discovery-logout-button"
                    >
                        خروج
                    </button>
                </form>

            @else

                <a
                    href="{{ route('login') }}"
                    class="discovery-header-link"
                >
                    ورود
                </a>

            @endauth


            {{-- =====================================================
                SALON CTA
            ====================================================== --}}
            @guest

                <a
                    href="{{ route('login') }}"
                    class="discovery-header-button"
                >
                    ثبت سالن
                </a>

            @else

                @if(auth()->user()->role === \App\Enums\UserRole::SALON_OWNER)

                    <a
                        href="{{ route('salon.dashboard') }}"
                        class="discovery-header-button"
                    >
                        پنل سالن
                    </a>

                @else

                    <a
                        href="{{ route('login') }}"
                        class="discovery-header-button"
                    >
                        ثبت سالن
                    </a>

                @endif

            @endguest


            {{-- =========================================================
                MOBILE MENU
            ========================================================== --}}
            <button
                type="button"
                class="discovery-mobile-toggle"
                id="discoveryMobileToggle"
                aria-label="باز کردن منو"
                aria-expanded="false"
                aria-controls="discoveryNav"
            >
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M4 7h16M4 12h16M4 17h16"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            </button>

        </div>

    </div>

</header>
