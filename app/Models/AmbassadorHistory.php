<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmbassadorHistory extends Model
{
    use HasFactory;

    protected $table = 'ambassador_histories';
    protected $fillable = [
        'user_id',
        'amount',
        'wallet',
        'type',
        'method',
        'description',
        'status',
    ];


    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
