<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalonRequest;
use App\Http\Requests\Admin\UpdateSalonRequest;
use App\Models\Barber;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
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
            ->with([
                'manager.user',
                'creator',
            ])
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
        $barbers = Barber::query()
            ->with('user')
            ->where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->where('role', 'barber');
            })
            ->orderBy('id')
            ->get();

        return view(
            'admin.salons.create',
            compact('barbers')
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
        | Immutable Public Code
        |--------------------------------------------------------------------------
        */

        $data['code'] = $this->generateUniqueCode();


        /*
        |--------------------------------------------------------------------------
        | Creator
        |--------------------------------------------------------------------------
        */

        $data['created_by'] = $request->user()->id;


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $data['is_active'] = $request->boolean(
            'is_active',
            true
        );


        /*
        |--------------------------------------------------------------------------
        | Logo Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request
                ->file('logo')
                ->store(
                    'salons/logos',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Cover Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request
                ->file('cover')
                ->store(
                    'salons/covers',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Request-only fields
        |--------------------------------------------------------------------------
        */

        unset(
            $data['logo'],
            $data['cover']
        );


        /*
        |--------------------------------------------------------------------------
        | Create Salon
        |--------------------------------------------------------------------------
        */

        $salon = Salon::create($data);


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
            'manager.user',
            'creator',
            'barbers.user',
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
        $barbers = Barber::query()
            ->with('user')
            ->where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->where('role', 'barber');
            })
            ->orderBy('id')
            ->get();

        return view(
            'admin.salons.edit',
            compact(
                'salon',
                'barbers'
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
        | Status
        |--------------------------------------------------------------------------
        */

        $data['is_active'] = $request->boolean(
            'is_active'
        );


        /*
        |--------------------------------------------------------------------------
        | Logo Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $this->deleteFile(
                $salon->logo_path
            );

            $data['logo_path'] = $request
                ->file('logo')
                ->store(
                    'salons/logos',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_logo') &&
            !$request->hasFile('logo')
        ) {
            $this->deleteFile(
                $salon->logo_path
            );

            $data['logo_path'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Cover Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover')) {
            $this->deleteFile(
                $salon->cover_path
            );

            $data['cover_path'] = $request
                ->file('cover')
                ->store(
                    'salons/covers',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Cover
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_cover') &&
            !$request->hasFile('cover')
        ) {
            $this->deleteFile(
                $salon->cover_path
            );

            $data['cover_path'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Request-only fields
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
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Salon code is immutable.
        | We never update it.
        |
        */

        $salon->update($data);


        return redirect()
            ->route(
                'admin.salons.show',
                $salon
            )
            ->with(
                'success',
                'اطلاعات سالن با موفقیت بروزرسانی شد.'
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
    | Generate Unique Salon Code
    |--------------------------------------------------------------------------
    */

    private function generateUniqueCode(): string
    {
        do {
            $code =
                'SALON-' .
                Str::upper(
                    Str::random(7)
                );
        } while (
            Salon::withTrashed()
                ->where(
                    'code',
                    $code
                )
                ->exists()
        );

        return $code;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Stored File
    |--------------------------------------------------------------------------
    */

    private function deleteFile(
        ?string $path
    ): void {
        if (!$path) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
