<?php

use App\Http\Controllers\Web\BillingPageController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventoryPageController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\MedicinePageController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SalesPageController;
use App\Http\Controllers\Web\SupplierPageController;
use Illuminate\Support\Facades\Route;

// ── Guest-only routes ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'show'])->name('login');
    Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [\App\Http\Controllers\Web\RegisterController::class, 'show'])->name('register');
    Route::post('/register',[\App\Http\Controllers\Web\RegisterController::class, 'register'])->name('register.post');
});

// Avatar proxy — served via /avatar/{filename} to completely bypass any /storage symlink
Route::get('/avatar/{filename}', function ($filename) {
    $path = storage_path('app/public/avatars/' . basename($filename));
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
})->name('avatar.proxy');

// Redirect to login
Route::get('/', function () {
    return redirect('/login');
})->name('dashboard');

// ── Authenticated staff routes ────────────────────────────────────────────────
Route::middleware(['auth', 'staff'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/billing',   BillingPageController::class)->name('billing');
    Route::get('/inventory', InventoryPageController::class)->name('inventory');
    Route::get('/medicines', MedicinePageController::class)->name('medicines');
    Route::get('/suppliers', SupplierPageController::class)->name('suppliers');
    Route::get('/sales',     SalesPageController::class)->name('sales');

    // Profile routes
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar',  [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Supplier Ordering Routes
    Route::post('/supplier-orders',                    [\App\Http\Controllers\Web\SupplierOrderController::class, 'store'])->name('supplier-orders.store');
    Route::get('/supplier/orders',                     [\App\Http\Controllers\Web\SupplierOrderController::class, 'index'])->name('supplier-orders.index');
    Route::post('/supplier/orders/{order}/approve',    [\App\Http\Controllers\Web\SupplierOrderController::class, 'approve'])->name('supplier-orders.approve');
    Route::get('/suppliers/{supplierId}/order',        [\App\Http\Controllers\Web\SupplierOrderController::class, 'orderPage'])->name('supplier-order-page');
    Route::get('/supplier/inventory',                  [\App\Http\Controllers\Web\SupplierInventoryController::class, 'index'])->name('supplier-inventory.index');
    Route::post('/supplier/inventory',                 [\App\Http\Controllers\Web\SupplierInventoryController::class, 'store'])->name('supplier-inventory.store');
});
