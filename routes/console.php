<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();


//\Illuminate\Support\Facades\Schedule::command('app:update-defer-pay')->dailyAt('02:28');
//\Illuminate\Support\Facades\Schedule::command('app:close-casher')->dailyAt('02:34');

\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('03:08');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('03:09');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:11');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:13');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:15');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:17');

