<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ReservationAvailableCottagesResponse implements Responsable
{
    protected $cottages;
    protected $addOns;

    public function __construct($cottages,  $addOns)
    {
        $this->cottages = $cottages;
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

            'addOns' => $this->addOns->map(function ($addOn) {
                return [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'price' => $addOn->price,
                    'isOutOfStock' => $addOn->currentQuantity <= 0
                ];
            }),
        ];
    }
}
