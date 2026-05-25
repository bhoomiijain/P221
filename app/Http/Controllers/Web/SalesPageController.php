<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Inertia\Inertia;

class SalesPageController extends Controller
{
    public function __invoke()
    {
        $sales = Sale::query()
            ->where('user_id', auth()->id())
            ->with('user', 'items.batch.medicine')
            ->latest()
            ->paginate(25);

        $totalRevenue = Sale::query()->where('user_id', auth()->id())->sum('total_amount');

        return Inertia::render('Sales/Index', [
            'sales'        => $sales,
            'totalRevenue' => (float)(string) $totalRevenue,
        ]);
    }
}
