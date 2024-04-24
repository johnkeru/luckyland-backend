<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class BackupIndexResponse implements Responsable
{
    protected $backups;

    public function __construct($backups)
    {
        $this->backups = $backups;
    }

    public function toResponse($request)
    {
        $currentPage = $this->backups->currentPage();
        $perPage = $this->backups->perPage();
        $total = $this->backups->total();
        $lastPage = $this->backups->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->backups->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->backups->nextPageUrl() : null;

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
                'url' => $this->backups->url($i),
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
            'first_page_url' => $this->backups->url(1),
            'from' => $this->backups->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->backups->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->backups->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->backups->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->backups->map(function ($backup) {
            return [
                'id' => $backup->id,
                'filename' => $backup->filename,
                'size' => $backup->size,
                'status' => $backup->status,
                'checksum' => $backup->checksum,
                'storage_location' => $backup->storage_location,
            ];
        });
    }
}
