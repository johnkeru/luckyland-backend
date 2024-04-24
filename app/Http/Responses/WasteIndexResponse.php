<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class WasteIndexResponse implements Responsable
{
    protected $wastes;

    public function __construct($wastes)
    {
        $this->wastes = $wastes;
    }

    public function toResponse($request)
    {
        $currentPage = $this->wastes->currentPage();
        $perPage = $this->wastes->perPage();
        $total = $this->wastes->total();
        $lastPage = $this->wastes->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->wastes->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->wastes->nextPageUrl() : null;

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
                'url' => $this->wastes->url($i),
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
            'first_page_url' => $this->wastes->url(1),
            'from' => $this->wastes->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->wastes->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->wastes->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->wastes->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->wastes->map(function ($waste) {
            return [
                'id' => $waste->id,
                'quantity' => $waste->quantity,
                'date' => $waste->created_at,

                'name' => $waste->item->name,
                'currentQuantity' => $waste->item->currentQuantity,
                'item_id' => $waste->item->id,
                'price' => $waste->item->price,
                'image' => $waste->item->image,

                'categories' => $waste->item->categories,
            ];
        });
    }
}
