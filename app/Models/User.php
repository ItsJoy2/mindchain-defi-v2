<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'sponsor_id',
        'user_name',
        'email',
        'password',
        'referral_code',
        'name',
        'image',
        'date_of_birth',
        'gender',
        'contact',
        'address',
        'city',
        'country',
        'postal_code',
        'nid_passport',
        'is_admin',
        'status',
        'merchant_status',
        'kyc',
        'consultant',
        'ambassador',
        'elite_club',
        'angel_club',
        'last_login'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login' => 'datetime',
        'is_admin' => 'boolean',
        'status' => 'boolean',
        'merchant_status' => 'boolean',
        'kyc' => 'boolean',
        'consultant' => 'boolean',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'sponsor_id');
    }
}
