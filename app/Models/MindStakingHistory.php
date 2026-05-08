<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindStakingHistory extends Model
{
    use HasFactory;

    protected $table = 'mind_staking_history';

    protected $fillable = [
        'user_id',
        'amount',
        'wallet',
        'type',
        'method',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
