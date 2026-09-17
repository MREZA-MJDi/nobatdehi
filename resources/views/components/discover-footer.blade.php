@props([])

<footer class="discover-footer" aria-labelledby="discover-footer-title">
    <div class="discover-footer-inner">
        <div class="discover-footer-brand">
            <a href="{{ route('salons.discover') }}" class="discover-footer-brand-link" aria-label="NOBAT — کشف سالن‌ها">
                <span class="discover-footer-mark" aria-hidden="true">N</span>
                <span>
                    <strong id="discover-footer-title">NOBAT</strong>
                    <small>پیدا کن · انتخاب کن · نوبت بگیر</small>
                </span>
            </a>

            <p>
                NOBAT مسیر پیدا کردن سالن، شناخت خدمات و انتخاب زمان مناسب را یک‌جا ساده می‌کند.
                اطلاعات نمایشی بر اساس داده‌های ثبت‌شده سالن‌ها و ظرفیت نوبت‌دهی آن‌هاست.
            </p>

            <div class="discover-footer-imprint" aria-label="هویت محصول">
                <span>RM / CO</span>
                <i aria-hidden="true"></i>
                <span>NOBAT PLATFORM</span>
            </div>
        </div>

        <div class="discover-footer-column">
            <h2>کشف</h2>
            <a href="{{ route('salons.discover') }}#results">همه سالن‌ها</a>
            <a href="{{ route('salons.discover', ['type' => 'barber']) }}#results">متخصص‌ها</a>
            <a href="{{ route('salons.discover', ['sort' => 'rating']) }}#results">بالاترین امتیاز</a>
            <a href="{{ route('salons.discover', ['sort' => 'distance']) }}#results">نزدیک من</a>
        </div>

        <div class="discover-footer-column">
            <h2>رزرو</h2>
            <a href="{{ route('salons.discover') }}#results">پیدا کردن خدمت</a>
            <a href="{{ route('salons.discover') }}#results">پیدا کردن سالن</a>
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}">نوبت‌های من</a>
                @endif
            @else
                <a href="{{ route('login') }}">ورود به حساب</a>
            @endauth
        </div>

        <div class="discover-footer-column">
            <h2>برای سالن‌ها</h2>
            <a href="{{ route('login') }}">ورود</a>
            <a href="{{ route('register') }}">ثبت‌نام</a>
            <a href="{{ route('salons.discover') }}">مشاهده NOBAT</a>
        </div>
    </div>

    <div class="discover-footer-bottom">
        <span>© {{ now()->year }} NOBAT</span>
        <span>طراحی و محصول · RM / CO</span>
        <a href="{{ route('salons.discover') }}">بازگشت به کشف <span aria-hidden="true">↑</span></a>
    </div>
</footer>
