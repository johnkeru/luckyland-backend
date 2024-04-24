<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class RoomTypesResponse implements Responsable
{
    protected $roomTypes;

    public function __construct($roomTypes)
    {
        $this->roomTypes = $roomTypes;
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
        return $this->roomTypes->map(function ($roomType) {
            return [
                'id' => $roomType->id,
                'type' => $roomType->type,
                'price' => $roomType->price,
                'description' => $roomType->description,
                'minCapacity' => $roomType->minCapacity,
                'maxCapacity' => $roomType->maxCapacity,
                'rooms_count' => $roomType->rooms_count,
                'attributes' => $roomType->attributes->map(function ($attribute) {
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                    ];
                }),
            ];
        });
    }
}
