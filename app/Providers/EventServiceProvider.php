<?php

namespace App\Providers;

use App\Events\Reservation\CustomerDeparture;
use App\Events\Reservation\CustomerJustReserved;
use App\Events\Reservation\RescheduledReservation;
use App\Events\Reservation\ReservationCancelled;
use App\Listeners\Reservation\EmailCustomerAndAdmin;
use App\Listeners\Reservation\EmailCustomerAndAdminAboutRescheduled;
use App\Listeners\Reservation\EmailCustomerWhenCancelled;
use App\Listeners\Reservation\NotifyHouseKeeper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CustomerJustReserved::class => [
            EmailCustomerAndAdmin::class,
        ],
        ReservationCancelled::class => [
            EmailCustomerWhenCancelled::class,
        ],
        CustomerDeparture::class => [
            NotifyHouseKeeper::class,
        ],
        RescheduledReservation::class => [
            EmailCustomerAndAdminAboutRescheduled::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
