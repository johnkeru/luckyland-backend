<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'companyName',
        'user_id',
        'status',
        'arrivalDate',
        'bill',
    ];

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('items', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('categories', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', '%' . $search . '%');
                        });
                })->orWhereHas('manage', function ($manageQuery) use ($search) {
                    $manageQuery->where('firstName', 'like', '%' . $search . '%')
                        ->orWhere('lastName', 'like', '%' . $search . '%');
                });
            })
                ->orWhere('companyName', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%');
        }
    }

    public function scopeFilterByStatus($query, $status)
    {
        if ($status) $query->where('status', '=', $status);
    }

    public function scopeOrderByCompanyName($query, $companyName)
    {
        if ($companyName == 'asc') {
            $query->oldest('companyName');
        } else if ($companyName == 'desc') {
            $query->latest('companyName');
        }
    }

    function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }

    function manage()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
