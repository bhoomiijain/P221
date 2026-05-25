<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\CustomerReview;
use App\Models\WishlistItem;
use App\Services\CustomerCatalogService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineShopController extends Controller
{
    public function index(Request $request, CustomerCatalogService $catalog)
    {
        $paginator = $catalog->list($request->only([
            'q', 'category_id', 'brand', 'prescription_required',
            'min_price', 'max_price', 'in_stock', 'sort', 'page',
        ]), 12);

        return Inertia::render('Customer/Medicines/Index', [
            'products' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
            'categories' => $catalog->categories(),
            'filters' => $request->only(['q', 'category_id', 'brand', 'sort', 'in_stock', 'prescription_required']),
            'brands' => Medicine::whereNotNull('brand')->pluck('brand')->unique()->values(),
        ]);
    }

    public function show(string $id, CustomerCatalogService $catalog, RecommendationService $rec)
    {
        $medicine = Medicine::with('category')->findOrFail($id);
        $wishlisted = auth()->check()
            ? WishlistItem::where('user_id', auth()->id())->where('medicine_id', $id)->exists()
            : false;

        return Inertia::render('Customer/Medicines/Show', [
            'product' => $catalog->formatMedicine($medicine, true),
            'related' => $rec->forMedicine($id, 6),
            'boughtTogether' => $rec->frequentlyBoughtTogether($id),
            'reviews' => CustomerReview::where('medicine_id', $id)->with('user')->latest()->limit(10)->get(),
            'wishlisted' => $wishlisted,
        ]);
    }

    public function compare(Request $request, CustomerCatalogService $catalog)
    {
        $ids = explode(',', $request->get('ids', ''));
        $products = Medicine::whereIn('_id', array_filter($ids))->get()
            ->map(fn ($m) => $catalog->formatMedicine($m, true));

        return Inertia::render('Customer/Medicines/Compare', ['products' => $products]);
    }
}
