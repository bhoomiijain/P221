<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MedicinePageController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $userId = $user?->getKey();

        // Show master categories + this user's own custom categories
        $categories = Category::query()
            ->masterOrOwned($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $medicineIds = Batch::where('user_id', $userId)
            ->pluck('medicine_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        $medicines = Medicine::query()
            ->with('category')
            ->where(function ($query) use ($userId, $medicineIds) {
                $query->whereIn('_id', $medicineIds)
                      ->orWhere('user_id', $userId);
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        // Build a user-scoped stock map (sum of ALL batches for this user, not just non-expired)
        $stockMap = Batch::where('user_id', $userId)
            ->whereIn('medicine_id', $medicines->map->getKey()->all())
            ->get()
            ->groupBy('medicine_id')
            ->map(fn ($batches) => $batches->sum('quantity'));

        // Attach user_stock to each medicine so the Products page shows the correct count
        $medicines = $medicines->map(function ($med) use ($stockMap) {
            $med->user_stock = (int) ($stockMap[$med->getKey()] ?? 0);
            return $med;
        });

        return Inertia::render('Medicines/Index', [
            'medicines'        => $medicines,
            'categories'       => $categories,
            'defaultProfitPct' => (float) ($user->default_profit_pct ?? 0),
        ]);
    }
}
