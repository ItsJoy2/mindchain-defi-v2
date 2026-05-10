<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindPurchaseStake extends Model
{
    use HasFactory;

    protected $table = 'mind_purchase_stake';

    protected $fillable = [
        'user_id',
        'amount',
        'duration',
        'received_days',
        'apy_value',
        'total_value',
        'daily',
        'seller_bonus_rate',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'apy_value' => 'float',
        'total_value' => 'float',
        'daily' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
