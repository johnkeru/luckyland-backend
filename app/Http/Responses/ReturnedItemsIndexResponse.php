<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ReturnedItemsIndexResponse implements Responsable
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function toResponse($request)
    {
        $currentPage = $this->customers->currentPage();
        $perPage = $this->customers->perPage();
        $total = $this->customers->total();
        $lastPage = $this->customers->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->customers->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->customers->nextPageUrl() : null;

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
                'url' => $this->customers->url($i),
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
            'first_page_url' => $this->customers->url(1),
            'from' => $this->customers->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->customers->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->customers->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->customers->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->customers->map(function ($returnedItem) {
            return [
                'id' => $returnedItem->borrows->id,
                'name' => $returnedItem->firstName . ' ' . $returnedItem->lastName,
                'return_quantity' => $returnedItem->borrows->return_quantity,
                'returned_at' => $returnedItem->borrows->returned_at,
                'borrowed_quantity' => $returnedItem->borrows->borrowed_quantity,
                'paid' => $returnedItem->borrows->paid
            ];
        });
    }
}
