<?php

namespace App\Console;

use App\Events\ShopperCheckedout;
use App\Models\Shopper\Shopper;
use App\Models\Shopper\Status;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {

            $shoppers = Shopper::where('check_in', '<',  now()->subHours(2))->where('status_id', Status::where('name', 'Active')->first()->id);
            $statusCompleted = Status::where('name', 'Completed')->first()->id;

            if($shoppers->count()) {

                foreach($shoppers->get() as $shopper) {

                    $shopper->status_id = $statusCompleted;
                    $shopper->check_out = date('Y-m-d G:i:s');
                    $shopper->save();

                    \Log::info('Scheduled job::setting to completed automatically. loc id: ' . $shopper->location_id . ', shopper id: ' . $shopper->id);

                    ShopperCheckedout::dispatch([
                        'location_id' => $shopper->location_id,
                        'id' => $shopper->id,
                    ]);
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
