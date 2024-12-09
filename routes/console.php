<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('toilets:release-expired', function () {
    $this->info(now()." : toilets release!");
})->everyMinute()->appendOutputTo(storage_path('schedule-log.txt'));
