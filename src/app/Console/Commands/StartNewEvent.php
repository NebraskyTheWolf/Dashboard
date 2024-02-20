<?php

namespace App\Console\Commands;

use App\Events\AkceUpdate;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StartNewEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-new-event';

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
        $events = Events::all();
        foreach ($events as $event) {
            if ($event->begin != null) {
                if (Carbon::parse($event->begin)->isPast()
                    && ($event->status !== "ENDED")
                    && ($event->status !== "CANCELLED")
                    && ($event->status !== "FINISHED")) {
                    $event->update(
                        [
                            'status' => 'STARTED'
                        ]
                    );

                    event(new AkceUpdate($event));
                }
            }
        }
    }
}
