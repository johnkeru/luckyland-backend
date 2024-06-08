<?php 

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

    
    ?>