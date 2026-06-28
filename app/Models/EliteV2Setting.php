<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EliteV2Setting extends Model
{
    use HasFactory;

    protected $table = 'elite_v2_settings';

    protected $fillable = [
        'mem_fee',
        'daily_bonus',
        'duration',
        'sponsor_bonus',
        'lvl1',
        'lvl2',
        'status',
    ];

    protected $casts = [
        'mem_fee' => 'integer',
        'daily_bonus' => 'decimal:3',
        'duration' => 'integer',
        'sponsor_bonus' => 'float',
        'lvl1' => 'integer',
        'lvl2' => 'integer',
        'status' => 'boolean',
    ];
}
