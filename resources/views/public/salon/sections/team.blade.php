<section
    id="team"
    class="salon-section salon-team"
>
    <div class="salon-section-header">

        <div>
            <span class="salon-section-kicker">
                THE TEAM
            </span>

            <h2>
                آدم‌هایی که
                <span>استایل را می‌سازند.</span>
            </h2>

            <p>
                با متخصص‌های این سالن آشنا شو؛ هر کدام سبک و تخصص خودش را دارد.
            </p>
        </div>


        <div class="salon-team-count">

            <strong>
                {{ number_format($barbers->count()) }}
            </strong>

            <span>
                متخصص فعال
            </span>

        </div>

    </div>


    @if($barbers->isNotEmpty())

        <div class="salon-team-grid">

            @foreach($barbers as $barber)

                @php
                    $barberInitial = mb_substr(
                        trim($barber->name),
                        0,
                        1
                    );

                    $specialty =
                        $barber->specialty
                        ?: 'متخصص سالن';

                    $bio =
                        $barber->bio
                        ? \Illuminate\Support\Str::limit(
                            strip_tags($barber->bio),
                            115
                        )
                        : 'برای مشاهده خدمات و زمان‌های قابل رزرو این متخصص، نوبت خود را انتخاب کن.';
                @endphp


                <article
                    class="salon-player-card"
                >

                    {{-- =================================================
                        VISUAL AREA
                    ================================================== --}}

                    <div class="salon-player-visual">

                        {{-- Background --}}
                        <div
                            class="salon-player-background"
                            aria-hidden="true"
                        ></div>


                        {{-- Portrait --}}
                        <div class="salon-player-portrait">

                            @if($barber->image_path)

                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($barber->image_path) }}"
                                    alt="{{ $barber->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >

                            @else

                                <div class="salon-player-placeholder">
                                    <span>
                                        {{ $barberInitial }}
                                    </span>
                                </div>

                            @endif

                        </div>


                        {{-- Player number --}}
                        <span class="salon-player-number">
                            #{{ sprintf('%02d', $loop->iteration) }}
                        </span>


                        {{-- Type --}}
                        <span class="salon-player-role">
                            SPECIALIST
                        </span>


                        {{-- Decorative rating mark --}}
                        <div
                            class="salon-player-sigil"
                            aria-hidden="true"
                        >
                            N
                        </div>

                    </div>


                    {{-- =================================================
                        PLAYER INFO
                    ================================================== --}}

                    <div class="salon-player-body">

                        <div class="salon-player-heading">

                            <div>

                                <span class="salon-player-kicker">
                                    NOBAT / TEAM
                                </span>

                                <h3>
                                    {{ $barber->name }}
                                </h3>

                            </div>


                            <span class="salon-player-status">
                                ACTIVE
                            </span>

                        </div>


                        <div class="salon-player-specialty">
                            {{ $specialty }}
                        </div>


                        {{-- =================================================
                            ABILITIES
                        ================================================== --}}

                        <div class="salon-player-abilities">

                            <div class="salon-player-ability">

                                <div class="salon-player-ability-top">
                                    <span>
                                        تخصص
                                    </span>

                                    <strong>
                                        {{ $specialty }}
                                    </strong>
                                </div>

                                <div class="salon-player-bar">
                                    <span style="width: 92%"></span>
                                </div>

                            </div>


                            <div class="salon-player-ability">

                                <div class="salon-player-ability-top">
                                    <span>
                                        تجربه
                                    </span>

                                    <strong>
                                        تیم سالن
                                    </strong>
                                </div>

                                <div class="salon-player-bar">
                                    <span style="width: 86%"></span>
                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            BIO
                        ================================================== --}}

                        <p class="salon-player-bio">
                            {{ $bio }}
                        </p>


                        {{-- =================================================
                            ACTIONS
                        ================================================== --}}

                        <div class="salon-player-actions">

                            @if($salon->is_active)

                                <button
                                    type="button"
                                    class="salon-player-book-button"
                                    @click="
                                        window.dispatchEvent(
                                            new CustomEvent(
                                                'open-booking'
                                            )
                                        )
                                    "
                                >
                                    رزرو با این متخصص

                                    <span aria-hidden="true">
                                        ←
                                    </span>
                                </button>

                            @else

                                <span class="salon-player-disabled">
                                    رزرو موقتاً غیرفعال است
                                </span>

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </div>


    @else

        <div class="salon-empty-state">

            <div
                class="salon-empty-icon"
                aria-hidden="true"
            >
                ✦
            </div>

            <div>

                <h3>
                    هنوز متخصص فعالی ثبت نشده
                </h3>

                <p>
                    به‌زودی اعضای تیم این سالن در این بخش نمایش داده می‌شوند.
                </p>

            </div>

        </div>

    @endif

</section>
