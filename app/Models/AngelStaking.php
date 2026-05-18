<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AngelStaking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'duration',
        'daily_bonus',
        'received_days',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'daily_bonus' => 'decimal:8',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
