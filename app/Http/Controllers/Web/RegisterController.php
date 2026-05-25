<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return Auth::user()->isCustomer()
                ? redirect('/shop/dashboard')
                : redirect('/');
        }

        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:pharmacist,supplier,customer'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'theme'    => 'light',
        ]);

        if ($user->role === 'supplier') {
            Supplier::create([
                'user_id' => $user->getKey(),
                'name'    => $user->name,
                'email'   => $user->email,
                'search_key' => strtolower($user->name),
            ]);
        }

        Auth::login($user);

        if ($user->isCustomer()) {
            return redirect()->intended('/shop/dashboard');
        }

        return redirect()->intended('/');
    }
}
