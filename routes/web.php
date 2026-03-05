<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\SyncController;
use App\Http\Controllers\Btoc\EmailSettingController;

// ================= AUTHENTICATION ROUTES =================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('btoc')->name('btoc.')->middleware('auth')->group(function () {

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

    // ================= NEXT ENGINE CALLBACK =================
    Route::get('/shop/{id}/re-authorize', [ShopController::class, 'reAuthorize'])->name('shop.reAuthorize');
    Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');
    
    // ================ TEST CONNECTION & REFRESH TOKEN =================
    Route::post('/shop/{id}/test-connection', [ShopController::class, 'testConnection'])->name('shop.testConnection');
    Route::post('/shop/{id}/refresh-token', [ShopController::class, 'refreshToken'])->name('shop.refreshToken');
});


// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');
