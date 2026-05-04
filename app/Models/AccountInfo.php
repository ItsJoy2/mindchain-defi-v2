<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentWay;

class AccountInfo extends Model
{
    use HasFactory;
      protected $table ="walletttt_names";
      public function payment_way(){
    return $this->belongsTo(PaymentWay::class, 'payment_way_id');
  }
}
