<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class AllRoomResponse implements Responsable
{
    protected $rooms;

    public function __construct($rooms)
    {
        $this->rooms = $rooms;
    }

    public function toResponse($request)
    {
        $data = $this->transformInventories();
        return response()->json([
            'data' => $data,
        ]);
    }


    protected function transformInventories()
    {
        return $this->rooms->map(function ($room) {
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
        });
    }
}
