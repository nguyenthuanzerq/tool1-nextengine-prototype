<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $oldSessionId = $request->session()->getId();

        $remember = $request->has('remember');
        if (Auth::attempt($credentials, $remember)) {
            return redirect()->intended('/btoc');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
