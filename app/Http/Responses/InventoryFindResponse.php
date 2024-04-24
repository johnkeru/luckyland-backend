<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class InventoryFindResponse implements Responsable
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function toResponse($request)
    {
        $data = $this->transformInventories();

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Successfuly retrieved all the item data',
        ]);
    }


    protected function transformInventories()
    {
        return $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'currentQuantity' => $item->currentQuantity,

                'item_id' => $item->id,
                'name' => $item->name,
                'image' => $item->image,

                'categories' => $item->categories,
            ];
        });
    }
}
