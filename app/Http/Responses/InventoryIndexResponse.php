<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class InventoryIndexResponse implements Responsable
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function toResponse($request)
    {
        $currentPage = $this->items->currentPage();
        $perPage = $this->items->perPage();
        $total = $this->items->total();
        $lastPage = $this->items->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->items->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->items->nextPageUrl() : null;

        $data = $this->transformInventories();

        // Build links array
        $links = [];
        $links[] = [
            'url' => $prevPageUrl,
            'label' => '&laquo; Previous',
            'active' => false,
        ];
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => $this->items->url($i),
                'label' => $i,
                'active' => $i === $currentPage,
            ];
        }
        $links[] = [
            'url' => $nextPageUrl,
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return response()->json([
            'current_page' => $currentPage,
            'data' => $data,
            'first_page_url' => $this->items->url(1),
            'from' => $this->items->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->items->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->items->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->items->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'currentQuantity' => $item->currentQuantity,
                'maxQuantity' => $item->maxQuantity,
                'reOrderPoint' => $item->reOrderPoint,
                'lastCheck' => $item->lastCheck,
                'status' => $item->status,
                'item_id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'description' => $item->description,
                'image' => $item->image,
                'isBorrowable' => $item->isBorrowable,

                'customers_who_borrows_count' => $item->customers_who_borrows_count,
                'deleted_at' => $item->deleted_at,

                'categories' => $item->categories,
            ];
        });
    }
}
