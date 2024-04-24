<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class EmployeeIndexResponse implements Responsable
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function toResponse($request)
    {
        $currentPage = $this->employees->currentPage();
        $perPage = $this->employees->perPage();
        $total = $this->employees->total();
        $lastPage = $this->employees->lastPage();

        $prevPageUrl = $currentPage > 1 ? $this->employees->previousPageUrl() : null;
        $nextPageUrl = $currentPage < $lastPage ? $this->employees->nextPageUrl() : null;

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
                'url' => $this->employees->url($i),
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
            'first_page_url' => $this->employees->url(1),
            'from' => $this->employees->firstItem(),
            'last_page' => $lastPage,
            'last_page_url' => $this->employees->url($lastPage),
            'links' => $links,
            'next_page_url' => $nextPageUrl,
            'path' => $this->employees->url(1),
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $this->employees->lastItem(),
            'total' => $total,
        ]);
    }


    protected function transformInventories()
    {
        return $this->employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'firstName' => $employee->firstName,
                'middleName' => $employee->middleName,
                'deleted_at' => $employee->deleted_at,

                'lastName' => $employee->lastName,
                'type' => $employee->type,
                'image' => $employee->image,
                'email' => $employee->email,
                'phoneNumber' => $employee->phoneNumber,
                'graduated_at' => $employee->graduated_at,
                'description' => $employee->description,
                'facebook' => $employee->facebook,
                'instagram' => $employee->instagram,
                'twitter' => $employee->twitter,
                'status' => $employee->status,
                'email_verified_at' => $employee->email_verified_at,
                'deleted_at' => $employee->deleted_at,
                'roles' => $employee->roles,
                'address' => $employee->address
            ];
        });
    }
}
