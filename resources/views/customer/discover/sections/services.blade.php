<section
    id="services"
    class="discover-section"
>
    <div class="discover-container">

        <x-discover.section-heading
            eyebrow="خدمات"
            title="دنبال چه خدمتی هستی؟"
            description="یک خدمت را انتخاب کن تا سالن‌هایی که آن را ارائه می‌دهند پیدا کنی."
        />

        @if($popularServices->isNotEmpty())

            <div class="discover-service-grid">

                @foreach($popularServices as $service)
                    <x-discover.service-card
                        :service="$service"
                    />
                @endforeach

            </div>

        @else

            <div class="discover-empty">
                <h3>
                    هنوز خدمتی برای نمایش نداریم.
                </h3>

                <p>
                    به‌زودی خدمات مختلف سالن‌های NOBAT اینجا نمایش داده می‌شوند.
                </p>
            </div>

        @endif

    </div>
</section>
