<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherType extends Model
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
        return $this->belongsToMany(OtherAttribute::class);
    }
    public function others()
    {
        return $this->hasMany(Other::class);
    }
}
