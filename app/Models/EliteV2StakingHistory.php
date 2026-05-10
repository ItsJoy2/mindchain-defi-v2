<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EliteV2StakingHistory extends Model
{
    use HasFactory;

    protected $table = 'elite_v2_staking_histories';

    protected $fillable = [
        'user_id',
        'amount',
        'daily_bonus',
        'wallet',
        'type',
        'duration',
        'received_days',
        'method',
        'description',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:8',
        'daily_bonus' => 'float',
        'duration' => 'integer',
        'received_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
