<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Kyc extends Model
{
    use HasFactory;
    protected $table ="kycs";
    public function user()
    {

         return $this->belongsTo(User::class, 'user_id');

    }
}
