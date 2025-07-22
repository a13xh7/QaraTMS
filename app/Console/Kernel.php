<?php

namespace App\Console;

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
        Commands\InspectMsReportTable::class,
        Commands\InspectAppsReportTable::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('jira:leadtime')
            ->hourlyAt(10)
            ->appendOutputTo(storage_path('logs/jira_leadtime.log'));

        $schedule->command('gitlab:leadtime daily')
            ->hourlyAt(20)
            ->appendOutputTo(storage_path('logs/gitlab_leadtime.log'));

        $schedule->command('gitlab:contributor monthly')
            ->hourlyAt(30)
            ->appendOutputTo(storage_path('logs/gitlab_contributor.log'));
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
