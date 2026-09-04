<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(
        Request $request
    ): View {

        $notifications =
            $request
                ->user()
                ->notifications()
                ->latest()
                ->paginate(20);


        return view(
            'customer.account.notifications',
            compact('notifications')
        );
    }


    public function read(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {

        abort_unless(
            (string) $notification->notifiable_id ===
            (string) $request->user()->id,
            404
        );


        $notification->markAsRead();


        return back();
    }


    public function readAll(
        Request $request
    ): RedirectResponse {

        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();


        return back()->with(
            'success',
            'همه اعلان‌ها خوانده شد.'
        );
    }
}
