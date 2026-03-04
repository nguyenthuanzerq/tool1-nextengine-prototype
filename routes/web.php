<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\SyncController;
use App\Http\Controllers\Btoc\EmailSettingController;

Route::get('/', function () {return redirect('/btoc/dashboard');});

Route::prefix('btoc')->name('btoc.')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ================= ORDER =================
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::post('/register-tracking', [OrderController::class, 'registerTracking'])->name('registerTracking');

    // ================= SHOP =================
    Route::get('/shops', [ShopController::class, 'shops'])->name('shops');
    Route::get('/shop/create', [ShopController::class, 'shopCreate'])->name('shop.create');
    Route::post('/shop/store', [ShopController::class, 'shopStore'])->name('shop.store');
    Route::get('/shop/{id}/edit', [ShopController::class, 'shopEdit'])->name('shop.edit');
    Route::post('/shop/{id}/update', [ShopController::class, 'shopUpdate'])->name('shop.update');
    Route::get('/shops/{id}', [ShopController::class, 'show'])->name('shops.show');

    // ================= INVENTORY =================
    Route::get('/inventory', [InventoryController::class, 'inventoryShipment'])->name('inventory');
    Route::post('/inventory/refresh', [InventoryController::class, 'refreshInventory'])->name('inventory.refresh');

    // ================= SYNC =================
    Route::post('/manual-sync', [SyncController::class, 'manualSync'])->name('manualSync');
    Route::get('/sync-detail/{id}', [SyncController::class, 'syncDetail'])->name('sync.detail');
    Route::get('/sync-history', [SyncController::class, 'syncHistory'])->name('sync.history');

    // ================= EMAIL =================
    Route::get('/email-settings', [EmailSettingController::class, 'emailSettings'])->name('email.settings');
    Route::post('/email-settings', [EmailSettingController::class, 'emailSettings']);
});


// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');
