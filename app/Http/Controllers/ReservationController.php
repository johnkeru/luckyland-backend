<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddReservationRequest;
use App\Http\Responses\ReservationAvailableCottagesResponse;
use App\Http\Responses\ReservationAvailableRoomsResponse;
use App\Http\Responses\ReservationIndexResponse;
use App\Http\Responses\ReservationSuggestionsResponse;
use App\Mail\CancelledReservationMail;
use App\Mail\RescheduleFrontDesksMail;
use App\Mail\RescheduleMail;
use App\Mail\SomeOneJustReservedMail;
use App\Mail\SuccessfulReservationWRescheduleMail;
use App\Models\Address;
use App\Models\Cottage;
use App\Models\Customer;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\ReservationPaymentToken;
use App\Models\Room;
use App\Models\Unavailable;
use App\Models\User;
use App\Models\Waste;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class ReservationController extends Controller
{
    public function index()
    {
        try {
            $search = request()->query('search');

            $room = request()->query('room');
            $cottage = request()->query('cottage');
            $status = request()->query('status');
            $month = request()->query('month');

            $reservation = Reservation::search($search)
                ->latest()
                ->filterByRoom($room)
                ->filterByCottage($cottage)
                ->filterByStatus($status)
                ->filterByMonth($month)
                ->with(['rooms', 'cottages', 'customer', 'customer.address'])
                ->paginate(8);

            $counts = [
                'Approved' => Reservation::where('status', 'Approved')->count(),
                'Cancelled' => Reservation::where('status', 'Cancelled')->count(),
                'Departed' => Reservation::where('status', 'Departed')->count(),
                'In Resort' => Reservation::where('status', 'In Resort')->count(),
            ];
            return new ReservationIndexResponse($reservation, $counts);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later or contact support.'
            ], 500);
        }
    }

    public function getOptionsForRoomsAndCottages()
    {
        try {
            $rooms = Room::latest()->get();
            $cottages = Cottage::latest()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rooms' => $rooms,
                    'cottages' => $cottages
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later or contact support.'
            ], 500);
        }
    }


    public function getUnavailableDatesByRooms($simpleResponse = false)
    {
        try {
            $unavailableDates = [];
            // this $previousDates is for resched purpose
            $previousDates = [];

            $reservationHASH = request()->reservationHASH ?? null;
            $roomsQuery = Room::with(['reservations' => function ($query) {
                $query->whereIn('status', ['Approved', 'In Resort']);
            }])->where('active', true);
            if ($reservationHASH !== null) {
                $existingReservationForResched = Reservation::where('reservationHASH', $reservationHASH)->first();
                $previousDates['checkIn'] = $existingReservationForResched->checkIn;
                $previousDates['checkOut'] = $existingReservationForResched->checkOut;
                $previousDates['duration'] = $existingReservationForResched->days;

                $roomsQuery->whereDoesntHave('reservations', function ($query) use ($reservationHASH) {
                    $query->where('reservationHASH', $reservationHASH);
                });
            }
            $rooms = $roomsQuery->get();

            // Create an array to store reservations for each date
            $reservationsByDate = [];

            // Iterate over each room
            foreach ($rooms as $room) {
                // Iterate over reservations of the current room
                foreach ($room->reservations as $reservation) {
                    // Generate range of dates between check-in and check-out
                    $datesRange = \Carbon\CarbonPeriod::create($reservation->checkIn, $reservation->checkOut);

                    // Add each date in the range to the reservations by date array
                    foreach ($datesRange as $date) {
                        $dateString = $date->format('Y-m-d');
                        if (!isset($reservationsByDate[$dateString])) {
                            $reservationsByDate[$dateString] = [];
                        }
                        $reservationsByDate[$dateString][$room->id] = true;
                    }
                }
            }

            // Iterate over reservations by date
            foreach ($reservationsByDate as $date => $reservedRooms) {
                // If count of reserved rooms equals total rooms, add date to unavailable dates
                if (count($reservedRooms) == count($rooms)) {
                    $unavailableDates[] = $date;
                }
            }

            // Return the array of unavailable dates
            if (!$simpleResponse) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'unavailableDates' => $unavailableDates,
                        'previousDates' => $previousDates
                    ]
                ], 200);
            } else {
                // this response is only for getUnavailableDatesByRoomsAndCottages().
                return [
                    'success' => true,
                    'data' => [
                        'unavailableDates' => $unavailableDates,
                        'previousDates' => $previousDates
                    ]
                ];
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create reservation'
            ], 500);
        }
    }

    public function getUnavailableDatesByCottages($simpleResponse = false)
    {
        try {
            // Initialize an empty array to store unavailable dates
            $unavailableDates = [];
            $previousDates = [];

            $reservationHASH = request()->reservationHASH ?? null;
            $cottagesQuery = Cottage::with(['reservations' => function ($query) {
                $query->whereIn('status', ['Approved', 'In Resort']);
            }])->where('active', true);

            if ($reservationHASH !== null) {
                $existingReservationForResched = Reservation::where('reservationHASH', $reservationHASH)->first();
                $previousDates['checkIn'] = $existingReservationForResched->checkIn;
                $previousDates['checkOut'] = $existingReservationForResched->checkOut;
                $previousDates['duration'] = $existingReservationForResched->days;

                $cottagesQuery->whereDoesntHave('reservations', function ($query) use ($reservationHASH) {
                    $query->where('reservationHASH', $reservationHASH);
                });
            }
            $cottages = $cottagesQuery->get();


            // Create an array to store reservations for each date
            $reservationsByDate = [];

            // Iterate over each cottage
            foreach ($cottages as $cottage) {
                // Iterate over reservations of the current cottage
                foreach ($cottage->reservations as $reservation) {
                    // Generate range of dates between check-in and check-out
                    $datesRange = \Carbon\CarbonPeriod::create($reservation->checkIn, $reservation->checkOut);

                    // Add each date in the range to the reservations by date array
                    foreach ($datesRange as $date) {
                        $dateString = $date->format('Y-m-d');
                        if (!isset($reservationsByDate[$dateString])) {
                            $reservationsByDate[$dateString] = [];
                        }
                        $reservationsByDate[$dateString][$cottage->id] = true;
                    }
                }
            }

            // Iterate over reservations by date
            foreach ($reservationsByDate as $date => $reservedCottages) {
                // If count of reserved cottages equals total cottages, add date to unavailable dates
                if (count($reservedCottages) == count($cottages)) {
                    $unavailableDates[] = $date;
                }
            }

            // Return the array of unavailable dates
            if (!$simpleResponse) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'unavailableDates' => $unavailableDates,
                        'previousDates' => $previousDates
                    ]
                ], 200);
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'unavailableDates' => $unavailableDates,
                        'previousDates' => $previousDates
                    ]
                ];
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get unavailable dates'
            ], 500);
        }
    }

    public function getUnavailableDatesByRoomsAndCottages()
    {
        try {
            $previousDates = [];
            $reservationHASH = request()->reservationHASH ?? null;
            if ($reservationHASH !== null) {
                $existingReservationForResched = Reservation::where('reservationHASH', $reservationHASH)->first();
                $previousDates['checkIn'] = $existingReservationForResched->checkIn;
                $previousDates['checkOut'] = $existingReservationForResched->checkOut;
                $previousDates['duration'] = $existingReservationForResched->days;
            }
            // Call the existing functions to get unavailable dates for rooms and cottages
            $unavailableDatesRooms = $this->getUnavailableDatesByRooms(true)['data']['unavailableDates'];
            $unavailableDatesCottages = $this->getUnavailableDatesByCottages(true)['data']['unavailableDates'];
            // error here,,,,,,,,

            // Find the intersection of unavailable dates from both sources
            $unavailableDates = array_intersect($unavailableDatesRooms, $unavailableDatesCottages);

            // Return the array of unavailable dates
            return response()->json([
                'success' => true,
                'data' => [
                    'unavailableDates' => array_values($unavailableDates),
                    'previousDates' => $previousDates
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get unavailable dates'
            ], 500);
        }
    }


    public function getAvailableRooms()
    {
        try {
            // Retrieve check-in and check-out dates from the request
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;

            // Calculate available rooms
            $availableRooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
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


            $addOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Room Add Ons');
            })->get();

            return new ReservationAvailableRoomsResponse($availableRooms, $addOns);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get available rooms'
            ], 500);
        }
    }

    public function getAvailableCottages()
    {
        try {
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;

            // Calculate available rooms
            $availableCottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Cottage');
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

            // Retrieve additional data
            $addOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Cottage Add Ons');
            })->get();

            return new ReservationAvailableCottagesResponse($availableCottages, $addOns);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get available cottages'
            ], 500);
        }
    }

    public function suggestions()
    {
        try {
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;
            $totalGuests = request()->totalGuests;

            // Get available rooms
            $availableRooms = null;
            if ($totalGuests <= 5) {
                $availableRooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('name', 'Room');
                    });
                }])
                    ->where('active', true)
                    ->whereHas('roomType', function ($query) {
                        $query->where('type', 'Friends/Couples');
                    })
                    ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                        $query->whereIn('status', ['Approved', 'In Resort'])
                            ->where(function ($q) use ($checkIn, $checkOut) {
                                $q->where('checkIn', '<', $checkOut)
                                    ->where('checkOut', '>', $checkIn);
                            });
                    })
                    ->get();
            } else if ($totalGuests >= 6 && $totalGuests <= 10) {
                $availableRooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('name', 'Room');
                    });
                }])
                    ->where('active', true)
                    ->whereHas('roomType', function ($query) {
                        $query->where('type', 'Family');
                    })
                    ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                        $query->whereIn('status', ['Approved', 'In Resort'])
                            ->where(function ($q) use ($checkIn, $checkOut) {
                                $q->where('checkIn', '<', $checkOut)
                                    ->where('checkOut', '>', $checkIn);
                            });
                    })
                    ->get();
            } else if ($totalGuests > 10) {
                $availableRooms = Room::with(['images', 'roomType.attributes', 'items' => function ($query) {
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
            }

            // Get available cottages
            $availableCottages = null;
            if ($totalGuests <= 10) {
                $availableCottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('name', 'Cottage');
                    });
                }])
                    ->where('active', true)
                    ->whereHas('cottageType', function ($query) {
                        $query->where('type', 'Small Cottages');
                    })
                    ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                        $query->whereIn('status', ['Approved', 'In Resort'])
                            ->where(function ($q) use ($checkIn, $checkOut) {
                                $q->where('checkIn', '<', $checkOut)
                                    ->where('checkOut', '>', $checkIn);
                            });
                    })
                    ->get();
            } else if ($totalGuests >= 11 && $totalGuests <= 20) {
                $availableCottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('name', 'Cottage');
                    });
                }])
                    ->where('active', true)
                    ->whereHas('cottageType', function ($query) {
                        $query->where('type', 'Big Cottages');
                    })
                    ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                        $query->whereIn('status', ['Approved', 'In Resort'])
                            ->where(function ($q) use ($checkIn, $checkOut) {
                                $q->where('checkIn', '<', $checkOut)
                                    ->where('checkOut', '>', $checkIn);
                            });
                    })
                    ->get();
            } else if ($totalGuests >= 20) {
                $availableCottages = Cottage::with(['images', 'cottageType.attributes', 'items' => function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('name', 'Cottage');
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
            }

            $roomAddOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Room Add Ons');
            })->get();

            // Retrieve additional data
            $cottageAddOns = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Cottage Add Ons');
            })->get();


            return new ReservationSuggestionsResponse($availableRooms, $availableCottages, $roomAddOns, $cottageAddOns);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get recommendations'
            ], 500);
        }
    }


    public function customerCreateReservation(AddReservationRequest $request)
    {
        try {

            $id = Auth::id();
            $validatedData = $request->validated();

            $checkIn = $validatedData['checkIn'];
            $checkOut = $validatedData['checkOut'];

            if (isset($validatedData['cottages'])) {
                $cottageIds = array_column($validatedData['cottages'], 'id');
                $availabilityResult = $this->validateCottageAvailability($cottageIds, $checkIn, $checkOut);
                if ($availabilityResult !== false) {
                    return $availabilityResult;
                }
            }

            if (isset($validatedData['rooms'])) {
                $roomIds = array_column($validatedData['rooms'], 'id');
                $availabilityResult = $this->validateRoomAvailability($roomIds, $checkIn, $checkOut);
                if ($availabilityResult !== false) {
                    return $availabilityResult;
                }
            }

            // reserved room/s (optionally with addOns)
            if (isset($validatedData['rooms'])) {
                foreach ($validatedData['rooms'] as $roomIdWithAddOns) {
                    $roomId = $roomIdWithAddOns['id'];
                    $room = Room::findOrFail($roomId);

                    $isBed = false;

                    if (isset($roomIdWithAddOns['addOns'])) {
                        foreach ($roomIdWithAddOns['addOns'] as $addOn) {
                            if ($addOn['name'] === 'Bed') {
                                $isBed = true;
                            }
                            $item_id = $addOn['item_id'];
                            $quantity = $addOn['quantity'];
                            $item = Item::where('id', $item_id)->first();
                            if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                                // this is only for add ons
                                return response()->json([
                                    'success' => false,
                                    'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                    'data' => ['reservedAddOnId' => ['id' => $room->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                                ], 400);
                            }
                            $item->currentQuantity -= $quantity;
                            if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                                $item->status = 'Low Stock';
                            } else if ($item->currentQuantity === 0) {
                                $item->status = 'Out of Stock';
                            }
                            $room->items()->attach([$item_id => ['minQuantity' => $quantity, 'maxQuantity' => $quantity, 'isBed' => $isBed]]); // same because this is just add Ons
                            $item->save();
                        }
                    }

                    // ITEMS FOR ROOMS
                    foreach ($room->items as $item) {
                        $pivot = $item->pivot;
                        if (!$pivot->isBed) { // if isBed is still false in pivot
                            $quantityToDeduct = $isBed ? $pivot->maxQuantity : $pivot->minQuantity;
                            if ($item->currentQuantity >= $quantityToDeduct) { // if currentQuantity below quantityToDeduct then don't allow it to deduct anymore as it will go currentQuantity to negative!. 
                                $item->currentQuantity -= $quantityToDeduct;
                                $room->items()->updateExistingPivot($item->id, ['isBed' => $isBed]);
                            } else {
                                // left the currentQuantity as it is.
                                $room->items()->updateExistingPivot($item->id, ['isBed' => $isBed, 'needStock' => $quantityToDeduct]);
                            }
                            if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                                $item->status = 'Low Stock';
                            } else if ($item->currentQuantity === 0) {
                                $item->status = 'Out of Stock';
                            }
                            $item->save();
                        }
                    }
                }
            }

            // reserved cottage/s (optionally with addOns)
            if (isset($validatedData['cottages'])) {
                foreach ($validatedData['cottages'] as $cottageIdWithAddOns) {
                    $cottageId = $cottageIdWithAddOns['id'];
                    $cottage = Cottage::findOrFail($cottageId);

                    if (isset($cottageIdWithAddOns['addOns'])) {
                        foreach ($cottageIdWithAddOns['addOns'] as $addOn) {
                            $item_id = $addOn['item_id'];
                            $quantity = $addOn['quantity'];
                            $item = Item::where('id', $item_id)->first();
                            if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                                // this is only for add ons
                                return response()->json([
                                    'success' => false,
                                    'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                    'data' => ['reservedAddOnId' => ['id' => $cottage->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                                ], 400);
                            }
                            $item->currentQuantity -= $quantity;
                            if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                                $item->status = 'Low Stock';
                            } else if ($item->currentQuantity === 0) {
                                $item->status = 'Out of Stock';
                            }
                            $cottage->items()->attach([$item_id => ['quantity' => $quantity]]);
                            $item->save();
                        }
                    }

                    foreach ($cottage->items as $item) {
                        $pivot = $item->pivot;
                        if ($item->currentQuantity >= $pivot->quantity) {
                            $item->currentQuantity -= $pivot->quantity;
                        } else {
                            // left the currentQuantity as it is.
                            $cottage->items()->updateExistingPivot($item->id, ['needStock' => $quantityToDeduct]);
                        }
                        if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                            $item->status = 'Low Stock';
                        } else if ($item->currentQuantity === 0) {
                            $item->status = 'Out of Stock';
                        }
                        $item->save();
                    }
                }
            }

            $customer = Customer::create($validatedData['customer']);
            $address = new Address([
                'province' => $validatedData['customer']['province'],
                'barangay' => $validatedData['customer']['barangay'],
                'city' => $validatedData['customer']['city'],
            ]);
            $customer->address()->save($address);

            $validatedData['customer_id'] = $customer->id;
            $validatedData['paid'] = 500;
            $validatedData['balance'] = $validatedData['total'] - $validatedData['paid'];
            $validatedData['reservationHASH'] = uniqid();
            $reservation = Reservation::create($validatedData);

            if (isset($validatedData['rooms'])) {
                $roomIds = array_column($validatedData['rooms'], 'id'); // Extract room IDs from validated data
                $reservation->rooms()->attach($roomIds);

                $roomIds = collect($validatedData['rooms'])->pluck('id')->toArray();
                foreach ($roomIds as $roomId) {
                    $room = Room::where('id', $roomId)->first();
                    $items = $room->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        $quantity = $pivot->isBed ? $pivot->maxQuantity : $pivot->minQuantity;
                        Unavailable::create([
                            'reservation_id' => $reservation->id,
                            'item_id' => $item->id,
                            'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName,
                            'quantity' => $quantity,
                        ]);
                    }
                }
            }

            if (isset($validatedData['cottages'])) {
                $cottageIds = array_column($validatedData['cottages'], 'id'); // Extract room IDs from validated data
                $reservation->cottages()->attach($cottageIds);

                $cottageIds = collect($validatedData['cottages'])->pluck('id')->toArray();
                foreach ($cottageIds as $cottageId) {
                    $cottage = Cottage::where('id', $cottageId)->first();
                    $items = $cottage->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        $quantity = $pivot->quantity;
                        Unavailable::create([
                            'reservation_id' => $reservation->id,
                            'item_id' => $item->id,
                            'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName,
                            'quantity' => $quantity,
                        ]);
                    }
                }
            }

            if ($id) {
                $actionDescription = 'Managed reservation for ' . $customer->firstName . ' ' . $customer->lastName;
                EmployeeLogs::create([
                    'action' => $actionDescription,
                    'user_id' => $id,
                    'type' => 'manage'
                ]);
            }

            // GCASH PAYMENT:
            $gcashPayment = request()->gCashRefNumber;
            $paid = request()->paid ?? 0;

            if ($id) {
                if ($paid) {
                    $reservation->update(['paid' => $paid, 'balance' => 0]);
                } else {
                    $reservation->update(['gCashRefNumber' => $gcashPayment, 'paid' => $paid, 'balance' => 0]);
                }
            }

            if (!$id) {
                $reservation->update(['gCashRefNumber' => $gcashPayment]);
                $recipient = $reservation->customer->email;
                $arrivalTime = "2:00pm";
                $departureTime = "12:00pm";
                $arrivalDate = \Carbon\Carbon::parse($reservation->checkIn)->format('F j');
                $departureDate = \Carbon\Carbon::parse($reservation->checkOut)->format('F j');
                $emailContent = [
                    'reservationHASH' => $reservation->reservationHASH,
                    'arrivalDateTime' => "$arrivalDate at $arrivalTime",
                    'departureDateTime' => "$departureDate at $departureTime",
                    'total' => number_format($reservation->total, 2),
                    'paid' => number_format($reservation->paid, 2),
                    'balance' => number_format($reservation->balance, 2),
                    'status' => $reservation->status,
                    'customerName' => $reservation->customer->firstName . ' ' . $reservation->customer->lastName,
                    'rooms' => $reservation->rooms,
                    'cottages' => $reservation->cottages, //(optional),
                    'rescheduleLink' => $this->generateTokenLinkForReschedule($reservation, $recipient)
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
            // END OF GCASH PAYMENT

            return response()->json([
                'success' => true,
                'message' => $id ? 'Successfully Reserved!' : 'Your GCash payment is being processed. We will validate the GCash reference code shortly.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    public function cancelReservation(Reservation $reservation)
    {
        try {
            $id = Auth::id();

            $reservation->status = 'Cancelled';
            $rooms = $reservation->rooms;
            foreach ($rooms as $room) {
                $items = $room->items;
                foreach ($items as $item) {
                    $isItemForRoom = array_reduce($item->categories->toArray(), function ($carry, $category) {
                        return $carry && ($category['name'] === 'Room' || $category['name'] === 'Resort');
                    }, true);

                    $pivot = $item->pivot;
                    // + $pivot->needStock?
                    if ($pivot->isBed) {
                        if ($pivot->needStock !== 0) {
                            $item->currentQuantity += 0;
                        } else {
                            $item->currentQuantity += $pivot->maxQuantity;
                        }
                    } else {
                        if ($pivot->needStock !== 0) {
                            $item->currentQuantity += 0;
                        } else {
                            $item->currentQuantity += $pivot->minQuantity;
                        }
                    }
                    if ($item->currentQuantity > $item->reOrderPoint) {
                        $item->status = 'In Stock';
                    }

                    if ($isItemForRoom) {
                        Unavailable::where('reservation_id', $reservation->id)->delete();
                        $room->items()->updateExistingPivot($item->id, ['isBed' => false, 'needStock' => 0]);
                    } else {
                        // $room->items()->detach($item->id);
                    }
                    $item->save();
                }
            }

            $cottages = $reservation->cottages;
            foreach ($cottages as $cottage) {
                $items = $cottage->items;
                foreach ($items as $item) {
                    $isItemForCottage = array_reduce($item->categories->toArray(), function ($carry, $category) {
                        return $carry && ($category['name'] === 'Cottage' || $category['name'] === 'Resort');
                    }, true);

                    $pivot = $item->pivot;
                    $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                    if ($item->currentQuantity > $item->reOrderPoint) {
                        $item->status = 'In Stock';
                    }
                    if ($isItemForCottage) {
                        Unavailable::where('reservation_id', $reservation->id)->delete();
                        $cottage->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                    } else {
                        $cottage->items()->detach($item->id);
                    }
                    $item->save();
                }
            }

            // deletes all reservation token related to this reservation, so that they won't allow to use the resched booking.
            ReservationPaymentToken::where('reservation_id', $reservation->id)->delete();

            // Compose cancellation email content
            $refundAndPaid = $reservation->paid / 2;
            $reservation->refund = $refundAndPaid;
            $reservation->paid  = $refundAndPaid;

            $cancelledEmailContent = [
                'name' => $reservation->customer->firstName . ' ' . $reservation->customer->lastName,
                'refund' => number_format($refundAndPaid, 2),
            ];
            // Send cancellation email to customer
            if (env('APP_PROD')) {
                Mail::to($reservation->customer->email)->send(new CancelledReservationMail($cancelledEmailContent));
            }

            $reservation->save();

            EmployeeLogs::create([
                'action' => 'Cancelled reservation status for customer ' . $reservation->customer->firstName . ' ' . $reservation->customer->lastName . '.',
                'user_id' => $id,
                'type' => 'cancel'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully cancelled.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update reservation'
            ], 500);
        }
    }

    // update status
    public function updateReservationStatus(Reservation $reservation)
    {
        try {
            $id = Auth::id();

            $requestStatus = request()->status;
            $reservation->status =  $requestStatus;

            if ($requestStatus === 'In Resort') {                     // ------------------ IN RESORT
                $reservation->paid = $reservation->total;
                $reservation->balance = 0;
                $reservation->actualCheckIn = now();

                $rooms = $reservation->rooms;
                foreach ($rooms as $room) {
                    $items = $room->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        // only room items that are consumable should put on waste.
                        $isItemOnlyForRoom = array_reduce($item->categories->toArray(), function ($carry, $category) {
                            return $carry && ($category['name'] === 'Room' && $category['name'] !== 'Resort');
                        }, true);

                        if ($isItemOnlyForRoom) {
                            Unavailable::where('item_id', $item->id)->delete();  // --------------- ONLY DELETE THE ITEM FOR ROOMS NOT ADD ONS(THINGS)
                            Waste::create([
                                'quantity' => $pivot->isBed ? $pivot->maxQuantity : $pivot->minQuantity,
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id
                            ]);
                            // $room->items()->updateExistingPivot($item->id, ['isBed' => false]);
                        } else {
                            Unavailable::where('item_id', $item->id)->update([
                                'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName
                            ]);  // --------------- CHANGE THE REASON, BECAUSE IT'S NOW USE 
                        }
                    }
                }

                $cottages = $reservation->cottages;
                foreach ($cottages as $cottage) {
                    $items = $cottage->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;

                        $isItemOnlyForCottage = array_reduce($item->categories->toArray(), function ($carry, $category) {
                            return $carry && ($category['name'] === 'Cottage' && $category['name'] !== 'Resort');
                        }, true);

                        if ($isItemOnlyForCottage) {
                            Unavailable::where('item_id', $item->id)->delete();
                            Waste::create([
                                'quantity' => $pivot->quantity,
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id
                            ]);
                        } else {
                            Unavailable::where('item_id', $item->id)->update([
                                'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName
                            ]);
                        }
                    }
                }
            }

            if ($requestStatus === 'Departed') {
                $reservation->actualCheckOut = now();
                // add ons item should go back to inventory
                $rooms = $reservation->rooms;
                foreach ($rooms as $room) {
                    $items = $room->items;
                    foreach ($items as $item) {
                        foreach ($item->categories as $category) {
                            $pivot = $item->pivot;
                            if ($category->name === 'Room Add Ons') {
                                Unavailable::where('item_id', $item->id)->delete();  // --------------- NOW REMOVE IT IN UNAVAILABLE.
                                $item->currentQuantity += $pivot->minQuantity;
                                if ($item->currentQuantity > $item->reOrderPoint) {
                                    $item->status = 'In Stock';
                                }
                            }
                            if ($category->name === 'Resort') {
                                Unavailable::where('item_id', $item->id)->delete();  // --------------- NOW REMOVE IT IN UNAVAILABLE.
                                $item->currentQuantity += $pivot->isBed ? $pivot->maxQuantity : $pivot->minQuantity;
                                if ($item->currentQuantity > $item->reOrderPoint) {
                                    $item->status = 'In Stock';
                                }
                            }
                            $item->save();
                            $room->items()->detach($item->id);
                        }
                    }
                    $room->items()->updateExistingPivot($item->id, ['isBed' => false]);
                    // $room->update(['active' => false]);        // !turns false because the rooms used will be clean first.
                }

                $cottages = $reservation->cottages;
                foreach ($cottages as $cottage) {
                    $items = $cottage->items;
                    foreach ($items as $item) {
                        foreach ($item->categories as $category) {
                            $pivot = $item->pivot;
                            if ($category->name === 'Cottage Add Ons') {
                                Unavailable::where('item_id', $item->id)->delete();  // --------------- NOW REMOVE IT IN UNAVAILABLE.
                                $item->currentQuantity += $pivot->minQuantity;
                                if ($item->currentQuantity > $item->reOrderPoint) {
                                    $item->status = 'In Stock';
                                }
                                $item->save();
                                $cottage->items()->detach($item->id);
                            }
                        }
                    }
                }
            }

            $reservation->save();

            EmployeeLogs::create([
                'action' => 'Updated reservation status for customer ' . $reservation->customer->firstName . ' ' . $reservation->customer->lastName . ' to ' . $reservation->status,
                'user_id' => $id,
                'type' => 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update'
            ], 500);
        }
    }


    private function validateCottageAvailability($cottageIds, $checkIn, $checkOut)
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

    private function validateRoomAvailability($roomIds, $checkIn, $checkOut)
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

    #resched!
    public function reschedule()
    {
        try {
            $reservationHASH = request()->reservationHASH;
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;
            $token = request()->token;
            $email = request()->email;
            $days = request()->days;
            $accommodationType = request()->accommodationType;

            // Check if any of the parameters is missing
            if (!$reservationHASH || !$token || !$email || !$accommodationType) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more required parameters is missing',
                ], 400);
            }


            // Validate the token
            $reservationToken = ReservationPaymentToken::where('token', $token)->first();
            if (!$reservationToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token',
                ], 400);
            }

            // Check token expiry
            if (Carbon::now()->gt($reservationToken->expiry_time)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired',
                ], 400);
            }


            $reservationToken->delete();

            // Proceed with rescheduling
            $reservation = Reservation::where('reservationHASH', $reservationHASH)->first();
            $reservation->checkIn = $checkIn;
            $reservation->checkOut = $checkOut;
            $reservation->days = $days;
            $reservation->total = ($reservation->totalRoomsPrice + $reservation->totalCottagesPrice) * $days;
            $reservation->balance = $reservation->total - 500;
            $reservation->save();

            $emailContent = [
                'customerName' => $reservation->customer->firstName . ' ' . $reservation->customer->lastName,
                'balance' => number_format($reservation->balance, 2),
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

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Reservation successfully rescheduled'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to reschedule the reservation'
            ], 500);
        }
    }
    ### reschedule link generator
    private function generateTokenLinkForReschedule($reservation, $customerEmail)
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
}













































































           // if (isset($validatedData['rooms']) && isset($validatedData['cottages'])) {
            //     $roomIds = array_column($validatedData['rooms'], 'id'); // Extract room IDs from validated data
            //     $cottageIds = array_column($validatedData['cottages'], 'id'); // Extract room IDs from validated data
            //     $availabilityResult = $this->validateRoomAndCottageAvailability($roomIds, $cottageIds, $validatedData['checkIn'], $validatedData['checkOut']);
            //     if ($availabilityResult !== false) {
            //         return $availabilityResult;
            //     }
            // }





    // private function validateRoomAndCottageAvailability($roomIds, $cottageIds, $checkIn, $checkOut)
    // {
    //     // Convert dates to MySQL date format
    //     $checkIn = date('Y-m-d', strtotime($checkIn));
    //     $checkOut = date('Y-m-d', strtotime($checkOut));

    //     // Query to check for overlapping reservations for rooms
    //     $roomOverlappingReservations = Reservation::where(function ($query) use ($checkIn, $checkOut) {
    //         $query->where('checkIn', '<', $checkOut)
    //             ->where('checkOut', '>', $checkIn);
    //     })->whereIn('status', ['Approved', 'In Resort'])
    //         ->whereHas('rooms', function ($query) use ($roomIds) {
    //             $query->whereIn('rooms.id', $roomIds);
    //         })
    //         ->get();

    //     // Query to check for overlapping reservations for cottages
    //     $cottageOverlappingReservations = Reservation::where(function ($query) use ($checkIn, $checkOut) {
    //         $query->where('checkIn', '<', $checkOut)
    //             ->where('checkOut', '>', $checkIn);
    //     })->whereIn('status', ['Approved', 'In Resort'])
    //         ->whereHas('cottages', function ($query) use ($cottageIds) {
    //             $query->whereIn('cottages.id', $cottageIds);
    //         })
    //         ->get();

    //     // Array to store reserved room names
    //     $reservedRoomNames = [];
    //     // Array to store reserved cottage names
    //     $reservedCottageNames = [];

    //     // Check for reserved rooms
    //     foreach ($roomOverlappingReservations as $reservation) {
    //         foreach ($reservation->rooms as $room) {
    //             if (in_array($room->id, $roomIds)) {
    //                 // Room is not available, add the reserved room name to the array
    //                 $reservedRoomNames[] = $room->name;
    //             }
    //         }
    //     }

    //     // Check for reserved cottages
    //     foreach ($cottageOverlappingReservations as $reservation) {
    //         foreach ($reservation->cottages as $cottage) {
    //             if (in_array($cottage->id, $cottageIds)) {
    //                 // Cottage is not available, add the reserved cottage name to the array
    //                 $reservedCottageNames[] = $cottage->name;
    //             }
    //         }
    //     }

    //     // If any room or cottage is reserved
    //     if (!empty($reservedRoomNames) || !empty($reservedCottageNames)) {
    //         $reservedRoomsMessage = !empty($reservedRoomNames) ? "One of the rooms you've selected (Room Name(s): " . implode(", ", $reservedRoomNames) . ") has just been reserved by someone." : '';
    //         $reservedCottagesMessage = !empty($reservedCottageNames) ? "One of the cottages you've selected (Cottage Name(s): " . implode(", ", $reservedCottageNames) . ") has just been reserved by someone." : '';

    //         $reservedRoomsAndCottagesMessage = trim($reservedRoomsMessage . ' ' . $reservedCottagesMessage);

    //         return [
    //             'success' => false,
    //             'message' => "We're sorry, $reservedRoomsAndCottagesMessage Please try picking another available rooms and cottages.",
    //             'data' => [
    //                 'reservedRoomIds' => $roomOverlappingReservations->pluck('rooms')->flatten()->pluck('id')->unique()->toArray(),
    //                 'reservedCottageIds' => $cottageOverlappingReservations->pluck('cottages')->flatten()->pluck('id')->unique()->toArray(),
    //             ]
    //         ];
    //     }

    //     // All rooms and cottages are available
    //     return false;
    // }











//   public function testMail()
//     {
//         $details = [
//             'title' => 'hello',
//             'content' => 'Mga handsome people.',
//             'image_url' => 'https://res.cloudinary.com/kerutman/image/upload/v1708349622/f8dtuhkgj3fk0j1madio.jpg'
//         ];

//         $recipients = [
//             'vincentd@forbescollege.org',
//             'krquirab@forbescollege.org',
//             'jmcordovilla@forbescollege.org',
//             'jrquerobin@forbescollege.org'
//         ];

//         Mail::to($recipients)->send(new \App\Mail\TestMail($details));
//         return 'Email sent at ' . now() . ' <3';
//     }