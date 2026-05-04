<?php

namespace App\Models;
use App\Models\BaseMind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityTokenPackageSettings extends Model
{
    use HasFactory;
    
    public function basemind()
    {
         return $this->belongsTo(BaseMind::class, 'base_mind_id');

    }
}
