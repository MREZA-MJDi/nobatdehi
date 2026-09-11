<section
    id="stylists"
    class="discover-section discover-stylists"
>
    <div class="discover-container">

        <x-discover.section-heading
            eyebrow="متخصص‌ها"
            title="متخصص مناسب خودت را پیدا کن."
            description="گاهی انتخاب آرایشگر از انتخاب سالن مهم‌تر است. متخصص موردنظرت را پیدا کن و از همان سالن نوبت بگیر."
        />

        @if($stylists->isNotEmpty())

            <div class="discover-stylist-grid">

                @foreach($stylists as $barber)
                    <x-discover.stylist-card
                        :barber="$barber"
                    />
                @endforeach

            </div>

        @else

            <div class="discover-empty">
                <h3>
                    هنوز متخصصی برای نمایش نداریم.
                </h3>

                <p>
                    به‌زودی متخصص‌های فعال سالن‌های NOBAT اینجا نمایش داده می‌شوند.
                </p>
            </div>

        @endif

    </div>
</section>
