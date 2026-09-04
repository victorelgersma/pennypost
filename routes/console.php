<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;


Schedule::command('messages:deliver')
    ->weeklyOn(5, '12:00') // Friday
    ->timezone('UTC');

Schedule::command('backup:database')
        ->weeklyOn(0, '03:00') // Sunday, off-peak
        ->timezone('UTC');
