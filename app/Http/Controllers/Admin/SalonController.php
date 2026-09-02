<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalonRequest;
use App\Http\Requests\Admin\UpdateSalonRequest;
use App\Models\Salon;
use App\Models\User;
use F9WebLtd\QrCode\Facades\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalonController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $salons = Salon::query()
            ->with('owner')
            ->latest('id')
            ->paginate(15);

        return view(
            'admin.salons.index',
            compact('salons')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'admin.salons.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreSalonRequest $request
    ): RedirectResponse {
        $data = $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Public Identity
        |--------------------------------------------------------------------------
        */

        $slug = $this->generateUniqueSlug(
            $data['name']
        );

        $code = $this->generateUniqueCode();


        /*
        |--------------------------------------------------------------------------
        | Uploaded Files
        |--------------------------------------------------------------------------
        */

        $logoPath = null;
        $coverPath = null;


        try {

            /*
            |--------------------------------------------------------------------------
            | Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {
                $logoPath = $request
                    ->file('logo')
                    ->store(
                        'salons/logos',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Cover
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cover')) {
                $coverPath = $request
                    ->file('cover')
                    ->store(
                        'salons/covers',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Owner + Salon
            |--------------------------------------------------------------------------
            */

            $salon = DB::transaction(function () use (
                $data,
                $slug,
                $code,
                $logoPath,
                $coverPath
            ) {

                /*
                |--------------------------------------------------------------------------
                | Find Existing Owner
                |--------------------------------------------------------------------------
                */

                $owner = User::query()
                    ->where(
                        'phone',
                        $data['manager_phone']
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Existing Owner
                |--------------------------------------------------------------------------
                */

                if ($owner) {

                    /*
                    |--------------------------------------------------------------------------
                    | Role Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $owner->role !==
                        UserRole::SALON_OWNER
                    ) {
                        throw ValidationException::withMessages([
                            'manager_phone' =>
                                'این شماره قبلاً برای حساب دیگری استفاده شده است.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | One Owner -> One Salon
                    |--------------------------------------------------------------------------
                    */

                    $alreadyOwnsSalon = Salon::query()
                        ->where(
                            'owner_id',
                            $owner->id
                        )
                        ->exists();

                    if ($alreadyOwnsSalon) {
                        throw ValidationException::withMessages([
                            'manager_phone' =>
                                'این حساب قبلاً مسئول یک سالن دیگر است.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sync Owner
                    |--------------------------------------------------------------------------
                    */

                    $owner->update([
                        'name' =>
                            $data['manager_name'],

                        'password' =>
                            $data['manager_password'],

                        'phone_verified_at' =>
                            $owner->phone_verified_at
                            ?? now(),
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Create Owner Account
                    |--------------------------------------------------------------------------
                    */

                    $owner = User::create([
                        'name' =>
                            $data['manager_name'],

                        'phone' =>
                            $data['manager_phone'],

                        'email' =>
                            null,

                        'password' =>
                            $data['manager_password'],

                        'role' =>
                            UserRole::SALON_OWNER,

                        'phone_verified_at' =>
                            now(),

                        'email_verified_at' =>
                            null,
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Create Salon
                |--------------------------------------------------------------------------
                */

                return Salon::create([

                    'name' =>
                        $data['name'],

                    'slug' =>
                        $slug,

                    'code' =>
                        $code,

                    'description' =>
                        $data['description']
                        ?? null,

                    'owner_id' =>
                        $owner->id,

                    /*
                    |--------------------------------------------------------------------------
                    | Salon Contact
                    |--------------------------------------------------------------------------
                    |
                    | Same phone as the salon owner login.
                    |
                    */

                    'phone' =>
                        $data['manager_phone'],

                    'email' =>
                        $data['email']
                        ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Branding
                    |--------------------------------------------------------------------------
                    */

                    'logo_path' =>
                        $logoPath,

                    'cover_path' =>
                        $coverPath,

                    'primary_color' =>
                        $data['primary_color']
                        ?? '#6757E8',

                    'secondary_color' =>
                        $data['secondary_color']
                        ?? '#37B8C8',

                    /*
                    |--------------------------------------------------------------------------
                    | Location
                    |--------------------------------------------------------------------------
                    */

                    'province' =>
                        $data['province']
                        ?? null,

                    'city' =>
                        $data['city']
                        ?? null,

                    'district' =>
                        $data['district']
                        ?? null,

                    'address' =>
                        $data['address']
                        ?? null,

                    'latitude' =>
                        $data['latitude']
                        ?? null,

                    'longitude' =>
                        $data['longitude']
                        ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Creator
                    |--------------------------------------------------------------------------
                    */

                    'created_by' =>
                        auth()->id(),

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    'is_active' =>
                        $data['is_active']
                        ?? true,
                ]);
            });


            /*
            |--------------------------------------------------------------------------
            | Public URL
            |--------------------------------------------------------------------------
            */

            $publicUrl = route(
                'public.salons.show',
                $salon
            );


            /*
            |--------------------------------------------------------------------------
            | QR Code
            |--------------------------------------------------------------------------
            */

            $qrPath =
                'salons/qr/' .
                $salon->slug .
                '.svg';

            $qrContents = QrCode::format('svg')
                ->size(800)
                ->margin(2)
                ->generate(
                    $publicUrl
                );

            Storage::disk('public')->put(
                $qrPath,
                $qrContents
            );


            /*
            |--------------------------------------------------------------------------
            | Save QR Path
            |--------------------------------------------------------------------------
            */

            $salon->update([
                'qr_code_path' =>
                    $qrPath,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'admin.salons.show',
                    $salon
                )
                ->with(
                    'success',
                    'سالن و حساب مسئول سالن با موفقیت ایجاد شدند.'
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Files
            |--------------------------------------------------------------------------
            */

            if ($logoPath) {
                Storage::disk('public')->delete(
                    $logoPath
                );
            }

            if ($coverPath) {
                Storage::disk('public')->delete(
                    $coverPath
                );
            }


            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Salon $salon
    ): View {
        $salon->load([
            'owner',
            'barbers',
            'services',
            'workingHours',
        ]);

        return view(
            'admin.salons.show',
            compact('salon')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Salon $salon
    ): View {
        $salon->load('owner');

        return view(
            'admin.salons.edit',
            compact('salon')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateSalonRequest $request,
        Salon $salon
    ): RedirectResponse {
        $data = $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Current Owner
        |--------------------------------------------------------------------------
        */

        $owner = $salon->owner;


        if (!$owner) {
            throw ValidationException::withMessages([
                'manager_phone' =>
                    'حساب مسئول این سالن پیدا نشد.',
            ]);
        }


        if (
            $owner->role !==
            UserRole::SALON_OWNER
        ) {
            throw ValidationException::withMessages([
                'manager_phone' =>
                    'حساب مسئول سالن نقش معتبری ندارد.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Owner Phone
        |--------------------------------------------------------------------------
        */

        $existingOwner = User::query()
            ->where(
                'phone',
                $data['manager_phone']
            )
            ->where(
                'id',
                '!=',
                $owner->id
            )
            ->first();


        if ($existingOwner) {
            throw ValidationException::withMessages([
                'manager_phone' =>
                    'این شماره موبایل قبلاً برای حساب دیگری ثبت شده است.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Old Files
        |--------------------------------------------------------------------------
        */

        $oldLogoPath =
            $salon->logo_path;

        $oldCoverPath =
            $salon->cover_path;


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
            | Upload New Logo
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
            | Upload New Cover
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
            | Database Update
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $data,
                $salon,
                $owner,
                $request,
                $newLogoPath,
                $newCoverPath
            ) {

                /*
                |--------------------------------------------------------------------------
                | Owner Account
                |--------------------------------------------------------------------------
                */

                $ownerData = [
                    'name' =>
                        $data['manager_name'],

                    'phone' =>
                        $data['manager_phone'],
                ];


                /*
                |--------------------------------------------------------------------------
                | Optional Password Change
                |--------------------------------------------------------------------------
                |
                | Requires manager_password to be present
                | in UpdateSalonRequest.
                |
                */

                if (
                    !empty(
                    $data['manager_password']
                    )
                ) {
                    $ownerData['password'] =
                        $data['manager_password'];
                }


                /*
                |--------------------------------------------------------------------------
                | If Phone Changes
                |--------------------------------------------------------------------------
                |
                | The new phone is the new login identity.
                |
                */

                if (
                    $owner->phone !==
                    $data['manager_phone']
                ) {
                    $ownerData[
                    'phone_verified_at'
                    ] = null;
                }


                $owner->update(
                    $ownerData
                );


                /*
                |--------------------------------------------------------------------------
                | Salon Data
                |--------------------------------------------------------------------------
                */

                $salonData = [

                    'name' =>
                        $data['name'],

                    'description' =>
                        $data['description']
                        ?? null,

                    'phone' =>
                        $data['manager_phone'],

                    'email' =>
                        $data['email']
                        ?? null,

                    'primary_color' =>
                        $data['primary_color']
                        ?? '#6757E8',

                    'secondary_color' =>
                        $data['secondary_color']
                        ?? '#37B8C8',

                    'province' =>
                        $data['province']
                        ?? null,

                    'city' =>
                        $data['city']
                        ?? null,

                    'district' =>
                        $data['district']
                        ?? null,

                    'address' =>
                        $data['address']
                        ?? null,

                    'latitude' =>
                        $data['latitude']
                        ?? null,

                    'longitude' =>
                        $data['longitude']
                        ?? null,

                    'is_active' =>
                        $data['is_active']
                        ?? true,
                ];


                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                if (
                    $request->boolean(
                        'remove_logo'
                    )
                ) {
                    $salonData[
                    'logo_path'
                    ] = null;

                } elseif ($newLogoPath) {

                    $salonData[
                    'logo_path'
                    ] = $newLogoPath;
                }


                /*
                |--------------------------------------------------------------------------
                | Cover
                |--------------------------------------------------------------------------
                */

                if (
                    $request->boolean(
                        'remove_cover'
                    )
                ) {
                    $salonData[
                    'cover_path'
                    ] = null;

                } elseif ($newCoverPath) {

                    $salonData[
                    'cover_path'
                    ] = $newCoverPath;
                }


                /*
                |--------------------------------------------------------------------------
                | Update Salon
                |--------------------------------------------------------------------------
                */

                $salon->update(
                    $salonData
                );
            });


            /*
            |--------------------------------------------------------------------------
            | Delete Old Logo
            |--------------------------------------------------------------------------
            */

            if (
                (
                    $request->boolean(
                        'remove_logo'
                    ) ||
                    $newLogoPath
                ) &&
                $oldLogoPath
            ) {
                Storage::disk('public')->delete(
                    $oldLogoPath
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Old Cover
            |--------------------------------------------------------------------------
            */

            if (
                (
                    $request->boolean(
                        'remove_cover'
                    ) ||
                    $newCoverPath
                ) &&
                $oldCoverPath
            ) {
                Storage::disk('public')->delete(
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
                    'admin.salons.show',
                    $salon
                )
                ->with(
                    'success',
                    'اطلاعات سالن با موفقیت به‌روزرسانی شد.'
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup New Files
            |--------------------------------------------------------------------------
            */

            if ($newLogoPath) {
                Storage::disk('public')->delete(
                    $newLogoPath
                );
            }

            if ($newCoverPath) {
                Storage::disk('public')->delete(
                    $newCoverPath
                );
            }


            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Salon $salon
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Files
        |--------------------------------------------------------------------------
        */

        if ($salon->logo_path) {
            Storage::disk('public')->delete(
                $salon->logo_path
            );
        }

        if ($salon->cover_path) {
            Storage::disk('public')->delete(
                $salon->cover_path
            );
        }

        if ($salon->qr_code_path) {
            Storage::disk('public')->delete(
                $salon->qr_code_path
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Soft Delete
        |--------------------------------------------------------------------------
        */

        $salon->delete();


        return redirect()
            ->route(
                'admin.salons.index'
            )
            ->with(
                'success',
                'سالن با موفقیت حذف شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $name
    ): string {
        $baseSlug =
            Str::slug($name);


        if ($baseSlug === '') {
            $baseSlug = 'salon';
        }


        $slug = $baseSlug;
        $counter = 2;


        while (
        Salon::query()
            ->where(
                'slug',
                $slug
            )
            ->exists()
        ) {
            $slug =
                $baseSlug .
                '-' .
                $counter;

            $counter++;
        }


        return $slug;
    }


    /*
    |--------------------------------------------------------------------------
    | Unique Public Code
    |--------------------------------------------------------------------------
    */

    private function generateUniqueCode(): string
    {
        do {

            $code =
                'SALON-' .
                Str::upper(
                    Str::random(5)
                );

        } while (
            Salon::query()
                ->where(
                    'code',
                    $code
                )
                ->exists()
        );


        return $code;
    }
}
