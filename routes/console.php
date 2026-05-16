<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// MIND STAKING
// Schedule::command('mind:staking-daily')->dailyAt('00:10');

// ELITE BONUS
// Schedule::command('elite:daily-bonus')->dailyAt('00:15');

// ELITE V2 BONUS
// Schedule::command('elitev2:daily-bonus')->dailyAt('00:20');

// OTP EXPIRE CHECK
Schedule::command('otp:expire')->everyMinute();


// BMIND STAKING
// Schedule::command('bmind:staking-daily')->dailyAt('00:05');

// MUSD STAKING
// Schedule::command('musd:staking-daily')->dailyAt('00:25');
