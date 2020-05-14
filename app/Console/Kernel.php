<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\R6SStatsController;
use Illuminate\Support\Facades\Redis;

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
        //백그라운드에서 연산, 요청이 필요한 유저 처리
        $schedule->call(function () {
            $len = Redis::llen('schedule:seasonAllRenew');
            if ($len > 0) {
                Log::info('now schedule:seasonAllRenew len:'.$len);
            }
            $list = [];
            for($i = 0; $i < $len; $i++) {
                array_push($list, Redis::lpop('schedule:seasonAllRenew'));
            }
            foreach ($list as $value) {
                if (!empty($value)) {   
                    R6SStatsController::seasonAllRenew($value);
                }
            }
        })->everyMinute();
        // ->runInBackground();

        //active 유저 백그라운드 처리
        $schedule->call(function () {
            $len = Redis::llen('active');
            if ($len > 0) {
                Log::info('today active user:'.$len);
            }
        })->daily();
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
