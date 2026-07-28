<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('staff.pos');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['phone' => 'required|string', 'password' => 'required|string']);
        $user = User::where('phone', $credentials['phone'])->where('active', true)->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['Thông tin đăng nhập không đúng.'])->withInput();
        }
        Auth::login($user, $request->boolean('remember'));
        return $user->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('staff.pos');
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }
}
