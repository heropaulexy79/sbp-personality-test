<?php

use App\Console\Commands\BillOrganisationsMonthly;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command(BillOrganisationsMonthly::class)->monthly()
    // ->environments(['staging', 'production'])
    ->runInBackground();
