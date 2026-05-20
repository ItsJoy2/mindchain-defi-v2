<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EliteStaking extends Model
{
    use HasFactory;

    protected $table = 'elite_stakings';

    protected $fillable = [
        'user_id',
        'amount',
        'daily_bonus',
        'wallet',
        'duration',
        'received_days',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:8',
        'daily_bonus' => 'float',
        'duration' => 'integer',
        'received_days' => 'integer',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
