<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class EmployeeLogsResponse implements Responsable
{
    protected $empLogs;
    protected $unread;

    public function __construct($empLogs, $unread)
    {
        $this->empLogs = $empLogs;
        $this->unread = $unread;
    }

    public function toResponse($request)
    {
        $currentPage = $this->empLogs->currentPage();
        $perPage = $this->empLogs->perPage();
        $total = $this->empLogs->total();
        $lastPage = $this->empLogs->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->empLogs->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->empLogs->nextPageUrl() : null;

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
                'url' => $this->empLogs->url($i),
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
            'first_page_url' => $this->empLogs->url(1),
            'from' => $this->empLogs->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->empLogs->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->empLogs->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->empLogs->lastItem(),
            'total' => $total,
            'unread' => $this->unread,
            'data' => $data,
        ]);
    }


    protected function transformInventories()
    {
        return $this->empLogs->map(function ($empLog) {
            return [
                "id" => $empLog->id,
                "action" => $empLog->action,
                "type" => $empLog->type,
                "visited" => $empLog->visited,
                "user_id" => $empLog->user_id,
                "created_at" => $empLog->created_at,
                "updated_at" => $empLog->updated_at
            ];
        });
    }
}
