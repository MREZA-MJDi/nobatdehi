<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Carbon\Carbon;
use Illuminate\View\View;

class SalonController extends Controller
{
    public function show(Salon $salon): View
    {
        abort_unless($salon->is_active, 404);

        $canManageSalon =
            auth()->check()
            && auth()->user()->isSalonOwner()
            && (int) $salon->owner_id === (int) auth()->id();

        $salon->load([
            'owner',
            'barbers' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            },
            'services' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },
            'workingHours' => function ($query) {
                $query->orderBy('day_of_week')
                    ->orderBy('sort_order')
                    ->orderBy('start_time');
            },
            'posts' => function ($query) {
                $query->where('is_active', true)
                    ->with(['barber', 'service'])
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->limit(36);
            },
            'reviews' => function ($query) {
                $query->where('is_published', true)
                    ->with(['customer', 'booking.service'])
                    ->latest()
                    ->limit(12);
            },
        ]);

        $postsCount = $salon->posts()->where('is_active', true)->count();
        $reviewsCount = $salon->reviews()->where('is_published', true)->count();
        $barbersCount = $salon->barbers()->where('is_active', true)->count();
        $servicesCount = $salon->services()->where('is_active', true)->count();

        $publicRating = $salon->reviews()
            ->where('is_published', true)
            ->avg('rating');

        $salon->setAttribute(
            'reviews_avg_rating',
            $publicRating !== null ? (float) $publicRating : null
        );

        $relatedSalons = collect();

        if (filled($salon->district)) {
            $relatedSalons = Salon::query()
                ->where('id', '!=', $salon->id)
                ->where('is_active', true)
                ->where('district', $salon->district)
                ->where('city', $salon->city)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg([
                    'reviews' => function ($query) {
                        $query->where('is_published', true);
                    },
                ], 'rating')
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(6)
                ->get();
        }

        if ($relatedSalons->count() < 6 && filled($salon->city)) {
            $existingIds = $relatedSalons->pluck('id')->push($salon->id);

            $citySalons = Salon::query()
                ->whereNotIn('id', $existingIds)
                ->where('is_active', true)
                ->where('city', $salon->city)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg([
                    'reviews' => function ($query) {
                        $query->where('is_published', true);
                    },
                ], 'rating')
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(6 - $relatedSalons->count())
                ->get();

            $relatedSalons = $relatedSalons->concat($citySalons)->values();
        }

        if ($relatedSalons->count() < 6 && filled($salon->province)) {
            $existingIds = $relatedSalons->pluck('id')->push($salon->id);

            $provinceSalons = Salon::query()
                ->whereNotIn('id', $existingIds)
                ->where('is_active', true)
                ->where('province', $salon->province)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg([
                    'reviews' => function ($query) {
                        $query->where('is_published', true);
                    },
                ], 'rating')
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(6 - $relatedSalons->count())
                ->get();

            $relatedSalons = $relatedSalons->concat($provinceSalons)->values();
        }

        $latitude = $salon->latitude;
        $longitude = $salon->longitude;
        $hasLocation = is_numeric($latitude) && is_numeric($longitude);

        $addressParts = collect([
            $salon->province,
            $salon->city,
            $salon->district,
            $salon->address,
        ])->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->values();

        $fullAddress = $addressParts->implode('، ');

        $googleMapsUrl = null;
        $mapsEmbedUrl = null;

        if ($hasLocation) {
            $coordinates = $latitude . ',' . $longitude;
            $googleMapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($coordinates);
            $mapsEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($coordinates) . '&z=16&output=embed';
        } elseif ($fullAddress !== '') {
            $googleMapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($fullAddress);
            $mapsEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($fullAddress) . '&z=16&output=embed';
        }

        $isFavorited = false;

        if (auth()->check() && auth()->user()->isCustomer()) {
            $isFavorited = auth()->user()
                ->favoriteSalons()
                ->whereKey($salon->id)
                ->exists();
        }

        $timezone = config('app.timezone', 'Asia/Tehran');
        $now = now($timezone);
        $today = $now->copy()->startOfDay();
        $todayDow = ($today->dayOfWeek + 1) % 7;

        $todayRows = ($salon->workingHours ?? collect())
            ->where('day_of_week', $todayDow)
            ->values();

        $todayHours = $todayRows
            ->filter(fn ($row) =>
                ! $row->is_closed &&
                filled($row->start_time) &&
                filled($row->end_time)
            )
            ->unique(fn ($row) =>
                substr((string) $row->start_time, 0, 5)
                . '|'
                . substr((string) $row->end_time, 0, 5)
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Canonical public schedule
        |--------------------------------------------------------------------------
        |
        | A break is represented as multiple working intervals. Older/partially
        | migrated data can still contain the former full-day interval alongside
        | the two real segments (e.g. 09-22 + 09-13 + 15-22). The full interval
        | is a stale container in that case and must not leak into the public
        | schedule or "open now" calculation.
        |--------------------------------------------------------------------------
        */

        $todayHours = $todayHours
            ->filter(function ($candidate) use ($todayHours) {
                $candidateStart = substr((string) $candidate->start_time, 0, 5);
                $candidateEnd = substr((string) $candidate->end_time, 0, 5);

                $nestedIntervals = $todayHours->filter(function ($other) use (
                    $candidate,
                    $candidateStart,
                    $candidateEnd
                ) {
                    if ($other === $candidate) {
                        return false;
                    }

                    $otherStart = substr((string) $other->start_time, 0, 5);
                    $otherEnd = substr((string) $other->end_time, 0, 5);

                    return
                        $otherStart >= $candidateStart
                        && $otherEnd <= $candidateEnd
                        && (
                            $otherStart > $candidateStart
                            || $otherEnd < $candidateEnd
                        );
                });

                return $nestedIntervals->count() < 2;
            })
            ->sortBy([
                ['start_time', 'asc'],
                ['end_time', 'asc'],
            ])
            ->values();

        $dailyStatus = $salon->dailyStatuses()
            ->whereDate('date', $today->toDateString())
            ->first();

        $isDailyClosed = (bool) ($dailyStatus?->is_closed);
        $isWeeklyClosed =
            $todayHours->isEmpty() &&
            $todayRows->isNotEmpty() &&
            $todayRows->every(fn ($row) => (bool) $row->is_closed);

        $isOpenToday =
            ! $isDailyClosed &&
            ! $isWeeklyClosed &&
            $todayHours->isNotEmpty();

        $isOpenNow = false;

        if ($isOpenToday) {
            foreach ($todayHours as $todayHour) {
                try {
                    $start = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $today->toDateString() . ' ' . substr((string) $todayHour->start_time, 0, 8),
                        $timezone
                    );
                    $end = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $today->toDateString() . ' ' . substr((string) $todayHour->end_time, 0, 8),
                        $timezone
                    );
                } catch (\Throwable) {
                    continue;
                }

                if ($end->lte($start)) {
                    $end->addDay();
                }

                if ($now->betweenIncluded($start, $end)) {
                    $isOpenNow = true;
                    break;
                }
            }
        }

        $statusText = match (true) {
            $isDailyClosed || $isWeeklyClosed => 'امروز تعطیل',
            $todayHours->isEmpty() => 'ساعات کاری ثبت نشده',
            $isOpenNow => 'الان باز است',
            default => 'امروز باز است',
        };

        $todayHoursText =
            ($isDailyClosed || $isWeeklyClosed)
                ? 'امروز تعطیل'
                : $todayHours->map(function ($hour) {
                    return substr((string) $hour->start_time, 0, 5)
                        . ' تا '
                        . substr((string) $hour->end_time, 0, 5);
                })->join('  •  ');

        if ($todayHoursText === '') {
            $todayHoursText = 'امروز ساعات کاری ثبت نشده';
        }

        /*
        |--------------------------------------------------------------------------
        | Public break display
        |--------------------------------------------------------------------------
        |
        | A break is the real gap between two effective working intervals.
        | Keep this derived from the same canonical interval list used by the
        | open/closed status so the public profile never advertises a break
        | that availability does not enforce.
        |--------------------------------------------------------------------------
        */
        $todayBreaks = [];

        if ($todayHours->count() > 1) {
            $orderedTodayHours = $todayHours->values();

            for ($index = 1, $count = $orderedTodayHours->count(); $index < $count; $index++) {
                $previousEnd = substr(
                    (string) $orderedTodayHours[$index - 1]->end_time,
                    0,
                    5
                );
                $currentStart = substr(
                    (string) $orderedTodayHours[$index]->start_time,
                    0,
                    5
                );

                if ($previousEnd < $currentStart) {
                    $todayBreaks[] = [
                        'start' => $previousEnd,
                        'end' => $currentStart,
                    ];
                }
            }
        }

        // public.salon derives its displayed "today" status from the loaded
        // workingHours relation. A daily close must therefore hide only today's
        // weekly rows so the cover badge cannot contradict the explicit close.
        if ($isDailyClosed) {
            $salon->setRelation(
                'workingHours',
                $salon->workingHours
                    ->reject(fn ($row) => (int) $row->day_of_week === (int) $todayDow)
                    ->values()
            );
        }

        return view(
            'public.salon',
            compact(
                'salon',
                'latitude',
                'longitude',
                'hasLocation',
                'fullAddress',
                'googleMapsUrl',
                'mapsEmbedUrl',
                'relatedSalons',
                'postsCount',
                'reviewsCount',
                'barbersCount',
                'servicesCount',
                'todayHours',
                'isOpenToday',
                'isOpenNow',
                'statusText',
                'todayHoursText',
                'todayBreaks',
                'dailyStatus',
                'isDailyClosed',
                'isFavorited',
                'canManageSalon'
            )
        );
    }
}
