<?php

namespace App\Http\Controllers\Admin;

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
            ->latest()
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
        $users = User::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.salons.create',
            compact('users')
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

        $slug = $this->generateUniqueSlug(
            $data['name']
        );

        $code = $this->generateUniqueCode();

        $logoPath = null;
        $coverPath = null;

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
        | Create Salon
        |--------------------------------------------------------------------------
        */

        try {
            $salon = DB::transaction(function () use (
                $data,
                $slug,
                $code,
                $logoPath,
                $coverPath
            ) {
                return Salon::create([
                    'name' => $data['name'],
                    'slug' => $slug,
                    'code' => $code,

                    'description' =>
                        $data['description'] ?? null,

                    'owner_id' =>
                        $data['owner_id'],

                    'phone' =>
                        $data['phone'] ?? null,

                    'email' =>
                        $data['email'] ?? null,

                    'logo_path' =>
                        $logoPath,

                    'cover_path' =>
                        $coverPath,

                    'primary_color' =>
                        $data['primary_color'] ?? null,

                    'secondary_color' =>
                        $data['secondary_color'] ?? null,

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

                    'created_by' =>
                        auth()->id(),

                    'is_active' =>
                        $data['is_active'] ?? true,
                ]);
            });
        } catch (\Throwable $e) {
            /*
             * If DB creation fails, remove uploaded files.
             */

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            throw $e;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Public URL
        |--------------------------------------------------------------------------
        */

        $publicUrl = route(
            'public.salons.show',
            $salon
        );

        /*
        |--------------------------------------------------------------------------
        | Generate QR
        |--------------------------------------------------------------------------
        */

        $qrPath =
            'salons/qr/' .
            $salon->slug .
            '.png';

        $qrContents = QrCode::format('png')
            ->size(800)
            ->margin(2)
            ->generate($publicUrl);

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
            'qr_code_path' => $qrPath,
        ]);

        return redirect()
            ->route(
                'admin.salons.show',
                $salon
            )
            ->with(
                'success',
                'سالن با موفقیت ایجاد شد.'
            );
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
        $users = User::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.salons.edit',
            compact(
                'salon',
                'users'
            )
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
        | Important
        |--------------------------------------------------------------------------
        |
        | slug and code are immutable.
        |
        */

        unset(
            $data['slug'],
            $data['code'],
            $data['created_by'],
            $data['qr_code_path']
        );


        /*
        |--------------------------------------------------------------------------
        | Logo Removal
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_logo')) {

            if ($salon->logo_path) {
                Storage::disk('public')->delete(
                    $salon->logo_path
                );
            }

            $data['logo_path'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | New Logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {

            if ($salon->logo_path) {
                Storage::disk('public')->delete(
                    $salon->logo_path
                );
            }

            $data['logo_path'] = $request
                ->file('logo')
                ->store(
                    'salons/logos',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Cover Removal
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_cover')) {

            if ($salon->cover_path) {
                Storage::disk('public')->delete(
                    $salon->cover_path
                );
            }

            $data['cover_path'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | New Cover
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover')) {

            if ($salon->cover_path) {
                Storage::disk('public')->delete(
                    $salon->cover_path
                );
            }

            $data['cover_path'] = $request
                ->file('cover')
                ->store(
                    'salons/covers',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Non-DB Fields
        |--------------------------------------------------------------------------
        */

        unset(
            $data['logo'],
            $data['cover'],
            $data['remove_logo'],
            $data['remove_cover']
        );


        /*
        |--------------------------------------------------------------------------
        | Update Salon
        |--------------------------------------------------------------------------
        */

        $salon->update($data);

        return redirect()
            ->route(
                'admin.salons.show',
                $salon
            )
            ->with(
                'success',
                'اطلاعات سالن با موفقیت به‌روزرسانی شد.'
            );
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
        | Delete Files
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
        | Soft Delete Salon
        |--------------------------------------------------------------------------
        */

        $salon->delete();

        return redirect()
            ->route('admin.salons.index')
            ->with(
                'success',
                'سالن با موفقیت حذف شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $name
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'salon';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
        Salon::where('slug', $slug)->exists()
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
    | Generate Unique Code
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
            Salon::where(
                'code',
                $code
            )->exists()
        );

        return $code;
    }
}
