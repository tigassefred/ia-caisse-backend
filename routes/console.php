<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();


\Illuminate\Support\Facades\Schedule::command('app:update-defer-pay')->dailyAt('02:28');
\Illuminate\Support\Facades\Schedule::command('app:close-casher')->dailyAt('02:34');

\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:23');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:25');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:30');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:36');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:37');
\Illuminate\Support\Facades\Schedule::command('app:test')->dailyAt('01:43');

