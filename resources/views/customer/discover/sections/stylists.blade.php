<section
    class="discover-section discover-team-section"
    id="stylists"
>
    <div class="discover-container">
        <div class="discover-section-heading discover-section-heading-inline">
            <div>
                <span class="discover-kicker">
                    تیم سالن
                </span>
                <h2>
                    تیم‌های واقعی هر سالن
                </h2>
                <p>
                    چند عضو از تیم را کوچک و جمع‌وجور می‌بینی؛ برای رزرو، وارد همان سالن شو.
                </p>
            </div>

            <a
                href="{{ route('salons.discover', ['type' => 'barber']) }}#results"
                class="discover-section-link"
            >
                همه متخصص‌ها
                ←
            </a>
        </div>

        <div class="discover-team-grid">
            @forelse($stylists->groupBy('salon_id') as $salonId => $team)
                @php
                    $salon = $team->first()?->salon;
                    $salonName = $salon?->name ?: 'سالن NOBAT';
                    $salonUrl = $salon ? route('public.salons.show', $salon) : route('salons.discover', ['type' => 'barber']);

                    $teamImages = $team->take(5);
                @endphp

                <a href="{{ $salonUrl }}" class="discover-team-card">
                    <div class="discover-team-head">
                        <div class="discover-team-copy">
                            <span>تیم سالن</span>
                            <strong>{{ $salonName }}</strong>
                            <small>{{ number_format($team->count()) }} متخصص فعال</small>
                        </div>

                        <span class="discover-team-arrow" aria-hidden="true">←</span>
                    </div>

                    <div class="discover-team-avatars" aria-label="اعضای تیم سالن">
                        @foreach($teamImages as $member)
                            @if($member->image_path)
                                <img
                                    src="{{ $resolveImage($member->image_path) }}"
                                    alt="{{ $member->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <span class="discover-team-avatar-fallback" aria-hidden="true">
                                    {{ mb_substr(trim($member->name), 0, 1) }}
                                </span>
                            @endif
                        @endforeach
                    </div>

                    <div class="discover-team-members">
                        @foreach($teamImages->take(3) as $member)
                            <span>{{ $member->name }}</span>
                        @endforeach
                        @if($team->count() > 3)
                            <span>+{{ number_format($team->count() - 3) }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="discover-empty-inline">
                    هنوز تیم فعالی برای نمایش وجود ندارد.
                </div>
            @endforelse
        </div>
    </div>
</section>