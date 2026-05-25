<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\CustomerReview;
use App\Models\Medicine;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(string $medicineId)
    {
        $reviews = CustomerReview::where('medicine_id', $medicineId)
            ->with('user:id,name')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json(['reviews' => $reviews]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => 'required|string',
            'order_id' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $verified = false;
        if (! empty($data['order_id'])) {
            $verified = CustomerOrder::where('user_id', auth()->id())
                ->where('_id', $data['order_id'])
                ->exists();
        }

        $review = CustomerReview::create([
            ...$data,
            'user_id' => auth()->id(),
            'verified_purchase' => $verified,
        ]);

        $medicine = Medicine::find($data['medicine_id']);
        if ($medicine) {
            $count = CustomerReview::where('medicine_id', $medicine->getKey())->count();
            $avg = CustomerReview::where('medicine_id', $medicine->getKey())->avg('rating');
            $medicine->update(['review_count' => $count, 'rating_avg' => round($avg, 1)]);
        }

        return response()->json(['review' => $review], 201);
    }
}
