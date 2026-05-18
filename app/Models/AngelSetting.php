<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AngelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_fee',
        'duration',
        'apy',
        'level_1_bonus',
        'level_2_bonus',
        'level_3_bonus',
        'total_member',
        'status',
    ];

    protected $casts = [
        'membership_fee' => 'decimal:8',
        'apy' => 'decimal:2',
        'level_1_bonus' => 'decimal:2',
        'level_2_bonus' => 'decimal:2',
        'level_3_bonus' => 'decimal:2',
        'status' => 'boolean',
    ];
}
