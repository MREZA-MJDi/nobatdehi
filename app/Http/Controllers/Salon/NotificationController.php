<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $notifications =
            $user
                ->notifications()
                ->latest()
                ->paginate(20);


        /*
        |--------------------------------------------------------------------------
        | Unread Count
        |--------------------------------------------------------------------------
        |
        | Required by layouts.salon
        |
        */

        $unreadNotifications =
            $user
                ->unreadNotifications()
                ->count();


        return view(
            'salon.notifications.index',
            compact(
                'notifications',
                'unreadNotifications'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark One Notification As Read
    |--------------------------------------------------------------------------
    */

    public function read(
        Request $request,
        string $notification
    ): RedirectResponse {

        $item =
            $request
                ->user()
                ->notifications()
                ->findOrFail(
                    $notification
                );


        $item->markAsRead();


        return back();
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */

    public function readAll(
        Request $request
    ): RedirectResponse {

        $request
            ->user()
            ->unreadNotifications()
            ->update([
                'read_at' =>
                    now(),
            ]);


        return back()
            ->with(
                'success',
                'همه اعلان‌ها خوانده شدند.'
            );
    }
}
