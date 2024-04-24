<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'email',
        'password',
        'firstName',
        'middleName',
        'lastName',
        'image',
        'job_title',
        'graduated_at',
        'description',
        'status',
        'facebook',
        'instagram',
        'twitter',
        'phoneNumber',
        'type',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    function activityLogs()
    {
        return $this->hasMany(EmployeeLogs::class, 'user_id', 'id');
    }

    public function address()
    {
        // return $this->morphMany(Address::class, 'addressable');
        return $this->hasOne(Address::class);
    }

    // FILTERS
    public function scopeWithTrashcan($query, $trash)
    {
        if ($trash) {
            $query->onlyTrashed();
        }
    }
    public function scopeOnlyActive($query, $status)
    {
        $query->where('status', $status);
    }

    public function scopeFilterByRole($query, $role)
    {
        if ($role) {
            $query->WhereHas('roles', function ($roleQuery) use ($role) {
                $roleQuery->where('roleName', 'like', '%' . $role . '%');
            });
        }
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('firstName', 'like', '%' . $search . '%')
                ->orWhere('middleName', 'like', '%' . $search . '%')
                ->orWhere('lastName', 'like', '%' . $search . '%')
                ->orWhere('phoneNumber', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhereHas('address', function ($addressQuery) use ($search) {
                    $addressQuery->where('barangay', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%')
                        ->orWhere('province', 'like', '%' . $search . '%');
                })->orWhereHas('roles', function ($roleQuery) use ($search) {
                    $roleQuery->where('roleName', 'like', '%' . $search . '%');
                });
        }
    }


    public function scopeOrderByFirstName($query, $firstName)
    {
        if ($firstName == 'asc') {
            $query->oldest('firstName');
        } else if ($firstName == 'desc') {
            $query->latest('firstName');
        }
    }

    public function scopeOrderByAddress($query, $address)
    {
        $query->join('addresses', 'users.id', '=', 'addresses.user_id')
            ->select('users.*') // Avoid selecting everything from the addresses table
            ->orderBy('addresses.barangay', $address == 'asc' ? 'asc' : 'desc');
    }
}
