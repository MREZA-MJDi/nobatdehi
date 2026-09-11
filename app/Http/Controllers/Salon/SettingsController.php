<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\UpdateSalonSettingsRequest;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display salon settings.
     */
    public function edit(): View
    {
        $salon = auth()
            ->user()
            ->managedSalons()
            ->with([
                'workingHours',
            ])
            ->firstOrFail();

        $workingHours = $salon
            ->workingHours
            ->groupBy('day_of_week');

        return view(
            'salon.settings.edit',
            compact(
                'salon',
                'workingHours'
            )
        );
    }

    /**
     * Update salon settings.
     */
    public function update(
        UpdateSalonSettingsRequest $request
    ): RedirectResponse {
        $salon = auth()
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Old Files
        |--------------------------------------------------------------------------
        */

        $oldLogoPath = $salon->logo_path;
        $oldCoverPath = $salon->cover_path;

        /*
        |--------------------------------------------------------------------------
        | New Files
        |--------------------------------------------------------------------------
        */

        $newLogoPath = null;
        $newCoverPath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {
                $newLogoPath = $request
                    ->file('logo')
                    ->store(
                        'salons/logos',
                        'public'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Upload Cover
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cover')) {
                $newCoverPath = $request
                    ->file('cover')
                    ->store(
                        'salons/covers',
                        'public'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Database
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $request,
                $salon,
                $data,
                $newLogoPath,
                $newCoverPath
            ) {

                /*
                |--------------------------------------------------------------------------
                | Salon Information
                |--------------------------------------------------------------------------
                */

                $salonData = [
                    'name' => $data['name'],

                    'description' =>
                        $data['description'] ?? null,

                    'phone' =>
                        $data['phone'] ?? null,

                    'email' =>
                        $data['email'] ?? null,

                    'primary_color' =>
                        $data['primary_color']
                        ?? '#6757E8',

                    'secondary_color' =>
                        $data['secondary_color']
                        ?? '#37B8C8',

                    'province' =>
                        $data['province'] ?? null,

                    'city' =>
                        $data['city'] ?? null,

                    'district' =>
                        $data['district'] ?? null,

                    'address' =>
                        $data['address'] ?? null,

                    'latitude' =>
                        $data['latitude'] ?? null,

                    'longitude' =>
                        $data['longitude'] ?? null,
                ];

                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                if ($request->boolean('remove_logo')) {

                    $salonData['logo_path'] = null;

                } elseif ($newLogoPath) {

                    $salonData['logo_path'] =
                        $newLogoPath;
                }

                /*
                |--------------------------------------------------------------------------
                | Cover
                |--------------------------------------------------------------------------
                */

                if ($request->boolean('remove_cover')) {

                    $salonData['cover_path'] = null;

                } elseif ($newCoverPath) {

                    $salonData['cover_path'] =
                        $newCoverPath;
                }

                /*
                |--------------------------------------------------------------------------
                | Update Salon
                |--------------------------------------------------------------------------
                */

                $salon->update($salonData);

                /*
                |--------------------------------------------------------------------------
                | Working Hours
                |--------------------------------------------------------------------------
                |
                | Structure:
                |
                | working_hours[0][0][start_time]
                | working_hours[0][0][end_time]
                | working_hours[0][0][is_closed]
                |
                | working_hours[0][1] => second interval
                |
                |--------------------------------------------------------------------------
                */

                $workingHours =
                    $data['working_hours'] ?? [];

                $salon
                    ->workingHours()
                    ->delete();

                foreach (
                    $workingHours as $dayOfWeek => $intervals
                ) {

                    foreach (
                        $intervals as $sortOrder => $interval
                    ) {

                        $isClosed =
                            !empty(
                            $interval['is_closed']
                            );

                        $startTime =
                            $interval['start_time']
                            ?? null;

                        $endTime =
                            $interval['end_time']
                            ?? null;

                        /*
                        |--------------------------------------------------------------------------
                        | Closed Day
                        |--------------------------------------------------------------------------
                        */

                        if ($isClosed) {

                            if ($sortOrder === 0) {

                                $salon
                                    ->workingHours()
                                    ->create([
                                        'day_of_week' =>
                                            (int) $dayOfWeek,

                                        'start_time' =>
                                            null,

                                        'end_time' =>
                                            null,

                                        'is_closed' =>
                                            true,

                                        'sort_order' =>
                                            0,
                                    ]);
                            }

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Empty Interval
                        |--------------------------------------------------------------------------
                        */

                        if (
                            empty($startTime) ||
                            empty($endTime)
                        ) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Open Interval
                        |--------------------------------------------------------------------------
                        */

                        $salon
                            ->workingHours()
                            ->create([
                                'day_of_week' =>
                                    (int) $dayOfWeek,

                                'start_time' =>
                                    $startTime,

                                'end_time' =>
                                    $endTime,

                                'is_closed' =>
                                    false,

                                'sort_order' =>
                                    (int) $sortOrder,
                            ]);
                    }
                }
            });

            /*
            |--------------------------------------------------------------------------
            | Delete Old Logo
            |--------------------------------------------------------------------------
            */

            if (
                (
                    $request->boolean('remove_logo') ||
                    $newLogoPath
                ) &&
                $oldLogoPath
            ) {
                Storage::disk('public')
                    ->delete($oldLogoPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Old Cover
            |--------------------------------------------------------------------------
            */

            if (
                (
                    $request->boolean('remove_cover') ||
                    $newCoverPath
                ) &&
                $oldCoverPath
            ) {
                Storage::disk('public')
                    ->delete($oldCoverPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('salon.settings.edit')
                ->with(
                    'success',
                    'تنظیمات سالن با موفقیت ذخیره شد.'
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup New Files
            |--------------------------------------------------------------------------
            */

            if ($newLogoPath) {
                Storage::disk('public')
                    ->delete($newLogoPath);
            }

            if ($newCoverPath) {
                Storage::disk('public')
                    ->delete($newCoverPath);
            }

            throw $e;
        }
    }
}
