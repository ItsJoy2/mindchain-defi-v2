<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkidsStakingSetting extends Model
{
    use HasFactory;

    protected $table = 'mkids_staking_settings';

    protected $fillable = [
        'amount',
        'token_bonus',
        'level_1_bonus',
        'level_2_bonus',
        'level_3_bonus',
        'status',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'token_bonus'     => 'decimal:2',
        'level_1_bonus'   => 'decimal:2',
        'level_2_bonus'   => 'decimal:2',
        'level_3_bonus'   => 'decimal:2',
        'status'          => 'boolean',
    ];
}
