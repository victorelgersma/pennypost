<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// where is the messages:deliver command even defined though? 

Schedule::command('messages:deliver')
    ->weeklyOn(5, '12:00') // Friday
    ->timezone('UTC');