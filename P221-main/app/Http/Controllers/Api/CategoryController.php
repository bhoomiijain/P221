<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Return master categories + the authenticated user's own categories.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            // Unauthenticated: return master categories only
            return Category::query()->where('type', 'master')->orderBy('name')->get();
        }

        return Category::query()
            ->masterOrOwned($user->getKey())
            ->orderBy('type')   // master first
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a user-defined category scoped to the authenticated user.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $category = Category::create([
            'name'    => $data['name'],
            'type'    => 'user',
            'user_id' => $user?->getKey(),
        ]);

        return response()->json($category, 201);
    }

    /**
     * Delete a category.
     * – Master categories are never deletable.
     * – Users can only delete their own user categories.
     */
    public function destroy(Category $category)
    {
        $user = Auth::user();

        if ($category->type === 'master') {
            return response()->json(['message' => 'Master categories cannot be deleted.'], 403);
        }

        if ((string) $category->user_id !== (string) $user?->getKey()) {
            return response()->json(['message' => 'You can only delete your own categories.'], 403);
        }

        $category->delete();

        return response()->noContent();
    }
}
