<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositJob extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_id',
        'amount',
        'wallet',
        'wallet_address',
        'tx_hash',
        'status',
        'gateway_response',
        'paid_at'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
