<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PackageSettings;

class Purchase extends Model
{
    use HasFactory;
    protected $table ="purchases";
    public function packages()
    {

         return $this->belongsTo(PackageSettings::class, 'package_id');

    }
}
