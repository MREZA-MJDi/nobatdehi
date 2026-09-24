<footer class="customer-footer">
    <div class="customer-container">

        <div class="customer-footer-grid">

            {{-- Brand --}}
            <div class="customer-footer-column">

                <a
                    href="{{ route('brand.intro') }}"
                    class="customer-footer-brand"
                    aria-label="نوبت‌دهی"
                >
                    <span
                        class="customer-brand-mark"
                        aria-hidden="true"
                    >
                        RM
                    </span>

                    <span class="customer-footer-brand-copy">
                        <strong>
                            نوبت‌دهی
                        </strong>

                        <small>
                            پیدا کن. انتخاب کن. نوبت بگیر.
                        </small>
                    </span>
                </a>

                <p class="customer-footer-description">
                    پلتفرم پیدا کردن سالن‌ها، متخصص‌ها و رزرو آنلاین نوبت؛
                    ساده، سریع و بدون تماس تلفنی.
                </p>

            </div>


            {{-- Discover --}}
            <div class="customer-footer-column">

                <h3 class="customer-footer-title">
                    کشف
                </h3>

                <nav
                    class="customer-footer-links"
                    aria-label="کشف"
                >
                    <a
                        href="{{ route('salons.discover') }}"
                    >
                        همه سالن‌ها
                    </a>

                    <a
                        href="{{ route('salons.discover', ['type' => 'barber']) }}"
                    >
                        آرایشگرها
                    </a>

                    <a
                        href="{{ route('salons.discover', ['sort' => 'rating']) }}"
                    >
                        محبوب‌ترین‌ها
                    </a>

                    <a
                        href="{{ route('salons.discover', ['sort' => 'newest']) }}"
                    >
                        جدیدترین سالن‌ها
                    </a>

                </nav>

            </div>


            {{-- For salons --}}
            <div class="customer-footer-column">

                <h3 class="customer-footer-title">
                    برای سالن‌ها
                </h3>

                <nav
                    class="customer-footer-links"
                    aria-label="برای سالن‌ها"
                >
                    <a
                        href="{{ route('login', ['entry' => 'salon']) }}"
                    >
                        ورود به پنل سالن
                    </a>

                    <a
                        href="{{ route('salons.discover') }}"
                    >
                        مشاهده بازار
                    </a>
                </nav>

            </div>

        </div>


        {{-- Bottom --}}
        <div class="customer-footer-bottom">

            <span>
                © {{ now()->year }} RM نوبت‌دهی
            </span>

            <span>
                همه حقوق محفوظ است.
            </span>

        </div>

    </div>
</footer>
