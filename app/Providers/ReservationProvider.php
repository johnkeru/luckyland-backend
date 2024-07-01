<?php

namespace App\Providers;

use App\Interfaces\Reservation\ReservationInterface;
use App\Repositories\Reservation\ReservationRepository;
use Illuminate\Support\ServiceProvider;


class ReservationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->singleton(ReservationInterface::class, function($app){
        //     return new ReservationRepository();
        // });
        // $this->app->singleton(ReservationInterface::class, function ($app) {
        //     $app->make(ReservationRepository::class);
        // });
        $this->app->bind(ReservationInterface::class, ReservationRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
