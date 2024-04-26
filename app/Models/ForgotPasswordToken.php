<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForgotPasswordToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'expiry_time',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
