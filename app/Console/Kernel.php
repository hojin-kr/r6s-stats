<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\rank;
use Illuminate\Support\Facades\Redis;
use App\LineNoti;

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

    //높은 스케쥴링 횟수
    const HIGHLEN = 100;

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
            Log::info('schedule:seasonAllRenew'.$len);
            if ($len > 0) {
                if ($len > static::HIGHLEN) {
                    Log::info('높은 스케쥴링 감지 schedule:seasonAllRenew:'.$len);
                    LineNoti::send('높은 스케쥴링 감지 schedule:seasonAllRenew:'.$len);
                }
                $list = [];
                for($i = 0; $i < $len; $i++) {
                    array_push($list, Redis::lpop('schedule:seasonAllRenew'));
                }   
                foreach ($list as $value) {
                    if (!empty($value)) {   
                        rank::seasonAllRenew($value);
                    }
                }
                Log::info('schedule:seasonAllRenew:'.$len);
            }
        })->everyMinute()->runInBackground();;

        //데일리 누적 활성 사용자
        $schedule->call(function () {
            $len = Redis::llen('active');
            if ($len > 0) {
                Log::info('today active user:'.$len);
                LineNoti::send('누적 활성 사용자 : '.$len);
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
