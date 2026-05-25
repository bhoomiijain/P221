<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use App\Services\CustomerCatalogService;
use Inertia\Inertia;

class WishlistPageController extends Controller
{
    public function __invoke(CustomerCatalogService $catalog)
    {
        $items = WishlistItem::where('user_id', auth()->id())->with('medicine')->get();

        return Inertia::render('Customer/Wishlist', [
            'items' => $items->map(fn ($w) => $catalog->formatMedicine($w->medicine))->filter()->values(),
        ]);
    }
}
