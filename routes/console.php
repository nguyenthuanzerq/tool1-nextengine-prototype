<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('app:sync-orders-to-next-engine')
        // ->everyTenSeconds()
        ->everyFiveMinutes()
        ->withoutOverlapping();

Schedule::command('app:pull-platform-orders')
        ->everyTenSeconds()
        ->withoutOverlapping();
