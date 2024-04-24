<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'maxQuantity' => 'integer',
        'currentQuantity' => 'integer',
        'reOrderPoint' => 'integer',
        'price' => 'integer',
    ];

    protected $hidden = [
        'delivery_id',
        'waste_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'isBorrowable',

        'maxQuantity',
        'reOrderPoint',
        'currentQuantity',
        'status',
        'lastCheck',
    ];


    protected static function booted()
    {
        static::deleting(function ($item) {
            // Soft delete related records
            $item->waste()->delete();
            $item->unavailable()->delete();
        });

        static::restoring(function ($item) {
            // Restore related records
            $item->waste()->withTrashed()->restore();
            $item->unavailable()->withTrashed()->restore();
        });
    }


    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function deliveries()
    {
        return $this->belongsToMany(Delivery::class)->withPivot('quantity');
    }
    function waste()
    {
        return $this->hasOne(Waste::class);
    }
    function unavailable()
    {
        return $this->hasOne(Unavailable::class);
    }
    function rooms()
    {
        return $this->belongsToMany(Room::class)->withPivot(['minQuantity', 'maxQuantity', 'isBed']);
    }
    function cottages()
    {
        return $this->belongsToMany(Cottage::class)->withPivot('quantity');
    }


    public function scopeWithTrashcan($query, $trash)
    {
        if ($trash) {
            $query->onlyTrashed();
        }
    }
    public function scopeFilterByStatus($query, $status)
    {
        if ($status) $query->where('status', '=', $status);
    }
    public function scopeOrderByItemName($query, $order)
    {
        if ($order === 'asc') {
            $query->oldest('name');
        } else {
            if ($order === 'desc') {
                $query->latest('name');
            }
        }
    }
    public function scopeFilterByCategory($query, $category)
    {
        if ($category) {
            $query->whereHas('categories', function ($categoryQuery) use ($category) {
                $categoryQuery->where('name', $category);
            });
        }
    }

    public function scopeOrderByCurrentQuantity($query, $currentQuantity)
    {
        if ($currentQuantity == 'asc') {
            $query->oldest('currentQuantity');
        } else if ($currentQuantity == 'desc') {
            $query->latest('currentQuantity');
        }
    }
    public function scopeOrderBy($query, $lastCheck)
    {
        if ($lastCheck == 'asc') {
            $query->oldest('lastCheck');
        } else if ($lastCheck == 'desc') {
            $query->latest('lastCheck');
        }
    }
    // END OF FILTERS FUNCTIONS

    function customersWhoBorrows()
    {
        return $this->belongsToMany(Customer::class, 'borrows') //custom pivot table 
            ->as('borrows') //instead of pivot, borrows
            ->wherePivotIn('status', ['Borrowed'])
            ->wherePivotNull('paid')
            ->withPivot(['status', 'borrowed_quantity', 'return_quantity', 'paid', 'borrowed_at', 'returned_at']); //also return the other pivot's attributes
    }

    function customersWhoReturn()
    {
        return $this->belongsToMany(Customer::class, 'borrows')
            ->as('borrows') //instead of pivot, borrows
            ->wherePivotIn('status', ['Returned', 'Paid'])
            ->withPivot(['status', 'borrowed_quantity', 'id', 'return_quantity', 'paid', 'borrowed_at', 'returned_at']);
    }
}
