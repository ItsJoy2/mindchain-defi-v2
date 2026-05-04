<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_name',
        'country',
        'city',
        'postal_code',
        'address',
        'sponsor',
        'is_admin',
        'bmind_team_invest',
        'status',
        'key_id',
        'is_email_verified',
        ''
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = ['referral_link'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function sponsors()
    {
        return $this->belongsTo(User::class,'sponsor');
    }


/**
 * A user has many referrals.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function referrals()
    {
        return $this->hasMany(User::class, 'sponsor', 'id');
    }
    public function getReferralLinkAttribute()
    {
        if($this->key_id == null )
        {
            $code = $this->user_name;
        }else{
            $code= $this->key_id;
        }
        return $this->referral_link = route('register', ['ref' => $code]);
    }


    public function purchaseBmind()
    {
        return $this->hasMany(PurcahseCommunityToken::class, 'user_id', 'id');
    }

    public function purchaseAmount()
    {
        return $this->purchaseBmind()->sum('token_amount');
    }

    public function referralsPurchaseAmount()
    {
        return $this->referrals()->with('purchaseBmind')->get()->sum(function ($referral) {
            return $referral->purchaseAmount();
        });
    }


}
