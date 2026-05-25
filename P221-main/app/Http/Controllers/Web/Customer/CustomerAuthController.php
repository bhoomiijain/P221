<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Customer/Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        if (! Auth::user()->isCustomer()) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account is for staff. Use /login instead.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/shop/dashboard');
    }

    public function showRegister()
    {
        return Inertia::render('Customer/Auth/Register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
            'theme' => 'light',
        ]);

        Auth::login($user);

        return redirect('/shop/verify-otp')->with('flash', ['message' => 'Account created! Verify OTP (demo: 123456)']);
    }

    public function showForgot()
    {
        return Inertia::render('Customer/Auth/ForgotPassword');
    }

    public function showOtp()
    {
        return Inertia::render('Customer/Auth/OtpVerify');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        auth()->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/shop');
    }
}
