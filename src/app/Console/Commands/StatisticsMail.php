<?php

namespace App\Console\Commands;

use App\Mail\WeeklyStatistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Orchid\Platform\Models\User;

/**
 * Class StatisticsMail
 *
 * An artisan console command class for sending statistics emails.
 *
 * @package App\Console\Commands
 */
class StatisticsMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:statistics-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (env('APP_TEST_MAIL', false)) {
            Mail::to("vakea@fluffici.eu")->send(new WeeklyStatistic());
            printf('Sending as debug to "vakea@fluffici.eu"');
        } else {
            $users = User::all();
            foreach ($users as $user) {
                if ($user->hasAccess('platform.systems.eshop')) {
                    Mail::to($user->email)
                        ->locale($user->getLanguage())
                        ->send(new WeeklyStatistic());
                }
            }
            printf('Sending as email globally.');
        }
    }
}
