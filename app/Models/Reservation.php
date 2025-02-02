<?php

namespace App\Models;

use App\Events\Reservation\ReservationCancelled;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Reservation extends Model
{
    use HasFactory, Prunable;

    public function prunable()
    {
        return self::query()
            ->where('status', 'Cancelled');
    }

    public static function updateStatusToCancelled()
    {
        $reservationsToCancel = self::query()
            ->where('status', 'Approved')
            ->whereDate('checkIn', '<', now()->addDay()->toDateString())
            ->get();

        foreach ($reservationsToCancel as $cancelledReservation) {
            info($cancelledReservation->reservationHASH . ' has been cancelled!');
            $cancelledReservation->update(['status' => 'Cancelled']);
            ReservationCancelled::dispatch($cancelledReservation);
        }
    }


    protected $fillable = [
        'reservationHASH',
        'checkIn',
        'checkOut',

        'actualCheckIn',
        'actualCheckOut',

        'totalRoomsPrice',
        'totalCottagesPrice',
        'totalOthersPrice',
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

        'isChecked',
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
                    })->orWhereHas('cottages', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('others', function ($categoryQuery) use ($search) {
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

    public function scopeFilterByOther($query, $other)
    {
        if ($other) {
            if ($other) $query->where(function ($query) use ($other) {
                $query->whereHas('others', function ($otherQuery) use ($other) {
                    $otherQuery->where('name', '=', $other);
                });
            });
        }
    }
}
