<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeUserImportSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('old_mysql')
            ->table('users')
            ->orderBy('id')
            ->chunk(1000, function ($users) {

                $insertData = [];

                foreach ($users as $old) {

                    $insertData[] = [
                        'sponsor_id' => $old->sponsor_id,

                        'user_name' => $old->user_name,
                        'email' => $old->email,
                        'password' => $old->password,

                        'name' => $old->name,
                        'image' => $old->image,
                        
                        'referral_code' => $old->referral_code,

                        'date_of_birth' => $old->date_of_birth,
                        'gender' => $old->gender,

                        'contact' => $old->contact,

                        'address' => $old->address,
                        'city' => $old->city,
                        'country' => $old->country,
                        'postal_code' => $old->postal_code,

                        'email_verified_at' => $old->email_verified_at,

                        'nid_passport' => $old->nid_passport,

                        'is_admin' => $old->is_admin,
                        'status' => $old->status ?? 1,

                        'merchant_status' => $old->merchant_status ?? 0,
                        'kyc' => $old->kyc ?? 0,
                        'consultant' => $old->consultant ?? 0,

                        'ambassador' => $old->ambassador ?? 0,
                        'elite_club' => $old->elite_club ?? 0,
                        'angel_club' => $old->angel_club ?? 0,

                        'last_login' => $old->last_login,

                        'created_at' => $old->created_at,
                        'updated_at' => $old->updated_at,
                    ];
                }

                DB::table('users')->insertOrIgnore($insertData);
            });
    }
}
