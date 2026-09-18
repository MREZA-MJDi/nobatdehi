<section class="discover-stats-section">

    <div class="discover-container">

        <div class="discover-stats-grid">

            <div class="discover-stat">

                <strong>
                    {{ number_format(
                        $stats['salons'] ?? 0
                    ) }}+
                </strong>

                <span>
                    سالن فعال
                </span>

            </div>


            <div class="discover-stat">

                <strong>
                    {{ number_format(
                        $stats['barbers'] ?? 0
                    ) }}+
                </strong>

                <span>
                    متخصص
                </span>

            </div>


            <div class="discover-stat">

                <strong>
                    {{ number_format(
                        $stats['services'] ?? 0
                    ) }}+
                </strong>

                <span>
                    خدمت
                </span>

            </div>

        </div>

    </div>

</section>
