<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the profile page (full-page, no sidebar layout).
     */
    public function show()
    {
        return Inertia::render('Profile/Show');
    }

    /**
     * Update the authenticated user's profile fields.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city'    => ['nullable', 'string', 'max:100'],
            'state'   => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
        ];

        if ($user->isPharmacist()) {
            $rules['license_number'] = ['nullable', 'string', 'max:50'];
            $rules['pharmacy_name']  = ['nullable', 'string', 'max:255'];
        }

        if ($user->isSupplier()) {
            $rules['business_name'] = ['nullable', 'string', 'max:255'];
            $rules['gst_number']    = ['nullable', 'string', 'max:30'];
            $rules['website']       = ['nullable', 'url', 'max:255'];
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        // Sync relevant fields to the Supplier record if applicable
        if ($user->isSupplier()) {
            Supplier::where('user_id', $user->getKey())->update([
                'name'       => $validated['name'],
                'phone'      => $validated['phone'] ?? null,
                'address'    => $validated['address'] ?? null,
                'city'       => $validated['city'] ?? null,
                'state'      => $validated['state'] ?? null,
                'pincode'    => $validated['pincode'] ?? null,
                'gst_number' => $validated['gst_number'] ?? null,
                'website'    => $validated['website'] ?? null,
                'search_key' => strtolower($validated['name']),
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload and save profile avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar_url) {
            // Support both old /storage/ paths and new /avatar/ paths
            $oldPath = str_starts_with($user->avatar_url, '/avatar/')
                ? 'public/avatars/' . basename($user->avatar_url)
                : str_replace('/storage/', 'public/', $user->avatar_url);
            Storage::delete($oldPath);
        }

        $ext  = $request->file('avatar')->getClientOriginalExtension();
        $name = 'avatar_' . $user->getKey() . '.' . $ext;
        $path = $request->file('avatar')->storeAs('public/avatars', $name);

        // Use /avatar/{name} prefix — served by proxy route, NOT symlink
        $url = '/avatar/' . $name;
        $user->update(['avatar_url' => $url]);

        // Sync to supplier record too
        if ($user->isSupplier()) {
            Supplier::where('user_id', $user->getKey())->update(['avatar_url' => $url]);
        }

        return back()->with('success', 'Profile photo updated!');
    }
}
