<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Btoc\BtocOrderController;
use App\Http\Controllers\OrderController;

Route::prefix('btoc')->name('btoc.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [BtocOrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [BtocOrderController::class, 'index'])->name('index');

    // ================= SHOP =================
    Route::get('/shops', [BtocOrderController::class, 'shops'])->name('shops');
    Route::get('/shop/create', [BtocOrderController::class, 'shopCreate'])->name('shop.create');
    Route::post('/shop/store', [BtocOrderController::class, 'shopStore'])->name('shop.store');
    Route::get('/shop/{id}/edit', [BtocOrderController::class, 'shopEdit'])->name('shop.edit');
    Route::post('/shop/{id}/update', [BtocOrderController::class, 'shopUpdate'])->name('shop.update');
    Route::get('/shops/{id}', [BtocOrderController::class, 'show'])->name('shops.show');

    // ================= INVENTORY =================
    Route::get('/inventory', [BtocOrderController::class, 'inventoryShipment'])->name('inventory');
    Route::post('/inventory/refresh', [BtocOrderController::class, 'refreshInventory'])->name('inventory.refresh');

    // ================= TRACKING =================
    Route::post('/register-tracking', [BtocOrderController::class, 'registerTracking'])->name('registerTracking');

    // ================= SYNC =================
    Route::post('/manual-sync', [BtocOrderController::class, 'manualSync'])->name('manualSync');

    // ================= EMAIL / HISTORY =================
    Route::get('/email-settings', [BtocOrderController::class, 'emailSettings'])->name('email.settings');
    Route::post('/email-settings', [BtocOrderController::class, 'emailSettings']);

    Route::get('/sync-detail/{id}', [BtocOrderController::class, 'syncDetail'])->name('sync.detail');
    Route::get('/sync-history', [BtocOrderController::class, 'syncHistory'])->name('sync.history');
});


// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');