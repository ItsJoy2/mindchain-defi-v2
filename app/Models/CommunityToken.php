<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityToken extends Model
{
    use HasFactory;
    protected $table ="community_tokens";
    
    protected $fillable = [
        'image',
        'token_name',
        'contract_address',
        'blockchain',
        'total_supply',

    ];
}
