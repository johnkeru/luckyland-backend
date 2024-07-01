<?php

namespace App\Listeners\Reservation;

use App\Events\Reservation\ReservationCancelled;
use App\Mail\CancelledReservationFrontDeskSideMail;
use App\Mail\CancelledReservationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailCustomerWhenCancelled
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
    public function handle(ReservationCancelled $event): void
    {

        $cancelledEmailContent = [
            'name' => $event->reservation->customer->firstName . ' ' . $event->reservation->customer->lastName,
            // 'refund' => number_format($event->reservation->refund, 2),
        ];
        // Send cancellation email to customer
        if (env('APP_PROD')) {
            Mail::to($event->reservation->customer->email)->send(new CancelledReservationMail($cancelledEmailContent));
            $frontDesksEmail = User::whereHas('roles', function ($query) {
                $query->where('roleName', 'Front Desk');
            })->pluck('email');
            Mail::to($frontDesksEmail)->send(new CancelledReservationFrontDeskSideMail($cancelledEmailContent));
        }
    }
}
