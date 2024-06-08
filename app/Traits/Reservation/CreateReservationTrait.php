<?php

namespace App\Traits\Reservation;

use App\Models\Cottage;
use App\Models\Item;
use App\Models\Other;
use App\Models\Room;
use App\Models\Unavailable;

trait CreateReservationTrait
{

    public function processReservationWithAddOns($validatedData)
    {
        // reserved room/s (optionally with addOns)
        if (isset($validatedData['rooms'])) {
            foreach ($validatedData['rooms'] as $roomIdWithAddOns) {
                $roomId = $roomIdWithAddOns['id'];
                $room = Room::findOrFail($roomId);

                if (isset($roomIdWithAddOns['addOns'])) {
                    foreach ($roomIdWithAddOns['addOns'] as $addOn) {
                        $item_id = $addOn['item_id'];
                        $item = Item::where('id', $item_id)->first();
                        if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                            // this is only for add ons
                            return response()->json([
                                'success' => false,
                                'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                'data' => ['reservedAddOnId' => ['id' => $room->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                            ], 400);
                        }
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
                        $item = Item::where('id', $item_id)->first();
                        if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                            // this is only for add ons
                            return response()->json([
                                'success' => false,
                                'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                'data' => ['reservedAddOnId' => ['id' => $cottage->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                            ], 400);
                        }
                    }
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
                        $item = Item::where('id', $item_id)->first();
                        if ($item->status === 'Out of Stock' || $item->currentQuantity === 0) {
                            // this is only for add ons
                            return response()->json([
                                'success' => false,
                                'message' => 'We apologize, but the previous reservation has taken the last ' . $item->name . '. Would you like to continue without it?',
                                'data' => ['reservedAddOnId' => ['id' => $other->id, 'item_id' => $item->id, 'price' => $item->price, 'name' => $item->name]]
                            ], 400);
                        }
                    }
                }
            }
        }
    }


    public function processReservationAttachment($validatedData, $reservationId)
    {
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

                        $item->currentQuantity -= $quantity;
                        if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                            $item->status = 'Low Stock';
                        } else if ($item->currentQuantity === 0) {
                            $item->status = 'Out of Stock';
                        }
                        $room->items()->attach([$item_id => ['minQuantity' => $quantity, 'maxQuantity' => $quantity, 'isBed' => $isBed, 'reservation_id' => $reservationId]]); // same because this is just add Ons
                        $item->save();
                    }
                }

                foreach ($room->items as $item) {
                    $pivot = $item->pivot;
                    if ($pivot->reservation_id === null) {
                        if (!$pivot->isBed) { // if this->isBed is still false in pivot
                            $quantityToDeduct = $isBed ? $pivot->maxQuantity : $pivot->minQuantity;
                            if ($item->currentQuantity >= $quantityToDeduct) { // if currentQuantity below quantityToDeduct then don't allow it to deduct anymore as it will go currentQuantity to negative!.
                                $item->currentQuantity -= $quantityToDeduct;
                                $room->items()->updateExistingPivot($item->id, ['isBed' => $pivot->isBed]);
                            } else {
                                // left the currentQuantity as it is.
                                $room->items()->updateExistingPivot($item->id, ['isBed' => $pivot->isBed, 'needStock' => $quantityToDeduct]);
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
                        $item->currentQuantity -= $quantity;
                        if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                            $item->status = 'Low Stock';
                        } else if ($item->currentQuantity === 0) {
                            $item->status = 'Out of Stock';
                        }
                        $cottage->items()->attach([$item_id => ['quantity' => $quantity, 'reservation_id' => $reservationId]]);
                        $item->save();
                    }
                }


                foreach ($cottage->items as $item) {
                    $pivot = $item->pivot;
                    if ($pivot->reservation_id === null) {
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
        }

        // OTHERS
        if (isset($validatedData['others'])) {
            foreach ($validatedData['others'] as $otherIdWithAddOns) {
                $otherId = $otherIdWithAddOns['id'];
                $other = Other::findOrFail($otherId);

                if (isset($otherIdWithAddOns['addOns'])) {
                    foreach ($otherIdWithAddOns['addOns'] as $addOn) {
                        $item_id = $addOn['item_id'];
                        $quantity = $addOn['quantity'];
                        $item = Item::where('id', $item_id)->first();
                        $item->currentQuantity -= $quantity;
                        if ($item->currentQuantity !== 0 && $item->currentQuantity < $item->reOrderPoint) {
                            $item->status = 'Low Stock';
                        } else if ($item->currentQuantity === 0) {
                            $item->status = 'Out of Stock';
                        }
                        $other->items()->attach([$item_id => ['quantity' => $quantity, 'reservation_id' => $reservationId]]);
                        $item->save();
                    }
                }

                foreach ($other->items as $item) {
                    $pivot = $item->pivot;
                    if ($pivot->reservation_id === null) { // not add ons
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
        }
    }

    function attachAndHandleUnavailableItems($reservation, $data, $relationType, $modelType, $customer)
    {
        if (isset($data[$relationType])) {
            $relationIds = array_column($data[$relationType], 'id'); // Extract room IDs from validated data
            $reservation->$relationType()->attach($relationIds);

            $relationIds = collect($data[$relationType])->pluck('id')->toArray();
            foreach ($relationIds as $relationId) {
                $relation = $modelType::where('id', $relationId)->first();
                $items = $relation->items;
                foreach ($items as $item) {
                    $pivot = $item->pivot;
                    $quantity = $pivot->quantity;
                    if ($pivot->needStock === 0) {
                        Unavailable::create([
                            'reservation_id' => $reservation->id,
                            'item_id' => $item->id,
                            'reason' => 'Reserved for guest' . ' ' . $customer->firstName . ' ' . $customer->lastName  . ' ' . 'in ' . $relation->name,
                            'quantity' => $quantity,
                        ]);
                    }
                }
            }
        }
    }
}
