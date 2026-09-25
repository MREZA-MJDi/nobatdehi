<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request
            ->user()
            ->favoriteSalons()
            ->where('is_active', true)
            ->withCount([
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->withAvg([
                'reviews' => fn ($query) => $query->where('is_published', true),
            ], 'rating')
            ->latest('salon_favorites.created_at')
            ->paginate(12)
            ->withQueryString();

        return view(
            'customer.account.favorites',
            compact('favorites')
        );
    }

    public function store(Request $request, Salon $salon): RedirectResponse
    {
        abort_unless($salon->is_active, 404);

        $request->user()->favoriteSalons()->syncWithoutDetaching([
            $salon->id,
        ]);

        return back()->with(
            'success',
            'سالن به علاقه‌مندی‌ها اضافه شد.'
        );
    }

    public function destroy(Request $request, Salon $salon): RedirectResponse
    {
        $request->user()->favoriteSalons()->detach($salon->id);

        return back()->with(
            'success',
            'سالن از علاقه‌مندی‌ها حذف شد.'
        );
    }
}
