<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Services\Sms\LogSmsSender;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SmsSender::class,
            LogSmsSender::class
        );
    }

    public function boot(): void
    {
        //
    }
}
