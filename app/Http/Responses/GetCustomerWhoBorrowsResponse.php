<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class GetCustomerWhoBorrowsResponse implements Responsable
{
    protected $borrowedItems;

    public function __construct($borrowedItems)
    {
        $this->borrowedItems = $borrowedItems;
    }

    public function toResponse($request)
    {
        $data = $this->transformInventories();

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Successfuly retrieved all the borrowed items data',
        ]);
    }


    protected function transformInventories()
    {
        return $this->borrowedItems->map(function ($borrowedItem) {
            return [
                'name' => $borrowedItem->borrows->name,
                'borrowed_quantity' => $borrowedItem->borrows->borrowed_quantity,
                'inventory_id' => $borrowedItem->id
            ];
        });
    }
}
