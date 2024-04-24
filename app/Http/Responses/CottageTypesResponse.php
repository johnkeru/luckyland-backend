<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class CottageTypesResponse implements Responsable
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
                'capacity' => $roomType->capacity,
                'cottages_count' => $roomType->cottages_count,
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
