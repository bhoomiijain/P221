<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return Auth::user()->isCustomer()
                ? redirect('/shop/dashboard')
                : redirect('/');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        if (Auth::user()->isCustomer()) {
            return redirect()->intended('/shop/dashboard');
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        $toShop = $request->user()?->isCustomer();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $toShop ? redirect('/shop') : redirect('/login');
    }
}
