<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\CustomerNotification;
use App\Models\CustomerOrder;
use App\Models\Prescription;
use App\Models\RefillReminder;
use App\Models\WishlistItem;
use App\Services\CustomerCatalogService;
use App\Services\CustomerOrderService;
use App\Services\RecommendationService;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    public function __invoke(
        CustomerOrderService $orders,
        RecommendationService $rec,
        CustomerCatalogService $catalog,
    ) {
        $userId = auth()->id();

        $recentOrders = CustomerOrder::where('user_id', $userId)->latest()->limit(5)->get();
        $wishlist = WishlistItem::where('user_id', $userId)->with('medicine')->limit(6)->get()
            ->map(fn ($w) => $w->medicine ? $catalog->formatMedicine($w->medicine) : null)->filter()->values();
        $addresses = CustomerAddress::where('user_id', $userId)->get();
        $prescriptions = Prescription::where('user_id', $userId)->latest()->limit(5)->get();
        $notifications = CustomerNotification::where('user_id', $userId)->whereNull('read_at')->limit(5)->get();

        return Inertia::render('Customer/Dashboard', [
            'recentOrders' => $recentOrders,
            'wishlist' => $wishlist,
            'addresses' => $addresses,
            'prescriptions' => $prescriptions,
            'recommended' => $rec->featured(6),
            'notifications' => $notifications,
            'unreadCount' => CustomerNotification::where('user_id', $userId)->whereNull('read_at')->count(),
        ]);
    }

    public function profile()
    {
        return Inertia::render('Customer/Profile', ['user' => auth()->user()]);
    }

    public function notifications()
    {
        $items = CustomerNotification::where('user_id', auth()->id())->latest()->paginate(20);

        return Inertia::render('Customer/Notifications', [
            'notifications' => $items,
        ]);
    }

    public function consultant()
    {
        return Inertia::render('Customer/Consultant');
    }
}
