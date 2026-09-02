<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $services = $salon
            ->services()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view(
            'salon.services.index',
            compact(
                'salon',
                'services'
            )
        );
    }

    public function create(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        return view(
            'salon.services.create',
            compact('salon')
        );
    }

    public function store(
        ServiceRequest $request
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();

        $salon->services()->create([
            'name' =>
                $data['name'],

            'description' =>
                $data['description']
                ?? null,

            'duration_minutes' =>
                $data['duration_minutes'],

            'price' =>
                $data['price'],

            'sort_order' =>
                $data['sort_order']
                ?? 0,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        return redirect()
            ->route(
                'salon.services.index'
            )
            ->with(
                'success',
                'خدمت با موفقیت ایجاد شد.'
            );
    }

    public function edit(
        Request $request,
        Service $service
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $service = $salon
            ->services()
            ->findOrFail(
                $service->id
            );

        return view(
            'salon.services.edit',
            compact(
                'salon',
                'service'
            )
        );
    }

    public function update(
        ServiceRequest $request,
        Service $service
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $service = $salon
            ->services()
            ->findOrFail(
                $service->id
            );

        $data = $request->validated();

        $service->update([
            'name' =>
                $data['name'],

            'description' =>
                $data['description']
                ?? null,

            'duration_minutes' =>
                $data['duration_minutes'],

            'price' =>
                $data['price'],

            'sort_order' =>
                $data['sort_order']
                ?? 0,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        return redirect()
            ->route(
                'salon.services.index'
            )
            ->with(
                'success',
                'خدمت با موفقیت به‌روزرسانی شد.'
            );
    }

    public function destroy(
        Request $request,
        Service $service
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $service = $salon
            ->services()
            ->findOrFail(
                $service->id
            );

        $service->delete();

        return redirect()
            ->route(
                'salon.services.index'
            )
            ->with(
                'success',
                'خدمت حذف شد.'
            );
    }
}
