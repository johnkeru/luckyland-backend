<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'room_type_id',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    function items()
    {
        return $this->belongsToMany(Item::class)->withPivot(['minQuantity', 'maxQuantity', 'isBed', 'needStock', 'reservation_id']);
    }

    function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
