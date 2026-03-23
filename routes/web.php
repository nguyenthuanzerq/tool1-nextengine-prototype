<?php

use App\Http\Controllers\Auth\LoginController;
use App\Models\Shop;
use App\Services\ECPlatforms\PlatformFactory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Btoc\AuthPlatformController;
use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\EcPlatformController;

// TEST API
Route::get('/test-refresh/{id}', function ($id) {
    $shop = Shop::findOrFail($id);
    $factory = app(PlatformFactory::class);
    $authenticator = $factory->makeAuthenticator($shop->ecPlatform->code);

    // Kiểm tra nếu hết hạn thì gọi refresh
    if ($authenticator->tokenExpired($shop)) {
        $authenticator->refreshToken($shop);
        return "Token đã hết hạn và đã được Refresh mới!";
    }

    return "Token vẫn còn hạn sử dụng.";
});

Route::post('/btoc/orders/sync/{shopId}', [OrderController::class, 'syncOrders'])->name('btoc.orders.sync');

// ================= AUTHENTICATION ROUTES =================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('btoc')->name('btoc.')->middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    // --- 1. QUẢN LÝ EC PLATFORM ---
    Route::resource('platforms', EcPlatformController::class);
    // --- 2. QUẢN LÝ SHOP ---
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::get('/create', [ShopController::class, 'create'])->name('create');
        Route::post('/', [ShopController::class, 'store'])->name('store');
        Route::get('/{id}', [ShopController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ShopController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShopController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShopController::class, 'destroy'])->name('destroy');
    });
    // --- 3. QUẢN LÝ ĐƠN HÀNG ---
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::put('/{id}', [OrderController::class, 'update'])->name('update');
        Route::post('/sync/{shopId}', [OrderController::class, 'syncOrders'])->name('sync');
    });

    // --- 4. AUTHENTICATION ---
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/{shop}/redirect',  [AuthPlatformController::class, 'redirectToProvider'])->name('redirect');
        Route::get('/{shop}/callback',  [AuthPlatformController::class, 'handleProviderCallback'])->name('callback');
    });

    Route::post('/shop/{id}/nextengine-connection', [ShopController::class, 'storeNextEngineConnection'])->name('shop.nextengine_connection');

});


