<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseMind extends Model
{
    use HasFactory;
    protected $fillable = [
        'total_token_sell',
    ];
    
    public function communityTokenPackageSettings()
    {
        return $this->hasMany(CommunityTokenPackageSettings::class, 'base_mind_id');
    }

}
