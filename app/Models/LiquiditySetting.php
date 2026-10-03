<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquiditySetting extends Model
{
    protected $fillable = [
        'min_amount',
        'reward_percentage',
        'lock_days',
        'status',
    ];

    protected $casts = [
        'min_amount'         => 'decimal:8',
        'reward_percentage'  => 'decimal:2',
        'lock_days'          => 'integer',
        'status'             => 'boolean',
    ];
}
