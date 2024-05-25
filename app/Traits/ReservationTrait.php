<?php
namespace App\Traits;

use App\Models\Cottage;
use App\Models\Reservation;
use App\Models\ReservationPaymentToken;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait ReservationTrait{
    public function generateTokenLinkForReschedule($reservation, $customerEmail)
    {
        $token = Str::random(60);
        $expiry = Carbon::now()->addHours(3);
        ReservationPaymentToken::create([
            'token' => $token,
            'expiry_time' => $expiry,
            'reservation_id' => $reservation->id,
        ]);
        $frontendURL = env('FRONTEND_URL');
        $link = URL::to("$frontendURL/reschedule/$token" . "?email=$customerEmail" . "&id=$reservation->reservationHASH" . "&type=$reservation->accommodationType");
        return $link;
    }

    public function validateCottageAvailability($cottageIds, $checkIn, $checkOut)
    {
        $checkIn = date('Y-m-d', strtotime($checkIn));
        $checkOut = date('Y-m-d', strtotime($checkOut));

        $overlappingReservations = Reservation::where(function ($query) use ($checkIn, $checkOut) {
            $query->where('checkIn', '<', $checkOut)
                ->where('checkOut', '>', $checkIn);
        })->whereIn('status', ['Approved', 'In Resort'])->get();

        $reserveCottageIds = [];

        $reservedCottageNames = [];

        foreach ($overlappingReservations as $reservation) {
            foreach ($reservation->cottages as $cottage) {
                if (in_array($cottage->id, $cottageIds)) {
                    $reserveCottageIds[] = $cottage->id;
                    $reservedCottageNames[] = $cottage->name;
                }
            }
        }

        foreach ($cottageIds as $cottageId) {
            $cottageFromReservation = Cottage::where('id', $cottageId)->first();
            if ($cottageFromReservation->active === 0) {
                $reserveCottageIds[] = $cottageFromReservation->id;
                $reservedCottageNames[] = $cottageFromReservation->name;
            }
        }

        if (!empty($reserveCottageIds)) {
            if (count($cottageIds) === 1) {
                return response()->json([
                    'success' => false,
                    'message' => "We're sorry, but the cottage you've selected ({$reservedCottageNames[0]}) has just been reserved by someone. Please try picking another available cottage.",
                    'data' => [
                        'reservedCottageIds' => $reserveCottageIds,
                    ]
                ], 400);
            } else {
                $reservedRoomMessage = "One of the cottages you've selected (Cottage Name(s): " . implode(", ", $reservedCottageNames) . ") has just been reserved by someone. Please try picking another available cottage(s).";
                return response()->json([
                    'success' => false,
                    'message' => $reservedRoomMessage,
                    'data' => [
                        'reservedCottageIds' => $reserveCottageIds,
                    ]
                ], 400);
            }
        }
        return false;
    }

    public function validateRoomAvailability($roomIds, $checkIn, $checkOut)
    {
        // Convert dates to MySQL date format
        $checkIn = date('Y-m-d', strtotime($checkIn));
        $checkOut = date('Y-m-d', strtotime($checkOut));

        // Query to check for overlapping reservations
        $overlappingReservations = Reservation::where(function ($query) use ($checkIn, $checkOut) {
            $query->where('checkIn', '<', $checkOut)
                ->where('checkOut', '>', $checkIn);
        })->whereIn('status', ['Approved', 'In Resort'])->get();

        // Array to store reserved room IDs
        $reservedRoomIds = [];

        // Array to store reserved room names
        $reservedRoomNames = [];

        // Iterate over each reservation and check if any of the room IDs match
        foreach ($overlappingReservations as $reservation) {
            foreach ($reservation->rooms as $room) {
                if (in_array($room->id, $roomIds)) {
                    // Room is not available, add the reserved room ID to the array
                    $reservedRoomIds[] = $room->id;
                    // Add the reserved room name to the array
                    $reservedRoomNames[] = $room->name;
                }
            }
        }


        foreach ($roomIds as $roomId) {
            $roomFromReservation = Room::where('id', $roomId)->first();
            if ($roomFromReservation->active === 0) {
                $reservedRoomIds[] = $roomFromReservation->id;
                $reservedRoomNames[] = $roomFromReservation->name;
            }
        }

        // If any room is reserved
        if (!empty($reservedRoomIds)) {
            // If only one room is selected
            if (count($roomIds) === 1) {
                return response()->json([
                    'success' => false,
                    'message' => "We're sorry, but the room you've selected ({$reservedRoomNames[0]}) has just been reserved by someone. Please try picking another available room.",
                    'data' => [
                        'reservedRoomIds' => $reservedRoomIds,
                    ]
                ], 400);
            }
            // If multiple rooms are selected
            else {
                $reservedRoomMessage = "One of the rooms you've selected (Room Name(s): " . implode(", ", $reservedRoomNames) . ") has just been reserved by someone. Please try picking another available room(s).";
                return response()->json([
                    'success' => false,
                    'message' => $reservedRoomMessage,
                    'data' => [
                        'reservedRoomIds' => $reservedRoomIds,
                    ]
                ], 400);
            }
        }
        // All rooms are available
        return false;
    }
}
