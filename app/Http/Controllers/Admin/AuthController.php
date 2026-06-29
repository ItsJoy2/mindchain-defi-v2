<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.pages.authentication.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required',
            'password' => 'required',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'user_name';


        $credentials = [
            $loginField => $request->login,
            'password'  => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput()
                ->withErrors([
                    'login' => 'Invalid credentials.'
                ]);
        }

        $request->session()->regenerate();

        if (!auth()->user()->is_admin) {
            Auth::logout();

            return back()->withErrors([
                'login' => 'Admin access required.'
            ]);
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function profile()
    {
        $admin = Auth::user();

        return view('admin.pages.authentication.profile', compact('admin'));
    }
    public function profileUpdate(Request $request)
    {
        $admin = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'user_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($admin->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($admin->id)
            ],
            'contact' => 'nullable|string|max:50',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($admin->image && file_exists(public_path($admin->image))) {
                unlink(public_path($admin->image));
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('uploads/admin'), $filename);

            $admin->image = 'uploads/admin/' . $filename;
        }

        $admin->update([
            'name'      => $request->name,
            'user_name' => $request->user_name,
            'email'     => $request->email,
            'contact'   => $request->contact,
            'image'     => $admin->image,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::user();

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.'
            ]);
        }

        $admin->password = $request->password;
        $admin->save();

        return back()->with('success', 'Password changed successfully.');
    }
}
