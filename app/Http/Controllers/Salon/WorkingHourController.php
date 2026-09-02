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
            ->get()
            ->keyBy('day_of_week');

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

            foreach ($data['hours'] as $hour) {

                $isClosed = (bool) $hour['is_closed'];

                $salon->workingHours()->create([
                    'day_of_week' =>
                        $hour['day_of_week'],

                    'start_time' =>
                        $isClosed
                            ? null
                            : $hour['start_time'],

                    'end_time' =>
                        $isClosed
                            ? null
                            : $hour['end_time'],

                    'is_closed' =>
                        $isClosed,

                    'sort_order' =>
                        $hour['day_of_week'],
                ]);
            }
        });

        return back()
            ->with(
                'success',
                'ساعات کاری سالن با موفقیت ذخیره شد.'
            );
    }
}
