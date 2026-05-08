<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsdtStakingHistory extends Model
{
    use HasFactory;

    protected $table = 'usdt_staking_histories';

    protected $fillable = [
        'user_id',
        'amount',
        'daily_bonus',
        'wallet',
        'type',
        'method',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'daily_bonus' => 'float',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
