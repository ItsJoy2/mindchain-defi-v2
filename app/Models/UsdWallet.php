<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsdWallet extends Model
{
    use HasFactory;
        protected $table ="usd_wallets";
        
        protected $fillable = [
        'status',
        'transaction_hash',
        // other fillable fields in your model
    ];
    public function user()
    {

         return $this->belongsTo(User::class, 'user_id');

    }
    public function merchant()
    {
        return $this->belongsTo(AccountInfo::class,'wallet_id');
    }
    
    public function wallet()
    {

         return $this->belongsTo(UserWallet::class, 'wallet_id');

    }
    
     public function payment()
    {

         return $this->belongsTo(UserPayment::class, 'payment_method_id');

    }
    
    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id');
    }
    public function sender()
    {
        return $this->belongsTo(User::class,'received_from');
    }

}
