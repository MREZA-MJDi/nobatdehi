<footer class="customer-footer">
    <div class="customer-container">
        <div class="customer-footer-grid">
            <div class="customer-footer-column">
                <a
                    href="{{ route('salons.discover') }}"
                    class="customer-footer-brand"
                    aria-label="NOBAT"
                >
                    <span class="customer-brand-mark" aria-hidden="true">N</span>
                    <span class="customer-footer-brand-copy">
                        <strong>NOBAT</strong>
                        <small>پیدا کن. انتخاب کن. نوبت بگیر.</small>
                    </span>
                </a>

                <p class="customer-footer-description">
                    مسیر پیدا کردن سالن، متخصص و زمان مناسب برای نوبت؛ یک‌جا، ساده و شفاف.
                </p>

                <div class="mt-4 flex items-center gap-2 text-[9px] font-black tracking-[0.14em] text-content-faint">
                    <span>RM</span>
                    <span aria-hidden="true">/</span>
                    <span>CO</span>
                    <span aria-hidden="true">·</span>
                    <span>NOBAT PLATFORM</span>
                </div>
            </div>

            <div class="customer-footer-column">
                <h3 class="customer-footer-title">کشف</h3>
                <nav class="customer-footer-links" aria-label="کشف">
                    <a href="{{ route('salons.discover') }}">همه سالن‌ها</a>
                    <a href="{{ route('salons.discover', ['type' => 'barber']) }}">متخصص‌ها</a>
                    <a href="{{ route('salons.discover', ['sort' => 'rating']) }}">بالاترین امتیاز</a>
                    <a href="{{ route('salons.discover', ['sort' => 'distance']) }}">نزدیک من</a>
                </nav>
            </div>

            <div class="customer-footer-column">
                <h3 class="customer-footer-title">رزرو</h3>
                <nav class="customer-footer-links" aria-label="رزرو">
                    <a href="{{ route('salons.discover') }}#results">پیدا کردن سالن</a>
                    <a href="{{ route('salons.discover') }}#services">پیدا کردن خدمت</a>
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.dashboard') }}">نوبت‌های من</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}">ورود</a>
                    @endauth
                </nav>
            </div>

            <div class="customer-footer-column">
                <h3 class="customer-footer-title">برای سالن‌ها</h3>
                <nav class="customer-footer-links" aria-label="برای سالن‌ها">
                    <a href="{{ route('login') }}">ورود به پنل</a>
                    <a href="{{ route('register') }}">ثبت‌نام</a>
                    <a href="{{ route('salons.discover') }}">مشاهده تجربه NOBAT</a>
                </nav>
            </div>
        </div>

        <div class="customer-footer-bottom">
            <span>© {{ now()->year }} NOBAT</span>
            <span>RM / CO</span>
            <span>همه حقوق محفوظ است.</span>
        </div>
    </div>
</footer>
