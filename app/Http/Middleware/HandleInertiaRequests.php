<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'demoAccounts' => [
                ['role' => 'Customer', 'email' => 'customer@pharmacy.local', 'password' => 'password', 'url' => '/shop/dashboard'],
                ['role' => 'Pharmacist', 'email' => 'demo@pharmacy.local', 'password' => 'password', 'url' => '/'],
                ['role' => 'Supplier', 'email' => 'demo-supplier@pharmacy.local', 'password' => 'password', 'url' => '/'],
            ],
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->getKey(),
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'theme'             => $user->theme ?? null,
                    'phone'             => $user->phone ?? null,
                    'address'           => $user->address ?? null,
                    'city'              => $user->city ?? null,
                    'state'             => $user->state ?? null,
                    'pincode'           => $user->pincode ?? null,
                    'license_number'    => $user->license_number ?? null,
                    'pharmacy_name'     => $user->pharmacy_name ?? null,
                    'business_name'     => $user->business_name ?? null,
                    'gst_number'        => $user->gst_number ?? null,
                    'website'           => $user->website ?? null,
                    'avatar_url'        => $user->avatar_url ?? null,
                    'profileCompletion' => $user->profileCompletion(),
                    'created_at'        => $user->created_at?->toDateTimeString(),
                ] : null,
            ],
        ];
    }
}
