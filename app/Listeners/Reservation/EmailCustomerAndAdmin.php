<?php

namespace App\Listeners\Reservation;

use App\Events\Reservation\CustomerJustReserved;
use App\Mail\SomeOneJustReservedMail;
use App\Mail\SuccessfulReservationWRescheduleMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailCustomerAndAdmin
{

    use \App\Traits\Reservation\ReservationTrait;
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
        $roomAddOns = $event->reservation->rooms()->with(['items' => function ($query) use ($event) {
            $query->whereHas('categories', function ($query) {
                $query->where('name', 'Room Add Ons')
                    ->whereNot('name', 'Room');
            })->where('reservation_id', $event->reservation->id);
        }])->get();
        $cottageAddOns = $event->reservation->cottages()->with(['items' => function ($query) use ($event) {
            $query->whereHas('categories', function ($query) {
                $query->where('name', 'Cottage Add Ons')
                    ->whereNot('name', 'Cottage');
            })->where('reservation_id', $event->reservation->id);
        }])->get();
        $otherAddOns = $event->reservation->others()->with(['items' => function ($query) use ($event) {
            $query->whereHas('categories', function ($query) {
                $query->where('name', 'Other Add Ons')
                    ->whereNot('name', 'Other');
            })->where('reservation_id', $event->reservation->id);
        }])->get();

        $recipient = $event->reservation->customer->email;
        $arrivalTime = "2:00pm";
        $departureTime = "12:00pm";
        $arrivalDate = \Carbon\Carbon::parse($event->reservation->checkIn)->format('F j');
        $departureDate = \Carbon\Carbon::parse($event->reservation->checkOut)->format('F j');
        $emailContent = [
            'guests' => $event->reservation->guests,
            'reservationHASH' => $event->reservation->reservationHASH,
            'arrivalDateTime' => "$arrivalDate at $arrivalTime",
            'departureDateTime' => "$departureDate at $departureTime",
            'total' => number_format($event->reservation->total, 2),
            'paid' => number_format($event->reservation->paid, 2),
            'balance' => number_format($event->reservation->balance, 2),
            'status' => 'Approved',
            'customerName' => $event->reservation->customer->firstName . ' ' . $event->reservation->customer->lastName,
            'rescheduleLink' => $this->generateTokenLinkForReschedule($event->reservation, $recipient),
            'roomAddOns' => $roomAddOns,
            'cottageAddOns' => $cottageAddOns,
            'otherAddOns' => $otherAddOns,
        ];
        if (env('APP_PROD')) {
            Mail::to($recipient)->send(new SuccessfulReservationWRescheduleMail($emailContent));
        }

        $frontDesksEmail = User::whereHas('roles', function ($query) {
            $query->where('roleName', 'Front Desk');
        })->pluck('email');
        $emailContentForAllFrontDesks = [
            'guests' => $emailContent['guests'],
            'reservationHASH' => $emailContent['reservationHASH'],
            'arrivalDateTime' => "$arrivalDate at $arrivalTime",
            'departureDateTime' => "$departureDate at $departureTime",
            'email' => $recipient,
            'customerName' => $emailContent['customerName'],
            'roomAddOns' => $emailContent['roomAddOns'],
            'cottageAddOns' => $emailContent['cottageAddOns'],
            'otherAddOns' => $emailContent['otherAddOns'],
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
