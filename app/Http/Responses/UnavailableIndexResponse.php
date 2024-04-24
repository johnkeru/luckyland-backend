<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class UnavailableIndexResponse implements Responsable
{
    protected $unavailables;

    public function __construct($unavailables)
    {
        $this->unavailables = $unavailables;
    }

    public function toResponse($request)
    {
        $currentPage = $this->unavailables->currentPage();
        $perPage = $this->unavailables->perPage();
        $total = $this->unavailables->total();
        $lastPage = $this->unavailables->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->unavailables->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->unavailables->nextPageUrl() : null;

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
                'url' => $this->unavailables->url($i),
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
            'first_page_url' => $this->unavailables->url(1),
            'from' => $this->unavailables->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->unavailables->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->unavailables->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->unavailables->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->unavailables->map(function ($unavailable) {
            return [
                'id' => $unavailable->id,
                'quantity' => $unavailable->quantity,
                'date' => $unavailable->created_at,
                'reason' => $unavailable->reason,

                'name' => $unavailable->item->name,
                'currentQuantity' => $unavailable->item->currentQuantity,
                'item_id' => $unavailable->item->id,
                'price' => $unavailable->item->price,
                'image' => $unavailable->item->image,

                'categories' => $unavailable->item->categories,
            ];
        });
    }
}
