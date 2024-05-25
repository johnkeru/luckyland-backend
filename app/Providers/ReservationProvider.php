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
