<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EliteSetting extends Model
{
    use HasFactory;
    protected $table = 'elite_settings';

    protected $fillable = [
        'mem_fee',
        'sponsor_bonus',
        'lvl1',
        'lvl2',
        'duration',
        'daily_bonus',
        'status',
    ];

    protected $casts = [
        'mem_fee'        => 'integer',
        'sponsor_bonus'  => 'float',
        'lvl1'           => 'integer',
        'lvl2'           => 'integer',
        'daily_bonus'    => 'decimal:3',
        'status'         => 'boolean',
    ];
}
