<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLogs extends Model
{
    use HasFactory;

    protected $fillable = ['action', 'user_id', 'type'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
