<?php

namespace App\Listeners\Reservation;

use App\Events\Reservation\CustomerDeparture;
use App\Mail\CustomerDepartedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotifyHouseKeeper
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CustomerDeparture $event): void
    {
        $departureMailContent = [
            'rooms' => $event->reservation->rooms,
            'customerName' => $event->reservation->customer->firstName . ' ' . $event->reservation->customer->lastName,
            'email' => $event->reservation->customer->email,
        ];
        // CustomerDepartedMail
        $houseKeeperEmails = User::whereHas('roles', function ($query) {
            $query->where('roleName', 'House Keeping');
        })->pluck('email');

        Mail::to($houseKeeperEmails)->send(new CustomerDepartedMail($departureMailContent));
    }
}
