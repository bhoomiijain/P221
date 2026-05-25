<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\HealthTip;
use App\Models\ShopBanner;
use App\Models\ShopTestimonial;
use App\Services\CustomerCatalogService;
use App\Services\RecommendationService;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function __invoke(RecommendationService $rec, CustomerCatalogService $catalog)
    {
        $offers = ShopBanner::where('active', true)->orderBy('sort_order')->get()
            ->map(fn ($b) => [
                'title' => $b->title,
                'code' => $b->code,
                'color' => $b->color ?? 'teal',
            ])->values()->all();

        if ($offers === []) {
            $offers = [
                ['title' => '20% Off Pain Relief', 'code' => 'PAIN20', 'color' => 'teal'],
                ['title' => 'Free Delivery ₹500+', 'code' => 'FREESHIP', 'color' => 'rose'],
                ['title' => 'First Order 15% Off', 'code' => 'WELCOME15', 'color' => 'amber'],
            ];
        }

        $testimonials = ShopTestimonial::where('active', true)->orderBy('sort_order')->get()
            ->map(fn ($t) => [
                'name' => $t->name,
                'text' => $t->text,
                'rating' => (int) $t->rating,
            ])->values()->all();

        $healthTips = HealthTip::where('active', true)->orderBy('sort_order')->get()
            ->map(fn ($t) => ['title' => $t->title, 'body' => $t->body])->values()->all();

        return Inertia::render('Customer/Landing', [
            'featured' => $rec->featured(8),
            'trending' => $rec->trending(8),
            'categories' => $catalog->categories(),
            'offers' => $offers,
            'testimonials' => $testimonials ?: [
                ['name' => 'Priya S.', 'text' => 'Fast delivery and genuine medicines.', 'rating' => 5],
            ],
            'healthTips' => $healthTips ?: [
                ['title' => 'Stay Hydrated', 'body' => 'Drink enough water daily.'],
            ],
        ]);
    }

    public function emergency()
    {
        return Inertia::render('Customer/Emergency', [
            'nearbyPharmacies' => [
                ['name' => 'City Care Pharmacy', 'distance' => '0.8 km', 'phone' => '+91 98765 43210', 'open' => true],
                ['name' => 'MedPlus Express', 'distance' => '1.2 km', 'phone' => '+91 98765 43211', 'open' => true],
                ['name' => 'Apollo Pharmacy', 'distance' => '2.1 km', 'phone' => '+91 98765 43212', 'open' => false],
            ],
            'emergencyNumbers' => [
                ['label' => 'Ambulance', 'number' => '108'],
                ['label' => 'Medical Helpline', 'number' => '102'],
                ['label' => 'Poison Control', 'number' => '1066'],
            ],
        ]);
    }
}
