<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkidsStakingProgram extends Model
{
    use HasFactory;

    protected $table = 'mkids_staking_programs';

    protected $fillable = [
        'user_id',
        'kids_name',
        'kids_username',
        'kids_father_name',
        'kids_mother_name',
        'dob',
        'age',
        'kids_birth_place',
        'country',
        'count',
    ];

    protected $casts = [
        'dob'   => 'date',
        'age'   => 'integer',
        'count' => 'integer',
    ];

    /**
     * User Relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
