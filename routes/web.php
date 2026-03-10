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
// Route::get('/debug-log', function () {
//     if (request('key') !== 'ne-debug-2026') abort(403);

//     $logPath = storage_path('logs/laravel.log');
//     if (!file_exists($logPath)) {
//         return '<pre>Log file không tồn tại: ' . $logPath . '</pre>';
//     }

//     $lines = array_slice(file($logPath), -150);
//     return '<pre style="background:#111;color:#eee;padding:20px;font-size:12px;word-wrap:break-word;">'
//         . htmlspecialchars(implode('', $lines))
//         . '</pre>';
// });

// Route::get('/debug-env', function () {
//     if (request('key') !== 'ne-debug-2026') abort(403);

//     return response()->json([
//         'APP_ENV'        => env('APP_ENV'),
//         'APP_DEBUG'      => env('APP_DEBUG'),
//         'DB_CONNECTION'  => env('DB_CONNECTION'),
//         'DB_HOST'        => env('DB_HOST'),
//         'DB_DATABASE'    => env('DB_DATABASE'),
//         'NE_API_URI'     => env('NEXT_ENGINE_API_URI') ? 'SET' : 'NOT SET',
//         'NE_CLIENT_ID'   => env('NEXT_ENGINE_CLIENT_ID') ? 'SET' : 'NOT SET',
//         'php_version'    => phpversion(),
//         'laravel'        => app()->version(),
//         'db_ok'          => (function() {
//             try { \DB::connection()->getPdo(); return true; }
//             catch (\Exception $e) { return $e->getMessage(); }
//         })(),
//         'migrations_ran' => (function() {
//             try {
//                 return [
//                     'orders_status'    => \Schema::hasColumn('orders', 'status'),
//                     'orders_shipped_at'=> \Schema::hasColumn('orders', 'shipped_at'),
//                     'shops_exists'     => \Schema::hasTable('shops'),
//                     'channels_exists'  => \Schema::hasTable('channels'),
//                 ];
//             } catch (\Exception $e) { return $e->getMessage(); }
//         })(),
//     ]);
// });

// Route::get('/debug-enable', function () {
//     if (request('key') !== 'ne-debug-2026') abort(403);

//     $envPath = base_path('.env');
//     if (!file_exists($envPath)) return 'File .env không tìm thấy';

//     $content = file_get_contents($envPath);
//     $content = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=true', $content);
//     file_put_contents($envPath, $content);

//     return 'APP_DEBUG=true đã được bật. Reload trang dashboard để xem lỗi chi tiết.';
// });
// =================  END DEBUG =================

// ================= AUTHENTICATION ROUTES =================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('btoc')->name('btoc.')->middleware('auth')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

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
        Route::get('/{id}', [OrderController::class, 'show'])->name('show'); 
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('edit'); 
        Route::put('/{id}', [OrderController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
    });
    // // Các thao tác (Action) với đơn hàng
    // Route::post('/orders/sync', [OrderController::class, 'sync'])->name('orders.sync');                                  // Đồng bộ đơn hàng
    // Route::post('/orders/export-instruction', [OrderController::class, 'exportInstruction'])->name('orders.export');       // Xuất CSV/Excel 作業指示書
    // Route::post('/orders/shipping-notify', [OrderController::class, 'shippingNotify'])->name('orders.shipping_notify');    // Thông báo xuất hàng
    // Route::post('/orders/register-tracking', [OrderController::class, 'registerTracking'])->name('orders.register_tracking'); // Đăng ký mã vận đơn (API cũ)

    // ================= INVENTORY =================
    Route::get('/inventory', [InventoryController::class, 'inventoryShipment'])->name('inventory');
    Route::post('/inventory/refresh', [InventoryController::class, 'refreshInventory'])->name('inventory.refresh');

    // ================= SYNC =================
    // Route::post('/manual-sync', [SyncController::class, 'manualSync'])->name('manualSync');
    // Route::get('/sync-detail/{id}', [SyncController::class, 'syncDetail'])->name('sync.detail');
    // Route::get('/sync-history', [SyncController::class, 'syncHistory'])->name('sync.history');

    // ================= EMAIL =================
    // Route::get('/email-settings', [EmailSettingController::class, 'emailSettings'])->name('email.settings');
    // Route::post('/email-settings', [EmailSettingController::class, 'emailSettings']);

    // // ================= NEXT ENGINE CALLBACK =================
    // Route::get('/shop/{id}/re-authorize', [ShopController::class, 'reAuthorize'])->name('shop.reAuthorize');
    // Route::get('/nextengine/callback', [ShopController::class, 'callback'])->name('nextengine.callback');

    // // ================ TEST CONNECTION & REFRESH TOKEN =================
    // Route::post('/shop/{id}/test-connection', [ShopController::class, 'testConnection'])->name('shop.testConnection');
    // Route::post('/shop/{id}/refresh-token', [ShopController::class, 'refreshToken'])->name('shop.refreshToken');
});


// ================= API (KHÔNG prefix btoc) =================
Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');
