<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAttribute extends Model
{
    use HasFactory;

    protected $table = 'room_attributes';

    protected $fillable = [
        'name',
        'type',
    ];

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class);
    }
}
