<footer class="customer-footer">

    <div class="customer-container">

        <div class="customer-footer-grid">

            <div>

                <a
                    href="{{ route('home') }}"
                    class="customer-footer-brand"
                >
                    <span class="customer-brand-mark">
                        RM
                    </span>

                    <span>
                        <strong>نوبت‌دهی</strong>

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


            <div>

                <h3>
                    کشف
                </h3>

                <div class="customer-footer-links">

                    <a href="{{ route('salons.discover') }}">
                        همه سالن‌ها
                    </a>

                    <a href="{{ route('salons.discover', ['type' => 'barber']) }}">
                        آرایشگرها
                    </a>

                    <a href="{{ route('salons.discover', ['sort' => 'rating']) }}">
                        محبوب‌ترین‌ها
                    </a>

                </div>

            </div>


            <div>

                <h3>
                    برای سالن‌ها
                </h3>

                <div class="customer-footer-links">

                    <a href="{{ route('login') }}">
                        ورود
                    </a>

                    <a href="{{ route('register') }}">
                        ثبت‌نام
                    </a>

                    <a href="{{ route('salons.discover') }}">
                        مشاهده بازار
                    </a>

                </div>

            </div>

        </div>


        <div class="customer-footer-bottom">

            <span>
                © {{ date('Y') }} RM نوبت‌دهی
            </span>

            <span>
                همه حقوق محفوظ است.
            </span>

        </div>

    </div>

</footer>
