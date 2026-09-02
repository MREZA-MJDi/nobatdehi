<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\BarberRequest;
use App\Models\Barber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BarberController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $barbers = $salon
            ->barbers()
            ->latest('id')
            ->paginate(12);

        return view(
            'salon.barbers.index',
            compact(
                'salon',
                'barbers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        return view(
            'salon.barbers.create',
            compact('salon')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        BarberRequest $request
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request
                ->file('image')
                ->store(
                    'salons/barbers',
                    'public'
                );
        }

        $salon->barbers()->create([
            'name' =>
                $data['name'],

            'phone' =>
                $data['phone']
                ?? null,

            'bio' =>
                $data['bio']
                ?? null,

            'specialty' =>
                $data['specialty']
                ?? null,

            'image_path' =>
                $imagePath,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        return redirect()
            ->route(
                'salon.barbers.index'
            )
            ->with(
                'success',
                'آرایشگر با موفقیت اضافه شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        Barber $barber
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ownership Check
        |--------------------------------------------------------------------------
        |
        | Never trust the Barber ID from the URL.
        |
        */

        $barber = $salon
            ->barbers()
            ->findOrFail(
                $barber->id
            );

        return view(
            'salon.barbers.edit',
            compact(
                'salon',
                'barber'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        BarberRequest $request,
        Barber $barber
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ownership Check
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->findOrFail(
                $barber->id
            );

        $data = $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Current Image
        |--------------------------------------------------------------------------
        */

        $oldImagePath =
            $barber->image_path;

        $newImagePath = null;


        /*
        |--------------------------------------------------------------------------
        | New Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $newImagePath = $request
                ->file('image')
                ->store(
                    'salons/barbers',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Data
        |--------------------------------------------------------------------------
        */

        $updateData = [
            'name' =>
                $data['name'],

            'phone' =>
                $data['phone']
                ?? null,

            'bio' =>
                $data['bio']
                ?? null,

            'specialty' =>
                $data['specialty']
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Remove Image
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean(
                'remove_image'
            )
        ) {
            $updateData['image_path'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Replace Image
        |--------------------------------------------------------------------------
        */

        if ($newImagePath) {
            $updateData['image_path'] =
                $newImagePath;
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        try {

            $barber->update(
                $updateData
            );

        } catch (\Throwable $e) {

            if ($newImagePath) {
                Storage::disk('public')->delete(
                    $newImagePath
                );
            }

            throw $e;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Old Image
        |--------------------------------------------------------------------------
        */

        if (
            $oldImagePath &&
            (
                $newImagePath ||
                $request->boolean(
                    'remove_image'
                )
            )
        ) {
            Storage::disk('public')->delete(
                $oldImagePath
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'salon.barbers.index'
            )
            ->with(
                'success',
                'اطلاعات آرایشگر با موفقیت به‌روزرسانی شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Barber $barber
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ownership Check
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->findOrFail(
                $barber->id
            );


        /*
        |--------------------------------------------------------------------------
        | Delete Image
        |--------------------------------------------------------------------------
        */

        if ($barber->image_path) {
            Storage::disk('public')->delete(
                $barber->image_path
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Soft Delete
        |--------------------------------------------------------------------------
        */

        $barber->delete();


        return redirect()
            ->route(
                'salon.barbers.index'
            )
            ->with(
                'success',
                'آرایشگر حذف شد.'
            );
    }
}
