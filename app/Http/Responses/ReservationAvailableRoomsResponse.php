<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ReservationAvailableRoomsResponse implements Responsable
{
    protected $rooms;
    protected $addOns;

    public function __construct($rooms,  $addOns)
    {
        $this->rooms = $rooms;
        $this->addOns = $addOns;
    }

    public function toResponse($request)
    {
        $data = $this->transformInventories();
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    protected function transformInventories()
    {
        return [
            'rooms' => $this->rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'active' => $room->active,
                    'type' => $room->roomType->type,
                    'price' => $room->roomType->price,
                    'description' => $room->roomType->description,
                    'minCapacity' => $room->roomType->minCapacity,
                    'maxCapacity' => $room->roomType->maxCapacity,
                    'images' => $room->images->map(function ($room) {
                        return [
                            'id' => $room->id,
                            'url' => $room->url,
                        ];
                    }),
                    'attributes' => $room->roomType->attributes->map(function ($attribute) {
                        return [
                            'id' => $attribute->id,
                            'name' => $attribute->name,
                        ];
                    }),
                    'items' => $room->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                        ];
                    }),
                ];
            }),

            'addOns' => $this->addOns->map(function ($addOn) {
                return [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'price' => $addOn->price,
                ];
            }),
        ];
    }
}
