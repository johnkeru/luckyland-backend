<?php

namespace App\Traits\Reservation;

use App\Models\Cottage;
use App\Models\Other;
use App\Models\Reservation;
use App\Models\ReservationPaymentToken;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait ReservationTrait
{
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

    public function validateOtherAvailability($otherId, $checkIn, $checkOut)
    {
        $checkIn = date('Y-m-d', strtotime($checkIn));
        $checkOut = date('Y-m-d', strtotime($checkOut));

        $overlappingReservations = Reservation::where(function ($query) use ($checkIn, $checkOut) {
            $query->where('checkIn', '<', $checkOut)
                ->where('checkOut', '>', $checkIn);
        })->whereIn('status', ['Approved', 'In Resort'])->get();

        $reservedOtherIds = [];

        $reservedOtherNames = [];

        foreach ($overlappingReservations as $reservation) {
            foreach ($reservation->others as $other) {
                if (in_array($other->id, $otherId)) {
                    $reservedOtherIds[] = $other->id;
                    $reservedOtherNames[] = $other->name;
                }
            }
        }


        foreach ($otherId as $otherId) {
            $otherFromReservation = Other::where('id', $otherId)->first();
            if ($otherFromReservation->active === 0) {
                $reservedOtherIds[] = $otherFromReservation->id;
                $reservedOtherNames[] = $otherFromReservation->name;
            }
        }

        if (!empty($reservedOtherIds)) {
            if (count($otherId) === 1) {
                return response()->json([
                    'success' => false,
                    'message' => "We're sorry, but the other you've selected ({$reservedOtherNames[0]}) has just been reserved by someone. Please try picking another available other.",
                    'data' => [
                        'reservedOtherIds' => $reservedOtherIds,
                    ]
                ], 400);
            } else {
                $reservedOtherMessage = "One of the others you've selected (Other Name(s): " . implode(", ", $reservedOtherNames) . ") has just been reserved by someone. Please try picking another available other(s).";
                return response()->json([
                    'success' => false,
                    'message' => $reservedOtherMessage,
                    'data' => [
                        'reservedOtherIds' => $reservedOtherIds,
                    ]
                ], 400);
            }
        }
        return false;
    }

    public function isSetAccommodation($key, $checkIn, $checkOut)
    {
        if (isset($validatedData[$key])) {
            $ids = array_column($validatedData[$key], 'id');
            if ($key === 'cottages') {
                $availabilityResult = $this->validateCottageAvailability($ids, $checkIn, $checkOut);
            } elseif ($key === 'rooms') {
                $availabilityResult = $this->validateRoomAvailability($ids, $checkIn, $checkOut);
            } else {
                $availabilityResult = $this->validateOtherAvailability($ids, $checkIn, $checkOut);
            }
            if ($availabilityResult !== false) {
                return $availabilityResult;
            }
        }
    }

    public function getRoomsByTotalGuests($totalGuests, $checkIn, $checkOut)
    {
        $maxCapacity = RoomType::max('maxCapacity');
        $minCapacity = RoomType::min('minCapacity');
        if ($totalGuests <= $minCapacity) {
            // Handle case where total guests are below min capacity
            $lowestMinCapacity = RoomType::where('minCapacity', '>=', $totalGuests)->min('minCapacity');

            return Room::with(['roomType', 'roomType.attributes', 'images', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Room');
                });
            }])
                ->whereHas('roomType', function ($query) use ($lowestMinCapacity) {
                    $query->where('minCapacity', $lowestMinCapacity);
                })
                ->where('active', true)
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else if ($totalGuests > $maxCapacity) {
            return Room::with(['roomType', 'roomType.attributes', 'images', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Room');
                });
            }])
                ->where('active', true)
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else {
            return Room::with(['roomType', 'roomType.attributes', 'images', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Room');
                });
            }])
                ->whereHas('roomType',  function ($query) use ($totalGuests) {
                    $query->where(function ($query) use ($totalGuests) {
                        $query->where('minCapacity', '<=', $totalGuests)
                            ->where('maxCapacity', '>=', $totalGuests);
                    });
                })
                ->where('active', true)
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        }
    }

    public function getCottagesByTotalGuests($totalGuests, $checkIn, $checkOut)
    {
        if ($totalGuests <= 10) {
            return Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->where('active', true)
                ->whereHas('cottageType', function ($query) {
                    $query->where('capacity', '<=', 10);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else if ($totalGuests > 10 && $totalGuests <= 20) {
            return Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->where('active', true)
                ->whereHas('cottageType', function ($query) {
                    $query->where('capacity', '>=', 10)
                        ->where('capacity', '<=', 20);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else if ($totalGuests > 20 && $totalGuests <= 30) {
            return Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->where('active', true)
                ->whereHas('cottageType', function ($query) {
                    $query->where('capacity', '>=', 20)
                        ->where('capacity', '<=', 30);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else if ($totalGuests > 30 && $totalGuests <= 40) {
            return Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->where('active', true)
                ->whereHas('cottageType', function ($query) {
                    $query->where('capacity', '>=', 30)
                        ->where('capacity', '<=', 40);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else {
            return Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
                });
            }])
                ->where('active', true)
                ->whereHas('cottageType', function ($query) {
                    $query->where('capacity', '>=', 40);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        }
    }

    public function getOthersByTotalGuests($totalGuests, $checkIn, $checkOut)
    {
        if ($totalGuests <= 20) {
            return Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
                });
            }])
                ->where('active', true)
                ->whereHas('otherType', function ($query) {
                    $query->where('capacity', '<=', 20);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else if ($totalGuests > 20 && $totalGuests <= 60) {
            return Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
                });
            }])
                ->where('active', true)
                ->whereHas('otherType', function ($query) {
                    $query->where('capacity', '>=', 20)
                        ->where('capacity', '<=', 60);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        } else {
            return Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
                });
            }])
                ->where('active', true)
                ->whereHas('otherType', function ($query) {
                    $query->where('capacity', '>=', 120);
                })
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->whereIn('status', ['Approved', 'In Resort'])
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->where('checkIn', '<', $checkOut)
                                ->where('checkOut', '>', $checkIn);
                        });
                })
                ->get();
        }
    }
}
