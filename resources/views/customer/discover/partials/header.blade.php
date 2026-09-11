<header class="discover-header">
    <div class="discover-container discover-nav">

        <a
            href="{{ route('salons.discover') }}"
            class="discover-brand"
            aria-label="NOBAT"
        >
            <span class="discover-brand-mark" aria-hidden="true">N</span>

            <span class="discover-brand-text">
                NOBAT
            </span>
        </a>

        <nav
            class="discover-nav-links"
            aria-label="منوی اصلی"
        >
            <a href="#services">
                خدمات
            </a>

            <a href="#salons">
                سالن‌ها
            </a>

            <a href="#stylists">
                متخصص‌ها
            </a>
        </nav>

        <div class="discover-nav-actions">

            @auth
                <a
                    href="{{ route('account') }}"
                    class="discover-account-link"
                >
                    حساب من
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="discover-account-link"
                >
                    ورود
                </a>
            @endauth

        </div>

    </div>
</header>
