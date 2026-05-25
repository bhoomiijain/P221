<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $rec)
    {
        return response()->json([
            'featured' => $rec->featured(8),
            'trending' => $rec->trending(8),
            'seasonal' => $rec->seasonal(4),
            'for_you' => $rec->forMedicine($request->get('medicine_id'), 6),
        ]);
    }
}
