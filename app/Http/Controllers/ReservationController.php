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
use App\Models\Unavailable;
use App\Models\User;
use App\Models\Waste;
use App\Traits\Reservation\ReservationTrait;
use App\Traits\Reservation\CreateReservationTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class ReservationController extends Controller
{
    use ReservationTrait, CreateReservationTrait;

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
                    $query->where('name', 'Room')
                        ->whereNot('name', 'Room Add Ons');
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
                $query->where('name', 'Room Add Ons')
                    ->whereNot('name', 'Room');
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
                    $query->where('name', 'Cottage')
                        ->whereNot('name', 'Cottage Add Ons');
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
                $query->where('name', 'Cottage Add Ons')
                    ->whereNot('name', 'Cottage');
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
                    $query->where('name', 'Other')
                        ->whereNot('name', 'Other Add Ons');
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
                $query->where('name', 'Other Add Ons')
                    ->whereNot('name', 'Other');
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

            $roomAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Room Add Ons')->whereNot('name', 'Room'))->get();
            $cottageAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Cottage Add Ons')->whereNot('name', 'Cottage'))->get();
            $otherAddOns = Item::whereHas('categories', fn ($query) => $query->where('name', 'Other Add Ons')->whereNot('name', 'Other'))->get();

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
            $this->processReservationWithAddOns($validatedData); // all
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
            $this->processReservationAttachment($validatedData, $reservation->id);
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
                                'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName  . ' ' . 'in ' . $room->name,
                                'quantity' => $quantity,
                            ]);
                        }
                    }
                }
            }
            // this two is same with room above.
            $this->attachAndHandleUnavailableItems($reservation, $validatedData, 'cottages', Cottage::class, $customer);
            $this->attachAndHandleUnavailableItems($reservation, $validatedData, 'others', Other::class, $customer);
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
                'message' => $id ? 'Successfully Reserved!' : 'Thank you for your booking with us. Your GCash payment is being processed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'message' => 'Check your internet connection.'
            ], 500);
        }
    }

    public function cancelReservation($reservationId): \Illuminate\Http\JsonResponse
    {
        try {
            // reservationId could be id or reservationHASH coloumn. reservationHASH for resched since id is encapsulated.
            $reservation = Reservation::where('id', $reservationId)->orWhere('reservationHASH', $reservationId)->first();
            $id = Auth::id();
            $reservation->status = 'Cancelled';
            $rooms = $reservation->rooms;
            foreach ($rooms as $room) {
                $items = $room->items;
                foreach ($items as $item) {
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

                    if ($pivot->reservation_id === null) {
                        $room->items()->updateExistingPivot($item->id, ['isBed' => false, 'needStock' => 0]);
                    }
                    $item->save();
                }
            }

            $cottages = $reservation->cottages;
            foreach ($cottages as $cottage) {
                $items = $cottage->items;
                foreach ($items as $item) {
                    $pivot = $item->pivot;
                    if ($item->currentQuantity > $item->reOrderPoint) {
                        $item->status = 'In Stock';
                    }
                    if ($pivot->reservation_id === null) { // if not add ons
                        $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                        $cottage->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                    }
                    if ($pivot->reservation_id === $reservation->id) {
                        $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                    }
                    $item->save();
                }
            }
            $others = $reservation->others;
            foreach ($others as $other) {
                $items = $other->items;
                foreach ($items as $item) {
                    $pivot = $item->pivot;
                    if ($item->currentQuantity > $item->reOrderPoint) {
                        $item->status = 'In Stock';
                    }
                    if ($pivot->reservation_id === null) { // if not add ons
                        $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                        $other->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                    }
                    if ($pivot->reservation_id === $reservation->id) {
                        // $other->items()->detach($item->id); //  i comment this instead, because even add ons should not be deleted, todo: could be here, because the item is detach. only add ons will detached
                        $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                    }
                    $item->save();
                }
            }

            // delete all records with that reservation id.
            Unavailable::where('reservation_id', $reservation->id)->delete();

            // deletes all reservation token related to this reservation, so that they won't allow to use the resched booking.
            ReservationPaymentToken::where('reservation_id', $reservation->id)->delete();

            // Compose cancellation email content
            // updated: no more refund!
            // $refundAndPaid = $reservation->paid / 2;
            // $reservation->refund = $refundAndPaid;
            // $reservation->paid  = $refundAndPaid;

            ReservationCancelled::dispatch($reservation); // trigger when cancelled.

            $reservation->save();

            if ($id) {
                EmployeeLogs::create([
                    'action' => 'Cancelled reservation status for customer ' . $reservation->customer->firstName . ' ' . $reservation->customer->lastName . '.',
                    'user_id' => $id,
                    'type' => 'cancel'
                ]);
            }

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
                        if ($pivot->needStock === 0 && $item->isConsumable) { // if no need stock then proceed. because only existing item in system will be record.
                            Waste::create([
                                'quantity' => $pivot->isBed ? $pivot->maxQuantity : $pivot->minQuantity,
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id
                            ]);
                            // $room->items()->updateExistingPivot($item->id, ['isBed' => false]);
                        }
                        Unavailable::where('item_id', $item->id)->update([
                            'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName  . ' ' . 'in ' . $room->name
                        ]);  // --------------- CHANGE THE REASON, BECAUSE IT'S NOW USE
                    }
                }

                $cottages = $reservation->cottages;
                foreach ($cottages as $cottage) {
                    $items = $cottage->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        if ($pivot->needStock === 0 && $item->isConsumable) { // if no need stock then proceed. because only existing item in system will be record.
                            Waste::create([
                                'quantity' => $pivot->quantity,
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id
                            ]);
                        }
                        Unavailable::where('item_id', $item->id)->update([
                            'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName . ' ' . 'in ' . $cottage->name,
                        ]);
                    }
                }

                $others = $reservation->others;
                foreach ($others as $other) {
                    $items = $other->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        if ($pivot->needStock === 0 && $item->isConsumable) { // if no need stock then proceed. because only existing item in system will be record.
                            Waste::create([
                                'quantity' => $pivot->quantity,
                                'reservation_id' => $reservation->id,
                                'item_id' => $item->id
                            ]);
                        }
                        Unavailable::where('item_id', $item->id)->update([
                            'reason' => "In use by guest " . $reservation->customer->firstName . ' ' . $reservation->customer->lastName . ' ' . 'in ' . $other->name,
                        ]);
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
                                $item->currentQuantity += $pivot->minQuantity;
                                if ($item->currentQuantity > $item->reOrderPoint) {
                                    $item->status = 'In Stock';
                                }
                            }
                            if ($category->name === 'Resort') {
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
                        $pivot = $item->pivot;
                        if ($item->currentQuantity > $item->reOrderPoint) {
                            $item->status = 'In Stock';
                        }
                        if ($pivot->reservation_id === null) { // if not add ons
                            $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                            $cottage->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                        }
                        if ($pivot->reservation_id === $reservation->id) {
                            // $cottage->items()->detach($item->id); //  i comment this instead, because even add ons should not be deleted, todo: could be here, because the item is detach. only add ons will detached
                            $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                        }
                        $item->save();
                    }
                }

                $others = $reservation->others;
                foreach ($others as $other) {
                    $items = $other->items;
                    foreach ($items as $item) {
                        $pivot = $item->pivot;
                        if ($item->currentQuantity > $item->reOrderPoint) {
                            $item->status = 'In Stock';
                        }
                        if ($pivot->reservation_id === null) { // if not add ons
                            $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                            $other->items()->updateExistingPivot($item->id, ['needStock' => 0]);
                        }
                        if ($pivot->reservation_id === $reservation->id) {
                            // $other->items()->detach($item->id); //  i comment this instead, because even add ons should not be deleted, todo: could be here, because the item is detach. only add ons will detached
                            $item->currentQuantity += $pivot->needStock !== 0 ? 0 : $pivot->quantity;
                        }
                        $item->save();
                    }
                }
                Unavailable::where('reservation_id', $reservation->id)->delete();
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
}
