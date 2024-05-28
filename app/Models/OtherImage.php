<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'url',
        'other_id',
    ];

    public function other()
    {
        return $this->belongsTo(Other::class);
    }
}
