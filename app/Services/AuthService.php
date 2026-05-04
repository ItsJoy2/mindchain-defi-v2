<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function attemptLogin($username, $password)
    {
        $user = User::where('user_name', $username)->first();

        if (!$user) return null;

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
