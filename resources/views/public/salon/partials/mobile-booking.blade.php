@if($salon->is_active && $services->isNotEmpty())

    <div class="salon-mobile-booking">

        <div class="salon-mobile-booking-inner">

            <div class="salon-mobile-booking-info">

                <span class="salon-mobile-booking-label">
                    رزرو آنلاین
                </span>

                <strong>
                    آماده‌ای نوبتت رو بگیری؟
                </strong>

                <span>
                    خدمت، متخصص و زمان دلخواهت را انتخاب کن.
                </span>

            </div>


            <button
                type="button"
                class="salon-mobile-booking-button"
                @click="
                    window.dispatchEvent(
                        new CustomEvent('open-booking')
                    )
                "
            >

                <span>
                    رزرو نوبت
                </span>

                <span aria-hidden="true">
                    ←
                </span>

            </button>

        </div>

    </div>

@endif
