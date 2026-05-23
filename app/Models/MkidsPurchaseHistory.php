<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkidsPurchaseHistory extends Model
{
    use HasFactory;

    protected $table = 'mkids_purchase_history';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'method',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * User Relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
