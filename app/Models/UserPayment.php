<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentWay;

class UserPayment extends Model
{
    use HasFactory;
      protected $table ="user_payments";
      public function payment_way(){
    return $this->belongsTo(PaymentWay::class, 'payment_way_id');
  }
}
