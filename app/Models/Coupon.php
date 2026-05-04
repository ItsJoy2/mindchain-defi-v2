<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Coupon extends Model
{
    use HasFactory;
    protected $table ="coupons";
       protected $guarded =[];
      public function created_by()
      {

           return $this->belongsTo(User::class, 'created_by');

      }
      public function owned()
      {
          return $this->belongsTo(User::class,'owned_by');
      }
     
       
      
}
