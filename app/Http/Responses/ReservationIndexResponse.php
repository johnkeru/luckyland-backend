<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ReservationIndexResponse implements Responsable
{
    protected $reservations;
    protected $counts;

    public function __construct($reservations, $counts)
    {
        $this->reservations = $reservations;
        $this->counts = $counts;
    }

    public function toResponse($request)
    {
        $currentPage = $this->reservations->currentPage();
        $perPage = $this->reservations->perPage();
        $total = $this->reservations->total();
        $lastPage = $this->reservations->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->reservations->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->reservations->nextPageUrl() : null;

        $data = $this->transformInventories();

        // Build links array
        $links = [];
        $links[] = [
            'url' => $prevPageUrl,
            'label' => '&laquo; Previous',
            'active' => false,
        ];
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => $this->reservations->url($i),
                'label' => $i,
                'active' => $i === $currentPage,
            ];
        }
        $links[] = [
            'url' => $nextPageUrl,
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return response()->json([
            'current_page' => $currentPage,
            'data' => $data,
            'counts' => $this->counts,
            'first_page_url' => $this->reservations->url(1),
            'from' => $this->reservations->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->reservations->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->reservations->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->reservations->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {

        return $this->reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'hash' => $reservation->reservationHASH,
                'gCashRefNumberURL' => $reservation->gCashRefNumberURL,
                'customerId' => $reservation->customer->id,
                'customerName' => $reservation->customer->firstName . ' ' . $reservation->customer->lastName,
                'contactEmail' => $reservation->customer->email,
                'contactPhoneNumber' => $reservation->customer->phoneNumber,
                'address' => $reservation->customer->address->barangay  . ', ' . $reservation->customer->address->province . ', ' . $reservation->customer->address->city,
                'checkIn' => $reservation->checkIn,
                'checkOut' => $reservation->checkOut,
                'days' => $reservation->days,

                'rooms' => $reservation->rooms->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'type' => $room->type,
                    ];
                }),
                'roomCounts' => $reservation->rooms->count(),
                'cottages' => $reservation->cottages->map(function ($cottage) {
                    return [
                        'id' => $cottage->id,
                        'name' => $cottage->name,
                        'type' => $cottage->type,
                    ];
                }),
                'cottageCounts' => $reservation->cottages->count(),
                'status' => $reservation->status,
                'total' => $reservation->total,
                'paid' => $reservation->paid,
                'balance' => $reservation->balance,
                'refund' => $reservation->refund,
                'guests' => $reservation->guests,
                'gCashRefNumber' => $reservation->gCashRefNumber,
                'isWalkIn' => $reservation->isWalkIn,

                'borrowedItems' => $reservation->customer->customersBorrows->map(function ($borrowedItem) {
                    return [
                        'id' => $borrowedItem->borrows->id,
                        'name' => $borrowedItem->name,
                        'price' => $borrowedItem->price,
                        'borrowed_quantity' => $borrowedItem->borrows->borrowed_quantity,
                        'paid' => $borrowedItem->borrows->paid,
                        'item_id' => $borrowedItem->id
                    ];
                })
            ];
        });
    }
}


// Reservation ID
// Customer Name
// Contact1
// Contact2
// Reservation Date
// Room
// Guest No.
// Status