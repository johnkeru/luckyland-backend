<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class AllCottageResponse implements Responsable
{
    protected $cottages;
    protected $isOther;

    public function __construct($cottages, $isOther = false)
    {
        $this->cottages = $cottages;
        $this->isOther = $isOther;
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
        return $this->cottages->map(function ($cottage) {
            return [
                'id' => $cottage->id,
                'name' => $cottage->name,
                'active' => $cottage->active,
                'type' => $this->isOther ?  $cottage->otherType->type : $cottage->cottageType->type,
                'price' => $this->isOther ?  $cottage->otherType->price : $cottage->cottageType->price,
                'description' => $this->isOther ?  $cottage->otherType->description : $cottage->cottageType->description,
                'capacity' => $this->isOther ?  $cottage->otherType->capacity : $cottage->cottageType->capacity,

                'images' => $cottage->images->map(function ($cottage) {
                    return [
                        'id' => $cottage->id,
                        'url' => $cottage->url,
                    ];
                }),

                'attributes' => $this->isOther ?  $cottage->otherType->attributes : $cottage->cottageType->attributes->map(function ($attribute) {
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                    ];
                }),

                'items' => $cottage->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
            ];
        });
    }
}
