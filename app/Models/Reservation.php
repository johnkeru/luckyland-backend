<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservationHASH',
        'checkIn',
        'checkOut',

        'actualCheckIn',
        'actualCheckOut',

        'totalRoomsPrice',
        'totalCottagesPrice',
        'days',
        'accommodationType',

        'total',
        'paid',
        'balance',
        'refund',
        'guests',

        'status',
        'user_id',
        'customer_id',
        'isWalkIn',

        'gCashRefNumber',
        'gCashRefNumberURL',

        'isMinimumAccepted',
        'isPaymentWithinDay',
        'isConfirmed',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
    public function cottages()
    {
        return $this->belongsToMany(Cottage::class);
    }
    public function others()
    {
        return $this->belongsToMany(Other::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($itemQuery) use ($search) {
                    $itemQuery->where('email', 'like', '%' . $search . '%')
                        ->orWhere('phoneNumber', 'like', '%' . $search . '%')
                        ->orWhere('firstName', 'like', '%' . $search . '%')
                        ->orWhere('lastName', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('rooms', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            })
                ->orWhere('reservationHASH', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%');
        }
    }

    public function scopeFilterByMonth($query, $month)
    {
        if ($month) {
            $monthNumber = $this->getMonthNumber($month);
            $query->where(function ($query) use ($monthNumber) {
                $query->whereMonth('checkIn', $monthNumber)
                    ->orWhereMonth('checkOut', $monthNumber);
            });
        }
    }

    private function getMonthNumber($monthName)
    {
        $months = [
            'January' => '01',
            'February' => '02',
            'March' => '03',
            'April' => '04',
            'May' => '05',
            'June' => '06',
            'July' => '07',
            'August' => '08',
            'September' => '09',
            'October' => '10',
            'November' => '11',
            'December' => '12',
        ];

        return $months[$monthName] ?? null;
    }

    public function scopeFilterByStatus($query, $status)
    {
        if ($status && $status !== 'all') $query->where('status', '=', $status);
    }

    public function scopeFilterByRoom($query, $room)
    {
        if ($room) {
            if ($room) $query->where(function ($query) use ($room) {
                $query->whereHas('rooms', function ($roomQuery) use ($room) {
                    $roomQuery->where('name', '=', $room);
                });
            });
        }
    }

    public function scopeFilterByCottage($query, $cottage)
    {
        if ($cottage) {
            if ($cottage) $query->where(function ($query) use ($cottage) {
                $query->whereHas('cottages', function ($cottageQuery) use ($cottage) {
                    $cottageQuery->where('name', '=', $cottage);
                });
            });
        }
    }
}
