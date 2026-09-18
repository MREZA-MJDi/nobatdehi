<section
    id="salons"
    class="discover-section discover-salons"
>
    <div class="discover-container">

        <x-discover.section-heading
            eyebrow="سالن‌ها"
            title="سالن مناسب خودت را پیدا کن."
            description="بین سالن‌های فعال بگرد، جزئیات را ببین و انتخابت را با خیال راحت انجام بده."
            :link="route('salons.discover')"
            linkText="مشاهده همه سالن‌ها"
        />


        {{-- Nearby / Recommended --}}
        @if($nearbySalons->isNotEmpty())

            <div class="discover-subsection">

                <div class="discover-subsection-head">
                    <div>
                        <span class="discover-eyebrow">
                            پیشنهادهای نزدیک
                        </span>

                        <h3>
                            سالن‌هایی که ارزش دیدن دارند
                        </h3>
                    </div>
                </div>

                <div class="discover-salon-grid">

                    @foreach($nearbySalons as $salon)
                        <x-discover.salon-card
                            :salon="$salon"
                        />
                    @endforeach

                </div>

            </div>

        @endif


        {{-- Main salon discovery --}}
        <div class="discover-subsection discover-all-salons">

            <div class="discover-subsection-head">
                <div>
                    <span class="discover-eyebrow">
                        انتخاب‌های بیشتر
                    </span>

                    <h3>
                        سالن‌های فعال NOBAT
                    </h3>
                </div>

                <a
                    href="{{ route('salons.discover') }}"
                    class="discover-text-link"
                >
                    همه سالن‌ها
                    <span aria-hidden="true">←</span>
                </a>
            </div>


            @if($salons->isNotEmpty())

                <div class="discover-salon-grid">

                    @foreach($salons as $salon)
                        <x-discover.salon-card
                            :salon="$salon"
                        />
                    @endforeach

                </div>

                @if($salons->hasPages())
                    <div class="discover-pagination">
                        {{ $salons->links() }}
                    </div>
                @endif

            @else

                <div class="discover-empty">
                    <h3>
                        سالنی پیدا نشد.
                    </h3>

                    <p>
                        عبارت جستجو را تغییر بده یا همه سالن‌ها را مشاهده کن.
                    </p>

                    <a
                        href="{{ route('salons.discover') }}"
                        class="discover-primary-button"
                    >
                        <span>
                            مشاهده همه سالن‌ها
                        </span>

                        <span aria-hidden="true">
                            ←
                        </span>
                    </a>
                </div>

            @endif

        </div>

    </div>
</section>
