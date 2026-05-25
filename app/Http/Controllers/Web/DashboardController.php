<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Services\InventoryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(InventoryService $inventory)
    {
        $user = auth()->user();

        if ($user && $user->isSupplier()) {
            return $this->supplierDashboard();
        }

        return $this->pharmacistDashboard($inventory);
    }

    private function pharmacistDashboard(InventoryService $inventory)
    {
        $userId = auth()->id();
        $todaySales = (float)(string) Sale::query()->where('user_id', $userId)->where('created_at', '>=', today())->sum('total_amount');
        $totalSales = (float)(string) Sale::query()->where('user_id', $userId)->sum('total_amount');

        // 7-day revenue for sparkline
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($userId) {
            $date = Carbon::today()->subDays($daysAgo);
            $total = (float)(string) Sale::query()
                ->where('user_id', $userId)
                ->where('created_at', '>=', $date->copy()->startOfDay())
                ->where('created_at', '<=', $date->copy()->endOfDay())
                ->sum('total_amount');
            return [
                'date'  => $date->format('d M'),
                'total' => round($total, 2),
            ];
        });

        // Recent sales mapped to "prescriptions" for UI
        $recentSales = Sale::query()
            ->where('user_id', $userId)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Sale $sale) => [
                'id'             => $sale->getKey(),
                'total_amount'   => (float)(string) $sale->total_amount,
                'payment_method' => $sale->payment_method ?? 'cash',
                'customer_name'  => $sale->customer_name,
                'cashier'        => $sale->user?->name,
                'created_at'     => $sale->created_at?->diffForHumans(),
            ]);

        $lowStockCount = $inventory->lowStock()->count();
        $expiringCount = $inventory->expiringBatches()->count();
        
        // Find single medicine with lowest stock > 0 but under threshold
        $lowestStockMedicine = Medicine::with('category')
            ->get()
            ->filter(function ($medicine) use ($userId) {
                if ($medicine->user_id === $userId) return true;
                return Batch::where('user_id', $userId)->where('medicine_id', $medicine->getKey())->exists();
            })
            ->map(function ($med) use ($userId) {
                return [
                    'id'       => $med->id,
                    'name'     => $med->name,
                    'image'    => $med->image,
                    'stock'    => (int) Batch::where('user_id', $userId)->where('medicine_id', $med->getKey())->sum('quantity'),
                    'category' => $med->category?->name ?? 'Medicine'
                ];
            })
            ->sortBy('stock')
            ->first();

        // Expiring in next 7 days for bar chart
        $expiringChart = collect(range(0, 6))->map(function ($daysAhead) use ($userId) {
            $date = Carbon::today()->addDays($daysAhead);
            $count = Batch::query()
                ->where('user_id', $userId)
                ->where('quantity', '>', 0)
                ->where('expiry_date', '>=', $date->copy()->startOfDay())
                ->where('expiry_date', '<=', $date->copy()->endOfDay())
                ->count();
            return $count;
        })->toArray();

        // Calculate active batches per category
        $categoriesData = Medicine::with('category')
            ->get()
            ->filter(function ($medicine) use ($userId) {
                if ($medicine->user_id === $userId) return true;
                return Batch::where('user_id', $userId)->where('medicine_id', $medicine->getKey())->exists();
            })
            ->groupBy(fn($med) => $med->category?->name ?? 'Other')
            ->map(fn($meds) => $meds->sum(fn($med) => (int) Batch::where('user_id', $userId)->where('medicine_id', $med->getKey())->sum('quantity')))
            ->sortByDesc(fn($val) => $val)
            ->take(3);

        $totalStock = max(1, $categoriesData->sum()); // prevent division by zero
        $topCategories = $categoriesData->map(function ($stock, $name) use ($totalStock) {
            return [
                'name' => $name,
                'percentage' => round(($stock / $totalStock) * 100)
            ];
        })->values();

        $lostAmount = Batch::query()
            ->where('user_id', $userId)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', today())
            ->get()
            ->sum(function ($batch) {
                return $batch->quantity * $batch->unit_price;
            });

        return Inertia::render('Dashboard/PharmacistIndex', [
            'metrics' => [
                'todaySales'     => $todaySales,
                'totalSales'     => $totalSales,
                'activeBatches'  => Batch::query()->where('user_id', $userId)->where('quantity', '>', 0)->count(),
                'lowStockCount'  => $lowStockCount,
                'expiringCount'  => $expiringCount,
                'totalOrders'    => Sale::query()->where('user_id', $userId)->count(),
                'lostAmount'     => $lostAmount,
            ],
            'last7Days'      => $last7Days,
            'recentSales'    => $recentSales,
            'lowestStockMedicine' => $lowestStockMedicine,
            'expiringChart'  => $expiringChart,
            'topCategories'  => $topCategories,
        ]);
    }

    private function supplierDashboard()
    {
        $user    = auth()->user();
        $request = request();

        // ── Revenue period ──────────────────────────────────────────────
        $period = $request->get('period', 'weekly');
        [$start, $days, $format] = match ($period) {
            'monthly' => [Carbon::now()->subDays(29), 30, 'd M'],
            'yearly'  => [Carbon::now()->subMonths(11)->startOfMonth(), 12, 'M Y'],
            default   => [Carbon::now()->subDays(6)->startOfDay(), 7, 'd M'],
        };

        if ($period === 'yearly') {
            $chartData = collect(range(0, 11))->map(function ($i) use ($user) {
                $date  = Carbon::now()->subMonths(11 - $i)->startOfMonth();
                $total = (float) (string) \App\Models\SupplierOrder::query()
                    ->where('supplier_id', $user->getKey())
                    ->where('status', 'payment_received')
                    ->where('created_at', '>=', $date->copy()->startOfMonth())
                    ->where('created_at', '<=', $date->copy()->endOfMonth())
                    ->sum('total_price');
                return ['date' => $date->format('M Y'), 'total' => round($total, 2)];
            });
        } elseif ($period === 'monthly') {
            $chartData = collect(range(29, 0))->map(function ($daysAgo) use ($user) {
                $date  = Carbon::today()->subDays($daysAgo);
                $total = (float) (string) \App\Models\SupplierOrder::query()
                    ->where('supplier_id', $user->getKey())
                    ->where('status', 'payment_received')
                    ->where('created_at', '>=', $date->copy()->startOfDay())
                    ->where('created_at', '<=', $date->copy()->endOfDay())
                    ->sum('total_price');
                return ['date' => $date->format('d M'), 'total' => round($total, 2)];
            });
        } else {
            $chartData = collect(range(6, 0))->map(function ($daysAgo) use ($user) {
                $date  = Carbon::today()->subDays($daysAgo);
                $total = (float) (string) \App\Models\SupplierOrder::query()
                    ->where('supplier_id', $user->getKey())
                    ->where('status', 'payment_received')
                    ->where('created_at', '>=', $date->copy()->startOfDay())
                    ->where('created_at', '<=', $date->copy()->endOfDay())
                    ->sum('total_price');
                return ['date' => $date->format('d M'), 'total' => round($total, 2)];
            });
        }

        // ── Revenue totals ──────────────────────────────────────────────
        $todaySales = (float) (string) \App\Models\SupplierOrder::query()
            ->where('supplier_id', $user->getKey())
            ->where('status', 'payment_received')
            ->where('created_at', '>=', today())
            ->sum('total_price');

        // ── Top medicines (best sold via this supplier's fulfilled orders) ──
        $topMedicines = \App\Models\SupplierOrder::query()
            ->where('supplier_id', $user->getKey())
            ->where('status', 'payment_received')
            ->with('medicine')
            ->get()
            ->groupBy('medicine_id')
            ->map(fn($orders) => [
                'name'       => $orders->first()->medicine?->name ?? 'Unknown',
                'total_sold' => $orders->sum('quantity'),
            ])
            ->sortByDesc('total_sold')
            ->take(5)
            ->values();

        // ── Paginated & searchable recent orders ────────────────────────
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);

        $ordersQuery = \App\Models\SupplierOrder::query()
            ->where('supplier_id', $user->getKey())
            ->with(['pharmacist', 'medicine'])
            ->latest();

        // Client-side search on fetched pharmacist names
        $allOrders = $ordersQuery->get()->filter(function ($order) use ($search) {
            if (!$search) return true;
            $name = strtolower($order->pharmacist?->name ?? '');
            return str_contains($name, strtolower($search));
        })->values();

        $totalRecords = $allOrders->count();
        $totalPages   = (int) ceil($totalRecords / $perPage);
        $page         = max(1, min($page, max($totalPages, 1)));

        $recentSales = $allOrders->slice(($page - 1) * $perPage, $perPage)->map(fn($order) => [
            'id'             => $order->getKey(),
            'total_amount'   => (float) (string) $order->total_price,
            'payment_method' => $order->payment_method,
            'customer_name'  => $order->pharmacist?->name ?? 'Unknown',
            'medicine_name'  => $order->medicine?->name ?? 'Unknown',
            'status'         => $order->status,
            'created_at'     => $order->created_at?->diffForHumans(),
        ])->values();

        return Inertia::render('Dashboard/Index', [
            'metrics'      => [
                'todaySales'  => $todaySales,
                'totalOrders' => \App\Models\SupplierOrder::query()
                    ->where('supplier_id', $user->getKey())
                    ->count(),
                'pendingCount' => \App\Models\SupplierOrder::query()
                    ->where('supplier_id', $user->getKey())
                    ->where('status', 'pending')
                    ->count(),
            ],
            'topMedicines' => $topMedicines,
            'last7Days'    => $chartData,
            'recentSales'  => $recentSales,
            'pagination'   => [
                'current_page' => $page,
                'total_pages'  => max($totalPages, 1),
                'per_page'     => $perPage,
                'total'        => $totalRecords,
            ],
            'filters'      => ['search' => $search, 'period' => $period],
            'stockHealth'  => ['healthy' => 0, 'low' => 0, 'critical' => 0],
            'lowStock'     => collect(),
        ]);
    }
}

