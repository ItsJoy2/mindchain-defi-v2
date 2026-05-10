<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelSetting extends Model
{
    use HasFactory;

    protected $table = 'level_settings';

    protected $fillable = [
        'lvl_1',
        'lvl_2',
        'lvl_3',
        'lvl_4',
        'lvl_5',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

}
