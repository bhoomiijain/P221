<?php

use App\Http\Controllers\Web\Customer\CartPageController;
use App\Http\Controllers\Web\Customer\CheckoutPageController;
use App\Http\Controllers\Web\Customer\CustomerAuthController;
use App\Http\Controllers\Web\Customer\CustomerDashboardController;
use App\Http\Controllers\Web\Customer\LandingController;
use App\Http\Controllers\Web\Customer\MedicineShopController;
use App\Http\Controllers\Web\Customer\OrderPageController;
use App\Http\Controllers\Web\Customer\PrescriptionPageController;
use App\Http\Controllers\Web\Customer\WishlistPageController;
use Illuminate\Support\Facades\Route;

/*
| Customer e-commerce module — /shop prefix
| Does not interfere with pharmacist/supplier staff routes.
*/

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', LandingController::class)->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.post');
        Route::get('/forgot-password', [CustomerAuthController::class, 'showForgot'])->name('forgot');
        Route::get('/verify-otp', [CustomerAuthController::class, 'showOtp'])->name('otp');
    });

    Route::get('/medicines', [MedicineShopController::class, 'index'])->name('medicines');
    Route::get('/medicines/{id}', [MedicineShopController::class, 'show'])->name('medicines.show');
    Route::get('/compare', [MedicineShopController::class, 'compare'])->name('compare');
    Route::get('/emergency', [LandingController::class, 'emergency'])->name('emergency');

    Route::middleware(['auth', 'customer'])->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
        Route::get('/cart', CartPageController::class)->name('cart');
        Route::get('/wishlist', WishlistPageController::class)->name('wishlist');
        Route::get('/checkout', CheckoutPageController::class)->name('checkout');
        Route::get('/orders', [OrderPageController::class, 'index'])->name('orders');
        Route::get('/orders/{id}', [OrderPageController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/track', [OrderPageController::class, 'track'])->name('orders.track');
        Route::get('/prescriptions', PrescriptionPageController::class)->name('prescriptions');
        Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerAuthController::class, 'updateProfile'])->name('profile.update');
        Route::get('/notifications', [CustomerDashboardController::class, 'notifications'])->name('notifications');
        Route::get('/consultant', [CustomerDashboardController::class, 'consultant'])->name('consultant');
    });
});
