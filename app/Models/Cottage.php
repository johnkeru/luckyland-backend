<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cottage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'cottage_type_id',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class)->withPivot('isCottageOvernight');
    }
    public function images()
    {
        return $this->hasMany(CottageImage::class);
    }
    function items()
    {
        return $this->belongsToMany(Item::class)->withPivot(['quantity', 'needStock']);
    }
    function cottageType()
    {
        return $this->belongsTo(CottageType::class);
    }
}
