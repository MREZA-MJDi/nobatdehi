<section class="discover-section">

    <div class="discover-container">

        <div class="discover-owner-cta">

            <div>

                <span class="discover-kicker">
                    برای صاحبان سالن
                </span>

                <h2>
                    صاحب سالن هستی؟
                </h2>

                <p>
                    NOBAT کمک می‌کند سالن تو راحت‌تر پیدا شود
                    و مشتری‌ها مسیر رزرو ساده‌تری داشته باشند.
                </p>

            </div>


            <div class="discover-end-salon-strip" aria-label="چند سالن فعال NOBAT">
                @foreach(($popularSalons ?? collect())->take(4) as $salon)
                    @php
                        $image = ($salon->cover_url ?? null)
                            ?: $resolveImage($salon->cover_path ?? null)
                            ?: $resolveImage($salon->logo_path ?? null);
                    @endphp

                    <a
                        href="{{ route('public.salons.show', $salon) }}"
                        class="discover-end-salon"
                        title="{{ $salon->name }}"
                    >
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $salon->name }}" loading="lazy" decoding="async">
                        @else
                            <span>{{ mb_substr(trim($salon->name), 0, 1) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <a
                href="{{ route('login') }}"
                class="discover-primary-button"
            >
                شروع کار
                <span aria-hidden="true">
                    ←
                </span>
            </a>

        </div>

    </div>

</section>
