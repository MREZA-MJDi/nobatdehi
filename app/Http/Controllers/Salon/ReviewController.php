<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Reviews List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Unread Notifications
        |--------------------------------------------------------------------------
        |
        | Required by layouts.salon
        |
        */

        $unreadNotifications = $request
            ->user()
            ->unreadNotifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        |
        | ?status=all
        | ?status=published
        | ?status=pending
        |
        */

        $status =
            $request->query(
                'status',
                'all'
            );


        $reviewsQuery = $salon
            ->reviews()
            ->with([
                'customer',
                'booking.barber',
                'booking.service',
            ])
            ->latest();


        if (
            $status === 'published'
        ) {

            $reviewsQuery
                ->where(
                    'is_published',
                    true
                );

        } elseif (
            $status === 'pending'
        ) {

            $reviewsQuery
                ->where(
                    'is_published',
                    false
                );

        } else {

            $status = 'all';

        }


        $reviews =
            $reviewsQuery
                ->paginate(20)
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $reviewsCount =
            $salon
                ->reviews()
                ->count();


        $publishedCount =
            $salon
                ->reviews()
                ->where(
                    'is_published',
                    true
                )
                ->count();


        $pendingCount =
            $salon
                ->reviews()
                ->where(
                    'is_published',
                    false
                )
                ->count();


        $averageRating =
            $salon
                ->reviews()
                ->avg('rating');


        /*
        |--------------------------------------------------------------------------
        | Rating Distribution
        |--------------------------------------------------------------------------
        */

        $ratingDistribution = [];

        for (
            $rating = 5;
            $rating >= 1;
            $rating--
        ) {

            $ratingDistribution[$rating] =
                $salon
                    ->reviews()
                    ->where(
                        'rating',
                        $rating
                    )
                    ->count();

        }


        return view(
            'salon.reviews.index',
            compact(
                'salon',
                'reviews',
                'averageRating',
                'reviewsCount',
                'publishedCount',
                'pendingCount',
                'ratingDistribution',
                'status',
                'unreadNotifications'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Published
    |--------------------------------------------------------------------------
    */

    public function togglePublished(
        Request $request,
        Review $review
    ): RedirectResponse {

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Security:
        | Review must belong to the current salon.
        |--------------------------------------------------------------------------
        */

        $review =
            $salon
                ->reviews()
                ->findOrFail(
                    $review->id
                );


        $review->update([
            'is_published' =>
                !$review->is_published,
        ]);


        return back()
            ->with(
                'success',
                $review->is_published
                    ? 'نظر با موفقیت منتشر شد.'
                    : 'نظر از صفحه عمومی سالن پنهان شد.'
            );
    }
}
