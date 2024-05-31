<?php

namespace App\Http\Controllers;

use App\Events\Reservation\CustomerDeparture;
use App\Events\Reservation\CustomerJustReserved;
use App\Events\Reservation\RescheduledReservation;
use App\Events\Reservation\ReservationCancelled;
use App\Http\Requests\AddReservationRequest;
use App\Http\Responses\ReservationAvailableCottagesResponse;
use App\Http\Responses\ReservationAvailableRoomsResponse;
use App\Http\Responses\ReservationSuggestionsResponse;
use App\Interfaces\Reservation\ReservationInterface;
use App\Mail\ReservationStockNeedMail;
use App\Models\Address;
use App\Models\Cottage;
use App\Models\Customer;
use App\Models\EmployeeLogs;
use App\Models\Item;
use App\Models\Other;
use App\Models\Reservation;
use App\Models\ReservationPaymentToken;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Unavailable;
use App\Models\User;
use App\Models\Waste;
use App\Traits\ReservationTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class ReservationController extends Controller
{
    use ReservationTrait;

    public function __construct(public ReservationInterface $reservationRepository)
    {
    }

    public function index()
    {
        return $this->reservationRepository->index();
    }

    public function getOptionsForRoomsAndCottages()
    {
        try {
            $rooms = Room::latest()->pluck('name');
            $cottages = Cottage::latest()->pluck('name');
            $others = Other::latest()->pluck('name');

            return response()->json([
                'success' => true,
                'data' => [
                    'rooms' => $rooms,
                    'cottages' => $cottages,
                    'others' => $others
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

    public function getUnavailableDatesByOthers($simpleResponse = false)
    {
        try {
            // Initialize an empty array to store unavailable dates
            $unavailableDates = [];
            $previousDates = [];

            $reservationHASH = request()->reservationHASH ?? null;
            $othersQuery = Other::with(['reservations' => function ($query) {
                $query->whereIn('status', ['Approved', 'In Resort']);
            }])->where('active', true);

            if ($reservationHASH !== null) {
                $existingReservationForResched = Reservation::where('reservationHASH', $reservationHASH)->first();
                $previousDates['checkIn'] = $existingReservationForResched->checkIn;
                $previousDates['checkOut'] = $existingReservationForResched->checkOut;
                $previousDates['duration'] = $existingReservationForResched->days;

                $othersQuery->whereDoesntHave('reservations', function ($query) use ($reservationHASH) {
                    $query->where('reservationHASH', $reservationHASH);
                });
            }
            $others = $othersQuery->get();


            // Create an array to store reservations for each date
            $reservationsByDate = [];

            // Iterate over each other
            foreach ($others as $other) {
                // Iterate over reservations of the current other
                foreach ($other->reservations as $reservation) {
                    // Generate range of dates between check-in and check-out
                    $datesRange = \Carbon\CarbonPeriod::create($reservation->checkIn, $reservation->checkOut);

                    // Add each date in the range to the reservations by date array
                    foreach ($datesRange as $date) {
                        $dateString = $date->format('Y-m-d');
                        if (!isset($reservationsByDate[$dateString])) {
                            $reservationsByDate[$dateString] = [];
                        }
                        $reservationsByDate[$dateString][$other->id] = true;
                    }
                }
            }

            // Iterate over reservations by date
            foreach ($reservationsByDate as $date => $reservedOthers) {
                // If count of reserved others equals total others, add date to unavailable dates
                if (count($reservedOthers) == count($others)) {
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
            $unavailableDatesOthers = $this->getUnavailableDatesByOthers(true)['data']['unavailableDates'];
            // error here,,,,,,,,

            // Find the intersection of unavailable dates from both sources
            $unavailableDates = array_intersect($unavailableDatesRooms, $unavailableDatesCottages, $unavailableDatesOthers);

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

    public function getAvailableOthers()
    {
        try {
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;

            // Calculate available rooms
            $availableCottages = Other::with(['images', 'otherType.attributes', 'items' => function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('name', 'Other');
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
                $query->where('name', 'Other Add Ons');
            })->get();

            return new ReservationAvailableCottagesResponse($availableCottages, $addOns, true);
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get available others'
            ], 500);
        }
    }

    public function suggestions()
    {
        try {
            $checkIn = request()->checkIn;
            $checkOut = request()->checkOut;
            $totalGuests = request()->totalGuests;

            $availableRooms = $this->getRoomsByTotalGuests($totalGuests, $checkIn, $checkOut);
            $availableCottages = $this->getCottagesByTotalGuests($totalGuests, $checkIn, $checkOut);
            $availableOthers = $this->getOthersByTotalGuests($totalGuests, $checkIn, $checkOut);

            $roomAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Room Add Ons'))->get();
            $cottageAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Cottage Add Ons'))->get();
            $otherAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Other Add Ons'))->get();

            return new ReservationSuggestionsResponse($availableRooms, $availableCottages, $availableOthers, $roomAddOns, $cottageAddOns, $otherAddOns);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get recommendations'
            ], 500);
        }
    }


    public function customerCreateReservation(AddReservationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {

            $id = Auth::id();
            $validatedData = $request->validated();

            $validatedData['checkIn'] = Carbon::parse($validatedData['checkIn'])->addDay()->toDateString();
            $validatedData['checkOut'] = Carbon::parse($validatedData['checkOut'])->addDay()->toDateString();

            $checkIn =  $validatedData['checkIn'];
            $checkOut =  $validatedData['checkOut'];

            // ITEMS FOR ROOMS, COTTAGES, OTHERS INCASE THERE'S NO STOCK ANYMORE FOR CERTAIN ACCOMMODATIONS.
            $roomsNeedStock = [];
            $cottagesNeedStock = [];
            $othersNeedStock = [];

            $this->isSetAccommodation('cottages', $checkIn, $checkOut);
            $this->isSetAccommodation('rooms', $checkIn, $checkOut);
            $this->isSetAccommodation('others', $checkIn, $checkOut);

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
                                $roomsNeedStock[] = [
                                    'room_name' => $room->name,
                                    'item_name' => $item->name,
                                    'quantity_need' => $quantityToDeduct
                                ];
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
                            $cottage->items()->updateExistingPivot($item->id, ['needStock' => $pivot->quantity]);
                            $cottagesNeedStock[] = [
                                'cottage_name' => $cottage->name,
                                'item_name' => $item->name,
                                'quantity_need' => $pivot->quantity
                            ];
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

            if (isset($validatedData['others'])) {
                foreach ($validatedData['others'] as $otherIdWithAddOns) {
                    $otherId = $otherIdWithAddOns['id'];
                    $other = Other::findOrFail($otherId);

                    if (isset($otherIdWithAddOns['addOns'])) {
                        foreach ($otherIdWithAddOns['addOns'] as $addOn) {
                            $item_id = $addOn['item_id'];
                            $quantity = $addOn['quantity'];
                            $item = Item::where('id', $item_id)->first();
                            if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                                // this is only for add ons
                                return response()->json([
                                    'success' => false,
                                    'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                    'data' => ['reservedAddOnId' => ['id' => $other->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                                ], 400);
                            }
                            $item->currentQuantity -= $quantity;
                            if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                                $item->status = 'Low Stock';
                            } else if ($item->currentQuantity === 0) {
                                $item->status = 'Out of Stock';
                            }
                            $other->items()->attach([$item_id => ['quantity' => $quantity]]);
                            $item->save();
                        }
                    }


                    foreach ($other->items as $item) {
                        $pivot = $item->pivot;
                        if ($item->currentQuantity >= $pivot->quantity) {
                            $item->currentQuantity -= $pivot->quantity;
                        } else {
                            // left the currentQuantity as it is.
                            $other->items()->updateExistingPivot($item->id, ['needStock' => $pivot->quantity]);
                            $othersNeedStock[] = [
                                'other_name' => $other->name,
                                'item_name' => $item->name,
                                'quantity_need' => $pivot->quantity
                            ];
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

            // ONLY GETS TRIGGER IF ROOM, COTTAGE AMENETIES NEED STOCK.
            if (!empty($roomsNeedStock) || !empty($cottagesNeedStock) || !empty($othersNeedStock)) {
                $roles = ['Admin', 'Inventory', 'Front Desk'];
                $recipients = User::whereHas('roles', function ($query) use ($roles) {
                    $query->whereIn('roleName', $roles);
                })->pluck('email')->toArray();
                // There is a need for stock in either rooms or cottages
                $emailContent = [
                    'roomsNeedStock' => $roomsNeedStock,
                    'cottagesNeedStock' => $cottagesNeedStock,
                    'othersNeedStock' => $othersNeedStock,
                ];
                Mail::to($recipients)->send(new ReservationStockNeedMail($emailContent));
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
                        if ($pivot->needStock === 0) { // need stock is only have value if no item in inventory anymore.
                            Unavailable::create([
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id,
                                'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName,
                                'quantity' => $quantity,
                            ]);
                        }
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
                        if ($pivot->needStock === 0) {
                            Unavailable::create([
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id,
                                'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName,
                                'quantity' => $quantity,
                            ]);
                        }
                    }
                }
            }

            if (isset($validatedData['others'])) {
                $otherIds = array_column($validatedData['others'], 'id'); // Extract room IDs from validated data
                $reservation->others()->attach($otherIds);

                $otherIds = collect($validatedData['others'])->pluck('id')->toArray();
                foreach ($otherIds as $otherId) {
                    $other = Other::where('id', $otherId)->first();
                    $items = $other->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        $quantity = $pivot->quantity;
                        if ($pivot->needStock === 0) {
                            Unavailable::create([
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id,
                                'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName,
                                'quantity' => $quantity,
                            ]);
                        }
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
                if ($paid) $reservation->update(['paid' => $paid, 'balance' => 0]);
                else  $reservation->update(['gCashRefNumber' => $gcashPayment, 'paid' => $paid, 'balance' => 0]);
            }

            CustomerJustReserved::dispatch($reservation); // trigger event when someone reserved.

            $reservation->update(['gCashRefNumber' => $gcashPayment]);
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

    public function cancelReservation(Reservation $reservation): \Illuminate\Http\JsonResponse
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
            $others = $reservation->others;
            foreach ($others as $other) {
                $items = $other->items;
                foreach ($items as $item) {
                    $isItemForOther = array_reduce($item->categories->toArray(), function ($carry, $category) {
                        return $carry && ($category['name'] === 'Other' || $category['name'] === 'Resort');
                    }, true);

                    $pivot = $item->pivot;
                    $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                    if ($item->currentQuantity > $item->reOrderPoint) {
                        $item->status = 'In Stock';
                    }
                    if ($isItemForOther) {
                        Unavailable::where('reservation_id', $reservation->id)->delete();
                        $other->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                    } else {
                        $other->items()->detach($item->id);
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

            ReservationCancelled::dispatch($reservation); // trigger when cancelled.

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
                            if ($pivot->needStock === 0) {
                                Waste::create([
                                    'quantity' => $pivot->isBed ? $pivot->maxQuantity : $pivot->minQuantity,
                                    'reservation_id' => $reservation->id,
                                    'item_id' => $item->id
                                ]);
                            }
                            // $room->items()->updateExistingPivot($item->id, ['isBed' => false]);
                        } else {
                            if ($pivot->needStock === 0) {
                                Unavailable::where('item_id', $item->id)->update([
                                    'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName
                                ]);  // --------------- CHANGE THE REASON, BECAUSE IT'S NOW USE
                            }
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
                            if ($pivot->needStock === 0) {
                                Waste::create([
                                    'quantity' => $pivot->quantity,
                                    'reservation_id' => $reservation->id,
                                    'item_id' => $item->id
                                ]);
                            }
                        } else {
                            if ($pivot->needStock === 0) {
                                Unavailable::where('item_id', $item->id)->update([
                                    'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName
                                ]);
                            }
                        }
                    }
                }

                $others = $reservation->others;
                foreach ($others as $other) {
                    $items = $other->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;

                        $isItemOnlyForOther = array_reduce($item->categories->toArray(), function ($carry, $category) {
                            return $carry && ($category['name'] === 'Other' && $category['name'] !== 'Resort');
                        }, true);

                        if ($isItemOnlyForOther) {
                            Unavailable::where('item_id', $item->id)->delete();
                            if ($pivot->needStock === 0) {
                                Waste::create([
                                    'quantity' => $pivot->quantity,
                                    'reservation_id' => $reservation->id,
                                    'item_id' => $item->id
                                ]);
                            }
                        } else {
                            if ($pivot->needStock === 0) {
                                Unavailable::where('item_id', $item->id)->update([
                                    'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName
                                ]);
                            }
                        }
                    }
                }
            }

            if ($requestStatus === 'Departed') {
                $reservation->actualCheckOut = now();
                // add ons item should go back to inventory
                $rooms = $reservation->rooms;

                CustomerDeparture::dispatch($reservation); // notify housekeeper to clean the room.

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
                        $room->items()->updateExistingPivot($item->id, ['isBed' => false]);
                    }
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

                $others = $reservation->others;
                foreach ($others as $other) {
                    $items = $other->items;
                    foreach ($items as $item) {
                        foreach ($item->categories as $category) {
                            $pivot = $item->pivot;
                            if ($category->name === 'Others Add Ons') {
                                Unavailable::where('item_id', $item->id)->delete();  // --------------- NOW REMOVE IT IN UNAVAILABLE.
                                $item->currentQuantity += $pivot->minQuantity;
                                if ($item->currentQuantity > $item->reOrderPoint) {
                                    $item->status = 'In Stock';
                                }
                                $item->save();
                                $other->items()->detach($item->id);
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

            $checkIn = Carbon::parse($checkIn)->addDay()->toDateString();
            $checkOut = Carbon::parse($checkOut)->addDay()->toDateString();

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
            $reservation->total = ($reservation->totalRoomsPrice + $reservation->totalCottagesPrice + $reservation->totalOthersPrice) * ($days ?? 1);
            $reservation->balance = $reservation->total - 500;
            $reservation->save();

            RescheduledReservation::dispatch($reservation); // email both frontdesk and customer.

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
