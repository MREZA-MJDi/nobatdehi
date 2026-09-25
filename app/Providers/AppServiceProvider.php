<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Notifications\BookingNotification;
use App\Services\Sms\LogSmsSender;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SMS Sender
        |--------------------------------------------------------------------------
        */

        $this->app->bind(
            SmsSender::class,
            LogSmsSender::class
        );
    }


    public function boot(): void
    {
        Event::listen(
            BookingCreated::class,
            function (BookingCreated $event): void {
                $booking = $event->booking->loadMissing([
                    'salon.owner',
                    'barber',
                    'service',
                    'customer',
                ]);

                if ($booking->is_manual || !$booking->salon?->owner) {
                    return;
                }

                $booking->salon->owner->notify(
                    new BookingNotification(
                        $booking,
                        'created'
                    )
                );
            }
        );

        Event::listen(
            BookingStatusChanged::class,
            function (BookingStatusChanged $event): void {
                $booking = $event->booking->loadMissing([
                    'salon.owner',
                    'barber',
                    'service',
                    'customer',
                ]);

                if ($booking->customer) {
                    $booking->customer->notify(
                        new BookingNotification(
                            $booking,
                            'status_changed',
                            $event->from
                        )
                    );
                }

                if (
                    $event->from->value === 'pending'
                    && $event->to->value === 'cancelled'
                    && !$booking->is_manual
                    && $booking->salon?->owner
                ) {
                    $booking->salon->owner->notify(
                        new BookingNotification(
                            $booking,
                            'customer_cancelled',
                            $event->from
                        )
                    );
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Salon Layout Shared Data
        |--------------------------------------------------------------------------
        |
        | These variables are used by layouts.salon on every salon page.
        |
        */

        View::composer(
            'layouts.salon',
            function ($view) {

                $user = auth()->user();


                /*
                |--------------------------------------------------------------------------
                | Default Values
                |--------------------------------------------------------------------------
                */

                $salon = null;

                $unreadNotifications = 0;


                /*
                |--------------------------------------------------------------------------
                | Authenticated Salon Owner
                |--------------------------------------------------------------------------
                */

                if ($user) {

                    $salon = $user
                        ->managedSalons()
                        ->first();


                    $unreadNotifications =
                        $user
                            ->unreadNotifications()
                            ->count();
                }


                /*
                |--------------------------------------------------------------------------
                | Share With Salon Layout
                |--------------------------------------------------------------------------
                */

                $view->with(
                    'salon',
                    $salon
                );


                $view->with(
                    'unreadNotifications',
                    $unreadNotifications
                );
            }
        );
    }
}

