<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BmindStakingSetting extends Model
{
    use HasFactory;

    protected $table = 'bmind_staking_settings';

    protected $fillable = [

        'min_staking',
        'max_staking',

        // Profit Revenue
        'days_180',
        'days_365',
        'days_730',

        // Affiliate Commission
        'days_180_af',
        'days_365_af',
        'days_730_af',

        'seller_bonus',
        'status',
    ];

    protected $casts = [

        'min_staking' => 'decimal:8',
        'max_staking' => 'decimal:8',

        'days_180' => 'decimal:2',
        'days_365' => 'decimal:2',
        'days_730' => 'decimal:2',

        'days_180_af' => 'decimal:2',
        'days_365_af' => 'decimal:2',
        'days_730_af' => 'decimal:2',

        'seller_bonus' => 'decimal:2',

        'status' => 'boolean',
    ];
}
