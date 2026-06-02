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

        // APY
        'days_180',
        'days_365',
        'days_730',
        'days_1825',

        // Level 1
        'days_180_af',
        'days_365_af',
        'days_730_af',
        'days_1825_af',

        // Level 2
        'days_180_af2',
        'days_365_af2',
        'days_730_af2',
        'days_1825_af2',

        // Level 3
        'days_180_af3',
        'days_365_af3',
        'days_730_af3',
        'days_1825_af3',

        'seller_bonus',
        'status',
    ];

    protected $casts = [

        'min_staking' => 'decimal:8',
        'max_staking' => 'decimal:8',

        // APY
        'days_180'  => 'decimal:2',
        'days_365'  => 'decimal:2',
        'days_730'  => 'decimal:2',
        'days_1825' => 'decimal:2',

        // Level 1
        'days_180_af'  => 'decimal:2',
        'days_365_af'  => 'decimal:2',
        'days_730_af'  => 'decimal:2',
        'days_1825_af' => 'decimal:2',

        // Level 2
        'days_180_af2'  => 'decimal:2',
        'days_365_af2'  => 'decimal:2',
        'days_730_af2'  => 'decimal:2',
        'days_1825_af2' => 'decimal:2',

        // Level 3
        'days_180_af3'  => 'decimal:2',
        'days_365_af3'  => 'decimal:2',
        'days_730_af3'  => 'decimal:2',
        'days_1825_af3' => 'decimal:2',

        'seller_bonus' => 'decimal:2',

        'status' => 'boolean',
    ];
}
