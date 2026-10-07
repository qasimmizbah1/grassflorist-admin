<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements CanResetPasswordContract, JWTSubject
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'source_id',
        'username',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'zip_code',
        'city',
        'district',
        'state',
        'country',
        'address_2',
        'password',
        'meta_data',
        'registered_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'registered_at' => 'datetime',
            'meta_data' => 'array',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}