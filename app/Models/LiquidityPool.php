<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidityPool extends Model
{
    protected $fillable = [
        'user_id',
        'wallet',
        'invested_amount',
        'reward_percentage',
        'reward_amount',
        'total_return_amount',
        'lock_days',
        'invested_at',
        'release_at',
        'status',
        'released_at',
        'description',
    ];

    protected $casts = [
        'invested_amount'     => 'decimal:8',
        'reward_percentage'   => 'decimal:2',
        'reward_amount'       => 'decimal:8',
        'total_return_amount' => 'decimal:8',
        'invested_at'         => 'datetime',
        'release_at'          => 'datetime',
        'released_at'         => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
