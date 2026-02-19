<?php

use App\Http\Controllers\Btoc\BtocOrderController;
use App\Http\Controllers\OrderController;

Route::get('/btoc', [BtocOrderController::class, 'index'])
    ->name('btoc.index');

Route::post('/btoc/register-tracking', [BtocOrderController::class, 'registerTracking'])
    ->name('btoc.registerTracking');

Route::post('/orders/sync-next-engine', [OrderController::class, 'syncNextEngine'])
    ->name('orders.sync-next-engine');
