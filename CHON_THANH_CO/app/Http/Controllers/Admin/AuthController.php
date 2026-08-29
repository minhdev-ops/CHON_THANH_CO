<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $validUsername = hash_equals(config('admin.username'), $credentials['username']);
        $validPassword = hash_equals(config('admin.password'), $credentials['password']);

        if (! $validUsername || ! $validPassword) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Thông tin đăng nhập không chính xác.']);
        }

        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_name', $credentials['username']);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
