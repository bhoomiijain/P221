<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\CustomerCatalogService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, CustomerCatalogService $catalog)
    {
        $paginator = $catalog->list($request->only([
            'q', 'category_id', 'brand', 'prescription_required',
            'min_price', 'max_price', 'in_stock', 'sort', 'page',
        ]), (int) $request->get('per_page', 12));

        return response()->json($paginator);
    }

    public function show(string $id, CustomerCatalogService $catalog, RecommendationService $rec)
    {
        $medicine = Medicine::with('category')->findOrFail($id);

        return response()->json([
            'product' => $catalog->formatMedicine($medicine, true),
            'related' => $rec->forMedicine($id, 6),
            'bought_together' => $rec->frequentlyBoughtTogether($id),
        ]);
    }

    public function suggestions(Request $request, CustomerCatalogService $catalog)
    {
        return response()->json([
            'suggestions' => $catalog->searchSuggestions($request->get('q', '')),
        ]);
    }

    public function categories(CustomerCatalogService $catalog)
    {
        return response()->json(['categories' => $catalog->categories()]);
    }

    public function brands()
    {
        $brands = Medicine::query()->whereNotNull('brand')->distinct('brand')->pluck('brand');

        return response()->json(['brands' => $brands]);
    }
}
