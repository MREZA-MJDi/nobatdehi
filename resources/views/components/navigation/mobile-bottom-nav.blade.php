<nav
    class="mobile-bottom-nav"
    aria-label="ناوبری موبایل"
>

    <div class="mobile-bottom-nav-inner">


        <a
            href="{{ route('home') }}"
            @class([
                'mobile-bottom-item',
                'is-active' => request()->routeIs('home'),
            ])
        >
            <span class="mobile-bottom-icon">
                ⌂
            </span>

            <span>
                خانه
            </span>
        </a>


        <a
            href="{{ route('salons.discover') }}"
            @class([
                'mobile-bottom-item',
                'is-active' => request()->routeIs('salons.discover'),
            ])
        >
            <span class="mobile-bottom-icon">
                ⌕
            </span>

            <span>
                کشف
            </span>
        </a>


        <a
            href="{{ route('salons.discover') }}"
            class="mobile-bottom-book"
            aria-label="رزرو نوبت"
        >
            <span>
                +
            </span>
        </a>


        <a
            href="{{ route('salons.discover', ['sort' => 'nearest']) }}"
            class="mobile-bottom-item"
        >
            <span class="mobile-bottom-icon">
                ◉
            </span>

            <span>
                نزدیک من
            </span>
        </a>


        @auth

            @if(auth()->user()->isCustomer())

                <a
                    href="{{ route('customer.dashboard') }}"
                    @class([
                        'mobile-bottom-item',
                        'is-active' =>
                            request()->routeIs('customer.*'),
                    ])
                >
                    <span class="mobile-bottom-icon">
                        ◯
                    </span>

                    <span>
                        حساب
                    </span>
                </a>

            @else

                <a
                    href="{{ route('home') }}"
                    class="mobile-bottom-item"
                >
                    <span class="mobile-bottom-icon">
                        ◯
                    </span>

                    <span>
                        حساب
                    </span>
                </a>

            @endif

        @else

            <a
                href="{{ route('login') }}"
                class="mobile-bottom-item"
            >
                <span class="mobile-bottom-icon">
                    ◯
                </span>

                <span>
                    حساب
                </span>
            </a>

        @endauth

    </div>

</nav>
