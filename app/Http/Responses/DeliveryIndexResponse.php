<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class DeliveryIndexResponse implements Responsable
{
    protected $deliveries;

    public function __construct($deliveries)
    {
        $this->deliveries = $deliveries;
    }

    public function toResponse($request)
    {
        $currentPage = $this->deliveries->currentPage();
        $perPage = $this->deliveries->perPage();
        $total = $this->deliveries->total();
        $lastPage = $this->deliveries->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->deliveries->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->deliveries->nextPageUrl() : null;

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
                'url' => $this->deliveries->url($i),
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
            'first_page_url' => $this->deliveries->url(1),
            'from' => $this->deliveries->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->deliveries->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->deliveries->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->deliveries->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->deliveries->map(function ($deliver) {
            return [
                'id' => $deliver->id,
                'companyName' => $deliver->companyName,
                'arrivalDate' => $deliver->arrivalDate,
                'bill' => $deliver->bill,
                'status' => $deliver->status,
                'deleted_at' => $deliver->deleted_at,

                'manageBy' => $deliver->manage ? $deliver->manage->firstName . ' ' . $deliver->manage->middleName . ' ' . $deliver->manage->lastName : null,

                'total_items' => $deliver->items->count(),
                'items' => $deliver->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'image' => $item->image,
                        'categories' => $item->categories,
                        'quantity' => $item->pivot->quantity
                    ];
                })
            ];
        });
    }
}
