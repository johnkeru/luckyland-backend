<?php

namespace App\Listeners\Reservation;

use App\Events\Reservation\CustomerJustReserved;
use App\Mail\SomeOneJustReservedMail;
use App\Mail\SuccessfulReservationWRescheduleMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailCustomerAndAdmin
{

    use \App\Traits\ReservationTrait;
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
    public function handle(CustomerJustReserved $event): void
    {
        $recipient = $event->reservation->customer->email;
        $arrivalTime = "2:00pm";
        $departureTime = "12:00pm";
        $arrivalDate = \Carbon\Carbon::parse($event->reservation->checkIn)->format('F j');
        $departureDate = \Carbon\Carbon::parse($event->reservation->checkOut)->format('F j');
        $emailContent = [
            'reservationHASH' => $event->reservation->reservationHASH,
            'arrivalDateTime' => "$arrivalDate at $arrivalTime",
            'departureDateTime' => "$departureDate at $departureTime",
            'total' => number_format($event->reservation->total, 2),
            'paid' => number_format($event->reservation->paid, 2),
            'balance' => number_format($event->reservation->balance, 2),
            'status' => 'Approved',
            'customerName' => $event->reservation->customer->firstName . ' ' . $event->reservation->customer->lastName,
            'rooms' => $event->reservation->rooms,
            'cottages' => $event->reservation->cottages, //(optional),
            'rescheduleLink' => $this->generateTokenLinkForReschedule($event->reservation, $recipient)
        ];
        if (env('APP_PROD')) {
            Mail::to($recipient)->send(new SuccessfulReservationWRescheduleMail($emailContent));
        }

        $frontDesksEmail = User::whereHas('roles', function ($query) {
            $query->where('roleName', 'Front Desk');
        })->pluck('email');
        $emailContentForAllFrontDesks = [
            'reservationHASH' => $emailContent['reservationHASH'],
            'arrivalDateTime' => "$arrivalDate at $arrivalTime",
            'departureDateTime' => "$departureDate at $departureTime",
            'email' => $recipient,
            'customerName' => $emailContent['customerName'],
            'rooms' => $emailContent['rooms'],
            'cottages' => $emailContent['cottages'], //(optional),
            'rescheduleLink' => $emailContent['rescheduleLink'],

            'total' => $emailContent['total'],
            'paid' => $emailContent['paid'],
            'balance' => $emailContent['balance'],
            'status' => $emailContent['status'],
        ];

        if (env('APP_PROD')) {
            Mail::to($frontDesksEmail)->send(new SomeOneJustReservedMail($emailContentForAllFrontDesks));
        }
    }
}
