<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\UpdateSalonSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Salon Settings
    |--------------------------------------------------------------------------
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


    /*
    |--------------------------------------------------------------------------
    | Update Salon Settings
    |--------------------------------------------------------------------------
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
        | Existing Files
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
            | Store New Logo
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
            | Store New Cover
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
            | Database Transaction
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $request,
                    $salon,
                    $data,
                    $newLogoPath,
                    $newCoverPath
                ): void {

                    /*
                    |--------------------------------------------------------------------------
                    | Salon Information
                    |--------------------------------------------------------------------------
                    */

                    $salonData = [
                        'name' =>
                            $data['name'],

                        'description' =>
                            $data['description'] ?? null,

                        'phone' =>
                            $data['phone'] ?? null,

                        'email' =>
                            $data['email'] ?? null,

                        /*
                        |--------------------------------------------------------------------------
                        | Preserve existing colors when fields are omitted.
                        |--------------------------------------------------------------------------
                        */

                        'primary_color' =>
                            $data['primary_color']
                            ?? $salon->primary_color
                            ?? '#6757E8',

                        'secondary_color' =>
                            $data['secondary_color']
                            ?? $salon->secondary_color
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
                    |
                    | New file has priority over remove_logo.
                    |
                    */

                    if ($newLogoPath) {

                        $salonData['logo_path'] =
                            $newLogoPath;

                    } elseif (
                        $request->boolean(
                            'remove_logo'
                        )
                    ) {

                        $salonData['logo_path'] =
                            null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Cover
                    |--------------------------------------------------------------------------
                    |
                    | New file has priority over remove_cover.
                    |
                    */

                    if ($newCoverPath) {

                        $salonData['cover_path'] =
                            $newCoverPath;

                    } elseif (
                        $request->boolean(
                            'remove_cover'
                        )
                    ) {

                        $salonData['cover_path'] =
                            null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Salon
                    |--------------------------------------------------------------------------
                    */

                    $salon->update(
                        $salonData
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Working Hours
                    |--------------------------------------------------------------------------
                    */

                    $workingHours =
                        $data['working_hours']
                        ?? [];

                    $this->replaceWorkingHours(
                        $salon,
                        $workingHours
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Delete Old Logo
            |--------------------------------------------------------------------------
            */

            $logoWasReplaced =
                $newLogoPath !== null;

            $logoWasRemoved =
                $request->boolean(
                    'remove_logo'
                ) &&
                !$logoWasReplaced;

            if (
                $oldLogoPath &&
                (
                    $logoWasReplaced ||
                    $logoWasRemoved
                )
            ) {
                Storage::disk('public')
                    ->delete(
                        $oldLogoPath
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Old Cover
            |--------------------------------------------------------------------------
            */

            $coverWasReplaced =
                $newCoverPath !== null;

            $coverWasRemoved =
                $request->boolean(
                    'remove_cover'
                ) &&
                !$coverWasReplaced;

            if (
                $oldCoverPath &&
                (
                    $coverWasReplaced ||
                    $coverWasRemoved
                )
            ) {
                Storage::disk('public')
                    ->delete(
                        $oldCoverPath
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'salon.settings.edit'
                )
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
                    ->delete(
                        $newLogoPath
                    );
            }

            if ($newCoverPath) {
                Storage::disk('public')
                    ->delete(
                        $newCoverPath
                    );
            }

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Replace Working Hours
    |--------------------------------------------------------------------------
    */

    private function replaceWorkingHours(
        $salon,
        array $workingHours
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Current UI sends:
        |
        | working_hours[day][start_time]
        | working_hours[day][end_time]
        | working_hours[day][is_closed]
        |
        | But this method also accepts:
        |
        | working_hours[day][interval][start_time]
        | working_hours[day][interval][end_time]
        | working_hours[day][interval][is_closed]
        |--------------------------------------------------------------------------
        */

        $salon
            ->workingHours()
            ->delete();


        foreach (
            $workingHours as $dayOfWeek => $dayData
        ) {

            $dayOfWeek = (int) $dayOfWeek;


            /*
            |--------------------------------------------------------------------------
            | Ignore invalid weekday indexes.
            |--------------------------------------------------------------------------
            */

            if (
                $dayOfWeek < 0 ||
                $dayOfWeek > 6
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Flat structure
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | [
            |     0 => [
            |         'start_time' => '09:00',
            |         'end_time' => '21:00',
            |         'is_closed' => false,
            |     ]
            | ]
            |
            */

            if (
                $this->isWorkingHourRow(
                    $dayData
                )
            ) {
                $this->createWorkingHour(
                    $salon,
                    $dayOfWeek,
                    0,
                    $dayData
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Nested structure
            |--------------------------------------------------------------------------
            */

            if (!is_array($dayData)) {
                continue;
            }


            foreach (
                $dayData as $sortOrder => $interval
            ) {

                if (!is_array($interval)) {
                    continue;
                }

                $this->createWorkingHour(
                    $salon,
                    $dayOfWeek,
                    (int) $sortOrder,
                    $interval
                );
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Detect Flat Working Hour Row
    |--------------------------------------------------------------------------
    */

    private function isWorkingHourRow(
        mixed $value
    ): bool {
        if (!is_array($value)) {
            return false;
        }

        return (
            array_key_exists(
                'start_time',
                $value
            ) ||
            array_key_exists(
                'end_time',
                $value
            ) ||
            array_key_exists(
                'is_closed',
                $value
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Working Hour
    |--------------------------------------------------------------------------
    */

    private function createWorkingHour(
        $salon,
        int $dayOfWeek,
        int $sortOrder,
        array $interval
    ): void {

        $isClosed =
            !empty(
                $interval['is_closed']
                ?? false
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

            /*
            |--------------------------------------------------------------------------
            | Only one closed row is needed for a day.
            |--------------------------------------------------------------------------
            */

            if ($sortOrder !== 0) {
                return;
            }

            $salon
                ->workingHours()
                ->create([
                    'day_of_week' =>
                        $dayOfWeek,

                    'start_time' =>
                        null,

                    'end_time' =>
                        null,

                    'is_closed' =>
                        true,

                    'sort_order' =>
                        0,
                ]);

            return;
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
            return;
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
                    $dayOfWeek,

                'start_time' =>
                    $startTime,

                'end_time' =>
                    $endTime,

                'is_closed' =>
                    false,

                'sort_order' =>
                    $sortOrder,
            ]);
    }
}
