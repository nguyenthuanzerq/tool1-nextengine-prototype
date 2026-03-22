<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('OK', 200);
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('btoc')->name('btoc.')->middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::get('/create', [ShopController::class, 'create'])->name('create');
        Route::post('/', [ShopController::class, 'store'])->name('store');
        Route::get('/{id}', [ShopController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ShopController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShopController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShopController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::put('/{id}', [OrderController::class, 'update'])->name('update');
    });

    Route::post('/shop/{id}/nextengine-connection', [ShopController::class, 'storeNextEngineConnection'])->name('shop.nextengine_connection');

    Route::get('/inventory', [InventoryController::class, 'inventoryShipment'])->name('inventory');
    Route::post('/inventory/refresh', [InventoryController::class, 'refreshInventory'])->name('inventory.refresh');
});

// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');

Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');
Route::get('/nextengine/connect', [ShopController::class, 'connect'])->name('nextengine.connect');
Route::get('/nextengine/sync/order', [ShopController::class, 'syncOrder'])->name('nextengine.sync.order');
