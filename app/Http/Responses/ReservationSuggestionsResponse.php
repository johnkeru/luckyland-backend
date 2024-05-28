<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ReservationSuggestionsResponse implements Responsable
{
    protected $rooms;
    protected $cottages;
    protected $others;
    protected $roomAddOns;
    protected $cottageAddOns;

    public function __construct($rooms, $cottages, $others, $roomAddOns, $cottageAddOns)
    {
        $this->rooms = $rooms;
        $this->cottages = $cottages;
        $this->others = $others;
        $this->roomAddOns = $roomAddOns;
        $this->cottageAddOns = $cottageAddOns;
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
                    'type' => $room->roomType->type,
                    'active' => $room->active,
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
                            'isOutOfStock' => $item->currentQuantity <= 3
                        ];
                    }),
                ];
            }),
            'cottages' => $this->cottages->map(function ($cottage) {
                return [
                    'id' => $cottage->id,
                    'name' => $cottage->name,
                    'active' => $cottage->active,
                    'type' => $cottage->cottageType->type,
                    'price' => $cottage->cottageType->price,
                    'description' => $cottage->cottageType->description,
                    'capacity' => $cottage->cottageType->capacity,
                    'images' => $cottage->images->map(function ($cottage) {
                        return [
                            'id' => $cottage->id,
                            'url' => $cottage->url,
                        ];
                    }),
                    'attributes' => $cottage->cottageType->attributes->map(function ($attribute) {
                        return [
                            'id' => $attribute->id,
                            'name' => $attribute->name,
                        ];
                    }),
                    'items' => $cottage->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'isOutOfStock' => $item->currentQuantity <= 3
                        ];
                    }),
                ];
            }),
            'others' => $this->others->map(function ($other) {
                return [
                    'id' => $other->id,
                    'name' => $other->name,
                    'active' => $other->active,
                    'type' => $other->otherType->type,
                    'price' => $other->otherType->price,
                    'description' => $other->otherType->description,
                    'capacity' => $other->otherType->capacity,
                    'images' => $other->images->map(function ($other) {
                        return [
                            'id' => $other->id,
                            'url' => $other->url,
                        ];
                    }),
                    'attributes' => $other->otherType->attributes->map(function ($attribute) {
                        return [
                            'id' => $attribute->id,
                            'name' => $attribute->name,
                        ];
                    }),
                    'items' => $other->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'isOutOfStock' => $item->currentQuantity <= 3
                        ];
                    }),
                ];
            }),
            'roomAddOns' => $this->roomAddOns->map(function ($addOn) {
                return [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'price' => $addOn->price,
                    'isOutOfStock' => $addOn->currentQuantity <= 0,
                ];
            }),
            'cottageAddOns' => $this->cottageAddOns->map(function ($addOn) {
                return [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'price' => $addOn->price,
                    'isOutOfStock' => $addOn->currentQuantity <= 0,
                ];
            }),
        ];
    }
}
