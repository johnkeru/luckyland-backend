<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CottageAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function cottageTypes()
    {
        return $this->belongsToMany(CottageType::class);
    }
}
