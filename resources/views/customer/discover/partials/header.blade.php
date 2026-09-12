<header class="discovery-header">

    <div class="discovery-container discovery-header-inner">

        {{-- LOGO --}}
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


        {{-- NAVIGATION --}}
        <nav
            class="discovery-nav"
            aria-label="ناوبری اصلی"
        >

            <a
                href="#salons"
                class="discovery-nav-link"
            >
                سالن‌ها
            </a>

            <a
                href="#services"
                class="discovery-nav-link"
            >
                خدمات
            </a>

            <a
                href="#stylists"
                class="discovery-nav-link"
            >
                متخصص‌ها
            </a>

            <a
                href="#nearby"
                class="discovery-nav-link"
            >
                نزدیک شما
            </a>

        </nav>


        {{-- ACTIONS --}}
        <div class="discovery-header-actions">

            @auth

                <a
                    href="{{ url('/account') }}"
                    class="discovery-header-link"
                >
                    حساب من
                </a>

            @else

                <a
                    href="{{ route('login') }}"
                    class="discovery-header-link"
                >
                    ورود
                </a>

            @endauth

            <a
                href="{{ route('brand.intro') }}"
                class="discovery-header-button"
            >
                ثبت سالن
            </a>

            <button
                type="button"
                class="discovery-mobile-toggle"
                aria-label="باز کردن منو"
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
