<?php

namespace App\Providers;

use App\Http\Controllers\SponsorableSponsorshipsController;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(SponsorableSponsorshipsController::class, function ($app) {
            return new SponsorableSponsorshipsController($app->make('App\PaymentGateway'));
        });
    }
}
