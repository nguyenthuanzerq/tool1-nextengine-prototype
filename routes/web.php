<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Btoc\DashboardController;
use App\Http\Controllers\Btoc\OrderController;
use App\Http\Controllers\Btoc\ShopController;
use App\Http\Controllers\Btoc\InventoryController;
use App\Http\Controllers\Btoc\SyncController;
use App\Http\Controllers\Btoc\EmailSettingController;

// =================  DEBUG TẠM THỜI — XÓA SAU KHI FIX XONG =================
// Truy cập: nextenginehub.com/debug-log?key=ne-debug-2026
Route::get('/debug-log', function () {
    if (request('key') !== 'ne-debug-2026') abort(403);

    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) {
        return '<pre>Log file không tồn tại: ' . $logPath . '</pre>';
    }

    $lines = array_slice(file($logPath), -150);
    return '<pre style="background:#111;color:#eee;padding:20px;font-size:12px;word-wrap:break-word;">'
        . htmlspecialchars(implode('', $lines))
        . '</pre>';
});

Route::get('/debug-env', function () {
    if (request('key') !== 'ne-debug-2026') abort(403);

    return response()->json([
        'APP_ENV'        => env('APP_ENV'),
        'APP_DEBUG'      => env('APP_DEBUG'),
        'DB_CONNECTION'  => env('DB_CONNECTION'),
        'DB_HOST'        => env('DB_HOST'),
        'DB_DATABASE'    => env('DB_DATABASE'),
        'NE_API_URI'     => env('NEXT_ENGINE_API_URI') ? 'SET' : 'NOT SET',
        'NE_CLIENT_ID'   => env('NEXT_ENGINE_CLIENT_ID') ? 'SET' : 'NOT SET',
        'php_version'    => phpversion(),
        'laravel'        => app()->version(),
        'db_ok'          => (function() {
            try { \DB::connection()->getPdo(); return true; }
            catch (\Exception $e) { return $e->getMessage(); }
        })(),
        'migrations_ran' => (function() {
            try {
                return [
                    'orders_status'    => \Schema::hasColumn('orders', 'status'),
                    'orders_shipped_at'=> \Schema::hasColumn('orders', 'shipped_at'),
                    'shops_exists'     => \Schema::hasTable('shops'),
                    'channels_exists'  => \Schema::hasTable('channels'),
                ];
            } catch (\Exception $e) { return $e->getMessage(); }
        })(),
    ]);
});

Route::get('/debug-enable', function () {
    if (request('key') !== 'ne-debug-2026') abort(403);

    $envPath = base_path('.env');
    if (!file_exists($envPath)) return 'File .env không tìm thấy';

    $content = file_get_contents($envPath);
    $content = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=true', $content);
    file_put_contents($envPath, $content);

    return 'APP_DEBUG=true đã được bật. Reload trang dashboard để xem lỗi chi tiết.';
});
// =================  END DEBUG =================

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
