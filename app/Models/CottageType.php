<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CottageType extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => 'integer',
    ];

    protected $fillable = [
        'type',
        'price',
        'rate',
        'description',
        'capacity',
    ];

    public function attributes()
    {
        return $this->belongsToMany(CottageAttribute::class);
    }
    public function cottages()
    {
        return $this->hasMany(Cottage::class);
    }
}
