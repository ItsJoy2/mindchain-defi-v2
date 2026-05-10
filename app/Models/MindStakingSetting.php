<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindStakingSetting extends Model
{
    use HasFactory;

    protected $table = 'mind_staking_settings';

    protected $fillable = [
        'min_staking',
        'max_staking',
        'days_90',
        'days_180',
        'days_365',
        'days_730',
        'days_1825',
        'days_90_af',
        'days_180_af',
        'days_365_af',
        'days_730_af',
        'days_1825_af',
        'seller_bonus',
        'status',
    ];
}
