<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waste extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'quantity' => 'integer'
    ];
    protected $fillable = [
        'quantity',
        'reservation_id',
        'item_id'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }


    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('item.categories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            })
                ->orWhere('quantity', 'like', '%' . $search . '%');
        }
    }
    public function scopeOrderByQuantity($query, $quantity)
    {
        if ($quantity) {
            if ($quantity == 'asc') {
                $query->oldest('quantity');
            } else if ($quantity == 'desc') {
                $query->latest('quantity');
            }
        }
    }
    public function scopeOrderByItemName($query, $order)
    {
        if ($order) {
            if ($order === 'asc') {
                $query->join('items', 'items.id', '=', 'wastes.item_id')
                    ->oldest('items.name');
            } else {
                if ($order === 'desc') {
                    $query->join('items', 'items.id', '=', 'wastes.item_id')
                        ->latest('items.name');
                }
            }
        }
    }
    public function scopeFilterByCategory($query, $category)
    {
        if ($category) {
            $query->whereHas('item.category', function ($categoryQuery) use ($category) {
                $categoryQuery->where('name', 'like', '%' . $category . '%');
            });
        }
    }
}
