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
        'chain_id',
        'type',
        'contract_address',
        'wallet_address',
        'tx_hash',
        'status',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'chain_id' => 'integer',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
