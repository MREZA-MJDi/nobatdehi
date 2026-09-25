<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\WorkingHourRequest;
use App\Models\Barber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkingHourController extends Controller
{
    public function edit(Request $request): View
    {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $barbers = $salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'specialty',
            ]);

        $rows = $salon
            ->workingHours()
            ->orderBy('day_of_week')
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        $salonRows = $rows
            ->filter(fn ($row) => $row->barber_id === null)
            ->values();

        $schedules = [
            'salon' => [
                'type' => 'salon',
                'name' => $salon->name,
                'inherited' => false,
                'hours' => $this->scheduleFromRows($salonRows),
            ],
        ];

        foreach ($barbers as $barber) {
            $barberRows = $rows
                ->filter(fn ($row) => (int) $row->barber_id === (int) $barber->id)
                ->values();

            $hasCustomSchedule = $barberRows->isNotEmpty();

            $schedules[(string) $barber->id] = [
                'type' => 'barber',
                'id' => (int) $barber->id,
                'name' => $barber->name,
                'specialty' => $barber->specialty,
                'inherited' => !$hasCustomSchedule,
                'hours' => $this->scheduleFromRows(
                    $hasCustomSchedule ? $barberRows : $salonRows
                ),
            ];
        }

        $selectedBarberId = $request->old(
            'barber_id',
            $request->query('barber_id')
        );

        $selectedScope = $selectedBarberId !== null && $selectedBarberId !== ''
            ? (string) $selectedBarberId
            : 'salon';

        $oldHours = $request->old('hours');

        if (is_array($oldHours)) {
            $normalizedOldHours = [];

            foreach (range(0, 6) as $day) {
                $oldDay = $oldHours[$day] ?? [];
                $intervals = [];

                foreach (($oldDay['intervals'] ?? []) as $interval) {
                    $intervals[] = [
                        'start' => $interval['start_time'] ?? '',
                        'end' => $interval['end_time'] ?? '',
                    ];
                }

                $normalizedOldHours[(string) $day] = [
                    'closed' => filter_var(
                        $oldDay['is_closed'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    ),
                    'intervals' => $intervals,
                ];
            }

            if (isset($schedules[$selectedScope])) {
                $schedules[$selectedScope]['hours'] = $normalizedOldHours;
                $schedules[$selectedScope]['inherited'] = false;
            }
        }

        return view(
            'salon.working-hours.edit',
            compact(
                'salon',
                'barbers',
                'schedules',
                'selectedScope'
            )
        );
    }

    public function update(WorkingHourRequest $request): RedirectResponse
    {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();

        $barberId = isset($data['barber_id']) && $data['barber_id'] !== ''
            ? (int) $data['barber_id']
            : null;

        DB::transaction(function () use ($salon, $data, $barberId): void {
            $salon
                ->workingHours()
                ->where('barber_id', $barberId)
                ->delete();

            foreach ($data['hours'] as $day) {
                $dayOfWeek = (int) $day['day_of_week'];
                $isClosed = filter_var(
                    $day['is_closed'],
                    FILTER_VALIDATE_BOOLEAN
                );

                if ($isClosed) {
                    $salon->workingHours()->create([
                        'barber_id' => $barberId,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => null,
                        'end_time' => null,
                        'is_closed' => true,
                        'sort_order' => 0,
                    ]);

                    continue;
                }

                foreach (array_values($day['intervals'] ?? []) as $sortOrder => $interval) {
                    $salon->workingHours()->create([
                        'barber_id' => $barberId,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $interval['start_time'],
                        'end_time' => $interval['end_time'],
                        'is_closed' => false,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        });

        return back()->with(
            'success',
            $barberId === null
                ? 'ساعات کاری کلی سالن با موفقیت ذخیره شد.'
                : 'ساعات کاری این آرایشگر با موفقیت ذخیره شد.'
        );
    }

    public function applyDefault(Request $request): RedirectResponse
    {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $barberId = $request->filled('barber_id')
            ? (int) $request->input('barber_id')
            : null;

        if ($barberId !== null) {
            Barber::query()
                ->whereKey($barberId)
                ->where('salon_id', $salon->id)
                ->firstOrFail();
        }

        $defaults = [
            0 => [['start_time' => '09:00', 'end_time' => '22:00']],
            1 => [['start_time' => '09:00', 'end_time' => '22:00']],
            2 => [['start_time' => '09:00', 'end_time' => '22:00']],
            3 => [['start_time' => '09:00', 'end_time' => '22:00']],
            4 => [['start_time' => '09:00', 'end_time' => '22:00']],
            5 => [['start_time' => '09:00', 'end_time' => '22:00']],
            6 => [],
        ];

        DB::transaction(function () use ($salon, $barberId, $defaults): void {
            $salon
                ->workingHours()
                ->where('barber_id', $barberId)
                ->delete();

            foreach ($defaults as $dayOfWeek => $intervals) {
                if ($intervals === []) {
                    $salon->workingHours()->create([
                        'barber_id' => $barberId,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => null,
                        'end_time' => null,
                        'is_closed' => true,
                        'sort_order' => 0,
                    ]);

                    continue;
                }

                foreach ($intervals as $sortOrder => $interval) {
                    $salon->workingHours()->create([
                        'barber_id' => $barberId,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $interval['start_time'],
                        'end_time' => $interval['end_time'],
                        'is_closed' => false,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        });

        return back()->with(
            'success',
            $barberId === null
                ? 'برنامه پیش‌فرض سالن با موفقیت اعمال شد.'
                : 'برنامه پیش‌فرض برای این آرایشگر با موفقیت اعمال شد.'
        );
    }

    private function scheduleFromRows($rows): array
    {
        $schedule = [];

        foreach (range(0, 6) as $day) {
            $dayRows = $rows
                ->where('day_of_week', $day)
                ->values();

            $closedRow = $dayRows->first(
                fn ($row) => $row->is_closed
            );

            if ($closedRow) {
                $schedule[(string) $day] = [
                    'closed' => true,
                    'intervals' => [],
                ];

                continue;
            }

            $intervals = $dayRows
                ->filter(
                    fn ($row) =>
                        !$row->is_closed &&
                        $row->start_time &&
                        $row->end_time
                )
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['start_time', 'asc'],
                ])
                ->map(
                    fn ($row) => [
                        'start' => substr((string) $row->start_time, 0, 5),
                        'end' => substr((string) $row->end_time, 0, 5),
                    ]
                )
                ->values()
                ->all();

            /*
             * No row means no configured availability for this day.
             * Treat it as closed in the editor instead of showing a misleading
             * "open" day with an impossible empty schedule.
             */
            $schedule[(string) $day] = [
                'closed' => $dayRows->isEmpty() || $intervals === [],
                'intervals' => $intervals,
            ];
        }

        return $schedule;
    }
}
