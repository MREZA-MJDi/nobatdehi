<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\PortfolioItemRequest;
use App\Models\PortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioItemController extends Controller
{
    public function index(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $portfolioItems = $salon
            ->portfolioItems()
            ->with([
                'barber',
                'service',
            ])
            ->paginate(12);

        return view(
            'salon.portfolio.index',
            compact(
                'salon',
                'portfolioItems'
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

        $barbers = $salon
            ->barbers()
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'salon.portfolio.create',
            compact(
                'salon',
                'barbers',
                'services'
            )
        );
    }


    public function store(
        PortfolioItemRequest $request
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $data = $request->validated();


        if (
            !empty($data['barber_id']) &&
            !$salon
                ->barbers()
                ->whereKey($data['barber_id'])
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'barber_id' =>
                        'آرایشگر انتخاب شده متعلق به این سالن نیست.',
                ])
                ->withInput();
        }


        if (
            !empty($data['service_id']) &&
            !$salon
                ->services()
                ->whereKey($data['service_id'])
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'service_id' =>
                        'خدمت انتخاب شده متعلق به این سالن نیست.',
                ])
                ->withInput();
        }


        $beforePath = $request
            ->file('before_image')
            ->store(
                'salons/portfolio',
                'public'
            );

        $afterPath = $request
            ->file('after_image')
            ->store(
                'salons/portfolio',
                'public'
            );


        $salon->portfolioItems()->create([
            'barber_id' =>
                $data['barber_id'] ?? null,

            'service_id' =>
                $data['service_id'] ?? null,

            'title' =>
                $data['title'],

            'description' =>
                $data['description'] ?? null,

            'before_image_path' =>
                $beforePath,

            'after_image_path' =>
                $afterPath,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),

            'sort_order' =>
                $data['sort_order'] ?? 0,
        ]);


        return redirect()
            ->route(
                'salon.portfolio.index'
            )
            ->with(
                'success',
                'نمونه‌کار با موفقیت اضافه شد.'
            );
    }


    public function edit(
        Request $request,
        PortfolioItem $portfolioItem
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $portfolioItem = $salon
            ->portfolioItems()
            ->findOrFail(
                $portfolioItem->id
            );

        $barbers = $salon
            ->barbers()
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'salon.portfolio.edit',
            compact(
                'salon',
                'portfolioItem',
                'barbers',
                'services'
            )
        );
    }


    public function update(
        PortfolioItemRequest $request,
        PortfolioItem $portfolioItem
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $portfolioItem = $salon
            ->portfolioItems()
            ->findOrFail(
                $portfolioItem->id
            );

        $data = $request->validated();


        if (
            !empty($data['barber_id']) &&
            !$salon
                ->barbers()
                ->whereKey($data['barber_id'])
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'barber_id' =>
                        'آرایشگر انتخاب شده متعلق به این سالن نیست.',
                ])
                ->withInput();
        }


        if (
            !empty($data['service_id']) &&
            !$salon
                ->services()
                ->whereKey($data['service_id'])
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'service_id' =>
                        'خدمت انتخاب شده متعلق به این سالن نیست.',
                ])
                ->withInput();
        }


        $oldBefore =
            $portfolioItem->before_image_path;

        $oldAfter =
            $portfolioItem->after_image_path;

        $newBefore = null;
        $newAfter = null;


        if ($request->hasFile('before_image')) {
            $newBefore = $request
                ->file('before_image')
                ->store(
                    'salons/portfolio',
                    'public'
                );
        }


        if ($request->hasFile('after_image')) {
            $newAfter = $request
                ->file('after_image')
                ->store(
                    'salons/portfolio',
                    'public'
                );
        }


        $updateData = [
            'barber_id' =>
                $data['barber_id'] ?? null,

            'service_id' =>
                $data['service_id'] ?? null,

            'title' =>
                $data['title'],

            'description' =>
                $data['description'] ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),

            'sort_order' =>
                $data['sort_order'] ?? 0,
        ];


        if ($newBefore) {
            $updateData['before_image_path'] =
                $newBefore;
        }

        if ($newAfter) {
            $updateData['after_image_path'] =
                $newAfter;
        }


        try {
            $portfolioItem->update(
                $updateData
            );
        } catch (\Throwable $e) {

            if ($newBefore) {
                Storage::disk('public')
                    ->delete($newBefore);
            }

            if ($newAfter) {
                Storage::disk('public')
                    ->delete($newAfter);
            }

            throw $e;
        }


        if ($newBefore && $oldBefore) {
            Storage::disk('public')
                ->delete($oldBefore);
        }

        if ($newAfter && $oldAfter) {
            Storage::disk('public')
                ->delete($oldAfter);
        }


        return redirect()
            ->route(
                'salon.portfolio.index'
            )
            ->with(
                'success',
                'نمونه‌کار با موفقیت به‌روزرسانی شد.'
            );
    }


    public function destroy(
        Request $request,
        PortfolioItem $portfolioItem
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $portfolioItem = $salon
            ->portfolioItems()
            ->findOrFail(
                $portfolioItem->id
            );


        $before =
            $portfolioItem->before_image_path;

        $after =
            $portfolioItem->after_image_path;


        $portfolioItem->delete();


        if ($before) {
            Storage::disk('public')
                ->delete($before);
        }

        if ($after) {
            Storage::disk('public')
                ->delete($after);
        }


        return redirect()
            ->route(
                'salon.portfolio.index'
            )
            ->with(
                'success',
                'نمونه‌کار حذف شد.'
            );
    }
}
