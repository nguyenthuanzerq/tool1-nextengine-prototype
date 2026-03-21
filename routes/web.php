<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Btoc\EcPlatformController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\SyncController;
use App\Http\Controllers\Btoc\EmailSettingController;
use App\Http\Controllers\NextEngineConectionController;
use App\Http\Controllers\NextEngineOrderController;

// ================= AUTHENTICATION ROUTES =================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('btoc')->name('btoc.')->middleware('auth')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    // --- 0. QUẢN LÝ NỀN TẢNG ---
    Route::prefix('ec-platforms')->name('ec-platforms.')->group(function () {
        Route::get('/', [EcPlatformController::class, 'index'])->name('index');
        Route::get('/create', [EcPlatformController::class, 'create'])->name('create');
        Route::post('/', [EcPlatformController::class, 'store'])->name('store');
        Route::get('/{ec_platform}/edit', [EcPlatformController::class, 'edit'])->name('edit');
        Route::put('/{ec_platform}', [EcPlatformController::class, 'update'])->name('update');
        Route::delete('/{ec_platform}', [EcPlatformController::class, 'destroy'])->name('destroy');
    });

    // --- 1. QUẢN LÝ SHOP ---
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::get('/create', [ShopController::class, 'create'])->name('create');
        Route::post('/', [ShopController::class, 'store'])->name('store');
        Route::get('/{id}', [ShopController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ShopController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShopController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShopController::class, 'destroy'])->name('destroy');
    });

    // --- 2. XÁC THỰC API NEXTENGINE ---
    Route::get('/shop/{id}/re-authorize', [ShopController::class, 'reAuthorize'])->name('shop.reAuthorize');
    Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');
    Route::post('/shop/{id}/test-connection', [ShopController::class, 'testConnection'])->name('shop.testConnection');

    // --- 3. QUẢN LÝ ĐƠN HÀNG (管理画面 - KANRI GAMEN) ---
    // Hiển thị danh sách đơn hàng (Sidebar của bạn đang gọi route 'btoc.index')
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create'); 
        Route::post('/', [OrderController::class, 'store'])->name('store'); 
        // Route::get('/{id}', [OrderController::class, 'show'])->name('show'); 
        // Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('edit'); 
        Route::put('/{id}', [OrderController::class, 'update'])->name('update'); 
        // Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
    });
    
    // ================= INVENTORY =================
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    // Route::get('/inventory', [InventoryController::class, 'inventoryShipment'])->name('inventory');
    

    
});


// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');

Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');
Route::get('/nextengine/connect', [ShopController::class, 'connect'])->name('nextengine.connect');;
Route::get('/nextengine/sync/order', [ShopController::class, 'syncOrder'])->name('nextengine.sync.order');