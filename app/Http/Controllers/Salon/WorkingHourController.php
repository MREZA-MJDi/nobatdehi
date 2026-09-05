<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\WorkingHourRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkingHourController extends Controller
{
    public function edit(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $hours = $salon
            ->workingHours()
            ->orderBy('day_of_week')
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view(
            'salon.working-hours.edit',
            compact(
                'salon',
                'hours'
            )
        );
    }

    public function update(
        WorkingHourRequest $request
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();

        DB::transaction(function () use (
            $salon,
            $data
        ) {
            $salon
                ->workingHours()
                ->delete();

            foreach ($data['hours'] as $day) {
                $dayOfWeek = (int) $day['day_of_week'];

                $isClosed = filter_var(
                    $day['is_closed'],
                    FILTER_VALIDATE_BOOLEAN
                );

                /*
                |--------------------------------------------------------------------------
                | Closed day
                |--------------------------------------------------------------------------
                |
                | We intentionally keep one DB row for a closed day.
                | This makes the weekly schedule predictable.
                |
                */

                if ($isClosed) {
                    $salon->workingHours()->create([
                        'day_of_week' => $dayOfWeek,
                        'start_time' => null,
                        'end_time' => null,
                        'is_closed' => true,
                        'sort_order' => 0,
                    ]);

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Open day
                |--------------------------------------------------------------------------
                */

                $intervals = $day['intervals'] ?? [];

                foreach (
                    array_values($intervals)
                    as $sortOrder => $interval
                ) {
                    $salon->workingHours()->create([
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
            'ساعات کاری سالن با موفقیت ذخیره شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Optional default schedule
    |--------------------------------------------------------------------------
    |
    | This method is kept for compatibility with the existing route.
    | The new UI applies defaults on the page itself.
    |
    */

    public function applyDefault(
        Request $request
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $defaults = [
            0 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            1 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            2 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            3 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            4 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            5 => [
                [
                    'start_time' => '09:00',
                    'end_time' => '22:00',
                ],
            ],

            6 => [],
        ];

        DB::transaction(function () use (
            $salon,
            $defaults
        ) {
            $salon
                ->workingHours()
                ->delete();

            foreach ($defaults as $dayOfWeek => $intervals) {
                if (empty($intervals)) {
                    $salon->workingHours()->create([
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
            'برنامه پیش‌فرض هفتگی با موفقیت اعمال شد.'
        );
    }
}
