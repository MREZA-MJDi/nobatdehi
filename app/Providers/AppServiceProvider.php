<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Services\Sms\LogSmsSender;
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

