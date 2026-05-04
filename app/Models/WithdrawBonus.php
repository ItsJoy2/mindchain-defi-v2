<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Withdraw;

class WithdrawBonus extends Model
{
    use HasFactory;
    protected $table ="withdraw_bonuses";
      protected $guarded =[];
    public function user()
    {

         return $this->belongsTo(User::class, 'user_id');

    }
    public function wallet()
    {

         return $this->belongsTo(UserWallet::class, 'wallet_method_id');

    }
}
