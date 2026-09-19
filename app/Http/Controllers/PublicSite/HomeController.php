<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $salons = Salon::query()
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'slug',
                'city',
                'district',
                'description',
                'logo_path',
                'cover_path',
                'primary_color',
                'secondary_color',
            ])
            ->withAvg([
                'reviews' => fn ($query) => $query->where('is_published', true),
            ], 'rating')
            ->withCount([
                'reviews' => fn ($query) => $query->where('is_published', true),
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->latest('id')
            ->limit(12)
            ->get();

        $featuredSalons = $salons
            ->sortByDesc(fn ($salon) =>
                ((float) ($salon->reviews_avg_rating ?? 0) * 20)
                + (int) ($salon->reviews_count ?? 0)
                + (int) ($salon->services_count ?? 0)
            )
            ->values()
            ->take(6);

        $popularServices = Service::query()
            ->where('is_active', true)
            ->whereHas('salon', fn ($query) => $query->where('is_active', true))
            ->with('salon:id,name,slug,city,district')
            ->latest('id')
            ->limit(8)
            ->get();

        $lookbook = $salons
            ->flatMap(function ($salon) {
                return $salon->posts()
                    ->where('is_active', true)
                    ->latest('id')
                    ->limit(3)
                    ->get()
                    ->map(fn ($post) => [
                        'id' => $post->id,
                        'title' => $post->title ?: 'نمونه‌کار تازه',
                        'caption' => $post->caption,
                        'media' => $post->thumbnail_path ?: $post->media_path,
                        'salon' => $salon->name,
                        'slug' => $salon->slug,
                    ]);
            })
            ->filter(fn ($item) => filled($item['media']))
            ->values()
            ->take(10);

        $stats = [
            'salons' => Salon::query()->where('is_active', true)->count(),
            'barbers' => Barber::query()->where('is_active', true)->count(),
            'services' => Service::query()->where('is_active', true)->count(),
        ];

        $categories = [
            ['label' => 'اصلاح و کوتاهی', 'query' => 'اصلاح', 'mark' => 'CUT'],
            ['label' => 'رنگ و مش', 'query' => 'رنگ', 'mark' => 'COLOR'],
            ['label' => 'کراتین و احیا', 'query' => 'کراتین', 'mark' => 'CARE'],
            ['label' => 'ناخن', 'query' => 'ناخن', 'mark' => 'NAIL'],
            ['label' => 'میکاپ', 'query' => 'میکاپ', 'mark' => 'MAKE'],
            ['label' => 'ابرو و مژه', 'query' => 'ابرو', 'mark' => 'BROW'],
        ];

        return view('home', compact(
            'featuredSalons',
            'popularServices',
            'lookbook',
            'categories',
            'stats',
        ));
    }

    public static function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '/')
        ) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
