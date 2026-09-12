<section class="discovery-section">

    <div class="discovery-container">

        <div class="discovery-stats">

            <div class="discovery-stats-grid">

                <div class="discovery-stat">

                    <span class="discovery-stat-number">
                        {{ number_format(
                            $stats['salons'] ?? 0
                        ) }}+
                    </span>

                    <span class="discovery-stat-label">
                        سالن فعال
                    </span>

                </div>


                <div class="discovery-stat">

                    <span class="discovery-stat-number">
                        {{ number_format(
                            $stats['barbers'] ?? 0
                        ) }}+
                    </span>

                    <span class="discovery-stat-label">
                        متخصص
                    </span>

                </div>


                <div class="discovery-stat">

                    <span class="discovery-stat-number">
                        {{ number_format(
                            $stats['services'] ?? 0
                        ) }}+
                    </span>

                    <span class="discovery-stat-label">
                        خدمت
                    </span>

                </div>


                <div class="discovery-stat">

                    <span class="discovery-stat-number">
                        {{ number_format(
                            $stats['bookings'] ?? 0
                        ) }}+
                    </span>

                    <span class="discovery-stat-label">
                        نوبت ثبت‌شده
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>
