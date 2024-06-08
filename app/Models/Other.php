<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Other extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'other_type_id',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class);
    }

    public function images()
    {
        return $this->hasMany(OtherImage::class);
    }

    function items()
    {
        return $this->belongsToMany(Item::class)->withPivot(['quantity', 'needStock', 'reservation_id']);
    }

    function otherType()
    {
        return $this->belongsTo(OtherType::class);
    }
}
