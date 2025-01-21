<?php

namespace App\Console;

use App\Console\Commands\RenewIps;
use App\Http\Controllers\Ads\CampaignsController;
use App\Http\Controllers\AdsHashesController;
use App\Http\Controllers\CurrenciesController;
use App\Http\Controllers\Invoices\InvoicesAdvertisersController;
use App\Http\Controllers\ScriptsController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        RenewIps::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // $schedule->command('inspire')->hourly();

        $schedule->call(fn() => AdsHashesController::cleanup())->everyTenMinutes();
        $schedule->call(fn() => CampaignsController::cleanupTracking())->daily();
        $schedule->call(fn() => InvoicesAdvertisersController::confirmPayments())->everyMinute();
        $schedule->call(fn() => CampaignsController::notifyExpiredCampaigns())->everyFiveMinutes();
        $schedule->call(fn() => CurrenciesController::UpdateBlockCount())->everyMinute();
        $schedule->call(fn() => ScriptsController::cleanupBlockedReferrers())->everyTwoHours();

        match (config('ads.exchange_rates_cache_age')) {
            '1min' => $schedule->call(fn() => CurrenciesController::ResetExchangeRates())->everyMinute(),
            '5min' => $schedule->call(fn() => CurrenciesController::ResetExchangeRates())->everyFiveMinutes(),
            '30sec' => $schedule->call(function () {
                CurrenciesController::ResetExchangeRates();
                sleep(30);
                CurrenciesController::ResetExchangeRates();
            })->everyMinute()
        };
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
