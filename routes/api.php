<?php

use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// ── Public routes (no auth required) ─────────────────────────────────────────
Route::post('login', [AuthController::class, 'login']);

// Inventory alerts are moved inside auth:sanctum to ensure auth()->id() works

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware(['Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful', 'auth:sanctum'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('inventory', [InventoryController::class, 'index']);
    Route::get('inventory/alerts', [InventoryController::class, 'alerts']);
    Route::put('inventory/alerts/{id}/resolve', [InventoryController::class, 'resolveAlert']);

    // Medicine search for billing — scoped to current pharmacist's stock + selling price
    Route::get('search/medicines', [InventoryController::class, 'search']);

    // Update pharmacist's global default profit %
    Route::put('user/profit-settings', [InventoryController::class, 'updateProfitSettings']);

    Route::post('billing/checkout', [BillingController::class, 'checkout']);
    Route::get('billing/{sale}/invoice', [BillingController::class, 'invoice']);
    Route::get('billing/{sale}/receipt', [BillingController::class, 'receipt']);
    Route::get('billing/history', [BillingController::class, 'history']);

    Route::apiResource('medicines', MedicineController::class);
    Route::apiResource('batches', BatchController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show', 'store']);
});

// ── Customer shop API (public catalog + authenticated customer) ───────────────
use App\Http\Controllers\Api\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Api\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Api\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Api\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\Customer\PrescriptionController as CustomerPrescriptionController;
use App\Http\Controllers\Api\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Api\Customer\RecommendationController as CustomerRecommendationController;
use App\Http\Controllers\Api\Customer\RefillController as CustomerRefillController;
use App\Http\Controllers\Api\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Api\Customer\WishlistController as CustomerWishlistController;

Route::prefix('customer')->group(function () {
    Route::get('products', [CustomerProductController::class, 'index']);
    Route::get('products/{id}', [CustomerProductController::class, 'show']);
    Route::get('products-search/suggestions', [CustomerProductController::class, 'suggestions']);
    Route::get('categories', [CustomerProductController::class, 'categories']);
    Route::get('brands', [CustomerProductController::class, 'brands']);
    Route::get('recommendations', [CustomerRecommendationController::class, 'index']);
    Route::get('medicines/{medicineId}/reviews', [CustomerReviewController::class, 'index']);
    Route::get('orders/ai-questions', [CustomerOrderController::class, 'aiQuestions']);

    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::middleware(['Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful', 'auth:sanctum', 'customer'])->group(function () {
        Route::get('cart', [CustomerCartController::class, 'index']);
        Route::post('cart', [CustomerCartController::class, 'store']);
        Route::put('cart/{id}', [CustomerCartController::class, 'update']);
        Route::delete('cart/{id}', [CustomerCartController::class, 'destroy']);
        Route::post('cart/coupon', [CustomerCartController::class, 'applyCoupon']);

        Route::get('wishlist', [CustomerWishlistController::class, 'index']);
        Route::post('wishlist', [CustomerWishlistController::class, 'store']);
        Route::delete('wishlist/{medicineId}', [CustomerWishlistController::class, 'destroy']);
        Route::post('wishlist/{medicineId}/move-to-cart', [CustomerWishlistController::class, 'moveToCart']);

        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::post('orders', [CustomerOrderController::class, 'store']);
        Route::get('orders/{id}', [CustomerOrderController::class, 'show']);
        Route::post('orders/{id}/cancel', [CustomerOrderController::class, 'cancel']);
        Route::get('orders/{id}/invoice', [CustomerOrderController::class, 'invoice']);
        Route::post('orders/analyze-ai', [CustomerOrderController::class, 'analyzeAi']);

        Route::apiResource('addresses', CustomerAddressController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('prescriptions', [CustomerPrescriptionController::class, 'index']);
        Route::post('prescriptions', [CustomerPrescriptionController::class, 'store']);
        Route::get('prescriptions/{id}', [CustomerPrescriptionController::class, 'show']);
        Route::post('reviews', [CustomerReviewController::class, 'store']);

        Route::get('notifications', [CustomerNotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [CustomerNotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [CustomerNotificationController::class, 'markAllRead']);

        Route::get('refill-reminders', [CustomerRefillController::class, 'index']);
        Route::post('refill-reminders', [CustomerRefillController::class, 'store']);
        Route::delete('refill-reminders/{id}', [CustomerRefillController::class, 'destroy']);
    });
});
