<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\SyncController;
use App\Http\Controllers\Btoc\UserController;
use App\Http\Controllers\Debug\LogViewerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('OK', 200);
});

Route::get('/policy', function () {
    return view('policy');
})->name('policy');

// ── Auth ───────────────────────────────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Btoc admin (auth + active check required) ─────────────────────────────────
Route::prefix('btoc')->name('btoc.')->middleware(['auth', 'active'])->group(function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Shops
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/',           [ShopController::class, 'index'])->name('index');
        Route::get('/create',     [ShopController::class, 'create'])->name('create');
        Route::post('/',          [ShopController::class, 'store'])->name('store');
        Route::get('/{id}',       [ShopController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [ShopController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [ShopController::class, 'update'])->name('update');
        Route::delete('/{id}',    [ShopController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/nextengine-connection', [ShopController::class, 'storeNextEngineConnection'])->name('nextengine_connection');
        Route::post('/{id}/test-connection', [ShopController::class, 'testConnection'])->name('test_connection');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',        [OrderController::class, 'index'])->name('index');
        Route::get('/{id}',    [OrderController::class, 'show'])->name('show');
        Route::put('/{id}',    [OrderController::class, 'update'])->name('update');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
    });

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');

    // Sync
    Route::prefix('shops/{shopId}/sync')->name('sync.')->group(function () {
        Route::post('/orders',    [SyncController::class, 'syncOrders'])->name('orders');
        Route::post('/inventory', [SyncController::class, 'syncInventory'])->name('inventory');
    });
    Route::get('/sync/history',      [SyncController::class, 'history'])->name('sync.history');
    Route::get('/sync/history/{id}', [SyncController::class, 'historyDetail'])->name('sync.history.detail');
    Route::post('/sync/retry/{id}',  [SyncController::class, 'retryOrder'])->name('sync.retry');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                        [UserController::class, 'index'])->name('index');
        Route::get('/create',                  [UserController::class, 'create'])->name('create');
        Route::post('/',                       [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit',               [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',                    [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',                 [UserController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/change-password',    [UserController::class, 'changePassword'])->name('change_password');
        Route::put('/{id}/change-password',    [UserController::class, 'updatePassword'])->name('update_password');
    });
});

// ── NextEngine OAuth ───────────────────────────────────────────────────────────
// connect requires auth — only logged-in users may initiate an OAuth flow
Route::get('/nextengine/connect', [ShopController::class, 'connect'])
    ->middleware('auth')
    ->name('nextengine.connect');
// callback has no auth middleware — NE redirects here after sign-in (external browser hop)
// shop_id is validated via session nonce set in connect(), NOT from the query string
Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');

// ── Yahoo Shopping OAuth ───────────────────────────────────────────────────────
Route::get('/yahoo/connect', [ShopController::class, 'connectYahoo'])
    ->middleware('auth')
    ->name('yahoo.connect');
Route::get('/yahoo/callback', [ShopController::class, 'callbackYahoo'])->name('yahoo.callback');

// ── Shopify OAuth ──────────────────────────────────────────────────────────────
Route::get('/shopify/connect', [ShopController::class, 'connectShopify'])
    ->middleware('auth')
    ->name('shopify.connect');
Route::get('/shopify/callback', [ShopController::class, 'callbackShopify'])->name('shopify.callback');

// ── Debug / Logs ───────────────────────────────────────────────────────────────
Route::get('/debug/logs', [LogViewerController::class, 'index'])->name('debug.logs');
Route::get('/debug/logs/api/{id}', [LogViewerController::class, 'apiDetail'])->name('debug.logs.api');
Route::get('/debug/logs/state/{id}', [LogViewerController::class, 'stateDetail'])->name('debug.logs.state');
