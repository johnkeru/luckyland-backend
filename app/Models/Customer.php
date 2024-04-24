<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'phoneNumber'
    ];

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('firstName', 'like', '%' . $search . '%')
                    ->orWhere('lastName', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhereHas('reservation', function ($query) use ($search) {
                $query->where('reservationHASH', 'like', '%' . $search . '%');
            });
        }
    }

    public function scopeSearchRecords($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('firstName', 'like', '%' . $search . '%')
                    ->orWhere('lastName', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhereHas('reservation', function ($query) use ($search) {
                $query->where('reservationHASH', 'like', '%' . $search . '%');
            });
        }
    }
    public function scopeFilterByYear($query, $year)
    {
        if ($year) {
            $query->whereHas('reservation', function ($reservationQuery) use ($year) {
                $reservationQuery->whereYear('checkOut', $year);
            });
        }
    }


    public function scopeFilterByMonth($query, $month)
    {
        if ($month) {
            $monthNumber = date('m', strtotime($month));
            $query->whereHas('reservation', function ($reservationQuery) use ($monthNumber) {
                $reservationQuery->whereMonth('checkOut', $monthNumber);
            });
        }
    }


    public function scopeInResort($query, $inResort)
    {
        if ($inResort) {
            $query->whereHas('reservation', function ($query) {
                $query->where('status', 'In Resort');
            });
        }
    }

    public function scopeOrderById($query, $id)
    {
        if ($id) {
            if ($id === 'asc') {
                $query->oldest('id');
            } else if ($id === 'desc') {
                $query->latest('id');
            }
        }
    }

    public function scopeOrderByFirstName($query, $firstName)
    {
        if ($firstName) {
            if ($firstName === 'asc') {
                $query->oldest('firstName');
            } else if ($firstName === 'desc') {
                $query->latest('firstName');
            }
        }
    }

    public function address()
    {
        // return $this->morphMany(Address::class, 'addressable');
        return $this->hasOne(Address::class);
    }

    public function reservation()
    {
        return $this->hasOne(Reservation::class);
    }

    function borrowedItems()
    {
        return $this->belongsToMany(Item::class, 'borrows')
            ->as('borrows')
            ->wherePivotNotIn('status', ['Paid', 'Returned'])
            ->withPivot([
                'status',
                'borrowed_quantity',
                'borrowed_at',
                'returned_at'
            ]);
    }

    function customersWhoBorrows()
    {
        return $this->belongsToMany(Item::class, 'borrows')
            ->as('borrows') //instead of pivot, borrows
            ->wherePivotIn('status', ['Borrowed'])
            ->wherePivotNull('paid')
            ->withPivot(['status', 'borrowed_quantity', 'return_quantity', 'paid', 'borrowed_at', 'returned_at']); //also return the other pivot's attributes
    }

    function customersBorrows() // this is only used in ReservationController
    {
        return $this->belongsToMany(Item::class, 'borrows')
            ->as('borrows')
            ->wherePivotNotIn('status', ['Returned'])
            ->withPivot(['status', 'borrowed_quantity', 'id', 'return_quantity', 'paid', 'borrowed_at', 'returned_at']); //also return the other pivot's attributes
    }
}
