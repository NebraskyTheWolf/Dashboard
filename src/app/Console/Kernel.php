<?php

namespace App\Console;

use App\Console\Commands\DeleteInvalidatedTokens;
use App\Console\Commands\DeleteOldProducts;
use App\Console\Commands\DeleteOldSales;
use App\Console\Commands\DeleteUsedVouchers;
use App\Console\Commands\FinishOldEvent;
use App\Console\Commands\GenerateAccountingReport;
use App\Console\Commands\GenerateMonthlyReport;
use App\Console\Commands\GenerateTransactionsReport;
use app\Console\Commands\RecurringPayment;
use App\Console\Commands\Refresh;
use App\Console\Commands\SendSchedulesEmail;
use App\Console\Commands\StartNewEvent;
use App\Console\Commands\StatisticsMail;
use App\Console\Commands\SyncAutumnFiles;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Schedule the commands for the application.
     *
     * @param Schedule $schedule The schedule instance.
     * @return void
     */
    protected function schedule(Schedule $schedule): void{
        $schedule->command(GenerateMonthlyReport::class)->monthly();
        $schedule->command(GenerateAccountingReport::class)->monthly();
        $schedule->command(GenerateTransactionsReport::class)->monthly();

        $schedule->command(Refresh::class)->daily();
        $schedule->command(StatisticsMail::class)->weekly();
        $schedule->command(SendSchedulesEmail::class)->weekly();

        $schedule->command(DeleteOldProducts::class)->everyTenSeconds();
        $schedule->command(DeleteOldSales::class)->everyTenSeconds();
        $schedule->command(FinishOldEvent::class)->everyTenSeconds();
        $schedule->command(StartNewEvent::class)->everyTenSeconds();
        $schedule->command(DeleteUsedVouchers::class)->everyTenSeconds();

        $schedule->command(RecurringPayment::class)->everyTenSeconds();

        $schedule->command(SyncAutumnFiles::class)->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
