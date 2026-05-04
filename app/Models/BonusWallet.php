<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AccountInfo;

class BonusWallet extends Model
{
    use HasFactory;
      protected $table ="bonus_wallets";
       protected $guarded =[];
      public function user()
      {

           return $this->belongsTo(User::class, 'user_id');

      }
      public function receiver()
      {
          return $this->belongsTo(User::class,'receiver_id');
      }
      public function sender()
      {
          return $this->belongsTo(User::class,'received_from');
      }
      
       
       public function merchant()
    {
        return $this->belongsTo(AccountInfo::class,'wallet_id');
    }
}
