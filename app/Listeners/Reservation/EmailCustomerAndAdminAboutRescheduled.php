<?php

namespace App\Listeners\Reservation;

use App\Events\Reservation\RescheduledReservation;
use App\Mail\RescheduleFrontDesksMail;
use App\Mail\RescheduleMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailCustomerAndAdminAboutRescheduled
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
    public function handle(RescheduledReservation $event): void
    {
        $email = $event->reservation->customer->email;
        $emailContent = [
            'customerName' => $event->reservation->customer->firstName . ' ' . $event->reservation->customer->lastName,
            'balance' => number_format($event->reservation->balance, 2),
            'email' => $email,
        ];
        if (env('APP_PROD')) {
            Mail::to($email)->send(new RescheduleMail($emailContent));
        }

        $frontDesksEmail = User::whereHas('roles', function ($query) {
            $query->where('roleName', 'Front Desk');
        })->pluck('email');

        if (env('APP_PROD')) {
            Mail::to($frontDesksEmail)->send(new RescheduleFrontDesksMail($emailContent));
        }
    }
}
