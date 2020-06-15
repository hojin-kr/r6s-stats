<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\rank;
use App\Http\Controllers\operator;
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

        $schedule->call(function (){
            //활성 유저 데이터 자동 갱신
            // $active = Redis::lrange('active', 0, -1);
            // $len = Redis::llen('active');
            // foreach ($active as $user) {
            //     operator::getOperstors($user);
            //     rank::getRank($user);
            // }
            // Log::info('active user auto refresh', $active);
            // LineNoti::send('활성 사용자 자동 갱신 수행 ('.$len.')', 1);
            
            // //현재 상위 순위
            // $rankMMR = Redis::zrevrange('rank:mmr', 0, 4);
            // $rankKD =  Redis::zrevrange('rank:kd', 0, 4);
            // LineNoti::send('TOP5 MMR '.implode(', ', $rankMMR), 1);
            // LineNoti::send('TOP5 K/D '.implode(', ', $rankKD), 1);
        })->daily();

        //백그라운드에서 연산, 요청이 필요한 유저 처리
        $schedule->call(function () {
            $len = Redis::llen('schedule:seasonAllRenew');
            if ($len > 0) {
                $list = [];
                for($i = 0; $i < $len; $i++) {
                    array_push($list, Redis::lpop('schedule:seasonAllRenew'));
                }   
                foreach ($list as $value) {
                    if (!empty($value)) {   
                        rank::seasonAllRenew($value);
                    }
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
