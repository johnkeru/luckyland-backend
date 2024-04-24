<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;


    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    function users()
    {
        // $this->belongsToMany(Personnel::class,
        // 'personnel_role', 'role_id', 'personnel_id');
        return $this->belongsToMany(User::class);
    }
}
