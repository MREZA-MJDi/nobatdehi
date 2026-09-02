<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(
        Request $request
    ): View {
        $notifications = $request
            ->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view(
            'salon.notifications.index',
            compact('notifications')
        );
    }


    public function read(
        Request $request,
        string $notification
    ): RedirectResponse {
        $item = $request
            ->user()
            ->notifications()
            ->findOrFail(
                $notification
            );

        $item->markAsRead();

        return back();
    }


    public function readAll(
        Request $request
    ): RedirectResponse {
        $request
            ->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()
            ->with(
                'success',
                'همه اعلان‌ها خوانده شدند.'
            );
    }
}
