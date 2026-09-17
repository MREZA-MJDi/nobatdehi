<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\UpdateSalonSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View|JsonResponse
    {
        $salon = auth()
            ->user()
            ->managedSalons()
            ->with(['workingHours'])
            ->firstOrFail();

        $workingHours = $salon
            ->workingHours
            ->groupBy('day_of_week');

        if (request()->expectsJson()) {
            $schedule = [];

            for ($day = 0; $day <= 6; $day++) {
                $rows = $workingHours->get($day, collect());
                $closed = $rows->isEmpty()
                    || $rows->contains(fn ($row): bool => (bool) $row->is_closed);

                $intervals = $rows
                    ->filter(fn ($row): bool => !$row->is_closed && $row->start_time && $row->end_time)
                    ->map(fn ($row): array => [
                        'start' => substr((string) $row->start_time, 0, 5),
                        'end' => substr((string) $row->end_time, 0, 5),
                    ])
                    ->values()
                    ->all();

                $schedule[$day] = [
                    'configured' => $rows->isNotEmpty(),
                    'closed' => $closed,
                    'intervals' => $intervals,
                ];
            }

            return response()->json([
                'ok' => true,
                'schedule' => $schedule,
            ]);
        }

        return view(
            'salon.settings.edit',
            compact(
                'salon',
                'workingHours'
            )
        );
    }

    public function update(
        UpdateSalonSettingsRequest $request
    ): RedirectResponse {
        $salon = auth()
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();
        $oldLogoPath = $salon->logo_path;
        $oldCoverPath = $salon->cover_path;
        $newLogoPath = null;
        $newCoverPath = null;

        try {
            if ($request->hasFile('logo')) {
                $newLogoPath = $request
                    ->file('logo')
                    ->store('salons/logos', 'public');
            }

            if ($request->hasFile('cover')) {
                $newCoverPath = $request
                    ->file('cover')
                    ->store('salons/covers', 'public');
            }

            DB::transaction(function () use (
                $request,
                $salon,
                $data,
                $newLogoPath,
                $newCoverPath
            ): void {
                $salonData = [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'primary_color' => $data['primary_color'] ?? $salon->primary_color ?? '#6757E8',
                    'secondary_color' => $data['secondary_color'] ?? $salon->secondary_color ?? '#37B8C8',
                    'province' => $data['province'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'address' => $data['address'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                ];

                if ($newLogoPath) {
                    $salonData['logo_path'] = $newLogoPath;
                } elseif ($request->boolean('remove_logo')) {
                    $salonData['logo_path'] = null;
                }

                if ($newCoverPath) {
                    $salonData['cover_path'] = $newCoverPath;
                } elseif ($request->boolean('remove_cover')) {
                    $salonData['cover_path'] = null;
                }

                $salon->update($salonData);

                $workingHours = $data['working_hours'] ?? [];
                $this->replaceWorkingHours($salon, $workingHours);
            });

            $logoWasReplaced = $newLogoPath !== null;
            $logoWasRemoved = $request->boolean('remove_logo') && !$logoWasReplaced;

            if (
                $oldLogoPath &&
                ($logoWasReplaced || $logoWasRemoved)
            ) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            $coverWasReplaced = $newCoverPath !== null;
            $coverWasRemoved = $request->boolean('remove_cover') && !$coverWasReplaced;

            if (
                $oldCoverPath &&
                ($coverWasReplaced || $coverWasRemoved)
            ) {
                Storage::disk('public')->delete($oldCoverPath);
            }

            return redirect()
                ->route('salon.settings.edit')
                ->with('success', 'تنظیمات سالن با موفقیت ذخیره شد.');
        } catch (\Throwable $e) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            if ($newCoverPath) {
                Storage::disk('public')->delete($newCoverPath);
            }

            throw $e;
        }
    }

    private function replaceWorkingHours(
        $salon,
        array $workingHours
    ): void {
        $salon
            ->workingHours()
            ->delete();

        foreach ($workingHours as $dayOfWeek => $dayData) {
            $dayOfWeek = (int) $dayOfWeek;

            if ($dayOfWeek < 0 || $dayOfWeek > 6) {
                continue;
            }

            if ($this->isWorkingHourRow($dayData)) {
                $this->createWorkingHour(
                    $salon,
                    $dayOfWeek,
                    0,
                    $dayData
                );

                continue;
            }

            if (!is_array($dayData)) {
                continue;
            }

            foreach ($dayData as $sortOrder => $interval) {
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

    private function isWorkingHourRow(
        mixed $value
    ): bool {
        if (!is_array($value)) {
            return false;
        }

        return (
            array_key_exists('start_time', $value) ||
            array_key_exists('end_time', $value) ||
            array_key_exists('is_closed', $value)
        );
    }

    private function createWorkingHour(
        $salon,
        int $dayOfWeek,
        int $sortOrder,
        array $interval
    ): void {
        $isClosed = !empty($interval['is_closed'] ?? false);
        $startTime = $interval['start_time'] ?? null;
        $endTime = $interval['end_time'] ?? null;

        if ($isClosed) {
            if ($sortOrder !== 0) {
                return;
            }

            $salon
                ->workingHours()
                ->create([
                    'day_of_week' => $dayOfWeek,
                    'start_time' => null,
                    'end_time' => null,
                    'is_closed' => true,
                    'sort_order' => 0,
                ]);

            return;
        }

        if (empty($startTime) || empty($endTime)) {
            return;
        }

        $salon
            ->workingHours()
            ->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_closed' => false,
                'sort_order' => $sortOrder,
            ]);
    }
}
